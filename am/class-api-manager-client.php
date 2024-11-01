<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays an inactive message if the API License Key has not yet been activated
 */
//if ( get_option( 'api_manager_example_activated' ) != 'Activated' ) {
if ( get_option( AME_WRI()->ame_activated_key ) != 'Activated' ) { // MODIFY RaPe	
    //add_action( 'admin_notices', 'API_Manager_WRI::am_example_inactive_notice' );
    add_action( 'admin_notices', 'API_Manager_WRI::am_client_inactive_notice' );
}

class API_Manager_WRI { //TBSET: Change the class names so they are unique to your plugin.

	/**
	 * Self Upgrade Values
	 */
	// Base URL to the remote upgrade API Manager server. If not set then the Author URI is used.
	//public $upgrade_url = 'http://tl/'; 												//TBSET: string - The URL for your WooCommerce store, where the API listens for client requests. For example: https://www.toddlahman.com/
	public $upgrade_url = 'https://webshoplogic.com/'; 									//TBSET: string - The URL for your WooCommerce store, where the API listens for client requests. For example: https://www.toddlahman.com/

	/**
	 * @var string
	 */
	public $version = WRI_VERSION; //'1.3'; //TBSET: string - The current version of the software.

	/**
	 * @var string
	 * This version is saved after an upgrade to compare this db version to $version
	 */
	public $api_manager_client_version_name = 'plugin_api_manager_wri_version'; 	//TBSET: string - saved in the options table to determine the current version of the software to compare with a new version after a software upgrade to trigger an event.

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 * used to defined localization for translation, but a string literal is preferred
	 *
	 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/issues/59
	 * http://markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 */
	public $text_domain = 'api-manager-wri_text'; 										//TBSET: string - class variable, change the value. It is better to hardcode the text domain into your plugin.

	/**
	 * Data defaults
	 * @var mixed
	 */
	private $ame_software_product_id;

	public $ame_data_key;
	public $ame_api_key;
	public $ame_activation_email;
	public $ame_product_id_key;
	public $ame_instance_key;
	public $ame_deactivate_checkbox_key;
	public $ame_activated_key;	

	public $ame_deactivate_checkbox;
	public $ame_activation_tab_key;
	public $ame_deactivation_tab_key;
	public $ame_settings_menu_title;
	public $ame_settings_title;
	public $ame_menu_tab_activation_title;
	public $ame_menu_tab_deactivation_title;

	public $ame_options;
	public $ame_plugin_name;
	public $ame_product_id;
	public $ame_renew_license_url;
	public $ame_instance_id;
	public $ame_domain;
	public $ame_software_version;
	public $ame_plugin_or_theme;

	public $ame_update_version;

	/**
	 * Used to send any extra information.
	 * @var mixed array, object, string, etc.
	 */
	public $ame_extra;

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

		//wsl_log(null, 'WRI class-api-manager-client.php activation WRI_PLUGIN_FILE: ' . wsl_vartotext(WRI_PLUGIN_FILE));
		//wsl_log(null, 'WRI class-api-manager-client.php activation WRI_PLUGIN_BASENAME: ' . wsl_vartotext(WRI_PLUGIN_BASENAME));
		
		// Run the activation function
		//register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_activation_hook( WRI_PLUGIN_FILE, array( $this, 'activation' ) ); //TBSET // MODIFY RaPe: it is not in the plugin main file, so we need this 
		
		// Ready for translation
		////load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( __FILE__ ) ) ) . '/languages' );
		load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( WRI_PLUGIN_FILE ) ) ) . '/languages' ); //TBSET //MODIFY RaPe

		if ( is_admin() ) {

			// Check for external connection blocking
			add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

			/**
			 * Software Product ID is the product title string
			 * This value must be unique, and it must match the API tab for the product in WooCommerce
			 */
			$this->ame_software_product_id = WRI_NAME; //'WP Related Items PRO'; 										//TBSET: string - Must match exactly, case sensitive, the Product > API tab > Software Title, as shown here: http://docs.woothemes.com/wp-content/uploads/2013/09/api-manager-simple-product-api.png

			/**
			 * Set all data defaults here
			 */
			$this->ame_data_key 				= 'api_manager_wri'; 									//TBSET: string - The options table option_name that contains an option_value array which contains the ame_api_key and ame_activation_email.
			$this->ame_api_key 					= 'api_key'; 												//TBSET: string - option_value contained in the ame_data_key data array as mentioned above.
			$this->ame_activation_email 		= 'activation_email'; 										//TBSET: string - option_value contained in the ame_data_key data array as mentioned above.
			$this->ame_product_id_key 			= 'api_manager_wri_product_id'; 						//TBSET: string - Is set to the Software Title value retrieved from the options table using the option_name key from $this->ame_software_product_id
			$this->ame_instance_key 			= 'api_manager_wri_instance'; 							//TBSET: string - must be unique for each activation or install. A randomly generated password will suffice.
			$this->ame_deactivate_checkbox_key 	= 'api_manager_wri_deactivate_checkbox'; 				//TBSET: option_name key used to store the option_value for the deactivation checkbox.
			$this->ame_activated_key 			= 'api_manager_wri_activated'; 							//TBSET: string - Saved in the options table to determine if the software has been activated.

			/**
			 * Set all admin menu data
			 */
			$this->ame_deactivate_checkbox 			= 'am_deactivate_wri_checkbox'; 					//TBSET: string - Determines the status of the deactivated checkbox.
			$this->ame_activation_tab_key 			= 'api_manager_wri_dashboard'; 						//TBSET: string - Determines the query string part of the URL for the deactivation tab.
			$this->ame_deactivation_tab_key 		= 'api_manager_wri_deactivation'; 					//TBSET: string - Determines the query string part of the URL for the activation tab.
			$this->ame_settings_menu_title 			= __('WP Related Items PRO', 'api-manager-wri_text' );	//TBSET: string - Title on the settings menu.
			$this->ame_settings_title 				= __('WP Related Items PRO', 'api-manager-wri_text' ); 	//TBSET: string - Title on the settings page.
			$this->ame_menu_tab_activation_title 	= __( 'License Activation', 'api-manager-wri_text' ); 	//TBSET: string - Title for the activation tab.
			$this->ame_menu_tab_deactivation_title 	= __( 'License Deactivation', 'api-manager-wri_text' );	//TBSET: string - Title for the deactivation tab.
			
			// ADD RaPe BEGIN
			// We do not use activation setting page, Set Activatio Key link is on the plugin list
			//$this->ame_settings_common_settings_menu_title 	= __('WebshopLogic Activation', 'api-manager-wri_text' ); 				//TBSET: WSL
			//$this->ame_settings_common_settings_title 		= __('WebshopLogic Plugin Activation Page', 'api-manager-wri_text' ); 	//TBSET: WSL
			//$this->ame_common_settings_page_key 			= 'api_manager_wsl_common_settings_page'; 								//TBSET: WSL
			// ADD RaPe END

			/**
			 * Set all software update data here
			 */
			$this->ame_options 				= get_option( $this->ame_data_key );
			//$this->ame_plugin_name 			= untrailingslashit( plugin_basename( __FILE__ ) ); // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->ame_plugin_name 			= untrailingslashit( plugin_basename( WRI_PLUGIN_FILE ) ); //TBSET // MODIFY RaPe // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->ame_product_id 			= get_option( $this->ame_product_id_key ); // Software Title
			//TBSET: string - The URL to the customer's My Account dashboard. For example https://www.toddlahman.com/my-account/:
			$this->ame_renew_license_url 	= 'http://localhost/toddlahman/my-account'; // URL to renew a license. Trailing slash in the upgrade_url is required. 
			$this->ame_instance_id 			= get_option( $this->ame_instance_key ); // Instance ID (unique to each blog activation)
			/**
			 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
			 * so only the host portion of the URL can be sent. For example the host portion might be
			 * www.example.com or example.com. http://www.example.com includes the scheme http,
			 * and the host www.example.com.
			 * Sending only the host also eliminates issues when a client site changes from http to https,
			 * but their activation still uses the original scheme.
			 * To send only the host, use a line like the one below:
			 *
			 * $this->ame_domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			 */
			$this->ame_domain 				= str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			$this->ame_software_version 	= $this->version; // The software version
			$this->ame_plugin_or_theme 		= 'plugin'; // 'theme' or 'plugin'

			// Performs activations and deactivations of API License Keys
			//require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-key-api.php' ); // MODIFY RaPe
			require_once( plugin_dir_path( __FILE__ ) . 'classes/class-wc-key-api.php' ); // MODIFY RaPe
			
			// Checks for software updatess
			//require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-plugin-update.php' ); // MODIFY RaPe
			require_once( plugin_dir_path( __FILE__ ) . 'classes/class-wc-plugin-update.php' ); // MODIFY RaPe

			// Admin menu with the license key and license email form
			//require_once( plugin_dir_path( __FILE__ ) . 'am/admin/class-wc-api-manager-menu.php' ); // MODIFY RaPe
			require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wc-api-manager-menu.php' ); // MODIFY RaPe

			// Common settings page
			// We do not use activation setting page, Set Activatio Key link is on the plugin list
			//require_once( plugin_dir_path( __FILE__ ) . 'class-api-manager-common-settings.php' ); // MODIFY RaPe ADD
			
			$options = get_option( $this->ame_data_key );

			/**
			 * Check for software updates
			 */
			if ( ! empty( $options ) && $options !== false ) {

				$this->update_check(
					$this->upgrade_url,
					$this->ame_plugin_name,
					$this->ame_product_id,
					$this->ame_options[$this->ame_api_key],
					$this->ame_options[$this->ame_activation_email],
					$this->ame_renew_license_url,
					$this->ame_instance_id,
					$this->ame_domain,
					$this->ame_software_version,
					$this->ame_plugin_or_theme,
					$this->text_domain
					);

			}
			
			add_action( 'admin_init', array( $this, 'admin_init' ) ); // ADD RaPe
			
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2); // ADD RaPe

		}

		/**
		 * Deletes all data if plugin deactivated
		 */
		//register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );
		register_deactivation_hook( WRI_PLUGIN_FILE, array( $this, 'uninstall' ) ); //TBSET // MODIFY RaPe
		

	}

	// ADD RaPe BEGIN
	// admin_init
	public function admin_init() { 
	
		////wsl_log(null, 'WRI class-api-manager-client.php admin_init 0: ' . wsl_vartotext('')); // wsl_log is not available here
		
		// We do not use activation setting page, Set Activatio Key link is on the plugin list
		//add_action( 'wsl_display_plugin_activation_settings_page_link', array( $this, 'display_plugin_activation_settings_page_link' ), 20, 0 );
	
	}
	
	// We do not use activation setting page, Set Activatio Key link is on the plugin list
	/*public function display_plugin_activation_settings_page_link() {
		
		// Display a link like this for this plugin activation page:
		// http://yoursite.com/wp-admin/admin.php?page=api_manager_wri_dashboard
		
		?>
			
			<p><a href="<?php echo admin_url() . 'admin.php?page=' . $this->ame_activation_tab_key; ?>">
				<?php echo $this->ame_settings_title; ?>
			</a></p>
				
		<?php
	}*/

	//Add Activation link to plugin item of plugin list	
	public function plugin_action_links( $links, $file ) {

		if ( $file == WRI_PLUGIN_BASENAME ) {
			$new_links = array(
				'activate_license' => '<a href="' . admin_url() . 'admin.php?page=' . $this->ame_activation_tab_key . '">' . __('Manage License Key', AME_WRI()->text_domain ) . '</a>',
			);

			return array_merge( $links, $new_links );
		}

		return (array) $links;
	}
	
	// ADD RaPe END
	

	/** Load Shared Classes as on-demand Instances **********************************************/

	/**
	 * API Key Class.
	 *
	 * @return  API_Manager_WRI_Key
	 */
	public function key() {
		
		//TBSET: class name, must match the class name in am/classes/class-wc-key-api.php:
		return API_Manager_WRI_Key::instance();
	}

	/**
	 * Update Check Class.
	 *
	 * @return API_Manager_WRI_Update_API_Check
	 */
	public function update_check( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra = '' ) {

		//TBSET: class name, must match the class name in am/classes/class-wc-plugin-update.php:
		return API_Manager_WRI_Update_API_Check::instance( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra );
	}

	public function plugin_url() {
		if ( isset( $this->plugin_url ) ) {
			return $this->plugin_url;
		}

		//return $this->plugin_url = plugins_url( '/', __FILE__ );
		return $this->plugin_url = plugins_url( '/', WRI_PLUGIN_FILE ); //TBSET //MODIFY RaPe
		
	}

	/**
	 * Generate the default data arrays (Normal WP plugin activation)
	 */
	public function activation() {
		global $wpdb;

		$global_options = array(
			$this->ame_api_key 				=> '',
			$this->ame_activation_email 	=> '',
					);

		update_option( $this->ame_data_key, $global_options );

		$single_options = array(
			$this->ame_product_id_key 			=> $this->ame_software_product_id,
			$this->ame_instance_key 			=> wp_generate_password( 12, false ),
			$this->ame_deactivate_checkbox_key 	=> 'on',
			$this->ame_activated_key 			=> 'Deactivated', //TBSET:  string - option_name key used to store the option_value for the activation checkbox.
			);

		//wsl_log(null, 'WRI class-api-manager-client.php plugin activation $single_options: ' . wsl_vartotext($single_options));

		foreach ( $single_options as $key => $value ) {
			update_option( $key, $value );
		}

		$curr_ver = get_option( $this->api_manager_client_version_name );

		// checks if the current plugin version is lower than the version being installed
		if ( version_compare( $this->version, $curr_ver, '>' ) ) {
			// update the version
			update_option( $this->api_manager_client_version_name, $this->version );
		}

	}

	/**
	 * Deletes all data if plugin deactivated
	 * @return void
	 */
	public function uninstall() {
		global $wpdb, $blog_id;

		$this->license_key_deactivation();

		// Remove options
		if ( is_multisite() ) {

			switch_to_blog( $blog_id );

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_deactivate_checkbox_key,
					$this->ame_activated_key,
					) as $option) {

					delete_option( $option );

					}

			restore_current_blog();

		} else {

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_deactivate_checkbox_key,
					$this->ame_activated_key
					) as $option) {

					delete_option( $option );

					}

		}

	}

	/**
	 * Deactivates the license on the API server
	 * @return void
	 */
	public function license_key_deactivation() {

		$activation_status = get_option( $this->ame_activated_key );

		$api_email = $this->ame_options[$this->ame_activation_email];
		$api_key = $this->ame_options[$this->ame_api_key];

		$args = array(
			'email' => $api_email,
			'licence_key' => $api_key,
			);

		if ( $activation_status == 'Activated' && $api_key != '' && $api_email != '' ) {
			$this->key()->deactivate( $args ); // reset license key activation
		}
	}

    /**
     * Displays an inactive notice when the software is inactive.
	 * 
	 * //TBSET: api_manager_example_dashboard - string, found inside the am_example_inactive_notice() static method. Must match the $this->ame_activation_tab_key class variable.
	 * //TBSET: n the line <p><?php printf( __( 'The API Manager Example API License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'api-manager-example' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=api_manager_example_dashboard' ) ) . '">', '</a>' ); ?></p>, change The API Manager Example to match your plugin's name, and api_manager_example_dashboard to match the $this->ame_activation_tab_key class variable.
     */
	public static function am_client_inactive_notice() { 
		
		//Original: printf( __( 'The WP Related Items PRO (WRI) by WebshopLogic License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'api-manager-wri_text' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=api_manager_wri_dashboard' ) ) . '">', '</a>' );
		?>
		<?php if ( ! current_user_can( 'manage_options' ) ) return; ?>
		<?php if ( isset( $_GET['page'] ) && 'api_manager_wri_dashboard' == $_GET['page'] ) return; ?>
		<div id="message" class="error">
			<p><?php printf( __( 'The WP Related Items PRO License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'api-manager-wri_text' ), 
			'<a href="' . esc_url( admin_url() . 'admin.php?page=api_manager_wri_dashboard' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check for external blocking contstant
	 * @return string
	 */
	public function check_external_blocking() {
		// show notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
		if( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {

			// check if our API endpoint is in the allowed hosts
			$host = parse_url( $this->upgrade_url, PHP_URL_HOST );

			if( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) {
				?>
				<div class="error">
					<p><?php printf( __( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %s updates. Please add %s to %s.', 'api-manager-wri_text' ), $this->ame_software_product_id, '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>'); ?></p>
				</div>
				<?php
			}

		}
	}

} // End of class

function AME_WRI() {
    return API_Manager_WRI::instance(); 	//TBSET: API_Manager_Example - class name found inside the AME_WRI) function, must match the class name in the api-manager-example.php file.
}

// Initialize the class instance only once
AME_WRI(); 											//TBSET IN-ALL-FILES: AME_WRI) - function. Do a find and replace throughout all the plugin example files. The functions makes the singular design pattern possible in the plugin example.
