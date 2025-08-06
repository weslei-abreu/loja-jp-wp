<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$current_user = wp_get_current_user();
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$classified = $post_id ? get_post($post_id) : null;
$title = $classified ? $classified->post_title : '';
$content = $classified ? $classified->post_content : '';
$price = $classified ? get_post_meta($post_id, '_price', true) : '';
$regular_price = $classified ? get_post_meta($post_id, '_regular_price', true) : '';
$thumbnail_id = $classified ? get_post_thumbnail_id($post_id) : '';
$gallery = $classified ? get_post_meta($post_id, '_product_image_gallery', true) : '';
$categories = $classified ? wp_get_post_terms($post_id, 'product_cat', ['fields' => 'ids']) : [];

$product_categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);

// Verificar se o usuário tem permissão
if ($post_id && !dokan_is_product_author($post_id)) {
    wp_die(__('Access Denied', 'j1_classificados'));
}

if (!dokan_is_seller_enabled(get_current_user_id())) {
    wp_die(__('Access Denied', 'j1_classificados'));
}

get_header();
?>

<?php do_action('dokan_dashboard_wrap_start'); ?>

<!-- ✅ Loading overlay para a página -->
<div id="j1-edit-page-loading" class="j1-edit-page-loading">
    <div>
        <div class="j1-loading-spinner"></div>
    </div>
</div>

<div class="dokan-dashboard-wrap j1-edit-page-content">
    <?php do_action('dokan_dashboard_content_before'); ?>

    <div class="dokan-dashboard-content dokan-product-edit dokan-layout">
        <?php do_action('dokan_product_content_inside_area_before'); ?>

        <header class="dokan-dashboard-header">
            <h1 class="entry-title">
                <?php echo $post_id ? __('Editar Classificado', 'j1_classificados') : __('Adicionar Novo Classificado', 'j1_classificados'); ?>
                
                <?php if ($post_id && $classified) : ?>
                    <span class="dokan-label <?php echo esc_attr(dokan_get_post_status_label_class($classified->post_status)); ?> dokan-product-status-label">
                        <?php echo esc_html(dokan_get_post_status($classified->post_status)); ?>
                    </span>
                <?php endif; ?>
            </h1>
            

        </header>

        <div class="product-edit-new-container product-edit-container">
            <?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
                <div class="dokan-message">
                    <button type="button" class="dokan-close" data-dismiss="alert">&times;</button>
                    <strong><?php esc_html_e('Sucesso!', 'j1_classificados'); ?></strong> 
                    <?php esc_html_e('O classificado foi salvo com sucesso.', 'j1_classificados'); ?>
                </div>
            <?php endif; ?>

            <form method="post" name="classified_form" enctype="multipart/form-data" class="dokan-product-edit-form" role="form" id="post">
                <?php wp_nonce_field('save_classified', 'classified_nonce'); ?>
                
                <?php do_action('dokan_product_edit_before_main'); ?>

                <div class="dokan-form-top-area">
                    <div class="content-half-part dokan-product-meta">
                        
                        <!-- Título -->
                        <div class="dokan-form-group">
                            <label for="classified_title" class="form-label">
                                <?php esc_html_e('Título', 'j1_classificados'); ?>
                            </label>
                            <input type="text" 
                                   name="classified_title" 
                                   id="classified_title" 
                                   class="dokan-form-control" 
                                   value="<?php echo esc_attr($title); ?>" 
                                   placeholder="<?php esc_attr_e('Nome do classificado...', 'j1_classificados'); ?>"
                                   required>
                            <div class="dokan-product-title-alert dokan-hide">
                                <?php esc_html_e('Por favor, insira o título do classificado!', 'j1_classificados'); ?>
                            </div>
                        </div>

                        <!-- Checkbox Vaga de Emprego -->
                        <div class="dokan-form-group">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" 
                                       name="classified_is_job" 
                                       id="classified_is_job" 
                                       value="1" 
                                       style="width: 16px; height: 16px; margin: 0;"
                                       <?php checked(get_post_meta($post_id, '_classified_is_job', true), '1'); ?>>
                                <label for="classified_is_job" style="margin: 0; cursor: pointer; font-weight: normal;">
                                    <?php esc_html_e('O classificado é uma vaga de emprego?', 'j1_classificados'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- Categorias -->
                        <div class="dokan-form-group">
                            <label for="classified_category" class="form-label">
                                <?php esc_html_e('Categoria', 'j1_classificados'); ?>
                            </label>
                            <select name="classified_category[]" id="classified_category" class="dokan-form-control dokan-select2" multiple>
                                <?php foreach ($product_categories as $cat) : ?>
                                    <option value="<?php echo esc_attr($cat->term_id); ?>" 
                                            <?php selected(in_array($cat->term_id, $categories)); ?>>
                                        <?php echo esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Preços e Condições -->
                        <div class="dokan-form-group dokan-clearfix dokan-price-container">
                            <div class="content-half-part regular-price">
                                <label for="classified_price" class="form-label">
                                    <?php esc_html_e('Valor', 'j1_classificados'); ?>
                                </label>
                                <div class="dokan-input-group">
                                    <span class="dokan-input-group-addon">¥</span>
                                    <input type="number" 
                                           name="classified_price" 
                                           id="classified_price" 
                                           class="dokan-form-control dokan-product-regular-price" 
                                           value="<?php echo esc_attr($price); ?>" 
                                           min="0" 
                                           step="1"
                                           placeholder="0">
                                </div>
                            </div>

                            <div class="content-half-part conditions-price" id="conditions-container" style="display: block;">
                                <label for="classified_conditions" class="form-label">
                                    <?php esc_html_e('Salário', 'j1_classificados'); ?>
                                </label>
                                <select name="classified_conditions" id="classified_conditions" class="dokan-form-control">
                                    <option value=""><?php esc_html_e('Selecione uma opção', 'j1_classificados'); ?></option>
                                    <option value="por_hora" <?php selected(get_post_meta($post_id, '_classified_conditions', true), 'por_hora'); ?>>
                                        <?php esc_html_e('Por hora', 'j1_classificados'); ?>
                                    </option>
                                    <option value="por_dia" <?php selected(get_post_meta($post_id, '_classified_conditions', true), 'por_dia'); ?>>
                                        <?php esc_html_e('Por dia', 'j1_classificados'); ?>
                                    </option>
                                    <option value="por_semana" <?php selected(get_post_meta($post_id, '_classified_conditions', true), 'por_semana'); ?>>
                                        <?php esc_html_e('Por semana', 'j1_classificados'); ?>
                                    </option>
                                    <option value="por_mes" <?php selected(get_post_meta($post_id, '_classified_conditions', true), 'por_mes'); ?>>
                                        <?php esc_html_e('Por mês', 'j1_classificados'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="content-half-part featured-image">
                        <div class="dokan-feat-image-upload">
                            <div class="instruction-inside<?php echo $thumbnail_id ? ' dokan-hide' : ''; ?>">
                                <input type="hidden" name="feat_image_id" class="dokan-feat-image-id" value="<?php echo esc_attr($thumbnail_id); ?>">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <a href="#" class="dokan-feat-image-btn btn btn-sm">
                                    <?php esc_html_e('Carregar uma imagem de capa do classificado', 'j1_classificados'); ?>
                                </a>
                            </div>

                                                         <div class="image-wrap<?php echo $thumbnail_id ? '' : ' dokan-hide'; ?>">
                                <a class="close dokan-remove-feat-image">&times;</a>
                                <?php if ($thumbnail_id) : ?>
                                    <?php echo get_the_post_thumbnail($post_id, 'shop_single', ['height' => '', 'width' => '']); ?>
                                <?php else : ?>
                                    <img height="" width="" src="" alt="">
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Galeria de Imagens -->
                        <div class="dokan-product-gallery">
                            <div class="dokan-side-body" id="dokan-product-images">
                                <div id="product_images_container">
                                    <ul class="product_images dokan-clearfix">
                                                                                 <?php
                                         if ($gallery) :
                                             $gallery_ids = explode(',', $gallery);
                                             foreach ($gallery_ids as $image_id) :
                                                 if (empty($image_id)) continue;
                                                 
                                                 $attachment_image = wp_get_attachment_image_src($image_id, 'thumbnail');
                                                 
                                                 if ($attachment_image) :
                                                     // Corrigir URL malformada - remover duplicação de domínio
                                                     $image_url = $attachment_image[0];
                                                     
                                                     if (strpos($image_url, 'https://loja.jp/wp-content/uploads/https:/loja.jp') === 0) {
                                                         $image_url = str_replace('https://loja.jp/wp-content/uploads/https:/loja.jp', 'https://loja.jp/wp-content/uploads', $image_url);
                                                     }
                                         ?>
                                            <li class="image" data-attachment_id="<?php echo esc_attr($image_id); ?>">
                                                <img src="<?php echo esc_url($image_url); ?>" alt="">
                                                <a href="#" class="action-delete" title="<?php esc_attr_e('Excluir imagem', 'j1_classificados'); ?>">&times;</a>
                                            </li>
                                        <?php 
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                        <li class="add-image add-product-images tips" data-title="<?php esc_html_e('Adicionar imagem da galeria', 'j1_classificados'); ?>">
                                            <a href="#" class="add-product-images"><i class="fas fa-plus" aria-hidden="true"></i></a>
                                        </li>
                                    </ul>
                                    <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr($gallery); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descrição -->
                <div class="dokan-product-description">
                    <label for="classified_content" class="form-label">
                        <?php esc_html_e('Descrição', 'j1_classificados'); ?>
                    </label>
                    <?php
                    wp_editor(
                        $content,
                        'classified_content',
                        [
                            'textarea_name' => 'classified_content',
                            'editor_height' => 200,
                            'media_buttons' => false,
                            'teeny' => false,
                            'editor_class' => 'post_content',
                            'quicktags' => true,
                        ]
                    );
                    ?>
                </div>

                <input type="hidden" name="classified_id" value="<?php echo $post_id; ?>">

                <div class="dokan-form-group" style="margin-top: 30px;">
                    <button type="submit" class="dokan-btn dokan-btn-theme dokan-btn-lg j1-submit-btn">
                        <span class="btn-text"><?php echo $post_id ? __('Salvar Alterações', 'j1_classificados') : __('Publicar Classificado', 'j1_classificados'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php do_action('dokan_dashboard_content_after'); ?>
</div>
</script>

<?php get_footer(); ?>
