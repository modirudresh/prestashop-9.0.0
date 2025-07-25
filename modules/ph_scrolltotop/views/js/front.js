/**
 * 2007-2024 ETS-Soft
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
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2024 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
$(document).ready(function (){
    $(".back-to-top a").fadeOut();
    $(".back-to-top a").click(function(e) {
        e.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, parseInt(500));
    });

    if (ETS_SCROLL_PIXEL) {
        $(window).scroll(function() {
            if ($(this).scrollTop() > ETS_SCROLL_PIXEL) {
                $('.back-to-top a').fadeIn();
            } else {
                $('.back-to-top a').fadeOut();
            }
        });
    }
});