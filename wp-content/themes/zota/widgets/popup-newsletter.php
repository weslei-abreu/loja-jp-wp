
<div class="popupnewsletter">
    <!-- Modal -->
    <div class="popup-newsletter-widget widget-newletter">
        
        <div class="popup-content">
            <a href="javascript:void(0);" data-id="data-close-newsletter" id="close-newsletter"><i class="tb-icon tb-icon-close-01"></i></a>
            <?php
                if( !empty($image) ) {
                    ?>
                        <img src="<?php echo esc_url($image) ?>" alt="<?php esc_attr_e('Image Newletter', 'zota'); ?>"> 
                    <?php
                }
            ?>
            <?php if(!empty($title)){ ?>
                <h3>
                    <span>
                        <?php 
                            $title = apply_filters('widget_title', $instance['title']);
                            echo trim( $title ); 
                        ?>          
                    </span>
                </h3>
            <?php } ?>  
            <?php if(!empty($description)){ ?>
                <p class="description">
                    <?php echo trim( $description ); ?>
                </p>
            <?php } ?> 
            <?php
                if (function_exists('mc4wp_show_form')) {
                    try {
                        $form = mc4wp_get_form();
                        echo do_shortcode('[mc4wp_form id="'. $form->ID .'"]');
                    } catch (Exception $e) {
                        esc_html_e('Please create a newsletter form from Mailchip plugins', 'zota');
                    }
                }
            ?>
            <?php if ( isset($socials) && is_array( $socials)) { ?>

            <?php if( count(array_column($socials, 'status')) > 0 ) : ?> 

                <?php 
                    $link_target = ( $social_link_checkbox == 'on' ) ? 'target="_blank"' : '';
                ?>
                <ul class="social list-inline style2">
                    <?php foreach( $socials as $key=>$social):
                        if( isset($social['status']) && !empty($social['page_url']) ): ?>
                            <li>
                                <a href="<?php echo esc_url($social['page_url']);?>" <?php echo trim($link_target); ?> class="<?php echo esc_attr($key); ?>">
                                    <i class="zmdi zmdi-<?php echo esc_attr($key); ?>"></i><span class="hidden"><?php echo esc_html($social['name']); ?></span>
                                </a>
                            </li>
                    <?php
                            endif;
                        endforeach;
                    ?>
                </ul>
            <?php endif; ?>

            <?php } ?>
        </div>
    </div>
</div>