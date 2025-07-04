{$imageType	= 'cart_default'}

{if isset($opThemect.general_product_image_type_small) && $opThemect.general_product_image_type_small}
	{$imageType = $opThemect.general_product_image_type_small}
{/if}	

<div class="axps-products-nav">
    {if isset($prev)}
		<div class="product-btn product-prev">
			<a href="{$prev.url}">
				{l s='Previous product' mod='nrtproductslinknav'}
				<span class="product-btn-icon"></span>
			</a>
			<div class="wrapper-short">
				<div class="product-short">
					<div class="product-short-image">
						<div class="wrapper-imgs">
							{if $prev.default_image}
								{$image = $prev.default_image}
							{else}
								{$image = $urls.no_picture_image}
							{/if}
							{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
						</div>
					</div>
					<div class="product-short-description">
						<a class="product-title" href="{$prev.url}">{$prev.name}</a>
                        {if $prev.show_price}
                            <span class="price">
                                {$prev.price}
                            </span>
                        {/if}
					</div>
				</div>
			</div>
		</div>
    {/if}
	<a href="{$urls.pages.index}" class="axps-back-btn" title="" data-original-title="{l s='Back to home' mod='nrtproductslinknav'}">
		{l s='Back to home' mod='nrtproductslinknav'}
	</a>
    {if isset($next)}
		<div class="product-btn product-next">
			<a href="{$next.url}">
				{l s='Next product' mod='nrtproductslinknav'}
				<span class="product-btn-icon"></span>
			</a>
			<div class="wrapper-short">
				<div class="product-short">
					<div class="product-short-image">
						<div class="wrapper-imgs">
							{if $next.default_image}
								{$image = $next.default_image}
							{else}
								{$image = $urls.no_picture_image}
							{/if}
							{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
						</div>
					</div>
					<div class="product-short-description">
						<a class="product-title" href="{$next.url}">{$next.name}</a>
                        {if $next.show_price}
                            <span class="price">
                                {$next.price}
                            </span>
                        {/if}
					</div>
				</div>
			</div>
		</div>
    {/if}
</div>


