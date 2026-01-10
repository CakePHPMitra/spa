# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### Added
- Initial release of CakeSPA plugin for CakePHP 5
- `SpaComponent` for handling AJAX requests and JSON responses
- `SpaHelper` with reactive UI elements:
  - `button()` - AJAX action buttons
  - `target()` - Auto-updating DOM elements
  - `navLink()` - SPA navigation links
  - `form()` - AJAX form submissions
  - `input()` - Debounced reactive inputs
  - `contentContainer()` - Main content wrapper
  - `scripts()` - Asset loading helper
  - `csrfMeta()` - CSRF token meta tag
- JavaScript library (`cake-spa.js`) with:
  - SPA navigation with History API support
  - Automatic DOM updates from JSON responses
  - CSRF token handling
  - Debounced input handling
  - Keyboard shortcut bindings
- CSS styles for loading states and transitions
- Full documentation with examples
