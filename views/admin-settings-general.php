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
  form {
  background: #fff;
  padding: 10px;
  margin: 10px;
  border: 1px solid #dadada;
  }
  .mid_left {
    display: block;
    width: 46%;
    margin: 1%;
    padding: 1%;
    float:left;
    clear:none;
    background: rgb(239, 239, 239);	
  }
  .mid_right {
    display: block;
    width: 46%;
    margin: 1%;
    padding: 1%;
    float: right;
    clear:none;
    background: rgb(239, 239, 239);	
  }
  .both_columns {
    display: block;
    width: 96%;
    margin: 1%;
    padding: 1%;
    float: left;
    clear:none;
    background: rgb(239, 239, 239);	
  }
</style>

<?php echo_form_messages($form_messages); 
$use_lang1 = get_option( 'lab_directory_use_lang1',true);
$use_lang2 = get_option( 'lab_directory_use_lang2',true);
$lab_directory_locale_first =  get_option( 'lab_directory_locale_first','1');
$lab_directory_default_template = get_option( 'lab_directory_default_template', 'staff_grid');
$lang1 = get_option( 'lab_directory_lang1',true);
$lang2 = get_option( 'lab_directory_lang2',true);
?>

<form method="post">
<h2><?php _e('Lab Directory : General settings','lab-directory'); ?></h2>

<div class="mid_left">
    <b>LDAP syncing</b><br/><br/>
	<input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Use LDAP and LDAP sync
</div>

<div class ="mid_right">
<b>Social network used in metafields</b><br/>
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
	<i><?php echo ($use_default_social_networks? 'Proposed social networks (default)':'Enabled'); ?></i><br> <?php echo $used ;?> 
	</div>
	<div style="float:left; width: 60%; min-width: 150px;">
	<i>Other available social networks </i><br> <?php echo $unused ;?> 
	</div> 
</div>

<div class="mid_left">
	<b>Staff Taxonomies</b><br/>
 	<p><input name="lab_directory_use_taxonomy1" type="checkbox"value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy1' ) ); ?> /> Use Taxonomy 1 (<?php echo $taxonomies['ld_taxonomy_team']['labels']['name']; ?>)</p>
    <p><input name="lab_directory_use_taxonomy2" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_taxonomy2' ) ); ?> /> Use Taxonomy 2 (<?php echo $taxonomies['ld_taxonomy_laboratory']['labels']['name']; ?>)</p>
	<p><input name="lab_directory_use_ld_footer_pages" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ld_footer_pages' ) ); ?> /> Display manager contact on each page.</p>
	<p><input name="lab_directory_use_ld_footer_posts" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ld_footer_posts' ) ); ?> /> Display manager contact on each post.</p>
</div>
<div class="mid_left">
	<b>Title for staff pages</b><br/>
	<label><input type="radio" name="lab_directory_title_firstname_first" value="1" <?php checked('1', $lab_directory_locale_first); ?> />Firstname Name</label>
	<label><input type="radio" name="lab_directory_title_firstname_first" value="0" <?php checked('0', $lab_directory_locale_first); ?> />Name Firstname</label>
</div>



<div class="mid_left">
	<b>Default template, format switcher for staff list</b><br/>
	<p><label><input type="radio" name="lab_directory_default_template" value="staff_grid" <?php checked('staff_grid', $lab_directory_default_template); ?> />Compact Grid</label>
	<label><input type="radio" name="lab_directory_default_template" value="staff_list" <?php checked('staff_list', $lab_directory_default_template); ?> />Compact list</label>
	<label><input type="radio" name="lab_directory_default_template" value="staff_trombi" <?php checked('staff_trombi', $lab_directory_default_template); ?> />Photos</label>
	</p>
	<p><label><input name="lab_directory_use_format_switcher" type="checkbox"value="1" <?php checked( '1', get_option( 'lab_directory_use_format_switcher' ) ); ?> /> lab_directory_use_format_switcher</label></p> 
	<p><label><input name="lab_directory_use_staff_search" type="checkbox"value="1" <?php checked( '1', get_option( 'lab_directory_use_staff_search' ) ); ?> /> lab_directory_use_staff search</label></p> 
</div>

<div class="both_columns">
	<b>URL slug for staff pages</b><br/>
	<table class="widefat striped" style="width: auto; table-layout: auto;" cellspacing="0" >
	<tbody>
		<tr>
			<td><b>URL slug</b></td>
			<td>replacement slug</td>
			<td>corresponding template description</td>
		</tr>
		<?php  
		$template_slugs = Lab_Directory_Shortcode::retrieve_template_list();
		$lab_directory_url_slugs = get_option('lab_directory_url_slugs');
		foreach ($template_slugs as $key => $info)  {
		$value = $lab_directory_url_slugs[$key];
		?>
		<tr>
			<td><b><?php echo $key;?></b></td>
			<td>
				<input name="lab_directory_url_slugs[<?php echo $key;?>]" id="lab_directory_url_slugs[<?php echo $value;?>]" value="<?php echo $value; ?>" type="text">
			</td>
			<td><p class="description"><?php echo $info?></p>
			</td>
		</tr>
		<?php } // end foreach ?>
		</tbody>
		</table>
			
	
</div>

<div class="both_columns">
	<b> <?php _e('Translation for subject, resume and goal metafields','lab-directory') ?> </b> (partially implemented)<br/><br/>
	
	<table class="widefat striped" style="width: auto; table-layout: auto;" cellspacing="0"  >
	<tr>
		<td><i>page language</i></td>
		<td colspan="2"><i>use language</i></td>
		<td><i>Replacement priority (use first available)</i></td>
	</tr>
	<tr>
		<td>default language</td>
		<td></td>
		<td><?php echo $language_list[$locale]['native_name']; ?>, <?php echo $language_list[$locale]['english_name']; ?></td>
		<td>locale lang1 lang2</td>
	</tr>
	<tr>
		<td>Language 1</td>
		<td><input name="lab_directory_use_lang1" type="checkbox" value="1" <?php 
			checked( '1', get_option( 'lab_directory_use_lang1' ) );
			echo count($languages)>0? ' ' : _e('Not available', 'lab-directory'); ?> /></td>
		<td><?php if (count($languages)>0) {	echo lab_directory_create_select('lab_directory_lang1', 
			$languages, $lang1, false, null, true, false);} ?>
		</td>
		<td>Language 1, locale, Language 2</td>
	</tr><tr>
		<td>Language 2</td>
		<td><input name="lab_directory_use_lang2" type="checkbox" value="1" <?php 
			checked( '1', get_option( 'lab_directory_use_lang2' ) );
			echo count($languages)>1? ' ' : _e('Not available', 'lab-directory'); ?> /></td>
		<td><?php if (count($languages)>1) {	echo lab_directory_create_select('lab_directory_lang1', 
			$languages, $lang2, false, null, true, false);} else { _e('Not available', 'lab-directory');} ?>
		</td>	<td>Language 2, locale, Language 1</td>
		<td></td>
	</tr>
	<tr>
		<td>others languages</td>
		<td><input type="radio" name="lab_directory_locale_first" value="1" <?php checked('1', $lab_directory_locale_first); ?> /></td>
		<td>Locale first</td>
		<td>locale, Language 1, Language 2</td>
	</tr><tr>
		<td>others languages</td>
		<td><input type="radio" name="lab_directory_locale_first" value="0" <?php checked('0', $lab_directory_locale_first); ?> /></td>
		<td>Language 1 first</td>
		<td>Language 1, locale, Language 2</td>
	</tr>
	</table> 
	
</div>



  <div class="clear"></div>

  <p>
    <button type="submit" name="admin-settings-general" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
  </p>
  	<?php wp_nonce_field('admin-settings-general'); ?>
</form>
