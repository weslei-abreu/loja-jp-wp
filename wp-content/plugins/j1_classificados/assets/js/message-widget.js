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
        var classifiedId = $(this).data('classified-id');
        var modal = $('#j1-message-modal-' + classifiedId);
        
        if (modal.length) {
            modal.addClass('show');
            $('body').addClass('modal-open');
            
            // Focar no primeiro campo
            setTimeout(function() {
                modal.find('input[type="text"]').first().focus();
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
            showFeedback(modal, 'error', 'Por favor, preencha todos os campos obrigatórios.');
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