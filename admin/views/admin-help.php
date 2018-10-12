<style type="text/css">
  div.help-topic {
    margin-bottom: 40px;
  }
 ol, ul {padding-left: 20px; list-style:disc;} 
</style>

<div class="help-topic" id="lab_directory_staff-shortcodes">
  <h2>Using Lab-Directory IS as simple as adding ONE shortcode ! </h2>
  <p>
    Single language website <strong>must have one and only one page or post </strong> containing the simple and exact shortcode <code>[lab-directory]</code> (without any parameter). 
    This <i>lab-directory main page</i> will be used to display all information such as staff list in different format, staff profile, staff phd information, defense list...
    This should works whatever permalink structure is used.   
  </p>
  <p>
  	For multiple language website you must provide a translation of <i>lab-directory main page</i> for each language who want to browse the directory. 
  	The language switcher is fully functionnal for Polylang plugin. It has not yet been implemented for other multilingual plugins
  </p>
  <p>Note that simple filtering of staff list can be obtained by adding parameters in the <i>lab-directory main page</i> URL as described below.</p>    
 <hr>  
  <h2>Lab-directory advanced usage</h2>
  <p>
    The <code>[lab-directory ...]</code> shortcode can also be used in a post or page to display a filtered list of staff (or a single staff). 
    This can be usefull to browse the staff list of one team in a specific page of your website. 
    <ul>
    <li>Note that, all staff URL in seach page or post will link to <i>lab-directory main page</i>.</li>
    <li>For that reason, if you only need simple filtering (by team or for a single staff) please consider using the <i>lab-directory main page</i> with additionnal parameters in URL. </li> 
    </ul>  
  </p>

  <p>
    The following parameters are accepted for filtering and templating: 
    <ul>
      <li><code>id</code> - the ID for a single lab_directory_staff member. (Ex: [lab-directory id=4])</li>
      <li>Not yet implemented <code>cat</code> - possibly category IDs or slugs. (Ex: [lab-directory cat=1,4] or [lab-directory cat="administration"])</li>
      <li>Not yet implemented <code>cat_field</code> - used with cat. (Ex: [lab-directory cat="administration" cat_field="slug"])</li>
      <li>Not yet implemented <code>cat_relation</code> - used with cat and cat_field. Possible values are "OR" and "AND". (Ex: [lab-directory cat="administration,corporate" cat_relation="OR"])</li>
      <li><code>orderby</code> - the attribute to use for ordering. Supported values are 'name' and 'ID'. (Ex: [lab-directory orderby=name])</li>
      <li><code>order</code> - the order in which to arrange the lab_directory_staff members. Supported values are 'asc' and 'desc'. (Ex: [lab-directory order=asc])</li>
      <li><code>staff_search</code> - for staff list, when true, add a staff filter above the list. (Ex: [lab-directory staff_search=true])</li>
      <li><code>format_switcher</code> - for staff list, when true, add a switch to comute between the 3 possible formats (grid list trombi). </li>
      <li><code>label</code> - set this parameter to true to display a label on each metafield (excepted staff photo)</li>
      <li><code>template</code> - the slug for the lab_directory_staff template to use:</li>
		  <ul style ="padding-left:15px;">
		  <li><code>[lab-directory id=122 template=staff]</code> - used to display one single staff profile (id should be given)</li>
		  <li><code>[lab-directory id=122 template=staff_phd]</code> - used to display PHD Information for one staff (id should be given)</li>
		  <li><code>[lab-directory id=122 template=staff_hdr]</code> - used to display HDR Information for one staff (id should be given)</li>
		  <li><code>[lab-directory template=staff_grid]</code> or <code> [lab-directory]</code> - used to display a compact grid of staff (default template)</li>
		  <li><code>[lab-directory template=staff_list]</code> - used to display a list (several lines for each staff, full width)</li>
		  <li><code>[lab-directory template=staff_trombi]</code> - used to display a grid of staff photos</li>
		  <li><code>[lab-directory template=defense_list]</code> (1) - used to display a list of HDR and PHD defenses</li>
		  <li>The real name of the template files and style sheet are ld_slug.php and ld_slug.css .</li>
		  <li>Instruction for template's location and customisation can be found in the help section of <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings&tab=templates">Staff Settings</a>.</li>
		  </ul>    
    </ul>
    Note - Ordering options can be viewed here - <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters</a>
  </p>
  <p>
  [lab-directory]</code> shortcode load a template (each template define a list of field to display and corresponding CSS rules) which call one secondary loop (or two secondary loops in case of defense list) to filter staff. 
  </p>
  <ul>
      <li><code>[lab_directory_staff_loop]</code> - this loop extract all staff profile (used by staff staff_list staff_trombi staff_grid templates)</li>
      <li><code>[lab_directory_hdr_loop period=??]</code> (1) - this loop extract staff having a HDR profiles (used in defense list template)</li>
      <li><code>[lab_directory_phd_loop period=??]</code> (1) - this loop extract staff having a PHD profiles (used in defense list template)</li>
		  <ul style ="padding-left:15px;">
		  <li>(1) parameter "period" is only used in defense_list , PHD and HDR loops: <code>period=past</code> <code>period=futur</code> <code>period=all</code> filter for defense date in the past futur or all (default = all).</li>
		  <li>(1) <code>period=futur-60</code> <code>period=futur+5</code> filter for futur defense starting with a delay of -30 or +5 days as compared to current date.</li>
		  <li>(1) defense_list PHD and HDR loops do not use <code>staff_search</code> <code>orderby</code> and <code>order</code>. They are ordered by descending defense date.</li>
		  </ul>    
  </ul>  
 
  <p>
  These secondary loops <code>[lab_directory_xxx_loop]</code> can be used as standalone shortcode in a post or page to display your lab_directory_staff. They use the same parameters as <code>[lab-directory]</code> shortcode. This method allow for a customisation of list of fields to display which be different on page. 
  </p>
  <p>
  However, in such case no template is loaded so that you can provide css for styling and the list of shortcode to display in your post or page as in the example below. Each item of the list is enclosed in a div having classes <code>ld_single_item</code> <code>ld__item</code> . 
  </p>
  <pre style="width: 55%; float:left;">
  Example of page content with CSS<br><code style="white-space:pre-wrap;"><?php echo  htmlentities(
  '<style type ="text/css"> 
hr {margin-top: 5px; margin-bottom: 5px;}
.clearfix { clear: both;}
.ld__item .ld_photo {float: right; margin-right: 15px;}
.ld__item .ld_photo img {max-width: 100px; height: auto;}
.ld__item .ld_name {font-size: 1em; line-height: 1em; margin-bottom: 4px;}
</style>
[lab_directory_hdr_loop period=all]
	[ld_photo]
	[ld_profile_link hdr=true] [ld_hdr_date add_div=false] : [ld_name_firstname add_div=false] [/ld_profile_link]
	[ld_hdr_subject]
	<div class="clearfix"></div><hr>
[/lab_directory_hdr_loop]');
?></code></pre>

  <pre style="width: 40%; float:right;">
  Page content without CSS<br><code style="white-space:pre-wrap;"><?php echo  htmlentities(
  '[lab_directory_hdr_loop period=futur css=defense_list]
	[ld_photo]
	[ld_profile_link content="HDR defense" hdr=true]
	[ld_hdr_subject]
	<div class="clearfix"></div><hr>
[/lab_directory_hdr_loop]');
?></code></pre></div>
<div class="clear"></div>
<p>
<code>[lab_directory_xxx_loop css=slug]</code> loops can also load one template css using parameter <code>css=slug</code> where slug is one template slug (staff_list, staff_trombi...). In that case, each item of the list enclosing div have the additionnal classes <code>ld_slug_item</code> .
</p>


<div class="help-topic" id="lab_directory_staff-template-tags">
  <h2>Lab Directory Loops shortcodes</h2>

  <p>
    Most shortcodes must be contained within one <code>[lab_directory_xxx_loop]</code> shortcode. LAb-Directory  provide a wide list of shotcode : 
  </p>
  <ol>
    <li>Each field, including custom fields have its shortcode (<code>[ld_firstname]</code>) which is given in  <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings&tab=fields">Settings page</a>)</li>
    <li>Some shortcodes agreggate multiple fields content or information (<code>[ld_photo_url]</code> <code>[ld_website_link]</code>) </li>
    <li>Some shortcodes are related to taxonomies used in Lab Directory (<code>[ld_team]</code> <code>[ld_laboratory]</code> ) .</li>
    <li>Some shortcodes can be used outside loops (<code>[ld_teams]</code> <code>[ld_laboratorys]</code> ) .</li>
    <li>Parameter common to all shortcode are presented after this shortcode list.</li>
    <li>Parameter restricted to one shortcode are list under their shortcode.</li>
  </ol>
   
  <h4>1 - Field's shortcodes</h4>
    <ul>
    </ul>
  <h4>2 - Complex shortcodes</h4>
    <ul>
    </ul>
  <h4>3 - Taxonomies shortcodes</h4>
    <ul>
    </ul>
  <h4>4 - Outside loops shortcodes</h4>
    <ul>
    </ul>
  <h4>5 - Shortcode's common parameters</h4>
    <ul>
    </ul>
  <p> 
  Custom Shortcodes are listed in the Custom Details Fields table on the <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings">Staff Settings page</a>. 
  </p>  
  <p>
    Preformatted shortcodes are listed below. There were more options in this list previously, but due to the addition of the Custom Details Fields above some of them were removed from the suggestions. They will still work for now, but deprecated shortcodes are marked below and will no longer work at some point in the future.
  </p>

  <ul>
    <li><code>[ld_photo_url]</code> - the url to the featured image for the lab_directory_staff member</li>
    <li><code>[ld_photo replace_empty=true]</code> - an &lt;img&gt; tag with the featured image for the lab_directory_staff member</li>
        <ul style="text-indent:25px;">
            <li>if replace_empty is given equal true add a nobody photo for non existant photo</li>
        </ul>    <li><code>[ld_name]</code> - the lab_directory_staff member's name</li>
    <li><code>[ld_bio]</code> - the lab_directory_staff member's bio</li>
    <li><code>[ld_team]</code> - the lab_directory_staff member's team (taxonomy 1) category (first category only)</li>
    <li><code>[ld_teams]</code> - all of the lab_directory_staff member's categories in a comma separated values list</li>

    <li><code>[ld_email_link]</code> Staff Email </li>
    <li><code>[ld_website_link]</code> Staff Website</li>

    <li><code>[ld_profile_link]</code> - wrapper or standalone - creates a link to the lab_directory_staff member's profile
        <ul style="text-indent:25px;">
            <li>Used as a wrapper: <code>[ld_profile_link target="_self"] Some Content [/ld_profile_link]</code></li>
            <li>Used standalone: <code>[ld_profile_link inner_text="Some Text" target="_self"]</code></li>
            <li>Notice the 'inner_text' and 'target' attributes. 'inner_text' is only available for standalone profile_link tags, while target is available for either.</li>
        </ul>
    </li>

  </ul>
</div>
<h2>Lab-directory main page filtering</h2>
  <p>
    The <code>[lab-directory]</code> shortcode of <i>lab-directory main page</i> do not contains any parameter. 
    Filtering parameters can be given in the URL of this page. Depending on permalink settings, two case are encoutered: 
  </p>
    <table class="widefat striped" style="width: auto; table-layout: auto;" cellspacing="0">
    <tr>
    <td>URL type</td>
    <td><i>lab-directory main page</i> URL</td>
    <td>URLextension structure</td>
    <td>example</td>
    </tr> 
    <tr>
    <td>Nice URL</td>
    <td>www.mydomain.net/xxx/people/ </td>
    <td>template_url_slug/name</td>
    <td>www.mydomain.net/xxx/people/staff_trombi/myteam<br>www.mydomain.net/xxx/people/photos/myteam</td>
    </tr> 
    <tr>
    <td>Simple URL</td>
    <td>www.mydomain.net/?p=123</td>
    <td>&template_url_slug=name</td>
    <td>www.mydomain.net/?p=123&staff_trombi=name<br>www.mydomain.net/?p=123&photos=name</td>
    </tr> 
    </table>
  <p> Where the couple <i>template_url_slug</i> and <i>name</i> are each optionnal but related to each other:
     
  </p>
    <table class="widefat striped" style="width: auto; table-layout: auto;" cellspacing="0">
    <tr>
    <td><i>template_url_slug</i> value</td>
    <td><i>name</i> value</td>
    </tr> 
    <tr>
    <td>staff<br>staff_hdr<br>staff_phd</td>
    <td>optionnal: the slug of one team or laboratory defined in lab-directory taxonomies settings</td>
    </tr> 
    <tr>
    <td>staff_list<br>staff_trombi<br>staff_grid</td>
    <td>mandatory: the slug of one staff (sanitized version of firstname-name or name-firtsname which can be found by hoovering a staff link)</td>
    </tr> 
    <tr>
    <td>defense_list</td>
    <td>not used</td>
    </tr> 
    </table>
    <p>Additionnally, <i>template_url_slug</i> can be modified in settings to get more significant URL. </p>


