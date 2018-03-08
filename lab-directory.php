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

// $lab_directory_table = $wpdb->prefix . 'lab_directory';  //Import
// define( 'LAB_DIRECTORY_TABLE', $wpdb->prefix . 'lab_directory' ); //Import
// define( 'LAB_DIRECTORY_TEMPLATES', $wpdb->prefix . 'lab_directory_templates' );
define( 'LAB_DIRECTORY_PHOTOS_DIRECTORY', WP_CONTENT_DIR . "/uploads/lab-directory-photos/" );
define( 'LAB_DIRECTORY_TEMPLATES', WP_CONTENT_DIR . "/plugins/lab-directory/templates" );
define( 'LAB_DIRECTORY_LANGUAGES', WP_CONTENT_DIR . "/plugins/lab-directory/languages" );

require_once ( dirname( __FILE__ ) . '/classes/lab-directory-settings.php' );

require_once ( dirname( __FILE__ ) . '/includes/admin_form.php' );

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

function modify_the_link( $post_url, $post ) {
	return '/wp-content/plugins/lab-directory/view.php?id=' . $post->ID;
}
add_filter( 'lab_directory_staff', "modify_the_link", 10, 2 );