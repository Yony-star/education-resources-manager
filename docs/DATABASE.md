# Base de datos — Education Resources Manager

## 1. Diagrama entidad-relación (ASCII)

```
┌─────────────────────────┐
│       wp_posts          │
│  ID (PK)                │
│  post_type =            │
│    'education_resource' │
│  post_title, content... │
└───────────┬─────────────┘
            │ 1
            │
            │ N
┌───────────▼─────────────┐       ┌─────────────────────────┐
│      wp_postmeta        │       │   wp_term_relationships │
│  post_id (FK)           │       │  object_id = post.ID    │
│  meta_key:              │       │  term_taxonomy_id       │
│   _erm_resource_type    │       └───────────┬─────────────┘
│   _erm_difficulty_level │                   │
│   _erm_duration_minutes │                   │ N
│   _erm_resource_url     │       ┌───────────▼─────────────┐
│   _erm_instructor       │       │   wp_term_taxonomy      │
│   _erm_price            │       │  taxonomy:              │
└─────────────────────────┘       │   resource_category   │
                                    │   skill_tag           │
                                    └───────────┬─────────────┘
                                                │ N
                                    ┌───────────▼─────────────┐
                                    │       wp_terms          │
                                    │  term_id, slug, name    │
                                    └─────────────────────────┘

┌─────────────────────────┐
│  {prefix}_erm_tracking  │  ← tabla custom del plugin
│  id (PK)                │
│  resource_id ───────────┼──► wp_posts.ID (lógica, sin FK DB)
│  user_id ───────────────┼──► wp_users.ID (nullable)
│  action_type            │
│  action_date            │
│  ip_address             │
│  user_agent             │
└─────────────────────────┘
```

---

## 2. Tabla `{prefix}_erm_tracking`

### Propósito

Almacenar eventos de interacción (vista, descarga, completado) de forma append-only. No sustituye al CPT: complementa la analítica con volumen y consultas agregadas eficientes.

### Columnas

| Nombre | Tipo | Nulo | Default | Descripción |
|--------|------|------|---------|-------------|
| `id` | `BIGINT(20) UNSIGNED` | NO | AUTO_INCREMENT | Clave primaria del evento |
| `resource_id` | `BIGINT(20) UNSIGNED` | NO | — | ID del post `education_resource` |
| `user_id` | `BIGINT(20) UNSIGNED` | SÍ | `NULL` | Usuario WP; `NULL` si anónimo |
| `action_type` | `VARCHAR(20)` | NO | `'view'` | `view`, `download` o `complete` |
| `action_date` | `DATETIME` | NO | `CURRENT_TIMESTAMP` | Momento del evento |
| `ip_address` | `VARCHAR(45)` | SÍ | `NULL` | IPv4/IPv6 validada en PHP |
| `user_agent` | `TEXT` | SÍ | `NULL` | Cadena del navegador sanitizada |

### Índices (creados por `dbDelta` en activación)

| Índice | Columnas | Uso principal |
|--------|----------|----------------|
| `PRIMARY` | `id` | Inserciones y referencia única |
| `resource_id` | `resource_id` | Conteos por recurso, JOIN con posts |
| `user_id` | `user_id` | `COUNT(DISTINCT user_id)` en resumen |
| `action_date` | `action_date` | Rangos temporales (futuro) |
| `action_type` | `action_type` | Filtro view/download en agregados |

**Recomendación producción** (`database/schema.sql`): índice compuesto `(resource_id, action_type, action_date)` para consultas del tipo «vistas del recurso X en un periodo».

---

## 3. Post meta keys del CPT

| Meta key | Tipo PHP | Valores permitidos | Descripción |
|----------|----------|-------------------|-------------|
| `_erm_resource_type` | `string` | `course`, `tutorial`, `ebook`, `video` | Tipo de recurso |
| `_erm_difficulty_level` | `string` | `beginner`, `intermediate`, `advanced` | Nivel de dificultad |
| `_erm_duration_minutes` | `int` | ≥ 0 | Duración estimada en minutos |
| `_erm_resource_url` | `string` | URL válida | Enlace externo al contenido |
| `_erm_instructor` | `string` | texto libre | Instructor o autor |
| `_erm_price` | `float` | ≥ 0 (`0` = gratuito) | Precio del recurso |
| `_erm_publication_status` | `string` | `draft`, `publish`, `erm_archived` | Espejo del estado de publicación (sincronizado con `post_status`) |

### Estados de publicación (`post_status`)

| Valor | Etiqueta | Comportamiento |
|-------|---------|----------------|
| `draft` | Borrador | No visible en shortcode ni REST público |
| `publish` | Publicado | Visible en frontend y API pública |
| `erm_archived` | Archivado | Estado custom registrado con `register_post_status()`; oculto del sitio público |

---

## 4. Taxonomías

### `resource_category`

- **Tipo:** jerárquica (como categorías).
- **Rewrite:** `categoria-recurso`.
- **Uso:** clasificación temática; filtro REST `category` (slug) y shortcode `category="slug"`.

### `skill_tag`

- **Tipo:** no jerárquica (como etiquetas).
- **Rewrite:** `habilidad`.
- **Uso:** habilidades asociadas; filtro REST `skill` (slug).

---

## 5. Queries principales (código real)

### 5.1 `ERM_Database::insert_tracking()`

```sql
INSERT INTO {prefix}_erm_tracking
  (resource_id, user_id, action_type, ip_address, user_agent)
VALUES (%d, %d, %s, %s, %s)
```

- **Complejidad:** O(1) por inserción.
- **Validación previa:** post existe y `post_type = education_resource`.
- **Post-acción:** `invalidate_cache()`.

### 5.2 `ERM_Database::get_resource_views()` / `get_resource_tracking_count()`

```sql
SELECT COUNT(*) FROM {prefix}_erm_tracking
WHERE resource_id = %d AND action_type = %s
```

- Usa índice `resource_id` + filtro en `action_type`.

### 5.3 `ERM_Database::get_top_resources()`

```sql
SELECT
    t.resource_id,
    p.post_title,
    COUNT(t.id) AS action_count
FROM {prefix}_erm_tracking t
INNER JOIN {prefix}posts p ON t.resource_id = p.ID
WHERE
    t.action_type = %s
    AND p.post_type = %s
    AND p.post_status = %s
GROUP BY t.resource_id, p.post_title
ORDER BY action_count DESC
LIMIT %d
```

- **Transient:** `erm_top_resources_{action_type}_{limit}` (1 hora).
- **Índices:** `resource_id`, `action_type`; JOIN por `p.ID`.

### 5.4 `ERM_Database::get_stats_summary()`

Fragmento sobre tracking:

```sql
SELECT
    SUM(CASE WHEN action_type = %s THEN 1 ELSE 0 END) AS total_views,
    SUM(CASE WHEN action_type = %s THEN 1 ELSE 0 END) AS total_downloads,
    COUNT(DISTINCT user_id) AS unique_users
FROM {prefix}_erm_tracking
```

Fragmento por tipo (postmeta):

```sql
SELECT pm.meta_value AS type, COUNT(*) AS total
FROM {prefix}posts p
INNER JOIN {prefix}postmeta pm ON p.ID = pm.post_id
WHERE p.post_type = %s
  AND p.post_status = %s
  AND pm.meta_key = %s
GROUP BY pm.meta_value
```

- **Transient:** `erm_stats_summary` (1 hora).
- **Total recursos:** `wp_count_posts('education_resource')->publish`.

### 5.5 `ERM_Database::get_monthly_stats()`

```sql
SELECT
    DATE_FORMAT(post_date, '%Y-%m') AS month,
    COUNT(*) AS total
FROM {prefix}posts
WHERE post_type = %s
  AND post_status = %s
  AND post_date >= DATE_SUB(NOW(), INTERVAL %d MONTH)
GROUP BY month
ORDER BY month ASC
```

- Usado en panel admin (gráfico Canvas) y endpoint `/stats` (`monthly_growth`).

### 5.6 Filtros REST / listado (`ERM_REST_API::get_resources()`)

Construye `WP_Query`:

```php
[
    'post_type'      => 'education_resource',
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $page,
    's'              => $search,           // opcional
    'meta_query'     => [ ... tipo, dificultad ],
    'tax_query'      => [ ... category, skill ],
]
```

- `orderby=views` en REST cae a `date` (orden por vistas no implementado en query).

---

## 6. Índices y optimización

| Escenario | Índice recomendado |
|-----------|-------------------|
| Conteo vistas por recurso | `resource_id` + `action_type` |
| Top N global | `action_type` + JOIN posts publicados |
| Usuarios únicos | `user_id` (parcial, muchos NULL) |
| Serie temporal futura | `action_date` o compuesto con `resource_id` |

**Caché:** transients evitan repetir agregados pesados; se borran al registrar tracking.

---

## 7. Migración y versionado

| Opción WP | Valor | Rol |
|-----------|-------|-----|
| `erm_version` | `1.0.0` | Versión del plugin (`ERM_VERSION`) |
| `erm_db_version` | `1.0.0` | Versión de esquema BD (`ERM_DB_VERSION`) |

- Creación inicial: `ERM_Activator::create_tables()` con `dbDelta()`.
- No hay migraciones incrementales en v1.0.0; futuras versiones compararían `erm_db_version` y aplicarían `dbDelta()` adicional.

---

## 8. Mantenimiento

### Backup de la tabla custom

```bash
wp db export backup.sql --tables=wp_erm_tracking
```

O con mysqldump:

```bash
mysqldump -u USER -p DATABASE wp_erm_tracking > erm_tracking_backup.sql
```

### Desinstalación

`uninstall.php` ejecuta:

```sql
DROP TABLE IF EXISTS {prefix}_erm_tracking;
```

y elimina opciones `erm_version`, `erm_db_version` y transients `erm_*`.

**Nota:** los posts `education_resource` no se borran (contenido del sitio).

### Limpiar caché tras importar `sample-data.sql`

```sql
DELETE FROM wp_options
WHERE option_name LIKE '_transient_erm_%'
   OR option_name LIKE '_transient_timeout_erm_%';
```
