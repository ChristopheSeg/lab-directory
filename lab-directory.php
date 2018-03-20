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

// Load Common classes
require_once ( dirname( __FILE__ ) . '/common/classes/lab-directory-common.php' );
Lab_Directory_Common::register_common_filters_and_actions();

//TODO Mix Admin/Frontend content!!
require_once ( dirname( __FILE__ ) . '/classes/lab-directory.php' );
Lab_Directory::register_post_types();
Lab_Directory::set_default_meta_fields_if_necessary();

if ( is_admin() ) {
    // Load admin classes
	require_once ( dirname( __FILE__ ) . '/admin/classes/lab-directory-settings.php' );
	require_once ( dirname( __FILE__ ) . '/admin/classes/lab-directory-admin.php' );
	Lab_Directory_Admin::register_admin_menu_items();
	
	if ( Lab_Directory::show_import_message() ) {
		Lab_Directory_Admin::register_import_old_lab_directory_staff_message();
	}
	
} else {
	// Load Frontend classes
	require_once ( dirname( __FILE__ ) . '/public/classes/lab-directory-shortcode.php' );
	Lab_Directory_Shortcode::register_shortcode();
	require_once ( dirname( __FILE__ ) . '/public/classes/ld_widget_defenses.php' );
	
}



