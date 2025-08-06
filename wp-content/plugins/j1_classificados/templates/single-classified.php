<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
    $price = get_post_meta( get_the_ID(), '_price', true );
    $is_job = get_post_meta( get_the_ID(), '_classified_is_job', true );
    $conditions = get_post_meta( get_the_ID(), '_classified_conditions', true );
    ?>
    
    <div class="classified-container" style="max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px;">
        <h1 style="margin-bottom: 10px;"><?php the_title(); ?></h1>

        <?php if ( $is_job ) : ?>
            <div style="margin-bottom: 15px;">
                <span style="background: #007cba; color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">
                    <?php esc_html_e('Vaga de Emprego', 'j1_classificados'); ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ( has_post_thumbnail() ) : ?>
            <div style="margin-bottom: 20px;">
                <?php the_post_thumbnail( 'large', ['style' => 'max-width:100%; border-radius:10px;'] ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $price ) : ?>
            <p style="font-size: 20px; font-weight: bold; margin-bottom: 15px;">
                Valor: ¥ <?php echo number_format($price, 0, ',', '.'); ?>
                <?php if ( $is_job && $conditions ) : ?>
                    <?php
                    $conditions_labels = [
                        'por_hora' => __('por hora', 'j1_classificados'),
                        'por_dia' => __('por dia', 'j1_classificados'),
                        'por_semana' => __('por semana', 'j1_classificados'),
                        'por_mes' => __('por mês', 'j1_classificados')
                    ];
                    ?>
                    <small style="font-size: 14px; color: #666;">
                        (<?php echo esc_html($conditions_labels[$conditions] ?? $conditions); ?>)
                    </small>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div style="margin-bottom: 20px; font-size: 16px;">
            <?php the_content(); ?>
        </div>
    </div>

    <?php
endwhile;

get_footer();
