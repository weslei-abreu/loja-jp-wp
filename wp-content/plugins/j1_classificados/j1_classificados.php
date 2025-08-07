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
    // Carregar quando estivermos no dashboard de classificados (listagem, add ou edit)
    if ( dokan_is_seller_dashboard() && 
         (isset( $_GET['classifieds'] ) || 
          (isset( $_GET['action'] ) && in_array( $_GET['action'], ['add', 'edit'] ))) ) {
        
        wp_enqueue_media();
        
        // Carregar CSS específico para a página de edição
        wp_enqueue_style(
            'j1-classificados-edit-style',
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            [],
            '1.1'
        );
        
        wp_enqueue_script(
            'j1-classificados-admin',
            plugin_dir_url( __FILE__ ) . 'assets/js/admin.js',
            ['jquery', 'media-upload'],
            '1.1',
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

// ✅ Integração com Elementor para widgets
add_action('plugins_loaded', function() {
    if (did_action('elementor/loaded')) {
        require_once plugin_dir_path(__FILE__) . 'includes/elementor/elementor-integration.php';
    }
});

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

// ✅ Sistema de visualizações para classificados
function j1_classificados_increment_views($post_id) {
    if (!$post_id) return;
    
    $current_views = get_post_meta($post_id, '_classified_views', true);
    $current_views = $current_views ? intval($current_views) : 0;
    $new_views = $current_views + 1;
    
    update_post_meta($post_id, '_classified_views', $new_views);
    return $new_views;
}

function j1_classificados_get_views($post_id) {
    if (!$post_id) return 0;
    
    $views = get_post_meta($post_id, '_classified_views', true);
    return $views ? intval($views) : 0;
}

// ✅ Hook para incrementar visualizações quando alguém acessa o classificado
add_action('wp_head', function() {
    if (is_singular('classified')) {
        $post_id = get_the_ID();
        if ($post_id) {
            j1_classificados_increment_views($post_id);
        }
    }
});

// ✅ AJAX para incrementar visualizações via JavaScript (opcional)
add_action('wp_ajax_j1_classificados_increment_views', function() {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id && get_post_type($post_id) === 'classified') {
        $new_views = j1_classificados_increment_views($post_id);
        wp_send_json_success(['views' => $new_views]);
    } else {
        wp_send_json_error('Invalid post ID');
    }
});

add_action('wp_ajax_nopriv_j1_classificados_increment_views', function() {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id && get_post_type($post_id) === 'classified') {
        $new_views = j1_classificados_increment_views($post_id);
        wp_send_json_success(['views' => $new_views]);
    } else {
        wp_send_json_error('Invalid post ID');
    }
});

// ✅ AJAX para obter ID do post pela URL
add_action('wp_ajax_j1_classificados_get_post_id_by_url', function() {
    $url = sanitize_url($_POST['url'] ?? '');
    if ($url) {
        $post_id = url_to_postid($url);
        if ($post_id && get_post_type($post_id) === 'classified') {
            wp_send_json_success(['post_id' => $post_id]);
        } else {
            wp_send_json_error('Post not found');
        }
    } else {
        wp_send_json_error('Invalid URL');
    }
});

add_action('wp_ajax_nopriv_j1_classificados_get_post_id_by_url', function() {
    $url = sanitize_url($_POST['url'] ?? '');
    if ($url) {
        $post_id = url_to_postid($url);
        if ($post_id && get_post_type($post_id) === 'classified') {
            wp_send_json_success(['post_id' => $post_id]);
        } else {
            wp_send_json_error('Post not found');
        }
    } else {
        wp_send_json_error('Invalid URL');
    }
});

// ✅ Modificar o widget de chat do Dokan para funcionar em páginas de classificados
add_action('wp_loaded', function() {
    // ✅ Filtro para permitir que o widget de chat funcione em páginas de classificados
    add_filter('dokan_is_store_page', function($is_store_page) {
        // Se já é uma página de loja, retorna true
        if ($is_store_page) {
            return true;
        }
        
        // Se é uma página de classificado, verifica se tem autor/vendedor
        if (is_singular('classified')) {
            $post_id = get_the_ID();
            if ($post_id) {
                $author_id = get_post_field('post_author', $post_id);
                if ($author_id && dokan_is_user_seller($author_id)) {
                    return true;
                }
            }
        }
        
        return $is_store_page;
    });
    
    // ✅ Filtro para obter o ID da loja em páginas de classificados
    add_filter('dokan_elementor_store_data_id', function($store_id) {
        if (!$store_id && is_singular('classified')) {
            $post_id = get_the_ID();
            if ($post_id) {
                $author_id = get_post_field('post_author', $post_id);
                if ($author_id && dokan_is_user_seller($author_id)) {
                    return $author_id;
                }
            }
        }
        return $store_id;
    });
    
    // ✅ Filtro para obter dados da loja em páginas de classificados
    add_filter('dokan_elementor_store_data', function($store_data) {
        if (empty($store_data['id']) && is_singular('classified')) {
            $post_id = get_the_ID();
            if ($post_id) {
                $author_id = get_post_field('post_author', $post_id);
                if ($author_id && dokan_is_user_seller($author_id)) {
                    $vendor = dokan()->vendor->get($author_id);
                    if ($vendor) {
                        $store_data['id'] = $author_id;
                        $store_data['name'] = $vendor->get_shop_name();
                        $store_data['banner'] = [
                            'id' => $vendor->get_banner_id(),
                            'url' => $vendor->get_banner(),
                        ];
                        $store_data['profile_picture'] = [
                            'id' => $vendor->get_avatar_id(),
                            'url' => $vendor->get_avatar(),
                        ];
                        $store_data['address'] = dokan_get_seller_short_address($author_id, false);
                        $store_data['phone'] = $vendor->get_phone();
                        $store_data['email'] = $vendor->show_email() ? $vendor->get_email() : '';
                    }
                }
            }
        }
        return $store_data;
    });
    
    // ✅ Filtro para modificar a verificação do widget de chat
    add_filter('elementor/frontend/widget/should_render', function($should_render, $widget) {
        if ($widget->get_name() === 'dokan-store-live-chat-button') {
            // Se é uma página de classificado, verifica se tem autor/vendedor
            if (is_singular('classified')) {
                $post_id = get_the_ID();
                if ($post_id) {
                    $author_id = get_post_field('post_author', $post_id);
                    if ($author_id && dokan_is_user_seller($author_id)) {
                        return true;
                    }
                }
            }
        }
        return $should_render;
    }, 10, 2);
});

// ✅ Sistema de mensagens para classificados
function j1_classificados_create_messages_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'j1_classified_messages';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        classified_id bigint(20) NOT NULL,
        sender_name varchar(100) NOT NULL,
        sender_email varchar(100) NOT NULL,
        sender_id bigint(20) DEFAULT NULL,
        receiver_id bigint(20) NOT NULL,
        message text NOT NULL,
        status varchar(20) DEFAULT 'unread',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        parent_id mediumint(9) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY classified_id (classified_id),
        KEY sender_id (sender_id),
        KEY receiver_id (receiver_id),
        KEY status (status),
        KEY parent_id (parent_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ✅ Criar tabela na ativação do plugin
register_activation_hook(__FILE__, 'j1_classificados_create_messages_table');

// ✅ AJAX para enviar mensagem
add_action('wp_ajax_j1_send_message', 'j1_classificados_send_message');
add_action('wp_ajax_nopriv_j1_send_message', 'j1_classificados_send_message');

function j1_classificados_send_message() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
        wp_send_json_error(['message' => 'Erro de segurança.']);
    }
    
    // Validar dados
    $classified_id = intval($_POST['classified_id'] ?? 0);
    $author_id = intval($_POST['author_id'] ?? 0);
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    
    if (!$classified_id || !$author_id || !$name || !$email || !$message) {
        wp_send_json_error(['message' => 'Todos os campos são obrigatórios.']);
    }
    
    // Verificar se o classificado existe
    if (get_post_type($classified_id) !== 'classified') {
        wp_send_json_error(['message' => 'Classificado não encontrado.']);
    }
    
    // Verificar se o autor é um vendedor
    if (!dokan_is_user_seller($author_id)) {
        wp_send_json_error(['message' => 'Vendedor não encontrado.']);
    }
    
    // Inserir mensagem no banco
    global $wpdb;
    $table_name = $wpdb->prefix . 'j1_classified_messages';
    
    $result = $wpdb->insert(
        $table_name,
        [
            'classified_id' => $classified_id,
            'sender_name' => $name,
            'sender_email' => $email,
            'sender_id' => get_current_user_id() ?: null,
            'receiver_id' => $author_id,
            'message' => $message,
            'status' => 'unread',
            'created_at' => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s']
    );
    
    if ($result === false) {
        wp_send_json_error(['message' => 'Erro ao salvar mensagem.']);
    }
    
    $message_id = $wpdb->insert_id;
    
    // Enviar email para o vendedor
    j1_classificados_send_message_email($message_id, $classified_id, $author_id, $name, $email, $message);
    
    wp_send_json_success(['message' => 'Mensagem enviada com sucesso!']);
}

// ✅ Função para enviar email
function j1_classificados_send_message_email($message_id, $classified_id, $author_id, $sender_name, $sender_email, $message) {
    $classified_title = get_the_title($classified_id);
    $classified_url = get_permalink($classified_id);
    $vendor = dokan()->vendor->get($author_id);
    $vendor_email = $vendor ? $vendor->get_email() : '';
    
    if (!$vendor_email) {
        return false;
    }
    
    $subject = sprintf(__('Nova mensagem sobre o classificado: %s', 'j1_classificados'), $classified_title);
    
    $body = sprintf(
        __("
Olá!

Você recebeu uma nova mensagem sobre o classificado: %s

De: %s (%s)
Mensagem:
%s

Para responder, acesse seu painel de classificados.

Atenciosamente,
%s
        ", 'j1_classificados'),
        $classified_title,
        $sender_name,
        $sender_email,
        $message,
        get_bloginfo('name')
    );
    
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $sender_name . ' <' . $sender_email . '>'
    ];
    
    return wp_mail($vendor_email, $subject, $body, $headers);
}

// 🔹 Incluir handlers AJAX
require_once dirname(__FILE__) . '/includes/ajax-handlers.php';

// 🔹 Incluir debug (remover em produção)
require_once dirname(__FILE__) . '/debug-widget.php';
