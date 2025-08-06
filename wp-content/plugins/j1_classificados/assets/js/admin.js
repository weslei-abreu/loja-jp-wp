jQuery(document).ready(function($) {
    'use strict';

    // Verificar se wp.media está disponível
    if (typeof wp === 'undefined' || !wp.media) {
        console.warn('wp.media não está disponível');
        return;
    }

    // Verificar se as strings localizadas estão disponíveis
    var strings = typeof j1_classificados_ajax !== 'undefined' ? j1_classificados_ajax.strings : {};

    // Otimização: debounce para eventos de mudança
    var debounceTimer;
    $('input, select, textarea').on('change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            // Ações que precisam ser executadas após mudanças
        }, 300);
    });

    // Otimização: lazy loading para imagens
    $('img').on('error', function() {
        console.warn('Erro ao carregar imagem:', this.src);
        // Fallback para imagem quebrada
        $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+SW1hZ2VuPC90ZXh0Pjwvc3ZnPg==');
    });

    // Otimização: reduzir eventos de mouseover/click
    $(document).on('mouseover', '.dokan-btn, .action-delete, .add-product-images', function() {
        // Usar requestAnimationFrame para otimizar repaints
        requestAnimationFrame(function() {
            $(this).addClass('hover');
        }.bind(this));
    });

    $(document).on('mouseout', '.dokan-btn, .action-delete, .add-product-images', function() {
        requestAnimationFrame(function() {
            $(this).removeClass('hover');
        }.bind(this));
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
                // Corrigir URL malformada - remover duplicação de domínio
                var imageUrl = attachment.url;
                if (imageUrl.indexOf('https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
                    imageUrl = imageUrl.replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads');
                }
                $('.dokan-feat-image-id').val(attachment.id);
                $('.image-wrap img').attr('src', imageUrl);
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

    // Upload de galeria
    $('.add-product-images').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: strings.select_gallery_images || 'Selecionar Imagens da Galeria',
            multiple: true
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var gallery_ids = $('#product_image_gallery').val();
            var ids = gallery_ids ? gallery_ids.split(',') : [];
            
            attachments.forEach(function(attachment) {
                if (ids.indexOf(attachment.id.toString()) === -1) {
                    ids.push(attachment.id);
                    
                    // Corrigir URL malformada - remover duplicação de domínio
                    var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                        attachment.sizes.thumbnail.url : attachment.url;
                    
                    if (imageUrl.indexOf('https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
                        imageUrl = imageUrl.replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads');
                    }
                    
                    var html = '<li class="image" data-attachment_id="' + attachment.id + '">' +
                              '<img src="' + imageUrl + '" alt="">' +
                              '<a href="#" class="action-delete" title="' + (strings.delete_image || 'Excluir imagem') + '">&times;</a>' +
                              '</li>';
                    $('.product_images').prepend(html);
                }
            });
            
            $('#product_image_gallery').val(ids.join(','));
        });

        frame.open();
    });

    // Remover imagem da galeria
    $(document).on('click', '.action-delete', function(e) {
        e.preventDefault();
        var attachment_id = $(this).parent().data('attachment_id');
        var gallery_ids = $('#product_image_gallery').val().split(',');
        var index = gallery_ids.indexOf(attachment_id.toString());
        
        if (index > -1) {
            gallery_ids.splice(index, 1);
            $('#product_image_gallery').val(gallery_ids.join(','));
        }
        
        $(this).parent().remove();
    });

    // Select2 para categorias - com verificação de disponibilidade
    if ($.fn.select2) {
        $('#classified_category').select2({
            placeholder: strings.select_categories || 'Selecione categorias',
            allowClear: true,
            width: '100%'
        });
    }

    // Toggle Condições baseado no checkbox de vaga de emprego
    $('#classified_is_job').on('change', function() {
        var conditionsContainer = $('#conditions-container');
        if ($(this).is(':checked')) {
            conditionsContainer.show();
        } else {
            conditionsContainer.hide();
            $('#classified_conditions').val(''); // Limpar o valor quando desmarcar
        }
    });

    // Inicializar estado das condições
    var isJobChecked = $('#classified_is_job').is(':checked');
    if (isJobChecked) {
        $('#conditions-container').show();
    } else {
        $('#conditions-container').hide();
    }
}); 