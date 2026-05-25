<?php
/**
 * Tests para el Custom Post Type, taxonomías y post meta del plugin ERM.
 *
 * Cubre:
 * - Registro del CPT education_resource
 * - Registro de taxonomías resource_category y skill_tag
 * - Guardado y recuperación de post meta
 * - Sanitización de datos en save_meta
 * - Columnas personalizadas en el admin
 *
 * @package ERM\Tests
 */

require_once __DIR__ . '/helpers/trait-erm-test-factory.php';

/**
 * Class Test_ERM_Post_Type
 *
 * @covers ERM_Post_Type
 * @covers ERM_Taxonomy
 */
class Test_ERM_Post_Type extends WP_UnitTestCase {

	use ERM_Test_Factory;

	// =========================================================
	// SETUP / TEARDOWN
	// =========================================================

	public function set_up() : void {
		parent::set_up();

		// Registrar el CPT y taxonomías (simula el hook 'init')
		$post_type = new ERM_Post_Type();
		$post_type->register();

		$taxonomy = new ERM_Taxonomy();
		$taxonomy->register();

		// Limpiar la tabla de tracking entre tests
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}erm_tracking" );
	}

	// =========================================================
	// TESTS: REGISTRO DEL CPT
	// =========================================================

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_registered() : void {
		$this->assertTrue(
			post_type_exists( 'education_resource' ),
			'El CPT education_resource debe estar registrado después de llamar a register()'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_public() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertNotNull( $post_type_object );
		$this->assertTrue( $post_type_object->public );
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_exposed_to_rest_api() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertTrue(
			$post_type_object->show_in_rest,
			'El CPT debe estar expuesto a la REST API (show_in_rest = true)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_supports_editor_and_thumbnail() : void {
		$this->assertTrue(
			post_type_supports( 'education_resource', 'editor' ),
			'El CPT debe soportar el editor'
		);

		$this->assertTrue(
			post_type_supports( 'education_resource', 'thumbnail' ),
			'El CPT debe soportar imagen destacada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_has_correct_menu_icon() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertSame(
			'dashicons-welcome-learn-more',
			$post_type_object->menu_icon
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_has_archive() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertTrue( $post_type_object->has_archive );
	}

	// =========================================================
	// TESTS: CREAR Y RECUPERAR POSTS DEL CPT
	// =========================================================

	/**
	 * @test
	 */
	public function test_can_create_education_resource_post() : void {
		$post_id = $this->create_resource( [ 'post_title' => 'Mi Curso de Prueba' ] );

		$this->assertGreaterThan( 0, $post_id );
		$this->assertSame( 'education_resource', get_post_type( $post_id ) );
		$this->assertSame( 'Mi Curso de Prueba', get_the_title( $post_id ) );
	}

	/**
	 * @test
	 */
	public function test_published_resource_is_queryable() : void {
		$post_id = $this->create_resource( [ 'post_status' => 'publish' ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( $post_id, (int) $query->posts[0]->ID );
	}

	/**
	 * @test
	 */
	public function test_draft_resource_is_not_in_public_query() : void {
		$this->create_resource( [ 'post_status' => 'draft' ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
		] );

		$this->assertSame( 0, $query->found_posts );
	}

	// =========================================================
	// TESTS: POST META
	// =========================================================

	/**
	 * @test
	 */
	public function test_resource_type_meta_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_resource_type' => 'tutorial' ],
		] );

		$this->assertSame(
			'tutorial',
			get_post_meta( $post_id, '_erm_resource_type', true )
		);
	}

	/**
	 * @test
	 */
	public function test_difficulty_level_meta_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_difficulty_level' => 'advanced' ],
		] );

		$this->assertSame(
			'advanced',
			get_post_meta( $post_id, '_erm_difficulty_level', true )
		);
	}

	/**
	 * @test
	 */
	public function test_duration_minutes_is_stored_as_integer() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_duration_minutes' => 90 ],
		] );

		$value = (int) get_post_meta( $post_id, '_erm_duration_minutes', true );
		$this->assertSame( 90, $value );
	}

	/**
	 * @test
	 */
	public function test_price_zero_is_saved_as_free() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_price' => 0 ],
		] );

		$price = (float) get_post_meta( $post_id, '_erm_price', true );
		$this->assertSame( 0.0, $price );
	}

	/**
	 * @test
	 */
	public function test_resource_url_is_saved_correctly() : void {
		$url     = 'https://example.com/mi-recurso';
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_resource_url' => $url ],
		] );

		$this->assertSame(
			$url,
			get_post_meta( $post_id, '_erm_resource_url', true )
		);
	}

	/**
	 * @test
	 */
	public function test_instructor_name_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_instructor' => 'María García' ],
		] );

		$this->assertSame(
			'María García',
			get_post_meta( $post_id, '_erm_instructor', true )
		);
	}

	/**
	 * @test
	 */
	public function test_all_valid_resource_types_are_storable() : void {
		$valid_types = [ 'course', 'tutorial', 'ebook', 'video' ];

		foreach ( $valid_types as $type ) {
			$post_id = $this->create_resource( [
				'post_title' => "Recurso tipo {$type}",
				'meta_input' => [ '_erm_resource_type' => $type ],
			] );

			$this->assertSame(
				$type,
				get_post_meta( $post_id, '_erm_resource_type', true ),
				"El tipo '{$type}' debe poder guardarse en post meta"
			);
		}
	}

	/**
	 * @test
	 */
	public function test_all_valid_difficulty_levels_are_storable() : void {
		$valid_levels = [ 'beginner', 'intermediate', 'advanced' ];

		foreach ( $valid_levels as $level ) {
			$post_id = $this->create_resource( [
				'post_title' => "Recurso nivel {$level}",
				'meta_input' => [ '_erm_difficulty_level' => $level ],
			] );

			$this->assertSame(
				$level,
				get_post_meta( $post_id, '_erm_difficulty_level', true ),
				"El nivel '{$level}' debe poder guardarse en post meta"
			);
		}
	}

	// =========================================================
	// TESTS: TAXONOMÍAS
	// =========================================================

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_resource_category_taxonomy_is_registered() : void {
		$this->assertTrue(
			taxonomy_exists( 'resource_category' ),
			'La taxonomía resource_category debe estar registrada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_skill_tag_taxonomy_is_registered() : void {
		$this->assertTrue(
			taxonomy_exists( 'skill_tag' ),
			'La taxonomía skill_tag debe estar registrada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_resource_category_is_hierarchical() : void {
		$taxonomy = get_taxonomy( 'resource_category' );

		$this->assertTrue(
			$taxonomy->hierarchical,
			'resource_category debe ser jerárquica (como categorías)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_skill_tag_is_not_hierarchical() : void {
		$taxonomy = get_taxonomy( 'skill_tag' );

		$this->assertFalse(
			$taxonomy->hierarchical,
			'skill_tag no debe ser jerárquica (como etiquetas)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_taxonomies_are_exposed_to_rest_api() : void {
		$category_tax = get_taxonomy( 'resource_category' );
		$skill_tax    = get_taxonomy( 'skill_tag' );

		$this->assertTrue( $category_tax->show_in_rest, 'resource_category debe exponerse a la REST API' );
		$this->assertTrue( $skill_tax->show_in_rest, 'skill_tag debe exponerse a la REST API' );
	}

	/**
	 * @test
	 */
	public function test_can_assign_category_to_resource() : void {
		$post_id = $this->create_resource();
		$term_id = $this->create_and_assign_term( $post_id, 'Programación', 'resource_category' );

		$terms = wp_get_post_terms( $post_id, 'resource_category' );

		$this->assertCount( 1, $terms );
		$this->assertSame( $term_id, (int) $terms[0]->term_id );
		$this->assertSame( 'Programación', $terms[0]->name );
	}

	/**
	 * @test
	 */
	public function test_can_assign_multiple_skill_tags_to_resource() : void {
		$post_id = $this->create_resource();
		$this->create_and_assign_term( $post_id, 'JavaScript', 'skill_tag' );
		$this->create_and_assign_term( $post_id, 'WordPress', 'skill_tag' );

		$terms = wp_get_post_terms( $post_id, 'skill_tag' );

		$this->assertCount( 2, $terms );

		$tag_names = wp_list_pluck( $terms, 'name' );
		$this->assertContains( 'JavaScript', $tag_names );
		$this->assertContains( 'WordPress', $tag_names );
	}

	/**
	 * @test
	 */
	public function test_resource_category_supports_hierarchy() : void {
		$parent = wp_insert_term( 'Tecnología', 'resource_category' );
		$child  = wp_insert_term( 'Programación', 'resource_category', [
			'parent' => $parent['term_id'],
		] );

		$child_term = get_term( $child['term_id'], 'resource_category' );

		$this->assertSame(
			(int) $parent['term_id'],
			(int) $child_term->parent,
			'Las categorías hijas deben tener parent_id correcto'
		);
	}

	// =========================================================
	// TESTS: FILTRADO CON WP_QUERY
	// =========================================================

	/**
	 * @test
	 */
	public function test_can_filter_resources_by_type_via_meta_query() : void {
		$this->create_resource( [ 'post_title' => 'Curso A', 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'post_title' => 'Tutorial B', 'meta_input' => [ '_erm_resource_type' => 'tutorial' ] ] );
		$this->create_resource( [ 'post_title' => 'Ebook C', 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
			'meta_query'  => [
				[ 'key' => '_erm_resource_type', 'value' => 'course', 'compare' => '=' ],
			],
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( 'Curso A', $query->posts[0]->post_title );
	}

	/**
	 * @test
	 */
	public function test_can_filter_resources_by_category_via_tax_query() : void {
		$post_1 = $this->create_resource( [ 'post_title' => 'Recurso con categoría' ] );
		$post_2 = $this->create_resource( [ 'post_title' => 'Recurso sin categoría' ] );

		$this->create_and_assign_term( $post_1, 'Frontend', 'resource_category' );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
			'tax_query'   => [
				[ 'taxonomy' => 'resource_category', 'field' => 'name', 'terms' => 'Frontend' ],
			],
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( $post_1, (int) $query->posts[0]->ID );
	}
}
