/**
 * Widget de Mensagens - J1 Classificados
 * 
 * @package J1_Classificados
 */

jQuery(document).ready(function($) {
    'use strict';

    // Abrir modal
    $(document).on('click', '.j1-message-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var isUserLoggedIn = button.data('user-logged-in') === 'true';
        
        // Verificar se o usuário está logado
        if (!isUserLoggedIn) {
            // Mostrar alerta para usuário não logado
            if (typeof dokan_sweetalert !== 'undefined') {
                // Usar o SweetAlert do Dokan se disponível
                dokan_sweetalert(j1_message_ajax.strings.login_required, {
                    icon: 'warning',
                    confirmButtonText: j1_message_ajax.strings.login_now,
                    showCancelButton: true,
                    cancelButtonText: j1_message_ajax.strings.cancel,
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirecionar para página de login
                        window.location.href = j1_message_ajax.login_url;
                    }
                });
            } else {
                // Fallback para alert padrão
                if (confirm(j1_message_ajax.strings.login_required + '\n\n' + j1_message_ajax.strings.login_now_confirm)) {
                    window.location.href = j1_message_ajax.login_url;
                }
            }
            return;
        }
        
        var classifiedId = button.data('classified-id');
        var modal = $('#j1-message-modal-' + classifiedId);
        
        if (modal.length) {
            modal.addClass('show');
            $('body').addClass('modal-open');
            
            // Focar no primeiro campo vazio ou no campo de mensagem
            setTimeout(function() {
                var firstEmptyField = modal.find('input[type="text"], input[type="email"]').filter(function() {
                    return !$(this).val();
                }).first();
                
                if (firstEmptyField.length) {
                    firstEmptyField.focus();
                } else {
                    modal.find('textarea').focus();
                }
            }, 300);
        }
    });

    // Fechar modal
    $(document).on('click', '.j1-message-modal-close, .j1-message-modal', function(e) {
        if (e.target === this) {
            var modal = $(this).closest('.j1-message-modal');
            closeModal(modal);
        }
    });

    // Fechar modal com ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            var openModal = $('.j1-message-modal.show');
            if (openModal.length) {
                closeModal(openModal);
            }
        }
    });

    // Enviar mensagem
    $(document).on('submit', '.j1-message-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('.j1-message-submit');
        var modal = form.closest('.j1-message-modal');
        var feedback = modal.find('.j1-message-feedback');
        
        // Validar campos
        var isValid = true;
        form.find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('error');
            } else {
                $(this).removeClass('error');
            }
        });

        if (!isValid) {
            showFeedback(modal, 'error', j1_message_ajax.strings.fill_required_fields);
            return;
        }

        // Mostrar loading
        submitBtn.addClass('loading').prop('disabled', true);
        hideFeedback(modal);

        // Preparar dados
        var formData = new FormData(form[0]);
        formData.append('action', 'j1_send_message');
        formData.append('nonce', j1_message_ajax.nonce);

        // Enviar via AJAX
        $.ajax({
            url: j1_message_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showFeedback(modal, 'success', response.data.message || j1_message_ajax.strings.success);
                    form[0].reset();
                    
                    // Preencher novamente os campos do usuário logado
                    if (j1_message_ajax.user_email) {
                        form.find('input[name="email"]').val(j1_message_ajax.user_email);
                    }
                    if (j1_message_ajax.user_name) {
                        form.find('input[name="name"]').val(j1_message_ajax.user_name);
                    }
                    
                    // Fechar modal após 2 segundos
                    setTimeout(function() {
                        closeModal(modal);
                    }, 2000);
                } else {
                    showFeedback(modal, 'error', response.data.message || j1_message_ajax.strings.error);
                }
            },
            error: function() {
                showFeedback(modal, 'error', j1_message_ajax.strings.error);
            },
            complete: function() {
                submitBtn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Função para fechar modal
    function closeModal(modal) {
        modal.removeClass('show');
        $('body').removeClass('modal-open');
        hideFeedback(modal);
        
        // Resetar formulário
        modal.find('form')[0].reset();
        modal.find('.error').removeClass('error');
        
        // Preencher novamente os campos do usuário logado
        if (j1_message_ajax.user_email) {
            modal.find('input[name="email"]').val(j1_message_ajax.user_email);
        }
        if (j1_message_ajax.user_name) {
            modal.find('input[name="name"]').val(j1_message_ajax.user_name);
        }
    }

    // Função para mostrar feedback
    function showFeedback(modal, type, message) {
        var feedback = modal.find('.j1-message-feedback');
        feedback.removeClass('success error').addClass(type).text(message).show();
        
        // Scroll para o feedback
        modal.find('.j1-message-modal-content').scrollTop(0);
    }

    // Função para esconder feedback
    function hideFeedback(modal) {
        modal.find('.j1-message-feedback').hide();
    }

    // Validação em tempo real
    $(document).on('input', '.j1-message-form input, .j1-message-form textarea', function() {
        $(this).removeClass('error');
    });

    // Prevenir fechamento do modal ao clicar no conteúdo
    $(document).on('click', '.j1-message-modal-content', function(e) {
        e.stopPropagation();
    });
}); 