<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Mocks;

use MAKS\Velox\Backend\Controller;

class ControllerMock extends Controller
{
    /**
     * @route("/test", {GET})
     */
    public function testAction(): string
    {
        return 'Response from ' . static::class;
    }

    protected function registerRoutes(): bool
    {
        return true;
    }

    protected function associateModel(): ?string
    {
        return ModelMock::class;
    }
}
