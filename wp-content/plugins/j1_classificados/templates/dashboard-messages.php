<?php
/**
 * Dashboard de Mensagens para Vendedores
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

if (!defined('ABSPATH')) exit;

// Verificar se o usuário está logado e é vendedor
if (!is_user_logged_in()) {
    wp_die(__('Você precisa estar logado para acessar esta página.', 'j1_classificados'));
}

if (!dokan_is_seller_enabled(get_current_user_id())) {
    wp_die(__('Você precisa ser um vendedor habilitado para acessar esta página.', 'j1_classificados'));
}

// Debug: Mostrar informações do usuário
$current_user = wp_get_current_user();
echo '<!-- Debug: Usuário ID: ' . esc_html($current_user->ID) . ' | Nome: ' . esc_html($current_user->display_name) . ' | Vendedor: ' . (dokan_is_seller_enabled($current_user->ID) ? 'Sim' : 'Não') . ' -->';

$current_user_id = get_current_user_id();
$classified_id = isset($_GET['classified_id']) ? intval($_GET['classified_id']) : 0;

get_header();
?>

<?php do_action('dokan_dashboard_wrap_start'); ?>

<div class="dokan-dashboard-wrap">
    <?php do_action('dokan_dashboard_content_before'); ?>

    <div class="dokan-dashboard-content dokan-messages-content">
        <?php do_action('dokan_product_content_inside_area_before'); ?>

        <header class="dokan-dashboard-header">
            <h1 class="entry-title">
                <?php if ($classified_id) : ?>
                    <?php 
                    $classified = get_post($classified_id);
                    if ($classified && intval($classified->post_author) === intval($current_user_id)) {
                        echo sprintf(__('Mensagens sobre: %s', 'j1_classificados'), $classified->post_title);
                    } else {
                        echo __('Mensagens', 'j1_classificados');
                    }
                    ?>
                <?php else : ?>
                    <?php esc_html_e('Mensagens', 'j1_classificados'); ?>
                <?php endif; ?>
            </h1>
            
            <?php if ($classified_id) : ?>
                <a href="<?php echo dokan_get_navigation_url('messages'); ?>" class="dokan-btn dokan-btn-default">
                    <i class="fas fa-arrow-left"></i> <?php esc_html_e('Voltar para todas as mensagens', 'j1_classificados'); ?>
                </a>
            <?php endif; ?>
        </header>

        <div class="dokan-messages-container">
            <?php if ($classified_id) : ?>
                <!-- Debug: Informações do classificado -->
                <?php 
                $debug_classified = get_post($classified_id);
                if ($debug_classified) {
                    echo '<!-- Debug: Classificado ID: ' . esc_html($classified_id) . ' | Título: ' . esc_html($debug_classified->post_title) . ' | Autor: ' . esc_html($debug_classified->post_author) . ' (tipo: ' . gettype($debug_classified->post_author) . ') | Status: ' . esc_html($debug_classified->post_status) . ' -->';
                } else {
                    echo '<!-- Debug: Classificado ID ' . esc_html($classified_id) . ' não encontrado -->';
                }
                ?>
                
                <!-- Mensagens de um classificado específico -->
                <?php render_classified_messages($classified_id, $current_user_id); ?>
            <?php else : ?>
                <!-- Lista de todos os classificados com mensagens -->
                <?php render_all_messages($current_user_id); ?>
            <?php endif; ?>
        </div>

        <?php do_action('dokan_product_content_inside_area_after'); ?>
    </div>

    <?php do_action('dokan_dashboard_content_after'); ?>
</div>

<?php do_action('dokan_dashboard_wrap_end'); ?>

<?php get_footer(); ?>

<?php
/**
 * Renderizar mensagens de um classificado específico
 */
function render_classified_messages($classified_id, $user_id) {
    global $wpdb;
    
    $table_messages = $wpdb->prefix . 'j1_messages';
    $classified = get_post($classified_id);
    
    // Debug: Verificar se o classificado existe
    if (!$classified) {
        echo '<div class="dokan-message dokan-error">';
        echo '<strong>Erro:</strong> Classificado ID ' . esc_html($classified_id) . ' não encontrado.';
        echo '</div>';
        return;
    }
    
    // Debug: Verificar se o usuário é o autor
    // Converter ambos para inteiros para comparação correta
    $author_id = intval($classified->post_author);
    $current_user_id = intval($user_id);
    
    if ($author_id !== $current_user_id) {
        echo '<div class="dokan-message dokan-error">';
        echo '<strong>Erro de Permissão:</strong> ';
        echo 'Você não é o autor deste classificado. ';
        echo 'Classificado ID: ' . esc_html($classified_id) . ' | ';
        echo 'Autor ID: ' . esc_html($classified->post_author) . ' (tipo: ' . gettype($classified->post_author) . ') | ';
        echo 'Seu ID: ' . esc_html($user_id) . ' (tipo: ' . gettype($user_id) . ') | ';
        echo 'Autor convertido: ' . esc_html($author_id) . ' | ';
        echo 'Seu ID convertido: ' . esc_html($current_user_id);
        echo '</div>';
        return;
    }
    
    // Obter mensagens organizadas por usuário
    $messages = $wpdb->get_results($wpdb->prepare(
        "SELECT m.*, u.display_name as sender_name, u.user_email as sender_email
         FROM $table_messages m
         LEFT JOIN {$wpdb->users} u ON m.sender_id = u.ID
         WHERE m.classified_id = %d
         ORDER BY m.created_at ASC",
        $classified_id
    ));
    
    if (empty($messages)) {
        echo '<div class="dokan-message dokan-info">' . __('Nenhuma mensagem encontrada para este classificado.', 'j1_classificados') . '</div>';
        return;
    }
    
    // Organizar por usuário
    $organized = [];
    foreach ($messages as $message) {
        $sender_id = $message->sender_id;
        if (!isset($organized[$sender_id])) {
            $organized[$sender_id] = [
                'user_id' => $sender_id,
                'user_name' => $message->sender_name,
                'user_email' => $message->sender_email,
                'messages' => [],
                'unread_count' => 0,
                'total_count' => 0
            ];
        }
        
        $organized[$sender_id]['messages'][] = $message;
        $organized[$sender_id]['total_count']++;
        
        if (!$message->is_read) {
            $organized[$sender_id]['unread_count']++;
        }
    }
    
    // Exibir conversas organizadas
    foreach ($organized as $sender_id => $conversation) {
        ?>
        <div class="j1-conversation-item" data-sender-id="<?php echo esc_attr($sender_id); ?>">
            <div class="j1-conversation-header">
                <div class="j1-user-info">
                    <h3><?php echo esc_html($conversation['user_name']); ?></h3>
                    <p class="j1-user-email"><?php echo esc_html($conversation['user_email']); ?></p>
                    <div class="j1-message-stats">
                        <span class="j1-total-count"><?php echo esc_html($conversation['total_count']); ?> mensagens</span>
                        <?php if ($conversation['unread_count'] > 0) : ?>
                            <span class="j1-unread-badge"><?php echo esc_html($conversation['unread_count']); ?> não lidas</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="j1-conversation-actions">
                    <button type="button" class="j1-btn j1-btn-primary j1-mark-all-read" 
                            data-classified-id="<?php echo esc_attr($classified_id); ?>" 
                            data-sender-id="<?php echo esc_attr($sender_id); ?>">
                        <?php esc_html_e('Marcar todas como lidas', 'j1_classificados'); ?>
                    </button>
                </div>
            </div>
            
            <div class="j1-messages-list">
                <?php foreach ($conversation['messages'] as $message) : ?>
                    <div class="j1-message-item <?php echo $message->is_read ? 'j1-read' : 'j1-unread'; ?>" 
                         data-message-id="<?php echo esc_attr($message->id); ?>">
                        <div class="j1-message-header">
                            <span class="j1-message-date">
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->created_at))); ?>
                            </span>
                            <?php if (!$message->is_read) : ?>
                                <span class="j1-unread-indicator"><?php esc_html_e('Nova', 'j1_classificados'); ?></span>
                            <?php endif; ?>
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
                        
                        <div class="j1-message-actions">
                            <?php if (!$message->is_read) : ?>
                                <button type="button" class="j1-btn j1-btn-small j1-mark-read" 
                                        data-message-id="<?php echo esc_attr($message->id); ?>">
                                    <?php esc_html_e('Marcar como lida', 'j1_classificados'); ?>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Botão para responder -->
                            <button type="button" class="j1-btn j1-btn-small j1-btn-primary j1-reply-btn" 
                                    data-message-id="<?php echo esc_attr($message->id); ?>"
                                    data-sender-id="<?php echo esc_attr($sender_id); ?>"
                                    data-classified-id="<?php echo esc_attr($classified_id); ?>">
                                <?php esc_html_e('Responder', 'j1_classificados'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Renderizar lista de todos os classificados com mensagens
 */
function render_all_messages($user_id) {
    global $wpdb;
    
    $table_messages = $wpdb->prefix . 'j1_messages';
    
    // Obter classificados com mensagens
    $classifieds_with_messages = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT m.classified_id, p.post_title, 
                COUNT(m.id) as total_messages,
                SUM(CASE WHEN m.is_read = 0 THEN 1 ELSE 0 END) as unread_messages,
                MAX(m.created_at) as last_message_date
         FROM $table_messages m
         LEFT JOIN {$wpdb->posts} p ON m.classified_id = p.ID
         WHERE m.receiver_id = %d
         GROUP BY m.classified_id
         ORDER BY last_message_date DESC",
        $user_id
    ));
    
    if (empty($classifieds_with_messages)) {
        echo '<div class="dokan-message dokan-info">' . __('Você ainda não recebeu nenhuma mensagem.', 'j1_classificados') . '</div>';
        return;
    }
    
    ?>
    <div class="j1-classifieds-messages-list">
        <table class="dokan-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Classificado', 'j1_classificados'); ?></th>
                    <th><?php esc_html_e('Total de Mensagens', 'j1_classificados'); ?></th>
                    <th><?php esc_html_e('Não Lidas', 'j1_classificados'); ?></th>
                    <th><?php esc_html_e('Última Mensagem', 'j1_classificados'); ?></th>
                    <th><?php esc_html_e('Ações', 'j1_classificados'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classifieds_with_messages as $item) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($item->post_title); ?></strong>
                        </td>
                        <td>
                            <span class="j1-message-count"><?php echo esc_html($item->total_messages); ?></span>
                        </td>
                        <td>
                            <?php if ($item->unread_messages > 0) : ?>
                                <span class="j1-unread-badge"><?php echo esc_html($item->unread_messages); ?></span>
                            <?php else : ?>
                                <span class="j1-all-read"><?php esc_html_e('Todas lidas', 'j1_classificados'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($item->last_message_date))); ?>
                        </td>
                        <td>
                            <a href="<?php echo dokan_get_navigation_url('messages'); ?>?classified_id=<?php echo esc_attr($item->classified_id); ?>" 
                               class="dokan-btn dokan-btn-default dokan-btn-sm">
                                <i class="fas fa-eye"></i> <?php esc_html_e('Ver Mensagens', 'j1_classificados'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>

<!-- Modal de Resposta -->
<div id="j1-reply-modal" class="j1-reply-modal" style="display: none;">
    <div class="j1-modal-content">
        <div class="j1-modal-header">
            <h3><?php esc_html_e('Responder Mensagem', 'j1_classificados'); ?></h3>
            <button type="button" class="j1-modal-close">&times;</button>
        </div>
        
        <div class="j1-modal-body">
            <form id="j1-reply-form">
                <input type="hidden" id="j1-reply-classified-id" name="classified_id" value="">
                <input type="hidden" id="j1-reply-sender-id" name="sender_id" value="">
                <input type="hidden" id="j1-reply-message-id" name="message_id" value="">
                
                <div class="j1-form-group">
                    <label for="j1-reply-subject"><?php esc_html_e('Assunto:', 'j1_classificados'); ?></label>
                    <input type="text" id="j1-reply-subject" name="subject" class="j1-form-control" 
                           placeholder="<?php esc_attr_e('Assunto da resposta', 'j1_classificados'); ?>" required>
                </div>
                
                <div class="j1-form-group">
                    <label for="j1-reply-message"><?php esc_html_e('Mensagem:', 'j1_classificados'); ?></label>
                    <textarea id="j1-reply-message" name="message" class="j1-form-control" rows="5" 
                              placeholder="<?php esc_attr_e('Digite sua resposta...', 'j1_classificados'); ?>" required></textarea>
                    <div class="j1-char-count">
                        <span id="j1-reply-char-count">0</span> / 1000 caracteres
                    </div>
                </div>
                
                <div class="j1-form-actions">
                    <button type="button" class="j1-btn j1-btn-default j1-modal-cancel">
                        <?php esc_html_e('Cancelar', 'j1_classificados'); ?>
                    </button>
                    <button type="submit" class="j1-btn j1-btn-primary">
                        <?php esc_html_e('Enviar Resposta', 'j1_classificados'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts para funcionalidades -->
<script>
// Objeto AJAX local para o dashboard
const j1_classifieds_ajax = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('j1_message_nonce'); ?>'
};

// Marcar mensagem como lida
function j1_mark_message_read(messageId) {
    const formData = new FormData();
    formData.append('action', 'j1_mark_message_read');
    formData.append('nonce', j1_classifieds_ajax.nonce);
    formData.append('message_id', messageId);
    
    // Verificar se o objeto AJAX está disponível
    if (!j1_classifieds_ajax || !j1_classifieds_ajax.ajax_url) {
        console.error('Erro: Sistema AJAX não disponível');
        return;
    }
    
    fetch(j1_classifieds_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar interface
            const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
            if (messageItem) {
                messageItem.classList.remove('j1-unread');
                messageItem.classList.add('j1-read');
                
                // Remover indicador de não lida
                const unreadIndicator = messageItem.querySelector('.j1-unread-indicator');
                if (unreadIndicator) {
                    unreadIndicator.remove();
                }
                
                // Remover botão de marcar como lida
                const markReadBtn = messageItem.querySelector('.j1-mark-read');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
                
                // Atualizar contadores
                j1_update_unread_count();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Marcar todas as mensagens de um usuário como lidas
function j1_mark_all_read(classifiedId, senderId) {
    // Implementar lógica para marcar todas como lidas
    // Esta função pode ser expandida conforme necessário
    console.log('Marcar todas como lidas:', classifiedId, senderId);
}

// Atualizar contador de mensagens não lidas
function j1_update_unread_count() {
    // Implementar atualização do contador
    // Esta função pode ser expandida conforme necessário
}

// Atualizar contadores quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    j1_update_unread_count();
    
    // Inicializar funcionalidades do modal de resposta
    j1_init_reply_modal();
});

// Inicializar modal de resposta
function j1_init_reply_modal() {
    // Botões para abrir modal
    document.querySelectorAll('.j1-reply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const messageId = this.getAttribute('data-message-id');
            const senderId = this.getAttribute('data-sender-id');
            const classifiedId = this.getAttribute('data-classified-id');
            
            j1_open_reply_modal(messageId, senderId, classifiedId);
        });
    });
    
    // Botão fechar modal
    document.querySelector('.j1-modal-close').addEventListener('click', j1_close_reply_modal);
    document.querySelector('.j1-modal-cancel').addEventListener('click', j1_close_reply_modal);
    
    // Contador de caracteres
    const messageTextarea = document.getElementById('j1-reply-message');
    const charCount = document.getElementById('j1-reply-char-count');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            if (length > 1000) {
                charCount.style.color = '#e74c3c';
            } else if (length > 800) {
                charCount.style.color = '#f39c12';
            } else {
                charCount.style.color = '#666';
            }
        });
    }
    
    // Formulário de resposta
    document.getElementById('j1-reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        j1_send_reply();
    });
}

// Abrir modal de resposta
function j1_open_reply_modal(messageId, senderId, classifiedId) {
    document.getElementById('j1-reply-classified-id').value = classifiedId;
    document.getElementById('j1-reply-sender-id').value = senderId;
    document.getElementById('j1-reply-message-id').value = messageId;
    
    // Obter o assunto da mensagem original para preencher automaticamente
    const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
    let originalSubject = '';
    
    if (messageItem) {
        const subjectElement = messageItem.querySelector('.j1-message-subject strong');
        if (subjectElement && subjectElement.nextSibling) {
            // Pegar o texto após "Assunto: "
            originalSubject = subjectElement.nextSibling.textContent.trim();
        }
    }
    
    // Preencher assunto com "Re: " + assunto original, ou "Re: Mensagem" se não houver assunto
    const replySubject = originalSubject ? `Re: ${originalSubject}` : 'Re: Mensagem';
    document.getElementById('j1-reply-subject').value = replySubject;
    
    // Limpar campo de mensagem
    document.getElementById('j1-reply-message').value = '';
    document.getElementById('j1-reply-char-count').textContent = '0';
    
    // Mostrar modal
    document.getElementById('j1-reply-modal').style.display = 'block';
}

// Fechar modal de resposta
function j1_close_reply_modal() {
    document.getElementById('j1-reply-modal').style.display = 'none';
}

// Enviar resposta
function j1_send_reply() {
    const formData = new FormData();
    formData.append('action', 'j1_send_reply');
    formData.append('nonce', j1_classifieds_ajax.nonce);
    formData.append('classified_id', document.getElementById('j1-reply-classified-id').value);
    formData.append('sender_id', document.getElementById('j1-reply-sender-id').value);
    formData.append('message_id', document.getElementById('j1-reply-message-id').value);
    formData.append('subject', document.getElementById('j1-reply-subject').value);
    formData.append('message', document.getElementById('j1-reply-message').value);
    
    // Mostrar loading
    const submitBtn = document.querySelector('#j1-reply-form button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Enviando...';
    submitBtn.disabled = true;
    
    // Verificar se o objeto AJAX está disponível
    if (!j1_classifieds_ajax || !j1_classifieds_ajax.ajax_url) {
        alert('Erro: Sistema AJAX não disponível. Recarregue a página e tente novamente.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    fetch(j1_classifieds_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            j1_close_reply_modal();
            
            // Mostrar mensagem de sucesso
            alert('Resposta enviada com sucesso!');
            
            // Recarregar página para mostrar a nova mensagem
            location.reload();
        } else {
            alert('Erro ao enviar resposta: ' + (data.data || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao enviar resposta. Tente novamente.');
    })
    .finally(() => {
        // Restaurar botão
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}
</script>
