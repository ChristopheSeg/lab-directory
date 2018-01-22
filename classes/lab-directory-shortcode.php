<?php

class Lab_Directory_Shortcode {

    public static $lab_directory_staff_query;
    public static $hdr_loop;
	static function register_shortcode() {

        //Main shortcode to initiate plugin
        add_shortcode( 'lab-directory', array( 'Lab_Directory_Shortcode', 'shortcode' ) );

        //Shortcode to initiate Lab Directory loops
        add_shortcode( 'lab_directory_staff_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_staff_loop_shortcode' ) );
        add_shortcode( 'lab_directory_single_staff_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_single_staff_loop_shortcode' ) );
        add_shortcode( 'lab_directory_hdr_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_hdr_loop_shortcode' ) );
        add_shortcode( 'lab_directory_phd_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_phd_loop_shortcode' ) );
        
        //List of other shortcode tags (they should use the ld_ suffix)
        $other_shortcodes = array(
            'ld_name_header',
        	'ld_name_firstname',
        	'ld_position',
            'ld_photo',
            'ld_bio_paragraph',
            'ld_profile_link',
            'ld_category', 
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

    static function ld_meta_shortcode( $atts, $content = NULL, $tag) {
        $meta_key             = substr($tag,3);
        $meta_value           = get_post_meta( get_the_ID(), $meta_key, true );
        
        if($meta_value) {
            return $meta_value;
        } else {
            return ''; // Print nothing and remove tag if no value
        }

    }

    static function lab_directory_single_staff_loop_shortcode( $atts, $content = NULL ) {
 
    global $post; 
    $output = "";
   	
   	// have_posts() must be called twice to work!!
   	// rewind_post() because have_posts() has already been called in the main (single.php) template
   	rewind_posts(); 
        if ( have_posts() ) {
        	// do not loop postshere it's always a single staff
            the_post();
            $output .= do_shortcode($content);
        }
        return $output;
    }
    
    static function lab_directory_staff_loop_shortcode( $atts, $content = NULL ) {

        $query = Lab_Directory_Shortcode::lab_directory_staff_query($atts);
        $output = "";

        if ( $query->have_posts() ) {

            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= do_shortcode($content);

            }
        }
        wp_reset_query();
        
        return $output;
    }

    static function lab_directory_hdr_loop_shortcode( $atts, $content = NULL ) {
        $query = Lab_Directory_Shortcode::lab_directory_hdr_query($atts);
        $output = "";

        if ( $query->have_posts() ) {

            while ( $query->have_posts() ) {
                $query->the_post();
                $output .= do_shortcode($content);

            }
        }
        wp_reset_query();
        
        return $output;
    }
 
    static function lab_directory_phd_loop_shortcode( $atts, $content = NULL ) {
    	$query = Lab_Directory_Shortcode::lab_directory_phd_query($atts);
    	$output = "";
    
    	if ( $query->have_posts() ) {
    
    		while ( $query->have_posts() ) {
    			$query->the_post();
    			$output .= do_shortcode($content);
    
    		}
    	}
    	wp_reset_query();
    
    	return $output;
    }
    static function ld_name_shortcode(){
        return get_post_meta( get_the_ID(), 'name', true );
    }

    static function ld_name_firstname_shortcode(){
        return get_post_meta( get_the_ID(), 'name', true ) . ' ' . get_post_meta( get_the_ID(), 'firstname', true );
    }

    static function ld_name_header_shortcode(){
        return "<h3>" . self::ld_name_shortcode() . "</h3>";
    }

    static function ld_photo_url_shortcode(){
        if ( has_post_thumbnail() ) {
            $attachment_array = wp_get_attachment_image_src( get_post_thumbnail_id() );
            return $attachment_array[0];   
        } else {
            return '';
        }
    }

    static function ld_widget_hdr_link_shortcode($atts){
    	
    	$format = isset($atts['format_date']) ? $atts['format_date']: 'd/m/Y';
    	$text = __('HDR', 'lab-directory') . ' ' .
    		date ($format, strtotime(get_post_meta( get_the_ID(), 'hdr_date', true ))) .
    		' : ' . self::ld_name_firstname_shortcode();
    	$output = self::ld_profile_link_shortcode(array('phd' => true, 'inner_text' => $text));
        return $output;
    	
    	$output = 
            self::ld_profile_link_shortcode(array('hdr' => true)) .
         	'">' . __('HDR', 'lab-directory') . ' ' .
        	date ($format, strtotime(get_post_meta( get_the_ID(), 'hdr_date', true ))) . 
        	' : ' . 
        	self::ld_name_firstname_shortcode() .
        	'</a>'; 
        return $output; 
    }
    static function ld_widget_phd_link_shortcode($atts){
    	 
    	$format = isset($atts['format_date']) ? $atts['format_date']: 'd/m/Y';
    	$text = __('PHD', 'lab-directory') . ' ' .
    		date ($format, strtotime(get_post_meta( get_the_ID(), 'phd_date', true ))) .
    		' : ' . self::ld_name_firstname_shortcode();
    	$output = self::ld_profile_link_shortcode(array('phd' => true, 'inner_text' => $text));
        return $output;
    }    
    static function ld_photo_shortcode($atts){
    	$atts = shortcode_atts( array(
            'replace_empty'     => false,
        ), $atts);
        $photo_url = self::ld_photo_url_shortcode();
        if(!empty($photo_url)){
            return '<img src="' . $photo_url . '" />';
        } else {
        	if ($atts['replace_empty']) {
            return '<img src="/wp-content/plugins/lab-directory/images/nobody.jpg" />';
        	} else {
        		return ""; 
        	}
        }
    }

    static function ld_bio_shortcode( $atts, $content = NULL, $tag){
        $bio = get_post_meta( get_the_ID(), $tag, true );
    	
        /* 
         * old code used the_content, no filter needed
         * $bio = get_the_content();
         * $bio = apply_filters( 'the_content', $bio );
         * $bio = str_replace( ']]>', ']]&gt;', $bio );
         * 
         */
        return $bio;
    }

    static function ld_bio_paragraph_shortcode( $atts, $content = NULL, $tag){
        return "<p>" . self::ld_bio_shortcode( $atts, $content , $tag) . "</p>";
    }

    static function ld_profile_link_shortcode($atts, $content = NULL){

        $atts = shortcode_atts( array(
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
            return "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . do_shortcode($content) . "</a>";
        } else {
            return "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . $atts['inner_text'] . "</a>";
        }
    }

   static function ld_category_shortcode($atts){
        $atts = shortcode_atts( array(
            'all' => false,
        ), $atts);
        $lab_directory_staff_categories = wp_get_post_terms( get_the_ID(), 'lab_category' );
    	$all_lab_directory_staff_categories = "";

        if ( ! is_a($lab_directory_staff_categories, 'WP_error')) {
            $lab_category = $lab_directory_staff_categories[0]->name;
            foreach ( $lab_directory_staff_categories as $category ) {
                $all_lab_directory_staff_categories .= $category->name . ", ";
            }
            $all_lab_directory_staff_categories = substr( $all_lab_directory_staff_categories, 0, strlen( $all_lab_directory_staff_categories ) - 2 );
        } else {
            $lab_category = "";
        }

        if( $atts['all'] === "true" || $atts['all'] === true ) {
            return $all_lab_directory_staff_categories;
        } else {
            return $lab_category;
        }

    }
    
    static function ld_categories_nav_shortcode($atts){
        
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

	 
	 static function shortcode( $params ) {

    	global $wp_query;
    	$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
    	 
       	if (  isset($wp_query->query_vars['hdr']) ) {
       		$template = isset($params['template'])? $params['template'] : 'single_staff_hdr';
       	}
       	elseif (  isset($wp_query->query_vars['phd']) ) {
       		$template = isset($params['template'])? $params['template'] : 'single_staff_phd';
       	} else {
       		$template = isset($params['template'])? $params['template'] : 'single_staff'; 
       	}
       	
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
		
		//Query to retrieve defense list filter with loop attributes
	
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
		if (isset($params['period'] ) ) {
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
	
		//Query to retrieve defense list filter with loop attributes
	
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
		if (isset($params['period'] ) ) {
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

    static function retrieve_template_html($slug) {
        // $slug => 'File Name'
	    global $wp_query;
	    
	    	$template_slugs = array(
            'staff_grid' => 'ld_staff_grid.php',
            'staff_list' => 'ld_staff_list.php',
        	'staff_trombi' => 'ld_staff_trombi.php',
        	'defense_list' => 'ld_defense_list.php',
        	'single_staff' => 'ld_single_staff.php',
       		'single_staff_hdr' => 'ld_single_staff_hdr.php',
       		'single_staff_phd' => 'ld_single_staff_phd.php',
	    	);

        // Check if template slug exists 
        $cur_template = isset($template_slugs[$slug]) ? $template_slugs[$slug] : false;
        
        // Check if template is overrided 
		$cur_template = self::ld_locate_template($cur_template); 

        if ($cur_template) {
            $template_contents = file_get_contents( $cur_template);
            return do_shortcode($template_contents);
        } else {
            $lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
            $output         = "";
            $template       = $lab_directory_staff_settings->get_custom_lab_directory_staff_template_for_slug( $slug );
            $template_html  = html_entity_decode(stripslashes( $template['html'] ));
            $template_css   = html_entity_decode(stripslashes( $template['css'] ));

            //Trick wordpress to not recognize html attributes,
            //before running do_shortcode()
            $template_html  = self::custom_pre_shortcode_escaping($template_html);

            $output .= "<style type='text/css'>$template_css</style>";
            $output .= do_shortcode($template_html);

            //Now that we've run all the shortcodes, lets un-trick wordpress
            $output = self::custom_pre_shortcode_decoding($output);

            return $output;
        }
    }

    /**
     * Function forked from Wordpress core 
     * Retrieve the name of the highest priority template file ($template_names) that exists in: 
     *  0- first look for template saved in settings
     *  1- wp-content/themes/lab-directory/ (highest priority )
     *  2- wp-content/themes/yourtheme/ (create a child theme before)
     *  3- wp-content/plugins/lab-directory/templates/ (default location)
     *
     *
     * @param string$template_names Template file(s) to search for, in order.
     * @return string The template filename if one is located.
     */
    static function ld_locate_template($template_name, $load = false, $require_once = true ) {
    	
    	// TODO First check if a template has been set in settings
    	if ( file_exists( WP_CONTENT_DIR . '/themes/lab-directory/' . $template_name ) )
    	{
    		return WP_CONTENT_DIR . '/themes/lab-directory/' . $template_name;
    	} 
    	elseif ( file_exists(get_template_directory() . '/' . $template_name) ) 
    	{
    		return get_template_directory() . '/' . $template_name;
    	} 
    	elseif ( file_exists(LAB_DIRECTORY_TEMPLATES . '/' . $template_name)) 
    	{
    		return LAB_DIRECTORY_TEMPLATES . '/' . $template_name;
    	}   		
    	return '';
    }
    
    
}


