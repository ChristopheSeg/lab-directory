<?php

/* 
 * Create a selec tinput from value list 
 * 
 * $allow_none: text for 'no selection' or false
 */


function lab_directory_create_select($name=false, $values, $current_value= null, $class=null, $allow_none=false, $disabled=false) {
	
	if (($name == false) OR ($values == false)) {
		return '';
	}
	
	
	if ($allow_none !== false) {
		if ($allow_none === true) {
			$no_selection= __('no selection', 'lab-directory');
		} else {
			$no_selection = $allow_none;
		}
	} else {
			$no_selection = '';
	}
	
	$select_options = '';
	
   	foreach( $values as $key => $value) {
   		$select_options .='<option value="' . $key . '" '. ($current_value==$key? 'selected':'') . '>'. $value . '</option>';
   		if ($current_value==$key) {
   			$noselection=false;
   		}
    }
	// $select = '<select ' . ($multiple? 'multiple ': ''). ($size? 'size="'. $size . '"': ''). 'class="' . $class . '"name="' . $name . '">';
	
	
	$select = '<select ' . ($disabled? 'hidden ':'') .' class="' . $class . '"name="' . $name . '">';
	if ($allow_none!== false) {
		$select .='<option value="none" ' . ($current_value=='none'? 'selected':'') . '>'. $no_selection . '</option>';
	}
	$select .= $select_options . '</select>';
	
	if ($disabled){
		// select is hidden, only current value displayed
		$select .= $values[$current_value];
	
	}
	
    return $select;
		
}