<?php

/*
 * Plugin Name: Lab Directory
 * Plugin URI: http://www.nourl.yet
 * Description: Allows Wordpress to keep track of your lab_directory_staff directory for your website. Good for
 * small companies, etc.
 * Version: 0.1
 * Author: Christophe Seguinot
 * Author URI: http://www.nourl.yet
 */
global $wpdb;

// .../wp-content/plugins/lab-directory
define( 'LAB_DIRECTORY_DIR', dirname( __FILE__ ) );
define( 'LAB_DIRECTORY_TEMPLATES', LAB_DIRECTORY_DIR . "/templates" );
define( 'LAB_DIRECTORY_URL', plugins_url('', __FILE__ ) );

/* Always load admin menus and corresponding languages mo file
 * This also register post_type lab_Directory_Staff, add tags and rewrite rules
 */
 
require_once ( dirname( __FILE__ ) . '/common/classes/lab-directory-base.php' );
Lab_Directory_Base::register_admin_menu_items();

// Widget not splitted frontend/admin so considered as base
require_once ( dirname( __FILE__ ) . '/public/classes/ld_widget_defenses.php' );


if ( is_admin() ) {
	 
	// Common classes are not needed for non lad directory staff pages 
	//TODO conditional load for admin settings OR edit staff (need ad'hoc separation in 2 classes!! 
	if (Lab_Directory_Base::$load_admin_class) {
				
		// Load Common classes
		require_once ( dirname( __FILE__ ) . '/common/classes/lab-directory-common.php' );
		Lab_Directory_Common::register_common_filters_and_actions();
		
		// Load admin classes if in lab-directory menu (not used in others cases). 
    	require_once ( dirname( __FILE__ ) . '/admin/classes/lab-directory.php' );
    	Lab_Directory::register_actions_and_filters();
    	Lab_Directory::set_default_meta_fields_if_necessary();
    	
    	require_once ( dirname( __FILE__ ) . '/admin/classes/lab-directory-settings.php' );
    	require_once ( dirname( __FILE__ ) . '/admin/classes/lab-directory-admin.php' );
    	Lab_Directory_Admin::add_admin_actions();
    	
    	
    	if ( Lab_Directory::show_import_message() ) {
    		Lab_Directory_Admin::register_import_old_lab_directory_staff_message();
    	} 
    }
} else {
	// Load Common classes
	require_once ( dirname( __FILE__ ) . '/common/classes/lab-directory-common.php' );
	Lab_Directory_Common::register_common_filters_and_actions();

	// Load Frontend classes
	require_once ( dirname( __FILE__ ) . '/public/classes/lab-directory-shortcode.php' );
	Lab_Directory_Shortcode::register_shortcode();
	
}



