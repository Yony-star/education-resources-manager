# AGENT 08 — WordPress REST API Correction Agent
## Education Resources Manager — Correcciones Post-Auditoría

---

## 🎯 Misión de Este Agente

Eres un agente especializado en **desarrollo WordPress Senior** con foco en REST API, `WP_Query`, y estándares de WordPress Coding Standards. Tu misión es corregir los 3 problemas detectados en la auditoría del plugin `education-resources-manager`, en el archivo `includes/class-erm-rest-api.php` y su documentación asociada.

**No toques lo que ya funciona.** Corrige solo lo que está marcado con ⚠️.

---

## 📋 Reporte de Auditoría — Problemas a Corregir

| # | Problema | Archivo | Severidad |
|---|----------|---------|-----------|
| 1 | `orderby=views` cae silenciosamente a `date`, sin ordenar por vistas reales | `class-erm-rest-api.php` | Media |
| 2 | Parámetro `period` en `/stats` se valida pero no filtra los agregados | `class-erm-rest-api.php` + `class-erm-database.php` | Media |
| 3 | Respuesta de `/stats` no incluye `by_difficulty` a nivel raíz (esperado por el evaluador) | `class-erm-rest-api.php` + `class-erm-database.php` | Baja |

---

## 🔍 Paso 0 — Lectura Obligatoria Antes de Tocar Código

Antes de cualquier cambio, ejecuta en Cursor:

```
@file includes/class-erm-rest-api.php
@file includes/class-erm-database.php
@file docs/API.md
```

Lee completo cada archivo. Mapea mentalmente:
- El método `get_resources()` y cómo construye `$query_args`
- El método `get_stats()` y qué datos obtiene de `ERM_Database`
- El método `get_stats_summary()` en `ERM_Database` y qué devuelve

---

## 🔧 CORRECCIÓN 1 — `orderby=views` funcional

### Problema raíz

El código actual hace esto:

```php
// ❌ CÓDIGO ACTUAL (INCORRECTO)
'orderby' => $request->get_param('orderby') !== 'views' 
    ? $request->get_param('orderby') 
    : 'date',  // ← vistas → cae a date sin avisar
```

`WP_Query` no puede ordenar por vistas nativamente porque las vistas están en la tabla `wp_erm_tracking`, no en `wp_posts`. La solución correcta es una query personalizada cuando se pide `orderby=views`.

### Solución — Estrategia de dos caminos

**Camino A** (orderby ≠ views): usar `WP_Query` directamente (comportamiento actual correcto).

**Camino B** (orderby = views): obtener los IDs ordenados por vistas desde `erm_tracking` y pasarlos a `WP_Query` via `post__in` + `orderby=post__in`.

### Implementación

#### En `includes/class-erm-rest-api.php`, método `get_resources()`:

Reemplaza el bloque de construcción de `$query_args` con:

```php
public function get_resources( WP_REST_Request $request ) {
	$page      = $request->get_param( 'page' );
	$per_page  = $request->get_param( 'per_page' );
	$orderby   = $request->get_param( 'orderby' );
	$order     = strtoupper( $request->get_param( 'order' ) );

	// Camino B: ordenar por vistas reales
	if ( 'views' === $orderby ) {
		return $this->get_resources_ordered_by_views( $request );
	}

	// Camino A: WP_Query estándar
	$query_args = array(
		'post_type'      => 'education_resource',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'orderby'        => $orderby,
		'order'          => $order,
	);

	// ... (resto del método igual: meta_query, tax_query, search)
	// ... NO cambies nada más de get_resources()
}
```

#### Añade el nuevo método privado `get_resources_ordered_by_views()`:

```php
/**
 * Obtiene recursos ordenados por número real de visualizaciones.
 * Usa una query directa a erm_tracking para obtener los IDs ordenados,
 * luego los hidrata vía WP_Query preservando paginación correcta.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
private function get_resources_ordered_by_views( WP_REST_Request $request ) {
	global $wpdb;

	$page     = $request->get_param( 'page' );
	$per_page = $request->get_param( 'per_page' );
	$order    = strtoupper( $request->get_param( 'order' ) );
	$order    = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

	$table_name = $wpdb->prefix . 'erm_tracking';

	// Paso 1: obtener todos los IDs publicados del CPT
	// (necesitamos el universo completo para aplicar filtros antes de ordenar)
	$base_query_args = array(
		'post_type'              => 'education_resource',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,  // Todos para poder ordenar por vistas
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	// Aplicar los mismos filtros de meta y taxonomy que en get_resources()
	$meta_query = array();
	if ( $type = $request->get_param( 'type' ) ) {
		$meta_query[] = array(
			'key'     => '_erm_resource_type',
			'value'   => $type,
			'compare' => '=',
		);
	}
	if ( $difficulty = $request->get_param( 'difficulty' ) ) {
		$meta_query[] = array(
			'key'     => '_erm_difficulty_level',
			'value'   => $difficulty,
			'compare' => '=',
		);
	}
	if ( ! empty( $meta_query ) ) {
		$base_query_args['meta_query'] = $meta_query;
	}

	$tax_query = array();
	if ( $category = $request->get_param( 'category' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'resource_category',
			'field'    => 'slug',
			'terms'    => $category,
		);
	}
	if ( $skill = $request->get_param( 'skill' ) ) {
		$tax_query[] = array(
			'taxonomy' => 'skill_tag',
			'field'    => 'slug',
			'terms'    => $skill,
		);
	}
	if ( ! empty( $tax_query ) ) {
		$base_query_args['tax_query'] = $tax_query;
	}

	if ( $search = $request->get_param( 'search' ) ) {
		$base_query_args['s'] = $search;
	}

	// Obtener IDs filtrados
	$filtered_ids = get_posts( $base_query_args );

	if ( empty( $filtered_ids ) ) {
		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'resources'  => array(),
				'pagination' => array(
					'total'        => 0,
					'total_pages'  => 0,
					'current_page' => $page,
					'per_page'     => $per_page,
					'has_more'     => false,
				),
			),
		) );
	}

	// Paso 2: obtener conteo de vistas para esos IDs y ordenar
	$ids_placeholder = implode( ',', array_map( 'absint', $filtered_ids ) );

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs saneados con absint
	$views_data = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT resource_id, COUNT(*) as view_count
			FROM {$table_name}
			WHERE resource_id IN ({$ids_placeholder})
			AND action_type = %s
			GROUP BY resource_id
			ORDER BY view_count {$order}",
			'view'
		)
	);

	// Construir mapa id => vistas
	$views_map = array();
	foreach ( $views_data as $row ) {
		$views_map[ (int) $row->resource_id ] = (int) $row->view_count;
	}

	// Ordenar todos los IDs: primero los que tienen vistas (ordenados), luego los que tienen 0
	$ids_with_views    = array_column( $views_data, 'resource_id' );
	$ids_without_views = array_diff( $filtered_ids, $ids_with_views );

	if ( 'ASC' === $order ) {
		// Con vistas ya vienen ASC del SQL; sin vistas van al final
		$sorted_ids = array_merge(
			array_map( 'intval', $ids_with_views ),
			array_map( 'intval', $ids_without_views )
		);
	} else {
		// DESC: los sin vistas (0) van al final también
		$sorted_ids = array_merge(
			array_map( 'intval', $ids_with_views ),
			array_map( 'intval', $ids_without_views )
		);
	}

	// Paso 3: paginación manual sobre el array ordenado
	$total        = count( $sorted_ids );
	$total_pages  = (int) ceil( $total / $per_page );
	$offset       = ( $page - 1 ) * $per_page;
	$page_ids     = array_slice( $sorted_ids, $offset, $per_page );

	if ( empty( $page_ids ) ) {
		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'resources'  => array(),
				'pagination' => array(
					'total'        => $total,
					'total_pages'  => $total_pages,
					'current_page' => $page,
					'per_page'     => $per_page,
					'has_more'     => $page < $total_pages,
				),
			),
		) );
	}

	// Paso 4: hidratar posts en el orden correcto
	$posts_query = new WP_Query( array(
		'post_type'              => 'education_resource',
		'post_status'            => 'publish',
		'post__in'               => $page_ids,
		'orderby'                => 'post__in',  // Preserva el orden del array
		'posts_per_page'         => $per_page,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	) );

	$resources = array();
	$db        = new ERM_Database();

	foreach ( $posts_query->posts as $post ) {
		$resource            = $this->format_resource( $post, $db, false );
		// Inyectar el conteo de vistas ya calculado (evita N+1 queries)
		$resource['views']   = $views_map[ $post->ID ] ?? 0;
		$resources[]         = $resource;
	}

	return rest_ensure_response( array(
		'success' => true,
		'data'    => array(
			'resources'  => $resources,
			'pagination' => array(
				'total'        => $total,
				'total_pages'  => $total_pages,
				'current_page' => $page,
				'per_page'     => $per_page,
				'has_more'     => $page < $total_pages,
			),
		),
	) );
}
```

### Verificación de Corrección 1

```bash
# Crea 3 recursos y registra vistas distintas en la BD, luego:
curl "http://localhost/wp-json/erm/v1/resources?orderby=views&order=DESC"
# El primer resultado debe tener el mayor número de 'views'

curl "http://localhost/wp-json/erm/v1/resources?orderby=views&order=ASC"
# El primer resultado debe tener el menor número de 'views'

curl "http://localhost/wp-json/erm/v1/resources?orderby=views&type=course"
# Debe filtrar por tipo Y ordenar por vistas
```

---

## 🔧 CORRECCIÓN 2 — Parámetro `period` en `/stats` que realmente filtra

### Problema raíz

El código actual hace:

```php
// ❌ CÓDIGO ACTUAL (INCORRECTO)
public function get_stats( WP_REST_Request $request ) {
	$period = $request->get_param( 'period' ); // Se lee pero nunca se usa
	$db     = new ERM_Database();
	$stats  = $db->get_stats_summary();        // Siempre devuelve todo, ignora $period
	// ...
}
```

### Solución — Dos cambios coordinados

#### Cambio A: `includes/class-erm-database.php`

Modifica `get_stats_summary()` para aceptar un parámetro `$period`:

```php
/**
 * Obtiene el resumen de estadísticas del plugin.
 *
 * @param string $period Periodo a filtrar: 'all' | 'month' | 'week'. Default 'all'.
 * @return array {
 *     @type int   total_resources Número de recursos publicados.
 *     @type array by_type         Conteo por tipo de recurso.
 *     @type array by_difficulty   Conteo por nivel de dificultad.
 *     @type int   total_views     Total de visualizaciones en el periodo.
 *     @type int   total_downloads Total de descargas en el periodo.
 *     @type int   unique_users    Usuarios únicos que interactuaron.
 * }
 */
public function get_stats_summary( $period = 'all' ) {
	global $wpdb;

	// Validar periodo
	$allowed_periods = array( 'all', 'month', 'week' );
	$period          = in_array( $period, $allowed_periods, true ) ? $period : 'all';

	// Cache key incluye el periodo para no mezclar resultados
	$cache_key = 'erm_stats_summary_' . $period;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// Construir cláusula WHERE de fecha para la tabla de tracking
	$date_where = '';
	switch ( $period ) {
		case 'week':
			$date_where = $wpdb->prepare(
				' AND action_date >= %s',
				gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			);
			break;
		case 'month':
			$date_where = $wpdb->prepare(
				' AND action_date >= %s',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			);
			break;
		case 'all':
		default:
			$date_where = '';
			break;
	}

	// Total de recursos (no cambia con el periodo — son recursos publicados, no tracking)
	$total_resources = (int) wp_count_posts( 'education_resource' )->publish;

	// Recursos por tipo
	$by_type_raw = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm.meta_value AS type, COUNT(*) AS total
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = %s
			GROUP BY pm.meta_value",
			'education_resource',
			'publish',
			'_erm_resource_type'
		)
	);

	// Convertir a objeto clave => valor para consistencia
	$by_type = array();
	foreach ( $by_type_raw as $row ) {
		$by_type[ $row->type ] = (int) $row->total;
	}
	// Garantizar que todos los tipos estén presentes (aunque sea con 0)
	foreach ( array( 'course', 'tutorial', 'ebook', 'video' ) as $type ) {
		if ( ! isset( $by_type[ $type ] ) ) {
			$by_type[ $type ] = 0;
		}
	}

	// Recursos por dificultad
	$by_difficulty_raw = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT pm.meta_value AS difficulty, COUNT(*) AS total
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = %s
			GROUP BY pm.meta_value",
			'education_resource',
			'publish',
			'_erm_difficulty_level'
		)
	);

	$by_difficulty = array();
	foreach ( $by_difficulty_raw as $row ) {
		$by_difficulty[ $row->difficulty ] = (int) $row->total;
	}
	// Garantizar que todos los niveles estén presentes
	foreach ( array( 'beginner', 'intermediate', 'advanced' ) as $level ) {
		if ( ! isset( $by_difficulty[ $level ] ) ) {
			$by_difficulty[ $level ] = 0;
		}
	}

	// Totales de tracking filtrados por periodo
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $date_where ya preparado
	$totals = $wpdb->get_row(
		"SELECT
			SUM(CASE WHEN action_type = 'view' THEN 1 ELSE 0 END) AS total_views,
			SUM(CASE WHEN action_type = 'download' THEN 1 ELSE 0 END) AS total_downloads,
			COUNT(DISTINCT user_id) AS unique_users
		FROM {$this->table_name}
		WHERE 1=1 {$date_where}"
	);

	$stats = array(
		'total_resources' => $total_resources,
		'by_type'         => $by_type,
		'by_difficulty'   => $by_difficulty,
		'total_views'     => (int) ( $totals->total_views ?? 0 ),
		'total_downloads' => (int) ( $totals->total_downloads ?? 0 ),
		'unique_users'    => (int) ( $totals->unique_users ?? 0 ),
		'period'          => $period,  // Incluir en respuesta para transparencia
	);

	set_transient( $cache_key, $stats, HOUR_IN_SECONDS );
	return $stats;
}
```

**Importante:** después de este cambio, en `ERM_Database::invalidate_cache()` añade las 3 variantes:

```php
public function invalidate_cache() {
	delete_transient( 'erm_stats_summary_all' );
	delete_transient( 'erm_stats_summary_month' );
	delete_transient( 'erm_stats_summary_week' );
	delete_transient( 'erm_top_resources_view_5' );
	delete_transient( 'erm_top_resources_download_5' );
}
```

#### Cambio B: `includes/class-erm-rest-api.php`

Pasa el `$period` a `get_stats_summary()`:

```php
public function get_stats( WP_REST_Request $request ) {
	$period = $request->get_param( 'period' ); // 'all' | 'month' | 'week'

	$db      = new ERM_Database();
	$summary = $db->get_stats_summary( $period ); // ← pasar el periodo
	$top     = $db->get_top_resources( 5 );
	$monthly = $db->get_monthly_stats( 6 );

	return rest_ensure_response( array(
		'success' => true,
		'data'    => array(
			'summary'        => $summary,
			'top_resources'  => $top,
			'monthly_growth' => $monthly,
		),
	) );
}
```

### Verificación de Corrección 2

```bash
# Sin filtro (all por defecto)
curl "http://localhost/wp-json/erm/v1/stats" --user "admin:APP_PASSWORD"
# Respuesta debe incluir "period": "all" en summary

# Solo último mes
curl "http://localhost/wp-json/erm/v1/stats?period=month" --user "admin:APP_PASSWORD"
# total_views/total_downloads deben reflejar solo los últimos 30 días

# Solo última semana
curl "http://localhost/wp-json/erm/v1/stats?period=week" --user "admin:APP_PASSWORD"
# total_views/total_downloads deben ser menores o iguales a los de month
```

---

## 🔧 CORRECCIÓN 3 — `by_difficulty` en la respuesta de `/stats`

### Problema raíz

La plantilla del evaluador espera `by_difficulty` a nivel raíz del objeto `data`. La respuesta actual solo tiene `summary.by_type` pero no expone `by_difficulty` a nivel raíz del `data`.

El API template muestra esta estructura esperada:

```json
{
  "data": {
    "summary": { ... },
    "by_type": { "course": 45, "tutorial": 60 },
    "by_difficulty": { "beginner": 70, "intermediate": 55, "advanced": 25 },
    "top_resources": [ ... ],
    "monthly_growth": [ ... ]
  }
}
```

### Solución — Solo en `class-erm-rest-api.php`

Expande la respuesta de `get_stats()` para exponer `by_type` y `by_difficulty` **tanto dentro de `summary` como a nivel raíz de `data`**:

```php
public function get_stats( WP_REST_Request $request ) {
	$period = $request->get_param( 'period' );

	$db      = new ERM_Database();
	$summary = $db->get_stats_summary( $period );
	$top     = $db->get_top_resources( 5 );
	$monthly = $db->get_monthly_stats( 6 );

	return rest_ensure_response( array(
		'success' => true,
		'data'    => array(
			// summary completo (incluye by_type, by_difficulty, totales, period)
			'summary'        => $summary,

			// Expuestos también a nivel raíz de data para compatibilidad con la plantilla
			'by_type'        => $summary['by_type'],
			'by_difficulty'  => $summary['by_difficulty'],

			// Top recursos y crecimiento mensual
			'top_resources'  => $top,
			'monthly_growth' => $monthly,
		),
	) );
}
```

### Verificación de Corrección 3

```bash
curl "http://localhost/wp-json/erm/v1/stats" --user "admin:APP_PASSWORD" | python3 -m json.tool
```

La respuesta debe tener esta estructura exacta:

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_resources": 12,
      "by_type": { "course": 5, "tutorial": 4, "ebook": 2, "video": 1 },
      "by_difficulty": { "beginner": 6, "intermediate": 4, "advanced": 2 },
      "total_views": 350,
      "total_downloads": 89,
      "unique_users": 45,
      "period": "all"
    },
    "by_type": { "course": 5, "tutorial": 4, "ebook": 2, "video": 1 },
    "by_difficulty": { "beginner": 6, "intermediate": 4, "advanced": 2 },
    "top_resources": [ ... ],
    "monthly_growth": [ ... ]
  }
}
```

---

## 📝 Actualizar `docs/API.md`

Después de aplicar las 3 correcciones, actualiza la documentación. Usa `@file docs/API.md` en Cursor y haz los siguientes cambios puntuales:

### Sección: parámetro `orderby` en `GET /resources`

Reemplaza la nota de advertencia actual por:

```markdown
| orderby | string | No | date | Ordenar por: `date`, `title`, `views`. Cuando se usa `views`, 
se ejecuta una query optimizada contra la tabla `erm_tracking` que 
obtiene los IDs ordenados por conteo real antes de hidratar los posts. |
```

### Sección: parámetro `period` en `GET /stats`

Reemplaza la nota de advertencia por:

```markdown
| period | string | No | all | Filtra los conteos de tracking (`total_views`, `total_downloads`, 
`unique_users`) al periodo indicado: `all` (todo el tiempo), `month` (últimos 30 días), 
`week` (últimos 7 días). El conteo de recursos (`total_resources`, `by_type`, `by_difficulty`) 
no se filtra por periodo ya que corresponde al inventario publicado, no a la actividad. |
```

### Sección: respuesta de `GET /stats`

Actualiza el JSON de ejemplo para incluir `by_difficulty` a nivel raíz:

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_resources": 150,
      "by_type": { "course": 45, "tutorial": 60, "ebook": 30, "video": 15 },
      "by_difficulty": { "beginner": 70, "intermediate": 55, "advanced": 25 },
      "total_views": 12453,
      "total_downloads": 3421,
      "unique_users": 892,
      "period": "all"
    },
    "by_type": { "course": 45, "tutorial": 60, "ebook": 30, "video": 15 },
    "by_difficulty": { "beginner": 70, "intermediate": 55, "advanced": 25 },
    "top_resources": [ ... ],
    "monthly_growth": [ ... ]
  }
}
```

---

## ✅ Checklist de Verificación Final

Ejecuta este checklist completo después de aplicar las 3 correcciones:

### Corrección 1 — orderby=views
- [ ] `GET /resources?orderby=views&order=DESC` devuelve recursos de mayor a menor vistas
- [ ] `GET /resources?orderby=views&order=ASC` devuelve recursos de menor a mayor vistas
- [ ] `GET /resources?orderby=views&type=course` combina filtro + ordenamiento por vistas
- [ ] `GET /resources?orderby=date` sigue funcionando como antes
- [ ] `GET /resources?orderby=title` sigue funcionando como antes
- [ ] Los recursos con 0 vistas aparecen al final en DESC, al principio en ASC
- [ ] La paginación funciona correctamente con `orderby=views`

### Corrección 2 — period en /stats
- [ ] `GET /stats` (sin `period`) devuelve totales de todo el tiempo
- [ ] `GET /stats?period=all` devuelve totales de todo el tiempo
- [ ] `GET /stats?period=month` devuelve totales de los últimos 30 días
- [ ] `GET /stats?period=week` devuelve totales de los últimos 7 días
- [ ] `total_views` con `period=week` ≤ `total_views` con `period=month`
- [ ] `total_resources`, `by_type`, `by_difficulty` NO cambian con el periodo (son inventario)
- [ ] La clave `"period"` aparece dentro de `summary` con el valor correcto
- [ ] `GET /stats?period=invalid` retorna error 400

### Corrección 3 — by_difficulty en respuesta
- [ ] La respuesta de `/stats` tiene `data.by_difficulty` a nivel raíz
- [ ] La respuesta de `/stats` tiene `data.by_type` a nivel raíz
- [ ] `data.by_difficulty` contiene las 3 claves: `beginner`, `intermediate`, `advanced`
- [ ] Los valores de `data.by_difficulty` coinciden con `data.summary.by_difficulty`
- [ ] La respuesta de `/stats` tiene `data.summary.by_difficulty` también (no eliminarlo)

### Regresión — nada roto
- [ ] `GET /resources` sin parámetros sigue funcionando
- [ ] `GET /resources/{id}` con ID válido devuelve 200
- [ ] `GET /resources/{id}` con ID inválido devuelve 404
- [ ] `POST /resources/{id}/track` sigue registrando en la BD
- [ ] El plugin se activa sin errores PHP

---

## 🧪 Script de Prueba Automático

Guarda esto como `test-api.sh` en la raíz del plugin y ejecútalo con `bash test-api.sh`:

```bash
#!/bin/bash
# Test rápido de los 3 fixes — ajusta BASE_URL y credenciales

BASE_URL="http://localhost"
ADMIN_USER="admin"
ADMIN_PASS="tu_application_password"

echo "=== TEST 1: orderby=views DESC ==="
curl -s "${BASE_URL}/wp-json/erm/v1/resources?orderby=views&order=DESC&per_page=3" \
  | python3 -c "import sys,json; d=json.load(sys.stdin); [print(f'  ID {r[\"id\"]}: {r[\"views\"]} vistas') for r in d['data']['resources']]"

echo ""
echo "=== TEST 2: period=month en /stats ==="
curl -s "${BASE_URL}/wp-json/erm/v1/stats?period=month" \
  --user "${ADMIN_USER}:${ADMIN_PASS}" \
  | python3 -c "import sys,json; d=json.load(sys.stdin); s=d['data']['summary']; print(f'  Period: {s[\"period\"]}, Views: {s[\"total_views\"]}')"

echo ""
echo "=== TEST 3: by_difficulty a nivel raíz ==="
curl -s "${BASE_URL}/wp-json/erm/v1/stats" \
  --user "${ADMIN_USER}:${ADMIN_PASS}" \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print('  by_difficulty presente:', 'by_difficulty' in d['data']); print('  Valores:', d['data'].get('by_difficulty', 'FALTA'))"

echo ""
echo "=== DONE ==="
```

---

## 🚨 Errores Comunes al Aplicar Estas Correcciones

### Error: "Indirect modification of overloaded property"
**Causa:** Intentar modificar el array de `$query_args` después de pasarlo a `WP_Query`.
**Solución:** Construir `$query_args` completo antes de instanciar `WP_Query`.

### Error: "WordPress database error: Table doesn't exist"
**Causa:** El prefijo de tabla en la query directa está hardcodeado.
**Solución:** Siempre usar `$wpdb->prefix . 'erm_tracking'` o `$this->table_name`.

### Error: El transient de `get_stats_summary_all` nunca expira
**Causa:** `ERM_Database::invalidate_cache()` no actualizado con las nuevas claves.
**Solución:** Asegurarse de que `invalidate_cache()` borra las 3 variantes (`_all`, `_month`, `_week`).

### Error: `by_difficulty` devuelve array vacío
**Causa:** Recursos creados sin el meta `_erm_difficulty_level`.
**Solución:** El código garantiza las 3 claves con valor 0 como fallback (verificar que el `foreach` de garantía esté presente).

### Error: La paginación con `orderby=views` está desincronizada
**Causa:** El offset se calcula sobre el total de IDs sin filtrar.
**Solución:** Verificar que `$filtered_ids` se calcula con los filtros aplicados **antes** de la paginación manual.

---

## 📌 Contexto para Pasar a Cursor al Inicio de Esta Sesión

```
Soy un agente de corrección WordPress. Lee estos archivos:
@file includes/class-erm-rest-api.php
@file includes/class-erm-database.php

Voy a aplicar 3 correcciones específicas descritas en AGENT_08_CORRECTIONS.md:
1. orderby=views usando query directa a erm_tracking + post__in
2. period en /stats que realmente filtra los totales de tracking
3. by_difficulty expuesto a nivel raíz de data en /stats

Aplica solo los cambios indicados. No refactorices nada más.
WordPress Coding Standards: tabs, not spaces. $wpdb->prepare() siempre.
```
