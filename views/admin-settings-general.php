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
$use_lang1 = get_option( 'lab_directory_use_lang1',true);
$use_lang2 = get_option( 'lab_directory_use_lang2',true);
$lab_directory_locale_first =  get_option( 'lab_directory_locale_first',true);
$lang1 = get_option( 'lab_directory_lang1',true);
$lang2 = get_option( 'lab_directory_lang2',true);
?>

<form method="post">
<h2><?php _e('Lab Directory : General settings','lab-directory'); ?></h2>
    <h2>LDAP syncing</h2>

	<input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

    <h2>Staff Taxonomies</h2>
    <p>Staff can be registerd in categories such as team, laboratory institution... using one or two taxonomies. By default, taxonomy1 corresponds to teams, taxonomy2 corresponds to laboratories  (usefull if your staff belong to several laboratories). Each taxonomy can be customized to correspond to others categorization.</p>
 	<p><input name="lab_directory_use_taxonomy1" type="checkbox"value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy1' ) ); ?> /> Check this to use Taxonomy 1 (<?php echo $taxonomies['ld_taxonomy_team']['labels']['name']; ?>)</p>
    <p><input name="lab_directory_use_taxonomy2" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy2' ) ); ?> /> Check this to use Taxonomy 2 (<?php echo $taxonomies['ld_taxonomy_laboratory']['labels']['name']; ?>)</p>


 
<h2>Languages for subject, resume and goal metafields (partially implemented)</h2>
<p>All metafields are single language without any reliable possibility to translate their content. In order to internationalise your staff directory, Lab-Directory propose 2 other languages for field corresponding to: subject, resume, and goal. They can be used for example to show an english version of a PHD subject on staff pages when using multiple language website. </p>
<p>The idea is to define 3 languages "locale" the main language of your website and two other laguages "language 1" and "language 2" (_lang1 and _lang2 suffix are added to multiple languages fields). As an example, PHD_subject, PHD_subject_lang1 and PHD_subject_lang2 represent a PHD subject given in 3 possible languages.</p>
<p></p>As most webmaster knows, when using several languages content on a website, most of the time people give you this content in one (or zero!) language. In order to be as efficient as possible, priority rules are defined for these content. <p>
<table>
<tr>
	<th>page language</th>
	<th colspan="2">use language</th>
	<th>Replacement priority (use first available)</th>
</tr>
<tr>
	<td>locale</td>
	<td></td>
	<td><?php echo $language_list[$locale]['native_name']; ?>, <?php echo $language_list[$locale]['english_name']; ?></td>
	<td>locale lang1 lang2</td>
</tr>
<tr>
	<td>Language 1</td>
	<td><input name="lab_directory_use_lang1" type="checkbox" value="1" <?php 
		checked( '1', get_option( 'lab_directory_use_lang1' ) );
		echo count($languages)>0? ' ' : 'disabled'; ?> /></td>
	<td><?php if (count($languages)>0) {	echo lab_directory_create_select('lab_directory_lang1', 
		$languages, $lang1, false, null, true, false);} ?>
	</td>
	<td>Language 1, locale, Language 2</td>
</tr><tr>
	<td>Language 2</td>
	<td><input name="lab_directory_use_lang2" type="checkbox" value="1" <?php 
		checked( '1', get_option( 'lab_directory_use_lang2' ) );
		echo count($languages)>1? ' ' : 'disabled'; ?> /></td>
	<td><?php if (count($languages)>1) {	echo lab_directory_create_select('lab_directory_lang1', 
		$languages, $lang2, false, null, true, false);} else { _e('disabled');} ?>
	</td>	<td>Language 2, locale, Language 1</td>
	<td></td>
</tr>
<tr>
	<td>others languages</td>
	<td><input type="radio" name="lab_directory_locale_first" value="1" <?php checked('1', $lab_directory_locale_first); ?> /></td>
	<td>Locale first:</td>
	<td>locale, Language 1, Language 2</td>
</tr><tr>
	<td>others languages</td>
	<td><input type="radio" name="lab_directory_locale_first" value="0" <?php checked('0', $lab_directory_locale_first); ?> /></td>
	<td>Language 1 first:</td>
	<td>Language 1, locale, Language 2</td>
</tr>
</table> 

<p>All metafield with lang1 and lang2 suffix have a <code>translate</code> parameter</p>
<ul style ="padding-left:15px;">
<li>Without this parameter (or <code>translate=true</code>) give one translation using the preceeding rule: <code>[PHD_subject]</code> will be rendered as one of the 3 possible existing values depending on lang1 and lang2 usage and browsed page language.</li>
<li>Use the parameter <code>[translate=false]</code> in shortcode to force language<code>[PHD_subject translate=false]</code> will be rendered as PHD_subject content (*) and <code>[PHD_subject_lang2 translate=false]</code> as PHD_subject_lang2 content (*) . (*) only if they exist!)</li>
<li>Use <code>[translate=all]</code> to display 1 to 3 translations of a field when they exist (they will appear according to the ordering rules defined above)</li>
<li>Please note that <code>[PHD_subject  translate=yyy]</code> and <code>[PHD_subject_xxx  translate=yyy]</code> are equivalent if yyy is not equal to false</li>
</ul>

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
