<?php


try {

	// Include the Stripe library:
	require_once('vendor/autoload.php');
	$stripe = array(
					'secret_key'      => 'sk_test_GBsb65uAB1MefNcwXKRBmjp6',
					'publishable_key' => 'pk_test_JRGK5txjD4IIgyJU5ztDwSz1'
	);
	\Stripe\Stripe::setApiKey($stripe['secret_key']);


	// set your secret key: remember to change this to your live secret key in production
	// see your keys here https://manage.stripe.com/account
	// Charge the order:
	$charge = \Stripe\Charge::retrieve('ch_102jyI2BAZoCjj35RP3QgGkT');
	$charge->capture();

	echo '<pre>' . print_r($charge, 1) . '</pre>';exit;

} catch (\Stripe\Error\Card $e) { // Stripe declined the charge.
	$e_json = $e->getJsonBody();
	$err = $e_json['error'];
	$message = $err['message'];
	echo '<script type="text/javascript">alert("1'.$message.'");</script>';

} catch (Exception $e) { // Try block failed somewhere else.
	$message = $e->getMessage();
	echo '<script type="text/javascript">alert("7'.$message.'");</script>';
}
