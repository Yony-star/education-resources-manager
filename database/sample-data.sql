-- ============================================================
-- Education Resources Manager - Sample Data
-- ============================================================
-- IMPORTANTE:
-- 1. Activa el plugin antes de ejecutar inserts.
-- 2. Crea recursos (CPT education_resource) en WordPress.
-- 3. Sustituye wp_ por tu prefijo de tablas.
-- 4. Ajusta resource_id a IDs reales de wp_posts en tu sitio.
-- ============================================================

-- Datos de prueba para wp_erm_tracking
-- Simula actividad de los últimos 30 días

INSERT INTO `wp_erm_tracking` (`resource_id`, `user_id`, `action_type`, `action_date`, `ip_address`, `user_agent`) VALUES
-- Recurso 1 (popular)
(1, NULL, 'view', DATE_SUB(NOW(), INTERVAL 1 DAY), '192.168.1.10', 'Mozilla/5.0 (Sample Browser)'),
(1, 2, 'view', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.1.11', 'Mozilla/5.0 (Sample Browser)'),
(1, NULL, 'download', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.1.12', 'Mozilla/5.0 (Sample Browser)'),
(1, 3, 'view', DATE_SUB(NOW(), INTERVAL 3 DAY), '10.0.0.1', 'Mozilla/5.0 (Sample Browser)'),
(1, NULL, 'view', DATE_SUB(NOW(), INTERVAL 4 DAY), '10.0.0.2', 'Mozilla/5.0 (Sample Browser)'),
(1, 2, 'complete', DATE_SUB(NOW(), INTERVAL 5 DAY), '10.0.0.3', 'Mozilla/5.0 (Sample Browser)'),
(1, NULL, 'view', DATE_SUB(NOW(), INTERVAL 6 DAY), '10.0.0.4', 'Mozilla/5.0 (Sample Browser)'),
(1, 1, 'view', DATE_SUB(NOW(), INTERVAL 7 DAY), '10.0.0.5', 'Mozilla/5.0 (Sample Browser)'),
-- Recurso 2
(2, NULL, 'view', DATE_SUB(NOW(), INTERVAL 1 DAY), '172.16.0.1', 'Mozilla/5.0 (Sample Browser)'),
(2, 3, 'view', DATE_SUB(NOW(), INTERVAL 3 DAY), '172.16.0.2', 'Mozilla/5.0 (Sample Browser)'),
(2, NULL, 'download', DATE_SUB(NOW(), INTERVAL 7 DAY), '172.16.0.3', 'Mozilla/5.0 (Sample Browser)'),
(2, NULL, 'view', DATE_SUB(NOW(), INTERVAL 14 DAY), '172.16.0.4', 'Mozilla/5.0 (Sample Browser)'),
-- Recurso 3
(3, 4, 'view', DATE_SUB(NOW(), INTERVAL 2 DAY), '192.168.2.1', 'Mozilla/5.0 (Sample Browser)'),
(3, NULL, 'view', DATE_SUB(NOW(), INTERVAL 10 DAY), '192.168.2.2', 'Mozilla/5.0 (Sample Browser)'),
(3, NULL, 'download', DATE_SUB(NOW(), INTERVAL 12 DAY), '192.168.2.3', 'Mozilla/5.0 (Sample Browser)'),
-- Recurso 4
(4, NULL, 'view', DATE_SUB(NOW(), INTERVAL 5 DAY), '203.0.113.1', 'Mozilla/5.0 (Sample Browser)'),
(4, 5, 'view', DATE_SUB(NOW(), INTERVAL 8 DAY), '203.0.113.2', 'Mozilla/5.0 (Sample Browser)'),
(4, NULL, 'complete', DATE_SUB(NOW(), INTERVAL 15 DAY), '203.0.113.3', 'Mozilla/5.0 (Sample Browser)'),
-- Recurso 5
(5, NULL, 'view', DATE_SUB(NOW(), INTERVAL 4 DAY), '198.51.100.1', 'Mozilla/5.0 (Sample Browser)'),
(5, 2, 'download', DATE_SUB(NOW(), INTERVAL 9 DAY), '198.51.100.2', 'Mozilla/5.0 (Sample Browser)'),
-- Recursos 6-10 (volumen ligero)
(6, NULL, 'view', DATE_SUB(NOW(), INTERVAL 3 DAY), '192.168.3.1', NULL),
(7, NULL, 'view', DATE_SUB(NOW(), INTERVAL 6 DAY), '192.168.3.2', NULL),
(8, 1, 'view', DATE_SUB(NOW(), INTERVAL 11 DAY), '192.168.3.3', NULL),
(9, NULL, 'download', DATE_SUB(NOW(), INTERVAL 13 DAY), '192.168.3.4', NULL),
(10, NULL, 'view', DATE_SUB(NOW(), INTERVAL 20 DAY), '192.168.3.5', NULL);


-- ============================================================
-- QUERIES DE VERIFICACIÓN
-- ============================================================
-- SELECT action_type, COUNT(*) AS total FROM wp_erm_tracking GROUP BY action_type;
-- SELECT resource_id, COUNT(*) AS views FROM wp_erm_tracking WHERE action_type = 'view' GROUP BY resource_id ORDER BY views DESC LIMIT 5;

-- Tras insertar datos de prueba, limpia caché de estadísticas en WordPress:
-- DELETE FROM wp_options WHERE option_name LIKE '_transient_erm_%';
