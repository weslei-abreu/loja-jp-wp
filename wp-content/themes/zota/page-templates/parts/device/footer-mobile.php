<?php
    /**
     * zota_before_topbar_mobile hook
     */
    do_action( 'zota_before_footer_mobile' );
?>
<div class="footer-device-mobile d-xl-none clearfix">

    <?php
        /**
        * zota_before_footer_mobile hook
        */
        do_action( 'zota_before_footer_mobile' );

        /**
        * Hook: zota_footer_mobile_content.
        *
        * @hooked zota_the_custom_list_menu_icon - 10
        */

        do_action( 'zota_footer_mobile_content' );

        /**
        * zota_after_footer_mobile hook
        */
        do_action( 'zota_after_footer_mobile' );
    ?>

</div>