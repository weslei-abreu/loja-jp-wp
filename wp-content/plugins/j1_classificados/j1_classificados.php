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
    $regular_price = floatval($_POST['classified_regular_price']);
    $categories = isset($_POST['classified_category']) ? array_map('intval', $_POST['classified_category']) : [];

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
        update_post_meta($post_id, '_regular_price', $regular_price);
        wp_set_post_terms($post_id, $categories, 'product_cat', false);

        // Upload imagem destacada
        if (!empty($_FILES['classified_thumbnail']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $file = wp_handle_upload($_FILES['classified_thumbnail'], ['test_form' => false]);
            if (!isset($file['error'])) {
                $attachment_id = wp_insert_attachment([
                    'post_mime_type' => $file['type'],
                    'post_title'     => sanitize_file_name($file['file']),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ], $file['file'], $post_id);
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attach_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
                wp_update_attachment_metadata($attachment_id, $attach_data);
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        // Upload galeria
        if (!empty($_FILES['classified_gallery']['name'][0])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $gallery_ids = [];
            foreach ($_FILES['classified_gallery']['name'] as $key => $value) {
                if (!empty($value)) {
                    $file = [
                        'name'     => $_FILES['classified_gallery']['name'][$key],
                        'type'     => $_FILES['classified_gallery']['type'][$key],
                        'tmp_name' => $_FILES['classified_gallery']['tmp_name'][$key],
                        'error'    => $_FILES['classified_gallery']['error'][$key],
                        'size'     => $_FILES['classified_gallery']['size'][$key],
                    ];
                    $upload = wp_handle_upload($file, ['test_form' => false]);
                    if (!isset($upload['error'])) {
                        $attach_id = wp_insert_attachment([
                            'post_mime_type' => $upload['type'],
                            'post_title'     => sanitize_file_name($upload['file']),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        ], $upload['file'], $post_id);
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        $gallery_ids[] = $attach_id;
                    }
                }
            }
            if (!empty($gallery_ids)) {
                update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery_ids));
            }
        }
    }

    wp_redirect(dokan_get_navigation_url('classifieds'));
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
