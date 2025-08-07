<?php
/*
Plugin Name: Fix Password Check ProBid + WP
Description: Suporta login usuários migrados ProBid com salt e usuários WordPress normais, incluindo alteração de senha via painel.
Version: 1.4
Author: Weslei
*/

// Intercepta validação de senha no WordPress
add_filter('check_password', function ($check, $password, $hash, $user_id) {
    $logFile = '/tmp/debug-login.log';
    $timestamp = date('Y-m-d H:i:s');

    if (!$user_id || !is_numeric($user_id)) {
        error_log("[$timestamp] [AUTH ERROR] user_id inválido");
        return $check;
    }

    $salt = get_user_meta($user_id, 'probid_salt', true);
    file_put_contents($logFile, "[$timestamp] [check_password] user_id=$user_id | probid_salt=" . ($salt ?: 'vazio') . " | hash_prefix=" . substr($hash, 0, 4) . "\n", FILE_APPEND);

    // IMPORTANTE: Se o hash tem prefixo $wp$, deixa o WordPress 6.8+ tratar
    if (strpos($hash, '$wp$') === 0) {
        file_put_contents($logFile, "[$timestamp] [check_password] Hash \$wp\$ detectado - deixando WordPress 6.8+ tratar | user_id=$user_id\n", FILE_APPEND);
        
        // Teste adicional: verificar se o WordPress 6.8+ consegue processar
        $password_to_verify = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));
        $hash_to_verify = substr($hash, 3);
        $test_check = password_verify($password_to_verify, $hash_to_verify);
        
        file_put_contents($logFile, "[$timestamp] [check_password] Teste interno \$wp\$: " . ($test_check ? 'OK' : 'FALHOU') . " | user_id=$user_id\n", FILE_APPEND);
        
        // Retorna o resultado do teste interno para hashes $wp$
        return $test_check;
    }

    // Só valida manualmente se tem probid_salt
    if (!empty($salt)) {
        // Usuário ProBid: primeiro faz HMAC + bcrypt
        $hmac = hash_hmac('sha256', $password, $salt);

        if (password_verify($hmac, $hash)) {
            file_put_contents($logFile, "[$timestamp] [check_password] ProBid OK | user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        // Caso senha já atualizada via WP, tenta bcrypt direto
        if (password_verify($password, $hash)) {
            file_put_contents($logFile, "[$timestamp] [check_password] WP fallback OK (senha atualizada) | user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        file_put_contents($logFile, "[$timestamp] [check_password] ProBid FAIL | user_id=$user_id\n", FILE_APPEND);
        return false;
    }

    // Para usuários sem salt e sem hash $wp$, deixa o WordPress validar normalmente
    file_put_contents($logFile, "[$timestamp] [check_password] Sem salt e sem \$wp\$ - deixando WP validar | user_id=$user_id\n", FILE_APPEND);
    return $check; // Retorna o valor original do WordPress
}, 20, 4);

// Bloqueia login se senha incorreta para usuários com salt ProBid
add_filter('wp_authenticate_user', function ($user, $password) {
    $logFile = '/tmp/debug-login.log';
    $timestamp = date('Y-m-d H:i:s');

    if (is_wp_error($user)) {
        // Se já é erro, retorna direto
        return $user;
    }

    $salt = get_user_meta($user->ID, 'probid_salt', true);
    file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] user_id={$user->ID} | probid_salt=" . ($salt ?: 'vazio') . " | hash_prefix=" . substr($user->user_pass, 0, 4) . "\n", FILE_APPEND);

    // Se tem hash $wp$, deixa o WordPress 6.8+ tratar
    if (strpos($user->user_pass, '$wp$') === 0) {
        file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] Hash \$wp\$ detectado - deixando WordPress 6.8+ tratar | user_id={$user->ID}\n", FILE_APPEND);
        
        // Teste adicional: verificar se o WordPress 6.8+ consegue processar
        $password_to_verify = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));
        $hash_to_verify = substr($user->user_pass, 3);
        $test_check = password_verify($password_to_verify, $hash_to_verify);
        
        file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] Teste interno \$wp\$: " . ($test_check ? 'OK' : 'FALHOU') . " | user_id={$user->ID}\n", FILE_APPEND);
        
        // Se o teste interno falhou, retorna erro
        if (!$test_check) {
            file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] AUTH FAIL \$wp\$ | user_id={$user->ID}\n", FILE_APPEND);
            return new WP_Error('authentication_failed', __('Incorrect password.'));
        }
        
        return $user; // Deixa o WordPress validar
    }

    // Só valida manualmente se tem probid_salt
    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);

        if (password_verify($hmac, $user->user_pass) || password_verify($password, $user->user_pass)) {
            file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] AUTH OK user_id={$user->ID}\n", FILE_APPEND);
            return $user;
        }

        file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] AUTH FAIL user_id={$user->ID}\n", FILE_APPEND);
        return new WP_Error('authentication_failed', __('Incorrect password.'));
    }

    // Para usuários sem salt, deixa o WordPress validar
    file_put_contents($logFile, "[$timestamp] [wp_authenticate_user] Sem salt - deixando WP validar | user_id={$user->ID}\n", FILE_APPEND);
    return $user;
}, 20, 2);

// Evita rehash automático para usuários com salt ProBid
add_filter('password_needs_rehash', function ($needs_rehash, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);

    // Se senha bate com password digitada, permite rehash (usuários WP normais que trocaram senha)
    if (!empty($_POST['pwd']) && password_verify($_POST['pwd'], $hash)) {
        return true;
    }

    // Para usuários ProBid com salt, não rehash
    return empty($salt) ? $needs_rehash : false;
}, 20, 3);

// Remove salt após troca de senha via painel
add_action('profile_update', function ($user_id) {
    if (!empty($_POST['pass1'])) {
        delete_user_meta($user_id, 'probid_salt');
        error_log("[fix-password-check] Salt removido após troca de senha para user_id={$user_id}");
    }
});
