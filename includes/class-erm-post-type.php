<?php
/**
 * Custom post type registration and meta boxes.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Post_Type
 */
class ERM_Post_Type {

	/**
	 * Valid resource types.
	 *
	 * @var string[]
	 */
	private $resource_types = array( 'course', 'tutorial', 'ebook', 'video' );

	/**
	 * Valid difficulty levels.
	 *
	 * @var string[]
	 */
	private $difficulty_levels = array( 'beginner', 'intermediate', 'advanced' );

	/**
	 * Register the education_resource post type.
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Recursos Educativos', 'education-resources-manager' ),
			'singular_name'      => __( 'Recurso Educativo', 'education-resources-manager' ),
			'add_new'            => __( 'Añadir Nuevo', 'education-resources-manager' ),
			'add_new_item'       => __( 'Añadir Nuevo Recurso', 'education-resources-manager' ),
			'edit_item'          => __( 'Editar Recurso', 'education-resources-manager' ),
			'new_item'           => __( 'Nuevo Recurso', 'education-resources-manager' ),
			'view_item'          => __( 'Ver Recurso', 'education-resources-manager' ),
			'search_items'       => __( 'Buscar Recursos', 'education-resources-manager' ),
			'not_found'          => __( 'No se encontraron recursos', 'education-resources-manager' ),
			'not_found_in_trash' => __( 'No hay recursos en la papelera', 'education-resources-manager' ),
			'menu_name'          => __( 'Recursos Edu.', 'education-resources-manager' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields' ),
			'taxonomies'          => array( 'resource_category', 'skill_tag' ),
			'has_archive'         => true,
			'rewrite'             => array(
				'slug'       => 'recursos',
				'with_front' => false,
			),
			'query_var'           => true,
		);

		register_post_type( 'education_resource', $args );
	}

	/**
	 * Register meta boxes for the CPT.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'erm_resource_details',
			__( 'Detalles del Recurso', 'education-resources-manager' ),
			array( $this, 'render_meta_box' ),
			'education_resource',
			'normal',
			'high'
		);
	}

	/**
	 * Render the resource details meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'erm_save_meta', 'erm_meta_nonce' );

		$resource_type    = get_post_meta( $post->ID, '_erm_resource_type', true );
		$difficulty_level = get_post_meta( $post->ID, '_erm_difficulty_level', true );
		$duration         = get_post_meta( $post->ID, '_erm_duration_minutes', true );
		$resource_url     = get_post_meta( $post->ID, '_erm_resource_url', true );
		$instructor       = get_post_meta( $post->ID, '_erm_instructor', true );
		$price            = get_post_meta( $post->ID, '_erm_price', true );

		$type_labels = array(
			'course'   => __( 'Curso', 'education-resources-manager' ),
			'tutorial' => __( 'Tutorial', 'education-resources-manager' ),
			'ebook'    => __( 'eBook', 'education-resources-manager' ),
			'video'    => __( 'Video', 'education-resources-manager' ),
		);

		$difficulty_labels = array(
			'beginner'     => __( 'Principiante', 'education-resources-manager' ),
			'intermediate' => __( 'Intermedio', 'education-resources-manager' ),
			'advanced'     => __( 'Avanzado', 'education-resources-manager' ),
		);
		?>
		<table class="form-table erm-meta-box">
			<tr>
				<th scope="row">
					<label for="erm_resource_type"><?php esc_html_e( 'Tipo de recurso', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<select name="erm_resource_type" id="erm_resource_type">
						<option value=""><?php esc_html_e( '— Seleccionar —', 'education-resources-manager' ); ?></option>
						<?php foreach ( $type_labels as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $resource_type, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="erm_difficulty_level"><?php esc_html_e( 'Nivel de dificultad', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<select name="erm_difficulty_level" id="erm_difficulty_level">
						<option value=""><?php esc_html_e( '— Seleccionar —', 'education-resources-manager' ); ?></option>
						<?php foreach ( $difficulty_labels as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $difficulty_level, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="erm_duration_minutes"><?php esc_html_e( 'Duración (minutos)', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<input type="number" name="erm_duration_minutes" id="erm_duration_minutes" min="0" value="<?php echo esc_attr( $duration ); ?>" class="small-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="erm_resource_url"><?php esc_html_e( 'URL del recurso', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<input type="url" name="erm_resource_url" id="erm_resource_url" value="<?php echo esc_attr( $resource_url ); ?>" class="large-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="erm_instructor"><?php esc_html_e( 'Instructor/Autor', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<input type="text" name="erm_instructor" id="erm_instructor" value="<?php echo esc_attr( $instructor ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="erm_price"><?php esc_html_e( 'Precio', 'education-resources-manager' ); ?></label>
				</th>
				<td>
					<input type="number" name="erm_price" id="erm_price" min="0" step="0.01" value="<?php echo esc_attr( $price ); ?>" class="small-text" />
					<span class="description"><?php esc_html_e( '(0 = gratuito)', 'education-resources-manager' ); ?></span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['erm_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['erm_meta_nonce'] ) ), 'erm_save_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['erm_resource_type'] ) ) {
			$type = sanitize_text_field( wp_unslash( $_POST['erm_resource_type'] ) );
			if ( in_array( $type, $this->resource_types, true ) ) {
				update_post_meta( $post_id, '_erm_resource_type', $type );
			}
		}

		if ( isset( $_POST['erm_difficulty_level'] ) ) {
			$level = sanitize_text_field( wp_unslash( $_POST['erm_difficulty_level'] ) );
			if ( in_array( $level, $this->difficulty_levels, true ) ) {
				update_post_meta( $post_id, '_erm_difficulty_level', $level );
			}
		}

		if ( isset( $_POST['erm_duration_minutes'] ) ) {
			update_post_meta( $post_id, '_erm_duration_minutes', absint( wp_unslash( $_POST['erm_duration_minutes'] ) ) );
		}

		if ( isset( $_POST['erm_resource_url'] ) ) {
			update_post_meta( $post_id, '_erm_resource_url', esc_url_raw( wp_unslash( $_POST['erm_resource_url'] ) ) );
		}

		if ( isset( $_POST['erm_instructor'] ) ) {
			update_post_meta( $post_id, '_erm_instructor', sanitize_text_field( wp_unslash( $_POST['erm_instructor'] ) ) );
		}

		if ( isset( $_POST['erm_price'] ) ) {
			$price = floatval( wp_unslash( $_POST['erm_price'] ) );
			update_post_meta( $post_id, '_erm_price', max( 0, $price ) );
		}
	}

	/**
	 * Add custom columns to the resources list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_custom_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'title' === $key ) {
				$new_columns['erm_type']      = __( 'Tipo', 'education-resources-manager' );
				$new_columns['erm_level']     = __( 'Nivel', 'education-resources-manager' );
				$new_columns['erm_duration']  = __( 'Duración', 'education-resources-manager' );
				$new_columns['erm_price']     = __( 'Precio', 'education-resources-manager' );
				$new_columns['erm_views']     = __( 'Visualizaciones', 'education-resources-manager' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'erm_type':
				echo esc_html( $this->get_type_label( get_post_meta( $post_id, '_erm_resource_type', true ) ) );
				break;
			case 'erm_level':
				echo esc_html( $this->get_difficulty_label( get_post_meta( $post_id, '_erm_difficulty_level', true ) ) );
				break;
			case 'erm_duration':
				$minutes = absint( get_post_meta( $post_id, '_erm_duration_minutes', true ) );
				if ( $minutes > 0 ) {
					/* translators: %d: duration in minutes */
					echo esc_html( sprintf( __( '%d min', 'education-resources-manager' ), $minutes ) );
				} else {
					echo '—';
				}
				break;
			case 'erm_price':
				$price = floatval( get_post_meta( $post_id, '_erm_price', true ) );
				if ( $price > 0 ) {
					echo esc_html( number_format_i18n( $price, 2 ) );
				} else {
					esc_html_e( 'Gratuito', 'education-resources-manager' );
				}
				break;
			case 'erm_views':
				$database = new ERM_Database();
				echo esc_html( number_format_i18n( $database->get_resource_views( $post_id ) ) );
				break;
		}
	}

	/**
	 * Get human-readable resource type label.
	 *
	 * @param string $type Resource type slug.
	 * @return string
	 */
	private function get_type_label( $type ) {
		$labels = array(
			'course'   => __( 'Curso', 'education-resources-manager' ),
			'tutorial' => __( 'Tutorial', 'education-resources-manager' ),
			'ebook'    => __( 'eBook', 'education-resources-manager' ),
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
	private function get_difficulty_label( $level ) {
		$labels = array(
			'beginner'     => __( 'Principiante', 'education-resources-manager' ),
			'intermediate' => __( 'Intermedio', 'education-resources-manager' ),
			'advanced'     => __( 'Avanzado', 'education-resources-manager' ),
		);

		return isset( $labels[ $level ] ) ? $labels[ $level ] : '—';
	}
}
