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
                    if ($classified && $classified->post_author === $current_user_id) {
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
                    echo '<!-- Debug: Classificado ID: ' . esc_html($classified_id) . ' | Título: ' . esc_html($debug_classified->post_title) . ' | Autor: ' . esc_html($debug_classified->post_author) . ' | Status: ' . esc_html($debug_classified->post_status) . ' -->';
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
    if ($classified->post_author !== $user_id) {
        echo '<div class="dokan-message dokan-error">';
        echo '<strong>Erro de Permissão:</strong> ';
        echo 'Você não é o autor deste classificado. ';
        echo 'Classificado ID: ' . esc_html($classified_id) . ' | ';
        echo 'Autor ID: ' . esc_html($classified->post_author) . ' | ';
        echo 'Seu ID: ' . esc_html($user_id);
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

<!-- Scripts para funcionalidades -->
<script>
// Marcar mensagem como lida
function j1_mark_message_read(messageId) {
    const formData = new FormData();
    formData.append('action', 'j1_mark_message_read');
    formData.append('nonce', j1_classifieds_ajax.nonce);
    formData.append('message_id', messageId);
    
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
});
</script>
