<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Helper;

use MAKS\Velox\Frontend\HTML;

/**
 * A class that dumps variables and exception in a nice formatting.
 */
class Dumper
{
    /**
     * Accent color of exceptions page and dump block.
     */
    public static string $accentColor = '#ff3a60';

    /**
     * Contrast color of exceptions page and dump block.
     */
    public static string $contrastColor = '#030035';

    /**
     * Colors of syntax tokens.
     *
     * @var string[]
     */
    public static array $syntaxHighlightColors = [
        'comment' => '#aeaeae',
        'keyword' => '#00bfff',
        'string'  => '#e4ba80',
        'default' => '#e8703a',
        'html'    => '#ab8703',
    ];

    /**
     * Additional CSS styling of syntax tokens.
     *
     * @var string[]
     */
    public static array $syntaxHighlightStyles = [
        'comment' => 'font-weight: lighter;',
        'keyword' => 'font-weight: bold;',
        'string'  => '',
        'default' => '',
        'html'    => '',
    ];

    private static array $syntaxHighlightTokens = ['comment', 'keyword', 'string', 'default', 'html'];



    /**
     * Dumps a variable and dies.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    public static function dd(...$variable): void
    {
        self::dump(...$variable);
        die;
    }

    /**
     * Dumps a variable in a nice HTML block with syntax highlighting.
     *
     * @param mixed ...$variable
     *
     * @return void The result will simply get echoed.
     */
    public static function dump(...$variable): void
    {
        $isCli = self::isCli();

        $accentColor   = self::$accentColor;
        $contrastColor = self::$contrastColor;

        if (!$isCli) {
            self::setSyntaxHighlighting();
        }

        $trace = array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1))[0];
        $trace = $trace['file'] . ':' . $trace['line'];

        $markup = [
            'traceBlock' => HTML::div($trace, [
                'style' => "background:#fff;color:{$accentColor};font-family:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;font-size:12px;padding:4px 8px;margin-bottom:18px;"
            ]),
            'dumpBlock'  => HTML::div('%s', [
                'style' => "display:table;background:{$contrastColor};color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:18px;padding:18px;margin:8px;"
            ]),
            'statsBlock' => HTML::div('START_TIME + %.2fms', [
                'style' => "display:table;background:{$accentColor};color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:12px;font-weight:bold;padding:12px;margin:8px;"
            ]),
        ];

        foreach ($variable as $dump) {
            if (!$isCli) {
                $code = highlight_string('<?php ' . self::exportVariable($dump), true);
                $html = sprintf(
                    $markup['dumpBlock'],
                    preg_replace(
                        '/&lt;\?php&nbsp;/',
                        $markup['traceBlock'],
                        $code
                    )
                );

                echo $html;
            } else {
                echo self::exportVariable($dump);
            }
        }

        $time  = (microtime(true) - START_TIME) * 1000;
        $stats = $isCli ? "\n[%.2fms]\n" : $markup['statsBlock'];
        echo sprintf($stats, $time);
    }

    /**
     * Dumps an exception in a nice HTML page or as string and exits the script.
     *
     * @param \Throwable $exception
     *
     * @return void The result will be echoed as HTML page or a string representation of the exception if the interface is CLI.
     */
    public static function dumpException(\Throwable $exception): void
    {
        if (self::isCli()) {
            echo $exception;
            exit;
        }

        self::setSyntaxHighlighting();

        $file        = $exception->getFile();
        $line        = $exception->getLine();
        $message     = $exception->getMessage();
        $trace       = $exception->getTrace();
        $traceString = $exception->getTraceAsString();
        $name        = get_class($exception);
        $filename    = basename($file);
        $lines       = null;

        if (file_exists($file)) {
            $lines = file($file);
        }

        $accentColor   = self::$accentColor;
        $contrastColor = self::$contrastColor;

        $style = ":root{--accent-color:{$accentColor};--contrast-color:{$contrastColor}}*,::after,::before{box-sizing:border-box}body{background:#fff;font-family:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;font-size:16px;line-height:1.5;margin:0}h1,h2,h3,h4,h5,h6{margin:0}h1,h2{color:var(--accent-color)}h1{font-size:32px}h2{font-size:28px}h3{color:#fff}.container{width:85vw;max-width:1200px;min-height:100vh;background:#fff;padding:7vh 3vw 10vh 3vw;margin:0 auto;overflow:hidden}.message{background:var(--accent-color);color:#fff;padding:2em 1em;margin:0 0 3em 0;}.code{overflow-y:scroll;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:14px;margin:0 0 3em 0;-ms-overflow-style: none;scrollbar-width: none}.code::-webkit-scrollbar{display:none}pre{white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word}ul{padding:2em 1em;margin:1em 0;background:var(--contrast-color)}ul li{white-space:pre;list-style-type:none;font-family:monospace}ul li span.line{display:inline-block;color:#fff;text-align:right;padding:4px 8px;user-select:none}ul li.exception-line span.line{color:var(--accent-color);font-weight:bold}ul li.exception-line span.line+code>span>span:not(:first-child){padding-bottom:3px;border-bottom:2px solid var(--accent-color)}table{width:100%;border-collapse:collapse;border-spacing:0}table th{background:var(--contrast-color);color:#fff;text-align:left;padding-top:12px;padding-bottom:12px}table td,table th{border-bottom:1px solid rgba(0,0,0,0.15);padding:6px}table tr:nth-child(even){background-color:rgba(0,0,0,0.05)}table td.number{text-align:left}table td.line{text-align:left}table td.class,table td.function{font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:14px;font-weight:700}table td.arguments span{display:inline-block;background:rgba(0,0,0,.15);color:var(--accent-color);font-style:italic;padding:2px 4px;margin:0 4px 0 0;border-radius:4px}";

        (new HTML(false))
            ->node('<!DOCTYPE html>')
            ->open('html', ['lang' => 'en'])
                ->open('head')
                    ->title('Oops, something went wrong')
                    ->style($style)
                ->close()
                ->open('body')
                    ->open('div', ['class' => 'container'])
                        ->h1("Uncaught {$name}")
                        ->p("An <b>{$name}</b> was thrown on line {$line} of file {$filename} which prevented further execution of the code.")
                        ->open('div', ['class' => 'message'])
                            ->h3($name)
                            ->p($message)
                        ->close()
                        ->h2('Thrown in:')
                        ->execute(function () use ($file, $line, $lines) {
                            /** @var HTML $this */
                            if (isset($lines)) {
                                $this->p($file);
                                $this->open('ul', ['class' => 'code']);
                                for ($i = $line - 3; $i < $line + 4; $i++) {
                                    if ($i > 0 && $i < count($lines)) {
                                        $highlightedCode = highlight_string('<?php ' . $lines[$i], true);
                                        $highlightedCode = preg_replace(
                                            ['/\n/', '/<br ?\/?>/', '/&lt;\?php&nbsp;/'],
                                            ['', '', ''],
                                            $highlightedCode
                                        );
                                        if ($i == $line - 1) {
                                            $arrow = str_pad('>', strlen("{$i}"), '=', STR_PAD_LEFT);
                                            $this
                                                ->open('li', ['class' => 'exception-line'])
                                                    ->span($arrow, ['class' => 'line'])
                                                    ->node($highlightedCode)
                                                ->close();
                                        } else {
                                            $number = strval($i + 1);
                                            $this
                                                ->open('li')
                                                    ->span($number, ['class' => 'line'])
                                                    ->node($highlightedCode)
                                                ->close();
                                        }
                                    }
                                }
                                $this->close();
                            }
                        })
                        ->h2('Stack trace:')
                        ->execute(function () use ($trace, $traceString) {
                            /** @var HTML $this */
                            if (is_array($trace)) {
                                $this->p('<i>Hover on fields with * to reveal more info.</i>');
                                $this->open('table', ['class' => 'trace'])
                                    ->open('thead')
                                        ->open('tr')
                                            ->th('No.')
                                            ->th('File *')
                                            ->th('Line')
                                            ->th('Class')
                                            ->th('Function')
                                            ->th('Arguments *')
                                        ->close()
                                    ->close()
                                    ->open('tbody')
                                    ->execute(function () use ($trace) {
                                        /** @var HTML $this */
                                        foreach ($trace as $i => $trace) {
                                            $this
                                            ->open('tr', ['class' => $i % 2 == 0 ? 'even' : 'odd'])
                                                ->td(strval($i + 1), ['class' => 'number'])
                                                ->td(isset($trace['file']) ? basename($trace['file']) : '', ['class' => 'file', 'title' => $trace['file'] ?? false])
                                                ->td(strval($trace['line'] ?? ''), ['class' => 'line'])
                                                ->td(strval($trace['class'] ?? ''), ['class' => 'class'])
                                                ->td(strval($trace['function'] ?? ''), ['class' => 'function'])
                                                ->open('td', ['class' => 'arguments'])
                                                ->execute(function () use ($trace) {
                                                    /** @var HTML $this */
                                                    if (isset($trace['args'])) {
                                                        foreach ($trace['args'] as $i => $arg) {
                                                            $this->span(gettype($arg), ['title' => print_r($arg, true)]);
                                                        }
                                                    } else {
                                                        $this->node('NULL');
                                                    }
                                                })
                                                ->close()
                                            ->close();
                                        }
                                    })
                                    ->close()
                                ->close();
                            } else {
                                $this->pre($traceString);
                            }
                        })
                    ->close()
                ->close()
            ->close()
        ->echo();

        exit;
    }

    private static function exportVariable($expression): string
    {
        $export = null;

        try {
            $export = var_export($expression, true);
        } catch (\Throwable $e) {
            $class = self::class;
            $line1 = "// {$class} failed to dump the variable. Reason: {$e->getMessage()}. " . PHP_EOL;
            $line2 = "// here is a dump of the variable using print_r()" . PHP_EOL . PHP_EOL . PHP_EOL;

            return $line1 . $line2 . print_r($expression, true);
        }

        // convert array construct to square brackets
        $afToSqPatterns = [
            '/(\()array\(/'                    => '$1[',
            '/\)(\))/'                         => ']$1',
            '/array \(/'                       => '[',
            '/^([ ]*)\)(,?)$/m'                => '$1]$2',
            '/=>[ ]?\n[ ]+\[/'                 => '=> [',
            '/([ ]*)(\'[^\']+\') => ([\[\'])/' => '$1$2 => $3',
        ];

        return preg_replace(
            array_keys($afToSqPatterns),
            array_values($afToSqPatterns),
            $export
        );
    }

    private static function setSyntaxHighlighting(): void
    {
        $tokens = self::$syntaxHighlightTokens;

        foreach ($tokens as $token) {
            $color = self::$syntaxHighlightColors[$token] ?? ini_get("highlight.{$token}");
            $style = self::$syntaxHighlightStyles[$token] ?? chr(8);

            $highlighting = sprintf('%s;%s', $color, $style);

            ini_set("highlight.{$token}", $highlighting);
        }
    }

    private static function isCli(): bool
    {
        return strpos(php_sapi_name(), 'cli') !== false;
    }
}
