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
    $(document).on('click','.checkbox_all input',function(){
        if($(this).is(':checked'))
        {
            $(this).closest('.form-group').find('input').prop('checked',true);
        }
        else
        {
            $(this).closest('.form-group').find('input').prop('checked',false);
        }
    });
    $(document).on('click','.checkbox input',function(){
        if($(this).is(':checked'))
        {
            if($(this).closest('.form-group').find('input:checked').length==$(this).closest('.form-group').find('input').length-1)
                 $(this).closest('.form-group').find('.checkbox_all input').prop('checked',true);
        }
        else
        {
            $(this).closest('.form-group').find('.checkbox_all input').prop('checked',false);
        } 
    });
    $('input#image_url_1').blur(function () {
        $(this).removeClass('ok').removeClass('error');
        $('button[name="save_baw_banner"]').removeClass('not_click_because_error');
        var input_url = $(this).val().slice(0, 8);
        if ( input_url != '' && input_url != ' '){
            if ( input_url == 'http://' || input_url == 'https://'  ){
                $(this).addClass('ok');
                $('button[name="save_baw_banner"]').removeClass('not_click_because_error');
            } else {
                $(this).addClass('error');
                $('button[name="save_baw_banner"]').addClass('not_click_because_error');
            }
        }
    });
    if($('.short-code').length)
    {
        $(document).on('click','.short-code',function(){
            $('.ets-baw-text-copy').remove();
            $(this).select();
            document.execCommand("copy");
            var copy_text = $('<span class="ets-baw-text-copy">'+$(this).parent().data('copied')+'</span>');
            $(this).after(copy_text);
            setTimeout(function() { copy_text.remove(); }, 2000);
        });
    }
    if($('#list-banner').length && $('#list-banner .dragHandle').length)
    {
        var $mybanner = $("#list-banner");
    	$mybanner.sortable({
    		opacity: 0.6,
            handle: ".dragHandle",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updateBannerOrdering&display_position="+$('select[name="display_position"]').val();						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
        			     if(jsonData.success)
                         {
                            showSuccessMessage(jsonData.success);
                            var i=1;
                            $('.dragGroup span').each(function(){
                                $(this).html(i+(jsonData.page-1)*jsonData.limit);
                                i++;
                            });
                         }
                         if(jsonData.error)
                         {
                            showErrorMessage(jsonData.error);
                            $mybanner.sortable("cancel");
                         }
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
});