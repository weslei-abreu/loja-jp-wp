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
?>

<div class="dokan-dashboard-wrap">
    <?php dokan_get_template_part('global/dashboard-nav'); ?>

    <div class="dokan-dashboard-content dokan-product-edit">
        <div class="dokan-dashboard-header">
            <h1><?php echo $post_id ? __('Editar Classificado', 'j1_classificados') : __('Adicionar Novo Classificado', 'j1_classificados'); ?></h1>
        </div>

        <form method="post" enctype="multipart/form-data" class="dokan-form">
            <?php wp_nonce_field('save_classified', 'classified_nonce'); ?>

            <div class="dokan-product-edit-area">
                
                <!-- Título -->
                <div class="dokan-form-group">
                    <label for="classified_title" class="dokan-control-label">
                        <?php _e('Título', 'j1_classificados'); ?>
                    </label>
                    <input type="text" name="classified_title" id="classified_title" class="dokan-form-control" value="<?php echo esc_attr($title); ?>" required>
                </div>

                <!-- Descrição -->
                <div class="dokan-form-group">
                    <label for="classified_content" class="dokan-control-label">
                        <?php _e('Descrição', 'j1_classificados'); ?>
                    </label>
                    <?php
                        wp_editor(
                            $content,
                            'classified_content',
                            [
                                'textarea_name' => 'classified_content',
                                'media_buttons' => false,
                                'textarea_rows' => 8,
                                'teeny' => true,
                            ]
                        );
                    ?>
                </div>

                <!-- Preço -->
                <div class="dokan-form-group">
                    <label for="classified_price" class="dokan-control-label"><?php _e('Preço', 'j1_classificados'); ?></label>
                    <input type="number" name="classified_price" id="classified_price" class="dokan-form-control" value="<?php echo esc_attr($price); ?>" min="0" step="1">
                </div>

                <!-- Preço Normal -->
                <div class="dokan-form-group">
                    <label for="classified_regular_price" class="dokan-control-label"><?php _e('Preço Normal', 'j1_classificados'); ?></label>
                    <input type="number" name="classified_regular_price" id="classified_regular_price" class="dokan-form-control" value="<?php echo esc_attr($regular_price); ?>" min="0" step="1">
                </div>

                <!-- Imagem Destacada -->
                <div class="dokan-form-group">
                    <label class="dokan-control-label"><?php _e('Imagem Destacada', 'j1_classificados'); ?></label>
                    <?php if ($thumbnail_id) : ?>
                        <div style="margin-bottom:10px;"><?php echo wp_get_attachment_image($thumbnail_id, 'thumbnail'); ?></div>
                    <?php endif; ?>
                    <input type="file" name="classified_thumbnail" accept="image/*" class="dokan-form-control">
                </div>

                <!-- Galeria de Imagens -->
                <div class="dokan-form-group">
                    <label for="classified_gallery" class="dokan-control-label"><?php _e('Galeria de Imagens', 'j1_classificados'); ?></label>
                    <input type="file" name="classified_gallery[]" multiple accept="image/*" class="dokan-form-control">
                </div>

                <!-- Categorias -->
                <div class="dokan-form-group">
                    <label for="classified_category" class="dokan-control-label"><?php _e('Categoria', 'j1_classificados'); ?></label>
                    <select name="classified_category[]" id="classified_category" class="dokan-form-control dokan-select2" multiple>
                        <?php foreach ($product_categories as $cat) : ?>
                            <option value="<?php echo $cat->term_id; ?>" <?php selected(in_array($cat->term_id, $categories)); ?>>
                                <?php echo esc_html($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="classified_id" value="<?php echo $post_id; ?>">

                <button type="submit" class="dokan-btn dokan-btn-theme dokan-btn-lg">
                    <?php echo $post_id ? __('Salvar Alterações', 'j1_classificados') : __('Publicar Classificado', 'j1_classificados'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    if ($.fn.select2) {
        $('#classified_category').select2({
            placeholder: '<?php _e("Selecione categorias", "j1_classificados"); ?>',
            allowClear: true
        });
    }
});
</script>
