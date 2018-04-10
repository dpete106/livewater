<?php

// This file manages the shopping cart.
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check for, or create, a user session:
if (isset($_COOKIE['SESSION']) && (strlen($_COOKIE['SESSION']) === 32)) {
	$uid = $_COOKIE['SESSION'];
} else {
	$uid = openssl_random_pseudo_bytes(16);
	$uid = bin2hex($uid);
}

// Send the cookie:
setcookie('SESSION', $uid, time()+(60*60*24*30));

// Include the header file:
$page_title = 'Your Shopping Cart';
include('./includes/header.html');

// Require the database connection:
require('./mysql.inc.php');

// Need the utility functions:
include('./includes/product_functions.inc.php');

// If there's a SKU value in the URL, break it down into its parts:
if (isset($_GET['sku'])) {
	list($type, $pid) = parse_sku($_GET['sku']);
}

if (isset($pid, $type, $_GET['action']) && ($_GET['action'] === 'add') ) { // Add a new product to the cart:

	$qty = 1;
	$cid = 0;
	$q = 'SELECT id, quantity FROM carts WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';

	$r = mysqli_query($dbc, $q);
	
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$cid = $row['id'];
		$qty = $qty + $row['quantity'];
	}

	
	if ($cid > 0) {
		$q = 'UPDATE carts SET quantity='. $qty .', date_modified=NOW() WHERE id='. $cid .'';
		
		$r = mysqli_query($dbc, $q);
		
	} else {

		$q = 'INSERT INTO carts (user_session_id, product_type, product_id, quantity) VALUES ("'. $uid .'", "'. $type .'", '. $pid .', '. $qty .')';
		$r = mysqli_query($dbc, $q);
		
		$result = mysqli_query($dbc, "SELECT * FROM carts");
		$num_rows = mysqli_num_rows($result);
		if ($num_rows > 0) { 
			//echo "$num_rows Rows Insert\n";
			mysqli_free_result($result);
		} else {//echo "zero Rows Insert\n";
		}
	}
		
} elseif (isset($type, $pid, $_GET['action']) && ($_GET['action'] === 'remove') ) { // Remove it from the cart.

	$q = 'DELETE FROM carts WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';
	$r = mysqli_query($dbc, $q);
	
} elseif (isset($type, $pid, $_GET['action'], $_GET['qty']) && ($_GET['action'] === 'move') ) { // Move it to the cart.

	// Determine the quantity:
	$qty = (filter_var($_GET['qty'], FILTER_VALIDATE_INT, array('min_range' => 1)) !== false) ? $_GET['qty'] : 1;
	$cid = 0;

	// Add it to the cart:
	$q = 'SELECT id, quantity FROM carts WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';

	$r = mysqli_query($dbc, $q);
	
	// For debugging purposes:
	if (!$r) echo mysqli_error($dbc);
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$cid = $row['id'];
		$qty = $qty + $row['quantity'];
	}

	
	if ($cid > 0) {
		
		$q = 'UPDATE carts SET quantity='. $qty .', date_modified=NOW() WHERE id='. $cid .'';
		
		$r = mysqli_query($dbc, $q);
		
	} else {

		$q = 'INSERT INTO carts (user_session_id, product_type, product_id, quantity) VALUES ("'. $uid .'", "'. $type .'", '. $pid .', '. $qty .')';
		
		$r = mysqli_query($dbc, $q);

	}
	
	// Remove it from the wish list:
	$q = 'DELETE FROM wish_lists WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';
	$r = mysqli_query($dbc, $q);

	

} elseif (isset($_POST['quantity'])) { // Update quantities in the cart.
	
	// Loop through each item:
	foreach ($_POST['quantity'] as $sku => $qty) {
		
		// Parse the SKU:
		list($type, $pid) = parse_sku($sku);
		
		if (isset($type, $pid)) {

			// Determine the quantity:
			$qty = (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 0)) !== false) ? $qty : 1;

			// Update the quantity in the cart:
			if ($qty > 0) {
				$q1 = 'UPDATE carts SET quantity=?, date_modified=? WHERE user_session_id=? AND product_type=? AND product_id=?';
				$date = date("Y-m-d H:i:s");
				$affected = 0;
				$stmt1 = mysqli_prepare($dbc, $q1);
				mysqli_stmt_bind_param($stmt1, 'isssi', $qty, $date, $uid, $type, $pid);
				mysqli_stmt_execute($stmt1);
				$affected += mysqli_stmt_affected_rows($stmt1);	
				if (!$affected > 0) echo mysqli_error($dbc);

			} else {
				$q1 = 'DELETE FROM carts WHERE (user_session_id=? AND product_type=? AND product_id=?)';
				$affected = 0;
				$stmt1 = mysqli_prepare($dbc, $q1);
				mysqli_stmt_bind_param($stmt1, 'ssi', $uid, $type, $pid);
				mysqli_stmt_execute($stmt1);
				$affected += mysqli_stmt_affected_rows($stmt1);	
				if (!$affected > 0) echo mysqli_error($dbc);


			}

		}
			
	} // End of FOREACH loop.
	
}// End of main IF.
		
// Get the cart contents:

$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("C", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="coffee" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'")';

$r = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$r) echo mysqli_error($dbc);


if (mysqli_num_rows($r) > 0) { // Products to show!
	include('./views/cart.html');
} else { // Empty cart!
		if (isset($_SESSION['order_id']) ) { 
		// Clear the session:
			$_SESSION = array(); // Destroy the variables.
			session_destroy(); // Destroy the session itself.
		} else {}
	
	include('./views/emptycart.html');
}

// Finish the page:
include('./includes/footer.html');
?>