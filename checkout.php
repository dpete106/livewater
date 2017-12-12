<?php
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Check for the user's session ID, to retrieve the cart contents:
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (isset($_COOKIE['SESSION']) && (strlen($_COOKIE['SESSION']) === 32)) {
		$uid = $_COOKIE['SESSION'];
		// Use the existing user ID:
		session_id($uid);
		// Start the session:
		session_start();
	} else { // Redirect the user.
		//$location = '/' . BASE_URL . 'cart.php';
		$location = '/livewater/' . BASE_URL . 'cart.php';
		header("Location: $location");
		exit();
	}
} else { // POST request.
	session_start();
	$uid = session_id();
}

// Create an actual session for the checkout process...

require('./mysql.inc.php');


// Validate the checkout form...

// For storing errors:
$shipping_errors = array();

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Check for Magic Quotes:
	if (get_magic_quotes_gpc()) {
		$_POST['first_name'] = stripslashes($_POST['first_name']);
	}

	// Check for a first name:
	if (preg_match ('/^[A-Z \'.-]{2,20}$/i', $_POST['first_name'])) {
		$fn = addslashes($_POST['first_name']);
	} else {
		$shipping_errors['first_name'] = 'Please enter your first name!';
	}
	
	// Check for a last name:
	if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $_POST['last_name'])) {
		$ln  = addslashes($_POST['last_name']);
	} else {
		$shipping_errors['last_name'] = 'Please enter your last name!';
	}
	
	// Check for a street address:
	if (preg_match ('/^[A-Z0-9 \',.#-]{2,80}$/i', $_POST['address1'])) {
		$a1  = addslashes($_POST['address1']);
	} else {
		$shipping_errors['address1'] = 'Please enter your street address!';
	}

	// Check for a second street address:
	if (empty($_POST['address2'])) {
		$a2 = NULL;
	} elseif (preg_match ('/^[A-Z0-9 \',.#-]{2,80}$/i', $_POST['address2'])) {
		$a2 = addslashes($_POST['address2']);
	} else {
		$shipping_errors['address2'] = 'Please enter your street address!';
	}
	
	// Check for a city:
	if (preg_match ('/^[A-Z \'.-]{2,60}$/i', $_POST['city'])) {
		$c = addslashes($_POST['city']);
	} else {
		$shipping_errors['city'] = 'Please enter your city!';
	}
	
	// Check for a state:
	if (preg_match ('/^[A-Z]{2}$/', $_POST['state'])) {
		$s = $_POST['state'];
	} else {
		$shipping_errors['state'] = 'Please enter your state!';
	}
	
	// Check for a zip code:
	if (preg_match ('/^(\d{5}$)|(^\d{5}-\d{4})$/', $_POST['zip'])) {
		$z = $_POST['zip'];
	} else {
		$shipping_errors['zip'] = 'Please enter your zip code!';
	}
	
	// Check for a phone number:
	// Strip out spaces, hyphens, and parentheses:
	$phone = str_replace(array(' ', '-', '(', ')'), '', $_POST['phone']);
	if (preg_match ('/^[0-9]{10}$/', $phone)) {
		$p  = $phone;
	} else {
		$shipping_errors['phone'] = 'Please enter your phone number!';
	}
	
	// Check for an email address:
	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$e = $_POST['email'];
		$_SESSION['email'] = $_POST['email'];
	} else {
		$shipping_errors['email'] = 'Please enter a valid email address!';
	}
	
	// Check if the shipping address is the billing address:
	if (isset($_POST['use']) && ($_POST['use'] === 'Y')) {
		$_SESSION['shipping_for_billing'] = true;
		$_SESSION['cc_first_name']  = $_POST['first_name'];
		$_SESSION['cc_last_name']  = $_POST['last_name'];
		$_SESSION['cc_address']  = $_POST['address1'] . ' ' . $_POST['address2'];
		$_SESSION['cc_city'] = $_POST['city'];
		$_SESSION['cc_state'] = $_POST['state'];
		$_SESSION['cc_zip'] = $_POST['zip'];
	}

	if (empty($shipping_errors)) { // If everything's OK...
		// check if duplicate customer

		$q = "(SELECT * FROM customers WHERE (email = '" . $e . "') AND (last_name = '" . $ln . "') AND (address1 = '" . $a1 . "') ORDER by email)";
		$r = mysqli_query($dbc, $q);

		if (!$r) echo mysqli_error($dbc);

		if (mysqli_num_rows($r) > 0) {
			while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) { // Fetch each item.
				list($_SESSION['customer_id']) = $row['id'];
			}
			
			$location = '/livewater/' . BASE_URL . 'billing.php';
			header("Location: $location");
			exit();
		} else { 
			// Add the user to the database...
			$q1 = 'INSERT INTO customers (email, first_name, last_name, address1, address2, city, state, zip, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
			$affected = 0;
			$stmt1 = mysqli_prepare($dbc, $q1);
			mysqli_stmt_bind_param($stmt1, 'sssssssss', $e, $fn, $ln, $a1, $a2, $c, $s, $z, $p);
			mysqli_stmt_execute($stmt1);
			$affected += mysqli_stmt_affected_rows($stmt1);	
			if (!$affected > 0) echo mysqli_error($dbc);

			// Confirm that it worked:
			if ($affected > 0) {
				// Retrieve the customer ID:
				$q = 'SELECT id FROM customers ORDER BY id DESC LIMIT 0, 1';

				//$r = mysqli_query($dbc, 'SELECT @cid');
				$r = mysqli_query($dbc, $q);

			
				if (mysqli_num_rows($r) == 1) {

					list($_SESSION['customer_id']) = mysqli_fetch_array($r);
				
					$location = '/livewater/' . BASE_URL . 'billing.php';
					header("Location: $location");
					exit();
				}
			}
			// Log the error, send an email, panic!
			trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
		} // IF customer not on DB
	} // Errors occurred IF.

} // End of REQUEST_METHOD IF.
							
// Include the header file:
$page_title = 'Checkout - Your Shipping Information';
include('./includes/checkout_header.html');

// Get the cart contents:
//$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'") UNION (SELECT CONCAT("C", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="coffee" AND c.user_session_id="'. $uid .'")';
$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'") 
UNION (SELECT CONCAT("C", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="coffee" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), c.quantity, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'")';

$r = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$r) echo mysqli_error($dbc);


if (mysqli_num_rows($r) > 0) { // Products to show!
	include('./views/checkout.html');
} else { // Empty cart!
	include('./views/emptycart.html');
}

// Finish the page:
include('./includes/footer.html');
?>