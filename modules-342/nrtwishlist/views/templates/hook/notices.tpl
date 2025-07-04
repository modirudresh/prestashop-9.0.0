{if isset($product)}
	{$imageType	= 'cart_default'}

	{if isset($opThemect.general_product_image_type_small) && $opThemect.general_product_image_type_small}
		{$imageType = $opThemect.general_product_image_type_small}
	{/if}	
	<div class="ax-n-wrapper">
		<h4><span>{$product.name}</span></h4>
		<a class="ax-imgs" href='{$product.url}' title='{$product.name}'>
			<div class="wrapper-imgs">
				{if $product.default_image}
					{$image = $product.default_image}
				{else}
					{$image = $urls.no_picture_image}
				{/if}
				{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
			</div>
		</a>
		<span class="ax-n-info">
			{l s='Product successfully added to your wishlist' mod='nrtwishlist'}
		</span>
		<div class='group_button'>
			<a href='{url entity='module' name='nrtwishlist' controller='view'}' title='{l s="Go to Wishlist" mod="nrtwishlist"}'>
				{l s='Go to Wishlist' mod='nrtwishlist'}
			</a>
		</div>
	</div>
{else}
	<div class="ax-n-wrapper">
		<span class="ax-n-error">
			{l s='Product not found.' mod='nrtwishlist'}
		</span>
		<a href='{url entity='module' name='nrtwishlist' controller='view'}' class='goto_page' title='{l s="Go to Wishlist" mod="nrtwishlist"}'>
			{l s='Go to Wishlist' mod='nrtwishlist'}
		</a>
	</div>
{/if}