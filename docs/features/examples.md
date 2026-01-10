# Examples

## Counter Application

A simple counter demonstrating reactive updates.

### Controller

```php
// src/Controller/CounterController.php
<?php
declare(strict_types=1);

namespace App\Controller;

class CounterController extends AppController
{
    public function index()
    {
        $count = $this->request->getSession()->read('counter', 0);
        $this->set(compact('count'));
    }

    public function increment()
    {
        $count = $this->request->getSession()->read('counter', 0) + 1;
        $this->request->getSession()->write('counter', $count);

        return $this->Spa->respond(['count' => $count]);
    }

    public function decrement()
    {
        $count = $this->request->getSession()->read('counter', 0) - 1;
        $this->request->getSession()->write('counter', $count);

        return $this->Spa->respond(['count' => $count]);
    }

    public function reset()
    {
        $this->request->getSession()->write('counter', 0);

        return $this->Spa->respond(['count' => 0]);
    }
}
```

### Template

```php
// templates/Counter/index.php
<div class="counter">
    <h1>Counter: <?= $this->Spa->target('count', $count, ['class' => 'count-display']) ?></h1>

    <div class="buttons">
        <?= $this->Spa->button('+', 'counter/increment', [
            'class' => 'btn btn-success',
            'key' => 'ArrowUp',
        ]) ?>

        <?= $this->Spa->button('-', 'counter/decrement', [
            'class' => 'btn btn-danger',
            'key' => 'ArrowDown',
        ]) ?>

        <?= $this->Spa->button('Reset', 'counter/reset', [
            'class' => 'btn btn-secondary',
            'key' => 'r',
        ]) ?>
    </div>

    <p>Keyboard: ↑ (increment) | ↓ (decrement) | R (reset)</p>
</div>
```

---

## Contact List with CRUD

Full CRUD example with filtering and pagination.

### Controller

```php
// src/Controller/ContactsController.php
<?php
declare(strict_types=1);

namespace App\Controller;

class ContactsController extends AppController
{
    public function index()
    {
        $search = $this->request->getQuery('search', '');
        $query = $this->Contacts->find();

        if ($search) {
            $query->where([
                'OR' => [
                    'name LIKE' => "%{$search}%",
                    'email LIKE' => "%{$search}%",
                ],
            ]);
        }

        $contacts = $this->paginate($query);
        $this->set(compact('contacts', 'search'));

        if ($this->Spa->isAjaxRequest()) {
            return $this->Spa->respond([
                'contacts_table' => $this->renderElement('contacts_table'),
            ]);
        }
    }

    public function add()
    {
        $contact = $this->Contacts->newEmptyEntity();

        if ($this->request->is('post')) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->getData());

            if ($this->Contacts->save($contact)) {
                return $this->Spa->success([
                    'contacts_table' => $this->renderElement('contacts_table'),
                ], 'Contact added!');
            }

            return $this->Spa->error('Failed to save contact');
        }

        $this->set(compact('contact'));
    }

    public function delete($id)
    {
        $this->request->allowMethod(['post', 'delete']);
        $contact = $this->Contacts->get($id);

        if ($this->Contacts->delete($contact)) {
            return $this->Spa->success([
                'contacts_table' => $this->renderElement('contacts_table'),
            ], 'Contact deleted!');
        }

        return $this->Spa->error('Failed to delete contact');
    }

    protected function renderElement(string $element): string
    {
        $contacts = $this->paginate($this->Contacts);
        $this->set(compact('contacts'));

        return $this->createView()->element($element);
    }
}
```

### Main Template

```php
// templates/Contacts/index.php
<div class="contacts">
    <h1>Contacts</h1>

    <div class="search-bar">
        <input type="text"
               name="search"
               value="<?= h($search) ?>"
               placeholder="Search..."
               data-spa-action="contacts/index"
               data-spa-push-url="contacts">
    </div>

    <div data-spa-model="contacts_table" data-spa-unsafe-html>
        <?= $this->element('contacts_table') ?>
    </div>
</div>
```

### Table Element

```php
// templates/element/contacts_table.php
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contacts as $contact): ?>
        <tr>
            <td><?= h($contact->name) ?></td>
            <td><?= h($contact->email) ?></td>
            <td>
                <?= $this->Spa->button('Delete', "contacts/delete/{$contact->id}", [
                    'class' => 'btn btn-sm btn-danger',
                ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

---

## SPA Navigation Layout

Complete layout with SPA navigation.

```php
// templates/layout/default.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ?></title>

    <?= $this->Spa->csrfMeta() ?>
    <?= $this->Html->css(['app']) ?>
    <?= $this->Spa->scripts() ?>
</head>
<body>
    <nav class="navbar">
        <div class="brand">
            <a href="/">MyApp</a>
        </div>
        <div class="nav-links">
            <?= $this->Spa->navLink('Dashboard', '/dashboard') ?>
            <?= $this->Spa->navLink('Contacts', '/contacts') ?>
            <?= $this->Spa->navLink('Settings', '/settings') ?>
        </div>
    </nav>

    <main class="container">
        <?= $this->Flash->render() ?>
        <?= $this->Spa->contentContainer($this->fetch('content')) ?>
    </main>

    <footer>
        <p>Powered by CakeSPA</p>
    </footer>
</body>
</html>
```

---

## Real-Time Search

Debounced search with instant results.

```php
// Controller action
public function search()
{
    $query = $this->request->getQuery('q', '');

    $results = $this->Items->find()
        ->where(['name LIKE' => "%{$query}%"])
        ->limit(10)
        ->all();

    return $this->Spa->respond([
        'results' => $this->renderResults($results),
        'count' => $results->count(),
    ]);
}
```

```php
// Template
<div class="search-container">
    <input type="text"
           name="q"
           placeholder="Search items..."
           data-spa-action="items/search"
           data-spa-push-url="items/search">

    <span>Found: <?= $this->Spa->target('count', 0) ?> items</span>

    <div data-spa-model="results" data-spa-unsafe-html>
        <!-- Results appear here -->
    </div>
</div>
```

---

## Form with Validation

AJAX form submission with error handling.

```php
// Controller
public function create()
{
    $item = $this->Items->newEmptyEntity();

    if ($this->request->is('post')) {
        $item = $this->Items->patchEntity($item, $this->request->getData());

        if ($item->hasErrors()) {
            return $this->Spa->error('Validation failed', 400, [
                'errors' => $item->getErrors(),
            ]);
        }

        if ($this->Items->save($item)) {
            return $this->Spa->success([
                'item' => ['id' => $item->id, 'name' => $item->name],
            ], 'Item created!');
        }

        return $this->Spa->error('Failed to save');
    }

    $this->set(compact('item'));
}
```

```php
// Template
<?= $this->Form->create($item, ['data-spa-form' => 'true']) ?>
    <div class="form-group">
        <?= $this->Form->control('name') ?>
    </div>
    <div class="form-group">
        <?= $this->Form->control('description') ?>
    </div>
    <?= $this->Form->button('Create', ['class' => 'btn btn-primary']) ?>
<?= $this->Form->end() ?>

<script>
document.addEventListener('spa:formSubmit', (e) => {
    if (e.detail.data.success) {
        alert(e.detail.data.message);
        e.detail.form.reset();
    }
});

document.addEventListener('spa:error', (e) => {
    if (e.detail.error.errors) {
        // Display validation errors
        console.log(e.detail.error.errors);
    }
});
</script>
```

---

## Infinite Scroll

Load more items on scroll.

```php
// Controller
public function loadMore()
{
    $page = (int)$this->request->getQuery('page', 1);

    $items = $this->paginate($this->Items, ['page' => $page]);

    return $this->Spa->respond([
        'items_html' => $this->renderItems($items),
        'hasMore' => $this->request->getAttribute('paging')['Items']['nextPage'],
    ]);
}
```

```html
<div id="items-container">
    <!-- Items here -->
</div>

<button id="load-more"
        data-spa-action="items/loadMore"
        data-spa-param-page="2"
        style="display: none;">
    Load More
</button>

<script>
let page = 1;

document.addEventListener('spa:action', (e) => {
    if (e.detail.action.includes('loadMore')) {
        page++;
        document.querySelector('#load-more')
            .setAttribute('data-spa-param-page', page + 1);

        if (!e.detail.data.hasMore) {
            document.querySelector('#load-more').style.display = 'none';
        }

        // Append items
        document.querySelector('#items-container').innerHTML +=
            e.detail.data.items_html;
    }
});

// Intersection observer for infinite scroll
const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
        document.querySelector('#load-more').click();
    }
});
observer.observe(document.querySelector('#load-more'));
</script>
```
