# Instrucciones - Etapa 3: Construcción y Despliegue del MVP

## 🎯 Objetivo de aprendizaje
Construir, con asistencia de agentes de IA y guiándose por su contrato técnico, un **Producto Mínimo Viable (MVP)** funcional, desplegado y verificable contra los criterios de aceptación que ustedes mismos definieron en la Etapa 2.

---

## 👥 Modalidad de trabajo
- **Desarrollo:** En equipo.
- **Entrega en Canvas:** Individual, igual que en las etapas anteriores. Cada integrante sube su propio archivo y debe poder explicar cualquier parte del sistema.

---

## ✅ ¿Qué es un "MVP listo"?
Su MVP se considera entregable cuando cumple **todo** lo siguiente:

- [ ] **Requisitos Funcionales Must:** Todos los requisitos funcionales *Must* de su contrato están implementados y cumplen sus criterios de aceptación.
- [ ] **Módulos integrados:** Los 3 módulos (o más) funcionan de punta a punta y están conectados entre sí, tal como lo describe su contrato.
- [ ] **Despliegue accesible:** El sistema está desplegado en el subdominio asignado a su equipo y es accesible por internet.
- [ ] **Diseño móvil (Responsive):** Funciona correctamente desde un celular (diseño responsive, usable en pantalla pequeña). *Esto es una materia de web para móviles: se revisa.*
- [ ] **Trazabilidad:** Los requisitos *Should* y *Could* pueden quedar pendientes, pero deben aparecer como tales en la tabla de trazabilidad.

---

## 🌐 Reglas del entorno de hosting
El hosting es compartido entre los 6 equipos, cada uno con su subdominio y su base de datos. Para que nadie afecte a los demás:

1. **Desarrollo local:** Desarrollen en local y desplieguen al hosting para demostraciones y entregas. No programen directamente en el servidor.
2. **Uso responsable:** No hagan pruebas de carga ni ejecuten procesos pesados o infinitos en el servidor: afecta a los demás equipos.
3. **Aislamiento:** Usen únicamente las credenciales de su base de datos. No intenten acceder a recursos de otros equipos.

---

## 🔒 Reglas mínimas de seguridad (Obligatorias)
- **Credenciales fuera del repositorio:** Ninguna credencial en el repositorio (contraseñas de base de datos, llaves, tokens). Usen un archivo de configuración o `.env`.
- **Consultas preparadas:** Consultas a la base de datos con sentencias preparadas (`PDO` o equivalente), nunca concatenando datos del usuario.
- **Hachado de contraseñas:** Contraseñas almacenadas con hash (`password_hash` o equivalente), nunca en texto plano.
- **Validación del lado del servidor:** Validación obligatoria de entradas del lado del servidor.
- **Datos de prueba ficticios:** No usen información real de personas.

---

## 🤖 Uso de agentes de IA en esta etapa
- Pidan al agente que lea sus archivos `01-vision-y-alcance.md` y `02-contrato-tecnico.md` y pídanle que proponga una forma de completarlos.
- Es posible que el trabajo se divida en tareas pequeñas (por requisito o escenario), pregunten a la IA si pueden hacer deployments pequeños o uno hasta el final.
- Revisen y entiendan el código que el agente genera antes de integrarlo. Se les podría pedir explicar partes del sistema en la demo.
- Mantengan el registro de **Uso de IA**: qué agente usaron, para qué, y si tuvieron que corregir algo o ajustar algo a mano.

---

## 🔄 Si el contrato cambia
> [!IMPORTANT]
> Si durante el desarrollo descubren que un criterio de aceptación era ambiguo o una decisión de arquitectura era inviable, actualicen el contrato (`02-contrato-tecnico.md`) con una nueva versión. **No basta con cambiar el código: el contrato debe seguir siendo cierto.**

---

## 📦 Qué tienen que entregar en Canvas
Un archivo `.md` que responda la pregunta: **¿Cómo sé si el MVP es correcto?**

> *Nota:* La propuesta es la siguiente (si tú y tu equipo quieren hacer otra propuesta, son libres de hacerlo siempre y cuando justifiquen y respondan la pregunta anterior):

### Archivo a entregar:
`03-trazabilidad_[NombreDelProyecto]_[TuApellido].md`, basado en la plantilla `03-trazabilidad-template.md`, que incluya:

#### Encabezado del archivo (al inicio):
- **URL del MVP desplegado:** Subdominio del equipo.
- **Instrucciones de ejecución:** Usuario y contraseña.
- *(Opcional)* Versión del contrato técnico con la que se construyó el MVP.

#### Contenido requerido:
1. **Tabla de trazabilidad completa:** Cada RF → Criterio de aceptación → Dónde se implementó → Estado (`✅ cumplido` / `⚠️ parcial` / `❌ no cumplido`).
2. **Resumen de requisitos:** Requisitos cumplidos, parciales y no cumplidos.
3. **Verificación de los requisitos no funcionales (RNF):** Cómo comprobaron cada uno.
4. **Sección de Uso de IA:** Registro de prompts, agentes y ajustes manuales.

---

## 💡 Notas para el equipo
- **Transparencia:** Si algo no funciona, márquenlo como parcial o no cumplido en la trazabilidad.
- **Prioridades:** Lo que se pide es un producto mínimo: prioricen que lo esencial funcione bien antes de agregar extras.
- **Soporte:** Cualquier duda sobre el stack, el hosting o el alcance, consúltenla pronto; no esperen a la semana de entrega.
