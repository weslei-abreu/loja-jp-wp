<?php

// Hook para verificar a senha (usuários migrados ou nativos)
add_filter('check_password', function ($check, $password, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);
    $logFile = '/tmp/debug-login.log';
    $timestamp = date('Y-m-d H:i:s');

    file_put_contents('/tmp/check_password_hook.log', "[$timestamp] Hook check_password chamado | user_id=$user_id\n", FILE_APPEND);

    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);
        $match = password_verify($hmac, $hash);
        file_put_contents($logFile, "[$timestamp] [ProBid] user_id=$user_id | salt=$salt | senha=$password | hmac=$hmac | match=" . ($match ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
        return $match;
    }

    $match = password_verify($password, $hash);
    file_put_contents($logFile, "[$timestamp] [WordPress] user_id=$user_id | senha=$password | match=" . ($match ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
    return $match;
}, 10, 4);

// Hook de autenticação (bloqueia login se hash não bater)
add_filter('wp_authenticate_user', function ($user, $password) {
    $salt = get_user_meta($user->ID, 'probid_salt', true);
    $timestamp = date('Y-m-d H:i:s');
    $logFile = '/tmp/debug-login.log';

    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);

        if (!password_verify($hmac, $user->user_pass)) {
            file_put_contents($logFile, "[$timestamp] [ProBid - AUTH] user_id={$user->ID} | senha=$password | hmac=$hmac | match=false | BLOQUEADO\n", FILE_APPEND);
            return new WP_Error('authentication_failed', __('Incorrect password.'));
        }

        file_put_contents($logFile, "[$timestamp] [ProBid - AUTH] user_id={$user->ID} | senha=$password | hmac=$hmac | match=true | LOGIN OK\n", FILE_APPEND);
    } else {
        $match = password_verify($password, $user->user_pass);
        file_put_contents($logFile, "[$timestamp] [WordPress - AUTH] user_id={$user->ID} | senha=$password | match=" . ($match ? 'true' : 'false') . PHP_EOL, FILE_APPEND);
    }

    return $user;
}, 10, 2);

// ✅ Impede que o WordPress reescreva a senha do usuário migrado
add_filter('password_needs_rehash', function ($needs_rehash, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);
    if (!empty($salt)) {
        return false;
    }
    return $needs_rehash;
}, 10, 3);
