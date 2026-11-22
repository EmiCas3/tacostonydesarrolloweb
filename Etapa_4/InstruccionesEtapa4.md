Objetivo de aprendizaje
Validar el MVP con cambios reales, corregirlo y mejorarlo a partir de esa retroalimentación, terminar todos los requisitos pendientes para considerar completo el producto final, y dejar la documentación final del sistema como una fuente única de verdad (SSOT): contrato y trazabilidad actualizados, arquitectura y changelog.

Modalidad de trabajo
Desarrollo: en equipo.
Entrega en Canvas: individual, como en las etapas anteriores. Cada integrante sube su archivo y debe poder explicar cualquier parte del sistema y del documento.
Qué tienen que hacer
1. Validar el MVP con usuarios.

Al final de este documento se encuentra la simulación de una corrección solicitada por un usuario. Hay una corrección por equipo. Deberán analizarla y tomarla como si fuera una corrección real de uno de sus usuarios. 
2. Consolidar hallazgos. Tabla con tipo (bug, usabilidad, requisito mal interpretado, requisito faltante) y severidad.

El cambio se documenta como: origen → requisito o criterio afectado → cambio en el contrato → cambio en el código → verificación. Si modifican un criterio de aceptación o una regla de negocio, actualicen el contrato y registren la nueva versión en su historial.

3. Completar en el desarrollo todos los requisitos que se habían dejado pendientes para considerar que el producto ya está completo.

4. Actualizar la trazabilidad. La misma tabla de la Etapa 3, ahora con el estado final de cada RF, incluyendo los RF nuevos que agregue cada cambio.

5. Documentación final (SSOT). Dentro del mismo archivo: descripción y diagrama de arquitectura, stack y versiones, cómo ejecutarlo localmente, decisiones de arquitectura vigentes (marcando cuáles cambiaron y por qué), changelog desde el MVP hasta la versión final, y pendientes conocidos.

Qué tienen que entregar en Canvas
Un archivo 04-validacion-entrega-final_[NombreDelProyecto]_[TuApellido].md, con estas secciones:

Encabezado: proyecto, equipo, tu nombre, URL del sistema final, link al repositorio, versión final del sistema y versión final del contrato técnico.
Validación con usuarios: resultados y evidencia.
Hallazgos consolidados.
Los cambios implementados (formato indicado arriba).
Trazabilidad final.
Documentación SSOT: arquitectura, ejecución local, decisiones vigentes, changelog, pendientes.
Uso de IA y reflexión: qué agente usaron, para qué, qué tuvieron que corregir a mano, y 3 a 5 líneas sobre qué hizo bien y mal la IA y qué harían distinto.
Se entrega un solo archivo .md (no Word ni PDF), y el contrato técnico actualizado debe estar en su repositorio.

Requisitos del sistema desplegado
Debe estar accesible en su URL durante la evaluación.
Credenciales de cuentas ficticias para que yo pueda evaluar. Nada de correos ni datos reales de personas.
Fechas
Entrega en Canvas: miércoles 25 de noviembre, 11:59 p.m.
Rúbrica de evaluación (100 pts)
Criterio	Puntos	Qué se evalúa
Hallazgos y priorización	10	Clasificados y priorizados con criterio, no una lista suelta
Cambios implementados y verificados	35	Los cambios funcionan en la demo, con origen y verificación documentados
Contrato y trazabilidad finales	20	Contrato versionado, trazabilidad completa y coherente con la demo
Documentación SSOT	20	Arquitectura, ejecución local, decisiones vigentes y changelog
Calidad y seguridad del sistema	10	Sin credenciales en el repo, datos ficticios, sistema accesible
Uso de IA y reflexión	10	Específico y honesto, incluyendo correcciones manuales
Notas para el equipo
Un hallazgo bien documentado que no alcanzaron a corregir vale más que una corrección que no se puede demostrar. Déjenlo en “pendientes conocidos”.
Los cambios del cliente se evalúan por la calidad con que los traducen a requisitos y criterios Given/When/Then, no solo porque “funcione”.
Si el agente de IA les propone algo que contradice el contrato, se corrige el código o se actualiza el contrato con una nueva versión; no se ignora.
Esta actividad sustituye a las antiguas Etapas 4 y 5. Cualquier duda sobre el alcance de su proyecto, consúltenla pronto.
Plantilla: 04-validacion-entrega-final-template.md

Equipo 4 (Tacos Tony) - Conteo físico con conciliación (corrección)
Por qué: el dueño dijo que el inventario digital no coincide con el físico y que hay pérdidas y posibles robos invisibles. Hoy el sistema solo registra ventas, entradas y mermas, pero no tiene cómo corregir el stock después de contar la bodega. Con el tiempo, el dato se desfasa y deja de ser confiable.

Qué pide el cliente: “Cada domingo cuento la bodega a mano. Quiero capturar lo que realmente hay y que el sistema me diga cuánto me falta o me sobra contra lo que él cree que tengo.”

Alcance sugerido:

Pantalla “Conteo físico”: lista de insumos, se captura la cantidad contada, y el sistema muestra la diferencia contra el stock del sistema.
Al confirmar, genera un movimiento nuevo de tipo AJUSTE (positivo o negativo) con usuario, fecha y motivo. No edita nada previo, así respeta la inmutabilidad del historial.
Reporte de diferencias del conteo, con las mayores faltantes arriba.
Criterio de aceptación de ejemplo:

Dado que el sistema tiene 5.000 kg de carne y el gerente cuenta 4.200 kg, cuando confirma el conteo, entonces se crea un AJUSTE de −0.800 kg, el stock queda en 4.200 y el reporte muestra la diferencia.
Detalles a vigilar: qué pasa si hay una venta mientras se cuenta (calcular la diferencia dentro de la transacción de confirmación) y si el Colaborador puede capturar el conteo con aprobación del Gerente (buen ejercicio de roles, opcional). Con IA son unas pocas horas de trabajo. Si adoptan el contrato v2.0, esto implica quitar de “fuera de alcance” los ajustes manuales, y debe quedar en el historial de versiones.