<?php
add_filter('check_password', function ($check, $password, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);
    $timestamp = date('Y-m-d H:i:s');
    $logFile = '/tmp/debug-login.log';
    file_put_contents('/tmp/check_password_hook.log', "[$timestamp] Hook check_password | user_id=$user_id\n", FILE_APPEND);

    if (!empty($salt)) {
        $hmac = hash_hmac('sha256', $password, $salt);

        if (password_verify($hmac, $hash)) {
            file_put_contents($logFile, "[$timestamp] [check_password] ProBid OK | user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        if (password_verify($password, $hash)) {
            file_put_contents($logFile, "[$timestamp] [check_password] WP fallback OK | user_id=$user_id\n", FILE_APPEND);
            return true;
        }

        file_put_contents($logFile, "[$timestamp] [check_password] FAIL | user_id=$user_id\n", FILE_APPEND);
        return false;
    }

    $match = password_verify($password, $hash);
    file_put_contents($logFile, "[$timestamp] [check_password] WP only user_id=$user_id | match=" . ($match ? 'true' : 'false') . "\n", FILE_APPEND);
    return $match;
}, 10, 4);

add_filter('password_needs_rehash', function ($needs_rehash, $hash, $user_id) {
    $salt = get_user_meta($user_id, 'probid_salt', true);
    return empty($salt) ? $needs_rehash : false;
}, 10, 3);

add_action('profile_update', function ($user_id) {
    if (!empty($_POST['pass1'])) {
        delete_user_meta($user_id, 'probid_salt');
        error_log("[fix-password-check] Salt removido após troca de senha para user_id={$user_id}");
    }
});
