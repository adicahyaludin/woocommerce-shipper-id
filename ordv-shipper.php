<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Ordv_Shipper
 *
 * @wordpress-plugin
 * Plugin Name:       OrangerDev - Shipper.id
 * Plugin URI:        https://ridwan-arifandi.com
 * Description:       Shipper.id integration with WooCommerce.
 * Version:           1.0.0
 * Author:            Ridwan Arifandi
 * Author URI:        https://ridwan-arifandi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ordv-shipper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ORDV_SHIPPER_VERSION', '1.0.0' );
define( 'API_URL', 'https://merchant-api-sandbox.shipper.id' );
define( 'COUNTRY_ID_NUM', '228' );

define( 'ORDV_SHIPPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ORDV_SHIPPER_URI', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ordv-shipper-activator.php
 */
function activate_ordv_shipper() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ordv-shipper-activator.php';
	Ordv_Shipper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ordv-shipper-deactivator.php
 */
function deactivate_ordv_shipper() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ordv-shipper-deactivator.php';
	Ordv_Shipper_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ordv_shipper' );
register_deactivation_hook( __FILE__, 'deactivate_ordv_shipper' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ordv-shipper.php';

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ordv_shipper() {

	$plugin = new Ordv_Shipper();
	$plugin->run();

}

if(!function_exists('__debug')) :
function __debug()
{
	$bt     = debug_backtrace();
	$caller = array_shift($bt);
	$args   = [
		"file"  => $caller["file"],
		"line"  => $caller["line"],
		"args"  => func_get_args()
	];

	do_action('qm/info', $args);
}
endif;

if(!function_exists('__print_debug')) :
function __print_debug()
{
	$bt     = debug_backtrace();
	$caller = array_shift($bt);
	$args   = [
		"file"  => $caller["file"],
		"line"  => $caller["line"],
		"args"  => func_get_args()
	];

	?><pre><?php print_r($args); ?></pre><?php
}
endif;

run_ordv_shipper();
