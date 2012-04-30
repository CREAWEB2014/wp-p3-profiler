<?php
/*
Plugin Name: P3 (Plugin Performance Profiler)
Plugin URI: http://support.godaddy.com/godaddy/wordpress-p3-plugin/
Description: See which plugins are slowing down your site.  Create a profile of your WordPress site's plugins' performance by measuring their impact on your site's load time.
Author: GoDaddy.com
Version: 1.3.0
Author URI: http://www.godaddy.com/
*/

// Make sure it's wordpress
if ( !defined( 'ABSPATH') )
	die( 'Forbidden' );

/**************************************************************************/
/**        PACKAGE CONSTANTS                                             **/
/**************************************************************************/

// Shortcut for knowing our path
define( 'P3_PATH',  realpath( dirname( __FILE__ ) ) );

// Directory for profiles
$uploads_dir = wp_upload_dir();
define( 'P3_PROFILES_PATH', $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles' );
unset( $uploads_dir );

// Plugin slug
define( 'P3_PLUGIN_SLUG', 'p3-profiler' );

/**************************************************************************/
/**        START PROFILING                                               **/
/**************************************************************************/

// Start profiling.  If it's already been started, this line won't do anything
// require_once P3_PATH . '/start-profile.php';

/**************************************************************************/
/**        PLUGIN HOOKS                                                  **/
/**************************************************************************/

/**
 * TODO
 * Make admin_notices more specific
 * Make action_init cleaner
 * Make use of die vs. wp_die in ajax calls conditional on wp version
 */

// Localization
load_plugin_textdomain( 'p3-profiler', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

// Admin hooks
if ( is_admin() ) {

	// Global plugin object
	require_once P3_PATH . '/classes/class.p3-profiler-plugin.php';

	// Localization
	load_plugin_textdomain( 'p3-profiler', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
	// Show the 'Profiler' option under the 'Plugins' menu
	add_action( 'admin_menu', array( 'P3_Profiler_Plugin', 'tools_menu' ) );

	if ( isset( $_REQUEST['page'] ) && P3_PLUGIN_SLUG == $_REQUEST['page'] ) {

		// Pre-processing of actions
		add_action( 'admin_init', array( 'P3_Profiler_Plugin', 'action_init' ) );

		// Show any notices
		add_action( 'admin_notices', array( 'P3_Profiler_Plugin', 'show_notices' ) );
	}
}

// Ajax actions
add_action( 'wp_ajax_p3_start_scan', array( 'P3_Profiler_Plugin', 'ajax_start_scan' ) );
add_action( 'wp_ajax_p3_stop_scan', array( 'P3_Profiler_Plugin', 'ajax_stop_scan' ) );
add_action( 'wp_ajax_p3_send_results', array( 'P3_Profiler_Plugin', 'ajax_send_results' ) );
add_action( 'wp_ajax_p3_save_settings', array( 'P3_Profiler_Plugin', 'ajax_save_settings' ) );

// Remove the admin bar when in profiling mode
if ( defined( 'WPP_PROFILING_STARTED' ) || isset( $_GET['P3_HIDE_ADMIN_BAR'] ) ) {
	add_action( 'plugins_loaded', array( 'P3_Profiler_Plugin', 'remove_admin_bar' ) );
}

// Install / uninstall hooks
register_activation_hook( P3_PATH . DIRECTORY_SEPARATOR . 'p3-profiler.php', array( 'P3_Profiler_Plugin', 'activate' ) );
register_deactivation_hook( P3_PATH . DIRECTORY_SEPARATOR . 'p3-profiler.php', array( 'P3_Profiler_Plugin', 'deactivate' ) );
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	add_action( 'wpmu_delete_blog', array( 'P3_Profiler_Plugin', 'delete_blog' ) );
}
