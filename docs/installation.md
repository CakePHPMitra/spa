# Installation

## Requirements

- PHP 8.1 or higher
- CakePHP 5.0 or higher

## Composer Installation

Install the plugin via Composer:

```bash
composer require mahankals/cakephp-spa
```

## Loading the Plugin

Add the plugin to your application in `src/Application.php`:

```php
public function bootstrap(): void
{
    parent::bootstrap();

    // Load CakeSPA plugin
    $this->addPlugin('CakeSPA');
}
```

The plugin will automatically:
- Register the SPA middleware
- Make the helper and component available

## Manual Setup (Alternative)

If you prefer manual control:

```php
// In Application.php
public function bootstrap(): void
{
    parent::bootstrap();

    $this->addPlugin('CakeSPA', [
        'bootstrap' => true,
        'routes' => false,
        'middleware' => true,
    ]);
}
```

## Loading the Component

Add the SPA component to your `AppController`:

```php
// src/Controller/AppController.php
public function initialize(): void
{
    parent::initialize();

    $this->loadComponent('CakeSPA.Spa');
}
```

## Loading the Helper

The helper is automatically available as `Spa` in your views. If you need to load it manually:

```php
// src/View/AppView.php
public function initialize(): void
{
    parent::initialize();

    $this->loadHelper('CakeSPA.Spa');
}
```

## Including Assets

Include the JavaScript and CSS in your layout:

```php
// templates/layout/default.php
<head>
    <?= $this->Spa->csrfMeta() ?>
    <?= $this->Spa->scripts() ?>
</head>
```

Or include them manually:

```php
<?= $this->Html->css('CakeSPA.cake-spa') ?>
<?= $this->Html->script('CakeSPA.cake-spa') ?>
```

## Verifying Installation

Create a simple test to verify everything works:

```php
// src/Controller/TestController.php
class TestController extends AppController
{
    public function index()
    {
        $this->set('count', 0);
    }

    public function increment()
    {
        return $this->Spa->respond(['count' => 1]);
    }
}
```

```php
// templates/Test/index.php
<?= $this->Spa->target('count', $count) ?>
<?= $this->Spa->button('Test', 'test/increment') ?>
```

If clicking the button updates the count without a page reload, the installation is successful.

## Next Steps

- [Configuration](development/configuration.md) - Customize CakeSPA settings
- [Usage Guide](features/usage.md) - Learn how to use CakeSPA features
- [Examples](features/examples.md) - See complete examples
