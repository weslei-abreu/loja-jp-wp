<?php
/*
Plugin Name: Test WP Hash
Description: Teste para entender como o WordPress 6.8+ processa hashes $wp$
Version: 1.0
Author: Weslei
*/

// Adiciona um hook para testar o processamento de hashes $wp$
add_action('init', function() {
    if (isset($_GET['test_wp_hash']) && current_user_can('manage_options')) {
        $password = 'test123';
        $hash = '$wp$2y$10$1Z2E2lWPgKW6oGJ7CcdGce4jL6CFSoUUiDf4Pe4j9/IhGEFwBoyQK';
        
        echo "<h2>Teste de Hash \$wp\$</h2>";
        echo "<p>Senha: $password</p>";
        echo "<p>Hash: $hash</p>";
        
        // Teste 1: Verificar se é hash $wp$
        if (strpos($hash, '$wp$') === 0) {
            echo "<p>✅ Hash \$wp\$ detectado</p>";
            
            // Teste 2: Processar como WordPress 6.8+
            $password_to_verify = base64_encode(hash_hmac('sha384', $password, 'wp-sha384', true));
            $hash_to_verify = substr($hash, 3);
            
            echo "<p>Password to verify: " . substr($password_to_verify, 0, 20) . "...</p>";
            echo "<p>Hash to verify: " . substr($hash_to_verify, 0, 20) . "...</p>";
            
            $check = password_verify($password_to_verify, $hash_to_verify);
            echo "<p>Resultado: " . ($check ? '✅ OK' : '❌ FALHOU') . "</p>";
            
            // Teste 3: Usar wp_check_password
            $wp_check = wp_check_password($password, $hash);
            echo "<p>wp_check_password: " . ($wp_check ? '✅ OK' : '❌ FALHOU') . "</p>";
        } else {
            echo "<p>❌ Hash \$wp\$ não detectado</p>";
        }
        
        exit;
    }
}); 