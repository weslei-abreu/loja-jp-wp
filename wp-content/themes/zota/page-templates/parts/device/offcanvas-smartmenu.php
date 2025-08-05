<?php 
    $location = 'mobile-menu';
    $tbay_location  = '';
    if ( has_nav_menu( $location ) ) { 
        $tbay_location = $location;
    }

    
    $mmenu_langue           = zota_tbay_get_config('enable_mmenu_langue', false); 
    $mmenu_currency         = zota_tbay_get_config('enable_mmenu_currency', false); 

    $menu_mobile_select    =  zota_tbay_get_config('menu_mobile_select');

?>
  
<div id="tbay-mobile-smartmenu" data-title="<?php esc_attr_e('Menu', 'zota'); ?>" class="tbay-mmenu d-xl-none"> 


    <div class="tbay-offcanvas-body">
        
        <div id="mmenu-close">
            <button type="button" class="btn btn-toggle-canvas" data-toggle="offcanvas">
                <i class="tb-icon tb-icon-close-01"></i>
            </button>
        </div>

        <?php 
        if ( empty($menu_mobile_select) ) {
            $locations  = get_nav_menu_locations();
            $menu_id    = $locations[ $tbay_location ] ;
            $menu_obj   = wp_get_nav_menu_object( $menu_id );
        } else {
            $menu_obj = wp_get_nav_menu_object($menu_mobile_select);
        }

        $menu_name = $menu_obj->slug;
        ?>
        <nav id="tbay-mobile-menu-navbar" class="menu navbar navbar-offcanvas navbar-static" data-id="<?php echo esc_attr($menu_name); ?>" >
            <?php 
                echo zota_get_mobile_menu_mmenu($menu_name);
            ?>
        </nav>


    </div>
    <?php if($mmenu_langue || $mmenu_currency ) {
        ?>
         <div id="mm-tbay-bottom">  
    
            <div class="mm-bottom-track-wrapper">

                <?php 
                    ?>
                    <div class="mm-bottom-langue-currency ">
                        <?php if( $mmenu_langue ): ?>
                            <div class="mm-bottom-langue">
                                <?php do_action('zota_tbay_header_custom_language'); ?>
                            </div>
                        <?php endif; ?>
                
                        <?php if( $mmenu_currency && class_exists('WooCommerce') && class_exists( 'WOOCS' ) ): ?>
                            <div class="mm-bottom-currency">
                                <div class="tbay-currency">
                                <?php echo do_shortcode( '[woocs txt_type = "desc"]' ); ?> 
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    <?php
                ?>
            </div>


        </div>
        <?php
    }
    ?>
   
</div>