
<script type="text/javascript">
function show_group(group) {

    var elems = document.getElementsByClassName("row");
    if (group) { 
        var show_group= 'row-' + group; 
    } else {
    	var show_group= 'row';
    }
    
    for(var i = 0; i != elems.length; ++i)
    {
    	if (elems[i].classList.contains(show_group)) {
    		   elems[i].style.display= 'table-row';
    	    } else {
    	    	elems[i].style.display = 'none';
    	    }
    }
} 
 
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
    <h2><?php
    /* translators: example, Lab Directory Settings : Translation for English (UK) / (en_GB) */ 
    $temp = "$lang_name ($lang)"; 
    printf( __('Lab Directory Setting: Taxonomies translation/customization for %s','lab-directory'), $temp);?></h2>

    <p>Staff can be registerd in categories such as team, laboratory institution... using one or two taxonomies. By default, taxonomy1 corresponds to teams, taxonomy2 corresponds to laboratories  (usefull if your staff belong to several laboratories). Each taxonomy can be customized to refer to others categorization then teams and laboratories.</p>
 	
 	<p>
	    <button type="submit" name="admin-settings-taxonomies" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
    	
    	<?php   
		$taxonomies = Lab_Directory_Common::lab_directory_get_taxonomies(true);
    	$t1 = get_option( 'lab_directory_use_taxonomy1' ) == '1' ? '' : ' ('. __('unactivated', 'lab-directory') . ') ' ; 
    	$t2 = get_option( 'lab_directory_use_taxonomy2' ) == '1' ? '' : ' ('. __('unactivated', 'lab-directory') . ') ' ; 
    	?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_group('taxonomy_1'); return false;"> Taxonomy 1 : <i><?php echo $taxonomies['ld_taxonomy_team']['labels']['name'] . $t1; ?></i></button>
   		&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_group('taxonomy_2'); return false;"> Taxonomy 2 : <i><?php echo $taxonomies['ld_taxonomy_laboratory']['labels']['name'] . $t2; ?></i></button>
     	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_group(); return false;"> <?php _e('All'); ?></button>

	</p>
    


		<table class="widefat fixed striped" cellspacing="0" id="lab_directory_acronyms">
		<thead>
		<tr>
		<th id="columnname" scope="col" style="width:5%;">ID</th>
		<th id="columnname" scope="col" style="width:12%;">Taxonomy  fields </th>
		<th style="width:15%;"><i>Original translation</i></th>
		<th id="columnname" scope="col" ><?php /* Translators: example translation for English (UK) */ printf (__('Translation for %s', 'lab-directory'), $lang_name); ?></th>
		<?php if ($lang_name!=$locale_name)  {?>
			<th id="columnname" scope="col" ><?php printf (__('Translation for %s', 'lab-directory'), $locale_name); ?></th>
		<?php }?>	
		</tr>
		</thead>
		<tfoot>
		<tr>
		<th id="columnname" scope="col">ID</th>
		<th id="columnname" scope="col">Taxonomy  fields </th>
		<th><i>Original translation</i></th>
		<th id="columnname" scope="col"><?php printf (__('Translation for %s', 'lab-directory'), $lang_name); ?></th>
		<?php if ($lang_name!=$locale_name)  {?>
			<th id="columnname" scope="col"><?php printf (__('Translation for %s', 'lab-directory'), $locale_name); ?></th>
		<?php }?>	
		</tr>
		</tfoot>
		
        <tbody>
		<?php 
		$index =0; 
		
		foreach ($taxonomies  as $key => $taxonomie) {
			$index++; 
			$labels = $taxonomie['labels'];

			$locale_translations = get_option('lab_directory_taxonomies_' . $locale);			
			// $ 
			foreach ($labels as $key => $original_translation) { 
			?>
			<tr class ="row  row-taxonomy_<?php echo $index; ?>">
			<td >T <?php echo $index ?></td>
			<td><?php echo $key; ?> </td>
			<td><i><?php echo $original_translation ; ?></i>
				<input type="hidden" name="lab_directory_taxonomies_slugs[]" 
				value="<?php echo $original_translation; ?>"/>
			</td>
			<td><input type="text" name="lab_directory_taxonomies_translations[]" class="input-in-td" 
				value="<?php echo $translations[$original_translation]; ?>"/>
			</td>
			<?php if ($lang_name!=$locale_name)  {?>
				<td>
					<?php echo $locale_translations[$original_translation]? $locale_translations[$original_translation] : $slug_name; ?>
				</td>
			<?php }?>	
			</tr>
			<?php }
		}
		?>
			
        </tbody>
        </table>
	
	<div class="clear"></div>
	<p>
	    <button type="submit" name="admin-settings-taxonomies" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
	</p>
	    <input type="hidden" name="lab_directory_taxonomies_for" value="<?php echo $lang; ?>"/>
	    
	<?php wp_nonce_field('admin-settings-taxonomies'); ?>
</form>


