# Documento de Visión y Alcance

**Proyecto:** Tacos Tony Inventario Móvil — control de inventario web responsive para Tacos Tony Camino Real
**Equipo:** Equipo 3 — Alexander Yamil Alcázar Muroaga, Fernando León Del Río, Emiliano André Castaños Mejía
**Fecha:** 22/09/2026
**Versión:** 1.0

---

## 1. Problema

### 1.1 Descripción del problema
La sucursal Tacos Tony Camino Real no tiene un registro confiable de sus materias primas (carne, tortillas, verdura, salsas, bebidas, desechables). El stock se conoce revisando físicamente la bodega y el refrigerador, las compras de reabastecimiento se acuerdan de manera informal y las mermas (producto caducado, dañado o mal preparado) no se registran. Como consecuencia, no hay forma de saber cuánto insumo consume realmente cada venta, cuánto se pierde y cuándo hay que volver a comprar.

El personal que necesita esta información normalmente está de pie, en cocina o en bodega, sin acceso a una computadora de escritorio, por lo que cualquier registro que dependa de "sentarse frente a la PC" termina sin hacerse.

### 1.2 ¿Quién lo sufre?
- **Gerente de sucursal:** decide qué y cuánto comprar sin datos; no puede explicar diferencias entre lo comprado y lo vendido.
- **Colaboradores (cocina y mostrador):** pierden tiempo revisando existencias a mano y se enteran de que falta un insumo cuando ya se necesita durante el servicio.
- **Dueño/franquicia:** absorbe el costo de desperdicios, faltantes y posibles robos que no se detectan.

### 1.3 Evidencia
Platicamos con el Dueño de Tacos Tony Camino Real, el señor Miguel León. La entrevista quedó documentada y resaltamos lo siguiente:

| Área | Situación actual reportada |
|---|---|
| Consulta de stock | Se hace por revisión física; no existe un registro digital. |
| Reportes de insumos, costos y stock | Son incompletos o no existen. |
| Pedidos de reabastecimiento | Se coordinan de manera informal (mensajes, de palabra), sin registro del estatus. |
| Control de inventario en general | No hay un registro claro de entradas, salidas ni mermas. |
| Dispositivos | Hay una computadora de escritorio en la sucursal, más los celulares de los empleados. |

Se va a incluir la entrevista en la entrega de la tarea.


### 1.4 Impacto de no resolverlo
- Se siguen quedando sin insumos a media operación, lo que obliga a compras de emergencia (más caras) o a dejar de vender productos del menú.
- Las pérdidas por merma y robo siguen invisibles; no se pueden medir ni reducir.
- Las compras se siguen haciendo "a ojo": se sobrecompra lo perecedero (más desperdicio) y se subcompra lo que sí rota.

---

## 2. Solución propuesta

### 2.1 Descripción general
Una aplicación web **responsive, diseñada primero para celular**, que el gerente y los colaboradores usan desde el navegador de su teléfono. Permite dar de alta insumos y las recetas de cada producto del menú; al registrar ventas (capturadas o importadas, simulando el punto de venta) descuenta automáticamente los insumos según la receta; registra entradas y mermas; y avisa cuándo un insumo está bajo, sugiriendo cuánto reabastecer con base en el consumo reciente.

### 2.2 ¿Por qué esta solución y no otra?

| Alternativa considerada | Por qué no la elegimos |
|---|---|
| Página web de escritorio (nuestro diseño original del SRS) | El personal no trabaja frente a una computadora; el registro ocurre en cocina y bodega. |
| App nativa (Android/iOS) | Requiere instalación, publicación en tiendas y dos plataformas; más costo y tiempo para un equipo de 4 en un semestre. |
| Hoja de cálculo compartida | No descuenta insumos automáticamente por receta, no controla permisos por rol y es fácil de romper desde el celular. |
| Software comercial de inventario para restaurantes | Costo de licencia mensual y no se adapta a las recetas ni a la forma de operar de la sucursal. |

La web responsive funciona en cualquier celular con navegador, no requiere instalación, se mantiene con un solo código y conserva lo que ya definimos en el SRS (roles, módulos, base de datos MySQL).

---

## 3. Usuarios y casos de uso

### 3.1 Perfiles de usuario (personas)

| Perfil | Rol/contexto | Necesidad principal |
|---|---|---|
| Gerente | Persona con experiencia y confianza del dueño. Ha usado programas para restaurantes, pero nunca un sistema de inventario. Acceso completo. Uso diario, frecuentemente desde el celular mientras supervisa. | Saber cuánto hay, cuánto se consume y cuánto se pierde, para decidir compras y reducir mermas. |
| Colaborador | Personal de cocina o mostrador con experiencia básica en herramientas digitales. Acceso limitado. Uso diario, con las manos ocupadas y poco tiempo. | Consultar rápido si hay insumo suficiente y confirmar cuando llega un pedido, sin poder modificar datos sensibles. |

### 3.2 Casos de uso clave
1. **Consultar existencias**: Como colaborador, quiero buscar un insumo desde mi celular y ver su stock actual para saber si alcanza para el servicio sin ir a revisar a la bodega.
2. **Registrar ventas del día**: Como gerente, quiero capturar o importar las ventas del día para que el sistema descuente automáticamente los insumos usados según la receta de cada producto.
3. **Registrar merma**: Como gerente, quiero registrar insumos caducados, dañados o desperdiciados con su motivo para que el inventario digital coincida con el físico y pueda ver dónde se pierde.
4. **Atender alertas de reabastecimiento**: Como gerente, quiero recibir una alerta con la cantidad sugerida a comprar cuando un insumo llegue a su mínimo para pedir a tiempo y sin sobrecomprar.
5. **Confirmar recepción de pedido**: Como colaborador, quiero marcar un pedido de reabastecimiento como recibido para que las cantidades entren al inventario sin que el gerente las capture de nuevo.

---

## 4. Alcance

El sistema se organiza en tres módulos conectados:

- **Módulo 1 — Catálogo de insumos y recetas:** define qué insumos existen, su stock mínimo y cuánto de cada insumo lleva cada producto del menú.
- **Módulo 2 — Ventas y movimientos:** registra ventas (que descuentan insumos según la receta del Módulo 1), entradas y mermas.
- **Módulo 3 — Alertas, reabastecimiento y reportes:** usa el stock y el consumo generados por el Módulo 2 para alertar, sugerir compras y reportar. Al confirmar un pedido recibido, genera entradas de vuelta en el Módulo 2.

### 4.1 Incluido en este proyecto
- [ ] Inicio de sesión con roles Gerente y Colaborador
- [ ] Catálogo de insumos (unidad, categoría, stock mínimo)
- [ ] Recetas por producto del menú
- [ ] Registro de ventas simuladas (captura manual e importación de archivo)
- [ ] Descuento automático de insumos por receta
- [ ] Registro de entradas por compra y de mermas con motivo
- [ ] Consulta de existencias con búsqueda y filtros
- [ ] Alertas de stock bajo y lista sugerida de reabastecimiento
- [ ] Confirmación de pedidos recibidos
- [ ] Historial de movimientos y reportes de consumo y merma
- [ ] Interfaz responsive pensada primero para celular

### 4.2 Explícitamente fuera de alcance
- Integración real con el sistema de punto de venta de la sucursal (en el MVP las ventas se capturan o importan).
- Cobro a clientes y pagos a proveedores.
- Envío automático de pedidos a proveedores (el sistema sugiere; la compra se hace por fuera).
- Contabilidad general, recursos humanos y nómina.
- App nativa o publicación en tiendas de aplicaciones.
- Manejo de varias sucursales.

---

## 5. Requisitos funcionales

| ID | Requisito | Prioridad (Must/Should/Could) |
|---|---|---|
| RF-01 | El sistema debe permitir iniciar sesión con usuario y contraseña y asignar a cada usuario un rol (Gerente o Colaborador). El Colaborador solo puede consultar existencias, consultar pedidos y confirmar recepciones; cualquier otra acción debe estar bloqueada para ese rol. | Must |
| RF-02 | El sistema debe permitir al Gerente dar de alta, modificar y desactivar insumos con nombre, unidad de medida (kg, g, L, ml, pieza), categoría y stock mínimo. Un insumo con movimientos no se elimina, solo se desactiva. | Must |
| RF-03 | El sistema debe permitir al Gerente registrar productos del menú y su receta: la cantidad de cada insumo que consume una unidad vendida del producto. | Must |
| RF-04 | El sistema debe permitir al Gerente capturar ventas indicando producto y cantidad vendida. | Must |
| RF-05 | Al registrar una venta, el sistema debe descontar de cada insumo la cantidad de la receta multiplicada por las unidades vendidas. Si el stock resultante es negativo, la venta se registra de todos modos y el insumo se marca con "discrepancia" para revisión del Gerente. | Must |
| RF-06 | El sistema debe permitir al Gerente registrar entradas de insumos por compra (insumo, cantidad, fecha), sumándolas al stock. | Must |
| RF-07 | El sistema debe permitir al Gerente registrar mermas indicando insumo, cantidad y motivo (caducidad, daño, error de preparación u otro), restándolas del stock. | Must |
| RF-08 | El sistema debe mostrar a ambos roles la lista de existencias con stock actual, con búsqueda por nombre y filtro por categoría y por estado (normal, bajo, discrepancia). | Must |
| RF-09 | El sistema debe marcar como "stock bajo" todo insumo cuyo stock sea menor o igual a su stock mínimo y mostrarlo en la pantalla principal del Gerente. | Must |
| RF-10 | El sistema debe registrar cada movimiento (venta, entrada, merma, recepción) con fecha y hora, usuario, insumo y cantidad, y permitir al Gerente consultarlo filtrando por fecha, tipo e insumo. | Must |
| RF-11 | El sistema debe generar una lista sugerida de reabastecimiento para insumos en stock bajo, calculando: (consumo promedio diario de los últimos 7 días × días de cobertura configurados) − stock actual, redondeado hacia arriba a la unidad del insumo. | Should |
| RF-12 | El sistema debe permitir al Gerente convertir la lista sugerida en un pedido de reabastecimiento y ajustar sus cantidades antes de guardarlo. | Should |
| RF-13 | El sistema debe permitir al Colaborador marcar un pedido como recibido (completo o con cantidades distintas a las pedidas), generando automáticamente las entradas correspondientes. | Should |
| RF-14 | El sistema debe permitir importar ventas desde un archivo CSV (producto, cantidad, fecha) y rechazar las filas con productos inexistentes, indicando cuáles fueron. | Should |
| RF-15 | El sistema debe generar un reporte por periodo con consumo por insumo, merma por insumo y por motivo, y el porcentaje de merma respecto al consumo. | Should |
| RF-16 | El sistema debe permitir exportar los reportes y el historial de movimientos a CSV. | Could |

---

## 6. Requisitos no funcionales

| ID | Requisito | Categoría |
|---|---|---|
| RNF-01 | El descuento de insumos y la actualización del stock tras registrar una venta deben completarse en menos de 2 segundos, medido en 20 ventas de prueba. | Rendimiento |
| RNF-02 | La pantalla de existencias debe cargar en menos de 3 segundos en un celular. | Rendimiento |
| RNF-03 | Toda la interfaz debe usarse sin desplazamiento horizontal en anchos de 360 px a 1280 px y funcionar en las dos últimas versiones de Chrome (Android) y Safari (iOS). | Compatibilidad |
| RNF-04 | Los botones y controles táctiles deben tener un tamaño adecuado para su uso, y registrar una merma no debe requerir más de 4 pasos desde la pantalla principal. | Usabilidad |
| RNF-05 | Las contraseñas se almacenan encryptadas, la sesión expira tras 30 minutos de inactividad y el 100 % de las operaciones de escritura validan el rol en el servidor, no solo en la interfaz. | Seguridad |
| RNF-06 | El 100 % de los movimientos quedan en el historial con usuario y fecha-hora y no pueden editarse ni borrarse; una corrección se registra como un movimiento nuevo. | Trazabilidad |

---

## 7. Métricas de éxito

1. **Exactitud del descuento por receta:** en un conjunto de 30 ventas de prueba con resultado calculado a mano, el 100 % de los insumos queda con el stock esperado.
2. **Rapidez de consulta:** un colaborador encuentra el stock de un insumo desde su celular en menos de 30 segundos, sin ayuda, en al menos 4 de 5 intentos de prueba.
3. **Facilidad de registro:** al menos el 80 % de los usuarios de prueba registran una merma y una entrada sin ayuda en su primer intento.

---

## 8. Riesgos y supuestos

| Tipo | Descripción | Mitigación |
|---|---|---|
| Riesgo | La sucursal no tiene recetas estandarizadas (cuánto insumo lleva cada taco), lo que hace impreciso el descuento automático. | Definir las recetas con el gerente en una sesión de trabajo usando cantidades aproximadas, y permitir editarlas después (RF-03). |
| Riesgo | Señal débil o nula en cocina y bodega. | Pantallas ligeras (RNF-02) y mensajes de error claros cuando una operación no se guarda. |
| Riesgo | El personal olvida registrar mermas y el inventario se desfasa. | Registro de merma en pocos pasos (RNF-04) e insumos con "discrepancia" visibles para el gerente. |
| Supuesto | El gerente y los colaboradores tienen un celular con navegador actualizado y acceso a internet durante el turno. | Confirmarlo con la sucursal antes del diseño detallado. |
| Supuesto | El gerente está dispuesto a validar recetas y participar en las pruebas. | Acordar fechas de validación desde el inicio del desarrollo. |
| Supuesto | La base de datos será MySQL, por compatibilidad con la infraestructura existente. | Mantener el acceso a datos desacoplado para facilitar una futura integración con el punto de venta. |

---

## 9. Uso de IA en esta etapa

| Agente/herramienta usada | Para qué se usó | Qué tuvo que corregirse manualmente |
|---|---|---|
| Claude Opus 5.5 (Anthropic) | Pasar nuestro SRS original (página web de escritorio) a esta plantilla de visión y alcance y adaptarlo a una app web responsive para celular. | Antes de redactar, la IA preguntó si la solución sería una app nativa o multiplataforma; tuvimos que aclarar que es web responsive y que la conexión con el punto de venta sería simulada en el MVP, no integrada.
||||

Además se revisó todo el .md para notar cualquier inconsistencia con lo que se tiene planeado hacer 