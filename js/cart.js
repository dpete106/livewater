
// Watch for the document to be ready:
$(function() {

	$('.nav-item a#home').removeClass('active');
	$('.nav-item a#cheese').removeClass('active');
	$('.nav-item a#goodies').removeClass('active');
	$('.nav-item a#sales').removeClass('active');
	$('.nav-item a#saved').removeClass('active');
	$('.nav-item a#cart').removeClass('active');
	$('.nav-item a#cart').addClass('active');
	
	localStorage.setItem('cart','1');
	
	$( "div.carticon" ).html( '<a class="nav-link" id="carticon" href="/livewater/cart.php">' +
			'<div class="icon-cart" style="float: left">' +
			'<div class="cart-line-1" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-2" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-3" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-wheel" style="background-color: #E5E9EA"></div>' +
			'</div></a>' ); 

	
}); // document ready.

function cartFunction() {
    var popup = document.getElementById("cartPopup");
    popup.classList.toggle("show");
}
