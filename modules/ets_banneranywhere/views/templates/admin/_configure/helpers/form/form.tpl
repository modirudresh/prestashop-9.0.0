{*
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
*}
{extends file="helpers/form/form.tpl"}
{block name="label"}
	{if isset($input.label)}
		<label class="control-label col-lg-3 {if (isset($input.required) && $input.required && $input.type != 'radio') || (isset($input.showRequired) && $input.showRequired)} required{/if}">
			{if isset($input.hint)}
			<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
						{foreach $input.hint as $hint}
							{if is_array($hint)}
								{$hint.text|escape:'html':'UTF-8'}
							{else}
								{$hint|escape:'html':'UTF-8'}
							{/if}
						{/foreach}
					{else}
						{$input.hint|escape:'html':'UTF-8'}
					{/if}">
			{/if}
			{$input.label|escape:'html':'UTF-8'}
			{if isset($input.hint)}
			</span>
			{/if}
		</label>
	{/if}
{/block}
{block name="legend"}
    {$smarty.block.parent}
    {if isset($configTabs) && $configTabs}
        <ul class="ets_rule_tabs_configs">
            {foreach from=$configTabs key='key' item='tab'}
                <li class="rule_tab rule_tab_{$tab.tab|escape:'html':'UTF-8'}{if $key==0} active{/if}" data-tab-id="{$tab.tab|escape:'html':'UTF-8'}">{$tab.name|escape:'html':'UTF-8'}</li>
            {/foreach}
        </ul>
    {/if}
    {if isset($fields_value.id_ets_baw_banner) && $fields_value.id_ets_baw_banner}
        <div class="form-group">
            <div class="col-lg-3">&nbsp;</div>
            <div class="col-lg-9">
                <div class="block-short-code alert_info">
					<span class="alert_icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30"><path d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zm-248 50c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z" class=""></path></svg>
					</span>
                    <label class="control-label"> {l s='Banner shortcode' mod='ets_banneranywhere'}</label>
                    <div class="copied_text" title="{l s='Click to copy' mod='ets_banneranywhere'}" data-copied="{l s='Copied' mod='ets_banneranywhere' js=1}">
                        <input class="short-code" value='[banner-any-where id="{$fields_value.id_ets_baw_banner|intval}"]' type="text" readonly="true"/>
                    </div>
                    <div>{l s='Copy the shortcode above, paste it into anywhere on your product description, CMS page content, tpl file, etc. in order to display this banner' mod='ets_banneranywhere'}</div>
                </div>
            </div>
        </div>
    {/if}
{/block}
{block name="input"}
    {if $input.type == 'checkbox'}
            {if isset($input.values.query) && $input.values.query}
                {assign var=id_checkbox value=$input.name|cat:'_'|cat:'all'}
                {assign var=checkall value=true}
				{if !(isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && in_array('all',$fields_value[$input.name]))} 
                    {assign var=checkall value=false}
                {/if}
                <div class="checkbox_all checkbox">
					{strip}
						<label for="{$id_checkbox|escape:'html':'UTF-8'}">                                
							<input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" id="{$id_checkbox|escape:'html':'UTF-8'}" value="all" {if $checkall} checked="checked"{/if} />
							{l s='Select/Unselect all' mod='ets_banneranywhere'}
						</label>
					{/strip}
				</div>
                {foreach $input.values.query as $value}
    				{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]|escape:'html':'UTF-8'}
    				<div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
    					{strip}
    						<label for="{$id_checkbox|escape:'html':'UTF-8'}">                                
    							<input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" id="{$id_checkbox|escape:'html':'UTF-8'}" {if isset($value[$input.values.id])} value="{$value[$input.values.id]|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && (in_array($value[$input.values.id],$fields_value[$input.name]) || in_array('all',$fields_value[$input.name])) } checked="checked"{/if} {if isset($value.class) && $value.class} class="{$value.class|escape:'html':'UTF-8'}"{/if}/>
    							{$value[$input.values.name]|replace:'[highlight]':'<strong>'|replace:'[end_highlight]':'</strong>' nofilter}
    						</label>
    					{/strip}
    				</div>
    			{/foreach} 
            {/if}
    {elseif $input.type == 'file_lang'}
		{if $languages|count > 1}
		  <div class="form-group">
		{/if}
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>

					<div class="col-lg-9">
						{/if}
						<div class="dummyfile input-group sass">
							<input id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}" type="file" name="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}" class="hide-file-upload" />
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='ets_banneranywhere'}
								</button>
							</span>
						</div>
                        {if isset($fields_value[$input.name]) && $fields_value[$input.name] && $fields_value[$input.name][$language.id_lang]}
                            <div class="col-lg-9 uploaded_img_wrapper">
                        		<img title="" style="display: inline-block; max-width: 200px;" src="{$image_baseurl|escape:'html':'UTF-8'}{$fields_value[$input.name][$language.id_lang]|escape:'html':'UTF-8'}" />
                                {if $input.required}
                                    <a class="delete_url" style="display: inline-block; text-decoration: none!important;" href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=ets_banneranywhere&deleteImage&id_ets_baw_banner={$fields_value.id_ets_baw_banner|intval}&id_lang={$language.id_lang|intval}"><span style="color: #666"><i style="font-size: 20px;" class="process-icon-delete"></i></span></a>
                                {/if}
                            </div>
						{/if}
						{if $languages|count > 1}
					</div>

					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							{$language.iso_code|escape:'html':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=lang}
							<li><a href="javascript:hideOtherLanguage({$lang.id_lang|intval});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				{/if}
				{if $languages|count > 1}
					</div>
				{/if}
				<script>
				$(document).ready(function(){
					$("#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}-selectbutton,#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}-name").click(function(e){
						$("#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}").trigger('click');
					});
					$("#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}").change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$("#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|intval}-name").val(file[file.length-1]);
					});
				});
			</script>
			{/foreach}
		{if $languages|count > 1}
		  </div>
		{/if}
    {elseif $input.type=='range'}
        <div class="range_custom">
            <input name="{$input.name|escape:'html':'UTF-8'}" min="{$input.min|intval}" max="{$input.max|intval}" value="{if $fields_value[$input.name]}{$fields_value[$input.name]|escape:'html':'UTF-8'}{else}1{/if}"  forever="1" type="range" data-suffix="{$input.data_suffix|escape:'html':'UTF-8'}" data-suffixs="{$input.data_suffixs|escape:'html':'UTF-8'}" />
            <div class="range_new">
                <span class="range_new_bar"></span>
                    <span class="range_new_run" style="">
                    <span class="range_new_button"></span>
                </span>
            </div>
            <span class="input-group-unit">{if $fields_value[$input.name] >1}{$fields_value[$input.name]|escape:'html':'UTF-8'}{else}1{/if}</span>
            <span class="range_suffixs">{$input.data_suffixs|escape:'html':'UTF-8'}</span>
            <span class="range_min">{$input.min|escape:'html':'UTF-8'}</span>
            <span class="range_max">{$input.max|escape:'html':'UTF-8'}</span>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name='description'}
    {if $input.type == 'file' && isset($input.is_image) && $input.is_image}
        {$smarty.block.parent}
        <p class="help-block">{l s='Available image type: jpg, png, gif, jpeg'  mod='ets_banneranywhere'}. {l s='Limit'  mod='ets_banneranywhere'} {Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|escape:'html':'UTF-8'}Mb</p>

    {elseif isset($input.desc) && !is_array($input.desc)}
        <p class="help-block">{$input.desc|replace:'[highlight]':'<code>'|replace:'[end_highlight]':'</code>'|replace:'[highlighta]':'<a href="#">'|replace:'[end_highlighta]':'</a>' nofilter}</p>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}