<script type="text/javascript">
jQuery(document).ready(function($){

	$('#add-new-acronym').on('click', function(ev){
		
		ev.preventDefault();
		var tr = $('<tr/>');
		tr.html($('#new-acronym').html());
		$("#add-new-acronym-row").before(tr);
		
	});

	$(document).on('click', '.remove-acronym', function(ev){
		ev.preventDefault();
		$(this).parent().parent().remove();
	});
});
</script>

<style type="text/css">
			#new-acronym {display: none;}
			label.lab_directory_staff-label {	
				width: 150px;
				display: inline-block;
			}
			.dashicons {
			    font-size: 16px;
			    }

 			input.large-text{ width: 80%; }
			.input-in-td{ width:100%; padding-left:0; padding-right:0; }
			a.normal {color:#0073aa;}
			textarea { resize:both; width: 100%;} 
			p{ padding-left: 5px; background-color: rgb(245, 245, 245);}
</style>
		
<?php echo_form_messages($form_messages); ?>


<form method="post">
	<?php if ($lang  == 'acronyms') { ?>
    <h2><?php echo __('Lab Directory Settings','lab-directory'). ' : '; _e("Acronyms tooltip's settings",'lab-directory'); ?></h2>
    <p>Each meta field may contains some acronyms (team, lab, university, institution acronyms). Here you can give acronyms description and link to replace these acronyms by tooltip with optional link to institution.  (see exemples at the end of this page). </p>
		<table class="widefat fixed striped" cellspacing="0" id="lab_directory_acronyms">
		<thead>
		<tr>
		<th id="columnname" scope="col" >Meta field</th>
		<th id="columnname" scope="col" ><?php _e('Acronym', 'lab-directory'); ?></th>
		<th id="columnname" scope="col" style="width:35%;">Acronym description for tooltip</th>
		<th id="columnname" scope="col" style="width:35%;">Link (Optional)</th>
		<td style="width:3%;"></td>
		</tr>
		</thead>
		<tfoot>
		<tr>
		<th id="columnname" scope="col">Meta field</th>
		<th id="columnname" scope="col"><?php _e('Acronym', 'lab-directory'); ?></th>
		<th id="columnname" scope="col">Acronym description for tooltip</th>
		<th id="columnname" scope="col">Link (Optional)</th>
		<td></td>
		</tr>
		</tfoot>
		
        <tbody>
		<?php $slugs = array(); 
		foreach ($translations as $translation) { 
		// foreach ($metafield_translations as $acronym => $translation) { ?>
		<tr>
		<td>
			<?php  echo self:: lab_directory_create_select('lab_directory_translations_metafields_slugs[]',
				Lab_Directory_Common::$default_meta_field_names, $translation['slug'], false, 'input-in-td'); 
			?>
		</td>
		<td><input type="text" name="lab_directory_translations_slugs[]" class="input-in-td" 
			value="<?php echo $translation['acronym']; // slug of the acronym ?>"/>
		</td>		
		<td><input type="text" name="lab_directory_translations_translations[]" class="input-in-td" 
			value="<?php echo isset($translation['translation']) ? $translation['translation']:''; ?>"/>
		</td>
		<td><input type="text" name="lab_directory_translations_links[]" class="input-in-td" 
			value="<?php echo isset($translation['link']) ? $translation['link']: ''; ?>"/>
		</td>
		<td>
              <a href="#" class="remove-filter"><span class="dashicons dashicons-trash"></span></a>
        </td>
		</tr>
		<?php } // } ?>
		<tr id="add-new-acronym-row" valign="top">
       	<td colspan=5>
       	<a href="#" class="normal" id="add-new-acronym">+ Add New acronym tooltip</a>
        </td>
        
        </tr>
        <tr id="new-acronym">
		<td>
			<?php echo self:: lab_directory_create_select('lab_directory_translations_metafields_slugs[]', 
              		Lab_Directory_Common::$default_meta_field_names, '', false, 'input-in-td'); 
			?>
		</td>
		<td><input type="text" name="lab_directory_translations_slugs[]" class="input-in-td" 
			value=""/>
		</td>		
		<td><input type="text" name="lab_directory_translations_translations[]" class="input-in-td" 
			value=""/>
		</td>
		<td><input type="text" name="lab_directory_translations_links[]" class="input-in-td" 
			value=""/>
		</td>
		<td>
              <a href="#" class="remove-acronym"><span class="dashicons dashicons-trash"></span></a>
        </td>
		</tr>			
        </tbody>
        </table>
        
 
	<?php } else {?>

    <h2><?php  /* translators: example, Lab Directory Settings : Translation for English (UK) / (en_GB) */ $temp = "$lang_name ($lang)"; printf( __('Lab Directory Setting: Translation for %s','lab-directory'), $temp);?></h2>
  
    <p>Lab-Directory allows for using optional custom fields, and one custom group of fields. These fields must be translated here depending of your field usage. </p>
		<table class="widefat fixed striped" cellspacing="0" id="lab_directory_acronyms">
		<thead>
		<tr>
		<th id="columnname" scope="col" style="width:15%;">Custom fields</th>
		<th id="columnname" scope="col" ><?php _e('translation'); echo " : $lang_name"; ?></th>
		<?php if ($lang_name!=$locale_name)  {?>
		<th id="columnname" scope="col" ><?php _e('translation'); echo " : $locale_name"; ?></th>
		<?php } ?>
		</tr>
		</thead>
		<tfoot>
		<tr>
		<th id="columnname" scope="col">Custom fields</th>
		<th id="columnname" scope="col"><?php _e('translation'); echo " : $lang_name"; ?></th>
		<?php if ($lang_name!=$locale_name)  {?>
		<th id="columnname" scope="col"><?php _e('translation'); echo " : $locale_name"; ?></th>
		<?php } ?>
		</tr>
		</tfoot>
		
        <tbody>
		<?php 
		$slugs = Lab_Directory_Settings::get_lab_directory_custom_fields();
		$locale_translations = get_option('lab_directory_translations_' . $locale);
		?>
		<tr>
		<td>[custom_group]
			<input type="hidden" name="lab_directory_translations_slugs[]" 
			value="custom_group"/>
		</td>
		<td><input type="text" name="lab_directory_translations_translations[]" class="input-in-td" 
			value="<?php echo isset($translations['custom_group'])? $translations['custom_group']: ''; ?>"/>
		</td>
		<?php if ($lang_name!=$locale_name)  {?>
		<td>
			<?php echo $locale_translations['custom_group']? $locale_translations['custom_group'] : $translations['custom_group']; ?>
		</td>
		<?php } ?>
		</tr>
		<?php 				
		foreach ($slugs as $slug => $slug_name) { 
		?>
		<tr>
		<td>
			<?php echo '[' . $slug . ']'; ?>
			<input type="hidden" name="lab_directory_translations_slugs[]" 
			value="<?php echo $slug; ?>"/>
		</td>
		<td><input type="text" name="lab_directory_translations_translations[]" class="input-in-td" 
			value="<?php echo isset($translations[$slug])? $translations[$slug] : ''; ?>"/>
		</td>
		<?php if ($lang_name!=$locale_name)  {?>
		<td>
			<?php echo isset($locale_translations[$slug])? $locale_translations[$slug] : $slug_name; ?>
		</td>
		<?php } ?>
		</tr>
			<?php } ?>
        </tbody>
        </table>
	
	<?php  } // end else?>
	<div class="clear"></div>
	<p>
	    <button type="submit" name="admin-settings-translations" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
	</p>
	    <input type="hidden" name="lab_directory_translations_for" value="<?php echo $lang; ?>"/>
	    
	<?php wp_nonce_field('admin-settings-translations'); ?>
</form>
<?php if ($lang  == 'acronyms') { ?>
       <p>For each meta fields values, if an acronym is found, the acronym is displayed and a tooltip is added with an optional link. </p>
       <p> Example without link: 
       <a href="#" data-html="true" data-toggle="tooltip" title="This is the Acronym description used for tooltip" id="info_employment_type" class="helptip">ACRONYM</a></p>
       <p>Example with link: 
       <a href="https://www.ircica.univ-lille.fr/" data-html="true" data-toggle="tooltip" title="Institut de Recherche sur les Composants logiciels et matériels pour l’Information et la Communication Avancée - USR 3380 du CNRS" id="info_employment_type" class="helptip">IRCICA</a>
       </p>
       
<?php } ?>

