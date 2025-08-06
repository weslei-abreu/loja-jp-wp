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

<?php do_action('dokan_dashboard_wrap_start'); ?>

<div class="dokan-dashboard-wrap">
    <?php do_action('dokan_dashboard_content_before'); ?>

    <div class="dokan-dashboard-content dokan-product-listing">
        <?php do_action('dokan_before_listing_classifieds'); ?>

        <article class="dokan-classifieds-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title"><?php esc_html_e('Meus Classificados', 'j1_classificados'); ?></h1>
            </header>

            <?php if ($query->have_posts()) : ?>
                <div class="product-listing-top dokan-clearfix">
                    <?php if (dokan_is_seller_enabled($current_user)) : ?>
                        <span class="dokan-add-product-link">
                            <?php if (current_user_can('dokan_add_product')) : ?>
                                <a href="<?php echo esc_url(add_query_arg(['action' => 'add'], dokan_get_navigation_url('classifieds'))); ?>" class="dokan-btn dokan-btn-theme">
                                    <i class="fas fa-briefcase">&nbsp;</i>
                                    <?php esc_html_e('Adicionar Novo Classificado', 'j1_classificados'); ?>
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="dokan-dashboard-product-listing-wrapper">
                    <table class="dokan-table dokan-table-striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Imagem', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Título', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Preço', 'j1_classificados'); ?></th>
                                <th><?php esc_html_e('Status', 'j1_classificados'); ?></th>
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
                                        <?php echo get_the_date(); ?>
                                    </td>
                                    <td>
                                        <div class="dokan-table-action">
                                            <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds'))); ?>" 
                                               class="dokan-btn dokan-btn-default dokan-btn-sm tips" 
                                               title="<?php esc_attr_e('Editar', 'j1_classificados'); ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="<?php echo esc_url(get_permalink()); ?>" 
                                               class="dokan-btn dokan-btn-default dokan-btn-sm tips" 
                                               title="<?php esc_attr_e('Ver', 'j1_classificados'); ?>" 
                                               target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds')), 'delete_classified_' . get_the_ID()); ?>" 
                                               class="dokan-btn dokan-btn-default dokan-btn-sm tips" 
                                               title="<?php esc_attr_e('Excluir', 'j1_classificados'); ?>"
                                               onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja excluir este classificado?', 'j1_classificados'); ?>');">
                                                <i class="fas fa-trash"></i>
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
