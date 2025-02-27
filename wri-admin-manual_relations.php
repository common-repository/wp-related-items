<?php

class WRI_manual_relationships {
	
	function __construct() {
			
		$wri_general_settings = get_option('wri_general_settings');
		
		if ( 1 == $wri_general_settings['enable_wri_manual_relationships'] ) {
			add_action( 'admin_init', array( $this, 'wri_manual_relationships_init' ) );
			
		}
		
	}

	//public $wri_supported_post_type_names;
	
	public $wri_used_post_type_names;

    public function wri_manual_relationships_init() {
			
		$this->register_manual_relationships_field_group();	
		
	}

 
/**
 *  Register Field Groups
 *
 *  The register_field_group function accepts 1 array which holds the relevant data to register a field group
 *  You may edit the array as you see fit. However, this may result in errors if the array is not compatible with ACF
 * 
 * The base of this function was generated by AFC plugin.
 * 
 * Using ACF in a plugin
 * Including the (free) Advanced Custom Fields plugin inside a free / premium plugin is allowed.
 * You can NOT include any purchased add-ons within the plugin.
 * For your plugin to use any of the premium Add-ons you must ask the customer / user to purchase and include the Add-ons. 
 * 
 * IMPORTANT
 *  For more information, please read:
 *  - http://www.advancedcustomfields.com/terms-conditions/
 *  - http://www.advancedcustomfields.com/resources/getting-started/including-lite-mode-in-a-plugin-theme/
 * 
 */

	function register_manual_relationships_field_group() {

		global $wri, $wri_is_premium;

		$locations = array();
		$i = 0;

		foreach ( $wri->wri_used_post_types('names') as $wri_used_post_type_name ) {
			$i++;
			$locations[][] = array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => $wri_used_post_type_name,
							'order_no' => 0,
							'group_no' => $i,
						);
		}

		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_wri-manual-relationships',
				'title' => 'WRI Manual Relationships - pro',
				'fields' => array (
					array (
						'key' => 'field_526cc294759d6',
						'label' => __('Set manual relationships','wp-related-items'),
						'name' => $wri_is_premium ? 
							'wri_manual_relationships' :
							'wri_manual_relationships_FREE',
						'type' => 'relationship',
						'instructions' => $wri_is_premium ? 
							__('Chose items from left side list and add it to the right side list.','wp-related-items') :						
							__('This function is available only on WRI premium version. Manually adjusted relationships have no effects on WRI free version. You can try, but dont use it on free version, because relationships are not saved!','wp-related-items'),
						'return_format' => 'id',
						'post_type' => $wri->wri_used_post_types('names'), 
						'taxonomy' => array (
							0 => 'all',
						),
						'filters' => array (
							0 => 'search',
							1 => 'post_type',
						),
						'result_elements' => array (
							0 => 'featured_image',
							1 => 'post_type',
							2 => 'post_title',
						),
						'max' => '',
					),
				),
				'location' => $locations,
				/*array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'post',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'product',
							'order_no' => 0,
							'group_no' => 1,
						),
					),
				),*/
				'options' => array (
					'position' => 'normal',
					'layout' => 'default',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			));
		}
	}
	
}