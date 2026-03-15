<?php
/**
 * Corrige la página Media Kit y desactiva el plugin AITOPIA
 * Sube a public_html/, visita soniayanez.com/fix-mediakit.php
 * Luego ELIMINA este archivo
 */
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Inicia sesión como admin primero. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Iniciar sesión</a>');
}

$results = [];

// ============================================================
// 1. DESACTIVAR PLUGIN AITOPIA (causa el texto basura)
// ============================================================
$active_plugins = get_option('active_plugins', []);
$aitopia_found = false;
$new_plugins = [];

foreach ($active_plugins as $plugin) {
    if (stripos($plugin, 'aitopia') !== false || stripos($plugin, 'ai-chatbot') !== false || stripos($plugin, 'aichat') !== false) {
        $aitopia_found = true;
        $results[] = 'Plugin desactivado: ' . $plugin;
    } else {
        $new_plugins[] = $plugin;
    }
}

if ($aitopia_found) {
    update_option('active_plugins', $new_plugins);
} else {
    // Buscar en la carpeta de plugins por nombre
    $plugins_dir = WP_PLUGIN_DIR;
    $found_dirs = [];
    if (is_dir($plugins_dir)) {
        foreach (scandir($plugins_dir) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            $dir_lower = strtolower($dir);
            if (strpos($dir_lower, 'aitopia') !== false || strpos($dir_lower, 'ai-chat') !== false || strpos($dir_lower, 'aichatbot') !== false) {
                $found_dirs[] = $dir;
            }
        }
    }
    // Buscar también por contenido del plugin
    foreach ($active_plugins as $plugin) {
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        if (file_exists($plugin_file)) {
            $content = file_get_contents($plugin_file, false, null, 0, 5000);
            if (stripos($content, 'aitopia') !== false || stripos($content, 'AITOPIA') !== false) {
                $aitopia_found = true;
                $new_plugins = array_diff($active_plugins, [$plugin]);
                update_option('active_plugins', array_values($new_plugins));
                $results[] = 'Plugin AITOPIA desactivado: ' . $plugin;
            }
        }
    }
    if (!$aitopia_found && !empty($found_dirs)) {
        $results[] = 'Carpeta AITOPIA encontrada pero plugin ya inactivo: ' . implode(', ', $found_dirs);
    } elseif (!$aitopia_found) {
        // Listar todos los plugins activos para diagnóstico
        $results[] = 'No se encontró AITOPIA por nombre. Plugins activos: ' . implode(', ', $active_plugins);
    }
}

// ============================================================
// 2. LIMPIAR CONTENIDO DE MEDIA KIT
// ============================================================
$page = get_page_by_path('media-kit');
if ($page) {
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
    $results[] = 'Contenido de /media-kit/ limpiado';
} else {
    $results[] = 'Página /media-kit/ no encontrada';
}

// ============================================================
// 3. LIMPIAR CACHÉ
// ============================================================
if (function_exists('wp_cache_flush')) wp_cache_flush();
if (function_exists('rocket_clean_domain')) rocket_clean_domain();
if (function_exists('w3tc_flush_all')) w3tc_flush_all();
// LiteSpeed Cache
if (class_exists('LiteSpeed_Cache_API')) LiteSpeed_Cache_API::purge_all();
// Hostinger cache
if (function_exists('hstngr_cache_purge')) hstngr_cache_purge();
$results[] = 'Caché limpiada';

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Corregido</title>
<style>body{font-family:sans-serif;background:#0a0a0a;color:#e0e0e0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:2rem;}
.box{background:#0a2a0a;border:1px solid #00C853;border-radius:12px;padding:2rem;text-align:center;max-width:600px;}
h1{color:#00C853;font-size:1.3rem;margin-bottom:1rem;}
a{color:#4be4ff;}
.log{background:#111;border-radius:8px;padding:1rem;margin:1rem 0;text-align:left;font-size:.85rem;font-family:monospace;}
.log li{padding:.3rem 0;border-bottom:1px solid #222;list-style:none;}
</style></head><body>
<div class="box">
<h1>&#10004; Correcciones aplicadas</h1>
<ul class="log">';
foreach ($results as $r) {
    echo '<li>' . htmlspecialchars($r) . '</li>';
}
echo '</ul>
<p><a href="https://soniayanez.com/media-kit/">Ver Media Kit</a></p>
<p style="margin-top:.5rem;"><a href="https://soniayanez.com/">Ver Home</a></p>
<p style="color:#D4AF37;margin-top:1rem;font-size:.85rem;">IMPORTANTE: Elimina este archivo (fix-mediakit.php) del servidor.</p>
</div></body></html>';
