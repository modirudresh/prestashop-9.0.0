{extends file='page.tpl'}

{$title_page = {l s='My Compare' mod='nrtcompare'}}
	 
{block name='page_header_title'}
	{$title_page}
{/block}

{block name="content"}
	<div id="my_compare">
		{if $list_products}
			{$imageType	= 'home_default'}

			{if isset($opThemect.general_product_image_type_large) && $opThemect.general_product_image_type_large}
				{$imageType = $opThemect.general_product_image_type_large}
			{/if}	
			<div id="js-compare-table">
				<div class="compare-table-actions" style="display: none;">
					<a href="javascript:void(0)" class="js-compare-remove-all">
						<i class="las la-trash-alt"></i> {l s='Remove all products' mod='nrtcompare'}
					</a>
				</div>
				<div class="wrapper-compare-table">
					<div class="compare-row">
						<div class="compare-col compare-label"></div>
						{foreach from=$list_products item="product"}
							<div class="compare-col compare-value js-compare-{$product.id_product}-0">
								<div class="compare-header">
									<a href="javascript:void(0)" class="js-compare-remove js-compare-remove-{$product.id_product}-0 btn-action-compare-remove"
										data-id-product="{$product.id_product}"
										data-id-product-attribute="0">
										{l s='Remove' mod='nrtcompare'}
									</a>
									<a href="{$product.url}" class="product-image" title="{$product.name}">
										<div class="wrapper-imgs">
                                            {if $product.cover}
                                                {$image = $product.cover}
                                            {else}
                                                {$image = $urls.no_picture_image}
                                            {/if}
											{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
										</div>
									</a>  
									<a class="product-title" href="{$product.url}">
										{$product.name}
									</a>
									{include file='catalog/_partials/miniatures/_element/price.tpl'}
									<div class="js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
										{include file='catalog/_partials/miniatures/_element/add-to-cart.tpl'}
									</div>
								</div>
							</div>
						{/foreach}
					</div>
					<div class="compare-row">
						<div class="compare-col compare-label">{l s='Description' mod='nrtcompare'}</div>
						{foreach from=$list_products item="product"}
							<div class="compare-col compare-value js-compare-{$product.id_product}-0">
								{$product.description_short nofilter}
							</div>
						{/foreach}
					</div>
					<div class="compare-row">
						<div class="compare-col compare-label">{l s='SKU' mod='nrtcompare'}</div>
						{foreach from=$list_products item="product"}
							<div class="compare-col compare-value js-compare-{$product.id_product}-0">
								{if $product.reference}
									{$product.reference nofilter}
								{else}
									N/A
								{/if}
							</div>
						{/foreach}
					</div>
					<div class="compare-row">
						<div class="compare-col compare-label">{l s='Availability' mod='nrtcompare'}</div>
						{foreach from=$list_products item="product"}
							<div class="compare-col compare-value js-compare-{$product.id_product}-0">
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
							</div>
						{/foreach}
					</div>
					{foreach from=$ordered_features item=feature}
						<div class="compare-row">
							<div class="compare-col compare-label">{$feature.name|escape:'html':'UTF-8'}</div>
							{foreach from=$list_products item="product"}
								<div class="compare-col compare-value js-compare-{$product.id_product}-0">
									{assign var='product_id' value=$product.id_product}
									{assign var='feature_id' value=$feature.id_feature}
									{if isset($product_features[$product_id])}
										{assign var='tab' value=$product_features[$product_id]}
										{if (isset($tab[$feature_id]))} {$tab[$feature_id]|escape:'html':'UTF-8'}{/if}
									{else}
										-
									{/if}
								</div>
							{/foreach}
						</div>
					{/foreach}
					{hook h='displayProductExtraComparison' list_ids_product=$list_ids_product}
				</div>
			</div>
			<div id="js-compare-warning" style="display:none;" class="empty-products">
				<p class="empty-title empty-title-compare">
					{l s='Compare list is empty.' mod='nrtcompare'}
				</p>
				<div class="empty-text">
					{l s='No products added in the compare list. You must add some products to compare them.' mod='nrtcompare'}
				</div>
				<p class="return-to-home">
					<a href="{$urls.pages.index}" class="btn btn-primary">
						<i class="las la-reply"></i>
						{l s='Return to home' mod='nrtcompare'}
					</a>
				</p>
			</div>
		{else}
			<div class="empty-products">
				<p class="empty-title empty-title-compare">
					{l s='Compare list is empty.' mod='nrtcompare'}				
				</p>
				<div class="empty-text">
					{l s='No products added in the compare list. You must add some products to compare them.' mod='nrtcompare'}
				</div>
				<p class="return-to-home">
					<a href="{$urls.pages.index}" class="btn btn-primary">
						<i class="las la-reply"></i>
						{l s='Return to home' mod='nrtcompare'}
					</a>
				</p>
			</div>
		{/if}
	</div>
{/block}