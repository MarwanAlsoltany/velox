<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend;

use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a fluent interface to write HTML in PHP. It also helps with creating HTML elements on the fly.
 *
 * Example:
 * ```
 * // return an HTML element by using some tag name as a static method
 * // div can be replaced by any other html element name
 * $html = HTML::div('This is a div!', ['class' => 'container']);
 *
 * // structuring deeply nested elements using wrapping methods
 * // ->condition() to make the next action conditional
 * // ->execute() to execute some logic (loops, complex if-statements)
 * // ->open() and ->close() for containing elements
 * // ->{$tagName}() or ->element() for elements, ->entity() for entities, ->comment() for comments
 * // ->echo() or ->return() for retrieving the final result
 * (new HTML())
 *     ->element('h1', 'HTML Forms', ['class' => 'title'])
 *     ->open('form', ['method' => 'POST'])
 *         ->h2('Example', ['class' => 'subtitle'])
 *         ->p('This is an example form.')
 *         ->br(null)
 *         ->condition($someVar === true)->div('The var was true')
 *         ->open('fieldset')
 *             ->legend('Form 1', ['style' => 'color: #555;'])
 *             ->label('Message: ', ['class' => 'text'])
 *             ->input(null, ['type' => 'text', 'required'])
 *             ->entity('nbsp', 2)
 *             ->input(null, ['type' => 'submit', 'value' => 'Submit'])
 *         ->close()
 *         ->condition(count($errors))
 *         ->open('ul', ['class' => 'errors'])
 *             ->execute(function () use ($errors) {
 *                 foreach ($errors as $error) {
 *                     $this->li($error);
 *                 }
 *             })
 *         ->close()
 *     ->close()
 * ->echo();
 * ```
 *
 * @method static string a(?string $content = '', array $attributes = [])
 * @method HTML a(?string $content = '', array $attributes = [])
 * @method static string abbr(?string $content = '', array $attributes = [])
 * @method HTML abbr(?string $content = '', array $attributes = [])
 * @method static string address(?string $content = '', array $attributes = [])
 * @method HTML address(?string $content = '', array $attributes = [])
 * @method static string area(?string $content = '', array $attributes = [])
 * @method HTML area(?string $content = '', array $attributes = [])
 * @method static string article(?string $content = '', array $attributes = [])
 * @method HTML article(?string $content = '', array $attributes = [])
 * @method static string aside(?string $content = '', array $attributes = [])
 * @method HTML aside(?string $content = '', array $attributes = [])
 * @method static string audio(?string $content = '', array $attributes = [])
 * @method HTML audio(?string $content = '', array $attributes = [])
 * @method static string b(?string $content = '', array $attributes = [])
 * @method HTML b(?string $content = '', array $attributes = [])
 * @method static string base(?string $content = '', array $attributes = [])
 * @method HTML base(?string $content = '', array $attributes = [])
 * @method static string bdi(?string $content = '', array $attributes = [])
 * @method HTML bdi(?string $content = '', array $attributes = [])
 * @method static string bdo(?string $content = '', array $attributes = [])
 * @method HTML bdo(?string $content = '', array $attributes = [])
 * @method static string blockquote(?string $content = '', array $attributes = [])
 * @method HTML blockquote(?string $content = '', array $attributes = [])
 * @method static string body(?string $content = '', array $attributes = [])
 * @method HTML body(?string $content = '', array $attributes = [])
 * @method static string br(?string $content = '', array $attributes = [])
 * @method HTML br(?string $content = '', array $attributes = [])
 * @method static string button(?string $content = '', array $attributes = [])
 * @method HTML button(?string $content = '', array $attributes = [])
 * @method static string canvas(?string $content = '', array $attributes = [])
 * @method HTML canvas(?string $content = '', array $attributes = [])
 * @method static string caption(?string $content = '', array $attributes = [])
 * @method HTML caption(?string $content = '', array $attributes = [])
 * @method static string cite(?string $content = '', array $attributes = [])
 * @method HTML cite(?string $content = '', array $attributes = [])
 * @method static string code(?string $content = '', array $attributes = [])
 * @method HTML code(?string $content = '', array $attributes = [])
 * @method static string col(?string $content = '', array $attributes = [])
 * @method HTML col(?string $content = '', array $attributes = [])
 * @method static string colgroup(?string $content = '', array $attributes = [])
 * @method HTML colgroup(?string $content = '', array $attributes = [])
 * @method static string data(?string $content = '', array $attributes = [])
 * @method HTML data(?string $content = '', array $attributes = [])
 * @method static string datalist(?string $content = '', array $attributes = [])
 * @method HTML datalist(?string $content = '', array $attributes = [])
 * @method static string dd(?string $content = '', array $attributes = [])
 * @method HTML dd(?string $content = '', array $attributes = [])
 * @method static string del(?string $content = '', array $attributes = [])
 * @method HTML del(?string $content = '', array $attributes = [])
 * @method static string details(?string $content = '', array $attributes = [])
 * @method HTML details(?string $content = '', array $attributes = [])
 * @method static string dfn(?string $content = '', array $attributes = [])
 * @method HTML dfn(?string $content = '', array $attributes = [])
 * @method static string dialog(?string $content = '', array $attributes = [])
 * @method HTML dialog(?string $content = '', array $attributes = [])
 * @method static string div(?string $content = '', array $attributes = [])
 * @method HTML div(?string $content = '', array $attributes = [])
 * @method static string dl(?string $content = '', array $attributes = [])
 * @method HTML dl(?string $content = '', array $attributes = [])
 * @method static string dt(?string $content = '', array $attributes = [])
 * @method HTML dt(?string $content = '', array $attributes = [])
 * @method static string em(?string $content = '', array $attributes = [])
 * @method HTML em(?string $content = '', array $attributes = [])
 * @method static string embed(?string $content = '', array $attributes = [])
 * @method HTML embed(?string $content = '', array $attributes = [])
 * @method static string fieldset(?string $content = '', array $attributes = [])
 * @method HTML fieldset(?string $content = '', array $attributes = [])
 * @method static string figcaption(?string $content = '', array $attributes = [])
 * @method HTML figcaption(?string $content = '', array $attributes = [])
 * @method static string figure(?string $content = '', array $attributes = [])
 * @method HTML figure(?string $content = '', array $attributes = [])
 * @method static string footer(?string $content = '', array $attributes = [])
 * @method HTML footer(?string $content = '', array $attributes = [])
 * @method static string form(?string $content = '', array $attributes = [])
 * @method HTML form(?string $content = '', array $attributes = [])
 * @method static string h1(?string $content = '', array $attributes = [])
 * @method HTML h1(?string $content = '', array $attributes = [])
 * @method static string h2(?string $content = '', array $attributes = [])
 * @method HTML h2(?string $content = '', array $attributes = [])
 * @method static string h3(?string $content = '', array $attributes = [])
 * @method HTML h3(?string $content = '', array $attributes = [])
 * @method static string h4(?string $content = '', array $attributes = [])
 * @method HTML h4(?string $content = '', array $attributes = [])
 * @method static string h5(?string $content = '', array $attributes = [])
 * @method HTML h5(?string $content = '', array $attributes = [])
 * @method static string h6(?string $content = '', array $attributes = [])
 * @method HTML h6(?string $content = '', array $attributes = [])
 * @method static string head(?string $content = '', array $attributes = [])
 * @method HTML head(?string $content = '', array $attributes = [])
 * @method static string header(?string $content = '', array $attributes = [])
 * @method HTML header(?string $content = '', array $attributes = [])
 * @method static string hr(?string $content = '', array $attributes = [])
 * @method HTML hr(?string $content = '', array $attributes = [])
 * @method static string html(?string $content = '', array $attributes = [])
 * @method HTML html(?string $content = '', array $attributes = [])
 * @method static string i(?string $content = '', array $attributes = [])
 * @method HTML i(?string $content = '', array $attributes = [])
 * @method static string iframe(?string $content = '', array $attributes = [])
 * @method HTML iframe(?string $content = '', array $attributes = [])
 * @method static string img(?string $content = '', array $attributes = [])
 * @method HTML img(?string $content = '', array $attributes = [])
 * @method static string input(?string $content = '', array $attributes = [])
 * @method HTML input(?string $content = '', array $attributes = [])
 * @method static string ins(?string $content = '', array $attributes = [])
 * @method HTML ins(?string $content = '', array $attributes = [])
 * @method static string kbd(?string $content = '', array $attributes = [])
 * @method HTML kbd(?string $content = '', array $attributes = [])
 * @method static string label(?string $content = '', array $attributes = [])
 * @method HTML label(?string $content = '', array $attributes = [])
 * @method static string legend(?string $content = '', array $attributes = [])
 * @method HTML legend(?string $content = '', array $attributes = [])
 * @method static string li(?string $content = '', array $attributes = [])
 * @method HTML li(?string $content = '', array $attributes = [])
 * @method static string link(?string $content = '', array $attributes = [])
 * @method HTML link(?string $content = '', array $attributes = [])
 * @method static string main(?string $content = '', array $attributes = [])
 * @method HTML main(?string $content = '', array $attributes = [])
 * @method static string map(?string $content = '', array $attributes = [])
 * @method HTML map(?string $content = '', array $attributes = [])
 * @method static string mark(?string $content = '', array $attributes = [])
 * @method HTML mark(?string $content = '', array $attributes = [])
 * @method static string meta(?string $content = '', array $attributes = [])
 * @method HTML meta(?string $content = '', array $attributes = [])
 * @method static string meter(?string $content = '', array $attributes = [])
 * @method HTML meter(?string $content = '', array $attributes = [])
 * @method static string nav(?string $content = '', array $attributes = [])
 * @method HTML nav(?string $content = '', array $attributes = [])
 * @method static string noscript(?string $content = '', array $attributes = [])
 * @method HTML noscript(?string $content = '', array $attributes = [])
 * @method static string object(?string $content = '', array $attributes = [])
 * @method HTML object(?string $content = '', array $attributes = [])
 * @method static string ol(?string $content = '', array $attributes = [])
 * @method HTML ol(?string $content = '', array $attributes = [])
 * @method static string optgroup(?string $content = '', array $attributes = [])
 * @method HTML optgroup(?string $content = '', array $attributes = [])
 * @method static string option(?string $content = '', array $attributes = [])
 * @method HTML option(?string $content = '', array $attributes = [])
 * @method static string output(?string $content = '', array $attributes = [])
 * @method HTML output(?string $content = '', array $attributes = [])
 * @method static string p(?string $content = '', array $attributes = [])
 * @method HTML p(?string $content = '', array $attributes = [])
 * @method static string param(?string $content = '', array $attributes = [])
 * @method HTML param(?string $content = '', array $attributes = [])
 * @method static string picture(?string $content = '', array $attributes = [])
 * @method HTML picture(?string $content = '', array $attributes = [])
 * @method static string pre(?string $content = '', array $attributes = [])
 * @method HTML pre(?string $content = '', array $attributes = [])
 * @method static string progress(?string $content = '', array $attributes = [])
 * @method HTML progress(?string $content = '', array $attributes = [])
 * @method static string q(?string $content = '', array $attributes = [])
 * @method HTML q(?string $content = '', array $attributes = [])
 * @method static string rp(?string $content = '', array $attributes = [])
 * @method HTML rp(?string $content = '', array $attributes = [])
 * @method static string rt(?string $content = '', array $attributes = [])
 * @method HTML rt(?string $content = '', array $attributes = [])
 * @method static string ruby(?string $content = '', array $attributes = [])
 * @method HTML ruby(?string $content = '', array $attributes = [])
 * @method static string s(?string $content = '', array $attributes = [])
 * @method HTML s(?string $content = '', array $attributes = [])
 * @method static string samp(?string $content = '', array $attributes = [])
 * @method HTML samp(?string $content = '', array $attributes = [])
 * @method static string script(?string $content = '', array $attributes = [])
 * @method HTML script(?string $content = '', array $attributes = [])
 * @method static string section(?string $content = '', array $attributes = [])
 * @method HTML section(?string $content = '', array $attributes = [])
 * @method static string select(?string $content = '', array $attributes = [])
 * @method HTML select(?string $content = '', array $attributes = [])
 * @method static string small(?string $content = '', array $attributes = [])
 * @method HTML small(?string $content = '', array $attributes = [])
 * @method static string source(?string $content = '', array $attributes = [])
 * @method HTML source(?string $content = '', array $attributes = [])
 * @method static string span(?string $content = '', array $attributes = [])
 * @method HTML span(?string $content = '', array $attributes = [])
 * @method static string strong(?string $content = '', array $attributes = [])
 * @method HTML strong(?string $content = '', array $attributes = [])
 * @method static string style(?string $content = '', array $attributes = [])
 * @method HTML style(?string $content = '', array $attributes = [])
 * @method static string sub(?string $content = '', array $attributes = [])
 * @method HTML sub(?string $content = '', array $attributes = [])
 * @method static string summary(?string $content = '', array $attributes = [])
 * @method HTML summary(?string $content = '', array $attributes = [])
 * @method static string sup(?string $content = '', array $attributes = [])
 * @method HTML sup(?string $content = '', array $attributes = [])
 * @method static string svg(?string $content = '', array $attributes = [])
 * @method HTML svg(?string $content = '', array $attributes = [])
 * @method static string table(?string $content = '', array $attributes = [])
 * @method HTML table(?string $content = '', array $attributes = [])
 * @method static string tbody(?string $content = '', array $attributes = [])
 * @method HTML tbody(?string $content = '', array $attributes = [])
 * @method static string td(?string $content = '', array $attributes = [])
 * @method HTML td(?string $content = '', array $attributes = [])
 * @method static string template(?string $content = '', array $attributes = [])
 * @method HTML template(?string $content = '', array $attributes = [])
 * @method static string textarea(?string $content = '', array $attributes = [])
 * @method HTML textarea(?string $content = '', array $attributes = [])
 * @method static string tfoot(?string $content = '', array $attributes = [])
 * @method HTML tfoot(?string $content = '', array $attributes = [])
 * @method static string th(?string $content = '', array $attributes = [])
 * @method HTML th(?string $content = '', array $attributes = [])
 * @method static string thead(?string $content = '', array $attributes = [])
 * @method HTML thead(?string $content = '', array $attributes = [])
 * @method static string time(?string $content = '', array $attributes = [])
 * @method HTML time(?string $content = '', array $attributes = [])
 * @method static string title(?string $content = '', array $attributes = [])
 * @method HTML title(?string $content = '', array $attributes = [])
 * @method static string tr(?string $content = '', array $attributes = [])
 * @method HTML tr(?string $content = '', array $attributes = [])
 * @method static string track(?string $content = '', array $attributes = [])
 * @method HTML track(?string $content = '', array $attributes = [])
 * @method static string u(?string $content = '', array $attributes = [])
 * @method HTML u(?string $content = '', array $attributes = [])
 * @method static string ul(?string $content = '', array $attributes = [])
 * @method HTML ul(?string $content = '', array $attributes = [])
 * @method static string var(?string $content = '', array $attributes = [])
 * @method HTML var(?string $content = '', array $attributes = [])
 * @method static string video(?string $content = '', array $attributes = [])
 * @method HTML video(?string $content = '', array $attributes = [])
 * @method static string wbr(?string $content = '', array $attributes = [])
 * @method HTML wbr(?string $content = '', array $attributes = [])
 *
 * @since 1.0.0
 * @api
 */
class HTML
{
    private bool $validate;

    private array $conditions;

    private array $buffer;

    private array $stack;


    /**
     * Class constructor.
     *
     * @param bool $validate Whether to validate the HTML upon return/echo or not.
     */
    public function __construct(bool $validate = true)
    {
        $this->validate   = $validate;
        $this->buffer     = [];
        $this->stack      = [];
        $this->conditions = [];
    }


    /**
     * Creates a complete HTML element (opening and closing tags) constructed from the specified parameters and pass it to the buffer.
     *
     * @param string $name A name of an HTML tag.
     * @param string|null $content [optional] The text or html content of the element, passing null will make it a self-closing tag.
     * @param string[] $attributes [optional] An associative array of attributes. To indicate a boolean-attribute (no-value-attribute), simply provide a key with value `null` or provide only a value without a key with the name of the attribute. As a shortcut, setting the value as `false` will exclude the attribute.
     *
     * @return $this
     *
     * @throws \Exception If the supplied name is invalid.
     */
    public function element(string $name, ?string $content = '', array $attributes = []): HTML
    {
        $this->agreeOrFail(
            !strlen($name),
            'Invalid name supplied to %s::%s() in %s on line %s. Tag name cannot be an empty string'
        );

        if ($this->isConditionTruthy()) {
            $tag = $content !== null
                ? '<{name}{attributes}>{content}</{name}>'
                : '<{name}{attributes} />';
            $name = strtolower(trim($name));
            $attributes = $this->stringifyAttributes($attributes);

            $this->buffer[] = $this->translateElement($tag, compact('name', 'content', 'attributes'));
        }

        return $this;
    }

    /**
     * Creates an HTML entity from the specified name and pass it to the buffer.
     *
     * @param string $name The name of the HTML entity without `&` nor `;`.
     *
     * @return $this
     *
     * @throws \Exception If the supplied name is invalid.
     */
    public function entity(string $name): HTML
    {
        $this->agreeOrFail(
            !strlen($name),
            'Invalid name supplied to %s::%s() in %s on line %s. Entity name cannot be an empty string'
        );

        if ($this->isConditionTruthy()) {
            $entity = sprintf('&%s;', trim($name, '& ;'));
            $this->buffer[] = $entity;
        }


        return $this;
    }

    /**
     * Creates an HTML comment from the specified text and pass it to the buffer.
     *
     * @param string $comment The text content of the HTML comment without `<!--` and `-->`.
     *
     * @return $this
     *
     * @throws \Exception If the supplied text is invalid.
     */
    public function comment(string $text): HTML
    {
        $this->agreeOrFail(
            !strlen($text),
            'Invalid text supplied to %s::%s() in %s on line %s. Comment text cannot be an empty string'
        );

        if ($this->isConditionTruthy()) {
            $comment = sprintf('<!-- %s -->', trim($text));
            $this->buffer[] = $comment;
        }

        return $this;
    }

    /**
     * Creates an arbitrary text-node from the specified text and pass it to the buffer (useful to add some special tags, "\<!DOCTYPE html>" for example).
     *
     * @param string $text The text to pass to the buffer.
     *
     * @return $this
     *
     * @throws \Exception If the supplied text is invalid.
     */
    public function node(string $text): HTML
    {
        $this->agreeOrFail(
            !strlen($text),
            'Invalid text supplied to %s::%s() in %s on line %s. Node text cannot be an empty string'
        );

        if ($this->isConditionTruthy()) {
            $text = trim($text);
            $this->buffer[] = $text;
        }

        return $this;
    }

    /**
     * Creates an HTML opening tag from the specified parameters and pass it to the buffer. Works in conjunction with `self::close()`.
     *
     * @param string $name A name of an HTML tag.
     * @param string[] $attributes [optional] An associative array of attributes. To indicate a boolean-attribute (no-value-attribute), simply provide a key with value `null`. Setting the value as `false` will exclude the attribute.
     *
     * @return $this
     *
     * @throws \Exception If the supplied name is invalid.
     */
    public function open(string $name, array $attributes = []): HTML
    {
        $this->agreeOrFail(
            !strlen($name),
            'Invalid name supplied to %s::%s() in %s on line %s. Tag name cannot be an empty string'
        );

        if ($this->isConditionTruthy(1)) {
            $tag = '<{name}{attributes}>';
            $name = strtolower(trim($name));
            $attributes = $this->stringifyAttributes($attributes);

            $this->buffer[] = $this->translateElement($tag, compact('name', 'attributes'));

            array_push($this->stack, $name);
        }

        return $this;
    }

    /**
     * Creates an HTML closing tag matching the last tag opened by `self::open()`.
     *
     * @return $this
     *
     * @throws \Exception If no tag has been opened.
     */
    public function close(): HTML
    {
        $this->agreeOrFail(
            !count($this->stack),
            'Not in a context to close a tag! Call to %s::%s() in %s on line %s is superfluous'
        );

        if ($this->isConditionTruthy(-1)) {
            $tag = '</{name}>';

            $name = array_pop($this->stack);

            $this->buffer[] = $this->translateElement($tag, compact('name'));
        }

        return $this;
    }

    /**
     * Takes a callback and executes it after binding $this (HTML) to it, useful for example to execute any PHP code while creating the markup.
     *
     * @param callable $callback The callback to call and bind $this to, this callback will also be passed the instance it was called on as the first parameter.
     *
     * @return $this
     */
    public function execute(callable $callback): HTML
    {
        if ($this->isConditionTruthy()) {
            $boundClosure = \Closure::fromCallable($callback)->bindTo($this);

            $boundClosure($this);
        }

        return $this;
    }

    /**
     * Takes a condition (some boolean value) to determine whether or not to create the very next element and pass it to the buffer.
     *
     * @param mixed $condition Any value that can be casted into a boolean.
     *
     * @return $this
     */
    public function condition($condition): HTML
    {
        $this->conditions[] = (bool)($condition);

        return $this;
    }

    /**
     * Determines whether or not the last set condition is truthy or falsy.
     *
     * @param int $parent [optional] A flag to indicate condition depth `[+1 = parentOpened, 0 = normal, -1 = parentClosed]`.
     *
     * @return bool The result of the current condition.
     */
    private function isConditionTruthy(int $parent = 0): bool
    {
        static $parentConditions = [];

        $result = true;

        if (!empty($this->conditions) || !empty($parentConditions)) {
            $actualCondition = $this->conditions[count($this->conditions) - 1] ?? $result;
            $parentCondition = $parentConditions[count($parentConditions) - 1] ?? $result;

            $condition = $parentCondition & $actualCondition;
            if (!$condition) {
                $result = false;
            }

            switch ($parent) {
                case +1:
                    array_push($parentConditions, $condition);
                    break;
                case -1:
                    array_pop($parentConditions);
                    break;
                case 0:
                    // NORMAL!
                    break;
            }

            array_pop($this->conditions);
        }

        return $result;
    }

    /**
     * Checks if the passed condition and throws exception if it's not truthy.
     * The message that is passed to this function should contain four `%s` placeholders for the
     * `class`, `function`, `file` and `line` of the previous caller (offset 2 of the backtrace)
     *
     * @param bool $condition
     * @param string $message
     *
     * @return void
     *
     * @throws \Exception
     */
    private function agreeOrFail(bool $condition, string $message): void
    {
        if ($condition) {
            $variables = ['class', 'function', 'file', 'line'];
            $backtrace = Misc::backtrace($variables, 2);
            $backtrace = is_array($backtrace) ? $backtrace : array_map('strtoupper', $variables);

            throw new \Exception(vsprintf($message, $backtrace));
        }
    }

    /**
     * Returns an HTML attributes string from an associative array of attributes.
     *
     * @param array $attributes
     *
     * @return string
     */
    private function stringifyAttributes(array $attributes): string
    {
        $attrStr = '';

        foreach ($attributes as $name => $value) {
            if ($value === false) {
                continue;
            }

            $attrStr .= is_string($name) && !is_null($value)
                ? sprintf(' %s="%s"', $name, $value)
                : sprintf(' %s', $value ?: $name);
        }

        return $attrStr;
    }

    /**
     * Replaces variables in the passed string with values with matching key from the passed array.
     *
     * @param string $text A string like `{var} world!`.
     * @param array $variables An array like `['var' => 'Hello']`.
     *
     * @return string A string like `Hello world!`
     */
    private function translateElement(string $text, array $variables = []): string
    {
        $replacements = [];

        foreach ($variables as $key => $value) {
            $replacements[sprintf('{%s}', $key)] = $value;
        }

        return strtr($text, $replacements);
    }

    /**
     * Asserts that the passed HTML is valid.
     *
     * @return void
     *
     * @throws \Exception If the passed html is invalid.
     */
    private function validate(string $html): void
    {
        $html = !empty($html) ? $html : '<br/>';

        $xml = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->validateOnParse = true;
        $dom->loadHTML($html);
        // $dom->saveHTML();

        $ignoredCodes = [801];
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($errors) && !in_array($errors[0]->code, $ignoredCodes)) {
            $file = Misc::backtrace('file', 3);
            $file = is_string($file) ? $file : 'FILE';

            throw new \Exception(
                vsprintf(
                    'HTML is invalid in %s! Found %s problem(s). Last LibXMLError: [level:%s/code:%s] %s',
                    [$file, count($errors), $errors[0]->level, $errors[0]->code, $errors[0]->message]
                )
            );
        }

        libxml_use_internal_errors($xml);
    }

    /**
     * Returns the created HTML elements found in the buffer and empties it.
     *
     * @return string
     *
     * @throws \Exception If not all open elements are closed or the generated html is invalid.
     */
    public function return(): string
    {
        if (count($this->stack)) {
            $file = Misc::backtrace('file', 2);
            $file = is_string($file) ? $file : 'FILE';

            throw new \Exception(
                sprintf(
                    "Cannot return HTML in %s. The following tag(s): '%s' has/have not been closed properly",
                    $file,
                    implode(', ', $this->stack)
                )
            );
        }

        $html = implode('', $this->buffer);

        $this->buffer = [];

        if ($this->validate) {
            $this->validate($html);
        }

        return $html;
    }

    /**
     * Echos the created HTML elements found in the buffer and empties it.
     *
     * @return void
     *
     * @throws \Exception If not all open elements are closed or the generated html is invalid.
     */
    public function echo(): void
    {
        echo $this->return();
    }

    /**
     * Minifies HTML buffers by removing all unnecessary whitespaces and comments.
     *
     * @param string $html
     *
     * @return string
     */
    public static function minify(string $html): string
    {
        $patterns = [
            '/(\s)+/s'          => '$1', // shorten multiple whitespace sequences
            '/>[^\S ]+/s'       => '>',  // remove spaces after tag, except one space
            '/[^\S ]+</s'       => '<',  // remove spaces before tag, except one space
            '/<(\s|\t|\r?\n)+/' => '<',  // remove spaces, tabs, and new lines after start of the tag
            '/(\s|\t|\r?\n)+>/' => '>',  // remove spaces, tabs, and new lines before end of the tag
            '/<!--(.|\s)*?-->/' => '',   // remove comments
        ];

        $minified = preg_replace(
            array_keys($patterns),
            array_values($patterns),
            $html
        );

        return trim($minified);
    }


    /**
     * Makes HTML tags available as methods on the class.
     */
    public function __call(string $method, array $arguments)
    {
        $name = $method;
        $content = $arguments[0] ?? (count($arguments) ? null : '');
        $attributes = $arguments[1] ?? [];

        return $this->element($name, $content, $attributes);
    }

    /**
     * Makes HTML tags available as static methods on the class.
     */
    public static function __callStatic(string $method, array $arguments)
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new HTML(false);
        }

        return $instance->condition(true)->{$method}(...$arguments)->return();
    }
}
