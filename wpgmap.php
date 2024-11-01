<?php
/*
Plugin Name: WPGMAP
Plugin URI: http://habibhadi.com/wpgmap
description: Wordpress Google Map plugin
Version: 1.0.1
Author: Habib Hadi
Author URI: http://habibhadi.com
License: GPLv3 or later
*/

defined('ABSPATH') or die('Direct access not allowed!');

define('WPGMAP', true);
define('WPGMAP_VERSION', '1.0.1');
define('WPGMAP_DIR', __DIR__);
define('WPGMAP_URL', plugin_dir_url( __FILE__ ));
define('WPGMAP_SLUG', 'wp-gmap');
define('WPGMAP_PREFIX', 'wp_gmap');
define('WPGMAP_NAME', 'WPGMAP');

require_once WPGMAP_DIR . '/vendor/autoload.php';

use WpGmap\install\Installer;
use WpGmap\core\Plugin;

$installer = new Installer();

register_activation_hook(__FILE__, [$installer, 'install']);

$plugin = new Plugin();
$plugin->run();