<script type="text/javascript">
function show_hide_unactivated() {

    var elems = document.getElementsByClassName("unactivated");
   
    for(var i = 0; i != elems.length; ++i)
    {
    	var test = elems[i].style.display;
    
    	   if (test == 'table-row') {
    		   elems[i].style.display= 'none';
    	    } else {
    	    	elems[i].style.display = 'table-row';
    	    }
    }
} 
 
jQuery(document).ready(function($){
	
  });
  
</script>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.select-toggleizer').toggleize();
        })

</script>

<style type="text/css">

  #new-field-template {
    display: none;
  }
 .input-in-td{ width:100%; border:0; padding-left:0; padding-right:0; }

 .widefat td, .widefat th {padding: 2px 10px 2px 10px;}

</style>

<?php wp_enqueue_style('jquery-toggleizer.css',plugins_url( '/css/jquery-toggleizer.css', dirname(__FILE__) ))?>

<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 ">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>
<?php if($did_reset_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 ">
    <p>All meta fields have been reset to their default values. Please press <b>Save</b> to validate this reset.</p>
  </div>
<?php endif; ?>
<form method="post">
    <h2>Custom Details Fields</h2>

    <p>
    This page allows you to set details fields and to create custom details fields for each Staff member. In case a group of fields is disabled, note that corresponding fields can be set but will never be used in the directory. 
    </p>
    <p>  
    In order to use one meta field: this one must be enabled, and the corresponding group must also be activated. 
    </p>
	<p>
    	<input type="submit" name="admin-settings-fields" class="button button-primary button-large" value="Save">
    	<?php if( current_user_can('administrator')): ?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_hide_unactivated(); return false;"> <?php _e('Show or Hide unactivated fields'); ?></button>
    	<?php endif; ?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" name="admin-resettings-fields" class="button button-primary button-large" 
    	 value="Reset" onclick="return confirm('<?php _e('Do you really want to reset all meta fields?  (all previously saved meta fields setting will be lost) .');?>') ;">
    	
  	</p>

    <table class="widefat fixed striped" cellspacing="0" id="lab_directory_staff-meta-fields">
      <thead>
       <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Order <a href="#footnote" title="Note"><sup>(1)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Meta Field Group</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Multivalue</th>
          <?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Fields</th>
          <?php endif; ?>
          <th id="columnname" class="manage-column column-columnname" scope="col"><?php _e('Activated', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Show frontend</th>
          </tr>
      </thead>

      <tfoot>
        <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Order <a href="#footnote" title="Note"><sup>(1)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Meta Field Group</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Multivalue</th>
<?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Fields</th>
          <?php endif; ?>
          <th id="columnname" class="manage-column column-columnname" scope="col"><?php _e('Activated', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Show frontend</th>
        </tr>
      </tfoot>

      <tbody>
        <?php
        	$index = 0;
        	$lab_directory_meta_field_types = Lab_Directory::get_lab_directory_meta_field_types();
        	$lab_directory_meta_field_names = Lab_Directory::get_lab_directory_default_meta_field_names();
        	$lab_directory_group_names = Lab_Directory::get_lab_directory_default_group_names();
        	$lab_directory_multivalues = Lab_Directory::get_lab_directory_multivalues();
        	$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
        	$group_activations = get_option( 'lab_directory_group_activations' ) ;
       	
        	$lab_directory_ldap_attributes = Lab_Directory::get_lab_directory_ldap_attributes();

        	foreach(get_option('lab_directory_staff_meta_fields') as $field): 
        		$index++;
        		$custom = (strpos($field['slug'], 'custom') !== false);
        		
        ?>
          <tr class="column-<?php echo $field['slug']; ?> <?php echo (($field['activated']=='0')? 'unactivated' : ''); ?>" 
          style="<?php echo (($field['activated']=='0')? 'display:none;' : 'display:table-row;'); ?> ">
            <td>
              <input name="lab_directory_staff_meta_fields_orders[<?php echo $index; ?>]" value="<?php echo $index; ?>" style="width: 40px;" />
            </td>
            <td>
              <?php echo $lab_directory_meta_field_names[$field['slug']]; ?>
            </td>
            <td>
              [<?php echo $field['slug']; ?>]
              <input name="lab_directory_staff_meta_fields_slugs[<?php echo $index; ?>]" type="hidden" 
              value="<?php echo $field['slug']; ?>" />
            </td>
            <td>
               	<?php echo create_select('lab_directory_staff_meta_fields_groups[' . $index . ']', 
              		$lab_directory_group_names, $field['group'], 'input-in-td', false, !$custom); 
					echo ($group_activations[$field['group']]? '':' <a href="#footnote" title="Note"><sup>(2)</sup></a>');    
					
               	?>
			</td>
            <td>
              	<?php echo create_select('lab_directory_staff_meta_fields_types[' . $index . ']', 
              		$lab_directory_meta_field_types, $field['type'], 'input-in-td'); ?>
            </td>
            <td>
 				<?php echo create_select('lab_directory_staff_meta_fields_multivalues[' . $index . ']', 
              		$lab_directory_multivalues, $field['multivalue'], 'input-in-td'); ?>
            </td>
            <?php if ($use_ldap) : ?> 
	        <td>
 				<?php echo create_select('lab_directory_staff_meta_fields_ldap_attributes[' . $index . ']', 
              		$lab_directory_ldap_attributes, $field['ldap_attribute'], 'input-in-td', true); ?>
	        </td>
	        <?php endif; ?>
            
            <td>
			    <input name="lab_directory_staff_meta_fields_activateds[<?php echo $index; ?>]" 
			    	id="lab_directory_staff_meta_fields_activateds_<?php echo $index; ?>" 
			    	type="checkbox" value="1" <?php checked( true, $field['activated'] ); ?> />
			    <label for="lab_directory_staff_meta_fields_activateds_<?php echo $index; ?>"></label>
            </td>
            <td>
			<input name="lab_directory_staff_meta_fields_show_frontends[<?php echo $index; ?>]" 
				id="lab_directory_staff_meta_fields_show_frontends_<?php echo $index; ?>" 
				type="checkbox" value="1" <?php checked( true, $field['show_frontend'] ); ?> />
 				<label for="lab_directory_staff_meta_fields_show_frontends_<?php echo $index; ?>"></label>
            </td>
          </tr>
        <?php endforeach; ?>

      </tbody>
    </table>

  <div class="clear"></div>

  <p>
    <input type="submit" name="admin-settings-fields" class="button button-primary button-large" value="Save">
  </p>
  <?php wp_nonce_field('admin-settings-fields', '_wpnonce'); ?>
</form>
<h4 id="footnote">Notes</h4>
<p>(1). If order is set to n, the corresponding fiels will be placed just before or just after the current n-th fields depending of its initial position before or after the n-th field. </p>
<p>(2). This group of field is disabled. Corresponding field(s) will not be used in the directory (even if fields(s) is (are)activated). </p>
