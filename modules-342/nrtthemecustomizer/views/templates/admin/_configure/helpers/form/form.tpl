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

{block name="defaultForm"}
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
            	<div class="col-md-2 left-column-config" >
                    <div id="nrtthemecustomizer-tabs">
                        {foreach $nrttabs as $tabTitle => $tabClass}
                            <span class="tab list-group-item" data-tab="{$tabClass}">
                                {$tabTitle}
                            </span>
                        {/foreach}
                    </div>
                </div>
       		    <div class="col-md-10 right-column-config">
                    {$smarty.block.parent}
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="label"}

	{if $input.type == "line_driver"}
    <hr>
    {elseif $input.type == "no_label"}

    {elseif $input.type == "title_label"}
        <div class="title-reparator">
        	<div class="col-lg-offset-3">{$input.label}</div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}


{/block}

{block name="input"}

    {if $input.type == "chose_image"}
        <p> 
        	<input id="{$input.name}" type="text" name="{$input.name}" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"> 
        </p>
        <a href="{__PS_BASE_URI__}{basename(_PS_ADMIN_DIR_)}/filemanager/dialog.php?type=1&field_id={$input.name}" class="btn btn-default iframe-upload"  data-input-name="{$input.name}" type="button">
        	{l s='Select image' mod='nrtthemecustomizer'} <i class="icon-angle-right"></i>
        </a>
    {elseif $input['name'] == "style_on_theme"}
    	<textarea id="style_on_theme" name="style_on_theme" class="hidden">
        	{$fields_value[$input.name] nofilter}
        </textarea>
		<div class="row" style="margin-bottom:15px;">
            <div class="col-xs-6">
            	<h4>{l s='Selector' mod='nrtthemecustomizer'}</h4>
            </div>
            <div class="col-xs-3">
            	<h4>{l s='Params' mod='nrtthemecustomizer'}</h4>
            </div>
            <div class="col-xs-3">
            	<h4>{l s='Value' mod='nrtthemecustomizer'}</h4>
            </div>
        </div>
        <div id="template_style_label" class="hidden"> 
        	<div class="row wrapper_style">
            	<div class="sort_tab icon icon-arrows"></div>
                <div class="col-xs-12">
                    <input class="label_input" name="style_label" value="Label">
                </div>
                <div class="delete_style">
                    <span class="icon icon-trash "></span> 
                </div>
            </div>
        </div>            
        <div id="template_style" class="hidden"> 
            <div class="row wrapper_style">
            	<div class="sort_tab icon icon-arrows"></div>
                <div class="col-xs-6">
                    <input name="style_selector"  value="" type="text">
                </div>
                <div class="col-xs-3">
                    <input name="style_params"  value="" type="text">
                </div>
                <div class="col-xs-3">
                    <input name="style_value"  value="" type="text">
                </div>
                <div class="delete_style">
                    <span class="icon icon-trash "></span> 
                </div>
            </div>	
        </div>	
        
        <div id="group_style">
        {foreach $fields_value[$input.name]|json_decode:true AS $style }
        	{if isset($style.label) && $style.label}
                <div class="row wrapper_style">
                    <div class="sort_tab icon icon-arrows"></div>
                    <div class="col-xs-12">
                        <input class="label_input" name="style_label" value="{$style.label}">
                    </div>
                    <div class="delete_style">
                        <span class="icon icon-trash "></span> 
                    </div>
                </div>
            {else}
                <div class="row wrapper_style">
                    <div class="sort_tab icon icon-arrows"></div>
                    <div class="col-xs-6">
                        <input name="style_selector"  value="{$style.selector}" type="text">
                    </div>
                    <div class="col-xs-3">
                        <input name="style_params"  value="{$style.params}" type="text">
                    </div>
                    <div class="col-xs-3">
                        <input name="style_value"  value="{$style.value}" type="text">
                    </div>
                    <div class="delete_style">
                        <span class="icon icon-trash "></span> 
                    </div>
                </div>	
        	{/if}
        {/foreach}
        </div>
        
        </br>
        <div id="button_template_style_label" class="btn btn-info"> 
        	+ {l s='Label' mod='nrtthemecustomizer'}
        </div>	
        <div id="button_template_style" class="btn btn-info"> 
        	+ {l s='Selector' mod='nrtthemecustomizer'}
        </div>		
    {elseif $input['name'] == "NRT_import_export"}
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="1">
					{l s='Import Config Default' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="2">
					{l s='Import Config Fashion2' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="3">
					{l s='Import Config Oganic' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="4">
					{l s='Import Config Anivio 1' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="5">
					{l s='Import Config Anivio 2' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="6">
					{l s='Import Config Anivio 3' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="7">
					{l s='Import Config Anivio 4' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="8">
					{l s='Import Config Supper Martket' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="9">
					{l s='Import Config Electronics' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="10">
					{l s='Import Config Decor' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="11">
					{l s='Import Config Bikes' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="12">
					{l s='Import Config Wine' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="13">
					{l s='Import Config Watches' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="14">
					{l s='Import Config Digitals' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="15">
					{l s='Import Config Flowers' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="16">
					{l s='Import Config Sport' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="17">
					{l s='Import Config Accessories' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="18">
					{l s='Import Config Jewellery' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="19">
					{l s='Import Config Furniture' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="20">
					{l s='Import Config Tools' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="21">
					{l s='Import Config Books' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="22">
					{l s='Import Config Shoes' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="23">
					{l s='Import Config Drinks' mod='nrtthemecustomizer'}
				</button>
			</div>
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="24">
					{l s='Import Config Gift' mod='nrtthemecustomizer'}
				</button>
			</div>
		</div>
        <hr>
		<div class="row">
			<div class="col-xs-3">
				<button type="submit" class="btn btn-default" name="importConfigurationDemo" value="25">
					{l s='Import Config Food' mod='nrtthemecustomizer'}
				</button>
			</div>	
		</div>
        <hr>
        <h4>{l s='Import configuration' mod='nrtthemecustomizer'}</h4>
        <hr>
        <div style="display:inline-block;">
        	<input id="uploadConfig" name="uploadConfig" type="file">
        </div>
        <button type="submit" class="btn btn-default btn-lg" name="importConfiguration">
            <span class="icon icon-upload"></span> 
            {l s='Import' mod='nrtthemecustomizer'}
        </button>
        <hr>
        <h4>{l s='Export configuration' mod='nrtthemecustomizer'}</h4>
        <hr>
        <a class="btn btn-default btn-lg" href="{$export_link}">
        	<span class="icon icon-share"></span> {l s='Export to file' mod='nrtthemecustomizer'}
        </a>
    {elseif $input['name'] == "NRT_add_hook_for_theme"}
		<div class="row">
			<div class="col-xs-6">
				<button type="submit" class="btn btn-default" name="nrtAddHooksForTheme" value="1">
					{l s='Add Hooks For Theme' mod='nrtthemecustomizer'}
				</button>
			</div>	
			<div class="col-xs-6">
				<button type="submit" class="btn btn-default" name="nrtClearCache" value="1">
					{l s='Clear Cache' mod='nrtthemecustomizer'}
				</button>
			</div>	
		</div>
    {elseif $input['type'] == "chose_style"}
    
        <div class="image-select">
            {foreach $input.values AS $option }
                <input id="{$input.name|escape:'html':'utf-8'}-{$option.value}" type="radio"
               name="{$input.name|escape:'html':'utf-8'}"
               class='hidden'
               value="{$option.value}" {if $fields_value[$input.name] == ''}{if $option@index eq 0} checked{/if}{/if} {if $option.value == $fields_value[$input.name]}checked{/if} />
                <div class="image-option">
                    <label for="{$input.name|escape:'html':'utf-8'}-{$option.value}">
                		<h4>{$option.name}</h4>
						<img src="{$option.img}" alt="{$option.name}" class="img-responsive"/>
                    </label>
                </div>
            {/foreach}
        </div>
    {else}

        {$smarty.block.parent}

    {/if}

{/block}

