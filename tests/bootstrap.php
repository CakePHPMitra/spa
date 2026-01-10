<?php
declare(strict_types=1);

/**
 * CakeSPA Test Bootstrap
 */

use Cake\Core\Configure;

// Load Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Load CakePHP bootstrap
require dirname(__DIR__, 3) . '/config/bootstrap.php';

// Load plugin configuration
Configure::write('CakeSPA', [
    'enabled' => true,
    'navigationHeader' => 'X-Live-Nav',
    'ajaxHeader' => 'X-Requested-With',
    'ajaxHeaderValue' => 'XMLHttpRequest',
]);
