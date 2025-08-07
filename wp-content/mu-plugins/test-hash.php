<?php
/*
Plugin Name: Test Hash
Description: Teste para verificar processamento de hash $wp$
Version: 1.1
Author: Weslei
*/

// Teste direto do hash $wp$
add_action('init', function() {
    if (isset($_GET['test_hash'])) {
        $password = 'rUrJOPciPz&EjZvnh(vnBghb';
        $hash = '$wp$2y$10$1Z2E2lWPgKW6oGJ7CcdGce4jL6CFSoUUiDf4Pe4j9/IhGEFwBoyQK';
        
        echo "<h2>Teste Direto do Hash \$wp\$</h2>";
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
            echo "<p>Resultado password_verify: " . ($check ? '✅ OK' : '❌ FALHOU') . "</p>";
            
            // Teste 3: Usar wp_check_password
            $wp_check = wp_check_password($password, $hash);
            echo "<p>wp_check_password: " . ($wp_check ? '✅ OK' : '❌ FALHOU') . "</p>";
            
            // Teste 4: Verificar se o usuário existe
            $user = get_user_by('ID', 1);
            if ($user) {
                echo "<p>Usuário encontrado: {$user->user_login} (ID: {$user->ID})</p>";
                echo "<p>Hash do usuário: " . substr($user->user_pass, 0, 20) . "...</p>";
                
                // Teste 5: Verificar senha do usuário
                $user_check = wp_check_password($password, $user->user_pass, $user->ID);
                echo "<p>Verificação da senha do usuário: " . ($user_check ? '✅ OK' : '❌ FALHOU') . "</p>";
            } else {
                echo "<p>❌ Usuário não encontrado</p>";
            }
        } else {
            echo "<p>❌ Hash \$wp\$ não detectado</p>";
        }
        
        exit;
    }
}); 