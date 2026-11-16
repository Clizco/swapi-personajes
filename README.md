# Sistema de Gestión de Personajes Star Wars

Aplicación web para gestionar personajes de Star Wars (CRUD completo) con importación desde la API pública [SWAPI](https://swapi.py4e.com/documentation).

## Información del Candidato

Nombre: Abraham Gonzalez
Email: gonzalezad06@gmail.com
Teléfono: +34 642 12 61 07

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

## Funcionalidades Implementadas

Obligatorias:
- CRUD completo de personajes (`/personajes`, `/personajes/crear`, `/personajes/{id}/editar`, `PUT` y `DELETE`)
- Integración con SWAPI (`POST /personajes/importar`) con mapeo inglés → español y bloqueo de duplicados
- Validaciones con Form Requests y mensajes en español, mostradas debajo de cada campo
- Paginación de 10 por página
- Componentes Vue reutilizables (`<script setup>` + `useForm()`)
- Diseño responsive (en móvil la tabla se desliza horizontalmente)
- Estados de carga (spinners, botones deshabilitados) y mensajes de éxito/error

Bonus:
- Service Layer: `PersonajeService` y `SwapiService`
- Pruebas automatizadas: 47 tests (feature + unit) con PHPUnit
- Soft Deletes (un personaje de SWAPI eliminado se puede volver a importar y se restaura)
- Búsqueda en tiempo real por nombre (con debounce de 350 ms)
- Filtro por género
- Ordenamiento de la tabla (nombre, altura, masa, género)
- Animaciones con Tailwind (modal, mensajes, entrada de páginas)
- Dark mode (respeta la preferencia del sistema y se recuerda en `localStorage`)
- Docker Compose funcional (app + MySQL)
- Seeders con datos de prueba (factory con 25 personajes)

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
- **Planeta de origen.** SWAPI devuelve una URL en `homeworld`, así que se hace una segunda consulta para obtener el nombre del planeta. Si esa consulta falla, el personaje se importa igual, con `planeta_origen = null`.- **Personajes inexistentes.** El ID 17 no existe en SWAPI (devuelve 404); la app lo informa en el modal.
- La URL de SWAPI y el timeout se configuran en `.env` con `SWAPI_BASE_URL` y `SWAPI_TIMEOUT`.

---

## Preguntas técnicas

### 1. Organización del código backend

Busqué que fuera simple de leer y de modificar, con una capa de servicios solo donde hay lógica de verdad:

- `PersonajeController`: recibe la petición y devuelve la respuesta (página Inertia o redirect con mensaje flash). Crear, editar y eliminar son una línea de Eloquent cada uno, así que van directo en el controlador.
- `PersonajeRequest` e `ImportarPersonajeRequest` (Form Requests): todas las validaciones.
- `PersonajeService`: lo que no es un simple CRUD. `listar()` aplica búsqueda, filtro por género, orden y paginación; `importarDesdeSwapi()` evita duplicados, restaura un personaje eliminado si se vuelve a importar y crea el registro.
- `SwapiService`: lo único que habla con SWAPI. Hace la petición HTTP con timeout, traduce los campos al español y convierte cada error en una `SwapiException` con un mensaje claro. Lee la URL y el timeout de `config/services.php`.
- El modelo `Personaje` define los campos asignables, los géneros válidos y las columnas por las que se puede ordenar.

Así, si mañana cambia la API, solo se toca `SwapiService`, y el mapeo se puede probar de forma aislada. No usé Repository Pattern porque Eloquent ya cumple ese papel y, en una app de este tamaño, agregaría una capa sin beneficio real.

### 2. Componentes Vue

- `PersonajeForm.vue`: un solo formulario para crear y editar. Recibe el personaje inicial, el método (`post`/`put`), la URL y el texto del botón. Dentro maneja `useForm()` y muestra los errores que devuelve Laravel.
- `PersonajeTable.vue`: muestra la lista. No hace peticiones: emite eventos (`ordenar`, `eliminar`) y la página decide qué hacer.
- `ImportModal.vue`: modal con su propio `useForm()` para importar desde SWAPI, con estado de carga y errores.
- `BaseModal.vue`: modal genérico (Teleport, animación, cierre con Esc o clic afuera, bloqueo mientras procesa). Lo usa `ImportModal` y sirve para cualquier otro modal.
- `Pagination.vue`: recibe directamente el paginador de Laravel.
- `BaseButton.vue`, `CampoFormulario.vue`, `Spinner.vue` y `FlashMessage.vue`: piezas de UI comunes (botón con variantes y estado de carga, label + input + error, indicador de carga y toast de mensajes).
- `AppLayout.vue`: layout con cabecera, título, slot de acciones y modo oscuro.

Son reutilizables porque reciben todo por props, comunican hacia afuera con eventos y no dependen de una página concreta.

### 3. Manejo de errores de SWAPI

Todo está en `SwapiService` y se traduce a `SwapiException`, que el controlador muestra en el modal como error del campo `swapi_id`:

- **Timeout o API caída:** timeout configurable (10 s por defecto). Si no responde, se muestra "No se pudo conectar con SWAPI…".
- **ID inexistente (404):** "No existe un personaje con el ID X en SWAPI."
- **Errores 4xx/5xx:** mensaje con el código HTTP.
- **Respuesta con formato inesperado** (no trae `name`): mensaje específico.
- **ID inválido:** el Form Request lo rechaza antes de llamar a la API (entero entre 1 y 83).
- **Duplicado:** `PersonajeService` revisa si ya existe ese `swapi_id` antes de llamar a la API. La columna además tiene un índice único en la base de datos como última protección.
- **Falla al consultar el planeta:** no bloquea la importación; ese campo queda vacío.

Cada caso tiene su prueba en `tests/Feature/ImportarPersonajeTest.php`, que simula SWAPI con `Http::fake()`.

### 4. Validaciones

Las reglas viven en un solo lugar, el backend:

- **Form Requests** con todas las reglas de la prueba y mensajes en español (`lang/es/validation.php`). Laravel ya recorta los espacios de los textos (middleware `TrimStrings`) y convierte los campos vacíos en `null`.
- Con `validated()` solo se guardan los campos permitidos, así que nadie puede cambiar `origen_swapi` o `swapi_id` desde el formulario.
- **En el frontend**, `useForm()` de Inertia recibe los errores automáticamente y cada campo los muestra debajo con `CampoFormulario`.

Elegí no duplicar las reglas en JavaScript: la validación del cliente se puede saltar, así que la del servidor es obligatoria, y tenerla en un solo sitio evita que las dos se desincronicen cuando cambia una regla. Con Inertia el error llega al instante y sin recargar la página.

### 5. Mayor dificultad

Lo que más me costó fue que los datos reales de SWAPI no encajaban con las reglas que pedía la prueba. Esperaba números y me encontré con textos como `"unknown"`, géneros como `hermaphrodite` y el caso de Jabba, cuya masa viene como `"1,358"`: es texto, tiene una coma y además pasa el máximo de 500.

La duda era qué hacer con esos valores. Si guardaba 1358, al editar a Jabba el propio formulario lo rechazaría. Decidí que todo lo importado cumpla las mismas reglas que un registro manual: en `SwapiService` quito la coma y redondeo, y si el valor no es un número o queda fuera de rango lo guardo como `null`. Los géneros que no son `male` o `female` pasan a `n/a`. Cada caso raro tiene su prueba unitaria, así que si cambio la conversión me entero enseguida si rompo algo.

Un problema parecido fue el soft delete: un personaje eliminado sigue ocupando su `swapi_id`, así que no se podía volver a importar. Lo resolví restaurando ese registro con los datos nuevos en vez de crear otro.

### 6. Uso de Inteligencia Artificial

Sí, y prefiero decirlo claro: usé Claude (Claude Code) para generar la mayor parte del código, incluidos el backend, los componentes Vue, las pruebas, Docker y buena parte de este README.

Ya había usado algo de Laravel y Vue, así que lo usé como un compañero que escribe rápido mientras yo decidía y revisaba. Mi trabajo fue sobre todo:

- **Revisar y dirigir.** La primera versión salió más grande de lo necesario, así que le pedí simplificarla: validación en un solo lugar, menos capas y menos componentes. Prefiero un código que pueda leer y cambiar yo mismo antes que uno que impresione pero no entienda del todo.
- **Comprobar que funciona.** Probé en el navegador crear, editar, eliminar, buscar, filtrar, ordenar e importar (incluidos duplicados y el ID 17, que no existe en SWAPI), en escritorio y en móvil, e importé personajes reales para revisar el mapeo.
- **Probar en un entorno parecido a producción.** Al correrlo en Docker con MySQL aparecieron dos errores que en Windows no se veían, y los corregimos.
- **Entender el código.** Repasé cada archivo para poder explicarlo y modificarlo en vivo. Los 47 tests me dan confianza para hacer cambios sin miedo.

### 7. Mejoras futuras

- Traducir al español los colores que vienen de SWAPI (hoy se guardan como llegan, p. ej. `blond`, `blue`).
- Cachear las respuestas de SWAPI (sobre todo los planetas) para no repetir consultas.
- Importación masiva desde la interfaz, con una cola (jobs) y progreso.
- Papelera para ver y restaurar personajes eliminados.
- Reintentos automáticos cuando SWAPI falla por la red, y pruebas end-to-end (Pest Browser o Playwright).
- Autenticación y permisos si la app se usara en producción.

### 8. Autoevaluación

8/10.

Lo que creo que está bien: cumple todos los requisitos obligatorios y la mayoría de los bonus, cada error de SWAPI tiene un mensaje claro para el usuario, y el código es corto y directo, con cada regla en un solo lugar. Eso lo hace fácil de probar y de cambiar.

Por qué no más: buena parte del código lo escribió una IA y todavía lo estoy terminando de interiorizar. Además, quedan mejoras que me hubiera gustado hacer, como cachear las consultas a SWAPI, una papelera para restaurar personajes eliminados y pruebas end-to-end.

### 9. Tiempo invertido

Unas 2,5 horas de trabajo con la IA, repartidas en dos sesiones:

- ~1 h 20 min (15/11): instalar PHP y Composer, generar el proyecto, probarlo en el navegador y en Docker con MySQL, y corregir los dos bugs que solo aparecían en Linux.
- ~1 h (16/11): simplificar el código para que fuera más fácil de leer y modificar, volver a probarlo y actualizar este README.

Aparte, tiempo revisando y estudiando el código para poder explicarlo.
