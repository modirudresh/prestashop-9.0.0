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

{if isset($customtabs) }
	{foreach from=$customtabs item=customtab key=customtabKey}
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#custom-tab-{$customtabKey}">
              {$customtab->title nofilter}
            </a>
        </li>
    {/foreach}
{else}  
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#custom-tab-global">
          {$title_global nofilter}
        </a>
    </li>
{/if}