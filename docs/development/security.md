# Security

CakeSPA is designed with security as a priority, following CakePHP best practices.

## CSRF Protection

### How It Works

CakeSPA automatically handles CSRF tokens:

1. The token is extracted from a `<meta>` tag or hidden input
2. All AJAX requests include the token in the `X-CSRF-Token` header
3. CakePHP's CSRF middleware validates the token

### Setup

Include the CSRF meta tag in your layout:

```php
<head>
    <?= $this->Spa->csrfMeta() ?>
</head>
```

This generates:

```html
<meta name="csrf-token" content="abc123...">
```

### Verifying CSRF

Ensure CakePHP's CSRF middleware is enabled in `Application.php`:

```php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue
        ->add(new CsrfProtectionMiddleware([
            'httponly' => true,
        ]));

    return $middlewareQueue;
}
```

## XSS Prevention

### Automatic Escaping

All helper output is escaped by default:

```php
// Safe - content is escaped
<?= $this->Spa->target('message', $userInput) ?>
// Output: &lt;script&gt;...

// Safe - button labels are escaped
<?= $this->Spa->button($userLabel, 'action') ?>
```

### HTML Content

When you need to render HTML, use the `html` option carefully:

```php
// Only use with trusted content!
<?= $this->Spa->target('content', $trustedHtml, ['html' => true]) ?>
```

**Never use `html => true` with user input!**

### JavaScript Updates

The JavaScript client also escapes content:

- `textContent` is used by default (safe)
- `innerHTML` is only used when `data-spa-unsafe-html` is present

## Request Validation

### Header Validation

CakeSPA validates request headers:

```php
// Only navigation header triggers layout disable
if ($request->getHeaderLine('X-Live-Nav') === 'true') {
    // Disable layout
}

// AJAX detection excludes navigation
if ($this->Spa->isAjaxRequest()) {
    // Navigation requests return false
}
```

### Method Validation

Always validate HTTP methods in your controllers:

```php
public function delete($id)
{
    $this->request->allowMethod(['post', 'delete']);

    // ... delete logic ...
}
```

## Authorization

### Controller-Level

Use CakePHP's authorization:

```php
public function initialize(): void
{
    parent::initialize();
    $this->loadComponent('Authorization.Authorization');
}

public function edit($id)
{
    $item = $this->Items->get($id);
    $this->Authorization->authorize($item);

    // ... edit logic ...
}
```

### Response-Level

Check authorization before responding:

```php
public function update($id)
{
    $item = $this->Items->get($id);

    if (!$this->Authorization->can($item, 'update')) {
        return $this->Spa->error('Unauthorized', 403);
    }

    // ... update logic ...
}
```

## Input Validation

### Always Validate

Never trust client-side data:

```php
public function save()
{
    $item = $this->Items->newEntity($this->request->getData());

    // Validate
    if ($item->getErrors()) {
        return $this->Spa->error('Validation failed', 400, [
            'errors' => $item->getErrors(),
        ]);
    }

    // Save
    if ($this->Items->save($item)) {
        return $this->Spa->success(['id' => $item->id]);
    }

    return $this->Spa->error('Save failed');
}
```

### Sanitize Output

When returning user data:

```php
return $this->Spa->respond([
    'name' => h($entity->name),  // Escape for display
    'html' => null,               // Never return raw HTML from user input
]);
```

## Content Security Policy

### Headers

Consider adding CSP headers:

```php
// In middleware
$response = $response->withHeader(
    'Content-Security-Policy',
    "default-src 'self'; script-src 'self'"
);
```

### Inline Scripts

CakeSPA doesn't require inline scripts. All JavaScript is in external files.

## Rate Limiting

Protect against abuse:

```php
public function search()
{
    // Implement rate limiting
    $ip = $this->request->clientIp();
    $key = "search_rate_{$ip}";

    if (Cache::read($key) > 10) {
        return $this->Spa->error('Too many requests', 429);
    }

    Cache::increment($key);
    // ... search logic ...
}
```

## Session Security

### Session Fixation

CakePHP handles session regeneration. Ensure it's configured:

```php
// config/app.php
'Session' => [
    'defaults' => 'php',
    'ini' => [
        'session.cookie_httponly' => true,
        'session.cookie_secure' => true,
    ],
],
```

### Session Data

Don't expose sensitive session data in responses:

```php
// Bad
return $this->Spa->respond([
    'session' => $this->request->getSession()->read(),
]);

// Good
return $this->Spa->respond([
    'count' => $this->request->getSession()->read('count'),
]);
```

## Security Checklist

- [ ] CSRF meta tag in layout
- [ ] CSRF middleware enabled
- [ ] All user input validated
- [ ] Output escaped (default)
- [ ] `html => true` only for trusted content
- [ ] Authorization checks in place
- [ ] HTTP methods validated
- [ ] Rate limiting for public actions
- [ ] Session security configured
- [ ] CSP headers considered
