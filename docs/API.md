# REST API — Education Resources Manager

## 1. Información general

| Campo | Valor |
|-------|-------|
| Namespace | `erm/v1` |
| URL base | `{site_url}/wp-json/erm/v1` |
| Formato | JSON |
| Envelope | `{ "success": true\|false, "data": {...}, "message"?: "..." }` |

Los endpoints de lectura y tracking son públicos (sin login). `/stats` requiere usuario con `manage_options`.

---

## 2. Autenticación

### Endpoints públicos

- `GET /resources`
- `GET /resources/{id}`
- `POST /resources/{id}/track`

### Endpoints protegidos

- `GET /stats` → capability `manage_options` (403 si no autorizado)

### Desde el frontend (shortcode)

El script localiza `ermPublic.nonce` con `wp_create_nonce('wp_rest')`. Enviar en cabecera:

```
X-WP-Nonce: {nonce}
```

### Desde aplicaciones externas

- Usuario administrador + [Application Passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/) (WordPress 5.6+).
- Autenticación básica en entornos de desarrollo (según configuración del servidor).

Ejemplo:

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  "https://tusitio.local/wp-json/erm/v1/stats"
```

---

## 3. Endpoints

### 3.1 Listar recursos

**`GET /resources`**

Lista recursos publicados con filtros y paginación.

#### Parámetros (query)

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | int | `1` | Página (mín. 1) |
| `per_page` | int | `10` | Por página (1–100) |
| `search` | string | — | Búsqueda en título/contenido |
| `type` | string | — | `course`, `tutorial`, `ebook`, `video` |
| `difficulty` | string | — | `beginner`, `intermediate`, `advanced` |
| `category` | string | — | Slug de `resource_category` |
| `skill` | string | — | Slug de `skill_tag` |
| `orderby` | string | `date` | Ordenar por: `date`, `title`, `views`. Con `views`, se consulta `erm_tracking` para ordenar por conteo real antes de hidratar los posts. |
| `order` | string | `DESC` | `ASC` o `DESC` |

#### Ejemplo cURL

```bash
curl "https://tusitio.local/wp-json/erm/v1/resources?type=course&difficulty=beginner&per_page=6&page=1"
```

#### Respuesta 200 (ejemplo)

```json
{
  "success": true,
  "data": {
    "resources": [
      {
        "id": 42,
        "title": "Introducción a PHP",
        "excerpt": "Curso básico de PHP para WordPress.",
        "type": "course",
        "difficulty": "beginner",
        "duration_minutes": 120,
        "url": "https://ejemplo.com/curso-php",
        "instructor": "María García",
        "price": 0,
        "categories": [
          { "id": 3, "name": "Programación", "slug": "programacion", "parent_id": 0 }
        ],
        "skills": [
          { "id": 8, "name": "PHP", "slug": "php" }
        ],
        "featured_image": "https://tusitio.local/wp-content/uploads/2024/01/curso.jpg",
        "views": 15,
        "permalink": "https://tusitio.local/recursos/introduccion-php/",
        "date_created": "2024-01-15T10:00:00+00:00"
      }
    ],
    "pagination": {
      "total": 1,
      "total_pages": 1,
      "current_page": 1,
      "per_page": 6,
      "has_more": false
    }
  }
}
```

---

### 3.2 Detalle de un recurso

**`GET /resources/{id}`**

Devuelve el recurso completo (incluye `content` y `downloads`).

#### Parámetros (ruta)

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | int | Sí | ID del post `education_resource` |

#### Ejemplo cURL

```bash
curl "https://tusitio.local/wp-json/erm/v1/resources/42"
```

#### Respuesta 200 (ejemplo)

```json
{
  "success": true,
  "data": {
    "id": 42,
    "title": "Introducción a PHP",
    "excerpt": "Curso básico de PHP para WordPress.",
    "type": "course",
    "difficulty": "beginner",
    "duration_minutes": 120,
    "url": "https://ejemplo.com/curso-php",
    "instructor": "María García",
    "price": 29.99,
    "categories": [],
    "skills": [],
    "featured_image": null,
    "views": 15,
    "permalink": "https://tusitio.local/recursos/introduccion-php/",
    "date_created": "2024-01-15T10:00:00+00:00",
    "content": "<p>Contenido HTML filtrado...</p>",
    "downloads": 3,
    "date_modified": "2024-02-01T14:30:00+00:00"
  }
}
```

#### Error 404

```json
{
  "code": "resource_not_found",
  "message": "El recurso solicitado no existe.",
  "data": { "status": 404 }
}
```

---

### 3.3 Registrar tracking

**`POST /resources/{id}/track`**

Inserta un evento en `{prefix}_erm_tracking`.

#### Parámetros

| Ubicación | Nombre | Tipo | Requerido | Valores |
|-----------|--------|------|-----------|---------|
| Ruta | `id` | int | Sí | ID del recurso |
| Body JSON | `action_type` | string | Sí | `view`, `download`, `complete` |

#### Ejemplo cURL

```bash
curl -X POST "https://tusitio.local/wp-json/erm/v1/resources/42/track" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: TU_NONCE_REST" \
  -d '{"action_type":"view"}'
```

#### Respuesta 201

```json
{
  "success": true,
  "data": {
    "tracking_id": 128,
    "resource_id": 42,
    "action_type": "view",
    "timestamp": "2024-05-22 15:30:00"
  },
  "message": "Acción registrada exitosamente."
}
```

#### Errores

| Código HTTP | `code` | Cuándo |
|-------------|--------|--------|
| 404 | `resource_not_found` | Post inexistente o no es `education_resource` |
| 500 | `tracking_failed` | Fallo al insertar en BD |
| 400 | `rest_invalid_param` | `action_type` inválido (validación REST) |

---

### 3.4 Estadísticas (admin)

**`GET /stats`**

Resumen agregado, top 5 y crecimiento mensual. Requiere `manage_options`.

#### Parámetros

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `period` | string | `all` | Filtra los conteos de tracking (`total_views`, `total_downloads`, `unique_users`): `all` (todo), `month` (30 días), `week` (7 días). `total_resources`, `by_type` y `by_difficulty` no dependen del periodo (inventario publicado). |

#### Ejemplo cURL

```bash
curl "https://tusitio.local/wp-json/erm/v1/stats" \
  -u "admin:APPLICATION_PASSWORD"
```

#### Respuesta 200 (ejemplo)

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_resources": 12,
      "by_type": { "course": 5, "tutorial": 4, "ebook": 2, "video": 1 },
      "by_difficulty": { "beginner": 6, "intermediate": 4, "advanced": 2 },
      "total_views": 340,
      "total_downloads": 45,
      "unique_users": 28,
      "period": "all"
    },
    "by_type": { "course": 5, "tutorial": 4, "ebook": 2, "video": 1 },
    "by_difficulty": { "beginner": 6, "intermediate": 4, "advanced": 2 },
    "top_resources": [
      {
        "resource_id": "42",
        "post_title": "Introducción a PHP",
        "action_count": "87"
      }
    ],
    "monthly_growth": [
      { "month": "2024-01", "total": "2" },
      { "month": "2024-02", "total": "5" }
    ]
  }
}
```

#### Error 403

```json
{
  "code": "forbidden",
  "message": "No tienes permisos para ver las estadísticas.",
  "data": { "status": 403 }
}
```

---

## 4. Modelos de datos (TypeScript)

```typescript
interface ERM_Category {
  id: number;
  name: string;
  slug: string;
  parent_id: number;
}

interface ERM_Skill {
  id: number;
  name: string;
  slug: string;
}

interface ERM_Resource {
  id: number;
  title: string;
  excerpt: string;
  type: 'course' | 'tutorial' | 'ebook' | 'video' | '';
  difficulty: 'beginner' | 'intermediate' | 'advanced' | '';
  duration_minutes: number;
  url: string;
  instructor: string;
  price: number;
  categories: ERM_Category[];
  skills: ERM_Skill[];
  featured_image: string | null;
  views: number;
  permalink: string;
  date_created: string;
  content?: string;
  downloads?: number;
  date_modified?: string;
}

interface ERM_Pagination {
  total: number;
  total_pages: number;
  current_page: number;
  per_page: number;
  has_more: boolean;
}

interface ERM_ListResponse {
  success: boolean;
  data: {
    resources: ERM_Resource[];
    pagination: ERM_Pagination;
  };
}

interface ERM_TrackResponse {
  success: boolean;
  data: {
    tracking_id: number;
    resource_id: number;
    action_type: string;
    timestamp: string;
  };
  message: string;
}
```

---

## 5. Códigos de error del plugin

| `code` | HTTP | Descripción |
|--------|------|-------------|
| `resource_not_found` | 404 | Recurso no publicado o inexistente |
| `tracking_failed` | 500 | Error al persistir tracking |
| `forbidden` | 403 | Sin permiso en `/stats` |
| `rest_invalid_param` | 400 | Parámetro REST no válido (core WP) |

---

## 6. Ejemplos JavaScript

### Listar con filtros

```javascript
const params = new URLSearchParams({
  page: '1',
  per_page: '10',
  type: 'video',
  difficulty: 'advanced',
});

const res = await fetch(`${ermPublic.apiUrl}/resources?${params}`, {
  headers: { 'X-WP-Nonce': ermPublic.nonce },
});

const json = await res.json();
if (json.success) {
  console.log(json.data.resources, json.data.pagination);
}
```

### Registrar vista

```javascript
async function trackView(resourceId) {
  const res = await fetch(`${ermPublic.apiUrl}/resources/${resourceId}/track`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': ermPublic.nonce,
    },
    body: JSON.stringify({ action_type: 'view' }),
  });

  if (!res.ok) {
    const err = await res.json();
    console.warn(err.code, err.message);
    return;
  }

  return res.json();
}
```

### Manejo de errores

```javascript
try {
  const res = await fetch(`${ermPublic.apiUrl}/resources/99999`);
  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.message || `HTTP ${res.status}`);
  }

  if (!data.success) {
    throw new Error('Respuesta sin success');
  }
} catch (e) {
  console.error('ERM API:', e);
}
```

---

## 7. Descubrimiento

Índice REST de WordPress:

```
GET /wp-json/
GET /wp-json/erm/v1
```

Los esquemas de argumentos se exponen según el registro en `ERM_REST_API::register_routes()`.
