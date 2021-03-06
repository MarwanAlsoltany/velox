<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);



if (!function_exists('he')) {
    /**
     * Encodes the passed text with `htmlentities()`.
     *
     * @param string $text
     *
     * @return string
     */
    function he($text) {
        return htmlentities((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('hd')) {
    /**
     * Decodes the passed text with `html_entity_decode()`.
     *
     * @param string $text
     *
     * @return string
     */
    function hd($text) {
        return html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('hse')) {
    /**
     * Encodes the passed text with `htmlspecialchars()`.
     *
     * @param string $text
     *
     * @return string
     */
    function hse($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('hsd')) {
    /**
     * Decodes the passed text with `htmlspecialchars_decode()`.
     *
     * @param string $text
     *
     * @return string
     */
    function hsd($text) {
        return htmlspecialchars_decode((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('st')) {
    /**
     * Strips the passed text with `strip_tags()`.
     *
     * @param string $text
     *
     * @return string
     */
    function st($text) {
        return strip_tags((string)$text);
    }
}

if (!function_exists('nb')) {
    /**
     * Turns `\n` to `<br>` in the passed text with `nl2br()`.
     *
     * @param string $text
     *
     * @return string
     */
    function nb($text) {
        return nl2br((string)$text);
    }
}
