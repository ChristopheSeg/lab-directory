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

if ( is_admin() ) {
    // we are in admin mode
    echo "<br> TOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOT ADMIN";
} else {
	// Frontend mode 
	echo "<br> TOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOTOT FRONTEND".
"<br>templates: ". LAB_DIRECTORY_TEMPLATES. 
"<br>dirname: ". LAB_DIRECTORY_DIR. 
"<br>url: ". 	LAB_DIRECTORY_URL;
}
require_once ( dirname( __FILE__ ) . '/classes/lab-directory-settings.php' );

require_once ( dirname( __FILE__ ) . '/classes/lab-directory.php' );
require_once ( dirname( __FILE__ ) . '/classes/lab-directory-shortcode.php' );
require_once ( dirname( __FILE__ ) . '/classes/lab-directory-admin.php' );
require_once ( dirname( __FILE__ ) . '/classes/ld_widget_defenses.php' );

Lab_Directory::register_post_types();
Lab_Directory::set_default_meta_fields_if_necessary();
Lab_Directory_Admin::register_admin_menu_items();
Lab_Directory_Shortcode::register_shortcode();

if ( Lab_Directory::show_import_message() ) {
	Lab_Directory_Admin::register_import_old_lab_directory_staff_message();
}
