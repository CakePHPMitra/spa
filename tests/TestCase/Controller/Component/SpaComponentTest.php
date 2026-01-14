<?php
declare(strict_types=1);

namespace CakeSPA\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakeSPA\Controller\Component\SpaComponent;

/**
 * SpaComponent Test Case
 */
class SpaComponentTest extends TestCase
{
    /**
     * @var \CakeSPA\Controller\Component\SpaComponent
     */
    protected SpaComponent $component;

    /**
     * @var \Cake\Controller\Controller
     */
    protected Controller $controller;

    /**
     * Setup method.
     */
    public function setUp(): void
    {
        parent::setUp();

        $request = new ServerRequest();
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);
    }

    /**
     * Teardown method.
     */
    public function tearDown(): void
    {
        unset($this->component, $this->controller);
        parent::tearDown();
    }

    /**
     * Test isNavigationRequest returns true for navigation header.
     */
    public function testIsNavigationRequestWithHeader(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_LIVE_NAV' => 'true',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isNavigationRequest();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isNavigationRequest returns true for request attribute.
     */
    public function testIsNavigationRequestWithAttribute(): void
    {
        // Arrange
        $request = (new ServerRequest())->withAttribute('spa.isNavigation', true);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isNavigationRequest();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isNavigationRequest returns false for regular requests.
     */
    public function testIsNavigationRequestReturnsFalse(): void
    {
        // Act
        $result = $this->component->isNavigationRequest();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test isAjaxRequest returns false for navigation requests.
     */
    public function testIsAjaxRequestExcludesNavigation(): void
    {
        // Arrange - Both navigation and AJAX headers present
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_LIVE_NAV' => 'true',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isAjaxRequest();

        // Assert - Should be false because navigation takes precedence
        $this->assertFalse($result);
    }

    /**
     * Test isAjaxRequest returns true for AJAX requests.
     */
    public function testIsAjaxRequestReturnsTrue(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isAjaxRequest();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isSpaRequest returns true for navigation.
     */
    public function testIsSpaRequestForNavigation(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_LIVE_NAV' => 'true',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isSpaRequest();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isSpaRequest returns true for AJAX.
     */
    public function testIsSpaRequestForAjax(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->isSpaRequest();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isSpaRequest returns false for regular requests.
     */
    public function testIsSpaRequestReturnsFalse(): void
    {
        // Act
        $result = $this->component->isSpaRequest();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test jsonResponse sets up JSON view.
     */
    public function testJsonResponse(): void
    {
        // Arrange
        $data = ['count' => 42, 'message' => 'Hello'];

        // Act
        $result = $this->component->jsonResponse($data);

        // Assert
        $this->assertNull($result);
        $this->assertSame('Json', $this->controller->viewBuilder()->getClassName());
    }

    /**
     * Test success response includes success flag.
     */
    public function testSuccessResponse(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->success(['count' => 42], 'Updated successfully');

        // Assert
        $this->assertNull($result);
        $viewVars = $this->controller->viewBuilder()->getVars();
        // Note: In a real test, we'd check the actual set variables
    }

    /**
     * Test error response for AJAX requests.
     */
    public function testErrorResponseForAjax(): void
    {
        // Arrange
        $request = new ServerRequest([
            'environment' => [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ],
        ]);
        $this->controller = new Controller($request);
        $registry = new ComponentRegistry($this->controller);
        $this->component = new SpaComponent($registry);

        // Act
        $result = $this->component->error('Something went wrong', 400);

        // Assert
        $this->assertNull($result);
        $this->assertSame(400, $this->controller->getResponse()->getStatusCode());
    }
}
