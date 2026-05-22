<?php
/**
 * Fired during plugin activation.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Activator
 */
class ERM_Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		self::check_requirements();
		self::create_tables();
		self::set_default_options();
		flush_rewrite_rules();
	}

	/**
	 * Verify PHP and WordPress versions.
	 */
	public static function check_requirements() {
		global $wp_version;

		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			wp_die(
				esc_html__( 'Education Resources Manager requiere PHP 7.4 o superior.', 'education-resources-manager' ),
				esc_html__( 'Error de activación', 'education-resources-manager' ),
				array( 'back_link' => true )
			);
		}

		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			wp_die(
				esc_html__( 'Education Resources Manager requiere WordPress 6.0 o superior.', 'education-resources-manager' ),
				esc_html__( 'Error de activación', 'education-resources-manager' ),
				array( 'back_link' => true )
			);
		}
	}

	/**
	 * Create custom database tables via dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . 'erm_tracking';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			resource_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			action_type varchar(20) NOT NULL DEFAULT 'view',
			action_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			ip_address varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY resource_id (resource_id),
			KEY user_id (user_id),
			KEY action_date (action_date),
			KEY action_type (action_type)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Store plugin version options.
	 */
	public static function set_default_options() {
		add_option( 'erm_version', ERM_VERSION );
		add_option( 'erm_db_version', ERM_DB_VERSION );
	}
}
