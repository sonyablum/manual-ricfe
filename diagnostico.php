<?php
/**
 * Script de diagnóstico para soniayanez.com
 *
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz de tu WordPress en Hostinger (public_html/)
 * 2. Visita: https://soniayanez.com/diagnostico.php
 * 3. Sigue las instrucciones que aparezcan
 * 4. IMPORTANTE: Elimina este archivo cuando termines por seguridad
 */

// Evitar caché
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Content-Type: text/html; charset=utf-8');

$results = [];
$fixes_applied = [];

// --- 1. Verificar versión de PHP ---
$php_version = phpversion();
$php_ok = version_compare($php_version, '7.4', '>=');
$results[] = [
    'test' => 'Versión de PHP',
    'value' => $php_version,
    'ok' => $php_ok,
    'fix' => $php_ok ? '' : 'Tu PHP es muy antiguo. Ve a Hostinger > Avanzado > Configuración PHP y selecciona PHP 8.0 o superior.'
];

// --- 2. Verificar memoria PHP ---
$memory_limit = ini_get('memory_limit');
$memory_bytes = wp_convert_hr_to_bytes_simple($memory_limit);
$memory_ok = $memory_bytes >= 128 * 1024 * 1024;
$results[] = [
    'test' => 'Límite de memoria PHP',
    'value' => $memory_limit,
    'ok' => $memory_ok,
    'fix' => $memory_ok ? '' : 'Memoria insuficiente. Se intentará corregir en wp-config.php'
];

// --- 3. Verificar wp-config.php ---
$wp_config_exists = file_exists(__DIR__ . '/wp-config.php');
$results[] = [
    'test' => 'wp-config.php existe',
    'value' => $wp_config_exists ? 'Sí' : 'No',
    'ok' => $wp_config_exists,
    'fix' => $wp_config_exists ? '' : 'Falta wp-config.php. Tu WordPress está dañado. Contacta soporte de Hostinger.'
];

// --- 4. Verificar .htaccess ---
$htaccess_exists = file_exists(__DIR__ . '/.htaccess');
$htaccess_content = $htaccess_exists ? file_get_contents(__DIR__ . '/.htaccess') : '';
$htaccess_ok = $htaccess_exists && strpos($htaccess_content, 'WordPress') !== false;
$results[] = [
    'test' => '.htaccess válido',
    'value' => $htaccess_exists ? 'Existe (' . strlen($htaccess_content) . ' bytes)' : 'No existe',
    'ok' => $htaccess_ok,
    'fix' => $htaccess_ok ? '' : 'Se regenerará el .htaccess con valores por defecto de WordPress.'
];

// --- 5. Verificar carpeta de plugins ---
$plugins_dir = __DIR__ . '/wp-content/plugins';
$plugins_exist = is_dir($plugins_dir);
$plugin_count = 0;
if ($plugins_exist) {
    $plugin_count = count(array_filter(scandir($plugins_dir), function($f) {
        return $f !== '.' && $f !== '..';
    }));
}
$results[] = [
    'test' => 'Plugins instalados',
    'value' => $plugins_exist ? "$plugin_count plugins" : 'Carpeta no encontrada',
    'ok' => true,
    'fix' => ''
];

// --- 6. Verificar permisos de archivos clave ---
$files_to_check = ['wp-config.php', '.htaccess', 'wp-content', 'wp-content/plugins', 'wp-content/themes'];
foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $is_dir = is_dir($path);
        $expected = $is_dir ? '0755' : '0644';
        $perm_ok = $perms === $expected || $perms === '0750' || $perms === '0640';
        $results[] = [
            'test' => "Permisos de $file",
            'value' => $perms,
            'ok' => $perm_ok,
            'fix' => $perm_ok ? '' : "Permisos incorrectos. Esperado: $expected, actual: $perms"
        ];
    }
}

// --- 7. Verificar error log ---
$error_log_paths = [
    __DIR__ . '/wp-content/debug.log',
    __DIR__ . '/error_log',
    __DIR__ . '/php_errorlog',
];
$error_log_content = '';
foreach ($error_log_paths as $log_path) {
    if (file_exists($log_path)) {
        $size = filesize($log_path);
        $error_log_content = "Encontrado: $log_path (" . round($size/1024, 1) . " KB)\n";
        // Leer últimas líneas
        $lines = file($log_path);
        $last_lines = array_slice($lines, -10);
        $error_log_content .= implode('', $last_lines);
        break;
    }
}
$results[] = [
    'test' => 'Log de errores',
    'value' => $error_log_content ?: 'No se encontró log de errores',
    'ok' => empty($error_log_content),
    'fix' => ''
];

// --- 8. Verificar conexión a base de datos ---
$db_ok = false;
$db_message = 'No se pudo verificar';
if ($wp_config_exists) {
    $config_content = file_get_contents(__DIR__ . '/wp-config.php');
    preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $config_content, $db_name);
    preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $config_content, $db_user);
    preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $config_content, $db_pass);
    preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $config_content, $db_host);

    if (!empty($db_name[1]) && !empty($db_user[1]) && !empty($db_host[1])) {
        $conn = @mysqli_connect($db_host[1], $db_user[1], $db_pass[1] ?? '', $db_name[1]);
        if ($conn) {
            $db_ok = true;
            $db_message = 'Conexión exitosa a ' . $db_name[1];
            mysqli_close($conn);
        } else {
            $db_message = 'Error: ' . mysqli_connect_error();
        }
    } else {
        $db_message = 'No se pudieron leer credenciales de wp-config.php';
    }
}
$results[] = [
    'test' => 'Conexión a base de datos',
    'value' => $db_message,
    'ok' => $db_ok,
    'fix' => $db_ok ? '' : 'Verifica las credenciales de la BD en Hostinger > Bases de datos'
];

// --- APLICAR REPARACIONES SI SE SOLICITA ---
$repair_mode = isset($_GET['reparar']) && $_GET['reparar'] === '1';

if ($repair_mode) {
    // Reparación 1: Regenerar .htaccess
    $default_htaccess = "# BEGIN WordPress\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /\nRewriteRule ^index\\.php$ - [L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . /index.php [L]\n</IfModule>\n# END WordPress\n";

    if (!$htaccess_ok) {
        if ($htaccess_exists) {
            copy(__DIR__ . '/.htaccess', __DIR__ . '/.htaccess.backup.' . date('Ymd_His'));
        }
        file_put_contents(__DIR__ . '/.htaccess', $default_htaccess);
        $fixes_applied[] = '.htaccess regenerado (backup creado si existía)';
    }

    // Reparación 2: Aumentar memoria en wp-config.php
    if ($wp_config_exists && !$memory_ok) {
        $config = file_get_contents(__DIR__ . '/wp-config.php');
        if (strpos($config, 'WP_MEMORY_LIMIT') === false) {
            $config = str_replace(
                "<?php",
                "<?php\ndefine('WP_MEMORY_LIMIT', '256M');",
                $config
            );
            copy(__DIR__ . '/wp-config.php', __DIR__ . '/wp-config.php.backup.' . date('Ymd_His'));
            file_put_contents(__DIR__ . '/wp-config.php', $config);
            $fixes_applied[] = 'Memoria aumentada a 256M en wp-config.php (backup creado)';
        }
    }

    // Reparación 3: Activar WP_DEBUG
    if ($wp_config_exists) {
        $config = file_get_contents(__DIR__ . '/wp-config.php');
        if (strpos($config, "'WP_DEBUG', false") !== false || strpos($config, '"WP_DEBUG", false') !== false) {
            $config = preg_replace(
                "/define\s*\(\s*['\"]WP_DEBUG['\"]\s*,\s*false\s*\)/",
                "define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true)",
                $config
            );
            file_put_contents(__DIR__ . '/wp-config.php', $config);
            $fixes_applied[] = 'WP_DEBUG activado para ver errores detallados en wp-content/debug.log';
        }
    }

    // Reparación 4: Desactivar plugins (renombrar carpeta)
    if (isset($_GET['desactivar_plugins']) && $_GET['desactivar_plugins'] === '1') {
        if ($plugins_exist) {
            $backup_name = $plugins_dir . '_backup_' . date('Ymd_His');
            rename($plugins_dir, $backup_name);
            mkdir($plugins_dir, 0755);
            $fixes_applied[] = "Plugins desactivados. Carpeta renombrada a: " . basename($backup_name);
        }
    }
}

function wp_convert_hr_to_bytes_simple($value) {
    $value = strtolower(trim($value));
    $bytes = (int)$value;
    if (strpos($value, 'g') !== false) $bytes *= 1024 * 1024 * 1024;
    elseif (strpos($value, 'm') !== false) $bytes *= 1024 * 1024;
    elseif (strpos($value, 'k') !== false) $bytes *= 1024;
    return $bytes;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - soniayanez.com</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0a0a; color: #e0e0e0; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #D4AF37; margin-bottom: 0.5rem; }
        .subtitle { color: #888; margin-bottom: 2rem; }
        .result { background: #1a1a1a; border-radius: 8px; padding: 1rem; margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 1rem; }
        .result .icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
        .result .details { flex: 1; }
        .result .test-name { font-weight: 600; }
        .result .test-value { color: #aaa; font-size: 0.9rem; margin-top: 0.25rem; white-space: pre-wrap; word-break: break-all; }
        .result .fix { color: #ff9800; font-size: 0.85rem; margin-top: 0.5rem; }
        .ok { border-left: 3px solid #00C853; }
        .fail { border-left: 3px solid #ff4444; }
        .actions { margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.95rem; cursor: pointer; border: none; }
        .btn-primary { background: #D4AF37; color: #000; }
        .btn-danger { background: #ff4444; color: #fff; }
        .btn:hover { opacity: 0.9; }
        .alert { background: #1a3a1a; border: 1px solid #00C853; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .alert-warning { background: #3a2a0a; border-color: #ff9800; }
        .warning { background: #3a1a1a; border: 1px solid #ff4444; border-radius: 8px; padding: 1rem; margin: 2rem 0; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de soniayanez.com</h1>
        <p class="subtitle">Verificando el estado de tu instalación de WordPress</p>

        <?php if (!empty($fixes_applied)): ?>
            <div class="alert">
                <strong>Reparaciones aplicadas:</strong>
                <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                    <?php foreach ($fixes_applied as $fix): ?>
                        <li><?= htmlspecialchars($fix) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top: 0.75rem;">Intenta visitar <a href="https://soniayanez.com" style="color: #D4AF37;">soniayanez.com</a> ahora.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($results as $r): ?>
            <div class="result <?= $r['ok'] ? 'ok' : 'fail' ?>">
                <span class="icon"><?= $r['ok'] ? '&#10004;' : '&#10008;' ?></span>
                <div class="details">
                    <div class="test-name"><?= htmlspecialchars($r['test']) ?></div>
                    <div class="test-value"><?= htmlspecialchars($r['value']) ?></div>
                    <?php if (!empty($r['fix'])): ?>
                        <div class="fix"><?= htmlspecialchars($r['fix']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="actions">
            <a href="?reparar=1" class="btn btn-primary" onclick="return confirm('¿Aplicar reparaciones automáticas? Se crearán backups de los archivos modificados.')">
                Reparar automáticamente
            </a>
            <a href="?reparar=1&desactivar_plugins=1" class="btn btn-danger" onclick="return confirm('Esto desactivará TODOS los plugins. ¿Continuar?')">
                Desactivar todos los plugins
            </a>
        </div>

        <div class="warning">
            <strong>IMPORTANTE:</strong> Elimina este archivo (<code>diagnostico.php</code>) de tu servidor cuando termines el diagnóstico. Dejarlo expuesto es un riesgo de seguridad.
        </div>
    </div>
</body>
</html>
