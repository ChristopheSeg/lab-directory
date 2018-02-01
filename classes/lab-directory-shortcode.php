<?php

class Lab_Directory_Shortcode {

    public static $lab_directory_staff_query;
    public static $current_template;
    static $lab_directory_main_shortcode_params = array(); 
    public static $hdr_loop = false; 
	static function register_shortcode() {

        //Main shortcode to initiate plugin
        add_shortcode( 'lab-directory', array( 'Lab_Directory_Shortcode', 'lab_directory_main_shortcode' ) );

        //Shortcode to initiate Lab Directory loops
        add_shortcode( 'lab_directory_staff_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_staff_loop_shortcode' ) );
        add_shortcode( 'lab_directory_single_staff_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_single_staff_loop_shortcode' ) );
        add_shortcode( 'lab_directory_hdr_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_hdr_loop_shortcode' ) );
        add_shortcode( 'lab_directory_phd_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_phd_loop_shortcode' ) );
        
        //List of other shortcode tags (they should use the ld_ suffix)
        $other_shortcodes = array(
            'ld_name_header',
        	'ld_name_firstname',
        	'ld_firstname_name',
        	'ld_position',
            'ld_photo',
            'ld_bio_paragraph',
            'ld_profile_link',
            'ld_team', 
        	'ld_teams',
        	'ld_laboratory',
        	'ld_laboratories',
        	'ld_categories_nav',
        	'ld_widget_hdr_link', 
        	'ld_widget_phd_link', 
        );

        //Add shortcodes for all $predefined_shortcodes, link to function by
        //the name of {$code}_shortcode
        foreach($other_shortcodes as $code){
            add_shortcode( $code, array( 'Lab_Directory_Shortcode', $code . '_shortcode' ) );
        }

        // Add shortcodes for all metafields, link to function ld_{$code}_shortcode
        // Or default function ld_meta_shortcode
         if ( !empty( Lab_Directory::$staff_meta_fields) ) {
            foreach ( Lab_Directory::$staff_meta_fields as $field ) {
                $meta_key = 'ld_' . $field['slug'];
                $shortcode_function = $meta_key . '_shortcode';
                if (method_exists('Lab_Directory_Shortcode',$shortcode_function)) {
                	add_shortcode( $meta_key, array( 'Lab_Directory_Shortcode', $shortcode_function ) ); 
                } else {
                	add_shortcode( $meta_key, array( 'Lab_Directory_Shortcode', 'ld_meta_shortcode' ) );
                }
            }
        }

	}

    /*** Begin shortcode functions ***/
	
	// TODO ALL Shortcode: test display frontend, activated (frontend...), MV, tooltips

    /* 
     * ld_meta_shortcode function is the default shortcode function 
     * used when no specific function has been written for a shortcode
     */
    static function ld_meta_shortcode( $atts, $content = NULL, $tag = '' ) {
	    $atts = shortcode_atts( array(
	    	'add_div'     => true,
	    ), $atts);
	    // remove 'ld_' prefix to get meta_key 
	    $meta_key             = substr($tag,3);
        $meta_value           = get_post_meta( get_the_ID(), $meta_key, true );
        
        if($meta_value) {
            $output = $meta_value;
        } else {
            $output = ''; // Print nothing and remove tag if no value
        }
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }
    
    static function lab_directory_single_staff_loop_shortcode( $atts, $content = NULL ) {
	    global $post; 
	    $output = "";
	    // Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
	    $atts = shortcode_atts( self::$lab_directory_main_shortcode_params, $atts);
	
	   	// If no id is given $post already contains the staff profile
	    // If an id is given (example [lab-directory id=766]) $post do not contains the staff profile 
	   	if (isset($atts['id'])) {
	   		// Query single post from ID
	   		query_posts(array(
			    'p' => $atts['id'],
			    'post_type' => 'lab_directory_staff'));
	   	}
	    
	   	// add template CSS part if atts['css'] is given
	   	if (isset ($atts['css']) ) { 
	   		$template= self::ld_load_template($atts['css'], true );
	   		// Save template to add to div in loop 
	   		self::$current_template = $atts['css'];
	   		if ($template['css']) {
	   			
	   			$output .= '<style type="text/css">' . $template['css'] .'</style>';
	   		}
	   	}
	   	
	    // Rewind_post() because have_posts() has already been called in the main (single.php) template
		rewind_posts(); 
        if ( have_posts() ) {
        	// do not loop posts here it's always a single staff
            the_post();
    		$content = str_replace('<br />', '', $content);
    		$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
        }
        return $output;
    }
    
    static function lab_directory_staff_loop_shortcode( $atts, $content = NULL ) {
 
    	$atts = shortcode_atts( array(
    		'staff_filter'     => false,
    	), $atts);
        $query = Lab_Directory_Shortcode::lab_directory_staff_query($atts);
        $output = "";
        if ( ($atts['staff_filter'] === true ) OR ($atts['staff_filter'] == 'true' ) ) {
        	// Add a text filter on this list 
        	$output .= ' 
<style type="text/css">
#filtre_dynamique_saisie {
    float: right;
    width: 160px;

}
.text_surligne {
  color: #000;
  background-color: #fff59b;
}
  label, input {
   float: right;
   width: auto;
   height: auto;
   padding: 3px;
   margin: 2px 5px;
   font-size: 1em;
   line-height: 1em;
}
</style>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/penagwinhighlight.js"></script>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/text_filter.js"></script>
				
<form id="filtre_dynamique">
  <input type="reset" id="filtre_dynamique_effacer" value="'. __('Clear filter','lab-directory'). '" />
  <input type="text" id="filtre_dynamique_saisie" />
  <label for="filtre_dynamique_saisie">'. __('Filter by name  or firstname','lab-directory') . '</label>
</form> 
<div class="clearfix"></div>
        		'; 
        	
    }

        if ( $query->have_posts() ) {
        	// add template CSS part if atts['css'] is given
        	if (isset ($atts['css']) ) {
        		$template= self::ld_load_template($atts['css'], true );
        		// Save template to add to div in loop 
        		self::$current_template = $atts['css'];
        		if ($template['css']) {
        			$output .= '<style type="text/css">' . $template['css'] .'</style>';
        		}
        	}

            while ( $query->have_posts() ) {
                $query->the_post();
    			$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
                
            }
        }
        wp_reset_query();
        // Add a wrapper for the text filter 
        if ( ($atts['staff_filter'] === true ) OR ($atts['staff_filter'] == 'true' ) ) {
        	$output = '<div id="lab-directory-wrapper">' . $output . '</div>';
        }
        return $output;
    }

    static function lab_directory_hdr_loop_shortcode( $atts, $content = NULL ) {
        
$query = Lab_Directory_Shortcode::lab_directory_hdr_query($atts);
        $output = "";

	   	if ( $query->have_posts() ) {

	   		// add template CSS part if atts['css'] is given
	   		if (isset ($atts['css']) ) {
	   			$template= self::ld_load_template($atts['css'], true );
	   			// Save template to add to div in loop 
	   			self::$current_template = $atts['css'];
	   			if ($template['css']) {
	   				$output .= '<style type="text/css">' . $template['css'] .'</style>';
	   			}
	   		}
	   		
            while ( $query->have_posts() ) {
                $query->the_post();
    			$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
                
            }
        }
        wp_reset_query();
        
        return $output;
    }
 
    static function lab_directory_phd_loop_shortcode( $atts, $content = NULL ) {
    	$query = Lab_Directory_Shortcode::lab_directory_phd_query($atts);
    	$output = "";
    
    	if ( $query->have_posts() ) {
    		// add template CSS part if atts['css'] is given
    		if (isset ($atts['css']) ) {
    			$template= self::ld_load_template($atts['css'], true );
    			// Save template to add to div in loop 
    			self::$current_template = $atts['css'];
    			if ($template['css']) {
    				$output .= '<style type="text/css">' . $template['css'] .'</style>';
    			}
    		}
    
    		while ( $query->have_posts() ) {
    			$query->the_post();
    			$content = str_replace('<br />', '', $content);
    			$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
    		}
    	}
    	wp_reset_query();
    
    	return $output;
    }

    static function ld_firstname_name_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    	), $atts);
    	$output = get_post_meta( get_the_ID(), 'firstname', true ) . ' ' . get_post_meta( get_the_ID(), 'name', true );
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }
    static function ld_name_firstname_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    	), $atts);
    	$output = get_post_meta( get_the_ID(), 'name', true ) . ' ' . get_post_meta( get_the_ID(), 'firstname', true );
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }

    static function ld_photo_url_shortcode($atts, $content = NULL, $tag = '' ){
     	$atts = shortcode_atts( array(
    		'add_div'     => true,
    	), $atts);
    	if ( has_post_thumbnail() ) {
            $attachment_array = wp_get_attachment_image_src( get_post_thumbnail_id() );
            $output = $attachment_array[0];   
        } else {
            $output = '';
        }
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;        
    }

    static function ld_widget_hdr_link_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    	), $atts);    	
    	$format = isset($atts['format_date']) ? $atts['format_date']: 'd/m/Y';
    	$text = __('HDR', 'lab-directory') . ' ' .
    		date ($format, strtotime(get_post_meta( get_the_ID(), 'hdr_date', true ))) .
    		' : ' . self::ld_name_firstname_shortcode(array('add_div' => false));
    	$output = self::ld_profile_link_shortcode(
    		array('add_div' => false, 'phd' => true, 'inner_text' => $text));
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
    		$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
    	}
        return $output;
	}
    
    static function ld_widget_phd_link_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    	), $atts);
    	$format = isset($atts['format_date']) ? $atts['format_date']: 'd/m/Y';
    	$text = __('PHD', 'lab-directory') . ' ' .
    		date ($format, strtotime(get_post_meta( get_the_ID(), 'phd_date', true ))) .
    		' : ' . self::ld_name_firstname_shortcode(array('add_div' => false)) . '</a>';
    	$output = self::ld_profile_link_shortcode(
    		array('add_div' => false, 'phd' => true, 'inner_text' => $text));
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
    		$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
    	}
    	return $output;
    }
    
    static function ld_photo_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
            'add_div'     => true,
    		'replace_empty'     => false,
        ), $atts);
        $photo_url = self::ld_photo_url_shortcode(array('add_div' => false) );
        $output = ''; 
        if(!empty($photo_url)){
            $output = '<img src="' . $photo_url . '" />';
        } elseif ($atts['replace_empty']) {
            $output = '<img src="/wp-content/plugins/lab-directory/images/nobody.jpg" />';
        }
        
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
        
    }

    static function ld_bio_shortcode( $atts, $content = NULL, $tag = '' ){
        $output = get_post_meta( get_the_ID(), $tag, true );
    	
        /* 
         * old code used the_content, no filter needed
         * $bio = get_the_content();
         * $bio = apply_filters( 'the_content', $bio );
         * $bio = str_replace( ']]>', ']]&gt;', $bio );
         * 
         */
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }

    static function ld_profile_link_shortcode($atts, $content = NULL, $tag = '' ){

        $atts = shortcode_atts( array(
            'add_div'     => true,
        	'target'     => "_self",
            'inner_text' => "Profile", 
        	'hdr' => false, 
        	'phd' => false,	 
        ), $atts);
        $profile_link = get_permalink( get_the_ID() );
        
        if ( $atts['hdr']) {
        	$profile_link .= 'hdr';
        }
        if ( $atts['phd']) {
        	$profile_link .= 'phd';
        }
        
        
        if(!empty($content)) {
            $output = "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . do_shortcode($content) . '</a>';
        } else {
            $output = "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . $atts['inner_text'] . '</a>';
        }
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }

	static function ld_teams_shortcode($atts, $content = NULL, $tag = '' ){
		// TODOTODO not working !!
		$ouput = ' ld_teams_shortcode NO YET IMPLEMENTED !!'; 
		return $output;
   	
	}
	
	static function ld_team_shortcode($atts, $content = NULL, $tag = '' ){

        $atts = shortcode_atts( array(
            'add_div' => true,
        ), $atts);
        
        $_categories = get_the_terms(get_the_ID(), 'ld_taxonomy_team');   	 
    	
        $categories= array(); 
    	
        if ($staff_categories ) {
	        foreach ( $staff_categories as $category ) {
	    		$categories[] = $category->name;
	        }
    	}
        
        $output = implode(', ',$categories); 
        
        if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
        	$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
        }
        return $output;
    }

    static function ld_laboratories_shortcode($atts, $content = NULL, $tag = '' ){
    	// TODOTODO not working !!
    
    	$ouput = ' ld_laboratories_shortcode NO YET IMPLEMENTED !!';
    	return $output;
    
    }
    static function ld_laboratory_shortcode($atts, $content = NULL, $tag = '' ){
    
    	$atts = shortcode_atts( array(
    		'add_div' => true,
    	), $atts);
    
    	$_categories = get_the_terms(get_the_ID(), 'ld_taxonomy_laboratory');
    	 
    	$categories= array();
    	 
    	if ($staff_categories ) {
    		foreach ( $staff_categories as $category ) {
    			$categories[] = $category->name;
    		}
    	}
    
    	$output = implode(', ',$categories);
    
    	if ( $output AND ( $atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
    		$output = '<div class=" '. $tag . ' ld_field">' . $output . '</div>';
    	}
    	return $output;
    }
    
    static function ld_categories_nav_shortcode($atts, $content = NULL, $tag = '' ){
        
        $lab_directory_staff_categories = get_terms( array(
		    'taxonomy' => 'lab_category',
		    'hide_empty' => false,
		) );
        
        if ( count( $lab_directory_staff_categories ) < 2 ) {
        	return ''; 
        }
       
        $current_url = explode('?', "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $all_lab_directory_staff_categories = '';
        $all_lab_directory_staff_categories = '<a href="' . $current_url[0] . '" >' . __(All) . '</a>, ';

		$all_lab_directory_staff_categories = '<a href="' . $current_url[0] . '" >' . __(All) . '</a>, ';
		foreach ( $lab_directory_staff_categories as $category ) {
			$all_lab_directory_staff_categories .= '<a href="' . $current_url[0] . '?cat=' . $category->term_id . '" >' .$category->name . '</a>, ';
		}
       return $all_lab_directory_staff_categories;
    }

	 
	 /* 
	  * [lab-directory] main shortcode
	  */
	  static function lab_directory_main_shortcode( $params ) {
    	global $wp_query;
    	// Save params for potential reuse in other staff_loops 
    	self::$lab_directory_main_shortcode_params = $params; 
    	
    	$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();

		if (isset($params['id'])) {
       		// Search for a single staff profile 
       		// TODOTODO revoir query si $params['id']
   			$template = isset($params['template'])? $params['template'] : 'single_staff'; 
       	} elseif (  isset($wp_query->query_vars['hdr']) ) {
       		$template = isset($params['template'])? $params['template'] : 'single_staff_hdr';
       	}
       	elseif (  isset($wp_query->query_vars['phd']) ) {
       		$template = isset($params['template'])? $params['template'] : 'single_staff_phd';
       	} else {
       		 {
       			$template = isset($params['template'])? $params['template'] : 'staff_grid';
       		}
       	}
       	// Save template name for use in loop's div
       	self::$current_template = $template;  
        return self::retrieve_template_html($template); 
	}

    /*** End shortcode functions ***/
	/*
	 * $paramscontains used attribute in loop
	 */
	
	static function lab_directory_staff_query( $params = null ) {
		global $wpdb;
		$default_params = array(
			'id'       => '',
			'cat'      => '',
			'cat_field' => 'ID',
			'cat_relation' => 'OR',
			'orderby'  => 'ID',
			'order'    => 'DESC',
			'meta_key' => '',
		);
		
		$params = shortcode_atts( $default_params, $params );
		
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID for your Lab Directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1
		);
	
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		}
		// ends single lab_directory_staff
	
		// If no cat nor id filter in loop
		if ( ( !isset( $params['cat'] ) || $params['cat'] == '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			// Try to add url parameter filter ($_GET) in $params filter
			if ($_GET['cat']) {
				$params['cat'] = $_GET['cat'];
			}
		}
	
		// check if we're returning a lab_directory_staff category
		if ( ( isset( $params['cat'] ) && $params['cat'] != '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			$cats_query = array();
	
			$cats = explode( ',', $params['cat'] );
	
			if (count($cats) > 1) {
				$cats_query['relation'] = $params['cat_relation'];
			}
	
			foreach ($cats as $cat) {
				$cats_query[] = array(
					'taxonomy' => 'lab_category',
					'terms'    => $cat,
					'field'    => $params['cat_field']
				);
			}
	
			$query_args['tax_query'] = $cats_query;
		}
	
		if ( isset( $params['orderby'] ) && $params['orderby'] != '' ) {
			$query_args['orderby'] = $params['orderby'];
		}
		if ( isset( $params['order'] ) && $params['order'] != '' ) {
			$query_args['order'] = $params['order'];
		}
		if ( isset( $params['meta_key'] ) && $params['meta_key'] != '' ) {
			$query_args['meta_key'] = $params['meta_key'];
		}
		
		$output = new WP_Query( $query_args );

		return $output;
	}
	
	static function lab_directory_hdr_query( $params = null ) {
		global $wpdb;

		//Query to retrieve defense list filtered with loop attributes
	
		$default_params = array(
			'id'       => '',
			'cat'      => '',
			'cat_field' => 'ID',
			'cat_relation' => 'OR',
			'period' => 'all',
		);
		
		$params = shortcode_atts( $default_params, $params );
		
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID for your Lab Directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1
		);
	
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		}
		// ends single lab_directory_staff
	
		// If no cat nor id filter in loop
		if ( ( !isset( $params['cat'] ) || $params['cat'] == '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			// Try to add url parameter filter ($_GET) in $params filter
			if ($_GET['cat']) {
				$params['cat'] = $_GET['cat'];
			}
		}
	
		// check if we're returning a lab_directory_staff category
		if ( ( isset( $params['cat'] ) && $params['cat'] != '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			$cats_query = array();
	
			$cats = explode( ',', $params['cat'] );
	
			if (count($cats) > 1) {
				$cats_query['relation'] = $params['cat_relation'];
			}
	
			foreach ($cats as $cat) {
				$cats_query[] = array(
					'taxonomy' => 'lab_category',
					'terms'    => $cat,
					'field'    => $params['cat_field']
				);
			}
	
			$query_args['tax_query'] = $cats_query;
		}
	
		$query_args['orderby'] = 'meta_value';
		$query_args['order'] = 'DESC';
		$query_args['meta_key'] = 'hdr_date';
		
		// restrict Query for defense if necessary
		$query_args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'     => 'staff_statuss',
				'value'   => 'HDR',
				// Warning, WP>=4.8.3: LIKE do not works in meta_key search https://core.trac.wordpress.org/ticket/42746
				'compare' => 'REGEXP',
			),
		);
		
		// restrict to past or futur period
		if (isset($params['period'] ) AND ($params['period']!='all' )) {
			if ($params['period']== 'futur') {
				$query_args['meta_query'][] = array(
					'key'     => 'hdr_date',
					'value'   => date( "Y-m-d" ),
					'compare' => '>=',
				);
				$query_args['order'] = 'ASC';				
			} 
			elseif ($params['period']== 'past') {
				$query_args['meta_query'][] = array(
					'key'     => 'hdr_date',
					'value'   => date( "Y-m-d" ),
					'compare' => '<=',
				);
				$query_args['order'] = 'DESC';
			} 
			if ($params['period']!= 'futur') {
				$query_args['meta_query'][] = array(
					'key'     => 'hdr_date',
					'value'   => '',
					'compare' => '!=',
				);
			}
		}
		
		$output = new WP_Query( $query_args );
		return $output;
	}
	
	static function lab_directory_phd_query( $params = null ) {
		global $wpdb;
	
		//Query to retrieve defense list filtered with loop attributes
		$default_params = array(
			'id'       => '',
			'cat'      => '',
			'cat_field' => 'ID',
			'cat_relation' => 'OR',
			'period' => 'all',			
		);
		
		$params = shortcode_atts( $default_params, $params );
		
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID for your Lab Directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1
		);
	
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		}
		// ends single lab_directory_staff
	
		// If no cat nor id filter in loop
		if ( ( !isset( $params['cat'] ) || $params['cat'] == '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			// Try to add url parameter filter ($_GET) in $params filter
			if ($_GET['cat']) {
				$params['cat'] = $_GET['cat'];
			}
		}
	
		// check if we're returning a lab_directory_staff category
		if ( ( isset( $params['cat'] ) && $params['cat'] != '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			$cats_query = array();
	
			$cats = explode( ',', $params['cat'] );
	
			if (count($cats) > 1) {
				$cats_query['relation'] = $params['cat_relation'];
			}
	
			foreach ($cats as $cat) {
				$cats_query[] = array(
					'taxonomy' => 'lab_category',
					'terms'    => $cat,
					'field'    => $params['cat_field']
				);
			}
	
			$query_args['tax_query'] = $cats_query;
		}
	
		$query_args['orderby'] = 'meta_value';
		$query_args['order'] = 'DESC';
		$query_args['meta_key'] = 'phd_date';	
		
		// restrict Query for defense if necessary
		$query_args['meta_query'] = array(
			'relation' => 'AND',
			array(
				'key'     => 'staff_statuss',
				'value'   => 'doctorate',
				// Warning, WP>=4.8.3: LIKE do not works in meta_key search https://core.trac.wordpress.org/ticket/42746
				'compare' => 'REGEXP',
			),
		);
	
			// restrict to past or futur period
		if (isset($params['period'] ) AND ($params['period']!='all' )) {
		if ($params['period']== 'futur') {
				$query_args['meta_query'][] = array(
					'key'     => 'phd_date',
					'value'   => date( "Y-m-d" ),
					'compare' => '>=',
				);
				$query_args['order'] = 'ASC';				
			} 
			elseif ($params['period']== 'past') {
				$query_args['meta_query'][] = array(
					'key'     => 'phd_date',
					'value'   => date( "Y-m-d" ),
					'compare' => '<=',
				);
				$query_args['order'] = 'DESC';
			} 
			if ($params['period']!= 'futur') {
				$query_args['meta_query'][] = array(
					'key'     => 'phd_date',
					'value'   => '',
					'compare' => '!=',
				);
			}
		}
	
		$output = new WP_Query( $query_args );
		return $output;
	}
	
    static function custom_pre_shortcode_escaping($html) {

        //Regex pattern to match all shortcodes
        $pattern   = '/\[[\w\-\/]+[\w\s\"\'\=\-\*\$\@\!]*\]/';
        $replacers = array();

        //Match all shortcodes in template
        preg_match_all($pattern, $html, $matches);

        //Take each shortcode, run htmlentities() on it, and push them to $replacers
        foreach($matches as $shortcode) {
            $replacers[] = htmlentities($shortcode[0]);
        }

        //Replace each shortcode with the replacer
        $html = str_replace($matches, $replacers, $html);

        //Now that we've eliminated all quote marks from shortcodes, we can trick
        //Wordpress's do_shortcode() so that it doesn't recognize any shortcodes
        //as being in an html attribute, by replacing all remaining quotes with
        //a unique string surrounded by < & >
        $html = str_replace('"', '<|-dbl_quote-|>', $html);
        $html = str_replace("'", "<|-sgl_quote-|>", $html);

        //Now we've replaced all quotes outside of a shortcode, lets just decode
        //the shortcodes
        $html = html_entity_decode($html);

        return $html;

    }

    static function custom_pre_shortcode_decoding($html) {

        //Pretty much just undoing the 'encoding' we did above
        $html = str_replace('<|-dbl_quote-|>', '"', $html);
        $html = str_replace("<|-sgl_quote-|>", "'", $html);

        return $html;

    }

    static function retrieve_template_list() {
		return array(  	
		    	'staff_grid' => __('This template is used to display staff directory as a grid', 'lab-directory'),
		    	'staff_list' => __('This template is used to display staff directory as a list', 'lab-directory'),
		    	'staff_trombi' => __('This template is used to display staff directory as a photo gallery', 'lab-directory'),
		    	'defense_list' => __('This template is used to display a defenses list', 'lab-directory'),
		    	'single_staff' => __('This template is used to display a single staff profile', 'lab-directory'),
		    	'single_staff_hdr' => __('This template is used to display HDR defense information for a single staff', 'lab-directory'),
		    	'single_staff_phd' => __('This template is used to display PHD defense information for a single staff', 'lab-directory'),
    );
    }
    
    static function retrieve_template_html($slug) {
	    
	    // TODOTODO  si slug inexistant !! error!! 
        
        // Load template (HTML and CSS)
		$template = self::ld_load_template($slug);
		$output = '';
		if ($template['css']) {
			$output .= '<style type="text/css">' . $template['css'] .'</style>';
		}
		
		//TODO single ID ???
		$output .= '<div class="ld_' . $slug . '_loop" >' . do_shortcode($template['html']) . '</div>';
		return $output;
		
		/* TODO OLD CODE Search utility of the escaping utility 
		 * 
        if ($cur_template) {
            // TODOTODO add div 
            
        } else {
            $lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
            $output         = "";
            // TODO Fatal error: Call to undefined method Lab_Directory_Settings::get_custom_lab_directory_staff_template_for_slug() in /home/seguinot/Documents/www/wp_ircica/wp-content/plugins/lab-directory/classes/lab-directory-shortcode.php on line 658
            $template       = $lab_directory_staff_settings->get_custom_lab_directory_staff_template_for_slug( $slug );
            $template_html  = html_entity_decode(stripslashes( $template['html'] ));
            $template_css   = html_entity_decode(stripslashes( $template['css'] ));

            //Trick wordpress to not recognize html attributes,
            //before running do_shortcode()
            $template_html  = self::custom_pre_shortcode_escaping($template_html);

            $output .= "<style type='text/css'>$template_css</style>";
            // TODOTODO add div 
            $output .= do_shortcode($template_html);

            //Now that we've run all the shortcodes, lets un-trick wordpress
            $output = self::custom_pre_shortcode_decoding($output);

            return $output;
        }
        */ 
    }

    /**
     * Retrieve the highest priority template file ($template_names.css /.php) that exists in: 
     * for php (Lab-Directory loop part)
     * 
     *      first search for a template file saved in settings (highest priority )
     *      wp-content/themes/lab-directory/ (preferred folder for overriding templates)
     *      wp-content/themes/yourtheme/ (this folder exists if you created a child theme called "yourtheme")
     *      wp-content/plugins/lab-directory/templates/ (default template if no other file found and template in settings is empty)
     *
     * For the css stylesheet, sequentially add if they exists: 
     * 	    wp-content/plugins/lab-directory/templates/ (this default stylesheet is always loaded first, lowest priority)
     *      wp-content/themes/yourtheme/ (this folder exists if you created a child theme called "yourtheme")
     *      wp-content/themes/lab-directory/ (preferred folder for overriding CSS stylesheet)
     *      look for template saved in settings (last added, highest priority )
     *      
     * @param string $template_names Template file(s) to search for, in order.
     * @return string The template filename if one is located.
     */

    static function ld_load_template($template_name, $only_css=false) {
    	
     	$template =array(
    		'html' =>'',
    		'css' => '',
    	);
    	if ($only_css==false) {
    		// Search for the HTML (Loop) template part
	    	$template_file = 'ld_' . $template_name . '.php';
	    	
	    	//TODO add settings
	    	if ($template['html'] = get_option( $template_file)) {
	    		// Nothing to do
	    	}
	    	elseif ( file_exists( WP_CONTENT_DIR . '/themes/lab-directory/' . $template_file) )
	    	{
	    		$template['html'] = file_get_contents( WP_CONTENT_DIR . '/themes/lab-directory/' . $template_file);
	    	} 
	    	elseif ( file_exists(get_template_directory() . '/' . $template_file) ) 
	    	{
	    		$template['html'] = file_get_contents( get_template_directory() . '/' . $template_file);
	    	} 
	    	elseif ( file_exists(LAB_DIRECTORY_TEMPLATES . '/' . $template_file)) 
	    	{
	    		$template['html'] = file_get_contents( LAB_DIRECTORY_TEMPLATES . '/' . $template_file);
	    	}
    	}
    	
    	// Search for CSS
        $css_file = 'ld_' . $template_name . '.css';
 
    	if ( file_exists(LAB_DIRECTORY_TEMPLATES . '/' . $css_file)) 
    	{
    		$template['css'] .= file_get_contents( LAB_DIRECTORY_TEMPLATES . '/' . $css_file);
    	}
    	if ( file_exists(get_template_directory() . '/' . $css_file) ) 
    	{
    		$template['css'] .= file_get_contents( get_template_directory() . '/' . $css_file);
    	} 
    	if ( file_exists( WP_CONTENT_DIR . '/themes/lab-directory/' . $css_file) )
    	{
    		$template['css'] .= file_get_contents( WP_CONTENT_DIR . '/themes/lab-directory/' . $css_file);
    	}
    	$template['css'] .= get_option( $css_file); 
    	return $template;
    	
    }
    
    
}


