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
     * Dispatches the passed event by executing all attached callbacks and passes them the passed arguments.
     *
     * @param string $event Event name.
     * @param array $arguments [optional] Arguments array. Note that the arguments will be spread (`...$args`) on the callback.
     * @param object|null $callbackThis [optional] The object the callback should be bound to.
     *
     * @return void
     */
    public static function dispatch(string $event, ?array $arguments = null, ?object $callbackThis = null): void
    {
        if (isset(static::$events[$event]) && count(static::$events[$event])) {
            $callbacks = &static::$events[$event];
            foreach ($callbacks as $callback) {
                $parameters = array_merge(array_values($arguments ?? []), [$event]);

                if ($callbackThis) {
                    $callback->call($callbackThis, ...$parameters);
                    continue;
                }

                $callback(...$parameters);
            }
        } else {
            static::$events[$event] = [];
        }
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
        static::$events[$event][] = \Closure::fromCallable($callback);
    }

    /**
     * Returns array of all registered events as an array `['event.name' => [...$callbacks]]`.
     *
     * @return array
     */
    public static function getRegisteredEvents(): array
    {
        return static::$events;
    }
}
