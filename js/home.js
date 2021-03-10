
// Watch for the document to be ready:
$(function() {

	//$('.nav li').siblings().removeClass('active');
	//$('.nav li#cheese').addClass('active');
	$('.nav-item a#home').removeClass('active');
	$('.nav-item a#beef').removeClass('active');
	$('.nav-item a#java').removeClass('active');
	$('.nav-item a#goodies').removeClass('active');
	$('.nav-item a#cheese').removeClass('active');
	$('.nav-item a#produce').removeClass('active');
	$('.nav-item a#maple').removeClass('active');
	$('.nav-item a#sales').removeClass('active');
	$('.nav-item a#saved').removeClass('active');
	$('.nav-item a#cart').removeClass('active');
	$('.nav-item a#home').addClass('active');

	
}); // document ready.

function homeFunction() {
    var popup = document.getElementById("homePopup");
    popup.classList.toggle("show");
}


