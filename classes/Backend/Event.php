<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend;

/**
 * A class that offers simple events handling functionality (dispatching and listening).
 *
 * Example:
 * ```
 * // listening on an event
 * Event::listen('some.event', function ($arg1, $arg2) {
 *     // do some stuff ...
 * });
 *
 * // dispatching an event
 * Event::dispatch('some.event', [$arg1, $arg2]);
 *
 * // check if an event has listeners
 * Event::hasListener('some.event');
 *
 * // check if an event is dispatched
 * Event::isDispatch('some.event');
 *
 * // get a registered or a new event object
 * Event::get('some.event');
 *
 * // get a all registered events
 * Event::getRegisteredEvents();
 * ```
 *
 * @package Velox\Backend
 * @since 1.2.0
 * @api
 */
class Event
{
    /**
     * Here live all bindings.
     */
    protected static array $events = [];


    /**
     * Dispatches the passed event by executing all attached listeners and passes them the passed arguments.
     *
     * @param string $event Event name.
     * @param array $arguments [optional] Arguments array.
     *      Note that the arguments will be spread (`...$args`) on the callback and an additional argument of the event name will be appended to the arguments.
     * @param object|null $callbackThis [optional] The object the callback should be bound to.
     *
     * @return void
     */
    public static function dispatch(string $event, ?array $arguments = null, ?object $callbackThis = null): void
    {
        if (static::isDispatched($event) === false) {
            static::get($event)->dispatched = true;
        }

        if (static::hasListener($event) === false) {
            return;
        }

        $callbacks = &static::get($event)->listeners;

        $parameters = array_merge(array_values($arguments ?? []), [$event]);

        // array_walk is used instead of foreach to give the possibility
        // for the callback to attach new listeners to the current event
        array_walk($callbacks, function (&$callback) use (&$callbackThis, &$parameters) {
            /** @var \Closure $callback */
            if ($callbackThis) {
                $callback->call($callbackThis, ...$parameters);

                return;
            }

            $callback(...$parameters);
        });
    }

    /**
     * Listens on the passed event and attaches the passed callback to it.
     *
     * @param string $event Event name.
     * @param callable $callback A callback to process the event.
     *
     * @return void
     */
    public static function listen(string $event, callable $callback): void
    {
        static::get($event)->listeners[] = \Closure::fromCallable($callback);
    }

    /**
     * Checks whether an event has already been dispatched or not.
     *
     * @param string $event Event name.
     *
     * @return bool
     *
     * @since 1.5.0
     */
    public static function isDispatched(string $event): bool
    {
        return static::get($event)->dispatched === true;
    }

    /**
     * Checks whether an event has any listeners or not.
     *
     * @param string $event Event name.
     *
     * @return bool
     *
     * @since 1.5.0
     */
    public static function hasListener(string $event): bool
    {
        return empty(static::get($event)->listeners) === false;
    }

    /**
     * Returns an event object by its name or creates it if it does not exist.
     * The event object consists of the following properties:
     * - `name`: The event name.
     * - `dispatched`: A boolean flag indicating whether the event has been dispatched or not.
     * - `listeners`: An array of callbacks.
     *
     * @param string $event Event name.
     *
     * @return object
     *
     * @since 1.5.0
     */
    public static function get(string $event): object
    {
        return static::$events[$event] ?? static::create($event);
    }

    /**
     * Returns array of all registered events as an array `['event.name' => $eventObject, ...]`.
     *
     * @return object[]
     */
    public static function getRegisteredEvents(): array
    {
        return static::$events;
    }

    /**
     * Creates an event object and adds it to the registered events.
     *
     * @param string $event Event name.
     *
     * @return object
     *
     * @since 1.5.0
     */
    protected static function create(string $event): object
    {
        return static::$events[$event] = (object)[
            'name'       => $event,
            'dispatched' => false,
            'listeners'  => [],
        ];
    }
}
