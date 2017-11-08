<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 lab_directory_staff-success-message">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>
<?php if($sync_test_result): ?>
  <?php if($sync_test_result['message_ok']): ?>
	  <div id="message" class="updated notice notice-success is-dismissible below-h2 lab_directory_staff-success-message">
	    <p><?php echo $sync_test_result['message_ok']; ?></p>
	  </div>
  <?php endif; ?>
  <?php if($sync_test_result['message_erreur']): ?>
	  <div id="error" class="updated error is-dismissible below-h2 lab_directory_staff-success-message">
	    <p><?php echo $sync_test_result['message_erreur']; ?></p>
	  </div>
  <?php endif; ?>
<?php endif; ?>

<form method="post">
  <h2>Test LDAP Synchronization</h2>
  
  <div class="clear"></div>
	<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="ldap_test_filter">Filtre de test LDAP.</label></th>
			<td>
				<input name="ldap_test_filter" id="ldap_test_filter" value="<?php echo $lab_directory_ldap_test_filter; ?>" class="large-text" type="text">
				<p class="description" id="ldap_test_filter-description">Filtre de test à utiliser pour tester l'extraction LDAP. Ce filtre ne sera pas utilisé pour la synchronisation. Par exemple (| (mailLocalAddress=christophe.Seguinot@univ-lille1.fr) (mailLocalAddress=uneautre.adressse@yourdomain.com) ) </p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ldap_test_email">Filtre par email</label></th>
			<td>
				<input name="ldap_test_email" id="ldap_test_email" value="<?php echo $lab_directory_ldap_test_email; ?>" class="large-text" type="text">
				<p class="description" id="ldap_test_filter-description">Email(s) pour tester l'import (séparés avec virgule) </p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ldap_test_avec_import">test AVEC import</label></th>
			<td>
				<input name="ldap_test_avec_import" type="checkbox" value="1" <?php checked( '1', $lab_directory_ldap_test_avec_import ); ?> /> 
				<p class="description" id="ldap_test_filter-description">Cochez pas cette case pour tester aussi l'importation des fiches LDAP dans l'annuaire (il est recommandé de faire les premiers tests sans import) </p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ldap_test_avec_import">Test sync with: </label></label></th>
			<td>
			    <input type="submit" name="admin-settings-test-sync-with-filter" class="button button-primary button-large" value="Test filter">&nbsp;&nbsp;&nbsp;
			    <input type="submit" name="admin-settings-test-sync-with-email" class="button button-primary button-large" value="Email(s) filter">&nbsp;&nbsp;&nbsp;
			    <input type="submit" name="admin-settings-test-sync-with-sync_filter" class="button button-primary button-large" value="Synchronisation filter">&nbsp;&nbsp;&nbsp;
			    <input type="submit" name="admin-settings-test-sync" class="button button-primary button-large" value="Save">
			</td>
		</tr>
		<tr>
			<th scope="row"></label></th>
			<td></td>
		</tr>	
		</tbody>
	</table>
	
	<hr>
	<h2>Last 10 sync.</h2>
	<ul>
		<?php  foreach($lab_directory_ldap_last10syncs as $sync): 
		echo '<li>' . $sync . '</li>'; 
		endforeach; ?>
	</ul>
  	<?php wp_nonce_field('admin-settings-test-sync'); ?>
</form>
