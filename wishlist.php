<?php
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

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
$page_title = 'Wishlist - saved items';
include('./includes/header.html');

require('./mysql.inc.php');


// Need the utility functions:
include('./includes/product_functions.inc.php');

// If there's a SKU value in the URL, break it down into its parts:
if (isset($_GET['sku'])) {
	list($type, $pid) = parse_sku($_GET['sku']);
}

if (isset($type, $pid, $_GET['action']) && ($_GET['action'] === 'remove') ) { // Remove it from the wish list.
	
	$q = 'DELETE FROM wish_lists WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';
	$r = mysqli_query($dbc, $q);

} elseif (isset($type, $pid, $_GET['action'], $_GET['qty']) && ($_GET['action'] === 'move') ) { // Move it to the wish list.

	// Determine the quantity:
	$qty = (filter_var($_GET['qty'], FILTER_VALIDATE_INT, array('min_range' => 1)) !== false) ? $_GET['qty'] : 1;
	$cid = 0;

	// Add it to the wish list:
	
	$q = 'SELECT id, quantity FROM wish_lists WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';

	$r = mysqli_query($dbc, $q);

	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$cid = $row['id'];
		$qty = $qty + $row['quantity'];
	}

	
	if ($cid > 0) {
		$q = 'UPDATE wish_lists SET quantity='. $qty .', date_modified=NOW() WHERE id='. $cid .'';
		
		$r = mysqli_query($dbc, $q);

	} else {
		$q = 'INSERT INTO wish_lists (user_session_id, product_type, product_id, quantity) VALUES ("'. $uid .'", "'. $type .'", '. $pid .', '. $qty .')';
		
		$r = mysqli_query($dbc, $q);
	
	}

	// Remove it from the cart:
	$q = 'DELETE FROM carts WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';
	$r = mysqli_query($dbc, $q);
	
} elseif (isset($_POST['quantity'])) { // Update quantities in the wish list.
	
	// Loop through each item:
	foreach ($_POST['quantity'] as $sku => $qty) {
		
		// Parse the SKU:
		list($type, $pid) = parse_sku($sku);
		
		if (isset($type, $pid)) {

			// Determine the quantity:
			$qty = (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 0)) !== false) ? $qty : 1;

			// Update the quantity in the wish list:
			if ($qty > 0) {
				$q = 'UPDATE wish_lists SET quantity='. $qty .', date_modified=NOW() WHERE user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .'';
		
				$r = mysqli_query($dbc, $q);

			} else {
				$q = 'DELETE FROM wish_lists WHERE (user_session_id="'. $uid .'" AND product_type="'. $type .'" AND product_id='. $pid .')';
				$r = mysqli_query($dbc, $q);

			}

		}

		} // End of FOREACH loop.
	
}// End of main IF.

// check the cart for contents for wishlist.html

$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("C", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="coffee" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'")';

$w = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$w) echo mysqli_error($dbc);

if (mysqli_num_rows($w) > 0) { 
	$cart_contents = 1;
} else { // Empty cart!
	$cart_contents = 0;
}
		
// Get the wish list contents:
$q = '(SELECT CONCAT("G", ncp.id) AS sku, wl.quantity, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM wish_lists AS wl INNER JOIN non_coffee_products AS ncp ON wl.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="goodies" AND wl.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("C", sc.id), wl.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="coffee" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), wl.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="maple" AND wl.user_session_id="'. $uid .'")';
$r = mysqli_query($dbc, $q);
	
	// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

if (mysqli_num_rows($r) > 0) { // Products to show!
	include('./views/wishlist.html');
} else { // Empty cart!
	include('./views/emptylist.html');
}

// Finish the page:
include('./includes/footer.html');
?>
