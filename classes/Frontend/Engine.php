<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Velox\Frontend;

use MAKS\Velox\Frontend\Path;
use MAKS\Velox\Helper\Misc;

/**
 * A class that serves as a templating engine for view files.
 * This templating engine is regular expression based.
 *
 * Templating tags:
 * - Create a block `{! @block name !}` ... `{! @endblock !}`, use `{! @super !}` to inherit parent block.
 * - Print a block `{! @block(name) !}`.
 * - Extend a file `{! @extends 'theme/layouts/file' !}`, blocks of this file will be inherited.
 * - Include a file `{! @include 'theme/includes/file' !}`, this will get rendered before inclusion.
 * - Embed a file `{! @embed 'theme/components/file' !}`, this will be included as is.
 * - Control structures `{! @if ($var) !}` ... `{! @endif !}`, `{! @foreach($vars as $var) !}` ... `{! @endforeach !}`.
 * - Variable assignments `{! $var = '' !}`, content can be a variable or any valid PHP expression.
 * - Print a variable `{{ $var }}`, content can be a variable or any PHP expression that can be casted to a string.
 * - Print a variable without escaping `{{{ $var }}}`, content can be a variable or any PHP expression that can be casted to a string.
 * - Comment something `{# This is a comment #}`, this will be a PHP comment (will not be available in final HTML).
 *
 * @internal
 * @since 1.3.0
 */
class Engine
{
    /**
     * Regular expressions for syntax tokens.
     */
    public const REGEX = [
        'dependency'             => '/{!\s*@(extends|embed)\s+[\'"]?(.*?)[\'"]?\s*!}/s',
        'include'                => '/{!\s*@include\s+[\'"]?(.*?)[\'"]?\s*!}/s',
        'block'                  => '/{!\s*@block\s+(.*?)\s*!}(.*?){!\s*@endblock\s*!}(?!.+{!\s*@endblock\s*!})/s',
        'block.child'            => '/{!\s*@block\s+.*?\s*!}.*({!\s*@block\s+(.*?)\s*!}(.*?){!\s*@endblock\s*!}).*{!\s*@endblock\s*!}/s',
        'block.print'            => '/{!\s*@block\((.*?)\)\s*!}/s',
        'block.super'            => '/{!\s*@super\s*!}/s',
        'php'                    => '/{!\s*(.+?)\s*!}/s',
        'comment'                => '/{#\s*(.+?)\s*#}/s',
        'controlStructure'       => '/@(if|else|elseif|endif|do|while|endwhile|for|endfor|foreach|endforeach|continue|switch|endswitch|break|return|require|include)/s',
        'controlStructure.start' => '/^@(if|else|elseif|while|for|foreach|switch)/s',
        'controlStructure.end'   => '/@(endif|endwhile|endfor|endforeach|endswitch)$/s',
        'print'                  => '/{{\s*(.+?)\s*}}/s',
        'print.unescaped'        => '/{{{\s*(.+?)\s*}}}/s',
    ];


    /**
     * Currently captured template blocks.
     */
    private array $blocks = [];

    /**
     * Template files directory.
     */
    protected string $templatesDirectory;

    /**
     * Template files file extension.
     */
    protected string $templatesFileExtension;

    /**
     * Template files cache directory.
     */
    protected string $cacheDirectory;

    /**
     * Whether or not to cache compiled template files.
     */
    protected bool $cache;


    /**
     * Whether or not to add debugging info to the compiled template.
     */
    public static bool $debug = false;


    /**
     * Class constructor.
     */
    public function __construct(
        string $templatesDirectory     = './templates',
        string $templatesFileExtension = '.phtml',
        string $cacheDirectory         = './cache/',
        bool $cache                    = true,
        bool $debug                    = false
    ) {
        $this->templatesDirectory     = $templatesDirectory;
        $this->templatesFileExtension = $templatesFileExtension;
        $this->cacheDirectory         = $cacheDirectory;
        $this->cache                  = $cache;

        $this->setDebug($debug);
    }


    /**
     * Renders a template file and pass the passed variables to it.
     *
     * @param string $file A relative path to template file from templates directory.
     * @param array $variables The variables to pass to the template.
     *
     * @return void
     */
    public function render(string $file, array $variables = []): void
    {
        $this->require(
            $this->getCompiledFile($file),
            $variables
        );
    }

    public function clearCache(): void
    {
        $files = glob(rtrim($this->cacheDirectory, '/') . '/*.php');

        array_map('unlink', $files);
    }

    /**
     * Compiles a template file and returns the path to the compiled template file from cache directory.
     *
     * @param string $file A relative path to template file from templates directory.
     *
     * @return string
     *
     * @throws \Exception If file does not exist.
     */
    public function getCompiledFile(string $file): string
    {
        $this->createCacheDirectory();

        $templateFile = $this->resolvePath($file);
        $cachedFile   = $this->resolveCachePath($file);

        $isCompiled = file_exists($cachedFile) && filemtime($cachedFile) > filemtime($templateFile);

        if (!$this->cache || !$isCompiled) {
            $content = vsprintf('<?php %s class_exists(\'%s\') or exit; ?> %s', [
                $this->cache ? '/* ' . $file . ' */' : 'unlink(__FILE__);',
                static::class,
                PHP_EOL . PHP_EOL . $this->getCompiledContent($file),
            ]);

            file_put_contents($cachedFile, $content);
        }

        return $cachedFile;
    }

    /**
     * Compiles a template file and returns the result after compilation.
     *
     * @param string $file A relative path to template file from templates directory.
     *
     * @return string
     *
     * @throws \Exception If file does not exist.
     */
    public function getCompiledContent(string $file): string
    {
        $file = $this->resolvePath($file);

        $this->assertFileExists($file);

        // execution order matters
        $code = $this->importDependencies($file);
        $code = $this->importIncludes($code);
        $code = $this->extractBlocks($code);
        $code = $this->injectBlocks($code);
        $code = $this->printUnescapedVariables($code);
        $code = $this->printVariables($code);
        $code = $this->wrapPhp($code);
        $code = $this->wrapComments($code);

        return $code;
    }

    /**
     * Evaluates a template file after compiling it in a temporary file and returns the result after evaluation.
     *
     * @param string $file A relative path to template file from templates directory.
     * @param array $variables The variables to pass to the template.
     *
     * @return string
     *
     * @throws \Exception If file does not exist.
     */
    public function getEvaluatedContent(string $file, array $variables = []): string
    {
        $this->createCacheDirectory();

        $content = $this->getCompiledContent($file);

        // an actual file is used here because 'require' does not work with 'php://temp'
        $file = tempnam($this->cacheDirectory, 'EVL');

        $temp = fopen($file, 'w');
        fwrite($temp, $content);
        fclose($temp);

        ob_start();
        $this->require($file, $variables);
        $content = ob_get_contents();
        ob_get_clean();

        unlink($file);

        return $content;
    }

    /**
     * Creates cache directory if it does not exist.
     *
     * @return void
     */
    private function createCacheDirectory(): void
    {
        if (!file_exists($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0744, true);
        }
    }

    /**
     * Asserts that a file exists.
     *
     * @param string $file An absolute path to a file.
     *
     * @return void
     *
     * @throws \Exception If file does not exist.
     */
    private function assertFileExists(string $file): void
    {
        if (!file_exists($file)) {
            throw new \Exception(
                'Template file "' . $file . '" does not exist. ' .
                'The path is wrong. Hint: a parent directory may be missing'
            );
        }
    }

    /**
     * Requires a PHP file and pass it the passed variables.
     *
     * @param string $file An absolute path to the file that should be compiled.
     * @param array|null $variables [optional] An associative array of the variables to pass.
     *
     * @return void
     */
    protected static function require(string $file, ?array $variables = null): void
    {
        $_file = $file;
        unset($file);

        if ($variables !== null) {
            extract($variables, EXTR_OVERWRITE);
            unset($variables);
        }

        require($_file);
        unset($_file);
    }

    /**
     * Resolves a template file path.
     *
     * @param string $file The file path to resolve.
     *
     * @return string
     */
    protected function resolvePath(string $file): string
    {
        return Path::normalize(
            $this->templatesDirectory,
            $file,
            $this->templatesFileExtension
        );
    }

    /**
     * Resolves a template file path from cache directory.
     *
     * @param string $file The file path to resolve.
     *
     * @return string
     */
    protected function resolveCachePath(string $file): string
    {
        if (!$this->cache) {
            return Path::normalize($this->cacheDirectory, md5('temporary'), '.tmp');
        }

        $templatePath = strtr($file, [
            $this->templatesDirectory => '',
            $this->templatesFileExtension => '',
        ]);
        $templateName = Misc::transform($templatePath, 'snake');
        $cacheName    = $templateName . '_' . md5($file);

        return Path::normalize($this->cacheDirectory, $cacheName, '.php');
    }

    /**
     * Imports template dependencies.
     *
     * @param string $file The template file.
     *
     * @return string
     *
     * @throws \Exception If file does not exist.
     */
    final protected function importDependencies(string $file): string
    {
        $this->assertFileExists($file);

        $code = file_get_contents($file);

        $count = preg_match_all(
            static::REGEX['dependency'],
            $code,
            $matches,
            PREG_SET_ORDER
        ) ?: 0;

        for ($i = 0; $i < $count; $i++) {
            $match = $matches[$i][0];
            $type  = $matches[$i][1];
            $path  = $matches[$i][2];
            $path  = $this->resolvePath($path);

            $startComment = sprintf('<!-- %s::(\'%s\') [START] -->', $type, $path) . PHP_EOL;
            $endComment   = sprintf('<!-- %s::(\'%s\') [END] -->', $type, $path) . PHP_EOL;
            $content      = $this->importDependencies($path);
            $requirement  = vsprintf(
                '%s%s%s',
                $this->isDebug() ? [$startComment, $content, $endComment] : ['', $content, '']
            );

            $code = str_replace($match, $requirement, $code);
        }

        $code = preg_replace(static::REGEX['dependency'], '', $code);

        return $code;
    }

    /**
     * Imports template includes.
     *
     * @param string $code The template code.
     *
     * @return string
     */
    final protected function importIncludes(string $code): string
    {
        $count = preg_match_all(
            static::REGEX['include'],
            $code,
            $matches,
            PREG_SET_ORDER
        ) ?: 0;

        for ($i = 0; $i < $count; $i++) {
            $match = $matches[$i][0];
            $path  = $matches[$i][1];

            $startComment = sprintf('<!-- include::(\'%s\') [START] -->', $path) . PHP_EOL;
            $endComment   = sprintf('<!-- include::(\'%s\') [END] -->', $path) . PHP_EOL;
            $content      = $this->getEvaluatedContent($path);
            $requirement  = vsprintf(
                '%s%s%s',
                $this->isDebug() ? [$startComment, $content, $endComment] : ['', $content, '']
            );

            $code = str_replace($match, $requirement, $code);
        }

        $code = preg_replace(static::REGEX['include'], '', $code);

        return $code;
    }

    /**
     * Parses a template block, extract data from it, and updates class internal state.
     *
     * @param string $code The template block.
     *
     * @return string
     */
    final protected function parseBlock(string $code): string
    {
        preg_match(static::REGEX['block'], $code, $matches);
        $name  = $matches[1];
        $value = $matches[2];

        $comment = '';

        if (preg_match(static::REGEX['block.super'], $value)) {
            $value = preg_replace(
                static::REGEX['block.super'],
                sprintf('{! @block(%s) !}', $name . 'Super'),
                $value
            );
        }

        if (isset($this->blocks[$name])) {
            $this->blocks[$name . 'Super'] = $value;

            $comment = sprintf('<!-- block::(\'%s\') [INHERIT] -->', $name);
        } else {
            $this->blocks[$name] = $value;

            $comment = sprintf('<!-- block::(\'%s\') [ASSIGN] -->', $name);
        }


        return $this->isDebug() ? $comment : '';
    }

    /**
     * Extract blocks data from template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function extractBlocks(string $code): string
    {
        $opening = '{! @block pseudo-' . md5($code) . ' !}';
        $closing = '{! @endblock !}';
        $code    = sprintf('%s%s%s', $opening, $code, $closing);

        while (preg_match(static::REGEX['block.child'], $code, $matches)) {
            $block = $matches[1];

            $code = str_replace($block, $this->parseBlock($block), $code);
        }

        $code = str_replace([$opening, $closing], ['', ''], $code);

        return $code;
    }

    /**
     * Injects blocks data in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function injectBlocks(string $code): string
    {
        while (preg_match(static::REGEX['block.print'], $code, $matches)) {
            $match = $matches[0];
            $block = $matches[1];

            $requirement = $this->blocks[$block] ?? '';

            if ($this->isDebug()) {
                $startComment     = sprintf('<!-- print::(\'%s\') [START] -->', $block) . PHP_EOL;
                $endComment       = sprintf('<!-- print::(\'%s\') [END] -->', $block) . PHP_EOL;
                $undefinedComment = sprintf('<!-- print::(\'$1\') [UNDEFINED] -->', $block);
                $requirement      = vsprintf(
                    '%s%s%s',
                    isset($this->blocks[$block]) ? [$startComment, $requirement, $endComment] : ['', $undefinedComment, '']
                );
            }

            $code = str_replace($match, $requirement, $code);
        }

        $this->blocks = [];

        return $code;
    }

    /**
     * Echos unescaped variables in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function printUnescapedVariables(string $code): string
    {
        $comment = $this->isDebug() ? '<!-- unescapedVariable::(\'$1\') [ECHO] -->' : '';

        return preg_replace(
            static::REGEX['print.unescaped'],
            $comment . '<?php echo (string)($1); ?>',
            $code
        );
    }

    /**
     * Echos escaped variables in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function printVariables(string $code): string
    {
        $comment = $this->isDebug() ? '<!-- escapedVariable::(\'$1\') [ECHO] -->' : '';

        return preg_replace(
            static::REGEX['print'],
            $comment . '<?php echo htmlentities((string)($1), ENT_QUOTES, \'UTF-8\'); ?>',
            $code
        );
    }

    /**
     * Wraps PHP in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function wrapPhp(string $code): string
    {
        $count = preg_match_all(
            static::REGEX['php'],
            $code,
            $matches,
            PREG_SET_ORDER
        ) ?: 0;

        for ($i = 0; $i < $count; $i++) {
            $match = trim($matches[$i][0]);
            $php   = trim($matches[$i][1]);

            $php = $this->wrapControlStructures($php);

            $comment     = sprintf('<!-- php::(\'%s\') [PHP] -->', $php) . PHP_EOL;
            $content     = sprintf('<?php %s ?>', $php);
            $requirement = vsprintf('%s%s', $this->isDebug() ? [$comment, $content] : ['', $content]);

            $code = str_replace($match, $requirement, $code);
        }

        return $code;
    }

    /**
     * Wraps control structures and PHP code in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function wrapControlStructures(string $code): string
    {
        if (preg_match(static::REGEX['controlStructure.start'], $code)) {
            // if code starts with an opening control structure
            // check if it ends with ':' and add ':' if it does not
            if (substr($code, 0, 1) !== ':') {
                $code = ltrim($code, '@ ') . ':';
            }
        } else {
            // if code ends with a closing control structure or anything else
            // check if it ends ';' and add ';' if it does not
            if (substr($code, -1) !== ';') {
                $code = ltrim($code, '@ ') . ';';
            }
        }

        return $code;
    }

    /**
     * Wraps comments in template code.
     *
     * @param string $code
     *
     * @return string
     */
    final protected function wrapComments(string $code): string
    {
        $comment = $this->isDebug() ? '<!-- comment::(\'$1\') [COMMENT] -->' : '';

        return preg_replace(
            static::REGEX['comment'],
            $comment . '<?php /* $1 */ ?>',
            $code
        );
    }

    /**
     * Get the value of `static::$debug`.
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Set the value of `static::$debug`
     *
     * @return $this
     */
    public function setDebug(bool $debug)
    {
        self::$debug = $debug;

        return $this;
    }
}
