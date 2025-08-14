<?php
/**
 * Integração do sistema de mensagens com J1 Classificados
 * 
 * @package J1_Classificados
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class J1_Classified_Messages_Integration {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    /**
     * Inicializar hooks
     */
    private function init() {
        // Carregar sistema de mensagens
        add_action('init', [$this, 'load_messages_system']);
        
        // Carregar widget do Elementor
        add_action('elementor/widgets/widgets_registered', [$this, 'load_elementor_widget']);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Incluir modal de mensagem
        add_action('wp_footer', [$this, 'include_message_modal']);
        
        // Adicionar coluna de mensagens no admin
        add_filter('manage_edit-classified_columns', [$this, 'add_messages_column_admin']);
        add_action('manage_classified_posts_custom_column', [$this, 'render_messages_column_admin'], 10, 2);
        
        // Adicionar menu de mensagens para clientes
        add_filter('woocommerce_account_menu_items', [$this, 'add_messages_menu_customer']);
        
        // Adicionar endpoint para mensagens do cliente
        add_action('init', [$this, 'add_customer_messages_endpoint']);
        add_filter('woocommerce_get_query_vars', [$this, 'add_customer_messages_query_vars']);
        add_action('woocommerce_account_mensagens_endpoint', [$this, 'render_customer_messages']);
        
        // AJAX endpoints
        add_action('wp_ajax_j1_load_message_modal', [$this, 'ajax_load_message_modal']);
        add_action('wp_ajax_j1_get_current_user', [$this, 'ajax_get_current_user']);
    }
    
    /**
     * Carregar sistema de mensagens
     */
    public function load_messages_system() {
        require_once plugin_dir_path(__FILE__) . 'class-messages.php';
    }
    
    /**
     * Carregar widget do Elementor
     */
    public function load_elementor_widget() {
        require_once plugin_dir_path(__FILE__) . 'class-elementor-widget.php';
        
        // Registrar o widget no Elementor
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \J1_Classified_Message_Widget());
    }
    
    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Só carregar em páginas de classificado
        if (is_singular('classified')) {
            wp_enqueue_style(
                'j1-messages-style',
                plugin_dir_url(__FILE__) . '../assets/css/messages.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'j1-messages-script',
                plugin_dir_url(__FILE__) . '../assets/js/messages.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script('j1-messages-script', 'j1_classifieds_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('j1_message_nonce'),
                'current_user' => wp_get_current_user(),
                'strings' => array(
                    'error' => __('Erro ao enviar mensagem', 'j1_classificados'),
                    'success' => __('Mensagem enviada com sucesso!', 'j1_classificados'),
                    'loading' => __('Enviando...', 'j1_classificados')
                )
            ));
        }
        
        // Carregar CSS e JS para o dashboard Dokan
        if (dokan_is_seller_dashboard()) {
            wp_enqueue_style(
                'j1-messages-style',
                plugin_dir_url(__FILE__) . '../assets/css/messages.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'j1-messages-script',
                plugin_dir_url(__FILE__) . '../assets/js/messages.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
    
    /**
     * Incluir modal de mensagem
     */
    public function include_message_modal() {
        if (is_singular('classified')) {
            include plugin_dir_path(__FILE__) . '../templates/modal-message.php';
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
            
            $total_messages = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_messages WHERE classified_id = %d",
                $post_id
            ));
            
            $unread_messages = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_messages WHERE classified_id = %d AND is_read = 0",
                $post_id
            ));
            
            if ($total_messages > 0) {
                echo '<a href="' . admin_url('edit.php?post_type=classified&page=dokan#messages-' . $post_id) . '">';
                echo '<strong>' . $total_messages . '</strong> mensagens';
                if ($unread_messages > 0) {
                    echo ' (<span style="color: red;">' . $unread_messages . ' não lidas</span>)';
                }
                echo '</a>';
            } else {
                echo '0 mensagens';
            }
        }
    }
    
    /**
     * Adicionar menu de mensagens para clientes
     */
    public function add_messages_menu_customer($menu_items) {
        $menu_items['mensagens'] = __('Mensagens', 'j1_classificados');
        return $menu_items;
    }
    
    /**
     * Adicionar endpoint para mensagens do cliente
     */
    public function add_customer_messages_endpoint() {
        add_rewrite_endpoint('mensagens', EP_ROOT | EP_PAGES);
    }
    
    /**
     * Adicionar query vars para mensagens
     */
    public function add_customer_messages_query_vars($vars) {
        $vars[] = 'mensagens';
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
        $received_messages = array();
        if (dokan_is_seller_enabled($current_user_id)) {
            $received_messages = $wpdb->get_results($wpdb->prepare(
                "SELECT m.*, p.post_title as classified_title, u.display_name as sender_name
                 FROM $table_messages m
                 LEFT JOIN {$wpdb->posts} p ON m.classified_id = p.ID
                 LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
                 WHERE m.receiver_id = %d
                 ORDER BY m.created_at DESC",
                $current_user_id
            ));
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
