<?php

#
# select input
#

function create_select($name=false, $values, $current_value= null, $class=null, $allow_none=false) {
	
	if (($name == false) OR ($values == false)) {
		return '';
	}
	$noelection=true; 
	$select_options = '';
	
   	foreach( $values as $key => $value) {
   		$select_options .='<option value="' . $key . '" '. ($current_value==$key? 'selected':'') . '>'. $value . '</option>';
   		if ($current_value==$key) {
   			$noselection=false;
   		}
    }
	// $select = '<select ' . ($multiple? 'multiple ': ''). ($size? 'size="'. $size . '"': ''). 'class="' . $class . '"name="' . $name . '">';
	$select = '<select class="' . $class . '"name="' . $name . '">';
	if ($allow_none) {
		$select .='<option value="none" ' . ($noselection? 'selected ': '') . '>'. __('no selection !', 'lab_directory') . '</option>';
	}
    $select .= $select_options . '</select>';
	return $select;
		
}