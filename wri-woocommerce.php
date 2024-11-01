<?php

function wri_woocommerce_init() {

	global $wri;

	add_filter( 'body_class', 'wri_woocommerce_item_columns_class' );
	add_filter( 'loop_shop_columns', 'wri_loop_shop_columns', 30); 

	//set woocommerce template decision roules
	add_filter('wri_choose_template', 'wri_choose_template_woocommerce',15,6); 

	//This is not necessary, because wri->modify_cpt_yarpp_support and wri->modify_tax_yarpp_support do this generally
	/*
	//set yarpp support for woocommerce product post type, tags and categories
	add_filter('woocommerce_register_post_type_product', 'woocommerce_register_post_type_product_filter' );
	add_filter('woocommerce_taxonomy_args_product_tag', 'woocommerce_taxonomy_args_product_tag_filter' );
	add_filter('woocommerce_taxonomy_args_product_cat', 'woocommerce_taxonomy_args_product_cat_filter' );
	*/

}

//This is not necessary, because wri->modify_cpt_yarpp_support and wri->modify_tax_yarpp_support do this generally
/*
function woocommerce_register_post_type_product_filter( $array ){
	//set yarpp support for woocommerce product post type
    $array['yarpp_support']=true;
    return $array;
}

function woocommerce_taxonomy_args_product_tag_filter( $array ){
	//set yarpp support for woocommerce tags
    $array['yarpp_support']=true;
    return $array;
}


function woocommerce_taxonomy_args_product_cat_filter( $array ){
	//set yarpp support for woocommerce categories
    $array['yarpp_support']=true;
    return $array;
}
*/

function wri_choose_template_woocommerce($val, $placement, $position, $widget_instance, $related_option, $reference2related_option) {
	//select the appropriate template files speciali for Woocommerce product

	$wri_template = null;
	
	if ('on_page' == $placement) {
		
		if ( 'product' == $reference2related_option['related_posttype'] and 'thumbnail' == $reference2related_option['list_thumbnail'] )
			$wri_template = 'wri_template/template-wri-thumbnails-woocommerce.php';

	}
	elseif ('on_widget' == $placement) {

		if ( 'product' == $reference2related_option['related_posttype'] and 'thumbnail' == $widget_instance['list_thumbnail'] )
			$wri_template = 'wri_template/template-wri-thumbnails-widget-woocommerce.php';
	
	}

	return nvl($wri_template, $val); 
	
}

function wri_loop_shop_columns( $columns ) {
	$post_type = get_post_type();
	
	$options = get_option( 'wri_related_items___product' );
	$column_option = $options['thumbnail_columns_number'];   	
	
	if ( $post_type = 'product' and isset( $column_option ) and $column_option != 0 and $column_option != null) //if column number is set for the actual post type, let's use it, else use the original
		$columns = $column_option;

	return $columns;
}

function wri_woocommerce_item_columns_class( $classes ) {
		
	$columns = apply_filters( 'loop_shop_columns', 4 );	
	$classes[] = 'wri-woocommerce-item-columns-' . $columns;
	return $classes;
}

//function wri_woocommerce_css() {
//	wp_enqueue_style( "wri-thumbnails-woocommerce", get_template_directory_uri() . '/' . 'wri_template/styles-wri-thumbnails_woocommerce.css');
//}

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) { 
	add_action( 'wri_intagration_init', 'wri_woocommerce_init' );
}

