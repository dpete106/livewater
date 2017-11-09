
// Watch for the document to be ready:
$(function() {

	
	var cart = localStorage.getItem('cart');
	
	if (cart == null){
		$( "div.carticon" ).html( '<a class="nav-link" id="carticon" href="/livewater/cart.php">' +
			'<div class="icon-cart" style="float: left">' +
			'<div class="cart-line-1" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-2" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-wheel" style="background-color: #E5E9EA"></div>' +
			'</div></a>' ); 
	} else {
		$( "div.carticon" ).html( '<a class="nav-link" id="carticon" href="/livewater/cart.php">' +
			'<div class="icon-cart" style="float: left">' +
			'<div class="cart-line-1" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-2" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-line-3" style="background-color: #E5E9EA"></div>' +
			'<div class="cart-wheel" style="background-color: #E5E9EA"></div>' +
			'</div></a>' ); 
	}
	
}); // document ready.

