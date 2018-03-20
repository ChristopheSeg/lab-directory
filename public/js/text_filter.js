
// function based on kitcnrs v5 (GNU GPL licencse) http://www.harmoweb.cnrs.fr
// C. Seguinot: rendre la fonction active sans accent sur le texte recherché 
jQuery(document).ready(function(){
 
jQuery.expr[':'].contains = function(a, i, m) {
  if (jQuery(a).find("div.ld_name").text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0){ return true;} 
  if (jQuery(a).find("div.ld_firstname").text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0){ return true;} 
  if (jQuery(a).find("div.ld_name_firstname").text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0){ return true;}
  // if (jQuery(a).find("div.ld_mails").text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0){ return true;}
  if (jQuery(a).find("div.ld_firstname_name").text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0){ return true;}
  return false; 
};


jQuery.fn.highlight_array = function(pat, classn) {
	/* 
	 Added by C. Seguinot (extend hihlight to text array (space  separated values)
	*/
	var pat_array = pat.split(" ");
	for (var d = 0; d < pat_array.length; ++d) {
		jQuery(this).highlight(pat_array[d], classn);
  }
}
//-- Champ de saisie
jQuery("#filtre_dynamique_saisie").keyup(function () {
  
  var saisie = jQuery(this).val().sansAccent();
  
 // highlight
  var items = jQuery("#lab-directory-wrapper").find("div.ld_single_item")
  items.removeHighlight("text_surligne");
  items.find("div.ld_name").highlight_array(saisie,"text_surligne");
  items.find("div.ld_firstname").highlight_array(saisie,"text_surligne");
  items.find("div.ld_name_firstname").highlight_array(saisie,"text_surligne");
  items.find("div.ld_firstname_name").highlight_array(saisie,"text_surligne");
  // Filtrage, basé sur http://stackoverflow.com/a/17075148/3177866
  // Split the current value of searchInput
  var data = saisie.split(" ");
  // Create a jquery object of the rows
  var jo = jQuery("#lab-directory-wrapper").find("div.ld_single_item"); 
  if (saisie == "") {
    jo.show();// show all rows
    return;
  }
  // Hide all the rows
  jo.hide();
  // Recusively filter the jquery object to show div containing text.
  jo.each(function(){  
    for (var d = 0; d < data.length; ++d) {
      if (jQuery(this).is(":contains('" + data[d] + "')")) {
    	  jQuery(this).show();// Show the rows that match
      }
    }
    
  }); 

  }); // jQuery("#filtre_dynamique_saisie").keyup(function () {...

//----- Bouton "Effacer"
jQuery("#filtre_dynamique_effacer").click(function () {
	jQuery("#lab-directory-wrapper").removeHighlight("text_surligne");
	jQuery("#lab-directory-wrapper").find("div").show();

}); // jQuery("#filtre_dynamique_effacer").click(function () {...
 
}); // jQuery(document).ready(function(){...


/* 
 * Source: http://www.finalclap.com/faq/257-javascript-supprimer-remplacer-accent
 */
String.prototype.sansAccent = function(){
    var accent = [
        /[\300-\306]/g, /[\340-\346]/g, // A, a
        /[\310-\313]/g, /[\350-\353]/g, // E, e
        /[\314-\317]/g, /[\354-\357]/g, // I, i
        /[\322-\330]/g, /[\362-\370]/g, // O, o
        /[\331-\334]/g, /[\371-\374]/g, // U, u
        /[\321]/g, /[\361]/g, // N, n
        /[\307]/g, /[\347]/g, // C, c
    ];
    var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];
     
    var str = this;
    for(var i = 0; i < accent.length; i++){
        str = str.replace(accent[i], noaccent[i]);
    }
     
    return str;
}