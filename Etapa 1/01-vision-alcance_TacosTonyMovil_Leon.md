# Documento de Visión y Alcance

**Proyecto:** Tacos Tony Inventario Móvil — control de inventario web responsive para Tacos Tony Camino Real
**Equipo:** Equipo 3 — Alexander Yamil Alcázar Muroaga, Fernando León Del Río, Emiliano André Castaños Mejía
**Fecha:** 06/10/2026
**Versión:** 2.0

---

## 1. Problema

### 1.1 Descripción del problema
La sucursal Tacos Tony Camino Real no tiene un registro confiable de sus materias primas (carne, pan árabe, tortillas, verdura, salsas, bebidas, desechables). Las existencias se conocen revisando físicamente la bodega y el refrigerador, las compras a proveedores se acuerdan de manera informal y no queda registro de cuánto entró ni de cuánto consumió cada venta. Como consecuencia, no hay forma de saber cuánto material gasta realmente cada producto del menú, cuánto cuesta producirlo ni cuándo hay que volver a comprar.

El personal que necesita esta información normalmente está de pie, en cocina, mostrador o bodega, sin acceso a una computadora de escritorio, por lo que cualquier registro que dependa de "sentarse frente a la PC" termina sin hacerse.

### 1.2 ¿Quién lo sufre?
- **Gerente de sucursal:** decide qué y cuánto comprar sin datos; no puede explicar diferencias entre lo comprado y lo vendido ni cuánto gana por producto.
- **Colaboradores (cocina y mostrador):** pierden tiempo revisando existencias a mano y se enteran de que falta un material cuando ya se necesita durante el servicio.
- **Dueño/franquicia:** absorbe el costo de faltantes, sobrecompras y diferencias de inventario que no se detectan.

### 1.3 Evidencia
Platicamos con el dueño de Tacos Tony Camino Real, el señor Miguel León. La entrevista quedó documentada y resaltamos lo siguiente:

| Área | Situación actual reportada |
|---|---|
| Consulta de existencias | Se hace por revisión física; no existe un registro digital. |
| Reportes de ventas, costos y existencias | Son incompletos o no existen. |
| Compras a proveedores | Se coordinan de manera informal (mensajes, de palabra), sin registro de qué entró ni a qué costo. |
| Control de inventario en general | No hay un registro claro de entradas ni de salidas. |
| Dispositivos | Hay una computadora de escritorio en la sucursal, más los celulares de los empleados. |

La entrevista se incluye en la entrega de la tarea.

### 1.4 Impacto de no resolverlo
- Se siguen quedando sin material a media operación, lo que obliga a compras de emergencia (más caras) o a dejar de vender productos del menú.
- Las diferencias entre lo comprado y lo vendido siguen invisibles; no se pueden medir ni reducir.
- Las compras se siguen haciendo "a ojo": se sobrecompra lo perecedero y se subcompra lo que sí rota.
- No se conoce la ganancia real por producto, porque no se relaciona el costo de los materiales con el precio de venta.

---

## 2. Solución propuesta

### 2.1 Descripción general
Una aplicación web **responsive, diseñada primero para celular**, que el gerente y los colaboradores usan desde el navegador de su teléfono. Permite dar de alta los materiales del inventario, los productos del menú y la receta de cada producto; registrar las entradas de material por compra a proveedores; y registrar las ventas (capturadas a mano, simulando el punto de venta), que descuentan automáticamente los materiales según la receta. Una pantalla de inicio avisa qué materiales están bajos y el gerente consulta reportes de ventas, costos y suministros.

### 2.2 ¿Por qué esta solución y no otra?

| Alternativa considerada | Por qué no la elegimos |
|---|---|
| Página web pensada solo para escritorio | El personal no trabaja frente a una computadora; el registro ocurre en cocina, mostrador y bodega. |
| App nativa (Android/iOS) | Requiere instalación, publicación en tiendas y dos plataformas; más costo y tiempo para un equipo de 3 en un semestre. |
| Hoja de cálculo compartida | No descuenta materiales automáticamente por receta, no controla permisos por rol y es fácil de romper desde el celular. |
| Software comercial de inventario para restaurantes | Costo de licencia mensual y no se adapta a las recetas ni a la forma de operar de la sucursal. |

La web responsive funciona en cualquier celular con navegador, no requiere instalación y se mantiene con un solo código.

---

## 3. Usuarios y casos de uso

### 3.1 Perfiles de usuario (personas)

| Perfil | Rol/contexto | Necesidad principal |
|---|---|---|
| Gerente | Persona con experiencia y confianza del dueño. Ha usado programas para restaurantes, pero nunca un sistema de inventario. Acceso completo. Uso diario, frecuentemente desde el celular mientras supervisa. | Saber cuánto hay, cuánto entra y cuánto se vende, para decidir compras y conocer la ganancia por producto. |
| Colaborador | Personal de cocina o mostrador con experiencia básica en herramientas digitales. Acceso limitado. Uso diario, con las manos ocupadas y poco tiempo. | Consultar rápido si hay material suficiente y registrar las ventas del turno, sin poder modificar datos sensibles. |

### 3.2 Casos de uso clave
1. **Consultar existencias:** Como colaborador, quiero buscar un material desde mi celular y ver sus existencias para saber si alcanza para el servicio sin ir a revisar a la bodega.
2. **Registrar una venta:** Como colaborador, quiero capturar los productos vendidos a un cliente para que el sistema descuente automáticamente los materiales usados según la receta de cada producto.
3. **Registrar una entrada:** Como gerente, quiero registrar lo que entregó un proveedor (material, cantidad y costo) para que las existencias suban y quede el costo de compra.
4. **Atender el inventario bajo:** Como gerente, quiero ver en la pantalla de inicio qué materiales están bajos para comprar a tiempo.
5. **Corregir una venta:** Como colaborador, quiero modificar un producto o una cantidad de una venta ya registrada para que el inventario refleje lo que realmente se vendió.
6. **Revisar el corte del día:** Como gerente, quiero ver el reporte de ventas del día desde mi celular y descargarlo en PDF.

---

## 4. Alcance

El sistema se organiza en tres módulos conectados:

- **Módulo 1 — Catálogos:** define qué materiales, productos, recetas, clientes, proveedores y colaboradores existen.
- **Módulo 2 — Movimientos:** registra entradas (que suman existencias) y ventas (que descuentan materiales según la receta del Módulo 1).
- **Módulo 3 — Inicio y reportes:** usa las existencias, ventas y entradas del Módulo 2 para avisar de inventario bajo y generar los reportes del gerente.

### 4.1 Incluido en este proyecto
- [ ] Inicio de sesión con roles Gerente y Colaborador, recuperación de contraseña y edición de perfil
- [ ] Alta y consulta de materiales con búsqueda y ordenamiento
- [ ] Productos del menú (alta, edición con imagen) y catálogo de consulta
- [ ] Recetas por producto del menú
- [ ] Registro de entradas de material por proveedor
- [ ] Registro de ventas simuladas (captura manual) con descuento automático de materiales por receta
- [ ] Modificación de ventas registradas
- [ ] Pantalla de inicio con inventario bajo, tiempo guardado y gráfica de existencias
- [ ] Administración de clientes, proveedores y colaboradores
- [ ] Ocho reportes para el gerente con descarga en PDF
- [ ] Interfaz responsive pensada primero para celular

### 4.2 Explícitamente fuera de alcance
- Integración real con el sistema de punto de venta de la sucursal (las ventas se capturan a mano).
- Cobro a clientes, pagos a proveedores y facturación electrónica.
- Registro de mermas, pedidos de reabastecimiento y sugerencias automáticas de compra.
- Importación o exportación de datos en CSV.
- Contabilidad general, recursos humanos y nómina.
- App nativa o publicación en tiendas de aplicaciones.
- Manejo de varias sucursales.

---

## 5. Requisitos funcionales

| ID | Requisito | Prioridad (Must/Should/Could) |
|---|---|---|
| RF-01 | El sistema debe permitir iniciar y cerrar sesión con correo y contraseña. El rol se determina por el salario registrado del empleado: mayor a $7,500 es Gerente; de lo contrario, Colaborador. El Colaborador solo puede ver la pantalla de inicio, consultar el inventario y el catálogo, registrar y modificar ventas y editar su perfil; cualquier otra acción debe estar bloqueada para ese rol. | Must |
| RF-02 | El sistema debe permitir a cualquier usuario recuperar su contraseña (se le envía por correo una contraseña temporal de 8 caracteres) y editar su perfil: correo y contraseña (mínimo 8 caracteres). | Should |
| RF-03 | El sistema debe permitir al Gerente dar de alta materiales con nombre (único) y existencias iniciales (cero o más). | Must |
| RF-04 | El sistema debe mostrar a ambos roles la lista de materiales con sus existencias, con búsqueda por ID, nombre o existencias y ordenamiento por esos mismos campos. | Must |
| RF-05 | El sistema debe permitir al Gerente dar de alta productos del menú con nombre (único) y precio, y editar su nombre, precio e imagen. Ambos roles pueden consultar el catálogo de productos (imagen, nombre y precio) en páginas de 14 productos. | Must |
| RF-06 | El sistema debe permitir al Gerente asignar a un producto su receta: la cantidad de cada material que consume una unidad vendida. Cada asignación crea una nueva versión de la receta y la vigente es siempre la más reciente. | Must |
| RF-07 | El sistema debe permitir al Gerente registrar entradas de material indicando proveedor, fecha y uno o más materiales con cantidad y precio unitario, sumando cada cantidad a las existencias. | Must |
| RF-08 | El sistema debe permitir a ambos roles registrar una venta indicando cliente, fecha, si es servicio a domicilio y uno o más productos con su cantidad; el subtotal de cada producto es cantidad × precio. El servicio a domicilio solo se permite si el cliente tiene dirección completa. | Must |
| RF-09 | Al registrar una venta, el sistema debe descontar de cada material la cantidad de la receta vigente multiplicada por las unidades vendidas. Si las existencias de cualquier material no alcanzan para la venta completa, la venta se rechaza, no se guarda nada y se indica qué materiales faltan y cuánto. | Must |
| RF-10 | El sistema debe permitir a ambos roles modificar una venta registrada: cambiar el producto o la cantidad de un renglón, o agregar un producto. Al modificar, se devuelven al inventario los materiales del renglón anterior y se descuentan los del nuevo, con la misma validación de existencias de RF-09. | Must |
| RF-11 | El sistema debe mostrar a ambos roles una pantalla de inicio con: los materiales con inventario bajo (existencias menores o iguales a 5), el tiempo guardado (días desde la última entrada de cada material) y una gráfica con las existencias de los 15 materiales con más stock. | Must |
| RF-12 | El sistema debe permitir al Gerente registrar clientes (y editarlos), proveedores y colaboradores, validando que teléfono, correo y RFC no se repitan. | Must |
| RF-13 | El sistema debe ofrecer al Gerente ocho reportes, filtrables por periodo y descargables en PDF: ingreso por producto mensual, corte diario, descripción de ventas mensual, ganancia por producto, rotación de productos, ventas diarias por semana, materiales más usados y suministros mensuales. | Should |

---

## 6. Requisitos no funcionales

| ID | Requisito | Categoría |
|---|---|---|
| RNF-01 | El registro de una venta (validación, descuento de materiales y actualización de existencias) debe completarse en menos de 2 segundos, medido en 20 ventas de prueba. | Rendimiento |
| RNF-02 | La pantalla de inventario debe cargar en menos de 3 segundos en un celular. | Rendimiento |
| RNF-03 | Toda la interfaz debe usarse sin desplazamiento horizontal en anchos de 360 px a 1280 px y funcionar en las dos últimas versiones de Chrome (Android) y Safari (iOS). | Compatibilidad |
| RNF-04 | Los botones y controles táctiles deben tener un tamaño adecuado para usarse con el dedo, y registrar una venta de un producto no debe requerir más de 3 pantallas desde el menú principal. | Usabilidad |
| RNF-05 | Las contraseñas se almacenan cifradas (hash), la sesión expira tras 30 minutos de inactividad y el 100 % de las operaciones validan la sesión y el rol en el servidor, no solo en la interfaz. | Seguridad |
| RNF-06 | Toda operación que cambia existencias (entrada, venta, modificación de venta) se guarda completa o no se guarda; las existencias de un material nunca quedan negativas. | Integridad |

---

## 7. Métricas de éxito

1. **Exactitud del descuento por receta:** en un conjunto de 30 ventas de prueba con resultado calculado a mano, el 100 % de los materiales queda con las existencias esperadas.
2. **Rapidez de consulta:** un colaborador encuentra las existencias de un material desde su celular en menos de 30 segundos, sin ayuda, en al menos 4 de 5 intentos de prueba.
3. **Facilidad de registro:** al menos el 80 % de los usuarios de prueba registran una venta y una entrada sin ayuda en su primer intento.

---

## 8. Riesgos y supuestos

| Tipo | Descripción | Mitigación |
|---|---|---|
| Riesgo | La sucursal no tiene recetas estandarizadas (cuánto material lleva cada taco), lo que hace impreciso el descuento automático. | Definir las recetas con el gerente en una sesión de trabajo usando cantidades aproximadas, y permitir reasignarlas después (RF-06). |
| Riesgo | Señal débil o nula en cocina y bodega. | Pantallas ligeras (RNF-02) y mensajes de error claros cuando una operación no se guarda. |
| Riesgo | El inventario digital se desfasa del físico y el sistema rechaza ventas por falta de material (RF-09). | Mensaje que indica exactamente qué falta, y registro de la entrada correspondiente en pocos pasos (RF-07). |
| Supuesto | El gerente y los colaboradores tienen un celular con navegador actualizado y acceso a internet durante el turno. | Confirmarlo con la sucursal antes del diseño detallado. |
| Supuesto | El gerente está dispuesto a validar recetas y participar en las pruebas. | Acordar fechas de validación desde el inicio del desarrollo. |
| Supuesto | La base de datos será MySQL. | Mantener el acceso a datos desacoplado para facilitar una futura integración con el punto de venta. |

---

## 9. Uso de IA en esta etapa

| Agente/herramienta usada | Para qué se usó | Qué tuvo que corregirse manualmente |
|---|---|---|
| Claude Opus 5.5 (Anthropic) | Redactar el documento en la plantilla de visión y alcance a partir de la entrevista y de la lista de funciones definida por el equipo. | Antes de redactar, la IA preguntó si la solución sería una app nativa o multiplataforma; tuvimos que aclarar que es web responsive y que el punto de venta sería simulado. En la versión 1.0 la IA propuso funciones que el equipo no planea construir (mermas, pedidos de reabastecimiento, importación CSV); en la versión 2.0 le indicamos quitarlas y dejar solo el alcance acordado. |

Además se revisó todo el documento para detectar cualquier inconsistencia con lo que se tiene planeado construir.

---

## Historial de cambios

| Versión | Fecha | Cambio |
|---|---|---|
| 1.0 | 22/09/2026 | Versión inicial. |
| 2.0 | 06/10/2026 | Se ajustaron alcance, casos de uso y requisitos (RF-01 a RF-13, RNF-01 a RNF-06) al alcance definitivo acordado por el equipo. |
