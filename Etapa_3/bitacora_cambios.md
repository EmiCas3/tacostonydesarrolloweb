# Bitácora de Cambios — Proyecto Desarrollo Web Móvil (ID: 3478788)

**Alumno:** Fernando León Del Río  
**ID / Carpeta:** `3478788`  
**Proyecto:** Tacos Tony Inventario Móvil / Práctica de Login  
**Servidor / Hosting:** `https://equipo4.centrofuyuan.com/3478788/`  
**Fecha de actualización:** 1 de noviembre de 2026  

---

## 1. Resumen Cronológico de Acciones Realizadas

### Hito 1: Análisis y Diagnóstico Inicial del Entorno
* **Inspección de archivos:** Se exploró el espacio de trabajo local y se identificó la presencia de Adminer (`gestorBDD.php`) en la carpeta del alumno, herramienta clave para gestionar la base de datos MySQL en el servidor remoto sin necesidad de abrir puertos externos.
* **Recopilación de parámetros:** Se configuró la conexión a la base de datos compartida del equipo (`uzfjkpte_equipo4`, usuario `uzfjkpte_equipo4_user`), aislando la contraseña fuera del código fuente mediante variables de entorno (`.env`).
* **Definición de reglas:** Cumplimiento de prefijos de tabla obligatorios (`3478788_usuarios`) para no sobreescribir datos de otros compañeros.

### Hito 2: Creación del Módulo de Login Base (Práctica Escolar)
* **Elaboración de Plan inicial:** Se redactó y aprobó el plan de implementación inicial para la práctica de login.
* **Generación de archivos base:**
  * `config.php`, `.env.example` y `config.example.php`: Conexión PDO con manejo de errores, lectura de `.env` y UTF-8.
  * `schema.sql`: DDL de la tabla `3478788_usuarios`.
  * `setup.php`: Script web para insertar el usuario de prueba `fer.leon_05` con `password_hash()` sin exponer contraseñas en código fuente.
  * `index.php`: Formulario de login con mensajes de error y validación CSRF.
  * `bienvenida.php` y `logout.php`: Control de acceso y destrucción de sesión.
  * `.htaccess`: Restricción de acceso directo a archivos sensibles (`.env`, `.sql`, `config.php`, `.md`).

### Hito 3: Integración de Restricciones del Hosting y Tacos Tony
* **Lectura de documentación:** Se analizaron los archivos [01-vision-alcance_TacosTonyMovil_Leon.md](./01-vision-alcance_TacosTonyMovil_Leon.md) y [02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md](./02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md).
* **Detección de incompatibilidad técnica:** El contrato v1.0 prescribía Node.js/Express, el cual requiere un proceso persistente en ejecución (inviable en un hosting compartido sin Docker ni procesos en segundo plano).
* **Propuesta y Adaptación a Contrato v1.1:**
  * Se preservó el contrato v1.0 original intacto.
  * Se generó la versión adaptada [02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md](./02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md).
  * Se documentó el nuevo **ADR-05 (Backend en PHP 8 sin framework)**.
  * Se adaptaron ADR-01, ADR-02, ADR-04, requisitos no funcionales (RNF-01, RNF-05), resolución de preguntas abiertas sobre la BD y la nueva estructura de despliegue (`deploy/`).

### Hito 4: Mejora y Evolución del Módulo de Login (Ergonomía y Seguridad)
* **Ergonomía Mobile-First (RNF-03 y RNF-04):**
  * Dimensionado de todas las áreas táctiles (`input`, `button`, `select`) a un mínimo estricto de **48×48 px**.
  * Incorporación de botón táctil ergonómico para alternar la visibilidad de la contraseña (mostrar/ocultar con icono 👁/🙈), diseñado para agilizar la captura en cocina con una sola mano.
* **Control de Acceso Basado en Roles (RF-01):**
  * Modificación del esquema en `schema.sql` y `setup.php` para incluir columnas `rol` (`ENUM('GERENTE', 'COLABORADOR')`) y `activo` (`TINYINT(1)`).
  * Validación en `index.php` de cuenta activa y almacenamiento del rol en la sesión.
  * Desglose en `bienvenida.php` de las atribuciones operativas según el rol del usuario autenticado (Gerente vs Colaborador).
* **Expiración de Sesión por Inactividad (RNF-05):**
  * Configuración en `config.php` de la constante `SESSION_TIMEOUT = 1800` (30 minutos).
  * Implementación de `verificarExpiracionSesion()` que calcula el tiempo transcurrido desde `$_SESSION['ultimo_acceso']`.
  * Redirección con aviso informativo al login cuando se supera el tiempo límite.

### Hito 5: Identidad Corporativa Real de Tacos Tony y Blindaje `.env` (Etapa 3)
* **Externalización de credenciales a `.env`:**
  * Creación de `.gitignore` en la raíz del repositorio para excluir cualquier archivo `.env`.
  * Creación de `.env.example` documentado e implementación de `cargarEnv()` nativo en `config.php`, eliminando cualquier contraseña fija del código fuente.
  * Refuerzo de `.htaccess` para denegar peticiones HTTP directas hacia `.env` y `.env.example`.
* **Integración de Colores e Imágenes Reales de Tacos Tony:**
  * Sustitución de colores genéricos en `styles.css` por la paleta corporativa oficial definida en el sistema de la empresa: Naranja Tony (`#F6821F`), Azul Institucional (`#073A79`), Amarillo Tony (`#F9D864`), Blanco (`#FFFFFF`), Negro (`#000000`) y Gris (`#E5E5E5`).
  * Incorporación de los activos gráficos reales en `login/assets/img/`: logotipo oficial (`Tacos_tony_logo.png`), favicon (`TTlogomini.png`), fotos reales de productos (`assets/img/productos/*.png`) e iconografía del sistema.
  * Rediseño de `index.php`, `bienvenida.php` y `setup.php` con la identidad oficial conservando el 100% de las reglas Mobile-First (RNF-03 y RNF-04).

### Hito 6: Construcción e Integración del MVP Completo y Trazabilidad (Fase 2 y 3)
* **Esquema Relacional de 7 Tablas y Semilla Automática (`schema.sql` e `init_mvp.php`):**
  * Creación automática de las 7 tablas con prefijo `3478788_`: `usuarios`, `insumos`, `productos`, `recetas`, `ventas`, `venta_detalles` y `movimientos_inventario`.
  * Carga inicial de los insumos reales de Tacos Tony, productos del menú con sus fotografías oficiales y recetas exactas en `DECIMAL(10,3)`, además de los dos usuarios de evaluación: `fer.leon_05` (`GERENTE`) y `colab.tony` (`COLABORADOR`).
* **Backend API-First en PHP 8 Nativo (`login/api/index.php` y `login/api/.htaccess`):**
  * Implementación de los contratos REST de la Sección 3.2 del Contrato Técnico v1.1 con autenticación JWT (HS256) y RBAC (`authenticateToken()`, `authorizeRole(['GERENTE'])`).
  * Transacciones ACID con bloqueo `SELECT ... FOR UPDATE` en `POST /api/ventas`, `POST /api/entradas` y `POST /api/mermas`.
  * Regla de negocio no trivial (`RF-05`): descuento automático por receta, detección de stock negativo (`estado = 'discrepancia'`) sin bloquear la venta (`CA-05.2`) y protección de inmutabilidad `405 Method Not Allowed` en `/api/movimientos` (`CA-10.2` / `RNF-06`).
* **Interfaz Operativa Mobile-First con Barra Inferior (`login/app.php`):**
  * Integración de las 5 vistas operativas (**Alertas**, **Existencias**, **Ventas/Recetas**, **Entradas/Mermas** y **Auditoría**) consumiendo la API REST con el token JWT.
* **Entregable de Trazabilidad Canvas (`03-trazabilidad_TacosTonyMovil_Leon.md`):**
  * Elaboración de la matriz completa verificando los 10 RF *Must* (`CA-01.1` a `CA-10.3`) y los 6 RNF (`RNF-01` a `RNF-06`).

---

## 2. Inventario Actual de Archivos del Módulo Login

```text
Etapa_3/login/
├── .env.example                 # Plantilla de variables de entorno sin credenciales reales
├── .htaccess                    # Protección de .env, archivos de configuración y scripts SQL
├── assets/
│   └── img/                     # Logotipo e iconos oficiales de Tacos Tony
│       ├── Tacos_tony_logo.png
│       ├── TTlogomini.png
│       ├── imagn-perfil.png
│       ├── log-out.png
│       ├── icon-admin.png
│       ├── icon-catalogo.png
│       ├── icon-config.png
│       ├── icon-dash.png
│       ├── icon-inv.png
│       ├── icon-mov.png
│       ├── icon-repo.png
│       └── icon-warning.png
├── bienvenida.php               # Pantalla protegida con identidad Tacos Tony, rol y permisos
├── config.example.php           # Alias de compatibilidad hacia config.php
├── config.php                   # Lector nativo de .env, conexión PDO y sesión segura (30 min)
├── index.php                    # Login Tacos Tony (logo oficial, colores reales, 48px, CSRF)
├── logout.php                   # Destrucción segura de sesiones y cookies
├── reflexion_entrega.md         # Plantilla de entrega y reflexión de 3 líneas
├── schema.sql                   # DDL para MySQL Workbench / Adminer con roles y campos seguros
├── setup.php                    # Asistente web para crear usuario y tabla con bcrypt costo 10
└── styles.css                   # Hoja de estilos Mobile-First con paleta oficial Tacos Tony
```

---

## 3. Estado de Cumplimiento de Requerimientos

| Requisito | Origen | Estado | Implementación |
| :--- | :--- | :---: | :--- |
| Carpeta `/<MI_ID>/login/` | Tarea escolar | ✅ | Directorio `Etapa_3/login/` autocontenido con `assets/img/` listo para FileZilla. |
| Tabla con prefijo `3478788_usuarios` | Tarea escolar | ✅ | Definida en `schema.sql` y `setup.php` dentro de la BD `uzfjkpte_equipo4`. |
| Usuario de prueba sin texto plano | Tarea escolar | ✅ | Usuario `fer.leon_05` creado vía `setup.php` con `password_hash()` (bcrypt costo 10). |
| Mensaje *"login incorrecto"* | Tarea escolar | ✅ | Desplegado en `index.php` ante credenciales erróneas o cuenta inactiva. |
| Redirección a bienvenida con nombre | Tarea escolar | ✅ | En `bienvenida.php` mostrando nombre completo, perfil y rol. |
| Ninguna credencial en repositorio (`.env`) | Etapa 3 | ✅ | `cargarEnv()` en `config.php`, `.env.example` versionado y `.env` ignorado en `.gitignore`. |
| Colores e imágenes reales de la empresa | Etapa 3 | ✅ | Paleta oficial (`#F6821F`, `#073A79`, `#F9D864`, `#E5E5E5`) y `Tacos_tony_logo.png`. |
| Roles RBAC (GERENTE / COLABORADOR) | RF-01 / Contrato v1.1 | ✅ | Almacenado en BD y sesión; renderiza badge y permisos en bienvenida. |
| Ergonomía táctil $\ge 48\times 48\text{ px}$ | RNF-04 / Contrato v1.1 | ✅ | Variables y reglas CSS aplicadas a todos los controles interactivos. |
| Expiración a los 30 min de inactividad | RNF-05 / Contrato v1.1 | ✅ | `SESSION_TIMEOUT = 1800` validado en cada petición autenticada. |
| Despliegue en hosting compartido | Restricción técnica | ✅ | PHP 8 nativo sin procesos en segundo plano ni dependencias externas. |
