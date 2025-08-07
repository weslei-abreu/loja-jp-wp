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
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/message-widget.js',
                ['jquery'],
                '1.0.0',
                true
            );

            wp_localize_script('j1-message-widget', 'j1_message_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('j1_message_nonce'),
                'strings' => [
                    'sending' => __('Enviando...', 'j1_classificados'),
                    'success' => __('Mensagem enviada com sucesso!', 'j1_classificados'),
                    'error' => __('Erro ao enviar mensagem. Tente novamente.', 'j1_classificados'),
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
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/message-widget.css',
                [],
                '1.0.0'
            );
        }
    }
}

// Inicializar a integração
new J1_Classifieds_Elementor_Integration(); 