<?php
/**
 * Admin main resources list view.
 *
 * @package Education_Resources_Manager
 *
 * @var WP_Query           $query         Resources query.
 * @var ERM_Database       $db            Database instance.
 * @var array              $stats_summary Stats summary.
 * @var string             $filter_type   Type filter.
 * @var string             $filter_difficulty Difficulty filter.
 * @var string             $filter_category Category slug filter.
 * @var string             $search        Search string.
 * @var int                $paged         Current page.
 * @var ERM_Admin          $this          Admin instance.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$type_options = array(
	'course'   => __( 'Curso', 'education-resources-manager' ),
	'tutorial' => __( 'Tutorial', 'education-resources-manager' ),
	'ebook'    => __( 'Ebook', 'education-resources-manager' ),
	'video'    => __( 'Video', 'education-resources-manager' ),
);

$difficulty_options = array(
	'beginner'     => __( 'Principiante', 'education-resources-manager' ),
	'intermediate' => __( 'Intermedio', 'education-resources-manager' ),
	'advanced'     => __( 'Avanzado', 'education-resources-manager' ),
);

$pagination_args = array(
	'base'      => add_query_arg( 'paged', '%#%' ),
	'format'    => '',
	'prev_text' => '&laquo;',
	'next_text' => '&raquo;',
	'total'     => $query->max_num_pages,
	'current'   => max( 1, $paged ),
	'add_args'  => array_filter(
		array(
			'page'           => 'erm-resources',
			'erm_type'       => $filter_type,
			'erm_difficulty' => $filter_difficulty,
			'erm_category'   => $filter_category,
			's'              => $search,
		)
	),
);
?>
<div class="wrap erm-admin-wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Recursos Educativos', 'education-resources-manager' ); ?>
	</h1>
	<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=education_resource' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Añadir Nuevo', 'education-resources-manager' ); ?>
	</a>
	<hr class="wp-header-end">

	<form method="get" action="" class="erm-admin-filters">
		<input type="hidden" name="page" value="erm-resources">

		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
			placeholder="<?php esc_attr_e( 'Buscar recursos...', 'education-resources-manager' ); ?>"
			class="erm-admin-filters__search">

		<select name="erm_type" class="erm-admin-filters__select">
			<option value=""><?php esc_html_e( 'Todos los tipos', 'education-resources-manager' ); ?></option>
			<?php foreach ( $type_options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter_type, $val ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="erm_difficulty" class="erm-admin-filters__select">
			<option value=""><?php esc_html_e( 'Todos los niveles', 'education-resources-manager' ); ?></option>
			<?php foreach ( $difficulty_options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $filter_difficulty, $val ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php submit_button( __( 'Filtrar', 'education-resources-manager' ), 'secondary', 'submit', false ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=erm-resources' ) ); ?>" class="button">
			<?php esc_html_e( 'Limpiar', 'education-resources-manager' ); ?>
		</a>
	</form>

	<div class="erm-stats-quick erm-stats-cards">
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats_summary['total_resources'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total recursos', 'education-resources-manager' ); ?></span>
		</div>
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-visibility" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats_summary['total_views'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total vistas', 'education-resources-manager' ); ?></span>
		</div>
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-download" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats_summary['total_downloads'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total descargas', 'education-resources-manager' ); ?></span>
		</div>
	</div>

	<?php if ( $query->have_posts() ) : ?>
		<table class="wp-list-table widefat fixed striped erm-resources-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Título', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Tipo', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Nivel', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Instructor', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Precio', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Visualizaciones', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Fecha', 'education-resources-manager' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Acciones', 'education-resources-manager' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id      = get_the_ID();
					$type         = get_post_meta( $post_id, '_erm_resource_type', true );
					$difficulty   = get_post_meta( $post_id, '_erm_difficulty_level', true );
					$instructor   = get_post_meta( $post_id, '_erm_instructor', true );
					$price        = floatval( get_post_meta( $post_id, '_erm_price', true ) );
					$views        = $db->get_resource_views( $post_id );
					$type_class   = $type ? ' erm-type-badge--' . esc_attr( $type ) : '';
					$level_class  = $difficulty ? ' erm-level-badge--' . esc_attr( $difficulty ) : '';
					?>
					<tr>
						<td>
							<strong>
								<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
									<?php the_title(); ?>
								</a>
							</strong>
							<?php
							$status_obj = get_post_status_object( get_post_status( $post_id ) );
							if ( 'publish' !== get_post_status( $post_id ) && $status_obj ) :
								?>
								<span class="erm-post-status"> — <?php echo esc_html( $status_obj->label ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $type ) : ?>
								<span class="erm-type-badge<?php echo esc_attr( $type_class ); ?>">
									<?php echo esc_html( $this->get_type_label( $type ) ); ?>
								</span>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $difficulty ) : ?>
								<span class="erm-level-badge<?php echo esc_attr( $level_class ); ?>">
									<?php echo esc_html( $this->get_difficulty_label( $difficulty ) ); ?>
								</span>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><?php echo $instructor ? esc_html( $instructor ) : '—'; ?></td>
						<td>
							<?php
							if ( $price > 0 ) {
								echo esc_html( number_format_i18n( $price, 2 ) );
							} else {
								esc_html_e( 'Gratuito', 'education-resources-manager' );
							}
							?>
						</td>
						<td><?php echo esc_html( number_format_i18n( $views ) ); ?></td>
						<td><?php echo esc_html( get_the_date() ); ?></td>
						<td class="erm-actions-cell">
							<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Editar', 'education-resources-manager' ); ?>
							</a>
							<?php if ( 'publish' === get_post_status( $post_id ) ) : ?>
								<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="button button-small" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Ver', 'education-resources-manager' ); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<?php
		$pagination = paginate_links( $pagination_args );
		if ( $pagination ) :
			?>
			<div class="tablenav bottom">
				<div class="tablenav-pages erm-pagination">
					<?php echo wp_kses_post( $pagination ); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<div class="erm-no-resources">
			<p><?php esc_html_e( 'No se encontraron recursos con los filtros aplicados.', 'education-resources-manager' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=education_resource' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Crear primer recurso', 'education-resources-manager' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>
