<style type="text/css">
  div.help-topic {
    margin-bottom: 40px;
  }
</style>

<div class="help-topic" id="lab_directory_staff-shortcodes">
  <h2>Shortcodes</h2>

  <p>
    Use the <code>[lab-directory]</code> shortcode in a post or page to display your lab_directory_staff.
  </p>

  <p>
    The following parameters are accepted: (TODO only template working at present)
    <ul>
      <li><code>id</code> - the ID for a single lab_directory_staff member. (Ex: [lab-directory id=4])</li>
      <li><code>cat</code> - possibly category IDs or slugs. (Ex: [lab-directory cat=1,4] or [lab-directory cat="administration"])</li>
      <li><code>cat_field</code> - used with cat. (Ex: [lab-directory cat="administration" cat_field="slug"])</li>
      <li><code>cat_relation</code> - used with cat and cat_field. Possible values are "OR" and "AND". (Ex: [lab-directory cat="administration,corporate" cat_relation="OR"])</li>
      <li><code>orderby</code> - the attribute to use for ordering. Supported values are 'name' and 'ID'. (Ex: [lab-directory orderby=name])</li>
      <li><code>order</code> - the order in which to arrange the lab_directory_staff members. Supported values are 'asc' and 'desc'. (Ex: [lab-directory order=asc])</li>
      <li><code>template</code> - the slug for the lab_directory_staff template to use.</li>
      <li><code>staff_filter</code> - for staff list, when true, add a staff filter above the list. (Ex: [lab-directory staff_filter=true])</li>
      
    </ul>
    Note - Ordering options can be viewed here - <a href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters</a>
  </p>
</div>

<div class="help-topic" id="lab_directory_staff-templates">
    <h2>Lab Directory Single Profile Template</h2>
    <p>
        By default, Lab Directory uses the ld_single_staff.php file, located in the plugin's templates folder, to display individual profile data. But you may create your own templates if you wish.
    </p>
    <p>
        Warning: Do not edit the ld_single_staff.php file directly. If you do, your changes will be overwritten when Lab Directory updates.
    </p>
    <p>
        Instruction for creating a custom single profile template can be found in the help section of <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings&tab=templates">Staff Settings page</a>
    </p>
</div>

<div class="help-topic" id="lab_directory_staff-templates">
  <h2>Lab Directory Staff Listing Templates</h2>

  <p>
    The <code>[lab-directory]</code> shortcode supports staff_grid as a default template or custom templates: 
  </p>
  <ul>
  <li><code>[lab-directory template=staff_grid]</code> or <code> [lab-directory]</code> - used to display a compact grid of staff</li>
  <li><code>[lab-directory template=staff_list]</code> - used to display a list (several lines for each staff, full width)</li>
  <li><code>[lab-directory template=staff_trombi]</code> - used to display a grid of staff photos</li>
  <li><code>[lab-directory template=defense_list]</code> - used to display a list of HDR and PHD defenses</li>
  </ul>
  <p>
    Each template is identified by a slug. The provided templates are "List" and "Grid", with their slugs being "list" and "grid" respectively. Each custom template uses the slug format "custom_[n]" where [n] is the custom template ID.
    So to use "Custom Template 1" you would use the shortcode like so: <code>[lab-directory template=custom_1]</code>.
  </p>
</div>

<div class="help-topic" id="lab_directory_staff-template-tags">
  <h2>Lab Directory Template Tags</h2>

  <p>
    Custom Shortcodes are listed in the Custom Details Fields table on the <a href="<?php echo get_admin_url(); ?>edit.php?post_type=lab_directory_staff&page=lab-directory-settings">Staff Settings page</a>. All template shortcodes must be contained within the <code>[lab_directory_staff_loop]</code> shortcodes.
  </p>

  <p>
    Preformatted shortcodes are listed below. There were more options in this list previously, but due to the addition of the Custom Details Fields above some of them were removed from the suggestions. They will still work for now, but deprecated shortcodes are marked below and will no longer work at some point in the future.
  </p>

  <ul>
    <li><code>[ld_photo_url]</code> - the url to the featured image for the lab_directory_staff member</li>
    <li><code>[ld_photo replace_empty=true]</code> - an &lt;img&gt; tag with the featured image for the lab_directory_staff member</li>
        <ul style="text-indent:25px;">
            <li>if replace_empty is given and non empty add a nobody photo for non existant photo</li>
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


