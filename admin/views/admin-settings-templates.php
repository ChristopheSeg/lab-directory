<script type="text/javascript">
	function empty_template(template_slug) {

     	var element = document.getElementById(template_slug + '_html');
    	element.value = '';
	}
	function empty_css(template_slug) {

     	var element = document.getElementById(template_slug + '_css');
    	element.value = '';
	}
		function load_default_template(template_slug) {

     	var default_template = document.getElementById('default_html_' + template_slug);
         	var element = document.getElementById(template_slug + '_html');
    	element.value = default_template.value;
	}
	function load_default_css(template_slug) {

     	var default_template = document.getElementById('default_css_' + template_slug);
         	var element = document.getElementById(template_slug + '_css');
    	element.value = default_template.value;
	}
	
  	function show_group(group) {

	    var elems = document.getElementsByClassName("custom-template");
	    
	    for(var i = 0; i != elems.length; ++i)
	    {
	    	if (elems[i].classList.contains(group)) {
	    		   elems[i].style.display= 'block';
	    	    } else {
	    	    	elems[i].style.display = 'none';
	    	    }
	    }
	} 
</script>

<style type="text/css">

  .custom-template-dropdown-arrow {
    text-decoration: none;
  }
  .custom-template {
  display: none;
    width:100%;
  }
  .custom-template-css {
    width: 45%;
    float: left;
    padding-right:10px;
    
  }
  
  .custom-template textarea {
    width: 100%;
    height: 300px;
  }
</style>

<?php 
echo_form_messages($form_messages); 
$template_slugs = Lab_Directory_Shortcode::retrieve_template_list();
?>

     	
    <form method="post">
    <h2><?php echo __('Lab Directory Settings','lab-directory'). ' : '; _e('Templates used in Lab-Directory loops','lab-directory'); ?></h2>
   
        <?php _e('Browse template (url_slug):', 'lab-directory');
    	foreach($template_slugs as $template_slug => $template_info) {
    		$url_slug = Lab_Directory::$lab_directory_url_slugs[$template_slug];
    		$url_slug = ($url_slug ==$template_slug) ? '' : " ($url_slug) ";    	?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <input type="button" onclick="show_group('<?php echo $template_slug; ?>'); return false;" value="<?php echo $template_slug.$url_slug; ?>"/>
    <?php }
    ?> 
    </p> <hr>
    <?php 
    // Display first item, hide others 
    $first = 'style="display: block;"';    
    foreach($template_slugs as $template_slug => $template_file): 
    $template_file =  'ld_' . $template_slug . '.php'; 
    $css_file =  'ld_' . $template_slug . '.css'; 
    $template_content = get_option( $template_file);
    $css_content = get_option( $css_file);
    $default_template = file_exists(LAB_DIRECTORY_TEMPLATES . '/' . $template_file) ?
    		file_get_contents(LAB_DIRECTORY_TEMPLATES . '/' . $template_file) : '';
    $default_css =  file_exists(LAB_DIRECTORY_TEMPLATES . '/' . $css_file) ?
    		file_get_contents(LAB_DIRECTORY_TEMPLATES . '/' . $css_file): '';
    
    ?>
     <div class="custom-template  <?php echo $template_slug; ?>" <?php echo $first; ?>>

		<b><?php echo __('Template') . ' : ' . $template_slug. ' / ' . __('URLslug') . ' : ' . Lab_Directory::$lab_directory_url_slugs[$template_slug]; ?></b> <i><?php echo $template_info; ?></i><br>
		
	  <div class="custom-template-css">
	  <label for="custom_staff_templates[<?php echo $template['index']; ?>][html]">HTML : </label>
      <input type="button" onclick="empty_template('<?php echo $template_slug; ?>'); return false;" value="Delete (Empty) template"/>
		&nbsp;&nbsp;&nbsp;&nbsp; <input type="button" onclick="load_default_template('<?php echo $template_slug; ?>'); return false;" value="Load default template (<?php echo $template_file; ?>)"/>
	  <textarea id="<?php echo $template_slug . '_html'; ?>" name="custom_lab_directory_staff_templates[<?php echo $template_slug; ?>][html]" ><?php echo html_entity_decode(stripslashes($template_content)); ?></textarea>
      <p>
      <button type="submit" name="admin-settings-templates" class="button button-primary button-large" value="Save"><?php _e('Save all templates and stylesheet', 'lab-directory'); ?></button>
      </p>
      </div>
      <div class="custom-template-css">
      <label for="custom_staff_templates[<?php echo $template['index']; ?>][css]">Additionnal CSS : </label>
      <input type="button" onclick="empty_css('<?php echo $template_slug; ?>'); return false;" value="Delete (Empty) CSS"/>
		&nbsp;&nbsp;&nbsp;&nbsp; <input type="button" onclick="load_default_css('<?php echo $template_slug; ?>'); return false;" value="Load default CSS (<?php echo $css_file; ?>)"/>
      <textarea id="<?php echo $template_slug . '_css'; ?>" name="custom_lab_directory_staff_templates[<?php echo $template_slug; ?>][css]" ><?php echo html_entity_decode(stripslashes($css_content)); ?></textarea>
      <p> <?php _e('When loading default css (it can be used to see all default CSS rules used in a template) only keep in Lab-Directory settings the CSS Rules that you modified (no need to override one rule with itself). ', 'lab-directory'); ?></p>
			</div>
      <textarea id="<?php echo 'default_html_'.$template_slug; ?>" style="display:none;"><?php echo html_entity_decode(stripslashes($default_template)); ?></textarea>
      <textarea id="<?php echo 'default_css_'.$template_slug; ?>" style="display:none;"><?php echo html_entity_decode(stripslashes($default_css)); ?></textarea>
            
    </div>

  <div class="clear"></div>
   
   
    <?php  $first = ''; endforeach; ?>

  <div class="clear"></div>

  
 
  	<?php wp_nonce_field('admin-settings-templates'); ?>
</form>
