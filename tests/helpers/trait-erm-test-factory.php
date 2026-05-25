<?php
/**
 * Trait con métodos factory para crear datos de prueba del plugin ERM.
 *
 * @package ERM\Tests
 */

trait ERM_Test_Factory {

	/**
	 * Crea un post del CPT education_resource con valores configurables.
	 *
	 * @param array $overrides Valores a sobreescribir en el post y su meta.
	 * @return int ID del post creado.
	 */
	protected function create_resource( array $overrides = [] ) : int {
		$defaults = [
			'post_title'   => 'Recurso de Prueba',
			'post_content' => 'Contenido del recurso de prueba.',
			'post_excerpt' => 'Extracto del recurso.',
			'post_status'  => 'publish',
			'post_type'    => 'education_resource',
			'meta_input'   => [
				'_erm_resource_type'    => 'course',
				'_erm_difficulty_level' => 'beginner',
				'_erm_duration_minutes' => 60,
				'_erm_resource_url'     => 'https://example.com/curso',
				'_erm_instructor'       => 'Juan Pérez',
				'_erm_price'            => 0,
			],
		];

		$meta_overrides = $overrides['meta_input'] ?? [];
		unset( $overrides['meta_input'] );

		$post_args               = array_merge( $defaults, $overrides );
		$post_args['meta_input'] = array_merge( $defaults['meta_input'], $meta_overrides );

		$post_id = wp_insert_post( $post_args, true );

		if ( is_wp_error( $post_id ) ) {
			$this->fail( 'No se pudo crear el recurso de prueba: ' . $post_id->get_error_message() );
		}

		return (int) $post_id;
	}

	/**
	 * Crea un registro de tracking en la tabla personalizada.
	 *
	 * @param int    $resource_id ID del recurso.
	 * @param string $action_type Tipo de acción: view | download | complete.
	 * @param int    $user_id     ID del usuario (0 = anónimo).
	 * @return int|false ID del registro insertado o false en error.
	 */
	protected function create_tracking( int $resource_id, string $action_type = 'view', int $user_id = 0 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'erm_tracking';

		$result = $wpdb->insert(
			$table,
			[
				'resource_id' => $resource_id,
				'user_id'     => $user_id ?: null,
				'action_type' => $action_type,
				'action_date' => current_time( 'mysql' ),
				'ip_address'  => '127.0.0.1',
				'user_agent'  => 'PHPUnit Test Runner',
			],
			[ '%d', '%d', '%s', '%s', '%s', '%s' ]
		);

		return $result !== false ? $wpdb->insert_id : false;
	}

	/**
	 * Crea un término de taxonomía y lo asigna al recurso (acumula términos existentes).
	 *
	 * @param int    $resource_id ID del recurso al que asignar.
	 * @param string $name        Nombre del término.
	 * @param string $taxonomy    Slug de la taxonomía.
	 * @return int term_id del término creado.
	 */
	protected function create_and_assign_term( int $resource_id, string $name, string $taxonomy = 'resource_category' ) : int {
		$term = wp_insert_term( $name, $taxonomy );

		if ( is_wp_error( $term ) ) {
			if ( 'term_exists' === $term->get_error_code() ) {
				$term_id = (int) $term->get_error_data( 'term_id' );
			} else {
				$this->fail( "No se pudo crear el término '{$name}' en taxonomía '{$taxonomy}': " . $term->get_error_message() );
			}
		} else {
			$term_id = (int) $term['term_id'];
		}

		$existing = wp_get_post_terms( $resource_id, $taxonomy, [ 'fields' => 'ids' ] );
		if ( is_wp_error( $existing ) ) {
			$existing = [];
		}

		$term_ids = array_unique( array_merge( $existing, [ $term_id ] ) );
		wp_set_post_terms( $resource_id, $term_ids, $taxonomy );

		return $term_id;
	}

	/**
	 * Crea y autentica un usuario administrador para los tests de la REST API.
	 *
	 * @return int user_id del admin creado.
	 */
	protected function create_admin_user() : int {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Crea y autentica un usuario suscriptor (sin permisos admin).
	 *
	 * @return int user_id del suscriptor creado.
	 */
	protected function create_subscriber_user() : int {
		$user_id = $this->factory->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Construye y despacha una WP_REST_Request.
	 *
	 * @param string $method Método HTTP: GET | POST | PUT | DELETE.
	 * @param string $route  Ruta sin el namespace, ej: '/resources'.
	 * @param array  $params Parámetros de la petición.
	 * @return WP_REST_Response
	 */
	protected function rest_request( string $method, string $route, array $params = [] ) : WP_REST_Response {
		$request = new WP_REST_Request( $method, '/erm/v1' . $route );

		if ( in_array( $method, [ 'GET', 'DELETE' ], true ) ) {
			$request->set_query_params( $params );
		} else {
			$request->set_body_params( $params );
		}

		$response = rest_get_server()->dispatch( $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Retorna el cuerpo decodificado de una WP_REST_Response.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array
	 */
	protected function get_response_data( WP_REST_Response $response ) : array {
		return $response->get_data();
	}
}
