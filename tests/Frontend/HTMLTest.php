<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Frontend\HTML;

class HTMLTest extends TestCase
{
    private HTML $html;


    public function setUp(): void
    {
        parent::setUp();

        $this->html = new HTML(true);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->html);
    }


    public function testHTMLClassHTMLOutputViaEchoMethod()
    {
        $this->expectOutputString('<!DOCTYPE html><head><title>Test Document</title></head><body><!-- Main Page Content --><h1 style="text-align: center;">Test</h1><div class="container" style="max-width: 80%;"><h2>This is a test!</h2><p>This is only for test purposes ...</p><hr />&copy;<span> 2021</span></div></body>');

        $this->html
            ->node('<!DOCTYPE html>')
            ->open('head')
                ->title('Test Document')
            ->close()
            ->open('body')
                ->comment('Main Page Content')
                ->element('h1', 'Test', ['style' => 'text-align: center;'])
                ->open('div', ['class' => 'container', 'style' => 'max-width: 80%;'])
                    ->h2('This is a test!')
                    ->p('This is only for test purposes ...')
                    ->hr(null)
                    ->entity('copy')
                    ->span(' 2021')
                ->close()
            ->close()
        ->echo();
    }

    public function testHTMLConditionMethod()
    {
        $html1 = $this->html
            ->open('div')
                ->p('This should be rendered. [ID: 1]')
                ->condition(true)
                ->open('div')
                    ->p('This should be rendered. [ID: 2]')
                    ->condition(false)
                    ->p('This should NOT be rendered. [ID: 1]')
                ->close()
            ->close()
        ->return();

        $this->assertStringContainsString('This should be rendered. [ID: 1]', $html1);
        $this->assertStringContainsString('This should be rendered. [ID: 2]', $html1);
        $this->assertStringNotContainsString('This should NOT be rendered. [ID: 1]', $html1);

        $html2 = $this->html
            ->condition(true)
            ->open('div')
                ->p('This should be rendered. [ID: 1]')
                ->condition(false)
                ->open('div')
                    ->p('This should NOT be rendered. [ID: 1]')
                    ->condition(true)
                    ->p('This should NOT be rendered. [ID: 2]')
                ->close()
            ->close()
        ->return();

        $this->assertStringContainsString('This should be rendered. [ID: 1]', $html2);
        $this->assertStringNotContainsString('This should NOT be rendered. [ID: 1]', $html2);
        $this->assertStringNotContainsString('This should NOT be rendered. [ID: 2]', $html2);
    }

    public function testHTMLExecuteMethod()
    {
        $list = ['One [I]', 'Two [II]', 'Three [III]'];

        $html = $this->html
            ->open('div')
                ->h4('This is a list:')
                ->open('ol')
                    ->execute(function ($html) use ($list) {
                        // $this === $html
                        $index = 0;
                        foreach($list as $item) {
                            $html->li($item, ['data-index' => $index]);
                            $index++;
                        }
                    })
                ->close()
            ->close()
        ->return();

        $this->assertStringContainsString('One [I]', $html);
        $this->assertStringContainsString('Two [II]', $html);
        $this->assertStringContainsString('Three [III]', $html);
    }

    public function testHTMLTagsMagicMethodsWithDifferentWaysOfWritingAttributes()
    {
        $html = '<input type="text" readonly disabled />';

        $this->assertEquals($html, HTML::input(null, ['type' => 'text', 'title' => false, 'readonly' => null, 'disabled']));
    }

    public function testHTMLCloseMethodFailsWhenCalledWithoutOpeningTag()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Not in a context to close a tag)/');

        $this->html
            ->open('section')
                ->h1('This will fail')
            ->close()
            ->close()
        ->echo();
    }

    public function testHTMLReturnMethodFailsWhenThereIsAnUnclosedHTMLTags()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Cannot return HTML in)/');

        $this->html
            ->open('section')
                ->h1('This will fail')
            // ->close()
        ->echo();
    }

    public function testHTMLValidateMethodFailsOnInvalidHTML()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(HTML is invalid in)/');

        $this->html
            ->img('Here we should pass "null" to make "img" a self closing tag!', [
                'src' => 'https://via.placeholder.com/148x148/00d1b2/ffffff?text=VELOX'
            ])
        ->echo();
    }

    public function testHTMLMinifyMethodMinifiesHTMLStrings()
    {
        $badHtml  = '<div ><     label>   Text:</label>   <input   type="text"         disabled   /> </div ' . PHP_EOL . '>';
        $goodHtml = '<div><label> Text:</label><input type="text" disabled /></div>';

        $this->assertEquals($goodHtml, HTML::minify($badHtml));
    }
}
