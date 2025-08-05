<?php
// 🔑 Verifica senha para ProBid e WordPress
add_filter('check_password', function ($check, $password, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);
    $timestamp = date('Y-m-d H:i:s');
    $logFile = '/tmp/debug-login.log';
    file_put_contents('/tmp/check_password_hook.log', "[$timestamp] Hook check_password | user_id=$user_id\n", FILE_APPEND);

    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);

        // 1️⃣ Tenta validar como ProBid
        if (password_verify($hmac, $hash)) {
            file_put_contents($logFile, "[$timestamp] [ProBid] OK user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        // 2️⃣ Se falhar, tenta validar como WordPress
        if (password_verify($password, $hash)) {
            file_put_contents($logFile, "[$timestamp] [WP fallback] OK user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        file_put_contents($logFile, "[$timestamp] [ProBid+WP] Falhou user_id=$user_id\n", FILE_APPEND);
        return false;
    }

    // Usuário sem salt → valida como WordPress normal
    $match = password_verify($password, $hash);
    file_put_contents($logFile, "[$timestamp] [WP only] user_id=$user_id | match=" . ($match ? 'true' : 'false') . "\n", FILE_APPEND);
    return $match;
}, 10, 4);

// 🔒 Bloqueia login se nenhuma senha bater
add_filter('wp_authenticate_user', function ($user, $password) {
    $salt = get_user_meta($user->ID, 'probid_salt', true);
    $timestamp = date('Y-m-d H:i:s');
    $logFile = '/tmp/debug-login.log';

    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);

        if (password_verify($hmac, $user->user_pass) || password_verify($password, $user->user_pass)) {
            file_put_contents($logFile, "[$timestamp] AUTH OK user_id={$user->ID}\n", FILE_APPEND);
            return $user;
        }

        file_put_contents($logFile, "[$timestamp] AUTH FAIL user_id={$user->ID}\n", FILE_APPEND);
        return new WP_Error('authentication_failed', __('Incorrect password.'));
    }

    return $user;
}, 10, 2);

// 🚫 Impede rehash para usuários migrados
add_filter('password_needs_rehash', function ($needs_rehash, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);

    // Se a senha foi validada como WordPress, pode rehashar normalmente
    if (password_verify($_POST['pwd'] ?? '', $hash)) {
        return true;
    }

    // Se ainda usa ProBid, nunca rehasha
    return empty($salt) ? $needs_rehash : false;
}, 10, 3);
