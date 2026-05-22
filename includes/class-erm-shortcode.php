<?php
/**
 * Shortcode handler.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Shortcode
 */
class ERM_Shortcode {

	/**
	 * Whether public assets were enqueued.
	 *
	 * @var bool
	 */
	private static $assets_enqueued = false;

	/**
	 * Render the recursos_educativos shortcode.
	 *
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'         => '',
				'difficulty'   => '',
				'category'     => '',
				'per_page'     => 10,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'title'        => __( 'Recursos Educativos', 'education-resources-manager' ),
				'show_filters' => 'yes',
			),
			$atts,
			'recursos_educativos'
		);

		$atts['type']         = sanitize_text_field( $atts['type'] );
		$atts['difficulty']   = sanitize_text_field( $atts['difficulty'] );
		$atts['category']     = sanitize_text_field( $atts['category'] );
		$atts['per_page']     = max( 1, min( 100, absint( $atts['per_page'] ) ) );
		$atts['orderby']      = in_array( $atts['orderby'], array( 'date', 'title', 'views' ), true ) ? $atts['orderby'] : 'date';
		$atts['order']        = in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : 'DESC';
		$atts['title']        = sanitize_text_field( $atts['title'] );
		$atts['show_filters'] = in_array( $atts['show_filters'], array( 'yes', 'no' ), true ) ? $atts['show_filters'] : 'yes';

		$valid_types = array( 'course', 'tutorial', 'ebook', 'video' );
		if ( $atts['type'] && ! in_array( $atts['type'], $valid_types, true ) ) {
			$atts['type'] = '';
		}

		$valid_difficulties = array( 'beginner', 'intermediate', 'advanced' );
		if ( $atts['difficulty'] && ! in_array( $atts['difficulty'], $valid_difficulties, true ) ) {
			$atts['difficulty'] = '';
		}

		$this->enqueue_assets( $atts );

		ob_start();
		include ERM_PLUGIN_DIR . 'public/views/shortcode-template.php';
		return ob_get_clean();
	}

	/**
	 * Enqueue frontend styles and scripts.
	 *
	 * @param array<string, mixed> $atts Parsed shortcode attributes.
	 */
	public function enqueue_assets( $atts ) {
		if ( self::$assets_enqueued ) {
			return;
		}

		wp_enqueue_style(
			'erm-public',
			ERM_PLUGIN_URL . 'public/css/erm-public.css',
			array(),
			ERM_VERSION
		);

		wp_enqueue_script(
			'erm-public',
			ERM_PLUGIN_URL . 'public/js/erm-public.js',
			array(),
			ERM_VERSION,
			true
		);

		wp_localize_script(
			'erm-public',
			'ermPublic',
			array(
				'apiUrl'  => esc_url_raw( rest_url( 'erm/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'loading'    => __( 'Cargando recursos...', 'education-resources-manager' ),
					'no_results' => __( 'No se encontraron recursos.', 'education-resources-manager' ),
					'error'      => __( 'Error al cargar los recursos.', 'education-resources-manager' ),
					'view'       => __( 'Ver recurso', 'education-resources-manager' ),
					'free'       => __( 'Gratuito', 'education-resources-manager' ),
				),
				'perPage' => (int) $atts['per_page'],
				'siteUrl' => get_site_url(),
			)
		);

		self::$assets_enqueued = true;
	}
}
