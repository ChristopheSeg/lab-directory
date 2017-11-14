<?php

class Lab_Directory_Admin {
	static function register_admin_menu_items() {
		add_action( 'admin_menu', array( 'Lab_Directory_Admin', 'add_admin_menu_items' ) );
	}

	static function add_admin_menu_items() {
		add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Add staff', 'Add staff', 'publish_posts',
			'add-staff', array( 'Lab_Directory_Admin', 'addstaff' ) );
		add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Settings', 'Settings', 'publish_posts',
			'lab-directory-settings', array( 'Lab_Directory_Admin', 'settings' ) );
		add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Help', 'Help', 'publish_posts',
			'lab-directory-help', array( 'Lab_Directory_Admin', 'help' ) );
		add_submenu_page( 'edit.php?post_type=lab_directory_staff', 'Lab Directory Import', 'Import Old Staff', 'publish_posts',
			'lab-directory-import', array( 'Lab_Directory_Admin', 'import' ) );
	}

	static function settings() {
		$current_tab = ( ! empty( $_GET['tab'] ) ) ? esc_attr( $_GET['tab'] ) : 'general';
		
		$tabs = array(
				'general'   => __( 'General', 'lab-directory' ),
				'ldap'   => __( 'LDAP server', 'lab-directory' ),
				'groups'   => __( 'Meta fields groups', 'lab-directory' ),
				'fields'  => __( 'Meta fields', 'lab-directory' ),
				'test_sync'   => __( 'LDAP sync', 'lab-directory' ),
				'acronyms'   => __( 'Acronyms', 'lab-directory' ),
				'taxonomy'  => __( 'Taxonomies', 'lab-directory' ),
				'third'  => 'TODO list',
		);
		$html = '<h2>Lab Directory Settings</h2>'; 
		$html .= '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ){
			$class = ( $tab == $current_tab ) ? 'nav-tab-active' : '';
			$html .= '<a class="nav-tab ' . $class . '" href="edit.php?post_type=lab_directory_staff&page=lab-directory-settings&tab=' . $tab . '">' . $name . '</a>';
		}
		$html .= '</h2>';
		echo $html;
		
		if ( $current_tab == 'general' ) {
			Lab_Directory_Admin::settings_general();
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
		elseif ( $current_tab == 'acronyms' ) {
			Lab_Directory_Admin::settings_acronyms();
		}
		elseif ( $current_tab == 'taxonomy' ) {
			Lab_Directory_Admin::settings_taxonomy();
		}
		else {
			// Temporary TODO lidt 
			?>
			<p>
		      TODO séparer admin et frontend (avant traduction) 
		      TODO ajouter wp_nonce partout !! <br/> 
		      TODO ajouter field timestamp dans onglet LDAP<br/>
		      TODO ajouter onglet traduction acronymes <br/>
		      TODO ajouter ordre, activé, ldapfield, sv mv, separator, translation.<br/>
		      TODO voir si on maintient les ,[shortcode], ou si on peut automatiser l'ordre dans les templates: comment faire une mise en forme ajustable mais respecter l'ordre défini en admin? <br/>
		      TODO ?? définir une [fields_loop], et une liste de champs limitéé pour list, grid, ...??
		    </p>
<p> 
<ul>

<li>champ système (à saisir ou calculer OU IMPORTER!!)</li>
<li>
			'statut_permanent_recherche' => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_administratif' => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_doctorant'   => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_postdoc'     => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_stagiaire'   => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_invite'      => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_cdd'         => 'tinyint(1) NOT NULL DEFAULT "0"',
			'statut_hdr'         => 'tinyint(1) NOT NULL DEFAULT "0"',

</li>
<li>champ à redéfinir autrement</li>
<li>
			'idequipe'           => 'bigint(21) NOT NULL DEFAULT "0"',
			'photo'              => 'varchar(255) DEFAULT NULL',
			'fiche_validee'      => 'SMALLINT NOT NULL DEFAULT "0"', devient post_status
			'resp_equipe'        => 'bigint(21) DEFAULT NULL',
			'resp_projets'       => 'TEXT NULL DEFAULT NULL',
			'resp_plateformes'    => 'TEXT NULL DEFAULT NULL',
			'resp_projets_articles' =>'TEXT NULL DEFAULT NULL',
			'resp_plateformes_articles' => 'TEXT NULL DEFAULT NULL',
			'id_personnel'       => 'bigint(21) NOT NULL',

</li>

</ul>

</p>		    
			<?php 
			
		}	
	}
        
	static function settings_general() {

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
		
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-general'])) {
			if ( !empty($_POST['admin-settings-general']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-general' )){

				// Process/save form fields
				update_option( 'lab_directory_use_ldap', isset( $_POST['lab_directory_use_ldap'] ) ? '1' : '0'  );
				
				if ( isset( $_GET['delete-template'] ) ) {
					$lab_directory_staff_settings->delete_custom_template( $_GET['delete-template'] );
				}
				if ( isset( $_POST['lab_directory_staff_single_template'] ) ) {
		            update_option( 'lab_directory_staff_single_template', $_POST['lab_directory_staff_single_template'] );
					$did_update_options = true;
				} else {
		            if ( get_option( 'lab_directory_staff_single_template' ) == '' ) {
		    			update_option( 'lab_directory_staff_single_template', 'default' );
		                $did_update_options = true;
		    		}
		        }
		
				if ( isset( $_POST['lab_directory_staff_templates']['slug'] ) ) {
					$lab_directory_staff_settings->update_default_lab_directory_staff_template_slug( $_POST['lab_directory_staff_templates']['slug'] );
					$did_update_options = true;
				}
		
				if ( isset( $_POST['custom_lab_directory_staff_templates'] ) ) {
					$lab_directory_staff_settings->update_custom_lab_directory_staff_templates( $_POST['custom_lab_directory_staff_templates'] );
					$did_update_options = true;
				}
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}

		$current_template = $lab_directory_staff_settings->get_current_default_lab_directory_staff_template();
		$custom_templates = $lab_directory_staff_settings->get_custom_lab_directory_staff_templates();

		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-general.php' );
	}
	static function settings_fields() {

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
		$did_reset_options = false;

		// Check $_POST and _wpnonce	
		if ( isset($_POST['admin-settings-fields']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-fields' )){

			// Process/save form fields
			if ( isset( $_POST['lab_directory_staff_meta_fields_slugs'] ) ) {

				$lab_directory_staff_settings->update_custom_lab_directory_staff_meta_fields();
				$did_update_options = true;
			}
				
		}elseif ( isset($_POST['admin-resettings-fields']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-fields' )){
			// reset meta fields
			$lab_directory_staff_settings->reset_custom_lab_directory_staff_meta_fields();
			$did_reset_options = true;
		}else{
			// Error
			echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
		}
		
		$use_ldap = (get_option( 'lab_directory_use_ldap' ) == '1');
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-fields.php' );
		
	}

	static function settings_ldap() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;

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
				$did_update_options = true;
				
				
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
		
		$ldap_server = get_option( 'lab_directory_ldap_server' );
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-ldap.php' );
	}

	static function settings_groups() {
		
		$default_group_names = Lab_Directory::get_lab_directory_default_group_names();
		$group_activations = get_option( 'lab_directory_group_activations' ) ;
		
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
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		// Always activate CV
		$group_activations['CV'] = true;
			update_option( 'lab_directory_group_activations', $group_activations);
		}
			
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-groups.php' );
		
		
	}
	
	static function settings_acronyms() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
	
		// Remove LDAP tab if LDAP not used
		if (get_option( 'lab_directory_use_ldap' ) == '0') {
			echo 'no LDAP ';
		}
			
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-acronyms'])) {
			if ( !empty($_POST['admin-settings-acronyms']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-acronyms' )){
	
				// Process/save form fields
	
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
	
		
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-acronyms.php' );
	}
	
	static function settings_test_sync() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
		$sync_test_result="";
	
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
				
				$did_update_options = true;
			}else{
				// Error
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
				$sync_test_result = Lab_Directory_Settings::import_annuaire_ldap($lab_directory_ldap_test_filter, '', true, $lab_directory_ldap_test_avec_import);
			}
		elseif ($test_sync == 'email') {
				// Test Sync with email filter 
				$sync_test_result = $lab_directory_staff_settings->import_annuaire_ldap('', $lab_directory_ldap_test_email, true, $lab_directory_ldap_test_avec_import);
			}	
		else {
				// Test Sync with synchronisation filter 
				$sync_test_result = $lab_directory_staff_settings->import_annuaire_ldap('', '', true, $lab_directory_ldap_test_avec_import);
			}		
		}

		$lab_directory_ldap_last10syncs = get_option( 'lab_directory_ldap_last10syncs', array('No sync operation performed up to now') );
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-test-sync.php' );
	}
	
	static function settings_taxonomy() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
	
		// Remove LDAP tab if LDAP not used
		if (get_option( 'lab_directory_use_ldap' ) == '0') {
			echo 'no LDAP ';
		}
			
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-taxonomy'])) {
			if ( !empty($_POST['admin-settings-taxonomy']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-taxonomy' )){
	
				// Process/save form fields
	
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
	
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-taxonomy.php' );
	}

	static function help() {
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-help.php' );
	}

	static function import() {
		$did_import_old_lab_directory_staff = false;
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
