<?php
/**
 * REST API routes.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_REST_API
 */
class ERM_REST_API {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	private $namespace = 'erm/v1';

	/**
	 * Valid resource types.
	 *
	 * @var string[]
	 */
	private $valid_types = array( 'course', 'tutorial', 'ebook', 'video' );

	/**
	 * Valid difficulty levels.
	 *
	 * @var string[]
	 */
	private $valid_difficulties = array( 'beginner', 'intermediate', 'advanced' );

	/**
	 * Valid tracking action types.
	 *
	 * @var string[]
	 */
	private $valid_actions = array( 'view', 'download', 'complete' );

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/resources',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_resources' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_resources_args(),
			)
		);

		register_rest_route(
			$this->namespace,
			'/resources/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_resource' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this, 'validate_positive_int' ),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/resources/(?P<id>\d+)/track',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'track_resource' ),
				'permission_callback' => array( $this, 'track_permission_check' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'action_type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( $this, 'validate_action_type' ),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'admin_permission_check' ),
				'args'                => array(
					'period' => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => 'all',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( $this, 'validate_stats_period' ),
					),
				),
			)
		);
	}

	/**
	 * Query args for the resources list endpoint.
	 *
	 * @return array
	 */
	private function get_resources_args() {
		return array(
			'page'       => array(
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page'   => array(
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			),
			'search'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'type'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => array( $this, 'validate_resource_type' ),
			),
			'difficulty' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => array( $this, 'validate_difficulty' ),
			),
			'category'   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'skill'      => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'orderby'    => array(
				'type'              => 'string',
				'default'           => 'date',
				'validate_callback' => array( $this, 'validate_orderby' ),
			),
			'order'      => array(
				'type'              => 'string',
				'default'           => 'DESC',
				'validate_callback' => array( $this, 'validate_order' ),
			),
		);
	}

	/**
	 * List published resources.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_resources( WP_REST_Request $request ) {
		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$orderby  = $request->get_param( 'orderby' );

		$query_args = array(
			'post_type'      => 'education_resource',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'views' !== $orderby ? $orderby : 'date',
			'order'          => strtoupper( $request->get_param( 'order' ) ),
		);

		$search = $request->get_param( 'search' );
		if ( $search ) {
			$query_args['s'] = $search;
		}

		$meta_query = array();
		$type       = $request->get_param( 'type' );
		if ( $type ) {
			$meta_query[] = array(
				'key'     => '_erm_resource_type',
				'value'   => $type,
				'compare' => '=',
			);
		}

		$difficulty = $request->get_param( 'difficulty' );
		if ( $difficulty ) {
			$meta_query[] = array(
				'key'     => '_erm_difficulty_level',
				'value'   => $difficulty,
				'compare' => '=',
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$tax_query = array();
		$category  = $request->get_param( 'category' );
		if ( $category ) {
			$tax_query[] = array(
				'taxonomy' => 'resource_category',
				'field'    => 'slug',
				'terms'    => $category,
			);
		}

		$skill = $request->get_param( 'skill' );
		if ( $skill ) {
			$tax_query[] = array(
				'taxonomy' => 'skill_tag',
				'field'    => 'slug',
				'terms'    => $skill,
			);
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$query     = new WP_Query( $query_args );
		$resources = array();
		$db        = new ERM_Database();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$resources[] = $this->format_resource( $post, $db, false );
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'resources'  => $resources,
					'pagination' => array(
						'total'        => (int) $query->found_posts,
						'total_pages'  => (int) $query->max_num_pages,
						'current_page' => (int) $page,
						'per_page'     => (int) $per_page,
						'has_more'     => $page < $query->max_num_pages,
					),
				),
			)
		);
	}

	/**
	 * Get a single resource.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_resource( WP_REST_Request $request ) {
		$id   = $request->get_param( 'id' );
		$post = get_post( $id );

		if ( ! $post || 'education_resource' !== $post->post_type || 'publish' !== $post->post_status ) {
			return new WP_Error(
				'resource_not_found',
				__( 'El recurso solicitado no existe.', 'education-resources-manager' ),
				array( 'status' => 404 )
			);
		}

		$db = new ERM_Database();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $this->format_resource( $post, $db, true ),
			)
		);
	}

	/**
	 * Track a resource action.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function track_resource( WP_REST_Request $request ) {
		$resource_id = $request->get_param( 'id' );
		$action_type = $request->get_param( 'action_type' );
		$post        = get_post( $resource_id );

		if ( ! $post || 'education_resource' !== $post->post_type ) {
			return new WP_Error(
				'resource_not_found',
				__( 'Recurso no encontrado.', 'education-resources-manager' ),
				array( 'status' => 404 )
			);
		}

		$db          = new ERM_Database();
		$tracking_id = $db->insert_tracking( $resource_id, $action_type );

		if ( false === $tracking_id ) {
			return new WP_Error(
				'tracking_failed',
				__( 'No se pudo registrar la acción.', 'education-resources-manager' ),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'tracking_id' => $tracking_id,
					'resource_id' => $resource_id,
					'action_type' => $action_type,
					'timestamp'   => current_time( 'mysql' ),
				),
				'message' => __( 'Acción registrada exitosamente.', 'education-resources-manager' ),
			),
			201
		);
	}

	/**
	 * Get aggregated statistics (admin only).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_stats( WP_REST_Request $request ) {
		$db      = new ERM_Database();
		$summary = $db->get_stats_summary();
		$top     = $db->get_top_resources( 5 );
		$monthly = $db->get_monthly_stats( 6 );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => array(
					'period'         => $request->get_param( 'period' ),
					'summary'        => $summary,
					'top_resources'  => $top,
					'monthly_growth' => $monthly,
				),
			)
		);
	}

	/**
	 * Permission check for tracking endpoint.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function track_permission_check( WP_REST_Request $request ) {
		return true;
	}

	/**
	 * Permission check for stats endpoint.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error
	 */
	public function admin_permission_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'forbidden',
				__( 'No tienes permisos para ver las estadísticas.', 'education-resources-manager' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Validate positive integer parameter.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_positive_int( $value ) {
		return absint( $value ) > 0;
	}

	/**
	 * Validate resource type filter.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_resource_type( $value ) {
		return empty( $value ) || in_array( $value, $this->valid_types, true );
	}

	/**
	 * Validate difficulty filter.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_difficulty( $value ) {
		return empty( $value ) || in_array( $value, $this->valid_difficulties, true );
	}

	/**
	 * Validate orderby parameter.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_orderby( $value ) {
		return in_array( $value, array( 'date', 'title', 'views' ), true );
	}

	/**
	 * Validate order direction.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_order( $value ) {
		return in_array( strtoupper( $value ), array( 'ASC', 'DESC' ), true );
	}

	/**
	 * Validate tracking action type.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_action_type( $value ) {
		return in_array( $value, $this->valid_actions, true );
	}

	/**
	 * Validate stats period parameter.
	 *
	 * @param mixed $value Parameter value.
	 * @return bool
	 */
	public function validate_stats_period( $value ) {
		return in_array( $value, array( 'all', 'month', 'week' ), true );
	}

	/**
	 * Format a post as API resource payload.
	 *
	 * @param WP_Post      $post Post object.
	 * @param ERM_Database $db   Database instance.
	 * @param bool         $full Include full content fields.
	 * @return array
	 */
	private function format_resource( $post, ERM_Database $db, $full = false ) {
		$categories = wp_get_post_terms( $post->ID, 'resource_category', array( 'fields' => 'all' ) );
		$skills     = wp_get_post_terms( $post->ID, 'skill_tag', array( 'fields' => 'all' ) );

		if ( is_wp_error( $categories ) ) {
			$categories = array();
		}

		if ( is_wp_error( $skills ) ) {
			$skills = array();
		}

		$resource = array(
			'id'               => $post->ID,
			'title'            => get_the_title( $post ),
			'excerpt'          => get_the_excerpt( $post ),
			'type'             => get_post_meta( $post->ID, '_erm_resource_type', true ),
			'difficulty'       => get_post_meta( $post->ID, '_erm_difficulty_level', true ),
			'duration_minutes' => (int) get_post_meta( $post->ID, '_erm_duration_minutes', true ),
			'url'              => esc_url( get_post_meta( $post->ID, '_erm_resource_url', true ) ),
			'instructor'       => get_post_meta( $post->ID, '_erm_instructor', true ),
			'price'            => (float) get_post_meta( $post->ID, '_erm_price', true ),
			'categories'       => array_map( array( $this, 'format_category_term' ), $categories ),
			'skills'           => array_map( array( $this, 'format_skill_term' ), $skills ),
			'featured_image'   => get_the_post_thumbnail_url( $post, 'medium' ) ? get_the_post_thumbnail_url( $post, 'medium' ) : null,
			'views'            => $db->get_resource_views( $post->ID ),
			'permalink'        => get_permalink( $post ),
			'date_created'     => get_the_date( 'c', $post ),
		);

		if ( $full ) {
			$resource['content']       = wp_kses_post( apply_filters( 'the_content', $post->post_content ) );
			$resource['downloads']     = $db->get_resource_tracking_count( $post->ID, 'download' );
			$resource['date_modified'] = get_the_modified_date( 'c', $post );
		}

		return $resource;
	}

	/**
	 * Format category term for API response.
	 *
	 * @param WP_Term $term Term object.
	 * @return array
	 */
	private function format_category_term( $term ) {
		return array(
			'id'        => $term->term_id,
			'name'      => $term->name,
			'slug'      => $term->slug,
			'parent_id' => $term->parent,
		);
	}

	/**
	 * Format skill term for API response.
	 *
	 * @param WP_Term $term Term object.
	 * @return array
	 */
	private function format_skill_term( $term ) {
		return array(
			'id'   => $term->term_id,
			'name' => $term->name,
			'slug' => $term->slug,
		);
	}
}
