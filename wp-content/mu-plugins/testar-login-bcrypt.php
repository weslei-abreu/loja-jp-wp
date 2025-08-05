<?php
add_action('init', function () {
    if (!isset($_GET['test_bcrypt_login']) || $_GET['token'] !== 'segreddao123') {
        return;
    }

    $senha = '12341234';
    $hash  = '$2y$10$J6Yu.G7.SGK1/bC99T6vYOHaUcK04gu.d0o0NzfOrtdUx5Y7gD43u';
    $user_id = 182997;

    $salt = get_user_meta($user_id, 'probid_salt', true);

    if ($salt) {
        $senha_hmac = hash_hmac('sha256', $senha, $salt);
    } else {
        $senha_hmac = $senha;
    }

    $wp_result  = wp_check_password($senha_hmac, $hash);
    $php_result = password_verify($senha_hmac, $hash);

    echo "<pre>";
    echo "🔑 Salt: " . ($salt ?: 'não encontrado') . "\n";
    echo "🧪 HMAC: " . $senha_hmac . "\n";
    echo "🔐 wp_check_password: ";
    var_dump($wp_result);
    echo "🔒 password_verify (PHP): ";
    var_dump($php_result);
    echo "</pre>";
    exit;
});
