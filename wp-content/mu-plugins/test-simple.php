<?php
/*
Plugin Name: Test Simple
Description: Teste simples para verificar se mu-plugins estão carregando
Version: 1.0
Author: Weslei
*/

// Teste simples
add_action('init', function() {
    if (isset($_GET['test_simple'])) {
        echo "<h2>Teste Simples - Mu-Plugin Funcionando!</h2>";
        echo "<p>✅ Mu-plugins estão carregando corretamente</p>";
        echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
        exit;
    }
}); 