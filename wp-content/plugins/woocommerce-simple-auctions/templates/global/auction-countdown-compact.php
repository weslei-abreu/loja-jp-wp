<?php
/**
 * Auction countdown template
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global  $product, $post;

if ( $product->get_type() !== 'auction'){
 return;
}
$format =  $product->get_seconds_remaining() > 86000 ?  'D' : 'HMS';

if ( ( $product->is_closed() === FALSE ) && ($product->is_started() === TRUE ) ) : ?>

		<div class="main-auction auction-time-countdown-compact" data-time="<?php echo $product->get_seconds_remaining() ?>" data-auctionid="<?php echo $product->get_id() ?>" data-format="<?php echo $format ?>"></div>

<?php 
elseif ( ( $product->is_closed() === FALSE ) && ($product->is_started() === FALSE ) ) :?>

		<div class="auction-time-countdown-compact future" data-time="<?php echo $product->get_seconds_to_auction() ?>" data-format="<?php echo $format ?>"></div>

<?php endif; 
