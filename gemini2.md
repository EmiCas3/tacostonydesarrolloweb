# Instrucciones para Gemini — Guardar Contraseña con Cifrado en Configuracion.php

## Contexto del Proyecto

Proyecto de desarrollo web para **Tacos Tony**, escrito en PHP con HTML embebido y hoja de estilos CSS (`estilos/estilogenerico.css`). El sistema gestiona inventario, movimientos, ventas, reportes y proveedores de una taquería. Los usuarios autenticados son empleados registrados en la base de datos.

---

## Objetivo de la Modificación

En la pantalla **Configuración / Editar Perfil** (`barra_lateral/Configuracion.php`) existe un formulario con un campo de "Nueva Contraseña" y un botón "GUARDAR CAMBIOS". Actualmente, al hacer clic en el botón solo se ejecuta una función JavaScript que valida el campo y muestra un `alert`, **pero no guarda nada en la base de datos**.

**Lo que se necesita:** crear un archivo PHP que reciba la nueva contraseña vía POST, la cifre con `SHA2(..., 256)` (el mismo método que usa el login), y actualice el registro del empleado en la tabla `t_empleados`. Además, modificar `Configuracion.php` para que el formulario envíe los datos a ese nuevo archivo.

---

## Archivo a Modificar y Archivo a Crear

```
tacostonydesarrolloweb/
└── barra_lateral/
    ├── Configuracion.php               ← MODIFICAR (ajustar el formulario y el JS)
    └── Pant_Ajustes/
        └── guardar_contrasena.php      ← CREAR (nuevo archivo, similar a guardar_asignacion.php)
```

---

## Estructura de la Base de Datos (tabla relevante)

### `t_empleados` — Registro de empleados/usuarios del sistema
| Columna            | Tipo          | Descripción                                      |
|--------------------|---------------|--------------------------------------------------|
| `id`               | int (PK, AI)  | Identificador único del empleado                 |
| `nombre`           | varchar(45)   | Nombre completo                                  |
| `salario`          | decimal(6,2)  | Salario (determina el rol en sesión)             |
| `numero_telefono`  | varchar(12)   | Teléfono (UNIQUE)                                |
| `correo`           | varchar(45)   | Correo electrónico (UNIQUE)                      |
| `contrasena`       | varchar(255)  | Contraseña cifrada con SHA2-256                  |

---

## Cómo funciona el cifrado de contraseñas en este proyecto

El cifrado se aplica **directamente en la consulta MySQL** usando la función `SHA2()` de MySQL. Así lo hace el login (`validar_login.php`):

```php
// Así se verifica la contraseña en el login:
$query = "SELECT id, nombre, salario, correo
          FROM t_empleados
          WHERE correo = '$correo_esc' AND contrasena = SHA2('$contra_esc', 256)";
```

Por lo tanto, para **guardar** la nueva contraseña, la consulta de UPDATE debe ser:

```sql
UPDATE t_empleados
SET contrasena = SHA2('nueva_contrasena_aqui', 256)
WHERE id = {id_del_empleado_en_sesion}
```

---

## Cómo se identifica al usuario en sesión

En `seguridad.php` y `validar_login.php` se guardan estos datos en `$_SESSION` al iniciar sesión:

```php
$_SESSION['id_empleado']      // ID del empleado en t_empleados (int)
$_SESSION['nombre_empleado']  // Nombre completo
$_SESSION['correo_empleado']  // Correo electrónico
$_SESSION['autentificado']    // "SI" si está autenticado
```

El archivo `guardar_contrasena.php` debe usar `$_SESSION['id_empleado']` como referencia para saber a qué fila de `t_empleados` actualizar.

---

## Archivo de Referencia: `guardar_asignacion.php`

Este archivo ya existe y funciona correctamente. Sirve como modelo de patrón para el nuevo archivo. Se encuentra en:

```
barra_lateral/Pant_inventario/Pant_asignar/guardar_asignacion.php
```

Su patrón es:
1. Incluye `seguridad.php` y `conex.php`, llama a `Conectarse()`.
2. Verifica que el método sea `POST`.
3. Valida y sanea los datos recibidos.
4. Ejecuta la consulta a la BD.
5. Redirige con `alert` de éxito o error usando `echo "<script>alert(...);</script>"`.

---

## Descripción Detallada de los Cambios

### 1. Nuevo archivo: `barra_lateral/Pant_Ajustes/guardar_contrasena.php`

Debe:
- Incluir `../../seguridad.php` y `../../conex.php`.
- Llamar a `Conectarse()` para obtener `$link`.
- Verificar que la petición sea `POST`.
- Leer `$_POST['password']` y `$_SESSION['id_empleado']`.
- Validar que la contraseña no esté vacía y tenga al menos 8 caracteres.
- Validar que `$_SESSION['id_empleado']` exista y sea un entero válido mayor a 0.
- Usar `mysqli_real_escape_string()` para sanear la contraseña antes de usarla en la consulta.
- Ejecutar el UPDATE con SHA2:
  ```sql
  UPDATE t_empleados SET contrasena = SHA2('$contrasena_esc', 256) WHERE id = $id_empleado
  ```
- Si el UPDATE tiene éxito: mostrar alert de éxito y redirigir a `../Configuracion.php`.
- Si falla: mostrar alert de error y redirigir a `../Configuracion.php`.
- Si el método no es POST: redirigir directamente a `../Configuracion.php`.

### 2. Modificar: `barra_lateral/Configuracion.php`

Actualmente el formulario tiene `action="#"` y el botón llama a una función JS que solo valida y muestra un `alert` sin enviar nada al servidor.

Hay que:
- Cambiar el `action` del `<form>` de `"#"` a `"Pant_Ajustes/guardar_contrasena.php"`.
- Verificar que `method="POST"` esté presente.
- Verificar que el campo de contraseña tenga `name="password"`.
- Actualizar la función JS `guardar_cambios()` para que, en lugar de mostrar `alert("Contraseña actualizada exitosamente")` y limpiar el campo, **envíe el formulario** (`document.querySelector('form').submit()`) después de pasar las validaciones.
- Cambiar el botón a `type="submit"` o mantenerlo como `type="button"` con el submit por JS (lo que resulte más limpio).

---

## Flujo Completo Esperado

```
Usuario escribe nueva contraseña en Configuracion.php
    ↓
Hace clic en "GUARDAR CAMBIOS"
    ↓
JS valida: ¿campo vacío? → alert y no continúa
           ¿menos de 8 chars? → alert y no continúa
           ✓ válido → submit del formulario (POST)
    ↓
guardar_contrasena.php recibe POST
    ↓
Valida método POST y datos
    ↓
UPDATE t_empleados SET contrasena = SHA2('nueva', 256) WHERE id = $_SESSION['id_empleado']
    ↓
✓ Éxito → alert "Contraseña actualizada" → redirect a Configuracion.php
✗ Error → alert "Error al actualizar" → redirect a Configuracion.php
```

---

## Seguridad a Considerar

- Usar `mysqli_real_escape_string($link, $contrasena)` antes de insertar en la query.
- Verificar `$_SESSION['id_empleado']` con `intval()` para asegurarse de que es un entero válido y mayor a 0.
- Si `id_empleado` no existe en sesión o es inválido, redirigir a `../Configuracion.php` con un mensaje de error.
- **No** usar `password_hash()` / `password_verify()` de PHP — el proyecto usa SHA2 de MySQL para mantener consistencia con el login existente.

---

## Rutas de Includes (importante)

`guardar_contrasena.php` estará en `barra_lateral/Pant_Ajustes/`, por lo tanto:
- `seguridad.php` está en la raíz → incluir como `../../seguridad.php`
- `conex.php` está en la raíz → incluir como `../../conex.php`
- Redirección de vuelta → `../Configuracion.php`

Para referencia, `guardar_asignacion.php` en `Pant_inventario/Pant_asignar/` usa `../../../` para llegar a la raíz, confirmando el patrón de rutas relativas del proyecto.

---

## Tareas a Realizar

> **Instrucción para Gemini:** Realiza **una tarea a la vez**. Al completar cada tarea, muévela al apartado **"Tareas Realizadas"** y pregunta al usuario si puede continuar con la siguiente.

### Pendientes

- [ ] **Tarea 1 — Análisis y revisión de archivos**
  Revisar `Configuracion.php` completo para confirmar: la estructura del `<form>` (action, method), el `type` del botón, los `name` de los campos del formulario (en especial el campo de contraseña), y la función JS `guardar_cambios()`. También revisar `guardar_asignacion.php` para confirmar el patrón de includes, validación y respuesta.

- [ ] **Tarea 2 — Crear el archivo `guardar_contrasena.php`**
  Crear `barra_lateral/Pant_Ajustes/guardar_contrasena.php` siguiendo el patrón de `guardar_asignacion.php`:
  - Includes de `seguridad.php` y `conex.php` con rutas relativas correctas (`../../`).
  - Verificación de método POST.
  - Lectura y saneamiento de `$_POST['password']` y `$_SESSION['id_empleado']`.
  - Validaciones (contraseña no vacía, mínimo 8 caracteres, id_empleado válido mayor a 0).
  - Query `UPDATE t_empleados SET contrasena = SHA2('...', 256) WHERE id = ...`.
  - Respuestas con `alert` y redirección a `../Configuracion.php`.

- [ ] **Tarea 3 — Modificar `Configuracion.php` para enviar el formulario**
  Actualizar el `<form>` en `Configuracion.php`:
  - Cambiar `action="#"` por `action="Pant_Ajustes/guardar_contrasena.php"`.
  - Verificar que `method="POST"` esté presente.
  - Verificar que el campo de contraseña tenga `name="password"`.
  - Actualizar la función JS `guardar_cambios()` para que, tras validar, ejecute `document.querySelector('form').submit()` en lugar del `alert` de éxito actual.

- [ ] **Tarea 4 — Pruebas y ajustes finales**
  Verificar que:
  - Al dejar el campo vacío y hacer clic → aparece alert de JS, no se envía el formulario.
  - Al ingresar menos de 8 caracteres → aparece alert de JS, no se envía.
  - Al ingresar una contraseña válida → el formulario se envía, `guardar_contrasena.php` actualiza la BD y redirige con mensaje de éxito.
  - Con la nueva contraseña es posible cerrar sesión e iniciar sesión de nuevo correctamente (el SHA2 del login sigue funcionando).
  - No hay errores PHP ni warnings.

- [ ] **Tarea 5 — Documentación en README.md**
  Crear (o actualizar si ya existe) el archivo `README.md` en la raíz del proyecto documentando:
  - Descripción general del proyecto Tacos Tony.
  - Descripción de la modificación realizada: archivos creados/modificados y por qué.
  - Flujo de actualización de contraseña (diagrama de texto o paso a paso).
  - Estructura de `t_empleados` y la columna `contrasena`.
  - Explicación del cifrado: SHA2-256 en MySQL y por qué se usa ese método (consistencia con `validar_login.php`).
  - Instrucciones para el usuario sobre cómo cambiar su contraseña.
  - Notas técnicas: rutas de includes, variables de sesión usadas, consideraciones de seguridad.

---

## Tareas Realizadas

> *(Esta sección se irá llenando conforme Gemini complete cada tarea.)*

---

## Referencias Rápidas

| Elemento                  | Detalle                                                                    |
|---------------------------|----------------------------------------------------------------------------|
| Archivo a modificar       | `barra_lateral/Configuracion.php`                                          |
| Archivo a crear           | `barra_lateral/Pant_Ajustes/guardar_contrasena.php`                        |
| Archivo de referencia     | `barra_lateral/Pant_inventario/Pant_asignar/guardar_asignacion.php`        |
| Tabla BD                  | `t_empleados`                                                              |
| Columna de contraseña     | `contrasena` (varchar 255)                                                 |
| Cifrado                   | `SHA2('texto', 256)` — función MySQL, NO `password_hash()` de PHP          |
| ID del usuario en sesión  | `$_SESSION['id_empleado']`                                                 |
| Conexión BD               | `include('../../conex.php')` → `$link = Conectarse()`                     |
| Seguridad                 | `include('../../seguridad.php')` al inicio del archivo                     |
| Redirección al terminar   | `../Configuracion.php`                                                     |
