# Tabla de Trazabilidad — MVP Tacos Tony Inventario Móvil

| Campo | Valor |
|---|---|
| **Proyecto** | Tacos Tony Inventario Móvil (Sucursal Camino Real) |
| **Alumno / Autor** | Fernando León Del Río (ID: `3478788`) — Equipo 3 |
| **Versión del MVP** | v1.0 (Etapa 3) |
| **Versión del Contrato Técnico** | [02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md](./02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md) (v1.1) |
| **Fecha de entrega** | Noviembre de 2026 |
| **URL del MVP desplegado** | https://equipo4.centrofuyuan.com/3478788/login/ |

---

## 🔑 Instrucciones de Ejecución y Acceso para Evaluación

1. Abrir la URL del MVP desplegado desde un navegador móvil o escritorio (con emulador móvil de DevTools a `360×640 px`):  
   👉 **https://equipo4.centrofuyuan.com/3478788/login/**
2. *(Opcional en primera ejecución)* Si se desea reiniciar o verificar las 7 tablas con prefijo `3478788_*` y datos semilla, abrir `setup.php`.
3. **Credenciales de prueba (contraseñas almacenadas con `bcrypt` costo 10):**
   - **Rol GERENTE (acceso total a los 5 módulos):**
     - **Usuario:** `fer.leon_05` *(o correo `gerente@tacostony.com`)*
     - **Contraseña:** `1234567890`
   - **Rol COLABORADOR (acceso exclusivo a Existencias y Alertas, `RF-01`):**
     - **Usuario:** `colab.tony` *(o correo `colaborador@tacostony.com`)*
     - **Contraseña:** `1234567890`
4. Al iniciar sesión, pulsar **"ABRIR INVENTARIO MÓVIL (MVP)"** en `bienvenida.php` para ingresar a `app.php` y recorrer las pestañas de la barra de navegación inferior (**Alertas**, **Existencias**, **Ventas**, **Entradas/Merma** y **Auditoría**).

---

## 1. Tabla de Trazabilidad Completa (RF y Criterios de Aceptación)

> Esta tabla conecta cada requisito funcional definido en la Etapa 1 con sus criterios de aceptación verificables (Etapa 2, Contrato Técnico v1.1) y el archivo/endpoint exacto donde fue implementado en el MVP.

| Requisito (RF) | Prioridad | Criterio de aceptación relacionado | Implementado en (módulo / archivo / pantalla / endpoint) | Estado | Notas de verificación |
|---|---|---|---|---|---|
| **RF-01** Autenticación y control de acceso RBAC | Debe (*Must*) | **CA-01.1 (Camino feliz):** Login válido emite JWT con rol (`GERENTE` o `COLABORADOR`) y redirige a su vista operativa, ocultando acciones restringidas al Colaborador. | `login/index.php`, `login/bienvenida.php`, `login/app.php`, `POST /api/auth/login` en `login/api/index.php` | ✅ Cumplido | Con `colab.tony` inicia en la vista `Existencias` y oculta formularios de Altas, Ventas, Mermas y Auditoría. |
| **RF-01** | Debe (*Must*) | **CA-01.2 (Caso de error):** Contraseña incorrecta devuelve HTTP `401` / mensaje genérico sin revelar qué campo falló ni emitir token. | `login/index.php` y `POST /api/auth/login` en `login/api/index.php` | ✅ Cumplido | Responde `401 Unauthorized` en API (`"Credenciales inválidas"`) y alerta en interfaz. |
| **RF-01** | Debe (*Must*) | **CA-01.3 (Caso límite):** Expiración automática tras 30 minutos de inactividad (`1800 s`), rechazando peticiones con `401` y redirigiendo a Login. | `verificarExpiracionSesion()` y `verificarJwt()` en `login/config.php`; `authenticateToken()` en `login/api/index.php` | ✅ Cumplido | Validado tanto en el campo `exp` del token JWT como en `$_SESSION['ultimo_acceso']`. |
| **RF-02** Catálogo de insumos y desactivación lógica | Debe (*Must*) | **CA-02.1 (Camino feliz):** El Gerente da de alta un insumo (ej. `"Cilantro Fresco"`, `kg`, `Verduras`, stock `2.000`, mín `0.500`) y aparece en estado `"normal"`. | Vista `Existencias` (`#formNuevoInsumo` en `login/app.php`) y `POST /api/insumos` en `login/api/index.php` | ✅ Cumplido | Responde `201 Created`, calcula estado inicial y actualiza el catálogo en tiempo real. |
| **RF-02** | Debe (*Must*) | **CA-02.2 (Caso de error):** Petición `POST /api/insumos` enviada con token de `COLABORADOR` es bloqueada en backend con HTTP `403 Forbidden`. | Middleware `authorizeRole($usuario, ['GERENTE'])` en `login/api/index.php` | ✅ Cumplido | Devuelve `403` con `"Acceso restringido: solo el Gerente puede dar de alta insumos"`. |
| **RF-02** | Debe (*Must*) | **CA-02.3 (Caso límite):** Desactivar un insumo con historial ejecuta baja lógica (`activo = false`) sin `DELETE` físico, preservando sus movimientos. | Botón `Desactivar` en `login/app.php` y `PATCH /api/insumos/:id/desactivar` en `login/api/index.php` | ✅ Cumplido | Actualiza `activo = 0` en `3478788_insumos` y conserva intactos los registros en `3478788_movimientos_inventario`. |
| **RF-03** Configuración de recetas por producto | Debe (*Must*) | **CA-03.1 (Camino feliz):** El Gerente asigna insumos y cantidades en `DECIMAL(10,3)` a un producto del menú y se guardan en `3478788_recetas`. | Panel `Configuración de Recetas` en `login/app.php` y `PUT /api/productos/:id/receta` en `login/api/index.php` | ✅ Cumplido | Transacción que actualiza los ingredientes del producto y confirma en pantalla. |
| **RF-03** | Debe (*Must*) | **CA-03.2 (Caso de error):** Cantidad negativa o cero (`<= 0`) en receta es rechazada con HTTP `400 Bad Request`. | Validación en `PUT /api/productos/:id/receta` (`login/api/index.php`) | ✅ Cumplido | Retorna `400` con `"La cantidad del insumo en la receta debe ser un número positivo mayor a cero"`. |
| **RF-03** | Debe (*Must*) | **CA-03.3 (Caso límite):** Porciones milimétricas (`0.003 kg`) se almacenan en `DECIMAL(10,3)` sin redondearse a cero ni perder precisión. | Columna `cantidad DECIMAL(10,3)` en `3478788_recetas` (`login/schema.sql`, `login/api/index.php`) | ✅ Cumplido | Formateo exacto a 3 decimales tanto en MySQL como en JSON. |
| **RF-04** Captura manual de ventas | Debe (*Must*) | **CA-04.1 (Camino feliz):** Selección de productos y confirmación crea registro en `3478788_ventas` y `3478788_venta_detalles` devolviendo el total. | Vista `Ventas` en `login/app.php` y `POST /api/ventas` en `login/api/index.php` | ✅ Cumplido | Tarjetas con imágenes reales de Tacos Tony, stepper táctil `+ / -` y cálculo de importe. |
| **RF-04** | Debe (*Must*) | **CA-04.2 (Caso de error):** Enviar venta sin productos (`items: []`) responde HTTP `400` y no altera el inventario. | Validación inicial en `POST /api/ventas` (`login/api/index.php`) | ✅ Cumplido | Devuelve `400 Bad Request` con `"La venta debe incluir al menos un producto válido"`. |
| **RF-04** | Debe (*Must*) | **CA-04.3 (Caso límite):** Venta multi-producto ejecuta todos los descuentos dentro de una única transacción SQL (`beginTransaction / commit / rollBack`). | Bloque transaccional con `SELECT ... FOR UPDATE` en `POST /api/ventas` (`login/api/index.php`) | ✅ Cumplido | Garantiza atomicidad ACID en InnoDB en menos de `0.4 s`. |
| **RF-05** Descuento por receta y discrepancias (*Regla no trivial*) | Debe (*Must*) | **CA-05.1 (Camino feliz):** Al vender $N$ unidades de un producto, se descuenta `N × cantidad_receta` de cada insumo y se crea el movimiento `VENTA`. | `POST /api/ventas` en `login/api/index.php` y previsualización en vivo en `login/app.php` | ✅ Cumplido | Registra `cantidad` negativa, `stock_previo`, `stock_posterior` y `referencia_id` de la venta. |
| **RF-05** | Debe (*Must*) | **CA-05.2 (Excepción de negocio no trivial):** Si el consumo supera el stock actual, la venta **no se bloquea**; el stock queda negativo, cambia a estado `"discrepancia"` y emite alerta. | `calcularEstadoInsumo()` en `login/config.php` y `discrepancias_detectadas` en `POST /api/ventas` (`login/api/index.php`) | ✅ Cumplido | Muestra banner amarillo/rojo inmediato y tarjeta de discrepancia en la pestaña `Alertas`. |
| **RF-05** | Debe (*Must*) | **CA-05.3 (Caso límite):** Si el consumo deja el stock en exactamente `0.000`, pasa a estado `"bajo"` y no a `"discrepancia"`. | `calcularEstadoInsumo()` en `login/config.php` | ✅ Cumplido | `0.000` se evalúa como `0 <= stock_actual <= stock_minimo` (`bajo`). |
| **RF-06** Registro de entradas por compra | Debe (*Must*) | **CA-06.1 (Camino feliz):** Registrar una entrada incrementa `stock_actual`, actualiza el estado a `"normal"` y crea movimiento `ENTRADA`. | Formulario `Registrar Entrada` en `login/app.php` y `POST /api/entradas` en `login/api/index.php` | ✅ Cumplido | Transacción con `FOR UPDATE` e inserción en `3478788_movimientos_inventario`. |
| **RF-06** | Debe (*Must*) | **CA-06.2 (Caso de error):** Cantidad de entrada `<= 0` es rechazada con HTTP `400 Bad Request`. | Validación en `POST /api/entradas` (`login/api/index.php`) | ✅ Cumplido | Devuelve `400` con `"La cantidad de entrada debe ser superior a cero"`. |
| **RF-06** | Debe (*Must*) | **CA-06.3 (Caso límite):** Pesos fraccionarios de báscula (ej. `15.375 kg`) se suman conservando los 3 decimales exactos. | `DECIMAL(10,3)` en `3478788_insumos` y `POST /api/entradas` | ✅ Cumplido | Sin truncamiento de milésimas. |
| **RF-07** Registro de mermas con motivo | Debe (*Must*) | **CA-07.1 (Camino feliz):** El Gerente registra una merma con motivo válido en $\le 4$ toques, descontando stock y creando movimiento `MERMA`. | Formulario `Registrar Merma` en `login/app.php` y `POST /api/mermas` en `login/api/index.php` | ✅ Cumplido | Accesible en 3 toques desde la tarjeta de insumo o pestaña `Entradas/Merma`. |
| **RF-07** | Debe (*Must*) | **CA-07.2 (Caso de error):** Motivo omitido o fuera del catálogo (`caducidad`, `dano`, `preparacion`, `otro`) responde HTTP `400`. | Validación de catálogo en `POST /api/mermas` (`login/api/index.php`) | ✅ Cumplido | Rechaza cualquier motivo no válido con código `400`. |
| **RF-07** | Debe (*Must*) | **CA-07.3 (Caso límite):** Merma por el total exacto del stock (`0.800 kg` de `0.800 kg`) deja el stock en `0.000 kg` y estado `"bajo"`. | `POST /api/mermas` en `login/api/index.php` | ✅ Cumplido | Queda en `0.000` sin errores de redondeo. |
| **RF-08** Consulta de existencias móvil y filtros | Debe (*Must*) | **CA-08.1 (Camino feliz):** Búsqueda por texto y filtro por categoría actualizan las tarjetas en tiempo real ($< 300\text{ ms}$) con indicador de color. | `filtrarInsumosEnTiempoReal()` en `login/app.php` y `GET /api/insumos` | ✅ Cumplido | Filtrado instantáneo en cliente ($< 20\text{ ms}$) y soportado por query params en API. |
| **RF-08** | Debe (*Must*) | **CA-08.2 (Caso de error / vacío):** Búsqueda sin coincidencias muestra estado vacío accesible con botón `"Limpiar filtros"`. | Renderizado condicional en `filtrarInsumosEnTiempoReal()` (`login/app.php`) | ✅ Cumplido | Incluye botón táctil de `48px` para restablecer búsqueda y categoría. |
| **RF-08** | Debe (*Must*) | **CA-08.3 (Caso límite):** Nombres largos en pantallas de `360 px` hacen salto de línea sin desbordar y con `0 px` de scroll horizontal. | Reglas `.insumo-nombre` (`word-break: break-word`) y `overflow-x: hidden` en `login/styles.css` | ✅ Cumplido | Probado en viewport de `360×640 px`. |
| **RF-09** Alertas de stock bajo en dashboard (*Interacción entre módulos*) | Debe (*Must*) | **CA-09.1, CA-09.2 y CA-09.3:** Cuando una venta o merma deja `stock_actual <= stock_minimo` (incluida la igualdad exacta), el insumo aparece automáticamente en las alertas del Dashboard. | Pestaña `Alertas` (`renderizarDashboardAlertas()` en `login/app.php`) alimentada tras `POST /api/ventas` y `POST /api/mermas` | ✅ Cumplido | Conecta en tiempo real el módulo de Movimientos con el módulo de Alertas e Inventario. |
| **RF-10** Historial inmutable de movimientos (*Interacción y auditoría*) | Debe (*Must*) | **CA-10.1 (Camino feliz):** Al registrar una entrada sobre un insumo bajo, el insumo sale de las alertas de stock bajo y aparece el movimiento `ENTRADA` en el historial. | `POST /api/entradas` + pestañas `Alertas` y `Auditoría` en `login/app.php` | ✅ Cumplido | Muestra `stock_previo`, `stock_posterior`, autor y fecha. |
| **RF-10** | Debe (*Must*) | **CA-10.2 (Inmutabilidad):** Cualquier petición `PUT`, `PATCH` o `DELETE` sobre `/api/movimientos` es rechazada con HTTP `405 Method Not Allowed`. | Guardia de inmutabilidad en `/movimientos` (`login/api/index.php`) y botón `🔒 Probar Inmutabilidad` en `login/app.php` | ✅ Cumplido | No existe ninguna sentencia `UPDATE` ni `DELETE` sobre `3478788_movimientos_inventario`. |
| **RF-10** | Debe (*Must*) | **CA-10.3 (Caso límite):** Consulta filtrada por fechas, tipo e insumo paginada con `LIMIT 25 OFFSET 0` e índices compuestos. | Índices `idx_mov_insumo_fecha` e `idx_mov_tipo_fecha` en `login/schema.sql` y `GET /api/movimientos` | ✅ Cumplido | Respuesta JSON paginada en $< 200\text{ ms}$. |
| **RF-11 a RF-16** (Sugerencia de compra, pedidos, recepción, CSV, reportes analíticos) | Debería / Podría (*Should / Could*) | Pospuestos formalmente fuera del alcance del MVP en la Sección 1 del Contrato Técnico v1.1. | Documentados en la tabla de alcance de `02-contrato-tecnico_TacosTonyMovil_Leon_v1.1.md` | ⏸️ Pospuesto (Fuera del MVP) | Cumple la regla de trazabilidad: todos los *Must* (`RF-01` a `RF-10`) están 100% implementados. |

---

## 2. Resumen de Requisitos Funcionales

- **Total de requisitos funcionales del proyecto:** 16 (`10 Must`, `5 Should`, `1 Could`)
- **Total de requisitos *Must* comprometidos en el MVP:** 10 (`RF-01` al `RF-10`, con 30 criterios `CA-01.1` a `CA-10.3`)
- **Requisitos *Must* cumplidos completamente:** **10 de 10 (100%)** ✅
- **Requisitos cumplidos parcialmente:** 0
- **Requisitos *Must* no cumplidos:** 0
- **Requisitos *Should / Could* pospuestos para fases posteriores:** 6 (`RF-11` al `RF-16`, según Contrato Técnico v1.1)

---

## 3. Requisitos No Funcionales (RNF) — Verificación

| RNF | Criterio medible del Contrato v1.1 | ¿Cómo se verificó? | Estado |
|---|---|---|---|
| **RNF-01 (Rendimiento en ventas)** | `POST /api/ventas` procesa la transacción ACID de descuento por receta en $< 1.5\text{ s}$ (máx. $2.0\text{ s}$). | Medición en pestaña *Network* de Chrome DevTools al confirmar ventas multi-producto en `app.php`; latencia registrada entre `80 ms` y `320 ms`. | ✅ Cumplido |
| **RNF-02 (Rendimiento móvil)** | La vista de Existencias está interactiva (TTI) en $< 3.0\text{ s}$ bajo perfil móvil 4G. | Hoja de estilos única (`styles.css` $< 15\text{ KB}$) sin frameworks pesados y consulta indexada a `3478788_insumos`. Carga interactiva en $< 1.2\text{ s}$. | ✅ Cumplido |
| **RNF-03 (Compatibilidad y Responsive)** | Cero desplazamiento horizontal (`scrollWidth <= innerWidth`) desde `360 px` hasta `1280 px`. | Verificación en emulador móvil de Chrome DevTools a `360×640`, `390×844`, `412×915` y `1280×800` con `overflow-x: hidden` y tarjetas flexibles. | ✅ Cumplido |
| **RNF-04 (Usabilidad y Ergonomía táctil)** | Todos los controles interactivos miden $\ge 48\times 48\text{ px}$ y el registro de una merma toma $\le 4$ toques. | Variable CSS `--touch-min-target: 48px` aplicada a inputs, botones, steppers y pestañas de la barra inferior. El acceso rápido desde la tarjeta de insumo permite registrar merma en 3 toques. | ✅ Cumplido |
| **RNF-05 (Seguridad)** | Contraseñas con `bcrypt` (costo 10), consultas preparadas PDO, credenciales en `.env` fuera del repositorio, expiración a los 30 min y RBAC en backend. | Inspección de hashes `$2y$10$...` en `3478788_usuarios`, validación de `.gitignore` + `.htaccess` bloqueando `.env`, y prueba de rechazo `403` con usuario `colab.tony`. | ✅ Cumplido |
| **RNF-06 (Trazabilidad e Inmutabilidad)** | El 100% de los cambios de stock quedan en `3478788_movimientos_inventario` sin endpoints de edición o borrado. | Prueba en vivo con el botón `🔒 Probar Inmutabilidad (CA-10.2)` en la pestaña `Auditoría`, confirmando respuesta `405 Method Not Allowed`. | ✅ Cumplido |

---

## 4. Uso de IA en esta Etapa

| Agente / herramienta usada | Para qué se usó | Qué tuvo que corregirse o ajustarse manualmente |
|---|---|---|
| **Gemini (Antigravity)** | Diagnóstico de documentos de la Etapa 3, auditoría de seguridad para externalizar credenciales hacia `.env` con un lector nativo en PHP (`cargarEnv()`), adaptación de la identidad corporativa real de Tacos Tony (`#F6821F`, `#073A79`, `#F9D864`, logotipo e imágenes reales de productos) y generación del backend API-First (`api/index.php`) e interfaz móvil (`app.php`). | 1. **Externalización de secretos:** En la práctica previa la contraseña de BD estaba fija en `config.php`; se corrigió creando `.gitignore`, `.env.example`, bloqueo en `.htaccess` y carga desde `.env` ingresado manualmente por el alumno.<br>2. **Identidad visual real:** El primer prototipo de login usaba colores genéricos (`#b91c1c`) y emojis; se reemplazaron por la paleta oficial de `estilogenerico.css` y las imágenes reales de `Imagenes/`.<br>3. **Rutas autocontenidas para FTP:** Se copiaron los logos, iconos y fotos de productos dentro de `login/assets/img/` para evitar enlaces rotos al desplegar la carpeta en `/3478788/login/` vía FileZilla. |
