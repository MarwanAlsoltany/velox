<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Helper;

use MAKS\Velox\App;
use MAKS\Velox\Frontend\HTML;
use MAKS\Velox\Helper\Misc;

/**
 * A class that dumps variables and exception in a nice formatting.
 *
 * @package Velox\Helper
 * @since 1.0.0
 */
class Dumper
{
    /**
     * Accent color of exceptions page and dump block.
     *
     * @var string
     */
    public static string $accentColor = '#ff3a60';

    /**
     * Contrast color of exceptions page and dump block.
     *
     * @var string
     */
    public static string $contrastColor = '#030035';

    /**
     * Dumper CSS styles.
     * The array contains styles for:
     * - `exceptionPage`
     * - `traceBlock`
     * - `dumpBlock`
     * - `timeBlock`
     * - `detailsBlock`
     *
     * Currently set dumper colors can be inject in CSS using the `%accentColor%` and `%contrastColor%` placeholders.
     *
     * @var array
     *
     * @since 1.5.2
     */
    public static array $styles = [
        'exceptionPage' => ":root{--light:#fff;--dark:#000;--accent-color:%accentColor%;--contrast-color:%contrastColor%;--font-normal:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;--font-mono:'Fira Code','Ubuntu Mono',Courier,monospace;--font-base-size:16px;--container-width:85vw;--container-max-width:1364px}@media (max-width:992px){:root{--font-base-size:14px;--container-width:100%;--container-max-width:100vw}}*,::after,::before{box-sizing:border-box;scrollbar-width:thin;scrollbar-color:var(--accent-color) rgba(0,0,0,.15)}::-webkit-scrollbar{width:8px;height:8px;opacity:1;-webkit-appearance:none}::-webkit-scrollbar-thumb{background:var(--accent-color);border-radius:4px}::-webkit-scrollbar-track,::selection{background:rgba(0,0,0,.15)}body{background:var(--light);color:var(--dark);font-family:var(--font-normal);font-size:var(--font-base-size);line-height:1.5;margin:0}h1,h2,h3,h4,h5,h6{margin:0}h1{color:var(--accent-color);font-size:2rem}h2{color:var(--accent-color);font-size:1.75rem}h3{color:var(--light)}p{font-size:1rem;margin:1rem 0}a{color:var(--accent-color)}a:hover{text-decoration:underline}ul{padding:1.5rem 1rem;margin:1rem 0}li{white-space:pre;list-style-type:none}pre{white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word}.monospace,code{font-family:var(--font-mono);word-wrap:break-word;word-break:break-all}.container{width:var(--container-width);max-width:var(--container-max-width);min-height:100vh;background:var(--light);padding:7vh calc((var(--container-max-width) * .03)) 10vh;margin:0 auto;overflow:hidden}.capture-section,.info-section,.trace-section{margin-bottom:3rem}.message{background:var(--accent-color);color:var(--light);padding:2rem 1rem 1rem 1rem}.scrollable{overflow-x:scroll}.code{display:block;width:max-content;min-width:100%;background:var(--contrast-color);font-family:var(--font-mono);font-size:.875rem;margin:0;overflow-y:scroll;-ms-overflow-style:none;scrollbar-width:none;cursor:initial}.code::-webkit-scrollbar{display:none}.code *{background:0 0}.code-line{display:inline-block;width:calc(3ch + (2 * .75ch));background:rgba(255,255,255,.25);color:var(--light);text-align:right;padding:.25rem .75ch;margin:0 1.5ch 0 0;user-select:none}.code-line.exception-line{color:var(--accent-color);font-weight:700}.code-line.exception-line+code>span>span:not(:first-child){padding-bottom:3px;border-bottom:2px solid var(--accent-color)}.button{display:inline-block;vertical-align:baseline;background:var(--accent-color);color:var(--light);font-size:1rem;text-decoration:none;padding:.5rem 1rem;margin:0 0 1rem 0;border:none;border-radius:2.5rem;cursor:pointer}.button:hover{background:var(--contrast-color);text-decoration:inherit}.button:last-child{margin-bottom:0}.table{width:100%;border-collapse:collapse;border-spacing:0}.table .table-cell{padding:.75rem}.table .table-head .table-cell{background:var(--contrast-color);color:var(--light);text-align:left;padding-top:.75rem;padding-bottom:.75rem}.table-cell.compact{width:1%}.table-row{background:var(--light);border-top:1px solid rgba(0,0,0,.15)}.table .table-row:hover{background:rgba(0,0,0,.065)!important}.table .table-row.additional .table-cell{padding:0}.table .table-row.odd,.table .table-row.odd+.additional{background:var(--light)}.table .table-row.even,.table .table-row.even+.additional{background:rgba(0,0,0,.035)}.table .table-row.even+.additional,.table .table-row.odd+.additional{border-top:none}.pop-up{cursor:help}.line,.number{text-align:center}.class,.function{font-size:.875rem;font-weight:700}.arguments{white-space:nowrap}.argument{display:inline-block;background:rgba(0,0,0,.125);color:var(--accent-color);font-size:.875rem;font-style:italic;padding:.125rem .5rem;margin:0 .25rem 0 0;border-radius:2.5rem}.argument:hover{background:var(--accent-color);color:var(--contrast-color)}.accordion{cursor:pointer;position:relative}.accordion-summary{width:1.5rem;height:1.5rem;background:var(--accent-color);color:var(--light);line-height:1.5rem;text-align:center;list-style:none;border-radius:50%;position:absolute;top:-2.2925rem;left:1.425rem;user-select:none;cursor:pointer}.accordion-summary:hover{background:var(--contrast-color)}.accordion-details{padding:0}",
        'traceBlock'    => "background:#fff;color:%accentColor%;font-family:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;font-size:12px;padding:4px 8px;margin-bottom:18px;",
        'dumpBlock'     => "display:table;background:%contrastColor%;color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:18px;padding:18px;margin-bottom:8px;",
        'timeBlock'     => "display:table;background:%accentColor%;color:#fff;font-family:'Fira Code','Ubuntu Mono',Courier,monospace;font-size:12px;font-weight:bold;padding:12px;margin-bottom:8px;",
        'detailsBlock'  => "background:%accentColor%;color:#fff;font-family:-apple-system,'Fira Sans',Ubuntu,Helvetica,Arial,sans-serif;font-size:12px;font-weight:bold;padding:12px;margin-bottom:8px;cursor:pointer;user-select:none;",
    ];

    /**
     * Colors of syntax tokens.
     *
     * @var array
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
     * @var array
     */
    public static array $syntaxHighlightStyles = [
        'comment' => 'font-weight: lighter;',
        'keyword' => 'font-weight: bold;',
        'string'  => '',
        'default' => '',
        'html'    => '',
    ];

    /**
     * PHP highlighting syntax tokens.
     *
     * @var string[]
     */
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
        self::dump(...$variable);

        App::terminate();
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
        $caller = self::getValidCallerTrace();
        $blocks = self::getDumpingBlocks();

        $dump = '';

        foreach ($variable as $var) {
            $trace = sprintf($blocks['traceBlock'], $caller);
            $highlightedDump = self::exportExpressionWithSyntaxHighlighting($var, $trace);
            $block = sprintf($blocks['dumpBlock'], $highlightedDump);

            $dump .= sprintf($blocks['detailsBlock'], $block);
        }

        $time = (microtime(true) - START_TIME) * 1000;
        $dump .= sprintf($blocks['timeBlock'], $time);

        if (self::isCli()) {
            echo $dump;

            return;
        }

        // @codeCoverageIgnoreStart
        (new HTML(false))
            ->open('div', ['id' => $id = 'dump-' . uniqid()])
                ->style("#{$id} * { background: transparent; padding: 0; }")
                ->div($dump)
            ->close()
        ->echo();
        // @codeCoverageIgnoreEnd
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

            App::terminate();
        }

        self::setSyntaxHighlighting();

        $reflection  = new \ReflectionClass($exception);
        $file        = $exception->getFile();
        $line        = $exception->getLine();
        $message     = $exception->getMessage();
        $trace       = $exception->getTrace();
        $traceString = $exception->getTraceAsString();
        $name        = $reflection->getName();
        $shortName   = $reflection->getShortName();
        $fileName    = basename($file);

        $style = Misc::interpolate(
            static::$styles['exceptionPage'],
            [
                'accentColor'   => static::$accentColor,
                'contrastColor' => static::$contrastColor
            ],
            '%%'
        );
        $favicon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="512" height="512"><circle cx="256" cy="256" r="256" fill="#F00" /></svg>';

        (new HTML(false))
            ->node('<!DOCTYPE html>')
            ->open('html', ['lang' => 'en'])
                ->open('head')
                    ->title('Oops! Something went wrong')
                    ->link(null, ['rel' => 'icon', 'href' => 'data:image/svg+xml;base64,' . base64_encode($favicon)])
                    ->style($style, ['type' => 'text/css'])
                ->close()

                ->open('body')
                    ->open('div', ['class' => 'container'])
                        ->open('section', ['class' => 'info-section'])
                            ->h1('Uncaught "' . Misc::transform($shortName, 'title') . '"')
                            ->p(
                                "<code><b>{$shortName}</b></code> was thrown on line <code><b>{$line}</b></code> of file " .
                                "<code><b>{$fileName}</b></code> which prevented further execution of the code."
                            )
                            ->open('div', ['class' => 'message'])
                                ->h3($name)
                                // we need to decode and encode because some messages come escaped
                                ->p(htmlspecialchars(htmlspecialchars_decode((string)$message), ENT_QUOTES, 'UTF-8'))
                            ->close()
                        ->close()

                        ->open('section', ['class' => 'capture-section'])
                            ->h2('Thrown in:')
                            ->execute(function (HTML $html) use ($file, $line) {
                                if (!file_exists($file)) {
                                    return;
                                }

                                $html
                                    ->open('p')
                                        ->node("File: <code><b>{$file}</b></code>")
                                        ->entity('nbsp')
                                        ->entity('nbsp')
                                        ->a('Open in <b>VS Code</b>', [
                                            'href'  => sprintf('vscode://file/%s:%d', $file, $line),
                                            'class' => 'button',
                                        ])
                                    ->close();

                                $html->div(Dumper::highlightFile($file, $line), ['class' => 'scrollable']);
                            })
                        ->close()

                        ->open('section', ['class' => 'trace-section'])
                            ->h2('Stack trace:')
                            ->execute(function (HTML $html) use ($trace, $traceString) {
                                if (!count($trace)) {
                                    $html->pre($traceString);

                                    return;
                                }

                                $html->node(Dumper::tabulateStacktrace($trace));
                            })
                        ->close()
                    ->close()
                ->close()
            ->close()
        ->echo();

        App::terminate();
    }

    /**
     * Highlights the passed file with the possibility to focus a specific line.
     *
     * @param string $file The file to highlight.
     * @param int $line The line to focus.
     *
     * @return string The hightailed file as HTML.
     *
     * @since 1.5.5
     *
     * @codeCoverageIgnore
     */
    private static function highlightFile(string $file, ?int $line = null): string
    {
        return (new HTML(false))
            ->open('div', ['class' => 'code-highlight'])
                ->open('ul', ['class' => 'code'])
                    ->execute(function (HTML $html) use ($file, $line) {
                        $file   = (string)$file;
                        $line   = (int)$line;
                        $lines  = file_exists($file) ? file($file) : [];
                        $count  = count($lines);
                        $offset = !$line ? $count : 5;

                        for ($i = $line - $offset; $i < $line + $offset; $i++) {
                            if (!($i > 0 && $i < $count)) {
                                continue;
                            }

                            $highlightedCode = highlight_string('<?php ' . $lines[$i], true);
                            $highlightedCode = preg_replace(
                                ['/\n/', '/<br ?\/?>/', '/&lt;\?php&nbsp;/'],
                                ['', '', ''],
                                $highlightedCode
                            );

                            $causer = $i === $line - 1;
                            $number = strval($i + 1);

                            if ($causer) {
                                $number = str_pad('>', strlen($number), '=', STR_PAD_LEFT);
                            }

                            $html
                                ->open('li')
                                    ->condition($causer === true)
                                    ->span($number, ['class' => 'code-line exception-line'])
                                    ->condition($causer === false)
                                    ->span($number, ['class' => 'code-line'])
                                    ->node($highlightedCode)
                                ->close();
                        }
                    })
                ->close()
            ->close()
        ->return();
    }

    /**
     * Tabulates the passed stacktrace in an HTML table.
     *
     * @param array $trace Exception stacktrace array.
     *
     * @return string The tabulated trace as HTML.
     *
     * @since 1.5.5
     *
     * @codeCoverageIgnore
     */
    private static function tabulateStacktrace(array $trace): string
    {
        return (new HTML(false))
            ->p('<i>Fields with * can reveal more info. * Hoverable. ** Clickable.</i>')
            ->open('div', ['class' => 'scrollable'])
                ->open('table', ['class' => 'table'])
                    ->open('thead', ['class' => 'table-head'])
                        ->open('tr', ['class' => 'table-row'])
                            ->th('No.&nbsp;**', ['class' => 'table-cell compact'])
                            ->th('File&nbsp;*', ['class' => 'table-cell'])
                            ->th('Line', ['class' => 'table-cell compact'])
                            ->th('Class', ['class' => 'table-cell'])
                            ->th('Function', ['class' => 'table-cell'])
                            ->th('Arguments&nbsp;*', ['class' => 'table-cell'])
                        ->close()
                    ->close()
                    ->open('tbody', ['class' => 'table-body'])
                        ->execute(function (HTML $html) use ($trace) {
                            foreach ($trace as $i => $trace) {
                                $count = (int)$i + 1;

                                $html
                                    ->open('tr', ['class' => 'table-row ' . ($count % 2 == 0 ? 'even' : 'odd')])
                                        ->td(isset($trace['file']) ? '' : strval($count), ['class' => 'table-cell number'])
                                        ->td(
                                            isset($trace['file'])
                                                ? sprintf('<a href="vscode://file/%s:%d" title="Open in VS Code">%s</a>', $trace['file'], $trace['line'], basename($trace['file']))
                                                : 'N/A',
                                            ['class' => 'table-cell file pop-up', 'title' => $trace['file'] ?? 'N/A']
                                        )
                                        ->td(strval($trace['line'] ?? 'N/A'), ['class' => 'table-cell line'])
                                        ->td(strval($trace['class'] ?? 'N/A'), ['class' => 'table-cell class monospace'])
                                        ->td(strval($trace['function'] ?? 'N/A'), ['class' => 'table-cell function monospace'])
                                        ->open('td', ['class' => 'table-cell arguments monospace'])
                                            ->execute(function (HTML $html) use ($trace) {
                                                if (!isset($trace['args'])) {
                                                    $html->node('NULL');

                                                    return;
                                                }

                                                foreach ($trace['args'] as $argument) {
                                                    $html->span(gettype($argument), [
                                                        'class' => 'argument pop-up',
                                                        'title' => htmlspecialchars(
                                                            Misc::callObjectMethod(Dumper::class, 'exportExpression', $argument),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ),
                                                    ]);
                                                }
                                            })
                                        ->close()
                                    ->close()
                                    ->execute(function (HTML $html) use ($trace, $count) {
                                        isset($trace['file']) && $html
                                            ->open('tr', ['class' => 'table-row additional', 'id' => 'trace-' . $count])
                                                ->open('td', ['class' => 'table-cell', 'colspan' => 6])
                                                    ->open('details', ['class' => 'accordion'])
                                                        ->summary(strval($count), ['class' => 'accordion-summary'])
                                                        ->div(
                                                            Dumper::highlightFile($trace['file'] ?? '', $trace['line'] ?? null),
                                                            ['class' => 'accordion-details']
                                                        )
                                                    ->close()
                                                ->close()
                                            ->close();
                                    });
                            }
                        })
                    ->close()
                ->close()
            ->close()
        ->return();
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
            '/(\n)([ ]*)\]\)/'                      => '$1$2 ])',
            '/([ ]*)(\'[^\']+\') => ([\[\'])/'      => '$1$2 => $3',
        ];

        return preg_replace(
            array_keys($acToSbPatterns),
            array_values($acToSbPatterns),
            $export
        );
    }

    /**
     * Dumps an expression using `var_export()` or `var_dump()` with syntax highlighting.
     *
     * @param mixed $expression
     * @param string|null $phpReplacement `<?php` replacement.
     *
     * @return string
     */
    private static function exportExpressionWithSyntaxHighlighting($expression, ?string $phpReplacement = ''): string
    {
        self::setSyntaxHighlighting();

        $export = self::exportExpression($expression);

        $code = highlight_string('<?php ' . $export, true);
        $html = preg_replace(
            '/&lt;\?php&nbsp;/',
            $phpReplacement ?? '',
            $code,
            1
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

    /**
     * Returns an array containing HTML/ANSI wrapping blocks.
     * Available blocks are: `traceBlock`, `dumpBlock`, `timeBlock`, and `detailsBlock`.
     * All this blocks will contain a placeholder for a `*printf()` function to inject content.
     *
     * @return void
     */
    private static function getDumpingBlocks(): array
    {
        $isCli = self::isCli();

        $colors = [
            'accentColor'   => static::$accentColor,
            'contrastColor' => static::$contrastColor,
        ];

        return [
            'traceBlock' => $isCli ? "\n// \e[33;1mTRACE:\e[0m \e[34;46m[%s]\e[0m \n\n" : HTML::div('%s', [
                'style' => Misc::interpolate(static::$styles['traceBlock'], $colors, '%%')
            ]),
            'dumpBlock' => $isCli ? '%s' : HTML::div('%s', [
                'style' => Misc::interpolate(static::$styles['dumpBlock'], $colors, '%%')
            ]),
            'timeBlock' => $isCli ? "\n\n// \e[36mSTART_TIME\e[0m + \e[35m%.2f\e[0mms \n\n\n" : HTML::div('START_TIME + %.2fms', [
                'style' => Misc::interpolate(static::$styles['timeBlock'], $colors, '%%')
            ]),
            'detailsBlock' => $isCli ? '%s' : (new HTML(false))
                ->open('details', ['open' => null])
                    ->summary('Expand/Collapse', [
                        'style' => Misc::interpolate(static::$styles['detailsBlock'], $colors, '%%')
                    ])
                    ->main('%s')
                ->close()
            ->return(),
        ];
    }

    /**
     * Returns the last caller trace before `dd()` or `dump()` if the format of `file:line`.
     *
     * @return string
     */
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

    /**
     * Converts a hex color to the closest standard ANSI color code.
     * Standard ANSI colors include: black, red, green, yellow, blue, magenta, cyan and white.
     *
     * @return int
     */
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
     * Sets PHP syntax highlighting colors according to current class state.
     *
     * @return void
     *
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

    /**
     * Checks whether the script is currently running in CLI mode or not.
     *
     * @return bool
     */
    private static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
