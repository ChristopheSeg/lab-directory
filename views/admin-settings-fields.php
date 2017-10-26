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

<style type="text/css">

  #new-field-template {
    display: none;
  }

</style>

<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 ">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>


<form method="post">
    <h2>Custom Details Fields</h2>

    <p>
      This allows you to create custom details fields for each Staff member.
      Name and bio fields are provided by default, so you don't need to add those here.
    </p>
	<p>
    	<input type="submit" name="admin-settings-fields" class="button button-primary button-large" value="Save">
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_hide_unactivated(); return false;"> <?php _e('Show or Hide unactivated fields'); ?></button>
    	
  	</p>

    <table class="widefat fixed striped" cellspacing="0" id="lab_directory_staff-meta-fields">
      <thead>
       <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Order</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Multivalue</th>
          <?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Fields</th>
          <?php endif; ?>
          <th id="columnname" class="manage-column column-columnname" scope="col"><?php _e('Activated', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Hide frontend</th>
          </tr>
      </thead>

      <tfoot>
        <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Order</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
<?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Fields</th>
          <?php endif; ?>
          <th id="columnname" class="manage-column column-columnname" scope="col"><?php _e('Activated', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Hide frontend</th>
        </tr>
      </tfoot>

      <tbody>
        <?php
        	$index = 0;
        	$lab_directory_meta_field_types = Lab_Directory::get_lab_directory_meta_field_types();
        	$lab_directory_multivalues = Lab_Directory::get_lab_directory_multivalues();
        	foreach(get_option('lab_directory_staff_meta_fields') as $field): 
        		$index++; 
        ?>
          <tr class="column-<?php echo $field['slug']; ?> <?php echo (($field['activated']=='0')? 'unactivated' : ''); ?>" 
          style="<?php echo (($field['activated']=='0')? 'display:none;' : 'display:table-row;'); ?> ">
            <td>
              <input name="lab_directory_staff_meta_fields_orders[]" value="<?php echo $index; ?>" style="width: 40px;" />
            </td>
            <td>
              <?php echo $fields_name[$field['slug']]; ?>
            </td>
            <td>
              [<?php echo $field['slug']; ?>]
              <input name="lab_directory_staff_meta_fields_slugs[]" type="hidden" 
              value="<?php echo $field['slug']; ?>" />
            </td>
            <td>
              	<?php echo create_select('lab_directory_staff_meta_fields_types[]', 
              		$lab_directory_meta_field_types, $field['type']); ?>
            </td>
            <td>
 				<?php echo create_select('lab_directory_staff_meta_fields_types[]', 
              		$lab_directory_multivalues, $field['multivalue']); ?>
            </td>
            <?php if ($use_ldap) : ?> 
	        <td>
	        ldapfields
	        </td>
	        <?php endif; ?>
            
            
            <td>
			    <select name="lab_directory_staff_meta_fields_activateds[]" data-role="flipswitch" data-mini="true">
			        <option value="1" <?php selected( true,  ($field['activated']==true), true); ?> ><?php _e('Yes');?></option>
			        <option value="0" <?php selected( false, ($field['activated']==true), true); ?> ><?php _e('No');?></option>
			    </select>
            </td>
            <td>
              xxxx
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

