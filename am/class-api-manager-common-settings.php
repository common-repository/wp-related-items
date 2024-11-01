<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists ( 'API_Manager_Common_Settings' ) ) {
	//This class should be load only once 
	class API_Manager_Common_Settings { //TBSET: Change the class names so they are unique to your plugin.
	
	    /**
	     * @var The single instance of the class
	     */
	    protected static $_instance = null;
	
	
	    public static function instance() {
	
	        if ( is_null( self::$_instance ) ) {
	        	self::$_instance = new self();
	        }
	
	        return self::$_instance;
	    }
	
		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.2
		 */
		public function __clone() {}
	
		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.2
		 */
		public function __wakeup() {}
	
		public function __construct() {
	
			add_action( 'admin_menu', array( $this, 'add_menu' ) );
			//add_action( 'admin_init', array( $this, 'load_settings' ) );
	
			//if ( is_admin() ) {}
	
		}
	
		// Add option page menu
		public function add_menu() {
	
			$page = add_options_page( AME_WRI()->ame_settings_common_settings_title, AME_WRI()->ame_settings_common_settings_menu_title,
							'manage_options', AME_WRI()->ame_common_settings_page_key, array( $this, 'config_page')
			);
	
		}
	
		// Draw option page
		public function config_page() {
	
			//wsl_log(null, 'WRI class-api-manager-common-settings.php config_page 0: ' . wsl_vartotext(''));
			
			?>
			<div class='wrap'>
				<?php //screen_icon(); ?>
				<h2><?php echo AME_WRI()->ame_settings_common_settings_title; ?></h2>
	
				<?php echo __( 'Choose the plugin that you want to activate or deactivate', AME_WRI()->text_domain ) . ':'; ?>
	
				<!-- Display the URL-s of the installed WebshopLogic plugins -->			
				<?php do_action( 'wsl_display_plugin_activation_settings_page_link' ); ?>
				
			</div>
			<?php
		}
	
	} // End of class
}

if ( ! function_exists ( 'AMESP' ) ) {
	function AMESP() {
	    return API_Manager_Common_Settings::instance(); 	//TBSET: API_Manager_Example - class name found inside the AME_WRI() function, must match the class name in the api-manager-example.php file.
	}
}

// Initialize the class instance only once
AMESP(); 											//TBSET IN-ALL-FILES: AME_WRI() - function. Do a find and replace throughout all the plugin example files. The functions makes the singular design pattern possible in the plugin example.
