<?php
class Lab_Directory_Admin_Menus {
	
	static $load_admin_class = false;
	
	static function register_admin_menu_items() {
		
		// $load_admin_class is true if all admin class should be loaded
		
		// WHEN URL contains post_type=lab_directory_staff
		self::$load_admin_class = false !== strpos($_SERVER['REQUEST_URI'],'post_type=lab_directory_staff');
		if (!self::$load_admin_class) {
			if ( get_post_type($_GET['post']) == 'lab_directory_staff' ) {
				// WHEN post_type=lab_directory_staff
				self::$load_admin_class = true;
			}
		}
		
		add_action( 'admin_menu', array( 'Lab_Directory_Admin_Menus', 'add_admin_menu_items' ) );
		
		add_action( 'init', array( 'Lab_Directory_Admin_Menus', 'create_post_types' ) );
		
		// Add an action lmink in LAb-Directory extension menu
		add_filter( 'plugin_action_links_lab-directory/lab-directory.php',  array( 'Lab_Directory_Admin_Menus',  'lab_directory_add_action_links')  );
				
	}

	static function add_admin_menu_items() {
		
		$ld_admin_page = add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Settings', 'Settings', 'publish_posts',
			'lab-directory-settings', array( 'Lab_Directory_Admin', 'settings' ) ); 
		add_action('load-' . $ld_admin_page,  array( 'Lab_Directory_Admin', 'ld_admin_help_tab_settings'));

		$ld_admin_page = add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Taxonomies', 'Taxonomies', 'publish_posts',
			'lab-directory-taxonomies', array( 'Lab_Directory_Admin', 'taxonomies' ) );
		add_action('load-' . $ld_admin_page, array( 'Lab_Directory_Admin', 'ld_admin_help_tab_taxonomies'));

		$ld_admin_page = add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Translations', 'Translations', 'publish_posts',
			'lab-directory-translations', array( 'Lab_Directory_Admin', 'translations' ) );
		add_action('load-' . $ld_admin_page, array( 'Lab_Directory_Admin',  'ld_admin_help_tab_translations'));

		$ld_admin_page = add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Help', 'Help', 'publish_posts',
			'lab-directory-help', array( 'Lab_Directory_Admin', 'help' ) );

		$ld_admin_page = add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Import', 'Import Old Staff', 'publish_posts',
			'lab-directory-import', array( 'Lab_Directory_Admin', 'import' ) );

		add_action('load-' . $ld_admin_page, array( 'Lab_Directory_Admin', 'ld_admin_help_tab_import'));

		add_action('load-post-new.php', array( 'Lab_Directory_Admin', 'ld_admin_help_add_new_staff'));
		add_action('load-edit-tags.php', array( 'Lab_Directory_Admin', 'ld_admin_help_edit_taxonomies'));
		add_action('load-post.php', array( 'Lab_Directory_Admin', 'ld_admin_help_edit_staff'));
		
	}
	
	static function create_post_types() {
	
		register_post_type(
			'lab_directory_staff',
			array(
				'labels' => array(
					'name' => __( 'Lab Directory staff', 'lab-directory' ),
					'singular_name' => __( 'Staff', 'lab-directory' ),
					'add_new' => __( 'New staff', 'lab-directory' ),
					'add_new_item' => __( 'Add a new staff', 'lab-directory' ),
					'edit_item' => __( 'Edit staff details', 'lab-directory' ),
					'new_item' => __( 'New staff', 'lab-directory' ),
					'view_item' => _x( 'View staff', 'single', 'lab-directory' ),
					'view_items' => _x( 'View staff', 'plural', 'lab-directory' ),
					'search_items' => __( 'Search staff', 'lab-directory' ),
					'not_found' => __( 'No staff found.', 'lab-directory' ),
					'not_found_in_trash' => __( 'No staff in Trash.', 'lab-directory' ),
					'all_items' => __( 'Staff list', 'lab-directory' ),
					'featured_image' => __( 'Staff photo', 'lab-directory' ),
					'set_featured_image' => __( 'Set staff photo', 'lab-directory' ),
					'remove_featured_image' => __( 'Remove staff photo', 'lab-directory' ),
					'use_featured_image' => __( 'Use a staff photo', 'lab-directory' ),
					'filter_items_list' => __( 'Filter staff list', 'lab-directory' ),
					'items_list_navigation' => __( 'Navigation in staff list', 'lab-directory' ),
					'items_list' => __( 'Staff list', 'lab-directory' ) ),
	
				'supports' => array( 'title',
					// 'editor',
					'thumbnail' ),  // disabled for ldap=1
	
				'public' => true,
				'has_archive' => false,
				'menu_icon' => 'dashicons-id',
			) );
	}
	/*
	 * Add a settings action link for Lab-Directory in Admin Extension list
	 */
	static function lab_directory_add_action_links ( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'edit.php?post_type=lab_directory_staff&page=lab-directory-settings' ) . '">Settings</a>',
		);
		return array_merge( $links, $mylinks );
	}
	

	

}


