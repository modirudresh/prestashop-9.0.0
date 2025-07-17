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
<div class="ets_baw_display_banner {$banner_class|escape:'html':'UTF-8'} {$position|escape:'html':'UTF-8'}">
    {if $banner.content_before_image}
        <div class="content_before_image">
            {$banner.content_before_image nofilter}
        </div>
    {/if}
    {if $banner.image}
        {if isset($banner.image_url) && $banner.image_url != ''}<a class="banner_image_url" href="{$banner.image_url|escape:'html':'UTF-8'}">{/if}
            {if $position == 'displaybanner'}
                    <div{if $banner.title} title="{$banner.title|escape:'html':'UTF-8'}"{/if} class="banner_top_site" style="background-image: url({$link->getMediaLink("`$smarty.const._PS_ETS_BAW_IMG_``$banner.image|escape:'htmlall':'UTF-8'`")})"></div>
            {else}
                    <img {if $banner.title} title="{$banner.title|escape:'html':'UTF-8'}"{/if} src="{$link->getMediaLink("`$smarty.const._PS_ETS_BAW_IMG_``$banner.image|escape:'htmlall':'UTF-8'`")}"{if $banner.image_alt} alt="{$banner.image_alt|escape:'html':'UTF-8'}"{/if} />
            {/if}
        {if isset($banner.image_url) && $banner.image_url != ''}</a>{/if}
    {/if}
    {if $banner.content_after_image}
        <div class="content_after_image">
            {$banner.content_after_image nofilter}
        </div>
    {/if}
</div>
<div class="clearfix"></div>