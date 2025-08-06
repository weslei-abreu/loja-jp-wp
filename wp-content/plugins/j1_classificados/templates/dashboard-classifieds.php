<?php
if (!defined('ABSPATH')) exit;

$current_user = get_current_user_id();

$args = [
    'post_type'      => 'classified',
    'author'         => $current_user,
    'posts_per_page' => -1,
    'post_status'    => apply_filters('dokan_product_listing_post_statuses', [
        'publish',
        'draft',
        'pending',
        'future',
    ]),
];

$query = new WP_Query($args);

get_header();
?>

<!-- ✅ Estilos inline para garantir que tudo funcione -->
<style>
/* ✅ Loading overlay */
.j1-loading-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.5) !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    z-index: 999999 !important;
    backdrop-filter: blur(4px) !important;
}

.j1-loading-overlay.hidden {
    display: none !important;
}

.j1-loading-spinner {
    width: 60px !important;
    height: 60px !important;
    border: 4px solid rgba(255, 255, 255, 0.3) !important;
    border-top: 4px solid #ffffff !important;
    border-radius: 50% !important;
    animation: j1-spin 1s linear infinite !important;
    margin: 0 auto !important;
    display: block !important;
}

@keyframes j1-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ✅ Header com botão reposicionado */
.dokan-dashboard-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
}

.j1-add-classified-btn {
    margin-left: auto !important;
}

/* ✅ Botões de ação brancos */
.dokan-table-action .dokan-btn,
.dokan-table-action a.dokan-btn {
    background: #ffffff !important;
    color: #333333 !important;
    border: 1px solid #ddd !important;
    padding: 6px 12px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
    margin-right: 5px !important;
    box-shadow: none !important;
}

.dokan-table-action .dokan-btn:hover,
.dokan-table-action a.dokan-btn:hover {
    background: #f8f9fa !important;
    color: #333333 !important;
    border-color: #007cba !important;
    text-decoration: none !important;
    box-shadow: none !important;
}

.dokan-table-action .dokan-btn:last-child,
.dokan-table-action a.dokan-btn:last-child {
    margin-right: 0 !important;
}

/* ✅ Estilos para a coluna de visualizações */
.j1-views-count {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    font-size: 12px !important;
    color: #666 !important;
    font-weight: 500 !important;
    padding: 4px 8px !important;
    background: #f8f9fa !important;
    border-radius: 4px !important;
    border: 1px solid #e9ecef !important;
}

.j1-views-count i {
    color: #007cba !important;
    font-size: 11px !important;
}

.j1-views-count:hover {
    background: #e9ecef !important;
    color: #333 !important;
}
</style>

<?php do_action('dokan_dashboard_wrap_start'); ?>

<!-- ✅ Loading overlay para a página (sem mensagem) -->
<div id="j1-page-loading" class="j1-loading-overlay">
    <div style="text-align: center;">
        <div class="j1-loading-spinner"></div>
    </div>
</div>

<!-- ✅ Script inline para garantir que o loading seja escondido -->
<script>
jQuery(document).ready(function($) {
    // Esconder loading após 1.5 segundos como fallback
    setTimeout(function() {
        $('#j1-page-loading').fadeOut(300, function() {
            $(this).addClass('hidden').hide();
        });
    }, 1500);
});
</script>

<div class="dokan-dashboard-wrap">
    <?php do_action('dokan_dashboard_content_before'); ?>

    <div class="dokan-dashboard-content dokan-product-listing">
        <?php do_action('dokan_before_listing_classifieds'); ?>

        <article class="dokan-classifieds-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title"><?php esc_html_e('Meus Classificados', 'j1_classificados'); ?></h1>
                
                <!-- ✅ Botão Adicionar Novo Classificado reposicionado -->
                <?php if (dokan_is_seller_enabled($current_user) && current_user_can('dokan_add_product')) : ?>
                    <div class="j1-add-classified-btn">
                        <a href="<?php echo esc_url(add_query_arg(['action' => 'add'], dokan_get_navigation_url('classifieds'))); ?>" class="dokan-btn dokan-btn-theme j1-loading-link">
                            <i class="fas fa-briefcase">&nbsp;</i>
                            <span class="btn-text"><?php esc_html_e('Adicionar Novo Classificado', 'j1_classificados'); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </header>

            <?php if ($query->have_posts()) : ?>
                <div class="dokan-dashboard-product-listing-wrapper" id="j1-classifieds-table">
                    <table class="dokan-table dokan-table-striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Imagem', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Título', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Preço', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Status', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Visualizações', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Data', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Ações', 'j1_classificados'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()) : $query->the_post(); ?>
                                <tr>
                                    <td>
                                        <?php
                                        $thumb_id = get_post_thumbnail_id(get_the_ID());
                                        if ($thumb_id) {
                                            $thumb_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                                            echo '<img src="' . esc_url($thumb_url) . '" width="60" height="60" alt="' . esc_attr(get_the_title()) . '" />';
                                        } else {
                                            echo '<img src="' . esc_url(wc_placeholder_img_src()) . '" width="60" height="60" alt="' . esc_attr__('Placeholder', 'j1_classificados') . '" />';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </td>
                                    <td>
                                        <?php
                                        $price = get_post_meta(get_the_ID(), '_price', true);
                                        $is_job = get_post_meta(get_the_ID(), '_classified_is_job', true);
                                        $conditions = get_post_meta(get_the_ID(), '_classified_conditions', true);
                                        
                                        if ($price) {
                                            echo '¥ ' . number_format_i18n($price);
                                            if ($is_job && $conditions) {
                                                $conditions_labels = [
                                                    'por_hora' => __('por hora', 'j1_classificados'),
                                                    'por_dia' => __('por dia', 'j1_classificados'),
                                                    'por_semana' => __('por semana', 'j1_classificados'),
                                                    'por_mes' => __('por mês', 'j1_classificados')
                                                ];
                                                echo ' <small>(' . esc_html($conditions_labels[$conditions] ?? $conditions) . ')</small>';
                                            }
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="dokan-label <?php echo esc_attr(dokan_get_post_status_label_class(get_post_status())); ?>">
                                            <?php echo esc_html(dokan_get_post_status(get_post_status())); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $views = j1_classificados_get_views(get_the_ID());
                                        echo '<span class="j1-views-count" data-post-id="' . get_the_ID() . '" title="' . esc_attr__('Visualizações', 'j1_classificados') . '">';
                                        echo '<i class="fas fa-eye"></i> ';
                                        echo number_format_i18n($views);
                                        echo '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo get_the_date(); ?>
                                    </td>
                                    <td>
                                        <div class="dokan-table-action">
                                            <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds'))); ?>" 
                                               class="dokan-btn dokan-btn-sm tips j1-loading-link" 
                                               title="<?php esc_attr_e('Editar', 'j1_classificados'); ?>">
                                                <i class="fas fa-edit"></i>
                                                <span class="btn-text"><?php esc_html_e('Editar', 'j1_classificados'); ?></span>
                                            </a>
                                            
                                            <a href="<?php echo esc_url(get_permalink()); ?>" 
                                               class="dokan-btn dokan-btn-sm tips j1-loading-link" 
                                               title="<?php esc_attr_e('Ver', 'j1_classificados'); ?>" 
                                               target="_blank">
                                                <i class="fas fa-eye"></i>
                                                <span class="btn-text"><?php esc_html_e('Ver', 'j1_classificados'); ?></span>
                                            </a>
                                            
                                            <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds')), 'delete_classified_' . get_the_ID()); ?>" 
                                               class="dokan-btn dokan-btn-sm tips j1-loading-link" 
                                               title="<?php esc_attr_e('Excluir', 'j1_classificados'); ?>"
                                               onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja excluir este classificado?', 'j1_classificados'); ?>');">
                                                <i class="fas fa-trash"></i>
                                                <span class="btn-text"><?php esc_html_e('Excluir', 'j1_classificados'); ?></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="dokan-dashboard-product-listing-wrapper">
                    <div class="dokan-no-product">
                        <div class="dokan-no-product-img">
                            <i class="fas fa-box-open" style="font-size: 48px; color: #ddd;"></i>
                        </div>
                        <p><?php esc_html_e('Você ainda não publicou nenhum classificado.', 'j1_classificados'); ?></p>
                        <a href="<?php echo esc_url(add_query_arg(['action' => 'add'], dokan_get_navigation_url('classifieds'))); ?>" class="dokan-btn dokan-btn-theme">
                            <?php esc_html_e('Adicionar Novo Classificado', 'j1_classificados'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </article>

        <?php do_action('dokan_after_listing_classifieds'); ?>
    </div>

    <?php do_action('dokan_dashboard_content_after'); ?>
</div>

<?php get_footer(); ?>
