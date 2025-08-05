<?php 
/**
 * Templates Name: Elementor
 * Widget: List Nav
 */

$available_menus = $this->get_available_menus();

if (!$available_menus) {
	return;
}

$settings = $this->get_active_settings();

extract( $settings );

$menuNav = wp_get_nav_menu_items($menu);

if( empty($menuNav) ) return;

$numItems = count($menuNav);
$i = 0;
$separator = $list_menu_separator;

$menu_object = wp_get_nav_menu_object($menu);
$menu_name = $menu_object->name;

if( !empty($list_menu_title) ) $menu_name = $list_menu_title;

?>
<div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

	<?php if( !empty($menu_name) ) : ?>
		<div class="list-menu-heading">
			<?php echo '<strong>'.trim($menu_name).':</strong>'; ?>
		</div>
	<?php endif; ?>
	<div class="list-menu-wrapper">
		<?php 
			foreach ( $menuNav as $navItem ) { 
				if( ++$i === $numItems ) $separator = '';

				echo '<a href="'. esc_url($navItem->url) .'" title="'. esc_attr($navItem->title) .'">'. trim($navItem->title) .'</a>'.$separator;
			
			}
		?>
	</div>


</div>