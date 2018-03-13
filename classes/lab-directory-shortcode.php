<?php

class Lab_Directory_Shortcode {

    public static $lab_directory_staff_query;
    public static $current_template;
    
	static $lab_directory_main_shortcode_default_params = array(
			'id'       => '',
			'cat'      => '',
			'cat_field' => 'ID',
			'cat_relation' => 'OR',
			'orderby'  => 'ID',
			'order'    => 'DESC',
			'meta_key' => '',
			'staff_filter'     => false,
			'label' => false,
			'translate' => false,
			'template' => '',
		);
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
        	'ld_laboratory',
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
                
            	// for xx_lang1 xx_lang2 slugs, call xx_shortcode shortcode
            	$base_slug = $field['slug'];
	        	if (strlen($base_slug)>5 AND strrpos($base_slug, '_lang', -4) >0) {
	        		$base_slug = substr($base_slug, 0, -6); 
	        	}
            	$meta_key = 'ld_' . $field['slug']; 
        		$shortcode_function = 'ld_' . $base_slug . '_shortcode';
                
                if (method_exists('Lab_Directory_Shortcode',$shortcode_function)) {
                	add_shortcode( $meta_key, array( 'Lab_Directory_Shortcode', $shortcode_function ) ); 
                } else {
                	add_shortcode( $meta_key, array( 'Lab_Directory_Shortcode', 'ld_meta_shortcode' ) );
                }
            }
        }
       
        
        //load default stylesheet
        wp_enqueue_style( 'default.css', plugins_url( ).'/lab-directory/css/default.css' );

	}

    /*** Begin shortcode functions ***/
	
	// TODO ALL Shortcode: test activated (frontend...), MV, tooltips

    /* 
     * add a div to all shortcode  
     */
    static function div_it($output, $tag= '', $atts= array()) {
    	
    	if ( ! $output)  {return '';}

    	$label = '';
    	if ( ( self::$lab_directory_main_shortcode_params['label'] ===true OR 
        	self::$lab_directory_main_shortcode_params['label'] =='true') AND
        (  ! isset($atts['label']) OR ($atts['label'] !== false AND $atts['label'] != 'false'  ) ) )
        {
    		$label = ' with_label';
    		$output ='<span class="label_field">' . __(lab_directory::$default_meta_field_names[substr($tag,3)], 'lab-directory') . '</span> <span class="content_field ' . $label . '">' .$output .  '</span>'; 
    	}
    	if ( isset($atts['add_div']) AND ($atts['add_div'] === true OR $atts['add_div'] == 'true' ) ) {
    		return '<div class=" '. $tag . $label . ' ld_field">' . $output . '</div>';
    	}
    	return $output;
    }
    
    /* 
     * ld_meta_shortcode function is the default shortcode function 
     * used when no specific function has been written for a shortcode
     */
    static function ld_meta_shortcode( $atts, $content = NULL, $tag = '' ) {
	    // Add all possible parameter + default value pairs in this array 
    	global $wpdb;
    	 
	    $atts = shortcode_atts( array(
	    	'add_div'     => true,
	    	'translate' => false,
	    	'add_link' => false,
	    ), $atts);
	    
	    return self::ld_get_meta_value ( $atts, $content , $tag ); 
    }
    
    /*
     * Function to retrieve meta field value in frontend, if not hidden
     * + translate when necessary
     * + add tooltips 
     * + convert (for MV) 
     * + divit
     * 
     * return the metafield value ready for displaying
     */
    
    static function ld_get_meta_value ( $atts, $content = NULL, $tag = '' ) {
    	
    	// remove 'ld_' prefix to get slug
    	$slug = substr($tag,3);
    	$to_translate = false;
    	// Return if meta field is hidden in frontend
    	if (Lab_Directory::$staff_meta_fields[$slug]['show_frontend'] != '1') {
    		return null;
    	}
    	 
    	// translation of  _resume _goal _subject suffixed metafields
    	
    	// search for xx_lang1 xx_lang2 suffixed tags
    	$lang == '';
    	if (strlen($tag)>5 AND strrpos($slug, '_lang', -4) >0) {
    		$to_translate=true;
    		$base_slug = substr($slug, -6, 6);
    		$lang = substr($base_slug, 0, -6);
    	}
    	// if no lang_x found, search for tag with _resume _goal _subject suffix
    	if ( $lang=='' AND (strrpos($tag, '_resume') > 1) OR (strrpos($tag, '_subject') > 1) OR(strrpos($tag, '_goal') > 1) ) {
    		$to_translate=true;
    		$base_slug = $slug;
    	}
    	 
    	
    	if ($to_translate==true) {
    		// 'translate' tag with _resume _goal _subject suffix 
    		$meta_key = 'ld_' . $base_slug;
    		$meta_value = self::translate($base_slug, $tag, $lang, $atts);
    	} else {
    		// get meta field value
    		$meta_key = 'ld_' . $field['slug'];
    		$meta_value = get_post_meta( get_the_ID(), $slug, true );
    	
    	}
    	 
    	// add tooltips when required 
    	Lab_Directory::add_tooltips($meta_value, Lab_Directory::$staff_meta_fields[$slug]);
    	 
    	// convert multivalues when required 
    	Lab_Directory::ld_value_to_something( $meta_value, Lab_Directory::$staff_meta_fields[$slug]['multivalue']);   	 
    	
    	// Add link
    	self::add_staff_profile_link ($meta_value, $atts);
	    
	    // Add enclosing div
    	return self::div_it($meta_value, $tag, $atts);
    	 
    }
    
    /*
     * Translate tag with _resume _goal _subject 
     * $lang = '' or '_lang1 or '_lang2'
     * $base_slug = hdr_subject (example)
     * $tag = ld_hdr_subject or ld_hdr_subject_lang1 or ld_hdr_subject_lang2 (example)
     */
    
    static function translate($base_slug, $tag='', $ld_lang='', $atts=array()) {
    	
    	global $lang; // Find ordering for languages
    	$lang1 = get_option( 'lab_directory_lang1');
    	$lang2 = get_option( 'lab_directory_lang2');
    	
    	$orders = array('','_lang1','_lang2'); 
    	if ($lang1 AND $lang1==$lang){
    		$orders = array('_lang1','','_lang2');
    	} elseif ($lang2 AND $lang2==$lang){
    		$orders = array('_lang2','','_lang1');
    	} elseif (get_option( 'lab_directory_locale_first','1')) {
    		$orders = array('','_lang1','_lang2');
    	} else {
    		$orders = array('_lang1','','_lang2');
    	}
   	
    	if ( ($atts['translate'] === true) OR ($atts['translate'] == 'true') ) {
    		// Find first avalaible according to language ordering
    		foreach ($orders as $ld_lang){
    			if ($temp = get_post_meta( get_the_ID(), $base_slug.$ld_lang, true ) ) {
    				return $temp;
    			}
    		}
    	}

    	if ($atts['translate'] == 'all') {
   			
    		// Find All avaliable translation in language order
    		$output = array();
    		foreach ($orders as $ld_lang) {
    			if ($value = get_post_meta( get_the_ID(), $base_slug.$ld_lang, true ) ) {
    				$output[]= $value;
    			}
    		}
    		// ADD emnpty label: <span class="label_champ">  </span>
    		if (!empty($output)) {
		        if (count($output)>1 ) {
	    				return '<span class="dashicons dashicons-arrow-right"></span>' .implode('<br /><span class="dashicons dashicons-arrow-right"></span>', 
	    					$output);
    			} else {
	    				return $output[0];
    			}    			
    			
    		} else {
    			return '';
    		}
   		}
   		
   		// (else) if ($atts['translate'] === false OR $atts['translate'] == 'false') {
    	return get_post_meta( get_the_ID(), $base_slug.$ld_lang, true );
    		
    	// return "<br> base slug=$base_slug  tag=$tag lang=$lang" . get_post_meta( get_the_ID(), $base_slug, true );
    }
    
    static function lab_directory_single_staff_loop_shortcode( $atts, $content = NULL ) {
	    // global $post; 
	    $output = "";
	    
	    // Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
    	if (self::$lab_directory_main_shortcode_params) {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_params, $atts);
	    } else {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_default_params, $atts);	
	    }

	    self::$lab_directory_main_shortcode_params = $atts; 

	   	// If an id is given (example [lab-directory id=766]) $post do not contains the staff profile 
	   	if (isset($atts['id']) AND ($atts['id'] !='')) {
	   		// Query single post from ID
	   		query_posts(array(
			    'p' => $atts['id'],
			    'post_type' => 'lab_directory_staff'));
	   	} 
	   	
	   	// if no success Now search from the main lopp params 
	   	elseif ( ( isset( self::$lab_directory_main_shortcode_params['staff_slug'] ) && 
	    		self::$lab_directory_main_shortcode_params['staff_slug'] != '' ) ) {
	   	// If no id is given load staff profile (post)
	    	query_posts(array(
			    'name' => self::$lab_directory_main_shortcode_params['staff_slug'],
			    'post_type' => 'lab_directory_staff'));
	   	}
	   	
	   	
	   	// add template CSS part if atts['css'] is given
	   	if (isset ($atts['css']) AND ($atts['css'] !='') ) { 
	   		$template= self::ld_load_template($atts['css'], true );
	   		// Save template to add to div in loop 
	   		self::$current_template = $atts['css'];
	   		if ($template['css']) {
	   			$output .= '<style type="text/css">' . $template['css'] .'</style>';
	   		}
	   	}
	    // Rewind_post() because have_posts() has already been called in the main (single.php) template
		// rewind_posts(); 
        if ( have_posts() ) {
        	// do not loop posts here it's always a single staff
            the_post();
    		$content = str_replace('<br />', '', $content);
    		$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
        } else {
        	$output .= '<p>' . __('Sorry, there is no staff corresponding to this request !', 'lab-directory') .  '</p>';
        }
        
        // delete save atts before exiting loop
        self::$lab_directory_main_shortcode_params = false;
        
        return $output;
    }
    
    static function lab_directory_staff_loop_shortcode( $atts, $content = NULL ) {
 
    	// Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
    	if (self::$lab_directory_main_shortcode_params) {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_params, $atts);
	    } else {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_default_params, $atts);	
	    }
	    self::$lab_directory_main_shortcode_params = $atts; 
	   	$query = Lab_Directory_Shortcode::lab_directory_staff_query($atts);
        $output = "";

        if ( $query->have_posts() ) {
        	// add template CSS part if atts['css'] is given
        	if (isset ($atts['css']) AND ($atts['css'] !='') ) { 
        		$template= self::ld_load_template($atts['css'], true );
        		// Save template to add to div in loop 
        		self::$current_template = $atts['css'];
        		if ($template['css']) {
        			$output .= '<style type="text/css">' . $template['css'] .'</style>';
        		}
        	}
        	
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

            while ( $query->have_posts() ) {
                $query->the_post();
    			$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
                
            }
            // Add a wrapper for the text filter
            if ( ($atts['staff_filter'] === true ) OR ($atts['staff_filter'] == 'true' ) ) {
            	$output = '<div id="lab-directory-wrapper">' . $output . '</div>';
            }
        }  else {
        	$output .= '<p>' . __('Sorry, there is no staff corresponding to this request !', 'lab-directory') .  '</p>';
        }
        wp_reset_query();

         
        // delete save atts before exiting loop
        self::$lab_directory_main_shortcode_params = false;
 
        return $output;
    }

    static function lab_directory_hdr_loop_shortcode( $atts, $content = NULL ) {
        
    	global $post; 
    		
    	// Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
    	if (self::$lab_directory_main_shortcode_params) {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_params, $atts);
	    } else {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_default_params, $atts);	
	    }
	    self::$lab_directory_main_shortcode_params = $atts; 
    	
	    $query = Lab_Directory_Shortcode::lab_directory_hdr_query($atts);
        $output = "";

	   	if ( $query->have_posts() ) {

	   		// add template CSS part if atts['css'] is given
	   		if (isset ($atts['css']) AND ($atts['css'] !='') ) { 
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
        }  else {
  
        	$output .= '<h4>' . __('Sorry, there is no information about this staff HDR !', 'lab-directory') .  '</h4>';
        	$output .= '<ul><li>' .  __('whether our website is not up-to-date', 'lab-directory') . '</li>
        		<li>' .  __('whether this staff has no HDR', 'lab-directory') . '</li></ul>';
        }
        
        wp_reset_query();

        // delete save atts before exiting loop
        self::$lab_directory_main_shortcode_params = false;
        
        return $output;
    }
 
    static function lab_directory_phd_loop_shortcode( $atts, $content = NULL ) {
	    
        global $post; 
        
    	// Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
	    if (self::$lab_directory_main_shortcode_params) {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_params, $atts);
	    } else {
	    	$atts = shortcode_atts( self::$lab_directory_main_shortcode_default_params, $atts);	
	    }
	    self::$lab_directory_main_shortcode_params = $atts; 
    	
	    $query = Lab_Directory_Shortcode::lab_directory_phd_query($atts);
    	$output = "";
    
    	if ( $query->have_posts() ) {
    		// add template CSS part if atts['css'] is given
    		if (isset ($atts['css']) AND ($atts['css'] !='') ) { 
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
    	}  else {
        	$output .= '<h4>' . __('Sorry, there is no information about this PHD !', 'lab-directory') .  '</h4>';
        }
    	wp_reset_query();

    	// delete save atts before exiting loop
    	self::$lab_directory_main_shortcode_params = false;
    	
    	return $output;
    }

    static function ld_firstname_name_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    		'add_link' => false, 
    	), $atts);
    	// Firstname and name sould never been hidden in frontend 
    	$output = get_post_meta( get_the_ID(), 'firstname', true ) . ' ' . get_post_meta( get_the_ID(), 'name', true );
        self::add_staff_profile_link ($output, $atts);
        return self::div_it($output, $tag, $atts);
    }
    static function ld_name_firstname_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    		'add_link' => false,
    	), $atts);
    	// Firstname and name sould never been hidden in frontend 
    	$output = get_post_meta( get_the_ID(), 'name', true ) . ' ' . get_post_meta( get_the_ID(), 'firstname', true );
    	self::add_staff_profile_link ($output, $atts);
        return self::div_it($output, $tag, $atts);
    }
    
    static function add_staff_profile_link (&$output, $atts) {
	    global $wp_query; 
	  
	    if ( ($atts['add_link']=='true' ) OR ($atts['add_link']===true) )  { 
    		$output = "<a href='" . self::get_ld_permalink(  
    			get_permalink($wp_query->query_vars['p']), 'staff', get_post_field( 'post_name', get_the_ID() ) ) . "'>" . $output . '</a>';
	    }	    
    }

    static function ld_photo_url_shortcode($atts, $content = NULL, $tag = '' ){
     	$atts = shortcode_atts( array(
    		'add_div'     => true,
     		'label' => 'false',
    	), $atts);
     	
    	// Return if photo is hidden in frontend
     	if (Lab_Directory::$staff_meta_fields['photo_url']['show_frontend'] != '1') {
     		return null;
     	}
     	
    	if ( has_post_thumbnail() ) {
            $attachment_array = wp_get_attachment_image_src( get_post_thumbnail_id() );
            $output = $attachment_array[0];   
        } else {
            $output = '';
        }
        return self::div_it($output, $tag, $atts);   
    }

    static function ld_widget_hdr_link_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    		'label' => false, 
    	
    	), $atts);    	
    	
    	$format = (isset($atts['format_date']) AND ($atts['format_date'] != '')) ? $atts['format_date']: 'd/m/Y';
    	$date = get_post_meta( get_the_ID(), 'hdr_date', true );
    	$date = $date? date ($format, strtotime($date)) : ' ?date? ';
    	$text = $date . ' ' . __('HDR', 'lab-directory')  . 
    		' : ' . self::ld_name_firstname_shortcode(array('add_div' => false, 'label' => 'false',));
    	$output = self::ld_profile_link_shortcode(
    		array('add_div' => false, 'label' => 'false', 'hdr' => true, 'inner_text' => $text));
        return self::div_it($output, $tag, $atts);
	}
    
    static function ld_widget_phd_link_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
    		'add_div'     => true,
    		'label' => 'false',
    	), $atts);
    	$format = isset($atts['format_date']) ? $atts['format_date']: 'd/m/Y';
    	$date = get_post_meta( get_the_ID(), 'phd_date', true );
    	$date = $date? date ($format, strtotime($date)) : __('Unknown date','lab_directory');
    	$text = $date . ' ' . __('PHD', 'lab-directory')  . 
    		' : ' . self::ld_name_firstname_shortcode(array('add_div' => false, 'label' => 'false')) . '</a>';
    	$output = self::ld_profile_link_shortcode(
    		array('add_div' => false, 'label' => 'false', 'phd' => true, 'inner_text' => $text));
        return self::div_it($output, $tag, $atts);
    }
    
    static function ld_photo_shortcode($atts, $content = NULL, $tag = '' ){
    	$atts = shortcode_atts( array(
            'add_div'     => false, // default no div for photo
    		'replace_empty'     => false,
    		'label' => 'false',
        ), $atts);
        
        // Return if photo is hidden in frontend
     	if (Lab_Directory::$staff_meta_fields['photo_url']['show_frontend'] != '1') {
     		return null;
     	}
     	$photo_url = self::ld_photo_url_shortcode(array('add_div' => false, 'label' => 'false',) );
        $output = ''; 
        if(!empty($photo_url)){
            $output = '<img class="ld_photo" src="' . $photo_url . '" />';
        } elseif ($atts['replace_empty']) {
            $output = '<img class="ld_photo" src="/wp-content/plugins/lab-directory/images/nobody.jpg" />';
        }
        
        return self::div_it($output, $tag, $atts);
    }

    static function ld_bio_shortcode( $atts, $content = NULL, $tag = '' ){
        
        // Return if Bio is hidden in frontend
     	if (Lab_Directory::$staff_meta_fields['bio']['show_frontend'] != '1') {
     		return null;
     	}
     	
     	$output = get_post_meta( get_the_ID(), $tag, true );
     	return self::div_it($output, $tag, $atts);
        /* 
         * old code used the_content, no filter needed
         * $bio = get_the_content();
         * $bio = apply_filters( 'the_content', $bio );
         * $bio = str_replace( ']]>', ']]&gt;', $bio );
         * 
         */
        
       
        
    }

    static function ld_profile_link_shortcode($atts, $content = NULL, $tag = '' ){

        global $wp_query; 
        $atts = shortcode_atts( array(
            'add_div'     => true,
        	'target'     => "_self",
            'inner_text' => "Profile", 
        	'hdr' => false, 
        	'phd' => false,	 
        ), $atts);
        
    	if ( $atts['hdr']) {
        	$template = 'staff_hdr';
        }
        elseif ( $atts['phd']) {
        	$template = 'staff_phd';
        }else {
        	$template = 'staff';
        }
        $profile_link = self::get_ld_permalink( 
        	get_permalink($wp_query->query_vars['p']), $template, get_post_field( 'post_name', get_the_ID() ) );
        
        
        
        if(!empty($content)) {
            $output = "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . do_shortcode($content) . '</a>';
        } else {
            $output = "<a href='" . $profile_link . "' target='" . $atts['target'] . "'>" . $atts['inner_text'] . '</a>';
        }
        return self::div_it($output, $tag, $atts);
    }

	
	static function ld_team_shortcode($atts, $content = NULL, $tag = '' ){

        $atts = shortcode_atts( array(
            'add_div' => true,
        ), $atts);
        
        $output = get_the_term_list( get_the_ID() , 'ld_taxonomy_team' , '' , ' | ' , '' );
        $output = strip_tags( $output );
        return self::div_it($output, $tag, $atts);
    }

    static function ld_laboratory_shortcode($atts, $content = NULL, $tag = '' ){
    
    	$atts = shortcode_atts( array(
    		'add_div' => true,
    	), $atts);
    
        $output = get_the_term_list( get_the_ID() , 'ld_taxonomy_laboratory' , '' , ' | ' , '' );
        $output = strip_tags( $output );
        return self::div_it($output, $tag, $atts);
    }
    
    static function ld_categories_nav_shortcode($atts, $content = NULL, $tag = '' ){
        
		global $post; 
		$taxonomies=lab_directory::lab_directory_get_taxonomies(); 
		$output =''; 
        $permalink = get_permalink();
       if  ($taxonomies) {
			
		  foreach ($taxonomies  as $key => $taxonomy ) {
		  	if ($output) {$output .= '<br>'; }
		  	$output .= $taxonomy['labels']['name'] . ' : ';
		    $terms = get_terms( array(
			    'taxonomy' => $key,
			    'hide_empty' => false,
			) );
		    
		    foreach ( $terms as $term) {
		    	$output .= '<a href="' . self::get_ld_permalink($permalink, self::$current_template, $term->slug) . '" >' .$term->name . '</a> | ';
            }
            $output .= '<a href="' . self::get_ld_permalink($permalink, self::$current_template, '') . '" >' . $taxonomy['labels']['all_items'] . '</a> ';
         }
        }  
        return $output;
    }
    /* 
     * $permalink : post permalink or URL
     * $slug staff_grid staff_list... staff
     * $id taxonomy or lab_directory staff id
     * $query_vars_only if true only return the end of the url containing query vars
     */
    
    static function get_ld_permalink($permalink='', $slug='',$id='', $lang=0, $query_string_only= false) {
    	$permalink = Lab_Directory::$main_ld_permalink[$lang]['permalink'];
    	
    	$simple_url = (strpos($permalink, '?') !== false);
    	if ($query_string_only) {
    		$permalink = ''; 
    	}
    	if ($slug)  {
    		if ($simple_url) {
    			$permalink .=  '&'. $slug;
    		} else {
    			// Add a / if it does not exist in permalink ( permalink structure set to 'numeric'
    			$permalink = trim ($permalink, '/'). '/'. $slug; 
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
    
    static function ld_phd_jury_shortcode($atts, $content = NULL, $tag = '' ){
    
    	// Return if Bio is hidden in frontend
     	if (Lab_Directory::$staff_meta_fields['phd_jury']['show_frontend'] != '1') {
     		return null;
     	}
     	return self::ld_jury_shortcode($atts, $content, $tag);
    }
    static function ld_hdr_jury_shortcode($atts, $content = NULL, $tag = '' ){
    
    	// Return if Bio is hidden in frontend
    	if (Lab_Directory::$staff_meta_fields['hdr_jury']['show_frontend'] != '1') {
    		return null;
    	}
    	return self::ld_jury_shortcode($atts, $content, $tag);
    }
    
    static function ld_jury_shortcode($atts, $content = NULL, $tag = '' ){
    
     	$jury_members = get_post_meta( get_the_ID(), substr($tag,3), true );
    	
    	if (empty($jury_members)) {
    		return ''; 
    	}
     	$column_f = false;
    	$column_t = false;
 
    	foreach ( $jury_members as $jury_member ) {
    		$column_f = $column_f || ($jury_member['function']!='');
    		$column_t = $column_t || ($jury_member['title']!='');
    	}
    	

    	$output='
    	<table class="jury striped" cellspacing="0" id="lab_directory_staff-meta-fields">
    		<thead>
    		<tr>'; 
    	if ($column_f) { $output .= '	<th id="columnname" scope="col">Function</th>';}
    	$output.= '	<th id="columnname" scope="col" >Name</th>';
    	if ($column_t) { $output .= '	<th id="columnname" scope="col">Title, University, enterprise</th>';}
		$output.= '	</tr>
	</thead>

    	
    				<tbody>';
    	foreach ( $jury_members as $jury_member ) {
     	$output .='
    				<tr>';
    	if ($column_f) { $output .= '<td>' . $jury_member['function'] . '</td>';}
    	$output .= '<td>' . $jury_member['name'] . '</td>';
    	if ($column_t) { $output .= '<td>' . $jury_member['title'] . '</td>';}
    	$output .= '</tr>';
    	}
    	$output .='	</tbody>
    	</table>';
    	return self::div_it($output, $tag, $atts);
    }
    

	 
	 /* 
	  * [lab-directory] main shortcode
	  */
	  static function lab_directory_main_shortcode( $params ) {
    	global $wp_query;
    	// Concatenate main loop params if a main loop was preceeding the staff loop and loop attributes
    	$params = shortcode_atts( self::$lab_directory_main_shortcode_default_params, $params);
    	
    	/*
    	 * 
    	echo "<br>========= lab_directory_main_shortcode =================";
    	
    	if(isset($wp_query->query_vars)) {
    		var_dump($wp_query->query_vars);
    		echo "<br>template=". urldecode($wp_query->query_vars['ld_template']);
    	}
    	
    	echo "<br>========= lab_directory_main_shortcode =================<br>";
    	 */
    	
    	$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();
    	 
	  	 // If some query_vars exists set tmpalte and staff or cat filter
	  	 if ( isset($wp_query->query_vars['staff_trombi']) ) {
       		$template = 'staff_trombi';
       		$params['category_name'] = $wp_query->query_vars['staff_trombi'];     
       		
	  	}elseif ( isset($wp_query->query_vars['staff_list']) ) {
       		$template = 'staff_list';
       		$params['category_name'] = $wp_query->query_vars['staff_list'];
       	
       	}elseif ( isset($wp_query->query_vars['staff_grid']) ) {
       		$template = 'staff_grid';
       		$params['category_name'] = $wp_query->query_vars['staff_grid'];
       	
       	}elseif ( isset($wp_query->query_vars['staff']) ) {
       		$template = 'single_staff';
       		$params['staff_slug'] = $wp_query->query_vars['staff'];
       	
       	}elseif ( isset($wp_query->query_vars['staff_hdr']) ) {
       		$template = 'single_staff_hdr';
       		$params['staff_slug'] = $wp_query->query_vars['staff_hdr'];
       	
       	}elseif ( isset($wp_query->query_vars['staff_phd']) ) {
       		$template = 'single_staff_phd';
       		$params['staff_slug'] = $wp_query->query_vars['staff_phd'];
       		
       	}else{  // Use default template if template not set by query vars
       		$template = isset($template)? $template : get_option( 'lab_directory_default_template', 'staff_grid');
       	}
       	// If template was set in params, override previous value
       	if ($params['template'] AND $params['template'] !='') {
       		$template = $params['template'];
       	}

       	// If params (id) exist ovverride previous value 
       	if ( isset($params['id']) AND ($params['id']!='') ) {
       		$template = 'single_staff'; 
       	}
       	
       	// Save template name for use in loop's div
       	self::$current_template = $template;  
        // Save $atts for using inside loop
        self::$lab_directory_main_shortcode_params = $params; 
        
        $output = self::retrieve_template_html($template); 
        // delete save atts before exiting loop 
        self::$lab_directory_main_shortcode_params = false;
        return $output; 
        
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
			'staff_filter'     => false,
			'label'	=> false,
			'translate' => false,
		);
		//TODO remove upper!! and load self::default ?? 
		$params = shortcode_atts( $default_params, $params );
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID for your Lab Directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1,
			'lang' => '', // do not restrict to one language (staff directory post do not have language)
		);
	
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		}
		// Search by post_slug  (post_name)
		if ( ( isset( self::$lab_directory_main_shortcode_params['staff_slug'] ) && self::$lab_directory_main_shortcode_params['staff_slug'] != '' ) ) {
			$query_args['name'] = self::$lab_directory_main_shortcode_params['staff_slug'];
		}
		
		// ends single lab_directory_staff
	
		// If no cat nor id filter in loop
		if ( ( !isset( $params['cat'] ) || $params['cat'] == '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			// TODO OBSOLETE Try to add url parameter filter ($_GET) in $params filter
			if ($_GET['cat']) {
				$params['cat'] = $_GET['cat'];
			}
		}

		// Search by post taxonomy (category_name)
		if ( ( isset( self::$lab_directory_main_shortcode_params['category_name'] ) && self::$lab_directory_main_shortcode_params['category_name'] != '' ) ) {
			$cats_query[] = array( 
					'relation' => 'OR',
					array(
					'taxonomy' => 'ld_taxonomy_team',
					'terms'    => self::$lab_directory_main_shortcode_params['category_name'],
					'field'    => 'slug',  ) ,
					array(
					'taxonomy' => 'ld_taxonomy_laboratory',
					'terms'    => self::$lab_directory_main_shortcode_params['category_name'],
					'field'    => 'slug', )
			);		
			$query_args['tax_query'] = $cats_query;
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
					'relation' => 'OR',
					array(
					'taxonomy' => 'ld_taxonomy_team',
					'terms'    => $cat,
					'field'    => 'id',  ) ,
					array(
					'taxonomy' => 'ld_taxonomy_laboratory',
					'terms'    => $cat,
					'field'    => 'id', )
					
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
			'staff_slug' => '',
			'period' => 'all',
		);

		$params = shortcode_atts( $default_params, $params );
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID for your Lab Directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1, 
			'lang' => '', // do not restrict to one language
		);
	
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		}
		// Search by post_slug  (post_name)
		if ( ( isset( self::$lab_directory_main_shortcode_params['staff_slug'] ) && self::$lab_directory_main_shortcode_params['staff_slug'] != '' ) ) {
			$query_args['name'] = self::$lab_directory_main_shortcode_params['staff_slug'];
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
			'staff_slug' => '',
			'period' => 'all',			
		);
		
		$params = shortcode_atts( $default_params, $params );
		// make sure we aren't calling both id and cat at the same time
		if ( isset( $params['id'] ) && $params['id'] != '' && isset( $params['cat'] ) && $params['cat'] != '' ) {
			return "<strong>ERROR: You cannot set both a single ID and a category ID when displaying a directory</strong>";
		}
	
		$query_args = array(
			'post_type'      => 'lab_directory_staff',
			'posts_per_page' => - 1,
			'lang' => '', // do not restrict to one language
		);
	
		
		// check if it's a single lab_directory_staff member first, since single members won't be ordered
		if ( ( isset( $params['id'] ) && $params['id'] != '' ) && ( ! isset( $params['cat'] ) || $params['cat'] == '' ) ) {
			$query_args['p'] = $params['id'];
		} 
		// Search by post_slug  (post_name)
		if ( ( isset( self::$lab_directory_main_shortcode_params['staff_slug'] ) && self::$lab_directory_main_shortcode_params['staff_slug'] != '' ) ) {
			$query_args['name'] = self::$lab_directory_main_shortcode_params['staff_slug'];
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
	   
        // Load template (HTML and CSS)
        $template = self::ld_load_template($slug);
		
		$output = '';
		if ($template['css']) {
			$output .= '<style type="text/css">' . $template['css'] .'</style>';
		}
	
		//TODO single ID ???
		$output .= '<div class="ld_' . $slug . '_loop" >' . do_shortcode($template['html']) . '</div>';
		return $output;
		
		/* TODO OLD CODE Search utility of escaping
		 * 
        if ($cur_template) {
            
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


