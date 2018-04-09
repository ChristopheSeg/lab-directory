<?php
class Lab_Directory_Base {
	
	static $load_admin_class = false;
	// url slug used for templates
	static $lab_directory_url_slugs =array();
	
	static function register_admin_menu_items() {
		
		// $load_admin_class is true if all admin class should be loaded
		
		// WHEN URL contains post_type=lab_directory_staff
		self::$load_admin_class = false !== strpos($_SERVER['REQUEST_URI'],'post_type=lab_directory_staff');
		
		// search post_type in $_POST (case saving a form)
		if (!self::$load_admin_class) {
			if ( $_POST AND isset($_POST['post_type']) AND ($_POST['post_type'] == 'lab_directory_staff') ) {
				// WHEN post_type=lab_directory_staff
				self::$load_admin_class = true;
			}
		}
		// search post_type in $_GET (case opening an admin page)
		if (!self::$load_admin_class) {
			if ( $_GET AND isset($_GET['post']) AND (get_post_type($_GET['post']) == 'lab_directory_staff' ) ) {
				// WHEN post_type=lab_directory_staff
				self::$load_admin_class = true;
			}
		}
		add_action( 'init', array( 'Lab_Directory_Base', 'register_ld_post_type' ) );
				
		add_action( 'admin_menu', array( 'Lab_Directory_Base', 'add_admin_menu_items' ) );
		
		// Load text_domain for admin menus
		add_action( 'plugins_loaded', array( 'Lab_Directory_Base', 'load_Lab_Directory_Base_textdomain' ) );
		
		// Add an action lmink in Lab-Directory extension menu
		add_filter( 'plugin_action_links_lab-directory/lab-directory.php',  array( 'Lab_Directory_Base',  'lab_directory_add_action_links')  );
		
		// IMPORTANT Add Query_vars and Tags here. That it used when flushing permalink outside lab-directory
		self::$lab_directory_url_slugs = get_option('lab_directory_url_slugs');
		add_filter( 'query_vars', array( 'Lab_Directory_Base', 'lab_directory_add_query_vars' ) );
		add_action( 'init', array( 'Lab_Directory_Base', 'lab_directory_add_rewrite_tags' ) , 10, 0);
		
	}

	/* 
	 * Register ld_post_type
	 */
	static function register_ld_post_type() {
	
		register_post_type(
			'lab_directory_staff',
			array(
				'labels' => array(
					/* translators: This is the plugin main menu name appearing in admin list. (if possible use less than 20 character)*/
					'name' => __( 'Lab Directory staff', 'lab-directory' ),
					'singular_name' => __( 'Staff', 'lab-directory' ),
					'add_new' => __( 'New staff', 'lab-directory' ),
					'add_new_item' => __( 'Add a new staff', 'lab-directory' ),
					'edit_item' => __( 'Edit staff profile', 'lab-directory' ),
					'new_item' => __( 'New staff', 'lab-directory' ),
					'view_item' => _x( 'View staff profile', 'single', 'lab-directory' ),
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
	
				'supports' => array( /* 'title', 'editor', */ 'thumbnail' ),  // disabled for ldap=1
	
				'public' => true,
				'has_archive' => false,
				'menu_icon' => 'dashicons-id',
			) );
	}
	static function add_admin_menu_items() {
		
		/* About add_action: Lab_Directory_Admin is not loaded, 
		 * but these action are only usefull when Lab_Directory_Admin will be loaded
		 */
		
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
	
	static function load_Lab_Directory_Base_textdomain() {
		
		$domain = 'lab-directory'; 
		$locale = apply_filters( 'plugin_locale', is_admin() ? get_user_locale() : get_locale(), $domain );
		
		$mofile = $domain . '-admin_menus-' . $locale . '.mo';
		
		// Try to load from the languages directory first.
		if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
			return;
		}
		
		// Else load from language dir 
		return load_textdomain( $domain, LAB_DIRECTORY_DIR . '/languages/' . $mofile );
	}
	
	static function lab_directory_add_query_vars( $qvars ) {
	
		foreach (self::$lab_directory_url_slugs as $slug => $slug_replacement) {
			// Why !! Without this test, query vars can be registered twice !!!
			if (! in_array($slug_replacement, $qvars) ) $qvars[] = $slug_replacement;
		}
	
		return $qvars;
	}
	
	static function lab_directory_add_rewrite_tags() {
	
		foreach (self::$lab_directory_url_slugs as $slug => $slug_replacement) {
			add_rewrite_tag("%$slug_replacement%", '([^&/]+)');
			add_rewrite_endpoint( $slug_replacement, EP_PERMALINK | EP_PAGES  );
		}
	}
	

}


