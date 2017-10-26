<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 lab_directory_staff-success-message">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>

<form method="post">
  <h2>Acronym</h2>
  
  <input name="lab_directory_use_ldap" type="checkbox" value="1" <?php checked( '1', get_option( 'lab_directory_use_ldap' ) ); ?> /> Check this to use LDAP and LDAP sync

  <div class="clear"></div>

  <p>
    <input type="submit" name="admin-settings-acronym" class="button button-primary button-large" value="Save">
  </p>
  	<?php wp_nonce_field('admin-settings-acronym'); ?>
</form>
