<?php
/**
 * Custom taxonomies registration.
 *
 * @package Education_Resources_Manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class ERM_Taxonomy
 */
class ERM_Taxonomy {

	/**
	 * Register resource taxonomies.
	 */
	public function register() {
		register_taxonomy(
			'resource_category',
			'education_resource',
			array(
				'labels'            => array(
					'name'              => __( 'Categorías de Recursos', 'education-resources-manager' ),
					'singular_name'     => __( 'Categoría', 'education-resources-manager' ),
					'search_items'      => __( 'Buscar Categorías', 'education-resources-manager' ),
					'all_items'         => __( 'Todas las Categorías', 'education-resources-manager' ),
					'parent_item'       => __( 'Categoría padre', 'education-resources-manager' ),
					'parent_item_colon' => __( 'Categoría padre:', 'education-resources-manager' ),
					'edit_item'         => __( 'Editar Categoría', 'education-resources-manager' ),
					'update_item'       => __( 'Actualizar Categoría', 'education-resources-manager' ),
					'add_new_item'      => __( 'Añadir Nueva Categoría', 'education-resources-manager' ),
					'new_item_name'     => __( 'Nueva Categoría', 'education-resources-manager' ),
					'menu_name'         => __( 'Categorías', 'education-resources-manager' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'categoria-recurso' ),
			)
		);

		register_taxonomy(
			'skill_tag',
			'education_resource',
			array(
				'labels'            => array(
					'name'                       => __( 'Etiquetas de Habilidades', 'education-resources-manager' ),
					'singular_name'              => __( 'Habilidad', 'education-resources-manager' ),
					'search_items'               => __( 'Buscar Habilidades', 'education-resources-manager' ),
					'popular_items'              => __( 'Habilidades Populares', 'education-resources-manager' ),
					'all_items'                  => __( 'Todas las Habilidades', 'education-resources-manager' ),
					'edit_item'                  => __( 'Editar Habilidad', 'education-resources-manager' ),
					'update_item'                => __( 'Actualizar Habilidad', 'education-resources-manager' ),
					'add_new_item'               => __( 'Añadir Nueva Habilidad', 'education-resources-manager' ),
					'new_item_name'              => __( 'Nueva Habilidad', 'education-resources-manager' ),
					'separate_items_with_commas' => __( 'Separar habilidades con comas', 'education-resources-manager' ),
					'add_or_remove_items'        => __( 'Añadir o quitar habilidades', 'education-resources-manager' ),
					'choose_from_most_used'      => __( 'Elegir de las más usadas', 'education-resources-manager' ),
					'menu_name'                  => __( 'Habilidades', 'education-resources-manager' ),
				),
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'habilidad' ),
			)
		);
	}
}
