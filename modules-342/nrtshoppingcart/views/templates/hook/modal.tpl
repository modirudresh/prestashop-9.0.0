{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{$imageType	= 'medium_default'}

{if isset($opThemect.general_product_image_type_large) && $opThemect.general_product_image_type_large}
	{$imageType = $opThemect.general_product_image_type_large}
{/if}	

<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title"><i class="las la-check-circle text-success"></i>&nbsp;{l s='Product successfully added to your shopping cart' d='Shop.Theme.Checkout'}</h4>
	</div>
	<div class="modal-body">
		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-6">
						<div class="wrapper-imgs">
							{if $product.default_image}
								{$image = $product.default_image}
							{else}
								{$image = $urls.no_picture_image}
							{/if}
							{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
						</div>
					</div>
					<div class="col-md-6">
						<br class="hidden-md-up" />
						<h6>{$product.name}</h6>
						<p class="product-price">{$product.price}</p>
						{hook h='displayProductPriceBlock' product=$product type="unit_price"}
						{foreach from=$product.attributes item="property_value" key="property"}
							<span>{l s='%label%:' sprintf=['%label%' => $property] d='Shop.Theme.Global'}<strong> {$property_value}</strong></span><br>
						{/foreach}
						<span class="product-quantity">{l s='Quantity:' d='Shop.Theme.Checkout'}&nbsp;<strong>{$product.cart_quantity}</strong></span>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="cart-content">
					<br class="hidden-md-up" />
					{if $cart.products_count > 1}
						<p class="cart-products-count h4">{l s='There are %products_count% items in your cart.' sprintf=['%products_count%' => $cart.products_count] d='Shop.Theme.Checkout'}</p>
					{else}
						<p class="cart-products-count h4">{l s='There is %products_count% item in your cart.' sprintf=['%products_count%' =>$cart.products_count] d='Shop.Theme.Checkout'}</p>
					{/if}
					<p><span class="label">{l s='Subtotal:' d='Shop.Theme.Checkout'}</span>&nbsp;<span class="subtotal value">{$cart.subtotals.products.value}</span></p>
					{if $cart.subtotals.shipping.value}
						<p><span>{l s='Shipping:' d='Shop.Theme.Checkout'}</span>&nbsp;<span class="shipping value">{$cart.subtotals.shipping.value} {hook h='displayCheckoutSubtotalDetails' subtotal=$cart.subtotals.shipping}</span></p>
					{/if}
					{if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
						<p><span>{$cart.totals.total.label}{if $configuration.display_taxes_label}&nbsp;{$cart.labels.tax_short}{/if}</span>&nbsp;<span>{$cart.totals.total.value}</span></p>
						<p class="product-total"><span class="label">{$cart.totals.total_including_tax.label}</span>&nbsp;<span class="value">{$cart.totals.total_including_tax.value}</span></p>
					{else}
						<p class="product-total"><span class="label">{$cart.totals.total.label}&nbsp;{if $configuration.taxes_enabled && $configuration.display_taxes_label}{$cart.labels.tax_short}{/if}</span>&nbsp;<span class="value">{$cart.totals.total.value}</span></p>
					{/if}

					{if $cart.subtotals.tax}
						<p class="product-tax">{l s='%label%:' sprintf=['%label%' => $cart.subtotals.tax.label] d='Shop.Theme.Global'}&nbsp;<span class="value">{$cart.subtotals.tax.value}</span></p>
					{/if}
					{hook h='displayCartModalContent' product=$product}
					{hook h='displayNrtCartInfo'}
					<div class="cart-content-btn">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">
							{l s='Continue shopping' d='Shop.Theme.Actions'}
						</button>
						<a href="{$cart_url}" class="btn btn-primary">{l s='Proceed to checkout' d='Shop.Theme.Actions'}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	{hook h='displayCartModalFooter' product=$product}
</div>
