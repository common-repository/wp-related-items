<?php

//These functions extend woocommerce_product_archive_customiser plugin.
//woocommerce_product_archive_customiser plugin handels only shop, product category and product tag pages.
//this is extend functionality every page type (function wc_pac_columns() if ( is_shop() || is_product_category() || is_product_tag() )

function wri_woocommerce_product_archive_customiser_init() {

	add_filter( 'loop_shop_columns', 'pac_loop_shop_columns' );
	add_filter( 'body_class', 'woocommerce_product_columns_class' );

}

function pac_loop_shop_columns() {
	$columns = get_option( 'wc_pac_columns' );
	return $columns;
}

function woocommerce_product_columns_class( $classes ) {
		
	$columns = apply_filters( 'loop_shop_columns', 4 );
	$classes[] = 'product-columns-' . $columns;
	return $classes;
}

if ( is_plugin_active( 'woocommerce-product-archive-customiser/archive-customiser.php' ) ) { 
	add_action( 'wri_intagration_init', 'wri_woocommerce_product_archive_customiser_init' );
}
