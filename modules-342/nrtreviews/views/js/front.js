$(document).ready(function() {
	
	reviewFulnessRefreshStatus();
	
	reviewUpdateAvgs();

	$('body').on('show.bs.modal','.quickview',(function() {
		reviewUpdateAvgs();
	}));
	
	prestashop.on('updatedProductList', function() {
		reviewUpdateAvgs();
	});
	
	prestashop.on('updatedProductAjax', function() {
		reviewUpdateAvgs();
	});

	$('#reviews_form form').on('submit', function (event) {
		var self = this;
		prestashop.emit('postReviewProduct', {
			data: new FormData(self),
		});
		event.preventDefault();
	});
	
	$('body').on('click','.js-review-fulness',(function(event) {
		var self = this;
		prestashop.emit('postReviewFulness', {
			dataset: self.dataset,
			self: self
		});
		event.preventDefault();
	}));	
	
	prestashop.on('postReviewFulness', function (elm) {		
		
		if($('.js-review-fulness-'+elm.dataset.idReview).hasClass("rv-processing")){
			return;
		}
		
		var data = {
			'process': 'fulness',
			'ajax': 1,
			'idReview': elm.dataset.idReview,
			'value': elm.dataset.value
		};
		
		$('.js-review-fulness-'+elm.dataset.idReview).addClass('rv-processing');
		
		$.post(opReviews.actions, data, null, 'json').then(function (resp) {
			if(resp.success){
				$('#js-fulness-text-'+elm.dataset.idReview).text(resp.is_fulness);
				$('#js-nofulness-text-'+elm.dataset.idReview).text(resp.no_fulness);
				opReviews.fulness = resp.fulness;
				reviewFulnessRefreshStatus();
			}
			$('.js-review-fulness-'+elm.dataset.idReview).removeClass('rv-processing');
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'postReviewFulness', resp: resp });
		});
		
	});

	prestashop.on('postReviewProduct', function (elm) {
		
		if($('#reviews_form form').hasClass("rv-processing")){
			return;
		}
		
		$('#reviews_form form').addClass('rv-processing');
		$('#reviews_form_btn button').addClass('processing');
		$('#reviews_form_error').hide();
		$('#reviews_form_error').html('');

		$.ajax({
			url: opReviews.actions + '?process=add',
			data:  elm.data,
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			dataType: "json",
			contentType: false, 
			processData: false,
			complete: function() {
				prestashop.emit('submitCompleteNrtForm', null);
			},
			success: function(resp){
				if(!resp.is_logged){
					reviewsShowLogin();
				}else{
					$('#reviews_form form').removeClass('rv-processing');
					$('#reviews_form_btn button').removeClass('processing');
					if(resp.success){
					   alert(resp.msg);
						if(resp.reload){
						   window.location.reload();
						}
					}else{					
						$.each(resp.errors, function(index, value) {
							$('#reviews_form_error').append('<div>'+value+'</div>');
						});
						$('#reviews_form_error').slideDown();
					}
				}
			}
		});
		
	});
	
	function reviewsShowLogin()
	{
		var data = {
			'process': 'login',
			'ajax': 1,
			'current_url': prestashop.urls.current_url
		};
		
		$.post(opReviews.login, data, null, 'json').then(function (resp) {
			$('#reviews_login').html(resp.html);	
			$('#modal_reviews').modal('show');
			$('#reviews_form form').removeClass('rv-processing');
			$('#reviews_form_btn button').removeClass('processing');
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'reviewsShowLogin', resp: resp });
		});
		
	}
	
	function reviewFulnessRefreshStatus()
	{
		$('.js-review-fulness').each(function(){
			
			var $el = $(this);
			var $idReview = $el.data('id-review');	
			
			if(typeof opReviews.fulness[$idReview] !== "undefined"){
				$('.js-review-fulness-'+$idReview).removeClass('active_color');
				if(Boolean(opReviews.fulness[$idReview])){
					$('.js-review-fulness-'+$idReview+'[data-value="1"]').addClass('active_color');
				}else{
					$('.js-review-fulness-'+$idReview+'[data-value="0"]').addClass('active_color');
				}
			}else{
				$el.removeClass('active_color');
			}
			
		});
	}
	
	$('body').on('click','#main-content .open-review-form',(function(event) {
		prestashop.emit('goToCommentForm', null);
		event.preventDefault();
	}));
	
	$('body').on('click','#main-content .goto-product-review-tab',(function(event) {
		prestashop.emit('goToCommentTab', null);
		event.preventDefault();
	}));	
	
	function reviewUpdateAvgs()
	{
		var listIds = [];
		
		$('.js-review-avgs').each(function(){
			var $el = $(this);

			var $idProduct = 0;

            if($el.closest('.js-product-miniature').length){
			    $idProduct = $el.closest('.js-product-miniature').data('id-product');
            }else if($('#product_page_product_id').length){
			    $idProduct = $('#product_page_product_id').val();
            }

            $el.attr('data-id-product', $idProduct);

			if(!listIds.includes($idProduct)){
				listIds.push($idProduct);
			}		
		});
		
		if(listIds.length < 1){
		   return;
		}
		
		var data = {
			'process': 'avg',
			'ajax': 1,
			'listIds': listIds,
		};
				
		$.post(opReviews.actions, data, null, 'json').then(function (resp) {
			if(resp.success){				
                $.each(resp.products, function(i, elem) {
					var $productsContainer = $('.js-review-avgs[data-id-product=' + elem.id_product + ']');
					if(elem.avgReviews.nbr > 0){
						$productsContainer.find('.star_content_avg').html('<span style="width:'+(elem.avgReviews.avg/5)*100+'%"></span>');
						$productsContainer.find('.r-nbr').html(elem.avgReviews.nbr);
						$productsContainer.removeClass('hidden');
					}
                });
			}
		}).fail(function (resp) {
			prestashop.emit('handleError', { eventType: 'reviewUpdateAvgs', resp: resp });
		});
	}

    $('body').on('click','.js-comment-pn',(function() {
		var data = {
			'process': 'comments',
			'ajax': 1,
			'id_product': $(this).data('id-product'),
            'page': $(this).data('page'),
		};

        $('html, body').animate({ scrollTop: $('#reviews-list-comments').offset().top - 100 }, 0, 'linear');

        $('#reviews-list-comments-item').html('<div class="placeholder-load-spin">Load more</div><hr>');

        $.post(opReviews.actions, data, null, 'json').then(function (resp) {
			$('#reviews-list-comments').html(resp.html);
			prestashop.emit('updatedReviewsAjax', null);
        });
    }));	
});