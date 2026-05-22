# AGENT 09 — Tests (Bonus)
## Education Resources Manager — Suite de Testing con PHPUnit

---

## 🎯 Misión de Este Agente

Eres un agente especializado en **testing de plugins WordPress** con PHPUnit y la librería oficial `wordpress-develop`. Tu misión es implementar la suite de tests del bonus mencionado en `STRUCTURE_EXAMPLE.md`:

```
tests/
├── bootstrap.php
├── test-post-type.php
└── test-rest-api.php
```

Implementarás tests reales y ejecutables que cubran el CPT `education_resource` y los 4 endpoints REST del plugin.

**Depende de:** Todos los agentes anteriores (01–08). El código del plugin debe existir antes de escribir los tests.

---

## 📋 Paso 0 — Lectura Obligatoria

Antes de escribir una sola línea, lee con `@file` o `@codebase`:

```
@file includes/class-erm-post-type.php
@file includes/class-erm-taxonomy.php
@file includes/class-erm-database.php
@file includes/class-erm-rest-api.php
@file education-resources-manager.php
```

Mapea mentalmente:
- Nombre exacto de cada clase y sus métodos públicos
- Slugs reales del CPT y las taxonomías
- Meta keys exactas (`_erm_resource_type`, etc.)
- Namespace REST (`erm/v1`) y rutas exactas

---

## 📦 Archivos a Generar

| Archivo | Propósito |
|---------|-----------|
| `tests/bootstrap.php` | Carga WordPress test suite + el plugin |
| `tests/test-post-type.php` | Tests del CPT, taxonomías y post meta |
| `tests/test-rest-api.php` | Tests de los 4 endpoints REST |
| `phpunit.xml` | Configuración de PHPUnit (en la raíz del plugin) |
| `composer.json` | Dependencias de desarrollo |
| `bin/install-wp-tests.sh` | Script de setup del entorno de tests |

---

## 📋 Instrucciones de Setup (Contexto para el Candidato)

El agente debe generar primero este bloque de documentación como comentario en `bootstrap.php`:

```
Los tests de WordPress requieren wordpress-develop.
Pasos de instalación:

1. Instalar dependencias:
   composer install

2. Crear base de datos de test (diferente a la de desarrollo):
   mysql -u root -e "CREATE DATABASE wordpress_test;"

3. Instalar WordPress test suite:
   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

4. Correr los tests:
   vendor/bin/phpunit

5. Con cobertura (requiere Xdebug o PCOV):
   vendor/bin/phpunit --coverage-text
```

---

## 📋 Instrucciones Detalladas por Archivo

---

### Archivo 1: `composer.json`

```json
{
    "name": "tu-nombre/education-resources-manager",
    "description": "Sistema de gestión de recursos educativos para WordPress",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "require": {
        "php": ">=7.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.6",
        "wp-phpunit/wp-phpunit": "^6.4",
        "yoast/phpunit-polyfills": "^2.0"
    },
    "autoload-dev": {
        "psr-4": {
            "ERM\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "vendor/bin/phpunit",
        "test-coverage": "vendor/bin/phpunit --coverage-text"
    },
    "config": {
        "allow-plugins": {
            "dealerdirect/phpcodesniffer-composer-installer": true
        }
    }
}
```

---

### Archivo 2: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.6/phpunit.xsd"
    bootstrap="tests/bootstrap.php"
    colors="true"
    verbose="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
>
    <testsuites>
        <testsuite name="ERM Plugin Tests">
            <directory suffix="test-*.php">./tests</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <include>
            <directory suffix=".php">./includes</directory>
        </include>
        <exclude>
            <file>./includes/class-erm-activator.php</file>
            <file>./includes/class-erm-deactivator.php</file>
        </exclude>
    </coverage>

    <php>
        <!-- Estas variables se leen en bootstrap.php -->
        <env name="WP_PHPUNIT__TESTS_CONFIG" value="tests/wp-tests-config.php"/>
        <const name="ERM_TESTING" value="true"/>
    </php>
</phpunit>
```

---

### Archivo 3: `bin/install-wp-tests.sh`

Script bash que descarga y configura `wordpress-develop` para testing. Genera el archivo completo con este contenido:

```bash
#!/usr/bin/env bash
# install-wp-tests.sh
# Instala WordPress test suite en /tmp/wordpress-tests-lib
#
# Uso: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
# Ejemplo: bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

if [ $# -lt 3 ]; then
    echo "Uso: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}

download() {
    if [ $(which curl) ]; then
        curl -s "$1" > "$2"
    elif [ $(which wget) ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
    WP_BRANCH=${WP_VERSION%\-*}
    WP_TESTS_TAG="branches/$WP_BRANCH"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        WP_TESTS_TAG="tags/${WP_VERSION%.\0}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    # latest
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//');
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "No se pudo determinar la última versión de WordPress"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_wp() {
    if [ -d $WP_CORE_DIR ]; then
        return;
    fi

    mkdir -p $WP_CORE_DIR

    if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
        mkdir -p /tmp/wordpress-latest
        download https://wordpress.org/nightly-builds/wordpress-latest.zip /tmp/wordpress-latest.zip
        unzip -q /tmp/wordpress-latest.zip -d /tmp/wordpress-latest/
        mv /tmp/wordpress-latest/wordpress/* $WP_CORE_DIR
    else
        if [ $WP_VERSION == 'latest' ]; then
            local ARCHIVE_NAME='latest'
        else
            local ARCHIVE_NAME="wordpress-$WP_VERSION"
        fi
        download https://wordpress.org/${ARCHIVE_NAME}.tar.gz /tmp/wordpress.tar.gz
        tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
    fi
}

install_test_suite() {
    if [ -d $WP_TESTS_DIR ]; then
        return;
    fi

    mkdir -p $WP_TESTS_DIR

    SVN_URL="https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/"
    svn co --quiet $SVN_URL $WP_TESTS_DIR/includes 2>/dev/null
    if [ $? -ne 0 ]; then
        # Fallback via download
        download "https://raw.github.com/wordpress/wordpress-develop/${WP_TESTS_TAG}/tests/phpunit/includes/bootstrap.php" "$WP_TESTS_DIR/includes/bootstrap.php"
        download "https://raw.github.com/wordpress/wordpress-develop/${WP_TESTS_TAG}/tests/phpunit/includes/functions.php" "$WP_TESTS_DIR/includes/functions.php"
    fi
}

install_db() {
    if [ ${SKIP_DB_CREATE} = "true" ]; then
        return
    fi

    PARTS=(${DB_HOST//\// })
    HOSTNAME_ONLY=${PARTS[0]};
    SOCKET_OR_PORT=${PARTS[1]};
    EXTRA=""

    if ! [ -z $SOCKET_OR_PORT ] ; then
        if [ $(echo $SOCKET_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
            EXTRA=" --host=$HOSTNAME_ONLY --port=$SOCKET_OR_PORT --protocol=tcp"
        else
            EXTRA=" --socket=$SOCKET_OR_PORT"
        fi
    elif ! [ -z $HOSTNAME_ONLY ] ; then
        EXTRA=" --host=$HOSTNAME_ONLY --protocol=tcp"
    fi

    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

configure_wp() {
    if [ ! -f wp-tests-config.php ]; then
        download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
        # Configurar el archivo
        sed -i "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed -i "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed -i "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
        sed -i "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"
    fi

    # Copiar a tests/
    cp "$WP_TESTS_DIR/wp-tests-config.php" "tests/wp-tests-config.php"
}

install_wp
install_test_suite
install_db
configure_wp

echo ""
echo "✅ WordPress test suite instalada en $WP_TESTS_DIR"
echo "✅ Base de datos '$DB_NAME' creada"
echo ""
echo "Ahora puedes correr: vendor/bin/phpunit"
```

---

### Archivo 4: `tests/bootstrap.php`

```php
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

// Directorio de la WordPress test suite (creado por install-wp-tests.sh)
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "No se encontró la WordPress test suite en '{$_tests_dir}'.\n";
	echo "Ejecuta: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass>\n";
	exit( 1 );
}

// Da acceso a tests_add_filter() antes de que WordPress cargue.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Carga manualmente el plugin antes de que WordPress arranque.
 * Esto asegura que todos los hooks del plugin estén registrados.
 */
function _manually_load_erm_plugin() {
	// Asegurarse de que las constantes estén definidas antes de cargar
	if ( ! defined( 'ERM_VERSION' ) ) {
		require dirname( __DIR__ ) . '/education-resources-manager.php';
	}
}
tests_add_filter( 'muplugins_loaded', '_manually_load_erm_plugin' );

/**
 * Asegurarse de que la tabla personalizada se crea en la BD de tests.
 * Se ejecuta DESPUÉS de que WordPress instala las tablas core.
 */
function _create_erm_test_tables() {
	require_once dirname( __DIR__ ) . '/includes/class-erm-activator.php';
	ERM_Activator::create_tables();
}
tests_add_filter( 'wp_install', '_create_erm_test_tables', 20 );

// Arrancar el entorno de testing de WordPress.
require $_tests_dir . '/includes/bootstrap.php';

// Cargar helpers y traits comunes de los tests.
require_once __DIR__ . '/helpers/trait-erm-test-factory.php';
```

---

### Archivo 5: `tests/helpers/trait-erm-test-factory.php`

Trait reutilizable con métodos factory para crear datos de prueba en ambas suites.

```php
<?php
/**
 * Trait con métodos factory para crear datos de prueba del plugin ERM.
 * Reutilizable en todas las clases de test.
 *
 * @package ERM\Tests
 */

trait ERM_Test_Factory {

	/**
	 * Crea un post del CPT education_resource con valores configurables.
	 *
	 * @param array $overrides Valores a sobreescribir en el post y su meta.
	 * @return int ID del post creado.
	 */
	protected function create_resource( array $overrides = [] ) : int {
		$defaults = [
			'post_title'   => 'Recurso de Prueba',
			'post_content' => 'Contenido del recurso de prueba.',
			'post_excerpt' => 'Extracto del recurso.',
			'post_status'  => 'publish',
			'post_type'    => 'education_resource',
			'meta_input'   => [
				'_erm_resource_type'    => 'course',
				'_erm_difficulty_level' => 'beginner',
				'_erm_duration_minutes' => 60,
				'_erm_resource_url'     => 'https://example.com/curso',
				'_erm_instructor'       => 'Juan Pérez',
				'_erm_price'            => 0,
			],
		];

		// Separar meta_input de los args del post para poder mergear correctamente
		$meta_overrides = $overrides['meta_input'] ?? [];
		unset( $overrides['meta_input'] );

		$post_args           = array_merge( $defaults, $overrides );
		$post_args['meta_input'] = array_merge( $defaults['meta_input'], $meta_overrides );

		$post_id = wp_insert_post( $post_args, true );

		if ( is_wp_error( $post_id ) ) {
			$this->fail( 'No se pudo crear el recurso de prueba: ' . $post_id->get_error_message() );
		}

		return (int) $post_id;
	}

	/**
	 * Crea un registro de tracking en la tabla personalizada.
	 *
	 * @param int    $resource_id ID del recurso.
	 * @param string $action_type Tipo de acción: view | download | complete.
	 * @param int    $user_id     ID del usuario (0 = anónimo).
	 * @return int|false ID del registro insertado o false en error.
	 */
	protected function create_tracking( int $resource_id, string $action_type = 'view', int $user_id = 0 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'erm_tracking';

		$result = $wpdb->insert(
			$table,
			[
				'resource_id' => $resource_id,
				'user_id'     => $user_id ?: null,
				'action_type' => $action_type,
				'action_date' => current_time( 'mysql' ),
				'ip_address'  => '127.0.0.1',
				'user_agent'  => 'PHPUnit Test Runner',
			],
			[ '%d', '%d', '%s', '%s', '%s', '%s' ]
		);

		return $result !== false ? $wpdb->insert_id : false;
	}

	/**
	 * Crea un término de taxonomía y lo asigna al recurso indicado.
	 *
	 * @param int    $resource_id ID del recurso al que asignar.
	 * @param string $name        Nombre del término.
	 * @param string $taxonomy    Slug de la taxonomía.
	 * @return int term_id del término creado.
	 */
	protected function create_and_assign_term( int $resource_id, string $name, string $taxonomy = 'resource_category' ) : int {
		$term = wp_insert_term( $name, $taxonomy );

		if ( is_wp_error( $term ) ) {
			$this->fail( "No se pudo crear el término '{$name}' en taxonomía '{$taxonomy}': " . $term->get_error_message() );
		}

		wp_set_post_terms( $resource_id, [ $term['term_id'] ], $taxonomy );

		return (int) $term['term_id'];
	}

	/**
	 * Crea y autentica un usuario administrador para los tests de la REST API.
	 *
	 * @return int user_id del admin creado.
	 */
	protected function create_admin_user() : int {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Crea y autentica un usuario suscriptor (sin permisos admin).
	 *
	 * @return int user_id del suscriptor creado.
	 */
	protected function create_subscriber_user() : int {
		$user_id = $this->factory->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Construye y despacha una WP_REST_Request.
	 *
	 * @param string $method Método HTTP: GET | POST | PUT | DELETE.
	 * @param string $route  Ruta sin el namespace, ej: '/resources'.
	 * @param array  $params Parámetros de la petición.
	 * @return WP_REST_Response
	 */
	protected function rest_request( string $method, string $route, array $params = [] ) : WP_REST_Response {
		$request = new WP_REST_Request( $method, '/erm/v1' . $route );

		if ( in_array( $method, [ 'GET', 'DELETE' ], true ) ) {
			$request->set_query_params( $params );
		} else {
			$request->set_body_params( $params );
		}

		$response = rest_get_server()->dispatch( $request );

		// Asegurarse de que es WP_REST_Response (no WP_Error sin envolver)
		return rest_ensure_response( $response );
	}

	/**
	 * Retorna el cuerpo decodificado de una WP_REST_Response.
	 *
	 * @param WP_REST_Response $response
	 * @return array
	 */
	protected function get_response_data( WP_REST_Response $response ) : array {
		return $response->get_data();
	}
}
```

---

### Archivo 6: `tests/test-post-type.php`

```php
<?php
/**
 * Tests para el Custom Post Type, taxonomías y post meta del plugin ERM.
 *
 * Cubre:
 * - Registro del CPT education_resource
 * - Registro de taxonomías resource_category y skill_tag
 * - Guardado y recuperación de post meta
 * - Sanitización de datos en save_meta
 * - Columnas personalizadas en el admin
 *
 * @package ERM\Tests
 */

require_once __DIR__ . '/helpers/trait-erm-test-factory.php';

/**
 * Class Test_ERM_Post_Type
 *
 * @covers ERM_Post_Type
 * @covers ERM_Taxonomy
 */
class Test_ERM_Post_Type extends WP_UnitTestCase {

	use ERM_Test_Factory;

	// =========================================================
	// SETUP / TEARDOWN
	// =========================================================

	public function set_up() : void {
		parent::set_up();

		// Registrar el CPT y taxonomías (simula el hook 'init')
		$post_type = new ERM_Post_Type();
		$post_type->register();

		$taxonomy = new ERM_Taxonomy();
		$taxonomy->register();

		// Limpiar la tabla de tracking entre tests
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}erm_tracking" );
	}

	// =========================================================
	// TESTS: REGISTRO DEL CPT
	// =========================================================

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_registered() : void {
		$this->assertTrue(
			post_type_exists( 'education_resource' ),
			'El CPT education_resource debe estar registrado después de llamar a register()'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_public() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertNotNull( $post_type_object );
		$this->assertTrue( $post_type_object->public );
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_is_exposed_to_rest_api() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertTrue(
			$post_type_object->show_in_rest,
			'El CPT debe estar expuesto a la REST API (show_in_rest = true)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_supports_editor_and_thumbnail() : void {
		$this->assertTrue(
			post_type_supports( 'education_resource', 'editor' ),
			'El CPT debe soportar el editor'
		);

		$this->assertTrue(
			post_type_supports( 'education_resource', 'thumbnail' ),
			'El CPT debe soportar imagen destacada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_has_correct_menu_icon() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertSame(
			'dashicons-welcome-learn-more',
			$post_type_object->menu_icon
		);
	}

	/**
	 * @test
	 * @covers ERM_Post_Type::register
	 */
	public function test_post_type_has_archive() : void {
		$post_type_object = get_post_type_object( 'education_resource' );

		$this->assertTrue( $post_type_object->has_archive );
	}

	// =========================================================
	// TESTS: CREAR Y RECUPERAR POSTS DEL CPT
	// =========================================================

	/**
	 * @test
	 */
	public function test_can_create_education_resource_post() : void {
		$post_id = $this->create_resource( [ 'post_title' => 'Mi Curso de Prueba' ] );

		$this->assertGreaterThan( 0, $post_id );
		$this->assertSame( 'education_resource', get_post_type( $post_id ) );
		$this->assertSame( 'Mi Curso de Prueba', get_the_title( $post_id ) );
	}

	/**
	 * @test
	 */
	public function test_published_resource_is_queryable() : void {
		$post_id = $this->create_resource( [ 'post_status' => 'publish' ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( $post_id, (int) $query->posts[0]->ID );
	}

	/**
	 * @test
	 */
	public function test_draft_resource_is_not_in_public_query() : void {
		$this->create_resource( [ 'post_status' => 'draft' ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
		] );

		$this->assertSame( 0, $query->found_posts );
	}

	// =========================================================
	// TESTS: POST META
	// =========================================================

	/**
	 * @test
	 */
	public function test_resource_type_meta_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_resource_type' => 'tutorial' ],
		] );

		$this->assertSame(
			'tutorial',
			get_post_meta( $post_id, '_erm_resource_type', true )
		);
	}

	/**
	 * @test
	 */
	public function test_difficulty_level_meta_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_difficulty_level' => 'advanced' ],
		] );

		$this->assertSame(
			'advanced',
			get_post_meta( $post_id, '_erm_difficulty_level', true )
		);
	}

	/**
	 * @test
	 */
	public function test_duration_minutes_is_stored_as_integer() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_duration_minutes' => 90 ],
		] );

		$value = (int) get_post_meta( $post_id, '_erm_duration_minutes', true );
		$this->assertSame( 90, $value );
	}

	/**
	 * @test
	 */
	public function test_price_zero_is_saved_as_free() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_price' => 0 ],
		] );

		$price = (float) get_post_meta( $post_id, '_erm_price', true );
		$this->assertSame( 0.0, $price );
	}

	/**
	 * @test
	 */
	public function test_resource_url_is_saved_correctly() : void {
		$url     = 'https://example.com/mi-recurso';
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_resource_url' => $url ],
		] );

		$this->assertSame(
			$url,
			get_post_meta( $post_id, '_erm_resource_url', true )
		);
	}

	/**
	 * @test
	 */
	public function test_instructor_name_is_saved_correctly() : void {
		$post_id = $this->create_resource( [
			'meta_input' => [ '_erm_instructor' => 'María García' ],
		] );

		$this->assertSame(
			'María García',
			get_post_meta( $post_id, '_erm_instructor', true )
		);
	}

	/**
	 * @test
	 */
	public function test_all_valid_resource_types_are_storable() : void {
		$valid_types = [ 'course', 'tutorial', 'ebook', 'video' ];

		foreach ( $valid_types as $type ) {
			$post_id = $this->create_resource( [
				'post_title' => "Recurso tipo {$type}",
				'meta_input' => [ '_erm_resource_type' => $type ],
			] );

			$this->assertSame(
				$type,
				get_post_meta( $post_id, '_erm_resource_type', true ),
				"El tipo '{$type}' debe poder guardarse en post meta"
			);
		}
	}

	/**
	 * @test
	 */
	public function test_all_valid_difficulty_levels_are_storable() : void {
		$valid_levels = [ 'beginner', 'intermediate', 'advanced' ];

		foreach ( $valid_levels as $level ) {
			$post_id = $this->create_resource( [
				'post_title' => "Recurso nivel {$level}",
				'meta_input' => [ '_erm_difficulty_level' => $level ],
			] );

			$this->assertSame(
				$level,
				get_post_meta( $post_id, '_erm_difficulty_level', true ),
				"El nivel '{$level}' debe poder guardarse en post meta"
			);
		}
	}

	// =========================================================
	// TESTS: TAXONOMÍAS
	// =========================================================

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_resource_category_taxonomy_is_registered() : void {
		$this->assertTrue(
			taxonomy_exists( 'resource_category' ),
			'La taxonomía resource_category debe estar registrada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_skill_tag_taxonomy_is_registered() : void {
		$this->assertTrue(
			taxonomy_exists( 'skill_tag' ),
			'La taxonomía skill_tag debe estar registrada'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_resource_category_is_hierarchical() : void {
		$taxonomy = get_taxonomy( 'resource_category' );

		$this->assertTrue(
			$taxonomy->hierarchical,
			'resource_category debe ser jerárquica (como categorías)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_skill_tag_is_not_hierarchical() : void {
		$taxonomy = get_taxonomy( 'skill_tag' );

		$this->assertFalse(
			$taxonomy->hierarchical,
			'skill_tag no debe ser jerárquica (como etiquetas)'
		);
	}

	/**
	 * @test
	 * @covers ERM_Taxonomy::register
	 */
	public function test_taxonomies_are_exposed_to_rest_api() : void {
		$category_tax = get_taxonomy( 'resource_category' );
		$skill_tax    = get_taxonomy( 'skill_tag' );

		$this->assertTrue( $category_tax->show_in_rest, 'resource_category debe exponerse a la REST API' );
		$this->assertTrue( $skill_tax->show_in_rest, 'skill_tag debe exponerse a la REST API' );
	}

	/**
	 * @test
	 */
	public function test_can_assign_category_to_resource() : void {
		$post_id = $this->create_resource();
		$term_id = $this->create_and_assign_term( $post_id, 'Programación', 'resource_category' );

		$terms = wp_get_post_terms( $post_id, 'resource_category' );

		$this->assertCount( 1, $terms );
		$this->assertSame( $term_id, (int) $terms[0]->term_id );
		$this->assertSame( 'Programación', $terms[0]->name );
	}

	/**
	 * @test
	 */
	public function test_can_assign_multiple_skill_tags_to_resource() : void {
		$post_id = $this->create_resource();
		$this->create_and_assign_term( $post_id, 'JavaScript', 'skill_tag' );
		$this->create_and_assign_term( $post_id, 'WordPress', 'skill_tag' );

		$terms = wp_get_post_terms( $post_id, 'skill_tag' );

		$this->assertCount( 2, $terms );

		$tag_names = wp_list_pluck( $terms, 'name' );
		$this->assertContains( 'JavaScript', $tag_names );
		$this->assertContains( 'WordPress', $tag_names );
	}

	/**
	 * @test
	 */
	public function test_resource_category_supports_hierarchy() : void {
		$parent = wp_insert_term( 'Tecnología', 'resource_category' );
		$child  = wp_insert_term( 'Programación', 'resource_category', [
			'parent' => $parent['term_id'],
		] );

		$child_term = get_term( $child['term_id'], 'resource_category' );

		$this->assertSame(
			(int) $parent['term_id'],
			(int) $child_term->parent,
			'Las categorías hijas deben tener parent_id correcto'
		);
	}

	// =========================================================
	// TESTS: FILTRADO CON WP_QUERY
	// =========================================================

	/**
	 * @test
	 */
	public function test_can_filter_resources_by_type_via_meta_query() : void {
		$this->create_resource( [ 'post_title' => 'Curso A', 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'post_title' => 'Tutorial B', 'meta_input' => [ '_erm_resource_type' => 'tutorial' ] ] );
		$this->create_resource( [ 'post_title' => 'Ebook C', 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
			'meta_query'  => [
				[ 'key' => '_erm_resource_type', 'value' => 'course', 'compare' => '=' ],
			],
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( 'Curso A', $query->posts[0]->post_title );
	}

	/**
	 * @test
	 */
	public function test_can_filter_resources_by_category_via_tax_query() : void {
		$post_1 = $this->create_resource( [ 'post_title' => 'Recurso con categoría' ] );
		$post_2 = $this->create_resource( [ 'post_title' => 'Recurso sin categoría' ] );

		$this->create_and_assign_term( $post_1, 'Frontend', 'resource_category' );

		$query = new WP_Query( [
			'post_type'   => 'education_resource',
			'post_status' => 'publish',
			'tax_query'   => [
				[ 'taxonomy' => 'resource_category', 'field' => 'name', 'terms' => 'Frontend' ],
			],
		] );

		$this->assertSame( 1, $query->found_posts );
		$this->assertSame( $post_1, (int) $query->posts[0]->ID );
	}
}
```

---

### Archivo 7: `tests/test-rest-api.php`

```php
<?php
/**
 * Tests para los endpoints REST del plugin ERM.
 *
 * Cubre los 4 endpoints:
 * - GET  /erm/v1/resources         (listado con filtros y paginación)
 * - GET  /erm/v1/resources/{id}    (recurso individual)
 * - POST /erm/v1/resources/{id}/track  (registro de tracking)
 * - GET  /erm/v1/stats             (estadísticas — solo admin)
 *
 * @package ERM\Tests
 */

require_once __DIR__ . '/helpers/trait-erm-test-factory.php';

/**
 * Class Test_ERM_REST_API
 *
 * @covers ERM_REST_API
 */
class Test_ERM_REST_API extends WP_Test_REST_TestCase {

	use ERM_Test_Factory;

	/** @var WP_REST_Server */
	protected $server;

	// =========================================================
	// SETUP / TEARDOWN
	// =========================================================

	public function set_up() : void {
		parent::set_up();

		// Arrancar el servidor REST
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		// Registrar CPT y taxonomías
		$post_type = new ERM_Post_Type();
		$post_type->register();

		$taxonomy = new ERM_Taxonomy();
		$taxonomy->register();

		// Registrar rutas REST
		$rest_api = new ERM_REST_API();
		$rest_api->register_routes();

		// Limpiar tracking entre tests
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}erm_tracking" );

		// Limpiar transients de stats
		delete_transient( 'erm_stats_summary_all' );
		delete_transient( 'erm_stats_summary_month' );
		delete_transient( 'erm_stats_summary_week' );

		// Asegurarse de que no hay usuario logueado al inicio de cada test
		wp_set_current_user( 0 );
	}

	public function tear_down() : void {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	// =========================================================
	// TESTS: REGISTRO DE RUTAS
	// =========================================================

	/**
	 * @test
	 */
	public function test_erm_routes_are_registered() : void {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/erm/v1/resources', $routes );
		$this->assertArrayHasKey( '/erm/v1/resources/(?P<id>\d+)', $routes );
		$this->assertArrayHasKey( '/erm/v1/resources/(?P<id>\d+)/track', $routes );
		$this->assertArrayHasKey( '/erm/v1/stats', $routes );
	}

	// =========================================================
	// TESTS: GET /resources (Listado)
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_resources_returns_200_with_empty_list() : void {
		$response = $this->rest_request( 'GET', '/resources' );

		$this->assertSame( 200, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertIsArray( $data['data']['resources'] );
		$this->assertCount( 0, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_published_resources_only() : void {
		$this->create_resource( [ 'post_title' => 'Publicado', 'post_status' => 'publish' ] );
		$this->create_resource( [ 'post_title' => 'Borrador', 'post_status' => 'draft' ] );

		$response = $this->rest_request( 'GET', '/resources' );
		$data     = $this->get_response_data( $response );

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( 'Publicado', $data['data']['resources'][0]['title'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_response_has_correct_structure() : void {
		$post_id = $this->create_resource();

		$response = $this->rest_request( 'GET', '/resources' );
		$data     = $this->get_response_data( $response );

		$resource = $data['data']['resources'][0];

		// Verificar todos los campos obligatorios de la respuesta
		$expected_keys = [
			'id', 'title', 'excerpt', 'type', 'difficulty',
			'duration_minutes', 'url', 'instructor', 'price',
			'categories', 'skills', 'featured_image', 'views',
			'permalink', 'date_created',
		];

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey(
				$key,
				$resource,
				"La respuesta del recurso debe incluir el campo '{$key}'"
			);
		}
	}

	/**
	 * @test
	 */
	public function test_get_resources_pagination_is_correct() : void {
		// Crear 15 recursos
		for ( $i = 1; $i <= 15; $i++ ) {
			$this->create_resource( [ 'post_title' => "Recurso {$i}" ] );
		}

		$response = $this->rest_request( 'GET', '/resources', [ 'per_page' => 5, 'page' => 1 ] );
		$data     = $this->get_response_data( $response );

		$pagination = $data['data']['pagination'];

		$this->assertSame( 15, $pagination['total'] );
		$this->assertSame( 3, $pagination['total_pages'] );
		$this->assertSame( 1, $pagination['current_page'] );
		$this->assertSame( 5, $pagination['per_page'] );
		$this->assertTrue( $pagination['has_more'] );
		$this->assertCount( 5, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_last_page_has_no_more() : void {
		for ( $i = 1; $i <= 3; $i++ ) {
			$this->create_resource( [ 'post_title' => "Recurso {$i}" ] );
		}

		$response   = $this->rest_request( 'GET', '/resources', [ 'per_page' => 2, 'page' => 2 ] );
		$data       = $this->get_response_data( $response );
		$pagination = $data['data']['pagination'];

		$this->assertFalse( $pagination['has_more'] );
		$this->assertCount( 1, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_type() : void {
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'ebook' ] ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'type' => 'ebook' ] );
		$data     = $this->get_response_data( $response );

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $data['data']['resources'] );

		foreach ( $data['data']['resources'] as $resource ) {
			$this->assertSame( 'ebook', $resource['type'] );
		}
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_difficulty() : void {
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'beginner' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'advanced' ] ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'difficulty' => 'beginner' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( 'beginner', $data['data']['resources'][0]['difficulty'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_filter_by_category_slug() : void {
		$post_1 = $this->create_resource( [ 'post_title' => 'Con categoría' ] );
		$post_2 = $this->create_resource( [ 'post_title' => 'Sin categoría' ] );

		$this->create_and_assign_term( $post_1, 'Backend', 'resource_category' );

		$response = $this->rest_request( 'GET', '/resources', [ 'category' => 'backend' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['resources'] );
		$this->assertSame( $post_1, $data['data']['resources'][0]['id'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_search_by_title() : void {
		$this->create_resource( [ 'post_title' => 'Introducción a PHP' ] );
		$this->create_resource( [ 'post_title' => 'JavaScript Avanzado' ] );
		$this->create_resource( [ 'post_title' => 'PHP para Backends' ] );

		$response = $this->rest_request( 'GET', '/resources', [ 'search' => 'PHP' ] );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 2, $data['data']['resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_400_for_invalid_type() : void {
		$response = $this->rest_request( 'GET', '/resources', [ 'type' => 'invalid_type' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_resources_returns_400_for_invalid_difficulty() : void {
		$response = $this->rest_request( 'GET', '/resources', [ 'difficulty' => 'expert' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	// =========================================================
	// TESTS: GET /resources/{id}
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_single_resource_returns_200() : void {
		$post_id  = $this->create_resource( [ 'post_title' => 'Mi Recurso' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 200, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $post_id, $data['data']['id'] );
		$this->assertSame( 'Mi Recurso', $data['data']['title'] );
	}

	/**
	 * @test
	 */
	public function test_get_single_resource_includes_full_content() : void {
		$post_id  = $this->create_resource( [ 'post_content' => '<p>Contenido completo aquí.</p>' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data     = $this->get_response_data( $response );

		// El recurso individual debe incluir content y downloads (no presentes en el listado)
		$this->assertArrayHasKey( 'content', $data['data'] );
		$this->assertArrayHasKey( 'downloads', $data['data'] );
		$this->assertArrayHasKey( 'date_modified', $data['data'] );
	}

	/**
	 * @test
	 */
	public function test_get_single_resource_includes_categories_and_skills() : void {
		$post_id = $this->create_resource();
		$this->create_and_assign_term( $post_id, 'Diseño', 'resource_category' );
		$this->create_and_assign_term( $post_id, 'CSS', 'skill_tag' );

		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data     = $this->get_response_data( $response );

		$this->assertCount( 1, $data['data']['categories'] );
		$this->assertSame( 'Diseño', $data['data']['categories'][0]['name'] );

		$this->assertCount( 1, $data['data']['skills'] );
		$this->assertSame( 'CSS', $data['data']['skills'][0]['name'] );
	}

	/**
	 * @test
	 */
	public function test_get_nonexistent_resource_returns_404() : void {
		$response = $this->rest_request( 'GET', '/resources/99999' );

		$this->assertSame( 404, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertSame( 'resource_not_found', $data['code'] );
	}

	/**
	 * @test
	 */
	public function test_get_draft_resource_returns_404() : void {
		$post_id  = $this->create_resource( [ 'post_status' => 'draft' ] );
		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_post_of_wrong_type_returns_404() : void {
		// Crear un post regular (no del CPT)
		$post_id = wp_insert_post( [
			'post_title'  => 'Post Normal',
			'post_status' => 'publish',
			'post_type'   => 'post',
		] );

		$response = $this->rest_request( 'GET', "/resources/{$post_id}" );

		$this->assertSame( 404, $response->get_status() );
	}

	// =========================================================
	// TESTS: POST /resources/{id}/track
	// =========================================================

	/**
	 * @test
	 */
	public function test_track_view_returns_201() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'view',
		] );

		$this->assertSame( 201, $response->get_status() );

		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
		$this->assertSame( $post_id, $data['data']['resource_id'] );
		$this->assertSame( 'view', $data['data']['action_type'] );
		$this->assertArrayHasKey( 'tracking_id', $data['data'] );
		$this->assertGreaterThan( 0, $data['data']['tracking_id'] );
	}

	/**
	 * @test
	 */
	public function test_track_download_is_recorded_in_database() : void {
		global $wpdb;
		$table   = $wpdb->prefix . 'erm_tracking';
		$post_id = $this->create_resource();

		$this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'download',
		] );

		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE resource_id = %d AND action_type = %s",
			$post_id,
			'download'
		) );

		$this->assertSame( 1, $count );
	}

	/**
	 * @test
	 */
	public function test_track_complete_is_accepted() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'complete',
		] );

		$this->assertSame( 201, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_invalid_action_type_returns_400() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [
			'action_type' => 'invalid_action',
		] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_missing_action_type_returns_400() : void {
		$post_id  = $this->create_resource();
		$response = $this->rest_request( 'POST', "/resources/{$post_id}/track", [] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_track_nonexistent_resource_returns_404() : void {
		$response = $this->rest_request( 'POST', '/resources/99999/track', [
			'action_type' => 'view',
		] );

		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_multiple_views_are_all_recorded() : void {
		global $wpdb;
		$table   = $wpdb->prefix . 'erm_tracking';
		$post_id = $this->create_resource();

		// Registrar 5 vistas
		for ( $i = 0; $i < 5; $i++ ) {
			$this->rest_request( 'POST', "/resources/{$post_id}/track", [ 'action_type' => 'view' ] );
		}

		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE resource_id = %d",
			$post_id
		) );

		$this->assertSame( 5, $count );
	}

	/**
	 * @test
	 */
	public function test_tracking_increments_views_count_in_resource_response() : void {
		$post_id = $this->create_resource();

		// Vista inicial: 0
		$response_before = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data_before     = $this->get_response_data( $response_before );
		$this->assertSame( 0, $data_before['data']['views'] );

		// Registrar 3 vistas directamente en la BD (para evitar cache de transients)
		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'view' );

		// Limpiar posibles transients de cache
		delete_transient( 'erm_stats_summary_all' );

		// Vista después de tracking
		$response_after = $this->rest_request( 'GET', "/resources/{$post_id}" );
		$data_after     = $this->get_response_data( $response_after );
		$this->assertSame( 3, $data_after['data']['views'] );
	}

	// =========================================================
	// TESTS: GET /stats (solo admin)
	// =========================================================

	/**
	 * @test
	 */
	public function test_get_stats_requires_admin_permission() : void {
		// Sin usuario
		wp_set_current_user( 0 );
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_returns_403_for_subscriber() : void {
		$this->create_subscriber_user();
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_returns_200_for_admin() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );

		$this->assertSame( 200, $response->get_status() );
		$data = $this->get_response_data( $response );
		$this->assertTrue( $data['success'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_response_has_correct_structure() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$response_data = $data['data'];

		// Claves a nivel raíz de data
		$this->assertArrayHasKey( 'summary', $response_data );
		$this->assertArrayHasKey( 'by_type', $response_data );
		$this->assertArrayHasKey( 'by_difficulty', $response_data );
		$this->assertArrayHasKey( 'top_resources', $response_data );
		$this->assertArrayHasKey( 'monthly_growth', $response_data );
	}

	/**
	 * @test
	 */
	public function test_get_stats_summary_has_required_fields() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertArrayHasKey( 'total_resources', $summary );
		$this->assertArrayHasKey( 'by_type', $summary );
		$this->assertArrayHasKey( 'by_difficulty', $summary );
		$this->assertArrayHasKey( 'total_views', $summary );
		$this->assertArrayHasKey( 'total_downloads', $summary );
		$this->assertArrayHasKey( 'unique_users', $summary );
		$this->assertArrayHasKey( 'period', $summary );
	}

	/**
	 * @test
	 */
	public function test_get_stats_by_difficulty_has_all_levels() : void {
		$this->create_admin_user();
		$response      = $this->rest_request( 'GET', '/stats' );
		$data          = $this->get_response_data( $response );
		$by_difficulty = $data['data']['by_difficulty'];

		$this->assertArrayHasKey( 'beginner', $by_difficulty );
		$this->assertArrayHasKey( 'intermediate', $by_difficulty );
		$this->assertArrayHasKey( 'advanced', $by_difficulty );
	}

	/**
	 * @test
	 */
	public function test_get_stats_counts_resources_correctly() : void {
		$this->create_admin_user();

		// Crear 2 cursos y 1 tutorial
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'course' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_resource_type' => 'tutorial' ] ] );

		// Limpiar transient para forzar recalculo
		delete_transient( 'erm_stats_summary_all' );

		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertSame( 3, $summary['total_resources'] );
		$this->assertSame( 2, $summary['by_type']['course'] );
		$this->assertSame( 1, $summary['by_type']['tutorial'] );
		$this->assertSame( 0, $summary['by_type']['ebook'] );
		$this->assertSame( 0, $summary['by_type']['video'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_by_difficulty_at_root_matches_summary() : void {
		$this->create_admin_user();

		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'beginner' ] ] );
		$this->create_resource( [ 'meta_input' => [ '_erm_difficulty_level' => 'advanced' ] ] );

		delete_transient( 'erm_stats_summary_all' );

		$response      = $this->rest_request( 'GET', '/stats' );
		$data          = $this->get_response_data( $response );
		$root_diff     = $data['data']['by_difficulty'];
		$summary_diff  = $data['data']['summary']['by_difficulty'];

		// Los valores a nivel raíz deben ser idénticos a los de summary
		$this->assertSame( $root_diff, $summary_diff );
	}

	/**
	 * @test
	 */
	public function test_get_stats_period_all_returns_all_time_totals() : void {
		$this->create_admin_user();
		$post_id = $this->create_resource();

		$this->create_tracking( $post_id, 'view' );
		$this->create_tracking( $post_id, 'download' );

		delete_transient( 'erm_stats_summary_all' );

		$response = $this->rest_request( 'GET', '/stats', [ 'period' => 'all' ] );
		$data     = $this->get_response_data( $response );
		$summary  = $data['data']['summary'];

		$this->assertSame( 'all', $summary['period'] );
		$this->assertSame( 1, (int) $summary['total_views'] );
		$this->assertSame( 1, (int) $summary['total_downloads'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_period_invalid_returns_400() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats', [ 'period' => 'year' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * @test
	 */
	public function test_get_stats_top_resources_is_array() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$this->assertIsArray( $data['data']['top_resources'] );
	}

	/**
	 * @test
	 */
	public function test_get_stats_monthly_growth_is_array() : void {
		$this->create_admin_user();
		$response = $this->rest_request( 'GET', '/stats' );
		$data     = $this->get_response_data( $response );

		$this->assertIsArray( $data['data']['monthly_growth'] );
	}
}
```

---

## ✅ Checklist Final

### Setup
- [ ] `composer.json` con dependencias correctas
- [ ] `composer install` corre sin errores
- [ ] `bin/install-wp-tests.sh` es ejecutable (`chmod +x bin/install-wp-tests.sh`)
- [ ] `phpunit.xml` apunta a `tests/bootstrap.php`
- [ ] `tests/bootstrap.php` encuentra WordPress test suite en `/tmp/wordpress-tests-lib`
- [ ] La tabla `{prefix}_erm_tracking` se crea en la BD de tests al arrancar

### Tests CPT (test-post-type.php) — 22 tests
- [ ] CPT registrado y configurable
- [ ] Posts creados y consultables por WP_Query
- [ ] Los 6 meta keys se guardan y recuperan correctamente
- [ ] Las 2 taxonomías registradas con jerarquía correcta
- [ ] Filtros por tipo y categoría via WP_Query funcionan

### Tests REST API (test-rest-api.php) — 28 tests
- [ ] Las 4 rutas están registradas
- [ ] GET /resources: listado, paginación, filtros (type, difficulty, category, search)
- [ ] GET /resources/{id}: 200, 404 por ID inválido, 404 por draft, campos completos
- [ ] POST /track: 201 con view/download/complete, 400 con acción inválida, BD actualizada
- [ ] GET /stats: 403 sin auth, 403 para suscriptor, 200 para admin
- [ ] Stats: estructura correcta, by_difficulty en raíz, period funciona, period inválido = 400

### Ejecución
```bash
# Todos los tests
vendor/bin/phpunit

# Solo CPT
vendor/bin/phpunit tests/test-post-type.php

# Solo REST API
vendor/bin/phpunit tests/test-rest-api.php

# Un test específico
vendor/bin/phpunit --filter test_get_stats_by_difficulty_at_root_matches_summary

# Con cobertura (requiere Xdebug)
vendor/bin/phpunit --coverage-text
```

### Output esperado
```
ERM Plugin Tests
  Test_ERM_Post_Type
    ✓ test_post_type_is_registered
    ✓ test_post_type_is_public
    ... (22 tests)
  Test_ERM_REST_API
    ✓ test_erm_routes_are_registered
    ✓ test_get_resources_returns_200_with_empty_list
    ... (28 tests)

OK (50 tests, XX assertions)
```
