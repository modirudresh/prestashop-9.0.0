jQuery(function($){
     $('#product_name').autocomplete(ajaxProductsListUrl, {
        minChars: 1,
        autoFill: true,
        max:20,
        matchContains: true,
        mustMatch:true,
        scroll:false,
        cacheLength:0,
        extraParams:{ excludeIds:getMenuProductsIds()},
        formatItem: function(item) {
            if (item.length == 2) {
              return item[1]+' - '+item[0];  
            } else {
                return '--';
            }
        }
    }).result(function(event, data, formatted) {
		if (data == null || data.length != 2)
			return false;
		var productId = data[1];
		var productName = data[0];
        var inputIdProduct = $('#inputMenuProducts');
        var divProductName = $('#curr_product_name');
        var intProductName = $('#nameMenuProducts');
        divProductName.append('<li class="form-control-static"><button type="button" class="delMenuProduct btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;'+ productName +'</li>');
		inputIdProduct.val(inputIdProduct.val() + productId + '-');
        intProductName.val(intProductName.val() + productName + '¤');

        $('#product_name').val('');
        $('#product_name').setOptions({
            extraParams: {excludeIds : getMenuProductsIds()}
        });
    }); 

    $('#curr_product_name').delegate('.delMenuProduct', 'click', function(){
        delMenuProduct($(this).attr('name'));
    });
    
    $('.nrt_delete_image').click(function(){
        var self = $(this);
        $.getJSON(self.attr('href')+'&act=delete_image&ts='+new Date().getTime(),
            function(json){
                if(json.r)
                {
                    self.closest('.form-group').remove();
                }
            }
        ); 
        return false;
    });
    $('#manufacturers').autocomplete(ajaxBrandsListUrl, {
        minChars: 1,
        autoFill: true,
        max:200,
        matchContains: true,
        mustMatch:true,
        scroll:true,
        cacheLength:0,
        extraParams:{ excludeIds:getBrandExcIds()},
        formatItem: function(item) {
            return item[1]+' - '+item[0];
        }
    }).result(function(event, data, formatted) {
		if (data == null)
			return false;
		var id = data[1];
		var name = data[0];
        
		$('#curr_manufacturers').append('<li>'+name+'<a href="javascript:void(0)" class="del_manufacturer"><i class="icon-remove text-danger"></i></a><input type="hidden" name="id_manufacturer[]" value="'+id+'" /></li>');
        
        $('#manufacturers').setOptions({
        	extraParams: {
        		excludeIds : getBrandExcIds()
        	}
	    });
        
    });
    $('#curr_manufacturers').delegate('.del_manufacturer', 'click', function(){
        $(this).closest('li').remove();
        $('#manufacturers').setOptions({
        	extraParams: {
        		excludeIds : getBrandExcIds()
        	}
	    });
    });
    
     $('select[name="links"]').change(function(){
        manageLinksStatus();
    });
    
    if ($('select[name="links"]').val())
        $('input[name^="link_"]').val('').attr("disabled",true);
});

var getBrandExcIds = function()
{
    var excludeIds = '';
    $(':hidden[name="id_manufacturer[]"]').each(function(){
        excludeIds += $(this).val()+',';
    });
    return excludeIds.substr(0, excludeIds.length-1);  
}

var getMenuProductsIds = function()
{
    if (!$('#inputMenuProducts').val())
        return '-1';
    return $('#inputMenuProducts').val().replace(/\-/g,',');
}


var delMenuProduct = function(id)
{
    var div = $('#curr_product_name');
    var input = $('#inputMenuProducts');
    var name = $('#nameMenuProducts');

    // Cut hidden fields in array
    var inputCut = input.val().split('-');
    var nameCut = name.val().split('¤');

    if (inputCut.length != nameCut.length)
        return jAlert('Bad size');

    // Reset all hidden fields
    input.val('');
    name.val('');
    div.empty();
    for (i in inputCut)
    {
        // If empty, error, next
        if (!inputCut[i] || !nameCut[i])
            continue ;

        // Add to hidden fields no selected products OR add to select field selected product
        if (inputCut[i] != id)
        {
            input.val(input.val()+inputCut[i]+'-');
            name.val(name.val()+nameCut[i]+'¤');
            div.append('<li class="form-control-static"><button type="button" class="delMenuProduct btn btn-default" name="' + inputCut[i] +'"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</li>');
        }
    }

    $('#product_name').setOptions({
        extraParams: {excludeIds : getMenuProductsIds()}
    });
};

var manageLinksStatus = function()
{
    var value = $('select[name="links"]').val();
                        
    if(value=='')
    {
        $('input[name^="link_"]').val('').attr("disabled",false); 
        $('select[name="links"]').find("option[value^='2_']").val('2_0').text('Choose ID product');
    }
    else if(value.substr(0,2) == "2_")
    {
        var id_product = prompt('Set ID product');
		if (id_product == null || id_product == "" || isNaN(id_product))
			return;
		$('select[name="links"]').find("option[value^='2_']").val('2_'+id_product).text('Product ID '+id_product);
        $('input[name^="link_"]').val('').attr("disabled",true); 
    }
    else
    {
        $('input[name^="link_"]').val('').attr("disabled",true); 
        $('select[name="links"]').find("option[value^='2_']").val('2_0').text('Choose ID product');
    }
}
