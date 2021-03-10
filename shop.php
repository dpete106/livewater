<?php

// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Validate the product type...
if (isset($_GET['type']) && ($_GET['type'] === 'bread')) {
	$page_title = 'Our Bread Products';
	$type = 'bread';
} elseif (isset($_GET['type']) && ($_GET['type'] === 'beef')) {
	$page_title = 'Our Farm Beef/Pork/Chicken Produce';
	$type = 'beef';
}  elseif (isset($_GET['type']) && ($_GET['type'] === 'cand')) {
	$page_title = 'Our Farm Beef/Pork/Chicken Produce';
	$type = 'cand';
} elseif (isset($_GET['type']) && ($_GET['type'] === 'dairy')) { 
	$page_title = 'Our Dairy Products';
	$type = 'dairy';	

} elseif (isset($_GET['type']) && ($_GET['type'] === 'goodies')) { 
	$page_title = 'Our Farm Craft Products';
	$type = 'goodies';	

}elseif (isset($_GET['type']) && ($_GET['type'] === 'maple')) { 
	$page_title = 'Our Maple Syrup Products';
	$type = 'maple';	

} elseif (isset($_GET['type']) && ($_GET['type'] === 'java')) { 
	$page_title = 'Our Coffee Products';
	$type = 'java';	

}else { // Default is produce!
	$page_title = 'Our Farm Fresh Products';
	$type = 'produce';	
}

// Include the header file:
include('./includes/header.html');

require('./mysql.inc.php');

if ($type == 'dairy' or $type == 'produce' or $type == 'maple' or $type == 'java' or $type == 'beef' or $type == 'bread' or $type == 'cand') {
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