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

$(document).ready(function () {
    $('input[name="YBC_PI_THOSE_PAGES[]"]').change(function () {
        var value = $(this).val();
        if(value == 'allpage'){
            $('input[name="YBC_PI_THOSE_PAGES[]"]:not(.all-page)').prop('checked', $(this).is(':checked'));
        }
        else{
            if($(this).is(':checked')){
                if($('input[name="YBC_PI_THOSE_PAGES[]"]:not(.all-page)').length == $('input[name="YBC_PI_THOSE_PAGES[]"]:not(.all-page):checked').length){
                    $('input[name="YBC_PI_THOSE_PAGES[]"].all-page').prop('checked', true);
                }
                else{
                    $('input[name="YBC_PI_THOSE_PAGES[]"].all-page').prop('checked', false);
                }
            }
            else{
                $('input[name="YBC_PI_THOSE_PAGES[]"].all-page').prop('checked', false);
            }
        }
    });
});