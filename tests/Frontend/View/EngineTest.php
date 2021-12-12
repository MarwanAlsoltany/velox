<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Frontend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Frontend\View\Engine;

class EngineTest extends TestCase
{
    private Engine $engine;
    private const TEST_DATA_DIR = __DIR__ . '/engine-test';
    private const TEST_DATA_TEMPLATES_DIR = __DIR__ . '/engine-test/templates';
    private const TEST_DATA_CACHE_DIR = __DIR__ . '/engine-test/cache';
    private const TEST_DATA_FILE_EXTENSION = '.phtml';


    public function setUp(): void
    {
        parent::setUp();

        $this->engine = new Engine(
            static::TEST_DATA_TEMPLATES_DIR,
            static::TEST_DATA_FILE_EXTENSION,
            static::TEST_DATA_CACHE_DIR,
            true,
            false
        );

        $this->createTestData();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->engine);

        $this->deleteTestData();
    }


    public function testEngineGetCompiledContentMethod(): void
    {
        $content = $this->engine->getCompiledContent('base');

        $this->assertIsString($content);
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('<?php echo (string)($title); ?>', $content);
    }

    public function testEngineGetCompiledContentThrowsAnExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Template file .+ does not exist)/');

        $this->engine->getCompiledContent('unknown');
    }

    public function testEngineGetCompiledFileMethod(): void
    {
        $file = $this->engine->getCompiledFile('base');

        $this->assertFileExists($file);
        $this->assertStringContainsString('<?php echo (string)($title); ?>', file_get_contents($file));
    }

    public function testEngineRenderMethod(): void
    {
        ob_start();
        $this->engine->render('base', ['title' => static::class]);
        $base = ob_get_clean();

        $this->assertIsString($base);
        $this->assertNotEmpty($base);
        $this->assertStringContainsString('<h1>' . static::class . '</h1>', $base);
        $this->assertStringContainsString('<p>This is the "included" file content.</p>', $base);
    }

    public function testEngineRenderMethodWithExtendedWithDebug(): void
    {
        $this->expectOutputRegex('~(' . static::class . ')~');
        $this->expectOutputRegex('~(<!--.+::.+-->)~');

        $this->setTestObjectProperty($this->engine, 'cache', false);

        $this->engine->setDebug(true);
        $this->engine->render('extended', ['title' => static::class]);
        $this->engine->setDebug(false);
    }

    public function testEngineRenderMethodWithExtendedWithNoCache(): void
    {
        $this->expectOutputRegex('~(' . static::class . ')~');
        $this->expectOutputRegex('~(This is the "embedded" file content)~');
        $this->expectOutputRegex('~(Item A)~');
        $this->expectOutputRegex('~(Item B)~');
        $this->expectOutputRegex('~(Item C)~');

        $this->setTestObjectProperty($this->engine, 'cache', false);

        $this->engine->render('extended', ['title' => static::class]);
    }

    public function testEngineCacheClearMethod(): void
    {
        $this->engine->getCompiledFile('base');

        $this->assertDirectoryExists(static::TEST_DATA_CACHE_DIR);
        $this->assertNotEmpty(glob(static::TEST_DATA_CACHE_DIR . '/base_*'));

        $this->engine->clearCache();

        $this->assertEmpty(glob(static::TEST_DATA_CACHE_DIR . '/*'));
    }


    private function deleteTestData(): void
    {
        $delete = function ($dir) use (&$delete) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        if (is_dir($dir . '/' . $object) && !is_link($dir . '/' . $object)) {
                            $delete($dir . '/' . $object);
                            continue;
                        }

                        unlink($dir . '/' . $object);
                    }
                }
                rmdir($dir);
            }
        };

        $delete(static::TEST_DATA_DIR);
    }

    private function createTestData(): void
    {
        $contents = $this->getTestContents();

        file_exists(static::TEST_DATA_TEMPLATES_DIR) || mkdir(static::TEST_DATA_TEMPLATES_DIR, 0777, true);

        foreach ($contents as $name => $content) {
            file_put_contents(
                static::TEST_DATA_TEMPLATES_DIR . '/' . $name . static::TEST_DATA_FILE_EXTENSION, $content
            );
        }
    }

    private function getTestContents(): array
    {
        return [
            'base' => <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    {! @block head !}
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ \$title }}</title>
    {! @endblock!}
    {! @block(head) !}
</head>
<body>
    {! @block body !}
        {! @block content !}
            <h1>{{{ \$title }}}</h1>
            <p>Base layout text.</p>
        {! @endblock!}
        {! @block(content) !}
    {! @endblock!}
    {! @block(body) !}

    {! @block includes !}
        <p>This is the includes block.</p>
        {! @include 'includable' !}
    {! @endblock !}
    {! @block(includes) !}
</body>
</html>
EOT,

        'extended' => <<<EOT
{! @extends 'base' !}

{! @block head !}
    {! @super !}
    <style>body { background: tomato; }</style>
{! @endblock !}

{! @block content !}
    <h1>{{{ \$title }}}</h1>
    <p>Extended layout text.</p>
    {! @super !}
    {! \$items = ['Item A', 'Item C', 'Item C'] !}
    <ul>
        {! @foreach (\$items as \$index => \$item) !}
            {! @if (\$item) !}
                <li>{{ \$item }}</li>
            {! @else !}
                <li>No item</li>
            {! @endif !}
        {! @endforeach !}
    </ul>
{! @endblock !}

{! @block includes !}
    {! @embed 'embeddable' !}
{! @endblock !}
EOT,

        'includable' => <<<EOT
<p>This is the "included" file content.</p>
<p>I cannot access the defined variables where I was included!</p>
EOT,

        'embeddable' => <<<EOT
<p>This is the "embedded" file content.</p>
<p>I can access the defined variables where I was embedded, there were {{ isset(\$items) ? count(\$items) : 'N/A' }} items available.</p>
EOT,
        ];
    }
}
