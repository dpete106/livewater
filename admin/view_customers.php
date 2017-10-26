<?php
// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'View All Customers';
include('../admin/includes/header.html');
// The header file begins the session.
require('../mysql.inc.php');

echo '<div style="background-color:rgba(192,192,192);" class="jumbotron"><div class="module">';
echo '<h3>View Customers With No Orders</h3><table class="table table-responsive table-sm" border="0" width="100%" cellspacing="4" cellpadding="4" id="orders">
<thead>
	<tr>
    <th align="center">ID</th>
    <th align="center">Customer Name</th>
    <th align="center">Email</th>
    <th align="center">Date</th>

  </tr></thead>
<tbody>';

// Make the query:
//$q = 'SELECT o.id, FORMAT(total/100, 2) AS total, c.id AS cid, CONCAT(last_name, ", ", first_name) AS name, city, state, zip, email
//FROM orders AS o JOIN customers AS c ON (o.customer_id = c.id) GROUP BY o.id DESC';
$q = 'SELECT c.id AS cid, CONCAT(last_name, ", ", first_name) AS name, email, CAST(date_created AS DATE) AS DATE_CREATED
FROM customers AS c  LEFT JOIN orders AS o ON (c.id = o.customer_id) WHERE o.customer_id IS NULL';

$r = mysqli_query($dbc, $q);
while ($row = mysqli_fetch_array ($r, MYSQLI_ASSOC)) {
	echo '<tr>
    <td align="center">' . $row['cid'] . '</td>
    <td align="center"><a href="delete_customer.php?cid=' . $row['cid'] . '">' . htmlspecialchars( $row['name']) .'</a></td>
    <td align="center">' . $row['email'] .'</td>
    <td align="center">' . $row['DATE_CREATED'] .'</td>
  </tr>';
// <td align="center"><a href="view_order.php?oid=' . $row['id'] . '">' . $row['id'] . '</a></td>
}

echo '</tbody></table>';
echo '</div></div>';
?>

<script type="text/javascript" src="/livewater/js/view_customers.js"></script>

<?php
// Include the footer file to complete the template.
 include('../admin/includes/footer.html');
?>