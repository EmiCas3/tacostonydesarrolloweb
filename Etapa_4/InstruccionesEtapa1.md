Objetivo de aprendizaje
Que el equipo defina con precisión el problema que va a resolver su proyecto, la solución propuesta y los requisitos que guiarán el desarrollo, como base formal para las siguientes etapas del proyecto.

⚠ Muy importante: el problema no debe ser tan pequeño que no represente reto, ni tan grande que se les salga de las manos. Como referencia, el sistema debe tener al menos 3 módulos funcionales que se relacionen entre sí (las acciones de uno afectan datos o decisiones de otro - no basta con 3 pantallas independientes), y al menos uno de esos módulos debe incluir una regla de negocio no trivial (ej. cálculos, validaciones condicionales, permisos por rol, generación de reportes).

Ejemplo: un sistema para una panadería con (1) catálogo y control de recetas/insumos, (2) registro de ventas que descuenta automáticamente insumos del inventario según la receta vendida, y (3) reporte de reabastecimiento que alerta qué insumos comprar según ventas proyectadas. Aquí los tres módulos están conectados: vender un producto impacta el inventario, y el inventario impacta las alertas de compra.

 

Modalidad de trabajo
Elaboración: en equipo (los 6 equipos ya conformados).
Entrega: individual. Cada integrante debe subir su propio archivo a Canvas, aunque el contenido haya sido elaborado en conjunto con su equipo. El objetivo de esto es que cada quien revise, entienda y pueda defender el documento completo — no solo la parte que redactó.
No es necesario que el contenido varíe entre integrantes del mismo equipo; sí es necesario que cada uno lo suba por su cuenta.
 

Definición de la entrega
Un archivo Markdown (.md) basado en la plantilla 01-vision-y-alcance-template.md proporcionada al final de esta página, completamente llenado con la información de su proyecto. El documento debe cubrir, como mínimo, estas secciones (el punto 8, riesgos y supuestos, es opcional):

Problema — descripción, a quién afecta, evidencia de que es real, impacto de no resolverlo. 
Solución propuesta — qué van a construir y por qué esa solución. 
Usuarios y casos de uso — al menos 2 perfiles de usuario y 3 casos de uso redactados como "Como [perfil], quiero [acción] para [beneficio]".
Alcance — qué incluye el proyecto y qué queda explícitamente fuera.
Requisitos funcionales — mínimo 8, numerados (RF-01, RF-02...) y priorizados (Must/Should/Could).
Requisitos no funcionales — mínimo 3, numerados (RNF-01...).
Métricas de éxito — al menos 2 métricas medibles.
Riesgos y supuestos — al menos 1 de cada uno.
Uso de IA — redacción o tabla sobre qué agente(s) de IA usaron, para qué, y qué tuvieron que corregir manualmente. Esta sección es obligatoria. No basta con "usamos DeepSeek para todo".
 

Formato y entrega
Archivo en formato Markdown puro (.md), no Word ni PDF.
Nombre del archivo: 01-vision-alcance_[NombreDelProyecto]_[TuApellido].md
(ejemplo: 01-vision-alcance_SapoGenio_Martinez.md)
Sube el archivo directamente en la tarea correspondiente en Canvas (no como link a Google Docs ni a un repositorio privado).
Si tu equipo ya tiene el documento en un repositorio de GitHub, puedes incluir el link del repo como referencia adicional, pero el archivo .md subido a Canvas es el que se califica.
 

Fecha límite
Martes 22 de septiembre, 11:59 p.m.
Entregas después de esa hora se consideran tardía y podría (y debería) recibir penalización.

 

Rúbrica de evaluación (100 pts)
Criterio	Puntos	Qué se evalúa
Problema claro y sustentado	15	El problema claramente definido y delimitado 
Solución justificada	10	Se explica por qué esta solución y no otra
Usuarios y casos de uso	15	Perfiles realistas, casos de uso completos y accionables
Alcance definido	10	Distinción clara entre lo incluido y lo excluido
Requisitos funcionales	20	Completos, numerados, priorizados, verificables (no ambiguos)
Requisitos no funcionales	10	Relevantes y medibles, no genéricos
Métricas de éxito	10	Medibles, no vagas (no es suficiente un "que funcione bien")
Uso de IA documentado	10	Honesto y específico sobre el uso del agente para esta entrega, y sus limitaciones.
Notas para el equipo
Este documento es la base de todo lo que sigue, así que vale la pena discutirlo bien como equipo antes de llenarlo.
Si usan un agente de IA para redactar o refinar el documento, revisen que los requisitos realmente correspondan a su proyecto y no queden genéricos o inflados por el modelo. 
Cualquier duda sobre el alcance de su proyecto particular, consúltenla antes del martes.
Plantilla 
La siguiente plantilla pretende ser una guía para la entrega de esta actividad. No es obligatorio que se basen en ella, sin embargo, contiene los criterios que pide la rúbrica.

01-vision-y-alcance-PLANTILLA.md