<?php
/*
 * WRI template for WooCommerce widget thumbnails
 * Don't modify this file and directory. If you need changes own templates can be used. Your own templates 
 * should be in the main directory of your active theme, the file name must conform 
 * to the following naming convention: yarpp-template-....php
 * Please find more details about templates in Yarpp documentations.
 */
 
global $woocommerce;

if ( have_posts() ) {

	if ( isset($before_widget) ) {
		echo $before_widget;
	}

	if ( isset($title) and $title ) {
		echo $before_title . $title . $after_title;
	}
		
	echo '<div class="woocommerce">';
	echo '<ul class="product_list_widget">';

	while ( have_posts()) {
		the_post();
		global $product;

		// check visibility, if not visible, then do not display
		if ( $product and $product->is_visible() ) {
			echo '<li>
				<a href="' . get_permalink() . '">
					' . ( has_post_thumbnail() ? get_the_post_thumbnail( null, 'shop_thumbnail' ) : woocommerce_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					
				</a> ' . $product->get_price_html() . '
			</li>';
		}
	}

	echo '</ul>';
	echo '</div>';

	if ( isset($after_widget) ) {
		echo $after_widget;
	}
}

wp_reset_postdata();
