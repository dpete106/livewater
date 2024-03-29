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
	$ship = $_GET['ship']; // shipping or FSO

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
		// shipping or FSO
		$q = 'INSERT INTO wish_lists (user_session_id, product_type, product_id, quantity, ship) VALUES ("'. $uid .'", "'. $type .'", '. $pid .', '. $qty .', '. $ship .')';
		
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
// shipping or FSO - add c.ship
$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, c.ship, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("B", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="beef" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="beef" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("C", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="cand" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="cand" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("D", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="dairy" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="dairy" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("J", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="java" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="java" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("O", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="bread" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="bread" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("P", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="produce" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="produce" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'")';

$w = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$w) echo mysqli_error($dbc);

if (mysqli_num_rows($w) > 0) { 
	$cart_contents = 1;
} else { // Empty cart!
	$cart_contents = 0;
}

if ($cart_contents == 1) { // Products to show!
?>
<script type="text/javascript">
    function codeAddress() {
		localStorage.setItem('cart','1');
		$( "div.carticon" ).html( '<a class="nav-link" id="carticon" href="/livewater/cart.php">' +
			'<div class="icon-cart" style="float: left">' +
			'<div class="cart-line-1" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-2" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-3" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-wheel" style="background-color: #E5E9EA"></div>' +
			'</div></a>' ); 
    }
    window.onload = codeAddress;
</script>
<?php

} else { // Empty cart!
?>
<script type="text/javascript">
    function codeAddress() {
		localStorage.removeItem('cart');
		$( "div.carticon" ).html( '<a class="nav-link" id="carticon" href="/livewater/cart.php">' +
			'<div class="icon-cart" style="float: left">' +
			'<div class="cart-line-1" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-2" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-wheel" style="background-color: #E5E9EA"></div>' +
			'</div></a>' ); 
    }
    window.onload = codeAddress;
</script>
<?php	
}
		
// Get the wish list contents:
// shipping or FSO - add wl.ship
$q = '(SELECT CONCAT("G", ncp.id) AS sku, wl.quantity, wl.ship, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM wish_lists AS wl INNER JOIN non_coffee_products AS ncp ON wl.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="goodies" AND wl.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("B", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="beef" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="beef" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("C", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="cand" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="cand" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("D", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="dairy" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="dairy" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("J", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="java" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="java" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("O", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="bread" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="bread" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("P", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="produce" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="produce" AND wl.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), wl.quantity, wl.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM wish_lists AS wl INNER JOIN specific_coffees AS sc ON wl.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE wl.product_type="maple" AND wl.user_session_id="'. $uid .'")';
$r = mysqli_query($dbc, $q);
	
	// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

if (mysqli_num_rows($r) > 0) { // Products to show!
	$wish_contents = 1;

	include('./views/wishlist.html');
} else { // Empty cart!
	$wish_contents = 0;
	include('./views/emptylist.html');
}

// Finish the page:
include('./includes/footer.html');
?>
