<?php

#
# select input
#

function create_select($name=false, $values, $current_value= null) {
	
	if (($name == false) OR ($values == false)) {
		return '';
	}
	$select = '<select name="'. $name . '">';

        	$index = 0; 
        	foreach( $values as $key => $value) {
        		$select .='<option value="' . $key . '" '. ($current_value==$value? 'selected':'') . '>'. $value . '</option>';
        		$index++; 
        	}
	$select .= '</select>';
	return $select;
		
}