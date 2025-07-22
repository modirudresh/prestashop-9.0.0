$(function () {
    const promoKey = 'topPromoClosedAt',
        delayMinutes = 70,
        msDelay = delayMinutes * 60000;

    const promo = $('#topPromo'),
        handle = $('#promoToggle'),
        message = promo.find('.message'),
        content = promo.find('.promo-content'),
        fullHeight = content.outerHeight();


    const lastClosed = localStorage.getItem(promoKey);
    if (lastClosed && (Date.now() - parseInt(lastClosed, 10)) < msDelay) {
        promo.hide();
        return;
    }


    promo.css('margin-top', -fullHeight + 'px');


    handle.on('click', function () {
        if (promo.hasClass('open')) {
            promo.animate({ 'margin-top': -fullHeight }, 300);
            message.delay(150).fadeIn(150);
            promo.removeClass('open');
        } else {
            message.fadeOut(150);
            promo.animate({ 'margin-top': 0 }, 300);
            promo.addClass('open');
        }
    });


    $('#promoClose').on('click', function () {
        promo.fadeOut();
        localStorage.setItem(promoKey, Date.now().toString());
    });
});
