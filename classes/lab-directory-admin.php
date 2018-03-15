<?php

class Lab_Directory_Admin {
	static function register_admin_menu_items() {
		add_action( 'admin_menu', array( 'Lab_Directory_Admin', 'add_admin_menu_items' ) );
		
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
		
		// ()
		add_action('load-post-new.php', array( 'Lab_Directory_Admin', 'ld_admin_help_add_new_staff'));
		add_action('load-edit-tags.php', array( 'Lab_Directory_Admin', 'ld_admin_help_edit_taxonomies'));
		add_action('load-post.php', array( 'Lab_Directory_Admin', 'ld_admin_help_edit_staff'));
		
	}

	static function ld_admin_help_tab_settings() {
		$screen = get_current_screen();
		$current_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'general';
		
		$tabs = array(
			'general'   => __( 'General', 'lab-directory' ),
			'capabilities' => __('Permissions', 'lab-directory' ),
			'ldap'   => __( 'LDAP server', 'lab-directory' ),
			'groups'   => __( 'Groups of fields', 'lab-directory' ),
			'fields'  => __( 'Meta fields', 'lab-directory' ),
			'test_sync'   => __( 'LDAP sync', 'lab-directory' ),
			'templates'   => __( 'Templates', 'lab-directory' ),
			);
		$screen = get_current_screen();
		
		// When using several tab, use unique IDs ! 
		switch ($current_tab) {
			case 'general':
				$content = '<p>' . __('These settings should be accessible only to authorized webmasters. It is used to activate/unactivate LDAP syncing, set taxonomies and social networks in use. ','lab-directory') . '</p>';
				$screen->add_help_tab( array(
						'id'	=> 'overview',
						'title'	=> $tabs[$current_tab],
						'content' => $content));
				$content = '<p><strong>' . __('Translation for subject, resume and goal metafields','lab-directory') . '</strong></p>';
				$content .= '<p>' . __('All metafields are single language without any reliable possibility to translate their content. In order to internationalise your staff directory, Lab-Directory propose 2 other languages for field corresponding to: subject, resume, and goal. They can be used for example to show an english version of a PHD subject on staff pages when using multiple language website.','lab-directory') . '</p>';
				$content .= '<p>' . __('The idea of subject, resume and goal metafields "translation" is to define 3 languages "locale" the main language of your website and two other laguages "language 1" and "language 2" (_lang1 and _lang2 suffix are added to multiple languages fields). As an example, PHD_subject, PHD_subject_lang1 and PHD_subject_lang2 represent a PHD subject given in 3 possible languages.','lab-directory') . '</p>';
				$content .= '<p>' . __('As most webmaster knows, when using several languages content on a website, most of the time people give you this content in one (or zero!) language. In order to be as efficient as possible, priority rules are defined for these content.','lab-directory') . '</p>';

				$content .= '<p><strong>' . __('All metafield with lang1 and lang2 suffix have a <code>translate</code> parameter','lab-directory') . '</strong></p>';
				$content .= '<ul style ="padding-left:15px;">';
				$content .= '<li>' . __('Without this parameter (or <code>translate=true</code>) give one translation using the preceeding rule: <code>[PHD_subject]</code> will be rendered as one of the 3 possible existing values depending on lang1 and lang2 usage and browsed page language.','lab-directory') . '</li>';
				$content .= '<li>' . __('Use the parameter <code>[translate=false]</code> in shortcode to force language<code>[PHD_subject translate=false]</code> will be rendered as PHD_subject content (*) and <code>[PHD_subject_lang2 translate=false]</code> as PHD_subject_lang2 content (*) . (*) only if they exist!)','lab-directory') . '</li>';
				$content .= '<li>' . __('Use <code>[translate=all]</code> to display 1 to 3 translations of a field when they exist (they will appear according to the ordering rules defined above)','lab-directory') . '</li>';
				$content .= '<li>' . __('Please note that <code>[PHD_subject  translate=yyy]</code> and <code>[PHD_subject_xxx  translate=yyy]</code> are equivalent if yyy is not equal to false','lab-directory') . '</li>';
				$content .= '</ul>';
				$screen->add_help_tab( array(
					'id'	=> 'Translation',
					'title'	=> __('Translation'),
					'content' => $content));
				
				$content = '<p>' . __('Staff can be categorized using taxonomies such as team, laboratory, institution... By default lab directory offers 2 taxonomies : taxonomy1 corresponds to teams, taxonomy2 corresponds to laboratories  (usefull if your staff belong to several laboratories). Each taxonomy can be customized to correspond to others categorization.','lab-directory') . '</p>';
				$screen->add_help_tab( array(
					'id'	=> 'Taxonomies',
					'title'	=> __('Taxonomies'),
					'content' => $content));
				$content = '<p>' . __('URL slug replacement is proposed for each possible template slug. Values are sanitized before save. Care and do some test when defining slug already used by Wordpress or other plugins. For example the slug "photo" could conflict with a photo plugging and break lab-directory and/or this photo plugin(s)','lab-directory') . '</p>';
				$content .= '</ul>';
				$screen->add_help_tab( array(
					'id'	=> 'url_slug',
					'title'	=> __('URL slug'),
					'content' => $content));
				URL slug for staff pages
				break;
			case 'capabilities':
				 $content  = '<p>' . __('Permission in lab-directory are given by checking first the wordpress group of a user (editor, author, ... subscriber)  and secondly the possibility for a user to pertain a lab-directory group of staff (permanent staff, doctorate...). At least one of these permission should be given to grant permission to the a user.','lab-directory') . '</p>'; 
				 $content .= '<h4>' . __('Permissions tests','lab-directory') . '</h4>';
				 $content .= '<p>' . __('Please note that when a user is selected, permissions are simulated with the user supposed to be connected. All granted permission are marked with a checked square in the first column. For one action ( for example Validate new staff entry), 2 permissions are calculated; and the permission would be granted if at least one is checked.','lab-directory') . '</p>'; 
				 $content .= '<h4>' . __('Permissions settings based on Wordpress groups','lab-directory') . '</h4>';
				 $content .= '<p>' . __('These permission depend on Worpdress groups. They are granted by webmaster having access to Wordpress group management.','lab-directory') . '</p>'; 
				 $content .= '<p>' . __('(1). For a specific capability (for example "Give PHd status") If a user is granted several permissions (can cannot cannot..) the less restrictive applies (can in this case). You can create a specific user roles in WordPress to manage permissions. This require installing a users/roles manager plugin. Note that Administrators have all permissions.','lab-directory') . '</p>';
				 $content .= '<h4>' . __('Permissions settings based on  Lab Directory groups', 'lab-directory')  . '</h4>';
				 $content .= '<p>' . __('These permissions will only be effective if (first) the user is registered and (secondly) lab-directory is able to link wordpress profile and lad-directory profile.','lab-directory') . '</p>'; 
				 $content .= '<p>' . __('(2). This (owner) permission should apply to owner (current logged user) if and only if the current logged user wordpress profile can be linked to the lab-directory profile 4.','lab-directory') . '</p>';
				 $content .= '<p>' . __('Scope limited permission should not be more restrictive than the same permission defined with a larger scope (all).','lab-directory') . '</p>';
				 $content .= '<p>' . __('(3). Permissions settings based on Lab Directory groups only apply if the current logged user wordpress profile can be linked to the lab-directory profile 4','lab-directory') . '</p>';
				 $content .= '<p>' . __('(4). This link is based on login or email comparison between Worpdress user profile and staff profile.','lab-directory') . '</p>';
				 $screen->add_help_tab( array(
					'id'	=> $current_tab,
					'title'	=> $tabs[$current_tab],
					'content' => $content));
				break;
			case 'ldap':
				$content = '<p>' . __('TODO help needed','lab-directory') . '</p>'; 
				$screen->add_help_tab( array(
					'id'	=> $current_tab,
					'title'	=> $tabs[$current_tab],
					'content' => $content));
				break;
			case 'groups':
				$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
				$screen->add_help_tab( array(
					'id'	=> $current_tab,
					'title'	=> $tabs[$current_tab],
					'content' => $content));				
				break;
			case 'fields':
				$content = '<p>' . __('This page allows you to set details fields and to create custom details fields for each Staff member. In case a group of fields is disabled, settings of corresponding fields can be changed but this fielfd will never be displayed in the directory.','lab-directory') . '</p>';
				$content .= '<p>' . __('In order to display one meta field in the staff directory pages: the meta field must be enabled, and the corresponding group must also be activated.','lab-directory') . '</p>';
				$screen->add_help_tab( array(
					'id'	=> $current_tab,
					'title'	=> __('Custom Fields','lab-directory'),
					'content' => $content));
				break;
			case 'test_sync':
				$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
				$screen->add_help_tab( array(
					'id'	=> $current_tab,
					'title'	=> $tabs[$current_tab],
					'content' => $content));
				break;
			case 'templates':
        		$content = '<p>' . __('In this window, you can browse each template, modify, empty or relaoad its defaults value. Saving will save all templates', 'lab-directory') . '</p>';
				$content .= '<p>' . __('Important: Loop templates defined in the present page of Lab-Directory settings have highest priority on every others. Improper settings of a template can break lab-directory post and pages. In order to disable one template, simply empty its content. ', 'lab-directory') . '</p>';
				$content .= '<p>' . __('Lab Directory templating is defined with Template (containing shortcode) and CSS Stylesheet containing aditionnal CSS code. Default templating can be overrided or modified.', 'lab-directory') . '</p>';
				$content .= '<p><b>' . __('Template and CSS Stylesheet are both overrided in a different way as explained in this help.', 'lab-directory') . '</b></p>';
				$screen->add_help_tab( array(
					'id'	=> 'general',
					'title'	=> __('Overview'),
					'content' => $content));
				$content =  '<p>' . __('When Lab-Directory search for a template, it uses the first found with respect to template priority definition (highest to lowest): ', 'lab-directory'). '</p>';
				$content .=  '<ol><li>' . __('first search for a template file saved in settings (highest priority )', 'lab-directory'). '</li>';
				/* translators: wp-content/themes/lab-directory/ (preferred folder for overriding templates */
				$content .=  '<li>' . sprintf( __('%s (preferred folder for overriding templates)', 'lab-directory'), 'wp-content/themes/lab-directory/') . '</li>';
				/* translators: wp-content/themes/lab-directory/ (this folder exists if you created a child theme called "yourtheme") */
				$content .=  '<li>' . sprintf( __('%s (this folder exists if you created a child theme called "yourtheme")', 'lab-directory'), 'wp-content/themes/yourtheme/') . '</li>';
				/* translators: wp-content/plugins/lab-directory/templates/ (default template if no other file found and template in settings is empty) */
				$content .=  '<li>' . sprintf( __('%s (default template if no other file found and template in settings is empty)', 'lab-directory'), 'wp-content/plugins/lab-directory/templates/') . '</li></ol></p>';
				$content .=  '<p><b>' . __('Important: do not modify files in folder wp-content/plugins/lab-directory/templates/ your changes will be lost on the next update of Lab-Directory plugin.', 'lab-directory') . '</b></p>';
				$screen->add_help_tab( array(
					'id'	=> 'templates',
					'title'	=>  __('Template'),
					'content' => $content));
				$content =  '<p>' . __('When Lab-Directory load a template, the following stylesheet are all added to CSS providing they exist: ', 'lab-directory'). '</li>';
				/* translators: wp-content/plugins/lab-directory/templates/ (this default stylesheet is always loaded first, lowest priority) */
				$content .=  '<ol><li>' . sprintf( __('%s (this default stylesheet is always loaded first, lowest priority)', 'lab-directory'), 'wp-content/plugins/lab-directory/templates/') . '</li>';
				/* translators: wp-content/themes/lab-directory/ (this folder exists if you created a child theme called "yourtheme") */
				$content .=  '<li>' . sprintf( __('%s (this folder exists if you created a child theme called "yourtheme")', 'lab-directory'), 'wp-content/themes/yourtheme/') . '</li>';
				/* translators: wp-content/themes/lab-directory/ (preferred folder for overriding CSS stylesheet) */
				$content .=  '<li>' . sprintf( __('%s (preferred folder for overriding CSS stylesheet)', 'lab-directory'), 'wp-content/themes/lab-directory/') . '</li>';
				$content .=  '<li>' . __('look for template saved in settings (last added, highest priority )', 'lab-directory'). '</li></ol></p>';
				$content .=  '<p>' . __('When loading default css (it can be used to see all default CSS rules used in a template) only keep in Lab-Directory settings the CSS Rules that you modified (no need to override one rule with itself). ', 'lab-directory'). '</p>';
				$content .=  '<p><b>' . __('Important: do not modify files in folder wp-content/plugins/lab-directory/templates/ your changes will be lost on the next update of Lab-Directory plugin.', 'lab-directory'). '</b></p>';
				$content .=  '<p>' . __('Class used in Lab-Directory template div (example given for staff template).', 'lab-directory'). '</p>';
				
				$content .=  '<p><ol><li><i>&lt;div class="ld_staff_loop"&gt;</i> : ' . __('This wrapper (div) enclose the whole Lab-Directory loop content.', 'lab-directory'). '</li>';
				$content .=  '<li><i>&lt;div class="ld_staff_item"&gt;</i> : ' . __('This div enclose each individual item found in the Lab-Directory loop. It is prefixed by the name of the template used ( here staff) ', 'lab-directory'). '</li>';
				$content .=  '<li><i>&lt;div class="ld_field ld_photo"&gt;</i> : ' . __('This div enclose each individual line of an item (name, photo, position). It has 2 classes "ld-field" (all fields have it) and a second class (ld_photo) equal to the Lab-Directory slug of that field', 'lab-directory'). '</li></ol>';
				
					$screen->add_help_tab( array(
					'id'	=> 'css',
					'title'	=> __('Stylesheet'),
					'content' => $content));
				break;
		}
		
	}
	
	static function ld_admin_help_tab_taxonomies() {
		$screen = get_current_screen();
		$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> __('Taxonomies'),
			'title'	=> __('Taxonomies translation','lab-directory'),
			'content' => $content,
		) );
	}	
	
	static function ld_admin_help_tab_translations() {
		$screen = get_current_screen();
		$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
		
		$screen->add_help_tab( array(
			'id'	=> 'acronyms',
			'title'	=> __('Acronyms', 'lab-directory'),
			'content' => $content,
			) );
		$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> 'translation',
			'title'	=> __('Translation'),
			'content' => $content,
			) );
	}	
	
	static function ld_admin_help_tab_import() {
		$screen = get_current_screen();
		$content = '<p>' . __('TODO help needed','lab-directory') . '</p>';
		
		$screen->add_help_tab( array(
			'id'	=> 'import',
			'title'	=> __('Import'),
			'content' => $content,
			) );
	}
	
	
	static function ld_admin_help_edit_staff() {
		self::ld_admin_help_add_new_staff('edit');
	}
	
		/*
		 * Edit != false used to generate edit staff help (use same translation)
		 */
	static function ld_admin_help_add_new_staff($edit=false) {
		$screen = get_current_screen();
		if ($edit) { 
		 	$content = '<p>' . __('This page is used to edit staff profile. Some fields may be locked depending on LDAP Syncing and user permissions.','lab-directory') . '</p>';
			$screen->add_help_tab( array(
				'id'	=> 'edit_staff',
				'title'	=> __( 'Edit staff profile', 'lab-directory' ),
				'content' => $content,
			) );
				
		}
		else {
			$content = '<p>' . __('This page is used to add a new staff in the staff directory. When LDAP syncing is used, do not add a staff that can be synced with your LDAP directory. ','lab-directory') . '</p>';
			$screen->add_help_tab( array(
				'id'	=> 'add_new_staff',
				'title'	=> __( 'New staff', 'lab-directory' ),
				'content' => $content,
			) );
				
		}
		
		$content = '<p>' . __('Staff meta fields are grouped by group of meta fields (CV, Biography...). These groups are restricted to those groups defined in the staff status. For a new staff, first save name and fisrtsname, then adjust staff  statuts, then you will have access to the entire staff profile settings. ','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> 'staff_details',
			'title'	=> __('Staff Details', 'lab-directory' ),
			'content' => $content,
		) );
		$content = '<p>' . __('Staff status define which group of mea filelds will be shown. Note that a staff access to certains pages is restricted/allowed on status base. Do not give some staff improper status which could lead to giving unwanted permissions to him. ','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> __('staff_status'),
			'title'	=> __('Staff status', 'lab-directory' ),
			'content' => $content,
		) );
		$content = '<p>' . __('When LDAP syncing is used and LDAP staff photo is synced, staff photo is disabled and replaced whith the LDAP Staff photo. In that case, you should add staff photo in the LDAP directory, it will appear in lab-directory at the next LDAP sync. ','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> 'staff_photo',
			'title'	=> __( 'Staff photo', 'lab-directory' ),
			'content' => $content,
		) );
		$content = '<p>' . __('If your webmaster allows using taxonomies, you can categorise staff depending on their team laboatory... Taxonomies will be used to filter staff list, by team for example. ','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> __('taxonomies'),
			'title'	=> __('Taxonomies'),
			'content' => $content,
		) );
	}

	static function ld_admin_help_edit_taxonomies() {
		$screen = get_current_screen();
		$content = '<p>' . __('Taxonomies are enabled on this staff directory. In order to categorise staff (by team for example), just add the correspoding teams one by one. You can also define nested categories (team and sub-team). Please note that the default taxonomies (Team and laboratory) can be overrided if you need using different taxonomies (see taxonomies menu in lab-directory admin menu).','lab-directory') . '</p>';
		$screen->add_help_tab( array(
			'id'	=> __('Taxonomies'),
			'title'	=> __('Edit Taxonomies','lab-directory'),
			'content' => $content,
		) );
	}
	
	
	
	static function translations() {	
		
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$language_list = wp_get_available_translations();
	
		$locale = Lab_Directory::$default_post_language; //string(5) "fr_FR" 
		$current_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'acronyms';
		
		$available_languages = get_available_languages(); // array(2) { [0]=> string(5) "en_GB" [1]=> string(5) "fr_FR" }  
		unset($available_languages[$locale]);
		
		if (($key = array_search($locale, $available_languages)) !== false) {
		    unset($available_languages[$key]);
		}
		array_unshift($available_languages, $locale);		
		array_unshift($available_languages, 'acronyms');
		
		$html = '<h2 class="nav-tab-wrapper">';
		foreach( $available_languages as $available_language){
			if ($available_language=='acronyms') {
				$language_name = __('Acronym tooltips', 'lab-directory'); 
			} else {
				$language_name = $language_list[$available_language]['native_name'] . ' ('. $available_language . ')';
			}
			$class = ( $available_language == $current_tab ) ? 'nav-tab-active' : '';
			$html .= '<a class="nav-tab ' . $class . '" href="edit.php?post_type=lab_directory_staff&page=lab-directory-translations&tab=' . $available_language . '">' . $language_name . '</a>';
		}
		$html .= '</h2>';
		echo $html;
		
		Lab_Directory_Admin::settings_translations($current_tab, $language_list[$current_tab]['native_name'], 
				$locale, $language_list[$locale]['native_name']);
	
	}
	
	static function taxonomies() {
	
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$language_list = wp_get_available_translations('core');
	
		$locale = Lab_Directory::$default_post_language; //string(5) "fr_FR"
		$current_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : $locale;
	
		$available_languages = get_available_languages(); // array(2) { [0]=> string(5) "en_GB" [1]=> string(5) "fr_FR" }
		unset($available_languages[$locale]);
	
		if (($key = array_search($locale, $available_languages)) !== false) {
			unset($available_languages[$key]);
		}
		array_unshift($available_languages, $locale);
	
		$html = '<h2 class="nav-tab-wrapper">';
		foreach( $available_languages as $available_language){
			$language_name = $language_list[$available_language]['native_name'] . ' ('. $available_language . ')';
			$class = ( $available_language == $current_tab ) ? 'nav-tab-active' : '';
			$html .= '<a class="nav-tab ' . $class . '" href="edit.php?post_type=lab_directory_staff&page=lab-directory-taxonomies&tab=' . $available_language . '">' . $language_name . '</a>';
		}
		$html .= '</h2>';
		echo $html;
	
		Lab_Directory_Admin::settings_taxonomies($current_tab, $language_list[$current_tab]['native_name'],
			$locale, $language_list[$locale]['native_name']);
	
	}
   
	static function settings() {
		$current_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'general';
	
		$tabs = array(
				'general'   => __( 'General settings', 'lab-directory' ),
				'capabilities' => __('Permissions', 'lab-directory' ),
				'ldap'   => __( 'LDAP server', 'lab-directory' ),
				'groups'   => __( 'Groups of fields', 'lab-directory' ),
				'fields'  => __( 'Meta fields', 'lab-directory' ),
				'test_sync'   => __( 'LDAP tests', 'lab-directory' ),
				'templates'   => __( 'Templates'),
				'third'  => __('About'),
		);
		
		$html .= '<h4 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current_tab ) ? 'nav-tab-active' : '';
			$html .= '<a class="nav-tab ' . $class . '" href="edit.php?post_type=lab_directory_staff&page=lab-directory-settings&tab=' . $tab . '">' . $name . '</a>';
		}
		$html .= '</h4>';
		echo $html;
	
		if ( $current_tab == 'general' ) {
			Lab_Directory_Admin::settings_general();
		}
		elseif ( $current_tab == 'capabilities' ) {
			Lab_Directory_Admin::settings_permissions();
		}
		elseif ( $current_tab == 'groups' ) {
			Lab_Directory_Admin::settings_groups();
		}
		elseif ( $current_tab == 'fields' ) {
			Lab_Directory_Admin::settings_fields();
		}
		elseif ( $current_tab == 'ldap' ) {
			Lab_Directory_Admin::settings_ldap();
		}
		elseif ( $current_tab == 'test_sync' ) {
			Lab_Directory_Admin::settings_test_sync();
		}
		elseif ( $current_tab == 'templates' ) {
			Lab_Directory_Admin::settings_templates();
		}
		
		else {
			// Temporary TODO list
			?>
			<style>
			ol, ul {padding-left: 20px; list-style:disc;}
			</style>
			
			<h2>About Lab-Directory</h2>
			<p>Lab-Directory is an Open Source plugin.</p>
			<ul>
				<li>Contributors: Christophe Seguinot, </li>
				<li><a href="https://github.com/ChristopheSeg/lab-directory">Lab-Directory on Github</a></li>
				<li>Tags: laboratory  directory, lab_directory_staff, employees, team members, faculty</li>
				<li>Requires at least: 4.8.2</li>
				<li>Tested up to: 4.9.12</li>
				<li>Stable Tag: tags/0.9</li>
				<li>License: GPLv2 or later</li>
				<li>License URI: http://www.gnu.org/licenses/gpl-2.0.html</li>
				<li></li>
				<li>This plugin is build from a fork of <a href="https://github.com/adamtootle/staff-directory">staff-directory from adamtootle</a></li>
				
			</ul></p>
			<h2>Changelog</h2>
			<h4>Version 0.9</h4>
			<ul>
			<li>- </li>
			<li>- </li>
			<li>- </li>
			<li>- </li>
			</ul>
			<h4>Beta (current)</h4>
			<ul>
			<li>- </li>
			<li>- </li>
			<li>- </li>
			<li>- </li>
			</ul>
			

	
	     
				<?php 
				
			}	
			
		}
		static function settings_translations($lang, $lang_name, $locale, $locale_name) {
		
			$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
			$form_messages = array('form_saved' => false);
		
			// Check $_POST and _wpnonce
			if ($_POST['admin-settings-translations']) {
				if ( ($_POST['admin-settings-translations']=='Save') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-translations' )){
					$lang = $_POST['lab_directory_translations_for'];
					$slugs = $_POST['lab_directory_translations_slugs'];
					$post_translations = $_POST['lab_directory_translations_translations'];
										
					$translations = array();

					if ($lang =='acronyms') {
						// Save acronyms 
						$metafields_slugs = $_POST['lab_directory_translations_metafields_slugs'];
						$links = $_POST['lab_directory_translations_links'];
						$index = -1; 
						foreach ( $slugs as $slug ) {
							$index++;
							$metafields_slug = sanitize_text_field($metafields_slugs[$index]);
							if ($metafields_slug AND ($post_translations[$index] OR $links[index])) {
								$translations[$index] = array(); 
								$translations[$index]['slug'] = $metafields_slug;
								$translations[$index]['acronym'] = $slug;
								if ($post_translations[$index]) {
									$translations[$index]['translation'] = 
									sanitize_text_field($post_translations[$index]) ;
								}
								if ($links[$index]) {
									$translations[$index]['link'] = 
									sanitize_text_field($links[$index]);
								}
							}
						}
						
					} else {
						// save translations
						$index = -1;
						foreach ( $slugs as $slug ) {
							$index++;
							if ($post_translations[$index]) {
								$translations[$slug] = $post_translations[$index];
							}
						}
					}
				
					// save used language  (ex: lab_directory_translations_fr_FR) or acronyms
					update_option( 'lab_directory_translations_' . $lang, $translations);
					$form_messages['form_saved']= true;	
					
				}else{
					// Error
					$form_messages['erreur'][]= __('Security check fail : form not saved.');
					echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
				}
			} else {
				// Form initialisation load only acronyms or used language translations (ex: lab_directory_translations_fr-FR)
				$translations = get_option('lab_directory_translations_' . $lang);
				
			}

			require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-translations.php' );
		
		}
		
	static function settings_taxonomies($lang, $lang_name, $locale, $locale_name) {
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false);
	
		// Check $_POST and _wpnonce
		if ($_POST['admin-settings-taxonomies']) {
			if ( ($_POST['admin-settings-taxonomies']=='Save') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-taxonomies' )){
				$lang = $_POST['lab_directory_taxonomies_for'];
				$slugs = $_POST['lab_directory_taxonomies_slugs'];
				$post_translations = $_POST['lab_directory_taxonomies_translations'];
									
				$translations = array();

				// save translations
				$index = -1;
				foreach ( $slugs as $slug ) {
					$index++;
					if ($post_translations[$index]) {
						$translations[$slug] = $post_translations[$index];
					}
				}
				
				// Test if taxonomies have different name translation
	
				// save used language  (ex: lab_directory_translations_fr_FR) or acronyms
				update_option( 'lab_directory_taxonomies_' . $lang, $translations);
				$form_messages['form_saved']= true;	
				
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		} else {
			// Form initialisation load only acronyms or used language translations (ex: lab_directory_translations_fr-FR)
			$translations = get_option('lab_directory_taxonomies_' . $lang);
			
		}
	
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-taxonomies.php' );
	}	
		
	static function settings_general() {

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false); 
		$locale = Lab_Directory::$default_post_language; //string(5) "fr_FR"
		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$language_list = wp_get_available_translations('core');
		$available_languages = get_available_languages(); // array(2) { [0]=> string(5) "en_GB" [1]=> string(5) "fr_FR" }
		$languages=array(); 
		
		foreach ($available_languages as $available_language){
			if ($locale != $available_language) {
				$languages[$available_language] = $language_list[$available_language]['native_name'];
			}
		}
		
		$taxonomies = Lab_Directory::lab_directory_get_taxonomies(true);
			
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-general'])) {
			if ( !empty($_POST['admin-settings-general']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-general' )){

				// Process/save form fields
				// update_option( 'lab_directory_use_ldap', isset( $_POST['lab_directory_use_ldap'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_ldap', isset( $_POST['lab_directory_use_ldap'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_taxonomy1', isset( $_POST['lab_directory_use_taxonomy1'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_taxonomy2', isset( $_POST['lab_directory_use_taxonomy2'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_ld_footer_pages', isset( $_POST['lab_directory_use_ld_footer_pages'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_ld_footer_posts', isset( $_POST['lab_directory_use_ld_footer_posts'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_lang1', isset( $_POST['lab_directory_use_lang1'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_lang2', isset( $_POST['lab_directory_use_lang2'] ) ? '1' : '0'  );
				update_option( 'lab_directory_locale_first', $_POST['lab_directory_locale_first']);
				update_option( 'lab_directory_lang1', $_POST['lab_directory_lang1']);
				update_option( 'lab_directory_lang2', $_POST['lab_directory_lang2']);
				update_option( 'lab_directory_title_firstname_first', $_POST['lab_directory_title_firstname_first']);
				update_option( 'lab_directory_default_template', $_POST['lab_directory_default_template']);
				update_option( 'lab_directory_use_format_switcher', isset( $_POST['lab_directory_use_format_switcher'] ) ? '1' : '0'  );
				update_option( 'lab_directory_use_staff_search', isset( $_POST['lab_directory_use_staff_search'] ) ? '1' : '0'  );
				
				// Url slugs; 
				$template_slugs = Lab_Directory_Shortcode::retrieve_template_list();
				$lab_directory_url_slugs = array(); 
				
				foreach ($_POST['lab_directory_url_slugs'] as $key => $value)  {
					$new_value = sanitize_title($value);
					if ($new_value) {
						$lab_directory_url_slugs[$key] = $new_value;
					} else {
						$lab_directory_url_slugs[$key] = $key; 
					}
				}
				update_option('lab_directory_url_slugs', $lab_directory_url_slugs);
				// Add a flag to update rewrite rules
				update_option( 'lab_directory_flush_rewrite_rules_flag', true );
				
				// Social networks
				$socialnetworks = array();
				if ( isset( $_POST['lab_directory_used_social_networks'] ) ) {	 
					foreach($_POST['lab_directory_used_social_networks'] as $key =>$value) {
						$socialnetworks[$key] = $value; 
					}
				}
				update_option( 'lab_directory_used_social_networks', $socialnetworks );
				
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}

		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-general.php' );
	}
	
	static function settings_templates() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false);
	
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-templates'])) {
			if ( !empty($_POST['admin-settings-templates']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-templates' )){
	
				if ( isset( $_POST['custom_lab_directory_staff_templates'] ) ) {
					foreach ($_POST['custom_lab_directory_staff_templates'] as $template_slug =>$template_and_css) {
						$template_file =  'ld_' . $template_slug . '.php';
						$css_file =  'ld_' . $template_slug . '.css';
						
						$template_content = trim ($template_and_css['html']);
						if ($template_content) {
							update_option( $template_file, $template_content);
						}
						else {
							delete_option($template_file);
						}
						$css_content = trim ($template_and_css['css']);
						if ($css_content) {
							update_option( $css_file, $css_content);
						}
						else {
							delete_option( $css_file);
						}
						
					}
				}
		
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
	
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-templates.php' );
	}
	
	
	static function settings_fields() {

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false); 

		// Check $_POST and _wpnonce	
		if ($_POST['admin-settings-fields']) {
			if ( ($_POST['admin-settings-fields']=='Save') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-fields' )){
	
				// Process/save form fields
				if ( isset( $_POST['lab_directory_staff_meta_fields_slugs'] ) ) {
	
					$lab_directory_staff_settings->update_custom_lab_directory_staff_meta_fields();
					$form_messages['form_saved']= true;
				}
					
			}elseif ( ($_POST['admin-settings-fields']=='Reset') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-fields' )){
				// reset meta fields
				$lab_directory_staff_settings->reset_custom_lab_directory_staff_meta_fields();
				$form_messages['warning'][]= 'All meta fields have been reset to their default values.';
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		} else {
			// Form initialisation : if the plugin has been upgraded and new fields added
			$lab_directory_staff_settings->upgrade_custom_lab_directory_staff_meta_fields();
	
		}
		
		$use_ldap = (get_option( 'lab_directory_use_ldap' ) == '1');
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-fields.php' );
		
	}

	static function settings_ldap() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false); 

		// Empty LDAP tab if not used
		if (get_option( 'lab_directory_use_ldap' ) == '0') {
			echo '<div class="notice notice-warning"><p>Please active ldap usage in "general settings" before setting LDAP server</p></div>';
			return; 
		} 
			
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-ldap'])) {
			if ( !empty($_POST['admin-settings-ldap']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-ldap' )){
	
				// Process/save form fields
				$ldap_server = array(); 
				$ldap_server['ldap_server'] = sanitize_text_field($_POST['ldap_server']);
				$ldap_server['ldap_dn'] = sanitize_text_field($_POST['ldap_dn']);
				$ldap_server['ldap_set_time_limit'] = intval($_POST['ldap_set_time_limit']);
				$ldap_server['ldap_filter'] = sanitize_text_field($_POST['ldap_filter']);
				$ldap_server['ldap_attributes'] = sanitize_text_field($_POST['ldap_attributes']);
				$ldap_server['ldap_timestamp_attribute'] = sanitize_text_field($_POST['ldap_timestamp_attribute']);
				// $ldap_server['ldap_test_filter'] = sanitize_text_field($_POST['ldap_test_filter']);
				update_option( 'lab_directory_ldap_server', $ldap_server);
				$form_messages['form_saved'] = true;
				
				
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		} else {
			// Form initialisation 
			$ldap_server = get_option( 'lab_directory_ldap_server' );	
		}
		
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-ldap.php' );
	}

	static function settings_groups() {
		
		$default_group_names = Lab_Directory::get_lab_directory_default_group_names();
		
		if (!is_array($group_activations)) {
			//Initiate $group_activations (fist use)
			$group_activations = array();
			foreach ($default_group_names as $key =>$default_group_name) {
				$group_activations[$key] = true;
			}
		}
		// Always activate CV
		$group_activations['CV'] = true;
		
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-groups'])) {
			if ( !empty($_POST['admin-settings-groups']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-groups' )){
					// Process form
					foreach ($default_group_names as $key =>$default_group_name) {
						if (isset($_POST['activated_'.$key])) {
							$group_activations[$key] = true;
						} else {
							$group_activations[$key] = false;
							}
					}	
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		// Always activate CV
		$group_activations['CV'] = true;
			update_option( 'lab_directory_group_activations', $group_activations);
		} else {
			// Form initialisation 
			$group_activations = get_option( 'lab_directory_group_activations' ) ;		
		}
		
			
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-groups.php' );
		
		
	}
	
	static function settings_permissions() {
		global $current_user;
		
		$form_messages = array('form_saved' => false);
	    $wp_roles = new WP_Roles();
	    $all_wp_roles = $wp_roles->roles;
	    // Remove administrator (can do everything) and translator (unused) roles

	    unset( $all_wp_roles['administrator'] );
	    unset( $all_wp_roles['translator'] );
	    
	    $all_ld_roles = Lab_Directory::get_lab_directory_default_statuss(); 
		$ld_permissions = array();
		
		// Check $_POST and _wpnonce
		$test_user_id = 0; 
		if(isset($_POST['admin-settings-permissions'])) {
			// User permission testing
			if (in_array('administrator',  $current_user->roles) and isset($_POST['test_user_id']))  {
				$test_user_id = intval($_POST['test_user_id']);
				$test_user_id = $test_user_id>0? $test_user_id: 0;
			};
				
			if ( ($_POST['admin-settings-permissions']=='Save') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-permissions' )){
				// Process form
				foreach (Lab_Directory::$capabilities as $capability_key => $capability){
					foreach ($all_wp_roles as $role_key => $role) {
						$name = 'wp_' . $role_key. '_'. $capability_key;
						$ld_permissions[$name] =  isset($_POST[$name])? '1':'0';  
					}
					foreach ($all_ld_roles as $role_key => $role) {
						$name = 'ld_' . $role_key. '_'. $capability_key;
						$ld_permissions[$name] =  isset($_POST[$name])? '1':'0'; 
					}
				}
				$form_messages['form_saved'] = true;
			} elseif  ( ($_POST['admin-settings-permissions']=='Reset') && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-permissions' )){
				// Resetting-permissions
				$ld_permissions =  Lab_Directory::get_lab_directory_default_permissions();
				$form_messages['warning'][] = 'Fields have been reset to default settings ';
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
				update_option( 'lab_directory_permissions', $ld_permissions);
				// Update static $ld_permissions used in this form 
				Lab_Directory::$ld_permissions = $ld_permissions;
		} else {
			// Form initialisation 
			$ld_permissions = get_option( 'lab_directory_permissions');
		}
		
			
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-permissions.php' );
		
		
	}
	
	
	static function settings_test_sync() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$form_messages = array('form_saved' => false);
	
			// Empty LDAP tab if LDAP not used
		if (get_option( 'lab_directory_use_ldap' ) == '0') {
			echo '<div class="notice notice-warning"><p>Please active ldap usage in "general settings" before testing LDAP sync</p></div>';
			return; 
		} 			
		// Check $_POST and _wpnonce
		$save = false; 
		$test_sync = false;
		
		if (isset($_POST['admin-settings-test-sync-with-filter'])) {
			$save = true;
			$test_sync = 'test_filter'; 
		}
		if (isset($_POST['admin-settings-test-sync-with-email'])) {
			$save = true;
			$test_sync = 'email';
		}
		if (isset($_POST['admin-settings-test-sync-with-sync_filter'])) {
			$save = true;
			$test_sync = 'sync_filter';
		}
		if (isset($_POST['admin-settings-test-sync'])) {
			//only save without test
			$save = true;
		}
		
		if($save) {
			if ( wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-test-sync' ) ){
	
				// Process/save form fields
				update_option( 'lab_directory_ldap_test_avec_import', isset( $_POST['ldap_test_avec_import'] ) ? '1' : '0'  );
				
				$lab_directory_ldap_test_filter = sanitize_text_field($_POST['ldap_test_filter']);
				update_option( 'lab_directory_ldap_test_filter', $lab_directory_ldap_test_filter);
				$lab_directory_ldap_test_email = sanitize_text_field($_POST['ldap_test_email']);
				update_option( 'lab_directory_ldap_test_email', $lab_directory_ldap_test_email);
				
				$form_messages['form_saved'] = true;
			}else{
				// Error
				$form_messages['erreur'][]= __('Security check fail : form not saved.');
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
		
		$lab_directory_ldap_test_filter = get_option( 'lab_directory_ldap_test_filter' );
		$lab_directory_ldap_test_email = get_option( 'lab_directory_ldap_test_email' );
		$lab_directory_ldap_test_avec_import = (get_option( 'lab_directory_ldap_test_avec_import' ) == '1');
		if ($test_sync) {
			// do synchronisation tests
			if ($test_sync == 'test_filter') {
				// Test Sync with test filter 
				Lab_Directory_Settings::import_annuaire_ldap($lab_directory_ldap_test_filter, '', true, $lab_directory_ldap_test_avec_import, $form_messages);
			}
		elseif ($test_sync == 'email') {
				// Test Sync with email filter 
				$lab_directory_staff_settings->import_annuaire_ldap('', $lab_directory_ldap_test_email, true, $lab_directory_ldap_test_avec_import, $form_messages);
			}	
		else {
				// Test Sync with synchronisation filter 
				$lab_directory_staff_settings->import_annuaire_ldap('', '', true, $lab_directory_ldap_test_avec_import, $form_messages);
			}		
		}
		
		$lab_directory_ldap_last10syncs = get_option( 'lab_directory_ldap_last10syncs', array('No sync operation performed up to now') );
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-test-sync.php' );
	}
	
	static function help() {
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-help.php' );
	}

	static function import() {
		
		$did_import_old_lab_directory_staff = false;
		
		// TODO TEMPORARY statement  REMOVE THIS  Lab_Directory::import_spip_staff();
		// Attention l'import SPIP casse les modifications (résumé et tite bilingues !!		
		
		if ( isset( $_GET['import'] ) && $_GET['import'] == 'true' ) {
			Lab_Directory::import_old_lab_directory_staff();
			$did_import_old_lab_directory_staff = true;
		}
		
		if ( Lab_Directory::has_old_lab_directory_staff_table() ):
			?>

			<h2>Lab Directory Import</h2>
			<p>
				This tool is provided to import lab_directory_staff from an older version of this plugin.
				This will copy old lab_directory_staff members over to the new format, but it is advised
				that you backup your database before proceeding. Chances are you won't need
				it, but it's always better to be safe than sorry! WordPress provides some
				<a href="https://codex.wordpress.org/Backing_Up_Your_Database" target="_blank">instructions</a>
				on how to backup your database.
			</p>

			<p>
				Once you're ready to proceed, simply use the button below to import old
				lab_directory_staff members to the newer version of the plugin.
			</p>

			<p>
				<a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-import&import=true"
				   class="button button-primary">Import Old Staff</a>
			</p>

		<?php else: ?>

			<?php if ( $did_import_old_lab_directory_staff ): ?>

				<div class="updated">
					<p>
						Old lab_directory_staff was successfully imported! You can <a
							href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff">view all lab_directory_staff here</a>.
					</p>
				</div>

			<?php else: ?>

				<p>
					It doesn't look like you have any lab_directory_staff members from an older version of the plugin. You're good to
					go!
				</p>

			<?php endif; ?>

			<?php

		endif;
	}

	static function register_import_old_lab_directory_staff_message() {
		add_action( 'admin_notices', array( 'Lab_Directory_Admin', 'show_import_old_lab_directory_staff_message' ) );
	}

	static function show_import_old_lab_directory_staff_message() {
		?>
		<div class="update-nag">
			It looks like you have lab_directory_staff from an older version of the Lab Directory plugin.
			You can <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-import">import
				them</a> to the newer version if you would like.
		</div>

		<?php
	}
	static function addstaff() {
		require_once( plugin_dir_path( __FILE__ ) . '../views/edit.php' );
	}
	
	

}
