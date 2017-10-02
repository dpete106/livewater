<?php

// This is the adminstrative home page.

// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'Livewater - Administration';
include('../admin/includes/header.html');
// The header file begins the session.
?>
<div style="background-color:rgba(192,192,192);" class="jumbotron"><div class="module">
<h3>Links</h3>
</div></div>

<?php include('../admin/includes/footer.html'); ?>