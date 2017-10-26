jQuery(document).ready(function($) {
  tinymce.create('tinymce.plugins.lab_directory_shortcode_plugin', {
    init : function(ed, url) {
      ed.addCommand('lab_directory_insert_shortcode', function() {
        tb_show('Lab Directory Shortcode Options', 'admin-ajax.php?action=get_my_form');
      });
      ed.addButton('lab_directory_button', {title : 'Insert Lab Directory Shortcode', cmd : 'lab_directory_insert_shortcode', image: url + '/../images/wp-editor-icon.png' });
    },
  });
  tinymce.PluginManager.add('lab_directory_button', tinymce.plugins.lab_directory_shortcode_plugin);
});

StaffDirectory = {
  formatShortCode: function(){
    var categoryVal = jQuery('[name="lab_directory_staff-category"]').val();
    var orderVal = jQuery('[name="lab_directory_staff-order"]').val();
    var templateVal = jQuery('[name="lab_directory_staff-template"]').val();
    
    var shortcode = '[lab-directory';

    if(categoryVal != '') {
      shortcode += ' cat=' + categoryVal;
    }

    if(orderVal != '') {
      shortcode += ' order=' + orderVal;
    }

    if(templateVal != '') {
      shortcode += ' template=' + templateVal;
    }

    shortcode += ']';
    
    tinymce.execCommand('mceInsertContent', false, shortcode);
    tb_remove();
  }
};