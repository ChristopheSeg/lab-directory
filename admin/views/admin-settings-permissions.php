<style type="text/css">
 .input-in-td{ width:100%; padding-left:0; padding-right:0; }
 .widefat td, .widefat th {padding: 2px 10px 2px 10px;}
 .toggleizered { display: none!important; }
</style>
<script type="text/javascript">
function show_hide(elemclass) {

    var elems = document.getElementsByClassName(elemclass);
   
    for(var i = 0; i != elems.length; ++i)
    {
    	var hide = elems[i].style.display =="none";
     
    	   if (hide) {
    		   elems[i].style.display="table";
    	    } else {
    	    	elems[i].style.display="none";
    	    }
    }
} 
 
</script>
<?php 
wp_enqueue_style('jquery-toggleizer.css', LAB_DIRECTORY_URL . '/admin/css/jquery-toggleizer.css');
$portee= array(
		'all' => __('All'),
		'own' => __('Owner'). '<a href="#footnote" title="Note"><sup>(2)</sup></a>',
);



?>

<?php echo_form_messages($form_messages); ?>

<?php 
if ($test_user_id) {
	$user = get_userdata($test_user_id );
}
?>
<form method="post">
<h2><?php echo __('Lab Directory Settings','lab-directory'). ' : '; _e("Permissions",'lab-directory'); ?></h2>
  
  <h2><?php echo __('Permissions settings based on Wordpress groups','lab-directory'); ?><a href="#footnote" title="Note"><sup>(1)</sup></a></h2>
 	<p>
    <button type="submit" name="admin-settings-permissions" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <button onclick="show_hide('wp_permissions_table'); return false;"> <?php _e('Show or Hide Permissions defined for Wordpress groups'); ?></button>

   <?php if (in_array('administrator',  $current_user->roles))  { ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
   	<button type="submit" name="admin-settings-permissions" class="button button-primary button-large" value="Reset" 
    onclick="return confirm('<?php _e('Do you really want to reset all permisions?  (all previously saved permissions will be lost) .', 'lab-directory');?>') ;">
    <?php _e('Reset all permissions to default')?>
    </button>
    &nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Test permisions for :', 'lab-directory');?>
    <?php     wp_dropdown_users( array('show' => 'display_name', 'name' => 'test_user_id', 
   				'show_option_none' => __('none'), 
   				'selected'=> $test_user_id
   		)); 
   
   }?>
    &nbsp;&nbsp;&nbsp;&nbsp;
    

  	</p>
  	<table class="widefat fixed striped wp_permissions_table" cellspacing="0" 
  		style="display: block;" id="lab_directory_staff-meta-fields">
	<thead>
	<tr>
	<?php if ($test_user_id) { ?>
	<th scope="col" >(*)</th>
	<?php } ?>
	<th scope="col" style="width:15%;">Which Wordpress group <br> cand do?</th>
	<th scope="col" ><?php _e('Scope'); ?></th>
	<?php foreach ($all_wp_roles as $role_key => $role) :?>
	<th scope="col" ><?php echo $role['name']; ?></th>
	<?php endforeach; ?>
	</tr>
	</thead>
	<tfoot>
	<tr>
	<?php if ($test_user_id) { ?>
	<th scope="col" >(*)</th>
	<?php } ?>
	<th scope="col" >Which Wordpress group <br> cand do?</th>
	<th scope="col" ><?php _e('Scope'); ?></th>
	<?php foreach ($all_wp_roles as $role_key => $role) :?>
	<th scope="col" ><?php echo $role['name']; ?></th>
	<?php endforeach; ?>
	</tfoot>
	<tbody>
	<?php foreach (Lab_Directory::$capabilities as $capability_key => $capability) :?>	
	<tr>
		<?php if ($test_user_id) { ?>
		<td scope="col" >
			<?php echo ld_user_can($capability_key, $test_user_id)? 
			'<i class="fa fa-check-square" aria-hidden="true"></i>': 
			'<i class="fa fa-square-o" aria-hidden="true"></i>'; ?>
		</td>
		<?php } ?>
		<td><?php echo $capability['name']; ?></td>
		<td><?php echo $portee[$capability['scope']]; ?></td>
		<?php foreach ($all_wp_roles as $role_key => $role['name']) :
		$name = 'wp_' . $role_key. '_'. $capability_key;
		$can = $ld_permissions[$name]; 
		?>
		<td>
			<input class="toggleizered" name="<?php echo $name;?>" id="<?php echo $name;?>" 
				type="checkbox" value="1" <?php checked( true, $can); ?> />
			<label for="<?php echo $name;?>"></label>
 		</td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>	

	</tbody>
	</table>

  <h2><?php _e('Permissions settings based on  Lab Directory groups', 'lab-directory'); ?><a href="#footnote" title="Note"><sup>(3)</sup></a></h2>
	<p>
    <button type="submit" name="admin-settings-permissions" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <button onclick="show_hide('ld_permissions_table'); return false;"> <?php _e('Show or Hide Permissions defined for Lab-Directory status', 'lab-directory'); ?></button>
	</p>
  	<table class="widefat fixed striped ld_permissions_table" cellspacing="0"  
  		style="display: block;" id="lab_directory_staff-meta-fields">
	<thead>
	<tr>
	<?php if ($test_user_id) { ?>
	<th scope="col" >(*)</th>
	<?php } ?>
	<th scope="col" style="width:15%;">Which Lab Directory group <br> cand do?</th>
	<th scope="col" ><?php _e('Scope'); ?></th>
	<?php foreach ($all_ld_roles as $role_key => $role) :?>
	<th scope="col" ><?php echo $role; ?></th>
	<?php endforeach; ?>
	</tr>
	</thead>
	<tfoot>
	<tr>
	<?php if ($test_user_id) { ?>
	<th scope="col" >(*)</th>
	<?php } ?>
	<th scope="col" >Which Lab Directory group <br> cand do?</th>
	<th scope="col" ><?php _e('Scope'); ?></th>
	<?php foreach ($all_ld_roles as $role_key => $role) :?>
	<th scope="col" ><?php echo $role; ?></th>
	<?php endforeach; ?>
	</tfoot>
	<tbody>
	<?php foreach (Lab_Directory::$capabilities as $capability_key => $capability) :?>	
	<tr>
		<?php if ($test_user_id) { ?>
		<td scope="col" >
			<?php echo ld_user_can($capability_key, $test_user_id)? 
			'<i class="fa fa-check-square" aria-hidden="true"></i>': 
			'<i class="fa fa-square-o" aria-hidden="true"></i>'; ?>
		</td>
		<?php } ?>
		<td><?php echo $capability['name']; ?></td>
		<td><?php echo $portee[$capability['scope']]; ?></td>
		<?php foreach ($all_ld_roles as $role_key => $role) :
		$name = 'ld_' . $role_key. '_'. $capability_key;
		$can = $ld_permissions[$name]; 
		?>
		<td>
			<input class="toggleizered" name="<?php echo $name;?>" id="<?php echo $name;?>" 
				type="checkbox" value="1" <?php checked( true, $can); ?> />
			<label for="<?php echo $name;?>"></label>
 		</td>
 		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>	

	</tbody>
	</table>
	
	<p>
    <button type="submit" name="admin-settings-permissions" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
	</p>
	<?php if ($test_user_id) {
		echo '<p>(*)';
		printf( __('Current permissions for user %s (checked when permission is granted to this user)', 'lab-directory'), $user->display_name);
		echo '</p>';
	} ?>


  	<?php wp_nonce_field('admin-settings-permissions'); ?>
</form>
<?php
/* note use this comment to set "public function get_lab_directory_default_permissions()"
echo ('<pre>');var_dump($ld_permissions);echo ('</pre>');
*/ ?>

