# Usage Guide

## Core Concepts

CakeSPA provides three main features:

1. **Actions** - AJAX calls that return JSON data
2. **Targets** - Elements that auto-update from JSON responses
3. **Navigation** - SPA-style page loading

## Helper Methods

### Buttons

Create buttons that trigger server actions:

```php
// Basic button
<?= $this->Spa->button('Save', 'items/save') ?>

// With options
<?= $this->Spa->button('Delete', 'items/delete', [
    'class' => 'btn btn-danger',
    'loading' => 'Deleting...',
    'params' => ['id' => $item->id],
]) ?>
```

**Options:**

| Option | Description |
|--------|-------------|
| `class` | CSS class(es) |
| `loading` | Text shown during request |
| `key` | Keyboard shortcut |
| `target` | Specific target selector |
| `pushUrl` | Update browser URL |
| `params` | Parameters to send |

### Links

Create links that trigger actions:

```php
<?= $this->Spa->link('Refresh', 'items/refresh', [
    'class' => 'refresh-link',
]) ?>
```

### Navigation Links

Create SPA navigation links:

```php
<?= $this->Spa->navLink('Dashboard', '/dashboard') ?>
<?= $this->Spa->navLink('Settings', ['controller' => 'Settings', 'action' => 'index']) ?>
```

### Targets

Create elements that receive updates:

```php
// Simple target
<?= $this->Spa->target('count', $count) ?>

// With custom tag
<?= $this->Spa->target('message', $message, ['tag' => 'div']) ?>

// With CSS class
<?= $this->Spa->target('status', $status, ['class' => 'status-badge']) ?>

// HTML content (unsafe)
<?= $this->Spa->target('content', $html, ['html' => true, 'tag' => 'div']) ?>
```

### Content Container

Wrap your main content for SPA navigation:

```php
// In layout
<?= $this->Spa->contentContainer($this->fetch('content')) ?>

// With options
<?= $this->Spa->contentContainer($this->fetch('content'), [
    'class' => 'main-content',
    'id' => 'app-content',
]) ?>
```

### Scripts and Meta Tags

Include required assets and meta tags:

```php
// In <head> - recommended approach
<?= $this->Spa->meta() ?>
<?= $this->Spa->scripts() ?>
```

The `meta()` method outputs both CSRF token and base URL meta tags:

```html
<meta name="csrf-token" content="...">
<meta name="base-url" content="https://example.com/myapp/">
```

**Individual methods:**

```php
<?= $this->Spa->csrfMeta() ?>    <!-- CSRF token only -->
<?= $this->Spa->baseUrlMeta() ?> <!-- Base URL only -->
<?= $this->Spa->scripts(['css' => false]) ?> <!-- JS only -->
<?= $this->Spa->scripts(['js' => false]) ?>  <!-- CSS only -->
```

### Subdirectory Deployments

When deploying under a subdirectory/alias (e.g., `/myapp/`), the base URL meta tag ensures all AJAX requests and SPA navigation use the correct path.

The JavaScript detects base URL using this priority:
1. `<meta name="base-url">` (server-generated, recommended)
2. `<base href>` tag
3. Script src auto-detection
4. Window origin fallback

## Component Methods

### Request Detection

```php
// Check if SPA navigation request
if ($this->Spa->isNavigationRequest()) {
    // Render without layout
}

// Check if AJAX request (excludes navigation)
if ($this->Spa->isAjaxRequest()) {
    // Return JSON
}

// Check if any SPA request
if ($this->Spa->isSpaRequest()) {
    // Handle SPA request
}
```

### Responses

```php
// Standard response
public function update()
{
    $data = ['count' => 42, 'message' => 'Updated'];
    return $this->Spa->respond($data);
}

// Success response
public function save()
{
    // ... save logic ...
    return $this->Spa->success(['id' => $entity->id], 'Saved successfully!');
}

// Error response
public function delete()
{
    if (!$this->authorize()) {
        return $this->Spa->error('Unauthorized', 403);
    }
    // ... delete logic ...
}

// JSON-only response
public function api()
{
    return $this->Spa->jsonResponse(['data' => $items]);
}
```

## Data Attributes

The helper generates these data attributes:

| Attribute | Description |
|-----------|-------------|
| `data-spa-action` | Action URL for AJAX call |
| `data-spa-model` | Model name for auto-update |
| `data-spa-nav` | SPA navigation link |
| `data-spa-content` | Content container |
| `data-spa-form` | AJAX form |
| `data-spa-loading` | Loading text |
| `data-spa-key` | Keyboard shortcut |
| `data-spa-target` | Target selector |
| `data-spa-push-url` | URL to push to history |
| `data-spa-param-*` | Action parameters |
| `data-spa-unsafe-html` | Allow HTML content |
| `data-spa-class` | Update element class |

## Forms

Create AJAX forms:

```php
<?= $this->Spa->formStart(null, ['url' => '/contacts/save']) ?>
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <button type="submit">Save</button>
<?= $this->Form->end() ?>
```

Or use data attributes directly:

```php
<?= $this->Form->create($contact, ['data-spa-form' => 'true']) ?>
    // ... form fields ...
<?= $this->Form->end() ?>
```

## JavaScript API

### Manual Actions

```javascript
// Call an action
const data = await CakeSPA.call('items/refresh', { page: 2 });

// Navigate programmatically
CakeSPA.navigate('/dashboard');

// Update an element manually
CakeSPA.update('#my-element', 'New content');
```

### Events

```javascript
// Action completed
document.addEventListener('spa:action', (e) => {
    console.log('Action:', e.detail.action);
    console.log('Data:', e.detail.data);
});

// Navigation completed
document.addEventListener('spa:navigate', (e) => {
    console.log('Navigated to:', e.detail.url);
});

// Form submitted
document.addEventListener('spa:formSubmit', (e) => {
    console.log('Form:', e.detail.form);
});

// Error occurred
document.addEventListener('spa:error', (e) => {
    console.error('Error:', e.detail.error);
    // Custom error handling
});

// Initialization
document.addEventListener('spa:init', (e) => {
    console.log('CakeSPA ready');
});
```

## Advanced Usage

### Custom Loading States

```php
<?= $this->Spa->button('Save', 'items/save', [
    'loading' => 'Saving...',
    'class' => 'btn',
    'id' => 'save-btn',
]) ?>
```

```css
#save-btn.spa-loading {
    background: #ccc;
}
```

### Targeting Specific Elements

```php
<?= $this->Spa->button('Refresh Table', 'items/list', [
    'target' => '#items-table',
]) ?>

<div id="items-table" data-spa-model="items_table" data-spa-unsafe-html>
    <?= $this->element('items_table') ?>
</div>
```

### URL Updates

```php
<!-- Update URL with action parameters -->
<?= $this->Spa->button('Search', 'items/search', [
    'pushUrl' => 'true',  // Uses action URL
]) ?>

<!-- Update URL with custom path -->
<?= $this->Spa->button('Filter', 'items/filter', [
    'pushUrl' => 'items',  // Uses /items?params
]) ?>
```

### Keyboard Shortcuts

```php
<?= $this->Spa->button('Save', 'items/save', ['key' => 's']) ?>
<?= $this->Spa->button('Delete', 'items/delete', ['key' => 'Delete']) ?>
```

Shortcuts are ignored when typing in input fields.
