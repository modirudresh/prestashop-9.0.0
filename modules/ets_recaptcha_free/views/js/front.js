/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*/
var ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY = ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY || false;
var ETS_RCF_GOOGLE_V3_POSITION = ETS_RCF_GOOGLE_V3_POSITION || 'bottomright';
var ETS_RCF_GOOGLE_CAPTCHA_THEME = 'light';
var func_ets_rcf = {
    loadReCaptchaV3: function (form) {
        if ($('form:not(.g-loaded)').length <= 0 || !ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY) {
            return false;
        }
        var g_captcha = form.find('[id*=g-recaptcha-response-]');
        if (g_captcha.length > 0 && $('body').attr('id')) {
            grecaptcha.ready(function () {
                var renderClientId = grecaptcha.render(g_captcha[0].id, {
                    'sitekey': ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY,
                    'badge': ETS_RCF_GOOGLE_V3_POSITION,
                    'size': 'invisible',
                    'theme' : ETS_RCF_GOOGLE_CAPTCHA_THEME,
                });
                grecaptcha.execute(renderClientId, {action: $('body').attr('id').replace(/(?=[^A-Za-z\_])([^A-Za-z\_])/g, '_')}).then(function (token) {
                    if (token) {
                        if (g_captcha.find('input[name=g-recaptcha-response]').length > 0) {
                            g_captcha.find('input[name=g-recaptcha-response]').val(token);
                        } else {
                            g_captcha.append('<input type="hidden" name="g-recaptcha-response" value="' + token + '"/>');
                        }
                        form.addClass('g-loaded');
                        func_ets_rcf.loadReCaptchaV3($('form:not(.g-loaded)'));
                    }
                });
            });
        }
    }
}
$(document).ready(function () {
    if (ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY) {
        func_ets_rcf.loadReCaptchaV3($('form:not(.g-loaded)'));
    }
    if($('#customer-form #field-captcha').length)
    {
        $('#customer-form #field-captcha').parents('.form-group.row').hide();
        if($('#field-captcha').next('.help-block').length)
        {
            $('.captcha_register .g-recaptcha').after($('#field-captcha').next('.help-block').clone());
        }
    }
    else if($('#customer-form input[name="captcha"]').length)
    {
        $('#customer-form input[name="captcha"]').parents('.form-group.row').hide();
    }
});