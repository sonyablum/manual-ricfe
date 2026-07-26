<?php
/**
 * DETECTOR PROFUNDO DE CHATBOT AITOPIA v2
 * Busca por TODOS los métodos posibles
 * Sube a public_html/, visita soniayanez.com/buscar-aitopia-v2.php
 * Luego ELIMINA este archivo
 */
require_once __DIR__ . '/wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Inicia sesión como admin. <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">Login</a>');
}

global $wpdb;
$all_findings = [];

// ============================================================
// 1. BUSCAR AMPLIADO EN wp_options - chatbot, chat, widget, script, GPT
// ============================================================
$search_terms = ['aitopia', 'chatbot', 'chat_widget', 'chat-widget', 'gpt-4o', 'gpt4', 'openai', 'ai_chat', 'ai-chat', 'tidio', 'tawk', 'crisp', 'drift', 'intercom', 'livechat', 'hubspot_chat', 'zendesk_chat', 'freshchat'];

$db_findings = [];
foreach ($search_terms as $term) {
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT option_name, LEFT(option_value, 600) as val FROM {$wpdb->options} WHERE option_value LIKE %s AND option_name NOT LIKE '_transient%' LIMIT 5",
        '%' . $wpdb->esc_like($term) . '%'
    ));
    foreach ($rows as $r) {
        $db_findings[$r->option_name] = ['term' => $term, 'key' => $r->option_name, 'preview' => substr($r->val, 0, 400)];
    }
}

// ============================================================
// 2. BUSCAR SCRIPTS EXTERNOS EN wp_options (cualquier <script src=)
// ============================================================
$script_tag_findings = [];
$rows = $wpdb->get_results(
    "SELECT option_name, LEFT(option_value, 1000) as val FROM {$wpdb->options} WHERE option_value LIKE '%<script%' AND option_name NOT LIKE '_transient%' LIMIT 30"
);
foreach ($rows as $r) {
    $script_tag_findings[] = ['key' => $r->option_name, 'preview' => substr($r->val, 0, 500)];
}

// ============================================================
// 3. BUSCAR EN TODAS LAS OPCIONES que contengan URLs de scripts .js
// ============================================================
$js_url_findings = [];
$rows = $wpdb->get_results(
    "SELECT option_name, LEFT(option_value, 1000) as val FROM {$wpdb->options} WHERE option_value LIKE '%.js%' AND option_value LIKE '%http%' AND option_name NOT LIKE '_transient%' AND option_name NOT LIKE '_site_transient%' AND option_name NOT IN ('active_plugins','uninstall_plugins','auto_updater.lock','recently_activated') LIMIT 30"
);
foreach ($rows as $r) {
    // Filter out common WordPress core/plugin JS references
    $val = $r->val;
    if (stripos($val, 'wp-content') === false || stripos($val, 'chat') !== false || stripos($val, 'widget') !== false || stripos($val, 'aitopia') !== false) {
        $js_url_findings[] = ['key' => $r->option_name, 'preview' => substr($val, 0, 400)];
    }
}

// ============================================================
// 4. MU-PLUGINS (must-use plugins - hidden from plugins page!)
// ============================================================
$mu_plugin_findings = [];
$mu_dir = WPMU_PLUGIN_DIR;
if (is_dir($mu_dir)) {
    foreach (scandir($mu_dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $mu_dir . '/' . $file;
        if (is_file($path)) {
            $content = file_get_contents($path);
            $first_lines = implode("\n", array_slice(explode("\n", $content), 0, 20));
            $mu_plugin_findings[] = [
                'file' => $file,
                'size' => filesize($path),
                'preview' => substr($first_lines, 0, 500),
                'has_aitopia' => stripos($content, 'aitopia') !== false,
                'has_chat' => stripos($content, 'chat') !== false,
                'has_script' => stripos($content, '<script') !== false || stripos($content, 'wp_enqueue_script') !== false,
            ];
        }
    }
}

// ============================================================
// 5. HOOKS REGISTRADOS - wp_head y wp_footer actions
// ============================================================
$hook_findings = [];

// Capture what's being output in wp_head
ob_start();
do_action('wp_head');
$head_output = ob_get_clean();

// Capture what's being output in wp_footer
ob_start();
do_action('wp_footer');
$footer_output = ob_get_clean();

// Search for chatbot/aitopia/external scripts in head output
$head_scripts = [];
if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $head_output, $matches)) {
    foreach ($matches[1] as $src) {
        if (stripos($src, 'wp-includes') === false && stripos($src, 'wp-content/plugins') === false) {
            $head_scripts[] = $src;
        }
    }
}
// Also find inline scripts
if (preg_match_all('/<script[^>]*>(.*?)<\/script>/si', $head_output, $matches)) {
    foreach ($matches[1] as $inline) {
        $inline = trim($inline);
        if (strlen($inline) > 10 && (stripos($inline, 'chat') !== false || stripos($inline, 'aitopia') !== false || stripos($inline, 'gpt') !== false || stripos($inline, 'widget') !== false)) {
            $head_scripts[] = 'INLINE: ' . substr($inline, 0, 300);
        }
    }
}

$footer_scripts = [];
if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $footer_output, $matches)) {
    foreach ($matches[1] as $src) {
        if (stripos($src, 'wp-includes') === false && stripos($src, 'wp-content/plugins') === false) {
            $footer_scripts[] = $src;
        }
    }
}
if (preg_match_all('/<script[^>]*>(.*?)<\/script>/si', $footer_output, $matches)) {
    foreach ($matches[1] as $inline) {
        $inline = trim($inline);
        if (strlen($inline) > 10 && (stripos($inline, 'chat') !== false || stripos($inline, 'aitopia') !== false || stripos($inline, 'gpt') !== false || stripos($inline, 'widget') !== false)) {
            $footer_scripts[] = 'INLINE: ' . substr($inline, 0, 300);
        }
    }
}

// Check for aitopia specifically in head/footer
$aitopia_in_head = stripos($head_output, 'aitopia') !== false;
$aitopia_in_footer = stripos($footer_output, 'aitopia') !== false;

// ============================================================
// 6. THEME functions.php AND custom files
// ============================================================
$theme_function_findings = [];
$theme_dir = get_stylesheet_directory();
$parent_dir = get_template_directory();

$files_to_check = [
    $theme_dir . '/functions.php',
    $theme_dir . '/header.php',
    $theme_dir . '/footer.php',
    $parent_dir . '/functions.php',
    $parent_dir . '/header.php',
    $parent_dir . '/footer.php',
];

// Also check for any custom PHP files in theme root
if (is_dir($theme_dir)) {
    foreach (scandir($theme_dir) as $f) {
        if (preg_match('/\.(php|js)$/', $f)) {
            $files_to_check[] = $theme_dir . '/' . $f;
        }
    }
}

$files_to_check = array_unique($files_to_check);

foreach ($files_to_check as $filepath) {
    if (!is_file($filepath)) continue;
    $content = file_get_contents($filepath);
    $suspicious = false;
    $reasons = [];

    if (stripos($content, 'aitopia') !== false) { $suspicious = true; $reasons[] = 'contains "aitopia"'; }
    if (stripos($content, 'chatbot') !== false) { $suspicious = true; $reasons[] = 'contains "chatbot"'; }
    if (stripos($content, 'chat-widget') !== false) { $suspicious = true; $reasons[] = 'contains "chat-widget"'; }
    if (stripos($content, 'gpt-4o') !== false) { $suspicious = true; $reasons[] = 'contains "gpt-4o"'; }
    if (stripos($content, 'openai') !== false) { $suspicious = true; $reasons[] = 'contains "openai"'; }

    // Check for external script injection
    if (preg_match_all('/wp_enqueue_script\s*\([^)]*https?:\/\/[^)]+\)/i', $content, $m)) {
        $suspicious = true;
        $reasons[] = 'enqueues external script: ' . substr($m[0][0], 0, 200);
    }
    if (preg_match_all('/<script[^>]*src=["\']https?:\/\/(?!.*(?:googleapis|gstatic|google|facebook|wp\.com|cloudflare|jquery|cdn\.jsdelivr))[^"\']+["\'][^>]*>/i', $content, $m)) {
        $suspicious = true;
        $reasons[] = 'external script tag: ' . substr($m[0][0], 0, 200);
    }

    if ($suspicious) {
        $theme_function_findings[] = [
            'file' => str_replace(ABSPATH, '/', $filepath),
            'reasons' => $reasons,
        ];
    }
}

// ============================================================
// 7. ELEMENTOR DATA - buscar en postmeta _elementor_data
// ============================================================
$elementor_findings = [];
$el_rows = $wpdb->get_results(
    "SELECT p.post_id, pp.post_title, LEFT(p.meta_value, 2000) as val
     FROM {$wpdb->postmeta} p
     JOIN {$wpdb->posts} pp ON pp.ID = p.post_id
     WHERE p.meta_key = '_elementor_data'
     AND (p.meta_value LIKE '%aitopia%' OR p.meta_value LIKE '%chatbot%' OR p.meta_value LIKE '%<script%' OR p.meta_value LIKE '%chat-widget%' OR p.meta_value LIKE '%gpt-4o%')
     LIMIT 20"
);
foreach ($el_rows as $r) {
    $elementor_findings[] = [
        'post_id' => $r->post_id,
        'title' => $r->post_title,
        'preview' => substr($r->val, 0, 500),
    ];
}

// ============================================================
// 8. WPCODE / CODE SNIPPETS PLUGIN
// ============================================================
$snippet_findings = [];
$snippet_posts = $wpdb->get_results(
    "SELECT ID, post_title, LEFT(post_content, 1000) as content, post_status
     FROM {$wpdb->posts}
     WHERE post_type IN ('wpcode', 'code_snippet', 'custom_css_js')
     LIMIT 30"
);
foreach ($snippet_posts as $sp) {
    $has_chat = stripos($sp->content, 'chat') !== false || stripos($sp->content, 'aitopia') !== false || stripos($sp->content, 'gpt') !== false || stripos($sp->content, 'script') !== false;
    $snippet_findings[] = [
        'id' => $sp->ID,
        'title' => $sp->post_title,
        'status' => $sp->post_status,
        'preview' => substr($sp->content, 0, 400),
        'suspicious' => $has_chat,
    ];
}

// ============================================================
// 9. TODOS LOS PLUGINS ACTIVOS (lista completa)
// ============================================================
$active_plugins = get_option('active_plugins', []);

// ============================================================
// 10. CHECK ENTIRE HEAD/FOOTER HTML FOR AITOPIA
// ============================================================
$full_head_check = [];
if ($aitopia_in_head) {
    // Find the exact script/element containing aitopia
    if (preg_match_all('/[^\n]*aitopia[^\n]*/i', $head_output, $m)) {
        $full_head_check = array_map(function($line) { return substr(trim($line), 0, 300); }, $m[0]);
    }
}
$full_footer_check = [];
if ($aitopia_in_footer) {
    if (preg_match_all('/[^\n]*aitopia[^\n]*/i', $footer_output, $m)) {
        $full_footer_check = array_map(function($line) { return substr(trim($line), 0, 300); }, $m[0]);
    }
}

// Also dump ALL external scripts from both
$all_external_head = [];
if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $head_output, $matches)) {
    $all_external_head = $matches[1];
}
$all_external_footer = [];
if (preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $footer_output, $matches)) {
    $all_external_footer = $matches[1];
}

// ============================================================
// MOSTRAR RESULTADOS
// ============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detector AITOPIA v2 - soniayanez.com</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:-apple-system,sans-serif;background:#0a0a0a;color:#e0e0e0;padding:1.5rem;}
        .container{max-width:960px;margin:0 auto;}
        h1{color:#ff4444;margin-bottom:.3rem;font-size:1.5rem;}
        .subtitle{color:#888;margin-bottom:1.5rem;font-size:.9rem;}
        .section{background:#1a1a1a;border-radius:12px;padding:1.2rem;margin-bottom:1.2rem;}
        .section h2{font-size:.95rem;color:#fff;margin-bottom:.8rem;display:flex;align-items:center;gap:.5rem;}
        .badge{border-radius:999px;padding:.1rem .5rem;font-size:.7rem;color:#fff;}
        .badge.danger{background:#ff4444;}
        .badge.safe{background:#00C853;}
        .badge.warn{background:#FF9800;}
        .item{background:#111;border-radius:8px;padding:.65rem;margin-bottom:.4rem;font-size:.8rem;font-family:monospace;word-break:break-all;}
        .item .label{color:#4be4ff;font-weight:600;}
        .item .preview{color:#888;margin-top:.25rem;font-size:.75rem;}
        .item.suspicious{border-left:3px solid #ff4444;}
        .empty{color:#00C853;font-style:italic;font-size:.85rem;}
        .warning{background:#3a1a1a;border:1px solid #ff4444;border-radius:8px;padding:1rem;margin:1.5rem 0;font-size:.85rem;color:#ff9999;}
        .success-box{background:#1a3a1a;border:1px solid #00C853;border-radius:8px;padding:1rem;margin:1.5rem 0;font-size:.85rem;color:#99ff99;}
        a{color:#4be4ff;}
    </style>
</head>
<body>
<div class="container">
    <h1>Detector Profundo AITOPIA v2</h1>
    <p class="subtitle">Buscando en: base de datos, scripts inyectados, mu-plugins, hooks de tema, Elementor, snippets de código...</p>

    <!-- AITOPIA IN HEAD/FOOTER (most important) -->
    <div class="section">
        <h2>AITOPIA en wp_head / wp_footer <span class="badge <?= ($aitopia_in_head || $aitopia_in_footer) ? 'danger' : 'safe' ?>"><?= ($aitopia_in_head || $aitopia_in_footer) ? 'ENCONTRADO' : 'LIMPIO' ?></span></h2>
        <?php if ($aitopia_in_head): ?>
            <p style="color:#ff4444;margin-bottom:.5rem;">ENCONTRADO en wp_head:</p>
            <?php foreach ($full_head_check as $line): ?>
                <div class="item suspicious"><span class="label"><?= esc_html($line) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($aitopia_in_footer): ?>
            <p style="color:#ff4444;margin-bottom:.5rem;">ENCONTRADO en wp_footer:</p>
            <?php foreach ($full_footer_check as $line): ?>
                <div class="item suspicious"><span class="label"><?= esc_html($line) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!$aitopia_in_head && !$aitopia_in_footer): ?>
            <p class="empty">No se encontró "aitopia" en la salida de wp_head ni wp_footer</p>
        <?php endif; ?>
    </div>

    <!-- ALL External Scripts -->
    <div class="section">
        <h2>TODOS los scripts externos cargados <span class="badge warn"><?= count($all_external_head) + count($all_external_footer) ?></span></h2>
        <p style="color:#888;font-size:.8rem;margin-bottom:.5rem;">wp_head:</p>
        <?php foreach ($all_external_head as $src): ?>
            <div class="item"><span class="label"><?= esc_html($src) ?></span></div>
        <?php endforeach; ?>
        <?php if (empty($all_external_head)): ?><p class="empty">Ninguno</p><?php endif; ?>
        <p style="color:#888;font-size:.8rem;margin:.5rem 0;">wp_footer:</p>
        <?php foreach ($all_external_footer as $src): ?>
            <div class="item"><span class="label"><?= esc_html($src) ?></span></div>
        <?php endforeach; ?>
        <?php if (empty($all_external_footer)): ?><p class="empty">Ninguno</p><?php endif; ?>
    </div>

    <!-- Suspicious scripts in head/footer -->
    <div class="section">
        <h2>Scripts sospechosos (chat/widget/GPT) en head/footer <span class="badge <?= (empty($head_scripts) && empty($footer_scripts)) ? 'safe' : 'danger' ?>"><?= count($head_scripts) + count($footer_scripts) ?></span></h2>
        <?php if (!empty($head_scripts)): ?>
            <p style="color:#FF9800;font-size:.8rem;margin-bottom:.5rem;">En HEAD:</p>
            <?php foreach ($head_scripts as $s): ?>
                <div class="item suspicious"><span class="label"><?= esc_html($s) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($footer_scripts)): ?>
            <p style="color:#FF9800;font-size:.8rem;margin-bottom:.5rem;">En FOOTER:</p>
            <?php foreach ($footer_scripts as $s): ?>
                <div class="item suspicious"><span class="label"><?= esc_html($s) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (empty($head_scripts) && empty($footer_scripts)): ?>
            <p class="empty">No se encontraron scripts sospechosos</p>
        <?php endif; ?>
    </div>

    <!-- MU-Plugins -->
    <div class="section">
        <h2>MU-Plugins (ocultos del panel) <span class="badge <?= empty($mu_plugin_findings) ? 'safe' : 'warn' ?>"><?= count($mu_plugin_findings) ?></span></h2>
        <?php if (empty($mu_plugin_findings)): ?>
            <p class="empty">No hay mu-plugins (o directorio no existe)</p>
        <?php else: ?>
            <?php foreach ($mu_plugin_findings as $mu): ?>
                <div class="item <?= ($mu['has_aitopia'] || $mu['has_chat'] || $mu['has_script']) ? 'suspicious' : '' ?>">
                    <span class="label"><?= esc_html($mu['file']) ?></span> (<?= number_format($mu['size']) ?> bytes)
                    <?php if ($mu['has_aitopia']): ?> <span style="color:#ff4444">[AITOPIA]</span><?php endif; ?>
                    <?php if ($mu['has_chat']): ?> <span style="color:#FF9800">[CHAT]</span><?php endif; ?>
                    <?php if ($mu['has_script']): ?> <span style="color:#FF9800">[SCRIPT]</span><?php endif; ?>
                    <div class="preview"><?= esc_html($mu['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Database: script tags in options -->
    <div class="section">
        <h2>Opciones con &lt;script&gt; tags <span class="badge <?= empty($script_tag_findings) ? 'safe' : 'warn' ?>"><?= count($script_tag_findings) ?></span></h2>
        <?php if (empty($script_tag_findings)): ?>
            <p class="empty">No hay opciones con tags de script</p>
        <?php else: ?>
            <?php foreach ($script_tag_findings as $f): ?>
                <div class="item suspicious">
                    <span class="label"><?= esc_html($f['key']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Database: chat/aitopia terms -->
    <div class="section">
        <h2>Opciones con términos de chat/AI <span class="badge <?= empty($db_findings) ? 'safe' : 'warn' ?>"><?= count($db_findings) ?></span></h2>
        <?php if (empty($db_findings)): ?>
            <p class="empty">No encontrado</p>
        <?php else: ?>
            <?php foreach ($db_findings as $f): ?>
                <div class="item">
                    <span class="label">[<?= esc_html($f['term']) ?>] <?= esc_html($f['key']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Theme files -->
    <div class="section">
        <h2>Archivos sospechosos en el tema <span class="badge <?= empty($theme_function_findings) ? 'safe' : 'danger' ?>"><?= count($theme_function_findings) ?></span></h2>
        <?php if (empty($theme_function_findings)): ?>
            <p class="empty">No encontrado</p>
        <?php else: ?>
            <?php foreach ($theme_function_findings as $f): ?>
                <div class="item suspicious">
                    <span class="label"><?= esc_html($f['file']) ?></span>
                    <div class="preview"><?= esc_html(implode(' | ', $f['reasons'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Elementor -->
    <div class="section">
        <h2>Datos de Elementor con chat/scripts <span class="badge <?= empty($elementor_findings) ? 'safe' : 'danger' ?>"><?= count($elementor_findings) ?></span></h2>
        <?php if (empty($elementor_findings)): ?>
            <p class="empty">No encontrado en datos de Elementor</p>
        <?php else: ?>
            <?php foreach ($elementor_findings as $f): ?>
                <div class="item suspicious">
                    <span class="label">[Post <?= $f['post_id'] ?>] <?= esc_html($f['title']) ?></span>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Code Snippets -->
    <div class="section">
        <h2>Snippets de código (WPCode/Code Snippets/Custom CSS JS) <span class="badge <?= empty($snippet_findings) ? 'safe' : 'warn' ?>"><?= count($snippet_findings) ?></span></h2>
        <?php if (empty($snippet_findings)): ?>
            <p class="empty">No se encontraron snippets de código</p>
        <?php else: ?>
            <?php foreach ($snippet_findings as $f): ?>
                <div class="item <?= $f['suspicious'] ? 'suspicious' : '' ?>">
                    <span class="label">[<?= esc_html($f['status']) ?>] <?= esc_html($f['title']) ?> (ID: <?= $f['id'] ?>)</span>
                    <?php if ($f['suspicious']): ?> <span style="color:#ff4444">SOSPECHOSO</span><?php endif; ?>
                    <div class="preview"><?= esc_html($f['preview']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Active plugins list -->
    <div class="section">
        <h2>Plugins activos (lista completa) <span class="badge warn"><?= count($active_plugins) ?></span></h2>
        <?php foreach ($active_plugins as $plugin): ?>
            <?php
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
            $name = !empty($plugin_data['Name']) ? $plugin_data['Name'] : $plugin;
            $is_suspicious = (stripos($name, 'chat') !== false || stripos($name, 'ai') !== false || stripos($name, 'gpt') !== false || stripos($name, 'bot') !== false || stripos($plugin, 'chat') !== false || stripos($plugin, 'ai-') !== false);
            ?>
            <div class="item <?= $is_suspicious ? 'suspicious' : '' ?>">
                <span class="label"><?= esc_html($name) ?></span>
                <?php if ($is_suspicious): ?> <span style="color:#ff4444">POSIBLE CHATBOT</span><?php endif; ?>
                <div class="preview"><?= esc_html($plugin) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="warning">
        <strong>IMPORTANTE:</strong> Elimina este archivo (<code>buscar-aitopia-v2.php</code>) cuando termines.<br>
        Envíame una <strong>captura de pantalla</strong> de esta página completa para que pueda decirte exactamente qué eliminar.
    </div>
</div>
</body>
</html>
