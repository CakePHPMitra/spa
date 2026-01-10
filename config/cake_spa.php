<?php
declare(strict_types=1);

/**
 * CakeSPA Plugin Configuration
 *
 * Copy this file to your application's config directory
 * and customize as needed.
 */
return [
    'CakeSPA' => [
        /**
         * Enable SPA mode globally.
         * When disabled, all requests are treated as regular page requests.
         */
        'enabled' => true,

        /**
         * HTTP header used to identify SPA navigation requests.
         * The JavaScript client sends this header for page navigation.
         */
        'navigationHeader' => 'X-Live-Nav',

        /**
         * HTTP header used to identify component/action requests.
         * These requests expect JSON responses.
         */
        'ajaxHeader' => 'X-Requested-With',

        /**
         * Value expected in the ajax header.
         */
        'ajaxHeaderValue' => 'XMLHttpRequest',

        /**
         * Default layout to use for full page requests.
         */
        'defaultLayout' => 'default',

        /**
         * CSS class applied to content container during loading.
         */
        'loadingClass' => 'spa-loading',

        /**
         * Default debounce time (ms) for input fields.
         */
        'debounceTime' => 500,

        /**
         * Enable History API for URL updates.
         */
        'historyEnabled' => true,

        /**
         * Attribute prefix for data attributes.
         */
        'attributePrefix' => 'data-spa',

        /**
         * CSRF configuration.
         */
        'csrf' => [
            /**
             * Meta tag name for CSRF token.
             */
            'metaName' => 'csrf-token',

            /**
             * Input field name for CSRF token.
             */
            'inputName' => '_csrfToken',

            /**
             * Header name for CSRF token in AJAX requests.
             */
            'headerName' => 'X-CSRF-Token',
        ],

        /**
         * JavaScript configuration.
         */
        'js' => [
            /**
             * Auto-initialize the SPA client.
             */
            'autoInit' => true,

            /**
             * Global variable name for the SPA instance.
             */
            'globalName' => 'cakeSpa',

            /**
             * Enable debug logging in console.
             */
            'debug' => false,
        ],

        /**
         * Helper configuration.
         */
        'helper' => [
            /**
             * Default CSS class for buttons.
             */
            'buttonClass' => 'btn',

            /**
             * Default loading text.
             */
            'loadingText' => 'Loading...',

            /**
             * Auto-include scripts when helper is used.
             */
            'autoIncludeScripts' => true,
        ],
    ],
];
