Hola. Vas a ayudarme a APLICAR directamente en mi WordPress unas correcciones
ya preparadas para mi sitio soniayanez.com. Trabaja conmigo paso a paso.

## CONTEXTO
- Sitio: soniayanez.com (WordPress en Hostinger).
- Repositorio con los archivos ya listos: github.com/sonyablum/manual-ricfe
  rama: claude/fix-sonia-yanez-website-inbHm
- Soy Sonia Yánez Blum (soniayanezblum@gmail.com), investigadora en reputación
  algorítmica y relaciones públicas.

## LO QUE HAY QUE APLICAR (3 cosas; 2 pendientes, 1 opcional)
1. PÁGINA RRPP 6.0 (lo más importante): reemplazar el contenido del post
   /rrpp-6-0/ (post ID 10751, título viejo "RRPP 6.0, 3 claves en su evolución")
   por el cuerpo nuevo del archivo `rrpp-6-0-contenido.html` del repo.
   Incluye: gráfico de línea de tiempo, los 6 depósitos de Zenodo, FAQ schema,
   y el párrafo con clase .rrpp-definicion (speakable).
   - Title SEO:  RRPP 6.0: IA conversacional y relaciones públicas
   - Meta description:  RRPP 6.0 explica cómo la IA conversacional transforma la
     gestión de reputación, autoridad y relaciones públicas desde 2025.
2. QUITAR el banner de la Maratón del Home (el evento ya pasó): reemplazar el
   archivo `wp-content/mu-plugins/maraton-banner-mu-plugin.php` por la versión
   del repo (v4, ya sin banner; conserva el fix de la BIO y el speakable).
3. OPCIONAL — schema: reemplazar el contenido del snippet id=22 (plugin de
   snippets) por `person-schema-rrpp6-snippet22.html` del repo (schema Person
   con RRPP 6.0 + los 6 DOIs de Zenodo).

## CÓMO ENTRAR A MI WORDPRESS (elige la vía que funcione)
- Para el PUNTO 1 (contenido del post): usa la REST API de WordPress con una
  Application Password. Haz un PUT a
  https://soniayanez.com/wp-json/wp/v2/posts/10751 con el campo "content".
  Yo te daré mi usuario y una Application Password (wp-admin → Usuarios →
  Perfil → Contraseñas de aplicación → crear una nueva). Pídemela cuando la
  necesites; no la pongas en ningún log.
- Para el PUNTO 2 (mu-plugin) y PUNTO 3 (snippet): requieren acceso al
  servidor de archivos (SFTP de Hostinger) o al panel. Si no puedes por API,
  guíame tú clic a clic y verifica en vivo después de cada paso.
- Si el entorno de seguridad bloquea el uso de la credencial, dímelo con
  honestidad y pásame a la vía manual (yo pego los archivos y tú verificas).

## REGLAS IMPORTANTES (no cambiar)
- Los seis depósitos de Zenodo son: 18802347, 19035939, 19388591, 20410739,
  20430991, 22050457. Úsalos tal cual (DOI, fechas y tipos). En citas
  académicas: sin ™ ni ®; no menciones "Journal Version" ni v10.1.
- Sistema de diseño de Sonia Yánez Blum (respetar en todo CSS):
  fuentes Raleway (display), Montserrat (UI), Open Sans (body);
  colores #5A189A (purple), #4BE4FF (cyan), #B28BE4 (lavender);
  superficies #FFFFFF / #0A0A0F; espaciado base 4px escala ×2; 60–70% de
  espacio negativo.
- No enlazar a Amazon ni a sistemaca.com. Contacto: hola@soniayanez.com.

## PASOS
1. Clona o lee los archivos de la rama claude/fix-sonia-yanez-website-inbHm.
2. Empieza por el PUNTO 1 (contenido del post vía REST API) — pídeme la
   Application Password.
3. Verifica en vivo (descarga https://soniayanez.com/rrpp-6-0/ y confirma que
   aparece el gráfico, los 6 depósitos y el título nuevo).
4. Sigue con el banner (PUNTO 2) y el schema (PUNTO 3).
5. Al terminar: recuérdame revocar la Application Password, y crear/actualizar
   el Pull Request de la rama.

Confírmame qué necesitas de mí para empezar por el PUNTO 1.
