<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

// ✅ Se existir template do Elementor para "single", usa ele e encerra
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
    get_footer();
    return;
}

// ✅ Se não houver template, usa fallback básico
while ( have_posts() ) : the_post();
    $price = get_post_meta( get_the_ID(), '_price', true );
    ?>
    <div class="j1-classified-container">
        <h1 class="j1-classified-title"><?php the_title(); ?></h1>

        <?php if ( has_post_thumbnail() ) : ?>
            <div class="j1-classified-image">
                <?php the_post_thumbnail( 'large' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $price ) : ?>
            <p class="j1-classified-price">
                Valor: R$ <?php echo number_format($price, 2, ',', '.'); ?>
            </p>
        <?php endif; ?>

        <div class="j1-classified-description">
            <?php the_content(); ?>
        </div>

        <?php if ( function_exists( 'j1_classificados_contact_button' ) ) : ?>
            <div class="j1-classified-contact">
                <?php j1_classificados_contact_button( get_the_ID() ); ?>
            </div>
        <?php endif; ?>
    </div>
<?php
endwhile;

get_footer();
