<?php
declare(strict_types=1);

namespace CakeSPA\Middleware;

use Cake\Core\Configure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SPA Middleware
 *
 * Handles SPA request detection and response modification.
 * Sets request attributes for downstream processing.
 */
class SpaMiddleware implements MiddlewareInterface
{
    /**
     * Configuration options.
     *
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $config Configuration options.
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'enabled' => true,
            'navigationHeader' => 'X-Live-Nav',
            'ajaxHeader' => 'X-Requested-With',
            'ajaxHeaderValue' => 'XMLHttpRequest',
        ];

        $configured = Configure::read('CakeSPA', []);
        $this->config = array_merge($defaults, $configured, $config);
    }

    /**
     * Process an incoming server request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->config['enabled']) {
            return $handler->handle($request);
        }

        // Detect SPA navigation request
        $isNavigation = $this->isNavigationRequest($request);

        // Detect AJAX/component request
        $isAjax = $this->isAjaxRequest($request);

        // Determine if this is an SPA request (either navigation or AJAX)
        $isSpaRequest = $isNavigation || $isAjax;

        // Set request attributes for downstream processing
        $request = $request
            ->withAttribute('spa.isNavigation', $isNavigation)
            ->withAttribute('spa.isAjax', $isAjax)
            ->withAttribute('spa.isSpaRequest', $isSpaRequest);

        $response = $handler->handle($request);

        // Add SPA-specific headers to response
        if ($isSpaRequest) {
            $response = $response->withHeader('X-SPA-Request', 'true');
        }

        return $response;
    }

    /**
     * Check if request is an SPA navigation request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return bool
     */
    protected function isNavigationRequest(ServerRequestInterface $request): bool
    {
        $header = $this->config['navigationHeader'];
        $value = $request->getHeaderLine($header);

        return $value === 'true';
    }

    /**
     * Check if request is an AJAX request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @return bool
     */
    protected function isAjaxRequest(ServerRequestInterface $request): bool
    {
        $header = $this->config['ajaxHeader'];
        $expectedValue = $this->config['ajaxHeaderValue'];
        $value = $request->getHeaderLine($header);

        return $value === $expectedValue;
    }
}
