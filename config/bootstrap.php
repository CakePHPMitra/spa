<?php
declare(strict_types=1);

/**
 * CakeSPA Plugin Bootstrap
 *
 * This file is loaded when the plugin is bootstrapped.
 */

use Cake\Core\Configure;

// Load default configuration if not already set
if (!Configure::check('CakeSPA')) {
    Configure::load('CakeSPA.cake_spa');
}
