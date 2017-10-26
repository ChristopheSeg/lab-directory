<?php

class Lab_Directory {

	#
	# Init custom post types
	#

	static function register_post_types() {
		add_action( 'init', array( 'Lab_Directory', 'create_post_types' ) );
		add_action( 'init', array( 'Lab_Directory', 'create_lab_directory_staff_taxonomies' ) );
		add_filter( "manage_edit-lab_directory_staff_columns", array( 'Lab_Directory', 'set_lab_directory_staff_admin_columns' ) );
		add_filter( "manage_lab_directory_staff_posts_custom_column", array(
			'Lab_Directory',
			'custom_lab_directory_staff_admin_columns'
		), 10, 3 );
		add_filter( 'manage_edit-lab_category_columns', array( 'Lab_Directory', 'set_lab_category_columns' ) );
		add_filter( 'manage_lab_category_custom_column', array( 'Lab_Directory', 'custom_lab_category_columns' ), 10, 3 );

		add_filter( 'enter_title_here', array( 'Lab_Directory', 'lab_directory_staff_title_text' ) );
		add_filter( 'admin_head', array( 'Lab_Directory', 'remove_media_buttons' ) );
		add_action( 'add_meta_boxes_lab_directory_staff', array( 'Lab_Directory', 'add_lab_directory_staff_custom_meta_boxes' ) );
		add_action( 'save_post', array( 'Lab_Directory', 'save_meta_boxes' ) );
		add_action( 'wp_enqueue_scripts', array( 'Lab_Directory', 'enqueue_fontawesome' ) );
		add_action( 'admin_enqueue_scripts', array( 'Lab_Directory', 'enqueue_fontawesome' ) );

		add_action( 'init', array( 'Lab_Directory', 'init_tinymce_button' ) );
		add_action( 'wp_ajax_get_my_form', array( 'Lab_Directory', 'thickbox_ajax_form' ) );
		add_action( 'pre_get_posts', array( 'Lab_Directory', 'manage_listing_query' ) );

    //load single-page/profile template
    add_filter('single_template', array( 'Lab_Directory', 'load_profile_template' ) );

    add_action( 'restrict_manage_posts', array( 'Lab_Directory', 'add_lab_directory_staff_categories_admin_filter' ) );
    add_action( 'pre_get_posts', array( 'Lab_Directory', 'filter_admin_lab_directory_staff_by_category' ) );
	}

	static function create_post_types() {
		register_post_type( 'lab_directory_staff',
			array(
				'labels'     => array(
					'name' => __( 'Lab_dir_Staff' )
				),
				'supports'   => array(
					'title',
					'editor',
					'thumbnail'
				),
				'public'     => true,
				'menu_icon'  => 'dashicons-groups',
				'taxonomies' => array( 'lab_category')
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
			'featured_image' => __( 'Featured Image' ),
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
	}

	static function lab_directory_staff_meta_box_output( $post ) {

		wp_nonce_field( 'lab_directory_staff_meta_box_nonce_action', 'lab_directory_staff_meta_box_nonce' );

		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();

		?>

		<style type="text/css">
			label.lab_directory_staff-label {
				float: left;
				line-height: 27px;
				width: 130px;
			}
		</style>

		<?php foreach ( $lab_directory_staff_settings->get_lab_directory_staff_details_fields() as $field ): ?>
			<p>
				<label for="lab_directory_staff[<?php echo $field['slug'] ?>]" class="lab_directory_staff-label"><?php _e( $field['name'] ); ?>
					:</label>
				<?php if ( $field['type'] == 'text' ): ?>
					<input type="text" name="lab_directory_staff_meta[<?php echo $field['slug'] ?>]"
					       value="<?php echo get_post_meta( $post->ID, $field['slug'], true ); ?>"/>
				<?php elseif ( $field['type'] == 'textarea' ): ?>
					<textarea cols=40 rows=5
					          name="lab_directory_staff_meta[<?php echo $field['slug'] ?>]"><?php echo get_post_meta( $post->ID,
							$field['slug'], true ); ?></textarea>
				<?php endif; ?>
			</p>
		<?php endforeach; ?>

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

		if ( ! current_user_can( 'edit_post', get_the_id() ) ) {
			return;
		}

		foreach ( array_keys( $_POST['lab_directory_staff_meta'] ) as $meta_field_slug ) {
			update_post_meta( $post_id, $meta_field_slug, esc_attr( $_POST['lab_directory_staff_meta'][ $meta_field_slug ] ) );
		}
	}

	
	static function set_default_meta_fields_if_necessary() {
		$current_meta_fields = get_option( 'lab_directory_staff_meta_fields' );

		if ( $current_meta_fields == null || $current_meta_fields = '' ) {
					
				/* $default_meta_fields  list all predefined filed ussable in lab directory 
				 * 
				 * structure of this variable: 
				 * 
				 * name : the name of the field in english default language (translatable)
				 * type : type of this field (see $default_type)
				 * slug : the slug define the field, it cannot be changed
				 * ldap_field : optionnal ldap field used to import or sync
				 * multivalue : as defined in $default_multivalue
				 * hide_frontend : '1' if this field should not be displayed in frontend
				 * predefined : '1' if this field is predefined by the plugin (always here) 
				 */
				 
			$default_meta_fields = array(
				array(
					'type' => 'text',
					'slug' => 'firstname',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),
				array(
					'type' => 'text',
					'slug' => 'name',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),	
				array(
					'type' => 'text',
					'slug' => 'position',
					'ldap_field' => '',
					'multivalue' => 'mv',
					'hide_frontend' =>'0',				
					'activated' => '1',
				),
				array(
					'type' => 'text',
					'slug' => 'login',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'1',
					'activated' => '1',
				),
				array(
					'type' => 'text',
					'slug' => 'mails',
					'ldap_field' => '',
					'multivalue' => 'mv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),						
				array(
					'type' => 'text',
					'slug' => 'idhal',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),						
					array(
					'type' => 'url',
					'slug' => 'photo_url',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'0',
					'activated' => '1',
					),
				array(
					'type' => 'url',
					'slug' => 'webpage',
					'ldap_field' => '',
					'multivalue' => 'sv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),
				array(
					'type' => 'text',
					'slug' => 'function',
					'ldap_field' => '',
					'multivalue' => 'mv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),
				array(
					'type' => 'text',
					'slug' => 'title',
					'ldap_field' => '',
					'multivalue' => 'mv',
					'hide_frontend' =>'0',
					'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'phone',
						'ldap_field' => '',
						'multivalue' => 'mv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'fax',
						'ldap_field' => '',
						'multivalue' => 'mv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'office',
						'ldap_field' => '',
						'multivalue' => 'mv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'team',
						'ldap_field' => '',
						'multivalue' => 'mv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'exit_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'1',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'hdr_subject',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'hdr_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'hdr_location',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'jury',
						'slug' => 'hdr_jury',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'hdr_resume',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'phd_start_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'phd_subject',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'phd_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'phd_location',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'jury',
						'slug' => 'phd_jury',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'phd_resume',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),						
				array(
						'type' => 'date',
						'slug' => 'post_doc_start_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),			
				array(
						'type' => 'date',
						'slug' => 'post_doc_end_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'post_doc_subject',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'internship_start_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'internship_end_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'internship_subject',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'internship_resume',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'studying_school',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'studying_level',
						'slug' => 'studying_level',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'invitation_start_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'invitation_end_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'invitation_goal',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'invited_position',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'invited_origin',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'cdd_start_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'date',
						'slug' => 'cdd_end_date',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'text',
						'slug' => 'cdd_goal',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),
				array(
						'type' => 'longtext',
						'slug' => 'cdd_position',
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
			 			'activated' => '1',
				),						
					
			);
			for ($i = 1; $i <= 10; $i++) {
				$default_meta_fields[] = array(
						'type' => 'text',
						'slug' => "custom_$i",
						'ldap_field' => '',
						'multivalue' => 'sv',
						'hide_frontend' =>'0',
						'activated' => '0',
				);
			}
				
			update_option( 'lab_directory_staff_meta_fields', $default_meta_fields );
		}
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
	
	public function get_lab_directory_meta_field_input_types() {

		// Define the default type and the input type to use for input
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
			'long_text' =>__( 'Long text', 'lab-directory'),
			'jury' =>__( 'Jury', 'lab-directory'),
		);
		return $default_type_texts; 
	}

	public function get_lab_directory_multivalues() {
		
		// Define the list of option related to single and multivalue of fields
		$default_multivalue = array(
			// TODO how to add __() to this?
			'SV' => 'Single valued (only take first value if LDAP fields si multivalued)' ,
			'MV' => 'Multiple valued (take all values if LDAP fields has multiple values)' ,
			','  => 'Comma separated list' ,
			';'  => 'Semicolumn separated list' ,
			'|'  => ' | separated values' ,
			'CR' => 'Carriage return separated values' ,
		);
		return $default_multivalue;
	}
	
	static function get_lab_directory_default_meta_fields_name() {
		
		// Transation of meta_fields are save here to be reloaded (refreshed) each time without saving in Database
		$default_meta_fields_name = array(
				'firstname' => __( 'Firstname', 'lab-directory'),
				'name' => __( 'Name', 'lab-directory'),
				'position' => __( 'Position', 'lab-directory'),
				'login' => __( 'Login', 'lab-directory'),
				'mails' => __( 'Mails', 'lab-directory'),
				'idhal' => __( 'ID HAL', 'lab-directory'),
				'photo_url' => __( 'Photo URL', 'lab-directory'),
				'webpage' => __( 'Professionnal webpage', 'lab-directory'),
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
				'invitation_start_date' => __( 'Invitation start date', 'lab-directory'),
				'invitation_end_date' => __( 'Invitation end date', 'lab-directory'),
				'invitation_goal' => __( 'Invitation goal', 'lab-directory'),
				'invited_position' => __( 'Invited position', 'lab-directory'),
				'invited_origin' => __( 'Invited origin', 'lab-directory'),
				'cdd_start_date' => __( 'Fixed-term contract start date', 'lab-directory'),
				'cdd_end_date' => __( 'Fixed-term contract end date', 'lab-directory'),
				'cdd_goal' => __( 'Fixed-term contract goal', 'lab-directory'),
				'cdd_position' => __( 'Fixed-term contract position', 'lab-directory'),
				'custom_1' => __( 'custom_fields_1', 'lab-directory'),
				'custom_2' => __( 'custom_fields_2', 'lab-directory'),
				'custom_3' => __( 'custom_fields_3', 'lab-directory'),
				'custom_4' => __( 'custom_fields_4', 'lab-directory'),
				'custom_5' => __( 'custom_fields_5', 'lab-directory'),
				'custom_6' => __( 'custom_fields_6', 'lab-directory'),
				'custom_7' => __( 'custom_fields_7', 'lab-directory'),
				'custom_8' => __( 'custom_fields_8', 'lab-directory'),
				'custom_9' => __( 'custom_fields_9', 'lab-directory'),
				'custom_10' => __( 'custom_fields_10', 'lab-directory'),
		);
		return $default_meta_fields_name;
	}

} 