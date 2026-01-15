/**
 * CakeSPA - Server-driven SPA for CakePHP 5
 *
 * Provides AJAX-based reactivity and SPA navigation without JavaScript frameworks.
 * Similar to Laravel Livewire but framework-agnostic.
 *
 * @version 1.0.0
 * @license MIT
 */

(function(global) {
    'use strict';

    /**
     * CakeSPA Configuration
     */
    const DEFAULT_CONFIG = {
        attributePrefix: 'data-spa',
        debounceTime: 500,
        loadingClass: 'spa-loading',
        updatingClass: 'spa-updating',
        csrfMetaName: 'csrf-token',
        csrfInputName: '_csrfToken',
        csrfHeaderName: 'X-CSRF-Token',
        navigationHeader: 'X-Live-Nav',
        ajaxHeader: 'X-Requested-With',
        debug: false
    };

    /**
     * CakeSPA Class
     */
    class CakeSPA {
        /**
         * Constructor
         * @param {Object} config - Configuration options
         */
        constructor(config = {}) {
            this.config = { ...DEFAULT_CONFIG, ...config };
            this.baseUrl = this.detectBaseUrl();
            this.csrfToken = null;
            this.loadingElements = new Set();
            this.debounceTimers = new Map();
            this.prefix = this.config.attributePrefix;

            // Auto-initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }

        /**
         * Initialize CakeSPA
         */
        init() {
            this.extractCsrfToken();
            this.bindActions();
            this.bindForms();
            this.bindNavigation();
            this.bindInputs();
            this.bindSelects();
            this.setupKeyboardShortcuts();
            this.setupHistoryHandling();

            this.log('CakeSPA initialized');
            this.dispatch('spa:init', { instance: this });
        }

        /**
         * Debug logging
         * @param {...any} args - Arguments to log
         */
        log(...args) {
            if (this.config.debug) {
                console.log('[CakeSPA]', ...args);
            }
        }

        /**
         * Detect base URL for subdirectory/alias deployments
         * Priority: 1) <base> tag, 2) meta[name="base-url"], 3) script src, 4) origin
         * @returns {string} Base URL with trailing slash
         */
        detectBaseUrl() {
            let baseUrl = null;

            // 1. Check for <base> tag (CakePHP best practice)
            const baseTag = document.querySelector('base[href]');
            if (baseTag && baseTag.href) {
                baseUrl = baseTag.href;
                this.log('Base URL from <base> tag:', baseUrl);
            }

            // 2. Check for meta tag
            if (!baseUrl) {
                const metaBase = document.querySelector('meta[name="base-url"]');
                if (metaBase) {
                    const content = metaBase.getAttribute('content');
                    if (content) {
                        // Handle relative or absolute URLs
                        baseUrl = content.startsWith('http')
                            ? content
                            : window.location.origin + content;
                        this.log('Base URL from meta tag:', baseUrl);
                    }
                }
            }

            // 3. Auto-detect from cake-spa.js script src
            if (!baseUrl) {
                const scripts = document.querySelectorAll('script[src]');
                for (const script of scripts) {
                    const src = script.src;
                    // Match: /cakephp/cake_s_p_a/js/cake-spa.js or similar patterns
                    if (src.includes('cake-spa') || src.includes('cake_s_p_a')) {
                        try {
                            const url = new URL(src);
                            // Extract path before cake_s_p_a or cake-spa
                            const match = url.pathname.match(/^(.*?)cake[_-]?s[_-]?p[_-]?a/i);
                            if (match) {
                                baseUrl = url.origin + match[1];
                                this.log('Base URL from script src:', baseUrl);
                                break;
                            }
                        } catch (e) {
                            // Invalid URL, skip
                        }
                    }
                }
            }

            // 4. Fallback to origin
            if (!baseUrl) {
                baseUrl = window.location.origin + '/';
                this.log('Base URL fallback to origin:', baseUrl);
            }

            // Ensure trailing slash
            return baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
        }

        /**
         * Extract CSRF token from meta tag or hidden input
         */
        extractCsrfToken() {
            // Try meta tag first
            const meta = document.querySelector(`meta[name="${this.config.csrfMetaName}"]`);
            if (meta) {
                this.csrfToken = meta.getAttribute('content');
                return;
            }

            // Try hidden input
            const input = document.querySelector(`input[name="${this.config.csrfInputName}"]`);
            if (input) {
                this.csrfToken = input.value;
            }
        }

        /**
         * Bind click events for action elements
         */
        bindActions() {
            document.addEventListener('click', (e) => {
                const selector = `[${this.prefix}-action]`;
                const element = e.target.closest(selector);

                // Skip form elements - they use different event handlers
                const skipTags = ['FORM', 'INPUT', 'TEXTAREA', 'SELECT'];
                if (element && !skipTags.includes(element.tagName)) {
                    e.preventDefault();
                    this.handleAction(element);
                }
            });
        }

        /**
         * Bind form submissions
         */
        bindForms() {
            document.addEventListener('submit', (e) => {
                const selector = `form[${this.prefix}-form]`;
                const form = e.target.closest(selector);
                if (form) {
                    e.preventDefault();
                    this.handleForm(form);
                }
            });
        }

        /**
         * Bind navigation links
         */
        bindNavigation() {
            document.addEventListener('click', (e) => {
                const selector = `a[${this.prefix}-nav]`;
                const link = e.target.closest(selector);
                if (link) {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    this.navigate(url);
                }
            });
        }

        /**
         * Bind input events with debouncing
         */
        bindInputs() {
            document.addEventListener('input', (e) => {
                const selector = `input[${this.prefix}-action], textarea[${this.prefix}-action]`;
                const element = e.target.closest(selector);

                if (element && !['submit', 'button'].includes(element.type)) {
                    // Clear existing timer
                    const existing = this.debounceTimers.get(element);
                    if (existing) clearTimeout(existing);

                    // Set new debounced timer
                    const timer = setTimeout(() => {
                        this.handleAction(element);
                        this.debounceTimers.delete(element);
                    }, this.config.debounceTime);

                    this.debounceTimers.set(element, timer);
                }
            });
        }

        /**
         * Bind select change events
         */
        bindSelects() {
            document.addEventListener('change', (e) => {
                const selector = `select[${this.prefix}-action]`;
                const element = e.target.closest(selector);
                if (element) {
                    e.preventDefault();
                    this.handleAction(element);
                }
            });
        }

        /**
         * Setup keyboard shortcuts
         */
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Skip when typing in inputs
                if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                    return;
                }

                const selector = `[${this.prefix}-key]`;
                const elements = document.querySelectorAll(selector);

                elements.forEach(element => {
                    const key = element.getAttribute(`${this.prefix}-key`);
                    if (key === e.key) {
                        e.preventDefault();
                        element.click();
                    }
                });
            });
        }

        /**
         * Setup History API handling
         */
        setupHistoryHandling() {
            // Handle back/forward navigation
            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.spa) {
                    this.loadPage(window.location.pathname + window.location.search, false);
                }
            });

            // Mark initial page load
            window.history.replaceState({ spa: true }, '', window.location.href);
        }

        /**
         * Handle action from element
         * @param {HTMLElement} element - The triggering element
         */
        async handleAction(element) {
            const action = element.getAttribute(`${this.prefix}-action`);
            if (!action) return;

            const params = this.extractParams(element);

            // Add input/select value to params
            if (['SELECT', 'INPUT', 'TEXTAREA'].includes(element.tagName)) {
                const name = element.getAttribute('name') || 'value';
                params[name] = element.value;
            }

            const target = element.getAttribute(`${this.prefix}-target`);
            const loading = element.getAttribute(`${this.prefix}-loading`);
            const pushUrl = element.getAttribute(`${this.prefix}-push-url`);

            this.setLoading(element, true, loading);

            try {
                const data = await this.request(action, params);

                // Update URL if requested
                if (pushUrl) {
                    const newUrl = pushUrl === 'true'
                        ? this.buildUrl(action, params)
                        : this.buildUrl(pushUrl, params);
                    window.history.pushState({ spa: true }, '', newUrl);
                }

                // Update targets
                if (target) {
                    this.updateTargets(target, data);
                } else {
                    this.autoUpdateTargets(data);
                }

                this.dispatch('spa:action', { action, data, element });

            } catch (error) {
                this.handleError(error, element);
            } finally {
                this.setLoading(element, false, loading);
            }
        }

        /**
         * Handle form submission
         * @param {HTMLFormElement} form - The form element
         */
        async handleForm(form) {
            const action = form.getAttribute('action') || window.location.pathname;
            const method = (form.getAttribute('method') || 'POST').toUpperCase();
            const formData = new FormData(form);
            const loading = form.getAttribute(`${this.prefix}-loading`);

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) this.setLoading(submitBtn, true, loading);

            try {
                let params = {};
                let body = null;

                if (method === 'GET') {
                    for (const [key, value] of formData.entries()) {
                        params[key] = value;
                    }
                } else {
                    body = formData;
                }

                const data = await this.request(action, params, method, body);
                this.autoUpdateTargets(data);

                this.dispatch('spa:formSubmit', { action, data, form });

            } catch (error) {
                this.handleError(error, form);
            } finally {
                if (submitBtn) this.setLoading(submitBtn, false, loading);
            }
        }

        /**
         * Navigate to a new page (SPA-style)
         * @param {string} url - The URL to navigate to
         */
        async navigate(url) {
            const container = document.querySelector(`[${this.prefix}-content]`);
            if (!container) {
                // Fallback to regular navigation
                window.location.href = url;
                return;
            }

            await this.loadPage(url, true);
        }

        /**
         * Load page content via AJAX
         * @param {string} url - The URL to load
         * @param {boolean} pushState - Whether to push to history
         */
        async loadPage(url, pushState = true) {
            const container = document.querySelector(`[${this.prefix}-content]`);
            if (!container) return;

            container.classList.add(this.config.loadingClass);

            try {
                const fullUrl = url.startsWith('http') ? url : this.baseUrl + url.replace(/^\//, '');

                const response = await fetch(fullUrl, {
                    headers: {
                        [this.config.ajaxHeader]: 'XMLHttpRequest',
                        [this.config.navigationHeader]: 'true',
                        'Accept': 'text/html'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const html = await response.text();
                container.innerHTML = html;

                if (pushState) {
                    window.history.pushState({ spa: true }, '', url);
                }

                // Re-extract CSRF token from new content
                this.extractCsrfToken();

                this.dispatch('spa:navigate', { url, container });

            } catch (error) {
                this.log('Navigation error:', error);
                // Fallback to regular navigation
                window.location.href = url;
            } finally {
                container.classList.remove(this.config.loadingClass);
            }
        }

        /**
         * Make an AJAX request
         * @param {string} actionPath - The action path
         * @param {Object} params - Query parameters
         * @param {string} method - HTTP method
         * @param {FormData|null} body - Request body
         * @returns {Promise<Object>} Response data
         */
        async request(actionPath, params = {}, method = 'GET', body = null) {
            let url = actionPath.startsWith('http') ? actionPath : this.baseUrl + actionPath;

            // Fix double slashes
            url = url.replace(/([^:]\/)\/+/g, '$1');

            // Add query params for GET requests
            if (method === 'GET' && Object.keys(params).length > 0) {
                const query = new URLSearchParams(params).toString();
                url += (url.includes('?') ? '&' : '?') + query;
            }

            const headers = {
                [this.config.ajaxHeader]: 'XMLHttpRequest',
                'Accept': 'application/json'
            };

            if (this.csrfToken) {
                headers[this.config.csrfHeaderName] = this.csrfToken;
            }

            const options = { method, headers };
            if (body) options.body = body;

            const response = await fetch(url, options);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            return response.json();
        }

        /**
         * Extract parameters from element attributes
         * @param {HTMLElement} element - The element
         * @returns {Object} Parameters object
         */
        extractParams(element) {
            const params = {};
            const prefix = `${this.prefix}-param-`;

            Array.from(element.attributes).forEach(attr => {
                if (attr.name.startsWith(prefix)) {
                    const paramName = attr.name.slice(prefix.length);
                    params[paramName] = attr.value;
                }
            });

            return params;
        }

        /**
         * Build URL with query parameters
         * @param {string} base - Base URL
         * @param {Object} params - Parameters
         * @returns {string} Full URL
         */
        buildUrl(base, params) {
            if (Object.keys(params).length === 0) return base;
            const query = new URLSearchParams(params).toString();
            return base + (base.includes('?') ? '&' : '?') + query;
        }

        /**
         * Update specific targets
         * @param {string} selector - Target selector
         * @param {Object} data - Data object
         */
        updateTargets(selector, data) {
            const targets = document.querySelectorAll(selector);
            targets.forEach(target => {
                const key = target.getAttribute(`${this.prefix}-model`);
                if (key && data.hasOwnProperty(key)) {
                    this.updateElement(target, data[key]);
                }
            });
        }

        /**
         * Auto-update all matching targets
         * @param {Object} data - Data object
         */
        autoUpdateTargets(data) {
            Object.entries(data).forEach(([key, value]) => {
                const selector = `[${this.prefix}-model="${key}"]`;
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => this.updateElement(element, value));
            });
        }

        /**
         * Update a single element
         * @param {HTMLElement} element - The element
         * @param {*} value - The new value
         */
        updateElement(element, value) {
            element.classList.add(this.config.updatingClass);

            if (element.hasAttribute(`${this.prefix}-unsafe-html`)) {
                element.innerHTML = value;
            } else if (element.hasAttribute(`${this.prefix}-class`)) {
                element.className = value;
            } else if (['INPUT', 'TEXTAREA'].includes(element.tagName)) {
                element.value = value;
            } else if (element.tagName === 'SELECT') {
                element.value = value;
            } else {
                element.textContent = value;
            }

            setTimeout(() => {
                element.classList.remove(this.config.updatingClass);
            }, 300);
        }

        /**
         * Set loading state on element
         * @param {HTMLElement} element - The element
         * @param {boolean} isLoading - Loading state
         * @param {string|null} loadingText - Text to show while loading
         */
        setLoading(element, isLoading, loadingText = null) {
            // Don't disable inputs - causes focus loss
            const skipDisable = ['INPUT', 'TEXTAREA'].includes(element.tagName);

            if (isLoading) {
                this.loadingElements.add(element);
                if (!skipDisable) element.disabled = true;
                element.classList.add(this.config.loadingClass);

                if (loadingText) {
                    element.dataset.originalText = element.textContent;
                    element.textContent = loadingText;
                }
            } else {
                this.loadingElements.delete(element);
                if (!skipDisable) element.disabled = false;
                element.classList.remove(this.config.loadingClass);

                if (element.dataset.originalText) {
                    element.textContent = element.dataset.originalText;
                    delete element.dataset.originalText;
                }
            }
        }

        /**
         * Handle errors
         * @param {Error} error - The error
         * @param {HTMLElement} element - The triggering element
         */
        handleError(error, element) {
            this.log('Error:', error.message);
            this.dispatch('spa:error', { error, element });

            // Default error handling - can be overridden via event listener
            console.error('[CakeSPA Error]', error.message);
        }

        /**
         * Dispatch custom event
         * @param {string} name - Event name
         * @param {Object} detail - Event detail
         */
        dispatch(name, detail) {
            const event = new CustomEvent(name, { detail, bubbles: true });
            document.dispatchEvent(event);
        }

        /**
         * Static method: Call an action manually
         * @param {string} action - Action path
         * @param {Object} params - Parameters
         * @returns {Promise<Object>} Response data
         */
        static async call(action, params = {}) {
            const data = await global.cakeSpa.request(action, params);
            global.cakeSpa.autoUpdateTargets(data);
            return data;
        }

        /**
         * Static method: Navigate to URL
         * @param {string} url - The URL
         */
        static navigate(url) {
            global.cakeSpa.navigate(url);
        }

        /**
         * Static method: Update a specific element
         * @param {string} selector - Element selector
         * @param {*} value - New value
         */
        static update(selector, value) {
            const element = document.querySelector(selector);
            if (element) {
                global.cakeSpa.updateElement(element, value);
            }
        }
    }

    // Auto-initialize with default config
    global.CakeSPA = CakeSPA;
    global.cakeSpa = new CakeSPA();

    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = CakeSPA;
    }

})(typeof window !== 'undefined' ? window : this);
