<?php
/**
 * Plugin Name:       Education Resources Manager
 * Plugin URI:        https://example.com/erm
 * Description:       Sistema de gestión de recursos educativos para WordPress
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            [Nombre del candidato]
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       education-resources-manager
 * Domain Path:       /languages
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ERM_VERSION', '1.0.0' );
define( 'ERM_DB_VERSION', '1.0.0' );
define( 'ERM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ERM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ERM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin dependencies.
 */
require_once ERM_PLUGIN_DIR . 'includes/class-erm-activator.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-deactivator.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-loader.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-post-type.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-taxonomy.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-database.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-admin.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-shortcode.php';
require_once ERM_PLUGIN_DIR . 'includes/class-erm-rest-api.php';

register_activation_hook( __FILE__, array( 'ERM_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ERM_Deactivator', 'deactivate' ) );

/**
 * Register hooks and run the plugin.
 */
function run_erm() {
	$loader = new ERM_Loader();

	$post_type = new ERM_Post_Type();
	$loader->add_action( 'init', $post_type, 'register' );
	$loader->add_action( 'add_meta_boxes', $post_type, 'add_meta_boxes' );
	$loader->add_action( 'save_post_education_resource', $post_type, 'save_meta' );
	$loader->add_action( 'save_post_education_resource', $post_type, 'sync_publication_meta', 20 );
	$loader->add_filter( 'display_post_states', $post_type, 'display_post_states', 10, 2 );
	$loader->add_filter( 'manage_education_resource_posts_columns', $post_type, 'add_custom_columns' );
	$loader->add_action( 'manage_education_resource_posts_custom_column', $post_type, 'render_custom_columns', 10, 2 );

	$taxonomy = new ERM_Taxonomy();
	$loader->add_action( 'init', $taxonomy, 'register' );

	new ERM_Database();

	$admin = new ERM_Admin();
	$loader->add_action( 'admin_menu', $admin, 'add_admin_menu' );
	$loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

	$shortcode = new ERM_Shortcode();
	add_shortcode( 'recursos_educativos', array( $shortcode, 'render' ) );

	$rest_api = new ERM_REST_API();
	$loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );

	$loader->run();
}

add_action( 'plugins_loaded', 'run_erm' );
