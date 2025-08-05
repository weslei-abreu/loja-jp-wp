<div id="tbay-offcanvas-main" class="tbay-offcanvas-main verticle-menu hidden-md"> 
    <div class="tbay-offcanvas-body">
        <div class="offcanvas-head">
        	<h3><?php esc_html_e('Menu','zota'); ?></h3>
            <a href="javascript:void(0);" class="btn-toggle-canvas" data-toggle="offcanvas"><i class="tb-icon tb-icon-close-01"></i></a>
        </div>

        <?php if ( has_nav_menu( 'primary' ) ) : ?>
	        <nav data-duration="400" class="hidden-xs hidden-sm tbay-megamenu slide animate navbar">
	        <?php
	            $args = array(
	                'theme_location' => 'primary',
	                'container_class' => 'collapse navbar-collapse',
	                'menu_class' => 'nav navbar-nav',
	                'fallback_cb' => '',
	                'menu_id' => 'primary-menu',
					'walker' => new zota_Tbay_Nav_Menu()
	            );
	            wp_nav_menu($args);
	        ?>
	        </nav>
		<?php endif; ?>

    </div>
</div>