function initZoom(){
	$('.easyzoom-product').easyZoom();
}	

$(document).ready(function () {
	
	initZoom();
	
	prestashop.on('updatedProductThumb',function (e) {
		initZoom();
	});	
	
	prestashop.on('updatedProduct', function (e) {
		initZoom();
	});
	
});