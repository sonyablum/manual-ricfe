<?php
/**
 * Plugin Name: SY — Fix BIO Home + Speakable RRPP 6.0
 * Description: Corrige el espacio en blanco de la sección BIO "28 años..." del
 *              Home de soniayanez.com y, en /rrpp-6-0/, marca el párrafo de la
 *              definición con la clase .rrpp-definicion para que el speakable
 *              del schema (snippet 22) funcione.
 *              (El anuncio de la Maratón se retiró: el evento ya pasó.)
 * Version: 4.0
 *
 * INSTALACIÓN: sube este archivo a wp-content/mu-plugins/ (crea la carpeta si
 * no existe). Se activa solo. Recarga con Ctrl+Shift+R.
 * PARA QUITARLO: borra este archivo de wp-content/mu-plugins/.
 */

if (!defined('ABSPATH')) { exit; }

function sy_is_home() {
    return (is_front_page() || is_home());
}

/* 1) Fix de la sección BIO "28 años construyendo reputación" (solo Home). */
add_action('wp_head', function () {
    if (!sy_is_home()) { return; }
    ?>
<style id="sy-fix-bio">
#sonia .sy-bio-grid{display:block !important;max-width:720px !important;margin:0 auto !important}
#sonia .sy-bio-h{font-size:clamp(1.9rem,3.5vw,2.6rem) !important;line-height:1.2 !important}
#sonia .sy-tray-strip{grid-template-columns:repeat(3,1fr) !important}
@media(max-width:560px){#sonia .sy-tray-strip{grid-template-columns:1fr !important}}
</style>
    <?php
});

/* ────────────────────────────────────────────────────────────────────────
 * 2) SPEAKABLE RRPP 6.0 — Solo en /rrpp-6-0/
 *    El schema Person (snippet 22) declara speakable con cssSelector
 *    ".rrpp-definicion". Si el contenido del post ya trae esa clase (porque se
 *    pegó el bloque nuevo), aquí no se hace nada. Si no, se añade como respaldo.
 * ──────────────────────────────────────────────────────────────────────── */
function sy_is_rrpp6() {
    if (function_exists('is_singular') && is_singular()) {
        $slug = basename(untrailingslashit((string) get_permalink()));
        if ($slug === 'rrpp-6-0') { return true; }
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? untrailingslashit($_SERVER['REQUEST_URI']) : '';
    return (substr($uri, -9) === '/rrpp-6-0');
}

add_action('wp_footer', function () {
    if (!sy_is_rrpp6()) { return; }
    ?>
<script>
(function(){
  if(document.querySelector('.rrpp-definicion'))return; // ya está en el contenido
  var c=document.querySelector('.entry-content, article .entry-content, .post-content');
  if(!c)return;
  var ps=c.querySelectorAll('p'), best=null;
  for(var i=0;i<ps.length;i++){
    var t=(ps[i].textContent||'').trim();
    if(/^RRPP\s*6\.0\s+(se define|es)/i.test(t)){ best=ps[i]; break; }
    if(!best && t.length>60) best=ps[i];
  }
  if(best) best.classList.add('rrpp-definicion');
}());
</script>
    <?php
});
