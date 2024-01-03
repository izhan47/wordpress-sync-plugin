<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://#
 * @since             1.0.0
 * @package           Wp_Blog_Exporter
 *
 * @wordpress-plugin
 * Plugin Name:       WP Blog Exporter
 * Plugin URI:        https://#
 * Description:       Plugin to connect multiple wordpress sites through API and import/export blog posts.
 * Version:           1.0.0
 * Author:            Izhan
 * Author URI:        https://#
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-blog-exporter
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
define( 'WP_BLOG_EXPORTER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-blog-exporter-activator.php
 */
function activate_wp_blog_exporter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-blog-exporter-activator.php';
	Wp_Blog_Exporter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-blog-exporter-deactivator.php
 */
function deactivate_wp_blog_exporter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-blog-exporter-deactivator.php';
	Wp_Blog_Exporter_Deactivator::deactivate();

}
function create_wpbe_database_table() {

	global $table_prefix, $wpdb;
    $table = 'wpbe_api_log';
	$table_name = $table_prefix . "$table ";

	if($wpdb->get_var( "show tables like '$table_name'" ) != $table_name) 
    {
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(200) DEFAULT '' NOT NULL,
			status tinytext NOT NULL,
			description tinytext NOT NULL,
			PRIMARY KEY  (id)
		  ) $charset_collate;";
		  
		  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		  dbDelta( $sql );
    }

}
function delete_wpbe_database_table() {
	global $table_prefix, $wpdb;
    $table = 'wpbe_api_log';
	$table_name = $table_prefix . "$table ";
	$sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    delete_option("WP_BLOG_EXPORTER_VERSION");
}
register_activation_hook( __FILE__, 'activate_wp_blog_exporter' );
register_activation_hook( __FILE__, 'create_wpbe_database_table' );
register_deactivation_hook( __FILE__, 'deactivate_wp_blog_exporter' );
register_deactivation_hook( __FILE__, 'delete_wpbe_database_table' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-blog-exporter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_blog_exporter() {

	$plugin = new Wp_Blog_Exporter();
	$plugin->run();

}
run_wp_blog_exporter();
