<?php
/**
 * Busca y elimina AITOPIA de todo WordPress
 * Sube a public_html/, visita soniayanez.com/buscar-aitopia.php
 * Luego ELIMINA este archivo
 */
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Inicia sesión como admin. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Login</a>');
}

$findings = [];

// ============================================================
// 1. BUSCAR EN OPCIONES DE WORDPRESS (widgets, scripts custom)
// ============================================================
global $wpdb;

// Buscar "aitopia" en todas las opciones
$opts = $wpdb->get_results(
    "SELECT option_name, LEFT(option_value, 500) as val FROM {$wpdb->options} WHERE option_value LIKE '%aitopia%' OR option_value LIKE '%AITOPIA%' LIMIT 20"
);
foreach ($opts as $o) {
    $findings[] = ['where' => 'wp_options', 'key' => $o->option_name, 'preview' => substr($o->val, 0, 300)];
}

// Buscar "GPT-4o" en opciones
$opts2 = $wpdb->get_results(
    "SELECT option_name, LEFT(option_value, 500) as val FROM {$wpdb->options} WHERE option_value LIKE '%GPT-4o%' OR option_value LIKE '%gpt-4o%' LIMIT 10"
);
foreach ($opts2 as $o) {
    $findings[] = ['where' => 'wp_options (GPT-4o)', 'key' => $o->option_name, 'preview' => substr($o->val, 0, 300)];
}

// ============================================================
// 2. BUSCAR EN POSTMETA (Elementor custom code, etc.)
// ============================================================
$metas = $wpdb->get_results(
    "SELECT post_id, meta_key, LEFT(meta_value, 500) as val FROM {$wpdb->postmeta} WHERE meta_value LIKE '%aitopia%' OR meta_value LIKE '%AITOPIA%' LIMIT 20"
);
foreach ($metas as $m) {
    $findings[] = ['where' => 'postmeta (post ' . $m->post_id . ')', 'key' => $m->meta_key, 'preview' => substr($m->val, 0, 300)];
}

// ============================================================
// 3. BUSCAR EN ARCHIVOS DEL TEMA ACTIVO
// ============================================================
$theme_dir = get_stylesheet_directory();
$theme_files_with_aitopia = [];

function scan_dir_for_string($dir, $search, &$results, $depth = 0) {
    if ($depth > 3 || !is_dir($dir)) return;
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scan_dir_for_string($path, $search, $results, $depth + 1);
        } elseif (preg_match('/\.(php|js|html|json)$/', $file)) {
            $content = file_get_contents($path);
            if (stripos($content, $search) !== false) {
                // Find the line
                $lines = explode("\n", $content);
                foreach ($lines as $num => $line) {
                    if (stripos($line, $search) !== false) {
                        $results[] = [
                            'file' => str_replace(ABSPATH, '/', $path),
                            'line' => $num + 1,
                            'content' => trim(substr($line, 0, 200)),
                        ];
                    }
                }
            }
        }
    }
}

scan_dir_for_string($theme_dir, 'aitopia', $theme_files_with_aitopia);
scan_dir_for_string($theme_dir, 'AITOPIA', $theme_files_with_aitopia);

// ============================================================
// 4. BUSCAR EN PLUGINS (archivos)
// ============================================================
$plugin_files_with_aitopia = [];
scan_dir_for_string(WP_PLUGIN_DIR, 'aitopia', $plugin_files_with_aitopia);
scan_dir_for_string(WP_PLUGIN_DIR, 'AITOPIA', $plugin_files_with_aitopia);

// ============================================================
// 5. BUSCAR EN WIDGETS
// ============================================================
$widgets = get_option('sidebars_widgets', []);
$widget_findings = [];
foreach ($widgets as $area => $widget_list) {
    if (!is_array($widget_list)) continue;
    foreach ($widget_list as $widget_id) {
        // Check custom HTML widgets
        if (strpos($widget_id, 'custom_html') !== false) {
            $num = preg_replace('/[^0-9]/', '', $widget_id);
            $html_widgets = get_option('widget_custom_html', []);
            if (isset($html_widgets[$num]) && isset($html_widgets[$num]['content'])) {
                if (stripos($html_widgets[$num]['content'], 'aitopia') !== false) {
                    $widget_findings[] = ['area' => $area, 'widget' => $widget_id, 'preview' => substr($html_widgets[$num]['content'], 0, 300)];
                }
            }
        }
        // Check text widgets
        if (strpos($widget_id, 'text-') !== false) {
            $num = preg_replace('/[^0-9]/', '', $widget_id);
            $text_widgets = get_option('widget_text', []);
            if (isset($text_widgets[$num]) && isset($text_widgets[$num]['text'])) {
                if (stripos($text_widgets[$num]['text'], 'aitopia') !== false) {
                    $widget_findings[] = ['area' => $area, 'widget' => $widget_id, 'preview' => substr($text_widgets[$num]['text'], 0, 300)];
                }
            }
        }
    }
}

// ============================================================
// 6. BUSCAR EN HEADER/FOOTER SCRIPTS (Insert Headers and Footers, etc.)
// ============================================================
$script_options = [
    'ihaf_insert_header', 'ihaf_insert_footer', 'ihaf_insert_body',
    'wpcode_snippets', 'wpseo_social',
    'rank_math_head_code', 'rank_math_body_code', 'rank_math_footer_code',
    'elementor_custom_code',
];
$script_findings = [];
foreach ($script_options as $opt_name) {
    $val = get_option($opt_name, '');
    if (is_string($val) && stripos($val, 'aitopia') !== false) {
        $script_findings[] = ['option' => $opt_name, 'preview' => substr($val, 0, 300)];
    }
}

// Rank Math custom scripts
$rm_general = get_option('rank-math-options-general', []);
if (is_array($rm_general)) {
    foreach (['head_code', 'body_code', 'footer_code'] as $code_key) {
        if (isset($rm_general[$code_key]) && stripos($rm_general[$code_key], 'aitopia') !== false) {
            $script_findings[] = ['option' => 'rank-math ' . $code_key, 'preview' => substr($rm_general[$code_key], 0, 300)];
        }
    }
}

// ============================================================
// 7. BUSCAR EN POSTS/PAGES CONTENT
// ============================================================
$posts_with_aitopia = $wpdb->get_results(
    "SELECT ID, post_title, post_type FROM {$wpdb->posts} WHERE post_content LIKE '%aitopia%' OR post_content LIKE '%AITOPIA%' LIMIT 20"
);

// ============================================================
// MOSTRAR RESULTADOS
// ============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar AITOPIA - soniayanez.com</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:-apple-system,sans-serif;background:#0a0a0a;color:#e0e0e0;padding:2rem;}
        .container{max-width:900px;margin:0 auto;}
        h1{color:#ff4444;margin-bottom:.5rem;}
        .subtitle{color:#888;margin-bottom:2rem;}
        .section{background:#1a1a1a;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;}
        .section h2{font-size:1rem;color:#fff;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
        .count{background:#7C3AED;color:#fff;border-radius:999px;padding:.1rem .5rem;font-size:.7rem;}
        .count.danger{background:#ff4444;}
        .count.safe{background:#00C853;}
        .item{background:#111;border-radius:8px;padding:.75rem;margin-bottom:.5rem;font-size:.85rem;font-family:monospace;word-break:break-all;}
        .item .label{color:#4be4ff;font-weight:600;}
        .item .preview{color:#888;margin-top:.3rem;font-size:.8rem;}
        .empty{color:#00C853;font-style:italic;}
        .warning{background:#3a1a1a;border:1px solid #ff4444;border-radius:8px;padding:1rem;margin:2rem 0;font-size:.9rem;color:#ff9999;}
    </style>
</head>
<body>
<div class="container">
    <h1>Buscando AITOPIA en tu WordPress</h1>
    <p class="subtitle">Escaneando base de datos, tema, plugins, widgets y scripts...</p>

    <!-- wp_options -->
    <div class="section">
        <h2>Base de datos (wp_options) <span class="count <?= empty($findings) ? 'safe' : 'danger' ?>"><?= count($findings) ?></span></h2>
        <?php if (empty($findings)): ?>
            <p class="empty">No encontrado en opciones de WordPress</p>
        <?php else: ?>
            <?php foreach ($findings as $f): ?>
                <div class="item">
                    <span class="label"><?= esc_html($f['key']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Theme files -->
    <div class="section">
        <h2>Archivos del tema <span class="count <?= empty($theme_files_with_aitopia) ? 'safe' : 'danger' ?>"><?= count($theme_files_with_aitopia) ?></span></h2>
        <?php if (empty($theme_files_with_aitopia)): ?>
            <p class="empty">No encontrado en archivos del tema</p>
        <?php else: ?>
            <?php foreach ($theme_files_with_aitopia as $f): ?>
                <div class="item">
                    <span class="label"><?= esc_html($f['file']) ?>:<?= $f['line'] ?></span>
                    <div class="preview"><?= esc_html($f['content']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Plugin files -->
    <div class="section">
        <h2>Archivos de plugins <span class="count <?= empty($plugin_files_with_aitopia) ? 'safe' : 'danger' ?>"><?= count($plugin_files_with_aitopia) ?></span></h2>
        <?php if (empty($plugin_files_with_aitopia)): ?>
            <p class="empty">No encontrado en plugins</p>
        <?php else: ?>
            <?php foreach ($plugin_files_with_aitopia as $f): ?>
                <div class="item">
                    <span class="label"><?= esc_html($f['file']) ?>:<?= $f['line'] ?></span>
                    <div class="preview"><?= esc_html($f['content']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Widgets -->
    <div class="section">
        <h2>Widgets <span class="count <?= empty($widget_findings) ? 'safe' : 'danger' ?>"><?= count($widget_findings) ?></span></h2>
        <?php if (empty($widget_findings)): ?>
            <p class="empty">No encontrado en widgets</p>
        <?php else: ?>
            <?php foreach ($widget_findings as $f): ?>
                <div class="item">
                    <span class="label"><?= esc_html($f['area']) ?> → <?= esc_html($f['widget']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Scripts header/footer -->
    <div class="section">
        <h2>Scripts en header/footer <span class="count <?= empty($script_findings) ? 'safe' : 'danger' ?>"><?= count($script_findings) ?></span></h2>
        <?php if (empty($script_findings)): ?>
            <p class="empty">No encontrado en scripts personalizados</p>
        <?php else: ?>
            <?php foreach ($script_findings as $f): ?>
                <div class="item">
                    <span class="label"><?= esc_html($f['option']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Posts/Pages -->
    <div class="section">
        <h2>Posts/Páginas con AITOPIA en contenido <span class="count <?= empty($posts_with_aitopia) ? 'safe' : 'danger' ?>"><?= count($posts_with_aitopia) ?></span></h2>
        <?php if (empty($posts_with_aitopia)): ?>
            <p class="empty">No encontrado en contenido de posts</p>
        <?php else: ?>
            <?php foreach ($posts_with_aitopia as $p): ?>
                <div class="item">
                    <span class="label">[<?= esc_html($p->post_type) ?>] <?= esc_html($p->post_title) ?> (ID: <?= $p->ID ?>)</span>
                    <div class="preview"><a href="<?= admin_url('post.php?post=' . $p->ID . '&action=edit') ?>" style="color:#4be4ff;">Editar</a></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="warning">
        <strong>IMPORTANTE:</strong> Elimina este archivo (<code>buscar-aitopia.php</code>) cuando termines. Envíame una captura de los resultados para que pueda ayudarte a eliminar AITOPIA de donde esté.
    </div>
</div>
</body>
</html>
