<?php
/**
 * Admin area functionality (stub — Agent 06).
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Admin
 */
class ERM_Admin {

	/**
	 * Register admin menu pages.
	 */
	public function add_admin_menu() {
		// Implemented in Agent 06.
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_scripts( $hook ) {
		// Implemented in Agent 06.
		unset( $hook );
	}
}
