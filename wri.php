<?php
/*
Plugin Name: WP Related Items (WRI) by WebshopLogic
Plugin URI: http://webshoplogic.com/wp-related-items/
Description: Would you like to offer some related products to your blog posts from your webshop? Do you have an event calendar plugin, end want to suggest some programs to an article? Do you have a custom movie catalog plugin and want to associate some articles to your movies? You need WordPress Related Items plugin, which supports cross post type relationships.
Version: 1.2.7
Author: WebshopLogic
Author URI: http://webshoplogic.com/
License: GPLv2 or later
Text Domain: wp-related-items
Requires at least: 3.7.1
Tested up to: 5.4.1
*/

$this_version = '1.2.7';
$this_plugin_name = 'WP Related Items (WRI) by WebshopLogic'; //can be commented out TEST

//PREV: Name: 'WP Related Items' in the header and in $this_plugin_name variable
//PREV: Version: '1.0.xx PRO' in the header and in $this_version variable
//PREV: Subdir and filename: wp-related-items/wri.php (without -pro)
//PREV: More two parts signed by PREV

/*  Copyright 2020 Peter Rath WebshopLogic

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'WRI' ) ) {

class WRI {

	public $version;
	public $plugin_name;

	function __construct() {
		global $wri_is_premium, $wri_general_settings;

		$version = WRI_VERSION;
		$plugin_name = WRI_NAME;
		$wri_is_premium = FALSE;
		
		include_once( 'wri-utils.php' );
		include_once( 'wri-admin-page.php' );

		//PREV: This is needed in case of previous solution 
		//if ($wri_is_premium) //disable auto update from wordpress.org
		//	add_filter( 'site_transient_update_plugins', array($this, 'filter_plugin_updates' ));


		add_action( 'init', array( $this, 'init' ), 0 );

		register_activation_hook( __FILE__, array( $this, 'wri_activation' ) );

		$wri_general_settings = get_option('wri_general_settings');

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //is_plugin_active needs to include this
		
		if ( 1 == $wri_general_settings['enable_plugin'] && is_plugin_active('yet-another-related-posts-plugin/yarpp.php')) {  //if WRI plugin and yarpp is ENABLED

			include_once( 'wri-widget.php' );
			include_once( 'phpthumb/ThumbLibWRI.inc.php' );
			include_once( 'wri-woocommerce.php' );
			include_once( 'wri-woocommerce-product-archive-customiser.php' );

			if ( ! class_exists( 'Acf' ) ) {  //if ACF plugin is installed, it is not needed
				
				define( 'ACF_LITE', true ); //remove all visual interfaces of ACF plugin
				include_once( 'advanced-custom-fields/acf.php' );

			}			
			
			include_once( 'wri-admin-manual_relations.php' );

			add_filter( 'the_content', array( $this, 'the_wri_content_page_bottom' ), 1200 );

			add_filter('wri_choose_template', array( $this, 'wri_choose_template'),10,6);
			//special templates can be inserted this filter (e.g. WooCommerce)

			//set yarpp_support for all selected post types chosen by user
			add_action( 'registered_post_type', array($this, 'modify_cpt_yarpp_support'), 10, 2 );
			add_action( 'registered_taxonomy', array($this, 'modify_tax_yarpp_support'), 10, 3 );

			do_action( 'wri_intagration_init' );
			
		}

	}

	public function init() {

		load_plugin_textdomain( 'wp-related-items', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


		global $wri_admin_page;
		$wri_admin_page = new WRI_Admin_Page;

		$wri_general_settings = get_option('wri_general_settings');

		if ( 1 == $wri_general_settings['enable_plugin'] && is_plugin_active('yet-another-related-posts-plugin/yarpp.php')) {  //if WRI plugin and yarpp is ENABLED

			add_filter( 'body_class', array( $this, 'wri_item_columns_class' ) ); //column handling part

			global $wri_manual_relationships;
			$wri_manual_relationships = new WRI_manual_relationships;

			$this->template_url = apply_filters( 'wri_template_url', 'wri/' );

			do_action( 'wri_init' );

			if ( 1 == $wri_general_settings['hide_woocommerce_related_products'] ) {
				// Remove WooCommerce Related Products
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
			}

			//Disable YARPP related.css stylesheet	
			if ( 1 == $wri_general_settings['dequeue_style_yarppRelatedCss'] ) {
				add_action('get_footer', array( $this, 'dequeue_footer_styles') );
			}
			
			//Disable YARPP widget.css stylesheet				
			if ( 1 == $wri_general_settings['dequeue_style_yarppWidgetCss'] ) {
				add_action('wp_print_styles',array( $this, 'dequeue_header_styles') );
			}	
			
		}

	}

	//set_css_category_column_width_on_list_tables
	function set_css_category_column_width_on_list_tables()
	{
		
		$wri_general_settings = get_option('wri_general_settings');
		
		$category_column_width = $wri_general_settings['category_column_width_on_list_tables'];
		if ( $category_column_width != null and $category_column_width != '' ) {
		
			echo '
				<style>
					.column-categories {
					    width: ' . $category_column_width . '% !important;
					}				 
				</style>
			';
		}

	}

	//Disable YARPP related.css stylesheet
	function dequeue_footer_styles()
	{
	  wp_dequeue_style('yarppRelatedCss');
	}
		
	//Disable YARPP widget.css stylesheet		
	function dequeue_header_styles()
	{
	  wp_dequeue_style('yarppWidgetCss');
	}	
	
	public function wri_activation() {

		require_once(ABSPATH . 'wp-admin/includes/file.php');

		$url = wp_nonce_url('themes.php?page=example','example-theme-options');
		if (false === ($creds = request_filesystem_credentials($url, '', false, false, null) ) ) {
			return;
		}

		if ( ! WP_Filesystem($creds) ) {
			request_filesystem_credentials($url, '', true, false, null);
			return;
		}

		$stylesheet_dir = get_stylesheet_directory();

		$plugin_dir = $this->plugin_path();

		global $wp_filesystem;
		if ( !$wp_filesystem->mkdir($stylesheet_dir . '/wri_template', FS_CHMOD_DIR) )
			new WP_Error('mkdir_failed', __('Could not create directory.','wp-related-items'), $stylesheet_dir . '/wri_template');

		if ( ! copy_dir( $plugin_dir . '/wri_template', $stylesheet_dir . '/wri_template')
		 ) {
    		echo __('Error copying file!','wp-related-items');
		}
	}


	//public $css_dependency_array = array('woocommerce_frontend_styles-css'); //This plugin overrides some css styles of other plugins


	public $plugin_path;

	public $template_url;

	function the_wri_content_page_bottom($content) {
		if ( in_the_loop() ) {
			remove_filter( 'the_content', array( $this, 'the_wri_content_page_bottom' ), 1200 );
			$content =
				apply_filters('wri_content_clear_start','<div class="wri_content_clear_both">')
				. $content
				. apply_filters('wri_content_clear_end','</div>')
				. $this->wri_display_related('on_page', 'bottom', null, TRUE)
				. $this->promote_text();
			add_filter( 'the_content', array( $this, 'the_wri_content_page_bottom' ), 1200 );
		}
		return $content;
	}


	function promote_text() {

		global $wri_general_settings, $wri_no_result;

		$ret = null;

		if ( 1 == $wri_general_settings['promote'] and !$wri_no_result) {

			$ret = '<div class="wri_promote" >'
				. (__('Related items is presented by WP Related Items Plugin.'))
				. '</div>';

		}
		return $ret;
	}

	function wri_display_related($placement, $position, $widget_instance, $enable_title) {


		ob_start();

		global $wpdb, $post;

		$reference_post_type_name = get_post_type();

		$wri_general_settings = get_option('wri_general_settings');

		if (is_array($wri_general_settings['wri_used_posttypes'])) {

			$wri_used_posttypes = array_keys( $wri_general_settings['wri_used_posttypes'] );

			if ( is_array($wri_used_posttypes) and  in_array($reference_post_type_name, $wri_used_posttypes) ) {  //reference post type is supported by WRI?

				unset($reference2related_option_array);    // This deletes the whole array

				//Collect the options into an array (for ordering)
				foreach ($wri_used_posttypes as $wri_used_posttype) {

					$reference2related_option_array[]=get_option( 'wri_reference2related_items__' . $reference_post_type_name . '--' . $wri_used_posttype );

				}

				if ( is_array($reference2related_option_array) ) {

					// Obtain a list of columns for sort field, then short the array by display order

					foreach ($reference2related_option_array as $key => $value) {
					    $tmp[$key]  = $value['display_order'];
					}

					array_multisort($tmp, SORT_ASC, $reference2related_option_array);

					$yarpp_option = get_option('yarpp');

					$singular = is_singular();

					foreach ($reference2related_option_array as $reference2related_option) {
						
						if (
								('on_page' == $placement && $singular && $reference2related_option['position'] == $position) //in case of on_page display, if position is fit
								||('on_page' == $placement && !$singular && $reference2related_option['position_in_archive'] == $position)
								||('on_widget' == $placement && $reference2related_option['related_posttype'] == $widget_instance['related_posttype'] //or in case of on_widget display and widget's related type is match
									&& ('just_on_widget' == $reference2related_option['position'] ||'1' != $widget_instance['hide_if_duplicate']) // and if the position setting is 'just on widget' or if not, but the hide_id_duplicate is turned off
								  )
							) {

							$related_option = get_option( 'wri_related_items___' . $reference2related_option['related_posttype'] );  //get related_item options

							$wri_template = '';
							$wri_template = apply_filters('wri_choose_template', '', $placement, $position, $widget_instance, $related_option, $reference2related_option);

							$option_array=array(
									// Pool options: these determine the "pool" of entities which are considered
									'post_type' => array( $reference2related_option['related_posttype'] ),
									//'show_pass_post' => false, // show password-protected posts
									//'past_only' => false, // show only posts which were published before the reference post
									//'exclude' => array(), // a list of term_taxonomy_ids. entities with any of these terms will be excluded from consideration.
									//'recent' => false, // to limit to entries published recently, set to something like '15 day', '20 week', or '12 month'.

									// Relatedness options: these determine how "relatedness" is computed
									// Weights are used to construct the "match score" between candidates and the reference post
									//'weight' => array(
									//	'body' => 1,
									//	'title' => 2, // larger weights mean this criteria will be weighted more heavily
									//	'tax' => array(
									//		'post_tag' => 1,
									//		'category' => 1
											//... put any taxonomies you want to consider here with their weights
									//	)
									//),
									// Specify taxonomies and a number here to require that a certain number be shared:
									//'require_tax' => array(
									//	'post_tag' => 1 // for example, this requires all results to have at least one 'post_tag' in common.
									//),
									// The threshold which must be met by the "match score"
									'threshold' => (int) nvl($widget_instance['match_threshold'], $reference2related_option['match_threshold']),
									// Display options:
									'template' => $wri_template, // either the name of a file in your active theme or boolean=false to use the builtin template
									'limit' => (int) nvl($widget_instance['display_limit'],nvl( $reference2related_option['display_limit'], $yarpp_option['limit'] )), // maximum number of results
									'order' => nvl($widget_instance['order'],nvl( $reference2related_option['order'], $yarpp_option['order'] )), // e.g. 'score DESC'
									'wri_title' => $enable_title ? $related_option['title'] : '',
									'wri_before_title_tags' => $related_option['before_title_tags'],
									'wri_after_title_tags' => $related_option['after_title_tags'],
									'wri_no_result_display_text' => nvl($related_option['no_result_display'], $yarpp_option['no_results']),
									'wri_thumbnail_width' => (int) $widget_instance['thumbnail_width'],
									'wri_thumbnail_height' => (int) $widget_instance['thumbnail_height'],
									'wri_maximum_excerpt_characters' => (int) $widget_instance['maximum_excerpt_characters'],
									'wri_widget_mode' => isset($widget_instance)


							);

							//wsl_log(null, 'wri-pro.php wri_display_related $option_array 0: ' . wsl_vartotext($option_array));
							
							//Remove empty (null) array elements. This is important because setup_active_cache function of yapp may baypass cache because of a null value;
							//0 or '0' is right, so we have to include the items with zero values 
							//$option_array = array_diff($option_array, array('')); //this first solution was not good, because PHP Notice:  Array to string conversion error occured, because it is only for handling string arrays, and not multi dimensional or other arrays 
							$oprion_array2 = array();
							foreach ($option_array as $key => $value) {
									
								//wsl_log(null, 'wri-pro.php wri_display_related $key: ' . wsl_vartotext($key));
								//wsl_log(null, 'wri-pro.php wri_display_related $value: ' . wsl_vartotext($value));
								//wsl_log(null, 'wri-pro.php wri_display_related !empty($value): ' . wsl_vartotext(!empty($value)));
								//wsl_log(null, 'wri-pro.php wri_display_related $value != üres: ' . wsl_vartotext($value != ''));
								//wsl_log(null, 'wri-pro.php wri_display_related !is_null($value): ' . wsl_vartotext(!is_null($value)));
								
								if ( (! empty($value) and $value != '' and ! is_null($value)) or $value === 0 or $value === '0' ) {
									$oprion_array2[$key] = $value;
								}
							}
							
							$option_array = $oprion_array2;
							  
							//wsl_log(null, 'wri-pro.php wri_display_related $option_array: ' . wsl_vartotext($option_array));							

							$wri_yarpp_related_options = ($option_array
								//,$reference_ID // second argument: (optional) the post ID. If not included, it will use the current post.
								//,true // third argument: (optional) true to echo the HTML block; false to return it
							);


							$wri_yarpp_related_options = apply_filters('wri_yarpp_related_options', $wri_yarpp_related_options); //you can change related options array using this filter

							yarpp_related($wri_yarpp_related_options);

						}

					wp_reset_postdata();

					} //end for

				} //end if

			} //end if

		} //end_if

		$content = ob_get_clean();

		return $content;

	}

	public function wri_choose_template($val, $placement, $position, $widget_instance, $related_option, $reference2related_option) {

		if ('on_page' == $placement) {

			if ( !empty($reference2related_option['custom_template']) )
				$wri_template = $reference2related_option['custom_template'];
			elseif ('list' == $reference2related_option['list_thumbnail'])
				$wri_template = 'wri_template/template-wri-list.php';
			elseif ('thumbnail' == $reference2related_option['list_thumbnail'])
				$wri_template = 'wri_template/template-wri-thumbnails.php';
			elseif ('yarpp_thumbnail' == $widget_instance['list_thumbnail'])
				$wri_template = 'thumbnails';  //use yarpp own default thumbnail templat

		}

		elseif ('on_widget' == $placement) {

			if ( !empty($widget_instance['custom_template']) )
				$wri_template = $reference2related_option['custom_template'];
			elseif ('list' == $widget_instance['list_thumbnail'])
				$wri_template = 'wri_template/template-wri-list-widget.php';
			elseif ('thumbnail' == $widget_instance['list_thumbnail'])
				$wri_template = 'wri_template/template-wri-thumbnails-widget.php';
			elseif ('yarpp_thumbnail' == $widget_instance['list_thumbnail'])
				$wri_template = 'thumbnails';  //use yarpp own default thumbnail templat

		}

		return nvl($wri_template, $val);

	}


	public function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function plugin_url() {
		if ( $this->plugin_url ) return $this->plugin_url;
		return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	//disable plugin update notice (in PRO)
	function filter_plugin_updates( $value ) {
		if(isset($value->response[ plugin_basename(__FILE__) ]))		
			unset($value->response[ plugin_basename(__FILE__) ]);	    
	    return $value;
	}	

	public function wri_supported_post_types($output) { // 'names' or 'objects'
		//all post type that supported in WRI

		$ret = get_post_types(array('public'=>true, 'publicly_queryable'=>true, 'show_ui'=>true), $output);
		$ret = array_merge( $ret, get_post_types(array('name'=>'page'), $output));

		$ret = apply_filters('wri_supported_post_types', $ret);

		return $ret;

	}

	public function wri_used_post_types($output) { // 'names' or 'objects'
		//a subset of WRI supported post types, according to user choice

		$ret=array();
		$wri_general_settings = get_option('wri_general_settings');

		if ( isset( $wri_general_settings['wri_used_posttypes'] ) && is_array( $wri_general_settings['wri_used_posttypes'] ) ) {
			foreach ( array_keys( $wri_general_settings['wri_used_posttypes'] ) as $act_wri_used_posttype_name ) {

				$ret = array_merge($ret, get_post_types(array('name'=>$act_wri_used_posttype_name), $output) );
				//$ret[] = get_post_types(array('name'=>$act_wri_used_posttype_name), $output);

			}
		}

		$ret = apply_filters('wri_used_post_types' ,$ret);

		return $ret;

	}


	function modify_cpt_yarpp_support( $post_type, $args ) {

		global $wp_post_types;

		$wri_general_settings = get_option('wri_general_settings');
		$wri_used_posttypes = $wri_general_settings['wri_used_posttypes'];

	    // Set yarpp_support argument for wri supported post types
   		if ( is_array($wri_used_posttypes) ) {	    
	   		if ( is_array($wri_used_posttypes) and in_array( $post_type, array_keys( $wri_used_posttypes ) ) ) {
	   			$wp_post_types[$post_type]->yarpp_support = true;
	   		}
		}

	}

	function modify_tax_yarpp_support( $taxonomy, $post_type, $args ) {

		global $wp_taxonomies;

	    // Set yarpp_support argument for all taxonomies that is registered to the post type.

		$wri_general_settings = get_option('wri_general_settings');
		$wri_used_posttypes = $wri_general_settings['wri_used_posttypes'];

		//if post_type is string, we convert it to array
	 	if (!is_array($post_type))
			 $post_type_arr[] = $post_type;
		else
			$post_type_arr = $post_type;

		//if the two array has common part:
		if ( is_array($post_type_arr) and is_array($wri_used_posttypes) ) {
			if (array_intersect($post_type_arr, array_keys( $wri_used_posttypes ))!=null){
				$wp_taxonomies[$taxonomy]->yarpp_support = true;
			}
		}

	}


	public function get_featured_image_path() {

		$post_id = get_the_ID();
		$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
		$image_url_parts = parse_url($image_url[0]);
		$image_path = $image_url_parts['path'];
		return $image_path;

	}

	public function get_thumb_url($thumbnail_path, $thumbnail_width, $thumbnail_height) {

		$thumb_store_dir = 'thumb_store';  //under plugin path

		$server_base_path = $_SERVER["DOCUMENT_ROOT"];

		$thumbnail_path = $server_base_path . $thumbnail_path;

		$thumbnail_path_components = pathinfo( $thumbnail_path );

		$thumb_store_filename = urlencode( $thumbnail_path_components['filename']
												. '-wri-' . $thumbnail_width . 'x' . $thumbnail_height
												. '.' . $thumbnail_path_components['extension'] );

		$thumb_store = $thumb_store_dir . '/' . $thumb_store_filename;


		if ( file_exists( $this->plugin_path . $thumb_store ) ) { //if already exists, use it, else make new one

			$ret =  $this->plugin_url() . '/' . $thumb_store;

		} else {


				$thumb = PhpThumbFactoryWRI::create( $thumbnail_path );

				$thumb->adaptiveResize( $thumbnail_width, $thumbnail_height );

				$thumb->save( $this->plugin_path() . '/' . $thumb_store );

				$ret = $this->plugin_url() . '/' . $thumb_store;

		}

		return $ret;

	}


	function wri_item_columns_class( $classes ) {

		$post_type = get_post_type();

		$options = get_option( 'wri_related_items___' . $post_type );
		$column_option = $options['thumbnail_columns_number'];

		if ( isset( $column_option ) and $column_option != 0 and $column_option != null and in_array($column_option, array(2, 3, 5)) ) {//if column number is set for the actual post type, let's use it, else use the original
			$columns = $column_option;
			$classes[] = 'wri-item-columns-' . $columns;
		}

		return $classes;
	}

}

}

// Define constasnts
		define( 'WRI_PLUGIN_FILE', __FILE__ );
		define( 'WRI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'WRI_VERSION', $this_version );
		define( 'WRI_NAME', $this_plugin_name );

//PREV: This does not needed in previous solutions

// Examine if Lite and PRO versions are used at the same time. BEGIN

	if ( ! function_exists ( 'wri_show_plugin_conflict_error_notice' ) ) {
		function wri_show_plugin_conflict_error_notice() {
			global $wri_conflict_plugin_names;
		
			if ( isset( $wri_conflict_plugin_names ) and is_array( $wri_conflict_plugin_names ) ) 
			foreach ($wri_conflict_plugin_names as $conflict_plugin_name ) {
				?>
				<div class="error notice">
				    <p><?php echo '<b>' . __( 'Plugin temporary disabled', 'wp-related-items' ) . ':</b> ' . $conflict_plugin_name . '<br>' . __( 'Lite and Pro or old and new versions of the plugin cannot be used at the same time! Deactivate one of them.', 'wp-related-items' ) . '<br>' . __( 'If you use an earlier and new version of PRO plugin, please use that one, that <b>name</b> contains the PRO sign. Deatcivate that one, that <b>version</b> number contains PRO.', 'wp-related-items' ); ?></p>
				</div>
				<?php			
			} 
		}	
	}

	$this_plugin = array( plugin_basename( __FILE__ ) );

	$active_plugins = (array) get_option( 'active_plugins', array() );
	$active_sitewide_plugins = (array) get_site_option( 'active_sitewide_plugins');
	
	$similar_plugin_names = array(
		'wp-related-items-pro/wri-pro.php',
		'wp-related-items/wri-pro.php',
		'wp-related-items/wri.php',
	);
	
	$possibly_conflict_plugin_names = array_diff( $similar_plugin_names, $this_plugin );
	
	//wsl_log(null, 'wri-pro.php $this_plugin: ' . wsl_vartotext($this_plugin));
	//wsl_log(null, 'wri-pro.php $similar_plugin_names: ' . wsl_vartotext($similar_plugin_names));
	//wsl_log(null, 'wri-pro.php $possibly_conflict_plugin_names: ' . wsl_vartotext($possibly_conflict_plugin_names));
	//wsl_log(null, 'wri-pro.php $active_plugins: ' . wsl_vartotext($active_plugins));
	
	global $wri_conflict_plugin_names;
	//$wri_conflict_plugin_names = array();
	if ( isset($possibly_conflict_plugin_names) and is_array($possibly_conflict_plugin_names) )
	foreach ( $possibly_conflict_plugin_names as $possibly_conflict_plugin_name ) {
	
		$is_conflict = in_array( $possibly_conflict_plugin_name, $active_plugins ) || ( is_multisite() and isset($active_sitewide_plugins[$possibly_conflict_plugin_name]) );
		
		//in case of real conflict, we add the plugin name to this
		if ( $is_conflict ) {
			$wri_conflict_plugin_names[] = $possibly_conflict_plugin_name;
		}
		
	}
	
	//wsl_log(null, 'wri-pro.php $wri_conflict_plugin_names: ' . wsl_vartotext($wri_conflict_plugin_names));
	
	//if there is plugin conflict
	if ( isset($wri_conflict_plugin_names) and is_array($wri_conflict_plugin_names) and count($wri_conflict_plugin_names) > 0 ) {
	
		add_action( 'admin_notices', 'wri_show_plugin_conflict_error_notice' );
		
		//wsl_log(null, 'wri-pro.php $conflict_plugin_names 2: ' . wsl_vartotext($wri_conflict_plugin_names));
					
		return;
	}
	
// Examine if Lite and PRO versions are used at the same time. END

		
//Init WRI class (only if there is no collution)
$GLOBALS['wri'] = new WRI();
