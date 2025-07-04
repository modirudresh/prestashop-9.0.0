{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if !empty($pagenums)}
    <nav class="pagination">
        {if $c > 1}
            {$page = $c-1}
            <a class="page-numbers prev js-comment-pn" href="javascript:void(0)" data-id-product="{$idProduct}" data-page="{$page}">
                « {l s='Previous' d='Shop.Theme.Actions'}
            </a>
        {/if}
        {for $k=0 to $pagenums}
            {if ($k+1) == $c}
                <span class="page-numbers current">{$k+1}</span>
            {else}
                {$page = $k+1}
                <a class="page-numbers js-comment-pn" href="javascript:void(0)" data-id-product="{$idProduct}" data-page="{$page}">{$k+1}</a>
            {/if}
        {/for}
        {if $c < ($pagenums+1)}		   
            {$page = $c+1}				   
            <a class="page-numbers next js-comment-pn" href="javascript:void(0)" data-id-product="{$idProduct}" data-page="{$page}">
                {l s='Next' d='Shop.Theme.Actions'} »
            </a>
        {/if}	
    </nav>
    <hr/>
{/if}
