<?php

class Lab_Directory_Admin {
	static function register_admin_menu_items() {
		add_action( 'admin_menu', array( 'Lab_Directory_Admin', 'add_admin_menu_items' ) );
	}

	static function add_admin_menu_items() {
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
				'general'   => __( 'general', 'lab-directory' ),
				'ldap'   => __( 'LDAP server', 'lab-directory' ),
				'fields'  => __( 'Directory fields', 'lab-directory' ),
				'test_sync'   => __( 'LDAP sync', 'lab-directory' ),
				'acronyms'   => __( 'acronyms', 'lab-directory' ),
				'taxonomy'  => __( 'Taxonomies', 'lab-directory' ),
				'third'  => __( 'TODO list', 'lab-directory' ),
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
			'ldap'               => 'tinyint(1) NOT NULL DEFAULT "0"',
			'ldap_timestamp'     => 'timestamp NULL DEFAULT NULL',
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

<li>champ inutiles</li>
<li>
			'statut'             => 'varchar(20)  DEFAULT "0" NOT NULL',
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

		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-fields'])) {
			if ( !empty($_POST['admin-settings-fields']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-fields' )){

				// Process/save form fields
				if ( isset( $_POST['lab_directory_staff_meta_fields_slugs'] ) ) {

					$lab_directory_staff_settings->update_custom_lab_directory_staff_meta_fields();
					$did_update_options = true;
				}
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
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

			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
		
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-ldap.php' );
	}

	static function settings_acronyms() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
	
		// Remove LDAP tab if not used
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
	
			// Empty LDAP tab if not used
		if (get_option( 'lab_directory_use_ldap' ) == '0') {
			echo '<div class="notice notice-warning"><p>Please active ldap usage in "general settings" before testing LDAP sync</p></div>';
			return; 
		} 			
		// Check $_POST and _wpnonce
		if(isset($_POST['admin-settings-test-sync'])) {
			if ( !empty($_POST['admin-settings-test-sync']) && wp_verify_nonce( $_POST['_wpnonce'], 'admin-settings-test-sync' )){
	
				// Process/save form fields
	
			}else{
				// Error
				echo '<div class="error notice"><p>Security check fail : form not saved !!</p></div>';
			}
		}
	
		require_once( plugin_dir_path( __FILE__ ) . '../views/admin-settings-test-sync.php' );
	}
	
	static function settings_taxonomy() {
	
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$did_update_options = false;
	
		// Remove LDAP tab if not used
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
}
