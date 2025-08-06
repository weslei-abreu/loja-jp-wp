<?php
/*
Plugin Name: J1 Classificados
Description: Adiciona um post type de classificados integrado ao Dokan.
Version: 1.1.0
Author: Wecod
*/

if (!defined('ABSPATH')) exit;

// 🔹 1. Registrar o post type "classified"
add_action('init', function () {
    $labels = [
        'name' => 'Classificados',
        'singular_name' => 'Classificado',
        'add_new' => 'Adicionar Novo',
        'add_new_item' => 'Adicionar Novo Classificado',
        'edit_item' => 'Editar Classificado',
        'new_item' => 'Novo Classificado',
        'view_item' => 'Ver Classificado',
        'search_items' => 'Buscar Classificados',
        'not_found' => 'Nenhum classificado encontrado',
        'not_found_in_trash' => 'Nenhum classificado na lixeira',
        'menu_name' => 'Classificados'
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'classificados'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields'],
        'taxonomies' => ['product_cat'],
        'show_in_rest' => true,
        'capability_type' => 'post'
    ];

    register_post_type('classified', $args);
});

// 🔹 2. Adicionar query var para o Dokan
add_filter('dokan_query_var_filter', function ($query_vars) {
    $query_vars[] = 'classifieds';
    return $query_vars;
});

// 🔹 3. Adicionar menu no dashboard Dokan
add_filter('dokan_get_dashboard_nav', function ($urls) {
    $urls['classifieds'] = [
        'title' => __('Classificados', 'j1_classificados'),
        'icon'  => '<i class="fas fa-bullhorn"></i>',
        'url'   => dokan_get_navigation_url('classifieds'),
        'pos'   => 55
    ];
    return $urls;
});

// 🔹 4. Endpoint para listar/adicionar/editar/excluir classificados
add_action('dokan_load_custom_template', function ($query_vars) {
    if (isset($query_vars['classifieds'])) {
        $action = $_GET['action'] ?? '';
        $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Excluir classificado
        if ($action === 'delete' && $post_id && wp_verify_nonce($_GET['_wpnonce'], 'delete_classified_' . $post_id)) {
            wp_delete_post($post_id, true);
            wp_redirect(dokan_get_navigation_url('classifieds'));
            exit;
        }

        // Formulário de adicionar/editar
        if ($action === 'add' || $action === 'edit') {
            include dirname(__FILE__) . '/templates/dashboard-classifieds-edit.php';
            exit;
        }

        // Listagem padrão
        include dirname(__FILE__) . '/templates/dashboard-classifieds.php';
        exit;
    }
    return $query_vars;
}, 10, 1);

// 🔹 5. Salvar ou atualizar classificado
add_action('template_redirect', function () {
    if (!isset($_POST['classified_nonce']) || !wp_verify_nonce($_POST['classified_nonce'], 'save_classified')) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) return;

    $post_id = intval($_POST['classified_id'] ?? 0);
    $title = sanitize_text_field($_POST['classified_title']);
    $content = wp_kses_post($_POST['classified_content']);
    $price = floatval($_POST['classified_price']);
    $is_job = isset($_POST['classified_is_job']) ? '1' : '0';
    $conditions = sanitize_text_field($_POST['classified_conditions'] ?? '');
    $categories = isset($_POST['classified_category']) ? array_map('intval', $_POST['classified_category']) : [];

    // Verificar permissões
    if ($post_id && !dokan_is_product_author($post_id)) {
        wp_die(__('Access Denied', 'j1_classificados'));
    }

    $post_data = [
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'classified',
        'post_author'  => $user_id,
    ];

    if ($post_id) {
        $post_data['ID'] = $post_id;
        $post_id = wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    if ($post_id) {
        update_post_meta($post_id, '_price', $price);
        update_post_meta($post_id, '_classified_is_job', $is_job);
        update_post_meta($post_id, '_classified_conditions', $conditions);
        wp_set_post_terms($post_id, $categories, 'product_cat', false);

        // Upload imagem destacada via media library
        if (!empty($_POST['feat_image_id'])) {
            $thumbnail_id = intval($_POST['feat_image_id']);
            if ($thumbnail_id > 0) {
                set_post_thumbnail($post_id, $thumbnail_id);
            }
        }

        // Upload galeria via media library
        if (!empty($_POST['product_image_gallery'])) {
            $gallery_ids = array_filter(array_map('intval', explode(',', $_POST['product_image_gallery'])));
            if (!empty($gallery_ids)) {
                update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery_ids));
            }
        }
    }

    wp_redirect(add_query_arg(['message' => 'success'], dokan_get_navigation_url('classifieds')));
    exit;
});

// 🔹 6. Template único para classificado
add_filter('template_include', function ($template) {
    if (is_singular('classified')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-classified.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
});

// 🔹 7. Colunas do admin
add_filter('manage_edit-classified_columns', function ($columns) {
    $new_columns = [];
    if (isset($columns['cb'])) $new_columns['cb'] = $columns['cb'];
    $new_columns['thumbnail'] = __('Imagem', 'j1_classificados');
    foreach ($columns as $key => $title) {
        if ($key !== 'cb' && $key !== 'date') $new_columns[$key] = $title;
    }
    if (isset($columns['date'])) $new_columns['date'] = $columns['date'];
    return $new_columns;
});

add_action('manage_classified_posts_custom_column', function ($column, $post_id) {
    if ($column === 'thumbnail') {
        $thumb = get_the_post_thumbnail($post_id, [60, 60]);
        echo $thumb ?: '—';
    }
}, 10, 2);

// 🔹 8. Garantir suporte a thumbnails
add_action('after_setup_theme', function () {
    add_post_type_support('classified', 'thumbnail');
});

// 🔹 9. Dynamic Tag do Elementor para exibir o preço do classificado
add_action('elementor/dynamic_tags/register', function($dynamic_tags) {

    class Elementor_Classified_Price_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_name() {
            return 'classified-price';
        }

        public function get_title() {
            return 'Preço Classificado';
        }

        public function get_group() {
            return 'post';
        }

        public function get_categories() {
            return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
        }

        public function render() {
            $price = get_post_meta(get_the_ID(), '_price', true);
            echo $price ? '¥ ' . number_format_i18n($price) : '';
        }
    }

    $dynamic_tags->register_tag('Elementor_Classified_Price_Tag');
});

// ✅ Carregar CSS do fallback da single-classified
add_action( 'wp_enqueue_scripts', function () {
    if ( is_singular( 'classified' ) ) {
        wp_enqueue_style(
            'j1-classificados-style',
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            [],
            '1.0'
        );
    }
});

// ✅ Carregar scripts para o dashboard de classificados
add_action( 'wp_enqueue_scripts', function () {
    // Carregar quando estivermos no dashboard de classificados (add ou edit)
    if ( dokan_is_seller_dashboard() && 
         (isset( $_GET['classifieds'] ) || 
          (isset( $_GET['action'] ) && in_array( $_GET['action'], ['add', 'edit'] ))) ) {
        
        wp_enqueue_media();
        wp_enqueue_script(
            'j1-classificados-admin',
            plugin_dir_url( __FILE__ ) . 'assets/js/admin.js',
            ['jquery', 'media-upload'],
            '1.0',
            true
        );
        
        // Adicionar dados localizados para o JavaScript
        wp_localize_script('j1-classificados-admin', 'j1_classificados_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('j1_classificados_nonce'),
            'strings' => array(
                'select_featured_image' => __('Selecionar Imagem Destacada', 'j1_classificados'),
                'select_gallery_images' => __('Selecionar Imagens da Galeria', 'j1_classificados'),
                'delete_image' => __('Excluir imagem', 'j1_classificados'),
                'select_categories' => __('Selecione categorias', 'j1_classificados')
            )
        ));
    }
});

// ✅ Permitir que o Elementor edite classifieds
add_action('init', function() {
    add_post_type_support('classified', 'elementor');
});

// ✅ Forçar suporte ao Elementor para este post type
add_filter('elementor/utils/is_post_type_support', function($is_supported, $post_type) {
    if ($post_type === 'classified') {
        return true;
    }
    return $is_supported;
}, 10, 2);

// ✅ Corrigir URLs malformadas de anexos
add_filter('wp_get_attachment_url', function($url, $attachment_id) {
    // DEBUG: Vamos investigar onde está acontecendo a concatenação
    if (strpos($url, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        error_log("DEBUG: URL malformada detectada em wp_get_attachment_url - Attachment ID: " . $attachment_id . " - URL: " . $url);
        $url = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $url);
        error_log("DEBUG: URL corrigida: " . $url);
    }
    return $url;
}, 10, 2);

// ✅ Corrigir URLs malformadas em wp_get_attachment_image_src
add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size, $icon) {
    if ($image && isset($image[0])) {
        if (strpos($image[0], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
            error_log("DEBUG: URL malformada detectada em wp_get_attachment_image_src - Attachment ID: " . $attachment_id . " - Size: " . $size . " - URL: " . $image[0]);
            $image[0] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $image[0]);
            error_log("DEBUG: URL corrigida: " . $image[0]);
        }
    }
    return $image;
}, 10, 4);

// ✅ Corrigir URLs malformadas em wp_get_attachment_image
add_filter('wp_get_attachment_image', function($html, $attachment_id, $size, $icon, $attr) {
    if (strpos($html, 'https://loja.jp/wp-content/uploads/https:/loja.jp') !== false) {
        $html = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $html);
    }
    return $html;
}, 10, 5);

// ✅ Corrigir URLs malformadas em wp_get_attachment_metadata
add_filter('wp_get_attachment_metadata', function($data, $attachment_id) {
    if ($data && isset($data['file'])) {
        if (strpos($data['file'], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
            $data['file'] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $data['file']);
        }
    }
    return $data;
}, 10, 2);

// ✅ Corrigir URLs malformadas em wp_attachment_is_image
add_filter('wp_attachment_is_image', function($result, $attachment_id) {
    // Este filtro é chamado antes de wp_get_attachment_url, então vamos garantir que a URL esteja correta
    $url = wp_get_attachment_url($attachment_id);
    if (strpos($url, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        // Forçar a correção da URL
        $corrected_url = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $url);
        update_attached_file($attachment_id, str_replace(wp_upload_dir()['baseurl'], '', $corrected_url));
    }
    return $result;
}, 10, 2);

// ✅ Corrigir URLs malformadas em wp_upload_dir
add_filter('upload_dir', function($uploads) {
    if (isset($uploads['url']) && strpos($uploads['url'], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $uploads['url'] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $uploads['url']);
    }
    if (isset($uploads['baseurl']) && strpos($uploads['baseurl'], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $uploads['baseurl'] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $uploads['baseurl']);
    }
    return $uploads;
}, 10, 1);

// ✅ Corrigir URLs malformadas em wp_get_attachment_image_attributes
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    if (isset($attr['src']) && strpos($attr['src'], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $attr['src'] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $attr['src']);
    }
    return $attr;
}, 10, 3);

// ✅ Corrigir URLs malformadas em wp_get_attachment_thumb_url
add_filter('wp_get_attachment_thumb_url', function($url, $attachment_id) {
    if (strpos($url, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $url = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $url);
    }
    return $url;
}, 10, 2);

// ✅ Corrigir URLs malformadas em wp_get_attachment_image_srcset
add_filter('wp_calculate_image_srcset', function($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    if (is_array($sources)) {
        foreach ($sources as $width => $source) {
            if (isset($source['url']) && strpos($source['url'], 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
                $sources[$width]['url'] = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $source['url']);
            }
        }
    }
    return $sources;
}, 10, 5);

// ✅ Corrigir URLs malformadas em wp_get_attachment_image_sizes
add_filter('wp_calculate_image_sizes', function($sizes, $size, $image_src, $image_meta, $attachment_id) {
    if (strpos($image_src, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $image_src = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $image_src);
    }
    return $sizes;
}, 10, 5);

// ✅ Função para limpar URLs malformadas no banco de dados
function j1_classificados_clean_malformed_urls() {
    global $wpdb;
    
    // DEBUG: Vamos investigar o que está no banco de dados
    $malformed_attachments = $wpdb->get_results("
        SELECT post_id, meta_key, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key IN ('_wp_attached_file', '_wp_attachment_metadata', '_product_image_gallery')
        AND meta_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
        LIMIT 10
    ");
    
    error_log("DEBUG: Encontrados " . count($malformed_attachments) . " registros com URLs malformadas no banco:");
    foreach ($malformed_attachments as $attachment) {
        error_log("DEBUG: Post ID: " . $attachment->post_id . " - Meta Key: " . $attachment->meta_key . " - Meta Value: " . $attachment->meta_value);
    }
    
    // Limpar URLs malformadas na tabela postmeta
    $wpdb->query("
        UPDATE {$wpdb->postmeta} 
        SET meta_value = REPLACE(meta_value, 'https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads')
        WHERE meta_key IN ('_wp_attached_file', '_wp_attachment_metadata', '_product_image_gallery')
        AND meta_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
    ");
    
    // Limpar URLs malformadas na tabela posts (guid)
    $wpdb->query("
        UPDATE {$wpdb->posts} 
        SET guid = REPLACE(guid, 'https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads')
        WHERE post_type = 'attachment'
        AND guid LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
    ");
    
    // Limpar URLs malformadas em opções do WordPress
    $wpdb->query("
        UPDATE {$wpdb->options} 
        SET option_value = REPLACE(option_value, 'https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads')
        WHERE option_name LIKE '%upload%'
        AND option_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
    ");
    
    // Limpar cache de transients
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_%'
        AND option_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
    ");
    
    // Limpar cache de object cache se existir
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    return true;
}

// ✅ Executar limpeza de URLs malformadas na ativação do plugin
register_activation_hook(__FILE__, 'j1_classificados_clean_malformed_urls');

// ✅ Adicionar ação para limpar URLs malformadas via AJAX
add_action('wp_ajax_j1_classificados_clean_urls', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'j1_classificados_nonce')) {
        wp_die('Security check failed');
    }
    
    j1_classificados_clean_malformed_urls();
    wp_send_json_success('URLs malformadas foram corrigidas');
});

// ✅ Executar limpeza de URLs malformadas quando necessário
add_action('init', function() {
    // Verificar se há URLs malformadas e limpar se necessário
    if (isset($_GET['clean_malformed_urls']) && current_user_can('manage_options')) {
        j1_classificados_clean_malformed_urls();
        wp_redirect(remove_query_arg('clean_malformed_urls'));
        exit;
    }
    
    // ✅ Forçar limpeza automática se detectar URLs malformadas
    if (isset($_GET['classifieds']) && current_user_can('manage_options')) {
        global $wpdb;
        $malformed_count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->postmeta} 
            WHERE meta_key IN ('_wp_attached_file', '_wp_attachment_metadata', '_product_image_gallery')
            AND meta_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
        ");
        
        if ($malformed_count > 0) {
            // Limpar URLs malformadas automaticamente
            j1_classificados_clean_malformed_urls();
        }
    }
});

// ✅ Adicionar filtro para corrigir URLs em tempo real
add_filter('wp_get_attachment_url', function($url, $attachment_id) {
    // Corrigir URLs malformadas em tempo real
    if (strpos($url, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
        $corrected_url = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $url);
        
        // Atualizar o banco de dados com a URL corrigida
        global $wpdb;
        $wpdb->update(
            $wpdb->postmeta,
            ['meta_value' => $corrected_url],
            [
                'post_id' => $attachment_id,
                'meta_key' => '_wp_attached_file'
            ]
        );
        
        return $corrected_url;
    }
    return $url;
}, 5, 2); // Prioridade 5 para executar antes dos outros filtros

// ✅ Função para investigar a origem das URLs malformadas
function j1_classificados_investigate_malformed_urls() {
    global $wpdb;
    
    error_log("=== INVESTIGAÇÃO DE URLs MALFORMADAS ===");
    
    // 1. Verificar configurações de upload
    $upload_dir = wp_upload_dir();
    error_log("DEBUG: Upload URL: " . $upload_dir['url']);
    error_log("DEBUG: Upload Base URL: " . $upload_dir['baseurl']);
    error_log("DEBUG: Upload Path: " . $upload_dir['path']);
    error_log("DEBUG: Upload Base Path: " . $upload_dir['basedir']);
    
    // 2. Verificar configurações do site
    error_log("DEBUG: Site URL: " . get_site_url());
    error_log("DEBUG: Home URL: " . get_home_url());
    error_log("DEBUG: WordPress URL: " . get_option('siteurl'));
    
    // 3. Verificar anexos com URLs malformadas
    $malformed_attachments = $wpdb->get_results("
        SELECT p.ID, p.post_title, p.guid, pm.meta_key, pm.meta_value
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'attachment'
        AND (p.guid LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%'
             OR pm.meta_value LIKE '%https://loja.jp/wp-content/uploads/https:/loja.jp%')
        LIMIT 5
    ");
    
    error_log("DEBUG: Encontrados " . count($malformed_attachments) . " anexos com URLs malformadas:");
    foreach ($malformed_attachments as $attachment) {
        error_log("DEBUG: Attachment ID: " . $attachment->ID);
        error_log("DEBUG: Attachment Title: " . $attachment->post_title);
        error_log("DEBUG: Attachment GUID: " . $attachment->guid);
        error_log("DEBUG: Meta Key: " . $attachment->meta_key);
        error_log("DEBUG: Meta Value: " . $attachment->meta_value);
        error_log("---");
    }
    
    error_log("=== FIM DA INVESTIGAÇÃO ===");
}

// ✅ Executar investigação quando necessário
add_action('init', function() {
    if (isset($_GET['investigate_urls']) && current_user_can('manage_options')) {
        j1_classificados_investigate_malformed_urls();
        wp_die('Investigation complete. Check error log.');
    }
});
