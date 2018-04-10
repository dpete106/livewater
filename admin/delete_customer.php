<?php
// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'Delete A Customer With No Order';
include('../admin/includes/header.html');
// The header file begins the session.

// Validate the customer ID:
$customer_id = false;
if (isset($_GET['cid']) && (filter_var($_GET['cid'], FILTER_VALIDATE_INT, array('min_range' => 1))) ) { // First access
	$customer_id = $_GET['cid'];
	$_SESSION['customer_id'] = $customer_id;
} elseif (isset($_SESSION['customer_id']) && (filter_var($_SESSION['customer_id'], FILTER_VALIDATE_INT, array('min_range' => 1))) ) {
	$customer_id = $_SESSION['customer_id'];
}

// Stop here if there's no $customer_id:
if (!$customer_id) {
	echo '<h3>Error!</h3><p>This page has been accessed in error.</p>';
	include('../admin/includes/footer.html');
	exit();
}

require('../mysql.inc.php');

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
	
	$q = 'SELECT c.id AS cid, CONCAT(last_name, ", ", first_name) AS name, email, CAST(date_created AS DATE) AS DATE_CREATED
	FROM customers AS c  WHERE c.id = '. $customer_id .'';

	$r = mysqli_query($dbc, $q);
	
	if (!$r) echo mysqli_error($dbc);
	
	if (mysqli_num_rows($r) === 1) { // Display the order info:
	
		$q = "DELETE FROM customers WHERE id=$customer_id";
		$r = mysqli_query($dbc, $q);
	
		if (!$r) echo mysqli_error($dbc);
	
		//echo '<script> location.replace("/admin/view_customers.php"); </script>';
		echo '<script> location.replace("/livewater/admin/view_customers.php"); </script>';
		exit();
	}				

} // End of the submission IF.

// Define the query:
$q = 'SELECT c.id AS cid, CONCAT(last_name, ", ", first_name) AS name, email, CAST(date_created AS DATE) AS DATE_CREATED
FROM customers AS c  WHERE c.id = '. $customer_id .'';

// Execute the query:
$r = mysqli_query($dbc, $q);
if (mysqli_num_rows($r) === 1) { // Display the order info:
	echo '<div style="background-color:rgba(192,192,192);" class="jumbotron"><div class="module">';

	echo '<h3>Delete A Customer With No Order</h3>
	<form action="delete_customer.php" method="post" accept-charset="utf-8">
		<fieldset>';
		
	// Get the first row:
	$row = mysqli_fetch_array($r, MYSQLI_ASSOC);


	// Create the table:
	echo '<table class="table table-responsive border="0" width="100%" cellspacing="8" cellpadding="6">
	<thead>
		<tr>
			<th align="center">ID</th>
			<th align="center">Customer Name</th>
			<th align="center">Email</th>
			<th align="center">Date</th>
	  </tr>
	</thead>
	<tbody>';
	
	// For confirming that the order has shipped:
	$shipped = true;
	
	// Print each item:
	do {
		
		// Create a row:
		echo '<tr>
				<td align="center">' . $row['cid'] . '</td>
				<td align="center"><a href="view_customers.php">' . htmlspecialchars( $row['name']) .'</a></td>
				<td align="center">' . $row['email'] .'</td>
				<td align="center">' . $row['DATE_CREATED'] .'</td>
		</tr>';
		
						
	} while ($row = mysqli_fetch_array($r));
	
	// Complete the table and the form:
	echo '</tbody></table>';
	
	// Only show the submit button if the order hasn't already shipped:
	if ($shipped) {
		echo '<div class="field"><input type="submit" value="Delete This Customer" class="btn btn-info" /></div>';	
	}
		
	// Complete the form:
	echo '</fieldset>
	</form>';
	echo '</div></div>';

} else { // No records returned!
	echo '<h3>Error!</h3><p>This page has been accessed in error.</p>';
	include('../admin/includes/footer.html');
	exit();	
}
	echo '<script type="text/javascript" src="/livewater/js/view_customers.js"></script>
';
	include('../admin/includes/footer.html');
?>