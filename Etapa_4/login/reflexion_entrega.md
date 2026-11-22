# Evidencia de Entrega — Práctica de Login (3478788)

**Proyecto:** Tacos Tony Inventario Móvil  
**Alumno:** Fernando León Del Río (ID: `3478788`)  
**URL de la práctica:** https://equipo4.centrofuyuan.com/3478788/login/  

**Usuario de prueba:** `fer.leon_05`  
**Rol asignado:** `GERENTE`  
*(La contraseña se entrega por separado para evaluación: `1234567890`; en la base de datos MySQL únicamente se almacena el hash seguro generado con bcrypt costo 10).*

---

## Reflexión de Entrega (3 líneas)

1. **Método de despliegue:** Los archivos fueron transferidos mediante el cliente FTP/SFTP FileZilla hacia la carpeta remota `/3478788/login/` del hosting compartido, inicializando la tabla con prefijo `3478788_usuarios` en la base de datos compartida `uzfjkpte_equipo4` a través de `setup.php` / MySQL Workbench.
2. **Qué falló o qué retos surgieron:** _(Completar según tu experiencia real al subir por FileZilla, por ejemplo: "El puerto 3306 de MySQL remoto estaba bloqueado desde el exterior, por lo que la tabla se gestionó mediante el script web setup.php y gestorBDD.php", o "Se debió configurar .htaccess para asegurar que no se bloquearan las peticiones en Apache")._
3. **Qué aprendí:** Comprendí la importancia de nunca almacenar contraseñas en texto plano utilizando `password_hash` y `password_verify`, el manejo estricto de sesiones PHP con expiración de 30 minutos (RNF-05), la protección contra inyecciones SQL con sentencias preparadas en PDO, y el diseño táctil mobile-first de 48px para entornos de cocina (RNF-04).
