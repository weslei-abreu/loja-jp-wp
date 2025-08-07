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

    // ✅ SIMPLES: Toggle Condições baseado no checkbox de vaga de emprego
    $(document).on('change', '#classified_is_job', function() {
        var conditionsContainer = $('#conditions-container');
        var isChecked = $(this).is(':checked');
        
        if (isChecked) {
            conditionsContainer.show();
        } else {
            conditionsContainer.hide();
            $('#classified_conditions').val(''); // Limpar o valor quando desmarcar
        }
    });

    // ✅ SIMPLES: Inicializar estado das condições
    function initializeConditionsState() {
        var isJobChecked = $('#classified_is_job').is(':checked');
        
        if (isJobChecked) {
            $('#conditions-container').show();
        } else {
            $('#conditions-container').hide();
        }
    }

    // Executar inicialização após o DOM estar pronto
    $(document).ready(function() {
        initializeConditionsState();
    });

    // ✅ Upload de imagem destacada
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

    // ✅ Upload galeria de imagens
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

    // ✅ Validação específica para nosso formulário
    $('form[name="classified_form"]').on('submit', function(e) {
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