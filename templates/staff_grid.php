<style type="text/css">
  .clearfix {
    clear: both;
  }
  #lab-directory-wrapper{
  	display: inline-block;
    }
   label, input {
    float: right;
    width: auto;
    height: auto;
    padding: 3px;
    margin: 2px 5px;
    font-size: 1em;
    line-height: 1em;
 }
 #filtre_dynamique_saisie {
     float: right;
     width: 160px;

 }
 
   .single_lab_directory_staff {
    float: left;
    width: 160px; 
    padding: 3px;
    margin: 2px 5px;
    bottom: 0;
    display: block;
    white-space: unset;
    word-wrap: break-word;
    background-color: #e9e9e9;;
    border-radius: .25em;
    font-size: 1em;
    line-height: 1em;
  }
  
  .text_surligne {
    color: #000;
    background-color: #fff59b;
}
</style>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/penagwinhighlight.js"></script>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/sansaccent.js"></script>

<script type="text/javascript">

// function based on kitcnrs v5 (GNU GPL licencse) http://www.harmoweb.cnrs.fr
// C. Seguinot: rendre la fonction active sans accent sur le texte recherché 
jQuery(document).ready(function(){
 
jQuery.expr[':'].contains = function(a, i, m) { 
  return jQuery(a).text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0; 
};


//-- Champ de saisie
jQuery("#filtre_dynamique_saisie").keyup(function () {
  
  var saisie = jQuery(this).val();
  
 // highlight
  jQuery("#lab-directory-wrapper").removeHighlight("text_surligne");
  jQuery("#lab-directory-wrapper").highlight(saisie,"text_surligne");
 
  // Filtrage, basé sur http://stackoverflow.com/a/17075148/3177866
  // Split the current value of searchInput
  var data = saisie.split(" ");
  // Create a jquery object of the rows
  var jo = jQuery("#lab-directory-wrapper").find("div.single_lab_directory_staff"); 
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
</script>

<form id="filtre_dynamique">
  <input type="reset" id="filtre_dynamique_effacer" value="Sans filtre" />
  <input type="text" id="filtre_dynamique_saisie" />
  <label for="filtre_dynamique_saisie">Filtrer sur ce texte</label>
</form>
<div id="lab-directory-wrapper">

    [lab_directory_staff_loop]
        <div class="single_lab_directory_staff">
            [ld_profile_link] [ld_name_firstname] [/ld_profile_link]
        </div>
    [/lab_directory_staff_loop]

</div>
ld_category: [ld_category all=true]
