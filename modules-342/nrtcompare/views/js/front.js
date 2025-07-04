$(document).ready(function() {
	
	compareRefreshStatus();
	
	$('body').on('show.bs.modal','.quickview',(function() {
		compareRefreshStatus();
	}));
	
    prestashop.on('updatedProductList', function (e) {
        compareRefreshStatus();
    });
	
    prestashop.on('updatedProduct', function (e) {
        compareRefreshStatus();
    });
	
    prestashop.on('updatedProductAjax', function (e) {
        compareRefreshStatus();
    });
			
	$('body').on('click', '.js-compare-add', function (event) {
		var self = this;
		prestashop.emit('clickCompareAdd', {
			dataset: self.dataset,
			self: self
		});
		event.preventDefault();
	});

	$('body').on('click', '.js-compare-remove', function (event) {
		var self = this;
		prestashop.emit('clickCompareRemove', {
			dataset: self.dataset
		});
		event.preventDefault();
	});

	$('body').on('click', '.js-compare-remove-all', function (event) {
		var self = this;
		prestashop.emit('clickCompareRemoveAll', {
			dataset: self.dataset
		});
		event.preventDefault();
	});

	prestashop.on('clickCompareAdd', function (elm) {
		
		if($('.js-compare-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).hasClass('loading')){
			return;
		}
		
		var data = {
			'process': 'add',
			'ajax': 1,
			'idProduct': elm.dataset.idProduct,
			'idProductAttribute': elm.dataset.idProductAttribute
		};
		
		$('.js-compare-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).addClass('loading');

		$.post(opCompare.actions, data, null, 'json').then(function (resp) {
			if (opCompare.enabled_notices && resp.notices) {						
				if($(resp.notices).find('.ax-n-success').length){
					toastr["success"](resp.notices);
				}else if($(resp.notices).find('.ax-n-info').length){
					toastr["info"](resp.notices);
				}else{
					toastr["error"](resp.notices);
				}
			}
			opCompare.ids = resp.productsIds;
			$('.js-compare-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).removeClass('loading');
			compareRefreshStatus();
				
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickCompareAdd', resp: resp });
		});
	});

	prestashop.on('clickCompareRemove', function (elm) {

		if($('.js-compare-remove-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).hasClass('loading')){
			return;
		}
		
		var data = {
			'process': 'remove',
			'ajax': 1,
			'idProduct': elm.dataset.idProduct,
			'idProductAttribute': elm.dataset.idProductAttribute
		};
		
		$('.js-compare-remove-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).addClass('loading');
		
		$.post(opCompare.actions, data, null, 'json').then(function (resp) {
			$('.js-compare-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).remove();
			
			opCompare.ids = resp.productsIds;
			compareRefreshStatus();
			
			if (opCompare.ids.length == 0) {
				$('#js-compare-table').remove();
				$('#js-compare-warning').show();
			}
		
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickCompareRemove', resp: resp });
		});
	});

	prestashop.on('clickCompareRemoveAll', function (elm) {
		
		if($('.js-compare-remove-all').hasClass('loading')){
			return;
		}
		
		var data = {
			'process': 'removeAll',
			'ajax': 1
		};
		
		$('.js-compare-remove-all').addClass('loading');
		
		$.post(opCompare.actions, data, null, 'json').then(function (resp) {
			
			opCompare.ids = resp.productsIds;
			compareRefreshStatus();
			
			$('#js-compare-table').remove();
			$('#js-compare-warning').show();
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickCompareRemoveAll', resp: resp });
		});
	});
		
	function compareRefreshStatus()
	{
		$('.js-compare').each(function(){
			
			var $el = $(this);

			var $idProduct = 0;
			var $idProductAttribute = 0;

            if($el.closest('.js-product-miniature').length){
			    $idProduct = $el.closest('.js-product-miniature').data('id-product');
            }else if($('#product_page_product_id').length){
			    $idProduct = $('#product_page_product_id').val();
            }

            $el.attr('data-id-product', $idProduct);
            $el.attr('data-id-product-attribute', $idProductAttribute);
			
			if (opCompare.ids.includes($idProduct + '-' + $idProductAttribute)){
				$el.removeClass('js-compare-add').addClass('added');
				$el.text(opCompare.alert.view);
				if (typeof $(this).attr('data-original-title') !== typeof undefined && $(this).attr('data-original-title') !== false) {
					$el.attr('data-original-title', opCompare.alert.view);
				}else{
					$el.attr('title', opCompare.alert.view);
				}
			}else{
				$el.addClass('js-compare-add').removeClass('added');
				$el.text(opCompare.alert.add);
				if (typeof $(this).attr('data-original-title') !== typeof undefined && $(this).attr('data-original-title') !== false) {
					$el.attr('data-original-title', opCompare.alert.add);
				}else{
					$el.attr('title', opCompare.alert.add);
				}
			}
			
			$el.addClass('js-compare-btn-'+$idProduct + '-' + $idProductAttribute);
			
		});
		
		$('.js-compare-nb').text(opCompare.ids.length);
						
	}
});
