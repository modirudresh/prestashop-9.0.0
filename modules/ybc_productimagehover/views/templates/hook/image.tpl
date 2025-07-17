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
{if isset($product_name) && isset($img_url)}
    <img class="{if $YBC_PI_TRANSITION_EFFECT}{$YBC_PI_TRANSITION_EFFECT|escape:'html':'UTF-8'}{else}fade{/if} replace-2x img-responsive ybc_img_hover" src="{$img_url|escape:'html':'UTF-8'}" alt="{$product_name|escape:'html':'UTF-8'}" itemprop="image" title="{$product_name|escape:'html':'UTF-8'}" />
{/if}