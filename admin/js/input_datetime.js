
//jquery datetimepicker
jQuery(document).ready(function($) {
	// format 'Y-m-d H:i' imposed for input and saving data
	$('.datetimepicker').datetimepicker({
		datepicker:true,
		format: 'Y-m-d H:i',
		formatTime:'H:i',
		formatDate:'Y-m-d',
		step:15,
		timepickerOptions: {
			dragAndDrop: false,
			mouseWheel: false,
		 }
	});
});

//jquery datepicker
jQuery(document).ready(function($) {
	// format 'Y-m-d ' imposed for input and saving data
	$('.datepicker').datetimepicker({ 
		format: 'Y-m-d', 
		timepicker: false,
		timepickerOptions: {
			dragAndDrop: false,
			mouseWheel: false,
		 }
});

