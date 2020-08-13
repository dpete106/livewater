<?php
$body_plain = "Thank you for your order. Your order number is {$_SESSION['order_id']}. All orders are processed on the next business day. You will be contacted in case of any delays.\n\n";

$body_html = file_get_contents('includes/plain_header.html');
$body_html .=  '<p>Thank you for your order. Your order number is ' . $_SESSION['order_id'] . '. All orders are processed on the next business day. You will be contacted in case of any delays.</p>
<table border="0" cellspacing="3" cellpadding="3">
	<tr>
		<th align="center">Item</th>
		<th align="center">Quantity</th>
		<th align="right">Price</th>
		<th align="right">Subtotal</th>
	</tr>';

// Get the cart contents for the confirmation email:
$oid = $_SESSION['order_id'];
//$q = 'SELECT oc.quantity, oc.price_per, (oc.quantity*oc.price_per) AS subtotal, ncc.category, ncp.name, o.total, o.shipping FROM order_contents AS oc INNER JOIN non_coffee_products AS ncp ON oc.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id INNER JOIN orders AS o ON oc.order_id=o.id WHERE oc.product_type="goodies" AND oc.order_id="'. $oid .'" UNION SELECT oc.quantity, oc.price_per, (oc.quantity*oc.price_per), gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), o.total, o.shipping FROM order_contents AS oc INNER JOIN specific_coffees AS sc ON oc.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id INNER JOIN orders AS o ON oc.order_id=o.id  WHERE oc.product_type="coffee" AND oc.order_id="'. $oid .'"';
$q = 'SELECT oc.quantity, oc.price_per, (oc.quantity*oc.price_per) AS subtotal, ncc.category, ncp.name, o.total, o.shipping FROM order_contents AS oc INNER JOIN non_coffee_products AS ncp ON oc.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id INNER JOIN orders AS o ON oc.order_id=o.id WHERE oc.product_type="goodies" AND oc.order_id="'. $oid .'" 
UNION SELECT oc.quantity, oc.price_per, (oc.quantity*oc.price_per), gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), o.total, o.shipping FROM order_contents AS oc INNER JOIN specific_coffees AS sc ON oc.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id INNER JOIN orders AS o ON oc.order_id=o.id  WHERE oc.product_type="coffee" AND oc.order_id="'. $oid .'"
UNION SELECT oc.quantity, oc.price_per, (oc.quantity*oc.price_per), gc.category, CONCAT_WS(" - ", s.size, sc.caf_decaf, sc.ground_whole), o.total, o.shipping FROM order_contents AS oc INNER JOIN specific_coffees AS sc ON oc.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id INNER JOIN orders AS o ON oc.order_id=o.id  WHERE oc.product_type="maple" AND oc.order_id="'. $oid .'"';
$r = mysqli_query($dbc, $q);
// For debugging purposes:
if (!$r) echo mysqli_error($dbc);
// Fetch each product:
while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
	
	// Add to the plain version:
	$body_plain .= "{$row['category']}::{$row['name']} ({$row['quantity']}) @ \$" . number_format($row['price_per']/100, 2) . " each: $" . number_format($row['subtotal']/100, 2) . "\n";
	
	// Add to the HTML:
	$body_html .= '<tr><td>' . $row['category'] . '::' . $row['name'] . '</td> 
		<td align="center">' . $row['quantity'] . '</td>
		<td align="right">$' . number_format($row['price_per']/100, 2) . '</td>
		<td align="right">$' . number_format($row['subtotal']/100, 2) . '</td>
	</tr>
	';
	
	// For reference after the loop:
	$shipping = number_format($row['shipping']/100, 2);
	$total = number_format($row['total']/100, 2);

} // End of WHILE loop. 

// Clear the stored procedure results:
mysqli_free_result($r);

// Add the shipping:
$body_plain .= "Shipping: \$$shipping\n";
$body_html .= '<tr>
	<td colspan="2"> </td><th align="right">Shipping</th>
	<td align="right">$' . $shipping . '</td>
</tr>
';

// Add the total:
$body_plain .= "Total: \$$total\n";
$body_html .= '<tr>
	<td colspan="2"> </td><th align="right">Total</th>
	<td align="right">$' . $total . '</td>
</tr>
';

// Complete the HTML body:
$body_html .= '</table></body></html>';

// Uses Composer to autoload the Zend Framework files:
require_once('./vendorZend/autoload.php');
// Create a new mail:

use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

// Create the parts:

$html = new MimePart($body_html);
$html->type = "text/html";

$plain = new MimePart($body_plain);
$plain->type = "text/plain";

// Create the message:

$body = new MimeMessage();
$body->setParts(array($plain, $html));
 
// Establish the email parameters:

$mail = new Mail\Message();
$mail->setFrom('dpete106@gmail.com');
$mail->addTo($_SESSION['email']);
$mail->setSubject("Order #{$_SESSION['order_id']} at the Coffee Site");
$mail->setEncoding("UTF-8");
$mail->setBody($body);
$mail->getHeaders()->get('content-type')->setType('multipart/alternative');
//echo $body_html;
//echo $body_plain; die();

// Send the email:

$transport = new Mail\Transport\Sendmail();
//$transport->send($mail);
