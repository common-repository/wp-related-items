<?php

class WRI_Admin_Page {
	
	function __construct() {
			
		//add_action( $tag, $function_to_add, $priority, $accepted_args );
		add_action( 'admin_menu', array( $this, 'wri_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wri_admin_init' ) );

		//Remove "Thank you for creating with WordPress." text from footer
		add_filter('update_footer', array( $this, 'hide_footer' ),1000);
		
		//global $yarpp;
		//require_once($yarpp->YARPP_DIR . '/class-cache.php');
		//require_once($yarpp->YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
		
		//$yarpp->storage_class = $yarpp_storage_class;
		//$yarpp->cache = new $yarpp->storage_class( $yarpp );
		
		//add_action( 'transition_post_status', array($yarpp->cache, 'transition_post_status'), 10, 3);
		
	}

	/**
	 * @var string
	 */
	public $wri_supported_post_types;	
	
	
	function wri_admin_menu () {
		
		//add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		add_options_page( __('WP Related Items (WRI)','wp-related-items'),__('WP Related Items (WRI)','wp-related-items'),'manage_options','wsl-post-relater-products', array( $this, 'add_options_page_callback' ) );
	}


    public function wri_admin_init()
    {

		wp_enqueue_style( 'wri-admin', plugins_url('wri-admin.css', __FILE__) );

		global $wri;
		$this->wri_supported_post_types = $wri->wri_supported_post_types('objects');
		$this->wri_used_post_types = $wri->wri_used_post_types('objects');
		
		$this->wri_set_defaults();    	
    	//different options are needed if fields go to different pages!
		
		//GENERAL TAB
		
    	$tabpage = 'wri_general_settings_tabpage';
    	
    	//register_setting( $option_group, $option_name, $sanitize_callback );        
        register_setting(
            $tabpage, // Option group
            'wri_general_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'wri_general_section', // ID 
            __('General Settings','wp-related-items'), // Title //ML
            array( $this, 'print_section_info' ), // Callback 
            'wri_general_settings_tabpage' // Page
        );
		
		//add_settings_field( $id, $title, $callback, $page, $section, $args );
		
        add_settings_field(
            'enable_plugin', // ID
            __('Enable WRI Plugin','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

        add_settings_field(
            'enable_wri_manual_relationships', // ID
            __('Enable manual settings of WRI relationships','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
		
        add_settings_field(
            'wri_maual_relationships_weight', // ID
            __('WRI Manual Relationships Weight (pro)','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
		

        add_settings_field(
            'enable_wri_category', // ID
            __('Enable Similarity Marker Categories (pro)','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

		
        add_settings_field(
            'thumbnail_width', // ID
            __('Thumbnail width','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
		
        add_settings_field(
            'thumbnail_height', // ID
            __('Thumbnail height','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

        add_settings_field(
            'wri_used_posttypes', // ID
            __('Used posttypes','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

        add_settings_field(
            'use_yarpp_title', // ID
            __('Use Yarpp tilte if WRI title is not set','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

        add_settings_field(
            'dequeue_style_yarppRelatedCss', // ID
            __('Disable YARPP related.css stylesheet','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );

        add_settings_field(
            'dequeue_style_yarppWidgetCss', // ID
            __('Disable YARPP widget.css stylesheet','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
                
        add_settings_field(
            'disable_styles_wri_thumbnails_woocommerceCss', // ID
            __('Disable WRI styles-wri-thumbnails_woocommerce.css stylesheet','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
                
                
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_settings_field(
	            'hide_woocommerce_related_products', // ID
	            __('Hide related products displayed by WooCommerce','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            'wri_general_settings_tabpage', // Page
	            'wri_general_section' // Section           
        	);
			
		}

        add_settings_field(
            'category_column_width_on_list_tables', // ID
            __('Category column height on admin list tables','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
        
        
        add_settings_field(
            'promote', // ID
            __('Promote WRI','wp-related-items'), // Title 
            array( $this, 'posttype_callback' ), // Callback
            'wri_general_settings_tabpage', // Page
            'wri_general_section' // Section           
        );
		

			  
        //RELATED TAB
        
        $tabpage = 'wri_related_item_types_tabpage';
		
        foreach ( $this->wri_used_post_types as $act_related_post_type ) {
            //$act_related_post_type

			
			$option_name = 'wri_related_items___' . $act_related_post_type->name; // Option name
			
			//register_setting( $option_group, $option_name, $sanitize_callback );
	        register_setting(
	            $tabpage, // Option group  ----$tabpage, // Option group
	            $option_name, // Option name
	            array( $this, 'sanitize' ) // Sanitize
	        );		
		
			//add_settings_section( $id, $title, $callback, $page );
			add_settings_section(
			    'wri_related_item_types_section', // ID
			    __('Related Items','wp-related-items'). ': ' . $act_related_post_type->labels->name, // Title
			    array( $this, 'print_section_info' ), // Callback
			    $tabpage // Page
			);  		

			//add_settings_field( $id, $title, $callback, $page, $section, $args );
	        add_settings_field( //HIDDEN
	            'posttype', // ID
	            'posttype', // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section           
	        );      

	        add_settings_field(
	            'title', // ID
	            __('Custom title','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section           
	        );      

	        add_settings_field(
	            'before_title_tags', // ID
	            __('Before title HTML tags','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section           
	        );      

	        add_settings_field(
	            'after_title_tags', // ID
	            __('After title HTML tags','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section           
	        );      

	        add_settings_field(
	            'no_result_display', // ID
	            __('Default display if no results','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section           
	        );      

			add_settings_field(
	            'thumbnail_columns_number', // ID
	            __('Number of thumbnail columns (pro)','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section
	        );
				            
	        add_settings_field(
	            'cross_taxonomies', // ID
	            __('Cross taxonomies','wp-related-items'), // Title 
	            array( $this, 'posttype_callback' ), // Callback
	            $tabpage, //Page  
	            'wri_related_item_types_section' // Section
	        );      
		} //end_for

		
		//OTHER TABS
		
        foreach ( $this->wri_used_post_types as $act_reference_post_type ) {				

			$tabpage = 'wri_related_items_settings_tabpage_' . $act_reference_post_type->name;
			
			
			$posttypes = get_post_types(array('public'=>true, 'publicly_queryable'=>true, 'show_ui'=>true), 'object');
			$posttypes = array_merge( $posttypes, get_post_types(array('name'=>'page'), 'object'));

	        foreach ($posttypes as $act_related_post_type) {
	            //$act_reference_post_type
			
				$option_name = 'wri_reference2related_items__' . $act_reference_post_type->name . '--' . $act_related_post_type->name; // Option name
			
				//register_setting( $option_group, $option_name, $sanitize_callback );
		        register_setting(

		            $tabpage, // Option group
		            $option_name, // Option name: wri_reference2related_items__yyy--xxx 2x_ 2x-
		            array( $this, 'sanitize' ) // Sanitize
		        );			            
			
				//add_settings_section( $id, $title, $callback, $page );
				add_settings_section(
				    'wri_reference2related_items', // ID
				    __('reference Items','wp-related-items') . $act_related_post_type->labels->name, // Title
				    array( $this, 'print_section_info' ), // Callback
				    $tabpage // Page
				);  		
	
		        add_settings_field( //HIDDEN
		            'reference_posttype', // ID
		            __('reference Posttype','wp-related-items'), // Title
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );      

		        add_settings_field( //HIDDEN
		            'related_posttype', // ID
		            __('Related post type','wp-related-items'), // Title
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );      
	
	
		        add_settings_field(
		            'order', // ID
		            __('Order','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   
				
		        add_settings_field(
		            'display_limit', // ID
		            __('Limit number of displayed related items','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );      
	
		        add_settings_field(
		            'match_threshold', // ID
		            __('Match threshold','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );      
	
		        add_settings_field(
		            'position', // ID
		            __('Position','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   

		        add_settings_field(
		            'position_in_archives', // ID
		            __('Position in Archives (pro)','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   

		        add_settings_field(  //Display orderb of blocks
		            'display_order', // ID
		            __('Display Order','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   

		        add_settings_field(
		            'list_thumbnail', // ID
		            __('List Thumbnail','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   

		        add_settings_field(
		            'custom_template', // ID
		            __('Custom template','wp-related-items'), // Title 
		            array( $this, 'posttype_callback' ), // Callback
		            $tabpage, // Page
		            'wri_reference2related_items' // Section           
		        );   
												
			}
						
		}

    }

    public function wri_set_defaults() {
    	
		global $wri;
		$this->wri_supported_post_types = $wri->wri_supported_post_types('objects');
		//we set all WRI supported post types, not only used post types (that users set) 
		
		/* It is possible to set yarpp settings of wri taxonomies weight automaticaly, but if these are switchd off later by user, this code set them back unnecessarily.
		$option_name = 'yarpp';
		
		$options = get_option( $option_name );

		$options['weight']['tax'] = wp_parse_args( $options['weight']['tax'], array(
			'wri_category' => '3',
			'wri_manual_relationships' => '3', 
		) );
	
		update_option( $option_name, $options );
		*/
		
		$option_name = 'wri_general_settings';
		
		$options = get_option( $option_name ); 

		
		
		$options = wp_parse_args( $options, array(
			'enable_plugin' => '',
			'thumbnail_width' => '', 
			'thumbnail_height' => '',
			'wri_maual_relationships_weight' => '100',
            'use_yarpp_title' => '0',
            'dequeue_style_yarppRelatedCss' => '0',
            'dequeue_style_yarppWidgetCss' => '0',
            'disable_styles_wri_thumbnails_woocommerceCss' => '0',
			'promote' => '0',
			'hide_woocommerce_related_products' => '0', 
			'category_column_width_on_list_tables' => '',
		) );
		
		update_option( $option_name, $options );

        foreach ( $this->wri_supported_post_types as $act_related_post_type ) {

        	$option_name = 'wri_related_items___' . $act_related_post_type->name; // Option name

        	$options = get_option( $option_name ); 
	
			$options = wp_parse_args( $options, array(
				'title' => sprintf(__('Related %s'), $act_related_post_type->labels->name),
				'before_title_tags' => '<h3>', 
				'after_title_tags' => '</h3>',
				'no_result_display' => '',
				'display_limit' => '',
				'match_threshold' => '',
				'thumbnail_columns_number' => '4',
			));
			
			update_option( $option_name, $options );
        	        	
		}		
		


        foreach ( $this->wri_supported_post_types as $act_reference_post_type ) {				

			$posttypes = $this->wri_supported_post_types;

			$i = 1;

	        foreach ($posttypes as $act_related_post_type) {
	        	
				$i = $i + 1;

	        	$option_name = 'wri_reference2related_items__' . $act_reference_post_type->name . '--' . $act_related_post_type->name; // Option name

				$options = get_option( $option_name ); 
		
				$options = wp_parse_args( $options, array(
					'reference_posttype' => $act_reference_post_type->name,
					'related_posttype' => $act_related_post_type->name,
					'order' => '',
					'position' => 'bottom',
					'position_in_archive' => 'just_on_widget',
					'display_order' => $i,
					'list_thumbnail' => 'thumbnail',
				) );
				
				update_option( $option_name, $options );
	        		        	
			}		
		}
		
					//delete wri options
					//delete from em_options where option_name like 'wri_gen%' or option_name like 'wri_rel%' or option_name like 'wri_ref%'			
		
    }

	
    /**
     * Options page callback
     */
    public function add_options_page_callback()
    {
		global $wri_is_premium;

        //check if yarpp is installed and active
    	if ( ! is_plugin_active( 'yet-another-related-posts-plugin/yarpp.php' ) ) {
    		showAdminMessage(__('This plugin is based on Yet Another Related Posts Plugin (YARPP) and complement its functionality. Please install and activate <a href="http://wordpress.org/plugins/yet-another-related-posts-plugin/" target="_blank">Yet Another Related Posts Plugin</a> first.','wp-related-items'), true);
		}
        
        ?>

        <div class="wrap">
            <?php //screen_icon(); ?>
            <h2><?php _e('WP Related Items (WRI) Settings - WebshopLogic','wp-related-items') ?></h2>    


			<?php
	    	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'wri_general_settings_tabpage';  //if empty this is the default			
			?>


			<!--define tabs-->
	
			<h2 class="nav-tab-wrapper">

			<!--General tab-->
			<?php echo '<a href="?page=wsl-post-relater-products&tab=wri_general_settings_tabpage" class="nav-tab '; echo $active_tab == 'wri_general_settings_tabpage' ? 'nav-tab-active' : ''; printf('">' . __('General','wp-related-items') . '</a>'); ?>			

			<!--Related tab-->
			<?php echo '<a href="?page=wsl-post-relater-products&tab=wri_related_item_types_tabpage" class="nav-tab '; echo $active_tab == 'wri_related_item_types_tabpage' ? 'nav-tab-active' : ''; printf('">' . __('Related Types','wp-related-items') . '</a>'); ?>
							
			<?php
			
			foreach ( $this->wri_used_post_types as $act_related_post_type ) {				
				
				?>
				<!--Reference tabs-->
			    <a href="?page=wsl-post-relater-products&tab=wri_related_items_settings_tabpage_<?php echo $act_related_post_type -> name ?>" class="nav-tab <?php echo $active_tab == ('wri_related_items_settings_tabpage_' . $act_related_post_type->name) ? 'nav-tab-active' : ''; ?>"><?php printf(__('%s Page','wp-related-items'),$act_related_post_type->labels->name) ?></a>  
				<?php 
			} //end foreach 
			?>

			<!--YARP tab-->
			<?php echo '<a href="?page=wsl-post-relater-products&tab=wri_yarpp_settings_tabpage" class="nav-tab '; echo $active_tab == 'wri_yarpp_settings_tabpage' ? 'nav-tab-active' : ''; printf('">' . __('Essential Yarpp Settings','wp-related-items') . '</a>'); ?>

			</h2>  

			<!--make sidebar for upgrade pro version context-->
			<?php if( $active_tab == 'wri_general_settings_tabpage' or $active_tab == 'wri_related_item_types_tabpage' or $active_tab == 'wri_yarpp_settings_tabpage') { ?>              

				<div style="float:left; width: 70%">
			
			<?php } else { ?>              
			
				<div style="float:left; width: 100%">
			
			<?php } ?>              	
	
            <form method="post"  action="options.php"><!--form-->  
	            
				<?php

				//do_settings_sections( 'wri_general_settings' ); 

        		if( $active_tab == 'wri_general_settings_tabpage' ) {  	

					settings_fields( $active_tab );					
					$this->options = get_option( 'wri_general_settings' ); //option_name

					?>
					<h3><?php _e('WP Related Items (WRI) general settings','wp-related-items') ?></h3>
					<?php echo __('Enter your settings below','wp-related-items') . ':' ?>
	
					<!-- GENERAL TAB -->
	
					<table class="form-table">

						<tr valign="top">
							<th scope="row"><?php echo __('Enable WRI plugin','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[enable_plugin]" value="0"/>
						            <input type="checkbox" id="enable_plugin" name="wri_general_settings[enable_plugin]"
					            	value="1"' . checked( 1, isset( $this->options['enable_plugin'] ) and $this->options['enable_plugin'], false ) . ' />'
								);
								echo '<br /><span class="description">' . __('Turns on WRI plugin. Turning on WRI plugin, you may want to turn off the "Automatically display" option of Yarpp plugin. In this case, YARPP is running in the background.','wp-related-items') . '</span>';
							    ?>    
							</td>
						</tr>							

						<tr valign="top">
							<th scope="row"><?php echo __('Thumbnail width / height','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						            '<input type="number" id="thumbnail_width" name="wri_general_settings[thumbnail_width]" value="%s" min="1" max="9999"/>',
						            esc_attr( $this->options['thumbnail_width'])
						        );
								printf(' / ');
						        printf(
						            '<input type="number" id="thumbnail_height" name="wri_general_settings[thumbnail_height]" value="%s"  min="1" max="9999"/>',
						            esc_attr( $this->options['thumbnail_height'])
						        );
								echo '<br /><span class="description">' . __('Operation of thumbnail size settings are template dependent.','wp-related-items') . '</span>';
								
							    ?>    
							</td>
						</tr>							

						<tr valign="top">
							<th scope="row"><?php echo __('Enable manual settings of WRI relationships','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[enable_wri_manual_relationships]" value="0"/>
						            <input type="checkbox" id="enable_wri_manual_relationships" name="wri_general_settings[enable_wri_manual_relationships]"
					            	value="1"' . checked( 1, isset( $this->options['enable_wri_manual_relationships'] ) and $this->options['enable_wri_manual_relationships'], false ) . ' />'
								);
							    echo '<br /><span class="description">' . __('This option allows you manual assignment of items (e.g. assign some product to a post). Assignment can be made on post edit pages, if this checkbox is on. (pro)','wp-related-items') . '</span>';
							    ?>    
							</td>
						</tr>
						
						<?php
						?>							

						<tr valign="top">
							<th scope="row"><?php echo __('Enable Similarity Marker Categories (pro)','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[enable_wri_category]" value="0"/>
						            <input type="checkbox" id="enable_wri_category" name="wri_general_settings[enable_wri_category]"
					            	value="1"' . checked( 1, isset( $this->options['enable_wri_category'] ) and $this->options['enable_wri_category'], false ) . ($wri_is_premium ? '' : 'disabled') . ' />'
								);
								echo '<br /><span class="description">' . __('WRI Similarity Marker Categories are special categories for setting relationships between different elements, helping to increase the correlation between them. If you switch it on, the categorization metabox appears on edit pages of all used post types.','wp-related-items') . '</span>';
							    ?>
							</td>
						</tr>							

						<tr valign="top">
							<th scope="row"><?php printf(__('Post types used by WRI','wp-related-items')) ?> </th>
							<td>
							<?php        

							foreach ( $this->wri_supported_post_types as $act_related_post_type ) {

						        printf(
						            //dont need hidden 0 value
						            '<input type="checkbox" id="wri_used_posttypes[' . $act_related_post_type -> name . ']" name="' . 'wri_general_settings' . '[wri_used_posttypes][' . $act_related_post_type -> name. ']"
					            	value="1"' . checked( 1, isset( $this->options['wri_used_posttypes'][$act_related_post_type -> name] ) and $this->options['wri_used_posttypes'][$act_related_post_type -> name], false ) . ' />'
								);
								printf( '<label for="wri_used_posttypes[' . $act_related_post_type -> name . ']"> ' . $act_related_post_type->labels->name . '</label><br>' );
								
							}
							echo '<br /><span class="description">' . __('You can select the post types you want to be treated. IMPORTANT: if you change this setting, you may need to save YARPP settings again in Settings -> Related Posts (YARPP) menu item, to refresh them.','wp-related-items') . '</span>';
							?>

							</td>
							
						</tr>

						<tr valign="top">
							<th scope="row"><?php echo __('Use Yarpp tilte if WRI title is not set','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[use_yarpp_title]" value="0"/>
						            <input type="checkbox" id="use_yarpp_title" name="wri_general_settings[use_yarpp_title]"
					            	value="1"' . checked( 1, isset( $this->options['use_yarpp_title'] ) and $this->options['use_yarpp_title'], false ) . ' />'
								);
							    ?>    
							</td>
						</tr>	

						<tr valign="top">
							<th scope="row"><?php echo __('Disable YARPP related.css stylesheet','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[dequeue_style_yarppRelatedCss]" value="0"/>
						            <input type="checkbox" id="dequeue_style_yarppRelatedCss" name="wri_general_settings[dequeue_style_yarppRelatedCss]"
					            	value="1"' . checked( 1, isset( $this->options['dequeue_style_yarppRelatedCss'] ) and $this->options['dequeue_style_yarppRelatedCss'], false ) . ' />'
								);
								echo '<br /><span class="description">' . __('Turn this on for use custom styles instead of YARPP standard related stylesheet.','wp-related-items') . '</span>';
							    ?>    
							</td>
						</tr>	
        
						<tr valign="top">
							<th scope="row"><?php echo __('Disable YARPP widget.css stylesheet','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[dequeue_style_yarppWidgetCss]" value="0"/>
						            <input type="checkbox" id="dequeue_style_yarppWidgetCss" name="wri_general_settings[dequeue_style_yarppWidgetCss]"
					            	value="1"' . checked( 1, isset( $this->options['dequeue_style_yarppWidgetCss'] ) and $this->options['dequeue_style_yarppWidgetCss'], false ) . ' />'
								);
								echo '<br /><span class="description">' . __('Turn this on for use custom styles instead of YARPP standard widget stylesheet.','wp-related-items') . '</span>';								
							    ?>    
							</td>
						</tr>	

						<tr valign="top">
							<th scope="row"><?php echo __('Disable WRI styles-wri-thumbnails_woocommerce.css stylesheet','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[disable_styles_wri_thumbnails_woocommerceCss]" value="0"/>
						            <input type="checkbox" id="disable_styles_wri_thumbnails_woocommerceCss" name="wri_general_settings[disable_styles_wri_thumbnails_woocommerceCss]"
					            	value="1"' . checked( 1, isset( $this->options['disable_styles_wri_thumbnails_woocommerceCss'] ) and $this->options['disable_styles_wri_thumbnails_woocommerceCss'], false ) . ' />'
								);
								echo '<br /><span class="description">' . __('Turn this on for use custom styles instead of WRI standard thumbnail stylesheet for WooCommerce.','wp-related-items') . '</span>';								
							    ?>    
							</td>
						</tr>	

						<?php
						if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
						?>

							<tr valign="top">
								<th scope="row"><?php echo __('Hide related products displayed by WooCommerce','wp-related-items') . ':' ?></th>
								<td>
									<?php
							        printf(
							        	'<input type="hidden" name="wri_general_settings[hide_woocommerce_related_products]" value="0"/>
							            <input type="checkbox" id="hide_woocommerce_related_products" name="wri_general_settings[hide_woocommerce_related_products]"
						            	value="1"' . checked( 1, isset( $this->options['hide_woocommerce_related_products'] ) and $this->options['hide_woocommerce_related_products'], false ) . ' />'
									);
									echo '<br /><span class="description">' . __('WooCommerce automatically show some related products. Set this option on to hide it, to avoid duplication of WRI and WooCommerce related items.','wp-related-items') . '</span>';									
								    ?>    
								</td>
							</tr>	

						<?php
						}
						?>

						<?php
						?>							

									
						<tr valign="top">
							<th scope="row"><?php echo __('Promote WRI','wp-related-items') . ':' ?></th>
							<td>
								<?php
						        printf(
						        	'<input type="hidden" name="wri_general_settings[promote]" value="0"/>
						            <input type="checkbox" id="promote" name="wri_general_settings[promote]"
					            	value="1"' . checked( 1, isset( $this->options['promote'] ) and $this->options['promote'], false ) . ' />'
								);
							    ?>    
							</td>
						</tr>	

					</table>

					<?php
				
				} else if ( $active_tab == 'wri_related_item_types_tabpage' ) {

				// RELATED TAB

					if (empty($this->wri_used_post_types)) {
						echo  ('<br>');
						printf(__('Please set at least one post type in <i>Post types used by WRI</i> field of <i>General</i> tab.','wp-related-items') );						
					}					
					
					foreach ( $this->wri_used_post_types as $act_related_post_type) {				

						$active_post_type = $act_related_post_type->name; 
						
						$option_name = 'wri_related_items___' . $act_related_post_type->name; // option name is e.g. wri_related_items___post (3x_)
						
						settings_fields( $active_tab ); 
						$this->options = get_option( $option_name ); 
						
						printf('<h2>' . __('Related <i>%s</i>','wp-related-items'). '</h2>', $act_related_post_type->labels->name);
	
						
						printf(__('The settings below overwrite YARPP default display settings specially for <b><i> %s items</i></b>.','wp-related-items'), $act_related_post_type->labels->singular_name . ' '); 
						printf(__('Leave these settings empty if YARPP default settings are appropriate for %s items.','wp-related-items'), $act_related_post_type->labels->singular_name); 
		
				        printf(
				            '<input type="hidden" id="posttype" name="' . $option_name . '[posttype]" value="%s" />',
				            $act_related_post_type->name 
						);
						?>

						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php echo __('Custom title','wp-related-items') . ':' ?></th>
								<td>
									<?php
							        printf(
							            '<input type="html" id="title" name="' . $option_name . '[title]" value="%s" />',
							            esc_attr( $this->options['title'])
							        );
									echo '<br /><span class="description">' . __('Specify a unique title for this type of related posts. (You can see the post type in the header of this section.) Leave the field empty if YARPP default title setting is right for this post type.','wp-related-items') . '</span>';
								    ?>
								</td>
							</tr>							
	
							<tr valign="top">
								<th scope="row"><?php echo __('Before / after title HTML tags','wp-related-items') . ':' ?></th>
								<td>
									<?php
							        printf(
							            '<input type="text" id="before_title_tags" name="' . $option_name . '[before_title_tags]" value="%s" size="10" />',
							            esc_attr( isset ($this->options['before_title_tags']) ? $this->options['before_title_tags'] : '<h3>' )
							        );
							        printf(
							            '<input type="text" id="after_title_tags" name="' . $option_name . '[after_title_tags]" value="%s" size="10" />',
							            esc_attr( isset ($this->options['after_title_tags']) ? $this->options['after_title_tags'] : '<h3>' )
							        );
								    ?>
								</td>
							</tr>							
	
							<tr valign="top">
								<th scope="row"><?php echo __('Default display if no results','wp-related-items') . ':' ?></th>
								<td>
									<?php
							        printf(
							            '<input type="text" id="no_result_display" name="' . $option_name . '[no_result_display]" value="%s" />',
							            esc_attr( $this->options['no_result_display'])
							        );
								    ?>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row"><?php echo __('Number of thumbnail columns (pro)','wp-related-items') . ':' ?></th>
								<td>
									<?php
							        printf(
							            '<input type="number" id="thumbnail_columns_number" name="' . $option_name . '[thumbnail_columns_number]" value="%s" min="2" max="5"' . ($wri_is_premium ? '' : 'disabled') . '/>',
							            esc_attr( $this->options['thumbnail_columns_number'])
							        );
									echo '<br /><span class="description">' . __('Operation of these settings is template dependent.','wp-related-items') . '</span>';
								    ?>
								</td>
							</tr>	
								
							<?php
							$taxonomies = array();
							$taxonomies = array_merge($taxonomies, get_taxonomies( array('name' => 'category'), 'objects' ));
							$taxonomies = array_merge($taxonomies, get_taxonomies( array('name' => 'post_tag'), 'objects' ));
							
							//add all show_ui type category
							$args = array(
  								//'yarpp_support'	=> true,
  								'show_ui'		=> true,	
  							);
							
							$taxonomies = array_merge($taxonomies, get_taxonomies( $args, 'objects' ));
							?>
							
							<tr valign="top">
								<th scope="row"><?php printf(__('Show selected cross taxonomy metaboxes on %s admin page (pro)','wp-related-items') . ':', $act_related_post_type->labels->name) ?> </th>
								<td>
								<?php        

								//CROSS TAXONOMY SETTINGS    
    							foreach ($taxonomies as $taxonomy) {
	    							
									//if not WRI own taxonomy (e.g. not wri_category)	
									if (! $taxonomy->wri_own) {					
	    								
	    								
								        printf(
								        	//dont need hidden 0 value
								            '<input type="checkbox" id="cross_taxonomies[' . $taxonomy->name . ']" name="' . $option_name . '[cross_taxonomies][' . $taxonomy->name . ']"
							            	value="1"' . checked( 1, isset( $this->options['cross_taxonomies'][$taxonomy->name] ) and $this->options['cross_taxonomies'][$taxonomy->name], false ) . ($wri_is_premium ? '' : 'disabled') . ' />'
										);
										printf( '<label for="cross_taxonomies[' . $taxonomy->name . ']"> ' . $taxonomy->labels->name . '</label><br>' );
									
									}									
									
								}
								echo '<br /><span class="description">' . __('Cross taxonomies can be used to increase similarities using common categorization between different post types. For example, you can switch on WordPress standard post categories for products, so the post category metabox appears on products admin page. In this way, different post types can be  in the same category, increasing the similarity rates.','wp-related-items') . '</span>';
								?>
								</td>
							</tr>			
												
						</table>
	
						<?php
						
					} //end for						
					
				} else if ( $active_tab == 'wri_yarpp_settings_tabpage' ) {
					
					$admin_url = get_admin_url();
					$yarpp_setting_page_url = $admin_url . 'options-general.php?page=yarpp';
					
					?>
					<h3><?php echo (__('Essential YARPP Settings','wp-related-items') . ':') ?></h3>
					<?php echo __('Using WRI plugin, YARPP similarity search service works in the background.','wp-related-items') . '<br>'
					. '<span style="color:red"><b>' . __('It is important to apply right YARPP settings for proper results!','wp-related-items') . '</b></span>' . '<br>'
					. __('Choose <i>Settings -> YARPP</i> item in WordPress Admin menu to setup YARPP options.','wp-related-items') ?>
	
					<!-- YARPP TAB -->
	
					<table class="form-table">

						<tr valign="top">
							<th scope="row"><?php echo __('"Relatedness" options','wp-related-items') . ':' ?></th>
							<td>
								<?php
								//echo '<br /><span class="description">' . __('Set all options of similarity evaulation, especially for consideration of custom post types categories and tags in <i>"Relatedness" options</i> section. Use <i>Screen Options</i> button on the upper right side of YARPP options page and check <i>"Relatedness" options</i> checkbox to display this section.','wp-related-items') . '</span>';
								echo '<br /><span class="description">' 
									. __('On the YARPP setting page, go to the "Relatedness Options" section.','wp-related-items') . '<br>'
									. __('Here you need to set what data the search consider or consider with extra weight, e.g. titles, bodies, standard or custom categories.') . '<br>'
									. '<span style="color:red"><b>' . __('For all the data you need, set it to consider the YARPP search engine!') . '</b></span>' . '<br>'
									. '<b>' . __('It is important to set "Consider" or "Consider with extra weight" for "WRI Similarity Marker Categories"!') . '</b>' . '<br>'
									. '<b>' . __('It is important to set "Consider" or "Consider with extra weight" for custom categories (if you use it)!') . '</b>' . '<br>'
									. '<b>' .__('It is also important to set "Consider with extra weight" for "Manual Relationships"!') . '</b>' . '<br>'
									. __('(If the "Relatedness options" section is not visible on the YARPP settings page, use the Screen Options button in the upper right corner of the screen and select the "Relatedness options" check box to display this section.)') . '<br>'
									. '</span>';
								
							    ?>    
							</td>
						</tr>							

						<tr valign="top">
							<th scope="row"><?php echo __('Automatically display', 'wp-related-items') . ':' ?></th>
							<td>
								<?php
								echo '<br /><span class="description" style="color:red"><b>' . __('Turn off Automatically display checkbox to disable YARPP display of related posts. In this case WRI displays related items, while YARPP works in the background.','wp-related-items') . '</b></span>';
							    ?>    
							</td>
						</tr>							

					</table>

					<?php

					echo __('Tip: If everything is set but no results are displayed, it often helps to go to the YARPP settings page and just press the "Save Changes" button without changing the settings.') . '<br><br>';
	
					echo '<a href="' . $yarpp_setting_page_url . '" class="wli_pro" target="_blank">' . __('Click here to go to YARPP Settings Page', 'wp-related-items') . '</a>';
					
				} else {
					
					$active_post_type = substr ( $active_tab, strlen ( 'wri_related_items_settings_tabpage_' ) ); //get the active post type name from the end of the active_tab name 
					$act_reference_post_type = get_post_type_object( $active_post_type );
					
					
					settings_fields( $active_tab ); 
				
					//Related Items
					printf(__('<h3>Related items on %s reference pages</h3>','wp-related-items'), $act_reference_post_type->labels->singular_name);

					printf('<span class="description">' 
							. __('On reference item tabs you can set how to display related items for the item type appointed by the actual tab. For example, if you want to set how to display related PRODUCTS for POSTS, you can use settings in PRODUCTS row on POST Reference Items tab. Such a matrix-like way you can specify all necessary variations of display settings.', 'wp-related-items') 
							. '</span><br><br>');
					printf(__('Related items display setting on <b><i>%1s pages</i></b> and <b><i>%2s archive pages</i></b>','wp-related-items') . ':'
						, $act_reference_post_type->labels->singular_name, $act_reference_post_type->labels->singular_name
					);
					?>
					
					<!-- OTHER TABS -->
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><b><?php _e('Related post type','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('Order','wp-related-items') ?></b></th>
							
							<th scope="row"><b><?php _e('Limit number of displayed related items','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('Match threshold','wp-related-items') ?></b></th>
							
							<th scope="row"><b><?php _e('Position','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('Position in Archives (pro)','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('Display order of blocks','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('List/Thumbnail','wp-related-items') ?></b></th>
							<th scope="row"><b><?php _e('Yarpp custom Template','wp-related-items') ?></b></th>
						</tr>
					
						<?php
						foreach ( $this->wri_used_post_types as $act_related_post_type ) {

							$option_name = 'wri_reference2related_items__' . $act_reference_post_type->name . '--' . $act_related_post_type->name; // option name is e.g. wri_reference2related_items__yyy--xxx 2x_ 2x-

							$this->options = get_option( $option_name ); 

							?>	

					        <?php
					        printf(
					            '<input type="hidden" id="reference_posttype" name="' . $option_name . '[reference_posttype]" value="%s" />',
					            $act_reference_post_type->name 
					            //esc_attr( $this->options['reference_posttype'])
							);
							?>
										
					        <?php
					        printf(
					            '<input type="hidden" id="related_posttype" name="' . $option_name . '[related_posttype]" value="%s" />',
					            $act_related_post_type->name 
					            //esc_attr( $this->options['related_posttype'])
							);
							?>										
										
							<tr valign="top">

								<td>
								<?php echo $act_related_post_type->labels->name ?>

								</td>

								<td>
								<?php
						        printf(
						            '<select id="order" name="' . $option_name . '[order]" value="%s" />
							            <option value=""' . selected( '', esc_attr( $this->options['order']), false) . '> </option>
							            <option value="score DESC"' . selected( 'score DESC', esc_attr( $this->options['order']), false) . '>' . __('score (high relevance to low)','wp-related-items') . '</option>
							            <option value="score ASC"' . selected( 'score ASC', esc_attr( $this->options['order']), false) . '>' . __('score (low relevance to high)','wp-related-items') . '</option>
							            <option value="post_date DESC"' . selected( 'post_date DESC', esc_attr( $this->options['order']), false) . '>' . __('date (new to old)','wp-related-items') . '</option>
							            <option value="post_date ASC"' . selected( 'post_date ASC', esc_attr( $this->options['order']), false) . '>' . __('date (old to new)','wp-related-items') . '</option>
							            <option value="post_title ASC"' . selected( 'post_title ASC', esc_attr( $this->options['order']), false) . '>' . __('title (alphabetical)','wp-related-items') . '</option>
							            <option value="post_title DESC"' . selected( 'post_title DESC', esc_attr( $this->options['order']), false) . '>' . __('title (reverse alphabetical)','wp-related-items') . '</option>
						            </select>',
						            esc_attr( $this->options['order'])
						        );
							    ?>
								</td>
								
								<td>
								<?php
						        printf(
						            '<input type="number" id="display_limit" name="' . $option_name . '[display_limit]" value="%s" min="0" max="999" />',
						            esc_attr( $this->options['display_limit'])
						        );
							    ?>
								</td>

								<td>
								<?php
						        printf(
						            '<input type="number" id="match_threshold" name="' . $option_name . '[match_threshold]" value="%s" min="0" max="999" />',
						            esc_attr( $this->options['match_threshold'])
						        );
							    ?>
								</td>
							
								<td>
								<?php
						        printf(
						            '<select id="position" name="' . $option_name . '[position]" />
							            <option value="top"' . selected( 'top', esc_attr( $this->options['position']), false) . ($wri_is_premium ? '' : 'disabled') . '>' . __('Top of page','wp-related-items') . '</option>
							            <option value="bottom"' . selected( 'bottom', esc_attr( $this->options['position']), false) . '>' . __('Bottom of page','wp-related-items') . '</option>'
							    );        
								if ( is_plugin_active( 'woocommerce/woocommerce.php' ) and $wri_is_premium and $act_reference_post_type->name == 'product' ) {
									 printf(
										'<option value="wc_product"' . selected( 'wc_product', esc_attr( $this->options['position']), false) . '>' . __('WooCommerce Product page','wp-related-items') . '</option>'
									);
								} 
								printf(
							        '<option value="just_on_widget"' . selected( 'just_on_widget', esc_attr( $this->options['position']), false) . '>' . __('None or just widgets','wp-related-items') . '</option>
						            </select>'
						        );
							    ?>
								</td>

								<td>
								<?php
						        printf(
						            '<select id="position_in_archive" name="' . $option_name . '[position_in_archive]" />
						            <option value="top"' . selected( 'top', esc_attr( $this->options['position_in_archive']), false) . ($wri_is_premium ? '' : 'disabled') . '>' . __('Top of items','wp-related-items') . '</option>
						            <option value="bottom"' . selected( 'bottom', esc_attr( $this->options['position_in_archive']), false) . ($wri_is_premium ? '' : 'disabled') . '>' . __('Bottom of items','wp-related-items') . '</option>
						            <option value="just_on_widget"' . selected( 'just_on_widget', esc_attr( $this->options['position_in_archive']), false) . '>' . __('None or just widgets','wp-related-items') . '</option>
						            </select>'
						        );
							    ?>
								</td>
								
								<td>
								<?php
						        printf(
						            '<input type="number" id="display_order" name="' . $option_name . '[display_order]" value="%s" min="0" max="9999" />',
						            esc_attr( $this->options['display_order'])
						        );
							    ?>
								</td>

								<td>
								<?php
						        printf(
						            '<select id="list_thumbnail" name="' . $option_name . '[list_thumbnail]" value="%s" />
						            <option value="list"' . selected( 'list', esc_attr( $this->options['list_thumbnail']), false) . '>' . __('List','wp-related-items') . '</option>
						            <option value="thumbnail"' . selected( 'thumbnail', esc_attr( $this->options['list_thumbnail']), false) . '>' . __('Thumbnail','wp-related-items') . '</option>
						            <option value="yarpp_thumbnail"' . selected( 'yarpp_thumbnail', esc_attr( $this->options['list_thumbnail']), false) . '>' . __('According to YARPP display options','wp-related-items') . '</option>
						            </select>',
						            esc_attr( $this->options['list_thumbnail'])
						        );
							    ?>
								</td>

								<td>
								<?php
								
								global $yarpp;
								$yarpp_templates_data_array = $yarpp->get_templates();
								
						        printf(
						            '<select id="custom_template" name="' . $option_name . '[custom_template]" value="%s" />
							            <option value=""' . selected( '', esc_attr( $this->options['custom_template']), false) . '> </option>', 
												esc_attr( $this->options['custom_template'])
								);

									if ( is_array( $yarpp_templates_data_array ) ) {
							            foreach ( $yarpp_templates_data_array as $yarpp_templates_data ) {
										
											printf('<option value="' . $yarpp_templates_data['basename'] . '"' . selected( $yarpp_templates_data['basename'], esc_attr( $this->options['custom_template']), false) . '>' . $yarpp_templates_data['name'] . '</option>');
														
										}
									}

			            
						        printf('</select>');
						            
							    ?>
								</td>
								
							</tr>
																
							<?php						
						}				
				
						?>
				
					</table>
					
 					<table class="form-table">
						<tr valign="top">
							 <h3><?php _e('Field Descriptions','wp-related-items') ?></h3>
							 </tr>
						<tr valign="top">
							<th scope="row"><?php _e('Order','wp-related-items') ?></th>
							<th  scope="row"><!--Order-->					<?php echo '<span class="description">' . __('Specify the order of displayed items for this type of related posts. Leave the field empty if YARPP default order setting is right for this post type.','wp-related-items') . '</span>'; ?></th>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Limit number of displayed related items','wp-related-items') ?></th>
							<th  scope="row"><!--Limit-->					<?php echo '<span class="description">' . __('Specify a unique limit of displayed items for this type of related posts. Leave the field empty if YARPP default limit setting is right for this post type.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Match threshold','wp-related-items') ?></th>
							<th  scope="row"><!--Threshold-->					<?php echo '<span class="description">' . __('Specify a unique threshold for this type of related posts. Leave the field empty if YARPP default threshold setting is right for this post type. The lover limit may cause a higher hit number.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Position','wp-related-items') ?></th>
							<th  scope="row"><!--Position-->				<?php echo '<span class="description">' . __('Position of items on single item pages.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Position in Archives','wp-related-items') ?></th>
							<th  scope="row"><!--Position in Archives-->	<?php echo '<span class="description">' . __('Position of items on archive item pages.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Display order of blocks','wp-related-items') ?></th>
							<th  scope="row"><!--Display order of blocks-->	<?php echo '<span class="description">' . __('You can set the order of blocks displayed on the same position. For example if you set related posts and products on the bottom of page, you can set the order of these two blocks.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('List/Thumbnail','wp-related-items') ?></th>
							<th  scope="row"><!--List/Thumbnail-->			<?php echo '<span class="description">' . __('You can set list or thumbnail mode, or to use settings in Yarpp plugin.','wp-related-items') . '</span>'; ?></th>							
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Yarpp custom Template','wp-related-items') ?></th>
							<th  scope="row"><!--Yarpp custom Template-->	<?php echo '<span class="description">' . __('Your own template files can be used. Your templates should be in main directory of your active theme, the file name must conform to following naming convention: yarpp-template-....php. Please find more details about templates in Yarpp documentations.)','wp-related-items') . '</span>'; ?>
							</th>
						</tr>
					</table>
					
					<?php
										
				} //end if

				if ($active_tab != 'wri_yarpp_settings_tabpage') {
					submit_button();
				}					 
				?>
				
				</div>

			</form><!--end form-->
			
			</div><!--emd float:left; width: 70% / 100% -->
			
			<?php 
			if (!$wri_is_premium) {

				if( $active_tab == 'wri_general_settings_tabpage' or $active_tab == 'wri_related_item_types_tabpage' or $active_tab == 'wri_yarpp_settings_tabpage') { 
					?>  			
	
					<div class="wri_admin_left_sidebar" style="float:right; ">
						
						<a href="http://webshoplogic.com/product/wp-related-items-wri-plugin/" class="wli_pro" target="_blank">
							<h2><?php _e('Upgrade to PRO version', 'wp-related-items'); ?></h2>
						</a>							
						
						<a href="http://webshoplogic.com/product/wp-related-items-wri-plugin/" class="wli_pro" target="_blank">
							<img src="<?php echo plugins_url('images/WLI_product_box_PRO_upgrade_right_v1_2e_235x235.png', __FILE__)?>" alt="Upgrade to PRO">
						</a>
						
						<ul>
							<b>
							<li><h3><?php _e('Cross taxonomies', 'wp-related-items'); ?></h3></li>
							<li><h3><?php _e('Common categories for relationships', 'wp-related-items'); ?></h3></li>
							<li><h3><?php _e('Manual relationship assignments', 'wp-related-items'); ?></h3></li>
							<li><?php _e('More positioning options', 'wp-related-items'); ?></li>
							<li><?php _e('Display related items on archive pages', 'wp-related-items'); ?></li>
							<li><?php _e('Configurable number of thumbnail columns', 'wp-related-items'); ?></li>
							<li><?php _e('Use different related item widget on different reference item types', 'wp-related-items'); ?></li>
							<li><?php _e('Use related item widget on archive pages', 'wp-related-items'); ?></li>
							</b>
						</ul>						
							
						<?php _e('Cross taxonomies can be used to increase similarities using common categorization between different post types. For example, you can switch on WordPress standard post categories for products, so the post category metabox appears on products admin page. In this way, different post types can be  in the same category, increasing the similarity rates.', 'wp-related-items'); ?>
						<br><br>
						<?php _e('Manual assignment of items is possible. This way you can define explicit relationship between different items (e.g. assign some product to a post or two related posts to each other)', 'wp-related-items'); ?>
							
						
					
					</div>
					<?php
				}
			} // endif
			?>
	
	
        </div> <!--#wrap-->

        <?php
    }	


    public function sanitize( $input )
    {
        if( isset($input['id_number']) and !is_numeric( $input['id_number'] ) )
            $input['id_number'] = '';  

        if( isset($input['title']) and !empty( $input['title'] ) )
            $input['title'] = sanitize_text_field( $input['title'] );

        return $input;
    }

    public function print_section_info()
    {
        print __('Enter your settings below') . ':';
    }

    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="wri_general_settings[id_number]" value="%s" />',
            esc_attr( $this->options['id_number'])
        );
    }

    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="wri_general_settings[title]" value="%s" />',
            esc_attr( $this->options['title'])
        );
    }

	function hide_footer() {
		echo '<style> #wpfooter {display: none;}</style>';
		return '';	
	}

}