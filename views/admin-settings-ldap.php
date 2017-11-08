<style type="text/css">
 .input-in-td{ width:100%; padding-left:0; padding-right:0; }
</style>

<?php if($did_update_options): ?>
  <div id="message" class="updated notice notice-success is-dismissible below-h2 lab_directory_staff-success-message">
    <p>Settings updated.</p>
  </div>
<?php endif; ?>

<form method="post">

  <h2>LDAP servers settings</h2>
  
	<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="ldap_server">Adresse LDAP</label></th>
			<td>
				<input name="ldap_server" id="ldap_server" value="<?php echo $ldap_server['ldap_server']; ?>" class="regular-text" type="text">
				<p class="description" id="ldap_server-description">Adresse du serveur LDAP. par exemple ldap-read.univ-lille1.fr.</p>
			</td>
		</tr><tr>
			<th scope="row"><label for="ldap_dn">LDAP DN </label></th>
			<td>
				<input name="ldap_dn" id="ldap_dn" value="<?php echo $ldap_server['ldap_dn']; ?>" class="regular-text" type="text">
				<p class="description" id="ldap_dn-description">Groupe d'accès (Distinguished Name) au serveur LDAP. Par exemple ou=people,dc=univ-lille1,dc=fr.</p>
			</td>
		</tr><tr>
			<th scope="row"><label for="ldap_attributes">LDAP Atributes</label></th>
			<td>
				<textarea name="ldap_attributes" class="large-text code input-in-td"><?php echo $ldap_server['ldap_attributes']; ?></textarea>
				<p class="description" id="ldap_attributes-description">Liste des attibuts à extraire de l'annuaire LDAP (séparés par des point virgules) par exemple: uid;cn;sn;givenname;ustldepartement;ustldepartementprimaire;ustlequipemailLocalAddress;ustlautremail;jpegphoto;supannaffectation;labeleduri;title;telephonenumber;facsimiletelephonenumber;roomnumber
;eduPersonPrimaryAffiliation;eduPersonnAffiliation</p>
			</td>
		</tr><tr>
			<th scope="row"><label for="ldap_timestamp_attribute">Timestamp Atribute</label></th>
			<td>
				<input name="ldap_timestamp_attribute" class="large-text code input-in-td" value="<?php echo $ldap_server['ldap_timestamp_attribute']; ?>" class="regular-text" type="text">
				<p class="description" id="ldap_timestamp_attribute-description">S'il existe, attributs du type modify timestamp de l'annuaire LDAP (facultatif, servira à limiter les extractions LDAP aux enregistrements modifiés récemment.</p>
			</td>
		</tr><tr>
			<th scope="row"><label for="ldap_filter">Filtre de synchronisation</label></th>
			<td>
				<input name="ldap_filter" id="ldap_filter" value="<?php echo $ldap_server['ldap_filter'];?>" class="large-text" type="text">
				<p class="description" id="ldap_filter-description">Filtre à utiliser pour l'extraction et la synchronisation LDAP par exemple (| (&(supannaffectation=IRCICA*)(mail=*)) )</p>
			</td>
		</tr><tr>
			<th scope="row"><label for="ldap_set_time_limit">Timeout</label></th>
			<td>
				<input name="ldap_set_time_limit" id="ldap_set_time_limit" value="<?php echo $ldap_server['ldap_set_time_limit']; ?>" class="regular-text" type="text">
				<p class="description" id="ldap_set_time_limit-description">La mise à jour lorsqu'elle est trop longue peut provoquer un "Time Out". Ce temps (en seconde) sera ajouté au temps d'exécution du script PHP avec la commande "set_time_limit"
Filtre LDAP de synchronisation.</p>
			</td>
			</tr>
	</tbody>
	</table>
  <p>
    <input type="submit" name="admin-settings-ldap" class="button button-primary button-large" value="Save">
  </p>
  	<?php wp_nonce_field('admin-settings-ldap'); ?>
</form>
