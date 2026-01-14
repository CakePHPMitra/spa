<?php
declare(strict_types=1);

namespace CakeSPA\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * SPA Helper
 *
 * View helper for creating SPA interactive elements.
 * Provides methods for buttons, links, targets, and navigation.
 *
 * Usage:
 * ```php
 * // Create a button that calls an action
 * echo $this->Spa->button('Increment', 'home/increment');
 *
 * // Create a target element that auto-updates
 * echo $this->Spa->target('counter', $number);
 *
 * // Create a navigation link
 * echo $this->Spa->navLink('Contacts', '/contacts');
 *
 * // Include the SPA scripts
 * echo $this->Spa->scripts();
 * ```
 */
class SpaHelper extends Helper
{
    /**
     * Helpers used by this helper.
     *
     * @var array<string>
     */
    protected array $helpers = ['Html', 'Url'];

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'autoIncludeScripts' => true,
        'buttonClass' => 'btn',
        'loadingText' => 'Loading...',
        'attributePrefix' => 'data-spa',
    ];

    /**
     * Track if scripts have been included.
     *
     * @var bool
     */
    protected bool $scriptsIncluded = false;

    /**
     * Initialize helper with configuration.
     *
     * @param array<string, mixed> $config Configuration options.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Merge with global configuration
        $helperConfig = Configure::read('CakeSPA.helper', []);
        foreach ($helperConfig as $key => $value) {
            if (!isset($config[$key])) {
                $this->setConfig($key, $value);
            }
        }
    }

    /**
     * Create an action button.
     *
     * @param string $label Button label.
     * @param string $action Controller action path (e.g., 'home/increment').
     * @param array<string, mixed> $options Options array.
     * @return string HTML button element.
     */
    public function button(string $label, string $action, array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');

        $defaults = [
            'class' => $this->getConfig('buttonClass'),
            $prefix . '-action' => $action,
        ];

        $defaults = $this->processCommonOptions($defaults, $options, $prefix);
        $attributes = array_merge($defaults, $options);
        $attrs = $this->buildAttributes($attributes);

        return sprintf('<button %s>%s</button>', $attrs, h($label));
    }

    /**
     * Create an action link.
     *
     * @param string $label Link label.
     * @param string $action Controller action path.
     * @param array<string, mixed> $options Options array.
     * @return string HTML link element.
     */
    public function link(string $label, string $action, array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');

        $defaults = [
            'href' => '#',
            $prefix . '-action' => $action,
        ];

        $defaults = $this->processCommonOptions($defaults, $options, $prefix);
        $attributes = array_merge($defaults, $options);
        $attrs = $this->buildAttributes($attributes);

        return sprintf('<a %s>%s</a>', $attrs, h($label));
    }

    /**
     * Create a navigation link (SPA-style page navigation).
     *
     * @param string $label Link label.
     * @param string|array $url URL or route array.
     * @param array<string, mixed> $options Options array.
     * @return string HTML link element.
     */
    public function navLink(string $label, $url, array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');
        $href = is_array($url) ? $this->Url->build($url) : $url;

        $defaults = [
            'href' => $href,
            $prefix . '-nav' => 'true',
        ];

        $attributes = array_merge($defaults, $options);
        $attrs = $this->buildAttributes($attributes);

        return sprintf('<a %s>%s</a>', $attrs, h($label));
    }

    /**
     * Create a target element that auto-updates.
     *
     * @param string $model Model name (key in JSON response).
     * @param mixed $value Initial value.
     * @param array<string, mixed> $options Options array.
     *   - `tag`: HTML tag to use (default: 'span')
     *   - `escape`: Whether to escape the value (default: true)
     *   - `html`: SECURITY WARNING - Set to true to allow raw HTML rendering.
     *             Only use with trusted, sanitized content. Never use with user input.
     * @return string HTML element.
     *
     * SECURITY: The 'html' option disables XSS protection. Only use when:
     * - Content is generated server-side and fully trusted
     * - Content has been sanitized with HTMLPurifier or similar
     * - Never use with any user-provided content
     */
    public function target(string $model, $value = '', array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');
        $tag = $options['tag'] ?? 'span';
        unset($options['tag']);

        $escape = $options['escape'] ?? true;
        unset($options['escape']);

        $defaults = [
            $prefix . '-model' => $model,
        ];

        // Handle unsafe HTML option
        // SECURITY: This bypasses XSS protection - use only with trusted content
        if (isset($options['html']) && $options['html'] === true) {
            $defaults[$prefix . '-unsafe-html'] = 'true';
            $escape = false;
            unset($options['html']);

            // Log warning in debug mode to help developers identify unsafe usage
            if (\Cake\Core\Configure::read('debug')) {
                \Cake\Log\Log::warning(
                    "SpaHelper::target() called with html=true for model '{$model}'. " .
                    "Ensure content is sanitized to prevent XSS."
                );
            }
        }

        $attributes = array_merge($defaults, $options);
        $attrs = $this->buildAttributes($attributes);

        $displayValue = $escape ? h($value) : $value;

        return sprintf('<%s %s>%s</%s>', $tag, $attrs, $displayValue, $tag);
    }

    /**
     * Create a content container for SPA navigation.
     *
     * @param string $content Initial content.
     * @param array<string, mixed> $options Options array.
     * @return string HTML element.
     */
    public function contentContainer(string $content = '', array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');
        $tag = $options['tag'] ?? 'div';
        unset($options['tag']);

        $defaults = [
            $prefix . '-content' => 'true',
        ];

        $attributes = array_merge($defaults, $options);
        $attrs = $this->buildAttributes($attributes);

        return sprintf('<%s %s>%s</%s>', $tag, $attrs, $content, $tag);
    }

    /**
     * Create a form with SPA handling.
     *
     * @param string|null $model Form model.
     * @param array<string, mixed> $options Form options.
     * @return string Form opening tag.
     */
    public function formStart(?string $model = null, array $options = []): string
    {
        $prefix = $this->getConfig('attributePrefix');

        $options[$prefix . '-form'] = 'true';

        // Use CakePHP's FormHelper if available
        if (isset($this->_View->Form)) {
            return $this->_View->Form->create($model, $options);
        }

        // Fallback to basic form
        $action = $options['url'] ?? '';
        $method = $options['method'] ?? 'post';
        unset($options['url'], $options['method']);

        $attrs = $this->buildAttributes($options);

        return sprintf('<form action="%s" method="%s" %s>', h($action), h($method), $attrs);
    }

    /**
     * Include SPA scripts and styles.
     *
     * @param array<string, mixed> $options Options array.
     * @return string HTML script and link tags.
     */
    public function scripts(array $options = []): string
    {
        if ($this->scriptsIncluded) {
            return '';
        }

        $this->scriptsIncluded = true;

        $includeCss = $options['css'] ?? true;
        $includeJs = $options['js'] ?? true;

        $output = '';

        if ($includeCss) {
            $output .= $this->Html->css('CakeSPA.cake-spa') . "\n";
        }

        if ($includeJs) {
            $output .= $this->Html->script('CakeSPA.cake-spa') . "\n";
        }

        return $output;
    }

    /**
     * Generate CSRF meta tag for JavaScript to use.
     *
     * @return string HTML meta tag.
     */
    public function csrfMeta(): string
    {
        $request = $this->_View->getRequest();
        $token = $request->getAttribute('csrfToken');

        if (!$token) {
            return '';
        }

        return sprintf('<meta name="csrf-token" content="%s">', h($token));
    }

    /**
     * Process common options and move to attributes.
     *
     * @param array<string, mixed> $defaults Default attributes.
     * @param array<string, mixed> $options User options (modified by reference).
     * @param string $prefix Attribute prefix.
     * @return array<string, mixed> Updated defaults.
     */
    protected function processCommonOptions(array $defaults, array &$options, string $prefix): array
    {
        // Loading text
        if (isset($options['loading'])) {
            $defaults[$prefix . '-loading'] = $options['loading'];
            unset($options['loading']);
        }

        // Keyboard shortcut
        if (isset($options['key'])) {
            $defaults[$prefix . '-key'] = $options['key'];
            unset($options['key']);
        }

        // Target selector
        if (isset($options['target'])) {
            $defaults[$prefix . '-target'] = $options['target'];
            unset($options['target']);
        }

        // Push URL
        if (isset($options['pushUrl'])) {
            $defaults[$prefix . '-push-url'] = $options['pushUrl'];
            unset($options['pushUrl']);
        }

        // Parameters
        if (isset($options['params']) && is_array($options['params'])) {
            foreach ($options['params'] as $key => $value) {
                $defaults[$prefix . '-param-' . $key] = $value;
            }
            unset($options['params']);
        }

        return $defaults;
    }

    /**
     * Build HTML attributes string.
     *
     * @param array<string, mixed> $attributes Attributes array.
     * @return string HTML attributes string.
     */
    protected function buildAttributes(array $attributes): string
    {
        $attrs = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            if ($value === true) {
                $attrs[] = h($key);
            } else {
                $attrs[] = sprintf('%s="%s"', h($key), h((string)$value));
            }
        }

        return implode(' ', $attrs);
    }
}
