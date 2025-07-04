$(document).ready(function() {
	
	wishlistRefreshStatus();

	$('body').on('show.bs.modal','.quickview',(function() {
		wishlistRefreshStatus();
	}));
	
    prestashop.on('updatedProductList', function (e) {
        wishlistRefreshStatus();
    });
	
    prestashop.on('updatedProduct', function (e) {
        wishlistRefreshStatus();
    });
	
    prestashop.on('updatedProductAjax', function (e) {
        wishlistRefreshStatus();
    });
	
	$('body').on('click', '.js-wishlist-add', function (event) {
		var self = this;
		prestashop.emit('clickWishListAdd', {
			dataset: self.dataset,
			self: self
		});
		event.preventDefault();
	});

	$('body').on('click', '.js-wishlist-remove', function (event) {
		var self = this;
		prestashop.emit('clickWishListRemove', {
			dataset: self.dataset
		});
		event.preventDefault();
	});

	$('body').on('click', '.js-wishlist-remove-all', function (event) {
		var self = this;
		prestashop.emit('clickWishListRemoveAll', {
			dataset: self.dataset
		});
		event.preventDefault();
	});

	prestashop.on('clickWishListAdd', function (elm) {
		
		if($('.js-wishlist-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).hasClass("loading")){
			return;
		}
		
		var data = {
			'process': 'add',
			'ajax': 1,
			'idProduct': elm.dataset.idProduct,
			'idProductAttribute': elm.dataset.idProductAttribute
		};
		
		$('.js-wishlist-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).addClass('loading');
		
		$.post(opWishList.actions, data, null, 'json').then(function (resp) {
			if(!resp.is_logged){
				wishlistShowLogin(elm.dataset.idProduct, elm.dataset.idProductAttribute);
			}else{
				if (opWishList.enabled_notices && resp.notices) {						
					if($(resp.notices).find('.ax-n-success').length){
						toastr["success"](resp.notices);
					}else if($(resp.notices).find('.ax-n-info').length){
						toastr["info"](resp.notices);
					}else{
						toastr["error"](resp.notices);
					}
				}
				opWishList.ids = resp.productsIds;
				$('.js-wishlist-btn-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).removeClass('loading');
				wishlistRefreshStatus();	
			}
			
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickWishListAdd', resp: resp });
		});
	});

	prestashop.on('clickWishListRemove', function (elm) {

		if($('.js-wishlist-remove-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).hasClass("loading")){
			return;
		}
		
		var data = {
			'process': 'remove',
			'ajax': 1,
			'idProduct': elm.dataset.idProduct,
			'idProductAttribute': elm.dataset.idProductAttribute
		};
		
		$('.js-wishlist-remove-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).addClass('loading');
		
		$.post(opWishList.actions, data, null, 'json').then(function (resp) {		
			
			if(!resp.is_logged){
				wishlistShowLogin(elm.dataset.idProduct, elm.dataset.idProductAttribute);
			}else{
				$('.js-wishlist-' + elm.dataset.idProduct + '-' + elm.dataset.idProductAttribute).remove();
				opWishList.ids = resp.productsIds;
				wishlistRefreshStatus();
				if (opWishList.ids.length == 0) {
					$('#js-wishlist-table').remove();
					$('#js-wishlist-warning').show();
				}
			}
								
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickWishListRemove', resp: resp });
		});
	});

	prestashop.on('clickWishListRemoveAll', function (elm) {
		
		if($('.js-wishlist-remove-all').hasClass('loading')){
			return;
		}
		
		var data = {
			'process': 'removeAll',
			'ajax': 1
		};
		
		$('.js-wishlist-remove-all').addClass('loading');
		
		$.post(opWishList.actions, data, null, 'json').then(function (resp) {
			if(!resp.is_logged){
				wishlistShowLogin(null, null);
			}else{
                opWishList.ids = resp.productsIds;
                wishlistRefreshStatus();
                
                $('#js-wishlist-table').remove();
                $('#js-wishlist-warning').show();
			}
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'clickWishListRemoveAll', resp: resp });
		});
	});
	
	function wishlistShowLogin($idProduct, $idProductAttribute)
	{
		var data = {
			'process': 'login',
			'ajax': 1,
			'current_url': prestashop.urls.current_url
		};
		
		$.post(opWishList.login, data, null, 'json').then(function (resp) {
			$('#wishlist_login').html(resp.html);
			$('#modal_wishlist').modal('show');
			if(!(typeof $idProduct === 'undefined' || typeof $idProductAttribute === 'undefined')){
				$('.js-wishlist-btn-' + $idProduct + '-' + $idProductAttribute).removeClass('loading');
			}
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'wishlistShowLogin', resp: resp });
		});
		
	}
		
	function wishlistRefreshStatus()
	{
		$('.js-wishlist').each(function(){
			
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
			
			if (opWishList.ids.includes($idProduct + '-' + $idProductAttribute)){
				$el.removeClass('js-wishlist-add').addClass('added');
				$el.text(opWishList.alert.view);
				if (typeof $(this).attr('data-original-title') !== typeof undefined && $(this).attr('data-original-title') !== false) {
					$el.attr('data-original-title', opWishList.alert.view);
				}else{
					$el.attr('title', opWishList.alert.view);
				}
			}else{
				$el.addClass('js-wishlist-add').removeClass('added');
				$el.text(opWishList.alert.add);
				if (typeof $(this).attr('data-original-title') !== typeof undefined && $(this).attr('data-original-title') !== false) {
					$el.attr('data-original-title', opWishList.alert.add);
				}else{
					$el.attr('title', opWishList.alert.add);
				}
			}
			
			$el.addClass('js-wishlist-btn-'+$idProduct + '-' + $idProductAttribute);
			
		});
		
		$('.js-wishlist-nb').text(opWishList.ids.length);
						
	}
	
	$('#wishlist-clipboard-btn').on('click', function () {

		var $this = $(this);

		$this.closest('.input-group').find('input.js-to-clipboard').select();

		if (document.execCommand('copy')) {
			$this.text($this.data('textCopied'));
			setTimeout(function () {
				$this.text($this.data('textCopy'));
			}, 1500);
		}
		
	});
	
});