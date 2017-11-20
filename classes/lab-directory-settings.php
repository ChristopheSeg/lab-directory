<?php

class Lab_Directory_Settings {
	public static function shared_instance() {
		static $shared_instance = null;
		if ( $shared_instance === null ) {
			$shared_instance = new static();
		}

		return $shared_instance;
	}

	public static function setup_defaults() {
		$lab_directory_staff_settings = Lab_Directory_Settings::shared_instance();

		$current_template_slug = $lab_directory_staff_settings->get_current_default_lab_directory_staff_template();
		if ( $current_template_slug == '' || $current_template_slug == null ) {

			$lab_directory_staff_settings->update_default_lab_directory_staff_template_slug( 'list' );

		} else if ( $current_template_slug == 'custom' || get_option( 'lab_directory_html_template', '' ) != '' ) {

			$templates_array   = array();
			$templates_array[] = array(
				'html' => get_option( 'lab_directory_html_template' ),
				'css'  => get_option( 'lab_directory_css_template' )
			);
			$lab_directory_staff_settings->update_custom_lab_directory_staff_templates( $templates_array );
			$lab_directory_staff_settings->update_default_lab_directory_staff_template_slug( 'custom_1' );

			delete_option( 'lab_directory_html_template' );
			delete_option( 'lab_directory_css_template' );

		}
	}

	#
	# setters
	#

	public function update_default_lab_directory_staff_template_slug( $slug = 'list' ) {
		update_option( 'lab_directory_template_slug', $slug );
	}

	public function update_custom_lab_directory_staff_templates( $templates = array() ) {
		$updated_templates_array = array();
		$index                   = 1;
		foreach ( $templates as $template ) {
			if ( $template['html'] != '' || $template['css'] != '' ) {
                $template['html']          = htmlentities($template['html'], ENT_QUOTES);
                $template['css']           = htmlentities($template['css'], ENT_QUOTES);
				$template['index']         = $index;
				$template['slug']          = 'custom_' . $index;
				$updated_templates_array[] = $template;
				$index ++;
			}
		}
		update_option( 'lab_directory_custom_templates', $updated_templates_array );
	}

	/* 
	 * reset all meta fields to theirs default values 
	 */
	
	public function reset_custom_lab_directory_staff_meta_fields() {
		
		$meta_fields_array = Lab_Directory::get_default_meta_fields(); 
		// Sort by activated, then by order
		usort($meta_fields_array, __NAMESPACE__ . '\compare_order');
		update_option( 'lab_directory_staff_meta_fields', $meta_fields_array );
		return; 
		
	}

	/*
	 * List all default meta fields, and add unregistered meta fields to $meta_fields_array
	 * This is usefull if the plugin has been upgraded and new fields added to the plugin
	 */
	
	public function upgrade_custom_lab_directory_staff_meta_fields() {
		
				$default_meta_fields = Lab_Directory::get_default_meta_fields();
		$meta_fields = get_option( 'lab_directory_staff_meta_fields');
		$meta_fields_slugs = array(); 
		foreach ($meta_fields as $meta_field) {
			$meta_fields_slugs[] = $meta_field['slug'];
		}
		$upgraded = false; 
	
		foreach ($default_meta_fields as $default_meta_field) {
			if( ! in_array($default_meta_field['slug'], $meta_fields_slugs)) {
				$upgraded = true;
				// Add  a new unactivated meta field with its default values to $meta_fields
				$default_meta_field['activated'] = '0'; 
				$meta_fields[] = $default_meta_field;
			}
		}
		if ($upgraded ) {
			// sort and save upgraded meta_fields
			usort($meta_fields, __NAMESPACE__ . '\compare_order');
			update_option( 'lab_directory_staff_meta_fields', $meta_fields );
		}
		return; 
	
	}
	
	/* 
	 * Update meta fileds after Form submission 
	 */
	
	public function update_custom_lab_directory_staff_meta_fields()  {
		
		$slugs = $_POST['lab_directory_staff_meta_fields_slugs'];
		$types = $_POST['lab_directory_staff_meta_fields_types'];
		$groups = $_POST['lab_directory_staff_meta_fields_groups'];
		$activateds = $_POST['lab_directory_staff_meta_fields_activateds'];
		$orders = $_POST['lab_directory_staff_meta_fields_orders'];
		$multivalues = $_POST['lab_directory_staff_meta_fields_multivalues'];
		$show_frontends = $_POST['lab_directory_staff_meta_fields_show_frontends'];
		$ldap_attributes = $_POST['lab_directory_staff_meta_fields_ldap_attributes'];
		
		$index = 0;

		$meta_fields_array = array();

		foreach ( $slugs as $slug ) {
			$index ++;
			$meta_fields_array[] = array(
					'slug' => $slug,
					'order' => $orders[ $index ],
					'type'=> $types[ $index ],
					'group' => $groups[ $index ],
					'activated' => isset($activateds[ $index ])? '1': '0',
					'order' => $orders[ $index ],
					'multivalue' => $multivalues[ $index ],
					'ldap_attribute' => ($ldap_attributes[ $index ]=='none'?  '': $ldap_attributes[ $index ]),
					'show_frontend' => isset($show_frontends[ $index ])? '1': '0',
				);
			
		}
		
		// sort by activated, then by order
		usort($meta_fields_array, __NAMESPACE__ . '\compare_order');
		update_option( 'lab_directory_staff_meta_fields', $meta_fields_array );
		// Update static variable
		Lab_Directory::$staff_meta_fields = $meta_fields_array; 
		
	}
	

	#
	# getters
	#

	
	public function get_current_default_lab_directory_staff_template() {
		$current_template = get_option( 'lab_directory_template_slug' );

		if ( $current_template == '' && get_option( 'lab_directory_html_template' ) != '' ) {
			update_option( 'lab_directory_template_slug', 'custom' );
			$current_template = 'custom';
		} else if ( $current_template == '' ) {
			update_option( 'lab_directory_template_slug', 'list' );
			$current_template = 'list';
		}

		return $current_template;
	}

	public function get_custom_lab_directory_staff_templates() {
		return get_option( 'lab_directory_custom_templates', array() );
	}

	public function get_custom_lab_directory_staff_template_for_slug( $slug = '' ) {
		$templates = $this->get_custom_lab_directory_staff_templates();
		foreach ( $templates as $template ) {
			if ( $template['slug'] == $slug ) {
				return $template;
			}
		}
	}

	public function get_lab_directory_staff_meta_fields() {
		return get_option( 'lab_directory_staff_meta_fields', array() );
	}

	#
	# delete functions
	#

	public function delete_custom_template( $index = null ) {
		if ( $index != null ) {
			$custom_templates = $this->get_custom_lab_directory_staff_templates();
			$new_custom_templates == array();
			foreach ( $custom_templates as $template ) {
				if ( $template['index'] != $index ) {
					$new_custom_templates[] = $template;
				}
			}
			$this->update_custom_lab_directory_staff_templates( $new_custom_templates );
		}
	}
	
	/*
	 * Fonction permettant d'importer OU de tester l'import LDAP
	 * $search_filter: string: filtre LDAP de test éventuel
	 * $search_mail: array: contient éventuellement des emails pour importer une seule fiche (préenregistrement) ou des fiches (test)
	 * $test: si true (mode test) la variable $form_messages retournera les messages d'erreur
	 * $import: l'import des fiches dans la base SPIP n'est fait que si $import==true
	 *
	 * 5 modes d'appel:
	 * 1- import_annuaire_ldap($filtre, '', true, $import, $form_messages): TEST: test de l'import ET éventuel import de l'annuaire LDAP pour le filtre sélectionné (filtre au format texte)
	 * 2- import_annuaire_ldap('', $mail, true, $import, $form_messages):TEST: recherche et import d'une fiche LDAP contenant l'email
	 * -- import_annuaire_ldap($filtre, $mail, true, $import, $form_messages): TEST: idem à cas 2 ($filtre non pris en compte)
	 * 3- import_annuaire_ldap('', '', true, $import, $form_messages): TEST: test ET éventuel import de tout l'annuaire LDAP (avec filfre de synchronisation)
	 *
	 * 4- import_annuaire_ldap(): IMPORT: import de tout l'annuaire LDAP (synchronisation)
	 * 5- import_annuaire_ldap('', $mail, ): IMPORT: import d'une fiche de l'annuaire LDAP (préenregistrement)
	 *
	 *
	 * Retour:
	 *   l'id_personnel si $search_mail est spécifié et si test==false et
	 *   	si une fiche a été trouvée dans l'annuaire LDAP
	 *   $form_messages si test est à true
	 *   aucun pour la synchronisation, import_annuaire_ldap(false, false)
	 * Log:
	 * dans chaque cas le fichier log peut contenir des informations supplémentaires
	 *
	 * N.B. la fonction semble complexe car une seule fonction couvre les besoins de
	 *    synchronisation de préenregistrement et de test avec ou sans import
	 *
	 */
	static function import_annuaire_ldap($search_filter = '', $search_mail='', $test=false, $import = true, &$form_messages) {
	
		$sync_info = date('Y-m-d H:i');
		if ($search_filter == '' AND $search_mail=='' AND !$test AND $import ) {
			$sync_info .= ' CRON Synchronisation';
		} elseif ($test) {
			$sync_info .= ' TEST:';
			if ($search_filter == '' AND $search_mail==''){
				$sync_info .= ' Synchronisation';
			}
			elseif($search_mail){
				$sync_info .= ' Email(s) filter';
			} else{
				$sync_info .= ' Test filter';
			}
			if ($import) {
				$sync_info .= ' with import';
				} else{
					$sync_info .= ' without import';
				}
		}
		
		if ($test) {
			write_log('TEST LDAP', 'Annuaire', _LOG_INFO);
			
			$form_messages['ok'][]= "Debut du TEST IMPORT LDAP<br>\n 
					Filtre de test: $search_filter<br>\n
					Email de test: $search_mail<br>\n";
		}
		else {
			write_log('Lancement du cron MAJ annuaire', 'Annuaire', _LOG_INFO);
		}
		// LDAP server
		$ldap_server = get_option('lab_directory_ldap_server');
		$dn = $ldap_server['ldap_dn']; 

	
		if (!$dn OR !$ldap_server) {
			write_log('Serveur LDAP mal configuré');
			write_log('Session LDAP annulée!');
			if ($test) {
				$form_messages['erreur'][] = "Veuillez configurer le serveur LDAP avant d'effectuer ce test<br />\n";
				return ;
			}
			return;
		}
	
		$active_meta_fields = Lab_Directory_Settings::get_active_meta_fields();
		$LDAPattributes=Lab_Directory_Settings::LDAPattributes($active_meta_fields);

		/*
		 * Calcul du filtre LDAP:
		 */
		$filter = $search_filter; // par défaut
		if (!$search_filter){
			// Filtrage de synchronisation
			$filter = $ldap_server['ldap_filter'];
			if (!$filter) {
				write_log('Filtres LDAP non configuré', 'Annuaire', _LOG_ERREUR);
				write_log('Session LDAP terminé!', 'Annuaire', _LOG_INFO);
				if ($test) {
					$form_messages['erreur'][] = "Veuillez configurer les filtres LDAP avant d'effectuer ce test<br />\n";
					return;
				}
				return;
			}
			if ($search_mail) {
				// Filtre par emails pour extraire une seule fiche ou des fiches!!
				$filtre_mails = array();
				$attributs_mails= array(); 
				
				if ($active_meta_fields['mails']['ldap_attribute']) {
					$attributs_mails[] = $active_meta_fields['mails']['ldap_attribute'];
				}
				if ($active_meta_fields['other_mails']['ldap_attribute']) {
					$attributs_mails[] = $active_meta_fields['other_mails']['ldap_attribute'];
				}
				if ($attributs_mails) {
					$search_mails= explode(',',$search_mail);
					foreach($attributs_mails as $attribut_mails) {
						
						foreach($search_mails as $mail) {
							$filtre_mails[]="($attribut_mails=$mail) ";
						}
					}
					$filter = "(| ".implode($filtre_mails) . ")";
				} else {
					$form_messages['erreur'][] = "Veuillez configurer les attributs LDAP [mails] et/ou [other_mails] avant d'effectuer ce test<br />\n";
					return;
				}
				
				
			}
		}
	
		/*
		 *  Si on appelle la fonction en mode synchronisation:
		 *  Ajout d'un filtre du type modifyTimeStamp > 20160614034958Z
		 *  Si l'attribut ldap_timestamp existe
		 */
	
		$attribut_ldap_timestamp = $ldap_server['ldap_timestamp_attribute'];
		$current_day = date('N');
		
		/* Syncing should be doen every day !! 
		 * this add timestamp filter at every sync, excepted once a week on sunday
		 * to prevent loosing directory upgrade if syncing is missing on day
		 * Every sunday, a complete sync of alla entries is made
		 */ 
		if (($current_day != '7') AND !$test AND !$search_mail AND $attribut_ldap_timestamp) {
			// synchronisation LDAP
			$last_sync_date = time() - (1 * 24 * 60 * 60); // j-1 
			$filter2 = "($attribut_ldap_timestamp>$last_sync_date)";
			$filter = "(| $filter $filter2)";	
		}
		
		$time = $ldap_server['ldap_set_time_limit'];
		if (!$test AND !$search_mail ) {
			// synchronisation LDAP
			if ($time) {
				// TODO INEFFICACE!! on prolonge la durée du script PHP (en synchronisation totale seulement)
				set_time_limit($time);
				write_log("Script allongé : set_time_limit($time); ");
			}
		}
	
		$ds=@ldap_connect($ldap_server['ldap_server']);
		if (!$ds) {
			write_log('Impossible de se connecter au serveur LDAP');
			write_log("Erreur LDAP n° " . ldap_errno($ds) . " : " . ldap_error($ds));
			write_log('TEST LDAP terminé!');
			if ($test) {
				$form_messages['erreur'][] = "Erreur LDAP n° " . ldap_errno($ds) . " : " . ldap_error($ds) . "<br />\n";
				return ;
			}
			return;
		}
	
		write_log('Connexion au serveur LDAP OK');
		if ($test) { $form_messages['ok'][] = "Connexion au serveur LDAP OK.";}
	
		// search the LDAP users
		$r=@ldap_bind($ds);     // connexion anonyme, typique
		if (!$r)
		{
			write_log('Liaison (bind) au serveur LDAP Impossible');
			write_log('SESSION LDAP terminé!');
			ldap_close($ds);
			if ($test) {
				$form_messages['erreur'][] = "Erreur LDAP n° " . ldap_errno($ds) . " : " . ldap_error($ds);
				return;
			}
			return;
		}
		
		write_log('Connecté et lié au serveur LDAP');
		write_log('filtre de synchronisation : '.$filter);
		if ($test) {
			$form_messages['ok'][] = "Connecté et lié au serveur LDAP";
	
			if ($search_mail) {
				$form_messages['ok'][] = "<b>Test avec filtrage des mails </b>";
			} elseif ($search_filter) {
				$form_messages['ok'][] = "<b>Test avec filtre de test </b>";
			} else {
				$form_messages['ok'][] = "<b>Test avec filtre de synchronisation</b>";
			}
			$form_messages['ok'][] = "Filtre: $filter ";
		}
	
		$sr=ldap_search($ds,$dn, $filter,$LDAPattributes);
		if (!$sr)
		{
			write_log('Impossible de se lier (bind) au serveur LDAP');
			write_log('SESSION  LDAP terminé!');
			if ($test) {
				$form_messages['erreur'][] =  "Erreur LDAP n° " . ldap_errno($ds) . " : " . ldap_error($ds) ."<br />\n filtre=". $filter."<br />\n";
				ldap_close($ds);
				return; 
		
			}
			ldap_close($ds);
	
			return;
		}
		$entrees_ldap = (array) ldap_get_entries($ds, $sr);
		$nb_fiches=$entrees_ldap["count"];
		write_log('Il y a ' . $nb_fiches . ' entrées dans l\'annuaire LDAP');
		if ($test){ $form_messages['ok'][] = '<b>Il y a '. $nb_fiches . " entrées dans l'annuaire LDAP </b>";}
		ldap_close($ds);

		global $wpdb;	
		
		//TODO revoir pour les MV CR
		// et pour les mails si MV 
		for ($i=0; $i<$entrees_ldap["count"]; $i++) {
	
			$mails='';
			$entree_ldap=$entrees_ldap[$i];
			
			$nom = $entree_ldap[$active_meta_fields['nom'][0]][0];
			$prenom = $entree_ldap[$active_meta_fields['prenom'][0]][0];
			$mail = $entree_ldap[$active_meta_fields['mails'][0]][0];
			//TODOTODO don't save if $nom $prenom vides !!
	
			// TODO 1 or 2 ?? 
			$champ_valeurs = array('ldap' => 1);
	
			foreach ($active_meta_fields as $active_meta_field )
			{
				$LDAPattribute = strtolower($active_meta_field['ldap_attribute']);
				$valeurs=array();
				$indexj=-1;
				// tester si l'entrée LDAP existe et si elle est multiple
				
				if (isset($entree_ldap[$LDAPattribute][0]) )
				{
					$nb_entrees=$entree_ldap[$LDAPattribute]['count'];
			
					for ($j = 0; $j <$nb_entrees; $j++) {
						$indexj++;
						$valeurs[$indexj] = str_replace("'","&#39;",$entree_ldap[$LDAPattribute][$j]);
						if (($nb_fiches<=10) AND ($test) ) {
							$form_messages['ok'][] = $active_meta_field['slug'] .': ' . $LDAPattribute."[$j]=". $entree_ldap[$LDAPattribute][$j]."<br/> \n";
						}
					}
				}
				// TODOTODO revoir 
				// Serialisation des valeurs
				if ($indexj>=0)
				{
					switch ($active_meta_field['multivalue']) { 
					    case 'SV' : // 'Single valued (only take first value if LDAP fields si multivalued)' ,
					    	$champ_valeurs[$active_meta_field['slug']] = $valeurs[0];
					        break;
					    case 'MV' : // 'Multiple valued (take all values if LDAP fields has multiple values)' ,
					    	$champ_valeurs[$active_meta_field['slug']] = $valeurs;
					        break;
					    case ',' : // 'Comma separated list' ,
					    	$champ_valeurs[$active_meta_field['slug']] = explode(',',$valeurs[0]);
					        break;
					    case ';'  : // 'Semicolumn separated list' ,
					    	$champ_valeurs[$active_meta_field['slug']] = explode(';',$valeurs[0]);
					        break;
					    case '|' : // '| separated values' ,
					    	$champ_valeurs[$active_meta_field['slug']] = explode('|',$valeurs[0]);
					        break;
					    case 'CR' : // 'Carriage return separated values' ,
					    	$champ_valeurs[$active_meta_field['slug']] = explode("\n",$valeurs[0]);
					        break;
					}
				}else {
					// ! Empty field !
					$champ_valeurs[$active_meta_field['slug']] = '';
				}
			}
			
			/*
			 *  Recherche de l'existence d'un id pour cette entrée.
			 *  Si possible on utilise les mails. et
			 *  Si possible on n'utilise pas nom et prénom car il pourraient différer
			 *    lors du préenregistrement et dans l'annuaire LDAP (accent...)
			 */
	
			$staff_post_id=false;
			
			// Recherche de l'id_personnel (post_id) par email
			$where = array(); 
			if ($champ_valeurs['mails']) {
				if (is_array($champ_valeurs['mails'])) {
					foreach ($champ_valeurs['mails'] as $mail) {
						$where[] ="(meta_key = 'mails' AND meta_value = '".$mail."')";
					}
				} else {
					$where[] ="(meta_key = 'mails' AND meta_value = '".$champ_valeurs['mails']."')";
				}
			}
			if ($champ_valeurs['other_mails']) {
				if (is_array($champ_valeurs['other_mails'])) {
					foreach ($champ_valeurs['other_mails'] as $mail) {
						$where[] ="(meta_key = 'other_mails' AND meta_value = '".$mail."')";
					}
				} else {
					$where[] ="(meta_key = 'other_mails' AND meta_value = '".$champ_valeurs['other_mails']."')";
				}
			}
				
			$where = " (". implode(' OR ', $where) .') ';

			$post_ids = $wpdb->get_results(
					"
					SELECT post_id
					FROM $wpdb->postmeta
					WHERE $where "
					);
			
			$readytoimport=true;

			if ($post_ids) {
				if (count($post_ids)==1) {
					$staff_post_id = $post_ids[0]->post_id;
				} elseif  (count($post_ids)>1) {
					// Erreur il y a plusieurs enregistrements
					$readytoimport=false;
					if ($test) {
						$form_messages['erreur'][] = "Erreur : importation impossible pour cette entrée de l'annuaire car il existe plusieurs 
								enregistrements en base de données pour cet email.";
					}
				}
			}
					
			/* S'il n'y a pas d'email correspondant aux emails de l'entrée ldap on recherche par nom+prénom
			 * Normalement ce cas est quasiment inexistant. Mais cela peut se produire si une si une fiche 
			 * locale (non synchronisée) existait sans email mais n'était pas encore dans le LDAP et vient 
			 * d'être ajoutée au LDAP  
			 */
			
			/* TODO temporary unactivated 
			if (!$staff_post_id AND $readytoimport) {
				
				 $where = array("nom='$nom'", "prenom='$prenom'");
				if ($result = sql_select(array('id_personnel'), "spip_personnels", $where))
				{
					if ($row = sql_fetch($result)) {
						$staff_post_id=$row['id_personnel'];
					}
				}

			}
			*/
			if ($test) {
				// Affichage messages avant import qui est optionnel
				if ($staff_post_id) {
					$form_messages['ok'][] = "<b>Enr. n°$i MAJ[id=$staff_post_id] :</b>  " . $champ_valeurs['firstname'] . ' ' . 
							$champ_valeurs['name'] . ' ' . 
							$champ_valeurs['mails'] ;
				} else {
					$form_messages['ok'][] = "<b>Enr. n°$i CREATION[] :</b> " . $champ_valeurs['firstname'] . ' ' . 
							$champ_valeurs['name'] . ' ' . 
							$champ_valeurs['mails'];
				}
			}					
			
			if ($import) {
	
				if ($staff_post_id) {
					write_log("Enr. n°$i MAJ[id=$staff_post_id] : $nom $prenom ", 'Annuaire', _LOG_INFO);
					
					// $success Lab_Directory_Settings::update_lab_directory_staff();
					
					
				} else {
					write_log("Enr. n°$i CREATION[] : " . $champ_valeurs['firstname'] . ' ' . $champ_valeurs['name'] , 'Annuaire', _LOG_INFO);
					// la fiche est invalidée par défaut!
					$champ_valeurs['fiche_validee'] = '0';
					$staff_post_id = Lab_Directory_Settings::register_new_staff($champ_valeurs); 
					$success = $staff_post_id>1;
				}
	
				if (!$success ){
					write_log('Erreur  lors de la MAJ ou de la CREATION id_personnel='.$staff_post_id, 'Annuaire', _LOG_ERREUR);
					if ($test) {
						$form_messages['erreur'][] = '<b>==> Erreur  lors de la MAJ ou de la CREATION id_personnel='.$staff_post_id. "</b>";
						return;
					}
					return;
				} else{
					$form_messages['ok'][] = "<b>==> MAJ ou CREATION[] terminée avec succès</b>";
				}

			}
		}
	
		write_log('SESSION LDAP terminée!', 'Annuaire', _LOG_INFO);
	
		if ($test) {
			$form_messages['ok'][] = 'SESSION LDAP terminée';
		}else {
			return $staff_post_id;
		}
		return;
	}
	
	function register_new_staff($champ_valeurs) {
	
		 $new_lab_directory_staff_array   = array(
		 'post_title'   => $champ_valeurs['firstname'] . ' ' .$champ_valeurs['name'] ,
		 'post_content' => '' , // $lab_directory_staff->bio,
		 'post_type'    => 'lab_directory_staff',
		 'post_status'  => 'publish'
		 );
		 echo 'New staff: <pre>';var_dump($new_lab_directory_staff_array); echo '</pre>';
		 $success=true; 
		 $new_lab_directory_staff_post_id = wp_insert_post( $new_lab_directory_staff_array );
		 if ($new_lab_directory_staff_post_id) {
		 	foreach ($champ_valeurs as $key => $value) {
			 	$success = $success AND update_post_meta( $new_lab_directory_staff_post_id, $key, $value );
			 }
			 if ($success) return $new_lab_directory_staff_post_id;
		 }
		 return false; 	
	}
	
	function update_sync_info ($sync_info, $success = false) {
		$lab_directory_ldap_last10syncs = get_option( 'lab_directory_ldap_last10syncs', array('No sync operation performed up to now') );
		array_unshift($lab_directory_ldap_last10syncs, ($success? 'SUCCESS ':'FAILED  ') . $sync_info);
		if (count($lab_directory_ldap_last10syncs) >10 ) {
			array_pop($lab_directory_ldap_last10syncs);
		}
		update_option( 'lab_directory_ldap_last10syncs', $lab_directory_ldap_last10syncs);
	}


	
	/*
	 *  Cette fonction utilise la liste des méta champs SPIP et les paramètres de
	 *  configuration relatifs aux attributs pour construire un tableau de correspondance
	 *  entre ces champs et les attibuts à extraire  du type
	 *
	 *
	 $meta_fields_array[] = array(
	 'slug' => $slug,
	 'order' => 2, 
				'type'=> $types[ $index ],
	 'activated' => $activateds[ $index ],
	 'order' => $orders[ $index ],
	 );
	
	 *
	 */
	function get_active_meta_fields() {
		$active_meta_fields = array();
		foreach(get_option('lab_directory_staff_meta_fields') as $field) {
			if ($field['activated']) {
				$active_meta_fields[$field['slug']] =  $field;
			}
		}
		return $active_meta_fields;
	}
	
	/*
	 Cette fonction retourne la liste des attributs LDAP utilisées pour constituer l'annuaire
	 sous la forme type:
	 $LDAPattributes=array("modifyTimeStamp","uid","cn","sn","givenname");
	
	 */
	function ldapattributes($active_meta_fields = null){
	
		if (! $active_meta_fields) {return false; }
	
		$LDAPattributes = array();
		foreach ($active_meta_fields as $active_meta_field)
		{
			if ($active_meta_field['ldap_attribute']) {
				// Add non empty attribute
				$LDAPattributes[] = $active_meta_field['ldap_attribute'];
			}
		}
	
		// On supprime les éventuels doublons
		$LDAPattributes = array_unique(array_values($LDAPattributes));
		// On ordonne les clés  de 0 à N par incrément de 1 sinon ldap-search échoue!!
		$LDAPattributes = array_values($LDAPattributes);
		return $LDAPattributes;
	}

	/*
	 * Cette fonction retourne la liste des groupes utilisés dans l'affichage des fiches personnelles;
	 */
	function get_used_groups($active_meta_fields = null , $staff_statuss=null, $bio=false ){
	
		if (! $active_meta_fields) {return false; }
		$group_activations = get_option( 'lab_directory_group_activations' ) ;
		$group_names = Lab_Directory::get_lab_directory_default_group_names();
		$used_groups = array();
		// Always use CV
		$used_groups['CV']=$group_names['CV'];
		if ($bio) {$used_groups['BIO']=$group_names['BIO'];}

		// That's all if $staff_statuss is empty
		if (! is_array($staff_statuss) OR empty($staff_statuss)) { return $used_groups; }

		foreach ($active_meta_fields as $active_meta_field)
		{
			$group = $active_meta_field['group'];
			
			if ($group AND ($group !='BIO') AND $staff_statuss[$group] AND !array_key_exists ( $group , $used_groups) ) {
				$used_groups[$group] = $group_names[$group];
			}
		}
	
		return $used_groups;
	}

}


function compare_order($a, $b)
{
	return 200* ((int)$b['activated'] - (int)$a['activated']) +
	((int)$a['order']-(int)$b['order']);
}

if (!function_exists('write_log')) {
	function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

function echo_form_messages($form_messages=null) {
	
	if (!$form_messages) {return;}
	
	if($form_messages['ok'] or $form_messages['form_saved']){
		echo ('<div id="message" class="updated notice notice-success is-dismissible below-h2 -success-message">');
		if ($form_messages['ok'] ) {
			foreach ($form_messages['ok'] as $message) {
				echo ('<p>' . $message . '</p>');
		}
			
		} else {
			echo ('<p>' . __('Form saved') . '</p>');
		}
		echo '</div>';
	}
	
	if($form_messages['warning']){
		echo ('<div id="warning" class="notice notice-warning  is-dismissible below-h2 -error-message"' );
			foreach ($form_messages['warning'] as $message) {
				echo ('<p>' . $message . '</p>');
		}
		echo '</div>';
	}
				  
	if($form_messages['erreur']){
		echo ('<div id="error" class="updated error is-dismissible below-h2 -error-message"' );
		foreach ($form_messages['erreur'] as $message) {
			echo ('<p>' . $message . '</p>'); 
		}
		echo '</div>';
	}
				  			
}


