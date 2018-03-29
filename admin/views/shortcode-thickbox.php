

<style type="text/css">
  #lab_directory_staff-categories-wrapper,
  #lab_directory_staff-order-wrapper,
  #lab_directory_staff-template-wrapper {
    margin: 20px 0px;
  }
</style>

<!-- TODO OBSOLETE rewrite for new categories   -->
<div id="lab_directory_staff-categories-wrapper">
  <label for="lab_directory_staff-category">Staff Category</label>
  <select name="lab_directory_staff-category">
    <option value=''>-- Select Category --</option>
    <?php foreach(get_terms('lab_category') as $cat): ?>
      <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div id="lab_directory_staff-order-wrapper">
  <label for="lab_directory_staff-order">Staff Order</label>
  <select name="lab_directory_staff-order">
    <option value=''>-- Use Default --</option>
    <option value="asc">Ascending</option>
    <option value="desc">Descending</option>
  </select>
</div>

<div id="lab_directory_staff-template-wrapper">
  <label for="lab_directory_staff-template">Staff Template</label>
  <select name="lab_directory_staff-template">
    <option value=''>-- Use Default --</option>
    <option value='list'>List</option>
    <option value='grid'>Grid</option>
    <?php foreach(Lab_Directory_Settings::get_custom_lab_directory_staff_templates() as $template): ?>
      <option value="<?php echo $template['slug'] ?>">Custom Template <?php echo $template['index']; ?></option>
    <?php endforeach; ?>
  </select>
</div>

<a href="javascript:StaffDirectory.formatShortCode();" class="button button-primary button-large">Insert Shortcode</a>