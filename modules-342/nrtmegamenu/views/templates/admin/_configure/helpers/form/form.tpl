{*
*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*
*}

{extends file="helpers/form/form.tpl"}
	
{block name="script"}
	$(document).ready(function() {
		$('.iframe-upload').fancybox({	
			'width'		: 900,
			'height'	: 600,
			'type'		: 'iframe',
			'autoScale' : false,
			'autoDimensions': false,
			'fitToView' : false,
			'autoSize' : false,
			onUpdate : function(){ 
				$('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
				$('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
			},
			afterShow: function(){
				$('.fancybox-iframe').contents().find('a.link').data('field_id', $(this.element).data("input-name"));
				$('.fancybox-iframe').contents().find('a.link').attr('data-field_id', $(this.element).data("input-name"));
			}
		});
	});
{/block}

{block name="field"}
	{if $input.type == 'dropdownlistgroup'}
		<div class="col-lg-{if isset($input.col)}{$input.col|intval}{else}8{/if}{if !isset($input.label)} col-lg-offset-4{/if}">
			<div class="row">
				{foreach $input.values.medias AS $media}
					<div class="col-xs-4 col-sm-3">
						<label data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{if $media=='fw'}{l s='If this is set and this module is hooked to the displayFullWidthXXX hooks, then this module will be displayed in full screen.' mod='nrtmegamenu'}{elseif $media=='xxl'}{l s='Desktops (>=1400px)' mod='nrtmegamenu'}{elseif $media=='xl'}{l s='Desktops (>=1200px)' mod='nrtmegamenu'}{elseif $media=='lg'}{l s='Desktops (>=1025px)' mod='nrtmegamenu'}{elseif $media=='md'}{l s='Tablets (>=768px)' mod='nrtmegamenu'}{elseif $media=='sm'}{l s='Phones (>=576px)' mod='nrtmegamenu'}{elseif $media=='xs'}{l s='Phones (<=575px)' mod='nrtmegamenu'}{/if}">{if $media=='fw'}{l s='Full screen' mod='nrtmegamenu'}{elseif $media=='xxl'}{l s='Extra large devices' mod='nrtmegamenu'}{elseif $media=='xl'}{l s='Large devices' mod='nrtmegamenu'}{elseif $media=='lg'}{l s='Medium devices' mod='nrtmegamenu'}{elseif $media=='md'}{l s='Small devices' mod='nrtmegamenu'}{elseif $media=='sm'}{l s='Extra small devices' mod='nrtmegamenu'}{elseif $media=='xs'}{l s='Extremely small devices' mod='nrtmegamenu'}{/if}</label>
						<select name="{$input.name}_{$media}" id="{$input.name}_{$media}" class="fixed-width-md">
            			{for $foo=1 to $input.values.maximum}
	                        <option value="{$foo}" {if $fields_value[$input['name']|cat:"_"|cat:$media] == $foo} selected="selected" {/if}>{$foo}</option>
	                    {/for}
            			</select>
					</div>
				{/foreach}
			</div>
			{if isset($input.desc) && !empty($input.desc)}
				<p class="help-block">
					{if is_array($input.desc)}
						{foreach $input.desc as $p}
							{if is_array($p)}
								<span id="{$p.id}">{$p.text}</span><br />
							{else}
								{$p}<br />
							{/if}
						{/foreach}
					{else}
						{$input.desc}
					{/if}
				</p>
			{/if}
		</div>
	{elseif $input.type == 'fontello'}
	
		{if $fields_value[$input.name] != ''} 
			{$value_check = $fields_value[$input.name]|json_decode:1}
		{/if}
		
		<div class="fontello_wrap">
			<a id="btn_{$input.name}" class="btn btn-default" data-toggle="modal" href="#" data-target="#modal_{$input.name}">
				{if isset($value_check)}
					{if $value_check.type == 1}
						<img style="margin-right: 10px;max-width: 200px;" src="{$value_check.value}" alt=""/>
					{else}
						<i style="margin-right: 10px;font-size: 20px;vertical-align: middle;" class="{$value_check.value}"></i>
					{/if}
				{/if}
				{l s='Edit'}
			</a>
			<div class="modal fade" id="modal_{$input.name}" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title">{l s="Icon"}</h4>
					</div>
					<div class="modal-body">
						<ul class="fontello_list clearfix">
							<li style="width: 100%;">
								<input type="radio"	name="{$input.name}" id="choose_class_none" value=""{if $fields_value[$input.name] == ''} checked="checked"{/if}/>
								<label for="choose_class_none">{l s="None"}</label>
							</li>
							<li style="width: 100%;">
								<input type="radio"	name="{$input.name}" id="choose_class_image" value='{if isset($value_check) && $value_check.type == 1}{$fields_value[$input.name]}{/if}'{if isset($value_check) &&  $value_check.type == 1} checked="checked"{/if}/>
								<label for="choose_class_image">{l s="Image"}</label>
								<div class="wrapper_input_icon">
									<p> 
										<input id="{$input.name}_image" onChange="changeIconMenu(1);" type="text" name="{$input.name}_image" value="{if isset($value_check) && $value_check.type == 1}{$value_check.value}{/if}"> 
									</p>
									<a href="{__PS_BASE_URI__}{basename(_PS_ADMIN_DIR_)}/filemanager/dialog.php?type=1&field_id={$input.name}_image" class="btn btn-default iframe-upload"  data-input-name="{$input.name}_image" type="button">
										{l s='Select image' mod='nrtmegamenu'} <i class="icon-angle-right"></i>
									</a>
								</div>	
							</li>
							<li style="width: 100%;">
								<input type="radio"	name="{$input.name}" id="choose_class_icon" value='{if isset($value_check) &&  $value_check.type == 2}{$fields_value[$input.name]}{/if}'{if isset($value_check) &&  $value_check.type == 2} checked="checked"{/if}/>
								<label for="choose_class_icon">{l s="Icon Class"}</label>
								<div class="wrapper_input_icon">
									<p> 
										<input id="{$input.name}_icon" onChange="changeIconMenu(2);" type="text" name="{$input.name}_icon" value="{if isset($value_check) &&  $value_check.type == 2}{$value_check.value}{/if}"> 
									</p>
								</div>	
							</li>
						</ul>
						
						<div class="alert alert-info">
						{l s='To add the icon class you need goto:' mod='nrtmegamenu'}
						<a href='https://icons8.com/line-awesome' target='_blank'>https://icons8.com/line-awesome</a><br/>
						( Ex:'lab la-facebook' ) {l s='for complete list of available icons.' mod='nrtmegamenu'}
						</div>
						
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">{l s="OK"}</button>
					</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				jQuery(function($){
					$("input[name={$input.name}]").change(function() { 
						if($("input[name={$input.name}]:checked").val() != ''){
							var obj = JSON.parse($("input[name={$input.name}]:checked").val()); 
						}else{
							var obj = [];
							obj['type'] = 1;
							obj['value'] = '';
						}
						
						var $html_icon = '';
						
						if(obj.value != ''){
							if(obj.type == 1){
								$html_icon = '<img style="margin-right: 10px;max-width: 200px;" src="'+obj.value+'" alt=""/>';
							}else{
								$html_icon = '<i style="margin-right: 10px;font-size: 20px;vertical-align: middle;" class="'+obj.value+'"></i>';
							}
						}

						$("#btn_{$input.name} i").remove();
						$("#btn_{$input.name} img").remove();
						$("#btn_{$input.name}").prepend($html_icon);
					});
				});
				function changeIconMenu(type){
					
					var $value = '';
					var $html_icon = '';
					
					if(type == 1){
						$('#choose_class_image').val('');
					    $value = $("#{$input.name}_image").val();
					}else{
						$('#choose_class_icon').val('');
						$value = $("#{$input.name}_icon").val();
					}
					
					{literal}
					var $json_icon = ({'type': type, 'value': $value});
					{/literal}
					 
					if($value != ''){
						if(type == 1){
							$html_icon = '<img style="margin-right: 10px;max-width: 200px;" src="'+$value+'" alt=""/>';
							$('#choose_class_image').val(JSON.stringify($json_icon));
						}else{
							$html_icon = '<i style="margin-right: 10px;font-size: 20px;vertical-align: middle;" class="'+$value+'"></i>';
							$('#choose_class_icon').val(JSON.stringify($json_icon));
						}
					}
					
					$("#btn_{$input.name} i").remove();
					$("#btn_{$input.name} img").remove();
					$("#btn_{$input.name}").prepend($html_icon);
				}
			</script>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}