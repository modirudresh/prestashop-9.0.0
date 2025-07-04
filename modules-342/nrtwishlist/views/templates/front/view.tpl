{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{extends file='customer/page.tpl'}
	 
{$title_page = {l s='My wishlist' mod='nrtwishlist'}}
	 
{block name='page_header_title'}
	{$title_page}
{/block}

{function name="faxps_wlProducts"}
  {strip}
    {if isset($wlProducts) && $wlProducts}
	 
		{$imageType	= 'home_default'}

		{if isset($opThemect.general_product_image_type_small) && $opThemect.general_product_image_type_small}
			{$imageType = $opThemect.general_product_image_type_small}
		{/if}	
	 	<div id="my_wishlist">
	 		<div id="js-wishlist-table" class="wrapper-wishlist-table">
				<div class="wishlist-table-actions" style="display: none;">
					<a href="javascript:void(0)" class="js-wishlist-remove-all">
						<i class="las la-trash-alt"></i> {l s='Remove all products' mod='nrtwishlist'}
					</a>
				</div>
				<table class="shop_table_responsive shop_table">
					<thead>
						<tr>
							{if !$readOnly}<th class="product-remove"></th>{/if}
							<th class="product-thumbnail"></th>
							<th class="product-name">{l s='Name' mod='nrtwishlist'}</th>
							<th class="product-w-price">{l s='Price' mod='nrtwishlist'}</th>
							<th class="product-stock">{l s='Stock' mod='nrtwishlist'}</th>
							<th class="product-button"></th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$wlProducts item="product"}
							<tr class="js-wishlist-{$product.id_product}-0">
								{if !$readOnly}
									<td class="product-remove">
										<a href="javascript:void(0)" class="js-wishlist-remove btn-action-wishlist-remove js-wishlist-remove-{$product.id_product}-0"
											data-id-product="{$product.id_product}"
											data-id-product-attribute="0">
											{l s='Remove' mod='nrtwishlist'}
										</a>
									</td>
								{/if}
								<td class="product-thumbnail">
									<a class="product-image" href="{$product.url}" title="{$product.name}">
									  <div class="wrapper-imgs">
										{if $product.cover}
											{$image = $product.cover}
										{else}
											{$image = $urls.no_picture_image}
										{/if}
										{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
									  </div>
									</a>  
								</td>
								<td class="product-name">
									<a class="product-title" href="{$product.url}">{$product.name}</a>
								</td>
								<td class="product-price price">
									{include file='catalog/_partials/miniatures/_element/price.tpl'}
								</td>	
								<td class="product-stock">
									{if $product.show_availability && $product.availability_message}
										{if $product.availability == 'available'}
											<span class="type-available">
										{elseif $product.availability == 'last_remaining_items'}
											<span class="type-last-remaining-items">
										{else}
											<span class="type-out-stock">
										{/if}
											{$product.availability_message}
											</span>
									{/if}
								</td>
								<td class="product-button">
									<div class="js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
										{include file='catalog/_partials/miniatures/_element/add-to-cart.tpl'}
									</div>	
								</td>							
							</tr>
						{/foreach}
					</tbody>
				</table>
				{if $customer.is_logged && $token}
					<h5>{l s='Share your wishlist' mod='nrtwishlist'}</h5>
					<div class="input-group">
						<input class="form-control js-to-clipboard" readonly="readonly" type="url" value="{url entity='module' name='nrtwishlist' relative_protocol=false controller='view' params=['token' => $token]}">
						<span class="input-group-btn">
							<button class="btn btn-secondary" type="button" id="wishlist-clipboard-btn" data-text-copied="{l s='Copied' mod='nrtwishlist'}" data-text-copy="{l s='Copy' mod='nrtwishlist'}">{l s='Copy' mod='nrtwishlist'}</button>
						</span>
					</div>
					{hook h='displayWishListShareButtons'}
				{/if}
			</div>
	 	</div>
		<div id="js-wishlist-warning" style="display:none;" class="empty-products">
			<p class="empty-title empty-title-wishlist">
				{l s='Wishlist is empty.' mod='nrtwishlist'}				
			</p>
			<div class="empty-text">
				{l s='No products added in the wishlist list. You must add some products to wishlist them.' mod='nrtwishlist'}
			</div>
			<p class="return-to-home">
				<a href="{$urls.pages.index}" class="btn btn-primary">
					<i class="las la-reply"></i>
					{l s='Return to home' mod='nrtwishlist'}
				</a>
			</p>
		</div>
    {else}
		<div class="empty-products">
			<p class="empty-title empty-title-wishlist">
				{l s='Wishlist list is empty.' mod='nrtwishlist'}				
			</p>
			<div class="empty-text">
				{l s='No products added in the wishlist list. You must add some products to wishlist them.' mod='nrtwishlist'}
			</div>
			<p class="return-to-home">
				<a href="{$urls.pages.index}" class="btn btn-primary">
					<i class="las la-reply"></i>
					{l s='Return to home' mod='nrtwishlist'}
				</a>
			</p>
		</div>
    {/if}
  {/strip}
{/function}

{if $customer.is_logged}
	{if !$readOnly}
		{block name='page_content'}
			{faxps_wlProducts}
		{/block}
	{else}
		{block name='axps_page_content'}
			{faxps_wlProducts}
		{/block}
	{/if}
{else}
	{block name='page_content'}
		{faxps_wlProducts}
	{/block}
{/if}