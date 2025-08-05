<?php

$footer = apply_filters('zota_tbay_get_footer_layout', 'footer_default');
$cookie_newsletter = zota_tbay_get_cookie('newsletter_remove');
?>

	</div><!-- .site-content -->
	<?php if (zota_tbay_active_newsletter_sidebar() && !$cookie_newsletter) : ?>
		<div id="newsletter-popup" class="newsletter-popup">
			<?php dynamic_sidebar('newsletter-popup'); ?>
		</div>
	<?php endif; ?>
	
	<?php if (!zota_checkout_optimized()) : ?>
		<footer id="tbay-footer" <?php zota_tbay_footer_class(); ?>>
			<?php if ($footer != 'footer_default'): ?>
				
				<?php zota_tbay_display_footer_builder(); ?>

			<?php else: ?> 
				
				<?php get_template_part('page-templates/footer-default'); ?>
				
			<?php endif; ?>			
		</footer><!-- .site-footer -->
	<?php endif; ?>

	<?php
		/**
		* zota_after_do_footer hook
		*
		* @hooked zota_after_do_footer - 10
		*/
		do_action('zota_after_do_footer');
	?>

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>