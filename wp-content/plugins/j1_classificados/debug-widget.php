<?php
/**
 * Script de debug para o widget de mensagem
 * 
 * @package J1_Classificados
 */

if (!defined('ABSPATH')) exit;

// Adicionar ação para debug
add_action('wp_footer', function() {
    if (is_singular('classified')) {
        ?>
        <script>
        console.log('🔍 Debug J1 Message Widget:');
        console.log('- Página de classificado detectada');
        console.log('- jQuery carregado:', typeof jQuery !== 'undefined');
        console.log('- j1_message_ajax disponível:', typeof j1_message_ajax !== 'undefined');
        
        if (typeof j1_message_ajax !== 'undefined') {
            console.log('- AJAX URL:', j1_message_ajax.ajax_url);
            console.log('- Nonce:', j1_message_ajax.nonce);
        }
        
        // Verificar se o widget está presente
        var widget = document.querySelector('.j1-message-widget');
        console.log('- Widget presente:', widget !== null);
        
        if (widget) {
            var button = widget.querySelector('.j1-message-button');
            var modal = widget.querySelector('.j1-message-modal');
            console.log('- Botão presente:', button !== null);
            console.log('- Modal presente:', modal !== null);
            
            if (button) {
                console.log('- Data classified-id:', button.dataset.classifiedId);
            }
        }
        </script>
        <?php
    }
}); 