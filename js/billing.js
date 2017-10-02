
// Watch for the document to be ready:
$(function() {

  $('#billing_form').submit(function() {
		var error = false;
		$('input[type=submit]', this).attr('disabled', 'disabled');

		var cc_number = $('#cc_number').val(), cc_cvv = $('#cc_cvv').val(), cc_exp_month = $('#cc_exp_month').val(), cc_exp_year = $('#cc_exp_year').val();
		
		if (!Stripe.validateCardNumber(cc_number)) {
			error = true;
			reportError('The credit card number appears to be invalid.');
		}

		
		if (!Stripe.validateExpiry(cc_exp_month, cc_exp_year)) {
			error = true;
			reportError('The expiration date appears to be invalid.');
		}
		if (!error) {
			Stripe.createToken({
				number: cc_number,
				cvc: cc_cvv,
				exp_month: cc_exp_month,
				exp_year: cc_exp_year
			}, stripeResponseHandler);
		}

		return false;

	}); // form submission
	
}); // document ready.

// Function handles the Stripe response:
function stripeResponseHandler(status, response) {

	if (response.error) {
		reportError(response.error.message);
	} else { // No errors, submit the form.
	  var billing_form = $('#billing_form');
	  var token = response.id;
	  billing_form.append("<input type='hidden' name='token' value='" + token + "' />");
	  
	  billing_form.get(0).submit();
	}
	
} // End of stripeResponseHandler() function.

function reportError(msg) {
	$('#error_span').text(msg);
    $('input[type=submit]').prop('disabled', false);
	return false;
}

