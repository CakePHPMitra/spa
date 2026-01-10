# Configuration

CakeSPA works out of the box with sensible defaults. All configuration is optional.

## Configuration File

Create `config/cake_spa.php` in your application:

```php
<?php
return [
    'CakeSPA' => [
        'enabled' => true,
        'navigationHeader' => 'X-Live-Nav',
        'ajaxHeader' => 'X-Requested-With',
        'ajaxHeaderValue' => 'XMLHttpRequest',
        'defaultLayout' => 'default',
        'loadingClass' => 'spa-loading',
        'debounceTime' => 500,
        'historyEnabled' => true,
        'attributePrefix' => 'data-spa',

        'csrf' => [
            'metaName' => 'csrf-token',
            'inputName' => '_csrfToken',
            'headerName' => 'X-CSRF-Token',
        ],

        'js' => [
            'autoInit' => true,
            'globalName' => 'cakeSpa',
            'debug' => false,
        ],

        'helper' => [
            'buttonClass' => 'btn',
            'loadingText' => 'Loading...',
            'autoIncludeScripts' => true,
        ],
    ],
];
```

Load the configuration in `config/bootstrap.php`:

```php
Configure::load('cake_spa');
```

## Configuration Options

### Core Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enabled` | bool | `true` | Enable/disable SPA functionality globally |
| `navigationHeader` | string | `X-Live-Nav` | Header for SPA navigation requests |
| `ajaxHeader` | string | `X-Requested-With` | Header for AJAX detection |
| `ajaxHeaderValue` | string | `XMLHttpRequest` | Expected value for AJAX header |
| `defaultLayout` | string | `default` | Layout for full page requests |
| `loadingClass` | string | `spa-loading` | CSS class during loading |
| `debounceTime` | int | `500` | Debounce time (ms) for input events |
| `historyEnabled` | bool | `true` | Enable History API integration |
| `attributePrefix` | string | `data-spa` | Prefix for data attributes |

### CSRF Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `csrf.metaName` | string | `csrf-token` | Meta tag name for CSRF token |
| `csrf.inputName` | string | `_csrfToken` | Input field name for CSRF token |
| `csrf.headerName` | string | `X-CSRF-Token` | Header name for CSRF in AJAX |

### JavaScript Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `js.autoInit` | bool | `true` | Auto-initialize on page load |
| `js.globalName` | string | `cakeSpa` | Global variable name |
| `js.debug` | bool | `false` | Enable debug logging |

### Helper Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `helper.buttonClass` | string | `btn` | Default button CSS class |
| `helper.loadingText` | string | `Loading...` | Default loading text |
| `helper.autoIncludeScripts` | bool | `true` | Auto-include JS/CSS |

## Component Configuration

Configure the component when loading:

```php
$this->loadComponent('CakeSPA.Spa', [
    'navigationHeader' => 'X-Custom-Nav',
    'enabled' => true,
]);
```

## Helper Configuration

Configure the helper in your view:

```php
$this->loadHelper('CakeSPA.Spa', [
    'buttonClass' => 'btn btn-primary',
    'attributePrefix' => 'data-live',
]);
```

## JavaScript Configuration

Configure the JavaScript client:

```html
<script>
    // Before cake-spa.js loads
    window.CakeSPAConfig = {
        debug: true,
        debounceTime: 300,
        attributePrefix: 'data-live'
    };
</script>
<?= $this->Spa->scripts() ?>
```

Or after initialization:

```javascript
// Reconfigure at runtime
cakeSpa.config.debug = true;
cakeSpa.config.debounceTime = 300;
```

## Environment-Specific Configuration

Use environment variables for different settings:

```php
// config/cake_spa.php
return [
    'CakeSPA' => [
        'js' => [
            'debug' => filter_var(
                env('CAKE_SPA_DEBUG', false),
                FILTER_VALIDATE_BOOLEAN
            ),
        ],
    ],
];
```

## Disabling for Specific Controllers

Disable SPA for specific controllers:

```php
class ApiController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        // Don't load SPA component for API
        if ($this->components()->has('Spa')) {
            $this->components()->unload('Spa');
        }
    }
}
```

Or configure per-action:

```php
public function beforeFilter(EventInterface $event): void
{
    parent::beforeFilter($event);

    if ($this->getRequest()->getParam('action') === 'export') {
        $this->Spa->setConfig('enabled', false);
    }
}
```
