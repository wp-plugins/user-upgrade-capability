<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();
	
if ( is_multisite( ) ) {

    $blogs = wp_list_pluck( wp_get_sites(), 'blog_id' );

    if ( $blogs ) {
        foreach( $blogs as $blog ) {
            switch_to_blog( $blog );
            uuc_clean_database();
        }
        restore_current_blog();
    }
} else {
	uuc_clean_database();
}

// remove all network-wide transients
delete_site_transient( 'uuc_blog_list' );
delete_site_transient( 'uuc_active_plugins' );
	
		
// remove all database entries for currently active blog on uninstall.
function uuc_clean_database() {
		
		delete_option('uuc_plugin_version');
		delete_option('uuc_reference_site');
		delete_option('uuc_reference_key_capability');
		delete_option('uuc_additional_capabilties');
		delete_option('uuc_install_date');
		
		// plugin specific database entries
		delete_option('uuc_user-role-editor_plugin');
		delete_option('uuc_join-my-multisite_plugin');
		delete_option('uuc_blog-copier_plugin');
		
		delete_option('uuc_deactivate_user-role-editor');
		delete_option('uuc_deactivate_join-my-multisite');
		delete_option('uuc_deactivate_blog-copier');
		
		// user specific database entries
		delete_user_meta( get_current_user_id(), 'uuc_prompt_timeout', $meta_value );
		delete_user_meta( get_current_user_id(), 'uuc_start_date', $meta_value );
		delete_user_meta( get_current_user_id(), 'uuc_hide_notice', $meta_value );

}

?>