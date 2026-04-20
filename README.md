# Proyecto Tacos Tony - Gestión de Suministros y Perfil

Este proyecto es un sistema de gestión para la taquería **Tacos Tony**, desarrollado en PHP. Permite administrar inventario, proveedores, movimientos, ventas y perfiles de empleados.

## Modificaciones Recientes

### 1. Reporte de Suministros Detallado
Se actualizó el reporte de **"Suministros por Fecha"** para incluir un desglose dinámico:
*   **Consulta SQL:** Calcula el Costo Total multiplicando `cantidad * costo`.
*   **Interfaz:** Botón **"▼ Ver detalle"** que despliega una subtabla con materiales, cantidades, costos unitarios y subtotales.
*   **Estética:** Uso de colores corporativos (Azul #073A79 y Naranja #F6821F).

### 2. Gestión de Perfil y Seguridad
Se habilitó la funcionalidad de cambio de contraseña en `Configuracion.php`:
*   **Validación:** Mínimo 8 caracteres, validado por JavaScript y PHP.
*   **Cifrado:** Las contraseñas se cifran usando **SHA2-256** directamente en MySQL para mantener la compatibilidad con `validar_login.php`.
*   **Procesamiento:** Se creó el archivo `barra_lateral/Pant_Ajustes/guardar_contrasena.php` para manejar la lógica de actualización.

### 3. Filtro de Ventas
En la sección de **Modificar Ventas**, se limitó la visualización de registros a únicamente las ventas realizadas en el **día actual** para agilizar la búsqueda de tickets recientes.

## Estructura de Base de Datos

*   `t_empleados`: Almacena usuarios, salarios y contraseñas (SHA2-256).
*   `t_proovedores` / `t_proporcionar_general` / `t_proporcionar_particular`: Gestión de suministros.
*   `t_materiales`: Catálogo de insumos.
*   `t_vender_general`: Cabecera de tickets de venta.

## Instrucciones de Uso

### Reportes
1.  Vaya a **Reportes** > **Suministros por Fecha**.
2.  Filtre por mes/año y use el botón de detalle para ver el desglose.

### Seguridad del Perfil
1.  Vaya a **Configuración** > **Editar Perfil**.
2.  Ingrese una nueva contraseña y guarde los cambios.
3.  El sistema le confirmará el éxito de la operación.

---
*Nota: Este proyecto utiliza rutas relativas estandarizadas. Asegúrese de mantener la estructura de carpetas para el correcto funcionamiento de los includes de seguridad y conexión.*
