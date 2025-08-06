jQuery(document).ready(function($) {
    'use strict';

    // Upload de imagem destacada
    $('.dokan-feat-image-btn').on('click', function(e) {
        e.preventDefault();
        
        var frame = wp.media({
            title: 'Selecionar Imagem Destacada',
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('.dokan-feat-image-id').val(attachment.id);
            $('.image-wrap img').attr('src', attachment.url);
            $('.image-wrap').removeClass('dokan-hide');
            $('.instruction-inside').addClass('dokan-hide');
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
            title: 'Selecionar Imagens da Galeria',
            multiple: true
        });

        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var gallery_ids = $('#product_image_gallery').val();
            var ids = gallery_ids ? gallery_ids.split(',') : [];
            
            attachments.forEach(function(attachment) {
                if (ids.indexOf(attachment.id.toString()) === -1) {
                    ids.push(attachment.id);
                    var html = '<li class="image" data-attachment_id="' + attachment.id + '">' +
                              '<img src="' + attachment.sizes.thumbnail.url + '" alt="">' +
                              '<a href="#" class="action-delete" title="Excluir imagem">&times;</a>' +
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

    // Select2 para categorias
    if ($.fn.select2) {
        $('#classified_category').select2({
            placeholder: 'Selecione categorias',
            allowClear: true
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