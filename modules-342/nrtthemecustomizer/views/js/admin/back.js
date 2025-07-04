$(document).ready(function(){
    // Make the first tab active
    var $_firstTab = $('#nrtthemecustomizer-tabs .tab').first();
    $_firstTab.addClass('active');

    var firstTabContentID = '#' + $_firstTab.attr('data-tab');
    // On tab click
    $('#nrtthemecustomizer-tabs .tab').on('click', function()
    {
        var tabContentID = '#' + $(this).attr('data-tab');
        $('#configuration_form .panel').hide();
        $('#configuration_form .panel' + tabContentID).show();

        $('#nrtthemecustomizer-tabs .tab').removeClass('active');
        $(this).addClass('active');
    });
	
	////////////////////////////////////////////////
	$('#button_template_style_label').click(function(e) {
      $('#group_style').append($('#template_style_label').html());  
    });
	$('#button_template_style').click(function(e) {
      $('#group_style').append($('#template_style').html());  
    });
	$('body').on('click', '.delete_style', function() {
		$(this).closest('.wrapper_style').remove();
	});
	
    $( "#group_style" ).sortable({
      placeholder: "ui-state-highlight"
    });
    $( "#group_style" ).disableSelection();
	
	$('button[name=savenrtThemeConfig]').click(function(e) {
		generateStyle();
    });
	
	function generateStyle(){
		var style_on_theme = {};
		$('#group_style .wrapper_style').each(function(index, element) {
			if($(this).find('input[name=style_label]').length){
				var style_label = $(this).find('input[name=style_label]').val();
				if(style_label){
					style_on_theme[index] = {label:style_label};
				}
			}else{
				var style_selector = $(this).find('input[name=style_selector]').val();
				var style_params = $(this).find('input[name=style_params]').val();
				var style_value = $(this).find('input[name=style_value]').val();
				if(style_selector && style_params && style_value){
					style_on_theme[index] = {selector:style_selector,params:style_params,value:style_value};
				}
			}
        });
		$('#style_on_theme').val(JSON.stringify(style_on_theme));
	}
});
