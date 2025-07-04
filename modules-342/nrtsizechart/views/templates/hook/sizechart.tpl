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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="moda_sizechart" class="modal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document">
    <div class="modal-content">
		<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
			<span aria-hidden="true">&times;</span>
		</button>
		<div id="nrtsizechart" class="modal-body">
			<h4>{l s='Size Guide' mod='nrtsizechart'}</h4>
			<div class="nrtsizechart-content">
				{if $sh_measure}   
					<ul class="nav nav-tabs">
						{if isset($guide->description) && isset($guide->description) !=''}
						<li class="nav-item"><a class="nav-link-size active" href="#nrtsizechart-guide"  title="{$guide->title} " data-toggle="tab" role="tab" aria-controls="nrtsizechart-guide">{$guide->title}</a></li>
						{else}
						{if $sh_global}<li class="nav-item"><a class="nav-link-size active" href="#nrtsizechart-global"  title="{l s='Guide' mod='nrtsizechart'}" data-toggle="tab" role="tab" aria-controls="nrtsizechart-global">{l s='Guide' mod='nrtsizechart'}</a></li>{/if}
						{/if}
						{if $sh_measure}<li class="nav-item"><a class="nav-link-size" href="#nrtsizechart-how"  title="{l s='How to measure' mod='nrtsizechart'}" data-toggle="tab" role="tab" aria-controls="nrtsizechart-how">{l s='How to measure' mod='nrtsizechart'}</a></li>{/if}
					</ul>
				{/if}
				<div class="tab-content">
					{if isset($guide->description) && isset($guide->description) !=''}
						<div id="nrtsizechart-guide" role="tabpanel" class="tab-pane rte fade active in">
							{$guide->description nofilter} 
						</div>
					{else}
						{if $sh_global}
							<div id="nrtsizechart-global"  class="tab-pane rte fade active in" role="tabpanel">
								{$global|stripslashes nofilter}
							</div>
						{/if}
					{/if}
					{if $sh_measure}
						<div id="nrtsizechart-how"  class="tab-pane rte fade" role="tabpanel">
							{$howto|stripslashes nofilter}
						</div>
					{/if}
				</div>
			</div>
		</div>
    </div>
</div></div>

