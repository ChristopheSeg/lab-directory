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
<h2><?php _e('Lab Directory : General settings','lab-directory'); ?></h2>
    <h2>LDAP syncing</h2>

	<input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

    <h2>Staff Taxonomies</h2>
    <p>Staff can be registerd in categories such as team, laboratory institution... using one or two taxonomies. By default, taxonomy1 corresponds to teams, taxonomy2 corresponds to laboratories  (usefull if your staff belong to several laboratories). Each taxonomy can be customized to correspond to others categorization.</p>
 	<p><input name="lab_directory_use_taxonomy1" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy1' ) ); ?> /> Check this to use Taxonomy 1</p>
    <p><input name="lab_directory_use_taxonomy2" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy2' ) ); ?> /> Check this to use Taxonomy 2</p>
    
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
			$used .= '<input name="lab_directory_used_social_networks[' . $key .']" value="' . $key .'" '. $checkhed .' type="checkbox">';
			$used .= $icon. ' ' . $value . '</div>';
		} else {
			// used social network
			$unused .= '<div style="float:left; width: 140px;" >';
			$unused .= '<input name="lab_directory_used_social_networks[' . $key .']" value="' . $key. '" type="checkbox">';
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
