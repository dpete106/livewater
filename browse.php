<?php
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Validate the required values:
$type = $sp_cat = $category = false;
if (isset($_GET['type'], $_GET['category'], $_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
	
	// Make the associations:
	$category = $_GET['category'];
	$sp_cat = $_GET['id'];
	
	// Validate the type:
	if ($_GET['type'] === 'goodies') {
		
		$type = 'goodies';
		
	} elseif ($_GET['type'] === 'dairy') {
		
		$type = 'dairy';
	} elseif ($_GET['type'] === 'maple') {
		
		$type = 'maple';
	}

}

// If there's a problem, display the error page:
if (!$type || !$sp_cat || !$category) {
	$page_title = 'Error!';
	include('./includes/header.html');
	include('./views/error.html');
	include('./includes/footer.html');
	exit();
}

// Create a page title:
$page_title = ucfirst($type) . ' to Buy::' . $category;

// Include the header file:
include('./includes/header.html');

// Require the database connection:
require('./mysql.inc.php');

if ($type == 'dairy') {
	$q = '(SELECT gc.description, gc.image, CONCAT("C", sc.id) AS sku, 
CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole, CONCAT("$", FORMAT(sc.price/100, 2))) AS name, 
sc.stock, sc.price, sales.price AS sale_price 
FROM specific_coffees AS sc INNER JOIN sizes AS s ON s.id=sc.size_id 
INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id 
LEFT OUTER JOIN sales ON (sales.product_id=sc.id 
AND sales.product_type="coffee" AND 
((NOW() BETWEEN sales.start_date AND sales.end_date) 
OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) 
WHERE general_coffee_id='. $sp_cat .' AND stock>0 
ORDER by name ASC)';
} elseif ($type == 'maple') {
	$q = '(SELECT gc.description, gc.image, CONCAT("M", sc.id) AS sku, 
CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole, CONCAT("$", FORMAT(sc.price/100, 2))) AS name, 
sc.stock, sc.price, sales.price AS sale_price 
FROM specific_coffees AS sc INNER JOIN sizes AS s ON s.id=sc.size_id 
INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id 
LEFT OUTER JOIN sales ON (sales.product_id=sc.id 
AND sales.product_type="coffee" AND 
((NOW() BETWEEN sales.start_date AND sales.end_date) 
OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) 
WHERE general_coffee_id='. $sp_cat .' AND stock>0 
ORDER by name ASC)';
} else {
	$q = '(SELECT ncc.description AS g_description, ncc.image AS g_image, 
CONCAT("G", ncp.id) AS sku, ncp.name, ncp.description, ncp.image, ncp.price, ncp.stock, sales.price AS sale_price
FROM non_coffee_products AS ncp INNER JOIN non_coffee_categories AS ncc 
ON ncc.id=ncp.non_coffee_category_id 
LEFT OUTER JOIN sales ON (sales.product_id=ncp.id 
AND sales.product_type="goodies" AND 
((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) )
WHERE non_coffee_category_id='. $sp_cat .' ORDER by date_created DESC)';
}
$r = mysqli_query($dbc, $q);
// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

// If records were returned, include the view file:
if (mysqli_num_rows($r) > 0) {
	if ($type === 'goodies') {
		// Three versions of this file:
		 include('./views/list_goodies.html');
	} elseif ($type === 'dairy') {
		 include('./views/list_coffees.html');

		mysqli_free_result($r);

		//include('./includes/handle_review.php');
		
		//$r = mysqli_query($dbc, "CALL select_reviews('$type', $sp_cat)");
		//include('./views/review.html');

	} elseif ($type === 'maple') {
		 include('./views/list_coffees.html');

		mysqli_free_result($r);

		//include('./includes/handle_review.php');
		
		//$r = mysqli_query($dbc, "CALL select_reviews('$type', $sp_cat)");
		//include('./views/review.html');

	}
} else { // Include the "noproducts" page:
	include('./views/noproducts.html');
}

// Include the footer file:
include('./includes/footer.html');
?>