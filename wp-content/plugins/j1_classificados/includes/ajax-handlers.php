<?php
/**
 * Handlers AJAX para o plugin J1 Classificados
 * 
 * @package J1_Classificados
 */

if (!defined('ABSPATH')) exit;

/**
 * Handler para enviar mensagem
 */
add_action('wp_ajax_j1_send_message', 'j1_handle_send_message');
// Remover a ação para usuários não logados
// add_action('wp_ajax_nopriv_j1_send_message', 'j1_handle_send_message');

function j1_handle_send_message() {
    // Verificar se o usuário está logado
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Você precisa estar logado para enviar mensagens.']);
    }

    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
        wp_send_json_error(['message' => 'Erro de segurança. Tente novamente.']);
    }

    // Validar campos obrigatórios
    $required_fields = ['name', 'email', 'message', 'classified_id', 'author_id'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(['message' => 'Todos os campos são obrigatórios.']);
        }
    }

    // Sanitizar dados
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    $classified_id = intval($_POST['classified_id']);
    $author_id = intval($_POST['author_id']);

    // Validar email
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Email inválido.']);
    }

    // Verificar se o classificado existe
    $classified = get_post($classified_id);
    if (!$classified || $classified->post_type !== 'classified') {
        wp_send_json_error(['message' => 'Classificado não encontrado.']);
    }

    // Verificar se o autor existe
    $author = get_user_by('ID', $author_id);
    if (!$author) {
        wp_send_json_error(['message' => 'Vendedor não encontrado.']);
    }

    // Verificar se o usuário não está tentando enviar mensagem para si mesmo
    $current_user_id = get_current_user_id();
    if ($current_user_id === $author_id) {
        wp_send_json_error(['message' => 'Você não pode enviar mensagem para si mesmo.']);
    }

    // Criar a mensagem
    $message_data = [
        'post_title' => sprintf('Mensagem sobre: %s', $classified->post_title),
        'post_content' => $message,
        'post_status' => 'publish',
        'post_type' => 'j1_message',
        'post_author' => $author_id,
        'meta_input' => [
            'j1_message_sender_name' => $name,
            'j1_message_sender_email' => $email,
            'j1_message_classified_id' => $classified_id,
            'j1_message_author_id' => $author_id,
            'j1_message_status' => 'unread'
        ]
    ];

    // Inserir a mensagem
    $message_id = wp_insert_post($message_data);

    if (is_wp_error($message_id)) {
        wp_send_json_error(['message' => 'Erro ao enviar mensagem. Tente novamente.']);
    }

    // Enviar email de notificação para o vendedor
    $subject = sprintf('Nova mensagem sobre: %s', $classified->post_title);
    $email_content = sprintf(
        "Olá %s,\n\nVocê recebeu uma nova mensagem sobre o classificado '%s'.\n\n" .
        "Nome: %s\nEmail: %s\nMensagem:\n%s\n\n" .
        "Para responder, acesse o painel do vendedor.\n\n" .
        "Atenciosamente,\n%s",
        $author->display_name,
        $classified->post_title,
        $name,
        $email,
        $message,
        get_bloginfo('name')
    );

    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $sent = wp_mail($author->user_email, $subject, $email_content, $headers);

    // Log da mensagem
    error_log(sprintf(
        'J1 Classificados - Nova mensagem enviada: ID=%d, Classificado=%d, Vendedor=%d, Remetente=%s',
        $message_id,
        $classified_id,
        $author_id,
        $email
    ));

    wp_send_json_success(['message' => 'Mensagem enviada com sucesso!']);
}

/**
 * Registrar post type para mensagens
 */
add_action('init', function() {
    $labels = [
        'name' => 'Mensagens',
        'singular_name' => 'Mensagem',
        'add_new' => 'Adicionar Nova',
        'add_new_item' => 'Adicionar Nova Mensagem',
        'edit_item' => 'Editar Mensagem',
        'new_item' => 'Nova Mensagem',
        'view_item' => 'Ver Mensagem',
        'search_items' => 'Buscar Mensagens',
        'not_found' => 'Nenhuma mensagem encontrada',
        'not_found_in_trash' => 'Nenhuma mensagem na lixeira',
        'menu_name' => 'Mensagens'
    ];

    $args = [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => false,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-email-alt',
        'menu_position' => 30
    ];

    register_post_type('j1_message', $args);
});

/**
 * Adicionar colunas personalizadas para mensagens
 */
add_filter('manage_j1_message_posts_columns', function($columns) {
    $new_columns = [
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'sender' => 'Remetente',
        'classified' => 'Classificado',
        'status' => 'Status',
        'date' => $columns['date']
    ];
    return $new_columns;
});

add_action('manage_j1_message_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'sender':
            $name = get_post_meta($post_id, 'j1_message_sender_name', true);
            $email = get_post_meta($post_id, 'j1_message_sender_email', true);
            echo sprintf('<strong>%s</strong><br><small>%s</small>', $name, $email);
            break;
        
        case 'classified':
            $classified_id = get_post_meta($post_id, 'j1_message_classified_id', true);
            if ($classified_id) {
                $classified = get_post($classified_id);
                if ($classified) {
                    echo sprintf('<a href="%s">%s</a>', get_edit_post_link($classified_id), $classified->post_title);
                }
            }
            break;
        
        case 'status':
            $status = get_post_meta($post_id, 'j1_message_status', true);
            $status_text = $status === 'read' ? 'Lida' : 'Não lida';
            $status_class = $status === 'read' ? 'read' : 'unread';
            echo sprintf('<span class="j1-message-status %s">%s</span>', $status_class, $status_text);
            break;
    }
}, 10, 2);

/**
 * Adicionar estilos para as colunas
 */
add_action('admin_head', function() {
    if (get_current_screen()->post_type === 'j1_message') {
        echo '<style>
            .j1-message-status {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
            }
            .j1-message-status.unread {
                background: #d4edda;
                color: #155724;
            }
            .j1-message-status.read {
                background: #e2e3e5;
                color: #383d41;
            }
        </style>';
    }
}); 