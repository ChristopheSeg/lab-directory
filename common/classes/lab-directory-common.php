<?php
/* 
 * This class is used in Frontend and Admin
 */
class Lab_Directory_Common {

	// The url of ld main page containing [lab-directory] exact shortcode without any parameter
	static $main_ld_permalink ='';
	
	// Default language use in frontend
	static $default_post_language = '';  //fr_FR
	static $default_post_language_slug = ''; // 'fr'
	
	static $staff_meta_fields = null;
	
	static $acronyms = null;
	
	// Translation of meta_fields are save here to be reloaded (refreshed) each time without saving in Database
	static $default_meta_field_names = array();
		
	
	/*
	 * $permalink : post permalink or URL
	 * $slug staff_grid staff_list... staff
	 * $id taxonomy or lab_directory staff id
	 * $query_vars_only if true only return the end of the url containing query vars
	 */

	static function register_common_filters_and_actions() {
		
		self::$default_post_language = get_option('WPLANG')? get_option('WPLANG'): get_locale() ;
		self::$default_post_language_slug = substr(self::$default_post_language, 0, 2)  ;
		self::$staff_meta_fields = get_option( 'lab_directory_staff_meta_fields', false );
		if (false ===self::$staff_meta_fields ) {
			self::$staff_meta_fields = self::get_default_meta_fields();
		}
		
		// add filter TODO Yoast SEO also use a filter!! 
		// add_filter('pre_get_document_title', 'change_my_title', 999, 1 );
		// Our function
		function change_my_title($title) {
			return 'My new title';
		}
		
		add_action( 'init', array( 'Lab_Directory_Common', 'initiate_main_ld_permalink' ) );
		add_action( 'init', array( 'Lab_Directory_Common', 'create_lab_directory_staff_taxonomies' ) );
		
		if (! defined( 'POLYLANG_DIR' ) )
			// Unneccesary if pll used!!
			add_action( 'plugins_loaded', array( 'Lab_Directory_Common', 'load_lab_directory_textdomain' ) );
		
		// Load text_domain after Polylang removed filter 'load_textdomain_mofile'
		add_action( 'pll_translate_labels', array( 'Lab_Directory_Common', 'load_lab_directory_textdomain' ) );
		
		add_action( 'plugins_loaded', array( 'Lab_Directory_Common', 'initiate_staff_meta_fields' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory_Common', 'load_ld_acronyms' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory_Common', 'initiate_default_meta_field_names' ) );
				
		add_action( 'wp_enqueue_scripts', array( 'Lab_Directory_Common', 'register_fontawesome' ) );
		add_action( 'admin_enqueue_scripts', array( 'Lab_Directory_Common', 'register_fontawesome' ) );
		
		add_filter( 'post_type_link', array( 'Lab_Directory_Common', 'lab_directory_post_type_link'), 10, 2 );

		add_action( 'admin_bar_menu', array( 'Lab_Directory_Common', 'lab_directory_admin_bar_render' ), 80 );

	}
	

		
	/*
	 * This function search for the unique posts (many language) having the exact shortcode [lab-directory]
	 */
	static function initiate_main_ld_permalink() {
		global $wpdb,$lang, $wp_query;
		self::$main_ld_permalink = array();
		$ld_posts = $wpdb->get_results("SELECT ID,guid FROM $wpdb->posts WHERE post_status = 'publish' AND post_content like '%[lab-directory]%'");
		if (count($ld_posts) == 0) {
			self::$main_ld_permalink = '';
			return;
		}
	
		if (count($ld_posts) >= 1) {
			//Save first lab-directory post in $main_ld_permalink[0] (in case lang is not found) but this can be in any languages!
			self::$main_ld_permalink['count'] = count($ld_posts); 
			self::$main_ld_permalink[0]['ID']= $ld_posts[0]->ID;
			self::$main_ld_permalink[0]['permalink']= get_permalink($ld_posts[0]->ID);
		}
		self::$main_ld_permalink['query_string'] = '';
		$current_url = get_site_url() . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];

		// Polylang case
		if (function_exists ('pll_languages_list') ) {
			$languages = pll_languages_list(array('fields' => 'locale'));

			foreach ($languages as $language){
				self::$main_ld_permalink[$language]['ID'] = pll_get_post(self::$main_ld_permalink[0]['ID'], $language);
				self::$main_ld_permalink[$language]['permalink'] = get_permalink(self::$main_ld_permalink[$language]['ID'] );
				if ( false !== strpos( $current_url, self::$main_ld_permalink[$language]['permalink'] ) ) {
					self::$main_ld_permalink['query_string'] = str_replace(
						self::$main_ld_permalink[$language]['permalink'], '', $current_url);
				}
			}
		}
		//TODO add wpml compatibility
	}
		
	/* 
	 * $lang =0 : search for default language permalink
	 * $lang='' : search for $lang permalink
	 */
	 
	 static function get_ld_permalink($slug='',$id='', $lang=0, $query_string_only= false) {
		
		if ( $lang == '') {
			// TODO plugin WPML support! 
			$lang = pll_current_language('locale'); 
		}

		
		if (isset(self::$main_ld_permalink[$lang]['permalink']) ){
			$permalink = self::$main_ld_permalink[$lang]['permalink'];
		} elseif (isset(self::$main_ld_permalink[0]['permalink']) ){
			$permalink = self::$main_ld_permalink[0]['permalink'];
		}
		if (! $permalink) {return ' '; }

		
		$simple_url = (strpos($permalink, '?') !== false);
		if ($query_string_only) {
			$permalink = '';
		}
		if ($slug)  {
			if ($simple_url) {
				$permalink .=  '&'. Lab_Directory_Base::$lab_directory_url_slugs[$slug];
			} else {
				// Add a / if it does not exist in permalink ( permalink structure set to 'numeric'
				$permalink = trim ($permalink, '/'). '/'. Lab_Directory_Base::$lab_directory_url_slugs[$slug];
			}
	
			if ($id)  {
				$permalink .=  $simple_url ?
				'='. $id:
				'/' . $id;
			}
		}
		// echo "<br> xxx=$lang perma=$permalink";
		 
		return $permalink;
	
	}
	
	
	static function load_lab_directory_textdomain() {
		
		$domain = 'lab-directory';
		$locale = apply_filters( 'plugin_locale', is_admin() ? get_user_locale() : get_locale(), $domain );
		
		$mofile = $domain . '-common-' . $locale . '.mo';
		
		// Try to load from the languages directory first.
		if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
			return;
		}
		
		// Else load from language dir
		return load_textdomain( $domain, LAB_DIRECTORY_DIR . '/languages/' . $mofile );
		
	}
	
	static function get_lab_directory_studying_levels() {
	
		// Define the list of studying levels and their translation
		$studying_levels = array(
				
			'L1' => __( 'L1 (Bachelor  1st year)', 'lab-directory' ),
			'L2' => __( 'L2 (Bachelor 2nd year)', 'lab-directory' ),
			'L3' => __( 'L3 (Bachelor 3rd year)', 'lab-directory' ),
			'M1' => __( 'M1 (Master 1st year)', 'lab-directory' ),
			'M2' => __( 'M2 (Master 2nd year)', 'lab-directory' ),
			'ING' => __( 'Engineering School', 'lab-directory' ) );
		return $studying_levels;
	}
	
	static function get_lab_directory_jury_functions() {
	
		// Define the list of function use in HDR and PHD jury and their translation
		$jury_functions = array(
			'guarantor' => __( 'HDR guarantor', 'lab-directory' ),
			'chairman' => __( 'President', 'lab-directory' ),
			'chairwoman' => __( 'President', 'lab-directory' ),
			'director' => __( 'Directeur', 'lab-directory' ),
			'directress' => __( 'Directrice', 'lab-directory' ),
			'directors' => __( 'Directeurs', 'lab-directory' ),
			'supervisors' => __( 'Supervisors', 'lab-directory' ),
			'supervisor' => __( 'Supervisor', 'lab-directory' ),
			'examiner' => _x( 'Examiner', 'male', 'lab-directory' ),
			'examiner_f' => _x( 'Examiner', 'female', 'lab-directory' ),
			'examiners' => __( 'Examiners', 'lab-directory' ),
			'referee' => _x( 'Referee', 'male', 'lab-directory' ),
			'referee_f' => _x( 'Referee', 'female', 'lab-directory' ),
			'referees' => __( 'Rapporters', 'lab-directory' ),
			'invited' => _x( 'Invited', 'male', 'lab-directory' ),
			'invited_f' => _x( 'Invited', 'female', 'lab-directory' ),
			'invited_p' => _x( 'Invited', 'plural', 'lab-directory' ) );
		return $jury_functions;
	}
	
	static function initiate_staff_meta_fields() {
		// self::$staff_meta_fields = get_option( 'lab_directory_staff_meta_fields' );
	
		// Add acronym tooltip in each metafield parameters
		self::load_ld_acronyms();
		if (empty(self::$acronyms)) return; 
		
		foreach ( self::$acronyms as $acronym ) {
			self::$staff_meta_fields[$acronym['slug']]['acronyms'][] = $acronym;
		}
	}
	
	static function load_ld_acronyms() {
		self::$acronyms = get_option( 'lab_directory_translations_acronyms' );
	}
	
	/*
	 * $all = true retrieve all taxonomies
	 * $all = false retrieve only used ones
	 */
	static function lab_directory_get_taxonomies( $all = false ) {
		$taxonomies = array();
	
		$t1 = get_option( 'lab_directory_use_taxonomy1' );
		$t2 = get_option( 'lab_directory_use_taxonomy2' );
		
		
		if ( $t1 or $all ) {
			// Taxonomy 1
			$taxonomies['ld_taxonomy_team'] = array(
				'hierarchical' => true,
				'labels' => array(
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'name' => _x( 'Staff Teams', '1st taxonomy general name', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'singular_name' => _x( 'Staff Team', '1st taxonomy singular name', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'search_items' => __( 'Search Staff Teams', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'all_items' => __( 'All Staff Teams', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'parent_item' => __( 'Parent Staff Team', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'parent_item_colon' => __( 'Parent Staff Team :', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'edit_item' => __( 'Edit Staff Team', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'update_item' => __( 'Update Staff Team', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'add_new_item' => __( 'Add New Staff Team', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'new_item_name' => __( 'New Staff Team Name', 'lab-directory' ),
					/* translators: this is related to taxonomy-1 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'menu_name' => __( 'Staff Teams', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'team_manager' => __( 'Team manager', 'lab-directory' ) ),
				'show_admin_column' => true,
 				'rewrite' => array(
					'slug' => 'lab_directory_staff-teams',
					'with_front' => false,
					'hierarchical' => true ) );
			
			// TODO adjust access rights (remove staff photo metabox)
			if (!current_user_can( 'administrator' )) {
				$taxonomies['ld_taxonomy_team']['meta_box_cb']=false;
			}
				
		}
		if ( $t2 or $all ) {
			// Taxonomy 2
			$taxonomies['ld_taxonomy_laboratory'] = array(
				'hierarchical' => true,
				'labels' => array(
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'name' => _x( 'Laboratories', '2nd taxonomy general name', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'singular_name' => _x( 'Laboratory', '2nd taxonomy singular name', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'search_items' => __( 'Search Laboratories', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'all_items' => __( 'All Laboratories', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'parent_item' => __( 'Parent Laboratory', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'parent_item_colon' => __( 'Parent Laboratory :', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'edit_item' => __( 'Edit Laboratory', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'update_item' => __( 'Update Laboratory', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'add_new_item' => __( 'Add Laboratory', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'new_item_name' => __( 'New Laboratory Name', 'lab-directory' ),
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'menu_name' => __( 'Staff Laboratories', 'lab-directory' ) ,
					/* translators: this is related to taxonomy-2 messages. This translation could be overrided depending on Lab-Directory settings..  */
					'laboratory_manager' => __( 'Laboratory manager', 'lab-directory' ) ),
				'rewrite' => array(
					'slug' => 'lab_directory_staff-laboratories',
					'with_front' => false,
					'hierarchical' => true ) );
 				
			// TODO adjust access rights (remove staff photo metabox)
			if (!current_user_can( 'administrator' )) {
				$taxonomies['ld_taxonomy_laboratory']['meta_box_cb']=false;
			}
				
		}
		return $taxonomies;
	}
	
	static function lab_directory_post_type_link( $url, $post ) {
		if ( 'lab_directory_staff' == get_post_type( $post ) ) {
			$url = Lab_Directory_Common::get_ld_permalink('staff',$post->post_name, '', false);
		}
		return $url;
	}
	


	/*
	 * This function convert a value depending on its multivalue type
	 */
	static function ld_value_to_something( &$value = false, $multivalue = false, $to = 'display' ) {
		// TODO common function for admin/frontend
		switch ( $to ) {
			case 'display' :
				// prepare metafield value for displaying ( with <br> instead fo line breaks)
				if ( ! $value ) {
					return;
				}
				switch ( $multivalue ) {
					case 'SV' :
						// nothing to do
						break;
					case 'CR' :
					case 'MV' :
						$value = nl2br( $value );
						break;
					case ',' :
						$value = str_replace( ',', '<br />', ( $value ) );
						break;
					case ';' :
						$value = str_replace( ';', '<br />', ( $value ) );
						break;
					case '|' :
						$value = str_replace( '|', '<br />', ( $value ) );
						break;
					case '/' :
						$value = str_replace( '/', '<br />', ( $value ) );
						break;
				}
				return;
				break;
			case 'array' :
				if ( ! $value ) {
					$value = array();
					return;
				}
				switch ( $multivalue ) {
					case 'special' :
					case 'SV' :
						$value = array( $value );
						break;
					case 'MV' :
					case ';' :
						$value = explode( ';', $value );
						break;
					case ',' :
						$value = explode( ',', $value );
						break;
					case '|' :
						$value = explode( '|', $value );
						break;
					case '/' :
						$value = explode( '/', $value );
						break;
					case 'CR' :
						$value = explode( "\n", $value );
						break;
				}
				return;
				break;
		}
		return;
	}
	
	static function ld_network_icon( $key ) {
		switch ( $key ) {
			case 'orcid' :
				return '<img class="fa" src="' . LAB_DIRECTORY_URL . '/common/images/academia.png" />';
				break;
			case 'academia' :
				return '<img class="fa" src="' . LAB_DIRECTORY_URL . '/common/images/orcid_32x32.png" />';
				break;
			case 'research-gate' :
				return '<img class="fa" src="' . LAB_DIRECTORY_URL . '/common/images/research_gate.png" />';
				break;
			default :
				return '<i class="fa fa-' . $key . '"></i>';
		}
	}
	static function initiate_default_meta_field_names() {
		if (is_admin() ) {
			$lang0 = ' (' .  get_locale() . ')';
		} else {
			// Do not display lang_i on frontend 
			$lang0 = '';
		}
		$lang1 = ' (' . get_option( 'lab_directory_lang1', true ) . ')';
		$lang2 = ' (' . get_option( 'lab_directory_lang2', true ) . ')';
	
		self::$default_meta_field_names = array(
			'firstname' => __( 'Firstname', 'lab-directory' ),
			'name' => __( 'Name', 'lab-directory' ),
			'position' => __( 'Position', 'lab-directory' ),
			'login' => __( 'Login', 'lab-directory' ),
			'wp_user_id' => __( 'Wordpress user ID', 'lab-directory' ),
			'mails' => __( 'Mail', 'lab-directory' ),
			'bio' => __( 'Biography', 'lab-directory' ),
			'other_mails' => __( 'Other mails', 'lab-directory' ),
			'idhal' => __( 'ID HAL', 'lab-directory' ),
			'photo_url' => __( 'Photo URL', 'lab-directory' ),
			'webpage' => __( 'Professionnal webpage', 'lab-directory' ),
			'social_network' => __( 'Social Network', 'lab-directory' ),
			'function' => __( 'Function', 'lab-directory' ),
			'title' => __( 'Title', 'lab-directory' ),
			'phone_number' => __( 'Phone number', 'lab-directory' ),
			'fax_number' => __( 'Fax number', 'lab-directory' ),
			'office' => __( 'Office', 'lab-directory' ),
			'team' => __( 'Team', 'lab-directory' ),
			'exit_date' => __( 'End activity date', 'lab-directory' ),
			'hdr_subject' => __( 'HDR subject', 'lab-directory' ) . $lang0,
			'hdr_subject_lang1' => __( 'HDR subject', 'lab-directory' ) . $lang1,
			'hdr_subject_lang2' => __( 'HDR subject', 'lab-directory' ) . $lang2,
			'hdr_date' => __( 'HDR defense date', 'lab-directory' ),
			'hdr_location' => __( 'HDR defense location', 'lab-directory' ),
			'hdr_url' => __( 'HDR document URL', 'lab-directory' ),
			'hdr_summary_url' => __( 'HDR summary URL', 'lab-directory' ),
			'hdr_jury' => __( 'HDR jury', 'lab-directory' ),
			'hdr_resume' => __( 'HDR resume', 'lab-directory' ) . $lang0,
			'hdr_resume_lang1' => __( 'HDR resume', 'lab-directory' ) . $lang1,
			'hdr_resume_lang2' => __( 'HDR resume', 'lab-directory' ) . $lang2,
			'phd_start_date' => __( 'PHD start date', 'lab-directory' ),
			'phd_end_date' => __( 'PHD end date', 'lab-directory' ),
			'phd_subject' => __( 'PHD subject', 'lab-directory' ) . $lang0,
			'phd_subject_lang1' => __( 'PHD subject', 'lab-directory' ) . $lang1,
			'phd_subject_lang2' => __( 'PHD subject', 'lab-directory' ) . $lang2,
			'phd_date' => __( 'PHD defense date', 'lab-directory' ),
			'phd_location' => __( 'PHD defense location', 'lab-directory' ),
			'phd_jury' => __( 'PHD jury', 'lab-directory' ),
			'phd_url' => __( 'PHD document URL', 'lab-directory' ),
			'phd_summary_url' => __( 'PHD summary URL', 'lab-directory' ),
			'phd_resume' => __( 'PHD resume', 'lab-directory' ) . $lang0,
			'phd_resume_lang1' => __( 'PHD resume', 'lab-directory' ) . $lang1,
			'phd_resume_lang2' => __( 'PHD resume', 'lab-directory' ) . $lang2,
			'post_doc_start_date' => __( 'Post Doct. start date', 'lab-directory' ),
			'post_doc_end_date' => __( 'Post Doct. end date', 'lab-directory' ),
			'post_doc_subject' => __( 'Post Doct. subject', 'lab-directory' ) . $lang0,
			'post_doc_subject_lang1' => __( 'Post Doct. subject', 'lab-directory' ) . $lang1,
			'post_doc_subject_lang2' => __( 'Post Doct. subject', 'lab-directory' ) . $lang2,
			'internship_start_date' => __( 'Internship start date', 'lab-directory' ),
			'internship_end_date' => __( 'Internship end date', 'lab-directory' ),
			'internship_subject' => __( 'Internship subject', 'lab-directory' ) . $lang0,
			'internship_subject_lang1' => __( 'Internship subject', 'lab-directory' ) . $lang1,
			'internship_subject_lang2' => __( 'Internship subject', 'lab-directory' ) . $lang2,
			'internship_resume' => __( 'Internship resume', 'lab-directory' ) . $lang0,
			'internship_resume_lang1' => __( 'Internship resume', 'lab-directory' ) . $lang1,
			'internship_resume_lang2' => __( 'Internship resume', 'lab-directory' ) . $lang2,
			'studying_school' => __( 'Trainee Studying school', 'lab-directory' ),
			'studying_level' => __( 'Trainee Studying level', 'lab-directory' ),
			'invitation_start_date' => __( 'Invitation Start date', 'lab-directory' ),
			'invitation_end_date' => __( 'Invitation End date', 'lab-directory' ),
			'invitation_goal' => __( 'Invitation goal', 'lab-directory' ) . $lang0,
			'invitation_goal_lang1' => __( 'Invitation goal', 'lab-directory' ) . $lang1,
			'invitation_goal_lang2' => __( 'Invitation goal', 'lab-directory' ) . $lang2,
			'invited_position' => __( 'Contractant Position', 'lab-directory' ),
			'invited_origin' => __( 'Invited origin', 'lab-directory' ),
			/* translators Fixed term contract information */
			'cdd_start_date' => __( 'Contract start date', 'lab-directory' ),
			/* translators Fixed term contract information */
			'cdd_end_date' => __( 'Contract end date', 'lab-directory' ),
			/* translators Fixed term contract information */
			'cdd_goal' => __( 'Contract goal', 'lab-directory' ) . $lang0,
			'cdd_goal_lang1' => __( 'Contract goal', 'lab-directory' ) . $lang1,
			'cdd_goal_lang2' => __( 'Contract goal', 'lab-directory' ) . $lang2,
			/* translators Fixed term contract information */
			'cdd_position' => __( 'Occupied position', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_1' => __( 'custom_field_1', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_2' => __( 'custom_field_2', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_3' => __( 'custom_field_3', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_4' => __( 'custom_field_4', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_5' => __( 'custom_field_5', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_6' => __( 'custom_field_6', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_7' => __( 'custom_field_7', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_8' => __( 'custom_field_8', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_9' => __( 'custom_field_9', 'lab-directory' ),
			/* translators: Do not translate.  Translation must be set in Lab Directory backend depending on custom fields usage. */
			'custom_field_10' => __( 'custom_field_10', 'lab-directory' ),
				
			// Others shortcodes requiring translation
			'name_firstname' => __( 'Name Firstname', 'lab-directory' ),
			'firstname_name' => __( 'Firstname Name', 'lab-directory' ),
			'social_link' =>  __( 'Social link', 'lab-directory' ),
			'phd_online' =>  __( 'PHD online', 'lab-directory' ),
			'hdr_online' =>  __( 'HDR online', 'lab-directory' ),
			'phd_link' => __( 'PHD page', 'lab-directory' ),
			'hdr_link' => __( 'HDR page', 'lab-directory' ),
		);
	}
	
	
	static function register_fontawesome() {
		// Used for social icons (Some plugin may load  (have already loaded) another fontawesome but this can't be avoided)
		wp_register_style(
			'font-awesome',
			'//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
			array(),
			'4.0.3' );
	}
	
	static function create_lab_directory_staff_taxonomies() {
		$taxonomies = self::lab_directory_get_taxonomies();
		foreach ( $taxonomies as $key => $taxonomie ) {
			register_taxonomy( $key, 'lab_directory_staff', $taxonomie );
		}
	}
	
	// add links/menus to the admin bar '<span class="dashicons dashicons-edit"></span>'.
	static function lab_directory_admin_bar_render($wp_admin_bar) {
	
		if (isset(Lab_Directory_Common::$main_ld_permalink['edit_staff_url']) AND Lab_Directory_Common::$main_ld_permalink['edit_staff_url']) {
			$wp_admin_bar->add_node( array(
				'parent' => false, // use 'false' for a root menu, or pass the ID of the parent menu
				'id' => 'editstaff', // this remove the previous edit link
				'title' =>  __('Edit staff profile'), // link title
				'href' => Lab_Directory_Common::$main_ld_permalink['edit_staff_url'], // name of file
				'meta' => array('class' => 'wp-admin-bar-edit') // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
			));
		}
	}

}
	
	
