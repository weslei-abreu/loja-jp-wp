<?php
/**
 * Integração com Elementor
 * 
 * @package J1_Classificados
 */

if (!defined('ABSPATH')) exit;

class J1_Classifieds_Elementor_Integration {

    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_categories']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
    }

    /**
     * Registrar widgets
     */
    public function register_widgets($widgets_manager) {
        // Verificar se o Elementor está ativo
        if (!did_action('elementor/loaded')) {
            return;
        }

        // Incluir o widget de mensagem
        require_once plugin_dir_path(__FILE__) . 'widgets/class-classified-message-widget.php';
        
        // Registrar o widget
        $widgets_manager->register(new J1_Classified_Message_Widget());
    }

    /**
     * Adicionar categoria de widgets
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'j1-classificados',
            [
                'title' => __('J1 Classificados', 'j1_classificados'),
                'icon' => 'fa fa-bullhorn',
            ]
        );
    }

    /**
     * Carregar scripts
     */
    public function enqueue_scripts() {
        if (is_singular('classified')) {
            wp_enqueue_script(
                'j1-message-widget',
                plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/message-widget.js',
                ['jquery'],
                '1.1.0',
                true
            );

            // Obter dados do usuário logado
            $current_user = wp_get_current_user();
            $user_email = '';
            $user_name = '';
            
            if (is_user_logged_in()) {
                $user_email = $current_user->user_email;
                $user_name = $current_user->display_name;
            }

            wp_localize_script('j1-message-widget', 'j1_message_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('j1_message_nonce'),
                'login_url' => wp_login_url(get_permalink()),
                'user_email' => $user_email,
                'user_name' => $user_name,
                'strings' => [
                    'sending' => __('Enviando...', 'j1_classificados'),
                    'success' => __('Mensagem enviada com sucesso!', 'j1_classificados'),
                    'error' => __('Erro ao enviar mensagem. Tente novamente.', 'j1_classificados'),
                    'login_required' => __('Você precisa estar logado para enviar mensagens.', 'j1_classificados'),
                    'login_now' => __('Fazer Login', 'j1_classificados'),
                    'login_now_confirm' => __('Deseja ir para a página de login?', 'j1_classificados'),
                    'cancel' => __('Cancelar', 'j1_classificados'),
                    'fill_required_fields' => __('Por favor, preencha todos os campos obrigatórios.', 'j1_classificados'),
                ]
            ]);
        }
    }

    /**
     * Carregar estilos
     */
    public function enqueue_styles() {
        if (is_singular('classified')) {
            wp_enqueue_style(
                'j1-message-widget',
                plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/message-widget.css',
                [],
                '1.1.0'
            );
        }
    }
}

// Inicializar a integração
new J1_Classifieds_Elementor_Integration(); 