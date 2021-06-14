<?php

// This is the adminstrative home page.

// Require the configuration before any PHP code as configuration controls error reporting.
require('../includes/config.inc.php');

// Set the page title and include the header:
$page_title = 'Livewater - Administration';
include('../dashboard/includes/header.html');
// The header file begins the session.
?>
<div style="background-color:rgba(192,192,192);" class="jumbotron">
<h3>Links</h3>
</div>
<script type="text/javascript" src="/livewater/js/admin.js"></script>

<?php include('../dashboard/includes/footer.html'); ?>