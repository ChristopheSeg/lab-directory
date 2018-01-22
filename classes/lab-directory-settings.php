<?php

class Lab_Directory_Settings {

	public static function shared_instance() {
		static $shared_instance = null;
		if ( $shared_instance === null ) {
			$shared_instance = new static();
		}
		
		return $shared_instance;
	}
	
	//
	// setters
	//

	/*
	 * reset all meta fields to theirs default values
	 */
	public function reset_custom_lab_directory_staff_meta_fields() {
		$meta_fields_array = Lab_Directory::get_default_meta_fields();
		// Sort by activated, then by order
		usort( $meta_fields_array, __NAMESPACE__ . '\compare_order' );
		update_option( 'lab_directory_staff_meta_fields', $meta_fields_array );
		return;
	}

	/*
	 * List all default meta fields, and add unregistered meta fields to $meta_fields_array
	 * This is usefull if the plugin has been upgraded and new fields added to the plugin
	 */
	public function upgrade_custom_lab_directory_staff_meta_fields() {
		$default_meta_fields = Lab_Directory::get_default_meta_fields();
		$meta_fields = get_option( 'lab_directory_staff_meta_fields' );
		$meta_fields_slugs = array();
		foreach ( $meta_fields as $meta_field ) {
			$meta_fields_slugs[$meta_field['slug']] = $meta_field['slug'];
		}
		$upgraded = false;
		
		foreach ( $default_meta_fields as $default_meta_field ) {
			if ( ! in_array( $default_meta_field['slug'], $meta_fields_slugs ) ) {
				$upgraded = true;
				
				// Add a new unactivated meta field with its default values to $meta_fields
				$default_meta_field['activated'] = '0';
				$meta_fields[] = $default_meta_field;
			}
		}
		
		if ( $upgraded ) {
			// sort and save upgraded meta_fields
			usort( $meta_fields, __NAMESPACE__ . '\compare_order' );
			update_option( 'lab_directory_staff_meta_fields', $meta_fields );
			// Renew static variable
			Lab_Directory::initiate_staff_meta_fields();
		}
		return;
	}

	/*
	 * Update meta fields after Form submission
	 */
	public function update_custom_lab_directory_staff_meta_fields() {
		$slugs = $_POST['lab_directory_staff_meta_fields_slugs'];
		$types = $_POST['lab_directory_staff_meta_fields_types'];
		$groups = $_POST['lab_directory_staff_meta_fields_groups'];
		$activateds = $_POST['lab_directory_staff_meta_fields_activateds'];
		$orders = $_POST['lab_directory_staff_meta_fields_orders'];
		$multivalues = $_POST['lab_directory_staff_meta_fields_multivalues'];
		$show_frontends = $_POST['lab_directory_staff_meta_fields_show_frontends'];
		$ldap_attributes = $_POST['lab_directory_staff_meta_fields_ldap_attributes'];
		
		$lab_directory_fixed_types = Lab_Directory_Settings::get_lab_directory_fixed_types();
		$lab_directory_fixed_MV = Lab_Directory_Settings::get_lab_directory_fixed_MV();
		$lab_directory_unsyncable = Lab_Directory_Settings::get_lab_directory_unsyncable();
		 
		
		/*
		// +++++++++++++++
		$temp = array(); 
		$default_meta_fields = Lab_Directory::get_default_meta_fields();
		foreach ( $default_meta_fields as $default_meta_field ) {
			$temp[$default_meta_field['slug']] = $default_meta_field; 
		}
		// +++++++++++++++
		*/
		

		$index = 0;
		
		$meta_fields_array = array();
		
		foreach ( $slugs as $slug ) {
			$index++;
			$fixed_type = (in_array($slug, $lab_directory_fixed_types)? true: false);
			$fixed_MV = (in_array($slug, $lab_directory_fixed_MV)? true: false);
			$unsyncable = (in_array($slug, $lab_directory_unsyncable)? true: false);
			
			if ( ! $unsyncable AND isset( $ldap_attributes[$index] ) ) {
				$calculated_ldap_attribute = $ldap_attributes[$index] == 'none' ? '' : $ldap_attributes[$index];
			} else {
				$calculated_ldap_attribute = '';
			}
			
			$meta_fields_array[] = array( 
				'slug' => $slug, 
				'order' => $orders[$index], 
				'type' => $types[$index], // Hidden field for $fixed_type
				'group' => $groups[$index], 
				'activated' => isset( $activateds[$index] ) ? '1' : '0', 
				'order' => $orders[$index], 
				'multivalue' => $multivalues[$index], // Hidden field for $fixed_MV
				'ldap_attribute' => $calculated_ldap_attribute, 
				'show_frontend' => isset( $show_frontends[$index] ) ? '1' : '0' );		
		}
		
		// sort by activated, then by order
		usort( $meta_fields_array, __NAMESPACE__ . '\compare_order' );
		update_option( 'lab_directory_staff_meta_fields', $meta_fields_array );
		// Update static variable
		Lab_Directory::$staff_meta_fields = $meta_fields_array;
	}
	
	//
	// getters
	//

	public function get_lab_directory_staff_meta_fields() {
		return get_option( 'lab_directory_staff_meta_fields', array() );
	}
	
	//
	// delete functions
	//
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
	 * $search_mail: array: contient éventuellement des emails pour importer une seule fiche (préenregistrement) ou des
	 * fiches (test)
	 * $test: si true (mode test) la variable $form_messages retournera les messages d'erreur
	 * $import: l'import des fiches dans la base SPIP n'est fait que si $import==true
	 *
	 * 5 modes d'appel:
	 * 1- import_annuaire_ldap($filtre, '', true, $import, $form_messages): TEST: test de l'import ET éventuel import de
	 * l'annuaire LDAP pour le filtre sélectionné (filtre au format texte)
	 * 2- import_annuaire_ldap('', $mail, true, $import, $form_messages):TEST: recherche et import d'une fiche LDAP
	 * contenant l'email
	 * -- import_annuaire_ldap($filtre, $mail, true, $import, $form_messages): TEST: idem à cas 2 ($filtre non pris en
	 * compte)
	 * 3- import_annuaire_ldap('', '', true, $import, $form_messages): TEST: test ET éventuel import de tout l'annuaire
	 * LDAP (avec filfre de synchronisation)
	 *
	 * 4- import_annuaire_ldap(): IMPORT: import de tout l'annuaire LDAP (synchronisation)
	 * 5- import_annuaire_ldap('', $mail, ): IMPORT: import d'une fiche de l'annuaire LDAP (préenregistrement)
	 *
	 *
	 * Retour:
	 * l'id_personnel si $search_mail est spécifié et si test==false et
	 * si une fiche a été trouvée dans l'annuaire LDAP
	 * $form_messages si test est à true
	 * aucun pour la synchronisation, import_annuaire_ldap(false, false)
	 * Log:
	 * dans chaque cas le fichier log peut contenir des informations supplémentaires
	 *
	 * N.B. la fonction semble complexe car une seule fonction couvre les besoins de
	 * synchronisation de préenregistrement et de test avec ou sans import
	 *
	 */
	static function import_annuaire_ldap( $search_filter = '', $search_mail = '', $test = false, $import = true, &$form_messages ) {
		$sync_info = date( 'Y-m-d H:i' );
		if ( $search_filter == '' and $search_mail == '' and ! $test and $import ) {
			$sync_info .= ' CRON Synchronisation';
		} elseif ( $test ) {
			$sync_info .= ' TEST:';
			if ( $search_filter == '' and $search_mail == '' ) {
				$sync_info .= ' Synchronisation';
			} elseif ( $search_mail ) {
				$sync_info .= ' Email(s) filter';
			} else {
				$sync_info .= ' Test filter';
			}
			if ( $import ) {
				$sync_info .= ' with import';
			} else {
				$sync_info .= ' without import';
			}
		}
		
		if ( $test ) {
			write_log( 'TEST LDAP', 'Annuaire', _LOG_INFO );
			
			$form_messages['ok'][] = "Debut du TEST IMPORT LDAP<br>\n 
					Filtre de test: $search_filter<br>\n
					Email de test: $search_mail<br>\n";
		} else {
			write_log( 'Lancement du cron MAJ annuaire', 'Annuaire', _LOG_INFO );
		}
		// LDAP server
		$ldap_server = get_option( 'lab_directory_ldap_server' );
		$dn = $ldap_server['ldap_dn'];
		
		if ( ! $dn or ! $ldap_server ) {
			write_log( 'Serveur LDAP mal configuré' );
			write_log( 'Session LDAP annulée!' );
			if ( $test ) {
				$form_messages['erreur'][] = "Veuillez configurer le serveur LDAP avant d'effectuer ce test<br />\n";
				return;
			}
			return;
		}
		
		$synced_meta_fields = Lab_Directory_Settings::get_synced_meta_fields();
		$LDAPattributes = Lab_Directory_Settings::LDAPattributes( $synced_meta_fields );
		
		/*
		 * Calcul du filtre LDAP:
		 */
		$filter = $search_filter; // par défaut
		if ( ! $search_filter ) {
			// Filtrage de synchronisation
			$filter = $ldap_server['ldap_filter'];
			if ( ! $filter ) {
				write_log( 'Filtres LDAP non configuré', 'Annuaire', _LOG_ERREUR );
				write_log( 'Session LDAP terminé!', 'Annuaire', _LOG_INFO );
				if ( $test ) {
					$form_messages['erreur'][] = "Veuillez configurer les filtres LDAP avant d'effectuer ce test<br />\n";
					return;
				}
				return;
			}
			if ( $search_mail ) {
				// Filtre par emails pour extraire une seule fiche ou des fiches!!
				$filtre_mails = array();
				$attributs_mails = array();
				
				if ( $synced_meta_fields['mails']['ldap_attribute'] ) {
					$attributs_mails[] = $synced_meta_fields['mails']['ldap_attribute'];
				}
				if ( $synced_meta_fields['other_mails']['ldap_attribute'] ) {
					$attributs_mails[] = $synced_meta_fields['other_mails']['ldap_attribute'];
				}
				if ( $attributs_mails ) {
					$search_mails = explode( ',', $search_mail );
					foreach ( $attributs_mails as $attribut_mails ) {
						
						foreach ( $search_mails as $mail ) {
							$filtre_mails[] = "($attribut_mails=$mail) ";
						}
					}
					$filter = "(| " . implode( $filtre_mails ) . ")";
				} else {
					$form_messages['erreur'][] = "Veuillez configurer les attributs LDAP [mails] et/ou [other_mails] avant d'effectuer ce test<br />\n";
					return;
				}
			}
		}
		
		/*
		 * Si on appelle la fonction en mode synchronisation:
		 * Ajout d'un filtre du type modifyTimeStamp > 20160614034958Z
		 * Si l'attribut ldap_timestamp existe
		 */
		
		$attribut_ldap_timestamp = $ldap_server['ldap_timestamp_attribute'];
		$current_day = date( 'N' );
		
		/*
		 * Syncing should be doen every day !!
		 * this add timestamp filter at every sync, excepted once a week on sunday
		 * to prevent loosing directory upgrade if syncing is missing on day
		 * Every sunday, a complete sync of alla entries is made
		 */
		if ( ( $current_day != '7' ) and ! $test and ! $search_mail and $attribut_ldap_timestamp ) {
			// synchronisation LDAP
			$last_sync_date = time() - ( 1 * 24 * 60 * 60 ); // j-1
			$filter2 = "($attribut_ldap_timestamp>$last_sync_date)";
			$filter = "(| $filter $filter2)";
		}
		
		$time = $ldap_server['ldap_set_time_limit'];
		if ( ! $test and ! $search_mail ) {
			// synchronisation LDAP
			if ( $time ) {
				// TODO INEFFICACE!! on prolonge la durée du script PHP (en synchronisation totale seulement)
				set_time_limit( $time );
				write_log( "Script allongé : set_time_limit($time); " );
			}
		}
		
		$ds = @ldap_connect( $ldap_server['ldap_server'] );
		if ( ! $ds ) {
			write_log( 'Impossible de se connecter au serveur LDAP' );
			write_log( "Erreur LDAP n° " . ldap_errno( $ds ) . " : " . ldap_error( $ds ) );
			write_log( 'TEST LDAP terminé!' );
			if ( $test ) {
				$form_messages['erreur'][] = "Erreur LDAP n° " . ldap_errno( $ds ) . " : " . ldap_error( $ds ) .
					 "<br />\n";
				return;
			}
			return;
		}
		
		write_log( 'Connexion au serveur LDAP OK' );
		if ( $test ) {
			$form_messages['ok'][] = "Connexion au serveur LDAP OK.";
		}
		
		// search the LDAP users
		$r = @ldap_bind( $ds ); // connexion anonyme, typique
		if ( ! $r ) {
			write_log( 'Liaison (bind) au serveur LDAP Impossible' );
			write_log( 'SESSION LDAP terminé!' );
			ldap_close( $ds );
			if ( $test ) {
				$form_messages['erreur'][] = "Erreur LDAP n° " . ldap_errno( $ds ) . " : " . ldap_error( $ds );
				return;
			}
			return;
		}
		
		write_log( 'Connecté et lié au serveur LDAP' );
		write_log( 'filtre de synchronisation : ' . $filter );
		if ( $test ) {
			$form_messages['ok'][] = "Connecté et lié au serveur LDAP";
			
			if ( $search_mail ) {
				$form_messages['ok'][] = "<b>Test avec filtrage des mails </b>";
			} elseif ( $search_filter ) {
				$form_messages['ok'][] = "<b>Test avec filtre de test </b>";
			} else {
				$form_messages['ok'][] = "<b>Test avec filtre de synchronisation</b>";
			}
			$form_messages['ok'][] = "Filtre: $filter ";
		}
		
		$sr = ldap_search( $ds, $dn, $filter, $LDAPattributes );
		if ( ! $sr ) {
			write_log( 'Impossible de se lier (bind) au serveur LDAP' );
			write_log( 'SESSION  LDAP terminé!' );
			if ( $test ) {
				$form_messages['erreur'][] = "Erreur LDAP n° " . ldap_errno( $ds ) . " : " . ldap_error( $ds ) .
					 "<br />\n filtre=" . $filter . "<br />\n";
				ldap_close( $ds );
				return;
			}
			ldap_close( $ds );
			
			return;
		}
		$entrees_ldap = (array) ldap_get_entries( $ds, $sr );
		$nb_fiches = $entrees_ldap["count"];
		write_log( 'Il y a ' . $nb_fiches . ' entrées dans l\'annuaire LDAP' );
		if ( $test ) {
			$form_messages['ok'][] = '<b>Il y a ' . $nb_fiches . " entrées dans l'annuaire LDAP </b>";
		}
		ldap_close( $ds );
		
		global $wpdb;
		
		$ldap_attribute_name = $LDAPattribute = strtolower( $synced_meta_fields['name']['ldap_attribute'] );
		$ldap_attribute_firstname = $LDAPattribute = strtolower( $synced_meta_fields['firstname']['ldap_attribute'] );
		
		for ( $i = 0; $i < $entrees_ldap["count"]; $i++ ) {
			
			$mails = '';
			$entree_ldap = $entrees_ldap[$i];
			$name = $entree_ldap[$ldap_attribute_name][0][0];
			$firstname = $entree_ldap[$ldap_attribute_firstname][0][0];
			
			// Don't save if $name $firstname empty: resulting from miss-configuration?
			if ( $name and $firstname ) {
				
				// TODO 1 or 2 ??
				$champ_valeurs = array( 'ldap' => 1, 'wp_user_id' => '' );
				
				// retrieve LDAP value foreach synced_metafield
				foreach ( $synced_meta_fields as $active_meta_field ) {
					$LDAPattribute = strtolower( $active_meta_field['ldap_attribute'] );
					$valeurs = array();
					$indexj = - 1;
					
					// tester si l'entrée LDAP existe et si elle est multiple
					if ( isset( $entree_ldap[$LDAPattribute][0] ) ) {
						$nb_entrees = $entree_ldap[$LDAPattribute]['count'];
						
						for ( $j = 0; $j < $nb_entrees; $j++ ) {
							$indexj++;
							$valeurs[$indexj] = str_replace( "'", "&#39;", $entree_ldap[$LDAPattribute][$j] );
							if ( ( $nb_fiches <= 10 ) and ( $test ) ) {
								$form_messages['ok'][] = $active_meta_field['slug'] . ': ' . $LDAPattribute . "[$j]=" .
									 $entree_ldap[$LDAPattribute][$j] . "<br/> \n";
							}
						}
					}
					
					// Serialisation des valeurs
					if ( $indexj >= 0 ) {
						switch ( $active_meta_field['multivalue'] ) {
							case 'SV' : // 'Single valued (only take first value if LDAP fields si multivalued)' ,
								$champ_valeurs[$active_meta_field['slug']] = $valeurs[0];
								break;
							case 'MV' : 
						    	/* Multiple valued (take all values if LDAP fields has multiple values ,
								/* saving MV fields on a single meta imposes imploding value array
								 * (can be annoying if $valeurs contains CR! )
								 */
						    	$champ_valeurs[$active_meta_field['slug']] = implode( "\n", $valeurs );
								break;
							case ',' : // 'Comma separated list' ,
								$champ_valeurs[$active_meta_field['slug']] = $valeurs[0]; // don't explode(',',$valeurs[0]);
								break;
							case ';' : // 'Semicolumn separated list' ,
								$champ_valeurs[$active_meta_field['slug']] = $valeurs[0]; // don't explode(';',$valeurs[0]);
								break;
							case '|' : // '| separated values' ,
							case '/' : // '| separated values' ,
								$champ_valeurs[$active_meta_field['slug']] = $valeurs[0]; // don't explode('|',$valeurs[0]);
								break;
							case 'CR' : // 'Carriage return separated values' ,
								$champ_valeurs[$active_meta_field['slug']] = $valeurs; // don't explode("\n",$valeurs[0]);
								break;
							default : //
								$champ_valeurs[$active_meta_field['slug']] = $valeurs; // don't explode("\n",$valeurs[0]);
								break;
						}
					} else {
						// ! Empty field !
						$champ_valeurs[$active_meta_field['slug']] = '';
					}
				}
				
				$mails = ld_value_to_something( 
					$champ_valeurs['mails'], 
					$synced_meta_fields['mails']['multivalue'], 
					'array' );
				// suppress duplicate email if both email metafields are used
		
				if ($champ_valeurs['other_mails'] AND $mails ) {
					$separator = $synced_meta_fields['other_mails']['multivalue'];
					$separator = ($separator =='CR' OR $separator =='MV')? "\r\n|\n|\r" : $separator;
	
					foreach ( $mails as $mail) {
						// Regexp look like /[ ]*ch\.se@toto1\.fr[ ]*(\n|$)/ (depending on mail and separator)
						$regexp = "/[ ]*" . str_replace('.', '\.', $mail) . "[ ]*($separator|$)/"; 
						$champ_valeurs['other_mails'] = preg_replace($regexp, '', $champ_valeurs['other_mails']);  
						
					}
				}
				
				$prenom_nom = $champ_valeurs['firstname'] . ' ' . $champ_valeurs['name'];
				/*
				 * Recherche de l'existence d'un id pour cette entrée.
				 * Si possible on utilise les mails. et
				 * Si possible on n'utilise pas nom et prénom car il pourraient différer
				 * lors du préenregistrement et dans l'annuaire LDAP (accent...)
				 */
				
				$staff_post_id = false;
				
				// $ldap_attribute_login = strtolower($active_meta_fields['login']['ldap_attribute']);
				// $ldap_attribute_mails = strtolower($active_meta_fields['mails']['ldap_attribute']);
				// $ldap_attribute_other_mails = strtolower($active_meta_fields['other_mails']['ldap_attribute']);
				
				// Recherche de l'id_personnel ($staff_post_id) par login
				$wp_user_id = false;
				$staff_post_id = false;
				if ( $champ_valeurs['login'] ) {
					$wp_user_ids = $wpdb->get_results( 
						"
							SELECT ID
							FROM $wpdb->users
							WHERE user_login =('" . $champ_valeurs['login'] . "')" );
					
					if ( $wp_user_ids ) {
						$wp_user_id = $wp_user_ids[0]->ID;
					}
				}
				
				// TODO mettre cela en function $id=search_for_an_existing_staff_id();
				// +
				// TODO mettre cela en function $id=search_for_an_existing_wp_id();
				// pour partager avec add_new (comment?)
				
				// Search for an existing staff entry (post_id) And an existing user_id
				
				$post_where = array();
				$user_where = array();
				
				if ( ! empty( $mails ) ) {
					foreach ( $mails as $mail ) {
						$post_where[] = "(meta_key = 'mails' AND meta_value = '" . $mail . "')";
						$user_where[] = "'" . $mail . "'";
					}
				}
				
				
				if ( ! empty( $other_mails ) ) {
					foreach ( $other_mails as $mail ) {
						$post_where[] = "(meta_key = 'other_mails' AND meta_value = '" . $mail . "')";
						$user_where[] = "'" . $mail . "'";
					}
				}
				
				if ( ! empty( $post_where ) ) {
					$where = " (" . implode( ' OR ', $post_where ) . ') ';
					
					$post_ids = $wpdb->get_results( 
						"
						SELECT DISTINCT post_id
						FROM $wpdb->postmeta
						LEFT JOIN $wpdb->posts ON ID = post_id 
						WHERE post_status='publish' AND post_type='lab_directory_staff' AND $where " );
				}
				$readytoimport = true;
				
				if ( $post_ids ) {
					if ( count( $post_ids ) == 1 ) {
						$staff_post_id = $post_ids[0]->post_id;
					} elseif ( count( $post_ids ) > 1 ) {
						// Erreur il y a plusieurs enregistrements
						$readytoimport = false;
						if ( $test ) {
							$form_messages['erreur'][] = "Erreur : importation impossible pour cette entrée de l'annuaire car il existe plusieurs 
									enregistrements en base de données pour cet email.";
						}
					}
				}
				
				// Recherche de l'id_personnel (post_id) par email
				if ( ! $wp_user_id and ! empty( $user_where ) ) {
					$where = implode( ' , ', $user_where );
					$wp_user_ids = $wpdb->get_results( 
						"
							SELECT ID
							FROM $wpdb->users
							WHERE user_email IN (" . $where . ")" );
					if ( $wp_user_ids ) {
						$wp_user_id = $wp_user_ids[0]->ID;
						// register wp_user_id in staff profile
						$champ_valeurs['wp_user_id'] = $wp_user_id;
					}
				}
				
				/*
				 * S'il n'y a pas d'email correspondant aux emails de l'entrée ldap on recherche par nom+prénom
				 * Normalement ce cas est quasiment inexistant. Mais cela peut se produire si une si une fiche
				 * locale (non synchronisée) existait sans email mais n'était pas encore dans le LDAP et vient
				 * d'être ajoutée au LDAP
				 */
				
				/*
				 * TODO temporary unactivated
				 *
				 *
				 * if (!$staff_post_id AND $readytoimport) {
				 *
				 * $where = array("nom='$name'", "prenom='$firstname'");
				 * if ($result = sql_select(array('id_personnel'), "spip_personnels", $where))
				 * {
				 * if ($row = sql_fetch($result)) {
				 * $staff_post_id=$row['id_personnel'];
				 * $champ_valeurs[] = ....;
				 * }
				 * }
				 *
				 * }
				 */
				
				$temp = $wp_user_id ? " WP user_id=$wp_user_id " : ' (No link to a WP user)';
				if ( $test ) {
					// Affichage messages avant import qui est optionnel
					if ( $staff_post_id ) {
						$form_messages['ok'][] = "<b>Do : Enr. LDAP n°$i MAJ[id=$staff_post_id] :</b>  " . $temp .
							 $prenom_nom . ' ' . $champ_valeurs['mails'];
					} else {
						$form_messages['ok'][] = "<b>Do : Enr. LDAP n°$i CREATION[] :</b> " . $temp . $prenom_nom . ' ' .
							 $champ_valeurs['mails'];
					}
				}
				
				if ( $import ) {
					
					if ( $staff_post_id ) {
						write_log( 
							"Do : Enr. LDAP n°$i MAJ[id=$staff_post_id] :  $temp $name $firstname ", 
							'Annuaire', 
							_LOG_INFO );
						$success = Lab_Directory_Settings::update_lab_directory_staff( $champ_valeurs, $staff_post_id );
						if ( $success ) {
							$form_messages['ok'][] = "<b>Done Enr. LDAP n°$i MAJ[id=$staff_post_id] :</b>  " . $temp .
								 $champ_valeurs['firstname'] . ' ' . $champ_valeurs['name'] . ' ' .
								 $champ_valeurs['mails'];
						}
					} else {
						write_log( 
							"Do : Enr. LDAP n°$i CREATION[] : " . $temp . $champ_valeurs['firstname'] . ' ' .
								 $champ_valeurs['name'], 
								'Annuaire', 
								_LOG_INFO );
						// la fiche est invalidée par défaut!
						$champ_valeurs['fiche_validee'] = '0';
						// TODO modify register
						$staff_post_id = Lab_Directory_Settings::register_new_staff( $champ_valeurs );
						$success = $staff_post_id !== false;
						if ( $success ) {
							$form_messages['ok'][] = "<b>Done Enr. LDAP n°$i CREATION[] :</b> " . $temp .
								 $champ_valeurs['firstname'] . ' ' . $champ_valeurs['name'] . ' ' .
								 $champ_valeurs['mails'];
						}
					}
					if ( $success AND $champ_valeurs['photo_url']) {
						// Import photo from URL
						$filename = sanitize_file_name( $prenom_nom );
						// Remove old attachement
						if(has_post_thumbnail( $staff_post_id )) {
				        	$attachment_id = get_post_thumbnail_id( $staff_post_id );
				        	wp_delete_attachment($attachment_id, true);
				        }
						
						$image = Lab_Directory::attach_external_image( $champ_valeurs['photo_url'], $staff_post_id, true, $filename, 
							array('post_title' => $prenom_nom));
						if ( ! is_wp_error( $image ) ) {
						$form_messages['ok'][]= ' done import photo de '.$prenom_nom ;
						$form_messages['ok'][]= (string) $temp ;
						// For future use !!
						update_post_meta( $post_id, 'date_photo_updated', time() );
					} else {
						// Process error
						$form_messages['erreur'][]= ' echec import photo de '.$prenom_nom ;
						
					}
		
					}
					if ( ! $success ) {
						write_log( 
							'Erreur  lors de la MAJ ou de la CREATION id_personnel=' . $staff_post_id, 
							'Annuaire', 
							_LOG_ERREUR );
						if ( $test ) {
							$form_messages['erreur'][] = '<b>==> Erreur  lors de la MAJ ou de la CREATION id_personnel=' .
								 $staff_post_id . "</b>";
							return;
						}
						return;
					} else {
						$form_messages['ok'][] = "<b>==> MAJ ou CREATION[] terminée avec succès</b>";
					}
					Lab_Directory_Settings::update_sync_info( $sync_info, $success );
				}
			} else {
				// name and firstname empty !!
				$form_messages['erreur'][] = '<b>==> Error  Ldap entry with empty name or firstname is forbidden. The cause of this error is probably an improper meta fields setting.</b>';
			}
		}
		
		write_log( 'SESSION LDAP terminée!', 'Annuaire', _LOG_INFO );
		
		if ( $test ) {
			$form_messages['ok'][] = 'SESSION LDAP terminée';
		} else {
			return $staff_post_id;
		}
		return;
	}

	function register_new_staff( $champ_valeurs ) {
		$new_lab_directory_staff_array = array( 
			'post_title' => $champ_valeurs['firstname'] . ' ' . $champ_valeurs['name'], 
			'post_content' => '',  // $lab_directory_staff->bio,
			'post_type' => 'lab_directory_staff', 
			'post_status' => 'publish' );
		$success = true;
		$new_lab_directory_staff_post_id = wp_insert_post( $new_lab_directory_staff_array );
		if ( $new_lab_directory_staff_post_id ) {
			foreach ( $champ_valeurs as $key => $value ) {
				$success = $success and update_post_meta( $new_lab_directory_staff_post_id, $key, $value );
			}
			if ( $success )
				return $new_lab_directory_staff_post_id;
		}
		return false;
	}

	function update_lab_directory_staff( $champ_valeurs, $post_id ) {
		$success = true;
		
		if ( $post_id ) {
			foreach ( $champ_valeurs as $key => $value ) {
				$success = $success and update_post_meta( $post_id, $key, $value );
			}
		} else {
			return false;
		}
		return $success;
	}

	function update_sync_info( $sync_info, $success = false ) {
		$lab_directory_ldap_last10syncs = get_option( 
			'lab_directory_ldap_last10syncs', 
			array( 'No sync operation performed up to now' ) );
		if ( count( $lab_directory_ldap_last10syncs ) > 10 ) {
			array_pop( $lab_directory_ldap_last10syncs );
		}
		array_unshift( $lab_directory_ldap_last10syncs, ( $success ? 'SUCCESS ' : 'FAILED  ' ) . $sync_info );
		update_option( 'lab_directory_ldap_last10syncs', $lab_directory_ldap_last10syncs );
	}

	/*
	 * Cette fonction utilise la liste des méta champs et les paramètres de
	 * configuration relatifs aux attributs pour construire un tableau de correspondance
	 * entre ces champs et les attibuts à extraire du type
	 * N.B. la clé est le slug du meta champ
	 * array(
	 * --------
	 * ["name"]=> array(8) {
	 * ["slug"]=> string(4) "name"
	 * ["order"]=> string(1) "2"
	 * ["type"]=> string(4) "text"
	 * ["group"]=> string(2) "CV"
	 * ["activated"]=> string(1) "1"
	 * ["multivalue"]=> string(7) "special"
	 * ["ldap_attribute"]=> string(2) "sn"
	 * ["show_frontend"]=> string(1) "1"
	 * }
	 * ["position"]=> array(8) {
	 * ["slug"]=> string(8) "position"
	 * ["order"]=> string(1) "3"
	 * ["type"]=> string(4) "text"
	 * ["group"]=> string(2) "CV"
	 * ["activated"]=> string(1) "1"
	 * ["multivalue"]=> string(2) "MV"
	 * ["ldap_attribute"]=> string(10) "labeleduri"
	 * ["show_frontend"]=> string(1) "1"
	 * }
	 * -----
	 * }
	 *
	 */
	function get_active_meta_fields() {
		$active_meta_fields = array();
		foreach ( get_option( 'lab_directory_staff_meta_fields' ) as $field ) {
			if ( $field['activated'] ) {
				$active_meta_fields[$field['slug']] = $field;
			}
		}
		return $active_meta_fields;
	}

	function get_synced_meta_fields() {
		$synced_meta_fields = array();
		foreach ( get_option( 'lab_directory_staff_meta_fields' ) as $field ) {
			if ( $field['activated'] AND $field['ldap_attribute']) {
				$synced_meta_fields[$field['slug']] = $field;
			}
		}
		return $synced_meta_fields;
	}
	/*
	 * Cette fonction retourne la liste des attributs LDAP utilisés pour constituer l'annuaire
	 * sous la forme type:
	 * $LDAPattributes=array("modifyTimeStamp","uid","cn","sn","givenname");
	 *
	 */
	function ldapattributes( $active_meta_fields = null ) {
		if ( ! $active_meta_fields ) {
			return false;
		}
		
		$LDAPattributes = array();
		foreach ( $active_meta_fields as $active_meta_field ) {
			if ( $active_meta_field['ldap_attribute']) {
				// Add non empty attribute
				$LDAPattributes[] = $active_meta_field['ldap_attribute'];
			}
		}
		
		// On supprime les éventuels doublons
		$LDAPattributes = array_unique( array_values( $LDAPattributes ) );
		// On ordonne les clés de 0 à N par incrément de 1 sinon ldap-search échoue!!
		$LDAPattributes = array_values( $LDAPattributes );
		return $LDAPattributes;
	}

	/*
	 * Cette fonction retourne la liste des groupes utilisés dans l'affichage des fiches personnelles;
	 */
	function get_used_groups( $active_meta_fields = null, $staff_statuss = null, $bio = false ) {
		if ( ! $active_meta_fields ) {
			return false;
		}
		$group_activations = get_option( 'lab_directory_group_activations' );
		$group_names = Lab_Directory::get_lab_directory_default_group_names();
		$used_groups = array();
		// Always use CV
		$used_groups['CV'] = $group_names['CV'];
		if ( $bio ) {
			$used_groups['BIO'] = $group_names['BIO'];
		}
		// That's all if $staff_statuss is empty
		if ( ! is_array( $staff_statuss ) or empty( $staff_statuss ) ) {
			return $used_groups;
		}
		
		foreach ( $active_meta_fields as $active_meta_field ) {
			$group = $active_meta_field['group'];
			
			if ( $group and ( $group != 'BIO' ) and $staff_statuss[$group] and ! array_key_exists( 
				$group, 
				$used_groups ) ) {
				$used_groups[$group] = $group_names[$group];
			}
		}
		return $used_groups;
	}

	static function get_lab_directory_custom_fields() {
		$custom_fields = array();
		foreach ( Lab_Directory::get_lab_directory_default_meta_field_names() as $key => $key_name ) {
			if ( strpos( $key, 'custom' ) !== false ) {
				$custom_fields[$key] = $key_name;
			}
		}
		return $custom_fields;
	}
	
	/*
	 *  Return an array of all metafields slug corresponding 
	 *  to metafields whose type is fixed  and should not be changed
	 */
	 static function get_lab_directory_fixed_types() {
		return array(
					'firstname',
					'name',
					'login',
					'wp_user_id',
					'mails',
					'other_mails',
					'bio',
					'photo_url',
					'webpage',
					'social_network',
					'hdr_date',
					'hdr_jury',
					'phd_start_date',
					'phd_date',
					'phd_jury',
					'post_doc_start_date',
					'post_doc_end_date',
					'internship_start_date',
					'internship_end_date',
					'studying_level',
					'invitation_start_date',
					'invitation_end_date',
					'cdd_start_date',
					'cdd_end_date',
					'exit_date',
		);
	}
	
	/*
	 *  Return an array of all metafields slug corresponding
	 *  to metafields whose type is fixed  and should not be changed
	 */
	static function get_lab_directory_fixed_MV() {
			return array(
					'firstname',
					'name',
					'login',
					'wp_user_id',
					'bio',
					'photo_url',
					'social_network',
					'hdr_date',
					'hdr_jury',
					'phd_start_date',
					'phd_date',
					'phd_jury',
					'post_doc_start_date',
					'post_doc_end_date',
					'internship_start_date',
					'internship_end_date',
					'studying_level',
					'invitation_start_date',
					'invitation_end_date',
					'cdd_start_date',
					'cdd_end_date',
					'exit_date',
		);
	}	
	/*
	 *  Return an array of all metafields slug corresponding
	 *  to metafields whose type is not syncable with LDAP directory
	 */
	static function get_lab_directory_unsyncable() {
			return array(
					'wp_user_id',
					'social_network',
					'hdr_jury',
					'phd_jury',
					'studying_level',
		);
	}				

} // End class

function compare_order( $a, $b ) {
	return 200 * ( (int) $b['activated'] - (int) $a['activated'] ) + ( (int) $a['order'] - (int) $b['order'] );
}

if ( ! function_exists( 'write_log' ) ) {

	function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

function echo_form_messages( $form_messages = null ) {
	if ( ! $form_messages ) {
		return;
	}
	
	if ( $form_messages['ok'] or $form_messages['form_saved'] ) {
		echo ( '<div id="message" class="updated notice notice-success is-dismissible below-h2 -success-message">' );
		if ( $form_messages['ok'] ) {
			foreach ( $form_messages['ok'] as $message ) {
				echo ( '<p>' . $message . '</p>' );
			}
		} else {
			echo ( '<p>' . __( 'Form saved' ) . '</p>' );
		}
		echo '</div>';
	}
	
	if ( $form_messages['warning'] ) {
		echo ( '<div id="warning" class="notice notice-warning  is-dismissible below-h2 -error-message"' );
		foreach ( $form_messages['warning'] as $message ) {
			echo ( '<p>' . $message . '</p>' );
		}
		echo '</div>';
	}
	
	if ( $form_messages['erreur'] ) {
		echo ( '<div id="error" class="updated error is-dismissible below-h2 -error-message"' );
		foreach ( $form_messages['erreur'] as $message ) {
			echo ( '<p>' . $message . '</p>' );
		}
		echo '</div>';
	}
}


