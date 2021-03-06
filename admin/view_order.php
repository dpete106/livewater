<?php
// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'View An Order';
include('../admin/includes/header.html');
// The header file begins the session.

// Validate the order ID:
$order_id = false;
if (isset($_GET['oid']) && (filter_var($_GET['oid'], FILTER_VALIDATE_INT, array('min_range' => 1))) ) { // First access
	$order_id = $_GET['oid'];
	$_SESSION['order_id'] = $order_id;
	
} elseif (isset($_SESSION['order_id']) && (filter_var($_SESSION['order_id'], FILTER_VALIDATE_INT, array('min_range' => 1))) ) {
	$order_id = $_SESSION['order_id'];
}
// Stop here if there's no $order_id:
if (!$order_id) {
	echo '<h3>Error1!</h3><p>This page has been accessed in error.</p>';
	include('../admin/includes/footer.html');
	exit();
}
require('../mysql.inc.php');


// ------------------------
// Process the payment!

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
	
	// Need to process payment, record the transaction, update the order_contents table, and update the inventory.
	
	// Get the order information:
	$q = 'SELECT customer_id, total, charge_id FROM orders AS o JOIN charges AS t ON (o.id=t.order_id AND t.type="auth_only") WHERE o.id=' . $order_id . '';
	$r = mysqli_query($dbc, $q);

	if (mysqli_num_rows($r) === 1) {
		
		// Get the returned values:
		list($customer_id, $order_total, $charge_id) = mysqli_fetch_array($r, MYSQLI_NUM);
		
		// Check for a positive order total:
		if ($order_total > 0) {
			
			// Make the request to the payment gateway:
	
			try {

				require_once('../vendor/autoload.php');
				$stripe = array(
					'secret_key'      => 'sk_test_GBsb65uAB1MefNcwXKRBmjp6',
					'publishable_key' => 'pk_test_JRGK5txjD4IIgyJU5ztDwSz1'
				);
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
			

			// Capture:
				$charge = \Stripe\Charge::retrieve($charge_id);
				$charge->capture();
				
				if ($charge->captured == 1) {
			// Add slashes to two text values:
					//$reason = addslashes($charge->response_reason_text);
					//$full_response = addslashes($charge->response);

			// Record the transaction:
					$captured = 'captured';
					$q = 'UPDATE charges SET type="' . $captured . '" WHERE charge_id="'.$charge_id.'"';

					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);


			// Upon success, update the order and the inventory:
				
					$message = 'The payment has been made. You may now ship the order. ' . $charge->outcome;
					
				// Update order_contents:
					$q = "UPDATE order_contents SET ship_date=NOW() WHERE order_id=$order_id";
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
				// Update the inventory...
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="beef" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="cand" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="bread" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="dairy" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="java" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="maple" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE specific_coffees AS sc, order_contents AS oc SET sc.stock=sc.stock-oc.quantity WHERE sc.id=oc.product_id AND oc.product_type="produce" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
					
					$q = 'UPDATE non_coffee_products AS ncp, order_contents AS oc SET ncp.stock=ncp.stock-oc.quantity WHERE ncp.id=oc.product_id AND oc.product_type="goodies" AND oc.order_id=' . $order_id;
					$r = mysqli_query($dbc, $q);
					if (!$r) echo mysqli_error($dbc);
								
				} else { // Do different things based upon the response:
				
					$error = 'The payment could not be processed because: ' . $charge->response_reason_text;

				} // End of payment response IF-ELSE.
			} catch (Stripe_InvalidRequestError $e) {
				$error1 = $e->getMessage();
			} catch (Stripe_AuthenticationError $e) {
				$error2 = $e->getMessage();
			} catch (Stripe_ApiConnectionError $e) {
				$error3 = $e->getMessage();
			} catch (Stripe_Error $e) {
				$error4 = $e->getMessage();
			} catch (Exception $e) { // Try block failed somewhere else.
				$error5 = $e->getMessage();
			}


		} else { // Invalid order total!

				$error = "The order total (\$$order_total) is invalid.";

		} // End of $order_total IF-ELSE.

	} else { // No matching order!
		
		$error = 'No matching order could be found.';
		
	} // End of transaction ID IF-ELSE.
	
	// Report any messages or errors:
	echo '<h3>Order Shipping Results</h3>';
	if (isset($message)) echo "<div class='alert alert-warning'>$message</div>";
	if (isset($error)) echo "<div class='alert alert-warning'>$error</div>";
	if (isset($error1)) echo "<div class='alert alert-warning'>1$error1</div>";
	if (isset($error2)) echo "<div class='alert alert-warning'>2$error2</div>";
	if (isset($error3)) echo "<div class='alert alert-warning'>3$error3</div>";
	if (isset($error4)) echo "<div class='alert alert-warning'>4$error4</div>";
	if (isset($error5)) echo "<div class='alert alert-warning'>5$error5</div>";

} // End of the submission IF.

// Above code added as part of payment processing.
// ------------------------

// Define the query:
$q = 'SELECT FORMAT(total/100, 2) AS total, FORMAT(shipping/100,2) AS shipping, credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %h:%i%p") AS od, email, CONCAT(last_name, ", ", first_name) AS name, CONCAT_WS(" ", address1, address2, city, state, zip) AS address, phone, customer_id, CONCAT_WS(" - ", ncc.category, ncp.name) AS item, ncp.stock, quantity, FORMAT(price_per/100,2) AS price_per, DATE_FORMAT(ship_date, "%b %e, %Y") AS sd FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN non_coffee_products AS ncp ON (oc.product_id = ncp.id AND oc.product_type="goodies") INNER JOIN non_coffee_categories AS ncc ON (ncc.id = ncp.non_coffee_category_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="beef") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="cand") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="bread") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="dairy") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="java") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="produce") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id . '
UNION 
SELECT FORMAT(total/100, 2), FORMAT(shipping/100,2), credit_card_number, pickup_time, DATE_FORMAT(order_date, "%a %b %e, %Y at %l:%i%p"), email, CONCAT(last_name, ", ", first_name), CONCAT_WS(" ", address1, address2, city, state, zip), phone, customer_id, CONCAT_WS(" - ", gc.category, s.size, sc.caf_decaf, sc.ground_whole) AS item, sc.stock, quantity, FORMAT(price_per/100,2), DATE_FORMAT(ship_date, "%b %e, %Y") FROM orders AS o INNER JOIN customers AS c ON (o.customer_id = c.id) INNER JOIN order_contents AS oc ON (oc.order_id = o.id) INNER JOIN specific_coffees AS sc ON (oc.product_id = sc.id AND oc.product_type="maple") INNER JOIN sizes AS s ON (s.id=sc.size_id) INNER JOIN general_coffees AS gc ON (gc.id=sc.general_coffee_id) WHERE o.id=' . $order_id;

// Execute the query:
$r = mysqli_query($dbc, $q);
if (mysqli_num_rows($r) > 0) { // Display the order info:
	echo '<div style="background-color:rgba(192,192,192);" class="jumbotron"><div class="module">';

	echo '<h3>View an Order</h3>
	<form action="view_order.php" method="post" accept-charset="utf-8">
		<fieldset>';
		
	// Get the first row:
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);

	// Display the order and customer information:
	echo '<p><strong>Order ID</strong>: ' . $order_id . '<br /><strong>Total</strong>: $' . $row['total'] . '<br /><strong>Shipping</strong>: $' . $row['shipping'] . '<br /><strong>Order Date</strong>: ' . $row['od'] . '<br /><strong>Customer Name</strong>: ' . htmlspecialchars($row['name']) . '<br /><strong>Customer Address</strong>: ' . htmlspecialchars($row['address']) . '<br /><strong>Customer Email</strong>: ' . htmlspecialchars($row['email']) . '<br /><strong>Customer Phone</strong>: ' . htmlspecialchars($row['phone']) . '<br /><strong>Credit Card Number Used</strong>: *' . $row['credit_card_number'] . '<br /><strong>Order Pick-up Day and Time</strong>: ' . $row['pickup_time'] . '</p>';

	// Create the table:
	echo '<table class="table table-responsive border="0" width="100%" cellspacing="8" cellpadding="6">
	<thead>
		<tr>
	    <th align="center">Item</th>
	    <th align="right">Price Paid</th>
	    <th align="center">Quantity in Stock</th>
	    <th align="center">Quantity Ordered</th>
	    <th align="center">Shipped?</th>
	  </tr>
	</thead>
	<tbody>';
	
	// For confirming that the order has shipped:
	$shipped = true;
	
	// Print each item:
	do {
		
		// Create a row:
		echo '<tr>
		    <td align="left">' . $row['item'] . '</td>
		    <td align="right">' . $row['price_per'] . '</td>
		    <td align="center">' . $row['stock'] . '</td>
		    <td align="center">' . $row['quantity'] . '</td>
		    <td align="center">' . $row['sd'] . '</td>
		</tr>';
		
		if (!$row['sd']) $shipped = false;
						
	} while ($row = mysqli_fetch_array($r));
	
	// Complete the table and the form:
	echo '</tbody></table>';
	
	// Only show the submit button if the order hasn't already shipped:
	if (!$shipped) {
		echo '<div class="field"><p class="error">Note that actual payments will be collected once you click this button!</p><input type="submit" value="Ship This Order" class="btn btn-info" /></div>';	
	}
		
	// Complete the form:
	echo '</fieldset>
	</form>';
	echo '</div></div>';

} else { // No records returned!
	echo '<h3>Error2!</h3><p>This page has been accessed in error.</p>';
	include('../admin/includes/footer.html');
	exit();	
}

	include('../admin/includes/footer.html');
?>