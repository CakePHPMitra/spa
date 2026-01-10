<?php
declare(strict_types=1);

namespace CakeSPA\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use CakeSPA\View\Helper\SpaHelper;

/**
 * SpaHelper Test Case
 */
class SpaHelperTest extends TestCase
{
    /**
     * @var \CakeSPA\View\Helper\SpaHelper
     */
    protected SpaHelper $helper;

    /**
     * @var \Cake\View\View
     */
    protected View $view;

    /**
     * Setup method.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->view = new View();
        $this->helper = new SpaHelper($this->view);
    }

    /**
     * Teardown method.
     */
    public function tearDown(): void
    {
        unset($this->helper, $this->view);
        parent::tearDown();
    }

    /**
     * Test button generates correct HTML.
     */
    public function testButton(): void
    {
        // Act
        $result = $this->helper->button('Click Me', 'home/action');

        // Assert
        $this->assertStringContainsString('<button', $result);
        $this->assertStringContainsString('data-spa-action="home/action"', $result);
        $this->assertStringContainsString('>Click Me</button>', $result);
        $this->assertStringContainsString('class="btn"', $result);
    }

    /**
     * Test button with options.
     */
    public function testButtonWithOptions(): void
    {
        // Act
        $result = $this->helper->button('Save', 'items/save', [
            'class' => 'btn-primary',
            'loading' => 'Saving...',
            'key' => 's',
            'params' => ['id' => 123],
        ]);

        // Assert
        $this->assertStringContainsString('class="btn-primary"', $result);
        $this->assertStringContainsString('data-spa-loading="Saving..."', $result);
        $this->assertStringContainsString('data-spa-key="s"', $result);
        $this->assertStringContainsString('data-spa-param-id="123"', $result);
    }

    /**
     * Test link generates correct HTML.
     */
    public function testLink(): void
    {
        // Act
        $result = $this->helper->link('Click Me', 'home/action');

        // Assert
        $this->assertStringContainsString('<a', $result);
        $this->assertStringContainsString('href="#"', $result);
        $this->assertStringContainsString('data-spa-action="home/action"', $result);
        $this->assertStringContainsString('>Click Me</a>', $result);
    }

    /**
     * Test navLink generates SPA navigation link.
     */
    public function testNavLink(): void
    {
        // Act
        $result = $this->helper->navLink('Home', '/home');

        // Assert
        $this->assertStringContainsString('<a', $result);
        $this->assertStringContainsString('href="/home"', $result);
        $this->assertStringContainsString('data-spa-nav="true"', $result);
        $this->assertStringContainsString('>Home</a>', $result);
    }

    /**
     * Test target generates model-bound element.
     */
    public function testTarget(): void
    {
        // Act
        $result = $this->helper->target('counter', 42);

        // Assert
        $this->assertStringContainsString('<span', $result);
        $this->assertStringContainsString('data-spa-model="counter"', $result);
        $this->assertStringContainsString('>42</span>', $result);
    }

    /**
     * Test target with custom tag.
     */
    public function testTargetWithCustomTag(): void
    {
        // Act
        $result = $this->helper->target('content', 'Hello', ['tag' => 'div']);

        // Assert
        $this->assertStringContainsString('<div', $result);
        $this->assertStringContainsString('</div>', $result);
    }

    /**
     * Test target with HTML content.
     */
    public function testTargetWithHtml(): void
    {
        // Act
        $result = $this->helper->target('html', '<strong>Bold</strong>', ['html' => true]);

        // Assert
        $this->assertStringContainsString('data-spa-unsafe-html="true"', $result);
        $this->assertStringContainsString('<strong>Bold</strong>', $result);
    }

    /**
     * Test target escapes content by default.
     */
    public function testTargetEscapesContent(): void
    {
        // Act
        $result = $this->helper->target('content', '<script>alert("xss")</script>');

        // Assert
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    /**
     * Test contentContainer generates content wrapper.
     */
    public function testContentContainer(): void
    {
        // Act
        $result = $this->helper->contentContainer('Initial content');

        // Assert
        $this->assertStringContainsString('<div', $result);
        $this->assertStringContainsString('data-spa-content="true"', $result);
        $this->assertStringContainsString('>Initial content</div>', $result);
    }

    /**
     * Test button with target option.
     */
    public function testButtonWithTarget(): void
    {
        // Act
        $result = $this->helper->button('Update', 'items/update', [
            'target' => '#my-container',
        ]);

        // Assert
        $this->assertStringContainsString('data-spa-target="#my-container"', $result);
    }

    /**
     * Test button with pushUrl option.
     */
    public function testButtonWithPushUrl(): void
    {
        // Act
        $result = $this->helper->button('Search', 'items/search', [
            'pushUrl' => 'items',
        ]);

        // Assert
        $this->assertStringContainsString('data-spa-push-url="items"', $result);
    }

    /**
     * Test scripts are only included once.
     */
    public function testScriptsIncludedOnce(): void
    {
        // Act
        $first = $this->helper->scripts();
        $second = $this->helper->scripts();

        // Assert
        $this->assertNotEmpty($first);
        $this->assertEmpty($second);
    }
}
