{*
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
*}
{extends file="helpers/form/form.tpl"}
{block name="label"}
    <label class="control-label col-lg-3{if ((isset($input.required) && $input.required) || (isset($input.showRequired) && $input.showRequired) ) && $input.type != 'radio'} required{/if}">
        {if isset($input.hint)}
            <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
            {foreach $input.hint as $hint}
                {if is_array($hint)} {$hint.text|escape:'html':'UTF-8'} {else} {$hint|escape:'html':'UTF-8'} {/if}
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
{/block}
{block name="input"}
    {if $input.type == 'checkbox'}
        {if isset($input.values.query) && $input.values.query}
            {assign var=id_checkbox value=$input.name|cat:'_'|cat:'all'}
            {assign var=checkall value=true}
			{foreach $input.values.query as $value}
				{if !(isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && in_array($value.value,$fields_value[$input.name]))} 
                    {assign var=checkall value=false}
                {/if}
			{/foreach} 
            {foreach $input.values.query as $value}
				{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]|escape:'html':'UTF-8'}
				<div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
					{strip}
						<label for="{$id_checkbox|escape:'html':'UTF-8'}">                                
							<input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" id="{$id_checkbox|escape:'html':'UTF-8'}" {if isset($value.value)} value="{$value.value|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && in_array($value.value,$fields_value[$input.name])} checked="checked"{/if} />
							{$value[$input.values.name]|escape:'html':'UTF-8'}
						</label>
					{/strip}
				</div>
			{/foreach} 
        {/if}
   {elseif $input.type == 'pa_img_radio'}
        {if isset($input.values) && $input.values}
            <ul class="ets_rcf_options">
                {foreach from=$input.values item='option'}
                    <li class="ets_rcf_item">
                        <div class="radio">
                            <label for="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}">
                                <input type="radio" style="outline: none;" id="{$input.name|escape:'html':'UTF-8'}_{$option.id_option|escape:'quotes':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{$option.id_option|escape:'quotes':'UTF-8'}" {if !empty($fields_value[$input.name]) && $fields_value[$input.name] == $option.id_option}checked{/if}>
                                <img src="{$path|cat: 'views/img/'|cat: $option.img|escape:'html':'UTF-8'}" >
                                {$option.name|escape:'html':'UTF-8'}
                            </label>
                        </div>
                    </li>
                {/foreach}
            </ul>
        {/if}
   {elseif $input.name == 'ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY'}
        {$smarty.block.parent}
        <p class="help-block">
            <a target="_blank" href="{if isset($path) && $path }{$path nofilter}views/pdf/recaptcha_v2.pdf{/if}" title="{l s='How to get Site key and Secret key?' mod='ets_recaptcha_free'}">{l s='How to get Site key and Secret key?' mod='ets_recaptcha_free'}</a>
        </p>
    {elseif $input.name == 'ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY'}
        {$smarty.block.parent}
        <p class="help-block">
            <a target="_blank" href="{if isset($path) && $path }{$path nofilter}views/pdf/recaptcha_v3.pdf{/if}" title="{l s='How to get Site key and Secret key?' mod='ets_recaptcha_free'}">{l s='How to get Site key and Secret key?' mod='ets_recaptcha_free'}</a>
        </p>
   {else}
    {$smarty.block.parent}
   {/if}
{/block}