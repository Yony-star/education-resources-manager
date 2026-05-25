<?php
/**
 * Bootstrap para la suite de tests de Education Resources Manager.
 *
 * Instalación del entorno de tests:
 *
 * 1. Instalar dependencias de desarrollo:
 *    composer install
 *
 * 2. Crear base de datos de test (¡distinta a la de desarrollo!):
 *    mysql -u root -e "CREATE DATABASE wordpress_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
 *
 * 3. Instalar WordPress test suite (ejecutar desde la raíz del plugin):
 *    bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
 *
 * 4. Correr los tests:
 *    vendor/bin/phpunit
 *    vendor/bin/phpunit --filter test_post_type_is_registered   (test específico)
 *    vendor/bin/phpunit tests/test-rest-api.php                  (suite específica)
 *
 * @package ERM\Tests
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "No se encontró la WordPress test suite en '{$_tests_dir}'.\n";
	echo "Ejecuta: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass>\n";
	exit( 1 );
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Carga manualmente el plugin antes de que WordPress arranque.
 */
function _manually_load_erm_plugin() {
	if ( ! defined( 'ERM_VERSION' ) ) {
		require dirname( __DIR__ ) . '/education-resources-manager.php';
	}
}
tests_add_filter( 'muplugins_loaded', '_manually_load_erm_plugin' );

/**
 * Crea la tabla personalizada en la BD de tests tras la instalación de WordPress.
 */
function _create_erm_test_tables() {
	require_once dirname( __DIR__ ) . '/includes/class-erm-activator.php';
	ERM_Activator::create_tables();
}
tests_add_filter( 'wp_install', '_create_erm_test_tables', 20 );

require $_tests_dir . '/includes/bootstrap.php';

require_once __DIR__ . '/helpers/trait-erm-test-factory.php';
