
<div class="header-default">
    <div class="container">
        <div class="row">
			<!-- //LOGO -->
            <div class="header-logo col-md-2">
                <?php 
                	zota_tbay_get_page_templates_parts('logo'); 
                ?> 
            </div>
			
			<div class="header-mainmenu col-md-9">
				<?php zota_tbay_get_page_templates_parts('nav'); ?>
			</div>

			<div class="col-md-1">

				<?php if ( !zota_catalog_mode_active() && zota_is_Woocommerce_activated() ): ?>
				<!-- Cart -->
				<div class="top-cart hidden-xs">
					<?php zota_tbay_get_woocommerce_mini_cart(); ?>
				</div>
				<?php endif; ?>

			</div>
        </div>
    </div>
</div>
