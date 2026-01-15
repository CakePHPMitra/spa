# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### Added
- `baseUrlMeta()` helper method for generating base URL meta tag
- `meta()` helper method combining CSRF and base URL meta tags
- Subdirectory/alias deployment support with automatic base URL detection
- JavaScript `detectBaseUrl()` with fallback chain (meta tag → base tag → script src → origin)
- JavaScript `buildFullUrl()` and `getHistoryPath()` helper methods for consistent URL handling

### Changed
- `navLink()` now uses `Url->build()` for proper base path handling
- `loadPage()` and `request()` now use base URL for AJAX calls and history

---

## [1.0.0] - 2025-01-15

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
