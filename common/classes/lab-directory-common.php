<?php
/* 
 * This class is used in Frontend and Admin
 */
class Lab_Directory_Common {

	// The url of ld main page containing [lab-directory] exact shortcode without any parameter
	static $main_ld_permalink =array();
	
	// Default language use in frontend
	static $default_post_language = '';  //fr_FR
	static $default_post_language_slug = ''; // 'fr'
	
	// url slug used for templates
	static $lab_directory_url_slugs =array();
		
	
	/*
	 * $permalink : post permalink or URL
	 * $slug staff_grid staff_list... staff
	 * $id taxonomy or lab_directory staff id
	 * $query_vars_only if true only return the end of the url containing query vars
	 */

	static function register_common_filters_and_actions() {
		
		self::$default_post_language = get_option('WPLANG')? get_option('WPLANG'): get_locale() ;
		self::$default_post_language_slug = substr(self::$default_post_language, 0, 2)  ;
		self::$lab_directory_url_slugs = get_option('lab_directory_url_slugs');
		
		add_action( 'init', array( 'Lab_Directory_Common', 'initiate_main_ld_permalink' ) );
		
		add_action( 'plugins_loaded', array( 'Lab_Directory_Common', 'load_lab_directory_textdomain' ) );
		
		
	}
	
	/*
	 * This function search for the unique posts (many language) having the exact shortcode [lab-directory]
	 */
	static function initiate_main_ld_permalink() {
		global $wpdb,$lang, $wp_query;
		self::$main_ld_permalink = array();
		$ld_posts = $wpdb->get_results("SELECT ID,guid FROM $wpdb->posts WHERE post_content like '%[lab-directory]%'");
		if (count(ldposts) == 0) {
			self::$main_ld_permalink = '';
			return;
		}
	
		if (count(ldposts) >= 1) {
			//Save first post in $main_ld_permalink[0] (in case lang is not found) but this can be in any languages!
			self::$main_ld_permalink[0]['ID']= $ld_posts[0]->ID;
			self::$main_ld_permalink[0]['permalink']= get_permalink($ld_posts[0]->ID);
		}
		self::$main_ld_permalink['query_string'] = '';
		$current_url = trim($_SERVER['SCRIPT_URI'], '/'). $_SERVER['QUERY_STRING'];
	
		// Polylang case
		if (function_exists (pll_languages_list) ) {
			$languages = pll_languages_list('slug');
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
		
	static function get_ld_permalink($slug='',$id='', $lang=0, $query_string_only= false) {
		$permalink = self::$main_ld_permalink[$lang]['permalink'];
		 
		$simple_url = (strpos($permalink, '?') !== false);
		if ($query_string_only) {
			$permalink = '';
		}
		if ($slug)  {
			if ($simple_url) {
				$permalink .=  '&'. self::$lab_directory_url_slugs[$slug];
			} else {
				// Add a / if it does not exist in permalink ( permalink structure set to 'numeric'
				$permalink = trim ($permalink, '/'). '/'. self::$lab_directory_url_slugs[$slug];
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
		load_plugin_textdomain( 'lab-directory',false, '/lab-directory/languages/' );
	}
	
	
}
