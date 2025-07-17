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
$(document).ready(function(){
    if ($('#etsBAWhookDisplayLeftColumnBefore').length > 0) {
        if ($('#left-column').length > 0) {
             $('#left-column').prepend($('#etsBAWhookDisplayLeftColumnBefore').html());
        }
        else if ($('#left_column').length > 0) {
            $('#left_column').prepend($('#etsBAWhookDisplayLeftColumnBefore').html());
        }
    }
    if ($('#etsBAWhookDisplayRightColumnBefore').length > 0) {
        if ($('#right-column').length > 0) {
            $('#right-column').prepend($('#etsBAWhookDisplayRightColumnBefore').html());
        }
        if ($('#right_column').length > 0) {
            $('#right_column').prepend($('#etsBAWhookDisplayRightColumnBefore').html());
        }
    }
    if ($('.cart-grid-body').length > 0) {
        if ($('#etsBAWhookDisplayCartGridBodyBefore1').length > 0) {
            $('.cart-grid-body').prepend($('#etsBAWhookDisplayCartGridBodyBefore1').html());
        }
        if ($('#etsBAWhookDisplayCartGridBodyBefore2').length > 0) {
            $('.cart-grid-body').prepend($('#etsBAWhookDisplayCartGridBodyBefore2').html());
        }
        if ($('#etsBAWhookDisplayCartGridBodyAfter').length > 0) {
            $('.cart-grid-body').append($('#etsBAWhookDisplayCartGridBodyAfter').html());
        }
    } 
    if ($('#js-product-list-header').length > 0) {
        if ($('#etsBAWhookDisplayProductListHeaderBefore').length > 0) {
            $('#js-product-list-header').prepend($('#etsBAWhookDisplayProductListHeaderBefore').html());
        }
        if ($('#etsBAWhookDisplayProductListHeaderAfter').length > 0) {
            $('#js-product-list-header').append($('#etsBAWhookDisplayProductListHeaderAfter').html());
        }
    }
    if ($('.product-variants').length > 0) {
        if ($('#etsBAWhookDisplayProductVariantsBefore').length > 0) {
            $('.product-variants').prepend($('#etsBAWhookDisplayProductVariantsBefore').html());
        }
    }
    if ($('.product_attributes').length > 0) {
        if ($('#etsBAWhookDisplayProductVariantsBefore').length > 0) {
            $('.product_attributes').prepend($('#etsBAWhookDisplayProductVariantsBefore').html());
        }
    }
    if ($('.product-variants').length > 0) {
        if ($('#etsBAWhookDisplayProductVariantsAfter').length > 0) {
            $('.product-variants').append($('#etsBAWhookDisplayProductVariantsAfter').html());
        }
    }
    if ($('.product_attributes').length > 0) {
        if ($('#etsBAWhookDisplayProductVariantsAfter').length > 0) {
            $('.product_attributes').append($('#etsBAWhookDisplayProductVariantsAfter').html());
        }
    }
    if ($('#product-comments-list-header').length > 0) {
        if ($('#etsBAWhookDisplayProductCommentsListHeaderBefore').length > 0) {
            $('#product-comments-list-header').before($('#etsBAWhookDisplayProductCommentsListHeaderBefore').html());
        }
    }
    if ($('.ets_rv_wrap').length > 0) {
        if ($('#etsBAWhookDisplayProductCommentsListHeaderBefore').length > 0) {
            $('.ets_rv_wrap').before($('#etsBAWhookDisplayProductCommentsListHeaderBefore').html());
        }
    }
});