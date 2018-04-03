<?php

class Lab_Directory_Shortcode {

    public static $lab_directory_staff_query;
    public static $current_template;
    
    static $translations = array();
    
    
	static $lab_directory_main_shortcode_default_params = array(
			'id'       => '',
			'cat'      => '',
			'cat_field' => 'ID',
			'cat_relation' => 'OR',
			'category_name' => '',
			'orderby'  => 'ID',
			'order'    => 'DESC',
			'meta_key' => '',
			'format_switcher' => null, 
			'staff_search'     => null,
			'label' => false,
			'translate' => false,
			'template' => '',
		);
    static $lab_directory_main_shortcode_params = array();
    
    public static $hdr_loop = false; 
	
	static function register_shortcode() {

		// Custom field translation filter
		add_action( 'plugins_loaded', array( 'Lab_Directory_Shortcode', 'initiate_translations' ) );
		
		if (! defined( 'POLYLANG_DIR' ) )
			// Unneccesary if pll used!!
			add_action( 'plugins_loaded', array( 'Lab_Directory_Shortcode', 'load_lab_directory_frontend_textdomain' ) );
	
		// Load text_domain after Polylang removed filter 'load_textdomain_mofile'
		add_action( 'pll_translate_labels', array( 'Lab_Directory_Shortcode', 'load_lab_directory_frontend_textdomain' ) );
		
		add_filter( 'gettext', array( 'Lab_Directory_Shortcode', 'lab_directory_custom_translations' ), 10, 3 );
		
		// load single-page/profile template
		add_filter( 'single_template', array( 'Lab_Directory_Shortcode', 'load_profile_template' ) );
		
		// add single post content hook (title and content )
		add_filter( 'the_content', array( 'Lab_Directory_Shortcode', 'ld_content_filter' ) );
		add_filter( 'posts_results', array( 'Lab_Directory_Shortcode', 'ld_posts_results_filter' ) );
				
		// add the pll_translation_url filter
		add_filter( 'pll_translation_url', array( 'Lab_Directory_Shortcode', 'filter_pll_translation_url' ), 10, 3 );
		
		//Main shortcode to initiate plugin
        add_shortcode( 'lab-directory', array( 'Lab_Directory_Shortcode', 'lab_directory_main_shortcode' ) );

        //Shortcode to initiate Lab Directory loops
        add_shortcode( 'lab_directory_staff_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_staff_loop_shortcode' ) );
        add_shortcode( 'lab_directory_hdr_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_hdr_loop_shortcode' ) );
        add_shortcode( 'lab_directory_phd_loop', array( 'Lab_Directory_Shortcode', 'lab_directory_phd_loop_shortcode' ) );
        
        // Delete wpautop after shortcode are loaded
        // http://sww.co.nz/solution-to-wordpress-adding-br-and-p-tags-around-shortcodes/
        remove_filter( 'the_content', 'wpautop' );
        // TODO Delaying is not enough !! Question: Does removing wpautop breaking some page/post ???
        // add_filter( 'the_content', 'wpautop' , 220);
        
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
        	'ld_social_link',
        );

        //Add shortcodes for all $predefined_shortcodes, link to function by
        //the name of {$code}_shortcode
        foreach($other_shortcodes as $code){
            add_shortcode( $code, array( 'Lab_Directory_Shortcode', $code . '_shortcode' ) );
        }
        
        // Add shortcodes for all metafields, link to function ld_{$code}_shortcode
        // Or default function ld_meta_shortcode
         if ( !empty( Lab_Directory_Common::$staff_meta_fields) ) {
            foreach ( Lab_Directory_Common::$staff_meta_fields as $field ) {
                
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
        add_action( 'wp_enqueue_scripts', array( 'Lab_Directory_shortcode', 'register_ld_default_style' ) );

	}
	
	static function register_ld_default_style() {
		//Register default stylesheet for conditionnal loading if Lab-Direcotry shortcode are used
        wp_register_style( 'lab-directory-default-css', LAB_DIRECTORY_URL . '/public/css/default.css' );

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
    		$output ='<span class="label_field">' . __(Lab_Directory_Common::$default_meta_field_names[substr($tag,3)], 'lab-directory') . '</span> <span class="content_field ' . $label . '">' .$output .  '</span>'; 
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
    	if (Lab_Directory_Common::$staff_meta_fields[$slug]['show_frontend'] != '1') {
    		return null;
    	}
    	 
    	// translation of  _resume _goal _subject suffixed metafields
    	
    	// search for xx_lang1 xx_lang2 suffixed tags
    	$lang = '';
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
    		$meta_key = 'ld_' . $slug;
    		$meta_value = get_post_meta( get_the_ID(), $slug, true );
    	
    	}
    	 
    	// add tooltips when required 
    	self::add_tooltips($meta_value, Lab_Directory_Common::$staff_meta_fields[$slug]);
    	 
    	// convert multivalues when required 
    	Lab_Directory_Common::ld_value_to_something( $meta_value, Lab_Directory_Common::$staff_meta_fields[$slug]['multivalue']);   	 
    	
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
    
 
    static function lab_directory_staff_loop_shortcode( $atts, $content = NULL ) {
 
    	global $lang; $post;
    	$use_staff_search = false; 
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
        		$atts['css']= sanitize_text_field($atts['css']);
        		$template= self::ld_load_template($atts['css'], true );
        		// Save template to add to div in loop 
        		self::$current_template = $atts['css'];
        		if ($template['css']) {
        			$output .= '<style type="text/css">' . $template['css'] .'</style>';
        		}
        	}
        	
        	
        	// For staff list only.. 
        	if ( self::$current_template == 'staff_trombi' OR self::$current_template == 'staff_grid' OR self::$current_template == 'staff_list') {
	        	
	        	// Add format switcher
	        	$use_format_switcher = (get_option( 'lab_directory_use_format_switcher' ) == '1');
	        	$use_format_switcher = ($atts['format_switcher']!== null) ? $atts['format_switcher'] : $use_format_switcher;
	        		
        		if  ($use_format_switcher === true  OR $use_format_switcher == 'true' )  {
	  				$output .= 'List format : ';
	  	
	        		// 'staff_trombi'
	        		if (self::$current_template != 'staff_trombi') {
	        			$link = Lab_Directory_Common::get_ld_permalink('staff_trombi', $atts['category_name'], 0, false);
	        			$output .= '<a href ="' . $link . '"><span class="dashicons dashicons-camera"></span></a>&nbsp;';
	        		} else {
	        			$output .= '<span class="dashicons dashicons-camera"></span>&nbsp;';
	        		}
	        		// 'staff_grid'
	        		if (self::$current_template != 'staff_grid') {
	        			$link = Lab_Directory_Common::get_ld_permalink( 'staff_grid', $atts['category_name'], 0, false);
	        			$output .= '<a href ="' . $link . '"><span class="dashicons dashicons-grid-view"></span></a>&nbsp;';
	        		}else {
	        			$output .= '<span class="dashicons dashicons-grid-view"></span>&nbsp;';
	        		}
	        		// 'staff_list'
	        		if (self::$current_template != 'staff_list') {
	        			$link = Lab_Directory_Common::get_ld_permalink('staff_list', $atts['category_name'], 0, false);
	        			$output .= '<a href ="' . $link . '"><span class="dashicons dashicons-list-view"></span></a>&nbsp;';
	        		}else {
	        			$output .= '<span class="dashicons dashicons-list-view"></span>&nbsp;';
	        		}
	        	}
				$use_staff_search = (get_option( 'lab_directory_use_staff_search' ) =='1');
	        	$use_staff_search = ($atts['staff_search'] !== null) ? $atts['staff_search'] : $use_staff_search;
	        	if ( $use_staff_search === true  OR $use_staff_search == 'true' ) {
	        	
	        		$output .= '
<style type="text/css">
#filtre_dynamique_saisie {
    float: right;
    width: 250px;
     
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
<script type="text/javascript" src="/wp-content/plugins/lab-directory/public/js/penagwinhighlight.js"></script>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/public/js/text_filter.js"></script>
     
<form id="filtre_dynamique" style="display: block;float: right;">
  <input type="reset" id="filtre_dynamique_effacer" value="X" />
  <input type="text" id="filtre_dynamique_saisie" placeholder="'. __('Search by name or firstname','lab-directory') . '"/>
  <label for="filtre_dynamique_saisie"><span class="dashicons dashicons-search"></span></label>
</form>
        		';
	        		 
	        	}
        	}
        	

        	$output .= '<div class="clearfix"></div>';

            while ( $query->have_posts() ) {
                $query->the_post();
                // Save the edit staff url 
                if ($query->post_count==1) {
           
                	Lab_Directory_Common::$main_ld_permalink['edit_staff_url'] = get_edit_post_link(get_the_ID());
                }
    			$output .= '<div class="ld_single_item ld_' . self::$current_template . '_item">' . do_shortcode($content) . '</div>';
                
            }
            // Add a wrapper for the text filter
            if ( $use_staff_search === true  OR $use_staff_search == 'true' ) {
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
	   			$atts['css']= sanitize_text_field($atts['css']);
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
    			$atts['css']= sanitize_text_field($atts['css']);
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
    		$output = "<a href='" . Lab_Directory_Common::get_ld_permalink(  
    			'staff', get_post_field( 'post_name', get_the_ID() ) ) . "'>" . $output . '</a>';
	    }	    
    }

    static function ld_photo_url_shortcode($atts, $content = NULL, $tag = '' ){
     	$atts = shortcode_atts( array(
    		'add_div'     => true,
     		'label' => 'false',
    	), $atts);
     	
    	// Return if photo is hidden in frontend
     	if (Lab_Directory_Common::$staff_meta_fields['photo_url']['show_frontend'] != '1') {
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
    	$date = $date? date ($format, strtotime($date)) : __('Unknown date','lab_directory');
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
     	if (Lab_Directory_Common::$staff_meta_fields['photo_url']['show_frontend'] != '1') {
     		return null;
     	}
     	$photo_url = self::ld_photo_url_shortcode(array('add_div' => false, 'label' => 'false',) );
        $output = ''; 
        if(!empty($photo_url)){
            $output = '<img class="ld_photo" src="' . $photo_url . '" />';
        } elseif ($atts['replace_empty']) {
            $output = '<img class="ld_photo" src="' . LAB_DIRECTORY_URL . '/common/images/nobody.jpg" />';
        }
        
        return self::div_it($output, $tag, $atts);
    }

    static function ld_bio_shortcode( $atts, $content = NULL, $tag = '' ){
        
        // Return if Bio is hidden in frontend
     	if (Lab_Directory_Common::$staff_meta_fields['bio']['show_frontend'] != '1') {
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

    static function ld_social_link_shortcode($atts, $content = NULL, $tag = '' ){
    
    	$atts = shortcode_atts( array(
    		'add_div' => true,
    	), $atts);
    	
    	$social_networks = get_post_meta( get_the_ID(), 'social_network', true );
    	$output = ''; 
    	$temp = array();
    	if (!empty($social_networks)) {
    		foreach ( $social_networks as $key => $url ) {
	    		$output.= "<a href='" . esc_url( $url ) . "' target='_blank' title='" . $key . "'>" . Lab_Directory_Common::ld_network_icon($key)  . '</a>&nbsp;&nbsp;';
    		}
    	} 
    	
    	return self::div_it($output, $tag, $atts);
    	
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
        $profile_link = Lab_Directory_Common::get_ld_permalink( 
        	$template, get_post_field( 'post_name', get_the_ID() ) );
        
        
        
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
        if ( ! is_wp_error( $output ) ) {
        	$output = strip_tags( $output );
        } else {
        	$output ='';
        }
						
        
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
		$taxonomies=Lab_Directory_Common::lab_directory_get_taxonomies(); 
		$output =''; 
       
       if  ($taxonomies) {
			
		  foreach ($taxonomies  as $key => $taxonomy ) {
		  	if ($output) {$output .= '<br>'; }
		  	$output .= $taxonomy['labels']['name'] . ' : ';
		    $terms = get_terms( array(
			    'taxonomy' => $key,
			    'hide_empty' => false,
			) );
		    
		    foreach ( $terms as $term) {
		    	$output .= '<a href="' . Lab_Directory_Common::get_ld_permalink(self::$current_template, $term->slug) . '" >' .$term->name . '</a> | ';
            }
            $output .= '<a href="' . Lab_Directory_Common::get_ld_permalink(self::$current_template, '') . '" >' . $taxonomy['labels']['all_items'] . '</a> ';
         }
        }  
        return $output;
    }
 
    
    static function ld_phd_jury_shortcode($atts, $content = NULL, $tag = '' ){
    
    	// Return if Bio is hidden in frontend
     	if (Lab_Directory_Common::$staff_meta_fields['phd_jury']['show_frontend'] != '1') {
     		return null;
     	}
     	return self::ld_jury_shortcode($atts, $content, $tag);
    }
    static function ld_hdr_jury_shortcode($atts, $content = NULL, $tag = '' ){
    
    	// Return if Bio is hidden in frontend
    	if (Lab_Directory_Common::$staff_meta_fields['hdr_jury']['show_frontend'] != '1') {
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
    	
    	// Enqueue style 
    	wp_enqueue_style( 'lab-directory-default-css');
    	wp_enqueue_style( 'font-awesome');
    	 // If some query_vars exists set template and staff or cat filter
	  	 if ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_trombi']]) ) {
       		$template = 'staff_trombi';
       		$params['category_name'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_trombi']];     
       		
	  	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_list']]) ) {
       		$template = 'staff_list';
       		$params['category_name'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_list']];
       	
       	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_grid']]) ) {
       		echo "<br> staff grid";
       		$template = 'staff_grid';
       		$params['category_name'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_grid']];
       	
       	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff']]) ) {
       		$template = 'staff';
       		$params['staff_slug'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff']];
       	
       	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_hdr']]) ) {
       		$template = 'staff_hdr';
       		$params['staff_slug'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_hdr']];
       	
       	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_phd']]) ) {
       		$template = 'staff_phd';
       		$params['staff_slug'] = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_phd']];
       		
       	}elseif ( isset($wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['defense_list']]) ) {
       		$template = 'defense_list';
       	
       	}
       	else{  // Use default template if template not set by query vars
       		$template = isset($template)? $template : get_option( 'lab_directory_default_template', 'staff_grid');
       	}
       	// If template was set in params, override previous value
       	if ($params['template'] AND $params['template'] !='') {
       		$template = $params['template'];
       	}

       	// If params (id) exist ovverride previous value 
       	if ( isset($params['id']) AND ($params['id']!='') ) {
       		$template = 'staff'; 
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
	 * $params contains used attribute in loop
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
			'format_switcher' => null, 
			'staff_search'     => null,
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
		
		// check if we're returning a lab_directory_staff category
		if ( ( isset( $params['cat'] ) && $params['cat'] != '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			$cats_query = array();
	
			$cats = explode( ',', $params['cat'] );
	
			if (count($cats) > 1) {
				$cats_query['relation'] = $params['cat_relation'];
			}
	
			//TODO OBSOLETE rewrite for new categories but 2 cats how to !! 
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
	
	
		// check if we're returning a lab_directory_staff category
		if ( ( isset( $params['cat'] ) && $params['cat'] != '' ) && ( ! isset( $params['id'] ) || $params['id'] == '' ) ) {
			$cats_query = array();
	
			$cats = explode( ',', $params['cat'] );
	
			if (count($cats) > 1) {
				$cats_query['relation'] = $params['cat_relation'];
			}
	
			//TODO OBSOLETE rewrite for new categories  but 2 cats how to !! 
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
    
    /* 
     * Load custom translation
     */
    static function initiate_translations() {
    	$translations = array();
    	$temp = get_option( 'lab_directory_translations_' . Lab_Directory_Common::$default_post_language );
    	if ( is_array( $temp ) ) {
    		$translations = array_merge( $translations, $temp );
    	}
    	$temp = get_option( 'lab_directory_taxonomies_' . Lab_Directory_Common::$default_post_language );
    	if ( is_array( $temp ) ) {
    		$translations = array_merge( $translations, $temp );
    	}
    	self::$translations = $translations;
    }
    
    static function lab_directory_custom_translations( $translated, $original, $domain ) {
    	if ( 'lab-directory' == $domain ) {
    		if ( array_key_exists( $original, self::$translations ) ) {
    			$translated = self::$translations[$original];
    		}
    	}
    
    	return $translated;
    }
    /******************************/


    /* add a pll_translation_url filter (only called when pll is in use)
     *
     */
    
    static function filter_pll_translation_url( $var, $lang ) {
    
    	$var .= Lab_Directory_Common::$main_ld_permalink['query_string'];
    	return $var;
    }
    
    
    static function ld_posts_results_filter( $posts ) {
    	global $wp_query;
    
    	if ( is_singular() and isset($posts[0]) ) {
    		if ( $posts[0]->post_type == 'lab_directory_staff' ) {
    			if ( $posts[0]->post_content == '' ) {
    				// add empty span to display hooked content on a page
    				$posts[0]->post_content = '<span></span>';
    			}
    		} else {
    			// Modify title
    			global $wpdb;
    				
    			if ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff']]) ) {
    				$posts[0]->post_title = $wpdb->get_var(
    					"SELECT post_title FROM $wpdb->posts WHERE post_name = '" .
    					$wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff']] . "' AND post_type='lab_directory_staff'") ;
    			}
    			if ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_hdr']] ) ) {
    				$posts[0]->post_title = "HDR: " . $wpdb->get_var(
    					"SELECT post_title FROM $wpdb->posts WHERE post_name = '" .
    					$wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_hdr']] . "' AND post_type='lab_directory_staff'") ;
    			}
    			if ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_phd']] ) ) {
    				$posts[0]->post_title = "PHD: " . $wpdb->get_var(
    					"SELECT post_title FROM $wpdb->posts WHERE post_name = '" .
    					$wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_phd']] . "' AND post_type='lab_directory_staff'") ;
    			}
    
    			// Set title  directory by team/laboratory (taxonomy)
    			if ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_grid']] ) ) {
    				$taxonomy = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_grid']] ;
    			} elseif ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_list']] ) ) {
    				$taxonomy = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_list']] ;
    			} elseif ( isset( $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_trombi']] ) ) {
    				$taxonomy = $wp_query->query_vars[Lab_Directory_Base::$lab_directory_url_slugs['staff_trombi']] ;
    			}
    			if ( isset( $taxonomy) AND $taxonomy !='') {
    				
    				$term = get_term_by('slug', $taxonomy, 'ld_taxonomy_team');
    				if ((! $term) OR ! $term->name) {
    					$term = get_term_by('slug', $taxonomy, 'ld_taxonomy_laboratory');
    				}
    				$posts[0]->post_title = ( $term AND $term->name)  ?
    				sprintf( __( "%s directory", 'lab-directory' ), $term->name  ) :
    				__( "Directory", 'lab-directory' );
    			}
    
    		}
    			
    			
    	}
    
    	return $posts;
    }
    
    
    
    static function ld_content_filter( $content ) {
    	global $wp_query, $post;
    
    	// Add ld_footer contentf to Pages and posts
    	if ( ( get_option( 'lab_directory_use_ld_footer_pages' ) and is_page() ) ||
    		( get_option( 'lab_directory_use_ld_footer_posts' ) and is_single() ) ) {
    				
    			$post_categories = wp_get_object_terms( $post->ID, array( 'category' ) );
    			$outputs = array();
    				
    			foreach ( $post_categories as $category ) {
    				$output = '';
    				foreach ( Lab_Directory_Common::lab_directory_get_taxonomies() as $slug => $ld_taxonomy ) {
    					echo "";
    					if ( $term = get_term_by( 'name', $category->name, $slug ) ) {
    						$term_meta = get_option( 'taxonomy_term_' . $term->term_taxonomy_id );
    
    						if ( $term_meta['display_style'] != 'None' and $term_meta['manager_ids'] ) {
    							foreach ( $term_meta['manager_ids'] as $id ) {
    								$mails = get_post_meta( $id, 'mails', true );
    								$name = get_post_field( 'post_name', $id);
    								Lab_Directory_Common::ld_value_to_something(
    									$mails,
    									Lab_Directory_Common::$staff_meta_fields['mails']['multivalue'],
    									'display' );
    
    									$output .= '&nbsp;&nbsp;&nbsp;<a href="' . Lab_Directory_Common::get_ld_permalink('staff', $name ) .
    									'"><span class="dashicons dashicons-phone"></span>' . get_the_title( $id ) . '</a>';
    									if ( $mails ) {
    										$output .= '&nbsp;<a href="mailto:' . $mails .
    										'"><span class="dashicons dashicons-email"></span></a>';
    									}
    							}
    						}
    						if ( $output ) {
    							if ( $term_meta['display_style'] == 'Contact' ) {
    								/*
    								 * translators: This is used in pages and posts footer to display contact, %s is the
    								 * team or laboratory (one categeory)
    								 */
    								$outputs[] = sprintf(
    									__( "%s contact : ", 'lab-directory' ),
    									'<i>' . $category->name . '</i> ' ) . $output;
    							} else {
    								/*
    								 * translators: This is used in pages and posts footer to display manager, %s is the
    								 * team or laboratory (one categeory)
    								 */
    								$outputs[] = sprintf(
    									__( "%s manager : ", 'lab-directory' ),
    									'<i>' . $category->name . '</i> ' ) . $output;
    							}
    						}
    					}
    				}
    			}
    				
    			// TODO suppress inline style (move)
    			if ( ! empty( $outputs ) ) {
    				$content .= '
	<style type="text/css">
	div.ld_footer {
	    background-color: #eee;
	    font-size: 0.9em;
	    padding: 3px;
	    margin-top: 5px;
	}
	</style>
	<div class="ld_footer">' . implode( '<br>', $outputs ) . '</div>';
    			}
    		} // End add ld_footer content
    
    		return $content;
    }
    
    //TODO Rename to get template url
    static function load_profile_template( $original ) {
    
    	return $original; //TODOTODO DEPRECATED !!
    	// get_page_templates
    	if ( is_singular( 'lab_directory_staff' ) ) {
    		$original = get_page_template();
    		return $original;
    			
    		// $single_template_option = get_option( 'lab_directory_staff_single_template' );
    		if ( strtolower( $single_template_option ) != 'default' ) {
    			$template = locate_template( $single_template_option );
    			if ( $template && ! empty( $template ) ) {
    				return $template;
    			}
    		}
    		// Option not set to default, and template not found, try to load
    		// default anyway. This will ensure that if, somehow, the user
    		// doesn't visit the settings page in order to instantiate the defaults,
    		// we'll still be using a template specified for lab-directory, not the
    		// default single.php
    		$default_file_name = 'single.php';
    		return LAB_DIRECTORY_TEMPLATES . '/' . $default_file_name;
    	}
    
    	return $original;
    }
    
    static function load_lab_directory_frontend_textdomain() {
    
    	$domain = 'lab-directory';
    	$locale = apply_filters( 'plugin_locale', is_admin() ? get_user_locale() : get_locale(), $domain );
    
    	$mofile = $domain . '-frontend-' . $locale . '.mo';
    
    	// Try to load from the languages directory first.
    	if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
    		return;
    	}
    
    	// Else load from plugin language dir
    	return load_textdomain( $domain, LAB_DIRECTORY_DIR . '/languages/' . $mofile );
    }
    
    static function add_tooltips( &$meta_value, $field ) {
    	if ( ! $meta_value ) {
    		return;
    	}
    	if ( ! isset( $field['acronyms'] ) ) {
    		return;
    	}
    
    	foreach ( $field['acronyms'] as $acronym ) {
    			
    		if ( strpos( $meta_value, $acronym['acronym'] ) !== false ) {
    
    			$link = '';
    			if ( isset($acronym['link']) AND $acronym['link'] ) {
    				$replace = '<a  title="' . $acronym['translation'] . '" href="' . $acronym['link'] . '">' .
    					$acronym['translation'] . '</a>';
    			} else {
    				$replace = '<acronym title="' . $acronym['translation'] . '">' . $acronym['acronym'] . '</acronym>';
    			}
    
    			$meta_value = str_replace( $acronym['acronym'], $replace, $meta_value );
    		}
    	}
    	return;
    }
    
}


