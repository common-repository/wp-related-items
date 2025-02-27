<?php
/**
 * Loop-shop - copy of WooCommerce deprecated loop-shop.php
 * (Use your own loop code, as well as the content-product.php template. loop-shop.php will be removed in WC 2.1.)
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce_loop;
$options = get_option( 'wri_related_items___product' );
$woocommerce_loop['columns'] = $options['thumbnail_columns_number'];

?>

<?php if ( have_posts() ) : ?>

	<?php do_action('woocommerce_before_shop_loop'); ?>

	<?php woocommerce_product_loop_start(); ?>

		<?php woocommerce_product_subcategories(); ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<?php wc_get_template_part( 'content', 'product' ); /*deprecated woocommerce_get_template_part*/?>

		<?php endwhile; // end of the loop. ?>

	<?php woocommerce_product_loop_end(); ?>

	<?php do_action('woocommerce_after_shop_loop'); ?>

<?php else : ?>

	<?php if ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

		<p><?php _e( 'No products found which match your selection.', 'woocommerce' ); ?></p>

	<?php endif; ?>

<?php endif; ?>

<div class="clear"></div>