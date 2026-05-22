# Education Resources Manager

Plugin de WordPress para gestionar recursos educativos: Custom Post Type, taxonomías, seguimiento de vistas y descargas, REST API, shortcode con filtros y panel de administración con estadísticas.

## Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.3+

## Instalación

### Vía ZIP (recomendado)

1. Descarga o genera un ZIP de la carpeta `education-resources-manager`.
2. En el escritorio de WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**.
3. Selecciona el archivo ZIP e instala.
4. Activa el plugin **Education Resources Manager**.

### Vía FTP / SFTP

1. Sube la carpeta `education-resources-manager` a `wp-content/plugins/`.
2. En **Plugins**, activa **Education Resources Manager**.

Tras la activación, el plugin registra sus hooks y opciones de versión. Los agentes posteriores del proyecto completan CPT, base de datos, REST API, shortcode y panel admin.

## Uso del shortcode

Inserta en cualquier entrada, página o plantilla:

```
[recursos_educativos]
```

### Atributos

| Atributo     | Valores                                      | Descripción                          |
|-------------|-----------------------------------------------|--------------------------------------|
| `type`      | `course`, `tutorial`, `ebook`, `video`        | Filtra por tipo de recurso           |
| `difficulty`| `beginner`, `intermediate`, `advanced`        | Filtra por nivel de dificultad       |
| `category`  | slug de `resource_category`                   | Filtra por categoría                 |
| `per_page`  | número entero (por defecto: `10`)             | Recursos por página                  |

### Ejemplos

```
[recursos_educativos type="course" difficulty="beginner"]
[recursos_educativos category="programacion" per_page="6"]
[recursos_educativos type="video" difficulty="advanced" per_page="12"]
```

## REST API

Namespace: `erm/v1` — URL base: `/wp-json/erm/v1`

| Método | Ruta                         | Descripción                    |
|--------|------------------------------|--------------------------------|
| GET    | `/resources`                 | Lista recursos con filtros     |
| GET    | `/resources/{id}`            | Detalle de un recurso          |
| POST   | `/resources/{id}/track`      | Registra vista, descarga, etc. |
| GET    | `/stats`                     | Estadísticas agregadas         |

Consulta `docs/API.md` (Agent 07) para parámetros, respuestas y ejemplos completos.

## Desinstalación

1. Desactiva el plugin desde **Plugins**.
2. Pulsa **Eliminar**.

El archivo `uninstall.php` elimina las opciones `erm_version` y `erm_db_version`, la tabla `{prefix}_erm_tracking` y los transients con prefijo `erm_`. **No** borra los posts del tipo `education_resource` (contenido del sitio).

## Changelog

### 1.0.0

- Scaffolding inicial: estructura de carpetas, loader de hooks, activación/desactivación y desinstalación limpia.
- Stubs de clases para CPT, taxonomías, base de datos, admin, shortcode y REST API (completados por agentes 02–06).
