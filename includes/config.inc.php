<?php

// Are we live?
define('LIVE', false);
if (!defined('LIVE')) DEFINE('LIVE', true);
//if (defined('LIVE')) echo 'LIVE';

// Errors are emailed here:
DEFINE('CONTACT_EMAIL', 'you@example.com');

// ************ SETTINGS ************ //
// ********************************** //

// ********************************** //
// ************ CONSTANTS *********** //

// Determine location of files and the URL of the site:
//define('BASE_URI', 'http://');
define('BASE_URI', 'http://livewater/');
define('BASE_URL', 'cheese/');
define('MYSQL', 'http://livewater/mysql.inc.php');
//define('MYSQL', 'http://mysql.inc.php');

function my_error_handler ($e_number, $e_message, $e_file, $e_line, $e_vars) {

	// Build the error message:
	$message = "An error occurred in script '$e_file' on line $e_line:\n$e_message\n";
	
	// Add the backtrace:
	$message .= "<pre>" .print_r(debug_backtrace(), 1) . "</pre>\n";
	
	// Or just append $e_vars to the message:
	//	$message .= "<pre>" . print_r ($e_vars, 1) . "</pre>\n";

	if (!LIVE) { // Show the error in the browser.
		
		echo '<div class="error">' . nl2br($message) . '</div>';

	} else { // Development (print the error).

		// Send the error in an email:
		error_log ($message, 1, CONTACT_EMAIL, 'From:admin@example.com');
		
		// Only print an error message in the browser, if the error isn't a notice:
		if ($e_number != E_NOTICE) {
			echo '<div class="error">A system error occurred. We apologize for the inconvenience.</div>';
		}

	} // End of $live IF-ELSE.
	
	return true; // So that PHP doesn't try to handle the error, too.

} // End of my_error_handler() definition.

// Use my error handler:
set_error_handler ('my_error_handler');

// ************ ERROR MANAGEMENT ************ //
// ****************************************** //

// Omit the closing PHP tag to avoid 'headers already sent' errors!