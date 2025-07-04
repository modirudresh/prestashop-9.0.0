$(document).ready(function () {
    if(opCaptcha.version == 3) {
        grecaptcha.ready(function() {
            grecaptcha.execute(opCaptcha.site_key, {action: "submitMessage"}).then(function(token) {
                $('form').prepend("<input type='hidden' name='g-recaptcha-response' value='" + token + "'>");
            });
        });
    }
    prestashop.on('submitCompleteNrtForm', function (e) {
        if(opCaptcha.version == 3) {
            grecaptcha.ready(function() {
                grecaptcha.execute(opCaptcha.site_key, {action: "submitMessage"}).then(function(token) {
                    $('form [name=g-recaptcha-response]').val(token);
                });
            });
        } else {
            grecaptcha.ready(function() {
                grecaptcha.reset();
            });
        }
    });
});
