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
var Ets_ImageHover = {
    inArray: function (item, items) {
        var length = items.length;
        for(var i = 0; i < length; i++) {
            if(items[i] == item) return true;
        }
        return false;
    },
    ets_run_v17: function(){
        if($('article.product-miniature').length >0){
            var temps = [], ids = '', item = 0;
            $('article.product-miniature').each(function(){
                item = parseInt($(this).data('id-product'));
                if(item > 0 && !Ets_ImageHover.inArray(item, temps))
                    temps.push(item);
            });
            for(var i = 0; i < temps.length; i++)
                (i != temps.length - 1)? ids += temps[i] + ',' : ids += temps[i];
            if(ids !=''){
                $.ajax({
                    url : baseAjax,
                    data : 'ids=' + ids,
                    type : 'get',
                    dataType: 'json',
                    success : function(json){
                        if(json){                            
                            $.each(json,function(i,image){
                                if($('.product-miniature[data-id-product="'+i+'"] a.product-thumbnail img').length > 0){
                                    $('.product-miniature[data-id-product="'+i+'"] a.product-thumbnail img').after(image);
                                }
                            });
                        }
                    }
                });
            }
        }
    },
    ets_run_v16: function(){
        if($('.ajax_add_to_cart_button').length >0){
            var temps = [], ids = '', item = 0;
            $('.ajax_add_to_cart_button').each(function(){
                item = parseInt($(this).data('id-product'));
                if(item > 0 && !Ets_ImageHover.inArray(item, temps))
                    temps.push(item);
            });
            for(var i = 0; i < temps.length; i++)
                (i != temps.length - 1)? ids += temps[i] + ',' : ids += temps[i];
            if(ids !=''){
                $.ajax({
                    url : baseAjax,
                    data : 'ids=' + ids,
                    type : 'get',
                    dataType: 'json',
                    success : function(json){
                        if(json){
                            $.each(json,function(i,image){
                                /*product list*/
                                if($('.ajax_add_to_cart_button[data-id-product="'+i+'"]').closest('.ajax_block_product').find('a.product_img_link img').length >0)
                                    $('.ajax_add_to_cart_button[data-id-product="'+i+'"]').closest('.ajax_block_product').find('a.product_img_link img').after(image);
                                /*module products category*/
                                if($('.ajax_add_to_cart_button[data-id-product="'+i+'"]').closest('li.product-box').find('a.product-image img').length >0)
                                    $('.ajax_add_to_cart_button[data-id-product="'+i+'"]').closest('li.product-box').find('a.product-image img').after(image);
                            });
                        }
                    }
                });
            }
        }
    },
    ets_run_v15: function(){
        if($('.ajax_add_to_cart_button').length >0){
            var temps = [], ids = '', item = 0;
            $('.ajax_add_to_cart_button').each(function(){
                item = parseInt($(this).attr('rel').replace('ajax_id_product_',''));
                if(item > 0 && !Ets_ImageHover.inArray(item, temps))
                    temps.push(item);
            });
            for(var i = 0; i < temps.length; i++)
                (i != temps.length - 1)? ids += temps[i] + ',' : ids += temps[i];
            if(ids !=''){
                $.ajax({
                    url : baseAjax,
                    data : 'ids=' + ids,
                    type : 'get',
                    dataType: 'json',
                    global: false,
                    success : function(json){
                        if(json){                        
                            $.each(json,function(i,image){
                                /*home product*/
                                if($('.ajax_add_to_cart_button[rel="ajax_id_product_'+i+'"]').closest('.ajax_block_product').find('a.product_image img').length >0 && !$('.accessories_block').length)
                                    $('.ajax_add_to_cart_button[rel="ajax_id_product_'+i+'"]').closest('.ajax_block_product').find('a.product_image img').after(image);
                                 /*product list*/
                                if($('.ajax_add_to_cart_button[rel="ajax_id_product_'+i+'"]').closest('.ajax_block_product').find('a.product_img_link img').length >0)
                                    $('.ajax_add_to_cart_button[rel="ajax_id_product_'+i+'"]').closest('.ajax_block_product').find('a.product_img_link img').after(image);
                            });
                        }
                    }
                });
            }
        }
    }    
}
$(document).ready(function(){
    /*ver 1.7*/
    if(_PI_VER_17_ >0)
    {
        Ets_ImageHover.ets_run_v17();
        $(document ).ajaxComplete(function( event, xhr, settings ) {
            if(xhr.responseText && xhr.responseText.indexOf("rendered_products")>=0)
            {
                Ets_ImageHover.ets_run_v17();
            }
        });
    }
    
    /*ver 1.6*/
    if(_PI_VER_17_ < 1 && _PI_VER_16_ >0)
    {
        Ets_ImageHover.ets_run_v16();
    }
    /*ver 1.5*/
    if(_PI_VER_16_ < 1)
    {
        Ets_ImageHover.ets_run_v15();
    }
});