<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Backend\Event;

class EventTest extends TestCase
{
    private Event $config;


    public function setUp(): void
    {
        parent::setUp();

        $this->event = new Event();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->event);
    }


    public function testEventDispatchAndListenMethods()
    {
        $this->event->listen('test.event.1', function ($param) {
            $this->assertEquals('param', $param);
        });
        $this->event->listen('test.event.2', function ($param) {
            $this->assertEquals('param', $param);
        });
        $this->event->listen('test.event.3', function ($param, $test) {
            /** @var Event $this */
            $events = $this->getRegisteredEvents();

            $test->assertIsArray($events);
            $test->assertEquals('param', $param);
        });

        $this->event->dispatch('test.event.0', ['no', 'one', 'will', 'listen', 'for', 'this']);
        $this->event->dispatch('test.event.1', ['param']);
        $this->event->dispatch('test.event.2', ['param'], $this);
        $this->event->dispatch('test.event.3', ['param', $this], $this->event);

        $events = $this->event->getRegisteredEvents();
        $this->assertNotEmpty($events);
        $this->assertArrayHasKey('test.event.0', $events);
        $this->assertArrayHasKey('test.event.1', $events);
        $this->assertArrayHasKey('test.event.2', $events);
        $this->assertArrayHasKey('test.event.3', $events);
    }
}
