# CakeSPA Plugin for CakePHP 5

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![CakePHP](https://img.shields.io/badge/CakePHP-5.x-red.svg)](https://cakephp.org)
[![GitHub](https://img.shields.io/github/stars/mahankals/cakephp-spa?style=social)](https://github.com/mahankals/cakephp-spa)

Server-driven SPA architecture for CakePHP 5. Build reactive, single-page applications without JavaScript frameworks.

Based on [CakePHP Plugin Template](https://github.com/mahankals/CakePHP-Plugin-Template).

## Features

- **SPA Navigation** - Load pages via AJAX with History API support
- **Reactive Components** - Livewire-like reactivity without writing JavaScript
- **JSON/HTML Hybrid** - Seamless handling of both response types
- **CSRF Compatible** - Full security integration with CakePHP
- **Zero Configuration** - Works out of the box with sensible defaults
- **Framework Agnostic** - No JavaScript framework dependencies

## Requirements

- PHP 8.1+
- CakePHP 5.0+

## Installation

You can install this plugin directly from GitHub using Composer:

1. Add the GitHub repository to your app's `composer.json`:

   ```json
   "repositories": [
       {
           "type": "vcs",
           "url": "https://github.com/mahankals/cakephp-spa"
       }
   ]
   ```

2. Require the plugin via Composer:

   ```bash
   composer require mahankals/cakephp-spa:dev-master
   ```

3. Load the plugin:

   **Method 1: from terminal**

   ```bash
   bin/cake plugin load CakeSPA
   ```

   **Method 2: load in `Application.php`, bootstrap method**

   ```php
   public function bootstrap(): void
   {
       parent::bootstrap();
       $this->addPlugin('CakeSPA');
   }
```

## Quick Start

### 1. Add the Component to Your Controller

```php
// src/Controller/AppController.php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('CakeSPA.Spa');
}
```

### 2. Use the Helper in Your Layout

```php
// templates/layout/default.php
<!DOCTYPE html>
<html>
<head>
    <?= $this->Spa->csrfMeta() ?>
    <?= $this->Spa->scripts() ?>
</head>
<body>
    <nav>
        <?= $this->Spa->navLink('Home', '/') ?>
        <?= $this->Spa->navLink('About', '/about') ?>
    </nav>

    <?= $this->Spa->contentContainer($this->fetch('content')) ?>
</body>
</html>
```

### 3. Create Reactive Actions

```php
// src/Controller/CounterController.php
class CounterController extends AppController
{
    public function index()
    {
        $count = $this->request->getSession()->read('count', 0);
        $this->set(compact('count'));
    }

    public function increment()
    {
        $count = $this->request->getSession()->read('count', 0) + 1;
        $this->request->getSession()->write('count', $count);

        return $this->Spa->respond(['count' => $count]);
    }
}
```

### 4. Build Your View

```php
// templates/Counter/index.php
<div>
    <p>Count: <?= $this->Spa->target('count', $count) ?></p>

    <?= $this->Spa->button('Increment', 'counter/increment') ?>
</div>
```

## Documentation

- [Installation Guide](docs/installation.md)
- [Configuration](docs/configuration.md)
- [Usage Guide](docs/usage.md)
- [Security](docs/security.md)
- [Examples](docs/examples.md)

## How It Works

CakeSPA uses a simple data-attribute based approach:

1. **Actions**: Elements with `data-spa-action` trigger AJAX requests
2. **Targets**: Elements with `data-spa-model` auto-update from JSON responses
3. **Navigation**: Links with `data-spa-nav` load pages without full reload

The server returns JSON for component updates and HTML for page navigation, determined automatically by request headers.

## Migration from CakeLive

If you're migrating from the original CakeLive implementation:

| CakeLive | CakeSPA |
|----------|---------|
| `$this->Live->button()` | `$this->Spa->button()` |
| `$this->Live->target()` | `$this->Spa->target()` |
| `data-live-action` | `data-spa-action` |
| `data-live-model` | `data-spa-model` |
| `LiveComponentTrait` | `SpaComponent` |

## Contributing

Contributions are welcome! Please read our contributing guidelines before submitting PRs.

- [GitHub Repository](https://github.com/mahankals/cakephp-spa)
- [Issue Tracker](https://github.com/mahankals/cakephp-spa/issues)

## Author

[Atul Mahankal](https://atulmahankal.github.io/atulmahankal/)

## License

MIT License. See [LICENSE](LICENSE) for details.
