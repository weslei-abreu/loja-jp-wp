<?php
/**
 * Widget de Mensagem para Classificados
 * 
 * @package J1_Classificados
 */

if (!defined('ABSPATH')) exit;

class J1_Classified_Message_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'j1-classified-message';
    }

    public function get_title() {
        return __('Conversar sobre Classificado', 'j1_classificados');
    }

    public function get_icon() {
        return 'eicon-message-circle-o';
    }

    public function get_categories() {
        return ['j1-classificados'];
    }

    protected function register_controls() {
        // Seção do Botão
        $this->start_controls_section(
            'button_section',
            [
                'label' => __('Botão', 'j1_classificados'),
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

        $this->end_controls_section();

        // Seção do Modal
        $this->start_controls_section(
            'modal_section',
            [
                'label' => __('Modal', 'j1_classificados'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'modal_title',
            [
                'label' => __('Título do Modal', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Enviar Mensagem', 'j1_classificados'),
                'placeholder' => __('Digite o título do modal', 'j1_classificados'),
            ]
        );

        $this->add_control(
            'form_title',
            [
                'label' => __('Título do Formulário', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Preencha os dados abaixo:', 'j1_classificados'),
                'placeholder' => __('Digite o título do formulário', 'j1_classificados'),
            ]
        );

        $this->end_controls_section();

        // Seção de Estilo do Botão
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Estilo do Botão', 'j1_classificados'),
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
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '4',
                    'right' => '4',
                    'bottom' => '4',
                    'left' => '4',
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'j1_classificados'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .j1-message-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => '12',
                    'right' => '24',
                    'bottom' => '12',
                    'left' => '24',
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Só renderizar se estivermos em uma página de classificado
        if (!is_singular('classified')) {
            echo '<p>' . __('Este widget só funciona em páginas de classificados.', 'j1_classificados') . '</p>';
            return;
        }

        $post_id = get_the_ID();
        $author_id = get_post_field('post_author', $post_id);
        
        // Verificar se o autor é um vendedor
        if (!$author_id || !dokan_is_user_seller($author_id)) {
            echo '<p>' . __('Este classificado não possui um vendedor válido.', 'j1_classificados') . '</p>';
            return;
        }

        // Verificar se o usuário está logado
        $is_user_logged_in = is_user_logged_in();
        $current_user = wp_get_current_user();
        $user_email = $is_user_logged_in ? $current_user->user_email : '';
        $user_name = $is_user_logged_in ? $current_user->display_name : '';

        ?>
        <div class="j1-message-widget">
            <!-- Botão para abrir o modal -->
            <button type="button" class="j1-message-button" data-classified-id="<?php echo esc_attr($post_id); ?>" data-user-logged-in="<?php echo $is_user_logged_in ? 'true' : 'false'; ?>">
                <?php if ($settings['button_icon']['value']) : ?>
                    <i class="<?php echo esc_attr($settings['button_icon']['value']); ?>"></i>
                <?php endif; ?>
                <?php echo esc_html($settings['button_text']); ?>
            </button>

            <!-- Modal -->
            <div id="j1-message-modal-<?php echo esc_attr($post_id); ?>" class="j1-message-modal">
                <div class="j1-message-modal-content">
                    <div class="j1-message-modal-header">
                        <h3><?php echo esc_html($settings['modal_title']); ?></h3>
                        <span class="j1-message-modal-close">&times;</span>
                    </div>
                    <div class="j1-message-modal-body">
                        <p class="j1-message-form-title"><?php echo esc_html($settings['form_title']); ?></p>
                        
                        <!-- Feedback messages -->
                        <div class="j1-message-feedback" style="display: none;"></div>
                        
                        <form class="j1-message-form">
                            <?php wp_nonce_field('j1_message_nonce', 'nonce'); ?>
                            
                            <input type="hidden" name="classified_id" value="<?php echo esc_attr($post_id); ?>">
                            <input type="hidden" name="author_id" value="<?php echo esc_attr($author_id); ?>">
                            
                            <div class="j1-form-group">
                                <label for="j1-message-name-<?php echo esc_attr($post_id); ?>"><?php _e('Nome', 'j1_classificados'); ?> *</label>
                                <input type="text" id="j1-message-name-<?php echo esc_attr($post_id); ?>" name="name" value="<?php echo esc_attr($user_name); ?>" required>
                            </div>

                            <div class="j1-form-group">
                                <label for="j1-message-email-<?php echo esc_attr($post_id); ?>"><?php _e('Email', 'j1_classificados'); ?> *</label>
                                <input type="email" id="j1-message-email-<?php echo esc_attr($post_id); ?>" name="email" value="<?php echo esc_attr($user_email); ?>" required>
                            </div>

                            <div class="j1-form-group">
                                <label for="j1-message-message-<?php echo esc_attr($post_id); ?>"><?php _e('Mensagem', 'j1_classificados'); ?> *</label>
                                <textarea id="j1-message-message-<?php echo esc_attr($post_id); ?>" name="message" rows="4" required></textarea>
                            </div>

                            <div class="j1-form-actions">
                                <button type="submit" class="j1-message-submit">
                                    <?php _e('Enviar Mensagem', 'j1_classificados'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} 