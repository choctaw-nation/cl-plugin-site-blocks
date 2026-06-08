<?php
/**
 * Plugin Name: [Choctaw Landing] Site Blocks
 * Plugin URI: https://github.com/choctaw-nation/cl-plugin-site-blocks
 * Description: Blocks for the Choctaw Landing site.
 * Version: 2.0.1
 * Author: Choctaw Nation of Oklahoma
 * Author URI: https://www.choctawnation.com
 * Text Domain: cno
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.8.0
 * Tested up to: 7.0.0
 * Requires Plugins: advanced-custom-fields-pro, cno-plugin-events
 *
 * @package ChoctawNation
 * @subpackage CL_SiteBlocks
 */

use ChoctawNation\CL_SiteBlocks\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$cno_autoload_path = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $cno_autoload_path ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>[Choctaw Landing] Site Blocks plugin is missing required dependencies. Please run Composer install or deploy the plugin with its vendor directory included.</p></div>';
		}
	);

	return;
}

require_once $cno_autoload_path;
$cno_plugin = new Plugin_Loader( __DIR__, plugin_dir_url( __FILE__ ) );

// Plugin Lifecycle Hooks
register_activation_hook( __FILE__, array( $cno_plugin, 'activate' ) );

// Static method for uninstall since the plugin can't rely on instance methods.
register_uninstall_hook( __FILE__, array( 'ChoctawNation\CL_SiteBlocks\Plugin_Loader', 'uninstall' ) );

// Load the Plugin
add_action( 'plugins_loaded', array( $cno_plugin, 'load_plugin' ) );