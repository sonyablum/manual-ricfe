# Hand-off — soniayanez.com

**Rama de trabajo:** `claude/fix-sonia-yanez-website-inbHm`
**Última verificación en vivo:** 6 nov 2026

---

## 1. Estado en vivo (qué está aplicado y qué falta)

| # | Cambio | Estado en vivo | Acción tuya |
|---|--------|----------------|-------------|
| 1 | Fix de la sección BIO "28 años…" (Home) | ✅ **Aplicado** | — |
| 2 | Schema Person con los 6 depósitos de Zenodo (incl. DOI 22050457) | ✅ **Aplicado** | (ver nota ⚠️) |
| 3 | Quitar banner de la Maratón (ya pasó) | ❌ **Pendiente** | Reemplazar mu-plugin |
| 4 | Contenido nuevo de la página /rrpp-6-0/ | ❌ **Pendiente** | Pegar en el post |

⚠️ **Nota sobre el schema:** la versión pegada en vivo tiene los 6 DOIs pero **no** incluye los 3 nodos de RRPP 6.0 (`DefinedTermSet`, `DefinedTerm`, `WebPage`). Mi archivo `person-schema-rrpp6-snippet22.html` trae ambas cosas. Si quieres la capa de "definición de RRPP 6.0" en el schema, pega mi versión completa en el snippet 22.

---

## 2. Archivos en el repositorio

| Archivo | Qué es | Dónde va en WordPress |
|---------|--------|------------------------|
| `maraton-banner-mu-plugin.php` (v4) | Sin banner (ya pasó) · conserva fix BIO + speakable RRPP 6.0 | `wp-content/mu-plugins/` — **reemplaza** el archivo actual |
| `rrpp-6-0-contenido.html` | Cuerpo completo de la página RRPP 6.0: gráfico de línea de tiempo + 6 depósitos Zenodo + FAQ schema + `.rrpp-definicion` | Post /rrpp-6-0/ → bloque "HTML personalizado" |
| `person-schema-rrpp6-snippet22.html` | Schema Person: RRPP 6.0 (3 nodos) + 6 DOIs | Snippet id=22 (plugin de snippets) |
| `css-wordpress-home.css` | CSS correctivo del Home (referencia; el fix BIO ya está activo vía mu-plugin) | Apariencia → Personalizar → CSS adicional |
| `aca-baseline-queries.md` | 40 consultas ACA baseline | Documento de trabajo (no va a la web) |

---

## 3. Pasos pendientes (en orden)

### Paso A — Quitar el banner de la Maratón
1. Hosting → **cPanel → Administrador de archivos** (o FTP).
2. Ve a `wp-content/mu-plugins/`.
3. Sube `maraton-banner-mu-plugin.php` **reemplazando** el que ya está.
4. Recarga soniayanez.com con **Ctrl+Shift+R**. El banner desaparece y se conservan el fix de la BIO y el speakable.

### Paso B — Publicar el contenido nuevo de /rrpp-6-0/
1. WordPress → edita el post **"RRPP 6.0"**.
2. Añade un bloque **"HTML personalizado"** y pega todo el contenido de `rrpp-6-0-contenido.html` (reemplaza el cuerpo viejo "3 claves en su evolución").
3. En tu plugin SEO (Yoast/Rank Math):
   - **Title:** `RRPP 6.0: IA conversacional y relaciones públicas`
   - **Meta:** `RRPP 6.0 explica cómo la IA conversacional transforma la gestión de reputación, autoridad y relaciones públicas desde 2025.`
4. Actualiza el post y recarga con Ctrl+Shift+R.

### Paso C (opcional) — Schema con la capa RRPP 6.0
Si quieres los 3 nodos de definición en el schema: pega `person-schema-rrpp6-snippet22.html` en el **snippet id=22** (reemplaza el contenido actual). Valida en [Google Rich Results Test](https://search.google.com/test/rich-results).

---

## 4. Verificación final (cuando termines A y B)
- Home: el banner de la Maratón ya no aparece; la bio "28 años…" se ve centrada sin hueco en blanco.
- /rrpp-6-0/: se ve el gráfico de línea de tiempo, los seis depósitos y el nuevo título en la pestaña del navegador.
- Valida /rrpp-6-0/ en Google Rich Results Test (debe detectar FAQPage y, si pegaste el schema completo, DefinedTerm).

---

## 5. Pendientes de seguridad / limpieza
- **Revocar la contraseña de aplicación** "Claude" que pegaste en el chat: wp-admin → Usuarios → Perfil → Contraseñas de aplicación → Revocar.
- Si quedaron scripts PHP de diagnóstico en el servidor (diagnostico.php, fix-mediakit.php), bórralos.
- El DOI 22050457 figura en Zenodo con el autor "Yánez, Sonia" (pendiente de corregir a "Yánez Blum, S." en Zenodo). No afecta la web.

---

## 6. Pull Request
Para fusionar la rama a `main`:
`github.com/sonyablum/manual-ricfe/compare/main...claude/fix-sonia-yanez-website-inbHm` → "Create pull request".
(La API de GitHub no está habilitada desde esta sesión, por eso se crea desde el navegador.)
