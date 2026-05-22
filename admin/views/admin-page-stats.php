<?php
/**
 * Admin statistics view.
 *
 * @package Education_Resources_Manager
 *
 * @var array    $stats   Stats summary from ERM_Database.
 * @var array    $top_5   Top resources by views.
 * @var array    $monthly Monthly creation stats.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$type_labels = array(
	'course'   => __( 'Curso', 'education-resources-manager' ),
	'tutorial' => __( 'Tutorial', 'education-resources-manager' ),
	'ebook'    => __( 'Ebook', 'education-resources-manager' ),
	'video'    => __( 'Video', 'education-resources-manager' ),
);

$total_by_type = 0;
if ( ! empty( $stats['by_type'] ) ) {
	foreach ( $stats['by_type'] as $row ) {
		$total_by_type += (int) $row->total;
	}
}
?>
<div class="wrap erm-admin-wrap">
	<h1><?php esc_html_e( 'Estadísticas', 'education-resources-manager' ); ?></h1>
	<hr class="wp-header-end">

	<div class="erm-stats-cards">
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-welcome-learn-more" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['total_resources'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total Recursos', 'education-resources-manager' ); ?></span>
		</div>
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-visibility" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['total_views'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total Vistas', 'education-resources-manager' ); ?></span>
		</div>
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-download" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['total_downloads'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Total Descargas', 'education-resources-manager' ); ?></span>
		</div>
		<div class="erm-stat-card">
			<span class="erm-stat-card__icon dashicons dashicons-groups" aria-hidden="true"></span>
			<span class="erm-stat-card__number"><?php echo esc_html( number_format_i18n( $stats['unique_users'] ) ); ?></span>
			<span class="erm-stat-card__label"><?php esc_html_e( 'Usuarios Únicos', 'education-resources-manager' ); ?></span>
		</div>
	</div>

	<div class="erm-stats-section">
		<h2><?php esc_html_e( 'Recursos por Tipo', 'education-resources-manager' ); ?></h2>
		<?php if ( ! empty( $stats['by_type'] ) ) : ?>
			<table class="widefat striped erm-type-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Tipo', 'education-resources-manager' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Cantidad', 'education-resources-manager' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Porcentaje', 'education-resources-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $stats['by_type'] as $row ) : ?>
						<?php
						$type_slug  = $row->type;
						$type_label = isset( $type_labels[ $type_slug ] ) ? $type_labels[ $type_slug ] : $type_slug;
						$count      = (int) $row->total;
						$percent    = $total_by_type > 0 ? round( ( $count / $total_by_type ) * 100, 1 ) : 0;
						?>
						<tr>
							<td>
								<span class="erm-type-badge erm-type-badge--<?php echo esc_attr( $type_slug ); ?>">
									<?php echo esc_html( $type_label ); ?>
								</span>
							</td>
							<td><?php echo esc_html( number_format_i18n( $count ) ); ?></td>
							<td>
								<?php echo esc_html( $percent ); ?>%
								<span class="erm-percent-bar" style="width: <?php echo esc_attr( min( 100, $percent ) ); ?>%;" aria-hidden="true"></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p class="erm-empty-message"><?php esc_html_e( 'No hay recursos publicados con tipo asignado.', 'education-resources-manager' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="erm-stats-section">
		<h2><?php esc_html_e( 'Top 5 Recursos Más Vistos', 'education-resources-manager' ); ?></h2>
		<?php if ( ! empty( $top_5 ) ) : ?>
			<ol class="erm-top-list">
				<?php foreach ( $top_5 as $item ) : ?>
					<li>
						<span class="erm-top-list__title">
							<a href="<?php echo esc_url( get_edit_post_link( $item->resource_id ) ); ?>">
								<?php echo esc_html( $item->post_title ); ?>
							</a>
						</span>
						<span class="erm-badge">
							<?php
							/* translators: %s: view count */
							echo esc_html( sprintf( __( '%s vistas', 'education-resources-manager' ), number_format_i18n( $item->action_count ) ) );
							?>
						</span>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p class="erm-empty-message"><?php esc_html_e( 'Aún no hay datos de visualización.', 'education-resources-manager' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="erm-stats-section">
		<h2><?php esc_html_e( 'Recursos Creados por Mes (últimos 6 meses)', 'education-resources-manager' ); ?></h2>
		<canvas id="erm-monthly-chart" width="700" height="300"
			aria-label="<?php esc_attr_e( 'Gráfico de recursos por mes', 'education-resources-manager' ); ?>"
			role="img">
		</canvas>
		<script>
			var ermMonthlyData = <?php echo wp_json_encode( $monthly ); ?>;
		</script>
	</div>
</div>
