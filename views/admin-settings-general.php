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

<?php echo_form_messages($form_messages); 
$use_lang1= get_option( 'lab_directory_use_lang1',true);
$use_lang2= get_option( 'lab_directory_use_lang2',true);
$lang1= get_option( 'lab_directory_lang1',true);
$lang2= get_option( 'lab_directory_lang2',true);

?>

<form method="post">
<h2><?php _e('Lab Directory : General settings','lab-directory'); ?></h2>
    <h2>LDAP syncing</h2>

	<input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

    <h2>Staff Taxonomies</h2>
    <p>Staff can be registerd in categories such as team, laboratory institution... using one or two taxonomies. By default, taxonomy1 corresponds to teams, taxonomy2 corresponds to laboratories  (usefull if your staff belong to several laboratories). Each taxonomy can be customized to correspond to others categorization.</p>
 	<p><input name="lab_directory_use_taxonomy1" type="checkbox"value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy1' ) ); ?> /> Check this to use Taxonomy 1 (<?php echo $taxonomies['ld_taxonomy_team']['labels']['name']; ?>)</p>
    <p><input name="lab_directory_use_taxonomy2" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy2' ) ); ?> /> Check this to use Taxonomy 2 (<?php echo $taxonomies['ld_taxonomy_laboratory']['labels']['name']; ?>)</p>


 
<h2>Languages for subject, resume and goal metafields</h2>
<p>All metafields are single language without any reliable possibility to translate their content. In order to internationalise your staff directory, Lab-Directory propose 2 other languages for field corresponding to: subject, resume, and goal. They can be used for example to show an english version of a PHD subject on staff pages when using multiple language website. </p>
<p>The idea is to define 3 languages "locale" the main language of your website and two other laguages "lang1" and "lang2" (_lang1 and _lang2 suffix are added to fields proposed in multiple languages). As an example, PHD_subject, PHD_subject_lang1 and PHD_subject_lang2 represent a PHD subject given in 3 possible languages.</p>
<p></p>As most webmaster knows, when using several languages content on a website, most of the time people give you this content in one (or zero!) language. In order to be as efficient as possible, priority rules are defined for these content. <p>
<table>
<tr>
	<th>page language</th>
	<th>use</th>
	<th>Replacement priority (use first available)</th>
</tr>
<tr>
	<td>locale</td>
	<td></td>
	<td><?php echo $locale; ?></td>
	<td>locale lang1 lang2</td>
</tr>
<tr>
	<td>lang1</td>
	<td><input name="lab_directory_use_lang1" type="checkbox" value="1" <?php 
		checked( '1', get_option( 'lab_directory_use_lang1' ) );
		echo count($language_list)>1? ' ' : 'disabled'; ?> /></td>
	<td><?php if (count($language_list)>1){ wp_dropdown_languages(array('name' =>'lab_directory_lang1','show_available_translations' => false)); } else {_e('Please install other language before using lang1 and lang2','lab-directory');} ?></td>
	<td>lang1 locale lang2</td>
</tr><tr>
	<td>lang2</td>
	<td><input name="lab_directory_use_lang2" type="checkbox" value="1" <?php 
		checked( '1', get_option( 'lab_directory_use_lang2' ) );
		echo count($language_list)>2? ' ' : 'disabled'; ?> /></td>
	<td><?php if (count($language_list)>2){ wp_dropdown_languages(array('name' =>'lab_directory_lang2','show_available_translations' => false)); } else {_e('Please install other language before using lang1 and lang2','lab-directory');} ?></td>
	<td>lang2 locale lang1</td>
	<td></td>
</tr>
<tr>
	<td>others languages</td>
	<td> </td>
	<td> </td>
	<td>choose between: lang1 locale lang2 or locale lang1 lang2</td>
</tr>
</table> 
<p>In order to let the possibility to not "translate" these fields use the parameter <code>translate=false</code> in shortcode</p>
<p>For example <code>PHD_subject</code> will be rendered as one of the 3 possible existing values depending on lang1 and lang2 usage and browsed page language.</p>
<p>Whilst <code>PHD_subject translate=false</code> will be rendered as PHD_subject content (*) and <code>PHD_subject_lang2 translate=false</code> as PHD_subject_lang2 content (*) . (*) only if they exist!)</p>

<p>TODO description / implementation to come soon!!</p>
    
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
