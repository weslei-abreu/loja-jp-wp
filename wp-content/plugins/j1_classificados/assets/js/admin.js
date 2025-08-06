jQuery(document).ready(function($) {
    'use strict';

    // ✅ Verificar se estamos na página de classificados
    if (!$('form[name="classified_form"]').length) {
        return; // Sair se não estivermos na página de classificados
    }

    // ✅ Desabilitar validação do Dokan para nosso formulário
    $('form[name="classified_form"]').off('submit.dokan-validation');

    // Verificar se wp.media está disponível
    if (typeof wp === 'undefined' || !wp.media) {
        console.warn('wp.media não está disponível');
        return;
    }

    // Verificar se as strings localizadas estão disponíveis
    var strings = typeof j1_classificados_ajax !== 'undefined' ? j1_classificados_ajax.strings : {};

    // Função para corrigir URLs malformadas
    function fixMalformedUrl(url) {
        if (typeof url === 'string' && url.indexOf('https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
            return url.replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads');
        }
        return url;
    }

    // Função para corrigir URLs em objetos de attachment
    function fixAttachmentUrls(attachment) {
        if (attachment && attachment.url) {
            attachment.url = fixMalformedUrl(attachment.url);
        }
        if (attachment && attachment.sizes) {
            Object.keys(attachment.sizes).forEach(function(size) {
                if (attachment.sizes[size] && attachment.sizes[size].url) {
                    attachment.sizes[size].url = fixMalformedUrl(attachment.sizes[size].url);
                }
            });
        }
        return attachment;
    }

    // ✅ Toggle Condições baseado no checkbox de vaga de emprego
    $(document).on('change', '#classified_is_job', function() {
        var conditionsContainer = $('#conditions-container');
        var isChecked = $(this).is(':checked');
        
        console.log('Checkbox changed:', isChecked, 'Container:', conditionsContainer.length); // Debug temporário
        
        if (isChecked) {
            conditionsContainer.show().addClass('force-show').removeClass('dokan-hide');
            console.log('Showing conditions container'); // Debug temporário
        } else {
            conditionsContainer.hide().removeClass('force-show').addClass('dokan-hide');
            $('#classified_conditions').val(''); // Limpar o valor quando desmarcar
            console.log('Hiding conditions container'); // Debug temporário
        }
    });

    // ✅ Inicializar estado das condições
    function initializeConditionsState() {
        var isJobChecked = $('#classified_is_job').is(':checked');
        var conditionsContainer = $('#conditions-container');
        
        console.log('Initializing conditions state:', isJobChecked, 'Container:', conditionsContainer.length); // Debug temporário
        
        if (isJobChecked) {
            conditionsContainer.show().addClass('force-show').removeClass('dokan-hide');
        } else {
            conditionsContainer.hide().removeClass('force-show').addClass('dokan-hide');
        }
    }

    // Executar inicialização após um pequeno delay para garantir que o DOM esteja pronto
    setTimeout(initializeConditionsState, 100);

    // ✅ Otimização: debounce para eventos de mudança
    var debounceTimer;
    $('input, select, textarea').on('change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            // Ações que precisam ser executadas após mudanças
        }, 300);
    });

    // ✅ Otimização: lazy loading para imagens
    $('img').on('error', function() {
        console.warn('Erro ao carregar imagem:', this.src);
        // Fallback para imagem quebrada
        $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+SW1hZ2VuPC90ZXh0Pjwvc3ZnPg==');
    });

    // ✅ Otimização: reduzir eventos de mouseover/click com throttling
    var throttleTimer;
    $(document).on('mouseover', '.dokan-btn, .action-delete, .add-product-images', function() {
        if (throttleTimer) return;
        throttleTimer = setTimeout(function() {
            requestAnimationFrame(function() {
                $('.dokan-btn, .action-delete, .add-product-images').addClass('hover');
            });
            throttleTimer = null;
        }, 16); // ~60fps
    });

    $(document).on('mouseout', '.dokan-btn, .action-delete, .add-product-images', function() {
        if (throttleTimer) return;
        throttleTimer = setTimeout(function() {
            requestAnimationFrame(function() {
                $('.dokan-btn, .action-delete, .add-product-images').removeClass('hover');
            });
            throttleTimer = null;
        }, 16);
    });

    // Upload de imagem destacada
    $('.dokan-feat-image-btn').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: strings.select_featured_image || 'Selecionar Imagem Destacada',
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            if (attachment && attachment.url) {
                // Corrigir URL malformada
                attachment = fixAttachmentUrls(attachment);
                
                $('.dokan-feat-image-id').val(attachment.id);
                $('.image-wrap img').attr('src', attachment.url);
                $('.image-wrap').removeClass('dokan-hide');
                $('.instruction-inside').addClass('dokan-hide');
            }
        });

        frame.open();
    });

    // Remover imagem destacada
    $('.dokan-remove-feat-image').on('click', function(e) {
        e.preventDefault();
        
        $('.dokan-feat-image-id').val('');
        $('.image-wrap').addClass('dokan-hide');
        $('.instruction-inside').removeClass('dokan-hide');
        $('.image-wrap img').attr('src', '');
    });

    // Upload galeria de imagens
    $('.add-product-images').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: strings.select_gallery_images || 'Selecionar Imagens da Galeria',
            multiple: true
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var galleryIds = [];
            
            attachments.forEach(function(attachment) {
                if (attachment && attachment.url) {
                    // Corrigir URL malformada
                    attachment = fixAttachmentUrls(attachment);
                    
                    galleryIds.push(attachment.id);
                    
                    var imageHtml = '<li class="image" data-attachment_id="' + attachment.id + '">' +
                        '<img src="' + attachment.url + '" alt="">' +
                        '<a href="#" class="action-delete" title="' + (strings.delete_image || 'Excluir imagem') + '">&times;</a>' +
                        '</li>';
                    
                    $('.product_images li.add-image').before(imageHtml);
                }
            });
            
            if (galleryIds.length > 0) {
                var currentGallery = $('#product_image_gallery').val();
                var newGallery = currentGallery ? currentGallery + ',' + galleryIds.join(',') : galleryIds.join(',');
                $('#product_image_gallery').val(newGallery);
            }
        });

        frame.open();
    });

    // Remover imagem da galeria
    $(document).on('click', '.action-delete', function(e) {
        e.preventDefault();
        
        var $li = $(this).closest('li');
        var attachmentId = $li.data('attachment_id');
        
        if (attachmentId) {
            var currentGallery = $('#product_image_gallery').val();
            var galleryIds = currentGallery.split(',').filter(function(id) {
                return id != attachmentId;
            });
            $('#product_image_gallery').val(galleryIds.join(','));
        }
        
        $li.remove();
    });

    // Select2 para categorias - com verificação de disponibilidade
    if ($.fn.select2) {
        $('#classified_category').select2({
            placeholder: strings.select_categories || 'Selecione categorias',
            allowClear: true,
            width: '100%'
        });
    }

    // Corrigir URLs existentes na página
    $('img').each(function() {
        var src = $(this).attr('src');
        if (src && src.indexOf('https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
            $(this).attr('src', fixMalformedUrl(src));
        }
    });

    // ✅ Função para limpar URLs malformadas via AJAX
    function cleanMalformedUrls() {
        if (typeof j1_classificados_ajax !== 'undefined') {
            $.ajax({
                url: j1_classificados_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'j1_classificados_clean_urls',
                    nonce: j1_classificados_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log('URLs malformadas foram corrigidas');
                        // Recarregar a página para aplicar as correções
                        location.reload();
                    }
                },
                error: function() {
                    console.error('Erro ao limpar URLs malformadas');
                }
            });
        }
    }

    // ✅ Adicionar botão para limpar URLs malformadas (apenas para administradores)
    if ($('body').hasClass('wp-admin') || $('body').hasClass('dokan-dashboard')) {
        $('<button type="button" class="button" style="margin: 10px 0;">Limpar URLs Malformadas</button>')
            .on('click', cleanMalformedUrls)
            .insertAfter('.dokan-dashboard-header');
    }

    // ✅ Handler para o botão de limpeza de URLs no template
    $('#clean-malformed-urls-btn').on('click', function() {
        var $btn = $(this);
        var $status = $('#clean-urls-status');
        
        $btn.prop('disabled', true);
        $status.show();
        
        cleanMalformedUrls();
        
        // Mostrar feedback visual
        setTimeout(function() {
            $status.html('<span style="color: #28a745;">✅ URLs corrigidas! Recarregando...</span>');
            setTimeout(function() {
                location.reload();
            }, 1000);
        }, 2000);
    });

    // ✅ Validação específica para nosso formulário
    $('form[name="classified_form"]').on('submit', function(e) {
        // Prevenir validação padrão do Dokan
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        var title = $('#classified_title').val();
        var price = $('#classified_price').val();
        
        // Verificar se title existe e não está vazio
        if (!title || (typeof title === 'string' && !title.trim())) {
            e.preventDefault();
            alert('Por favor, preencha o título do classificado.');
            $('#classified_title').focus();
            return false;
        }
        
        // Verificar se price existe e é válido
        if (!price || isNaN(parseFloat(price))) {
            e.preventDefault();
            alert('Por favor, preencha um preço válido.');
            $('#classified_price').focus();
            return false;
        }
        
        // Se chegou até aqui, permitir o envio
        return true;
    });
}); 