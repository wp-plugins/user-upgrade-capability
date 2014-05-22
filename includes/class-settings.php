<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

Class UUC_Extendible_Settings  {

    private $handlers = array();
	
    public function registerHandler($handler) {
        $this->handlers[] = $handler;
    }

    public function __call($method, $arguments) {
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, $method)) {
                return call_user_func_array(
                    array($handler, $method),
                    $arguments
                );
            }
        }
    }
}

/**
 * UUC_Settings class.
 */
class UUC_Settings extends UUC_Extendible_Settings {

	private static $settings = '';
	private $default_tab_key = 'uuc_general';
	private $plugin_options_key = UUC_SETTINGS_PAGE;
	private $plugin_settings_tabs = array();
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */	 
	function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		add_action( 'do_settings_sections', array( $this, 'hook_section_callback' ) );
	}


	/**
	 * init_settings function.
	 *
	 * @access private
	 * @return void
	 */
	private static function init_settings() {
		self::$settings = apply_filters( 'uuc_settings',
			array(
				'uuc_general' => array(
					'title' 		=> __( 'General', 'user-upgrade-capability-text-domain' ),
					'description' 	=> __( 'The "User Upgrade Capability" plugin simplifies the administration of user capabilities and allows several sites/blogs to have access rights cascaded out from one reference site.  Define here the capabilities that a user will receive for this site if they already have a single "key" capability on a reference site/blog within the multi-site network.  On user login to the current local site the plugin will expand the local capabilities to include those listed below. If the "key" reference capability is removed from the user at the reference site then all capabilities will be removed from this site the user will need to re-enrole. ( the plugin is intended to be used with plugins listed under the "Plugin Extensions" tab ).', 'user-upgrade-capability-text-domain' ),
					'settings' 		=> array(		
											array(
												'name' 		=> 'uuc_reference_site',
												'std' 		=> '0',
												'label' 	=> __( 'Reference Site', 'user-upgrade-capability-text-domain' ),
												'desc'		=> __( 'WARNING !! changing this setting will replace this site available roles/capabilities from the reference site selected YOU CANNOT UNDO.  Only change this option on new sites or when you a wish to do so.  (If left as "-none-" this plugin will be disabled).', 'user-upgrade-capability-text-domain' ),
												'type'      => 'field_site_list_option',
												),
											array(
												'name' 		=> 'uuc_reference_key_capability',
												'std' 		=> '',
												'label' 	=> __( 'Reference Site Key Capability', 'user-upgrade-capability-text-domain' ),
												'desc'		=> __( 'If left blank the site name will be used to search for as the capability on the reference site.', 'user-upgrade-capability-text-domain' ),
												),													
											array(
												'columns'   => "37",
												'rows'   	=> "10",
												'name' 		=> 'uuc_additional_capabilties',
												'std' 		=> '',
												'label' 	=> __( 'Local site Additional Capabilities', 'user-upgrade-capability-text-domain' ),
												'desc'		=> __( 'Put each capability on a separate row.', 'user-upgrade-capability-text-domain' ),
												'type'      => 'field_textarea_option',
												),
										),										
				),
				'uuc_plugin_extension' => array(
						'title' 		=> __( 'Plugin Extensions', 'user-upgrade-capability-text-domain' ),
						'description' 	=> __( 'Most Plugins listed here are intended for use with the "Upgrade Use Capability" plugin.  Selection of a plugin will prompt you through the installation and the plugin will be forced network active while this is selected; deselecting will not remove the plugin, you will need to manually deactiate and uninstall from the network.', 'user-upgrade-capability-text-domain' ),					
						'settings' 		=> array(
												array(
													'name' 		=> 'uuc_user_role_editor_plugin',
													'std' 		=> false,
													'label' 	=> 'User Role Editor',
													'cb_label'  => __( 'Enable', 'user-upgrade-capability-text-domain' ),
													'desc'		=> __( "This plugin gives the ability to edit users capabilities.  Once installed go to menu [users]..[User Role Editor].", 'user-upgrade-capability-text-domain' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'user-role-editor', 
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),							
												array(
													'name' 		=> 'uuc_join_my_multisite_plugin',
													'std' 		=> false,
													'label' 	=> 'Join My Multi-site',
													'cb_label'  => __( 'Enable', 'user-upgrade-capability-text-domain' ),
													'desc'		=> __( "This plugin allows for users to auto en-role on sites.  Once installed go to menu [users]..[Join My Multisite].", 'user-upgrade-capability-text-domain' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'join-my-multisite', 
													'filename'      			=> 'joinmymultisite', 
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),
												array(
													'name' 		=> 'uuc_blog_copier_plugin',
													'std' 		=> false,
													'label' 	=> 'Blog Copier',
													'cb_label'  => __( 'Enable', 'user-upgrade-capability-text-domain' ),
													'desc'		=> __( "This is optional and is very helpful in the creation of new sites by simply copying an existing site, you can then create template sites for re-use.  Once installed go to the main Network..[sites]...[Blog Copier]", 'user-upgrade-capability-text-domain' ),
													'type'      => 'field_plugin_checkbox_option',
													// the following are for tgmpa_register activation of the plugin
													'slug'      			=> 'blog-copier',
													'required'              => false,
													'force_deactivation' 	=> false,
													'force_activation'      => true,		
													),
												),
				)
			)
		);
	}

	
	/**
	 * Called during admin_menu, adds rendered using the plugin_options_page method.
	 *
	 * @access public
	 * @return void
	 */	 
	public function add_admin_menus() {
		add_submenu_page( 'users.php', __( 'Upgrade User Capability', 'user-upgrade-capability-text-domain' ), __( 'Upgrade Capability', 'user-upgrade-capability-text-domain' ), 'manage_network_users', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
	}

	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		self::init_settings();
		foreach ( self::$settings as $options_group => $section  ) {
			foreach ( $section['settings'] as $option ) {
				$this->current_section = $section;
				if ( isset( $option['std'] ) )
					add_option( $option['name'], $option['std'] );
				$this->plugin_settings_tabs[$options_group] = $section['title'];
				register_setting( $options_group, $option['name'] );
				add_settings_section( $options_group, $section['title'], array( $this, 'hook_section_callback' ), $options_group );
				
				$callback_type = ( isset( $option['type'] ) ? $option['type'] : "field_default_option" );
				add_settings_field( $option['name'].'_setting-id', $option['label'], array( $this, $callback_type ), $options_group, $options_group, $option );	
			}
		}
	}

	/**
	 * Settings page rendering it checks for active tab and replaces key with the related
	 * settings key. Uses the plugin_options_tabs method to render the tabs.
	 *
	 * @access public
	 * @return void
	 */		 
	public function plugin_options_page() {
		//global $songbook;
		$tab = isset( $_GET['tab'] ) ? sanitize_key($_GET['tab'] ) : $this->default_tab_key;
		if ( ! empty( $_GET['settings-updated'] )) {
			//flush_rewrite_rules();
			//$songbook->do_on_activation();		// add the active capabilities
			//UUC_Capabilities::songs_clean_inactive_capabilties();	// remove the inactive role capabilities
		}
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options-nonce', 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Wordpress by default doesn't include a section callback to send any section descriptive text 
	 * to the form.  This function uses section description based on the current section id being processed.
	 *
	 * @param section_passed.
	 * @access public
	 * @return void
	 */	
	public function hook_section_callback( $section_passed ){
		foreach ( self::$settings as $options_group => $section  ) {
			if (( $section_passed['id'] == $options_group) && ( ! empty( $section['description'] ))) {
				echo esc_html( self::$settings[$options_group]['description'] );	
			}
		}
	 }
	
	/**
	 * field_checkbox_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_checkbox_option( array $args  ) {
		$option   = $args['option'];
		$value = get_option( $option['name'] );
		?><label><input id="setting-<?php echo esc_html( $option['name'] ); ?>" name="<?php echo esc_html( $option['name'] ); ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> /> <?php echo esc_html( $option['cb_label'] ); ?></label><?php
		if ( ! empty( $option['desc'] ))
		echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';
	}

	
	

	/**
	 * field_site_list_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_site_list_option( array $option  ) {
		
		global $wpdb;
		
		$defaults = array(
					);
			
		$option = wp_parse_args( $option, $defaults );
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
		$sites[0] = '-None-';
		foreach ($blogs as $blog) {
			$sites[$blog->blog_id] = get_blog_option($blog->blog_id, 'blogname');
		}
		natsort($sites);
		
		?>
		<form action="<?php bloginfo('url'); ?>" method="get">
			<select id="setting-<?php echo esc_html( $option['name'] ); ?>" class="regular-text" name="<?php echo esc_html( $option['name'] ); ?>">
											<?php foreach( $sites as $site_id=>$site ) { ?>
												<option value="<?php echo $site_id; ?>" <?php selected( $site_id, $value ); ?>><?php echo $site; ?></option>
											<?php } ?>		
			</select>
				<class="description"> ( site_id:  <?php echo get_option('uuc_reference_site')?> )
		</form>


		<?php
		
		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';		
	}

	/**
	 * field_plugin_checkbox_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_plugin_checkbox_option( array $option  ) {

		$defaults = array(
					'filename' => $option['slug'],
					'plugin_main_file' => UUC_MYPLUGINNAME_PATH . $option['slug'] . '/' .  $option['slug'] . '.php',
					);
			
		$option = wp_parse_args( $option, $defaults );
		$value = get_option( $option['name'] );

		if ( is_plugin_active_for_network( $option['slug'] . '/' . $option['filename']  . '.php' )) {
			update_option( $option['name'], false );  // force option off to allow Network to deactivate the plugin
			?><label><input id="setting-<?php echo esc_html( $option['name'] ); ?>" name="<?php echo esc_html( $option['name'] ); ?>" type="checkbox" disabled="disabled" checked="checked"/> <?php
		} else {
			?><label><input id="setting-<?php echo esc_html( $option['name'] ); ?>" name="<?php echo esc_html( $option['name'] ); ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> /> <?php 
		}

		if ( ! file_exists( $option['plugin_main_file'] ) ) {
			echo esc_html__( 'Enable to prompt installation and force active.', 'user-upgrade-capability-text-domain' ) . ' ( ';
			if ( $value ) echo '  <a href="' . add_query_arg( 'page', TGM_Plugin_Activation::$instance->menu, admin_url( 'themes.php' ) ) . '">' .  esc_html__( "Install", 'user-upgrade-capability-text-domain' ) . " </a> | " ;
			
		} elseif ( is_plugin_active( $option['slug'] . '/' . $option['slug'] . '.php' ) &&  ! is_plugin_active_for_network( $option['slug'] . '/' . $option['slug'] . '.php' )) {
			echo esc_html__(  'Force Active', 'user-upgrade-capability-text-domain' ) . ' ( ';
			if ( ! $value ) echo '<a href="plugins.php?s=' . esc_html( $option['label'] )	 . '">' .  esc_html__( "Deactivate", 'user-upgrade-capability-text-domain' ) . "</a> | " ;	
		} else {
			echo esc_html__(  'Force Active', 'user-upgrade-capability-text-domain' ) . ' ( ';
		}
		echo ' <a href="http://wordpress.org/plugins/' . esc_html( $option['slug'] ) . '">' .  esc_html__( "wordpress.org", 'user-upgrade-capability-text-domain' ) . " </a> )" ;		
		?></label><?php
		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';
	}

	/**
	 * field_textarea_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_textarea_option( array $option  ) {

		$defaults = array( 	
					'columns' => "40",
					'rows' => "3",
					);
			
		$option = wp_parse_args( $option, $defaults );
		$value = get_option( $option['name'] );

		?><textarea id="setting-<?php echo esc_html( $option['name'] ); ?>" cols=<?php echo $option['columns']; ?> rows=<?php echo $option['rows']; ?> name="<?php echo esc_html( $option['name'] ); ?>" ><?php echo esc_textarea( $value ); ?></textarea><?php
		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';
	}

	/**
	 * field_select_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_select_option( array $option  ) {

		$defaults = array(
					);
			
		$option = wp_parse_args( $option, $defaults );
		$value = get_option( $option['name'] );

		?><select id="setting-<?php echo esc_html( $option['name'] ); ?>" class="regular-text" name="<?php echo esc_html( $option['name'] ); ?>"><?php
			foreach( $option['options'] as $key => $name )
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';
		?></select><?php

		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';
	}
	

	/**
	 * field_default_option 
	 *
	 * @param array of arguments to pass the option name to render the form field.
	 * @access public
	 * @return void
	 */
	public function field_default_option( array $option  ) {

		$defaults = array(
					);
			
		$option = wp_parse_args( $option, $defaults );
		$value = get_option( $option['name'] );

		?><input id="setting-<?php echo esc_html( $option['name'] ); ?>" class="regular-text" type="text" name="<?php echo esc_html( $option['name'] ); ?>" value="<?php esc_attr_e( $value ); ?>" /><?php

		if ( ! empty( $option['desc'] ))
			echo ' <p class="description">' . esc_html( $option['desc'] ) . '</p>';
			
	}
	
	/**
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one.
	 *
	 * @access public
	 * @return void
	 */
	public function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : $this->default_tab_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}
	
	
	/**
	 * install_plugins function.
	 *
	 * @access public
	 * @return array of plugins for installation via the TGM_Plugin_Activation class
	 */
	public static function install_plugins() {
		
		self::init_settings();
		$plugin_array = self::$settings['uuc_plugin_extension']['settings'];
		$plugins = array();
		
		foreach ( $plugin_array as $plugin ) {
		
			if ( get_option( $plugin['name'] ) ) {
				// change the array element key name from 'label' to 'name' for use by TGM Activation
				$plugin['option-name'] = $plugin['name'];
				$plugin['name'] = $plugin['label'];
				unset($plugin['label']);
				$plugins[] = $plugin;
			}
		}
		return $plugins; 
	}
}

?>