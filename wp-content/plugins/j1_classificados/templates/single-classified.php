<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
    $price = get_post_meta( get_the_ID(), '_price', true );
    ?>
    
    <div class="classified-container" style="max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px;">
        <h1 style="margin-bottom: 10px;"><?php the_title(); ?></h1>

        <?php if ( has_post_thumbnail() ) : ?>
            <div style="margin-bottom: 20px;">
                <?php the_post_thumbnail( 'large', ['style' => 'max-width:100%; border-radius:10px;'] ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $price ) : ?>
            <p style="font-size: 20px; font-weight: bold; margin-bottom: 15px;">
                Valor: R$ <?php echo number_format($price, 2, ',', '.'); ?>
            </p>
        <?php endif; ?>

        <div style="margin-bottom: 20px; font-size: 16px;">
            <?php the_content(); ?>
        </div>

        <?php j1_classificados_contact_button( get_the_ID() ); ?>
    </div>

    <?php
endwhile;

get_footer();
