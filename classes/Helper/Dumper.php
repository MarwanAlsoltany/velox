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
     *
     * @codeCoverageIgnore
     */
    public static function dd(...$variable): void
    {
        $GLOBALS['_DIE'][__METHOD__] = true;

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
        self::setSyntaxHighlighting();
        $accentColor   = self::$accentColor;
        $contrastColor = self::$contrastColor;

        $isCli = self::isCli();
        $trace = self::getValidCallerTrace();

        $blocks = [
            'traceBlock' => $isCli ? "\n// \e[33;1mTRACE:\e[0m \e[34;46m[{$trace}]\e[0m \n\n" : HTML::div($trace, [
                'style' => "background:#fff;color:{$accentColor};font-family:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;font-size:12px;padding:4px 8px;margin-bottom:18px;"
            ]),
            'dumpBlock' => $isCli ? '%s' : HTML::div('%s', [
                'style' => "display:table;background:{$contrastColor};color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:18px;padding:18px;margin:8px;"
            ]),
            'timeBlock' => $isCli ? "\n\n// \e[36mSTART_TIME\e[0m + \e[35m%.2f\e[0mms \n\n\n" : HTML::div('START_TIME + %.2fms', [
                'style' => "display:table;background:{$accentColor};color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:12px;font-weight:bold;padding:12px;margin:8px;"
            ]),
        ];

        foreach ($variable as $dump) {
            $highlightedDump = self::exportExpressionWithSyntaxHighlighting($dump, $blocks['traceBlock']);
            printf($blocks['dumpBlock'], $highlightedDump);
        }

        $time = (microtime(true) - START_TIME) * 1000;
        printf($blocks['timeBlock'], $time);
    }

    /**
     * Dumps an exception in a nice HTML page or as string and exits the script.
     *
     * @param \Throwable $exception
     *
     * @return void The result will be echoed as HTML page or a string representation of the exception if the interface is CLI.
     *
     * @codeCoverageIgnore
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
        $lines       = file_exists($file) ? file($file) : null;

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
                        ->p("<code><b>{$name}</b></code> was thrown on line <code><b>{$line}</b></code> of file <code><b>{$filename}</b></code> which prevented further execution of the code.")
                        ->open('div', ['class' => 'message'])
                            ->h3($name)
                            ->p((string)htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))
                        ->close()
                        ->h2('Thrown in:')
                        ->execute(function (HTML $html) use ($file, $line, $lines) {
                            if (isset($lines)) {
                                $html->p("File: <code><b>{$file}</b></code>");
                                $html->open('ul', ['class' => 'code']);
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
                                            $html
                                                ->open('li', ['class' => 'exception-line'])
                                                    ->span($arrow, ['class' => 'line'])
                                                    ->node($highlightedCode)
                                                ->close();
                                        } else {
                                            $number = strval($i + 1);
                                            $html
                                                ->open('li')
                                                    ->span($number, ['class' => 'line'])
                                                    ->node($highlightedCode)
                                                ->close();
                                        }
                                    }
                                }
                                $html->close();
                            }
                        })
                        ->h2('Stack trace:')
                        ->execute(function (HTML $html) use ($trace, $traceString) {
                            if (count($trace)) {
                                $html->p('<i>Hover on fields with * to reveal more info.</i>');
                                $html->open('table', ['class' => 'trace'])
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
                                    ->execute(function (HTML $html) use ($trace) {
                                        foreach ($trace as $i => $trace) {
                                            $count = (int)$i + 1;
                                            $html
                                            ->open('tr', ['class' => $count % 2 == 0 ? 'even' : 'odd'])
                                                ->td(strval($count), ['class' => 'number'])
                                                ->td(isset($trace['file']) ? basename($trace['file']) : '', ['class' => 'file', 'title' => $trace['file'] ?? false])
                                                ->td(strval($trace['line'] ?? ''), ['class' => 'line'])
                                                ->td(strval($trace['class'] ?? ''), ['class' => 'class'])
                                                ->td(strval($trace['function'] ?? ''), ['class' => 'function'])
                                                ->open('td', ['class' => 'arguments'])
                                                ->execute(function (HTML $html) use ($trace) {
                                                    if (isset($trace['args'])) {
                                                        foreach ($trace['args'] as $argument) {
                                                            $html->span(gettype($argument), [
                                                                'title' => htmlspecialchars(
                                                                    Dumper::exportExpression($argument),
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                )
                                                            ]);
                                                        }
                                                    } else {
                                                        $html->node('NULL');
                                                    }
                                                })
                                                ->close()
                                            ->close();
                                        }
                                    })
                                    ->close()
                                ->close();
                            } else {
                                $html->pre($traceString);
                            }
                        })
                    ->close()
                ->close()
            ->close()
        ->echo();

        exit;
    }

    /**
     * Dumps an expression using `var_export()` or `print_r()`.
     *
     * @param mixed $expression
     *
     * @return string
     */
    private static function exportExpression($expression): string
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
        $acToSbPatterns = [
            '/(\()array\(/'                         => '$1[',
            '/\)(\))/'                              => ']$1',
            '/array \(/'                            => '[',
            '/\(object\) array\(/'                  => '(object)[',
            '/^([ ]*)\)(,?)$/m'                     => '$1]$2',
            '/\[\n\]/'                              => '[]',
            '/\[[ ]?\n[ ]+\]/'                      => '[]',
            '/=>[ ]?\n[ ]+(\[|\()/'                 => '=> $1',
            '/=>[ ]?\n[ ]+([a-zA-Z0-9_\x7f-\xff])/' => '=> $1',
            '/(\n)([ ]*)\]\)/'                      => '$1$2  ])',
            '/([ ]*)(\'[^\']+\') => ([\[\'])/'      => '$1$2 => $3',
        ];

        return preg_replace(
            array_keys($acToSbPatterns),
            array_values($acToSbPatterns),
            $export
        );
    }

    /**
     * Dumps an expression using `var_export()` or `print_r()` with syntax highlighting.
     *
     * @param mixed $expression
     * @param string|null $phpReplacement `<?php` replacement.
     *
     * @return string
     */
    private static function exportExpressionWithSyntaxHighlighting($expression, ?string $phpReplacement = ''): string
    {
        $export = self::exportExpression($expression);

        $code = highlight_string('<?php ' . $export, true);
        $html = preg_replace(
            '/&lt;\?php&nbsp;/',
            $phpReplacement ?? '',
            $code
        );

        if (!self::isCli()) {
            // @codeCoverageIgnoreStart
            return $html;
            // @codeCoverageIgnoreEnd
        }

        $mixed = preg_replace_callback(
            '/@CLR\((#\w+)\)/',
            fn ($matches) => self::getAnsiCodeFromHexColor($matches[1]),
            preg_replace(
                ['/<\w+\s+style="color:\s*(#[a-z0-9]+)">(.*?)<\/\w+>/im', '/<br ?\/?>/', '/&nbsp;/'],
                ["\e[@CLR($1)m$2\e[0m", "\n", " "],
                $html
            )
        );

        $ansi = trim(html_entity_decode(strip_tags($mixed)));

        return $ansi;
    }

    private static function getValidCallerTrace(): string
    {
        $trace = 'Trace: N/A';

        array_filter(array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), function ($backtrace) use (&$trace) {
            static $hasFound = false;
            if (!$hasFound && in_array($backtrace['function'], ['dump', 'dd'])) {
                $trace = $backtrace['file'] . ':' . $backtrace['line'];
                $hasFound = true;

                return true;
            }

            return false;
        });

        return $trace;
    }

    private static function getAnsiCodeFromHexColor(string $color): int
    {
        $colors = [
            'black'   => ['ansi' => 30, 'rgb' => [0, 0, 0]],
            'red'     => ['ansi' => 31, 'rgb' => [255, 0, 0]],
            'green'   => ['ansi' => 32, 'rgb' => [0, 128, 0]],
            'yellow'  => ['ansi' => 33, 'rgb' => [255, 255, 0]],
            'blue'    => ['ansi' => 34, 'rgb' => [0, 0, 255]],
            'magenta' => ['ansi' => 35, 'rgb' => [255, 0, 255]],
            'cyan'    => ['ansi' => 36, 'rgb' => [0, 255, 255]],
            'white'   => ['ansi' => 37, 'rgb' => [255, 255, 255]],
            'default' => ['ansi' => 39, 'rgb' => [128, 128, 128]],
        ];

        $hexClr = ltrim($color, '#');
        $hexNum = strval(strlen($hexClr));
        $hexPos = [
            '3' => [0, 0, 1, 1, 2, 2],
            '6' => [0, 1, 2, 3, 4, 5],
        ];

        [$r, $g, $b] = [
            $hexClr[$hexPos[$hexNum][0]] . $hexClr[$hexPos[$hexNum][1]],
            $hexClr[$hexPos[$hexNum][2]] . $hexClr[$hexPos[$hexNum][3]],
            $hexClr[$hexPos[$hexNum][4]] . $hexClr[$hexPos[$hexNum][5]],
        ];

        $color = [hexdec($r), hexdec($g), hexdec($b)];

        $distances = [];
        foreach ($colors as $name => $values) {
            $distances[$name] = sqrt(
                pow($values['rgb'][0] - $color[0], 2) +
                pow($values['rgb'][1] - $color[1], 2) +
                pow($values['rgb'][2] - $color[2], 2)
            );
        }

        $colorName = '';
        $minDistance = pow(2, 30);
        foreach ($distances as $key => $value) {
            if ($value < $minDistance) {
                $minDistance = $value;
                $colorName   = $key;
            }
        }

        return $colors[$colorName]['ansi'];
    }

    /**
     * @codeCoverageIgnore
     */
    private static function setSyntaxHighlighting(): void
    {
        if (self::isCli()) {
            // use default entries for better contrast.
            return;
        }

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
        return PHP_SAPI === 'cli';
    }
}
