
$(document).ready(function () {

	$('body').on('hidden.bs.modal','#nrtshoppingcart-modal',(function(event) { 
		prestashop.emit('updateProduct', {
			reason: event.currentTarget.dataset,
			event,
		});
	}));	

	prestashop.buy_now_cart = false;
	
	$('body').on('click', '[data-button-action=add-to-cart], .js-add-to-cart', function (event) {
		if($(this).closest('form').find('input[name=qty]').val() > 0){
			if($(this).hasClass('js-buy-now')){
				prestashop.buy_now_cart = true;
			}
			$(this).addClass('loading').prop('disabled', true);
		}
	});	

	$('body').on('click', '.js-add-to-cart', function (event) {
        event.preventDefault();

        const $form = $(event.currentTarget.form);
        $form.find('input[name="token"]').val(prestashop.static_token);
        const query = `${$form.serialize()}&add=1&action=update`;
        const actionURL = $form.attr('action');

        $.post(actionURL, query, null, 'json')
          .then((resp) => {
            if (!resp.hasError) {
              prestashop.emit('updateCart', {
                reason: {
                  idProduct: resp.id_product,
                  idProductAttribute: resp.id_product_attribute,
                  idCustomization: resp.id_customization,
                  linkAction: 'add-to-cart',
                  cart: resp.cart,
                },
                resp,
              });
            } else {
              prestashop.emit('handleError', {
                eventType: 'addProductToCart',
                resp,
              });
            }
          })
          .fail((resp) => {
            prestashop.emit('handleError', {
              eventType: 'addProductToCart',
              resp,
            });
          });
	});	

	$('body').on('click', '[data-link-action="delete-from-cart"], [data-link-action="remove-voucher"]', function (event) {
		$('body').addClass('cart-processing');
	});	
	
	$('body').on('click', '[data-link-action="delete-all-cart"]', function (event) {
		var refreshURL = opShoppingCart.ajax;
		var requestData = {};
		requestData = {
			action: 'delete-all-cart'
		};
		$('body').addClass('cart-processing');
		$.post(refreshURL, requestData).then(function (resp) {
			$('[data-link-action="delete-from-cart"]').first().click();
		});
	});
	
	$('.js-cart-nbr').text(prestashop.cart.products_count);

	$('.js-cart-amount').text(prestashop.cart.subtotals.products.value);
	
	prestashop.on('updatedCart',function (event) {
		if($(event.resp.cart_detailed).find('.empty-products').length > 0){
			$('body').addClass('cart-is-empty');
			if(typeof prestashop.page.page_name != 'undefined' && prestashop.page.page_name == 'checkout'){
				location.assign(prestashop.urls.pages.index);
			}
		}else{
			$('body').removeClass('cart-is-empty');
		}
	});

	prestashop.on('handleError',function (event) {
		$('[data-button-action="add-to-cart"], .js-add-to-cart').removeClass('loading').prop('disabled', false);
        if (event && typeof event.resp !== 'undefined' && event.resp.errors && typeof prestashop.page.page_name != 'undefined' && prestashop.page.page_name != 'cart' && prestashop.page.page_name != 'checkout') {
            toastr["error"](Array.isArray(event.resp.errors)?event.resp.errors.join('<br/>'):event.resp.errors);
        }
	});

	prestashop.on('updateCart',function (event) {
		$('[data-button-action="add-to-cart"], .js-add-to-cart').removeClass('loading').prop('disabled', false);
        if (event && typeof event.resp !== 'undefined' && event.resp.errors && typeof prestashop.page.page_name != 'undefined' && prestashop.page.page_name != 'cart' && prestashop.page.page_name != 'checkout') {
            toastr["error"](Array.isArray(event.resp.errors)?event.resp.errors.join('<br/>'):event.resp.errors);
        }
	});

	prestashop.on(
	  	'updateCart',
		function (event) {
			var refreshURL = opShoppingCart.ajax;
			var requestData = {};

			if (event && event.reason && typeof event.resp !== 'undefined' && !event.resp.hasError) {
				requestData = {
					id_customization: event.reason.idCustomization,
					id_product_attribute: event.reason.idProductAttribute,
					id_product: event.reason.idProduct,
					action: event.reason.linkAction
				};
			}

			if(prestashop.buy_now_cart || (!opShoppingCart.has_ajax && event.reason.linkAction == 'add-to-cart')){
				location.assign(prestashop.urls.pages.order);
			}else{

				$('body').addClass('cart-processing');

				if (event && event.resp && event.resp.hasError) {
					prestashop.emit('showErrorNextToAddtoCartButton', { errorMessage: event.resp.errors.join('<br/>')});
				}

				$.post(refreshURL, requestData).then(function (resp) {

					$('body').removeClass('cart-processing');

					$('[data-button-action="add-to-cart"], .js-add-to-cart').removeClass('loading').prop('disabled', false);

					$('.js-shopping-cart').replaceWith($(resp.canvas).find('.js-shopping-cart'));

					$('.js-cart-canvans-title').replaceWith($(resp.canvas).find('.js-cart-canvans-title'));

					$('.js-cart-nbr').replaceWith($(resp.preview).find('.js-cart-nbr'));

					$('.js-cart-amount').replaceWith($(resp.preview).find('.js-cart-amount'));

					if (resp.modal || resp.notices) {						
						if( opShoppingCart.action_after == 'canvas' ){
							prestashop.emit('show_canvas_widget', $('#canvas-mini-cart'));
							prestashop.emit('updateProduct', { reason: opShoppingCart.action_after, resp});
						}else if(opShoppingCart.action_after == 'modal'){
                            $('#nrtshoppingcart-modal-content').html(resp.modal);
                            $('#nrtshoppingcart-modal').modal('show');
						}else{
							if($(resp.notices).find('.ax-n-success').length){
								toastr["success"](resp.notices);
							}else if($(resp.notices).find('.ax-n-info').length){
								toastr["info"](resp.notices);
							}else{
								toastr["error"](resp.notices);
							}
							prestashop.emit('updateProduct', { reason: opShoppingCart.action_after, resp});
						}
					}else{
						prestashop.emit('updateProduct', { reason: opShoppingCart.action_after, resp});
					}

					prestashop.emit('updateCarted', null);

				}).fail(function (resp) {
					$('body').removeClass('cart-processing');

					$('[data-button-action="add-to-cart"], .js-add-to-cart').removeClass('loading').prop('disabled', false);
					
					prestashop.emit('handleError', {eventType: 'updateShoppingCart', resp: resp});
				});

			}
		}
	);
});