/**
 * Widget de Mensagens - J1 Classificados
 * 
 * @package J1_Classificados
 */

(function($) {
    'use strict';

    // Classe principal do widget de mensagens
    class J1MessageWidget {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Abrir modal quando clicar no botão
            $(document).on('click', '.j1-message-button', (e) => {
                e.preventDefault();
                this.openModal($(e.currentTarget));
            });

            // Fechar modal quando clicar no X
            $(document).on('click', '.j1-message-modal-close', (e) => {
                e.preventDefault();
                this.closeModal($(e.currentTarget).closest('.j1-message-modal'));
            });

            // Fechar modal quando clicar fora dele
            $(document).on('click', '.j1-message-modal', (e) => {
                if ($(e.target).hasClass('j1-message-modal')) {
                    this.closeModal($(e.target));
                }
            });

            // Fechar modal com ESC
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    const openModal = $('.j1-message-modal.show');
                    if (openModal.length) {
                        this.closeModal(openModal);
                    }
                }
            });

            // Enviar mensagem
            $(document).on('submit', '.j1-message-form', (e) => {
                e.preventDefault();
                this.sendMessage($(e.currentTarget));
            });
        }

        openModal(button) {
            const classifiedId = button.data('classified-id');
            const modal = $(`#j1-message-modal-${classifiedId}`);
            
            if (modal.length) {
                modal.addClass('show');
                $('body').addClass('j1-modal-open');
                
                // Focar no primeiro campo
                setTimeout(() => {
                    modal.find('input[name="name"]').focus();
                }, 300);
            }
        }

        closeModal(modal) {
            modal.removeClass('show').addClass('fade-out');
            $('body').removeClass('j1-modal-open');
            
            // Limpar formulário
            modal.find('.j1-message-form')[0].reset();
            modal.find('.j1-message-feedback').remove();
            
            // Remover classe de fade-out após animação
            setTimeout(() => {
                modal.removeClass('fade-out');
            }, 300);
        }

        sendMessage(form) {
            const submitButton = form.find('.j1-message-submit');
            const modal = form.closest('.j1-message-modal');
            
            // Verificar se já está enviando
            if (submitButton.hasClass('loading')) {
                return;
            }

            // Validar formulário
            if (!this.validateForm(form)) {
                return;
            }

            // Mostrar loading
            submitButton.addClass('loading').text(j1_message_ajax.strings.sending);

            // Preparar dados
            const formData = new FormData(form[0]);
            formData.append('action', 'j1_send_message');
            formData.append('nonce', j1_message_ajax.nonce);

            // Enviar via AJAX
            $.ajax({
                url: j1_message_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.handleResponse(response, modal, form);
                },
                error: (xhr, status, error) => {
                    this.handleError(error, modal);
                },
                complete: () => {
                    submitButton.removeClass('loading').text('Enviar Mensagem');
                }
            });
        }

        validateForm(form) {
            let isValid = true;
            const requiredFields = form.find('[required]');

            // Limpar mensagens de erro anteriores
            form.find('.j1-form-group').removeClass('error');
            form.find('.j1-error-message').remove();

            requiredFields.each((index, field) => {
                const $field = $(field);
                const value = $field.val().trim();
                const fieldName = $field.attr('name');

                if (!value) {
                    this.showFieldError($field, 'Este campo é obrigatório.');
                    isValid = false;
                } else if (fieldName === 'email' && !this.isValidEmail(value)) {
                    this.showFieldError($field, 'Digite um email válido.');
                    isValid = false;
                }
            });

            return isValid;
        }

        showFieldError(field, message) {
            const formGroup = field.closest('.j1-form-group');
            formGroup.addClass('error');
            
            if (!formGroup.find('.j1-error-message').length) {
                formGroup.append(`<div class="j1-error-message">${message}</div>`);
            }
        }

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        handleResponse(response, modal, form) {
            if (response.success) {
                // Sucesso
                this.showFeedback(modal, j1_message_ajax.strings.success, 'success');
                
                // Limpar formulário
                form[0].reset();
                
                // Fechar modal após 2 segundos
                setTimeout(() => {
                    this.closeModal(modal);
                }, 2000);
            } else {
                // Erro
                const errorMessage = response.data && response.data.message 
                    ? response.data.message 
                    : j1_message_ajax.strings.error;
                this.showFeedback(modal, errorMessage, 'error');
            }
        }

        handleError(error, modal) {
            console.error('Erro ao enviar mensagem:', error);
            this.showFeedback(modal, j1_message_ajax.strings.error, 'error');
        }

        showFeedback(modal, message, type) {
            // Remover feedback anterior
            modal.find('.j1-message-feedback').remove();
            
            // Criar novo feedback
            const feedback = $(`<div class="j1-message-feedback ${type}">${message}</div>`);
            
            // Inserir no início do formulário
            modal.find('.j1-message-form').prepend(feedback);
            
            // Scroll para o feedback
            modal.scrollTop(0);
        }
    }

    // Inicializar quando o documento estiver pronto
    $(document).ready(() => {
        new J1MessageWidget();
    });

})(jQuery); 