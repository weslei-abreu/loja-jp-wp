<?php
/**
 * Sistema de Mensagens para J1 Classificados
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

if (!defined('ABSPATH')) exit;

class J1_Classified_Messages {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Criar tabelas na ativação
        register_activation_hook(plugin_dir_path(dirname(__FILE__)) . 'j1_classificados.php', [$this, 'create_tables']);
        
        // Hooks para mensagens
        add_action('wp_ajax_j1_send_message', [$this, 'ajax_send_message']);
        add_action('wp_ajax_nopriv_j1_send_message', [$this, 'ajax_send_message']);
        add_action('wp_ajax_j1_mark_message_read', [$this, 'ajax_mark_message_read']);
        add_action('wp_ajax_j1_get_messages', [$this, 'ajax_get_messages']);
        
        // Adicionar coluna no dashboard do vendedor
        add_filter('dokan_product_listing_table_columns', [$this, 'add_messages_column']);
        add_action('dokan_product_listing_table_custom_column', [$this, 'render_messages_column'], 10, 2);
        
        // Adicionar menu de mensagens no dashboard
        add_filter('dokan_get_dashboard_nav', [$this, 'add_messages_menu']);
        
        // Template para mensagens
        add_action('dokan_load_custom_template', [$this, 'load_messages_template']);
        
        // Adicionar query var para mensagens
        add_filter('dokan_query_var_filter', [$this, 'add_messages_query_var']);
        
        // Notificações por email
        add_action('j1_message_sent', [$this, 'send_email_notification'], 10, 2);
        
        // Contador de mensagens não lidas
        add_action('wp_ajax_j1_get_unread_count', [$this, 'ajax_get_unread_count']);
        add_action('wp_ajax_nopriv_j1_get_unread_count', [$this, 'ajax_get_unread_count']);
        
        // Endpoint para enviar respostas
        add_action('wp_ajax_j1_send_reply', [$this, 'ajax_send_reply']);
        
        // Endpoint para cliente ver suas mensagens enviadas
        add_action('wp_ajax_j1_get_my_messages', [$this, 'ajax_get_my_messages']);
    }
    
    /**
     * Criar tabelas necessárias
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabela de threads de mensagens
        $table_threads = $wpdb->prefix . 'j1_message_threads';
        $sql_threads = "CREATE TABLE $table_threads (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            classified_id bigint(20) NOT NULL,
            status varchar(20) DEFAULT 'open',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY classified_id (classified_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Tabela de mensagens
        $table_messages = $wpdb->prefix . 'j1_messages';
        $sql_messages = "CREATE TABLE $table_messages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            thread_id bigint(20) NOT NULL,
            classified_id bigint(20) NOT NULL,
            sender_id bigint(20) NOT NULL,
            receiver_id bigint(20) NOT NULL,
            subject varchar(255) DEFAULT '',
            message longtext NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY thread_id (thread_id),
            KEY classified_id (classified_id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_threads);
        dbDelta($sql_messages);
        
        // Adicionar versão das tabelas
        add_option('j1_messages_db_version', '1.0');
    }
    
    /**
     * Enviar mensagem via AJAX
     */
    public function ajax_send_message() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Verificar se usuário está logado
        if (!is_user_logged_in()) {
            wp_send_json_error('User must be logged in');
        }
        
        $classified_id = intval($_POST['classified_id']);
        $message = sanitize_textarea_field($_POST['message']);
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        
        if (empty($message) || empty($classified_id)) {
            wp_send_json_error('Message and classified ID are required');
        }
        
        $user_id = get_current_user_id();
        $classified = get_post($classified_id);
        
        if (!$classified || $classified->post_type !== 'classified') {
            wp_send_json_error('Invalid classified');
        }
        
        $receiver_id = $classified->post_author;
        
        // Verificar se não está enviando para si mesmo
        if ($user_id === $receiver_id) {
            wp_send_json_error('Cannot send message to yourself');
        }
        
        // Criar ou obter thread
        $thread_id = $this->get_or_create_thread($classified_id);
        
        // Salvar mensagem
        $message_id = $this->save_message($thread_id, $classified_id, $user_id, $receiver_id, $subject, $message);
        
        if ($message_id) {
            // Atualizar thread
            $this->update_thread($thread_id);
            
            // Disparar ação para notificações
            do_action('j1_message_sent', $message_id, $receiver_id);
            
            wp_send_json_success([
                'message' => 'Message sent successfully',
                'message_id' => $message_id
            ]);
        } else {
            wp_send_json_error('Failed to send message');
        }
    }
    
    /**
     * Obter ou criar thread de mensagens
     */
    private function get_or_create_thread($classified_id) {
        global $wpdb;
        
        $table_threads = $wpdb->prefix . 'j1_message_threads';
        
        // Verificar se já existe thread para este classificado
        $existing_thread = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_threads WHERE classified_id = %d",
            $classified_id
        ));
        
        if ($existing_thread) {
            return $existing_thread;
        }
        
        // Criar nova thread
        $wpdb->insert(
            $table_threads,
            [
                'classified_id' => $classified_id,
                'status' => 'open'
            ],
            ['%d', '%s']
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Salvar mensagem no banco
     */
    private function save_message($thread_id, $classified_id, $sender_id, $receiver_id, $subject, $message) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        $result = $wpdb->insert(
            $table_messages,
            [
                'thread_id' => $thread_id,
                'classified_id' => $classified_id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'subject' => $subject,
                'message' => $message,
                'is_read' => 0
            ],
            ['%d', '%d', '%d', '%d', '%s', '%s', '%d']
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Atualizar thread
     */
    private function update_thread($thread_id) {
        global $wpdb;
        
        $table_threads = $wpdb->prefix . 'j1_message_threads';
        
        $wpdb->update(
            $table_threads,
            ['updated_at' => current_time('mysql')],
            ['id' => $thread_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Marcar mensagem como lida
     */
    public function ajax_mark_message_read() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $message_id = intval($_POST['message_id']);
        $user_id = get_current_user_id();
        
        if ($this->mark_message_read($message_id, $user_id)) {
            wp_send_json_success('Message marked as read');
        } else {
            wp_send_json_error('Failed to mark message as read');
        }
    }
    
    /**
     * Marcar mensagem como lida
     */
    private function mark_message_read($message_id, $user_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        return $wpdb->update(
            $table_messages,
            ['is_read' => 1],
            [
                'id' => $message_id,
                'receiver_id' => $user_id
            ],
            ['%d'],
            ['%d', '%d']
        );
    }
    
    /**
     * Obter mensagens de um classificado
     */
    public function ajax_get_messages() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $classified_id = intval($_POST['classified_id']);
        $user_id = get_current_user_id();
        
        // Verificar permissões
        $classified = get_post($classified_id);
        if (!$classified || $classified->post_type !== 'classified') {
            wp_send_json_error('Invalid classified');
        }
        
        // Apenas o autor do classificado pode ver as mensagens
        // Converter ambos para inteiros para comparação correta
        $author_id = intval($classified->post_author);
        $user_id_int = intval($user_id);
        
        if ($author_id !== $user_id_int) {
            wp_send_json_error('Access denied');
        }
        
        $messages = $this->get_messages_by_classified($classified_id);
        wp_send_json_success($messages);
    }
    
    /**
     * Obter mensagens organizadas por usuário
     */
    private function get_messages_by_classified($classified_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, u.display_name as sender_name, u.user_email as sender_email
             FROM $table_messages m
             LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
             WHERE m.classified_id = %d
             ORDER BY m.created_at ASC",
            $classified_id
        ));
        
        // Organizar por thread de conversa (usuário que iniciou)
        $organized = [];
        foreach ($messages as $message) {
            // Para respostas, usar o receiver_id original para manter na mesma conversa
            $conversation_key = $message->sender_id;
            
            // Se é uma resposta do vendedor, agrupar com a conversa original do cliente
            if ($message->receiver_id !== $message->sender_id) {
                // Buscar a primeira mensagem desta thread para agrupar corretamente
                $first_message = $wpdb->get_row($wpdb->prepare(
                    "SELECT sender_id FROM $table_messages 
                     WHERE thread_id = %d 
                     ORDER BY created_at ASC 
                     LIMIT 1",
                    $message->thread_id
                ));
                
                if ($first_message) {
                    $conversation_key = $first_message->sender_id;
                }
            }
            
            if (!isset($organized[$conversation_key])) {
                $organized[$conversation_key] = [
                    'user_id' => $conversation_key,
                    'user_name' => $message->sender_name,
                    'user_email' => $message->sender_email,
                    'messages' => [],
                    'unread_count' => 0,
                    'total_count' => 0
                ];
            }
            
            $organized[$conversation_key]['messages'][] = $message;
            $organized[$conversation_key]['total_count']++;
            
            if (!$message->is_read) {
                $organized[$conversation_key]['unread_count']++;
            }
        }
        
        return $organized;
    }
    
    /**
     * Adicionar coluna de mensagens no dashboard
     */
    public function add_messages_column($columns) {
        $columns['messages'] = __('Mensagens', 'j1_classificados');
        return $columns;
    }
    
    /**
     * Renderizar coluna de mensagens
     */
    public function render_messages_column($column, $post_id) {
        if ($column === 'messages') {
            $unread_count = $this->get_unread_count_for_classified($post_id);
            $total_count = $this->get_total_count_for_classified($post_id);
            
            $class = $unread_count > 0 ? 'unread' : '';
            $badge = $unread_count > 0 ? "<span class='unread-badge'>$unread_count</span>" : '';
            
            echo "<a href='" . dokan_get_navigation_url('messages') . "?classified_id=$post_id' class='messages-link $class'>";
            echo "<i class='fas fa-comments'></i> $total_count $badge";
            echo "</a>";
        }
    }
    
    /**
     * Contar mensagens não lidas para um classificado
     */
    private function get_unread_count_for_classified($classified_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_messages 
             WHERE classified_id = %d AND receiver_id = %d AND is_read = 0",
            $classified_id,
            get_current_user_id()
        ));
    }
    
    /**
     * Contar total de mensagens para um classificado
     */
    private function get_total_count_for_classified($classified_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_messages WHERE classified_id = %d",
            $classified_id
        ));
    }
    
    /**
     * Adicionar menu de mensagens no dashboard
     */
    public function add_messages_menu($urls) {
        $urls['messages'] = [
            'title' => __('Mensagens', 'j1_classificados'),
            'icon'  => '<i class="fas fa-comments"></i>',
            'url'   => dokan_get_navigation_url('messages'),
            'pos'   => 60
        ];
        return $urls;
    }
    
    /**
     * Adicionar query var para mensagens
     */
    public function add_messages_query_var($query_vars) {
        $query_vars[] = 'messages';
        return $query_vars;
    }
    
    /**
     * Carregar template de mensagens
     */
    public function load_messages_template($query_vars) {
        if (isset($query_vars['messages'])) {
            include dirname(__FILE__) . '/../templates/dashboard-messages.php';
            exit;
        }
        return $query_vars;
    }
    
    /**
     * Enviar notificação por email
     */
    public function send_email_notification($message_id, $receiver_id) {
        $receiver = get_user_by('ID', $receiver_id);
        if (!$receiver) return;
        
        $message = $this->get_message($message_id);
        if (!$message) return;
        
        $classified = get_post($message->classified_id);
        $sender = get_user_by('ID', $message->sender_id);
        
        $subject = sprintf(__('Nova mensagem sobre: %s', 'j1_classificados'), $classified->post_title);
        
        $body = sprintf(
            __('Olá %s,

Você recebeu uma nova mensagem sobre o classificado "%s".

De: %s
Mensagem: %s

Para responder, acesse seu dashboard de vendedor.

Atenciosamente,
Equipe %s', 'j1_classificados'),
            $receiver->display_name,
            $classified->post_title,
            $sender->display_name,
            $message->message,
            get_bloginfo('name')
        );
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($receiver->user_email, $subject, $body, $headers);
    }
    
    /**
     * Obter mensagem por ID
     */
    private function get_message($message_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_messages WHERE id = %d",
            $message_id
        ));
    }
    
    /**
     * Obter contador de mensagens não lidas via AJAX
     */
    public function ajax_get_unread_count() {
        if (!is_user_logged_in()) {
            wp_send_json_success(['count' => 0]);
        }
        
        $user_id = get_current_user_id();
        $count = $this->get_total_unread_count($user_id);
        
        wp_send_json_success(['count' => $count]);
    }
    
    /**
     * Contar total de mensagens não lidas para um usuário
     */
    private function get_total_unread_count($user_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_messages 
             WHERE receiver_id = %d AND is_read = 0",
            $user_id
        ));
    }
    
    /**
     * Enviar resposta a uma mensagem
     */
    public function ajax_send_reply() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $classified_id = intval($_POST['classified_id']);
        $sender_id = intval($_POST['sender_id']); // ID do usuário que enviou a mensagem original
        $message_id = intval($_POST['message_id']);
        $subject = sanitize_text_field($_POST['subject']);
        $message = sanitize_textarea_field($_POST['message']);
        
        // Validar dados
        if (empty($subject) || empty($message)) {
            wp_send_json_error('Subject and message are required');
        }
        
        // Verificar se o usuário atual é o autor do classificado
        $classified = get_post($classified_id);
        if (!$classified || $classified->post_type !== 'classified') {
            wp_send_json_error('Invalid classified');
        }
        
        $current_user_id = get_current_user_id();
        
        // Debug: Log dos IDs para verificar permissão
        error_log("J1 Debug - Classified ID: $classified_id | Author ID: " . $classified->post_author . " | Current User ID: $current_user_id");
        error_log("J1 Debug - Author type: " . gettype($classified->post_author) . " | Current User type: " . gettype($current_user_id));
        
        // Converter ambos para inteiros para comparação correta
        $author_id = intval($classified->post_author);
        $current_user_id_int = intval($current_user_id);
        
        if ($author_id !== $current_user_id_int) {
            wp_send_json_error("Access denied - you are not the author of this classified. Author: $author_id, Current User: $current_user_id_int");
        }
        
        // Verificar se a mensagem original existe
        $original_message = $this->get_message($message_id);
        if (!$original_message) {
            wp_send_json_error('Original message not found');
        }
        
        // Criar ou obter thread existente
        $thread_id = $this->get_or_create_thread($classified_id);
        
        if (!$thread_id) {
            wp_send_json_error('Failed to create thread');
        }
        
        // Enviar a resposta - O vendedor (autor do classificado) responde para o cliente
        // IMPORTANTE: O vendedor (current_user_id) envia para o cliente (sender_id)
        $reply_id = $this->save_message($thread_id, $classified_id, $current_user_id, $sender_id, $subject, $message);
        
        if ($reply_id) {
            // Enviar notificação por email
            $this->send_email_notification($reply_id, $sender_id);
            
            wp_send_json_success('Reply sent successfully');
        } else {
            wp_send_json_error('Failed to send reply');
        }
    }
    
    /**
     * Cliente ver suas mensagens enviadas
     */
    public function ajax_get_my_messages() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        
        // Buscar mensagens que o usuário enviou
        $messages = $this->get_messages_sent_by_user($user_id);
        wp_send_json_success($messages);
    }
    
    /**
     * Obter mensagens enviadas por um usuário
     */
    private function get_messages_sent_by_user($user_id) {
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, p.post_title as classified_title, u.display_name as receiver_name
             FROM $table_messages m
             LEFT JOIN {$wpdb->posts} p ON m.classified_id = p.ID
             LEFT JOIN {$wpdb->users} u ON m.receiver_id = u.ID
             WHERE m.sender_id = %d
             ORDER BY m.created_at DESC",
            $user_id
        ));
        
        // Organizar por classificado
        $organized = [];
        foreach ($messages as $message) {
            $classified_id = $message->classified_id;
            if (!isset($organized[$classified_id])) {
                $organized[$classified_id] = [
                    'classified_id' => $classified_id,
                    'classified_title' => $message->classified_title,
                    'receiver_name' => $message->receiver_name,
                    'messages' => [],
                    'unread_count' => 0,
                    'total_count' => 0
                ];
            }
            
            $organized[$classified_id]['messages'][] = $message;
            $organized[$classified_id]['total_count']++;
            
            if (!$message->is_read) {
                $organized[$classified_id]['unread_count']++;
            }
        }
        
        return $organized;
    }
}

// Inicializar a classe
J1_Classified_Messages::get_instance();
