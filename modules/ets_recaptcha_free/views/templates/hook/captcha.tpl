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
<{if $is16}div{else}p{/if} class="captcha_{$posTo|escape:'html':'UTF-8'} {if $is16}form-group row required{else}text{/if} {if isset($posTo) && $posTo}{$posTo|escape:'quotes'}{/if} page_{$captcha_page|escape:'html':'UTF-8'} ver1{if $is17}7{elseif $is16}6{else}5{/if}">
    {if $ETS_RCF_CAPTCHA_TYPE != 'google_v3'}
        <label for="pa_captcha" {if $is16}class="col-md-3"{/if}>
            {if isset($ETS_RCF_CAPTCHA_TYPE) && $ETS_RCF_CAPTCHA_TYPE != 'google'}
                {l s='Enter security code' mod='ets_recaptcha_free'}
            {else}
                {l s='Security check' mod='ets_recaptcha_free'}
            {/if}
        </label>
    {else}
        <label for="pa_captcha" class="col-md-3"></label>
    {/if}
    <{if $is16}div{else}span{/if} class="pa-captcha-inf {if $is16}col-md-6{else}pa-captcha-field-cell{/if}">
        {if $ETS_RCF_CAPTCHA_TYPE == 'google_v3'}
            <div id="g-recaptcha-response-{rand()|intval}"></div>
        {elseif $ETS_RCF_CAPTCHA_TYPE == 'google'}
            <div class="g-recaptcha"></div>
        {/if}
    </{if $is16}div{else}span{/if}>
    {if $is16}<div class="col-md-3 form-control-comment"></div>{/if}
</{if $is16}div{else}p{/if}>