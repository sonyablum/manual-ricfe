<?php
/**
 * Plugin Name: SY — Anuncio Maratón RRPP 2026 + Fix BIO Home
 * Description: Inserta en el Home de soniayanez.com el anuncio de la Maratón de
 *              las Relaciones Públicas 2026 (enlace a maratondelasrrpp.org) y
 *              corrige el espacio en blanco de la sección BIO "28 años...".
 * Version: 1.0
 *
 * CÓMO INSTALARLO (una sola vez, sin tocar más código):
 *   1. Entra a tu hosting (cPanel → Administrador de archivos, o por FTP).
 *   2. Ve a la carpeta:  wp-content/mu-plugins/
 *      (si la carpeta "mu-plugins" no existe, créala con ese nombre exacto).
 *   3. Sube este archivo ahí. Se activa solo, sin hacer nada más.
 *   4. Recarga soniayanez.com con Ctrl+Shift+R.
 *
 * PARA QUITARLO: borra este archivo de wp-content/mu-plugins/.
 */

if (!defined('ABSPATH')) { exit; }

/* Solo en la portada / Home, no en el resto de páginas. */
function sy_maraton_is_home() {
    return (is_front_page() || is_home());
}

/* 1) CSS: fix de la sección BIO + estilos del anuncio de la Maratón. */
add_action('wp_head', function () {
    if (!sy_maraton_is_home()) { return; }
    ?>
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:opsz,wght@6..96,400;6..96,700&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
<style id="sy-maraton-2026">
/* --- Fix sección BIO "28 años construyendo reputación" --- */
#sonia .sy-bio-grid{display:block !important;max-width:720px !important;margin:0 auto !important}
#sonia .sy-bio-h{font-size:clamp(1.9rem,3.5vw,2.6rem) !important;line-height:1.2 !important}
#sonia .sy-tray-strip{grid-template-columns:repeat(3,1fr) !important}
@media(max-width:560px){#sonia .sy-tray-strip{grid-template-columns:1fr !important}}

/* --- Anuncio Maratón de las RRPP 2026 --- */
#maraton-rrpp{background:#ffffff;border-top:4px solid #E90B18;border-bottom:1px solid #e5e5e5;padding:88px 0}
#maraton-rrpp .sy-mar26-wrap{max-width:1100px;margin:0 auto;padding:0 clamp(24px,5vw,64px);display:grid;grid-template-columns:1fr 300px;gap:64px;align-items:center}
#maraton-rrpp .sy-mar26-eyebrow{display:inline-flex;align-items:center;gap:10px;font:800 .72rem/1 'Montserrat',system-ui,sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#111216;margin-bottom:22px}
#maraton-rrpp .sy-mar26-eyebrow::before{content:'';display:inline-block;width:10px;height:10px;border-radius:50%;background:#C6EB06;border:2px solid #111216}
#maraton-rrpp .sy-mar26-h{font-family:'Bodoni Moda',Georgia,serif;font-weight:700;font-size:clamp(2.2rem,4.6vw,3.6rem);line-height:1.05;color:#111216;margin:0 0 18px}
#maraton-rrpp .sy-mar26-h em{font-style:normal;color:#E90B18}
#maraton-rrpp .sy-mar26-pos{font:700 .95rem/1.5 'Montserrat',system-ui,sans-serif;text-transform:uppercase;letter-spacing:.04em;color:#111216;border-left:4px solid #E90B18;padding-left:14px;margin:0 0 20px}
#maraton-rrpp .sy-mar26-p{font:400 1rem/1.75 'Montserrat',system-ui,sans-serif;color:#3d3f45;margin:0 0 26px;max-width:560px}
#maraton-rrpp .sy-mar26-datos{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 30px;padding:0;list-style:none}
#maraton-rrpp .sy-mar26-datos li{font:600 .8rem/1 'Montserrat',system-ui,sans-serif;color:#111216;background:#fff;border:1.5px solid #111216;border-radius:100px;padding:9px 16px}
#maraton-rrpp .sy-mar26-ctas{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
#maraton-rrpp .sy-mar26-btn{display:inline-flex;align-items:center;gap:8px;font:700 .95rem/1 'Montserrat',system-ui,sans-serif;background:#E90B18;color:#fff;padding:16px 32px;border-radius:100px;text-decoration:none;transition:transform .2s,box-shadow .2s}
#maraton-rrpp .sy-mar26-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(233,11,24,.3);color:#fff}
#maraton-rrpp .sy-mar26-link{font:600 .9rem/1 'Montserrat',system-ui,sans-serif;color:#111216;text-decoration:underline;text-underline-offset:4px}
#maraton-rrpp .sy-mar26-link:hover{color:#E90B18}
#maraton-rrpp .sy-mar26-org{font:400 .74rem/1.6 'Montserrat',system-ui,sans-serif;color:#6b6e76;margin:26px 0 0}
#maraton-rrpp .sy-mar26-visual{display:flex;justify-content:center}
#maraton-rrpp .sy-mar26-circle{width:260px;height:260px;border-radius:50%;border:2px solid #111216;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;background:#fff}
#maraton-rrpp .sy-mar26-circle::after{content:'';position:absolute;top:14px;right:26px;width:16px;height:16px;border-radius:50%;background:#E90B18}
#maraton-rrpp .sy-mar26-circle-n{font-family:'Bodoni Moda',Georgia,serif;font-weight:700;font-size:5rem;line-height:1;color:#111216}
#maraton-rrpp .sy-mar26-circle-t{font:700 .68rem/1.5 'Montserrat',system-ui,sans-serif;letter-spacing:.1em;text-transform:uppercase;color:#111216;margin-top:8px;max-width:170px}
#maraton-rrpp .sy-mar26-circle-d{font:600 .72rem/1 'Montserrat',system-ui,sans-serif;color:#E90B18;margin-top:10px}
@media(max-width:880px){
  #maraton-rrpp{padding:60px 0}
  #maraton-rrpp .sy-mar26-wrap{grid-template-columns:1fr;gap:40px}
  #maraton-rrpp .sy-mar26-visual{order:-1}
  #maraton-rrpp .sy-mar26-circle{width:190px;height:190px}
  #maraton-rrpp .sy-mar26-circle-n{font-size:3.4rem}
}
</style>
    <?php
});

/* 2) HTML del anuncio: se imprime al final y un script lo coloca tras el hero. */
add_action('wp_footer', function () {
    if (!sy_maraton_is_home()) { return; }
    ?>
<section id="maraton-rrpp">
  <div class="sy-mar26-wrap">
    <div>
      <span class="sy-mar26-eyebrow">Maratón de las Relaciones Públicas 2026</span>
      <h2 class="sy-mar26-h">La máquina ya habló <em>de ti.</em></h2>
      <p class="sy-mar26-pos">Las Relaciones Públicas tienen que estar en esta conversación.</p>
      <p class="sy-mar26-p">Los sistemas de IA ya responden preguntas sobre tu organización con las fuentes que encuentran. El 26 de septiembre me uno a la quinta edición de la Maratón: 15 horas en directo para dar a las RR.&nbsp;PP. argumentos, método y autoridad frente a la IA.</p>
      <ul class="sy-mar26-datos">
        <li>Sábado 26 · septiembre · 2026</li>
        <li>15 horas en directo · 6:45–21:45 GMT-5</li>
        <li>Participación gratuita con registro</li>
      </ul>
      <div class="sy-mar26-ctas">
        <a href="https://maratondelasrrpp.org/#inscripcion" target="_blank" rel="noopener" class="sy-mar26-btn">Regístrate gratis →</a>
        <a href="https://maratondelasrrpp.org" target="_blank" rel="noopener" class="sy-mar26-link">maratondelasrrpp.org</a>
      </div>
      <p class="sy-mar26-org">Organiza Academia de Relaciones Públicas — ARP · Con el apoyo de CONFIARP · Con el aval de South Florida International College — SFIC</p>
    </div>
    <div class="sy-mar26-visual">
      <div class="sy-mar26-circle">
        <div class="sy-mar26-circle-n">5.ª</div>
        <div class="sy-mar26-circle-t">Edición · Día Interamericano de las Relaciones Públicas</div>
        <div class="sy-mar26-circle-d">26 · 09 · 2026</div>
      </div>
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
