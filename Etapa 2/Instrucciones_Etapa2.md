Objetivo de aprendizaje
Que el equipo traduzca los requisitos de la Etapa 1 en un contrato técnico verificable: decisiones de arquitectura justificadas, modelo de datos, interfaces y criterios de aceptación que permitan saber si el sistema funciona o no. Este contrato será la base del MVP (Etapa 3) y el contexto que le darán a su agente de IA para generar código.

Importante: el contrato no es una copia de la Etapa 1. No repitan los requisitos: refiéranse a ellos por su ID (RF-03, RNF-02...) y agreguen solo lo que falta para poder construir y probar. 

Un criterio de aceptación debe poder responderse con "pasa" o "no pasa". Ejemplo con el sistema de panadería de la Etapa 1:

❌ "El sistema debe descontar los insumos correctamente." (no es verificable)
✅ Given que la receta del pan de caja usa 500 g de harina y el inventario tiene 5,000 g, When se registra la venta de 2 panes de caja, Then el inventario de harina queda en 4,000 g y se genera un movimiento de inventario asociado a esa venta.
 

Modalidad de trabajo
Elaboración: en equipo (los mismos 6 equipos).
Entrega: individual. Cada integrante debe subir su propio archivo a Canvas, aunque el contenido haya sido elaborado en conjunto. Cada quien debe poder explicar y defender el contrato completo, no solo la parte que redactó.
No es necesario que el contenido varíe entre integrantes del mismo equipo; sí es necesario que cada uno lo suba por su cuenta.
 

Definición de la entrega
Un archivo Markdown (.md) opcionalmente basado en la plantilla 02-contrato-tecnico-template.md proporcionada al final de esta página, completamente llenado con la información de su proyecto. Es una sugerencia es cubrir, como mínimo, estas secciones (el punto 7, supuestos y preguntas abiertas, es opcional):

Contexto y alcance del MVP: un párrafo de contexto con enlace a la Etapa 1, y una tabla que indique qué RF entran al MVP. Todos los RF Must deben entrar.
Decisiones de arquitectura: mínimo 3, cada una con contexto, opciones consideradas, decisión, justificación y consecuencias. Al menos una debe explicar cómo la arquitectura facilitará el complemento móvil (Etapa 6).
Modelo de datos e interfaces: entidades principales con sus relaciones y al menos 5 contratos de interfaz (endpoints o pantallas principales) con entrada, salida y errores posibles.
Criterios de aceptación: en formato Given/When/Then.
Cada RF del MVP debe tener mínimo 3 criterios: camino feliz, caso de error y caso límite.
Al menos un criterio debe verificar la regla de negocio no trivial de su proyecto.
Al menos 2 criterios deben verificar la interacción entre módulos (que la acción en uno afecte al otro).
Requisitos no funcionales verificables: todos los RNF de la Etapa 1, con una métrica concreta y una forma de verificación.
Fuera de alcance técnico: qué decisiones o funcionalidades técnicas no se harán en el MVP.
Supuestos y preguntas abiertas.
Contexto para el asistente de IA: el bloque que pegarán al inicio de cada sesión con su agente (stack, estructura, convenciones y reglas). Esta sección es obligatoria.
Uso de IA en esta entrega: qué agente(s) usaron, para qué partes del contrato, y qué tuvieron que corregir manualmente. Esta sección es obligatoria. No basta con "usamos ChatGPT para todo".
 

Formato y entrega
Archivo en formato Markdown puro (.md), no Word ni PDF.
Nombre del archivo: 02-contrato-tecnico_[NombreDelProyecto]_[TuApellido].md (ejemplo: 02-contrato-tecnico_Restaurante_Martinez.md)
Sube el archivo directamente en la tarea correspondiente en Canvas (no como link a Google Docs ni a un repositorio privado).
Si su equipo ya tiene el documento en un repositorio de GitHub, pueden incluir el link como referencia adicional, pero el archivo .md subido a Canvas es el que se califica.
 

Fecha límite
Martes 6 de octubre, 11:59 p.m.
Entregas después de esa hora se consideran tardías y podrían (y deberían) recibir penalización.

 

Criterio de Calificación
El contrato técnico debe convertir los requisitos en criterios (checklist o Given/When/Then) con casos límite y errores. También deben establecerse decisiones de arquitectura, modelo de datos y contratos de interfaz

 

Notas para el equipo
Este documento es la base del MVP y de la tabla de trazabilidad de la Etapa 3: cada criterio que escriban será una fila que tendrán que demostrar como cumplida.
Antes de escribir los criterios, discutan en equipo cada RF del MVP. Si no logran explicar con claridad cuándo estaría "bien hecho", el requisito necesita ajustarse.
Si usan un agente de IA para redactar criterios, revisen que no sean genéricos ni que inventen funcionalidades que no están en su proyecto. Un contrato inflado por el modelo es peor que uno corto y preciso.
Cualquier duda sobre su proyecto particular, consúltenla antes de la fecha límite.
 

Plantilla
La siguiente plantilla es una guía para la entrega. No es obligatorio basarse en ella, pero contiene los elementos que pide la rúbrica.