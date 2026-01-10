<?php
declare(strict_types=1);

namespace CakeSPA\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * SPA Component
 *
 * Provides SPA functionality to controllers.
 * Handles request detection, JSON responses, and layout switching.
 *
 * Usage:
 * ```php
 * // In your controller
 * public function initialize(): void
 * {
 *     parent::initialize();
 *     $this->loadComponent('CakeSPA.Spa');
 * }
 *
 * public function increment(): ?Response
 * {
 *     $count = $this->request->getSession()->read('count', 0) + 1;
 *     $this->request->getSession()->write('count', $count);
 *
 *     return $this->Spa->respond(['count' => $count]);
 * }
 * ```
 */
class SpaComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'enabled' => true,
        'navigationHeader' => 'X-Live-Nav',
    ];

    /**
     * Controller instance.
     *
     * @var \Cake\Controller\Controller
     */
    protected $controller;

    /**
     * Constructor.
     *
     * @param \Cake\Controller\ComponentRegistry $registry Component registry.
     * @param array<string, mixed> $config Configuration options.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        // Merge with global configuration
        $globalConfig = Configure::read('CakeSPA', []);
        $config = array_merge($globalConfig, $config);

        parent::__construct($registry, $config);
    }

    /**
     * Called before the controller action.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        $this->controller = $this->getController();
    }

    /**
     * Called before rendering.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        if ($this->isNavigationRequest()) {
            // Disable layout for SPA navigation requests
            $this->controller->viewBuilder()->disableAutoLayout();
        }
    }

    /**
     * Check if the current request is an SPA navigation request.
     *
     * @return bool
     */
    public function isNavigationRequest(): bool
    {
        // Check request attribute first (set by middleware)
        $fromAttribute = $this->getController()->getRequest()->getAttribute('spa.isNavigation');
        if ($fromAttribute !== null) {
            return (bool)$fromAttribute;
        }

        // Fallback to header check
        $header = $this->getConfig('navigationHeader');

        return $this->getController()->getRequest()->getHeaderLine($header) === 'true';
    }

    /**
     * Check if the current request is an SPA AJAX request.
     * Excludes navigation requests which should render HTML.
     *
     * @return bool
     */
    public function isAjaxRequest(): bool
    {
        // Navigation requests should render HTML, not JSON
        if ($this->isNavigationRequest()) {
            return false;
        }

        // Check request attribute first
        $fromAttribute = $this->getController()->getRequest()->getAttribute('spa.isAjax');
        if ($fromAttribute !== null) {
            return (bool)$fromAttribute;
        }

        // Fallback to standard AJAX detection
        $request = $this->getController()->getRequest();

        return $request->is('ajax') ||
            $request->is('json') ||
            $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Check if this is any type of SPA request.
     *
     * @return bool
     */
    public function isSpaRequest(): bool
    {
        return $this->isNavigationRequest() || $this->isAjaxRequest();
    }

    /**
     * Return a response for SPA requests.
     *
     * For AJAX requests: Returns JSON
     * For navigation requests: Continues with normal rendering (without layout)
     * For regular requests: Redirects to referer
     *
     * @param array<string, mixed> $data Data to return.
     * @param array<string, mixed> $options Response options.
     * @return \Cake\Http\Response|null
     */
    public function respond(array $data, array $options = []): ?Response
    {
        $controller = $this->getController();

        if ($this->isAjaxRequest()) {
            return $this->jsonResponse($data);
        }

        if ($this->isNavigationRequest()) {
            // Set view variables for HTML rendering
            $controller->set($data);

            return null;
        }

        // Regular request - redirect
        return $this->redirectResponse($options);
    }

    /**
     * Return a JSON response.
     *
     * @param array<string, mixed> $data Data to serialize.
     * @return \Cake\Http\Response|null
     */
    public function jsonResponse(array $data): ?Response
    {
        $controller = $this->getController();

        $controller->viewBuilder()->setClassName('Json');
        $controller->set($data);
        $controller->viewBuilder()->setOption('serialize', array_keys($data));

        return null;
    }

    /**
     * Return a success response.
     *
     * @param array<string, mixed> $data Data to return.
     * @param string $message Success message.
     * @return \Cake\Http\Response|null
     */
    public function success(array $data, string $message = 'Success'): ?Response
    {
        $data['success'] = true;
        $data['message'] = $message;

        return $this->respond($data, ['message' => $message]);
    }

    /**
     * Return an error response.
     *
     * @param string $message Error message.
     * @param int $code HTTP status code.
     * @param array<string, mixed> $data Additional data.
     * @return \Cake\Http\Response|null
     */
    public function error(string $message, int $code = 400, array $data = []): ?Response
    {
        $controller = $this->getController();

        if ($this->isAjaxRequest()) {
            $controller->setResponse($controller->getResponse()->withStatus($code));

            $responseData = array_merge($data, [
                'error' => true,
                'message' => $message,
                'code' => $code,
            ]);

            return $this->jsonResponse($responseData);
        }

        // Regular request - flash message and redirect
        if ($controller->components()->has('Flash')) {
            $controller->Flash->error($message);
        }

        return $controller->redirect($controller->getRequest()->referer(true));
    }

    /**
     * Redirect response for non-AJAX requests.
     *
     * @param array<string, mixed> $options Redirect options.
     * @return \Cake\Http\Response
     */
    protected function redirectResponse(array $options = []): Response
    {
        $controller = $this->getController();

        $url = $options['url'] ?? $controller->getRequest()->referer(true);
        $status = $options['status'] ?? 302;

        if (isset($options['message']) && $controller->components()->has('Flash')) {
            $controller->Flash->success($options['message']);
        }

        return $controller->redirect($url, $status);
    }
}
