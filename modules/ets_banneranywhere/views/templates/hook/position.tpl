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
{if $display_positions}
    {if in_array('all',$display_positions)}
        <p>{l s='All' mod='ets_banneranywhere'}</p>
    {else}
        <ul>
            {foreach from=$display_positions item='display_position'}
                {if isset($positions[$display_position])}
                    <li>{$positions[$display_position]|replace:'[highlight]':'<strong>'|replace:'[end_highlight]':'</strong>' nofilter}</li>
                {/if}
            {/foreach}
        </ul>
    {/if}
{/if}