<?php
if (!defined('ABSPATH')) exit;

$current_user = get_current_user_id();

$args = [
    'post_type'      => 'classified',
    'author'         => $current_user,
    'posts_per_page' => -1,
];

$query = new WP_Query($args);

get_header();
?>

<div class="dokan-dashboard-wrap">
    <?php do_action('dokan_dashboard_content_before'); ?>

    <div class="dokan-dashboard-content dokan-product-listing">
        <?php do_action('dokan_before_listing_classifieds'); ?>

        <article class="dokan-classifieds-area">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title">Meus Classificados</h1>
            </header>

            <p>
                <a href="<?php echo esc_url(add_query_arg(['action' => 'add'], dokan_get_navigation_url('classifieds'))); ?>" class="dokan-btn dokan-btn-theme">
                    + Adicionar Novo Classificado
                </a>
            </p>

            <?php if ($query->have_posts()) : ?>
                <table class="dokan-table">
                    <thead>
                        <tr>
                            <th><?php _e('Imagem', 'j1_classificados'); ?></th>
                            <th><?php _e('Título', 'j1_classificados'); ?></th>
                            <th><?php _e('Preço', 'j1_classificados'); ?></th>
                            <th><?php _e('Status', 'j1_classificados'); ?></th>
                            <th><?php _e('Data', 'j1_classificados'); ?></th>
                            <th><?php _e('Ações', 'j1_classificados'); ?></th>
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
                                        echo '<img src="' . esc_url($thumb_url) . '" width="60" height="60" />';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                                <td>
                                    <?php
                                    $price = get_post_meta(get_the_ID(), '_price', true);
                                    echo $price ? '¥ ' . number_format_i18n($price) : '—';
                                    ?>
                                </td>
                                <td><?php echo ucfirst(get_post_status()); ?></td>
                                <td><?php echo get_the_date(); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds'))); ?>" class="dokan-btn dokan-btn-success">Editar</a>
                                    <a href="<?php echo wp_nonce_url(add_query_arg(['action' => 'delete', 'id' => get_the_ID()], dokan_get_navigation_url('classifieds')), 'delete_classified_' . get_the_ID()); ?>" class="dokan-btn dokan-btn-danger" onclick="return confirm('Tem certeza que deseja excluir este classificado?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Você ainda não publicou nenhum classificado.</p>
            <?php endif; ?>
        </article>

        <?php do_action('dokan_after_listing_classifieds'); ?>
    </div>

    <?php do_action('dokan_dashboard_content_after'); ?>
</div>

<?php get_footer(); ?>
