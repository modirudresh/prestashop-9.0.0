{**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 **}

<div class="axps-dropdown-wrapper">
	<div class="axps-dropdown-toggle" data-toggle="axps-dropdown-widget">
		<span>{$current_currency.sign} {$current_currency.iso_code}</span>
		<span class="icon-toggle fa fa-angle-down"></span>
	</div>
	<div class="axps-dropdown-menu">
		{foreach from=$currencies item=currency}
			<a data-btn-currency="{$currency.id}" href="javascript:void(0)" {if $currency.current} class="selected"{/if}>
				{$currency.iso_code} {$currency.sign}
			</a>
		{/foreach}
	</div>
</div>