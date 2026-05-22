<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Deactivator
 */
class ERM_Deactivator {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		delete_transient( 'erm_top_resources' );
		delete_transient( 'erm_stats_summary' );
	}
}
