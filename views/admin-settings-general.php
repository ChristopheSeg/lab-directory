<script type="text/javascript">
  jQuery(document).ready(function($){


    $(document).on('click', '.custom-template-dropdown-arrow', function(ev){
      ev.preventDefault();
      $(this).toggleClass('fa-angle-down');
      $(this).toggleClass('fa-angle-up');

      var customTemplate = $(this).parent().next(); // .custom-template
      customTemplate.slideToggle();
    });

    $(document).on('click', '.delete-template', function(ev){
      ev.preventDefault();

      var templateIndex = $(this).data('template-index');
      if(confirm("Are you sure you want to delete Custom Template " + (templateIndex) + "? This cannot be undone.")) {
        window.location.href = "<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings&delete-template=" + templateIndex;
      }
    });
  });
</script>

<style type="text/css">

  .custom-template {
    display: none;
    margin-bottom: 40px;
  }
  .custom-template-dropdown-arrow {
    text-decoration: none;
  }
  .lab_directory_staff-template-textarea-wrapper {
    float: left;
    width: 40%;
  }
  .lab_directory_staff-template-textarea-wrapper textarea {
    height: 170px;
  }
</style>

<?php echo_form_messages($form_messages); ?>


<form method="post">

    <h2>General settings</h2>

	<input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

    <h2>Single profile templates</h2>
    <p>Template instructions can be found on the <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-help#lab_directory_staff-template-tags">Staff Help page</a></p>

    <p>Custom templates can be created by adding a php file to your theme directory with the prefix 'lab_directory_staff_single_'.</p>

    <p>
        <select name="lab_directory_staff_single_template">
            <?php
                $val = strtolower(get_option( 'lab_directory_staff_single_template' ));

                $template_path      = get_stylesheet_directory();
                $lab_directory_staff_single_files = glob($template_path . "/single-lab_directory_staff-*.php");
            ?>
            <option value="default" <?php selected( $val, 'default'); ?>>Default</option>
            <?php
                $output = "";
                foreach($lab_directory_staff_single_files as $choice) {
                    if(substr($choice, 0, strlen($template_path . "/")) == $template_path . "/") {
                        $choice = substr($choice, strlen($template_path . "/"));
                    }
                    $value   = ' value="' . strtolower($choice) . '" ';
                    $output .= '<option' . $value . selected( $val, $choice) . '>' . $choice . '</option>';
                }
                echo $output;
            ?>
        </select>
    </p>

    <h2>Staff directory list templates</h2>

    <p>Templates can be chosen manually with the [lab-directory] shortcode (slugs shown in parentheses), or you can choose to set a default template here:</p>

    <p>
        <input type="radio" name="lab_directory_staff_templates[slug]" value="list"
        <?php checked( $current_template, 'list', true ); ?> />
        List (list)
    </p>

    <p>
        <input type="radio" name="lab_directory_staff_templates[slug]" value="grid"
        <?php checked( $current_template, 'grid', true ); ?> />
        Grid (grid)
    </p>

    <?php foreach($custom_templates as $template): ?>
      <?php require(plugin_dir_path(__FILE__) . '/partials/admin-custom-template.php'); ?>
    <?php endforeach; ?>

    <p>
      <input type="radio" name="lab_directory_staff_templates[slug]" value="custom_<?php echo count($custom_templates) + 1; ?>" disabled>
      Custom Template <?php echo count($custom_templates) + 1; ?> (save template before you select) <a href="#" class="fa fa-angle-down custom-template-dropdown-arrow"></a>
    </p>

    <div class="custom-template">
      <div class="lab_directory_staff-template-textarea-wrapper">
        <label for="custom_lab_directory_staff_templates[<?php echo count($custom_templates) + 1; ?>][html]">HTML:</label>
        <p>
          <textarea name="custom_lab_directory_staff_templates[<?php echo count($custom_templates) + 1; ?>][html]" class="large-text code"></textarea>
        </p>
      </div>

      <div class="lab_directory_staff-template-textarea-wrapper">
        <label for="custom_lab_directory_staff_templates[<?php echo count($custom_templates) + 1; ?>][css]">CSS:</label>
        <p>
          <textarea name="custom_lab_directory_staff_templates[<?php echo count($custom_templates) + 1; ?>][css]" class="large-text code"></textarea>
        </p>
      </div>
    </div>
<div class="clear"></div>
<h2>Social network used in metafields</h2>
<p>Select whose social networks can be enabled for displaying links in LAB directory. </p>
	<?php 
	wp_enqueue_style('social-icons-css',
			plugins_url( '/css/social_icons.css', dirname(__FILE__) ));
	
	$possible_social_networks= get_possible_social_networks();
	$lab_directory_used_social_networks = get_option ('lab_directory_used_social_networks', false );
	$use_default_social_networks = $lab_directory_used_social_networks? false :true; 
	if ($use_default_social_networks) {
		$lab_directory_used_social_networks = get_default_social_networks();
		$checkhed =' ';
	} else {
		$checkhed = ' checked="checked" ';
	}
	
	$used=''; 
	$unused=''; 
	
	foreach ($possible_social_networks as $key =>$value ) {
		$icon = ld_network_icon($key); 
		if (array_key_exists($key, $lab_directory_used_social_networks)) {
			// used social network 
			$used .= '<div style="float:left; width: 140px;" >';
			$used .= '<input name="lab_directory_used_social_networks[]" value="' . $key .'" '. $checkhed .' type="checkbox">';
			$used .= $icon. ' ' . $value . '</div>';
		} else {
			// used social network
			$unused .= '<div style="float:left; width: 140px;" >';
			$unused .= '<input name="lab_directory_used_social_networks[]" value="' . $key. '" type="checkbox">';
			$unused .= $icon. ' ' . $value . '</div>';
		}
	}
	?> 
	<div style="float:left; width: 20%; min-width: 150px;">
	<strong><?php echo ($use_default_social_networks? 'Proposed social networks (default)':'Enabled'); ?></strong><br> <?php echo $used ;?> 
	</div>
	<div style="float:left; width: 40%; min-width: 150px;">
	<strong>Other available social networks </strong><br> <?php echo $unused ;?> 
	</div> 


  <div class="clear"></div>

  <p>
    <button type="submit" name="admin-settings-general" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
  </p>
  	<?php wp_nonce_field('admin-settings-general'); ?>
</form>
