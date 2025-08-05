<?php
/*
Plugin Name: Testa Coluna Classified
Description: Apenas para testar hooks das colunas.
Version: 1.0
Author: Wecod
*/

add_action('admin_init', function () {
    error_log("🔥 admin_init disparado");

    add_filter('manage_edit-classified_columns', function ($columns) {
        error_log("🔥 manage_edit-classified_columns executado");
        $columns['thumb_test'] = 'Thumb Teste';
        return $columns;
    });

    add_action('manage_classified_posts_custom_column', function ($column, $post_id) {
        if ($column === 'thumb_test') {
            error_log("✅ manage_classified_posts_custom_column executado para {$post_id}");
            echo '🖼';
        }
    }, 10, 2);
});
