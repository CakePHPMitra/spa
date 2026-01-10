<?php
declare(strict_types=1);

namespace CakeSPA\Test\TestCase\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakeSPA\Middleware\SpaMiddleware;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SpaMiddleware Test Case
 */
class SpaMiddlewareTest extends TestCase
{
    /**
     * Test middleware detects navigation requests.
     */
    public function testDetectsNavigationRequest(): void
    {
        // Arrange
        $middleware = new SpaMiddleware();
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_LIVE_NAV' => 'true',
            ],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function ($req) {
            // Verify the request attribute was set
            $this->assertTrue($req->getAttribute('spa.isNavigation'));
            $this->assertTrue($req->getAttribute('spa.isSpaRequest'));

            return new Response();
        });

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        $this->assertSame('true', $response->getHeaderLine('X-SPA-Request'));
    }

    /**
     * Test middleware detects AJAX requests.
     */
    public function testDetectsAjaxRequest(): void
    {
        // Arrange
        $middleware = new SpaMiddleware();
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function ($req) {
            // Verify the request attribute was set
            $this->assertTrue($req->getAttribute('spa.isAjax'));
            $this->assertTrue($req->getAttribute('spa.isSpaRequest'));
            $this->assertFalse($req->getAttribute('spa.isNavigation'));

            return new Response();
        });

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        $this->assertSame('true', $response->getHeaderLine('X-SPA-Request'));
    }

    /**
     * Test middleware handles regular requests.
     */
    public function testHandlesRegularRequest(): void
    {
        // Arrange
        $middleware = new SpaMiddleware();
        $request = new ServerRequest();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function ($req) {
            // Verify the request attributes are false
            $this->assertFalse($req->getAttribute('spa.isNavigation'));
            $this->assertFalse($req->getAttribute('spa.isAjax'));
            $this->assertFalse($req->getAttribute('spa.isSpaRequest'));

            return new Response();
        });

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        $this->assertEmpty($response->getHeaderLine('X-SPA-Request'));
    }

    /**
     * Test middleware can be disabled.
     */
    public function testCanBeDisabled(): void
    {
        // Arrange
        $middleware = new SpaMiddleware(['enabled' => false]);
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_LIVE_NAV' => 'true',
            ],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function ($req) {
            // Attributes should not be set when disabled
            $this->assertNull($req->getAttribute('spa.isNavigation'));

            return new Response();
        });

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        $this->assertEmpty($response->getHeaderLine('X-SPA-Request'));
    }

    /**
     * Test custom header configuration.
     */
    public function testCustomHeaderConfiguration(): void
    {
        // Arrange
        $middleware = new SpaMiddleware([
            'navigationHeader' => 'X-Custom-Nav',
        ]);
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_CUSTOM_NAV' => 'true',
            ],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(function ($req) {
            $this->assertTrue($req->getAttribute('spa.isNavigation'));

            return new Response();
        });

        // Act
        $response = $middleware->process($request, $handler);

        // Assert
        $this->assertSame('true', $response->getHeaderLine('X-SPA-Request'));
    }
}
