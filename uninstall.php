<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

delete_option( 'erm_version' );
delete_option( 'erm_db_version' );

$table_name = $wpdb->prefix . 'erm_tracking';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is built from $wpdb->prefix.
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

delete_transient( 'erm_top_resources' );
delete_transient( 'erm_stats_summary' );

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_erm_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_erm_' ) . '%'
	)
);
