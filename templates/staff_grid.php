<style type="text/css">
  .clearfix {
    clear: both;
  }
  #lab-directory-wrapper{
  	display: inline-block;
    }
  .single-lab_directory_staff {
    float: left;
    width: 200px;
    padding: 3px;
    margin: 2px 5px;
    bottom: 0;
    display: block;
    float: left;
    width: 160px; 
    white-space: unset;
    word-wrap: break-word;
    background-color: #e9e9e9;;
    border-radius: .25em;
    font-size: 1em;
    line-height: 1em;
  }
  
</style>
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/penagwinhighlight.js"></script>

<script type="text/javascript">
jQuery(document).ready(function(){

 
// rendre jQuery :contains case insensitive (utilisé dans le code de filtrage) Cf. http://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
// C. Seguinot: rendre la fonction active sans accent sur le texte recherché 
jQuery.expr[':'].contains = function(a, i, m) { 
  return jQuery(a).text().sansAccent().toUpperCase().indexOf(m[3].sansAccent().toUpperCase()) >= 0; 
};


//-- Champ de saisie
jQuery("#filtre_dynamique_saisie").keyup(function () {
  
  var saisie = jQuery(this).val();
  
 // highlight
  jQuery("#content").removeHighlight("spip_surligne");
  jQuery("#content").highlight(saisie,"spip_surligne");
 
  // filtrage, basé sur http://stackoverflow.com/a/17075148/3177866
  //split the current value of searchInput
  var data = saisie.split(" ");
  //create a jquery object of the rows
  var jo = jQuery("#content").find("div.single-lab_directory_staff"); 
  if (saisie == "") {
    jo.show();
    return;
  }
  //hide all the rows
  jo.hide();

  //Recusively filter the jquery object to get results.
  jo.filter(function (i, v) {
    var $t = $(this);
    for (var d = 0; d < data.length; ++d) {
      //pverrier modif ligne ci-dessous pour être case insensitive ; initialement: if ($t.is(":contains('" + data[d] + "')")) {
      if ($t.is(":contains('" + data[d] + "')")) {
        return true;
      }
    }
    return false;
  })
  //show the rows that match.
  .show();


}); // $("#filtre_dynamique_saisie").keyup(function () {...

//----- Bouton "Effacer"
jQuery("#filtre_dynamique_effacer").click(function () {
	jQuery("#content").removeHighlight("spip_surligne");
	jQuery("#content").find("div").show();


}); // $("#filtre_dynamique_effacer").click(function () {...
 
}); // $(document).ready(function(){...
</script>

<form id="filtre_dynamique">
  <label for="filtre_dynamique_saisie">Filtrer sur ce texte</label>
  <input type="text" id="filtre_dynamique_saisie" />
  <input type="reset" id="filtre_dynamique_effacer" value="Réinitialiser" />
</form>
<div id="lab-directory-wrapper">

    [lab_directory_staff_loop]
        <div class="single-lab_directory_staff">
            [ld_profile_link] [ld_name_firstname] [/ld_profile_link]
        </div>
    [/lab_directory_staff_loop]

</div>
ld_category: [ld_category all=true]
