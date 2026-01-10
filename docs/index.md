# CakeSPA Documentation

Welcome to the CakeSPA plugin documentation.

## What is CakeSPA?

CakeSPA is a server-driven SPA (Single Page Application) architecture for CakePHP 5. It provides Livewire-like reactivity without requiring JavaScript frameworks.

## Key Features

- **Zero JavaScript Frameworks** - No React, Vue, or Angular required
- **Server-Driven** - All logic stays on the server
- **Automatic Updates** - DOM updates automatically from JSON responses
- **SPA Navigation** - Page loads without full refresh
- **History API** - Back/forward buttons work correctly
- **CSRF Compatible** - Full security integration
- **Debounced Inputs** - Efficient handling of text inputs
- **Keyboard Shortcuts** - Bind keys to actions

## How It Works

1. **User interacts** with an element (click, type, submit)
2. **JavaScript sends** an AJAX request with appropriate headers
3. **Server processes** and returns JSON or HTML
4. **DOM updates** automatically based on response

```
┌─────────────┐     AJAX      ┌─────────────┐
│   Browser   │ ────────────> │   Server    │
│             │               │             │
│  data-spa-  │ <──────────── │  respond()  │
│    model    │    JSON       │             │
└─────────────┘               └─────────────┘
```

## Quick Example

```php
// Controller
public function like($id)
{
    $post = $this->Posts->get($id);
    $post->likes++;
    $this->Posts->save($post);

    return $this->Spa->respond(['likes' => $post->likes]);
}

// Template
<?= $this->Spa->button('👍 Like', "posts/like/{$post->id}") ?>
Likes: <?= $this->Spa->target('likes', $post->likes) ?>
```

That's it! Clicking the button updates the like count without any page reload.

## Documentation Sections

- [Installation](installation.md) - Getting started
- [Configuration](configuration.md) - Customizing behavior
- [Usage Guide](usage.md) - Complete feature guide
- [Security](security.md) - Security best practices
- [Examples](examples.md) - Real-world examples

## Migration from CakeLive

If you're coming from the original CakeLive implementation, see the migration guide in the README.

## Support

- [GitHub Repository](https://github.com/mahankals/cakephp-spa)
- [GitHub Issues](https://github.com/mahankals/cakephp-spa/issues)
- [CakePHP Slack](https://cakesf.slack.com)
