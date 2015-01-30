<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Append new links to the Plugin admin side
add_filter( 'plugin_action_links_' . UUC::get_instance()->plugin_file , 'uuc_plugin_action_links');

function uuc_plugin_action_links( $links ) {
	$upgrade_user = UUC::get_instance();
	
	$settings_link = '<a href="users.php?page=' . $upgrade_user->menu . '">' . __( 'Settings', 'user-upgrade-capability' ) . "</a>";
	array_push( $links, $settings_link );
	return $links;	
}


// add action after the settings save hook.
add_action( 'tabbed_settings_after_update', 'uuc_after_settings_update' );

function uuc_after_settings_update( ) {

	flush_rewrite_rules();	
	
}



/**
 * UUC_Settings class.
 *
 * Main Class which inits the CPTs and plugin
 */
class UUC_Settings {
	
	// Refers to a single instance of this class.
    private static $instance = null;
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Called during admin_menu, adds rendered using the plugin_options_page method.
	 *
	 * @access public
	 * @return void
	 */
	public function add_admin_menus() {

		add_submenu_page( 'users.php', __( 'Upgrade User Capability', 'user-upgrade-capability' ), __( 'Upgrade Capability', 'user-upgrade-capability' ), 'manage_network_users', UUC_Settings::get_instance()->menu, array( $this, 'plugin_options_page' ) );	
	}
	
	/**
     * Creates or returns an instance of this class.
     *
     * @return   A single instance of this class.
     */
    public static function get_instance() {

		$upgrade_user = UUC::get_instance();
		
		$config = array(
				'default_tab_key' => 'uuc_general',					// Default settings tab, opened on first settings page open.
				'menu_parent' => 'users.php',    					// menu options page slug name.
				'menu_access_capability' => 'list_users', 			// menu options page slug name.
				'menu' => $upgrade_user->menu,    					// menu options page slug name.
				'menu_title' => $upgrade_user->menu_title,    		// menu options page slug name.
				'page_title' => $upgrade_user->page_title,    		// menu options page title.
				);

		$settings = 	apply_filters( 'uuc_settings',
			array(
				'uuc_general' => array(
					'access_capability' => 'manage_network_users',
					'title' 		=> __( 'General', 'user-upgrade-capability' ),
					'description' 	=> __( 'The "User Upgrade Capability" plugin simplifies the administration of user capabilities and allows several sites/blogs to have access rights cascaded out from one reference site.', 'user-upgrade-capability' ),
					'settings' 		=> array(		
											array(
												'name' 		=> 'uuc_reference_site',
												'std' 		=> '0',
												'label' 	=> __( 'Reference Site', 'user-upgrade-capability' ),
												'desc'		=> __( 'WARNING !! changing this setting will replace this site available roles/capabilities from the reference site selected YOU CANNOT UNDO.  Only change this option on new sites or when you wish to do so.  (If left as "-none-" this plugin will be disabled).', 'user-upgrade-capability' ),
												'type'      => 'field_site_list_option',
												),
											array(
												'name' 		=> 'uuc_reference_key_capability',
												'std' 		=> '',
												'label' 	=> __( 'Reference Site Key Capability', 'user-upgrade-capability' ),
												'desc'		=> __( 'If left blank the site name will be used to search for as the capability on the reference site.', 'user-upgrade-capability' ),
												),													
										),										
				),
				'uuc_user_capabilities' => array(
					'access_capability' => 'promote_users',
					'title' 		=> __( 'User Capability', 'user-upgrade-capability' ),
					'description' 	=> __( 'Define here the capabilities that a user will receive for this site if they already have a single "key" capability on a reference site/blog within the multi-site network.  On user login to the current local site the plugin will expand the local capabilities to include those listed below. If the "key" reference capability is removed from the user at the reference site then all capabilities will be removed from this site the user will need to re-enrole.', 'user-upgrade-capability' ),
					'settings' 		=> array(														
											array(
												'columns'   => "45",
												'rows'   	=> "8",
												'name' 		=> 'uuc_additional_capabilties',
												'std' 		=> '',
												'label' 	=> __( 'Local site Additional Capabilities', 'user-upgrade-capability' ),
												'desc'		=> __( 'Put each capability on a separate row.', 'user-upgrade-capability' ),
												'type'      => 'field_textarea_option',
												),
										),										
				),
				'uuc_plugin_extension' => array(
						'access_capability' => 'install_plugins',
						'title' 		=> __( 'Plugin Suggestions', 'user-upgrade-capability' ),
						'description' 	=> __( 'Most Plugins listed here are intended for use with the "Upgrade Use Capability" plugin.  Selection of a plugin will prompt you through the installation and the plugin will be forced network active while this is selected; deselecting will not remove the plugin, you will need to manually deactiate and uninstall from the network.', 'user-upgrade-capability' ),					
						'settings' 		=> array(
												array(
													'name' 		=> 'uuc_user_role_editor_plugin',
													'std' 		=> false,
													'label' 	=> 'User Role Editor',
													'desc'		=> __( "This plugin gives the ability to edit users capabilities.  Once installed go to menu [users]..[User Role Editor].", 'user-upgrade-capability' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'user-role-editor',
													'plugin_dir'			=> UUC_PLUGIN_DIR,
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),							
												array(
													'name' 		=> 'uuc_join_my_multisite_plugin',
													'std' 		=> false,
													'label' 	=> 'Join My Multi-site',
													'desc'		=> __( "This plugin allows for users to auto en-role on sites.  Once installed go to menu [users]..[Join My Multisite].", 'user-upgrade-capability' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'join-my-multisite',
													'plugin_dir'			=> UUC_PLUGIN_DIR,
													'filename'      			=> 'joinmymultisite', 
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),
												array(
													'name' 		=> 'uuc_blog_copier_plugin',
													'std' 		=> false,
													'label' 	=> 'Blog Copier',
													'desc'		=> __( "This is optional and is very helpful in the creation of new sites by simply copying an existing site, you can then create template sites for re-use.  Once installed go to the main Network..[sites]...[Blog Copier]", 'user-upgrade-capability' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'blog-copier',
													'plugin_dir'			=> UUC_PLUGIN_DIR,
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),
												),
					)
				)
			);
		
							
        if ( null == self::$instance ) {
            self::$instance = new Tabbed_Settings( $settings, $config );
        }

        return self::$instance;
 
    } 
}


/**
 * UUC_Settings_Additional_Methods class.
 */

/**
 * UUC_Settings_Additional_Methods class.
 */
class UUC_Settings_Additional_Methods {

	/**
	 * field_site_list_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_site_list_option( array $args ) {

		global $wpdb;

		$defaults = array(
					'name' 		=> '',
					);
			
		$option = wp_parse_args( $args['option'], $defaults );
		$value = get_option( $option['name'] );

		$blogs = $wpdb->get_results("
			SELECT blog_id
			FROM {$wpdb->blogs}
			WHERE site_id = '{$wpdb->siteid}'
			AND spam = '0'
			AND deleted = '0'
			AND archived = '0'
		");

		$sites = array();
		$sites[0] = _x( "- None -", 'text for no site selected', 'user-upgrade-capability' );
		foreach ($blogs as $blog) {
			$sites[$blog->blog_id] = get_blog_option($blog->blog_id, 'blogname');
		}
		natsort($sites);

		?>
		<form action="<?php site_url(); ?>" method="get">
			<select id="setting-<?php echo esc_html( $option['name'] ); ?>" class="regular-text" name="<?php echo esc_html( $option['name'] ); ?>">
											<?php foreach( $sites as $blog_id=>$site ) { ?>
												<option value="<?php echo $blog_id; ?>" <?php selected( $blog_id, $value ); ?>><?php echo $site; ?></option>
											<?php } ?>		
			</select>
				<class="description"> ( blog_id:  <?php echo get_option('uuc_reference_site')?> )
		</form>


		<?php
		
		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';		
	}
}


// Include the Tabbed_Settings class.

require_once( dirname( __FILE__ ) . '/class-tabbed-settings.php' );

// Create new tabbed settings object for this plugin..
// and Include additional functions that are required.
UUC_Settings::get_instance()->registerHandler( new UUC_Settings_Additional_Methods() );


?>