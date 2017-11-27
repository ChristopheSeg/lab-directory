<?php

class Lab_Directory {

	// Declaration of static variables used by almost all admin/frontend page
	
	/* 
	 *  Lab_Directory::$ld_permissions
	 *  list all permissions settings (value is "0" or "1")
	 *  
	 *  = array {
	 *   "wp_editor_settings_general" => string(1) "0",
	 *   "wp_author_settings_general" => string(1) "1",
	 *   "wp_contributor_settings_general" => string(1) "0",
	 *   -------
	 *   "ld_post-doctorate_give_phd_status" => string(1) "0"
	 *   "ld_internship_give_phd_status" => string(1) "0"
	 *   };
	 */
	
	static $ld_permissions = null; 
	
	/* 
	 * Lab_Directory::$capabilities
	 * List all capabilities, their name and scope ("all" or "own")
	 * 
	 * 	Lab_Directory::$capabilities = array(
	 *  		'settings_general' => array(
	 * 					'name' =>'General settings',
	 * 					'scope' =>'all'),
	 * 			'settings_permissions' => array(
	 * 					'name' =>'Permissions settings',
	 * 					'scope' =>'all'),
	 * 		-------
	 * 			'edit_own_staff_profile' => array(
	 * 					'name' =>'Edit its own profile',
	 * 					'scope' =>'own'),
	 *  	);
	 */

	static $capabilities = null;
	
	
	/* 
	 *   Lab_Directory::$staff_meta_fields
	 *   ordered list of all metafields in and their description 
	 *   
	 *   	Lab_Directory::$staff_meta_fields = array(
	 *   [0]=>
	 *     array(8) {
	 *       ["slug"]=>    string(9) "firstname"
	 *       ["order"]=>   string(1) "1"
	 *       ["type"]=>    string(4) "text"
	 *       ["group"]=>    string(2) "CV"
	 *       ["activated"]=>    string(1) "1"
	 *       ["multivalue"]=>    'special'
	 *       ["ldap_attribute"]=>    string(2) "sn" ( 'disabled' if ldap syncing disabled)
	 *       ["show_frontend"]=>     string(1) "1"
	 *     }
	 *     [1]=>
	 *     array(8) {
 	 *      ["slug"]=>     string(4) "name"
 	 *      ["order"]=>    string(1) "2"
	 *       ["type"]=>    string(4) "text"
	 *       ["group"]=>    string(2) "CV"
	 *       ["activated"]=>    string(1) "1"
	 *       ["multivalue"]=>    special
	 *       ["ldap_attribute"]=>    string(0) ""
	 *       ["show_frontend"]=>    string(1) "1"
	 *      }
	 *   }
	 */
	
	static $staff_meta_fields = null;
	
	static $translations = null;
	
	static $acronyms = null;
	
	#
	# Init custom post types
	#

	static function register_post_types() {
		add_action( 'init', array( 'Lab_Directory', 'create_post_types' ) );
		add_action( 'init', array( 'Lab_Directory', 'create_lab_directory_staff_taxonomies' ) );
		
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_ld_permissions' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_capabilities' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_staff_meta_fields' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_translations' ) );
		add_action( 'plugins_loaded', array( 'Lab_Directory', 'initiate_acronyms' ) );
		
		add_filter( 'get_sample_permalink_html', array( 'Lab_Directory', 'hide_permalink' ));
		add_filter( 'admin_post_thumbnail_html', array( 'Lab_Directory', 'lab_directory_staff_photo_meta_box'), 10, 3 );
		add_filter( "manage_edit-lab_directory_staff_columns", array( 'Lab_Directory', 'set_lab_directory_staff_admin_columns' ) );
		add_filter( "manage_lab_directory_staff_posts_custom_column", array(
			'Lab_Directory',
			'custom_lab_directory_staff_admin_columns'
		), 10, 3 );
		add_filter( 'manage_edit-lab_category_columns', array( 'Lab_Directory', 'set_lab_category_columns' ) );
		add_filter( 'manage_lab_category_custom_column', array( 'Lab_Directory', 'custom_lab_category_columns' ), 10, 3 );

		add_filter( 'enter_title_here', array( 'Lab_Directory', 'lab_directory_staff_title_text' ) );
		add_filter( 'admin_head', array( 'Lab_Directory', 'remove_media_buttons' ) );
		add_action( 'admin_menu', array( 'Lab_Directory', 'remove_publish_box' ));
		add_action( 'add_meta_boxes_lab_directory_staff', array( 'Lab_Directory', 'add_lab_directory_staff_custom_meta_boxes' ) );
		add_action( 'save_post', array( 'Lab_Directory', 'save_meta_boxes' ) );
		add_action( 'wp_enqueue_scripts', array( 'Lab_Directory', 'enqueue_fontawesome' ) );
		add_action( 'admin_enqueue_scripts', array( 'Lab_Directory', 'enqueue_fontawesome' ) );
		add_action( 'admin_enqueue_scripts', array( 'Lab_Directory', 'lab_directory_scripts_and_css_for_tabs') );

		add_action( 'init', array( 'Lab_Directory', 'init_tinymce_button' ) );
		add_action( 'wp_ajax_get_my_form', array( 'Lab_Directory', 'thickbox_ajax_form' ) );
		add_action( 'pre_get_posts', array( 'Lab_Directory', 'manage_listing_query' ) );
		add_filter( 'post_row_actions', array( 'Lab_Directory', 'modify_quick_edit'), 10, 1 );
		
	    //load single-page/profile template
	    add_filter('single_template', array( 'Lab_Directory', 'load_profile_template' ) );
	
	    add_action( 'restrict_manage_posts', array( 'Lab_Directory', 'add_lab_directory_staff_categories_admin_filter' ) );
	    add_action( 'pre_get_posts', array( 'Lab_Directory', 'filter_admin_lab_directory_staff_by_category' ) );
	    
	    // Custom field translation filter
	    add_filter( 'gettext', array( 'Lab_Directory', 'lab_directory_custom_translations'), 10, 3 );	     


	}
	
	static function create_post_types() {
		register_post_type( 'lab_directory_staff',
			array(
				'labels'     => array(
						'name' => __( 'Staffs (Lab Directory)', 'lab-directory'),
						'singular_name' => __( 'Staffs', 'lab-directory'),
						'add_new' => __('New staff', 'lab-directory'),
						'add_new_item' => __('Add a new staff', 'lab-directory'),
						'edit_item' => __('Edit staff details', 'lab-directory'),
						'new_item' => __('New staff', 'lab-directory'),
						'view_item' => __('View staff', 'lab-directory'),
						'view_items' => __('View staffs', 'lab-directory'), 
						'search_items' => __('Search staff', 'lab-directory'),
						'not_found' => __('No staff found.', 'lab-directory'),
						'not_found_in_trash' => __('No staff in Trash.', 'lab-directory'),
						'all_items' => __( 'Staffs list', 'lab-directory'),
						'featured_image' => __( 'Staff photo', 'lab-directory'),
						'set_featured_image' => __( 'Set staff photo', 'lab-directory'),
						'remove_featured_image' => __( 'Remove staff photo', 'lab-directory'),
						'use_featured_image' => __( 'Use a staff photo', 'lab-directory'),
						'filter_items_list' =>  __( 'Filter staffs list' , 'lab-directory'),
						'items_list_navigation' =>  __( 'Staffs list navigation' , 'lab-directory'),
						'items_list' => __( 'Staffs list' , 'lab-directory'),
						
				),
				'supports'   => array(
					'title',
					// 'editor',
					'thumbnail' // disabled for ldap=1
				),
				'public'     => true,
				'has_archive' => false,
				'menu_icon'  => 'dashicons-id',
				'taxonomies' => array( 'lab_category', ),
			)
		);
	}

	static function create_lab_directory_staff_taxonomies() {
		register_taxonomy( 'lab_category', 'lab_directory_staff', array(
			'hierarchical' => true,
			'labels'       => array(
				'name'              => _x( 'Staff Category', 'taxonomy general name' ),
				'singular_name'     => _x( 'lab_directory_staff-category', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Staff Categories' ),
				'all_items'         => __( 'All Staff Categories' ),
				'parent_item'       => __( 'Parent Staff Category' ),
				'parent_item_colon' => __( 'Parent Staff Category:' ),
				'edit_item'         => __( 'Edit Staff Category' ),
				'update_item'       => __( 'Update Staff Category' ),
				'add_new_item'      => __( 'Add New Staff Category' ),
				'new_item_name'     => __( 'New Staff Category Name' ),
				'menu_name'         => __( 'Staff Categories' ),
			),
			'rewrite'      => array(
				'slug'         => 'lab_directory_staff-categories',
				'with_front'   => false,
				'hierarchical' => true
			),
		) );
	}

    static function load_profile_template($original){
        global $post;

        if (is_singular('lab_directory_staff')) {
	        $single_template_option = get_option('lab_directory_staff_single_template');
          if(strtolower($single_template_option) != 'default'){
          	$template = locate_template($single_template_option);
            if ($template && !empty($template)){
                return $template;
            }
          }
	        //Option not set to default, and template not found, try to load
	        //default anyway. This will ensure that if, somehow, the user
	        //doesn't visit the settings page in order to instantiate the defaults,
	        //we'll still be using a template specified for lab-directory, not the
	        //default single.php
	        $default_file_name = 'single-lab_directory_staff.php';
	        return LAB_DIRECTORY_LIST_TEMPLATES . $default_file_name;
        }

        return $original;
    }

	static function set_lab_directory_staff_admin_columns() {
		$new_columns = array(
			'cb'             => '<input type="checkbox" />',
			'title'          => __( 'Title' ),
			'id'             => __( 'ID' ),
			'featured_image' => __( 'Staff photo' ),
			'date'           => __( 'Date' )
		);

		return $new_columns;
	}

	static function custom_lab_directory_staff_admin_columns( $column_name, $post_id ) {
		$out = '';
		switch ( $column_name ) {
			case 'featured_image':
				$attachment_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ) );
				$photo_url        = $attachment_array[0];
				$out .= '<img src="' . $photo_url . '" style="max-height: 60px; width: auto;" />';
				break;

			case 'id':
				$out .= $post_id;
				break;

			default:
				break;
		}
		echo $out;
	}

	static function set_lab_category_columns() {
		$new_columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __( 'Name' ),
			'id'          => __( 'ID' ),
			'description' => __( 'Description' ),
			'slug'        => __( 'Slug' ),
			'posts'       => __( 'Posts' )
		);

		return $new_columns;
	}

	static function custom_lab_category_columns( $out, $column_name, $theme_id ) {
		switch ( $column_name ) {
			case 'id':
				$out .= $theme_id;
				break;

			default:
				break;
		}

		return $out;
	}

// enqueue tabs script
	static function lab_directory_scripts_and_css_for_tabs() {
	 wp_enqueue_script( 'jquery-ui-tabs' );
	 wp_enqueue_script( 'jquery-ui-datepicker' );
	 wp_enqueue_script( 'custom-tabs', plugins_url( '/js/tabs.js', dirname(__FILE__) ), array('jquery'));
	 wp_enqueue_script( 'timepicker-addon', plugins_url( '/js/jquery.datetimepicker.js', dirname(__FILE__) ), array('jquery'));
	 
	 $wp_scripts = wp_scripts();
	wp_enqueue_style('timepicker-addon-css',
	                plugins_url( '/css/jquery.datetimepicker.css', dirname(__FILE__) ));
	
	wp_enqueue_style('plugin_name-admin-ui-css',
			'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css',
			false,
			PLUGIN_VERSION,
			false);
	}
	
	static function enqueue_fontawesome() {
		wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css',
			array(), '4.0.3' );
	}

	#
	# Custom post type customizations
	#

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
		add_meta_box( 'lab_directory_staff-meta-box', __( 'Staff Details' ), array(
			'Lab_Directory',
			'lab_directory_staff_meta_box_output'
		), 'lab_directory_staff', 'normal', 'high' );
		add_meta_box( 'lab_directory_staff-meta-box_statut', __( 'Staff status' ), array(
			'Lab_Directory',
			'lab_directory_staff_meta_box_statut'
		), 'lab_directory_staff', 'normal', 'high' );
	}

	/* 
	 * Simply add a metabox to change statut of this staff
	 */
	 
	static function lab_directory_staff_meta_box_statut( $post ) {
		
		$statuss = Lab_Directory::get_lab_directory_default_statuss();
		$staff_statuss = get_post_meta( $post->ID, 'staff_statuss', true );
		$group_activations = get_option( 'lab_directory_group_activations' ) ;
		
		foreach ($statuss as $key =>$status) {
			$staff_status = false; 
			// TODO add capability 
			
			$activated = false; 
			if ( ( $key=='permanent' )  OR ($key=='administrator') 
					OR 
					(isset ($group_activations[$key]) AND $group_activations[$key])) {
				$activated = true; 
			}
			$disabled = $activated? '':'disabled ';  
			// initially  is not set  
			if (isset($staff_statuss[$key])){
				$staff_status = $staff_statuss[$key];
			}
			
			?>
			<p>
			<?php if (!$activated) {echo '<span class="dashicons dashicons-lock"></span>';} else {echo '<span class="dashicons"></span>';}
			?>
			<input name="status_<?php echo $key; ?>" type="checkbox" <?php echo $disabled; ?> value="1" <?php checked( true, $staff_status ); ?> /> 
			<?php 
			echo $statuss[$key]; 
			?>
			</p>

			<?php 
		}
		?> 
		<p>
		<button name="save" class="button button-primary button-large" id="publish2" value="Update_Status" type="submit"><?php _e('Update staff status')?></button>
    	</p>
    	<p>
		Note: Staff details and staff status cannot be modified at the same time. Please, first modify staff status, then modify staff details.  
    	</p>
    	<p><span class="dashicons dashicons-lock"></span> This group is disabled in general settings. Corresponding status is not available.</p>
    
		<?php 
	}
	
	/* 
	 * Output the meta_box form content
	 * 
	 */
	 
	static function lab_directory_staff_meta_box_output( $post ) {

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$lab_directory_meta_field_names = Lab_Directory::get_lab_directory_default_meta_field_names();
		$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
		$studying_levels = Lab_Directory::get_lab_directory_studying_levels();
		$jury_functions = Lab_Directory::get_lab_directory_jury_functions();
		$staff_statuss = get_post_meta( $post->ID, 'staff_statuss', true );
		$group_activations = get_option( 'lab_directory_group_activations' ) ;
		$used_groups = Lab_Directory_Settings::get_used_groups($active_meta_fields, $staff_statuss, $group_activations['BIO']);
		
		?> 
		<script type="text/javascript">
		jQuery(document).ready(function($){
		
			$('#add-new-jury-member').on('click', function(ev){
				ev.preventDefault();
				var tr = $('<tr/>');
				tr.html($('#new-jury-member').html());
				$("#add-new-jury-member-row").before(tr);
			});
		
				$(document).on('click', '.remove-field', function(ev){
					ev.preventDefault();
					$(this).parent().parent().remove();
				});
		});
		</script>
		
		<style type="text/css">
			#new-jury-member {display: none;}
			label.lab_directory_staff-label {	
				width: 150px;
				display: inline-block;
			}
			.dashicons {
			    font-size: 16px;
			    }

 			input.large-text{ width: 80%; }
			.input-in-td{ width:100%; padding-left:0; padding-right:0; }
			a.normal {color:#0073aa;}
			textarea { resize:both; width: 100%;} 
			p{ padding-left: 5px; background-color: rgb(245, 245, 245);}
		</style>
		<?php
		// Display Form 
		?>
	

		<?php 
		echo_form_messages(get_post_meta( $post->ID, 'messages', true)); 
		delete_post_meta( $post->ID, 'messages');
		?>

     <div id="demoTabsId" class="labDirectoryTabsClass">
        <ul>
            <?php
            foreach ($used_groups as $key => $group_name) {
            	echo '<li><a href="#Tab-'. $key .'">' . $group_name . '</a></li>';
            }
            ?>
        </ul>
        <?php
        // TODO disable input depending on capability , LDAP...
        $ldap_synced = (get_post_meta( $post->ID, 'ldap', true )!='0');
        foreach ($used_groups as $key => $group_name) {
        	echo '<div id="Tab-'. $key .'">';
             foreach ( $lab_directory_staff_settings->get_lab_directory_staff_meta_fields() as $field ) {
            	if (($field['group'] == $key) AND ($field['activated']=='1')) {
            		Lab_Directory::lab_directory_staff_meta_box_render_input($post, $field, $lab_directory_meta_field_names[$field['slug']], 
            				$studying_levels, $jury_functions, $ldap_synced); 
          		}
            }
        	echo '</div>';
        }
        ?>
       
    </div>
    <p>
    <button name="save" class="button button-primary button-large" id="publish2" value="Update" type="submit"><?php _e('Update')?></button>
    </p>
    <?php if ($ldap_synced) {?>
     <p><span class="dashicons dashicons-lock"></span>This field is synced with LDAP and cannot be modified.</p>   
    <?php }?>
    <?php wp_nonce_field( 'lab_directory_staff_meta_box_nonce_action', 'lab_directory_staff_meta_box_nonce' ); ?>
    
		<?php
	}

	function lab_directory_staff_meta_box_render_input($post, $field, $field_name, $studying_levels, $jury_functions, $ldap_synced ){
		
	
		$field_type = $field['type'] ;

		// TODO disable input depending on capability , LDAP...
		
		// Disable input when field is synced with LDAP 
		if ( $ldap_synced AND isset($field['ldap_attribute']) AND 
				($field['ldap_attribute']!='disabled') ) {
			if ($field['ldap_attribute']){ 
				$field_type = 'disabled';
			}
		}
		// Disable wp_user_id field
		if ($field['slug']=='wp_user_id'){
			$field_type = 'disabled';
		}
		
		$value = get_post_meta( $post->ID,$field['slug'], true );
		
		if ($field_type != 'disabled') {
			// handle Multivalue
			$mv_cr=false; // only true if "multiple values separated with CR" used
			
			switch ($field['multivalue']) {
				case ',' :
					$mv = '<br /><i>' . __('This entry accept multiple values', 'lab-directory') . ' (' . __("Comma (,) separated values", 'lab-directory') . ')</i>';
					break;
				case ';' :
					$mv = '<br /><i>' . __('This entry accept multiple values', 'lab-directory') . ' (' . __('Semicolumn (;) separated values', 'lab-directory') . ')</i>';
					break;
				case  '|' :
					$mv = '<br /><i>' . __('This entry accept multiple values', 'lab-directory') . ' (' . __('Vertical bar (|) separated values', 'lab-directory') . ')</i>';
					break;
				case 'MV':
				case 'CR':
					$mv = '<br /><i>' . __('This entry accept multiple values (one value per line, values separated by a carriage return)', 'lab-directory') . ')</i>';
					$mv_cr=true;
					break;
				default:
					$mv = '';
					break;
			
			}
			
			
			// override $field_type in case of multiple value accepted
			if ($mv_cr){
				// Switch to a textarea as input because value separated by a CR
				if (($field_type == 'text') OR ($field_type == 'mail') OR ($field_type == 'url') OR ($field_type == 'phone_number')
						OR ($field_type == 'longtext') )  {
							$field_type = 'textarea';
						}
			}
			elseif ($mv) {
				// else switch to a longtext
				if (($field_type == 'text') OR ($field_type == 'mail') OR ($field_type == 'url') OR ($field_type == 'phone_number')) {
					$field_type = 'long_text';
				}
			}
		}

			// Label 		$label = '';

		$label = '<label for="lab_directory_staff_meta_' . $field['slug'] . '" class="lab_directory_staff-label">';
		if ($field_type == 'disabled') {
			$label .= '<span class="dashicons dashicons-lock"></span>';
		}		
		$label .=$field_name . '</label>';
		echo '<p>';	
		
		
		
		switch ($field_type) {
			case 'text' :
			case 'mail' :
			case 'url' :
			case 'phone_number' :
				echo $label;
				?>
					<input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>"
						value="<?php echo $value; ?>"/>  
					<?php
					echo $mv;
					break;
				case 'long_text' : 
					echo $label; 
					?>
					<textarea rows=1
						name="lab_directory_staff_meta_<?php echo $field['slug'] ?>"><?php echo $value; ?></textarea>
					<?php
					echo $mv;
					break;
				case 'textarea' :
					echo $label; 
					?>
					<textarea rows=2
						name="lab_directory_staff_meta_<?php echo $field['slug'] ?>"><?php echo $value; ?></textarea>
					<?php
					echo $mv;
					break;
				case 'editor' :
					wp_editor( $post->post_content, 'attachment_content', $editor_args );
					break;
				case 'date' :
					echo $label; 
					?>
					<input type="text" class="datepicker" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>"
						value="<?php echo $value; ?>"/>
					<?php
					break;
				case 'datetime' :
					echo $label; 
					?>
					<input type="text" class="datetimepicker" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>"
						value="<?php echo $value; ?>"/>
					<?php
					break;
				case 'url' :
				case 'phone_number' :
				case 'studying_level' :
					echo $label; 
					echo lab_directory_create_select('lab_directory_staff_meta_'.$field['slug'], 
		              		$studying_levels, get_post_meta( $post->ID, $field['slug'], __('None') ) ); 
					break;
				case 'jury' :
					echo $label; 
					$jury_members = $value;
					if (is_array($jury_members)) {
						$nb_members = count($jury_members);
					} else {
						$nb_members = 0;
						$jury_members = array();
					}
		
					for ($i=$nb_members; $i<max($nb_members,8); $i++ ) {
						$jury_members[] = array(
								'order' => $i+1 ,
								'function' => '',
								'name' =>  '',
								'title' => ''
						);
					}
					?> 
					<table class="widefat fixed striped" cellspacing="0" id="lab_directory_staff-meta-fields">
					<thead>
					<tr>
					<th id="columnname" scope="col" style="width:5%;">Order</th>
					<th id="columnname" scope="col" style="width:20%;">Function</th>
					<th id="columnname" scope="col" style="width:25%;">Name</th>
					<th id="columnname" scope="col" ">Title, University, enterprise</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
					<th id="columnname" scope="col">Order</th>
					<th id="columnname" scope="col">Function</th>
					<th id="columnname" scope="col">Name</th>
					<th id="columnname" scope="col">Title, University, enterprise</th>
					</tr>
					</tfoot>
					
			        <tbody>
					<?php 
					$index = 0;
					foreach($jury_members as $jury_member) {
						$index++; 
						?>
					<tr>
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_orders[]" style="width:40px;"
						value="<?php echo $index; ?>"/>
					</td>
					<td><?php echo lab_directory_create_select($field['slug'].'_functions[]', 
		              		$jury_functions, $jury_member['function'], 'input-in-td', true); //TODO(true) ?>
					</td>
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_names[]" class="input-in-td" 
						value="<?php echo $jury_member['name']; ?>"/>
					</td>
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_titles[]" class="input-in-td" 
						value="<?php echo $jury_member['title']; ?>"/>
					</td>
					</tr>
					<?php }
					?>
					<tr id="add-new-jury-member-row" valign="top">
		          	<td colspan=4>
		            <a href="#" class="normal" id="add-new-jury-member">+ Add New jury member</a>
		          	</td>
		        	</tr>
		        	<tr id="new-jury-member">
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_orders[]" style="width:40px;"
						value="<?php echo $index; ?>"/>
					</td>
					<td><?php echo lab_directory_create_select('lab_directory_staff_meta_' . $field['slug'] . '_functions[]', 
		              		$jury_functions, $jury_member['function'], 'input-in-td', true);//TODO(true)?>
					</td>
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_names[]" class="input-in-td" 
						value="<?php echo $jury_member['firstname_name']; ?>"/>
					</td>
					<td><input type="text" name="lab_directory_staff_meta_<?php echo $field['slug'] ?>_titles[]" class="input-in-td" 
						value="<?php echo $jury_member['title']; ?>"/>
					</td>
					</tr>			
			        </tbody>
			        </table>
					<?php 	
					break;
				case 'social_network' : 
					echo 'TODO implement social_network ';
					break; 
				case 'disabled' : 
				default : // We should never arrive there !!
					// Only display field value
					echo $label;
					echo $value; // echo $field['slug'];var_dump($value);
					break; 
				}
		?>
		</p>
		<div class="clear"></div> 
        <?php
	}
	static function save_meta_boxes( $post_id ) {
		
				
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! isset( $_POST['lab_directory_staff_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['lab_directory_staff_meta_box_nonce'],
				'lab_directory_staff_meta_box_nonce_action' )
		) {
			return;
		}
		if ($_POST['save']=='Update_Status'){
			// Update staff status
			$statuss = Lab_Directory::get_lab_directory_default_statuss();
			$staff_statuss = array(); 
			foreach ($statuss as $key =>$status) {
				$staff_statuss[$key] = isset($_POST['status_'.$key]); 					
			}
			update_post_meta( $post_id, 'staff_statuss', $staff_statuss);
			return; 
		}
		
		// Else update staff entry 
		if ( ! current_user_can( 'edit_post', get_the_id() ) ) {
			return;
		}
		
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
		$lab_directory_meta_field_names = Lab_Directory::get_lab_directory_default_meta_field_names();
		$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
		$staff_statuss = get_post_meta( $post->ID, 'staff_statuss', true );
		$group_activations = get_option( 'lab_directory_group_activations' ) ;
		$used_groups = Lab_Directory_Settings::get_used_groups($active_meta_fields, $staff_statuss, $group_activations['BIO']);
		
		
		// Process form 
		// TODO this is not displayed !!
		$form_messages = array('form_saved' => false);
		// Notice: this error detection can fail if 2 users modify the same staff detail at the same time
		// TODO add $form_messages !! 
		update_post_meta( $post_id, 'messages', $form_messages);
		
		
		// Do this for each group first (to simply add capacity 
		foreach ($used_groups as $key => $group_name) {
			// Then do it for each field in a group 
			foreach ( $lab_directory_staff_settings->get_lab_directory_staff_meta_fields() as $field ) {
				if ($field['group'] == $key) {			
					Lab_Directory::lab_directory_save_meta_boxes_save_meta($post_id, $field, $lab_directory_meta_field_names[$field['slug']]);
				}
			}
		}
		return; 
		
	}

	static function lab_directory_save_meta_boxes_save_meta($post_id, $field, $field_name){
	
		$slug = 'lab_directory_staff_meta_' . $field['slug'];
		
	
		// Unsanitized value (excepted for jury social network)
		$value =  isset($_POST[$slug]) ? $_POST[$slug]: '';
				
		switch ($field['type']) {
			case 'text' :
				$value = sanitize_text_field($value); 
				break;
			case 'longtext' : 
				$value = sanitize_text_field($value);
				break;
			case 'textarea' :
				$value = esc_textarea($value);
				break;
			case 'editor' :
				$value = esc_textarea($value);
				break;
			case 'date' :
				$value = lab_directory_strtotime($value, "Y-m-d");
				break;
			case 'datetime' :
				$value = lab_directory_strtotime($value, "Y-m-d h:m"); 
				break;
			case 'mail' :
				$value = sanitize_email($value);
				break;
			case 'url' :
				$value = esc_url($value);
				break;
			case 'phone_number' :
				$value = sanitize_text_field($value);
				break;
			case 'studying_level' :
				$value = sanitize_text_field($value);
				break;
			case 'jury' :
				$orders = $_POST[$slug . '_orders'];
				$names = $_POST[$slug . '_names'];
				$functions = $_POST[$slug . '_functions'];
				$titles = $_POST[$slug . '_titles'];
				$index = 0;
				
				$value = array();
				foreach ( $orders as $order ) {
					if ($names[ $index ]) { 
						$value[] = array(
							'order' => (int) $order,
							'function' => sanitize_text_field($functions[ $index ]),
							'name' => sanitize_text_field($names[ $index ]),
							'title' => sanitize_text_field($titles[ $index ]),
						);
					}
					$index ++;
				}
				usort($value, __NAMESPACE__ . '\ld_compare_jury_order');
				break;
			default : // We should never arrive there !!
				// update_post_meta( $post_id, $meta_field_slug, esc_attr( $_POST['lab_directory_staff_meta'][ $meta_field_slug ] ) );
				$value= esc_attr($value); 
				
				break; 
		}
	
		if ($value !== null) {
			update_post_meta( $post_id, $field['slug'], $value );
		}
	}
		
	
	static function set_default_meta_fields_if_necessary() {
		$current_meta_fields = get_option( 'lab_directory_staff_meta_fields' );

		if ( $current_meta_fields == null || $current_meta_fields = '' ) {				 
			$default_meta_fields = array();
			update_option( 'lab_directory_staff_meta_fields', $default_meta_fields );
		}
	}

	static function get_default_meta_fields() {
					
		/* $default_meta_fields  list all predefined fields usable in lab directory 
		 * 
		 * structure of this variable: 
		 * 
		 * name : the name of the field in english default language (translatable)
		 * type : type of this field (see $default_type)
		 * slug : the slug define the field, it cannot be changed
		 * ldap_attribute : empty string or 'disabled' if this field is not LDAP syncable
		 * multivalue : as defined in $default_multivalue 
		 * 		or 'special' for field always having single value and fixed type
		 * 		(example jury, dates, studying level...)
		 * show_frontend : '1' if this field should not be displayed in frontend
		 * predefined : '1' if this field is predefined by the plugin (always here) 
		 */
		 
		//TODO move slug to main key? 
		$default_meta_fields = array(
			array(
				'order' => 1, 
				'type'=> 'text',
				'slug' => 'firstname',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
			),
			array(
				'order' => 2, 
				'type'=> 'text',
				'slug' => 'name',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
			),	
			array(
				'order' => 3, 
				'type'=> 'text',
				'slug' => 'position',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'MV',
				'show_frontend' =>'1',				
				'activated' => '1',
			),
			array(
				'order' => 4,
				'type'=> 'text',
				'slug' => 'login',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'0',
				'activated' => '1',
			),
			array(
				'order' => 4.5,
				'type'=> 'text',
				'slug' => 'wp_user_id',
				'group' => 'CV',
				'ldap_attribute' => 'disabled',
				'multivalue' => 'special',
				'show_frontend' =>'0',
				'activated' => '1',
			),
				array(
				'order' => 5, 
				'type'=> 'text',
				'slug' => 'mails',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'SV',
				'show_frontend' =>'1',
				'activated' => '1',
			),
				array(
				'order' => 5, 
				'type'=> 'text',
				'slug' => 'other_mails',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'MV',
				'show_frontend' =>'1',
				'activated' => '1',
			),
				
			array(
				'order' => 6, 
				'type'=> 'editor',
				'slug' => 'bio',
				'group' => 'BIO',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
			),	
			array(
				'order' => 7, 
				'type'=> 'text',
				'slug' => 'idhal',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
			),						
				array(
				'order' => 8,
				'type'=> 'url',
				'slug' => 'photo_url',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
				),
			array(
				'order' => 9, 
				'type'=> 'url',
				'slug' => 'webpage',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'special',
				'show_frontend' =>'1',
				'activated' => '1',
			),
			array(
				'order' => 10, 
				'type'=> 'text',
				'slug' => 'function',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'MV',
				'show_frontend' =>'1',
				'activated' => '1',
			),
			array(
					'order' => 10.5,
					'type'=> 'social_network',
					'slug' => 'social_network',
					'group' => 'CV',
					'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
					'activated' => '1',
				),
			array(
				'order' => 11, 
				'type'=> 'text',
				'slug' => 'title',
				'group' => 'CV',
				'ldap_attribute' => '',
				'multivalue' => 'MV',
				'show_frontend' =>'1',
				'activated' => '1',
			),
			array(
					'order' => 12, 
				'type'=> 'text',
					'slug' => 'phone',
					'group' => 'CV',
				'ldap_attribute' => '',
					'multivalue' => 'MV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 13, 
				'type'=> 'text',
					'slug' => 'fax',
					'group' => 'CV',
				'ldap_attribute' => '',
					'multivalue' => 'MV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 14, 
				'type'=> 'text',
					'slug' => 'office',
					'group' => 'CV',
				'ldap_attribute' => '',
					'multivalue' => 'MV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 15, 
				'type'=> 'text',
					'slug' => 'team',
					'group' => 'CV',
				'ldap_attribute' => '',
					'multivalue' => 'MV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 16, 
				'type'=> 'date',
					'slug' => 'exit_date',
					'group' => 'CV',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'0',
		 			'activated' => '1',
			),
			array(
					'order' => 17, 
				'type'=> 'longtext',
					'slug' => 'hdr_subject',
					'group' => 'HDR',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 18, 
				'type'=> 'date',
					'slug' => 'hdr_date',
					'group' => 'HDR',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 19, 
				'type'=> 'text',
					'slug' => 'hdr_location',
					'group' => 'HDR',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 22, 
				'type'=> 'jury',
					'slug' => 'hdr_jury',
					'group' => 'HDR',
				'ldap_attribute' => 'disabled',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 21, 
				'type'=> 'longtext',
					'slug' => 'hdr_resume',
					'group' => 'HDR',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 22, 
				'type'=> 'date',
					'slug' => 'phd_start_date',
					'group' => 'HDR',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 23, 
				'type'=> 'longtext',
					'slug' => 'phd_subject',
					'group' => 'doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 24, 
				'type'=> 'date',
					'slug' => 'phd_date',
					'group' => 'doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 25, 
				'type'=> 'text',
					'slug' => 'phd_location',
					'group' => 'doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 26, 
				'type'=> 'jury',
					'slug' => 'phd_jury',
					'group' => 'doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 27, 
				'type'=> 'longtext',
					'slug' => 'phd_resume',
					'group' => 'doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),						
			array(
					'order' => 28, 
				'type'=> 'date',
					'slug' => 'post_doc_start_date',
					'group' => 'post-doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),			
			array(
					'order' => 29, 
				'type'=> 'date',
					'slug' => 'post_doc_end_date',
					'group' => 'post-doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 30, 
				'type'=> 'longtext',
					'slug' => 'post_doc_subject',
					'group' => 'post-doctorate',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 31, 
				'type'=> 'date',
					'slug' => 'internship_start_date',
					'group' => 'internship',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 32, 
				'type'=> 'date',
					'slug' => 'internship_end_date',
					'group' => 'internship',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 32.1, 
				'type'=> 'text',
					'slug' => 'internship_subject',
					'group' => 'internship',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 32.2, 
				'type'=> 'longtext',
					'slug' => 'internship_resume',
					'group' => 'internship',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 33, 
				'type'=> 'longtext',
					'slug' => 'studying_school',
					'group' => 'internship',
				'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 34, 
				'type'=> 'studying_level',
					'slug' => 'studying_level',
					'group' => 'internship',
				'ldap_attribute' => 'disabled',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 35, 
				'type'=> 'date',
					'slug' => 'invitation_start_date',
					'group' => 'invited',
				'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 36, 
					'type'=> 'date',
					'slug' => 'invitation_end_date',
					'group' => 'invited',
					'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 37, 
					'type'=> 'text',
					'slug' => 'invitation_goal',
					'group' => 'invited',
					'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 38, 
					'type'=> 'longtext',
					'slug' => 'invited_position',
					'group' => 'invited',
					'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 39, 
					'type'=> 'longtext',
					'slug' => 'invited_origin',
					'group' => 'invited',
					'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 40, 
					'type'=> 'date',
					'slug' => 'cdd_start_date',
					'group' => 'CDD',
					'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 41, 
					'type'=> 'date',
					'slug' => 'cdd_end_date',
					'group' => 'CDD',
					'ldap_attribute' => '',
					'multivalue' => 'special',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 42, 
					'type'=> 'text',
					'slug' => 'cdd_goal',
					'group' => 'CDD',
					'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),
			array(
					'order' => 43, 
					'type'=> 'longtext',
					'slug' => 'cdd_position',
					'group' => 'CDD',
					'ldap_attribute' => '',
					'multivalue' => 'SV',
					'show_frontend' =>'1',
		 			'activated' => '1',
			),						
				
		);
	for ($i = 1; $i <= 10; $i++) {
				$default_meta_fields[] = array(
						'order' => 50+$i, 
						'type'=> 'text',
						'slug' => "custom_field_$i",
						'group' => 'custom_group',
						'ldap_attribute' => '',
						'multivalue' => 'SV',
						'show_frontend' =>'1',
						'activated' => '0',
				);
			}
				
	return $default_meta_fields;
	}

	#
	# Default templates
	#

	static function set_default_templates_if_necessary() {
		if ( get_option( 'lab_directory_template_slug' ) == '' ) {
			update_option( 'lab_directory_template_slug', 'list' );
		}

		$has_custom_templates = count(Lab_Directory_Settings::shared_instance()->get_custom_lab_directory_staff_templates()) > 0;

		if ( get_option( 'lab_directory_html_template' ) == '' && !$has_custom_templates ) {
			$default_html_template = <<<EOT
<div class="lab-directory">

  [lab_directory_staff_loop]

    [name_header]
    [bio_paragraph]

    <div class="lab-directory-divider"></div>

  [/lab_directory_staff_loop]

</div>
EOT;
			update_option( 'lab_directory_html_template', $default_html_template );
		}

		if ( get_option( 'lab_directory_css_template' ) == '' && !$has_custom_templates ) {
			$default_css_template = <<<EOT
.lab-directory-divider{
  border-top: solid black thin;
  width: 90%;
  margin:15px 0;
}
EOT;
			update_option( 'lab_directory_css_template', $default_css_template );
		}
	}

	#
	# Related to old lab_directory_staff members
	#

	static function has_old_lab_directory_staff_table() {
		global $wpdb;
		$lab_directory_table = $wpdb->prefix . 'lab_directory';

		$old_lab_directory_staff_sql           = "SHOW TABLES LIKE '$lab_directory_table'";
		$old_lab_directory_staff_table_results = $wpdb->get_results( $old_lab_directory_staff_sql );

		return count( $old_lab_directory_staff_table_results ) > 0;
	}

	static function show_import_message() {
		if (
			isset( $_GET['page'] )
			&&
			$_GET['page'] == 'lab-directory-import'
			&&
			isset( $_GET['import'] )
			&&
			$_GET['import'] == 'true'
		) {
			return false;
		}

		return Lab_Directory::has_old_lab_directory_staff_table();
	}

	static function get_old_lab_directory_staff( $orderby = null, $order = null, $filter = null ) {
		global $wpdb;
		$lab_directory_table      = $wpdb->prefix . 'lab_directory';
		$lab_directory_categories = $wpdb->prefix . 'lab_directory_categories';

		if ( ( isset( $orderby ) AND $orderby != '' ) AND ( isset( $order ) AND $order != '' ) AND ( isset( $filter ) AND $filter != '' ) ) {

			if ( $orderby == 'name' ) {

				$all_lab_directory_staff = $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE . " WHERE `category` = $filter ORDER BY `name` $order" );

			}

			if ( $orderby == 'category' ) {

				$categories = $wpdb->get_results( "SELECT * FROM $lab_directory_categories WHERE `cat_id` = $filter ORDER BY name $order" );

				foreach ( $categories as $category ) {
					$cat_id = $category->cat_id;
					//echo $cat_id;
					$lab_directory_staff_by_cat = $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE . " WHERE `category` = $cat_id" );
					foreach ( $lab_directory_staff_by_cat as $lab_directory_staff ) {
						$all_lab_directory_staff[] = $lab_directory_staff;
					}
				}
			}

			return $all_lab_directory_staff;


		} elseif ( ( isset( $orderby ) AND $orderby != '' ) AND ( isset( $order ) AND $order != '' ) ) {

			if ( $orderby == 'name' ) {

				$all_lab_directory_staff = $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE . " ORDER BY `name` $order" );

			}

			if ( $orderby == 'category' ) {

				$all_lab_directory_staff = $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE . " ORDER BY category $order" );

			}


			return $all_lab_directory_staff;

		} elseif ( isset( $filter ) AND $filter != '' ) {

			$all_lab_directory_staff = $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE . " WHERE `category` = $filter" );
			if ( isset( $all_lab_directory_staff ) ) {
				return $all_lab_directory_staff;
			}

		} else {

			return $wpdb->get_results( "SELECT * FROM " . LAB_DIRECTORY_TABLE );

		}
	}

	static function import_old_lab_directory_staff() {
		global $wpdb;

		$old_categories_table      = $wpdb->prefix . 'lab_directory_categories';
		$old_lab_directory_table = $wpdb->prefix . 'lab_directory';
		$old_templates_table       = LAB_DIRECTORY_TEMPLATES;

		#
		# Copy old categories over first
		#

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

		#
		# Now copy old lab_directory_staff members over
		#

		$old_lab_directory_staff = Lab_Directory::get_old_lab_directory_staff();
		foreach ( $old_lab_directory_staff as $lab_directory_staff ) {
			$new_lab_directory_staff_array   = array(
				'post_title'   => $lab_directory_staff->name,
				'post_content' => $lab_directory_staff->bio,
				'post_type'    => 'lab_directory_staff',
				'post_status'  => 'publish'
			);
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
				$old_category     = $wpdb->get_results( $old_category_sql );
				$new_category     = get_term_by( 'name', $old_category[0]->name, 'lab_category' );
				wp_set_post_terms( $new_lab_directory_staff_post_id, array( $new_category->term_id ), 'lab_category' );
			}

			if ( isset( $lab_directory_staff->photo ) && $lab_directory_staff->photo != '' ) {
				$upload_dir    = wp_upload_dir();
				$upload_dir    = $upload_dir['basedir'];
				$image_path    = $upload_dir . '/lab-directory-photos/' . $lab_directory_staff->photo;
				$filetype      = wp_check_filetype( $image_path );
				$attachment_id = wp_insert_attachment( array(
					'post_title'     => $lab_directory_staff->photo,
					'post_content'   => '',
					'post_status'    => 'publish',
					'post_mime_type' => $filetype['type']
				), $image_path, $new_lab_directory_staff_post_id );
				set_post_thumbnail( $new_lab_directory_staff_post_id, $attachment_id );
			}
		}

		#
		# Now copy templates over
		#

		$old_html_template_sql     = "
      SELECT
        template_code

      FROM
        $old_templates_table

      WHERE
        template_name='lab_directory_staff_index_html'
    ";
		$old_html_template_results = $wpdb->get_results( $old_html_template_sql );
		update_option( 'lab_directory_html_template', $old_html_template_results[0]->template_code );

		$old_css_template_sql     = "
      SELECT
        template_code

      FROM
        $old_templates_table

      WHERE
        template_name='lab_directory_staff_index_css'
    ";
		$old_css_template_results = $wpdb->get_results( $old_css_template_sql );
		update_option( 'lab_directory_css_template', $old_css_template_results[0]->template_code );

		#
		# Now delete the old tables
		#

		$drop_tables_sql = "
      DROP TABLE
        $old_categories_table, $old_lab_directory_table, $old_templates_table
    ";
		$wpdb->get_results( $drop_tables_sql );
	}

	static function init_tinymce_button() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			return;
		}

		add_filter( "mce_external_plugins", array( 'Lab_Directory', 'register_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( 'Lab_Directory', 'add_tinymce_button' ) );
	}

	static function register_tinymce_plugin( $plugin_array ) {
		$plugin_array['lab_directory_button'] = plugins_url( '/../js/shortcode.js', __FILE__ );;

		return $plugin_array;
	}

	static function add_tinymce_button( $buttons ) {
		$buttons[] = "lab_directory_button";

		return $buttons;
	}

	static function thickbox_ajax_form() {
		require_once( plugin_dir_path( __FILE__ ) . '/../views/shortcode-thickbox.php' );
		exit;
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
			$order   = $query->get( 'order' ) ?: 'ASC';

			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );
		}
	}
	
	/** 
	 * Remove quick-edit action
	 */
	static function modify_quick_edit( $actions ) {
		
	if( get_post_type() == 'lab_directory_staff' ) {
        // unset( $actions['edit'] );
        // unset( $actions['view'] );
        // TODO permissions ?? 
        unset( $actions['trash'] );
        unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}
	
	
	static function add_lab_directory_staff_categories_admin_filter(){
    global $post_type;

    if($post_type == 'lab_directory_staff'){

      $lab_category_args = array(
        'show_option_all'   => 'All Staff Categories',
        'orderby'           => 'ID',
        'order'             => 'ASC',
        'name'              => 'lab_category_admin_filter',
        'taxonomy'          => 'lab_category'
      );

      if (isset($_GET['lab_category_admin_filter'])) {
        $lab_category_args['selected'] = sanitize_text_field($_GET['lab_category_admin_filter']);
      }

      wp_dropdown_categories($lab_category_args);

    }
	}

	static function filter_admin_lab_directory_staff_by_category($query) {
		global $post_type, $pagenow;

    if($pagenow == 'edit.php' && $post_type == 'lab_directory_staff'){
        if(isset($_GET['lab_category_admin_filter'])){

          $post_format = sanitize_text_field($_GET['lab_category_admin_filter']);

          if($post_format != 0){
            $query->query_vars['tax_query'] = array(
              array(
                'taxonomy'  => 'lab_category',
                'field'     => 'ID',
                'terms'     => array($post_format)
              )
            );
          }
        }
    } 
	}
	
	static function lab_directory_custom_translations( $translated, $original, $domain ) {
	
	
		if ( 'lab-directory' == $domain ) {
			if ( array_key_exists($original, self::$translations )) {
				$translated = self::$translations[$original];
			}
		}
	
		return $translated;
	}
	
	
	
	
	public function get_lab_directory_default_permissions() {

		// Define default permissions;
		$permissions = array (
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
			  "ld_custom_group_view_staff_lists_profiles" => "0"
			); 
		
		return $permissions;
	}
	
	public function get_lab_directory_meta_field_input_types() {

		// Define the default type and the input type to use for input
		
		// Deprecatedand unused , don't use this 
		$default_type_input_types = array(
			'text' =>'text',
			'mail' =>'text',
			'url' =>'text',
			'phone_number' =>'text',
			'date' =>'date',
			'long_text' =>'textarea',
			'jury' =>'jury',
			'studying_level' => 'studying_level'
		);
		return $default_type_input_types; 
	}
			
	public function get_lab_directory_meta_field_types() {
		
		// Define the default type text to use for field name and their internationalisation
		$default_type_texts = array(
			'text' => __( 'text', 'lab-directory'),
			'mail' =>__( 'mail', 'lab-directory'),
			'url' =>__( 'URL', 'lab-directory'),
			'phone_number' =>__( 'Phone number', 'lab-directory'),
			'date' =>__( 'Date', 'lab-directory'),
			'datetime' =>__( 'Date and Time', 'lab-directory'),
			'long_text' =>__( 'Long text', 'lab-directory'),
			'studying_level' => __( 'Studying_level', 'lab-directory'),
			'editor' =>__( 'HTML Text', 'lab-directory'),
			'jury' =>__( 'Jury', 'lab-directory'),
		);
		return $default_type_texts; 
	}

	function get_lab_directory_default_group_names() {
	
		// Define the default groups used for meta field grouping
		$groups = array(
				/* translators: CV Curriculum Vitae (no need to translate this) */ 
				'CV' => __( 'CV', 'lab-directory'),
				'BIO' => __('Biography', 'lab-directory'),
		);
		$groups = array_merge($groups,self::get_lab_directory_default_group_names2());
		return $groups;
	}
	function get_lab_directory_default_group_names2() {
	
		// Define the default groups used for meta field grouping
		$groups = array(
				/* translators: HDR french acronym for Habilitation à Diriger les Recherches */
				'HDR' =>__( 'HDR', 'lab-directory'),
				'doctorate' =>__( 'Doctorate', 'lab-directory'),
				'post-doctorate' =>__( 'Post-doctorate', 'lab-directory'),
				'internship' =>__( 'Internship', 'lab-directory'),
				'invited' =>__( 'Invited', 'lab-directory'),
				/* translators: CDD is a french acronym for Fixed term contract*/
				'CDD' =>__( 'CDD', 'lab-directory'),
				'custom_group' =>__( 'custom_group', 'lab-directory'),
		);
		return $groups;
	}
	
	function get_lab_directory_default_statuss() {
	
		// Define the default groups used for meta field grouping
		$statuss = array(
				/* translators: CV Curriculum Vitae (no need to translate this) */
				'permanent' => __( 'Permanent staff', 'lab-directory'),
				'administrator' => __('Administrative staff', 'lab-directory'),
		);
		$statuss = array_merge($statuss,self::get_lab_directory_default_group_names2());
		return $statuss;
	}
	
	
	
	public function get_lab_directory_multivalues() {
		
		// Define the list of option related to single and multivalue of fields
		$default_multivalue = array(
			
			'SV' => __('Single valued', 'lab-directory'),
			'MV' => __('Multiple valued', 'lab-directory'),
			','  => __("(') separated list", 'lab-directory'),
			';'  => __('(;) separated list', 'lab-directory'),
			'|'  => __('(|) separated list', 'lab-directory'),
			'CR' => __('CR separated list', 'lab-directory'),

		);
		return $default_multivalue;
	}

	public function get_lab_directory_multivalues_names() {
	
		// Explain the list of option related to single and multivalue of fields
		$note1 = ' (' . __('only first value of attribute will be extracted if LDAP attribute is multivalued', 'lab-directory'). ')';
		$default_multivalue_names = array(
					
				'SV' => __('Single valued (only first value will be extracted if LDAP attribute is multivalued and has multiple values)', 'lab-directory'),
				'MV' => __('Multiple valued (extract all values if LDAP attribute is multivalued)', 'lab-directory'),
				','  => __("Comma (') separated list", 'lab-directory') . $note1,
				';'  => __('Semicolumn (;) separated list (', 'lab-directory') . $note1,
				'|'  => __('Vertical bar (|) separated list', 'lab-directory') . $note1,
				'CR' => __('Carriage return separated list (*)', 'lab-directory') . $note1,
				'(*)' => __('Carriage return separated list are saved in database using a Semicolumn (;) separated list', 'lab-directory'),
		);
		return $default_multivalue_names;
	}
	
	public function get_lab_directory_studying_levels() {
	
		// Define the list of studying levels
		$studying_levels = array(
	
		'L1' => __('L1 (Bachelor  1st year)', 'lab-directory'),
		'L2' => __('L2 (Bachelor 2nd year)', 'lab-directory'),
		'L3' => __('L3 (Bachelor 3rd yaer)', 'lab-directory'),
		'M1' => __('M1 (Master 1st year)', 'lab-directory'),
		'M2' => __('M2 (Master 2ème année)', 'lab-directory'),
		'ING' => __('Engineering School', 'lab-directory'),
			);
	return $studying_levels;
	}
	
	public function get_lab_directory_jury_functions() {
	
		// Define the list of function use in HDR and PHD jury
		$jury_functions = array(	
	'guarantor' => __('Garant de la HDR', 'lab-directory'),
	'chairman' => __('President', 'lab-directory'),
	'chairwoman' => __('Presidente', 'lab-directory'),
	'director' => __('Directeur', 'lab-directory'),
	'directress' => __('Directrice', 'lab-directory'),
	'directors' => __('Directeurs', 'lab-directory'),
	'examiner' => __('Examinateur', 'lab-directory'),
	/* translator examiner / female */
	'examiner_f' => __('Examinatrice', 'lab-directory'),
	'examiners' => __('Examinateurs', 'lab-directory'),
	'referee' => __('Rapporteur', 'lab-directory'),
	/* translator referee / female*/
	'referee_f' => __('Rapportrice', 'lab-directory'),
	'referees' => __('Rapporteurs', 'lab-directory'),
	'invited' => __('Invité', 'lab-directory'),
	'invited_f' => __('Invitée', 'lab-directory'),
	'inviteds' => __('Invités', 'lab-directory'),
			);
	return $jury_functions;
	}
	
	static function get_lab_directory_ldap_attributes() {
    
        	$ldap_attributes = array();
        	$ldap_server = get_option( 'lab_directory_ldap_server' );
        	$keys = explode (';',$ldap_server['ldap_attributes']);
        	foreach ($keys as $key){
        		$ldap_attributes[$key] = $key;
        	}
        	return $ldap_attributes;
        	
}

	static function get_lab_directory_default_meta_field_names() {
		
		// Translation of meta_fields are save here to be reloaded (refreshed) each time without saving in Database
		$default_meta_field_names = array(
				'firstname' => __( 'Firstname', 'lab-directory'),
				'name' => __( 'Name', 'lab-directory'),
				'position' => __( 'Position', 'lab-directory'),
				'login' => __( 'Login', 'lab-directory'),
				'wp_user_id' => __( 'Wordpress user ID', 'lab-directory'),
				'mails' => __( 'Mail', 'lab-directory'),
				'bio' => __('Biography', 'lab-directory'),
				'other_mails' => __( 'Other mails', 'lab-directory'),
				'idhal' => __( 'ID HAL', 'lab-directory'),
				'photo_url' => __( 'Photo URL', 'lab-directory'),
				'webpage' => __( 'Professionnal webpage', 'lab-directory'),
				'social_network' => __('Social Network', 'lab-directory'),
				'function' => __( 'Function', 'lab-directory'),
				'title' => __( 'Title', 'lab-directory'),
				'phone' => __( 'Phone number', 'lab-directory'),
				'fax' => __( 'Fax number', 'lab-directory'),
				'office' => __( 'Office', 'lab-directory'),
				'team' => __( 'Team', 'lab-directory'),
				'exit_date' => __( 'End activity date', 'lab-directory'),
				'hdr_subject' => __( 'HDR subject', 'lab-directory'),
				'hdr_date' => __( 'HDR defense date', 'lab-directory'),
				'hdr_location' => __( 'HDR defense location', 'lab-directory'),
				'hdr_jury' => __( 'HDR jury', 'lab-directory'),
				'hdr_resume' => __( 'HDR resume', 'lab-directory'),
				'phd_start_date' => __( 'PHD start date', 'lab-directory'),
				'phd_subject' => __( 'PHD subject', 'lab-directory'),
				'phd_date' => __( 'PHD defense date', 'lab-directory'),
				'phd_location' => __( 'PHD defense location', 'lab-directory'),
				'phd_jury' => __( 'PHD jury', 'lab-directory'),
				'phd_resume' => __( 'PHD resume', 'lab-directory'),
				'post_doc_start_date' => __( 'Post Doct. start date', 'lab-directory'),
				'post_doc_end_date' => __( 'Post Doct. end date', 'lab-directory'),
				'post_doc_subject' => __( 'Post Doct. subject', 'lab-directory'),
				'internship_start_date' => __( 'Internship start date', 'lab-directory'),
				'internship_end_date' => __( 'Internship end date', 'lab-directory'),
				'internship_subject' => __( 'Internship subject', 'lab-directory'),
				'internship_resume' => __( 'Internship resume', 'lab-directory'),
				'studying_school' => __( 'Studying school', 'lab-directory'),
				'studying_level' => __( 'Studying level', 'lab-directory'),
				'invitation_start_date' => __( 'Start date', 'lab-directory'),
				'invitation_end_date' => __( 'End date', 'lab-directory'),
				'invitation_goal' => __( 'Invitation goal', 'lab-directory'),
				'invited_position' => __( 'Position', 'lab-directory'),
				'invited_origin' => __( 'Invited origin', 'lab-directory'),
				/* translators Fixed term contract information */
				'cdd_start_date' => __( 'Contract start date', 'lab-directory'),
				/* translators Fixed term contract information */
				'cdd_end_date' => __( 'Contract end date', 'lab-directory'),
				/* translators Fixed term contract information */
				'cdd_goal' => __( 'Contract goal', 'lab-directory'),
				/* translators Fixed term contract information */
				'cdd_position' => __( 'Occupied position', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_1' => __( 'custom_field_1', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_2' => __( 'custom_field_2', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_3' => __( 'custom_field_3', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_4' => __( 'custom_field_4', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_5' => __( 'custom_field_5', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_6' => __( 'custom_field_6', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_7' => __( 'custom_field_7', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_8' => __( 'custom_field_8', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_9' => __( 'custom_field_9', 'lab-directory'),
				/* translators: Do not translate.  This will be translated by each user depending on their custom fields usage. */ 
				'custom_field_10' => __( 'custom_field_10', 'lab-directory'),
		);
		return $default_meta_field_names;
	}

	/* 
	 *  This function is runned once at init to calculate almost every permissions one time
	 *   (excepted for own permissions) in order to speed up ld_user_can function
	 */
	static function initiate_ld_permissions() {
		self::$ld_permissions =  get_option( 'lab_directory_permissions');	
	}
	
	static function initiate_staff_meta_fields() {
		self::$staff_meta_fields =  get_option( 'lab_directory_staff_meta_fields');
	} 

	static function initiate_translations() {
		
		self::$translations =  get_option( 'lab_directory_translations_' . get_locale());
	}
	
	static function initiate_acronyms() {
		self::$acronyms =  get_option( 'lab_directory_translations_acronyms');
	}
	
	static function initiate_capabilities() {
		$temp = array("0" => "test permissions modifié");
		self::$capabilities = array(
				'settings_general' => array(
						'name' =>'General settings',
						'scope' =>'all'),
				'settings_permissions' => array(
						'name' =>'Permissions settings',
						'scope' =>'all'),
				'ldap_settings' => array(
						'name' =>'LDAP settings',
						'scope' =>'all'),
				'ldap_syncing' => array(
						'name' =>'LDAP syncing',
						'scope' =>'all'),
				'group_of_fields_settings' => array(
						'name' =>'Groups of field settings',
						'scope' =>'all'),
				'meta_fields_settings' => array(
						'name' =>'Meta field settings',
						'scope' =>'all'),
				'acronyms_settings' => array(
						'name' =>'Acronyms settings',
						'scope' =>'all'),
				'taxonomies_settings' => array(
						'name' =>'Taxonomies settings',
						'scope' =>'all'),
				'validate_new_staff_entry' => array(
						'name' =>'Validate new staff entry',
						'scope' =>'all'),
				'give_permanent_status' => array(
						'name' =>'Give permanent status',
						'scope' =>'all'),
				'give_administrative_status' => array(
						'name' =>'Give administrative status',
						'scope' =>'all'),
				'give_hdr_status' => array(
						'name' =>'Give HDR status',
						'scope' =>'all'),
				'give_phd_status' => array(
						'name' =>'Give PHD status',
						'scope' =>'all'),
				'give_post_doc_status' => array(
						'name' =>'Give post-doc status',
						'scope' =>'all'),
				'give_internship_status' => array(
						'name' =>'Give internship status',
						'scope' =>'all'),
				'give_invited_status' => array(
						'name' =>'Give HDR status',
						'scope' =>'all'),
				'give_cdd_status' => array(
						'name' =>'Give PHD status',
						'scope' =>'all'),
				'give_other_status' => array(
						'name' =>'Give "other" (custom) status', // Is this usefull? 
						'scope' =>'all'),
				'edit_staff_profile' => array(
						'name' =>'Edit staff profile',
						'scope' =>'all'),
				'edit_own_staff_profile' => array(
						'name' =>'Edit its own profile',	
						'scope' =>'own'),
				'view_staff_lists_profiles' => array(
						'name' =>'View staff lists and profiles',
						'scope' =>'all'),

		);
		
	}
	
	static function lab_directory_staff_photo_meta_box( $content, $post_id, $thumbnail_id ){

		if (get_post_meta( $post_id, 'ldap', true)>0 ) {
			if ($thumbnail_id) {
				$content = preg_match('#(<img.*?>)#', $content, $matches)? $matches[0]: '';
			}
			
			$content .= '<p><i><span class="dashicons dashicons-lock"></span>' . __( 'Staff photo synchronised with LDAP can only be changed on LDAP directory', 'lab_directory' ) . '</i></p>';
		}
    return $content;
	}
	
    static function remove_publish_box() {
    	remove_meta_box( 'submitdiv', 'lab_directory_staff', 'side' );
    }
    static function hide_permalink($content) {
       return $content;
    	
    }

}

function lab_directory_strtotime($time, $format="Y-m-d") {
	$out = ''; 
	if ($time) {
		$out = date($format, strtotime($time));
	}
	return $out; 
}

function ld_compare_jury_order($a, $b)
{
	return (int)$a['order']-(int)$b['order'];
}

/*
 * public function to ger user permission
 *  $capability: capability key 
 */
function ld_user_can($capability, $user_id=null) {
	global $current_user;
	if (!$capability) {return false;} 
	if ($user_id) {
		$user = get_userdata($user_id );
		if ($user ) {
			return ld_user_can_by_user($capability, $user);
		} else {
			return false; 
		}
	} else {
		return ld_user_can_by_user($capability, $current_user); 
	}

}
function ld_user_can_by_user($capability, $user) {

	// if (in_array('administrator',  $user->roles))  { return true;};
	if (!$user->id) { return false;}
	
	$scope = Lab_Directory::$capabilities[$capability]['scope'];
	// unvalid scope (capability) 
	if (! $scope) {return false;}
	
	// scope = all, capability based on WP groups
	if ($scope == 'all') {
		foreach ($user->roles as $role_key => $role ) {
			$capability_key = 'wp_' . $role . '_' . $capability;
	// echo ($capability_key); echo '<pre>';var_dump(Lab_Directory::$ld_permissions);echo '</pre>';die();
			
	if (Lab_Directory::$ld_permissions[$capability_key]==true) {return true;}	
		}
	}

	// the rest need link between WP and LD??
	$user_ld_id = false; 
	
	// TODO temporary return capability calculation not completed; 
	return false; 
	// query select staff_id from staf / wp_user_id= $current_user->id;
	
	
	if (! $user_ld_id) {return false;}
	
	// scope = owner capability based on WP groups
	
	return false; 
	
	// scope = all capability based on LD groups 

	// scope = owner capability based on LD groups
	return false; 
}

/*
 * This function convert a value depending on its multivalue type
 */
function ld_value_to_something($value=false, $multivalue=false, $to='string') {
	switch ($to) {
		case 'string':
			 //TODO usefull? 
			 if (! $value) {return '';}
			 switch ($multivalue) {
			 	case 'special': 
			  	case 'SV':
				 	break;
				 case 'MV':
				 	break;
				 case ',':
				 	break;
				 case ';':
				 	break;
				 case '|':
				 	break;
				 case 'CR':
				 	break;
			}
			break; 
		case 'array': 
			if (! $value) {
				$res = array(); 
				return $res;
			}
			switch ($multivalue) {
				case 'special':
			  	case 'SV': 
					$value = array($value);
					break;
				case 'MV':
				case ';':
					$value = explode(';',$value);
					break;
				case ',':
					$value = explode(',',$value);
					break;
				case '|':
					$value = explode('|',$value);
					break;
				case 'CR':
					$value = explode("\n",$value);
					break;
			}
			return $value;
			break; 
	}
	return $res; 
			
	}


	