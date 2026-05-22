-- ============================================================
-- Education Resources Manager - Database Schema
-- Version: 1.0.0
-- Requires: WordPress 6.0+, MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

-- Tabla de tracking de recursos educativos
-- Registra visualizaciones, descargas y completados

CREATE TABLE IF NOT EXISTS `wp_erm_tracking` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`resource_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK lógica a wp_posts.ID (post_type = education_resource)',
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
-- Este script NO es obligatorio en instalaciones normales.
-- La tabla se crea al activar el plugin mediante
-- ERM_Activator::create_tables() usando dbDelta().
--
-- dbDelta() crea índices con nombres: resource_id, user_id,
-- action_date, action_type (sin el índice compuesto extra).
-- El índice compuesto idx_resource_action_date es recomendado
-- en producción para consultas analíticas frecuentes.
--
-- Para ejecutar manualmente:
-- 1. Sustituye wp_ por el prefijo real de tu instalación.
-- 2. Selecciona la base de datos de WordPress.
-- 3. Ejecuta este script en phpMyAdmin o cliente MySQL.
-- ============================================================
