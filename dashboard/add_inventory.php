<?php
// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'Add Inventory';
include('../dashboard/includes/header.html');
// The header file begins the session.

require('../mysql.inc.php');

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
	// Need the product functions:
	require('../includes/product_functions.inc.php');

	// Check for a added inventory:
	if (isset($_POST['add']) && is_array($_POST['add'])) {
		
		// Need the product functions:
		//require('../includes/product_functions.inc.php');
		
		// Define the two queries:
		$q1 = 'UPDATE specific_coffees SET stock=stock+? WHERE id=?';
		$q2 = 'UPDATE non_coffee_products SET stock=stock+? WHERE id=?';

		// Prepare the statements:
		$stmt1 = mysqli_prepare($dbc, $q1);
		$stmt2 = mysqli_prepare($dbc, $q2);
		
		// Bind the variables:
		mysqli_stmt_bind_param($stmt1, 'ii', $qty, $id);
		mysqli_stmt_bind_param($stmt2, 'ii', $qty, $id);
		
		// Count the number of affected rows:
		$affected = 0;
		
		// Loop through each submitted value:
		foreach ($_POST['add'] as $sku => $qty) {
			// Validate the added quantity:
			if (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 1))) {

				// Parse the SKU:
				list($type, $id) = parse_sku($sku);
				// Determine which query to execute based upon the type:
				if ($type === 'beef' || $type === 'bread' || $type === 'cand' || $type === 'dairy' || $type === 'java' || $type === 'maple' || $type === 'produce') { // here for specific_coffees products type is always cand
					// Execute the query:
					mysqli_stmt_execute($stmt1);
					
					// Add to the affected rows:
					$affected += mysqli_stmt_affected_rows($stmt1);				

				} elseif ($type === 'goodies') { // non_coffee_products
					// Execute the query:
					mysqli_stmt_execute($stmt2);
					
					// Add to the affected rows:
					$affected += mysqli_stmt_affected_rows($stmt2);				

				}
				
			} // End of IF.

		} // End of FOREACH.
		
		// Print a message:
		echo "<h4>$affected Items(s) Were Updated!</h4>";

	} // End of $_POST['add'] IF.
	
	if (isset($_POST['reset']) && is_array($_POST['reset'])) {
		
		// Need the product functions:
		//require('../includes/product_functions.inc.php');
		
		// Define the two queries:
		$q1 = 'UPDATE specific_coffees SET stock=? WHERE id=?';
		$q2 = 'UPDATE non_coffee_products SET stock=? WHERE id=?';

		// Prepare the statements:
		$stmt1 = mysqli_prepare($dbc, $q1);
		$stmt2 = mysqli_prepare($dbc, $q2);
		
		// Bind the variables:
		mysqli_stmt_bind_param($stmt1, 'ii', $qty, $id);
		mysqli_stmt_bind_param($stmt2, 'ii', $qty, $id);
		
		// Count the number of affected rows:
		$affected = 0;
		
		// Loop through each submitted value:
		foreach ($_POST['reset'] as $sku => $qty) {
		//echo "<h4>qty = $qty</h4>";
			// Validate the added quantity:
			//if (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 1))) {
			if (filter_var($qty, FILTER_VALIDATE_INT, array('min_range' => 1)) || $qty == "zero") {
				if ($qty == "zero") {
					$qty = 0;
				}
				// Parse the SKU:
				list($type, $id) = parse_sku($sku);
				// Determine which query to execute based upon the type:
				if ($type === 'beef' || $type === 'bread' || $type === 'cand' || $type === 'dairy' || $type === 'java' || $type === 'maple' || $type === 'produce') { // here for specific_coffees products type is always cand
					// Execute the query:
					mysqli_stmt_execute($stmt1);
					
					// Add to the affected rows:
					$affected += mysqli_stmt_affected_rows($stmt1);				

				} elseif ($type === 'goodies') { // non_coffee_products
					// Execute the query:
					mysqli_stmt_execute($stmt2);
					
					// Add to the affected rows:
					$affected += mysqli_stmt_affected_rows($stmt2);				

				}
				
			} // End of IF.

		} // End of FOREACH.
		
		// Print a message:
		echo "<h4>$affected Items(s) Were Reset!</h4>";

	} // End of $_POST['reset'] IF.

} // End of the submission IF.

?>
<div style="background-color:rgba(192,192,192);" class="jumbotron"><div class="module">

<h3>Add Inventory</h3>

<form action="add_inventory.php" method="post" accept-charset="utf-8">

	<fieldset><legend>Indicate how many additional quantity of each product should be added to the inventory.</legend>
		<legend>Use numbers to Reset an item's inventory.</legend>
		<legend>To Reset inventory to zero, key in the text "zero".</legend>
		<table class="table table-responsive table-sm" border="0" width="100%" cellspacing="4" cellpadding="4">
		<thead>
			<tr>
		    <th align="right">Item</th>
		    <th align="right">Normal Price</th>
		    <th align="right">Quantity in Stock</th>
		    <th align="center">Add</th>
		    <th align="center">Reset</th>
		  </tr></thead>
		<tbody>		
		<?php
		
		// Fetch every product:
		$q = '(SELECT CONCAT("G", ncp.id) AS sku, ncc.category, ncp.name, FORMAT(ncp.price/100, 2) AS price, ncp.stock FROM non_coffee_products AS ncp INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id ORDER BY category, name) UNION (SELECT CONCAT("C", sc.id), gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole),FORMAT(sc.price/100, 2), sc.stock FROM specific_coffees AS sc INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id ORDER BY sc.general_coffee_id, sc.size_id, sc.caf_decaf, sc.ground_whole)';
		$r = mysqli_query($dbc, $q);
		
		// Display form elements for each product:
		while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
			echo '<tr>
		    <td align="right">' . htmlspecialchars($row['category']) . '::' . htmlspecialchars($row['name']) . '</td>
		    <td align="center">' . $row['price'] .'</td>
		    <td align="center">' . $row['stock'] .'</td>
		    <td align="center"><input type="text" name="add[' . $row['sku'] . ']"  id="add[' . $row['sku'] . ']" size="5" class="small" /></td>
		    <td align="center"><input type="text" name="reset[' . $row['sku'] . ']"  id="reset[' . $row['sku'] . ']" size="5" class="small" /></td>
		  </tr>';
		}
		
?>

	</tbody></table>
	<div class="field"><input type="submit"  class="btn btn-info" value="Add The Inventory" class="button" />	
	<input type="submit"  class="btn btn-info" value="Reset The Inventory" class="button" /></div>	
	</fieldset>
</form>
</div></div>
<script type="text/javascript" src="/livewater/js/add_inventory.js"></script>

<?php
 include('../dashboard/includes/footer.html');
?>