<?php
/**
 * Corrige el contenido de la página Media Kit
 * Sube a public_html/, visita soniayanez.com/fix-mediakit.php
 * Luego ELIMINA este archivo
 */
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Inicia sesión como admin primero. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Iniciar sesión</a>');
}

$page = get_page_by_path('media-kit');
if (!$page) {
    die('No se encontró la página /media-kit/');
}

$clean_content = '<p>Esta página está en construcción. Mientras tanto, puedes contactarnos para recursos de prensa.</p>

<h2>Recursos disponibles</h2>

<ul>
<li>Biografía oficial (3 versiones: corta, media, extendida)</li>
<li>Fotos profesionales en alta resolución</li>
<li>Logo de Blum Digital PR</li>
<li>Información sobre frameworks: RICFE®, ACA™, RRPP 6.0</li>
<li>Datos del Manual Estratégico de IA &amp; Prompts para Comunicadores</li>
</ul>

<p><strong>Contacto para prensa:</strong> <a href="mailto:hola@soniayanez.com">hola@soniayanez.com</a></p>';

wp_update_post([
    'ID' => $page->ID,
    'post_content' => $clean_content,
]);

// Limpiar caché
if (function_exists('wp_cache_flush')) wp_cache_flush();
if (function_exists('rocket_clean_post')) rocket_clean_post($page->ID);

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Corregido</title>
<style>body{font-family:sans-serif;background:#0a0a0a;color:#e0e0e0;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
.box{background:#0a2a0a;border:1px solid #00C853;border-radius:12px;padding:2rem;text-align:center;max-width:500px;}
h1{color:#00C853;font-size:1.3rem;}a{color:#4be4ff;}</style></head><body>
<div class="box"><h1>&#10004; Página Media Kit corregida</h1>
<p>Se eliminaron los caracteres y código basura.</p>
<p><a href="https://soniayanez.com/media-kit/">Ver página</a></p>
<p style="color:#D4AF37;margin-top:1rem;font-size:.85rem;">IMPORTANTE: Elimina este archivo (fix-mediakit.php) del servidor.</p>
</div></body></html>';
