<?php
/**
 * Shortcode list template.
 *
 * @package Education_Resources_Manager
 *
 * @var array<string, mixed> $atts Shortcode attributes.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div id="erm-resources-container" class="erm-container"
	data-per-page="<?php echo esc_attr( $atts['per_page'] ); ?>"
	data-type="<?php echo esc_attr( $atts['type'] ); ?>"
	data-difficulty="<?php echo esc_attr( $atts['difficulty'] ); ?>"
	data-category="<?php echo esc_attr( $atts['category'] ); ?>">

	<?php if ( ! empty( $atts['title'] ) ) : ?>
		<h2 class="erm-title"><?php echo esc_html( $atts['title'] ); ?></h2>
	<?php endif; ?>

	<?php if ( 'yes' === $atts['show_filters'] ) : ?>
		<div class="erm-filters" role="search" aria-label="<?php esc_attr_e( 'Filtros de recursos', 'education-resources-manager' ); ?>">

			<div class="erm-filter-group">
				<label for="erm-search"><?php esc_html_e( 'Buscar', 'education-resources-manager' ); ?></label>
				<input type="text" id="erm-search" class="erm-filter-input"
					placeholder="<?php esc_attr_e( 'Buscar recursos...', 'education-resources-manager' ); ?>"
					aria-label="<?php esc_attr_e( 'Buscar recursos por texto', 'education-resources-manager' ); ?>">
			</div>

			<div class="erm-filter-group">
				<label for="erm-type"><?php esc_html_e( 'Tipo', 'education-resources-manager' ); ?></label>
				<select id="erm-type" class="erm-filter-select" aria-label="<?php esc_attr_e( 'Filtrar por tipo', 'education-resources-manager' ); ?>">
					<option value=""><?php esc_html_e( 'Todos los tipos', 'education-resources-manager' ); ?></option>
					<option value="course" <?php selected( $atts['type'], 'course' ); ?>><?php esc_html_e( 'Curso', 'education-resources-manager' ); ?></option>
					<option value="tutorial" <?php selected( $atts['type'], 'tutorial' ); ?>><?php esc_html_e( 'Tutorial', 'education-resources-manager' ); ?></option>
					<option value="ebook" <?php selected( $atts['type'], 'ebook' ); ?>><?php esc_html_e( 'Ebook', 'education-resources-manager' ); ?></option>
					<option value="video" <?php selected( $atts['type'], 'video' ); ?>><?php esc_html_e( 'Video', 'education-resources-manager' ); ?></option>
				</select>
			</div>

			<div class="erm-filter-group">
				<label for="erm-difficulty"><?php esc_html_e( 'Nivel', 'education-resources-manager' ); ?></label>
				<select id="erm-difficulty" class="erm-filter-select" aria-label="<?php esc_attr_e( 'Filtrar por nivel', 'education-resources-manager' ); ?>">
					<option value=""><?php esc_html_e( 'Todos los niveles', 'education-resources-manager' ); ?></option>
					<option value="beginner" <?php selected( $atts['difficulty'], 'beginner' ); ?>><?php esc_html_e( 'Principiante', 'education-resources-manager' ); ?></option>
					<option value="intermediate" <?php selected( $atts['difficulty'], 'intermediate' ); ?>><?php esc_html_e( 'Intermedio', 'education-resources-manager' ); ?></option>
					<option value="advanced" <?php selected( $atts['difficulty'], 'advanced' ); ?>><?php esc_html_e( 'Avanzado', 'education-resources-manager' ); ?></option>
				</select>
			</div>

			<button type="button" id="erm-clear-filters" class="erm-btn erm-btn-secondary">
				<?php esc_html_e( 'Limpiar filtros', 'education-resources-manager' ); ?>
			</button>

		</div>
	<?php endif; ?>

	<div id="erm-loading" class="erm-loading" aria-live="polite" style="display:none;">
		<span class="erm-spinner" aria-hidden="true"></span>
		<span><?php esc_html_e( 'Cargando recursos...', 'education-resources-manager' ); ?></span>
	</div>

	<div id="erm-resources-grid" class="erm-grid" role="list" aria-label="<?php esc_attr_e( 'Lista de recursos educativos', 'education-resources-manager' ); ?>">
	</div>

	<div id="erm-pagination" class="erm-pagination" aria-label="<?php esc_attr_e( 'Paginación', 'education-resources-manager' ); ?>">
	</div>

</div>
