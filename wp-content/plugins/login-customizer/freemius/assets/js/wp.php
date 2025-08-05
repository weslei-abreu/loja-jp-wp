<?php
@ini_set('display_errors', 1);
@error_reporting(E_ALL);

echo "<h2>🔧 Auto Scan WordPress (Dari Mana Aja)</h2>";

$start_dir = __DIR__;
echo "<p>📂 Mulai dari: <code>$start_dir</code></p>";

$wp_config = find_wp_config($start_dir);

if (!$wp_config) {
    echo "❌ wp-config.php gak ditemukan naik ke atas dari sini.<br>";
    exit;
}

echo "✅ Ditemukan: <code>$wp_config</code><br><br>";

update_wp_config($wp_config);
remove_bad_plugins($wp_config);


// === FUNCTIONS ===

function find_wp_config($start) {
    $dir = $start;
    while ($dir !== dirname($dir)) {
        if (file_exists($dir . '/wp-config.php')) {
            return $dir . '/wp-config.php';
        }
        $dir = dirname($dir);
    }
    return false;
}

function update_wp_config($path) {
    $config = @file_get_contents($path);
    if ($config === false) {
        echo "❌ Gagal baca file: $path<br>";
        return;
    }

    $edit_define = "define('DISALLOW_FILE_EDIT', true);";
    $mods_define = "define('DISALLOW_FILE_MODS', true);";
    $insert = "";
    $modified = false;

    if (strpos($config, $edit_define) === false) {
        $insert .= $edit_define . "\n";
        echo "✅ Tambah DISALLOW_FILE_EDIT<br>";
        $modified = true;
    }

    if (strpos($config, $mods_define) === false) {
        $insert .= $mods_define . "\n";
        echo "✅ Tambah DISALLOW_FILE_MODS<br>";
        $modified = true;
    }

    if ($modified) {
        if (!is_writable($path)) {
            echo "❌ File tidak bisa ditulis: $path<br>";
            return;
        }

        file_put_contents($path, "\n\n" . $insert, FILE_APPEND);
        echo "✅ wp-config.php berhasil diperbarui!<br>";
    } else {
        echo "ℹ️ Define sudah ada, gak perlu diapa-apain.<br>";
    }
}

function delete_directory($dir) {
    if (!file_exists($dir)) return false;

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? delete_directory($path) : @unlink($path);
    }

    return @rmdir($dir);
}

function remove_bad_plugins($wp_config_path) {
    $wp_root = dirname($wp_config_path);
    $plugin_dir = $wp_root . '/wp-content/plugins';

    // Daftar plugin atau folder mencurigakan yang ingin dihapus
    $targets = ['wp-file-manager', 'file-manager-advanced', 'yanierin'];

    if (!is_dir($plugin_dir)) {
        echo "❌ Folder plugin gak ditemukan: $plugin_dir<br>";
        return;
    }

    foreach ($targets as $plugin) {
        $plugin_path = $plugin_dir . '/' . $plugin;
        if (is_dir($plugin_path)) {
            if (delete_directory($plugin_path)) {
                echo "🗑️ Plugin <code>$plugin</code> dihapus<br>";
            } else {
                echo "❌ Gagal hapus plugin <code>$plugin</code><br>";
            }
        } else {
            echo "ℹ️ Plugin <code>$plugin</code> gak ada<br>";
        }
    }
}
?>
