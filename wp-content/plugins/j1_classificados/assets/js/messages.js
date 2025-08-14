/**
 * JavaScript para Sistema de Mensagens - J1 Classificados
 * 
 * @package J1_Classificados
 * @since 1.2.0
 */

(function($) {
    'use strict';

    // Variáveis globais
    let j1MessageModal = {
        isOpen: false,
        currentClassifiedId: null,
        notificationTimeout: null
    };

    // Inicializar quando o DOM estiver pronto
    $(document).ready(function() {
        j1_init_messages_system();
        
        // Detectar mudanças de login/logout
        j1_init_login_detection();
    });

    /**
     * Inicializar sistema de mensagens
     */
    function j1_init_messages_system() {
        // Carregar estilos CSS
        j1_load_messages_css();
        
        // Inicializar notificações desktop
        j1_init_desktop_notifications();
        
        // Inicializar contador de mensagens não lidas
        j1_init_unread_counter();
        
        // Inicializar modal de mensagem
        j1_init_message_modal();
        
        // Inicializar funcionalidades do dashboard
        if (j1_is_dashboard_page()) {
            j1_init_dashboard_features();
        }
    }

    /**
     * Carregar CSS do sistema de mensagens
     */
    function j1_load_messages_css() {
        // CSS já é carregado via PHP, não precisamos carregar via JS
        // Esta função foi removida para evitar problemas de carregamento
    }

    /**
     * Inicializar notificações desktop
     */
    function j1_init_desktop_notifications() {
        if ('Notification' in window && Notification.permission === 'default') {
            // Solicitar permissão para notificações
            Notification.requestPermission();
        }
    }

    /**
     * Inicializar contador de mensagens não lidas
     */
    function j1_init_unread_counter() {
        // Atualizar contador a cada 30 segundos
        setInterval(function() {
            j1_update_unread_count();
        }, 30000);

        // Atualizar contador inicial
        j1_update_unread_count();
    }

    /**
     * Atualizar contador de mensagens não lidas
     */
    function j1_update_unread_count() {
        if (!j1_classifieds_ajax || !j1_classifieds_ajax.ajax_url) {
            return;
        }

        $.ajax({
            url: j1_classifieds_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'j1_get_unread_count',
                nonce: j1_classifieds_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.count > 0) {
                    j1_show_unread_badge(response.data.count);
                } else {
                    j1_hide_unread_badge();
                }
            },
            error: function() {
                // Silenciar erros para não poluir o console
            }
        });
    }

    /**
     * Mostrar badge de mensagens não lidas
     */
    function j1_show_unread_badge(count) {
        // Procurar por menu de mensagens no Dokan
        let $messagesMenu = $('.dokan-dashboard-navigation a[href*="messages"]');
        
        if ($messagesMenu.length) {
            // Remover badge existente
            $messagesMenu.find('.j1-unread-badge').remove();
            
            // Adicionar novo badge
            $messagesMenu.append('<span class="j1-unread-badge">' + count + '</span>');
        }

        // Mostrar notificação desktop se permitido
        if (Notification.permission === 'granted' && count > 0) {
            j1_show_desktop_notification(count);
        }
    }

    /**
     * Esconder badge de mensagens não lidas
     */
    function j1_hide_unread_badge() {
        $('.j1-unread-badge').remove();
    }

    /**
     * Mostrar notificação desktop
     */
    function j1_show_desktop_notification(count) {
        if (Notification.permission === 'granted') {
            new Notification('Novas Mensagens', {
                body: 'Você tem ' + count + ' mensagem(ns) não lida(s)',
                icon: '/wp-content/plugins/j1_classificados/assets/images/icon.png',
                tag: 'j1-messages'
            });
        }
    }

    /**
     * Inicializar modal de mensagem
     */
    function j1_init_message_modal() {
        // Event listeners para fechar modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && j1MessageModal.isOpen) {
                j1_close_message_modal();
            }
        });

        $(document).on('click', '.j1-modal-overlay', function() {
            j1_close_message_modal();
        });
    }

    /**
     * Carregar modal de mensagem via AJAX
     */
    function j1_load_message_modal(callback) {
        if (!j1_classifieds_ajax || !j1_classifieds_ajax.ajax_url) {
            if (callback) callback();
            return;
        }

        $.ajax({
            url: j1_classifieds_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'j1_load_message_modal',
                nonce: j1_classifieds_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('body').append(response.data.html);
                    if (callback) callback();
                } else {
                    if (callback) callback();
                }
            },
            error: function() {
                if (callback) callback();
            }
        });
    }

    /**
     * Abrir modal de mensagem
     */
    window.j1_open_message_modal = function(classifiedId) {
        console.log('j1_open_message_modal chamado com ID:', classifiedId);
        
        // Verificar se o usuário está logado (verificação mais robusta)
        if (!j1_is_user_logged_in()) {
            console.log('Usuário não está logado');
            j1_show_error('Você precisa estar logado para enviar mensagens.');
            return;
        }

        console.log('Usuário está logado, verificando se não está enviando para si mesmo');

        // Verificar se não está enviando para si mesmo
        if (classifiedId === j1_classifieds_ajax.current_user.id) {
            console.log('Usuário tentando enviar mensagem para si mesmo');
            j1_show_error('Você não pode enviar mensagem para si mesmo.');
            return;
        }

        console.log('Configurando modal para classificado:', classifiedId);
        j1MessageModal.currentClassifiedId = classifiedId;
        j1MessageModal.isOpen = true;

        // Mostrar modal diretamente (já está incluído na página)
        if ($('#j1-message-modal').length) {
            console.log('Modal encontrado, mostrando...');
            j1_show_modal();
        } else {
            console.log('Modal não encontrado na página');
            j1_show_error('Modal não encontrado. Recarregue a página.');
        }
    };

    /**
     * Mostrar modal
     */
    function j1_show_modal() {
        $('#j1-message-modal').fadeIn(300);
        $('body').css('overflow', 'hidden');

        // Focar no campo de mensagem
        setTimeout(function() {
            $('#j1-message-content').focus();
        }, 300);

        // Carregar dados do classificado
        j1_load_classified_info(j1MessageModal.currentClassifiedId);
    }

    /**
     * Fechar modal de mensagem
     */
    window.j1_close_message_modal = function() {
        j1MessageModal.isOpen = false;
        j1MessageModal.currentClassifiedId = null;

        $('#j1-message-modal').fadeOut(300);
        $('body').css('overflow', '');

        // Limpar formulário
        $('#j1-message-form')[0].reset();
        $('#j1-char-count').text('0');

        // Esconder notificações
        $('.j1-notification').fadeOut(300);
    };

    /**
     * Carregar informações do classificado
     */
    function j1_load_classified_info(classifiedId) {
        // Implementar carregamento de informações se necessário
        // Por enquanto, as informações já estão no modal
    }

    /**
     * Enviar mensagem
     */
    $(document).on('submit', '#j1-message-form', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#j1-send-message-btn');
        const $btnText = $submitBtn.find('.btn-text');
        const $btnLoading = $submitBtn.find('.btn-loading');

        // Validar campos
        const message = $('#j1-message-content').val().trim();
        if (!message) {
            j1_show_error('Por favor, digite sua mensagem.');
            return;
        }

        // Mostrar loading
        $submitBtn.prop('disabled', true);
        $btnText.hide();
        $btnLoading.show();

        // Preparar dados
        const formData = new FormData();
        formData.append('action', 'j1_send_message');
        formData.append('nonce', j1_classifieds_ajax.nonce);
        formData.append('classified_id', j1MessageModal.currentClassifiedId);
        formData.append('subject', $('#j1-message-subject').val().trim());
        formData.append('message', message);

        // Enviar via AJAX
        $.ajax({
            url: j1_classifieds_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    j1_show_success('Mensagem enviada com sucesso!');
                    
                    // Fechar modal após 2 segundos
                    setTimeout(function() {
                        j1_close_message_modal();
                    }, 2000);

                    // Atualizar contador de mensagens
                    j1_update_unread_count();
                } else {
                    j1_show_error(response.data || 'Erro ao enviar mensagem.');
                }
            },
            error: function() {
                j1_show_error('Erro de conexão. Tente novamente.');
            },
            complete: function() {
                // Restaurar botão
                $submitBtn.prop('disabled', false);
                $btnText.show();
                $btnLoading.hide();
            }
        });
    });

    /**
     * Contador de caracteres
     */
    $(document).on('input', '#j1-message-content', function() {
        const maxLength = 2000;
        const currentLength = this.value.length;
        const $charCount = $('#j1-char-count');

        $charCount.text(currentLength);

        if (currentLength > maxLength * 0.9) {
            $charCount.css('color', '#ff6b6b');
        } else {
            $charCount.css('color', '#666');
        }
    });

    /**
     * Mostrar notificação de sucesso
     */
    function j1_show_success(message) {
        j1_show_notification(message, 'success');
    }

    /**
     * Mostrar notificação de erro
     */
    function j1_show_error(message) {
        j1_show_notification(message, 'error');
    }

    /**
     * Mostrar notificação
     */
    function j1_show_notification(message, type) {
        // Remover notificações existentes
        $('.j1-notification').remove();

        // Criar nova notificação
        const $notification = $('<div>', {
            class: 'j1-notification j1-notification-' + type,
            html: `
                <div class="j1-notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `
        });

        $('body').append($notification);

        // Mostrar notificação
        $notification.fadeIn(300);

        // Esconder após 5 segundos
        clearTimeout(j1MessageModal.notificationTimeout);
        j1MessageModal.notificationTimeout = setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Verificar se estamos na página do dashboard
     */
    function j1_is_dashboard_page() {
        return $('.dokan-dashboard-wrap').length > 0;
    }

    /**
     * Verificar se o usuário está logado (verificação robusta)
     */
    function j1_is_user_logged_in() {
        // Verificar se temos dados do usuário no objeto AJAX
        if (j1_classifieds_ajax && j1_classifieds_ajax.current_user && j1_classifieds_ajax.current_user.id) {
            return true;
        }
        
        // Verificar se há elementos que indicam que o usuário está logado
        if ($('.logged-in').length > 0 || $('.wp-admin-bar').length > 0) {
            return true;
        }
        
        // Verificar se há cookies de login
        if (document.cookie.indexOf('wordpress_logged_in_') !== -1) {
            return true;
        }
        
        return false;
    }

    /**
     * Atualizar dados do usuário via AJAX
     */
    function j1_update_user_data() {
        if (!j1_classifieds_ajax || !j1_classifieds_ajax.ajax_url) {
            return;
        }

        $.ajax({
            url: j1_classifieds_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'j1_get_current_user',
                nonce: j1_classifieds_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Atualizar dados do usuário no objeto global
                    j1_classifieds_ajax.current_user = response.data;
                }
            }
        });
    }

    /**
     * Inicializar funcionalidades do dashboard
     */
    function j1_init_dashboard_features() {
        // Marcar mensagem como lida
        $(document).on('click', '.j1-mark-read', function() {
            const messageId = $(this).data('message-id');
            j1_mark_message_read(messageId);
        });

        // Marcar todas as mensagens como lidas
        $(document).on('click', '.j1-mark-all-read', function() {
            const classifiedId = $(this).data('classified-id');
            const senderId = $(this).data('sender-id');
            j1_mark_all_messages_read(classifiedId, senderId);
        });
    }

    /**
     * Marcar mensagem como lida
     */
    function j1_mark_message_read(messageId) {
        $.ajax({
            url: j1_classifieds_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'j1_mark_message_read',
                nonce: j1_classifieds_ajax.nonce,
                message_id: messageId
            },
            success: function(response) {
                if (response.success) {
                    // Atualizar interface
                    const $messageItem = $(`[data-message-id="${messageId}"]`);
                    if ($messageItem.length) {
                        $messageItem.removeClass('j1-unread').addClass('j1-read');
                        
                        // Remover indicador de não lida
                        $messageItem.find('.j1-unread-indicator').remove();
                        
                        // Remover botão de marcar como lida
                        $messageItem.find('.j1-mark-read').remove();
                        
                        // Atualizar contadores
                        j1_update_unread_count();
                    }
                }
            }
        });
    }

    /**
     * Marcar todas as mensagens como lidas
     */
    function j1_mark_all_messages_read(classifiedId, senderId) {
        // Implementar funcionalidade para marcar todas como lidas
        // Esta função pode ser expandida conforme necessário
        console.log('Funcionalidade em desenvolvimento');
    }

    /**
     * Inicializar detecção de mudanças de login
     */
    function j1_init_login_detection() {
        // Verificar a cada 2 segundos se o status de login mudou
        setInterval(function() {
            const wasLoggedIn = j1_classifieds_ajax.current_user && j1_classifieds_ajax.current_user.id;
            const isCurrentlyLoggedIn = j1_is_user_logged_in();
            
            // Se o status mudou, atualizar dados
            if (isCurrentlyLoggedIn && !wasLoggedIn) {
                j1_update_user_data();
            }
        }, 2000);
    }

    /**
     * Funções globais para uso externo
     */
    window.j1Messages = {
        openModal: window.j1_open_message_modal,
        closeModal: window.j1_close_message_modal,
        updateCount: j1_update_unread_count,
        showSuccess: j1_show_success,
        showError: j1_show_error
    };

})(jQuery);
