<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Backend\Session;

use MAKS\Velox\App;
use MAKS\Velox\Backend\Config;
use MAKS\Velox\Backend\Globals;
use MAKS\Velox\Backend\Session;
use MAKS\Velox\Frontend\HTML;

/**
 * A class that offers a simple interface to protect against Cross-Site Request Forgery.
 *
 * Example:
 * ```
 * // create, store, and return a token
 * $csrf = new CSRF();
 * $token = $csrf->token();
 *
 * // render a hidden input field containing the token
 * $html = $csrf->html();
 *
 * // check if the token is valid
 * $csrf->check();
 *
 * // validate if request token matches the stored token
 * $status = $csrf->isValid();
 * ```
 *
 * @package Velox\Backend\Session
 * @since 1.5.4
 */
class CSRF
{
    private string $name;

    private string $token;


    /**
     * Class constructor.
     *
     * @param string|null $name The name of the CSRF token (input field).
     */
    public function __construct(?string $name = null)
    {
        Session::start();

        $this->name  = $name ?? Config::get('session.csrf.name', '_token');
        $this->token = Session::get($this->name) ?? '';
    }

    /**
     * Returns the HTML input element containing the CSRF token.
     */
    public function __toString()
    {
        return $this->html();
    }


    /**
     * Returns an HTML input element containing a CSRF token after storing it in the session.
     * This method will be called automatically if the object is casted to a string.
     *
     * @return string
     */
    public function html(): string
    {
        return HTML::input(null, [
            'type'  => 'hidden',
            'name'  => $this->name,
            'value' => $this->token()
        ]);
    }

    /**
     * Generates a CSRF token, stores it in the session and returns it.
     *
     * @return string The CSRF token.
     */
    public function token(): string
    {
        $this->token = empty($this->token) ? bin2hex(random_bytes(64)) : $this->token;

        Session::get($this->name, $this->token);

        return $this->token;
    }

    /**
     * Checks whether the request token matches the token stored in the session.
     *
     * @return bool
     */
    public function check(): void
    {
        if ($this->isValid()) {
            return;
        }

        $this->fail();
    }

    /**
     * Renders 403 error page.
     *
     * @return void
     *
     * @codeCoverageIgnore Can't test methods that send headers.
     */
    public static function fail(): void
    {
        App::log('Responded with 403 to the request for "{uri}". CSRF is detected. Client IP address {ip}', [
            'uri' => Globals::getServer('REQUEST_URI'),
            'ip'  => Globals::getServer('REMOTE_ADDR'),
        ], 'system');

        App::abort(403, null, 'Invalid CSRF token!');
    }

    /**
     * Validate the request token with the token stored in the session.
     *
     * @return bool Whether the request token matches the stored one or not.
     */
    public function isValid(): bool
    {
        if ($this->isWhitelisted() || $this->isIdentical()) {
            return true;
        }

        Session::cut($this->name);

        return false;
    }

    private function isWhitelisted(): bool
    {
        $method = Globals::getServer('REQUEST_METHOD');
        $client = Globals::getServer('REMOTE_HOST') ?? Globals::getServer('REMOTE_ADDR');

        return (
            in_array($client, Config::get('session.csrf.whitelisted', [])) ||
            !in_array($method, Config::get('session.csrf.methods', []))
        );
    }

    private function isIdentical(): bool
    {
        $token = Globals::cutPost($this->name) ?? Globals::cutGet($this->name) ?? '';

        return empty($this->token) || hash_equals($this->token, $token);
    }
}
