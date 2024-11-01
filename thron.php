<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.thron.com
 * @since             1.0.0
 * @package           Thron
 *
 * @wordpress-plugin
 * Plugin Name:       THRON
 * Plugin URI:
 * Description:       Select the assets to insert within your pages directly from the DAM library
 * Version:           1.3.3
 * Author:            THRON
 * Author URI:        https://www.thron.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       thron
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
define( 'THRON_VERSION', '1.3.2' );
define( 'THRON_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'THRON_PLUGIN_PATH', plugin_dir_path(__FILE__) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-thron-activator.php
 */
function activate_thron() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-thron-activator.php';
	Thron_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-thron-deactivator.php
 */
function deactivate_thron() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-thron-deactivator.php';
	Thron_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_thron' );
register_deactivation_hook( __FILE__, 'deactivate_thron' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-thron.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_thron() {

	$plugin = new Thron();
	$plugin->run();

}
run_thron();
