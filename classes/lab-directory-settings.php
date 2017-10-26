<?php

class Lab_Directory_Settings {
	public static function shared_instance() {
		static $shared_instance = null;
		if ( $shared_instance === null ) {
			$shared_instance = new static();
		}

		return $shared_instance;
	}

	public static function setup_defaults() {
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();

		$current_template_slug = $lab_directory_staff_settings->get_current_default_lab_directory_staff_template();
		if ( $current_template_slug == '' || $current_template_slug == null ) {

			$lab_directory_staff_settings->update_default_lab_directory_staff_template_slug( 'list' );

		} else if ( $current_template_slug == 'custom' || get_option( 'lab_directory_html_template', '' ) != '' ) {

			$templates_array   = array();
			$templates_array[] = array(
				'html' => get_option( 'lab_directory_html_template' ),
				'css'  => get_option( 'lab_directory_css_template' )
			);
			$lab_directory_staff_settings->update_custom_lab_directory_staff_templates( $templates_array );
			$lab_directory_staff_settings->update_default_lab_directory_staff_template_slug( 'custom_1' );

			delete_option( 'lab_directory_html_template' );
			delete_option( 'lab_directory_css_template' );

		}
	}

	#
	# setters
	#

	public function update_default_lab_directory_staff_template_slug( $slug = 'list' ) {
		update_option( 'lab_directory_template_slug', $slug );
	}

	public function update_custom_lab_directory_staff_templates( $templates = array() ) {
		$updated_templates_array = array();
		$index                   = 1;
		foreach ( $templates as $template ) {
			if ( $template['html'] != '' || $template['css'] != '' ) {
                $template['html']          = htmlentities($template['html'], ENT_QUOTES);
                $template['css']           = htmlentities($template['css'], ENT_QUOTES);
				$template['index']         = $index;
				$template['slug']          = 'custom_' . $index;
				$updated_templates_array[] = $template;
				$index ++;
			}
		}
		update_option( 'lab_directory_custom_templates', $updated_templates_array );
	}

	public function update_custom_lab_directory_staff_meta_fields()  {
		
		$slugs = $_POST['lab_directory_staff_meta_fields_slugs'];
		$types = $_POST['lab_directory_staff_meta_fields_types'];
		$activateds = $_POST['lab_directory_staff_meta_fields_activateds'];
		$orders = $_POST['lab_directory_staff_meta_fields_orders'];
	
		$index = 0;

		$meta_fields_array = array();
		foreach ( $slugs as $slug ) {
			$meta_fields_array[] = array(
					'slug' => $slug,
					'type' => $types[ $index ],
					'activated' => $activateds[ $index ],
					'order' => $orders[ $index ],
				);
			$index ++;
		}
		
		// sort by activated, then by order
		usort($meta_fields_array, __NAMESPACE__ . '\compare_order');
		
		update_option( 'lab_directory_staff_meta_fields', $meta_fields_array );
	}
	

	#
	# getters
	#

	
	public function get_current_default_lab_directory_staff_template() {
		$current_template = get_option( 'lab_directory_template_slug' );

		if ( $current_template == '' && get_option( 'lab_directory_html_template' ) != '' ) {
			update_option( 'lab_directory_template_slug', 'custom' );
			$current_template = 'custom';
		} else if ( $current_template == '' ) {
			update_option( 'lab_directory_template_slug', 'list' );
			$current_template = 'list';
		}

		return $current_template;
	}

	public function get_custom_lab_directory_staff_templates() {
		return get_option( 'lab_directory_custom_templates', array() );
	}

	public function get_custom_lab_directory_staff_template_for_slug( $slug = '' ) {
		$templates = $this->get_custom_lab_directory_staff_templates();
		foreach ( $templates as $template ) {
			if ( $template['slug'] == $slug ) {
				return $template;
			}
		}
	}

	public function get_lab_directory_staff_details_fields() {
		return get_option( 'lab_directory_staff_meta_fields', array() );
	}

	#
	# delete functions
	#

	public function delete_custom_template( $index = null ) {
		if ( $index != null ) {
			$custom_templates = $this->get_custom_lab_directory_staff_templates();
			$new_custom_templates == array();
			foreach ( $custom_templates as $template ) {
				if ( $template['index'] != $index ) {
					$new_custom_templates[] = $template;
				}
			}
			$this->update_custom_lab_directory_staff_templates( $new_custom_templates );
		}
	}
	
	 
}


function compare_order($a, $b)
{
	return 200* ((int)$b['activated'] - (int)$a['activated']) +
	((int)$a['order']-(int)$b['order']);
}

