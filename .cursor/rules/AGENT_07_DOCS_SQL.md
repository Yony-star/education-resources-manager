# AGENT 07 — Documentación + SQL
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Generar los archivos de documentación técnica completos (`ARCHITECTURE.md`, `DATABASE.md`, `API.md`) y los scripts SQL (`schema.sql`, `sample-data.sql`) basándose en todo el código ya generado por los agentes anteriores.

**Depende de:** Todos los agentes anteriores (01–06). Ejecutar este agente AL FINAL.

---

## 📦 Archivos a Generar

1. `docs/ARCHITECTURE.md` — Arquitectura completa del sistema
2. `docs/DATABASE.md` — Documentación de BD con queries reales
3. `docs/API.md` — Documentación completa de la REST API
4. `database/schema.sql` — Script SQL ejecutable
5. `database/sample-data.sql` — Datos de prueba

---

## 📋 Instrucciones para Cada Archivo

### Instrucción al agente:

Antes de generar cada archivo de documentación, usa `@codebase` o `@file` en Cursor para leer el código ya generado. Los documentos deben reflejar la implementación real, no plantillas vacías.

---

### Archivo 1: `docs/ARCHITECTURE.md`

Debe cubrir:

1. **Visión General** — qué hace el plugin, para quién, alcance
2. **Diagrama de Arquitectura en texto** — usando caracteres ASCII (como los de los templates)
3. **Estructura de Componentes** — describir cada clase real:
   - `ERM_Activator` — qué hace al activar
   - `ERM_Deactivator` — qué hace al desactivar
   - `ERM_Loader` — patrón de gestión de hooks
   - `ERM_Post_Type` — CPT, meta boxes, columnas custom
   - `ERM_Taxonomy` — las dos taxonomías registradas
   - `ERM_Database` — tabla custom, métodos CRUD, uso de transients
   - `ERM_Admin` — menú, páginas, filtros
   - `ERM_Shortcode` — shortcode, template, assets
   - `ERM_REST_API` — endpoints, validación, respuestas
4. **Flujos de Datos** — los 4 flujos principales (crear recurso, ver en frontend, filtrar AJAX, registrar tracking)
5. **Decisiones Técnicas** con justificación real:
   - Por qué CPT (no tabla custom) para los recursos
   - Por qué tabla custom (no postmeta) para el tracking
   - Por qué REST API (no admin-ajax) para los filtros
   - Por qué Canvas nativo (no Chart.js) para el gráfico
   - Uso de transients para cache de estadísticas
6. **Seguridad** — medidas implementadas (nonces, sanitización, prepared statements, capabilities)
7. **Performance** — transients, enqueue condicional, limits en WP_Query
8. **Dependencias** — WordPress 6.0+, PHP 7.4+, extensiones requeridas

---

### Archivo 2: `docs/DATABASE.md`

Debe cubrir:

1. **Diagrama ER** completo (en ASCII) mostrando:
   - `wp_posts` (education_resource)
   - `wp_postmeta` con las meta keys del plugin
   - `wp_terms` + `wp_term_relationships` (taxonomías)
   - `{prefix}_erm_tracking` (tabla custom)

2. **Tabla personalizada: `{prefix}_erm_tracking`**
   - Descripción de propósito
   - Tabla markdown con columnas: Nombre | Tipo | Nulo | Default | Descripción
   - Los índices y su justificación

3. **Post Meta Keys del CPT**
   - Tabla con: Meta Key | Tipo PHP | Valores Permitidos | Descripción

4. **Taxonomías**
   - `resource_category` — descripción y uso
   - `skill_tag` — descripción y uso

5. **Queries Principales** (copiar las queries reales del código generado en Agent 03 y 04):
   - `get_top_resources()` con análisis de índices usados
   - `get_stats_summary()` con nota sobre el transient
   - `insert_tracking()` con nota de complejidad O(1)
   - `get_monthly_stats()` con el DATE_FORMAT
   - Query de filtros en la REST API (WP_Query con meta_query + tax_query)

6. **Índices y Optimización** — tabla de índices con justificación

7. **Sistema de Migración/Versionado** — cómo se versiona el esquema (`ERM_DB_VERSION`)

8. **Mantenimiento** — cómo hacer backup de la tabla custom

---

### Archivo 3: `docs/API.md`

Basarse en `API_TEMPLATE.md` proporcionado pero con los datos **reales** de la implementación. Debe incluir:

1. **Información General** — namespace `erm/v1`, URL base, formato de respuesta
2. **Autenticación** — endpoints públicos vs protegidos, nonce para AJAX, Application Passwords para externo
3. **4 Endpoints documentados**:

   Para cada endpoint:
   - Método HTTP + path
   - Descripción
   - Tabla de parámetros (reales según el código del Agent 04)
   - Ejemplo de request (cURL)
   - Ejemplo de respuesta exitosa (JSON real basado en `format_resource()`)
   - Códigos de error posibles

4. **Modelos de Datos** — interfaces TypeScript de Resource, Category, Skill, Pagination (ya definidos en el template)

5. **Códigos de Error** — los reales del plugin (`resource_not_found`, `tracking_failed`, etc.)

6. **Ejemplos de Uso** en JavaScript:
   - Fetch con filtros
   - Track con nonce
   - Manejo de errores

---

### Archivo 4: `database/schema.sql`

Script SQL ejecutable directamente en MySQL. Usar el prefijo `wp_` como ejemplo estándar.

```sql
-- ============================================================
-- Education Resources Manager - Database Schema
-- Version: 1.0.0
-- Requires: WordPress 6.0+, MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

-- Tabla de tracking de recursos educativos
-- Registra visualizaciones, descargas y completados

CREATE TABLE IF NOT EXISTS `wp_erm_tracking` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `resource_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK a wp_posts.ID (education_resource)',
    `user_id` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'NULL para visitantes no autenticados',
    `action_type` VARCHAR(20) NOT NULL DEFAULT 'view' COMMENT 'view | download | complete',
    `action_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la acción',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IPv4 o IPv6 del cliente',
    `user_agent` TEXT DEFAULT NULL COMMENT 'User agent del navegador',
    PRIMARY KEY (`id`),
    KEY `idx_resource_id` (`resource_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action_date` (`action_date`),
    KEY `idx_action_type` (`action_type`),
    KEY `idx_resource_action_date` (`resource_id`, `action_type`, `action_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- NOTAS DE INSTALACIÓN
-- ============================================================
-- Este script NO necesita ejecutarse manualmente.
-- La tabla se crea automáticamente al activar el plugin
-- mediante la función ERM_Activator::create_tables() que usa dbDelta().
--
-- Para crear manualmente (reemplaza {prefix} con tu prefijo de WP):
-- 1. Abre phpMyAdmin o tu cliente MySQL
-- 2. Selecciona la base de datos de WordPress
-- 3. Ejecuta este script reemplazando wp_ por tu prefijo real
-- ============================================================


-- ============================================================
-- ÍNDICE COMPUESTO (recomendado para producción)
-- ============================================================
-- El índice compuesto optimiza la query más frecuente:
-- "¿Cuántas veces fue visto el recurso X en el último mes?"
-- ALTER TABLE `wp_erm_tracking`
-- ADD INDEX `idx_resource_action_date` (`resource_id`, `action_type`, `action_date`);
-- (Ya incluido en la definición de tabla arriba)
```

---

### Archivo 5: `database/sample-data.sql`

Datos de prueba para demostrar el plugin funcionando. Incluir:

1. Comentario explicando que requiere WordPress instalado y los posts deben existir
2. INSERT de tracking records para los primeros 10 IDs de post (asumiendo que existen):

```sql
-- ============================================================
-- Education Resources Manager - Sample Data
-- ============================================================
-- IMPORTANTE: Ejecutar DESPUÉS de activar el plugin y crear
-- algunos recursos desde el admin de WordPress.
-- Asegúrate de que los resource_id referenciados existen en wp_posts.
-- Ajusta los IDs según tu instalación.
-- ============================================================

-- Datos de prueba para la tabla wp_erm_tracking
-- Simula actividad de los últimos 30 días

INSERT INTO `wp_erm_tracking` (`resource_id`, `user_id`, `action_type`, `action_date`, `ip_address`) VALUES
-- Recurso ID 1 - muy popular (ajustar ID según tu instalación)
(1, NULL, 'view', DATE_SUB(NOW(), INTERVAL 1 DAY), '192.168.1.1'),
(1, 2, 'view', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.1.2'),
(1, NULL, 'download', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.1.3'),
(1, 3, 'view', DATE_SUB(NOW(), INTERVAL 3 DAY), '10.0.0.1'),
(1, NULL, 'view', DATE_SUB(NOW(), INTERVAL 4 DAY), '10.0.0.2'),
(1, 2, 'complete', DATE_SUB(NOW(), INTERVAL 5 DAY), '10.0.0.3'),
-- Recurso ID 2
(2, NULL, 'view', DATE_SUB(NOW(), INTERVAL 1 DAY), '172.16.0.1'),
(2, 3, 'view', DATE_SUB(NOW(), INTERVAL 3 DAY), '172.16.0.2'),
(2, NULL, 'download', DATE_SUB(NOW(), INTERVAL 7 DAY), '172.16.0.3'),
-- Recurso ID 3
(3, 4, 'view', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.2.1'),
(3, NULL, 'view', DATE_SUB(NOW(), INTERVAL 10 DAY), '192.168.2.2'),
-- Añadir más registros para tener datos significativos en el gráfico...
;

-- ============================================================
-- QUERY DE VERIFICACIÓN
-- ============================================================
-- Ejecuta esto para verificar que los datos se insertaron:
-- SELECT action_type, COUNT(*) as total FROM wp_erm_tracking GROUP BY action_type;
```

---

## ✅ Criterios de Aceptación

- [ ] `ARCHITECTURE.md` tiene diagramas ASCII reales (no placeholders)
- [ ] `ARCHITECTURE.md` justifica cada decisión técnica con razones concretas
- [ ] `DATABASE.md` tiene el diagrama ER completo
- [ ] `DATABASE.md` muestra las queries reales del código (no pseudocódigo)
- [ ] `API.md` tiene ejemplos de respuesta JSON reales (basados en `format_resource()`)
- [ ] `schema.sql` es ejecutable directamente en MySQL
- [ ] `schema.sql` incluye comentarios explicativos
- [ ] `sample-data.sql` tiene instrucciones claras de uso
- [ ] Toda la documentación está en español
- [ ] Los archivos no tienen secciones `[TODO]` o `[Completa aquí]` vacías

---

## 💡 Prompt de Cursor para Este Agente

```
Lee los siguientes archivos del proyecto usando @codebase:
- includes/class-erm-rest-api.php
- includes/class-erm-database.php
- includes/class-erm-post-type.php
- includes/class-erm-taxonomy.php
- includes/class-erm-admin.php
- includes/class-erm-shortcode.php
- includes/class-erm-activator.php

Basándote en el código real de esos archivos, genera los 5 archivos
de documentación descritos en AGENT_07_DOCS_SQL.md.

La documentación debe reflejar la implementación real, con los
nombres de métodos reales, las queries reales, y los tipos de
datos reales. No uses placeholders ni texto genérico.
```
