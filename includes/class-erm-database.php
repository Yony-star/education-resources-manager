<?php
/**
 * Custom tracking table access.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Database
 */
class ERM_Database {

	/**
	 * Tracking table name including prefix.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Allowed action types.
	 *
	 * @var string[]
	 */
	private $action_types = array( 'view', 'download', 'complete' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'erm_tracking';
	}

	/**
	 * Insert a tracking record.
	 *
	 * @param int    $resource_id Resource post ID.
	 * @param string $action_type Action type (view, download, complete).
	 * @return int|false Insert ID or false on failure.
	 */
	public function insert_tracking( $resource_id, $action_type = 'view' ) {
		global $wpdb;

		$resource_id = absint( $resource_id );
		$action_type = sanitize_text_field( $action_type );

		if ( $resource_id < 1 || ! in_array( $action_type, $this->action_types, true ) ) {
			return false;
		}

		$post = get_post( $resource_id );
		if ( ! $post || 'education_resource' !== $post->post_type ) {
			return false;
		}

		$user_id = get_current_user_id();
		$data    = array(
			'resource_id' => $resource_id,
			'user_id'     => $user_id ? $user_id : null,
			'action_type' => $action_type,
			'ip_address'  => $this->get_client_ip(),
			'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] )
				? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
				: '',
		);
		$format  = array( '%d', '%d', '%s', '%s', '%s' );

		$result = $wpdb->insert( $this->table_name, $data, $format );

		if ( false !== $result ) {
			$this->invalidate_cache();
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get view count for a resource.
	 *
	 * @param int $resource_id Resource post ID.
	 * @return int
	 */
	public function get_resource_views( $resource_id ) {
		return $this->get_resource_tracking_count( $resource_id, 'view' );
	}

	/**
	 * Get top resources by action count.
	 *
	 * @param int    $limit       Number of results.
	 * @param string $action_type Action type filter.
	 * @return array
	 */
	public function get_top_resources( $limit = 5, $action_type = 'view' ) {
		global $wpdb;

		$limit       = absint( $limit );
		$action_type = sanitize_text_field( $action_type );

		if ( ! in_array( $action_type, $this->action_types, true ) ) {
			$action_type = 'view';
		}

		$cache_key = 'erm_top_resources_' . $action_type . '_' . $limit;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					t.resource_id,
					p.post_title,
					COUNT(t.id) as action_count
				FROM {$this->table_name} t
				INNER JOIN {$wpdb->posts} p ON t.resource_id = p.ID
				WHERE
					t.action_type = %s
					AND p.post_type = %s
					AND p.post_status = %s
				GROUP BY t.resource_id, p.post_title
				ORDER BY action_count DESC
				LIMIT %d",
				$action_type,
				'education_resource',
				'publish',
				$limit
			)
		);

		set_transient( $cache_key, $results, HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Get monthly resource creation stats.
	 *
	 * @param int $months Number of months to include.
	 * @return array
	 */
	public function get_monthly_stats( $months = 6 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					DATE_FORMAT(post_date, '%%Y-%%m') as month,
					COUNT(*) as total
				FROM {$wpdb->posts}
				WHERE
					post_type = %s
					AND post_status = %s
					AND post_date >= DATE_SUB(NOW(), INTERVAL %d MONTH)
				GROUP BY month
				ORDER BY month ASC",
				'education_resource',
				'publish',
				absint( $months )
			)
		);
	}

	/**
	 * Get aggregated statistics summary.
	 *
	 * @param string $period Period to filter tracking totals: 'all', 'month', or 'week'.
	 * @return array {
	 *     @type int   $total_resources Published resource count.
	 *     @type array $by_type         Counts keyed by resource type slug.
	 *     @type array $by_difficulty   Counts keyed by difficulty slug.
	 *     @type int   $total_views     View events in the period.
	 *     @type int   $total_downloads Download events in the period.
	 *     @type int   $unique_users    Distinct users with tracking in the period.
	 *     @type string $period         Period used for tracking totals.
	 * }
	 */
	public function get_stats_summary( $period = 'all' ) {
		$allowed_periods = array( 'all', 'month', 'week' );
		$period          = in_array( $period, $allowed_periods, true ) ? $period : 'all';

		$cache_key = 'erm_stats_summary_' . $period;
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$date_where = '';
		switch ( $period ) {
			case 'week':
				$date_where = $wpdb->prepare(
					' AND action_date >= %s',
					gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
				);
				break;
			case 'month':
				$date_where = $wpdb->prepare(
					' AND action_date >= %s',
					gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
				);
				break;
			case 'all':
			default:
				$date_where = '';
				break;
		}

		$count_posts       = wp_count_posts( 'education_resource' );
		$total_resources   = isset( $count_posts->publish ) ? (int) $count_posts->publish : 0;

		$by_type_raw = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS type, COUNT(*) AS total
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND p.post_status = %s
				AND pm.meta_key = %s
				GROUP BY pm.meta_value",
				'education_resource',
				'publish',
				'_erm_resource_type'
			)
		);

		$by_type = array();
		foreach ( $by_type_raw as $row ) {
			$by_type[ $row->type ] = (int) $row->total;
		}
		foreach ( array( 'course', 'tutorial', 'ebook', 'video' ) as $type ) {
			if ( ! isset( $by_type[ $type ] ) ) {
				$by_type[ $type ] = 0;
			}
		}

		$by_difficulty_raw = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value AS difficulty, COUNT(*) AS total
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND p.post_status = %s
				AND pm.meta_key = %s
				GROUP BY pm.meta_value",
				'education_resource',
				'publish',
				'_erm_difficulty_level'
			)
		);

		$by_difficulty = array();
		foreach ( $by_difficulty_raw as $row ) {
			$by_difficulty[ $row->difficulty ] = (int) $row->total;
		}
		foreach ( array( 'beginner', 'intermediate', 'advanced' ) as $level ) {
			if ( ! isset( $by_difficulty[ $level ] ) ) {
				$by_difficulty[ $level ] = 0;
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_where is prepared when non-empty.
		$totals = $wpdb->get_row(
			"SELECT
				SUM(CASE WHEN action_type = 'view' THEN 1 ELSE 0 END) AS total_views,
				SUM(CASE WHEN action_type = 'download' THEN 1 ELSE 0 END) AS total_downloads,
				COUNT(DISTINCT user_id) AS unique_users
			FROM {$this->table_name}
			WHERE 1=1 {$date_where}"
		);

		$stats = array(
			'total_resources' => $total_resources,
			'by_type'         => $by_type,
			'by_difficulty'   => $by_difficulty,
			'total_views'     => $totals ? (int) ( $totals->total_views ?? 0 ) : 0,
			'total_downloads' => $totals ? (int) ( $totals->total_downloads ?? 0 ) : 0,
			'unique_users'    => $totals ? (int) ( $totals->unique_users ?? 0 ) : 0,
			'period'          => $period,
		);

		set_transient( $cache_key, $stats, HOUR_IN_SECONDS );

		return $stats;
	}

	/**
	 * Get tracking count for a resource.
	 *
	 * @param int         $resource_id Resource post ID.
	 * @param string|null $action_type Optional action type filter.
	 * @return int
	 */
	public function get_resource_tracking_count( $resource_id, $action_type = null ) {
		global $wpdb;

		$resource_id = absint( $resource_id );

		if ( $action_type ) {
			$action_type = sanitize_text_field( $action_type );
			if ( ! in_array( $action_type, $this->action_types, true ) ) {
				return 0;
			}

			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table_name} WHERE resource_id = %d AND action_type = %s",
					$resource_id,
					$action_type
				)
			);
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_name} WHERE resource_id = %d",
				$resource_id
			)
		);
	}

	/**
	 * Invalidate cached statistics.
	 */
	public function invalidate_cache() {
		delete_transient( 'erm_stats_summary_all' );
		delete_transient( 'erm_stats_summary_month' );
		delete_transient( 'erm_stats_summary_week' );
		delete_transient( 'erm_stats_summary' );

		foreach ( array( 'view', 'download', 'complete' ) as $action_type ) {
			foreach ( array( 5, 10, 20 ) as $limit ) {
				delete_transient( 'erm_top_resources_' . $action_type . '_' . $limit );
			}
		}

		delete_transient( 'erm_top_resources' );
	}

	/**
	 * Detect and validate client IP address.
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$headers = array(
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );

			if ( 'HTTP_X_FORWARDED_FOR' === $header && strpos( $raw, ',' ) !== false ) {
				$parts = explode( ',', $raw );
				$raw   = trim( $parts[0] );
			}

			if ( filter_var( $raw, FILTER_VALIDATE_IP ) ) {
				return $raw;
			}
		}

		return '';
	}
}
