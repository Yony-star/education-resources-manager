<?php
/**
 * Tests para los endpoints REST del plugin ERM.
 *
 * Cubre los 4 endpoints:
 * - GET  /erm/v1/resources         (listado con filtros y paginación)
 * - GET  /erm/v1/resources/{id}    (recurso individual)
 * - POST /erm/v1/resources/{id}/track  (registro de tracking)
 * - GET  /erm/v1/stats             (estadísticas — solo admin)
 *
 * @package ERM\Tests
 */

require_once __DIR__ . '/helpers/trait-erm-test-factory.php';

/**
 * Class Test_ERM_REST_API
 *
 * @covers ERM_REST_API
 */
class Test_ERM_REST_API extends WP_Test_REST_TestCase {

	use ERM_Test_Factory;

	/** @var WP_REST_Server */
	protected $server;

	// =========================================================
	// SETUP / TEARDOWN
	// =========================================================

	public function set_up() : void {
		parent::set_up();

		// Arrancar el servidor REST
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		// Registrar CPT y taxonomías
		$post_type = new ERM_Post_Type();
		$post_type->register();

		$taxonomy = new ERM_Taxonomy();
		$taxonomy->register();

		// Registrar rutas REST
		$rest_api = new ERM_REST_API();
		$rest_api->register_routes();

		// Limpiar tracking entre tests
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}erm_tracking" );

		// Limpiar transients de stats
		delete_transient( 'erm_stats_summary_all' );
		delete_transient( 'erm_stats_summary_month' );
		delete_transient( 'erm_stats_summary_week' );

		// Asegurarse de que no hay usuario logueado al inicio de cada test
		wp_set_current_user( 0 );
	}

	public function tear_down() : void {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	// =========================================================
	// TESTS: REGISTRO DE RUTAS
	// =========================================================

	/**
	 * @test
	 */
	public function test_erm_routes_are_registered() : void {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/erm/v1/resources', $routes );
		$this->assertArrayHasKey( '/erm/v1/resources/(?P<id>\d+)', $routes );
		$this->assertArrayHasKey( '/erm/v1/resources/(?P<id>\d+)/track', $routes );
		$this->assertArrayHasKey( '/erm/v1/stats', $routes );
	}

	// =========================================================
	// TESTS: GET /resources (Listado)
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_resources_returns_200_with_empty_list() : void {
		$response = $this->rest_request( 'GET', '/resources' );

		$this->assertSame( 200, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertIsArray( $data['data']['resources'] );
		$this->assertCount( 0, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_published_resources_only() : void {
		$this->create_resource( [ 'post_title' => 'Publicado', 'post_status' => 'publish' ] );
		$this->create_resource( [ 'post_title' => 'Borrador', 'post_status' => 'draft' ] );

		$response = $this->rest_request( 'GET', '/resources' );
		$data     = $this->get_response_data( $response );

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( 'Publicado', $data['data']['resources'][0]['title'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_response_has_correct_structure() : void {
		$post_id = $this->create_resource();

		$response = $this->rest_request( 'GET', '/resources' );
		$data     = $this->get_response_data( $response );

		$resource = $data['data']['resources'][0];

		// Verificar todos los campos obligatorios de la respuesta
		$expected_keys = [
			'id', 'title', 'excerpt', 'type', 'difficulty',
			'duration_minutes', 'url', 'instructor', 'price',
			'categories', 'skills', 'featured_image', 'views',
			'permalink', 'date_created',
		];

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey(
				$key,
				$resource,
				"La respuesta del recurso debe incluir el campo '{$key}'"
			);
		}
	}

	/**
	 * @test
	 */
	public function test_get_resources_pagination_is_correct() : void {
		// Crear 15 recursos
		for ( $i = 1; $i <= 15; $i++ ) {
			$this->create_resource( [ 'post_title' => "Recurso {$i}" ] );
		}

		$response = $this->rest_request( 'GET', '/resources', [ 'per_page' => 5, 'page' => 1 ] );
		$data     = $this->get_response_data( $response );

		$pagination = $data['data']['pagination'];

		$this->assertSame( 15, $pagination['total'] );
		$this->assertSame( 3, $pagination['total_pages'] );
		$this->assertSame( 1, $pagination['current_page'] );
		$this->assertSame( 5, $pagination['per_page'] );
		$this->assertTrue( $pagination['has_more'] );
		$this->assertCount( 5, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_last_page_has_no_more() : void {
		for ( $i = 1; $i <= 3; $i++ ) {
			$this->create_resource( [ 'post_title' => "Recurso {$i}" ] );
		}

		$response   = $this->rest_request( 'GET', '/resources', [ 'per_page' => 2, 'page' => 2 ] );
		$data       = $this->get_response_data( $response );
		$pagination = $data['data']['pagination'];

		$this->assertFalse( $pagination['has_more'] );
		$this->assertCount( 1, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_type() : void {
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'type' => 'ebook' ] );
		$data     = $this->get_response_data( $response );

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $data['data']['resources'] );

		foreach ( $data['data']['resources'] as $resource ) {
			$this->assertSame( 'ebook', $resource['type'] );
		}
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_difficulty() : void {
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'beginner' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'advanced' ] ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'difficulty' => 'beginner' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( 'beginner', $data['data']['resources'][0]['difficulty'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_category_slug() : void {
		$post_1 = $this->create_resource( [ 'post_title' => 'Con categoría' ] );
		$post_2 = $this->create_resource( [ 'post_title' => 'Sin categoría' ] );

		$this->create_and_assign_term( $post_1, 'Backend', 'resource_category' );

		$response = $this->rest_request( 'GET', '/resources', [ 'category' => 'backend' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( $post_1, $data['data']['resources'][0]['id'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_search_by_title() : void {
		$this->create_resource( [ 'post_title' => 'Introducción a PHP' ] );
		$this->create_resource( [ 'post_title' => 'JavaScript Avanzado' ] );
		$this->create_resource( [ 'post_title' => 'PHP para Backends' ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'search' => 'PHP' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 2, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_400_for_invalid_type() : void {
		$response = $this->rest_request( 'GET', '/resources', [ 'type' => 'invalid_type' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_400_for_invalid_difficulty() : void {
		$response = $this->rest_request( 'GET', '/resources', [ 'difficulty' => 'expert' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	// =========================================================
	// TESTS: GET /resources/{id}
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_single_resource_returns_200() : void {
		$post_id  = $this->create_resource( [ 'post_title' => 'Mi Recurso' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 200, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $post_id, $data['data']['id'] );
		$this->assertSame( 'Mi Recurso', $data['data']['title'] );
	}

	/**
	 * @test
	 */
	public function test_get_single_resource_includes_full_content() : void {
		$post_id  = $this->create_resource( [ 'post_content' => '<p>Contenido completo aquí.</p>' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data     = $this->get_response_data( $response );

		// El recurso individual debe incluir content y downloads (no presentes en el listado)
		$this->assertArrayHasKey( 'content', $data['data'] );
		$this->assertArrayHasKey( 'downloads', $data['data'] );
		$this->assertArrayHasKey( 'date_modified', $data['data'] );
	}

	/**
	 * @test
	 */
	public function test_get_single_resource_includes_categories_and_skills() : void {
		$post_id = $this->create_resource();
		$this->create_and_assign_term( $post_id, 'Diseño', 'resource_category' );
		$this->create_and_assign_term( $post_id, 'CSS', 'skill_tag' );

		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['categories'] );
		$this->assertSame( 'Diseño', $data['data']['categories'][0]['name'] );

		$this->assertCount( 1, $data['data']['skills'] );
		$this->assertSame( 'CSS', $data['data']['skills'][0]['name'] );
	}

	/**
	 * @test
	 */
	public function test_get_nonexistent_resource_returns_404() : void {
		$response = $this->rest_request( 'GET', '/resources/99999' );

		$this->assertSame( 404, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertSame( 'resource_not_found', $data['code'] );
	}

	/**
	 * @test
	 */
	public function test_get_draft_resource_returns_404() : void {
		$post_id  = $this->create_resource( [ 'post_status' => 'draft' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_post_of_wrong_type_returns_404() : void {
		// Crear un post regular (no del CPT)
		$post_id = wp_insert_post( [
			'post_title'  => 'Post Normal',
			'post_status' => 'publish',
			'post_type'   => 'post',
		] );

		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 404, $response->get_status() );
	}

	// =========================================================
	// TESTS: POST /resources/{id}/track
	// =========================================================

	/**
	 * @test
	 */
	public function test_track_view_returns_201() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'view',
		] );

		$this->assertSame( 201, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $post_id, $data['data']['resource_id'] );
		$this->assertSame( 'view', $data['data']['action_type'] );
		$this->assertArrayHasKey( 'tracking_id', $data['data'] );
		$this->assertGreaterThan( 0, $data['data']['tracking_id'] );
	}

	/**
	 * @test
	 */
	public function test_track_download_is_recorded_in_database() : void {
		global $wpdb;
		$table   = $wpdb->prefix . 'erm_tracking';
		$post_id = $this->create_resource();

		$this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'download',
		] );

		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE resource_id = %d AND action_type = %s",
			$post_id,
			'download'
		) );

		$this->assertSame( 1, $count );
	}

	/**
	 * @test
	 */
	public function test_track_complete_is_accepted() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'complete',
		] );

		$this->assertSame( 201, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_invalid_action_type_returns_400() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'invalid_action',
		] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_missing_action_type_returns_400() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_nonexistent_resource_returns_404() : void {
		$response = $this->rest_request( 'POST', '/resources/99999/track', [
			'action_type' => 'view',
		] );

		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_multiple_views_are_all_recorded() : void {
		global $wpdb;
		$table   = $wpdb->prefix . 'erm_tracking';
		$post_id = $this->create_resource();

		// Registrar 5 vistas
		for ( $i = 0; $i < 5; $i++ ) {
			$this->rest_request( 'POST', "/resources/{$post_id}/track", [ 'action_type' => 'view' ] );
		}

		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE resource_id = %d",
			$post_id
		) );

		$this->assertSame( 5, $count );
	}

	/**
	 * @test
	 */
	public function test_tracking_increments_views_count_in_resource_response() : void {
		$post_id = $this->create_resource();

		// Vista inicial: 0
		$response_before = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data_before     = $this->get_response_data( $response_before );
		$this->assertSame( 0, $data_before['data']['views'] );

		// Registrar 3 vistas directamente en la BD (para evitar cache de transients)
		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'view' );

		// Limpiar posibles transients de cache
		delete_transient( 'erm_stats_summary_all' );

		// Vista después de tracking
		$response_after = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data_after     = $this->get_response_data( $response_after );
		$this->assertSame( 3, $data_after['data']['views'] );
	}

	// =========================================================
	// TESTS: GET /stats (solo admin)
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_stats_requires_admin_permission() : void {
		// Sin usuario
		wp_set_current_user( 0 );
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_returns_403_for_subscriber() : void {
		$this->create_subscriber_user();
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_returns_200_for_admin() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 200, $response->get_status() );
		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_response_has_correct_structure() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$response_data = $data['data'];

		// Claves a nivel raíz de data
		$this->assertArrayHasKey( 'summary', $response_data );
		$this->assertArrayHasKey( 'by_type', $response_data );
		$this->assertArrayHasKey( 'by_difficulty', $response_data );
		$this->assertArrayHasKey( 'top_resources', $response_data );
		$this->assertArrayHasKey( 'monthly_growth', $response_data );
	}

	/**
	 * @test
	 */
	public function test_get_stats_summary_has_required_fields() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertArrayHasKey( 'total_resources', $summary );
		$this->assertArrayHasKey( 'by_type', $summary );
		$this->assertArrayHasKey( 'by_difficulty', $summary );
		$this->assertArrayHasKey( 'total_views', $summary );
		$this->assertArrayHasKey( 'total_downloads', $summary );
		$this->assertArrayHasKey( 'unique_users', $summary );
		$this->assertArrayHasKey( 'period', $summary );
	}

	/**
	 * @test
	 */
	public function test_get_stats_by_difficulty_has_all_levels() : void {
		$this->create_admin_user();
		$response      = $this->rest_request( 'GET', '/stats' );
		$data          = $this->get_response_data( $response );
		$by_difficulty = $data['data']['by_difficulty'];

		$this->assertArrayHasKey( 'beginner', $by_difficulty );
		$this->assertArrayHasKey( 'intermediate', $by_difficulty );
		$this->assertArrayHasKey( 'advanced', $by_difficulty );
	}

	/**
	 * @test
	 */
	public function test_get_stats_counts_resources_correctly() : void {
		$this->create_admin_user();

		// Crear 2 cursos y 1 tutorial
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'tutorial' ] ] );

		// Limpiar transient para forzar recalculo
		delete_transient( 'erm_stats_summary_all' );

		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertSame( 3, $summary['total_resources'] );
		$this->assertSame( 2, $summary['by_type']['course'] );
		$this->assertSame( 1, $summary['by_type']['tutorial'] );
		$this->assertSame( 0, $summary['by_type']['ebook'] );
		$this->assertSame( 0, $summary['by_type']['video'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_by_difficulty_at_root_matches_summary() : void {
		$this->create_admin_user();

		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'beginner' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'advanced' ] ] );

		delete_transient( 'erm_stats_summary_all' );

		$response      = $this->rest_request( 'GET', '/stats' );
		$data          = $this->get_response_data( $response );
		$root_diff     = $data['data']['by_difficulty'];
		$summary_diff  = $data['data']['summary']['by_difficulty'];

		// Los valores a nivel raíz deben ser idénticos a los de summary
		$this->assertSame( $root_diff, $summary_diff );
	}

	/**
	 * @test
	 */
	public function test_get_stats_period_all_returns_all_time_totals() : void {
		$this->create_admin_user();
		$post_id = $this->create_resource();

		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'download' );

		delete_transient( 'erm_stats_summary_all' );

		$response = $this->rest_request( 'GET', '/stats', [ 'period' => 'all' ] );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertSame( 'all', $summary['period'] );
		$this->assertSame( 1, (int) $summary['total_views'] );
		$this->assertSame( 1, (int) $summary['total_downloads'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_period_invalid_returns_400() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats', [ 'period' => 'year' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_top_resources_is_array() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$this->assertIsArray( $data['data']['top_resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_monthly_growth_is_array() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$this->assertIsArray( $data['data']['monthly_growth'] );
	}
}
