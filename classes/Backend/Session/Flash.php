<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend\Session;

use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Helper\Misc;

/**
 * A class that offers a simple interface to write flash messages.
 *
 * Example:
 * ```
 * // write a flash message
 * $flash = new Flash();
 * $flash->message('Some message!');
 *
 * // write a flash message that will be rendered immediately
 * $flash->message('Some other message!', 'success', true);
 *
 * // write a flash message with a predefined type
 * $flash->error('Some error!', true);
 *
 * // write a flash message with your own type
 * $flash->yourCustomType('Custom type!');
 *
 * // render the flash messages
 * $flash->render();
 *
 * // render the flash messages using a custom callback
 * $flash->render(function ($text, $type) {
 *      return sprintf('<div class="alert alert-%s">%s</div>', $type, $text);
 * });
 * ```
 *
 * @package Velox\Backend\Session
 * @since 1.5.4
 *
 * @method static success(string $text, bool $now = false) Adds a `success` message to the flash.
 * @method static info(string $text, bool $now = false) Adds an `info` message to the flash.
 * @method static warning(string $text, bool $now = false) Adds a `warning` message to the flash.
 * @method static error(string $text, bool $now = false) Adds an `error` message to the flash.
 * @method static anyType(string $text, bool $now = false) Adds a message of `anyType` to the flash. `anyType` is user defined and will be used as a CSS class if the default rendering callback is used (`camelCase` will be changed to `kebab-case` automatically).
 */
class Flash
{
    private string $name;

    private array $messages;


    /**
     * Class constructor.
     *
     * @param string|null $name The name of the flash messages store (session key).
     */
    public function __construct(?string $name = null)
    {
        $this->name     = $name ?? '_flash';
        $this->messages = Globals::getSession($this->name) ?? [];

        Globals::setSession($this->name, []);
    }

    /**
     * Makes the class callable as a function, the call is forwarded to `self::message()`.
     */
    public function __invoke()
    {
        return $this->message(...func_get_args());
    }

    /**
     * Aliases some magic methods for `self::message()`.
     */
    public function __call(string $method, array $arguments)
    {
        return $this->message(
            Misc::transform($method, 'kebab'),
            $arguments[0] ?? '',
            $arguments[1] ?? false
        );
    }

    /**
     * Returns the HTML containing the flash messages.
     */
    public function __toString()
    {
        return $this->render();
    }


    /**
     * Writes a flash message to the session.
     *
     * @param string $type Message type.
     * @param string $text Message text.
     * @param bool $now [optional] Whether to write and make the message available for rendering immediately or wait for the next request.
     *
     * @return $this
     */
    public function message(string $type, string $text, bool $now = false)
    {
        $id = uniqid(md5($text) . '-');

        if ($now) {
            $this->messages[$id] = [
                'type' => $type,
                'text' => $text
            ];

            return $this;
        }

        Globals::setSession($this->name . '.' . $id, [
            'type' => $type,
            'text' => $text
        ]);

        return $this;
    }

    /**
     * Renders the flash messages using the default callback or the passed one.
     * This method will be called automatically if the object is casted to a string.
     *
     * @param callable|null $callback Rendering callback. The callback will be passed: `$text`, `$type`
     *
     * @return string The result of the passed callback or the default one.
     */
    public function render(?callable $callback = null): string
    {
        $callback = $callback ?? function ($text, $type) {
            return HTML::div($text, [
                'class' => 'flash-message ' . $type
            ]);
        };

        $html = '';

        foreach ($this->messages as $message) {
            $html .= $callback($message['text'], $message['type']);
        }

        return $html;
    }
}
