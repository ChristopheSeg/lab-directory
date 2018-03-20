//jquery-ui-tabs
jQuery(document).ready(function($) {
    $( ".labDirectoryTabsClass" ).tabs();

   //hover states on the static widgets
    $('#dialog_link, ul#icons li').hover(
    function() { $(this).addClass('ui-state-hover'); },
    function() { $(this).removeClass('ui-state-hover'); }
);
    
});

// jquery datepicker
jQuery(document).ready(function($) {
    $(".datepicker").datepicker();
});

// jquery datetimepicker
jQuery(document).ready(function($) {
	$('.datetimepicker').datetimepicker({
		datepicker:true,
		formatTime:'H:i',
		formatDate:'d.m.Y',
		step:15
	});
});