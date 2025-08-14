<?php
/**
 * Integração do Sistema de Mensagens - J1 Classificados
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

if (!defined('ABSPATH')) exit;

class J1_Classified_Messages_Integration {
    
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
        // Carregar sistema de mensagens
        add_action('init', [$this, 'load_messages_system']);
        
        // Carregar widget do Elementor
        add_action('elementor/init', [$this, 'load_elementor_widget']);
        
        // Carregar assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Incluir modal nas páginas de classificados
        add_action('wp_footer', [$this, 'include_message_modal']);
        
        // Adicionar coluna de mensagens na listagem de classificados
        add_filter('manage_edit-classified_columns', [$this, 'add_messages_column_admin']);
        add_action('manage_classified_posts_custom_column', [$this, 'render_messages_column_admin'], 10, 2);
        
        // Adicionar menu de mensagens para clientes
        add_action('woocommerce_account_menu_items', [$this, 'add_messages_menu_customer']);
        add_action('woocommerce_account_messages_endpoint', [$this, 'render_customer_messages']);
        
        // Adicionar endpoint para mensagens do cliente
        add_action('init', [$this, 'add_customer_messages_endpoint']);
        add_filter('query_vars', [$this, 'add_customer_messages_query_vars']);
        
        // AJAX para carregar modal
        add_action('wp_ajax_j1_load_message_modal', [$this, 'ajax_load_message_modal']);
        add_action('wp_ajax_nopriv_j1_load_message_modal', [$this, 'ajax_load_message_modal']);
        
        // AJAX para obter informações do usuário atual
        add_action('wp_ajax_j1_get_current_user', [$this, 'ajax_get_current_user']);
        add_action('wp_ajax_nopriv_j1_get_current_user', [$this, 'ajax_get_current_user']);
    }
    
    /**
     * Carregar sistema de mensagens
     */
    public function load_messages_system() {
        // Incluir classe principal de mensagens
        require_once dirname(__FILE__) . '/class-messages.php';
    }
    
    /**
     * Carregar widget do Elementor
     */
    public function load_elementor_widget() {
        // Incluir widget do Elementor
        require_once dirname(__FILE__) . '/class-elementor-widget.php';
    }
    
    /**
     * Carregar assets
     */
    public function enqueue_assets() {
        // Carregar CSS e JS apenas quando necessário
        if (is_singular('classified') || is_page('my-account') || dokan_is_seller_dashboard()) {
            wp_enqueue_style(
                'j1-messages-style',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/messages.css',
                [],
                '1.2.0'
            );
            
            wp_enqueue_script(
                'j1-messages-script',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/messages.js',
                ['jquery'],
                '1.2.0',
                true
            );
            
            // Localizar script com dados necessários
            wp_localize_script('j1-messages-script', 'j1_classifieds_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('j1_message_nonce'),
                'plugin_url' => plugin_dir_url(dirname(__FILE__)),
                'current_user' => [
                    'id' => get_current_user_id(),
                    'name' => wp_get_current_user()->display_name,
                    'email' => wp_get_current_user()->user_email
                ],
                'strings' => [
                    'login_required' => __('Você precisa estar logado para enviar mensagens.', 'j1_classificados'),
                    'cannot_send_to_self' => __('Você não pode enviar mensagem para si mesmo.', 'j1_classificados'),
                    'message_sent' => __('Mensagem enviada com sucesso!', 'j1_classificados'),
                    'error_sending' => __('Erro ao enviar mensagem.', 'j1_classificados'),
                    'connection_error' => __('Erro de conexão. Tente novamente.', 'j1_classificados')
                ]
            ]);
        }
    }
    
    /**
     * Incluir modal de mensagem nas páginas de classificados
     */
    public function include_message_modal() {
        if (is_singular('classified')) {
            include dirname(__FILE__) . '/../templates/modal-message.php';
        }
    }
    
    /**
     * Adicionar coluna de mensagens no admin
     */
    public function add_messages_column_admin($columns) {
        $columns['messages'] = __('Mensagens', 'j1_classificados');
        return $columns;
    }
    
    /**
     * Renderizar coluna de mensagens no admin
     */
    public function render_messages_column_admin($column, $post_id) {
        if ($column === 'messages') {
            global $wpdb;
            
            $table_messages = $wpdb->prefix . 'j1_messages';
            $total_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_messages WHERE classified_id = %d",
                $post_id
            ));
            
            $unread_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_messages WHERE classified_id = %d AND is_read = 0",
                $post_id
            ));
            
            if ($total_count > 0) {
                echo '<div class="j1-admin-messages">';
                echo '<span class="j1-total-count">' . $total_count . '</span>';
                if ($unread_count > 0) {
                    echo '<span class="j1-unread-badge">' . $unread_count . '</span>';
                }
                echo '</div>';
            } else {
                echo '<span class="j1-no-messages">—</span>';
            }
        }
    }
    
    /**
     * Adicionar menu de mensagens para clientes
     */
    public function add_messages_menu_customer($items) {
        // Inserir após "Dashboard"
        $new_items = [];
        foreach ($items as $key => $item) {
            $new_items[$key] = $item;
            if ($key === 'dashboard') {
                $new_items['messages'] = __('Mensagens', 'j1_classificados');
            }
        }
        
        return $new_items;
    }
    
    /**
     * Adicionar endpoint para mensagens do cliente
     */
    public function add_customer_messages_endpoint() {
        add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
    }
    
    /**
     * Adicionar query vars para mensagens do cliente
     */
    public function add_customer_messages_query_vars($vars) {
        $vars[] = 'messages';
        return $vars;
    }
    
    /**
     * Renderizar mensagens do cliente
     */
    public function render_customer_messages() {
        $current_user_id = get_current_user_id();
        
        if (!$current_user_id) {
            echo '<p>' . __('Você precisa estar logado para ver suas mensagens.', 'j1_classificados') . '</p>';
            return;
        }
        
        global $wpdb;
        
        $table_messages = $wpdb->prefix . 'j1_messages';
        
        // Obter mensagens enviadas pelo cliente
        $sent_messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, p.post_title as classified_title
             FROM $table_messages m
             LEFT JOIN {$wpdb->posts} p ON m.classified_id = p.ID
             WHERE m.sender_id = %d
             ORDER BY m.created_at DESC",
            $current_user_id
        ));
        
        // Obter mensagens recebidas pelo cliente (se for vendedor)
        $received_messages = [];
        if (dokan_is_seller_enabled($current_user_id)) {
            $received_messages = $wpdb->get_results($wpdb->prepare(
                "SELECT m.*, p.post_title as classified_title, u.display_name as sender_name
                 FROM $table_messages m
                 LEFT JOIN {$wpdb->posts} p ON m.classified_id = p.ID
                 LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
                 WHERE m.receiver_id = %d
                 ORDER BY m.created_at DESC",
                $current_user_id
            );
        }
        
        ?>
        <div class="j1-customer-messages">
            <h2><?php esc_html_e('Minhas Mensagens', 'j1_classificados'); ?></h2>
            
            <!-- Mensagens Enviadas -->
            <div class="j1-messages-section">
                <h3><?php esc_html_e('Mensagens Enviadas', 'j1_classificados'); ?></h3>
                
                <?php if (empty($sent_messages)) : ?>
                    <p><?php esc_html_e('Você ainda não enviou nenhuma mensagem.', 'j1_classificados'); ?></p>
                <?php else : ?>
                    <div class="j1-messages-list">
                        <?php foreach ($sent_messages as $message) : ?>
                            <div class="j1-message-item">
                                <div class="j1-message-header">
                                    <h4><?php echo esc_html($message->classified_title); ?></h4>
                                    <span class="j1-message-date">
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($message->created_at))); ?>
                                    </span>
                                </div>
                                
                                <?php if ($message->subject) : ?>
                                    <div class="j1-message-subject">
                                        <strong><?php esc_html_e('Assunto:', 'j1_classificados'); ?></strong> 
                                        <?php echo esc_html($message->subject); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="j1-message-content">
                                    <?php echo wp_kses_post(wpautop($message->message)); ?>
                                </div>
                                
                                <div class="j1-message-status">
                                    <?php if ($message->is_read) : ?>
                                        <span class="j1-status-read"><?php esc_html_e('Lida', 'j1_classificados'); ?></span>
                                    <?php else : ?>
                                        <span class="j1-status-unread"><?php esc_html_e('Não lida', 'j1_classificados'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mensagens Recebidas (se for vendedor) -->
            <?php if (!empty($received_messages)) : ?>
                <div class="j1-messages-section">
                    <h3><?php esc_html_e('Mensagens Recebidas', 'j1_classificados'); ?></h3>
                    
                    <div class="j1-messages-list">
                        <?php foreach ($received_messages as $message) : ?>
                            <div class="j1-message-item">
                                <div class="j1-message-header">
                                    <h4><?php echo esc_html($message->classified_title); ?></h4>
                                    <span class="j1-message-date">
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($message->created_at))); ?>
                                    </span>
                                </div>
                                
                                <div class="j1-message-sender">
                                    <strong><?php esc_html_e('De:', 'j1_classificados'); ?></strong> 
                                    <?php echo esc_html($message->sender_name); ?>
                                </div>
                                
                                <?php if ($message->subject) : ?>
                                    <div class="j1-message-subject">
                                        <strong><?php esc_html_e('Assunto:', 'j1_classificados'); ?></strong> 
                                        <?php echo esc_html($message->subject); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="j1-message-content">
                                    <?php echo wp_kses_post(wpautop($message->message)); ?>
                                </div>
                                
                                <div class="j1-message-status">
                                    <?php if ($message->is_read) : ?>
                                        <span class="j1-status-read"><?php esc_html_e('Lida', 'j1_classificados'); ?></span>
                                    <?php else : ?>
                                        <span class="j1-status-unread"><?php esc_html_e('Não lida', 'j1_classificados'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * AJAX para carregar modal
     */
    public function ajax_load_message_modal() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        ob_start();
        include dirname(__FILE__) . '/../templates/modal-message.php';
        $html = ob_get_clean();
        
        wp_send_json_success(['html' => $html]);
    }
    
    /**
     * AJAX para obter usuário atual
     */
    public function ajax_get_current_user() {
        if (!wp_verify_nonce($_POST['nonce'], 'j1_message_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $current_user = wp_get_current_user();
        
        if ($current_user->ID) {
            wp_send_json_success([
                'id' => $current_user->ID,
                'name' => $current_user->display_name,
                'email' => $current_user->user_email
            ]);
        } else {
            wp_send_json_error('User not logged in');
        }
    }
}

// Inicializar a integração
J1_Classified_Messages_Integration::get_instance();
