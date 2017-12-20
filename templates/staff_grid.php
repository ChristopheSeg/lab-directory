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
<script type="text/javascript" src="/wp-content/plugins/lab-directory/js/text_filter.js"></script>

<script type="text/javascript">
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
ld_categories_nav : [ld_categories_nav all=true]
