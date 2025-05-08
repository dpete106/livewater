<?php # connect.php
ob_start();
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
  <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2BVD1C8EZM"></script>
    <script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-2BVD1C8EZM');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="A Vermont grass based dairy farm operation sustainably producing organic cheese, milk and maple syrup products. Please shop at the Farm Stand.">
    <meta name="keywords" content="vermont milk cheese maple syrup beef">
	<meta name="author" content="livewaterfarm.net">
    <meta name="generator" content="Jekyll v4.0.1">
	<title>a Vermont dairy farm producing cheese and maple syrup&#124; Livewater Farm&amp; Dairy;</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script> 
    $(function(){
      $("#includedContent").load("http://localhost/livewater/includes/main_header.html"); 
    });
    $(function(){
      $("#includedMenu").load("http://localhost/livewater/includes/header_menu.html"); 
    });
    </script> 
	
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	 
    <!-- Bootstrap core CSS -->
    <link href="http://localhost/livewater/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicons -->
	<link rel="icon" href="/favicon.ico">
	<meta name="theme-color" content="#563d7c">


<style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }

      .btn-bd-primary {
        --bd-violet-bg: #712cf9;
        --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-violet-bg);
        --bs-btn-border-color: var(--bd-violet-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6528e0;
        --bs-btn-hover-border-color: #6528e0;
        --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #5a23c8;
        --bs-btn-active-border-color: #5a23c8;
      }

      .bd-mode-toggle {
        z-index: 1500;
      }

      .bd-mode-toggle .dropdown-menu .active .bi {
        display: block !important;
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="http://localhost/livewater/css/home.css" rel="stylesheet">

    <link href="http://localhost/livewater/css/contact.css" rel="stylesheet">
 </head>
  <body>
  
  <div id="includedMenu"></div>

<main role="main">

  <div id="includedContent"></div>

<?php
if (isset($_POST['submitted'])) { // Handle the form.
	// Trim all the incoming data:
	$trimmed = array_map('trim', $_POST);
	
	// Assume invalid values:
	$fn = $ln = $ms = $e = FALSE;
	
		
		$fn = stripslashes( strip_tags( $trimmed['first_name'] ) );
		$ln = stripslashes( strip_tags( $trimmed['last_name'] ) );
		$ms = stripslashes( strip_tags( $trimmed['message'] ) );
		$e = stripslashes( strip_tags( $trimmed['email'] ) );

	if ($fn && $ln && $ms && $e) { // If everything's OK...
				$ef = $e;
				$et = 'farmer@livewaterfarm.net';
				
	//	$emailto = $et;
	//	$toname = 'LIVEWATER FARM';
	//	$emailfrom = $ef;
	//	$fromname = $fn . ' ' . $ln;
	//	$subject = 'Customer Message';
	//	$messagebody = $ms;
	//	$headers = 
	//'Return-Path: ' . $emailfrom . "\r\n" . 
	//'From: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" . 
	//'X-Priority: 3' . "\r\n" . 
	//'X-Mailer: PHP ' . phpversion() .  "\r\n" . 
	//'Reply-To: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
	//'MIME-Version: 1.0' . "\r\n" . 
	//'Content-Transfer-Encoding: 8bit' . "\r\n" . 
	//'Content-Type: text/plain; charset=UTF-8' . "\r\n";
	//	$params = '-f ' . $emailfrom;
	
	// new code	
	$emailto = $et;
    $toname = 'LIVEWATER FARM & DAIRY';
    $emailfrom = $ef;
    $fromname = $fn . ' ' . $ln;
    $subject = 'Customer Message';
    $messagebody = $ms;
    $headers = 
	'Return-Path: ' . $emailfrom . "\r\n" . 
	'From: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" . 
	'X-Priority: 3' . "\r\n" . 
	'X-Mailer: PHP ' . phpversion() .  "\r\n" . 
	'Reply-To: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
	'MIME-Version: 1.0' . "\r\n" . 
	'Content-Transfer-Encoding: 8bit' . "\r\n" . 
	'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $params = '-f ' . $emailfrom;	
		
		$test = 1;
		$test = mail($emailto, $subject, $messagebody, $headers, $params);
		if ($test){
			echo '<div class="alert alert-success">Thank you for sending a message to Livewater Farm & Dairy.</div>';
		}

	} else { // If one of the data tests failed.
		echo '<div class="alert alert-danger">Please re-enter your message and try again.</div>';
		
	}
}

?>	

<body class="text-center">
<!--    <form class="form-signin">
      <h1 class="h3 mb-3 font-weight-normal">Send a Message to Livewater Farm & Dairy</h1>
      <label for="inputEmail" class="sr-only">Email address</label>
      <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
      <label for="inputPassword" class="sr-only">Password</label>
      <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
      <button class="btn btn-lg btn-primary btn-block" type="submit">Send</button>
      <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
    </form> -->
<div class="col-lg-12">
	<h2>Contact Livewater Farm & Dairy</h2>
	<form class="form-signin" action="http://localhost/livewater/contact.php" method="post" onsubmit="return validateRecaptcha()";>
			<div class="form-group">
			<!--<label for="inputFirstName">First Name</label>-->
			<input type="text" class="form-control" id="inputFirstName" placeholder="FirstName" name="first_name" value="" required autofocus>
			</div>
		
			<div class="form-group">
			<!--<label for="inputLastName">Last Name</label>-->
			<input type="text" class="form-control" id="inputLastName" placeholder="LastName" name="last_name" value="" required>
			</div>
		
			<div class="form-group">
			<label for="inputLastName">Message</label>
			<textarea rows=5 cols=50 class="form-control" id="inputMessage" name="message" required></textarea>
			</div>
		
			<div class="form-group">
			<!--<label for="inputEmail">Email Address</label>-->
			<input type="email" class="form-control" id="inputEmail" placeholder="Your Email" name="email" value="" required>
			</div>
		
			<div class="form-group">
			
			<div class="g-recaptcha" data-sitekey="6Lch8SorAAAAAB8vtHs6hsvk4hbm6M0ZMQKhXskg"></div>
			
			<button class="btn btn-lg btn-primary btn-block" type="submit">Send Email</button>
			<input type="hidden" name="submitted" value="TRUE" />
			</div>
			
			<script type="text/javascript">
			var onloadCallback = function() {
			};
			function validateRecaptcha() {
				var response = grecaptcha.getResponse();
				if (response.length === 0) {
					alert("you are a robot!");
					return false;
				} else {
				 //alert("validated");
					return true;
				}
			}
			</script>
	</form>
</div>
<div class="col-lg-12">
	<h2>Phone</h2>
	<p>(802) 222-1525</p>
</div>
</body>
</main>

<footer class="footer mt-auto py-3">
  <div class="container">
    <p class="float-right"><a href="#">Back to top</a></p>
    <p>&copy; 2025 Livewater Farm & Dairy, Westminster West, VT, &middot; Vermont Cheese</p>
  </div>
</footer>
<!--
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="/docs/4.5/assets/js/vendor/jquery.slim.min.js"><\/script>')</script>
-->	
<script src="http://localhost/livewater/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script></body>

</html>
<?php

ob_end_flush();

?>
