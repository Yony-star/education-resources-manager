<?php
/**
 * Admin area functionality.
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
		add_menu_page(
			__( 'Recursos Educativos', 'education-resources-manager' ),
			__( 'Recursos Edu.', 'education-resources-manager' ),
			'manage_options',
			'erm-resources',
			array( $this, 'render_main_page' ),
			'dashicons-welcome-learn-more',
			25
		);

		add_submenu_page(
			'erm-resources',
			__( 'Todos los Recursos', 'education-resources-manager' ),
			__( 'Todos los Recursos', 'education-resources-manager' ),
			'manage_options',
			'erm-resources',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			'erm-resources',
			__( 'Estadísticas', 'education-resources-manager' ),
			__( 'Estadísticas', 'education-resources-manager' ),
			'manage_options',
			'erm-stats',
			array( $this, 'render_stats_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_scripts( $hook ) {
		$plugin_pages = array( 'toplevel_page_erm-resources', 'erm-resources_page_erm-stats' );

		if ( ! in_array( $hook, $plugin_pages, true ) ) {
			return;
		}

		wp_enqueue_style(
			'erm-admin',
			ERM_PLUGIN_URL . 'admin/css/erm-admin.css',
			array(),
			ERM_VERSION
		);

		wp_enqueue_script(
			'erm-admin',
			ERM_PLUGIN_URL . 'admin/js/erm-admin.js',
			array( 'jquery' ),
			ERM_VERSION,
			true
		);

		wp_localize_script(
			'erm-admin',
			'ermAdmin',
			array(
				'apiUrl'     => esc_url_raw( rest_url( 'erm/v1' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'adminNonce' => wp_create_nonce( 'erm_admin_nonce' ),
			)
		);
	}

	/**
	 * Render the main resources list page.
	 */
	public function render_main_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'No tienes permisos para acceder a esta página.', 'education-resources-manager' ),
				esc_html__( 'Acceso denegado', 'education-resources-manager' ),
				array( 'response' => 403 )
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list filters use GET.
		$filter_type       = isset( $_GET['erm_type'] ) ? sanitize_text_field( wp_unslash( $_GET['erm_type'] ) ) : '';
		$filter_difficulty = isset( $_GET['erm_difficulty'] ) ? sanitize_text_field( wp_unslash( $_GET['erm_difficulty'] ) ) : '';
		$filter_category   = isset( $_GET['erm_category'] ) ? sanitize_text_field( wp_unslash( $_GET['erm_category'] ) ) : '';
		$search            = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged             = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;

		$filter_type       = $this->sanitize_type_filter( $filter_type );
		$filter_difficulty = $this->sanitize_difficulty_filter( $filter_difficulty );

		$query_args = array(
			'post_type'      => 'education_resource',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 20,
			'paged'          => max( 1, $paged ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $search ) {
			$query_args['s'] = $search;
		}

		$meta_query = array();

		if ( $filter_type ) {
			$meta_query[] = array(
				'key'     => '_erm_resource_type',
				'value'   => $filter_type,
				'compare' => '=',
			);
		}

		if ( $filter_difficulty ) {
			$meta_query[] = array(
				'key'     => '_erm_difficulty_level',
				'value'   => $filter_difficulty,
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		if ( $filter_category ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'resource_category',
					'field'    => 'slug',
					'terms'    => $filter_category,
				),
			);
		}

		$query         = new WP_Query( $query_args );
		$db            = new ERM_Database();
		$stats_summary = $db->get_stats_summary();

		include ERM_PLUGIN_DIR . 'admin/views/admin-page-main.php';
	}

	/**
	 * Render the statistics page.
	 */
	public function render_stats_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'No tienes permisos para acceder a esta página.', 'education-resources-manager' ),
				esc_html__( 'Acceso denegado', 'education-resources-manager' ),
				array( 'response' => 403 )
			);
		}

		$db      = new ERM_Database();
		$stats   = $db->get_stats_summary();
		$top_5   = $db->get_top_resources( 5 );
		$monthly = $db->get_monthly_stats( 6 );

		include ERM_PLUGIN_DIR . 'admin/views/admin-page-stats.php';
	}

	/**
	 * Get human-readable resource type label.
	 *
	 * @param string $type Resource type slug.
	 * @return string
	 */
	public function get_type_label( $type ) {
		$labels = array(
			'course'   => __( 'Curso', 'education-resources-manager' ),
			'tutorial' => __( 'Tutorial', 'education-resources-manager' ),
			'ebook'    => __( 'Ebook', 'education-resources-manager' ),
			'video'    => __( 'Video', 'education-resources-manager' ),
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : '—';
	}

	/**
	 * Get human-readable difficulty label.
	 *
	 * @param string $level Difficulty slug.
	 * @return string
	 */
	public function get_difficulty_label( $level ) {
		$labels = array(
			'beginner'     => __( 'Principiante', 'education-resources-manager' ),
			'intermediate' => __( 'Intermedio', 'education-resources-manager' ),
			'advanced'     => __( 'Avanzado', 'education-resources-manager' ),
		);

		return isset( $labels[ $level ] ) ? $labels[ $level ] : '—';
	}

	/**
	 * Sanitize type filter value.
	 *
	 * @param string $type Raw type slug.
	 * @return string
	 */
	private function sanitize_type_filter( $type ) {
		$valid = array( 'course', 'tutorial', 'ebook', 'video' );
		return in_array( $type, $valid, true ) ? $type : '';
	}

	/**
	 * Sanitize difficulty filter value.
	 *
	 * @param string $level Raw difficulty slug.
	 * @return string
	 */
	private function sanitize_difficulty_filter( $level ) {
		$valid = array( 'beginner', 'intermediate', 'advanced' );
		return in_array( $level, $valid, true ) ? $level : '';
	}
}
