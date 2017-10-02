<?php
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Validate the product type...
if (isset($_GET['type']) && ($_GET['type'] === 'goodies')) {
	$page_title = 'Our Goodies, by Category';
	$type = 'goodies';
} else { // Default is cheese!
	$page_title = 'Our Cheese Products';
	$type = 'cheese';	
}

// Include the header file:
include('./includes/header.html');

require('./mysql.inc.php');

if ($type == 'cheese') {
	$q = "(SELECT * FROM general_coffees ORDER by category)";
} else {
	$q = "(SELECT * FROM non_coffee_categories ORDER by category)";
}

$r = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

// If records were returned, include the view file:
if (mysqli_num_rows($r) > 0) {
		include ('./views/list_categories.html');
} else { // Include the error page:
	include ('./views/error.html');
}

// Include the footer file:
include ('./includes/footer.html');
?>