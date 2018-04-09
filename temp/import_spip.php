<h2>Import SPIP IRCICA</h2>
<?php
// Test table spip_personnels
global $wpdb;
$spip_personnels = $wpdb->get_results("SELECT * FROM spip_personnels WHERE 1");
	
if ( ! $spip_personnels ) {
	echo "Il n'y a aucun personel à importer";
	return false; 
}
echo "<br> Importation: ";
$total = 0; 
$sans_fiche_perso=0; 
$nb_wp_users=0; 
$nb_emails_a_importer=0;
$liste_emails_a_importer =array();
$liste_emails_autres =array();
$lab_directory_title_firstname_first = get_option('lab_directory_title_firstname_first');
 
foreach ($spip_personnels as $spip_personnel)  {
 	$total ++; 
 	$title = $lab_directory_title_firstname_first ? 
 			$spip_personnel->prenom . ' ' .$spip_personnel->nom:
 			$spip_personnel->nom . ' ' .$spip_personnel->prenom;
 	
 	$title2= $lab_directory_title_firstname_first ? 
 			$spip_personnel->nom . ' ' .$spip_personnel->prenom:
 			$spip_personnel->prenom . ' ' .$spip_personnel->nom;
 	
 	$mails = my_unserialize($spip_personnel->mails);
 	$wp_staff_id = get_wp_staff_id($title, $title2);
 	
 	if ($wp_staff_id == 'STOP') {
 		echo '<br>' .
 			' ldap='.$spip_personnel->ldap.
 			' staff_id=plusieurs fiches personnelles pour: '.  $title;
 				
 	} else {
 		// $wp_staff_id = id OR false
 		$wp_user_id = get_wp_user_id($spip_personnel->login);
  		echo '<br>' .
	 			' ldap='.$spip_personnel->ldap.
	  			' staff_id='.($wp_staff_id? $wp_staff_id: '--') .
	 			' wp_user_id='.($wp_user_id? $wp_user_id: '--') 
	 			. ' ' .  $title;
	
	 	if (! $wp_staff_id) {
	 		$sans_fiche_perso++;
	 		import_fiche($spip_personnel, $title);
	 		if ($mails[0]) {
	 			$liste_emails_a_importer[] = $mails[0];
	 			$nb_emails_a_importer++;
	 		} else {
	 			$liste_emails_autres[] = $title;
	 		}
	 	} else {
	 		maj_fiche($spip_personnel,$wp_staff_id); 
	 	}
	 	if ($wp_user_id) {$nb_wp_user++;}
 	}	
 	
 } // end foreach personnels
 
 	
 echo "<br><br><b>total=$total sans_fiche_perso=$sans_fiche_perso nb_wp_users=$nb_wp_users</b>";


 echo "<br><br><b>emails à importer ($nb_emails_a_importer):</b> <br>".implode( ",", $liste_emails_a_importer);
 $liste_emails_autres = implode( "\n", $liste_emails_autres);
 echo "<br><br><b>personnel sans email à importer (". (string)($sans_fiche_perso-$nb_emails_a_importer) . "):</b> <br>$liste_emails_autres";
 
 	?>
<h2>FIN Import SPIP IRCICA</h2>
<?php  return true;

function maj_fiche($personnel,$wp_staff_id) {
	echo ' =>MAJ fiche';
	$liste_champs = liste_champs_maj();
	// traitement des champs spéciaux
	
	// statuts 
	$staff_statuss = array(); 
	$staff_statuss['permanent'] = ($personnel->statut_permanent_recherche =='1'); 
	$staff_statuss['administrator'] = ($personnel->statut_administratif =='1'); 
	$staff_statuss['doctorate'] = ($personnel->statut_doctorant =='1'); 
	$staff_statuss['post-doctorate'] = ($personnel->statut_postdoc =='1'); 
	$staff_statuss['internship'] = ($personnel->statut_stagiaire =='1'); 
	$staff_statuss['invited'] = ($personnel->statut_invite =='1'); 
	$staff_statuss['CDD'] = ($personnel->statut_cdd =='1'); 
	$staff_statuss['HDR'] = ($personnel->statut_hdr =='1'); 
	
	$liste_champs['staff_statuss'] = 'staff_statuss';
	$personnel->staff_statuss = $staff_statuss;
	
	// 'other_mails' => 'other_mails',
	if ($personnel->mails) {
		$personnel->mails = my_unserialize($personnel->mails);
	}
	
	if (is_array($personnel->mails)) {
		$personnel->other_mails = array_slice($personnel->mails, 0, 1); 
		if ($personnel->other_mails) {
			$personnel->other_mails = implode("\n",$personnel->other_mails);
		}
	}
	
	// 'mails' => 'mails',
	if (is_array($personnel->mails)) {
		$personnel->mails = array_slice($personnel->mails, 1); 
	} else {
		// $personnel->mails = $personnel->mails;
	}
	
	// 'titre' => 'title',
	$personnel->titre = $personnel->prenom . ' ' .$personnel->nom ;
	
	// 'jury_hdr' => 'hdr_jury',
	if ($personnel->jury_hdr) {	
		$jury = array(); 
		$index =0; 
		foreach (my_unserialize($personnel->jury_hdr) as $membre) {
			$jury[$index] =  array(
				'function' => jury_function($membre['fonction']),
				'name' => $membre['nomprenom'],
				'title' => $membre['titre'],
				); 
			$index++; 
		}
		$personnel->jury_hdr=$jury;
	}
	
	// 'jury_these' => 'phd_jury',
	if ($personnel->jury_these) {
		$jury = array();
		$index =0;
		foreach (my_unserialize($personnel->jury_these) as $membre) {
			$jury[$index] =  array(
				'function' => jury_function($membre['fonction']),
				'name' => $membre['nomprenom'],
				'title' => $membre['titre'],
			);
			$index++;
		}
		$personnel->jury_these=$jury;
	} 
	
	// Equipe 
	$teams= array( 
		/*
		 * 'idspip' => 'nom wp',
		 */
		'11' => 'Photonique',
		'12' => 'CSAM',
		'13' => 'MINT',
		'14' => '2XS',
		'51' => 'Dreampal',
		'53' => 'FOX',
		'101' => 'EMERAUDE',
		'104' => 'Administration', 
		); 

	$team ='--';
	if ($personnel->idequipe) { 
		$team = $teams[$personnel->idequipe];

		if ($wp_team = get_term_by('name', $team, 'ld_taxonomy_team') ) {
			$wp_team_id = $wp_team->term_taxonomy_id;
			wp_set_object_terms( $wp_staff_id, $wp_team_id, 'ld_taxonomy_team' );
			$team= "=> {$wp_team->name}/$wp_team_id";
		}
	}
	
	// Photo
	$photo = maj_photo($personnel->url_photo, $wp_staff_id,$personnel->titre);
	echo ' '. $photo; 
	// Import de tous les champs
	foreach ($liste_champs as $champ_spip => $champ_wp) {
		if ($personnel->$champ_spip=='0000-00-00 00:00:00') {$personnel->$champ_spip='';}
		if ($personnel->$champ_spip instanceof stdClass) {
			$personnel->$champ_spip=json_decode(json_encode($personnel->$champ_spip), true);;
		}
		
		if ($personnel->$champ_spip) {
			
			if ($champ_wp == 'staff_statuss') {
				echo "<br>&nbsp;&nbsp;&nbsp;id=$wp_staff_id,  equipe=$team,  champ=$champ_wp, valeur= array";
				Lab_Directory::update_staff_statuss( $wp_staff_id, $personnel->$champ_spip);
			} else {
				echo "<br>&nbsp;&nbsp;&nbsp;id=$wp_staff_id,  equipe=$team,  champ=$champ_wp, valeur= ". (string)$personnel->$champ_spip;
				update_post_meta( $wp_staff_id, $champ_wp, $personnel->$champ_spip);
			}
			
		}
		
	}
	
}
	

function import_fiche($personnel, $title) {
	echo ' =>IMPORT fiche';
	$wp_staff_id='new';
	// creation fiche, 

	$new_lab_directory_staff_array = array(
		'post_title' => $title, 
		'post_content' => '',  // $lab_directory_staff->bio,
		'post_type' => 'lab_directory_staff',
		'post_status' => 'publish' );
	$wp_staff_id = wp_insert_post( $new_lab_directory_staff_array );
	
	// maj_fiche 
	if ($wp_staff_id) {

		$liste_champs = liste_champs_import();
		// traitement des champs spéciaux
		
		// statuts
		$staff_statuss = array();
		$staff_statuss['permanent'] = ($personnel->statut_permanent_recherche =='1');
		$staff_statuss['administrator'] = ($personnel->statut_administratif =='1');
		$staff_statuss['doctorate'] = ($personnel->statut_doctorant =='1');
		$staff_statuss['post-doctorate'] = ($personnel->statut_postdoc =='1');
		$staff_statuss['internship'] = ($personnel->statut_stagiaire =='1');
		$staff_statuss['invited'] = ($personnel->statut_invite =='1');
		$staff_statuss['CDD'] = ($personnel->statut_cdd =='1');
		$staff_statuss['HDR'] = ($personnel->statut_hdr =='1');
		$liste_champs['staff_statuss'] = 'staff_statuss';
		$personnel->staff_statuss = $staff_statuss;
	   
	    //  function 
		if ($personnel->fonction) {
			$personnel->fonction = implode("\n",my_unserialize($personnel->fonction));
		}
		
		// 'fax' => 'fax_number',
		if ($personnel->fax) {
			$personnel->fax = implode("\n",my_unserialize($personnel->fax));
		}
		// 'telephones' => 'phone_number',
		if ($personnel->telephones) {
			$personnel->telephones = implode("\n",my_unserialize($personnel->telephones));
		}
		// 'other_mails' => 'other_mails',
		if ($personnel->mails) {
			$personnel->mails = my_unserialize($personnel->mails);
		}

		// bureaux
		if ($personnel->bureaux) {
			$personnel->bureaux = implode("/",my_unserialize($personnel->bureaux));
		}
		
		//other_mails CR 
		if (is_array($personnel->mails)) {
			$personnel->other_mails = array_slice($personnel->mails, 0, 1);
			if ($personnel->other_mails) {
				$personnel->other_mails = implode("\n",$personnel->other_mails);
			}
		}
		
		// 'mails' => 'mails',
		if (is_array($personnel->mails)) {
			$personnel->mails = array_slice($personnel->mails, 1);
		} else {
			// $personnel->mails = $personnel->mails;
		}
		
		// 'titre' => 'title',
		$personnel->titre = $personnel->prenom . ' ' .$personnel->nom ;
		
		// 'jury_hdr' => 'hdr_jury',
		if ($personnel->jury_hdr) {
			$jury = array();
			foreach (my_unserialize($personnel->jury_hdr) as $membre) {
				$jury[] =  array(
					'function' => jury_function($membre['fonction']),
					'name' => $membre['nomprenom'],
					'title' => $membre['titre'],
				);
			}
			$personnel->jury_hdr=$jury;
		}
		
		// 'jury_these' => 'phd_jury',
		if ($personnel->jury_these) {
			$jury = array();
			foreach (my_unserialize($personnel->jury_these) as $membre) {
				$jury[] =  array(
					'function' => jury_function($membre['fonction']),
					'name' => $membre['nomprenom'],
					'title' => $membre['titre'],
				);
			}
			$personnel->jury_these=$jury;
		}
		
		// Photo
		$photo = maj_photo($personnel->url_photo, $wp_staff_id,$personnel->titre);
		echo ' '. $photo;
		// Import de tous les champs
		foreach ($liste_champs as $champ_spip => $champ_wp) {
			if ($personnel->$champ_spip=='0000-00-00 00:00:00') {$personnel->$champ_spip='';}
			if ($personnel->$champ_spip) {
				if ($champ_wp == 'staff_statuss') {
					Lab_Directory::update_staff_statuss( $wp_staff_id, $personnel->$champ_spip);
				} else {
					update_post_meta( $wp_staff_id, $champ_wp, $personnel->$champ_spip);
				}
			
			echo "<br>&nbsp;&nbsp;&nbsp;id=$wp_staff_id, champ=$champ_wp, valeur=". $personnel->$champ_spip;
			/* 
			if (is_array($personnel->$champ_spip)) {
				echo "<br>&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp "; var_dump($personnel->$champ_spip); 
			}*/			
			}
		}
		
		
	}	
}

function maj_photo($url_photo='', $wp_staff_id=false, $prenom_nom ){
	if (! $wp_staff_id) return; 
	if (! $url_photo) return; 
	$message = ' No photo';
	
	// Add staff photo
	// Import photo from URL
	$filename = sanitize_file_name( $prenom_nom );
	// Remove old attachement
	if(has_post_thumbnail( $staff_post_id )) {
		$attachment_id = get_post_thumbnail_id( $staff_post_id );
		wp_delete_attachment($attachment_id, true);
	}
	
	$image = Lab_Directory::attach_external_image( $url_photo, $wp_staff_id, true, $filename,
		array('post_title' => $prenom_nom));
	if ( ! is_wp_error( $image ) ) {
		$message= " Photo OK ($url_photo)";
		// For future use !!
		update_post_meta( $wp_staff_id, 'date_photo_updated', time() );
	}
	return $message; 
}

	function my_unserialize($vs) {

	if (substr($vs, 0, 2)=='a:') {
		$vs=unserialize($vs);
	} else {
		$vs=array($vs);
	}
	return $vs;
}
function get_wp_user_id($login) {
	global $wpdb;
	if (!$login) return false; 
	
	$id = $wpdb->get_results(
			"
			SELECT ID
			FROM $wpdb->users
			WHERE user_login ='" . $login . "' " ) ;
	if ( $wpdb->num_rows ==1 ) {
		return $id[0]->ID; 
	}

	return false; 
}
/* 
 * $title the title attributed to this staff
 * $title2 alternative possible title depending on title settings
 */
function get_wp_staff_id($title, $title2) {
	global $wpdb;
	$title = esc_sql( $title );
	$title2 = esc_sql( $title2 );
	/* Search for all possible title in case title settings has been changed recently
	 * do not use AND post_status='publish' to avoid import trashed profile
	*/ 
	$id =$resultats = $wpdb->get_results(
	    $wpdb->prepare(
	        "SELECT ID FROM {$wpdb->posts} WHERE ( post_title ='%s' OR post_title ='%s') 
			AND post_type = 'lab_directory_staff'  " ,
	        $title, $title2  )
	); 
	/*
	$wpdb->get_results(
			"
			SELECT ID
			FROM $wpdb->posts
			WHERE ( post_title ='$title' OR post_title ='$title2') 
			AND post_type = 'lab_directory_staff' " ) ;
	*/
	
	echo "<br>ligne=$wpdb->num_rows ==>SELECT ID	FROM $wpdb->posts WHERE ( post_title ='$title' OR post_title ='$title2')  AND post_type = 'lab_directory_staff' "; 
	if ( $wpdb->num_rows ==1 ) {
		return $id[0]->ID; 
	}
	// TODO if multiple staff profile found (may not occurs)
	if ( $wpdb->num_rows >1 ) {
		return 'STOP';
	}
	// Not yet registered 
	return false; 
}

function liste_champs_maj() {
return array(
	//  'idequipe' => '',
	'date_sortie' => 'exit_date',
	
	'sujet_hdr' => 'hdr_subject',
	'date_hdr' => 'hdr_date',
	'lieu_hdr' => 'hdr_location',
	'resume_hdr' => 'hdr_resume',
	'jury_hdr' => 'hdr_jury',
	
	'debut_these' => 'phd_start_date',
	'sujet_these' => 'phd_subject',
	'date_these'  => 'phd_date',
	'lieu_these' => 'phd_location',
	'resume_these' => 'phd_resume',
	'jury_these' => 'phd_jury',
	
	'debut_post_doc' => 'post_doc_start_date',
	'fin_post_doc' => 'post_doc_end_date',
	'sujet_post_doc' => 'post_doc_subject',
	
	'debut_stage' => 'internship_start_date',
	'fin_stage' => 'internship_end_date',
	'resume_stage' => 'internship_resume',
	'ecole_stage' => 'studying_school',
	'niveau_stage' => 'studying_level',
	
	'debut_invitation' => 'invitation_start_date',
	'fin_invitation' => 'invitation_end_date',
	'objet_invitation' => 'invitation_goal',
	'origine_invitation' => 'invitation_origin',
	'fonction_invitation' => 'invitation_position',
	
	'debut_cdd' => 'cdd_start_date',
	'fin_cdd' => 'cdd_end_date',
	'objet_cdd' => 'cdd_start_goal',
	'fonction_cdd' => 'cdd_start_position',
);
}

function jury_function($fonction) {

	switch ($fonction) {
		case 'garant': 
			$res='guarantor'; 
			break; 
		case 'president': 
			$res='chairman'; 
			break; 
		case 'presidente': 
			$res='chairwoman'; 
			break; 
		case 'directeur': 
			$res='director'; 
			break;  
		case 'directrice': 
			$res='directress'; 
			break;  
		case 'directeurs': 
			$res='directors'; 
			break; 
		case 'rapporteur': 
			$res='referee'; 
			break; 
		case 'rapportrice': 
			$res='referee_f'; 
			break;  
		case 'rapporteurs': 
			$res= 'referees'; 
			break;  
		case 'examinateur' : 
			$res= 'examiner'; 
			break; 
		case 'examinatrice': 
			$res= 'examiner_f'; 
			break; 
		case 'examinateurs' : 
			$res= 'examiners'; 
			break; 
		case 'invite' : 
			$res=  'invited'; 
			break; 
		case 'invitee' : 
			$res= 'invited_f'; 
			break;  
		case 'invites' : 
			$res=  'inviteds'; 
			break; 
		default: 
			$res='';
			break; 
		
		};
 return $res;
}

function liste_champs_import() {

return array(
		//  'idequipe' => '',
		'login' => 'login',
		'nom' => 'name',
		'prenom' => 'firstname',
		'ldap' => 'ldap', // maintenir pour les ldap=0
		// 'ldap_timestamp' => '',
		// 'fiche_validee' => '', // ????

		'other_mails' => 'other_mails',
		'mails' => 'mails',

		'url_photo' => 'photo_url',
		'url_page_perso' => 'webpage',
		'fonction' => 'function',
		'titre' => 'title',
		'telephones' => 'phone_number',
		'fax' => 'fax_number',
		'bureaux' => 'office',
		'equipes' => 'team',
		'idhal' => 'idhal',
		'date_sortie' => 'exit_date',

		'sujet_hdr' => 'hdr_subject',
		'date_hdr' => 'hdr_date',
		'lieu_hdr' => 'hdr_location',
		'resume_hdr' => 'hdr_resume',
		'jury_hdr' => 'hdr_jury',

		'debut_these' => 'phd_start_date',
		'sujet_these' => 'phd_subject',
		'date_these'  => 'phd_date',
		'lieu_these' => 'phd_location',
		'resume_these' => 'phd_resume',
		'jury_these' => 'phd_jury',

		'debut_post_doc' => 'post_doc_start_date',
		'fin_post_doc' => 'post_doc_end_date',
		'sujet_post_doc' => 'post_doc_subject',

		'debut_stage' => 'internship_start_date',
		'fin_stage' => 'internship_end_date',
		'resume_stage' => 'internship_resume',
		'ecole_stage' => 'studying_school',
		'niveau_stage' => 'studying_level',

		'debut_invitation' => 'invitation_start_date',
		'fin_invitation' => 'invitation_end_date',
		'objet_invitation' => 'invitation_goal',
		'origine_invitation' => 'invitation_origin',
		'fonction_invitation' => 'invitation_position',

		'debut_cdd' => 'cdd_start_date',
		'fin_cdd' => 'cdd_end_date',
		'objet_cdd' => 'cdd_start_goal',
		'fonction_cdd' => 'cdd_start_position',
	);
}
