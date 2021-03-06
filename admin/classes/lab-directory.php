<?php

class Lab_Directory {
	
	// Declaration of static variables used by almost all admin/frontend page
	
	/*
	 * Lab_Directory::$ld_permissions
	 * list all permissions settings (value is "0" or "1")
	 *
	 * = array {
	 * "wp_editor_settings_general" => string(1) "0",
	 * "wp_author_settings_general" => string(1) "1",
	 * "wp_contributor_settings_general" => string(1) "0",
	 * -------
	 * "ld_post-doctorate_give_phd_status" => string(1) "0"
	 * "ld_internship_give_phd_status" => string(1) "0"
	 * };
	 */
	static $ld_permissions = null;

	/*
	 * Lab_Directory::$capabilities
	 * List all capabilities, their name and scope ("all" or "own")
	 *
	 * Lab_Directory::$capabilities = array(
	 * 'settings_general' => array(
	 * 'name' =>'General settings',
	 * 'scope' =>'all'),
	 * 'settings_permissions' => array(
	 * 'name' =>'Permissions settings',
	 * 'scope' =>'all'),
	 * -------
	 * 'edit_own_staff_profile' => array(
	 * 'name' =>'Edit its own profile',
	 * 'scope' =>'own'),
	 * );
	 */
	static $capabilities = null;

	/*
	 * Lab_Directory_Common::$staff_meta_fields
	 * ordered list of all metafields in and their description
	 *
	 * Lab_Directory_Common::$staff_meta_fields = array(
	 * [0]=>
	 * array(8) {
	 * ["slug"]=> string(9) "firstname"
	 * ["order"]=> string(1) "1"
	 * ["type"]=> string(4) "text"
	 * ["group"]=> string(2) "CV"
	 * ["activated"]=> string(1) "1"
	 * ["multivalue"]=> 'special'
	 * ["ldap_attribute"]=> string(2) "sn"
	 * ["show_frontend"]=> string(1) "1"
	 * }
	 * [1]=>
	 * array(8) {
	 * ["slug"]=> string(4) "name"
	 * ["order"]=> string(1) "2"
	 * ["type"]=> string(4) "text"
	 * ["group"]=> string(2) "CV"
	 * ["activated"]=> string(1) "1"
	 * ["multivalue"]=> special
	 * ["ldap_attribute"]=> string(0) ""
	 * ["show_frontend"]=> string(1) "1"
	 * }
	 * }
	 */
		
	
	
	//
	// Init custom post types
	//
	static function register_actions_and_filters() {

		
		// add_action( 'init', array( 'Lab_Directory', 'lab_directory_flush_rewrite_rules'), 20 );
		
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_ld_permissions' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_capabilities' ) );
		
		add_filter( 'pll_get_post_types',array( 'Lab_Directory',  'add_cpt_to_pll'), 10, 2);
		
		add_filter( 'admin_post_thumbnail_html', array( 'Lab_Directory', 'lab_directory_staff_photo_meta_box' ), 10, 3 );
		add_filter( 
			'manage_edit-lab_directory_staff_columns', 
			array( 'Lab_Directory', 'set_lab_directory_staff_admin_columns' ), 10, 3 );
		add_filter( 
			'manage_lab_directory_staff_posts_custom_column', 
			array( 'Lab_Directory', 'custom_lab_directory_staff_admin_columns' ), 
			10, 
			3 );
		
		add_filter( 'enter_title_here', array( 'Lab_Directory', 'lab_directory_staff_title_text' ) );
		add_filter( 'admin_head', array( 'Lab_Directory', 'remove_media_buttons' ) );
		add_action( 'admin_menu', array( 'Lab_Directory', 'remove_publish_box' ) );
		add_action( 
			'add_meta_boxes_lab_directory_staff', 
			array( 'Lab_Directory', 'add_lab_directory_staff_custom_meta_boxes' ) );
		
		add_action( 'save_post_lab_directory_staff', array( 'Lab_Directory', 'save_meta_boxes' ));
	
		add_action( 'admin_enqueue_scripts', array( 'Lab_Directory', 'lab_directory_scripts_and_css_for_tabs' ) );
		
		add_action( 'admin_title', array( 'Lab_Directory', 'edit_page_title' ) );
		
		add_action( 'init', array( 'Lab_Directory', 'init_tinymce_button' ) );
		
		// TODO Ajax seems broken ?
		add_action( 'wp_ajax_get_my_form', array( 'Lab_Directory', 'thickbox_ajax_form' ) );
		add_action( 'pre_get_posts', array( 'Lab_Directory', 'manage_listing_query' ) );
		add_filter( 'post_row_actions', array( 'Lab_Directory', 'modify_quick_edit' ), 10, 1 );
		add_filter( 'bulk_actions-edit-lab_directory_staff', array( 'Lab_Directory', 'modify_bulk_actions' ), 10, 1 );
		
		add_filter( 'disable_months_dropdown', array( 'Lab_Directory', 'ld_disable_months_dropdown' ), 10, 2 );
		// remove description from taxonomies
		add_filter( 
			'manage_edit-ld_taxonomy_team_columns', 
			array( 'Lab_Directory', 'ld_taxonomy_team_description' ), 
			10, 
			1 );
		add_filter( 
			'manage_edit-ld_taxonomy_laboratory_columns', 
			array( 'Lab_Directory', 'ld_taxonomy_laboratory_description' ), 
			10, 
			1 );
		add_action( 'ld_taxonomy_team_add_form', array( 'Lab_Directory', 'ld_taxonomies_form' ), 10, 2 );
		add_action( 'ld_taxonomy_laboratory_add_form', array( 'Lab_Directory', 'ld_taxonomies_form' ), 10, 2 );
		add_action( 'ld_taxonomy_team_edit_form', array( 'Lab_Directory', 'ld_taxonomies_form' ), 10, 2 );
		add_action( 'ld_taxonomy_laboratory_edit_form', array( 'Lab_Directory', 'ld_taxonomies_form' ), 10, 2 );
		
		// add manager_ids column to taxonomies
		add_filter( 
			'manage_ld_taxonomy_team_custom_column', 
			array( 'Lab_Directory', 'ld_taxonomies_columns_content' ), 
			10, 
			3 );
		add_filter( 
			'manage_ld_taxonomy_laboratory_custom_column', 
			array( 'Lab_Directory', 'ld_taxonomies_columns_content' ), 
			10, 
			3 );
		
		// Add custom field to taxonomies
		add_action( 
			'ld_taxonomy_team_edit_form_fields', 
			array( 'Lab_Directory', 'ld_taxonomy_team_custom_fields' ), 
			10, 
			2 );
		add_action( 
			'ld_taxonomy_team_add_form_fields', 
			array( 'Lab_Directory', 'ld_taxonomy_team_custom_fields' ), 
			10, 
			2 );
		add_action( 
			'ld_taxonomy_laboratory_edit_form_fields', 
			array( 'Lab_Directory', 'ld_taxonomy_laboratory_custom_fields' ), 
			10, 
			2 );
		add_action( 
			'ld_taxonomy_laboratory_add_form_fields', 
			array( 'Lab_Directory', 'ld_taxonomy_laboratory_custom_fields' ), 
			10, 
			2 );
		
		// Save the custom field changes made on taxonomies
		add_action( 'edited_ld_taxonomy_team', array( 'Lab_Directory', 'save_ld_taxonomies_custom_fields' ), 10, 2 );
		add_action( 'created_ld_taxonomy_team', array( 'Lab_Directory', 'save_ld_taxonomies_custom_fields' ), 10, 2 );
		add_action( 
			'edited_ld_taxonomy_laboratory', 
			array( 'Lab_Directory', 'save_ld_taxonomies_custom_fields' ), 
			10, 
			2 );
		add_action( 
			'created_ld_taxonomy_laboratory', 
			array( 'Lab_Directory', 'save_ld_taxonomies_custom_fields' ), 
			10, 
			2 );
		
	
		add_filter( 'get_the_excerpt', array( 'Lab_Directory', 'ld_filter_excerpt' ) );
		
		add_action( 
			'restrict_manage_posts', 
			array( 'Lab_Directory', 'add_lab_directory_staff_categories_admin_filter' ) );
		add_action( 'pre_get_posts', array( 'Lab_Directory', 'filter_admin_lab_directory_staff_by_category' ) );
	
		add_filter( 'post_updated_messages', array( 'Lab_Directory', 'ld_profile_updated_messages' ) );
		
		add_action( 'admin_notices', array( 'Lab_Directory', 'ld_admin_notice__error') );
	}
	
	/**
	 * Flush rewrite rules if the previously added flag exists,
	 * and then remove the flag (this is used in settings).
	 */
	 static function lab_directory_flush_rewrite_rules() {
		if ( get_option( 'lab_directory_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'lab_directory_flush_rewrite_rules_flag' );
		}
	 }

	 /*
	  * This function issue a warning if no [lab-directory] shortcode is found in post/page
	  */
	 static function ld_admin_notice__error() {
	 	if ( (isset (Lab_Directory_Common::$main_ld_permalink['count']))  AND 
	 		Lab_Directory_Common::$main_ld_permalink['count'] > 1) {
	 		// several post or page containing [lab-directory] are defined 
	 		/* 
	 		 * This error is no longer shown since using several language, this page is duplicated... 
	 		$class = 'notice notice-error is-dismissible';
	 		$message = __('Important ! In order to use Lab-Directory, you must define one and only one page or post containing the lab directory shortcode [lab-directory] .', 'lab-directory' );
	 		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	 		*/
	 	}
	 		
	 	if (Lab_Directory_Common::$main_ld_permalink != '') return;
	 	// $main_ld_permalink is not defined 
	 	$class = 'notice notice-error is-dismissible';
	 	$message = __('Important ! In order to use Lab-Directory, you must at least create a page or post containing the lab directory shortcode [lab-directory] .', 'lab-directory' );
	 	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	 }
	 
	/* edit the admin page title for a particular custom post type */
		static function edit_page_title() {
			global $post, $title, $action, $current_screen;
			if( isset( $current_screen->post_type ) && $current_screen->post_type == 'lab_directory_staff' && 
				$action == 'edit' && get_post_type() == 'lab_directory_staff' ) {
				/* this is the new page title */
				$title = sprintf( __('%s profile', 'lab-directory'), $post->post_title);
			} 
			return $title;
		}
		
		static function add_cpt_to_pll($post_types, $hide) {
		// hides 'lab_directory_staff' from the list of custom post types in Polylang settings
		unset($post_types['lab_directory_staff']);
		return $post_types;
	}
	
	/**
	 * Staff profile update messages.
	 * @return array Amended post update messages with new CPT update messages.
	 */
	static function ld_profile_updated_messages( $messages ) {
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );
	
		$messages['lab_directory_staff'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Staff profile updated.', 'lab-directory' ),
			4  => __( 'Staff profile updated.', 'lab-directory' ),
			6  => __( 'Staff profile published.', 'lab-directory' ),
			7  => __( 'Staff profile saved.', 'lab-directory' ),
			8  => __( 'Staff profile submitted.', 'lab-directory' ),
		);
	
		if ( $post_type_object->publicly_queryable && 'lab_directory_staff' === $post_type ) {
			$permalink = get_permalink( $post->ID );
	
			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View staff profile', 'lab-directory' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
	
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'View staff profile', 'lab-directory' ) );
			$messages[ $post_type ][8]  .= $preview_link;
	
		}
	
		return $messages;
	}
		
	static function ld_filter_excerpt( $excerpt ) {
		global $post;
		if ( $post->post_type == 'lab_directory_staff' ) {
			$mails = get_post_meta( $post->ID, 'mails', true );
			Lab_Directory_Common::ld_value_to_something(
				$mails,
				Lab_Directory_Common::$staff_meta_fields['mails']['multivalue'],
				'display' );
				return $mails . ' (' . __( 'Staff directory item', 'lab_directory' ) . ')';
		} else {
			return $excerpt;
		}
	}


	static function set_lab_directory_staff_admin_columns() {
		
		
		$new_columns = array( 
			'cb' => '<input type="checkbox" />', 
			'title' => __( 'Title' ), 
			// 'id' => __( 'ID' ),
			'ld_taxonomy_team' => _x( 'Staff Team', '1st taxonomy singular name', 'lab-directory' ), 
			'ld_taxonomy_laboratory' => _x( 'Laboratories', '2nd taxonomy general name', 'lab-directory' ), 
			'ldap' => __( 'LDAP syncing', 'lab-directory' ), 
			'featured_image' => __( 'Staff photo', 'lab-directory' ), 
			'date' => __( 'Date' ) );
		
		// remove unused taxonomies
		if ( ! get_option( 'lab_directory_use_taxonomy1' ) ) {
			unset( $new_columns['ld_taxonomy_team'] );
		}
		if ( ! get_option( 'lab_directory_use_taxonomy2' ) ) {
			unset( $new_columns['ld_taxonomy_laboratory'] );
		}
		return $new_columns;
	}

	static function custom_lab_directory_staff_admin_columns( $column_name, $post_id ) {
		
		$out = '';
		
		switch ( $column_name ) {
			case 'featured_image' :
				$attachment_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ) );
				$photo_url = $attachment_array[0];
				$out .= '<img src="' . $photo_url . '" style="max-height: 60px; width: auto;" />';
				break;
			
			case 'id' :
				$out .= $post_id;
				break;
			
			case 'ldap' :
				$out = get_post_meta( $post_id, 'ldap', true ) == '1' ? '<span class="dashicons dashicons-yes"></span>' : '';
				break;
			
			case 'ld_taxonomy_team' :
				$out = get_the_term_list( $post_id, 'ld_taxonomy_team', '', ',', '' );
				break;
			
			case 'ld_taxonomy_laboratory' :
				$out = get_the_term_list( $post_id, 'ld_taxonomy_laboratory', '', ',', '' );
				break;
			
			default :
				break;
		}
		echo $out;
	}
	
	// register tabs script
	static function lab_directory_scripts_and_css_for_tabs() {
		
		wp_enqueue_script( 'custom-tabs', LAB_DIRECTORY_URL . '/admin/js/tabs.js', array( 'jquery' ));
		wp_enqueue_script( 'input_datetime', LAB_DIRECTORY_URL . '/admin/js/input_datetime.js', array( 'jquery' ));
		wp_enqueue_script( 
			'timepicker-addon', 
			LAB_DIRECTORY_URL . '/admin/js/jquery.datetimepicker.js', 
			array( 'jquery' ) );
		
		wp_enqueue_style( 'timepicker-addon-css', LAB_DIRECTORY_URL . '/admin/css/jquery.datetimepicker.css' );
		wp_enqueue_style( 'mytimepicker-css', LAB_DIRECTORY_URL . '/admin/css/datetimepicker.css' );
		
		$wp_scripts = wp_scripts();
		wp_enqueue_style( 
			'lab-directory-admin-ui-css', 
			(is_ssl() ? 'https' : 'http') . '://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver .
				 '/themes/smoothness/jquery-ui.css', 
				false, 
				'', 
				false );
	}

	//
	// Custom post type customizations
	//
	static function lab_directory_staff_title_text( $title ) {
		$screen = get_current_screen();
		if ( $screen->post_type == 'lab_directory_staff' ) {
			$title = "Enter lab_directory_staff member's name";
		}
		
		return $title;
	}

	static function remove_media_buttons() {
		$screen = get_current_screen();
		if ( $screen->post_type == 'lab_directory_staff' ) {
			remove_action( 'media_buttons', 'media_buttons' );
		}
	}

	static function add_lab_directory_staff_custom_meta_boxes() {
		
		// Enqueue style and scripts
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'custom-tabs' );
		wp_enqueue_script( 'timepicker-addon' );
		wp_enqueue_style( 'timepicker-addon-css' );
		wp_enqueue_style( 'lab-directory-admin-ui-css' );
		
		// TODO test access rights after plugins loaded
		if (!current_user_can( 'administrator' )) {
			return; 
		}

		
		add_meta_box( 
			'lab_directory_staff-meta-box', 
			__( 'Staff Details', 'lab-directory' ), 
			array( 'Lab_Directory', 'lab_directory_staff_meta_box_output' ), 
			'lab_directory_staff', 
			'normal', 
			'high' );
		add_meta_box( 
			'lab_directory_staff-meta-box_statut', 
			__( 'Staff status', 'lab-directory' ), 
			array( 'Lab_Directory', 'lab_directory_staff_meta_box_statut' ), 
			'lab_directory_staff', 
			'side', 
			'high' );
	}

	/*
	 * A function to save statuss as a comma separated values list of activated statuss
	 */
	static function update_staff_statuss( $post_ID, $statuss ) {
		// Only keep activated status
		$meta_value = '';
		foreach ( $statuss as $status => $value ) {
			if ( $value ) {
				$meta_value .= $status . ',';
			}
		}
		// Set activated statuss to true
		return update_post_meta( $post_ID, 'staff_statuss', $meta_value );
	}

	/*
	 * A function to retrieve statuss from a comma separated values list of activated statuss
	 */
	static function get_staff_statuss( $post_ID ) {
		// Initiate statuss to false for all status
		$statuss = array( 
			'permanent' => false, 
			'administrator' => false, 
			'HDR' => false, 
			'doctorate' => false, 
			'post-doctorate' => false, 
			'internship' => false, 
			'invited' => false, 
			'CDD' => false, 
			'custom_group' => false );
		// Set activated statuss to true
		$activated_statuss = explode( ',', get_post_meta( $post_ID, 'staff_statuss', $single = true ) );
		foreach ( $activated_statuss as $key => $status ) {
			$statuss[$status] = true;
		}
		return $statuss;
	}

	/*
	 * Simply add a metabox to change statut of this staff
	 */
	static function lab_directory_staff_meta_box_statut( $post ) {
		if ( $post->post_status == 'auto-draft' ) {
			// Do not propose status meta_box when adding a new staff
			echo '<p>' .
				 __( 
					'You must first save staff name and firstname before being able to change his/her status', 
					'lab-directory' ) . '</p>';
			
			return;
		}
		
		$statuss = self::get_lab_directory_default_statuss();
		$staff_statuss = self::get_staff_statuss( $post->ID );
		$group_activations = get_option( 'lab_directory_group_activations' );
		
		foreach ( $statuss as $key => $status ) {
			$staff_status = false;
			// TODO add capability
			
			$activated = false;
			if ( ( $key == 'permanent' ) or ( $key == 'administrator' ) or
				 ( isset( $group_activations[$key] ) and $group_activations[$key] ) ) {
				$activated = true;
			}
			$disabled = $activated ? '' : 'disabled ';
			// initially is not set
			if ( isset( $staff_statuss[$key] ) ) {
				$staff_status = $staff_statuss[$key];
			}
			
			?>
<p>
			<?php
			
			if ( ! $activated ) {
				echo '<span class="dashicons dashicons-lock"></span>';
			} else {
				echo '<span class="dashicons"></span>';
			}
			?>
			<input name="status_<?php echo $key; ?>" type="checkbox"
		<?php echo $disabled; ?> value="1"
		<?php checked( true, $staff_status ); ?> /> 
			<?php
			echo $statuss[$key];
			?>
			</p>

<?php
		}
		?>
<p>
	<button name="save" class="button button-primary button-large"
		id="publish2" value="Update" type="submit"><?php _e('Update')?></button>
</p>
<p>Note: Please, modify staff status before modifying staff details.</p>
<p>
	<span class="dashicons dashicons-lock"></span> When a lock is displayed before a status, it has been disabled
	in general settings and is not available.
</p>

<?php
	}

	/*
	 * Disble date filter for lab_directory_staff post
	 */
	static function ld_disable_months_dropdown( $false , $post_type ) {
	
	 return ($post_type =='lab_directory_staff');	
}
/*
	 * Output the meta_box form content
	 *
	 */
	static function lab_directory_staff_meta_box_output( $post ) {
		$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
		$studying_levels = Lab_Directory_Common::get_lab_directory_studying_levels();
		$jury_functions = Lab_Directory_Common::get_lab_directory_jury_functions();
		$staff_statuss = self::get_staff_statuss( $post->ID );
		$group_activations = get_option( 'lab_directory_group_activations' );
		$used_groups = Lab_Directory_Settings::get_used_groups( 
			$active_meta_fields, 
			$staff_statuss, 
			$group_activations['BIO'] );
		wp_enqueue_style( 'font-awesome');
		
		?>
<script type="text/javascript">
		jQuery(document).ready(function($){
			$('#add-new-jury-member').on('click', function(ev){
				ev.preventDefault();
				var tr = $('<tr/>');
				tr.html($('#new-jury-member').html());
				$("#add-new-jury-member-row").before(tr);
			});
		
			$(document).on('click', '.remove-jury-member', function(ev){
				ev.preventDefault();
				$(this).parent().parent().remove();
			});
		});

		function show_hide_social_networks() {
		
		    var elems = document.getElementsByClassName("social_networks");
		   
		    for(var i = 0; i != elems.length; ++i)
		    {
		    	var test = elems[i].style.display;
		    
		    	   if (test == 'none') {
		    		   elems[i].style.display= '';
		    	    } else {
		    	    	elems[i].style.display = 'none';
		    	    }
		    }
		} 
	
		</script>

<style type="text/css">
#new-jury-member {
	display: none;
}

#titlediv {
	display: none;
}

label.lab_directory_staff-label {
	width: 150px;
	display: inline-block;
	vertical-align: top;
}

span.value {
	display: inline-block;
}

.dashicons {
	font-size: 16px;
}

textarea {
	resize: both;
	width: 80%;
}

textarea.large-text, input.large-text {
	width: 80%;
}

.input-in-td {
	width: 100%;
	padding-left: 0;
	padding-right: 0;
}

a.normal {
	color: #0073aa;
}

div.lab_directory_staff_meta {
	padding-left: 5px;
	background-color: rgb(245, 245, 245);
	font-size: 0.9em;
	margin: 1em 0;
}

</style>
<?php
		// Display Form
		?>
	

		<?php
		// TODO pas de ID pour nouveau !!
		echo_form_messages( get_post_meta( $post->ID, 'messages', true ) );
		delete_post_meta( $post->ID, 'messages' );
		
		if ( $post->post_status == 'auto-draft' ) {
			// Add New staff
			$ldap_synced = false;
		} else {
			// Edit staff
			$ldap_synced = ( get_post_meta( $post->ID, 'ldap', true ) != '0' );
		}
		
		?>

<div id="demoTabsId" class="labDirectoryTabsClass">
	<ul>
            <?php
		foreach ( $used_groups as $key => $group_name ) {
			echo '<li><a href="#Tab-' . $key . '">' . $group_name . '</a></li>';
		}
		?>
        </ul>
        <?php
		// TODO disable input depending on capability , LDAP...
		
		foreach ( $used_groups as $key => $group_name ) {
			echo '<div id="Tab-' . $key . '">';
			foreach ( Lab_Directory_Settings::get_lab_directory_staff_meta_fields() as $field ) {
				if ( ( $field['group'] == $key ) and ( $field['activated'] == '1' ) ) {
					Lab_Directory::lab_directory_staff_meta_box_render_input( 
						$post, 
						$field, 
						Lab_Directory_Common::$default_meta_field_names[$field['slug']], 
						$studying_levels, 
						$jury_functions, 
						$ldap_synced );
				}
			}
			echo '</div>';
		}
		?>
       
    </div>
<p>
	<button name="save" class="button button-primary button-large"
		id="publish2" value="Update" type="submit"><?php _e('Update')?></button>
</p>
<?php if ($ldap_synced) {?>
<p>
	<span class="dashicons dashicons-lock"></span>
	<?php echo __( 'These fields are synced with LDAP and cannot be modified.', 'lab-directory' )?>
	
</p>
<?php }?>
    <?php wp_nonce_field( 'lab_directory_staff_meta_box_nonce_action', 'lab_directory_staff_meta_box_nonce' ); ?>
    
		<?php
	}

	static function lab_directory_staff_meta_box_render_input( 
		$post, 
		$field, 
		$field_name, 
		$studying_levels, 
		$jury_functions, 
		$ldap_synced ) {
		
		$field_type = $field['type'];
		
		// TODO disable input depending on capability , LDAP...
		
		// Disable input when field is synced with LDAP
		if ( $ldap_synced and isset( $field['ldap_attribute'] ) and ( $field['ldap_attribute'] != '' ) ) {
			$field_type = 'disabled';
		}
		// Disable wp_user_id field
		if ( $field['slug'] == 'wp_user_id' ) {
			$field_type = 'disabled';
		}
		$required = '';
		if ( ( $field['slug'] == 'name' ) or ( $field['slug'] == 'firstname' ) ) {
			$required = ' required ';
		}
		
		$value = get_post_meta( $post->ID, $field['slug'], true );
		
		if ( $field_type != 'disabled' ) {
			// handle Multivalue
			$mv_cr = false; // only true if "multiple values separated with CR" used
			
			switch ( $field['multivalue'] ) {
				case ',' :
					$mv = '<br /><i>' . __( 'This entry accept multiple values', 'lab-directory' ) . ' (' .
						 __( "Comma (,) separated values", 'lab-directory' ) . ')</i>';
					break;
				case ';' :
					$mv = '<br /><i>' . __( 'This entry accept multiple values', 'lab-directory' ) . ' (' .
						 __( 'Semicolumn (;) separated values', 'lab-directory' ) . ')</i>';
					break;
				case '|' :
					$mv = '<br /><i>' . __( 'This entry accept multiple values', 'lab-directory' ) . ' (' .
						 __( 'Vertical bar (|) separated values', 'lab-directory' ) . ')</i>';
					break;
				case '/' :
					$mv = '<br /><i>' . __( 'This entry accept multiple values', 'lab-directory' ) . ' (' .
						 __( 'Slash (/) separated values', 'lab-directory' ) . ')</i>';
					break;
				case 'MV' :
				case 'CR' :
					$mv = '<br /><i>' . __( 
						'This entry accept multiple values (one value per line, values separated by a carriage return)', 
						'lab-directory' ) . ')</i>';
					$mv_cr = true;
					break;
				default :
					$mv = '';
					break;
			}
			
			// override $field_type in case of multiple value accepted
			if ( $mv_cr ) {
				// Switch to a textarea as input because value separated by a CR
				if ( ( $field_type == 'text' ) or ( $field_type == 'mail' ) or ( $field_type == 'url' ) or
					 ( $field_type == 'phone_number' ) or ( $field_type == 'longtext' ) ) {
					$field_type = 'textarea';
				}
			} elseif ( $mv ) {
				// else switch to a longtext
				if ( ( $field_type == 'text' ) or ( $field_type == 'mail' ) or ( $field_type == 'url' ) or
					 ( $field_type == 'phone_number' ) ) {
					$field_type = 'longtext';
				}
			}
		}
		
		// Label $label = '';
		
		$label = '<label for="lab_directory_staff_meta_' . $field['slug'] . '" class="lab_directory_staff-label">';
		if ( $field_type == 'disabled' ) {
			$label .= '<span class="dashicons dashicons-lock"></span>';
		}
		$label .= $field_name . '</label>';
		echo '<div class="lab_directory_staff_meta">';
		
		switch ( $field_type ) {
			case 'text' :
			case 'mail' :
			case 'phone_number' :
				echo $label;
				// $required is only used for name and firstname
				?>
<input type="text"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	<?php echo $required; ?> value="<?php echo $value; ?>" />
<?php
				echo $mv;
				break;
			case 'url' :
				echo $label;
				// $required is only used for name and firstname
				?>
<input class="large-text" type="text"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	<?php echo $required; ?> value="<?php echo $value; ?>" />
<?php
				echo $mv;
				break;
			case 'longtext' :
				echo $label;
				?>
<textarea rows="1"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	cols=""><?php echo $value; ?></textarea>
<?php
				echo $mv;
				break;
			case 'textarea' :
				echo $label;
				?>
<textarea rows="2"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	cols=""><?php echo $value; ?></textarea>
<?php
				echo $mv;
				break;
			case 'editor' :
				echo $label;
				$name = 'lab_directory_staff_meta_' . $field['slug'];
				wp_editor( $value, $name, null); //TODO add $editor_args );
				break;
			case 'date' :
				echo $label;
				?>
<input type="text" class="datepicker"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	value="<?php echo $value; ?>" />
<?php
				break;
			case 'datetime' :
				echo $label;
				?>
<input type="text" class="datetimepicker"
	name="lab_directory_staff_meta_<?php echo $field['slug']; ?>"
	value="<?php echo $value; ?>" />
<?php
				break;
			case 'url' :
			case 'phone_number' :
			case 'studying_level' :
				echo $label;
				echo Lab_Directory_Admin::lab_directory_create_select( 
					'lab_directory_staff_meta_' . $field['slug'], 
					$studying_levels, 
					get_post_meta( $post->ID, $field['slug'], false, __( 'None' ) ) );
				break;
			case 'jury' :
				echo $label;
				
				$jury_members = $value;
				if ( is_array( $jury_members ) ) {
					$nb_members = count( $jury_members );
				} else {
					$nb_members = 0;
					$jury_members = array();
				}
				
				for ( $i = $nb_members; $i < max( $nb_members, 8 ); $i++ ) {
					$jury_members[] = array( 'order' => $i + 1, 'function' => '', 'name' => '', 'title' => '' );
				}
				?>
<table class="widefat fixed striped" cellspacing="0"
	id="lab_directory_staff-meta-fields">
	<thead>
		<tr>
			<th id="columnname" scope="col" style="width: 5%;">Order</th>
			<th id="columnname" scope="col" style="width: 20%;">Function</th>
			<th id="columnname" scope="col" style="width: 25%;">Name</th>
			<th id="columnname" scope="col">Title, University, enterprise</th>
			<td style="width: 5%;"></td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th id="columnname" scope="col">Order</th>
			<th id="columnname" scope="col">Function</th>
			<th id="columnname" scope="col">Name</th>
			<th id="columnname" scope="col">Title, University, enterprise</th>
			<td></td>
		</tr>
	</tfoot>

	<tbody>
					<?php
				$index = 0;
				foreach ( $jury_members as $jury_member ) {
					$index++;
					?>
					<tr>
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_orders[]"
				style="width: 40px;" value="<?php echo $index; ?>" /></td>
			<td><?php
					
					echo Lab_Directory_Admin::lab_directory_create_select( 
						'lab_directory_staff_meta_' . $field['slug'] . '_functions[]', 
						$jury_functions, 
						$jury_member['function'], 
						false, 
						'input-in-td', 
						' ' ); // TODO(true) ?>
			</td>
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_names[]"
				class="input-in-td"
				value="<?php echo $jury_member['name']; ?>" /></td>
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_titles[]"
				class="input-in-td"
				value="<?php echo $jury_member['title']; ?>" /></td>
			<td>
              <a href="#" class="normal remove-jury-member"><span class="dashicons dashicons-trash"></span></a>
        	</td>
		</tr>
					<?php
				}
				?>
		<tr id="add-new-jury-member-row" valign="top">
			<td colspan="4"><a href="#" class="normal" id="add-new-jury-member">+
					Add New jury member</a></td>
		</tr>
		<tr id="new-jury-member">
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_orders[]"
				style="width: 40px;" value="<?php echo $index; ?>" /></td>
			<td><?php
				
				echo Lab_Directory_Admin::lab_directory_create_select( 
					'lab_directory_staff_meta_' . $field['slug'] . '_functions[]', 
					$jury_functions, 
					'', false, 
					'input-in-td', 
					' ' ); // TODO(true) ?>
					</td>
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_names[]"
				class="input-in-td"
				value="" /></td>
			<td><input type="text"
				name="lab_directory_staff_meta_<?php echo $field['slug']; ?>_titles[]"
				class="input-in-td"
				value="" /></td>
			<td>
              <a href="#" class="remove-jury-member"><span class="dashicons dashicons-trash"></span></a>
        	</td>
		</tr>
	</tbody>
</table>
<?php
				break;
			case 'social_network' :
				echo $label;
				$possible_social_networks = self::get_possible_social_networks();
				$lab_directory_used_social_networks = get_option( 'lab_directory_used_social_networks', false );
				if ( empty( $lab_directory_used_social_networks ) ) {
					// This should not be the case but...
					echo __( 'No Social networks activated yet! (ask to an administrator)', 'lab-directory' );
				} else {
					if ( count( $lab_directory_used_social_networks ) > 2 ) {
						// Add fold unfold buttoon if more than 2 entries
						echo '<button onclick="show_hide_social_networks(); return false;">' .
							 __( 'Fold/Unfold Social networks input', 'lab-directory' ) .
							 '<span class="social_networks" style="display:none;"><span class="dashicons dashicons-arrow-down"></span></span>' .
							 '<span class="social_networks"><span class="dashicons dashicons-arrow-up"></span></span></button>';
					}
					
					wp_enqueue_style( 'social-icons-css', LAB_DIRECTORY_URL . '/common/css/social_icons.css');
					$used = '';
					
					foreach ( $lab_directory_used_social_networks as $key => $temp ) {
						$icon = Lab_Directory_Common::ld_network_icon( $key );
						$url = isset( $value[$key] ) ? $value[$key] : '';
						$used .= '<p class="social_networks">';
						$used .= '<label  class="lab_directory_staff-label" >' . $icon . ' ' .
							 $possible_social_networks[$key] . '</label>';
						$used .= '<input name="lab_directory_staff_meta_social_network[' . $key . ']" value="' . $url .
							 '" type="text" size="60"></p>';
						;
					}
					echo $used;
				}
				
				break;
			case 'disabled' : // Only display field value
			default : // We should never arrive to default !!
				
				echo $label;
				Lab_Directory_Common::ld_value_to_something( $value, $field['multivalue'], 'display' );
				echo '<span class="value">' . $value . '</span>'; 
				break;
		}
		echo '</div>';
		?>

<div class="clear"></div>
<?php
	}

	static function save_meta_boxes( $post_id ) {
		global $wpdb, $post;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['lab_directory_staff_meta_box_nonce'] ) || ! wp_verify_nonce( 
			$_POST['lab_directory_staff_meta_box_nonce'], 
			'lab_directory_staff_meta_box_nonce_action' ) ) {
			return;
		}
		
		// TODO adjust access rights
		if (!current_user_can( 'administrator' )) {
			return;
		}
		
		if ( ! current_user_can( 'edit_post', get_the_id() ) ) {
			return;
		}
		
		if ( get_post_status( $post_id ) == 'draft' ) {
			// Add New staff
			$ldap_synced = false;
			update_post_meta( $post_id, 'ldap', '0' );
			$post_title = get_option( 'lab_directory_title_firstname_first' ) ? 
				$_POST['lab_directory_staff_meta_firstname'] . ' ' . 
				$_POST['lab_directory_staff_meta_name'] : 
				$_POST['lab_directory_staff_meta_name'] . ' ' .
				$_POST['lab_directory_staff_meta_firstname'];
  
			// Update the post title  the database
  			wp_update_post( array(
				      'ID'           => $post_id,
				      'post_title'   => $post_title,
				      'post_status'  => 'publish',
				  ) );
			clean_post_cache( $post_id );
		} else {
			// Edit staff
			$ldap_synced = ( get_post_meta( $post_id, 'ldap', true ) != '0' );
		}
		
		if ( $_POST['save'] == 'Update' ) { // Now we save all (status + All tabs) at the same time if ( $_POST['save'] == 'Update_Status' ) {
			// Update Title sanitize_text_field(
			$post_title = get_option( 'lab_directory_title_firstname_first' ) ? 
				get_post_meta( $post_id,'firstname',true ) . ' ' . 
				get_post_meta( $post_id, 'name', true ) : 
				get_post_meta( $post_id, 'name', true ) . ' ' .
				get_post_meta( $post_id, 'firstname', true );
		
			$wpdb->update( 
				$wpdb->posts, 
				array( 'post_title' => $post_title ), 
				array( 'ID' => $post_id ) );
			
			// Update staff status
			$statuss = self::get_lab_directory_default_statuss();
			$staff_statuss = array();
			foreach ( $statuss as $key => $status ) {
				$staff_statuss[$key] = isset( $_POST['status_' . $key] );
			}
			self::update_staff_statuss( $post_id, $staff_statuss );
			// return; now we continue 
		}
		
		// Else update staff entry
		if ( $_POST['save'] == 'Update' ) {
			
			$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
			$staff_statuss = self::get_staff_statuss( $post_id );
			
			$group_activations = get_option( 'lab_directory_group_activations' );
			$used_groups = Lab_Directory_Settings::get_used_groups( 
				$active_meta_fields, 
				$staff_statuss, 
				$group_activations['BIO'] );
			
			// Process form
			// TODO this is not displayed !!
			$form_messages = array( 'form_saved' => false );
			// Notice: this error detection can fail if 2 users modify the same staff detail at the same time
			// TODO add $form_messages !!
			update_post_meta( $post_id, 'messages', $form_messages );
			
			// Loop for each group first (needed to simply add capacity hereafter)
			foreach ( $used_groups as $key => $group_name ) {
				// Then do it for each field in a group
				foreach ( Lab_Directory_Settings::get_lab_directory_staff_meta_fields() as $field ) {
					
					if ( $field['group'] == $key ) {
						$field_type=$field['type'];
						// TODO disable input depending on capability , LDAP...
						
						// Disable input when field is synced with LDAP
						if ( $ldap_synced and isset( $field['ldap_attribute'] ) ) {
							if ( $field['ldap_attribute'] ) {
								$field_type = 'disabled';
							}
						}
						// Disable wp_user_id field
						if ( $field['slug'] == 'wp_user_id' ) {
							$field_type = 'disabled';
						}
						if ( $field_type != 'disabled' ) {
							self::lab_directory_save_meta_boxes_save_meta( 
								$post_id, 
								$field, 
								Lab_Directory_Common::$default_meta_field_names[$field['slug']] );
						}
					}
				}
			}
		}
		return;
	}

	static function lab_directory_save_meta_boxes_save_meta( $post_id, $field, $field_name ) {
		$slug = 'lab_directory_staff_meta_' . $field['slug'];
		
		// Do not save if meta field is disabled or unset (EXCEPTED FOR JURY)
		if ( ($field['type']!='jury') AND (! isset( $_POST[$slug] ) ) ) {
			return;
		}
		
		// Unsanitized value
		$value = $_POST[$slug];
		switch ( $field['type'] ) {
			case 'text' :
				$value = sanitize_text_field( $value );
				break;
			case 'longtext' :
				$value = sanitize_text_field( $value );
				break;
			case 'textarea' :
				$value = esc_textarea( $value );
				break;
			case 'editor' :
				$value = esc_textarea( $value );
				break;
			case 'date' :
				// format 'Y-m-d' imposed for ordering
				$value = self::lab_directory_strtotime( $value, "Y-m-d" );
				break;
			case 'datetime' :
				// format 'Y-m-d H:i' imposed for ordering
				$value = self::lab_directory_strtotime( $value, "Y-m-d H:i" );
				break;
			case 'mail' :
				$value = sanitize_email( $value );
				break;
			case 'url' :
				$value = esc_url( $value );
				break;
			case 'phone_number' :
				$value = sanitize_text_field( $value );
				break;
			case 'studying_level' :
				$value = sanitize_text_field( $value );
				break;
			case 'jury' : // phd_jury AND hdr_jury
				$orders = $_POST[$slug . '_orders'];
				$names = $_POST[$slug . '_names'];
				$functions = $_POST[$slug . '_functions'];
				$titles = $_POST[$slug . '_titles'];
				
				$index = 0;
				if (! isset( $names ) ) {
					return;
				}
				
				$value = array();
				foreach ( $orders as $order ) {
					if ( $names[$index] ) {
						$function = sanitize_text_field( $functions[$index] ); 
						$function = $function == 'none' ? '' : $function;
						$value[] = array( 
							'order' => (int) $order, 
							'function' => $function, 
							'name' => sanitize_text_field( $names[$index] ), 
							'title' => sanitize_text_field( $titles[$index] ) );
					}
					$index++;
				}
				// Sort jury members
				usort( $value, __NAMESPACE__ . 'self::ld_compare_jury_order' );
				break;
			case 'social_network' :
				
				$temp = array();
				foreach ( $value as $key => $url ) {
					if ( $url ) {
						$temp[$key] = esc_url( $url );
					}
				}
				$value = $temp;
				break;
			
			default : // We should never arrive there !!
			          // update_post_meta( $post_id, $meta_field_slug, esc_attr( $_POST['lab_directory_staff_meta'][
			          // $meta_field_slug ] ) );
				die( 'OUPS Something went wrong!! ' . $field['type'] );
				$value = esc_attr( $value );
				
				break;
		}
		
		if ( $value !== null ) {
			update_post_meta( $post_id, $field['slug'], $value );
		}
	}

	static function set_default_meta_fields_if_necessary() {
		$current_meta_fields = get_option( 'lab_directory_staff_meta_fields' );
		
		if ( $current_meta_fields === false || empty($current_meta_fields) ) {
			$default_meta_fields = Lab_Directory::get_default_meta_fields();
			update_option( 'lab_directory_staff_meta_fields', $default_meta_fields );
		}
	}

	/**
	 * This function in taken from Open source  somatic framework 
	 * https://wordpress.org/plugins/somatic-framework/
	 *
	 * Download an image from the specified URL and attach it to a post.
	 * Modified version of core function media_sideload_image() in /wp-admin/includes/media.php  (which returns an html img tag instead of attachment ID)
	 * Additional functionality: ability override actual filename, set as post thumbnail, and to pass $post_data to override values in wp_insert_attachment (original only allowed $desc)
	 *
	 *  
	 * @since 1.4
	 *
	 * @param string $url (required) The URL of the image to download
	 * @param int $post_id (required) The post ID the media is to be associated with
	 * @param bool $thumb (optional) Whether to make this attachment the Featured Image for the post
	 * @param string $filename (optional) Replacement filename for the URL filename (do not include extension)
	 * @param array $post_data (optional) Array of key => values for wp_posts table (ex: 'post_title' => 'foobar', 'post_status' => 'draft')
	 * @return int|object The ID of the attachment or a WP_Error on failure
	 */
	static function attach_external_image( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
		if ( ! $url || ! $post_id )
			return new WP_Error( 'missing', "Need a valid URL and post ID..." );
			// if ( !self::array_is_associative( $post_data ) ) return new WP_Error('missing', "Must pass post data as
		// associative array...");
			
		// Download file to temp location, returns full server path to temp file, ex;
		// /home/somatics/public_html/mysite/wp-content/26192277_640.tmp MUST BE FOLLOWED WITH AN UNLINK AT SOME POINT
		$tmp = download_url( $url );
		
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] ); // clean up
			$file_array['tmp_name'] = '';
			return $tmp; // output wp_error
		}
		
		preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches ); // fix file filename for
		                                                                                     // query strings
		$url_filename = basename( $matches[0] ); // extract filename from url for title
		$url_type = wp_check_filetype( $url_filename ); // determine file type (ext and mime/type)
		                                              
		// override filename if given, reconstruct server path
		if ( ! empty( $filename ) ) {
			$filename = sanitize_file_name( $filename );
			$tmppath = pathinfo( $tmp ); // extract path parts
			$new = $tmppath['dirname'] . "/" . $filename . "." . $tmppath['extension']; // build new path
			rename( $tmp, $new ); // renames temp file on server
			$tmp = $new; // push new filename (in path) to be used in file array later
		}
		
		// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
		$file_array['tmp_name'] = $tmp; // full server path to temp file
		
		if ( ! empty( $filename ) ) {
			$file_array['name'] = $filename . "." . $url_type['ext']; // user given filename for title, add original URL
			                                                          // extension
		} else {
			$file_array['name'] = $url_filename; // just use original URL filename
		}
		
		// set additional wp_posts columns
		if ( empty( $post_data['post_title'] ) ) {
			$post_data['post_title'] = basename( $url_filename, "." . $url_type['ext'] ); // just use the original filename
			                                                                            // (no extension)
		}
		
		// make sure gets tied to parent
		if ( empty( $post_data['post_parent'] ) ) {
			$post_data['post_parent'] = $post_id;
		}
		
		// required libraries for media_handle_sideload
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		require_once ( ABSPATH . 'wp-admin/includes/media.php' );
		require_once ( ABSPATH . 'wp-admin/includes/image.php' );
		
		// do the validation and storage stuff
		$att_id = media_handle_sideload( $file_array, $post_id, null, $post_data ); // $post_data can override the items
		                                                                            // saved to wp_posts table, like
		                                                                            // post_mime_type, guid, post_parent,
		                                                                            // post_title, post_content, post_status
		                                                                            
		// If error storing permanently, unlink
		if ( is_wp_error( $att_id ) ) {
			@unlink( $file_array['tmp_name'] ); // clean up
			return $att_id; // output wp_error
		}
		
		// set as post thumbnail if desired
		if ( $thumb ) {
			set_post_thumbnail( $post_id, $att_id );
		}
		
		return $att_id;
	}

	static function get_default_meta_fields() {
		
		/*
		 * $default_meta_fields list all predefined fields usable in lab directory
		 *
		 * structure of this variable:
		 *
		 * name : the name of the field in english default language (translatable)
		 * type : type of this field (see $default_type)
		 * slug : the slug define the field, it cannot be changed
		 * ldap_attribute : empty string or ldap attribute used for syncing
		 * multivalue : as defined in $default_multivalue
		 * =SV for special fields (example jury, dates, studying level...)
		 * show_frontend : '1' if this field should not be displayed in frontend
		 * predefined : '1' if this field is predefined by the plugin (always here)
		 */
		$default_meta_fields = array( 
			array( 
				'order' => 1, 
				'type' => 'text', 
				'slug' => 'firstname', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 2, 
				'type' => 'text', 
				'slug' => 'name', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 3, 
				'type' => 'longtext', 
				'slug' => 'position', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 4, 
				'type' => 'text', 
				'slug' => 'login', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '0', 
				'activated' => '1' ), 
			array( 
				'order' => 4.5, 
				'type' => 'text', 
				'slug' => 'wp_user_id', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '0', 
				'activated' => '1' ), 
			array( 
				'order' => 5, 
				'type' => 'mail', 
				'slug' => 'mails', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 5, 
				'type' => 'mail', 
				'slug' => 'other_mails', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			
			array( 
				'order' => 6, 
				'type' => 'editor', 
				'slug' => 'bio', 
				'group' => 'BIO', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 7, 
				'type' => 'text', 
				'slug' => 'idhal', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 8, 
				'type' => 'url', 
				'slug' => 'photo_url', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 9, 
				'type' => 'url', 
				'slug' => 'webpage', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 10, 
				'type' => 'text', 
				'slug' => 'function', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 10.5, 
				'type' => 'social_network', 
				'slug' => 'social_network', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 11, 
				'type' => 'longtext', 
				'slug' => 'title', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 12, 
				'type' => 'phone_number', 
				'slug' => 'phone_number', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 13, 
				'type' => 'phone_number', 
				'slug' => 'fax_number', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 14, 
				'type' => 'text', 
				'slug' => 'office', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 15, 
				'type' => 'text', 
				'slug' => 'team', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'MV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 16, 
				'type' => 'date', 
				'slug' => 'exit_date', 
				'group' => 'CV', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '0', 
				'activated' => '1' ), 
			array( 
				'order' => 17, 
				'type' => 'longtext', 
				'slug' => 'hdr_subject', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 17, 
				'type' => 'longtext', 
				'slug' => 'hdr_subject_lang1', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 17, 
				'type' => 'longtext', 
				'slug' => 'hdr_subject_lang2', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 18, 
				'type' => 'datetime', 
				'slug' => 'hdr_date', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 19, 
				'type' => 'longtext', 
				'slug' => 'hdr_location', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 22, 
				'type' => 'jury', 
				'slug' => 'hdr_jury', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 23, 
				'type' => 'url', 
				'slug' => 'hdr_url', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array(
				'order' => 23,
				'type' => 'url',
				'slug' => 'hdr_summary_url',
				'group' => 'HDR',
				'ldap_attribute' => '',
				'multivalue' => 'SV',
				'show_frontend' => '1',
				'activated' => '1' ),
			array( 
				'order' => 21, 
				'type' => 'editor', 
				'slug' => 'hdr_resume', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 21, 
				'type' => 'editor', 
				'slug' => 'hdr_resume_lang1', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 21, 
				'type' => 'editor', 
				'slug' => 'hdr_resume_lang2', 
				'group' => 'HDR', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 22, 
				'type' => 'date', 
				'slug' => 'phd_start_date', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 22, 
				'type' => 'date', 
				'slug' => 'phd_end_date', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ),
			array( 
				'order' => 23, 
				'type' => 'url', 
				'slug' => 'phd_url', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array(
				'order' => 23,
				'type' => 'url',
				'slug' => 'phd_summary_url',
				'group' => 'doctorate',
				'ldap_attribute' => '',
				'multivalue' => 'SV',
				'show_frontend' => '1',
				'activated' => '1' ),
			array( 
				'order' => 23, 
				'type' => 'longtext', 
				'slug' => 'phd_subject', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 23, 
				'type' => 'longtext', 
				'slug' => 'phd_subject_lang1', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 23, 
				'type' => 'longtext', 
				'slug' => 'phd_subject_lang2', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 24, 
				'type' => 'datetime', 
				'slug' => 'phd_date', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'special', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 25, 
				'type' => 'longtext', 
				'slug' => 'phd_location', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 26, 
				'type' => 'jury', 
				'slug' => 'phd_jury', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 27, 
				'type' => 'editor', 
				'slug' => 'phd_resume', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 27, 
				'type' => 'editor', 
				'slug' => 'phd_resume_lang1', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 27, 
				'type' => 'editor', 
				'slug' => 'phd_resume_lang2', 
				'group' => 'doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 28, 
				'type' => 'date', 
				'slug' => 'post_doc_start_date', 
				'group' => 'post-doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 29, 
				'type' => 'date', 
				'slug' => 'post_doc_end_date', 
				'group' => 'post-doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 30, 
				'type' => 'longtext', 
				'slug' => 'post_doc_subject', 
				'group' => 'post-doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 30, 
				'type' => 'longtext', 
				'slug' => 'post_doc_subject_lang1', 
				'group' => 'post-doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 30, 
				'type' => 'longtext', 
				'slug' => 'post_doc_subject_lang2', 
				'group' => 'post-doctorate', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 31, 
				'type' => 'date', 
				'slug' => 'internship_start_date', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32, 
				'type' => 'date', 
				'slug' => 'internship_end_date', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.1, 
				'type' => 'longtext', 
				'slug' => 'internship_subject', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.1, 
				'type' => 'longtext', 
				'slug' => 'internship_subject_lang1', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.1, 
				'type' => 'longtext', 
				'slug' => 'internship_subject_lang2', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.2, 
				'type' => 'editor', 
				'slug' => 'internship_resume', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.2, 
				'type' => 'editor', 
				'slug' => 'internship_resume_lang1', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 32.2, 
				'type' => 'editor', 
				'slug' => 'internship_resume_lang2', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 33, 
				'type' => 'longtext', 
				'slug' => 'studying_school', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 34, 
				'type' => 'studying_level', 
				'slug' => 'studying_level', 
				'group' => 'internship', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 35, 
				'type' => 'date', 
				'slug' => 'invitation_start_date', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 36, 
				'type' => 'date', 
				'slug' => 'invitation_end_date', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 37, 
				'type' => 'longtext', 
				'slug' => 'invitation_goal', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 37, 
				'type' => 'longtext', 
				'slug' => 'invitation_goal_lang1', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 37, 
				'type' => 'longtext', 
				'slug' => 'invitation_goal_lang2', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 38, 
				'type' => 'longtext', 
				'slug' => 'invited_position', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 39, 
				'type' => 'longtext', 
				'slug' => 'invited_origin', 
				'group' => 'invited', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 40, 
				'type' => 'date', 
				'slug' => 'cdd_start_date', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 41, 
				'type' => 'date', 
				'slug' => 'cdd_end_date', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 42, 
				'type' => 'longtext', 
				'slug' => 'cdd_goal', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 42, 
				'type' => 'longtext', 
				'slug' => 'cdd_goal_lang1', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 42, 
				'type' => 'longtext', 
				'slug' => 'cdd_goal_lang2', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ), 
			array( 
				'order' => 43, 
				'type' => 'longtext', 
				'slug' => 'cdd_position', 
				'group' => 'CDD', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '1' ) );

		
		for ( $i = 1; $i <= 10; $i++ ) {
			$default_meta_fields[] = array( 
				'order' => 50 + $i, 
				'type' => 'text', 
				'slug' => "custom_field_$i", 
				'group' => 'custom_group', 
				'ldap_attribute' => '', 
				'multivalue' => 'SV', 
				'show_frontend' => '1', 
				'activated' => '0' );
		}
		$output = array();
		// reconstruct the array adding slug as key
		foreach ( $default_meta_fields as $default_meta_field ) {
			$output[$default_meta_field['slug']] = $default_meta_field;
		}
		return $output;
	}
	
	//
	// TODO TEMPORARY, REMOVE THIS FUNCTION
	// Try to import from spip
	//
	static function import_spip_staff() {
		// TODO adjust access rights
		if (!current_user_can( 'administrator' )) {
			return;
		}
		
		require_once (LAB_DIRECTORY_DIR . '/temp/import_spip.php' );
		return;
	}
	
	//
	// Related to old lab_directory_staff members
	//
	static function has_old_lab_directory_staff_table() {
		global $wpdb;
		$lab_directory_table = $wpdb->prefix . 'lab_directory';
		
		$old_lab_directory_staff_sql = "SHOW TABLES LIKE '$lab_directory_table'";
		$old_lab_directory_staff_table_results = $wpdb->get_results( $old_lab_directory_staff_sql );
		
		return count( $old_lab_directory_staff_table_results ) > 0;
	}

	static function show_import_message() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'lab-directory-import' && isset( $_GET['import'] ) &&
			 $_GET['import'] == 'true' ) {
			return false;
		}
		
		return Lab_Directory::has_old_lab_directory_staff_table();
	}

	static function get_old_lab_directory_staff( $orderby = null, $order = null, $filter = null ) {
		global $wpdb;
		$lab_directory_table = $wpdb->prefix . 'lab_directory';
		$lab_directory_categories = $wpdb->prefix . 'lab_directory_categories';
		
		if ( ( isset( $orderby ) and $orderby != '' ) and ( isset( $order ) and $order != '' ) and
			 ( isset( $filter ) and $filter != '' ) ) {
			
			if ( $orderby == 'name' ) {
				
				$all_lab_directory_staff = $wpdb->get_results( 
					"SELECT * FROM " . LAB_DIRECTORY_TABLE .
					 " WHERE custom_groupcategorycustom_group = $filter ORDER BY custom_groupnamecustom_group $order" );
			}
			
			if ( $orderby == 'category' ) {
				
				$categories = $wpdb->get_results( 
					"SELECT * FROM $lab_directory_categories WHERE custom_groupcat_idcustom_group = $filter ORDER BY name $order" );
				
				foreach ( $categories as $category ) {
					$cat_id = $category->cat_id;
					// echo $cat_id;
					$lab_directory_staff_by_cat = $wpdb->get_results( 
						"SELECT * FROM " . LAB_DIRECTORY_TABLE . " WHERE custom_groupcategorycustom_group = $cat_id" );
					foreach ( $lab_directory_staff_by_cat as $lab_directory_staff ) {
						$all_lab_directory_staff[] = $lab_directory_staff;
					}
				}
			}
			
			return $all_lab_directory_staff;
		} elseif ( ( isset( $orderby ) and $orderby != '' ) and ( isset( $order ) and $order != '' ) ) {
			
			if ( $orderby == 'name' ) {
				
				$all_lab_directory_staff = $wpdb->get_results( 
					"SELECT * FROM " . LAB_DIRECTORY_TABLE . " ORDER BY custom_groupnamecustom_group $order" );
			}
			
			if ( $orderby == 'category' ) {
				
				$all_lab_directory_staff = $wpdb->get_results( 
					"SELECT * FROM " . LAB_DIRECTORY_TABLE . " ORDER BY category $order" );
			}
			
			return $all_lab_directory_staff;
		} elseif ( isset( $filter ) and $filter != '' ) {
			
			$all_lab_directory_staff = $wpdb->get_results( 
				"SELECT * FROM " . LAB_DIRECTORY_TABLE . " WHERE custom_groupcategorycustom_group = $filter" );
			if ( isset( $all_lab_directory_staff ) ) {
				return $all_lab_directory_staff;
			}
		} else {
			
			return $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE );
		}
	}

	// OBSOLETE Keep it for a futeure import from CSV 
	static function import_old_lab_directory_staff() {
		global $wpdb;
		
		// TODO adjust access rights
		if (!current_user_can( 'administrator' )) {
			return;
		}
		
		
		$old_categories_table = $wpdb->prefix . 'lab_directory_categories';
		$old_lab_directory_table = $wpdb->prefix . 'lab_directory';
		// $old_templates_table = LAB_DIRECTORY_TEMPLATES;
		
		//
		// Copy old categories over first
		//
		
		$old_lab_directory_staff_categories_sql = "
      SELECT
        cat_id, name

      FROM
        $old_categories_table
    ";
		
		$old_lab_directory_staff_categories = $wpdb->get_results( $old_lab_directory_staff_categories_sql );
		
		foreach ( $old_lab_directory_staff_categories as $category ) {
			wp_insert_term( $category->name, 'lab_category' );
		}
		
		//
		// Now copy old lab_directory_staff members over
		//
		
		$old_lab_directory_staff = Lab_Directory::get_old_lab_directory_staff();
		foreach ( $old_lab_directory_staff as $lab_directory_staff ) {
			$new_lab_directory_staff_array = array( 
				'post_title' => $lab_directory_staff->name, 
				'post_content' => $lab_directory_staff->bio, 
				'post_type' => 'lab_directory_staff', 
				'post_status' => 'publish' );
			$new_lab_directory_staff_post_id = wp_insert_post( $new_lab_directory_staff_array );
			update_post_meta( $new_lab_directory_staff_post_id, 'position', $lab_directory_staff->position );
			update_post_meta( $new_lab_directory_staff_post_id, 'email', $lab_directory_staff->email_address );
			update_post_meta( $new_lab_directory_staff_post_id, 'phone_number', $lab_directory_staff->phone_number );
			
			if ( isset( $lab_directory_staff->category ) ) {
				$old_category_sql = "
          SELECT
            cat_id, name

          FROM
            $old_categories_table

          WHERE
            cat_id=$lab_directory_staff->category
        ";
				$old_category = $wpdb->get_results( $old_category_sql );
				$new_category = get_term_by( 'name', $old_category[0]->name, 'lab_category' );
				wp_set_post_terms( $new_lab_directory_staff_post_id, array( $new_category->term_id ), 'lab_category' );
			}
			
			if ( isset( $lab_directory_staff->photo ) && $lab_directory_staff->photo != '' ) {
				$upload_dir = wp_upload_dir();
				$upload_dir = $upload_dir['basedir'];
				$image_path = $upload_dir . '/lab-directory-photos/' . $lab_directory_staff->photo;
				$filetype = wp_check_filetype( $image_path );
				$attachment_id = wp_insert_attachment( 
					array( 
						'post_title' => $lab_directory_staff->photo, 
						'post_content' => '', 
						'post_status' => 'publish', 
						'post_mime_type' => $filetype['type'] ), 
					$image_path, 
					$new_lab_directory_staff_post_id );
				set_post_thumbnail( $new_lab_directory_staff_post_id, $attachment_id );
			}
		}
		
		//
		// Now copy templates over
		//
		
		$old_html_template_sql = "
      SELECT
        template_code

      FROM
        $old_templates_table

      WHERE
        template_name='lab_directory_staff_index_html'
    ";
		$old_html_template_results = $wpdb->get_results( $old_html_template_sql );
		// update_option( 'lab_directory_html_template', $old_html_template_results[0]->template_code );
		
		$old_css_template_sql = "
      SELECT
        template_code

      FROM
        $old_templates_table

      WHERE
        template_name='lab_directory_staff_index_css'
    ";
		$old_css_template_results = $wpdb->get_results( $old_css_template_sql );
		// update_option( 'lab_directory_css_template', $old_css_template_results[0]->template_code );
		
		//
		// Now delete the old tables
		//
		
		$drop_tables_sql = "
      DROP TABLE
        $old_categories_table, $old_lab_directory_table, $old_templates_table
    ";
		$wpdb->get_results( $drop_tables_sql );
	}

	static function init_tinymce_button() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) &&
			 get_user_option( 'rich_editing' ) == 'true' ) {
			return;
		}
		
		add_filter( "mce_external_plugins", array( 'Lab_Directory', 'register_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( 'Lab_Directory', 'add_tinymce_button' ) );
	}

	static function register_tinymce_plugin( $plugin_array ) {
		$plugin_array['lab_directory_button'] = LAB_DIRECTORY_URL . '/admin/js/shortcode.js';
		
		return $plugin_array;
	}

	static function add_tinymce_button( $buttons ) {
		$buttons[] = "lab_directory_button";
		
		return $buttons;
	}

	static function thickbox_ajax_form() {
		//TODO Test this, move to frontend? 
		require_once ( LAB_DIRECTORY_DIR . '/admin/views/shortcode-thickbox.php' );
		exit();
	}

	/**
	 * Allows for managing the custom post type query for things like admin sorting handling and defaults.
	 *
	 * @param object $query data
	 */
	static function manage_listing_query( $query ) {
		global $wp_the_query;
		// Admin Listing
		if ( $wp_the_query === $query && is_admin() && $query->get( 'post_type' ) === 'lab_directory_staff' ) {
			$orderby = $query->get( 'orderby' ) ?: 'name';
			$order = $query->get( 'order' ) ?: 'ASC';
			
			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );
		}
	}

	/** 
	 * Remove bulk actions
	 */
	static function modify_bulk_actions( $actions ) {
		if ( get_post_type() == 'lab_directory_staff' ) {
			// unset( $actions['edit'] );
			// unset( $actions['view'] );
			
			if ( ! current_user_can( 'administrator' ) ) {
				unset( $actions['trash'] );
				unset( $actions['inline hide-if-no-js'] );
			}
		}
		return $actions;
	}

	/**
	 * Remove quick-edit action
	 */
	static function modify_quick_edit( $actions ) {
		if ( get_post_type() == 'lab_directory_staff' ) {
			// unset( $actions['edit'] );
			// unset( $actions['view'] );
			// TODO permissions ??
			
			unset( $actions['trash'] );
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	static function add_lab_directory_staff_categories_admin_filter() {
		global $post_type;
		
		if ( $post_type == 'lab_directory_staff' ) {
			foreach ( Lab_Directory_Common::lab_directory_get_taxonomies() as $slug => $ld_taxonomy ) {
				
				$filter_name = $slug.'_filter';
				$lab_category_args = array(
					'show_option_all' => $ld_taxonomy['labels']['all_items'],
					'orderby' => 'ID',
					'order' => 'ASC',
					'name' => $filter_name,
					'taxonomy' => $slug );
					
				if ( isset( $_GET[$filter_name] ) ) {
					$lab_category_args['selected'] = sanitize_text_field( $_GET[$filter_name] );
				}
					
				wp_dropdown_categories( $lab_category_args );
			}
			

		}
	}

	static function filter_admin_lab_directory_staff_by_category( $query ) {
		global $post_type, $pagenow;
		
		if ( $pagenow == 'edit.php' && $post_type == 'lab_directory_staff' ) {
			
			foreach ( Lab_Directory_Common::lab_directory_get_taxonomies() as $slug => $ld_taxonomy ) {
			
				$filter_name = $slug.'_filter';
				if ( isset( $_GET[$filter_name] ) ) {
				$post_format = sanitize_text_field( $_GET[$filter_name] );

					if ( $post_format != 0 ) {
						$query->query_vars['tax_query'] = array( 
							array( 'taxonomy' => $slug, 'field' => 'ID', 'terms' => array( $post_format ) ) );
					}
				}
			}
		}
	}



	public function get_lab_directory_default_permissions() {
		
		// Define default permissions;
		$permissions = array( 
			"wp_editor_settings_general" => "0", 
			"wp_author_settings_general" => "0", 
			"wp_contributor_settings_general" => "0", 
			"wp_subscriber_settings_general" => "0", 
			"ld_permanent_settings_general" => "0", 
			"ld_administrator_settings_general" => "0", 
			"ld_HDR_settings_general" => "0", 
			"ld_doctorate_settings_general" => "0", 
			"ld_post-doctorate_settings_general" => "0", 
			"ld_internship_settings_general" => "0", 
			"ld_invited_settings_general" => "0", 
			"ld_CDD_settings_general" => "0", 
			"ld_custom_group_settings_general" => "0", 
			"wp_editor_settings_permissions" => "0", 
			"wp_author_settings_permissions" => "0", 
			"wp_contributor_settings_permissions" => "0", 
			"wp_subscriber_settings_permissions" => "0", 
			"ld_permanent_settings_permissions" => "0", 
			"ld_administrator_settings_permissions" => "0", 
			"ld_HDR_settings_permissions" => "0", 
			"ld_doctorate_settings_permissions" => "0", 
			"ld_post-doctorate_settings_permissions" => "0", 
			"ld_internship_settings_permissions" => "0", 
			"ld_invited_settings_permissions" => "0", 
			"ld_CDD_settings_permissions" => "0", 
			"ld_custom_group_settings_permissions" => "0", 
			"wp_editor_ldap_settings" => "1", 
			"wp_author_ldap_settings" => "0", 
			"wp_contributor_ldap_settings" => "0", 
			"wp_subscriber_ldap_settings" => "0", 
			"ld_permanent_ldap_settings" => "0", 
			"ld_administrator_ldap_settings" => "0", 
			"ld_HDR_ldap_settings" => "0", 
			"ld_doctorate_ldap_settings" => "0", 
			"ld_post-doctorate_ldap_settings" => "0", 
			"ld_internship_ldap_settings" => "0", 
			"ld_invited_ldap_settings" => "0", 
			"ld_CDD_ldap_settings" => "0", 
			"ld_custom_group_ldap_settings" => "0", 
			"wp_editor_ldap_syncing" => "0", 
			"wp_author_ldap_syncing" => "0", 
			"wp_contributor_ldap_syncing" => "0", 
			"wp_subscriber_ldap_syncing" => "0", 
			"ld_permanent_ldap_syncing" => "0", 
			"ld_administrator_ldap_syncing" => "0", 
			"ld_HDR_ldap_syncing" => "0", 
			"ld_doctorate_ldap_syncing" => "0", 
			"ld_post-doctorate_ldap_syncing" => "0", 
			"ld_internship_ldap_syncing" => "0", 
			"ld_invited_ldap_syncing" => "0", 
			"ld_CDD_ldap_syncing" => "0", 
			"ld_custom_group_ldap_syncing" => "0", 
			"wp_editor_group_of_fields_settings" => "0", 
			"wp_author_group_of_fields_settings" => "0", 
			"wp_contributor_group_of_fields_settings" => "0", 
			"wp_subscriber_group_of_fields_settings" => "0", 
			"ld_permanent_group_of_fields_settings" => "0", 
			"ld_administrator_group_of_fields_settings" => "0", 
			"ld_HDR_group_of_fields_settings" => "0", 
			"ld_doctorate_group_of_fields_settings" => "0", 
			"ld_post-doctorate_group_of_fields_settings" => "0", 
			"ld_internship_group_of_fields_settings" => "0", 
			"ld_invited_group_of_fields_settings" => "0", 
			"ld_CDD_group_of_fields_settings" => "0", 
			"ld_custom_group_group_of_fields_settings" => "0", 
			"wp_editor_meta_fields_settings" => "0", 
			"wp_author_meta_fields_settings" => "0", 
			"wp_contributor_meta_fields_settings" => "0", 
			"wp_subscriber_meta_fields_settings" => "0", 
			"ld_permanent_meta_fields_settings" => "0", 
			"ld_administrator_meta_fields_settings" => "0", 
			"ld_HDR_meta_fields_settings" => "0", 
			"ld_doctorate_meta_fields_settings" => "0", 
			"ld_post-doctorate_meta_fields_settings" => "0", 
			"ld_internship_meta_fields_settings" => "0", 
			"ld_invited_meta_fields_settings" => "0", 
			"ld_CDD_meta_fields_settings" => "0", 
			"ld_custom_group_meta_fields_settings" => "0", 
			"wp_editor_acronyms_settings" => "0", 
			"wp_author_acronyms_settings" => "0", 
			"wp_contributor_acronyms_settings" => "0", 
			"wp_subscriber_acronyms_settings" => "0", 
			"ld_permanent_acronyms_settings" => "0", 
			"ld_administrator_acronyms_settings" => "0", 
			"ld_HDR_acronyms_settings" => "0", 
			"ld_doctorate_acronyms_settings" => "0", 
			"ld_post-doctorate_acronyms_settings" => "0", 
			"ld_internship_acronyms_settings" => "0", 
			"ld_invited_acronyms_settings" => "0", 
			"ld_CDD_acronyms_settings" => "0", 
			"ld_custom_group_acronyms_settings" => "0", 
			"wp_editor_taxonomies_settings" => "0", 
			"wp_author_taxonomies_settings" => "0", 
			"wp_contributor_taxonomies_settings" => "0", 
			"wp_subscriber_taxonomies_settings" => "0", 
			"ld_permanent_taxonomies_settings" => "0", 
			"ld_administrator_taxonomies_settings" => "0", 
			"ld_HDR_taxonomies_settings" => "0", 
			"ld_doctorate_taxonomies_settings" => "0", 
			"ld_post-doctorate_taxonomies_settings" => "0", 
			"ld_internship_taxonomies_settings" => "0", 
			"ld_invited_taxonomies_settings" => "0", 
			"ld_CDD_taxonomies_settings" => "0", 
			"ld_custom_group_taxonomies_settings" => "0", 
			"wp_editor_validate_new_staff_entry" => "0", 
			"wp_author_validate_new_staff_entry" => "0", 
			"wp_contributor_validate_new_staff_entry" => "0", 
			"wp_subscriber_validate_new_staff_entry" => "0", 
			"ld_permanent_validate_new_staff_entry" => "0", 
			"ld_administrator_validate_new_staff_entry" => "0", 
			"ld_HDR_validate_new_staff_entry" => "0", 
			"ld_doctorate_validate_new_staff_entry" => "0", 
			"ld_post-doctorate_validate_new_staff_entry" => "0", 
			"ld_internship_validate_new_staff_entry" => "0", 
			"ld_invited_validate_new_staff_entry" => "0", 
			"ld_CDD_validate_new_staff_entry" => "0", 
			"ld_custom_group_validate_new_staff_entry" => "0", 
			"wp_editor_give_permanent_status" => "0", 
			"wp_author_give_permanent_status" => "0", 
			"wp_contributor_give_permanent_status" => "0", 
			"wp_subscriber_give_permanent_status" => "0", 
			"ld_permanent_give_permanent_status" => "0", 
			"ld_administrator_give_permanent_status" => "0", 
			"ld_HDR_give_permanent_status" => "0", 
			"ld_doctorate_give_permanent_status" => "0", 
			"ld_post-doctorate_give_permanent_status" => "0", 
			"ld_internship_give_permanent_status" => "0", 
			"ld_invited_give_permanent_status" => "0", 
			"ld_CDD_give_permanent_status" => "0", 
			"ld_custom_group_give_permanent_status" => "0", 
			"wp_editor_give_administrative_status" => "0", 
			"wp_author_give_administrative_status" => "0", 
			"wp_contributor_give_administrative_status" => "0", 
			"wp_subscriber_give_administrative_status" => "0", 
			"ld_permanent_give_administrative_status" => "0", 
			"ld_administrator_give_administrative_status" => "0", 
			"ld_HDR_give_administrative_status" => "0", 
			"ld_doctorate_give_administrative_status" => "0", 
			"ld_post-doctorate_give_administrative_status" => "0", 
			"ld_internship_give_administrative_status" => "0", 
			"ld_invited_give_administrative_status" => "0", 
			"ld_CDD_give_administrative_status" => "0", 
			"ld_custom_group_give_administrative_status" => "0", 
			"wp_editor_give_hdr_status" => "0", 
			"wp_author_give_hdr_status" => "0", 
			"wp_contributor_give_hdr_status" => "0", 
			"wp_subscriber_give_hdr_status" => "0", 
			"ld_permanent_give_hdr_status" => "0", 
			"ld_administrator_give_hdr_status" => "0", 
			"ld_HDR_give_hdr_status" => "0", 
			"ld_doctorate_give_hdr_status" => "0", 
			"ld_post-doctorate_give_hdr_status" => "0", 
			"ld_internship_give_hdr_status" => "0", 
			"ld_invited_give_hdr_status" => "0", 
			"ld_CDD_give_hdr_status" => "0", 
			"ld_custom_group_give_hdr_status" => "0", 
			"wp_editor_give_phd_status" => "0", 
			"wp_author_give_phd_status" => "0", 
			"wp_contributor_give_phd_status" => "0", 
			"wp_subscriber_give_phd_status" => "0", 
			"ld_permanent_give_phd_status" => "0", 
			"ld_administrator_give_phd_status" => "0", 
			"ld_HDR_give_phd_status" => "0", 
			"ld_doctorate_give_phd_status" => "0", 
			"ld_post-doctorate_give_phd_status" => "0", 
			"ld_internship_give_phd_status" => "0", 
			"ld_invited_give_phd_status" => "0", 
			"ld_CDD_give_phd_status" => "0", 
			"ld_custom_group_give_phd_status" => "0", 
			"wp_editor_give_post_doc_status" => "0", 
			"wp_author_give_post_doc_status" => "0", 
			"wp_contributor_give_post_doc_status" => "0", 
			"wp_subscriber_give_post_doc_status" => "0", 
			"ld_permanent_give_post_doc_status" => "0", 
			"ld_administrator_give_post_doc_status" => "0", 
			"ld_HDR_give_post_doc_status" => "0", 
			"ld_doctorate_give_post_doc_status" => "0", 
			"ld_post-doctorate_give_post_doc_status" => "0", 
			"ld_internship_give_post_doc_status" => "0", 
			"ld_invited_give_post_doc_status" => "0", 
			"ld_CDD_give_post_doc_status" => "0", 
			"ld_custom_group_give_post_doc_status" => "0", 
			"wp_editor_give_internship_status" => "0", 
			"wp_author_give_internship_status" => "0", 
			"wp_contributor_give_internship_status" => "0", 
			"wp_subscriber_give_internship_status" => "0", 
			"ld_permanent_give_internship_status" => "0", 
			"ld_administrator_give_internship_status" => "0", 
			"ld_HDR_give_internship_status" => "0", 
			"ld_doctorate_give_internship_status" => "0", 
			"ld_post-doctorate_give_internship_status" => "0", 
			"ld_internship_give_internship_status" => "0", 
			"ld_invited_give_internship_status" => "0", 
			"ld_CDD_give_internship_status" => "0", 
			"ld_custom_group_give_internship_status" => "0", 
			"wp_editor_give_invited_status" => "0", 
			"wp_author_give_invited_status" => "0", 
			"wp_contributor_give_invited_status" => "0", 
			"wp_subscriber_give_invited_status" => "0", 
			"ld_permanent_give_invited_status" => "0", 
			"ld_administrator_give_invited_status" => "0", 
			"ld_HDR_give_invited_status" => "0", 
			"ld_doctorate_give_invited_status" => "0", 
			"ld_post-doctorate_give_invited_status" => "0", 
			"ld_internship_give_invited_status" => "0", 
			"ld_invited_give_invited_status" => "0", 
			"ld_CDD_give_invited_status" => "0", 
			"ld_custom_group_give_invited_status" => "0", 
			"wp_editor_give_cdd_status" => "0", 
			"wp_author_give_cdd_status" => "0", 
			"wp_contributor_give_cdd_status" => "0", 
			"wp_subscriber_give_cdd_status" => "0", 
			"ld_permanent_give_cdd_status" => "0", 
			"ld_administrator_give_cdd_status" => "0", 
			"ld_HDR_give_cdd_status" => "0", 
			"ld_doctorate_give_cdd_status" => "0", 
			"ld_post-doctorate_give_cdd_status" => "0", 
			"ld_internship_give_cdd_status" => "0", 
			"ld_invited_give_cdd_status" => "0", 
			"ld_CDD_give_cdd_status" => "0", 
			"ld_custom_group_give_cdd_status" => "0", 
			"wp_editor_give_other_status" => "0", 
			"wp_author_give_other_status" => "0", 
			"wp_contributor_give_other_status" => "0", 
			"wp_subscriber_give_other_status" => "0", 
			"ld_permanent_give_other_status" => "0", 
			"ld_administrator_give_other_status" => "0", 
			"ld_HDR_give_other_status" => "0", 
			"ld_doctorate_give_other_status" => "0", 
			"ld_post-doctorate_give_other_status" => "0", 
			"ld_internship_give_other_status" => "0", 
			"ld_invited_give_other_status" => "0", 
			"ld_CDD_give_other_status" => "0", 
			"ld_custom_group_give_other_status" => "0", 
			"wp_editor_edit_staff_profile" => "0", 
			"wp_author_edit_staff_profile" => "0", 
			"wp_contributor_edit_staff_profile" => "0", 
			"wp_subscriber_edit_staff_profile" => "0", 
			"ld_permanent_edit_staff_profile" => "0", 
			"ld_administrator_edit_staff_profile" => "0", 
			"ld_HDR_edit_staff_profile" => "0", 
			"ld_doctorate_edit_staff_profile" => "0", 
			"ld_post-doctorate_edit_staff_profile" => "0", 
			"ld_internship_edit_staff_profile" => "0", 
			"ld_invited_edit_staff_profile" => "0", 
			"ld_CDD_edit_staff_profile" => "0", 
			"ld_custom_group_edit_staff_profile" => "0", 
			"wp_editor_edit_own_staff_profile" => "0", 
			"wp_author_edit_own_staff_profile" => "0", 
			"wp_contributor_edit_own_staff_profile" => "0", 
			"wp_subscriber_edit_own_staff_profile" => "0", 
			"ld_permanent_edit_own_staff_profile" => "0", 
			"ld_administrator_edit_own_staff_profile" => "0", 
			"ld_HDR_edit_own_staff_profile" => "0", 
			"ld_doctorate_edit_own_staff_profile" => "0", 
			"ld_post-doctorate_edit_own_staff_profile" => "0", 
			"ld_internship_edit_own_staff_profile" => "0", 
			"ld_invited_edit_own_staff_profile" => "0", 
			"ld_CDD_edit_own_staff_profile" => "0", 
			"ld_custom_group_edit_own_staff_profile" => "0", 
			"wp_editor_view_staff_lists_profiles" => "0", 
			"wp_author_view_staff_lists_profiles" => "0", 
			"wp_contributor_view_staff_lists_profiles" => "0", 
			"wp_subscriber_view_staff_lists_profiles" => "0", 
			"ld_permanent_view_staff_lists_profiles" => "0", 
			"ld_administrator_view_staff_lists_profiles" => "0", 
			"ld_HDR_view_staff_lists_profiles" => "0", 
			"ld_doctorate_view_staff_lists_profiles" => "0", 
			"ld_post-doctorate_view_staff_lists_profiles" => "0", 
			"ld_internship_view_staff_lists_profiles" => "0", 
			"ld_invited_view_staff_lists_profiles" => "0", 
			"ld_CDD_view_staff_lists_profiles" => "0", 
			"ld_custom_group_view_staff_lists_profiles" => "0" );
		
		return $permissions;
	}

	static function get_lab_directory_meta_field_types() {
		
		// Define the default type text to use for field name and their internationalisation
		$default_type_texts = array( 
			'text' => __( 'text', 'lab-directory' ), 
			'longtext' => __( 'Long text', 'lab-directory' ), 
			'textarea' => __( 'Multiline text', 'lab-directory' ), 
			'editor' => __( 'HTML Text', 'lab-directory' ), 
			'mail' => __( 'Mail', 'lab-directory' ), 
			'url' => __( 'URL', 'lab-directory' ), 
			'phone_number' => __( 'Phone number', 'lab-directory' ), 
			'date' => __( 'Date', 'lab-directory' ), 
			'datetime' => __( 'Date and Time', 'lab-directory' ), 
			'studying_level' => __( 'Studying_level', 'lab-directory' ), 
			'jury' => __( 'PHD or HDR Jury', 'lab-directory' ), 
			'social_network' => __( 'Social link', 'lab-directory' ) );
		return $default_type_texts;
	}

	static function get_lab_directory_default_group_names() {
		
		// Define the default groups used for meta field grouping
		$groups = array(
				/* translators: CV stands for Curriculum Vitae */ 
				'CV' => __( 'CV', 'lab-directory' ), 'BIO' => __( 'Biography', 'lab-directory' ) );
		$groups = array_merge( $groups, self::get_lab_directory_default_group_names2() );
		return $groups;
	}

	static function get_lab_directory_default_group_names2() {
		
		// Define the default groups used for meta field grouping
		$groups = array(
			/* translators: HDR french acronym for "Habilitation a Diriger les Recherches" */
			'HDR' => __( 'HDR', 'lab-directory' ), 
			'doctorate' => __( 'Doctorate', 'lab-directory' ), 
			'post-doctorate' => __( 'Post-doctorate', 'lab-directory' ), 
			'internship' => __( 'Internship', 'lab-directory' ),
			/* translators: "invited" refers to some people (teacher or researcher) being invited but not permanent staff of the structure*/
			'invited' => __( 'Invited', 'lab-directory' ),
			/* translators: CDD in french or "Fixed term contract" in english. Please use a short string, preferably less then 20 characters*/
			'CDD' => __( 'Fixed term contract', 'lab-directory' ), 
			'custom_group' => __( 'custom_group', 'lab-directory' ) );
		return $groups;
	}

	static function get_lab_directory_default_statuss() {
		
		// Define the default groups used for meta field grouping
		$statuss = array(
				/* translators: CV Curriculum Vitae (no need to translate this) */
				'permanent' => __( 'Permanent staff', 'lab-directory' ), 
			'administrator' => __( 'Administrative staff', 'lab-directory' ) );
		$statuss = array_merge( $statuss, self::get_lab_directory_default_group_names2() );
		return $statuss;
	}

	static function get_lab_directory_multivalues() {
		
		// Define the list of option related to single and multivalue of fields
		$default_multivalue = array( 
			
			'SV' => __( 'Single valued', 'lab-directory' ), 
			'MV' => __( 'Multiple valued', 'lab-directory' ), 
			',' => __( "(') separated values", 'lab-directory' ), 
			';' => __( '(;) separated values', 'lab-directory' ), 
			'|' => __( '(|) separated values', 'lab-directory' ), 
			'/' => __( '(/) separated values', 'lab-directory' ), 
			'CR' => __( 'CR separated values', 'lab-directory' ) );

		
		return $default_multivalue;
	}

	static function get_lab_directory_multivalues_names() {
		
		// Explain the list of option related to single and multivalue of fields
		$note1 = ' (' .
			 __( 'Only first value of attribute will be extracted if LDAP attribute is multivalued', 'lab-directory' ) .
			 ')';
		$default_multivalue_names = array( 
			
			'SV' => __( 
				'Single valued (only first value will be extracted if LDAP attribute is multivalued and has multiple values)', 
				'lab-directory' ), 
			'MV' => __( 'Multiple valued (extract all values if LDAP attribute is multivalued)', 'lab-directory' ), 
			',' => __( "Comma (,) separated values", 'lab-directory' ) . $note1, 
			';' => __( 'Semicolumn (;) separated values (', 'lab-directory' ) . $note1, 
			'|' => __( 'Vertical bar (|) separated values', 'lab-directory' ) . $note1, 
			'/' => __( 'Slash (/) separated values', 'lab-directory' ) . $note1, 
			'CR' => __( 'Carriage return separated values', 'lab-directory' ) . $note1 );
		return $default_multivalue_names;
	}


	static function get_lab_directory_ldap_attributes() {
		$ldap_attributes = array();
		$ldap_server = get_option( 'lab_directory_ldap_server' );
		$keys = explode( ';', $ldap_server['ldap_attributes'] );
		foreach ( $keys as $key ) {
			$ldap_attributes[$key] = $key;
		}
		return $ldap_attributes;
	}

	/*
	 * This function is runned once at init to calculate almost every permissions one time
	 * (excepted for own permissions) in order to speed up ld_user_can function
	 */
	static function initiate_ld_permissions() {
		// TODO probably move to common if permissions used in Frontend 
		self::$ld_permissions = get_option( 'lab_directory_permissions' );
	}


	static function initiate_capabilities() {
		$temp = array( "0" => "test permissions modifié" );
		// TODO add translations
		self::$capabilities = array( 
			'settings_general' => array( 'name' => 'General settings', 'scope' => 'all' ), 
			'settings_permissions' => array( 'name' => 'Permissions settings', 'scope' => 'all' ), 
			'ldap_settings' => array( 'name' => 'LDAP settings', 'scope' => 'all' ), 
			'ldap_syncing' => array( 'name' => 'LDAP syncing', 'scope' => 'all' ), 
			'group_of_fields_settings' => array( 'name' => 'Groups of field settings', 'scope' => 'all' ), 
			'meta_fields_settings' => array( 'name' => 'Meta field settings', 'scope' => 'all' ), 
			'acronyms_settings' => array( 'name' => 'Acronyms settings', 'scope' => 'all' ), 
			'taxonomies_settings' => array( 'name' => 'Taxonomies settings', 'scope' => 'all' ), 
			'validate_new_staff_entry' => array( 'name' => 'Validate new staff entry', 'scope' => 'all' ), 
			'give_permanent_status' => array( 'name' => 'Give permanent status', 'scope' => 'all' ), 
			'give_administrative_status' => array( 'name' => 'Give administrative status', 'scope' => 'all' ), 
			'give_hdr_status' => array( 'name' => 'Give HDR status', 'scope' => 'all' ), 
			'give_phd_status' => array( 'name' => 'Give PHD status', 'scope' => 'all' ), 
			'give_post_doc_status' => array( 'name' => 'Give post-doc status', 'scope' => 'all' ), 
			'give_internship_status' => array( 'name' => 'Give internship status', 'scope' => 'all' ), 
			'give_invited_status' => array( 'name' => 'Give HDR status', 'scope' => 'all' ), 
			'give_cdd_status' => array( 'name' => 'Give PHD status', 'scope' => 'all' ), 
			'give_other_status' => array( 'name' => 'Give "other" (custom) status', // Is this usefull?
'scope' => 'all' ), 
			'edit_staff_profile' => array( 'name' => 'Edit staff profile', 'scope' => 'all' ), 
			'edit_own_staff_profile' => array( 'name' => 'Edit its own profile', 'scope' => 'own' ), 
			'view_staff_lists_profiles' => array( 'name' => 'View staff lists and profiles', 'scope' => 'all' ) );

		
	}

	static function lab_directory_staff_photo_meta_box( $content, $post_id, $thumbnail_id ) {
		
		// 	TODO adjust access rights TODO2 this do not remove metabox but only its content !!
		if (!current_user_can( 'administrator' )) {
			return;
		}
		if ( get_post_meta( $post_id, 'ldap', true ) > 0 ) {
			if ( $thumbnail_id ) {
				$content = preg_match( '#(<img.*?>)#', $content, $matches ) ? $matches[0] : '';
			} else {
				$content = '';
			}
			$content .= '<p><i><span class="dashicons dashicons-lock"></span>' .
				 __( 
					'This staff profile (including photo) is synchronised with LDAP. Staff photo must be added or changed on LDAP directory', 
					'lab-directory' ) . '</i></p>';
		}
		return $content;
	}

	static function remove_publish_box() {
		remove_meta_box( 'submitdiv', 'lab_directory_staff', 'side' );
	}


	/**
	 * Remove the 'description' column from the taxonomies table in 'edit-tags.php'
	 */
	static function ld_taxonomy_team_description( $columns ) {
		if ( isset( $columns['description'] ) )
			unset( $columns['description'] );
		if ( isset( $columns['slug'] ) )
			unset( $columns['slug'] );
		
		$columns['display_style'] = __( 'Display as', 'lab_directory' );
		$columns['manager_ids'] = __( 'Team manager', 'lab_directory' );
		
		// Push Post column (total) at the end
		if ( isset( $columns['posts'] ) ) {
			$temp = $columns['posts'];
			unset( $columns['posts'] );
			$columns['posts'] = $temp;
		}
		
		return $columns;
	}

	static function ld_taxonomy_laboratory_description( $columns ) {
		if ( isset( $columns['description'] ) )
			unset( $columns['description'] );
		if ( isset( $columns['slug'] ) )
			unset( $columns['slug'] );
		
		$columns['display_style'] = __( 'Display as', 'lab_directory' );
		$columns['manager_ids'] = __( 'Laboratory manager', 'lab_directory' );
		
		// Push Post column (total) at the end
		if ( isset( $columns['posts'] ) ) {
			$temp = $columns['posts'];
			unset( $columns['posts'] );
			$columns['posts'] = $temp;
		}
		
		return $columns;
	}

	static function ld_taxonomies_columns_content( $content, $column_name, $term_id ) {
		if ( 'manager_ids' == $column_name ) {
			global $wpdb;
			$term_meta = get_option( "taxonomy_term_$term_id" );
			
			$content = array();
			if ($term_meta AND isset($term_meta['manager_ids'])) {
				foreach ( $term_meta['manager_ids'] as $ID ) {
					$row = $wpdb->get_row( 
						"SELECT post_title FROM $wpdb->posts
						WHERE post_type = 'lab_directory_staff' AND ID = '$ID'", 
						'OBJECT' );
					if ( $row ) {
						$content[] = $row->post_title;
					}
				}
			}
			$content = implode( '<br>', $content );
		}
		
		if ( 'display_style' == $column_name ) {
			$term_meta = get_option( "taxonomy_term_$term_id" );
			$content = '';
			if ( $term_meta['display_style'] != 'None' ) {
				$content = __( $term_meta['display_style'], 'lab_directory' );
			} else {
				$content = __( 'None' );
			}
		}
		
		return $content;
	}

	/**
	 * Hide the term description in the taxonomies edit/add form
	 */
	static function ld_taxonomies_form( $columns ) {
		?><style type="text/css">
.term-description-wrap {
	display: none;
}
</style><?php
	}
	
	// A callback function to add a custom field to our "ld_taxonomy_team" taxonomy
	static function ld_taxonomy_team_custom_fields( $tag ) {
		// Check for existing taxonomy meta for the term you're editing
		if (is_object($tag) AND get_class($tag) == 'WP_Term') {
			$t_id = $tag->term_id; // Get the ID of the term you're editing
			$term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check
			$term_meta_manager_ids = isset($term_meta['manager_ids']) ? $term_meta['manager_ids']: array();
			$term_meta_display_style = isset($term_meta['display_style']) ? $term_meta['display_style'] : 'Manager';
		} else {
			$term_meta_manager_ids = array();
			$term_meta_display_style = 'Manager';
		}
		?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="manager_ids"><?php _e('Team manager', 'lab_directory'); ?></label>
	</th>
	<td>  
	            <?php echo self::staff_select('term_meta[manager_ids][]', $term_meta_manager_ids); ?><br />
		<span class="description"><?php _e('Team manager is used for displaying a contact at the end of pages and posts having the same taxonomy name that the present one.', 'lab_directory'); ?></span>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="display_style"><?php _e('Display style', 'lab_directory'); ?></label>
	</th>
	<td><select name="term_meta[display_style]"
		id="term_meta[display_style]">
			<option value="None"
				<?php echo $term_meta_display_style=='None'? 'selected=""':''; ?>><?php _e('Do not display', 'lab_directory'); ?></option>
			<option value="Contact"
				<?php echo $term_meta_display_style=='Contact'? 'selected=""':''; ?>><?php _e('Contact', 'lab_directory'); ?></option>
			<option value="Team manager"
				<?php echo $term_meta_display_style=='Manager'? 'selected=""':''; ?>><?php _e('Manager', 'lab_directory'); ?></option>
	</select><br /> <span class="description"><?php _e('For each category you can choose to hide manager, or to display as a contact.', 'lab_directory'); ?></span>
	</td>
</tr>

<?php
	}
	
	// A callback function to add a custom field to our "ld_taxonomy_laboratory" taxonomy
	static function ld_taxonomy_laboratory_custom_fields( $tag ) {
		// Check for existing taxonomy meta for the term you're editing
		if (is_object($tag) AND get_class($tag) == 'WP_Term') {
			$t_id = $tag->term_id; // Get the ID of the term you're editing
			$term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check
			$term_meta_manager_ids = $term_meta['manager_ids'];
			$term_meta_display_style = $term_meta['display_style'];
			if ( ! $term_meta['display_style'] ) {
				$term_meta_display_style = 'Manager';
			}
			if ( ! $term_meta['manager_ids'] ) {
				$term_meta_manager_ids = array();
			}
		} else {
			$term_meta_manager_ids = array();
			$term_meta_display_style = 'Manager';
		}		
		
		?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="manager_ids"><?php _e('Laboratory manager', 'lab_directory'); ?></label>
	</th>
	<td><?php echo self::staff_select('term_meta[manager_ids][]', $term_meta_manager_ids); ?><br />
		<span class="description"><?php _e('Laboratory manager is used for displaying a contact at the end of pages and posts having the same taxonomy name that the present one.', 'lab_directory'); ?></span>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="display_style"><?php _e('Display style', 'lab_directory'); ?></label>
	</th>
	<td><select name="term_meta[display_style]"
		id="term_meta[display_style]">
			<option value="None"
				<?php echo $term_meta_display_style=='None'? 'selected=""':''; ?>><?php _e('Do not display', 'lab_directory'); ?></option>
			<option value="Contact"
				<?php echo $term_meta_display_style=='Contact'? 'selected=""':''; ?>><?php _e('Contact', 'lab_directory'); ?></option>
			<option value="Laboratory manager"
				<?php echo $term_meta_display_style=='Manager'? 'selected=""':''; ?>><?php _e('Manager', 'lab_directory'); ?></option>
	</select><br /> <span class="description"><?php _e('For each category you can choose to hide manager, or to display as a contact.', 'lab_directory'); ?></span>
	</td>
</tr>

<?php
	}
	
	// Save the custom field changes made on taxonomies
	static function save_ld_taxonomies_custom_fields( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$t_id = $term_id;
			$term_meta = get_option( "taxonomy_term_$t_id" );
			$cat_keys = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			
			// save the option array
			update_option( "taxonomy_term_$t_id", $term_meta );
		}
	}

	static function staff_select( $name, $current_staff_ids ) {
		global $wpdb;
		$results = $wpdb->get_results( 
			"SELECT ID, post_title FROM $wpdb->posts 
			WHERE post_type = 'lab_directory_staff' AND post_status = 'publish' ORDER BY post_title", 
			'OBJECT' );
		
		$output = '';
		$my_query = null;
		$my_query = new WP_Query( );
		if ( $results ) {
			$output .= '<select multiple name="' . $name . '" id="' . $name . '" >';
			$output .= '<option value="" disabled ' . ( $current_staff_ids ? 'selected=""' : '' ) . '>' .
				 __( 'Select contact(s) or manager(s)', 'lab_directory' ) . '</option>';
			foreach ( $results as $result ) {
				
				$output .= '<option value="' . $result->ID . '" ' .
					 ( in_array( $result->ID, $current_staff_ids ) ? 'selected=""' : '' ) . '>' . $result->post_title .
					 '</option>';
			}
			$output .= '</select>';
		}
		return $output;
	}


	/*
	 * public function to ger user permission
	 * $capability: capability key
	 */
	static function ld_user_can( $capability, $user_id = null ) {
		global $current_user;
		if ( ! $capability ) {
			return false;
		}
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				return self::ld_user_can_by_user( $capability, $user );
			} else {
				return false;
			}
		} else {
			return self::ld_user_can_by_user( $capability, $current_user );
		}
	}
	
	static function ld_user_can_by_user( $capability, $user ) {
	
		if (in_array('administrator', $user->roles)) { return true;};
		if ( ! $user->id ) {
			return false;
		}
	
		$scope = Lab_Directory::$capabilities[$capability]['scope'];
		// unvalid scope (capability)
		if ( ! $scope ) {
			return false;
		}
	
		// scope = all, capability based on WP groups
		if ( $scope == 'all' ) {
			foreach ( $user->roles as $role_key => $role ) {
				$capability_key = 'wp_' . $role . '_' . $capability;
					
				if ( Lab_Directory::$ld_permissions[$capability_key] == true ) {
					return true;
				}
			}
		}
	
		// the rest need link between WP and LD??
		$user_ld_id = false;
	
		// TODO temporary return capability calculation not completed;
		return false;
		// query select staff_id from staf / wp_user_id= $current_user->id;
	
		if ( ! $user_ld_id ) {
			return false;
		}
	
		// scope = owner capability based on WP groups
	
		return false;
	
		// scope = all capability based on LD groups
	
		// scope = owner capability based on LD groups
		return false;
	}
	
	static function get_possible_social_networks() {
		$networks = array(
			'twitter' => 'Twitter',
			'linkedin' => 'Linkedin',
			'academia' => 'Academia',
			'orcid' => 'Orcid',
			'research-gate' => 'Research Gate',
			'viadeo' => 'Viadeo',
			'vimeo' => 'Vimeo',
			'github' => 'Github',
			'vine' => 'Vine',
			'facebook' => 'Facebook',
			'instagram' => 'Instagram',
			'google-plus' => 'Google Plus',
			'heart' => 'Blogovin',
			'pinterest' => 'Pinterest',
			'youtube' => 'YouTube',
			'tumblr' => 'Tumblr',
			'rss' => 'rss',
			'envelope' => 'Email' );
	
		return $networks;
	}
	
	static function ld_compare_jury_order( $a, $b ) {
		return (int) $a['order'] - (int) $b['order'];
	}

	static function lab_directory_strtotime( $time, $format = "Y-m-d" ) {
		$out = '';
		if ( $time ) {
			$out = date( $format, strtotime( $time ) );
		}
		return $out;
	}
	
	
} // End of class 








