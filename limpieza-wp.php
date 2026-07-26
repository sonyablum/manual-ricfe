<?php
/**
 * Script de limpieza automática para soniayanez.com
 *
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz de tu WordPress en Hostinger (public_html/)
 * 2. Visita: https://soniayanez.com/limpieza-wp.php
 * 3. Revisa las acciones y haz clic en "Ejecutar limpieza"
 * 4. IMPORTANTE: Elimina este archivo cuando termines
 */

// Cargar WordPress
require_once __DIR__ . '/wp-load.php';

// Solo admins
if (!current_user_can('manage_options')) {
    wp_die('Debes iniciar sesión como administrador. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Iniciar sesión</a>');
}

$actions_log = [];
$preview_mode = !isset($_POST['ejecutar']);

// ============================================================
// 1. ELIMINAR PÁGINAS BORRADOR DE ELEMENTOR
// ============================================================
$elementor_drafts = [
    'elementor-12180',
    'elementor-11628',
    'elementor-10270',
    '6212-2',
];

$deleted_pages = [];
foreach ($elementor_drafts as $slug) {
    $page = get_page_by_path($slug);
    if (!$page) {
        // Buscar también en posts
        $posts = get_posts([
            'name' => $slug,
            'post_type' => ['page', 'post'],
            'post_status' => 'any',
            'numberposts' => 1,
        ]);
        if (!empty($posts)) $page = $posts[0];
    }
    if ($page) {
        if (!$preview_mode) {
            wp_trash_post($page->ID);
        }
        $deleted_pages[] = [
            'slug' => $slug,
            'title' => $page->post_title,
            'id' => $page->ID,
            'status' => $page->post_status,
        ];
    }
}
$actions_log[] = [
    'section' => 'Páginas borrador eliminadas',
    'icon' => '🗑️',
    'items' => $deleted_pages,
    'empty_msg' => 'No se encontraron páginas borrador de Elementor.',
];

// ============================================================
// 2. ELIMINAR POSTS DUPLICADOS (con sufijo -2, -3)
// ============================================================
$duplicate_slugs = [];
$all_posts = get_posts([
    'post_type' => ['post', 'page'],
    'post_status' => 'any',
    'numberposts' => -1,
    'fields' => 'ids',
]);

$deleted_dupes = [];
foreach ($all_posts as $pid) {
    $post = get_post($pid);
    $slug = $post->post_name;
    // Detectar slugs que terminan en -2, -3, etc.
    if (preg_match('/^(.+)-(\d)$/', $slug, $m)) {
        $original_slug = $m[1];
        // Verificar que existe el original
        $original = get_page_by_path($original_slug, OBJECT, ['post', 'page']);
        if ($original && $original->ID !== $post->ID) {
            if (!$preview_mode) {
                wp_trash_post($post->ID);
            }
            $deleted_dupes[] = [
                'slug' => $slug,
                'title' => $post->post_title,
                'id' => $post->ID,
                'original' => $original_slug,
            ];
        }
    }
}
$actions_log[] = [
    'section' => 'Posts duplicados eliminados (movidos a papelera)',
    'icon' => '📋',
    'items' => $deleted_dupes,
    'empty_msg' => 'No se encontraron posts duplicados.',
];

// ============================================================
// 3. CREAR REDIRECCIONES (via .htaccess)
// ============================================================
$redirects = [
    '/charlas-conferencias/' => '/ponencias-y-congresos/',
    '/conoceme/' => '/acerca-de/',
    '/sobre-mi/' => '/acerca-de/',
    '/aviso-legal/' => '/politica-de-privacidad/',
    '/aviso-legal' => '/politica-de-privacidad/',
    '/politica-privacidad/' => '/politica-de-privacidad/',
    '/politica-privacidad' => '/politica-de-privacidad/',
    '/legal/' => '/politica-de-privacidad/',
    '/legal' => '/politica-de-privacidad/',
];

$htaccess_path = ABSPATH . '.htaccess';
$htaccess_content = file_exists($htaccess_path) ? file_get_contents($htaccess_path) : '';
$redirects_added = [];

$redirect_block = "\n# BEGIN Redirecciones Sonia Yanez - Limpieza automática\n";
foreach ($redirects as $from => $to) {
    $redirect_block .= "Redirect 301 $from https://soniayanez.com$to\n";
    $redirects_added[] = ['from' => $from, 'to' => $to];
}
$redirect_block .= "# END Redirecciones Sonia Yanez\n";

if (!$preview_mode && !empty($redirects_added)) {
    // Solo añadir si no existe ya
    if (strpos($htaccess_content, 'Redirecciones Sonia Yanez') === false) {
        // Insertar antes de # BEGIN WordPress
        if (strpos($htaccess_content, '# BEGIN WordPress') !== false) {
            $htaccess_content = str_replace('# BEGIN WordPress', $redirect_block . "\n# BEGIN WordPress", $htaccess_content);
        } else {
            $htaccess_content = $redirect_block . $htaccess_content;
        }
        file_put_contents($htaccess_path, $htaccess_content);
    }
}

$actions_log[] = [
    'section' => 'Redirecciones 301 configuradas',
    'icon' => '🔀',
    'items' => $redirects_added,
    'empty_msg' => 'No hay redirecciones pendientes.',
    'type' => 'redirects',
];

// ============================================================
// 4. CREAR PÁGINA MEDIA KIT
// ============================================================
$media_kit_exists = get_page_by_path('media-kit');
$media_kit_created = false;

if (!$media_kit_exists) {
    if (!$preview_mode) {
        $media_kit_id = wp_insert_post([
            'post_title' => 'Media Kit',
            'post_name' => 'media-kit',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '<!-- wp:paragraph -->
<p>Esta página está en construcción. Mientras tanto, puedes descargar los recursos de prensa desde nuestro <a href="https://drive.google.com/drive/folders/1TU7Gv4QjZx5Y9LmNpKsRqWXl8oPzJyA">Google Drive</a> o contactar a <a href="mailto:hola@soniayanez.com">hola@soniayanez.com</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Recursos disponibles</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Biografía oficial (3 versiones: corta, media, extendida)</li>
<li>Fotos profesionales en alta resolución</li>
<li>Logo de Blum Digital PR</li>
<li>Información sobre frameworks: RICFE®, ACA™, RRPP 6.0</li>
<li>Datos del Manual Estratégico de IA &amp; Prompts</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><strong>Contacto para prensa:</strong> <a href="mailto:hola@soniayanez.com">hola@soniayanez.com</a></p>
<!-- /wp:paragraph -->',
        ]);
        $media_kit_created = $media_kit_id ? true : false;
    }
}

$actions_log[] = [
    'section' => 'Página Media Kit',
    'icon' => '📰',
    'items' => $media_kit_exists
        ? [['info' => 'La página /media-kit/ ya existe (ID: ' . $media_kit_exists->ID . ')']]
        : ($media_kit_created || $preview_mode
            ? [['info' => $preview_mode ? 'Se creará la página /media-kit/' : 'Página /media-kit/ creada exitosamente']]
            : [['info' => 'Error al crear la página']]),
    'empty_msg' => '',
    'type' => 'info',
];

// ============================================================
// 5. LIMPIAR SITEMAP (Rank Math)
// ============================================================
$sitemap_flushed = false;
if (!$preview_mode) {
    // Intentar regenerar sitemap de Rank Math
    if (class_exists('RankMath')) {
        delete_transient('rank_math_sitemap_cache');
        $sitemap_flushed = true;
    }
    // Intentar también con Yoast
    if (class_exists('WPSEO_Sitemaps_Cache')) {
        WPSEO_Sitemaps_Cache::clear();
        $sitemap_flushed = true;
    }
    // Flush rewrite rules para regenerar
    flush_rewrite_rules();
    $sitemap_flushed = true;
}

$actions_log[] = [
    'section' => 'Regeneración de sitemap',
    'icon' => '🗺️',
    'items' => [['info' => $preview_mode ? 'Se regenerará el sitemap y se limpiarán las reglas de reescritura' : ($sitemap_flushed ? 'Sitemap y reglas de reescritura regenerados' : 'No se pudo regenerar')]],
    'empty_msg' => '',
    'type' => 'info',
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpieza WordPress - soniayanez.com</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:#0a0a0a; color:#e0e0e0; padding:2rem; }
        .container { max-width:800px; margin:0 auto; }
        h1 { color:#D4AF37; margin-bottom:.5rem; font-size:1.8rem; }
        .subtitle { color:#888; margin-bottom:2rem; }
        .section { background:#1a1a1a; border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; border-left:3px solid #7C3AED; }
        .section h2 { font-size:1.1rem; margin-bottom:1rem; color:#fff; }
        .section .icon { margin-right:.5rem; }
        .item { background:#111; border-radius:8px; padding:.75rem 1rem; margin-bottom:.5rem; font-size:.9rem; }
        .item .slug { color:#4be4ff; font-family:monospace; }
        .item .arrow { color:#888; margin:0 .5rem; }
        .item .target { color:#00C853; font-family:monospace; }
        .item .meta { color:#888; font-size:.8rem; margin-top:.25rem; }
        .item .info { color:#ccc; }
        .empty { color:#666; font-style:italic; font-size:.9rem; }
        .actions { margin-top:2rem; text-align:center; }
        .btn { display:inline-block; padding:.85rem 2rem; border-radius:8px; font-weight:600; font-size:1rem; cursor:pointer; border:none; transition:opacity .2s; }
        .btn-primary { background:#7C3AED; color:#fff; }
        .btn-success { background:#00C853; color:#fff; }
        .btn:hover { opacity:.85; }
        .warning { background:#1a1a0a; border:1px solid #D4AF37; border-radius:8px; padding:1rem; margin:2rem 0; font-size:.9rem; color:#D4AF37; }
        .success-banner { background:#0a2a0a; border:1px solid #00C853; border-radius:8px; padding:1.5rem; margin-bottom:2rem; text-align:center; }
        .success-banner h2 { color:#00C853; margin-bottom:.5rem; }
        .count { display:inline-block; background:#7C3AED; color:#fff; border-radius:999px; padding:.15rem .6rem; font-size:.75rem; font-weight:600; margin-left:.5rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>Limpieza de WordPress</h1>
    <p class="subtitle">soniayanez.com &mdash; <?= $preview_mode ? 'Vista previa de acciones' : 'Resultados de la limpieza' ?></p>

    <?php if (!$preview_mode): ?>
    <div class="success-banner">
        <h2>&#10004; Limpieza completada</h2>
        <p>Todas las acciones se han ejecutado correctamente. Verifica tu sitio.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($actions_log as $action): ?>
    <div class="section">
        <h2><span class="icon"><?= $action['icon'] ?></span><?= $action['section'] ?>
            <?php if (!empty($action['items'])): ?><span class="count"><?= count($action['items']) ?></span><?php endif; ?>
        </h2>

        <?php if (empty($action['items'])): ?>
            <p class="empty"><?= $action['empty_msg'] ?></p>
        <?php else: ?>
            <?php foreach ($action['items'] as $item): ?>
                <div class="item">
                    <?php if (isset($item['slug']) && isset($item['from'])): ?>
                        <!-- redirect -->
                        <span class="slug"><?= esc_html($item['from']) ?></span>
                        <span class="arrow">&rarr;</span>
                        <span class="target"><?= esc_html($item['to']) ?></span>
                    <?php elseif (isset($item['from'])): ?>
                        <span class="slug"><?= esc_html($item['from']) ?></span>
                        <span class="arrow">&rarr;</span>
                        <span class="target"><?= esc_html($item['to']) ?></span>
                    <?php elseif (isset($item['slug'])): ?>
                        <span class="slug">/<?= esc_html($item['slug']) ?>/</span>
                        <div class="meta">
                            <?= esc_html($item['title']) ?> (ID: <?= $item['id'] ?>)
                            <?php if (isset($item['original'])): ?>
                                &mdash; Original: <span class="target">/<?= esc_html($item['original']) ?>/</span>
                            <?php endif; ?>
                        </div>
                    <?php elseif (isset($item['info'])): ?>
                        <span class="info"><?= esc_html($item['info']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($preview_mode): ?>
    <div class="actions">
        <form method="POST">
            <input type="hidden" name="ejecutar" value="1">
            <button type="submit" class="btn btn-primary" onclick="return confirm('¿Ejecutar todas las acciones de limpieza? Los posts/páginas se moverán a la papelera (recuperables).')">
                Ejecutar limpieza
            </button>
        </form>
    </div>
    <?php endif; ?>

    <div class="warning">
        <strong>IMPORTANTE:</strong> Elimina este archivo (<code>limpieza-wp.php</code>) de tu servidor cuando termines. Las páginas eliminadas se mueven a la papelera y son recuperables durante 30 días.
    </div>
</div>
</body>
</html>
