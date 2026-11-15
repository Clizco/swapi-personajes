# Sistema de Gestión de Personajes Star Wars

Aplicación web para gestionar personajes de Star Wars (CRUD completo) con importación desde la API pública [SWAPI](https://swapi.py4e.com/documentation).

## Información del Candidato

Nombre: Abraham Gonzalez
Email: [TU EMAIL]
Teléfono: [TU TELÉFONO]

## Requisitos del Sistema

- PHP 8.3+ (Laravel 13 lo requiere) con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`
- Composer
- Node.js 18+
- MySQL 8+ (o MariaDB 10.6+)

## Instalación

1. Clonar repositorio

```bash
git clone [tu-repo]
cd swapi-personajes
```

2. Instalar dependencias

```bash
composer install
npm install
```

3. Configurar base de datos

```bash
cp .env.example .env
```

Editar `.env` con las credenciales de MySQL (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y crear la base de datos indicada en `DB_DATABASE`. Luego:

```bash
php artisan key:generate
```

4. Ejecutar migraciones (y opcionalmente datos de prueba)

```bash
php artisan migrate
php artisan db:seed
```

5. Compilar assets

```bash
npm run build
```

6. Iniciar servidor

```bash
php artisan serve
```

7. Acceder

http://localhost:8000/personajes

### Alternativa con Docker

```bash
docker compose up --build
```

Levanta MySQL 8.4 y la aplicación en http://localhost:8000/personajes (migra y carga datos de prueba automáticamente). MySQL queda expuesto en el puerto `3307` del host para no chocar con una instalación local.

### Pruebas

```bash
php artisan test
```

Las pruebas usan SQLite en memoria y simulan SWAPI con `Http::fake()`, así que no necesitan MySQL ni conexión a internet.

### Importar varios personajes por consola

```bash
php artisan swapi:importar 1 4 10
php artisan swapi:importar --desde=1 --hasta=20
```

## Funcionalidades Implementadas

Obligatorias:
- CRUD completo de personajes (`/personajes`, `/personajes/crear`, `/personajes/{id}/editar`, `PUT` y `DELETE`)
- Integración con SWAPI (`POST /personajes/importar`) con mapeo inglés → español y bloqueo de duplicados
- Validaciones backend (Form Requests) y frontend (antes de enviar), con mensajes en español
- Paginación de 10 por página
- Componentes Vue reutilizables (`<script setup>` + `useForm()`)
- Diseño responsive: tabla en escritorio y tarjetas en móvil
- Estados de carga (spinners, botones deshabilitados) y mensajes de éxito/error

Bonus:
- Service Layer: `PersonajeService` y `SwapiService`
- Pruebas automatizadas: 48 tests (feature + unit) con PHPUnit
- Soft Deletes (un personaje de SWAPI eliminado se puede volver a importar y se restaura)
- Comando Artisan `swapi:importar` para importar varios personajes
- Búsqueda en tiempo real por nombre (con debounce de 350 ms)
- Filtro por género
- Ordenamiento de la tabla (nombre, altura, masa, género)
- Animaciones con Tailwind (modales, toasts, entrada de páginas)
- Dark mode (respeta la preferencia del sistema y se recuerda en `localStorage`)
- Docker Compose funcional (app + MySQL)
- Seeders con datos de prueba (factory con 25 personajes)
- GitHub Actions: corre las pruebas en SQLite y en MySQL en cada push

## Tecnologías

- Laravel 13.x (la prueba pide 10+; `laravel new` instala la última versión)
- Vue.js 3.5 (Composition API, `<script setup>`)
- Inertia.js 3
- Tailwind CSS 4
- Vite 8
- MySQL 8

## Notas

- **Datos de SWAPI que no encajan en las reglas.** Algunos personajes traen valores como `"unknown"` o `"1,358"` (la masa de Jabba). El servicio quita el separador de miles y redondea decimales. Si el valor no es numérico o queda fuera del rango permitido (altura 1–300, masa 1–500), lo guarda como `null`. Así todo lo importado cumple las mismas reglas que un registro manual y se puede editar sin errores de validación.
- **Géneros fuera del mapeo.** SWAPI también devuelve `hermaphrodite` o `none`. Como `genero` solo acepta `masculino`, `femenino` o `n/a`, cualquier valor distinto de `male`/`female` se guarda como `n/a`.
- **Planeta de origen.** SWAPI devuelve una URL en `homeworld`, así que se hace una segunda consulta para obtener el nombre del planeta. Si esa consulta falla, el personaje se importa igual, con `planeta_origen = null`. Solo se siguen URLs que empiezan con la URL base configurada.
- **Personajes inexistentes.** El ID 17 no existe en SWAPI (devuelve 404); la app lo informa en el modal.
- La URL de SWAPI y el timeout se configuran en `.env` con `SWAPI_BASE_URL` y `SWAPI_TIMEOUT`.

---

## Preguntas técnicas

### 1. Organización del código backend

Usé controladores delgados con una capa de servicios:

- `PersonajeController` solo recibe la petición, delega y devuelve la respuesta (página Inertia o redirect con mensaje flash).
- `PersonajeRequest` e `ImportarPersonajeRequest` (Form Requests) concentran las validaciones.
- `PersonajeService` tiene la lógica de negocio: listar con filtros, orden y paginación; crear, actualizar y eliminar; e importar desde SWAPI, incluida la regla de no duplicar y la restauración de registros eliminados.
- `SwapiService` es lo único que sabe hablar con SWAPI: hace la petición HTTP con timeout y reintentos, traduce los campos al español y convierte los errores en una `SwapiException` con mensajes claros. Se registra como singleton en `AppServiceProvider` con la URL y el timeout de `config/services.php`.
- El modelo `Personaje` define los géneros válidos, las columnas ordenables y dos scopes (`buscarPorNombre`, `delGenero`).

Esta separación sirve para reutilizar la importación tanto desde el controlador como desde el comando Artisan, y para probar el mapeo de SWAPI de forma aislada. No usé Repository Pattern porque Eloquent ya cumple ese papel y, en una app de este tamaño, agregaría una capa sin beneficio real.

### 2. Componentes Vue

- `PersonajeForm.vue`: un solo formulario para crear y editar. Recibe el personaje inicial, el método (`post`/`put`), la URL y el texto del botón. Dentro maneja `useForm()`, la validación del frontend y los errores del backend.
- `PersonajeTable.vue`: muestra la lista como tabla en escritorio y como tarjetas en móvil. No hace peticiones: emite eventos (`ordenar`, `eliminar`), así que se puede usar con cualquier lista.
- `ImportModal.vue`: modal con su propio `useForm()` para importar desde SWAPI, con estado de carga y errores.
- `BaseModal.vue`: modal genérico (Teleport, animación, cierre con Esc o clic afuera, bloqueo mientras procesa). Lo usan `ImportModal` y `ConfirmDialog`.
- `ConfirmDialog.vue`: confirmación genérica, aquí para eliminar, pero sirve para cualquier acción destructiva.
- `Pagination.vue`: recibe directamente el paginador de Laravel.
- `BaseButton.vue`, `CampoFormulario.vue`, `Spinner.vue` y `FlashMessage.vue`: piezas de UI comunes (botón con variantes y estado de carga, label + input + error, indicador de carga y toast de mensajes).
- `AppLayout.vue`: layout con cabecera, título, slot de acciones y modo oscuro.

Son reutilizables porque reciben todo por props, comunican hacia afuera con eventos y no dependen de una página concreta.

### 3. Manejo de errores de SWAPI

Todo está en `SwapiService` y se traduce a `SwapiException`, que el controlador muestra en el modal como error del campo `swapi_id`:

- **Timeout o API caída:** timeout configurable (10 s por defecto) y 2 reintentos solo ante errores de conexión. Si siguen fallando, se muestra "No se pudo conectar con SWAPI…" y se registra en el log.
- **ID inexistente (404):** "No existe un personaje con el ID X en SWAPI."
- **Errores 4xx/5xx:** mensaje con el código HTTP.
- **Respuesta con formato inesperado** (no es JSON o no trae `name`): mensaje específico.
- **ID inválido o duplicado:** se rechaza en el Form Request antes de llamar a la API (entero entre 1 y 83, `swapi_id` único). Además, el servicio vuelve a comprobarlo y captura la violación del índice único por si llegan dos peticiones a la vez.
- **Falla al consultar el planeta:** no bloquea la importación; ese campo queda vacío.

Cada caso tiene su prueba en `tests/Feature/ImportarPersonajeTest.php`, que simula SWAPI con `Http::fake()`.

### 4. Validaciones

En dos capas:

- **Backend (fuente de verdad):** Form Requests con todas las reglas de la prueba, mensajes en español (`lang/es/validation.php`) y `trim` de los textos antes de validar. Con `validated()` solo se guardan los campos permitidos, así que nadie puede cambiar `origen_swapi` o `swapi_id` desde el formulario.
- **Frontend:** `PersonajeForm` e `ImportModal` revisan obligatorios, longitudes y rangos antes de enviar, para dar respuesta inmediata sin ir al servidor. Los errores se escriben con `form.setError()`, así que se ven igual que los del backend.

Elegí esta estrategia porque la validación del cliente se puede saltar fácilmente, así que la del servidor es obligatoria. La del cliente solo mejora la experiencia.

### 5. Mayor dificultad

<!-- Revisa y ajusta esta respuesta con tus propias palabras -->
Lo más delicado fueron los datos reales de SWAPI, que no siempre encajan con las reglas de la tabla: alturas `"unknown"`, la masa de Jabba como `"1,358"` (texto con coma y mayor al máximo de 500) y géneros como `hermaphrodite`. Lo resolví con funciones de conversión en `SwapiService` y pruebas unitarias para cada caso.

También había que decidir cómo convivían el soft delete y el `swapi_id` único: si un personaje importado se elimina, el índice único impediría volver a importarlo. Lo resolví restaurando y actualizando el registro eliminado en lugar de crear uno nuevo.

### 6. Uso de Inteligencia Artificial

<!-- Revisa y ajusta esta respuesta con tus propias palabras -->
Sí. Usé Claude (Claude Code) para generar la mayor parte del código: estructura del backend, servicios, componentes Vue, pruebas, Docker y este README. Para comprobar que funcionaba:

- Corrí la suite de pruebas (48 tests) que cubre CRUD, validaciones, todos los errores de SWAPI y el comando Artisan.
- Probé manualmente en el navegador crear, editar, eliminar, buscar, filtrar, ordenar, importar (incluidos duplicados y el ID 17) y la vista móvil.
- Importé personajes reales desde SWAPI para revisar el mapeo con datos verdaderos.
- Revisé el código para poder explicarlo y modificarlo.

### 7. Mejoras futuras

- Traducir al español los colores que vienen de SWAPI (hoy se guardan como llegan, p. ej. `blond`, `blue`).
- Cachear las respuestas de SWAPI (sobre todo los planetas) para no repetir consultas.
- Importación masiva desde la interfaz, con una cola (jobs) y progreso.
- Papelera para ver y restaurar personajes eliminados.
- Ordenamiento también en la vista móvil, y pruebas end-to-end (Pest Browser o Playwright).
- Autenticación y permisos si la app se usara en producción.

### 8. Autoevaluación

<!-- Revisa y ajusta esta respuesta con tus propias palabras -->
8/10. Cumple todos los requisitos obligatorios y la mayoría de los bonus, con buena cobertura de pruebas. Lo mejor es el manejo de errores de SWAPI y la separación en servicios, que hace el código fácil de probar y de cambiar. Me resto puntos porque gran parte del código lo generó una IA y porque quedan mejoras pendientes, como la caché de SWAPI y la papelera.

### 9. Tiempo invertido

[X horas]
