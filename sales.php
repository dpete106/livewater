<?php
// Require the configuration before any PHP code:
require('./includes/config.inc.php');

// Include the header file:
$page_title = 'Sale Items';
include('./includes/header.html');

// Require the database connection:
require('./mysql.inc.php');

// shipping
$q = "(SELECT CONCAT('G', ncp.id) AS sku, sa.price AS sale_price, sa.product_type AS product_type, sa.ship, ncc.category, ncp.image, ncp.name, ncp.price AS price, ncp.stock, ncp.description FROM sales AS sa INNER JOIN non_coffee_products AS ncp ON sa.product_id=ncp.id INNER JOIN non_coffee_categories AS ncc ON ncc.id=ncp.non_coffee_category_id WHERE sa.ship=0 AND sa.product_type='goodies' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ) )
UNION (SELECT CONCAT('B', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='beef' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('C', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='cand' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('D', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='dairy' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('J', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='java' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('O', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='bread' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('P', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='produce' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
UNION (SELECT CONCAT('M', sc.id), sa.price, sa.product_type AS product_type, sa.ship, gc.category, gc.image, CONCAT_WS(' - ', s.size, sc.caf_decaf, sc.ground_whole), sc.price, sc.stock, gc.description FROM sales AS sa INNER JOIN specific_coffees AS sc ON sa.product_id=sc.id INNER JOIN sizes AS s ON s.id=sc.size_id INNER JOIN general_coffees AS gc ON gc.id=sc.general_coffee_id WHERE sa.ship=0 AND sa.product_type='maple' AND ((NOW() BETWEEN sa.start_date AND sa.end_date) OR (NOW() > sa.start_date AND sa.end_date IS NULL) ))
";
$r = mysqli_query($dbc, $q);

// For debugging purposes:
if (!$r) echo mysqli_error($dbc);

if (mysqli_num_rows($r) > 0) {
	include('./views/list_sales.html');
} else {
	include('./views/noproducts.html');
}

// Include the footer file:
include('./includes/footer.html');
?>