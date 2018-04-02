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

 .input-in-td{ width: 100%;
 border:0; padding-left:0; padding-right:0;
 }
 select {
 font-size: inherit; float:right}

 .widefat td, .widefat th {padding: 2px 10px 2px 10px;}
 .toggleizered { display: none!important; }

</style>

<?php 
wp_enqueue_style('jquery-toggleizer.css',LAB_DIRECTORY_URL . '/admin/css/jquery-toggleizer.css');

echo_form_messages($form_messages); 
$lab_directory_group_names = Lab_Directory::get_lab_directory_default_group_names();
$group_activations = get_option( 'lab_directory_group_activations' ) ;
$lab_directory_group_names = Lab_Directory::get_lab_directory_default_group_names();
   
?>
<form method="post">
    <h2><?php echo __('Lab Directory Settings','lab-directory'). ' : '; _e('Custom Fields','lab-directory'); ?></h2>
	<p>
    	<button type="submit" name="admin-settings-fields" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
    	
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_hide_unactivated(); return false;"> <?php _e('Show or Hide unactivated fields'); ?></button>
    	<?php foreach( $lab_directory_group_names as $group =>$name) { 
    	$activated = ($group_activations[$group]? '':' (2)')?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_group('<?php echo $group; ?>'); return false;"> <?php echo $name; echo $activated?></button>
    	<?php } ?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_group(); return false;"> <?php _e('All groups'); ?></button>
    	
    	<?php if( current_user_can('administrator')): ?>
    	&nbsp;&nbsp;&nbsp;&nbsp; <button type="submit" name="admin-settings-fields" class="button button-primary button-large" 
    	 value="Reset" onclick="return confirm('<?php _e('Do you really want to reset all meta fields?  (all previously saved meta fields setting will be lost) .');?>') ;"><?php _e('Reset')?></button>
    	<?php endif; ?>
  	</p>

    <table class="widefat fixed striped" cellspacing="0" id="lab_directory_staff-meta-fields">
      <thead>
       <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col" style="width:5%;">Order <a href="#footnote" title="Note"><sup>(1)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col" style="width:8%;">Meta Field Group</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Field Type 
            <a href="#footnote" title="Note"><sup>(3)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Multivalue
			<a href="#footnote" title="Note"><sup>(4)(6)</sup></a></th>
		  <?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Attribute<a href="#footnote" title="Note"><sup>(5)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col" style="width:2.5%;"><a href="#footnote" title="Note"><sup>(7)</sup></a></th>
          <?php endif; ?>
         <th id="columnname" class="manage-column column-columnname" scope="col" style="width:5%;"><?php _e('Enabled', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col" style="width:5%;">Show in frontend</th>
          </tr>
      </thead>

      <tfoot>
        <tr>
          <th id="columnname" class="manage-column column-columnname" scope="col">Order <a href="#footnote" title="Note"><sup>(1)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Name</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Template Shortcode</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Meta Field Group</th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Field Type 
            <a href="#footnote" title="Note"><sup>(3)</sup></a></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Multivalue
			<a href="#footnote" title="Note"><sup>(4)(6)</sup></a></th>
		  <?php if ($use_ldap) : ?> 
          <th id="columnname" class="manage-column column-columnname" scope="col">LDAP Attribute<a href="#footnote" title="Note"><sup>(5)</sup></a></th>
           <th id="columnname" class="manage-column column-columnname" scope="col"><a href="#footnote" title="Note"><sup>(7)</sup></a></th>
          <?php endif; ?>
          <th id="columnname" class="manage-column column-columnname" scope="col"><?php _e('Enabled', 'lab-directory'); ?></th>
          <th id="columnname" class="manage-column column-columnname" scope="col">Show in frontend</th>
        </tr>
      </tfoot>

      <tbody>
        <?php
        	$index = 0;
        	$lab_directory_meta_field_types = Lab_Directory::get_lab_directory_meta_field_types();
        	$lab_directory_multivalues = Lab_Directory::get_lab_directory_multivalues();
        	$lab_directory_ldap_attributes = Lab_Directory::get_lab_directory_ldap_attributes();
        	$lab_directory_fixed_types = Lab_Directory_Settings::get_lab_directory_fixed_types();
        	$lab_directory_fixed_MV = Lab_Directory_Settings::get_lab_directory_fixed_MV();
       		$lab_directory_unsyncable = Lab_Directory_Settings::get_lab_directory_unsyncable();
       		
        	foreach(Lab_Directory_Common::$staff_meta_fields as $field) : 
        		$index++;
        		$custom = (strpos($field['slug'], 'custom') !== false);
        		$fixed_type = (in_array($field['slug'], $lab_directory_fixed_types)? true: false);
        		$fixed_MV = (in_array($field['slug'], $lab_directory_fixed_MV)? true: false);
        		$unsyncable = (in_array($field['slug'], $lab_directory_unsyncable)? true: false);
        		
        ?>
          <tr class="row row-ld_<?php echo $field['slug']; ?> row-<?php echo $field['group']; ?> <?php echo (($field['activated']=='0')? 'unactivated' : ''); ?>" 
          style="<?php echo (($field['activated']=='0')? 'display:none;' : 'display:table-row;'); ?> ">
            <td>
              <input name="lab_directory_staff_meta_fields_orders[<?php echo $index; ?>]" value="<?php echo $index; ?>" style="width: 40px;" />
            </td>
            <td>
              <?php echo Lab_Directory_Common::$default_meta_field_names[$field['slug']]; ?>
            </td>
            <td>
              [ld_<?php echo $field['slug']; ?>]
              <input name="lab_directory_staff_meta_fields_slugs[<?php echo $index; ?>]" type="hidden" 
              value="<?php echo $field['slug']; ?>" />
            </td>
            <td>
               	<?php echo self::lab_directory_create_select('lab_directory_staff_meta_fields_groups[' . $index . ']', 
              		$lab_directory_group_names, $field['group'], false, 'input-in-td', false, false); 
					echo ($group_activations[$field['group']]? '':' <a href="#footnote" title="Note"><sup>(2)</sup></a>');    
               	?>
			</td>
            <td>
              	<?php 
				if ($fixed_type) {
 					echo '<span class="dashicons dashicons-lock"></span>';
 				}
 				echo self:: lab_directory_create_select('lab_directory_staff_meta_fields_types[' . $index . ']', 
              		$lab_directory_meta_field_types, $field['type'], false, 'input-in-td', false, $fixed_type); ?>
            </td>
            <td>
 				<?php 
 				if ($fixed_MV) {
 					echo '<span class="dashicons dashicons-lock"></span>';
 				}  
				echo self:: lab_directory_create_select('lab_directory_staff_meta_fields_multivalues[' . $index . ']', 
            	$lab_directory_multivalues, $field['multivalue'], false, 'input-in-td',false, $fixed_MV); 
 				?>
             </td>
            <?php if ($use_ldap) : ?> 
	        <td>
 				<?php 
 				if ($unsyncable) {
 					// Nothing to display this ldap field is disabled (unsyncable)
 					echo '<span class="dashicons dashicons-lock"></span>';echo __('Not syncable', 'Lab-Directory');
 				} else { 
 					echo self:: lab_directory_create_select('lab_directory_staff_meta_fields_ldap_attributes[' . $index . ']', 
 							$lab_directory_ldap_attributes, $field['ldap_attribute'], !$fixed_MV, 'input-in-td', __('No syncing'));
 					
 				}
              		
 				?>
	        </td>
	        <td>
	        <?php 
	        if (!$fixed_MV) {
 				echo '<span class="dashicons dashicons-images-alt"></span>';
 			}
	        ?>
            </td>
            <?php endif; ?><td>
			    <input class="toggleizered" name="lab_directory_staff_meta_fields_activateds[<?php echo $index; ?>]" 
			    	id="lab_directory_staff_meta_fields_activateds_<?php echo $index; ?>" 
			    	type="checkbox" value="1" <?php checked( true, $field['activated'] ); ?> />
			    <label for="lab_directory_staff_meta_fields_activateds_<?php echo $index; ?>"></label>
            </td>
            <td>
			<input class="toggleizered" name="lab_directory_staff_meta_fields_show_frontends[<?php echo $index; ?>]" 
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
    <button type="submit" name="admin-settings-fields" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
     	&nbsp;&nbsp;&nbsp;&nbsp; <button onclick="show_hide_unactivated(); return false;"> <?php _e('Show or Hide unactivated fields'); ?></button>
  </p>
  <?php wp_nonce_field('admin-settings-fields', '_wpnonce'); ?>
</form>
<h4 id="footnote">Notes</h4>
<p>(1). If order is set to n, the corresponding fiels will be placed just before or just after the current n-th fields depending of its initial position before or after the n-th field. </p>
<p>(2). This group of fields is disabled. Corresponding fields will not be used in the staff directory (even if some fields(s) is (are) activated). </p>
<p>(3). type column Lock <span class="dashicons dashicons-lock"></span> indicates that corresponding field(s) type cannot be changed. </p>
<p>(4). Multivalue column Lock <span class="dashicons dashicons-lock"></span> indicates that corresponding field(s) has a fixed single or multivalue setting. </p>
<p>(5). LDAP attribute column Lock <span class="dashicons dashicons-lock"></span> indicates that corresponding fields are not syncable with LDAP directory. </p>
<p>(6). Information about Multivalued LDAP fields: metafields can store multivalued data.<br>
<?php 
$notes = Lab_Directory::get_lab_directory_multivalues_names();
foreach ($notes as $key =>$note) {
echo '&nbsp;&nbsp;&nbsp;&nbsp;' . ($lab_directory_multivalues[$key]? $lab_directory_multivalues[$key]: $key) . ' : ' . $note . '<br>';
}?>
</p>
<p>(7). This <span class="dashicons dashicons-images-alt"></span> icon indicates that this metafield can aggregate multiple LDAP fields. 
In that case, this metafield can not be single valued. You must set it to one of the possible multivalued  or separated list option. 
When syncing to LDAP directory, this settings will apply to both selected LDAP attributes. For this reason, it is not possible to aggregate some LDAP attributes having 
different multivalue formats.,  However it remains possible to aggregate one multivalued LDAP attribute with other single valued attributes. 
</p>