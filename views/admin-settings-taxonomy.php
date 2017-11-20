<?php echo_form_messages($form_messages); ?>


<form method="post">
  <h2>Taxonomy</h2>
  
  <input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

  <div class="clear"></div>

  <p>
    <button type="submit" name="admin-settings-taxonomy" class="button button-primary button-large" value="Save"><?php _e('Save')?></button>
  </p>
  	<?php wp_nonce_field('admin-settings-taxonomy'); ?>
</form>
