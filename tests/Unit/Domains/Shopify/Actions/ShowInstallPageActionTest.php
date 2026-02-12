<?php

namespace Tests\Unit\Domains\Shopify\Actions;

use App\Domains\Shopify\Actions\ShowInstallPageAction;
use Illuminate\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowInstallPageActionTest extends TestCase
{
    private ShowInstallPageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new ShowInstallPageAction();
    }

    #[Test]
    public function it_returns_install_view()
    {
        // Act
        $response = $this->action->__invoke();

        // Assert
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('shopify.install', $response->getName());
    }

    #[Test]
    public function it_does_not_require_any_parameters()
    {
        // Assert no exception is thrown
        $this->action->__invoke();

        $this->assertTrue(true);
    }
}
