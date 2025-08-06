jQuery(document).ready(function($) {
    'use strict';

    // ✅ Verificar se estamos na página de classificados
    if (!$('form[name="classified_form"]').length && !$('#j1-classifieds-table').length) {
        return; // Sair se não estivermos na página de classificados
    }

    // ✅ FUNÇÕES DE LOADING
    function showPageLoading(text = 'Carregando...') {
        var loadingOverlay = $('#j1-page-loading');
        if (loadingOverlay.length) {
            loadingOverlay.find('.j1-loading-text').text(text);
            loadingOverlay.removeClass('hidden').show();
        } else {
            // Criar overlay se não existir
            var overlay = $('<div id="j1-page-loading" class="j1-loading-overlay">' +
                '<div style="text-align: center; background: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); min-width: 200px;">' +
                '<div class="j1-loading-spinner"></div>' +
                '<div class="j1-loading-text">' + text + '</div>' +
                '</div>' +
                '</div>');
            $('body').append(overlay);
        }
    }

    function hidePageLoading() {
        var loadingOverlay = $('#j1-page-loading');
        if (loadingOverlay.length) {
            loadingOverlay.addClass('hidden').hide();
        }
    }

    function showButtonLoading(button, text = 'Carregando...') {
        var $btn = $(button);
        $btn.addClass('j1-btn-loading');
        $btn.find('.btn-text').text(text);
    }

    function hideButtonLoading(button) {
        var $btn = $(button);
        $btn.removeClass('j1-btn-loading');
        $btn.find('.btn-text').text($btn.data('original-text') || 'Carregar');
    }

    function showFormLoading(form) {
        var $form = $(form);
        $form.addClass('j1-form-loading');
    }

    function hideFormLoading(form) {
        var $form = $(form);
        $form.removeClass('j1-form-loading');
    }

    function showUploadLoading(element) {
        var $element = $(element);
        $element.addClass('j1-upload-loading');
    }

    function hideUploadLoading(element) {
        var $element = $(element);
        $element.removeClass('j1-upload-loading');
    }

    // ✅ FUNÇÕES ESPECÍFICAS PARA LOADING DA PÁGINA DE EDIÇÃO
    function showEditPageLoading() {
        var loadingOverlay = $('#j1-edit-page-loading');
        if (loadingOverlay.length) {
            loadingOverlay.removeClass('hidden').show();
        }
    }

    function hideEditPageLoading() {
        var loadingOverlay = $('#j1-edit-page-loading');
        var pageContent = $('.j1-edit-page-content');
        
        if (loadingOverlay.length) {
            loadingOverlay.addClass('hidden').hide();
            
            // Mostrar conteúdo da página com fade in
            if (pageContent.length) {
                pageContent.addClass('loaded');
            }
        }
    }

    // ✅ INICIALIZAR LOADINGS
    function initializeLoadings() {
        // Verificar se estamos na página de edição/criação
        var isEditPage = $('form[name="classified_form"]').length > 0;
        
        if (isEditPage) {
            initializeEditPageLoading();
        } else {
            initializeDefaultLoading();
        }
    }

    // ✅ LOADING ESPECÍFICO PARA PÁGINA DE EDIÇÃO/CRIAÇÃO
    function initializeEditPageLoading() {
        // Mostrar loading inicialmente
        showEditPageLoading();
        
        // Esconder loading após 2 segundos
        setTimeout(function() {
            hideEditPageLoading();
        }, 2000);
    }

    // ✅ LOADING PADRÃO PARA OUTRAS PÁGINAS
    function initializeDefaultLoading() {
        // Mostrar loading inicialmente
        showPageLoading('Carregando...');
        
        // Esconder loading da página após carregamento completo
        $(window).on('load', function() {
            setTimeout(function() {
                hidePageLoading();
            }, 500);
        });

        // Fallback: esconder loading após 3 segundos se a página não carregar completamente
        setTimeout(function() {
            hidePageLoading();
        }, 3000);

        // Adicionar loading nos links
        $('.j1-loading-link').on('click', function(e) {
            showPageLoading('Carregando...');
            showButtonLoading(this, 'Carregando...');
        });

        // Adicionar loading nos links de ação da tabela
        $('.dokan-table-action a').on('click', function(e) {
            if (!$(this).hasClass('j1-loading-link')) {
                showPageLoading('Carregando...');
                showButtonLoading(this, 'Carregando...');
            }
        });

        // Adicionar loading no formulário
        $('form[name="classified_form"]').on('submit', function(e) {
            showPageLoading('Publicando...');
            showFormLoading(this);
            showButtonLoading('.j1-submit-btn', 'Publicando...');
        });

        // Adicionar loading nos links de navegação do Dokan
        $('.dokan-dashboard-navigation a, .dokan-dashboard-wrap a[href*="classifieds"]').on('click', function(e) {
            if (!$(this).hasClass('j1-loading-link')) {
                showPageLoading('Carregando...');
            }
        });

        // Adicionar loading para imagens
        $('img').on('load', function() {
            $(this).removeClass('j1-image-loading');
        }).on('error', function() {
            $(this).removeClass('j1-image-loading');
        });

        // Adicionar classe de loading para imagens que ainda não carregaram
        $('img').each(function() {
            if (!this.complete) {
                $(this).addClass('j1-image-loading');
            }
        });

        // Adicionar loading para a tabela
        if ($('#j1-classifieds-table').length) {
            $('#j1-classifieds-table').addClass('j1-table-loading');
            setTimeout(function() {
                $('#j1-classifieds-table').removeClass('j1-table-loading');
            }, 1000);
        }

        // Adicionar loading para o formulário
        if ($('form[name="classified_form"]').length) {
            $('form[name="classified_form"]').addClass('j1-form-loading');
            setTimeout(function() {
                $('form[name="classified_form"]').removeClass('j1-form-loading');
            }, 800);
        }

        // Adicionar loading para a galeria de imagens
        if ($('.dokan-product-gallery').length) {
            $('.dokan-product-gallery').addClass('j1-upload-loading');
            setTimeout(function() {
                $('.dokan-product-gallery').removeClass('j1-upload-loading');
            }, 600);
        }

        // Adicionar loading para o upload de imagens
        if ($('.dokan-feat-image-upload').length) {
            $('.dokan-feat-image-upload').addClass('j1-upload-loading');
            setTimeout(function() {
                $('.dokan-feat-image-upload').removeClass('j1-upload-loading');
            }, 500);
        }
    }

    // ✅ EXECUTAR INICIALIZAÇÃO
    initializeLoadings();
    
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

    // ✅ TESTE: Adicionar evento de clique também
    $(document).on('click', '#classified_is_job', function() {
        console.log('Checkbox CLICKED!');
    });

    // ✅ SIMPLES: Inicializar estado das condições
    function initializeConditionsState() {
        var isJobChecked = $('#classified_is_job').is(':checked');
        var conditionsContainer = $('#conditions-container');
        
        if (isJobChecked) {
            conditionsContainer.show();
        } else {
            conditionsContainer.hide();
        }
    }

    // Executar inicialização
    initializeConditionsState();

    // ✅ TESTE: Verificar se os elementos existem
    console.log('Checkbox exists:', $('#classified_is_job').length);
    console.log('Container exists:', $('#conditions-container').length);
    console.log('Form exists:', $('form[name="classified_form"]').length);

    // ✅ Upload de imagem destacada com loading
    $('.dokan-feat-image-btn').on('click', function(e) {
        e.preventDefault();
        
        showPageLoading('Carregando mídia...');
        showUploadLoading('.dokan-feat-image-upload');
        
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
            hidePageLoading();
            hideUploadLoading('.dokan-feat-image-upload');
        });

        frame.on('close', function() {
            hidePageLoading();
            hideUploadLoading('.dokan-feat-image-upload');
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

    // ✅ Upload galeria de imagens com loading
    $('.add-product-images').on('click', function(e) {
        e.preventDefault();
        
        showPageLoading('Carregando mídia...');
        showUploadLoading('.dokan-product-gallery');
        
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
            hidePageLoading();
            hideUploadLoading('.dokan-product-gallery');
        });

        frame.on('close', function() {
            hidePageLoading();
            hideUploadLoading('.dokan-product-gallery');
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

    // ✅ Validação específica para nosso formulário com loading
    $('form[name="classified_form"]').on('submit', function(e) {
        // Prevenir validação padrão do Dokan
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        var title = $('#classified_title').val();
        var price = $('#classified_price').val();
        
        // Verificar se title existe e não está vazio
        if (!title || (typeof title === 'string' && !title.trim())) {
            e.preventDefault();
            hideFormLoading(this);
            alert('Por favor, preencha o título do classificado.');
            $('#classified_title').focus();
            return false;
        }
        
        // Verificar se price existe e é válido
        if (!price || isNaN(parseFloat(price))) {
            e.preventDefault();
            hideFormLoading(this);
            alert('Por favor, preencha um preço válido.');
            $('#classified_price').focus();
            return false;
        }
        
        // Mostrar loading no formulário
        showFormLoading(this);
        
        // Se chegou até aqui, permitir o envio
        return true;
    });
}); 