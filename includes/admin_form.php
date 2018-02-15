<?php

/* 
 * Create a selec tinput from value list 
 * 
 * $allow_none: text for 'no selection' or false
 */


function lab_directory_create_select($name=false, $values, $current_value= null, $multiple=false, $class=null, $allow_none=false, $disabled=false) {
	
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
	
	// Convert single current_value to array 
	if (! is_array($current_value) ) {
		$current_value = array($current_value); 
	}
	$current_values = ''; 
   	foreach( $values as $key => $value) {
   		if (in_array($key, $current_value, true) ) {
   			$noselection=false;
   			$current_values .= $values[$key];
   			$select_options .='<option value="' . $key . '" selected="selected">' . $value . '</option>';
   		} else {
   			$select_options .='<option value="' . $key . '">' . $value . '</option>';
   			 
   		}
    }
	
	$select = '<select ' . ($disabled? 'hidden ':'') . ($multiple? 'multiple size="3" ': '') . ' class="' . $class . '"name="' . $name . ($multiple? '[]': '') . '">';
	if ($allow_none!== false) {
		$select .='<option value="none" ' . (in_array('note', $current_value, true)? 'selected="selected"':'') . '>'. $no_selection . '</option>';
	}
	$select .= $select_options . '</select>';
	if ($disabled){
		// When select is hidden, only current value(s) is displayed
		$select .= $current_values;
	
	}
	
    return $select;
		
}