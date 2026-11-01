# Bitácora de Cambios — Proyecto Desarrollo Web Móvil (ID: 3478788)

**Alumno:** Fernando León Del Río  
**ID / Carpeta:** `3478788`  
**Proyecto:** Tacos Tony Inventario Móvil / Práctica de Login  
**Servidor / Hosting:** `https://equipo4.centrofuyuan.com/3478788/`  
**Fecha de registro:** 22 de octubre de 2026  

---

## 1. Resumen Cronológico de Acciones Realizadas

### Hito 1: Análisis y Diagnóstico Inicial del Entorno
* **Inspección de archivos:** Se exploró el espacio de trabajo local y se identificó la presencia de [gestorBDD.php](file:///c:/Users/ferle/OneDrive/Documentos/Universidad/7toSemestre/Desarrollo%20Web%20Movil/3478788/gestorBDD.php) (Adminer 6.1.2) en la carpeta del alumno, herramienta clave para gestionar la base de datos MySQL en el servidor remoto sin necesidad de abrir puertos externos.
* **Recopilación de credenciales:** Se obtuvieron los parámetros de conexión de la base de datos compartida del equipo (`uzfjkpte_equipo4`, usuario `uzfjkpte_equipo4_user`, pass `EY4VLKyFpnlyICJ)`).
* **Definición de reglas:** Cumplimiento de prefijos de tabla obligatorios (`3478788_usuarios`) para no sobreescribir datos de otros compañeros.

### Hito 2: Creación del Módulo de Login Base (Práctica Escolar)
* **Elaboración de Plan inicial:** Se redactó y aprobó el plan de implementación inicial para la práctica de login.
* **Generación de archivos base:**
  * `config.php` y `config.example.php`: Conexión PDO con manejo de errores y UTF-8.
  * `schema.sql`: DDL de la tabla `3478788_usuarios`.
  * `setup.php`: Script web para insertar el usuario de prueba `fer.leon_05` con `password_hash()` sin exponer contraseñas en código fuente.
  * `index.php`: Formulario de login con mensajes de error y validación CSRF.
  * `bienvenida.php` y `logout.php`: Control de acceso y destrucción de sesión.
  * `.htaccess`: Restricción de acceso directo a archivos sensibles (`.sql`, `config.php`, `.md`).

### Hito 3: Integración de Restricciones del Hosting y Tacos Tony
* **Lectura de documentación:** Se analizaron los archivos [01-vision-alcance_TacosTonyMovil_Leon.md](file:///c:/Users/ferle/OneDrive/Documentos/Universidad/7toSemestre/Desarrollo%20Web%20Movil/Proyecto/01-vision-alcance_TacosTonyMovil_Leon.md) y [02-contrato-tecnico_TacosTonyMovil_Leon.md](file:///c:/Users/ferle/OneDrive/Documentos/Universidad/7toSemestre/Desarrollo%20Web%20Movil/Proyecto/02-contrato-tecnico_TacosTonyMovil_Leon.md).
* **Detección de incompatibilidad técnica:** El contrato v1.0 prescribía Node.js/Express, el cual requiere un proceso persistente en ejecución (inviable en un hosting compartido sin Docker ni procesos en segundo plano).
* **Propuesta y Adaptación a Contrato v1.1:**
  * Se preservó el contrato v1.0 original intacto.
  * Se generó la versión adaptada [02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md](file:///c:/Users/ferle/OneDrive/Documentos/Universidad/7toSemestre/Desarrollo%20Web%20Movil/Proyecto/02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md).
  * Se documentó el nuevo **ADR-05 (Backend en PHP 8 sin framework)**.
  * Se adaptaron ADR-01, ADR-02, ADR-04, requisitos no funcionales (RNF-01, RNF-05), resolución de preguntas abiertas sobre la BD y la nueva estructura de despliegue (`deploy/`).

### Hito 4: Mejora y Evolución del Módulo de Login (Plan Tacos Tony)
* **Planificación:** Se redactó y aprobó el plan de mejora enfocado en los requerimientos del Contrato Técnico v1.1.
* **Actualización estética e identidad:** Reemplazo del diseño genérico por la identidad de *Tacos Tony Camino Real* (paleta rojo carbón/brasa `#b91c1c`, naranja cálido `#ea580c` y blanco neutro).
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
* **Evidencia de entrega:** Actualización de `reflexion_entrega.md` vinculando el despliegue mediante FileZilla con las lecciones aprendidas de seguridad y ergonomía.

---

## 2. Inventario Actual de Archivos del Proyecto

```text
3478788/
├── bitacora_cambios.md              # Este registro histórico completo
├── gestorBDD.php                    # Adminer para administración de base de datos MySQL en servidor
└── login/
    ├── .htaccess                    # Protección de archivos de configuración y scripts SQL
    ├── bienvenida.php               # Pantalla protegida con badge de rol, atribuciones y expiración
    ├── bitacora_cambios.md          # Copia local de la bitácora para entrega del módulo
    ├── config.php                   # Credenciales de conexión PDO y funciones de sesión segura
    ├── config.example.php           # Plantilla sin credenciales para control de versiones
    ├── index.php                    # Formulario de login Tacos Tony (ergonómico 48px, toggle contraseña, CSRF)
    ├── logout.php                   # Destrucción segura de sesiones y cookies
    ├── reflexion_entrega.md         # Plantilla de entrega y reflexión de 3 líneas
    ├── schema.sql                   # DDL y DML para MySQL Workbench / Adminer con roles y campos seguros
    ├── setup.php                    # Asistente web para crear usuario y tabla con bcrypt costo 10
    └── styles.css                   # Hoja de estilos Mobile-First (RNF-03, RNF-04, paleta gastronómica)
```

---

## 3. Estado de Cumplimiento de Requerimientos

| Requisito | Origen | Estado | Implementación |
| :--- | :--- | :---: | :--- |
| Carpeta `/<MI_ID>/login/` | Tarea escolar | ✅ | Directorio `3478788/login/` listo para transferir vía FileZilla. |
| Tabla con prefijo `3478788_usuarios` | Tarea escolar | ✅ | Definida en `schema.sql` y `setup.php` dentro de la BD `uzfjkpte_equipo4`. |
| Usuario de prueba sin texto plano | Tarea escolar | ✅ | Usuario `fer.leon_05` creado vía `setup.php` con `password_hash()` (bcrypt costo 10). |
| Mensaje *"login incorrecto"* | Tarea escolar | ✅ | Desplegado en `index.php` ante credenciales erróneas o cuenta inactiva. |
| Redirección a bienvenida con nombre | Tarea escolar | ✅ | En `bienvenida.php` mostrando nombre completo, inicial y rol. |
| Ninguna credencial en repositorio | Tarea escolar | ✅ | `config.example.php` para distribución; `.htaccess` bloquea acceso web. |
| Roles RBAC (GERENTE / COLABORADOR) | RF-01 / Contrato v1.1 | ✅ | Almacenado en BD y sesión; renderiza badge y permisos en bienvenida. |
| Ergonomía táctil $\ge 48\times 48\text{ px}$ | RNF-04 / Contrato v1.1 | ✅ | Variables y reglas CSS aplicadas a todos los controles interactivos. |
| Expiración a los 30 min de inactividad | RNF-05 / Contrato v1.1 | ✅ | `SESSION_TIMEOUT = 1800` validado en cada petición autenticada. |
| Despliegue en hosting compartido | Restricción técnica | ✅ | PHP 8 nativo sin procesos en segundo plano ni dependencias externas. |
