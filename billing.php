<?php

ini_set('display_errors', 1);

// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Start the session:
session_start();

// The session ID is the user's cart ID:
$uid = session_id();

// Check that this is valid:
if (!isset($_SESSION['customer_id'])) { // Redirect the user.
	$location = '/livewater/' . BASE_URL . 'checkout.php';
	//*URL*$location = '/' . BASE_URL . 'checkout.php';
	header("Location: $location");
	exit();
}

// Require the database connection:
require('./mysql.inc.php');

// Validate the billing form...

// For storing errors:
$billing_errors = array();

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	//if (get_magic_quotes_gpc()) {
		//$_POST['cc_first_name'] = stripslashes($_POST['cc_first_name']);
		// Repeat for other variables that could be affected.
	//}

	// Check for a pickup:
	if (preg_match ('/^[A-Z0-9 \',.#-]{2,160}$/i', $_POST['cc_pickup'])) {
		$cc_pickup = $_POST['cc_pickup'];
	} else {
		$billing_errors['cc_pickup'] = 'Please enter your pick-up day and time!';
	}
	
	// Check for a first name:
	if (preg_match ('/^[A-Z \'.-]{2,20}$/i', $_POST['cc_first_name'])) {
		$cc_first_name = $_POST['cc_first_name'];
	} else {
		$billing_errors['cc_first_name'] = 'Please enter your first name!';
	}

	// Check for a last name:
	if (preg_match ('/^[A-Z \'.-]{2,40}$/i', $_POST['cc_last_name'])) {
		$cc_last_name  = $_POST['cc_last_name'];
	} else {
		$billing_errors['cc_last_name'] = 'Please enter your last name!';
	}
	
	// Check for a Stripe token:
	if (isset($_POST['token'])) {
		$token = $_POST['token'];		
	} else {
		$message = 'The order cannot be processed. Please make sure you have JavaScript enabled and try again.';
		$billing_errors['token'] = true;
	}

	
	// Check for a street address:
	if (preg_match ('/^[A-Z0-9 \',.#-]{2,160}$/i', $_POST['cc_address'])) {
		$cc_address  = $_POST['cc_address'];
	} else {
		$billing_errors['cc_address'] = 'Please enter your street address!';
	}
		
	// Check for a city:
	if (preg_match ('/^[A-Z \'.-]{2,60}$/i', $_POST['cc_city'])) {
		$cc_city = $_POST['cc_city'];
	} else {
		$billing_errors['cc_city'] = 'Please enter your city!';
	}

	// Check for a state:
	if (preg_match ('/^[A-Z]{2}$/', $_POST['cc_state'])) {
		$cc_state = $_POST['cc_state'];
	} else {
		$billing_errors['cc_state'] = 'Please enter your state!';
	}

	// Check for a zip code:
	if (preg_match ('/^(\d{5}$)|(^\d{5}-\d{4})$/', $_POST['cc_zip'])) {
		$cc_zip = $_POST['cc_zip'];
	} else {
		$billing_errors['cc_zip'] = 'Please enter your zip code!';
	}
	
	if (empty($billing_errors)) { // If everything's OK...
		// Check for an existing order ID:
		if (isset($_SESSION['order_id']) && isset($_SESSION['order_total'])) { // Use existing order info:
			$order_id = $_SESSION['order_id'];
			$order_total = $_SESSION['order_total'];
		} else { // Create a new order record:
			// Get the last four digits of the credit card number:
			// Temporary solution for Stripe:
			$cc_last_four = 1234;
//			$cc_last_four = substr($cc_number, -4);

			$shipping = $_SESSION['shipping'] * 100;
			$customer_id = $_SESSION['customer_id'];

			$q1 = 'INSERT INTO orders (customer_id, shipping, credit_card_number, pickup_time, order_date) VALUES (?, ?, ?, ?, NOW())';
			$affected = 0;
			$stmt1 = mysqli_prepare($dbc, $q1);
			mysqli_stmt_bind_param($stmt1, 'iiis', $customer_id, $shipping, $cc_last_four, $cc_pickup);
			mysqli_stmt_execute($stmt1);
			$affected += mysqli_stmt_affected_rows($stmt1);	
			if (!$affected > 0) echo mysqli_error($dbc);

			// Confirm that it worked:
			if ($affected > 0) {

				$q = 'SELECT LAST_INSERT_ID()';

				$r = mysqli_query($dbc, $q);
				if (mysqli_num_rows($r) == 1) {
					list($oid) = mysqli_fetch_array($r);
					
					//					$q = 'INSERT INTO order_contents (order_id, product_type, product_id, quantity, price_per) SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, ncp.price) FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'" UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="coffee" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="coffee" AND c.user_session_id="'. $uid .'"';
					$q = 'INSERT INTO order_contents (order_id, product_type, product_id, quantity, price_per) SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, ncp.price) FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'" 
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="beef" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="beef" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="cand" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="cand" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="dairy" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="dairy" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="java" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="java" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="bread" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="bread" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="produce" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="produce" AND c.user_session_id="'. $uid .'"
					UNION SELECT '. $oid .', c.product_type, c.product_id, c.quantity, IFNULL(sales.price, sc.price) FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'"';
					$r = mysqli_query($dbc, $q);
					// For debugging purposes:
					if (!$r) echo mysqli_error($dbc);
					
					if ($r) {
						
						$q = 'SELECT SUM(quantity*price_per) AS value_sum FROM order_contents WHERE order_id='. $oid .'';
						$r = mysqli_query($dbc, $q);
						// For debugging purposes:
						if (!$r) echo mysqli_error($dbc);
						$subtotal =  0;
						while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
							
							$subtotal =  $subtotal + $row['value_sum'];
						}

					} else {
						// Log the error, send an email, panic!
						trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
					}

					$q = 'UPDATE orders SET total = ('. $subtotal .' + ' . $shipping .') WHERE id='. $oid .'';
					
					$r = mysqli_query($dbc, $q);
					// For debugging purposes:
					if (!$r) echo mysqli_error($dbc);

					$order_total = $subtotal + $shipping;
					$order_total = floor($order_total);

					$order_id = $oid;
					
					$_SESSION['order_total'] = $order_total;
					$_SESSION['order_id'] = $order_id;
					
				} else { // Could not retrieve the order ID and total.
					unset($cc_number, $cc_cvv, $_POST['cc_number'], $_POST['cc_cvv']);
					trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
				}
			} else { // The add_order() procedure failed.
				trigger_error('Your order could not be processed due to a system error. We apologize for the inconvenience.');
			}
			
		} // End of isset($_SESSION['order_id']) IF-ELSE.
		
		// ------------------------
		// Process the payment!
		if (isset($order_id, $order_total)) {

			try {
// sk_test_GBsb65uAB1MefNcwXKRBmjp6
// pk_test_JRGK5txjD4IIgyJU5ztDwSz1
				// Include the Stripe library:
				require_once('./vendor/autoload.php');
				$stripe = array(
					'secret_key'      => 'sk_test_51IlwvZIsCpufva0SEkM4GCfAhh23t21fGN274tg82XkEDTCuNlFTaejbvuzpsJc1arhEV044YL62aYJWkzC7vil400rPtLSWHf',
					'publishable_key' => 'pk_test_51IlwvZIsCpufva0SahP5ooGz6UcNDZkf4wUpmdap8hDuugrGFYmTQ2yGgFBAMiX8ZeAeawzf5uKXNz4c5zg4ArmQ00QCRSlgx7'
				);
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
				// set your secret key: remember to change this to your live secret key in production
				//Stripe::setApiKey('pk_test_JRGK5txjD4IIgyJU5ztDwSz1');

				// Charge the order:
				$charge = \Stripe\Charge::create(array(
					'amount' => $order_total,
					'currency' => 'usd',
					'card' => $token,
					'description' => $_SESSION['email'],
					'capture' => false
					)
				);


				// Did it work?
				if ($charge->paid == 1) {

					// Add slashes to two text values:
					$full_response = addslashes(serialize($charge));

					// Record the transaction:
					$charge_id = $charge->id;
					
					$q = 'INSERT INTO charges VALUES (NULL, "'. $charge_id .'", '. $order_id .', "auth_only", '. $order_total .', "'. $full_response .'", NOW());';
					$r = mysqli_query($dbc, $q);
					// For debugging purposes:
					if (!$r) echo mysqli_error($dbc);

					
					// Add the transaction info to the session:
					$_SESSION['response_code'] = $charge->paid;  // = 1
					
					// Redirect to the next page:
					//$location = 'https://' . BASE_URL . 'final.php';
					//*URL*$location = '/' . BASE_URL . 'final.php';
					$location = '/livewater/' . BASE_URL . 'final.php';
					header("Location: $location");
					exit();

				} else {
					$message = $charge->response_reason_text;
					echo '<script type="text/javascript">alert("0'.$message.'");</script>';

				}

			} catch (\Stripe\Error\Card $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
			
			} catch (\Stripe\Error\RateLimit $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
				
			} catch (\Stripe\Error\InvalidRequest $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
				
			} catch (\Stripe\Error\Authentication $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
			} catch (\Stripe\Error\ApiConnection $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
			} catch (\Stripe\Error\Base $e) { // Stripe declined the charge.
				$e_json = $e->getJsonBody();
				$err = $e_json['error'];
				$bankerror = $err['message'];
			} catch (Exception $e) { // Try block failed somewhere else.
				$bankerror = $e->getMessage();
			}

		} // End of isset($order_id, $order_total) IF.
		// Above code added as part of payment processing.
		// ------------------------

	} // Errors occurred IF

} // End of REQUEST_METHOD IF.
							
// Include the header file:
$page_title = 'Checkout - Your Billing Information';
include('./includes/checkout_header.html');

// Get the cart contents:
// shipping or FSO - add c.ship
$q = '(SELECT CONCAT("G", ncp.id) AS sku, c.quantity, c.ship, ncc.category, ncp.name, ncp.price, ncp.stock, sales.price AS sale_price FROM carts AS c INNER JOIN non_coffee_products AS ncp ON c.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id LEFT OUTER JOIN sales ON (sales.product_id=ncp.id AND sales.product_type="goodies" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="goodies" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("B", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="beef" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="beef" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("C", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="cand" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="cand" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("D", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="dairy" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="dairy" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("J", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="java" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="java" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("O", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="bread" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="bread" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("P", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="produce" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="produce" AND c.user_session_id="'. $uid .'")
UNION (SELECT CONCAT("M", sc.id), c.quantity, c.ship, gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, sales.price FROM carts AS c INNER JOIN specific_coffees AS sc ON c.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id LEFT OUTER JOIN sales ON (sales.product_id=sc.id AND sales.product_type="maple" AND ((NOW() BETWEEN sales.start_date AND sales.end_date) OR (NOW() > sales.start_date AND sales.end_date IS NULL)) ) WHERE c.product_type="maple" AND c.user_session_id="'. $uid .'")';

$r = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

if (mysqli_num_rows($r) > 0) { // Products to show!
	if (isset($_SESSION['shipping_for_billing']) && ($_SERVER['REQUEST_METHOD'] !== 'POST')) {
		$values = 'SESSION';
	} else {
		$values = 'POST';
	}
	include('./views/billing.html');
} else { // Empty cart!
	include('./views/emptycart.html');
}

// Finish the page:
include('./includes/footer.html');
?>