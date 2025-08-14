<?php
/**
 * Modal de Mensagem para Classificados
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

if (!defined('ABSPATH')) exit;

// Verificar se estamos em uma página de classificado
if (!is_singular('classified')) {
    return;
}

$classified_id = get_the_ID();
$classified = get_post($classified_id);
$current_user = wp_get_current_user();
$is_user_logged_in = is_user_logged_in();

// Se não estiver logado, não mostrar o modal
if (!$is_user_logged_in) {
    return;
}

// Verificar se não está tentando enviar mensagem para si mesmo
if ($current_user->ID === $classified->post_author) {
    return;
}

$nonce = wp_create_nonce('j1_message_nonce');
?>

<!-- Modal de Mensagem -->
<div id="j1-message-modal" class="j1-message-modal" style="display: none;">
    <div class="j1-modal-overlay" onclick="j1_close_message_modal()"></div>
    
    <div class="j1-modal-content">
        <!-- Header do Modal -->
        <div class="j1-modal-header">
            <h3><?php esc_html_e('Enviar Mensagem', 'j1_classificados'); ?></h3>
            <button type="button" class="j1-modal-close" onclick="j1_close_message_modal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Corpo do Modal -->
        <div class="j1-modal-body">
            <!-- Informações do Classificado -->
            <div class="j1-classified-info">
                <h4><?php esc_html_e('Sobre:', 'j1_classificados'); ?></h4>
                <p><strong><?php echo esc_html($classified->post_title); ?></p>
                <?php if (has_post_thumbnail($classified_id)) : ?>
                    <div class="j1-classified-thumbnail">
                        <?php echo get_the_post_thumbnail($classified_id, 'thumbnail'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulário de Mensagem -->
            <form id="j1-message-form" class="j1-message-form">
                <?php wp_nonce_field('j1_message_nonce', 'j1_message_nonce'); ?>
                <input type="hidden" name="classified_id" value="<?php echo esc_attr($classified_id); ?>">
                
                <!-- Assunto (Opcional) -->
                <div class="j1-form-group">
                    <label for="j1-message-subject"><?php esc_html_e('Assunto (opcional):', 'j1_classificados'); ?></label>
                    <input type="text" 
                           id="j1-message-subject" 
                           name="subject" 
                           class="j1-form-control" 
                           placeholder="<?php esc_attr_e('Digite um assunto para sua mensagem...', 'j1_classificados'); ?>"
                           maxlength="255">
                </div>
                
                <!-- Mensagem -->
                <div class="j1-form-group">
                    <label for="j1-message-content"><?php esc_html_e('Mensagem:', 'j1_classificados'); ?> <span class="required">*</span></label>
                    <textarea id="j1-message-content" 
                              name="message" 
                              class="j1-form-control" 
                              rows="5" 
                              placeholder="<?php esc_attr_e('Digite sua mensagem aqui...', 'j1_classificados'); ?>"
                              required
                              maxlength="2000"></textarea>
                    <div class="j1-char-count">
                        <span id="j1-char-count">0</span> / 2000
                    </div>
                </div>
                
                <!-- Informações do Remetente -->
                <div class="j1-sender-info">
                    <h4><?php esc_html_e('Suas informações:', 'j1_classificados'); ?></h4>
                    <div class="j1-sender-details">
                        <p><strong><?php esc_html_e('Nome:', 'j1_classificados'); ?></strong> <?php echo esc_html($current_user->display_name); ?></p>
                        <p><strong><?php esc_html_e('Email:', 'j1_classificados'); ?></strong> <?php echo esc_html($current_user->user_email); ?></p>
                    </div>
                </div>
                
                <!-- Botões de Ação -->
                <div class="j1-form-actions">
                    <button type="button" class="j1-btn j1-btn-secondary" onclick="j1_close_message_modal()">
                        <?php esc_html_e('Cancelar', 'j1_classificados'); ?>
                    </button>
                    <button type="submit" class="j1-btn j1-btn-primary" id="j1-send-message-btn">
                        <span class="btn-text"><?php esc_html_e('Enviar Mensagem', 'j1_classificados'); ?></span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> <?php esc_html_e('Enviando...', 'j1_classificados'); ?>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="j1-message-loading" class="j1-loading-overlay" style="display: none;">
    <div class="j1-loading-content">
        <div class="j1-loading-spinner"></div>
        <p><?php esc_html_e('Enviando mensagem...', 'j1_classificados'); ?></p>
    </div>
</div>

<!-- Notificação de Sucesso -->
<div id="j1-message-success" class="j1-notification j1-notification-success" style="display: none;">
    <div class="j1-notification-content">
        <i class="fas fa-check-circle"></i>
        <span><?php esc_html_e('Mensagem enviada com sucesso!', 'j1_classificados'); ?></span>
    </div>
</div>

<!-- Notificação de Erro -->
<div id="j1-message-error" class="j1-notification j1-notification-error" style="display: none;">
    <div class="j1-notification-content">
        <i class="fas fa-exclamation-circle"></i>
        <span id="j1-error-message"><?php esc_html_e('Erro ao enviar mensagem.', 'j1_classificados'); ?></span>
    </div>
</div>

<script>
// Variáveis globais para o modal
window.j1MessageModal = {
    classifiedId: <?php echo esc_js($classified_id); ?>,
    nonce: '<?php echo esc_js($nonce); ?>',
    currentUser: {
        id: <?php echo esc_js($current_user->ID); ?>,
        name: '<?php echo esc_js($current_user->display_name); ?>',
        email: '<?php echo esc_js($current_user->user_email); ?>'
    }
};

// Função para abrir o modal
function j1_open_message_modal(classifiedId) {
    // Verificar se o usuário está logado
    if (!window.j1MessageModal.currentUser.id) {
        alert('<?php esc_html_e('Você precisa estar logado para enviar mensagens.', 'j1_classificados'); ?>');
        window.location.href = '<?php echo esc_url(wp_login_url(get_permalink())); ?>';
        return;
    }
    
    // Verificar se não está enviando para si mesmo
    if (classifiedId === window.j1MessageModal.currentUser.id) {
        alert('<?php esc_html_e('Você não pode enviar mensagem para si mesmo.', 'j1_classificados'); ?>');
        return;
    }
    
    // Mostrar o modal
    document.getElementById('j1-message-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Focar no campo de mensagem
    setTimeout(function() {
        document.getElementById('j1-message-content').focus();
    }, 100);
}

// Função para fechar o modal
function j1_close_message_modal() {
    document.getElementById('j1-message-modal').style.display = 'none';
    document.body.style.overflow = '';
    
    // Limpar formulário
    document.getElementById('j1-message-form').reset();
    document.getElementById('j1-char-count').textContent = '0';
    
    // Esconder notificações
    document.getElementById('j1-message-success').style.display = 'none';
    document.getElementById('j1-message-error').style.display = 'none';
}

// Contador de caracteres
document.getElementById('j1-message-content').addEventListener('input', function() {
    const maxLength = 2000;
    const currentLength = this.value.length;
    const charCount = document.getElementById('j1-char-count');
    
    charCount.textContent = currentLength;
    
    if (currentLength > maxLength * 0.9) {
        charCount.style.color = '#ff6b6b';
    } else {
        charCount.style.color = '#666';
    }
});

// Envio do formulário será gerenciado pelo arquivo messages.js

// Funções de notificação
function j1_show_success() {
    const notification = document.getElementById('j1-message-success');
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 5000);
}

function j1_show_error(message) {
    const notification = document.getElementById('j1-message-error');
    const errorText = document.getElementById('j1-error-message');
    
    errorText.textContent = message;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 5000);
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        j1_close_message_modal();
    }
});

// Fechar modal clicando fora
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('j1-modal-overlay')) {
        j1_close_message_modal();
    }
});
</script>
