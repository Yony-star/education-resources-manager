soluciona lo que no se cumple del siguiente reporte:


3. Frontend (README.md)
Requisito	Estado	Notas
Shortcode [recursos_educativos]
✅
class-erm-shortcode.php
Paginación
✅
REST + erm-public.js
Filtros AJAX: tipo, nivel, búsqueda
✅
Sin recargar página
Filtro por categoría en UI
⚠️
Solo vía atributo del shortcode category="slug", no hay <select> de categorías en el template
Tarjetas (título, tipo, nivel, duración, botón + tracking)
✅
trackResource() → POST /track
Loading states
✅
#erm-loading + spinner
Uso REST API
✅
fetch a erm/v1/resources
Validación de formularios
⚠️
No hay validación explícita (p. ej. búsqueda vacía/mínimo caracteres); el enunciado es genérico y aquí casi no hay formularios POST