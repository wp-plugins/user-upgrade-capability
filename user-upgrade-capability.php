<?php
/**
Plugin Name: User Upgrade Capability
Plugin URI: http://justinandco.com/plugins/user-upgrade-capabilities/
Description: Link multiple network sites/blogs together - Maintain only one site list of users.
Version: 1.1.1
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

	// Refers to a single instance of this class.
    private static $instance = null;
	
    public	 $plugin_full_path;
	public   $plugin_file = 'user-upgrade-capability/user-upgrade-capability.php';
	
	// Settings page slug	
    public	 $menu = 'uuc-settings';
	
	// Settings Admin Menu Title
    public	 $menu_title = 'Upgrade Capability';
	
	// Settings Page Title
    public	 $page_title = 'Upgrade Capability';
    
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

		// Attached to set_current_user. Loads the plugin installer CLASS after themes are set-up to stop duplication of the CLASS.
		// this should remain the hook until TGM-Plugin-Activation version 2.4.0 has had time to roll out to the majority of themes and plugins.
		add_action( 'set_current_user', array( $this, 'set_current_user' ));
		
		// register admin side - Loads the textdomain, upgrade routine and menu item.
		add_action( 'admin_init', array( $this, 'admin_init' ));
       // add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		
		// register the selected-active Help Note post types
		add_action( 'init', array( $this, 'init' ));

		// Load admin error messages	
		add_action( 'admin_init', array( $this, 'deactivation_notice' ));
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ));
		
		
		// Load user with new capabilities	
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
		define( 'UUC_PLUGIN_DIR', trailingslashit( plugin_dir_path( UUC_MYPLUGINNAME_PATH )));
		define( 'UUC_PLUGIN_URI', plugins_url('', __FILE__) );
		
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
		require_once( UUC_MYPLUGINNAME_PATH . 'includes/settings.php' );  
	
	}
	
	/**
	 * Initialise the plugin installs
	 *
	 * @return void
	 */
	public function set_current_user() {

		// install the plugins and force activation if they are selected within the plugin settings
		require_once( UUC_MYPLUGINNAME_PATH . 'includes/plugin-install.php' );
		
	}

        
    /**
	 * Initialise the plugin menu. 
	 *
	 * @return void
	 */
	public function admin_menu() {
		//add_submenu_page( 'users.php' , __(  $this->page_title, 'user-upgrade-capabilty-text-domain' ),  __( $this->menu_title, 'songbook-text-domain' ),  'edit_users', $this->menu, array( &$this, 'sub_menu_page' ) );
	}
    
	/**
	 * sub_menu_page: 
	 *
	 * @return void
	 */
	public function sub_menu_page() {
		// 
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

			// create the plugin_version store option if not already present.
			$plugin_version = self::plugin_get_version();
			update_option('uuc_plugin_version', $plugin_version ); 
			
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
		
		/*
		// upgrade code when required.
		if ( $current_plugin_version < '1.0' ) {

			delete_option('help_note_caps_created');

		}
		*/
	}

	/**
	 * Registers all required Help Notes
	 *
	 * @access public	 
	 * @return void
	 */
	public function init() {
		
		$this->action_init_store_user_meta();
			
	}

	/**
	 * Add capabilities and Flush your rewrite rules for plugin activation.
	 *
	 * @access public
	 * @return $settings
	 */	
	static function do_on_activation() {

		// Record plugin activation date.
		add_option('uuc_install_date',  time() ); 

		// Add protection activation by clearing the reference site and so initially disabling the plugin
		update_option('uuc_reference_site',  '0' ); 

		flush_rewrite_rules();
	}

	/**
	 * Returns current plugin version.
	 *
	 * @access public
	 * @return $plugin_version
	 */	
	static function plugin_get_version() {

		$plugin_data = get_plugin_data( UUC_MYPLUGINNAME_FULL_PATH, false, false );	

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
		$plugins = UUC_Settings::get_instance()->selected_plugins( 'uuc_plugin_extension' );

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
		$plugins = UUC_Settings::get_instance()->selected_plugins( 'uuc_plugin_extension' );

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
				wp_redirect( 'http://wordpress.org/support/view/plugin-reviews/user-upgrade-capability' );
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

			// Update the current local site defined roles to match
			update_option( $table_prefix . 'user_roles', $jmf_wp_user_roles );    
			
			// Now based on the current user having a capability within the base site start to open up more capability within this site     
			if( $key_capability . '' == '' ) {
				// if no explicit capability is defined use the site url path name ( this allows a level of flexibility on automatically looking for a named capability without the need to set this option manually )
				$site_inital_required_capability = basename( site_url() );
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
	

	/**
     * Creates or returns an instance of this class.
     *
     * @return   A single instance of this class.
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
 
        return self::$instance;
 
    }		
}

register_activation_hook( __FILE__, array( 'UUC', 'do_on_activation' ) );

/**
 * Init Upgrade User Capability class
 */
 
UUC::get_instance();

?>