<?php
/**
 * Plugin Name: SY — Anuncio Maratón RRPP 2026 + Fix BIO Home
 * Description: Muestra en el Home de soniayanez.com el AFICHE OFICIAL de la
 *              Maratón de las Relaciones Públicas 2026 dentro de una tarjeta
 *              especial centrada (enlace a maratondelasrrpp.org) y corrige el
 *              espacio en blanco de la sección BIO "28 años...".
 * Version: 3.0
 *
 * ════════════════════════════════════════════════════════════════════════
 *  PASO 1 — SUBE TU AFICHE A WORDPRESS
 *    a) Entra a wp-admin → Medios → Añadir nuevo.
 *    b) Arrastra la imagen del afiche oficial (el vertical) y súbela.
 *    c) Haz clic en la imagen y copia la "URL del archivo".
 *
 *  PASO 2 — PEGA ESA URL AQUÍ ABAJO
 *    Reemplaza el texto entre comillas en la línea SY_MARATON_AFICHE_URL
 *    por la URL que copiaste. (Eso es lo único que tienes que editar.)
 *
 *  PASO 3 — SUBE ESTE ARCHIVO
 *    a wp-content/mu-plugins/  (crea la carpeta si no existe). Se activa solo.
 *    Recarga soniayanez.com con Ctrl+Shift+R.
 *
 *  PARA QUITARLO: borra este archivo de wp-content/mu-plugins/.
 * ════════════════════════════════════════════════════════════════════════
 */

if (!defined('ABSPATH')) { exit; }

/* 👇👇👇  PEGA AQUÍ LA URL DE TU AFICHE (la de Medios de WordPress)  👇👇👇 */
define('SY_MARATON_AFICHE_URL', 'https://soniayanez.com/wp-content/uploads/2026/09/afiche-maraton-rrpp-2026.jpg');
/* 👆👆👆  ————————————————————————————————————————————————————————  👆👆👆 */

define('SY_MARATON_REGISTRO_URL', 'https://maratondelasrrpp.org/#inscripcion');

function sy_maraton_is_home() {
    return (is_front_page() || is_home());
}

/* 1) CSS: fix de la sección BIO + estilos de la tarjeta del afiche. */
add_action('wp_head', function () {
    if (!sy_maraton_is_home()) { return; }
    ?>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&display=swap" rel="stylesheet">
<style id="sy-maraton-2026">
/* --- Fix sección BIO "28 años construyendo reputación" --- */
#sonia .sy-bio-grid{display:block !important;max-width:720px !important;margin:0 auto !important}
#sonia .sy-bio-h{font-size:clamp(1.9rem,3.5vw,2.6rem) !important;line-height:1.2 !important}
#sonia .sy-tray-strip{grid-template-columns:repeat(3,1fr) !important}
@media(max-width:560px){#sonia .sy-tray-strip{grid-template-columns:1fr !important}}

/* --- Sección contenedora (da aire alrededor de la tarjeta) --- */
#maraton-rrpp{background:#faf8ff;padding:80px 20px;display:flex;justify-content:center}
/* --- La TARJETA especial (compacta, centrada) con el afiche oficial --- */
#maraton-rrpp .mar-card{position:relative;background:#fff;max-width:520px;width:100%;border-radius:20px;
  overflow:hidden;box-shadow:0 30px 70px rgba(17,18,22,.16);border:1px solid #efeaf7}
#maraton-rrpp .mar-card::before{content:'';position:absolute;top:0;left:0;right:0;height:6px;z-index:2;
  background:linear-gradient(90deg,#111216 0 55%,#E90B18 55% 80%,#C6EB06 80% 100%)}
#maraton-rrpp .mar-pill{position:absolute;top:20px;left:20px;z-index:2;
  background:#E90B18;color:#fff;font:800 .66rem/1 'Montserrat',system-ui,sans-serif;
  letter-spacing:.16em;text-transform:uppercase;padding:8px 14px;border-radius:100px;
  box-shadow:0 6px 18px rgba(233,11,24,.3)}
#maraton-rrpp .mar-afiche{display:block;width:100%;height:auto}
#maraton-rrpp .mar-foot{padding:24px 28px 30px;text-align:center}
#maraton-rrpp .mar-cta{display:inline-flex;align-items:center;gap:8px;background:#E90B18;color:#fff;
  font:800 .95rem/1 'Montserrat',system-ui,sans-serif;text-transform:uppercase;letter-spacing:.04em;
  padding:16px 38px;border-radius:100px;text-decoration:none;transition:transform .2s,box-shadow .2s}
#maraton-rrpp .mar-cta:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(233,11,24,.32);color:#fff}
#maraton-rrpp .mar-url{display:block;font:600 .8rem/1 'Montserrat',system-ui,sans-serif;color:#111216;text-decoration:none;margin-top:14px}
#maraton-rrpp .mar-url:hover{color:#E90B18}
@media(max-width:560px){#maraton-rrpp{padding:52px 16px}#maraton-rrpp .mar-foot{padding:20px 18px 26px}}
</style>
    <?php
});

/* 2) HTML de la tarjeta: se imprime al final y un script la coloca tras el hero. */
add_action('wp_footer', function () {
    if (!sy_maraton_is_home()) { return; }
    $afiche   = esc_url(SY_MARATON_AFICHE_URL);
    $registro = esc_url(SY_MARATON_REGISTRO_URL);
    ?>
<section id="maraton-rrpp">
  <div class="mar-card">
    <span class="mar-pill">Muy pronto · Regístrate</span>
    <a href="<?php echo $registro; ?>" target="_blank" rel="noopener" aria-label="Maratón de las Relaciones Públicas 2026 — Regístrate">
      <img class="mar-afiche" src="<?php echo $afiche; ?>" alt="Maratón de las Relaciones Públicas 2026 — 5.ª edición · 26 de septiembre · 15 horas en vivo · gratis online en español" loading="lazy">
    </a>
    <div class="mar-foot">
      <a href="<?php echo $registro; ?>" target="_blank" rel="noopener" class="mar-cta">Regístrate gratis →</a>
      <a href="https://maratondelasrrpp.org" target="_blank" rel="noopener" class="mar-url">maratondelasrrpp.org</a>
    </div>
  </div>
</section>
<script>
(function(){
  var s=document.getElementById('maraton-rrpp');
  if(!s)return;
  var ref=document.getElementById('investigacion')||document.getElementById('servicios');
  if(ref&&ref.parentNode){ref.parentNode.insertBefore(s,ref);}
}());
</script>
    <?php
});
