<?php
/**
Plugin Name: User Upgrade Capability
Plugin URI: http://justinandco.com
Description: Link multiple network sites/blogs together - Maintain only one site list of users.
Version: 1.0
Author: Justin Fletcher
Author URI: http://justinandco.com
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * UUC class.
 *
 * Main Class which inits the CPTs and plugin
 */
class UUC {
    
    /**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Set the constants needed by the plugin.
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		
		/* Load the functions files. */
		add_action( 'plugins_loaded', array( $this, 'includes' ), 2 );
		
		/* Hooks... */
		
		// A settings page to the admin acitve plugin listing
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ));

		// Attached to set_current_user. Loads the plugin installer CLASS after themes are set-up to stop duplication of the CLASS.
		// this should remain the hook until TGM-Plugin-Activation version 2.4.0 has had time to roll out to the majority of themes and plugins.
		add_action( 'set_current_user', array( $this, 'set_current_user' ));
		
		// register admin side - Loads the textdomain, upgrade routine and menu item.
		add_action( 'admin_init', array( $this, 'admin_init' ));
		
		// register the selected-active Help Note post types
		add_action( 'init', array( $this, 'init' ));

		// Load admin error messages	
		add_action( 'admin_init', array( $this, 'deactivation_notice' ));
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ));
		
		// Load user with new capabilities	
		add_filter( 'template_include', array( $this, 'userupgradecapabilities_template'), 99 );

		add_action('init', array( $this, 'override_wp_user_roles'));  
				
	}

	
	/**
	 * Defines constants used by the plugin.
	 *
	 * @return void
	 */
	public function constants() {

		// Define constants
		define( 'UUC_MYPLUGINNAME_PATH', plugin_dir_path( __FILE__ ) );
		define( 'UUC_MYPLUGINNAME_FULL_PATH', UUC_MYPLUGINNAME_PATH . 'user-upgrade-capability.php' );
		define( 'UUC_PLUGIN_DIR', plugin_dir_path( UUC_MYPLUGINNAME_PATH ) );
		define( 'UUC_PLUGIN_URI', plugins_url('', __FILE__) );
		define( 'UUC_SETTINGS_PAGE', 'uuc-settings');
		
		// admin prompt constants
		define( 'UUC_PROMPT_DELAY_IN_DAYS', 30);
		define( 'UUC_PROMPT_ARGUMENT', 'uuc_hide_notice');
		
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @return void
	 */
	public function includes() {


		// settings 
		//require_once( UUC_MYPLUGINNAME_PATH . 'includes/settings.php' );  
		require_once( UUC_MYPLUGINNAME_PATH . 'includes/class-settings.php' );  
		
		// if selected install the plugin(s) and force activation
		require_once( UUC_MYPLUGINNAME_PATH . 'includes/class-install-plugins.php' );    

	
	}

	
	/**
	 * Append new links to the Plugin admin side
	 *
	 * @param array $links Current links/urls provided for the plugin admin.
	 * @return array $links
	 */
	public function action_links( $links ) {
		array_unshift( $links, '<a href="users.php?page=' . UUC_SETTINGS_PAGE . '">' . __( 'Settings' ) . "</a>" );
		return $links;
	}


	public function userupgradecapabilities_template( $template ) {

		// call the settings saved post-type to use in the expression to limit access
		// First, we read the option collection  
		$options = get_option('uuc_plugin_uuc_option');


		// convert  to array delimiting by "," also trim off white space
		$uuc_userupgradecapabilities_array =   array_map( 'trim',explode( ",", trim($options['uuc_current_capability'] )));  

		if ( is_singular( $uuc_userupgradecapabilities_array) && !current_user_can( $options['uuc_capability'] ) )

			if ( locate_template( array($options['uuc_template']) ) != '' ) {
				// locate_template() returns path to file
				// if either the child theme or the parent theme have overridden the template
				$template = locate_template( array( $options['uuc_template']  ) );
			}
			 else {
				// If neither the child nor parent theme have overridden the template,
				// we load the template from the 'templates' sub-directory of the directory this file is in
				$template = UUC_MYPLUGINNAME_PATH.'template/no-access.php';
			}
		return $template;
	}

	
	/**
	 * Initialise the plugin installs
	 *
	 * @return void
	 */
	public function set_current_user() {

		// install the plugins and force activation if they are selected within the plugin settings
		require_once( UUC_MYPLUGINNAME_PATH . 'includes/class-install-plugins.php' );    	
		
	}
        
	/**
	 * Initialise the plugin by handling upgrades and loading the text domain. 
	 *
	 * @return void
	 */
	public function admin_init() {
		
		$plugin_current_version = get_option( 'uuc_plugin_version' );
		$plugin_new_version =  $this->plugin_get_version();
		
		// Admin notice hide prompt notice catch
		$this->catch_hide_notice();

		if ( empty($plugin_current_version) || $plugin_current_version < $plugin_new_version ) {
		
			$plugin_current_version = isset( $plugin_current_version ) ? $plugin_current_version : 0;

			$this->uuc_upgrade( $plugin_current_version );

			// set default options if not already set..
			$this->do_on_activation();
			
			// Update the option again after uuc_upgrade() changes and set the current plugin revision	
			update_option('uuc_plugin_version', $plugin_new_version ); 
		}
			
		load_plugin_textdomain('user-upgrade-capability-text-domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Provides an upgrade path for older versions of the plugin
	 *
	 * @param float $current_plugin_version the local plugin version prior to an update 
	 * @return void
	 */
	public function uuc_upgrade( $current_plugin_version ) {
		
		// move current database stored values into the next structure
		if ( $current_plugin_version < '1.1.1.1.1.1' ) {

			delete_option('help_note_caps_created');

		}
	}

	/**
	 * Registers all required Help Notes
	 *
	 * @access public	 
	 * @return void
	 */
	public function init() {
		
		$this->action_init_store_user_meta();

		/**
		 * Init settings class
		 */
		$uuc_settings = new UUC_Settings();

		if (class_exists( 'UUC_EXTRA_Settings' )) {
			$uuc_settings->registerHandler(new UUC_EXTRA_Settings_Additional_Methods());
		}
			
	}



	/**
	 * Add capabilities and Flush your rewrite rules for plugin activation.
	 *
	 * @access public
	 * @return $settings
	 */	
	public function do_on_activation() {

		// Record plugin activation date.
		add_option('uuc_install_date',  time() ); 
		
		// create the plugin_version store option if not already present.
		$plugin_version = $this->plugin_get_version();
		update_option('uuc_plugin_version', $plugin_version ); 

		flush_rewrite_rules();
	}

	/**
	 * Returns current plugin version.
	 *
	 * @access public
	 * @return $plugin_version
	 */	
	public function plugin_get_version() {
		$plugin_data = get_plugin_data( UUC_MYPLUGINNAME_FULL_PATH );	
		$plugin_version = $plugin_data['Version'];	
		return filter_var($plugin_version, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	
		
	/**
	 * Register Plugin Deactivation Hooks for all the currently 
	 * enforced active extension plugins.
	 *
	 * @access public
	 * @return null
	 */
	public function deactivation_notice() {

		// loop plugins forced active.
		$plugins = UUC_Settings::install_plugins();

		foreach ( $plugins as $plugin ) {
			$plugin_file = UUC_PLUGIN_DIR . $plugin["slug"] . '\\' . $plugin['slug'] . '.php' ;
			register_deactivation_hook( $plugin_file, array( 'UUC', 'on_deactivation' ) );
		}
	}

	/**
	 * This function is hooked into plugin deactivation for 
	 * enforced active extension plugins.
	 *
	 * @access public
	 * @return null
	 */
	public static function on_deactivation()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );
	
		$plugin_slug = explode( "/", $plugin);
		$plugin_slug = $plugin_slug[0];
		update_option( "uuc_deactivate_{$plugin_slug}", true );
    }
	
	/**
	 * Display the admin warnings.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_notices() {

		// loop plugins forced active.
		$plugins = UUC_Settings::install_plugins();

		// for each extension plugin enabled (forced active) add a error message for deactivation.
		foreach ( $plugins as $plugin ) {
			$this->action_admin_plugin_forced_active_notices( $plugin["slug"] );
		}
		
		// Prompt for rating
		$this->action_admin_rating_prompt_notices();
	}
	
	/**
	 * Display the admin error message for plugin forced active.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_plugin_forced_active_notices( $plugin ) {
	
		$plugin_message = get_option("uuc_deactivate_{$plugin}");
		if ( ! empty( $plugin_message ) ) {
			?>
			<div class="error">
				  <p><?php esc_html_e(sprintf( __( 'Error the %1$s plugin is forced active with ', 'user-upgrade-capability-text-domain'), $plugin)); ?>
				  <a href="options-general.php?page=<?php echo UUC_SETTINGS_PAGE ?>&tab=uuc_plugin_extension"> <?php echo esc_html(__( 'Help Note Settings!', 'user-upgrade-capability-text-domain')); ?> </a></p>
			</div>
			<?php
			update_option("uuc_deactivate_{$plugin}", false); 
		}
	}

		
	/**
	 * Store the current users start date with Help Notes.
	 *
	 * @access public
	 * @return null
	 */
	public function action_init_store_user_meta() {
		
		// start meta for a user
		add_user_meta( get_current_user_id(), 'uuc_start_date', time(), true );
		add_user_meta( get_current_user_id(), 'uuc_prompt_timeout', time() + 60*60*24*  UUC_PROMPT_DELAY_IN_DAYS, true );

	}

	
	/**
	 * Display the admin message for plugin rating prompt.
	 *
	 * @access public
	 * @return null
	 */
	public function action_admin_rating_prompt_notices( ) {

		$user_responses =  array_filter( (array)get_user_meta( get_current_user_id(), UUC_PROMPT_ARGUMENT, true ));	
		if ( in_array(  "done_now", $user_responses ) ) 
			return;

		if ( current_user_can( 'install_plugins' ) ) {
			
			$next_prompt_time = get_user_meta( get_current_user_id(), 'uuc_prompt_timeout', true );
			if ( ( time() > $next_prompt_time )) {
				$plugin_user_start_date = get_user_meta( get_current_user_id(), 'uuc_start_date', true );
				?>
				<div class="update-nag">
					
					<p><?php esc_html(printf( __("You've been using <b>Upgrade User Capability</b> for more than %s.  How about giving it a review by logging in at wordpress.org ?", 'user-upgrade-capability-text-domain'), human_time_diff( $plugin_user_start_date) )); ?>
				
					</p>
					<p>

						<?php echo '<a href="' .  esc_url(add_query_arg( array( UUC_PROMPT_ARGUMENT => 'doing_now' )))  . '">' .  esc_html__( "Yes, please take me there.", 'user-upgrade-capability-text-domain' ) . '</a> '; ?>
						
						| <?php echo ' <a href="' .  esc_url(add_query_arg( array( UUC_PROMPT_ARGUMENT => 'not_now' )))  . '">' .  esc_html__( "Not right now thanks.", 'user-upgrade-capability-text-domain' ) . '</a> ';?>
						
						<?php
						if ( in_array(  "not_now", $user_responses ) || in_array(  "doing_now", $user_responses )) { 
							echo '| <a href="' .  esc_url(add_query_arg( array( UUC_PROMPT_ARGUMENT => 'done_now' )))  . '">' .  esc_html__( "I've already done this !", 'user-upgrade-capability-text-domain' ) . '</a> ';
						}?>

					</p>
				</div>
				<?php
			}
		}	
	}
	
	/**
	 * Store the user selection from the rate the plugin prompt.
	 *
	 * @access public
	 * @return null
	 */
	public function catch_hide_notice() {
	
		if ( isset($_GET[UUC_PROMPT_ARGUMENT]) && $_GET[UUC_PROMPT_ARGUMENT] && current_user_can( 'install_plugins' )) {
			
			$user_user_hide_message = array( sanitize_key( $_GET[UUC_PROMPT_ARGUMENT] )) ;				
			$user_responses =  array_filter( (array)get_user_meta( get_current_user_id(), UUC_PROMPT_ARGUMENT, true ));	

			if ( ! empty( $user_responses )) {
				$response = array_unique( array_merge( $user_user_hide_message, $user_responses ));
			} else {
				$response =  $user_user_hide_message;
			}
			
			check_admin_referer();	
			update_user_meta( get_current_user_id(), UUC_PROMPT_ARGUMENT, $response );

			if ( in_array( "doing_now", (array_values((array)$user_user_hide_message ))))  {
				$next_prompt_time = time() + ( 60*60*24*  UUC_PROMPT_DELAY_IN_DAYS ) ;
				update_user_meta( get_current_user_id(), 'uuc_prompt_timeout' , $next_prompt_time );
				wp_redirect( 'http://wordpress.org/support/view/plugin-reviews/upgrade-user-capability' );
				exit;					
			}

			if ( in_array( "not_now", (array_values((array)$user_user_hide_message ))))  {
				$next_prompt_time = time() + ( 60*60*24*  UUC_PROMPT_DELAY_IN_DAYS ) ;
				update_user_meta( get_current_user_id(), 'uuc_prompt_timeout' , $next_prompt_time );		
			}
				
				
			wp_redirect( remove_query_arg( UUC_PROMPT_ARGUMENT ) );
			exit;		
		}
	}
	

	public function override_wp_user_roles() {
	
		$primary_ref_site = get_option('uuc_reference_site');

		if ( !empty( $primary_ref_site ) && is_multisite() && ( get_current_blog_id() != $primary_ref_site ) ) {
		
			$key_capability = get_option('uuc_reference_key_capability');
			$new_capabilities = get_option('uuc_additional_capabilties');

			switch_to_blog( $primary_ref_site );
			global $table_prefix;
			$primary_ref_site_table_prefix = $table_prefix;
			restore_current_blog();

			global $table_prefix;


			// Collect the site roles defined in the network primary reference site 
			// wp_ used here is not the table prefix but the option-name prefix and so doesn't change along with the $table_prefix
			$jmf_wp_user_roles = get_blog_option( $primary_ref_site, $primary_ref_site_table_prefix . 'user_roles' );

//echo var_dump("get_blog_option( $primary_ref_site, $primary_ref_site_table_prefix . 'user_roles'  )") ;    		
//echo var_dump($jmf_wp_user_roles) ;        	
//echo var_dump("update_option( $table_prefix . 'user_roles', $jmf_wp_user_roles )") ;   


			// Update the current local site defined roles to match
			update_option( $table_prefix . 'user_roles', $jmf_wp_user_roles );    
			
			// Now based on the current user having a capability within the base site start to open up more capability within this site     
			if( $key_capability . '' == '' ) {
				// if no explicit capability is defined use the site url path name ( this allows a level of flexibility on automatically looking for a named capability without the need to set this option manually )
				$site_inital_required_capability = ltrim( parse_url( get_bloginfo( 'siteurl' ), PHP_URL_PATH ), '/' ); 
			} else {
				// otherwise use the capability defined
				$site_inital_required_capability = $key_capability; 
			}  


			// check for the capability on the base site
			if ( current_user_can_for_blog( $primary_ref_site, $site_inital_required_capability )) {
				
				$user = new WP_User( get_current_user_id() );
																									
				// add the key capability for the current site
				$user->add_cap( $site_inital_required_capability );
				
				// then add new capabilities
				// convert capability option string to array delimiting by "new line" also trim off white space
				$caps =   array_map('trim',explode( "\r\n", trim( $new_capabilities )));  
				// add $caps capability for this user
				foreach ( $caps as $cap ){
					$user->add_cap( $cap );
				}

			} else {

				// remove any existing capability if no longer with the initial required capability in the primary_ref_site
				$user = new WP_User( get_current_user_id( ) );
				$user->remove_all_caps();
			}
		}
	}
}

/**
 * Init Upgrade User Capability class
 */
 
$UUC = new UUC();

?>