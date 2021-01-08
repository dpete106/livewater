<?php
function create_form_input($name, $type, $errors = array(), $values = 'POST', $options = array()) {
	
	// Assume no value already exists:
	$value = false;

	// Get the existing value, if it exists:
	if ($values === 'SESSION') {
		
		if (isset($_SESSION[$name])) $value = htmlspecialchars($_SESSION[$name], ENT_QUOTES, 'UTF-8');
	
	} elseif ($values === 'POST') {
		
		if (isset($_POST[$name])) $value = htmlspecialchars($_POST[$name], ENT_QUOTES, 'UTF-8');
		// Strip slashes if Magic Quotes is enabled:
		//if ($value && get_magic_quotes_gpc()) $value = stripslashes($value);

	}

	// Conditional to determine what kind of element to create:
	if ( ($type === 'text') || ($type === 'textnreq') ||($type === 'password') || ($type === 'email') ) { // Create text or password inputs.
		
		// Start creating the input:
		if ($type === 'textnreq') {
			echo '<input type="text" class="form-control" name="' . $name . '" id="' . $name . '" ';
		} else {
			echo '<input type="' . $type . '" class="form-control" name="' . $name . '" id="' . $name . '" required';
		}

		// Add the value to the input:
		if ($value) echo ' value="' . $value . '"';

		// Check for any extras:
		if (!empty($options) && is_array($options)) {
			foreach ($options as $k => $v) {
				echo " $k=\"$v\"";
			}
		}

		// Check for an error:
		if (array_key_exists($name, $errors)) {
			echo 'class="alert alert-warning" /> <span style="padding: 5px;" class="alert alert-warning">' . $errors[$name] . '</span>';
		} else {
			echo ' />';		
		}
		
	} elseif ($type === 'select') { // Select menu.
		
		if (($name === 'state') || ($name === 'cc_state')) { // Create a list of states.
			
			$data = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming');
			
		} elseif ($name === 'cc_exp_month') { // Create a list of months.

			$data = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',  'September', 'October', 'November', 'December');
			
		} elseif ($name === 'cc_exp_year') { // Create a list of years.
			
			$data = array();
			$start = date('Y'); // Start with current year.
			for ($i = $start; $i <= $start + 5; $i++) { // Add five more.
				$data[$i] = $i;
			}
			
		}  // End of $name IF-ELSEIF.
		
		// Start the tag:
		echo '<select name="' . $name  . '"';
	
		// Add the error class, if applicable:
		if (array_key_exists($name, $errors)) echo ' class="alert alert-warning"';

		// Close the tag:
		echo '>';		
	
		// Create each option:
		foreach ($data as $k => $v) {
			echo "<option value=\"$k\"";
			
			// Select the existing value:
			if ($value === $k) echo ' selected="selected"';
			
			echo ">$v</option>\n";
			
		} // End of FOREACH.
	
		// Complete the tag:
		echo '</select>';
		
		// Add an error, if one exists:
		if (array_key_exists($name, $errors)) {
			echo '<br /><span class="alert alert-warning">' . $errors[$name] . '</span>';
		}
		
	} elseif ($type === 'textarea') { // Create a TEXTAREA.

		// Display the error first: 
		if (array_key_exists($name, $errors)) echo ' <span class="alert alert-warning">' . $errors[$name] . '</span><br />';

		// Start creating the textarea:
		echo '<textarea class="form-control" name="' . $name . '" id="' . $name . '" rows="5" cols="75"';

		// Add the error class, if applicable:
		if (array_key_exists($name, $errors)) {
			echo ' class="alert alert-warning">';
		} else {
			echo '>';		
		}

		// Add the value to the textarea:
		if ($value) echo $value;

		// Complete the textarea:
		echo '</textarea>';

	} // End of primary IF-ELSEIF.

} // End of the create_form_input() function.

// Omit the closing PHP tag to avoid 'headers already sent' errors!