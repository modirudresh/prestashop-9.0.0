
function initCountDown() {
    $('.js-countdown-timer').each(function () {
        var $this = $(this);
        var finalDate = 0;

        if($this.closest('.js-product-specific-to').length){
            finalDate = moment.tz($this.closest('.js-product-specific-to').data('specific-to'), opCountDown.timezone);

            $this.countdown(finalDate.toDate(), function (event) {
                $this.find('.js-time-days').html(event.strftime('%D'));
                $this.find('.js-time-hours').html(event.strftime('%H'));
                $this.find('.js-time-minutes').html(event.strftime('%M'));
                $this.find('.js-time-seconds').html(event.strftime('%S'));
            });
        }
    });
}

$(window).load(function () {
    initCountDown();

    prestashop.on('updatedProduct', function (e) {
        initCountDown();
    });

    prestashop.on('updatedProductList', function (e) {
        initCountDown();
    });
	
	prestashop.on('updatedProductAjax',function (e) {
		initCountDown();
	});	
	
	$('body').on('show.bs.modal','.quickview',(function() {
		initCountDown();
	}));

});


