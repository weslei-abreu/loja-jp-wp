<?php
/**
 * Widget Elementor para Botão "Conversar"
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

if (!defined('ABSPATH')) exit;

// Verificar se o Elementor está ativo
if (!did_action('elementor/loaded')) {
    return;
}

class J1_Classified_Message_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'j1_classified_message';
    }
    
    public function get_title() {
        return __('Botão Conversar - Classificado', 'j1_classificados');
    }
    
    public function get_icon() {
        return 'eicon-button';
    }
    
    public function get_categories() {
        return ['general'];
    }
    
    public function get_keywords() {
        return ['classificado', 'mensagem', 'conversar', 'chat', 'contato'];
    }
    
    protected function register_controls() {
        // Seção de Conteúdo
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Conteúdo', 'j1_classificados'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'button_text',
            [
                'label' => __('Texto do Botão', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Conversar', 'j1_classificados'),
                'placeholder' => __('Digite o texto do botão', 'j1_classificados'),
            ]
        );
        
        $this->add_control(
            'button_icon',
            [
                'label' => __('Ícone', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-comments',
                    'library' => 'fa-solid',
                ],
            ]
        );
        
        $this->add_control(
            'show_for_logged_in_only',
            [
                'label' => __('Mostrar apenas para usuários logados?', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'j1_classificados'),
                'label_off' => __('Não', 'j1_classificados'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'guest_message',
            [
                'label' => __('Mensagem para visitantes', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Faça login para conversar', 'j1_classificados'),
                'placeholder' => __('Mensagem para usuários não logados', 'j1_classificados'),
                'condition' => [
                    'show_for_logged_in_only' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de Estilo
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Estilo', 'j1_classificados'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .j1-message-button',
            ]
        );
        
        $this->start_controls_tabs('button_styles');
        
        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'j1_classificados'),
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Cor do Texto', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_color',
            [
                'label' => __('Cor de Fundo', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'j1_classificados'),
            ]
        );
        
        $this->add_control(
            'button_text_color_hover',
            [
                'label' => __('Cor do Texto (Hover)', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_color_hover',
            [
                'label' => __('Cor de Fundo (Hover)', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#005a87',
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .j1-message-button',
            ]
        );
        
        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Raio da Borda', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '4',
                    'right' => '4',
                    'bottom' => '4',
                    'left' => '4',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .j1-message-button',
            ]
        );
        
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '12',
                    'right' => '24',
                    'bottom' => '12',
                    'left' => '24',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'button_margin',
            [
                'label' => __('Margin', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de Ícone
        $this->start_controls_section(
            'icon_section',
            [
                'label' => __('Ícone', 'j1_classificados'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'icon_size',
            [
                'label' => __('Tamanho do Ícone', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .j1-message-button svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'icon_spacing',
            [
                'label' => __('Espaçamento do Ícone', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button i' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .j1-message-button svg' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Verificar se estamos em uma página de classificado
        if (!is_singular('classified')) {
            echo '<div class="j1-widget-notice">' . __('Este widget só funciona em páginas de classificados.', 'j1_classificados') . '</div>';
            return;
        }
        
        $classified_id = get_the_ID();
        $is_user_logged_in = is_user_logged_in();
        $show_for_logged_in_only = $settings['show_for_logged_in_only'] === 'yes';
        
        // Se o widget está configurado para mostrar apenas para logados e o usuário não está logado
        if ($show_for_logged_in_only && !$is_user_logged_in) {
            $this->render_guest_button($settings);
            return;
        }
        
        // Botão para usuários logados
        $this->render_message_button($settings, $classified_id);
    }
    
    private function render_guest_button($settings) {
        $guest_message = $settings['guest_message'] ?: __('Faça login para conversar', 'j1_classificados');
        ?>
        <div class="j1-message-widget">
            <button type="button" class="j1-message-button j1-guest-button" onclick="j1_redirect_to_login()">
                <?php if ($settings['button_icon']['value']) : ?>
                    <?php \Elementor\Icons_Manager::render_icon($settings['button_icon'], ['aria-hidden' => 'true']); ?>
                <?php endif; ?>
                <?php echo esc_html($guest_message); ?>
            </button>
        </div>
        <script>
        function j1_redirect_to_login() {
            window.location.href = '<?php echo esc_url(wp_login_url(get_permalink())); ?>';
        }
        </script>
        <?php
    }
    
    private function render_message_button($settings, $classified_id) {
        $button_text = $settings['button_text'] ?: __('Conversar', 'j1_classificados');
        ?>
        <div class="j1-message-widget">
            <button type="button" class="j1-message-button" onclick="j1_open_message_modal(<?php echo esc_attr($classified_id); ?>)">
                <?php if ($settings['button_icon']['value']) : ?>
                    <?php \Elementor\Icons_Manager::render_icon($settings['button_icon'], ['aria-hidden' => 'true']); ?>
                <?php endif; ?>
                <?php echo esc_html($button_text); ?>
            </button>
        </div>
        <?php
    }
    
    protected function content_template() {
        ?>
        <div class="j1-message-widget">
            <button type="button" class="j1-message-button">
                <# if (settings.button_icon.value) { #>
                    <i class="{{{ settings.button_icon.value }}}"></i>
                <# } #>
                {{{ settings.button_text }}}
            </button>
        </div>
        <?php
    }
}

// Registrar o widget
add_action('elementor/widgets/register', function($widgets_manager) {
    $widgets_manager->register(new J1_Classified_Message_Widget());
});
