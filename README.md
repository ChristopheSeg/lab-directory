### lab-directory
a Future laboratory Directory plugin for Wordpress

### Do not use this plugin it is in development 

I started this plugin in october 2017. At present time, this repro is only used as backup. 

### Features overview
Lab-directory is a directory plugins that can :
- display individual staff profile and staff list as grid list or staff photo gallery
- manage staff directory (in back-end)
- populate and sync some staff profile from an LDAP directory (optionnal, only one way syncing from LDAP to Lab-Directory)

Lab-directory is primarily designed for Laboratory and University team. It is highly configurable so it that can be used to browse most directory list :
- special group of fields are used for rendering HDR an PHD informations (jury, date, subject...)
- unwanted group of fields can be disabled
- custom fields are available for customization  

Lab-directory require only a little CSS and coding : 
- all Lab-Directory content is included in your default template page or post.
- all displays (list, grid...) can be configured using simple wordpress shortcode (wordpress loop) 

### Lab-Directory Taxonomy (Optional)
Lab-Directory can use up to 2 taxonomies to organise your staff directory. Default taxonomies are Team and Laboratories. They can be set to refer to your custom taxonomies. . 

### Lab-Directory I18n (Internationalisation)
Lab-Directory is ready to be translated in your language. Default language are French and English 
### Lab-Directory Widgets
- Defense List (HDR and PHD)


### Sample Lab-Directory loop code
<code>
<style type="text/css">
  .clearfix {
    clear: both;
  }
  .single-lab_directory_staff {
    margin-bottom: 50px;
  }
  .single-lab_directory_staff .ld_photo {
    float: left;
    margin-right: 15px;
  }
  .single-lab_directory_staff .ld_photo img {
    max-width: 100px;
    height: auto;
  }
  .single-lab_directory_staff .ld_name {
    font-size: 1em;
    line-height: 1em;
    margin-bottom: 4px;
  }
  .single-lab_directory_staff .ld_position {
    font-size: .9em;
    line-height: .9em;
    margin-bottom: 10px;
  }
  .single-lab_directory_staff .ld_bio {
    margin-bottom: 8px;
  }

</style>
<div id="lab-directory-wrapper">
    SINGLE_STAFF.PHP
    [lab_directory_single_staff_loop]
        <div class="single-lab_directory_staff">
                <div class="ld_photo">[ld_photo]</div>
            	<div class="ld_name" >[ld_name]</div> 
				<div class="ld_name" >[ld_position]</div>
                <div class="ld_name" >[ld_mails]</div>
                <div class="ld_name" >[ld_phone_number]</div>
                <div class="ld_name" >[ld_webpage]</div>
                <div class="ld_name" >[ld_bio]</div>
                <div class="ld_name" >[ld_category]</div>
            <div class="clearfix"></div>
        </div>
    [/lab_directory_single_staff_loop]
</div>
</code> 

