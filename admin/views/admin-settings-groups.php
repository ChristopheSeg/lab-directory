<style type="text/css">
 .input-in-td{ width:100%; padding-left:0; padding-right:0; }
</style>

<?php echo_form_messages($form_messages); ?>


<form method="post">
Lab Directory 
  <h2><?php echo __('Lab Directory Settings','lab-directory'). ' : '; _e("Meta fields group's",'lab-directory'); ?></h2>
  
  <p>Here you can activate or disable groups of fields. Disabled groups (and corresponding fields) will never appears in the directory. However these disabled fields can remains in some settings page.</p>
	<table class="widefat striped" style="width: auto; table-layout: auto;" cellspacing="0" id="lab_directory_staff-meta-fields">
	<thead>
	<tr>
	<th id="columnname" scope="col" >Group</th>
	<th id="columnname" scope="col" >Activated</th>
	<th id="columnname" scope="col" >Group description</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
	<th id="columnname" scope="col" >Group</th>
	<th id="columnname" scope="col" >Activated</th>
	<th id="columnname" scope="col" >Group description</th>
	</tr>
	</tfoot>
	<tbody>
	<?php foreach ($default_group_names as $key =>$group_name) :?>	
	<tr>
		<td><label for="<?php echo $key; ?>"><?php echo $key; ?></label></td>
		<td><input <?php ($key=='CV'? 'disabled':''); ?> name="activated_<?php echo $key; ?>" type="checkbox" value="1" <?php checked( true, $group_activations[$key] ); ?> /> 
		</td>
		<td><?php echo $group_name; ?></td>
	</tr>
	<?php endforeach; ?>	
	</tbody>
	</table>
  <p>
    <button type="submit" name="admin-settings-groups" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
  </p>
  	<?php wp_nonce_field('admin-settings-groups'); ?>
</form>
