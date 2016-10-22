<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/kenross
 * @since             1.0.0
 * @package           Liked_User_Content
 *
 * @wordpress-plugin
 * Plugin Name:       Liked User Content
 * Plugin URI:        https://github.com/kenross/liked-user-content
 * Description:       LUC enables logged-in WordPress users to save image attachments to their own personal gallery.
 * Version:           1.0.0
 * Author:            Kenneth Ross
 * Author URI:        https://github.com/kenross
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       liked-user-content
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-liked-user-content-activator.php
 */
function activate_liked_user_content() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-liked-user-content-activator.php';
	Liked_User_Content_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-liked-user-content-deactivator.php
 */
function deactivate_liked_user_content() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-liked-user-content-deactivator.php';
	Liked_User_Content_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_liked_user_content' );
register_deactivation_hook( __FILE__, 'deactivate_liked_user_content' );

/**
 * The core plugin class that is used to define admin-specific hooks and
 * public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-liked-user-content.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_liked_user_content() {

	$plugin = new Liked_User_Content();
	$plugin->run();

}
run_liked_user_content();