{if $product}
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
			<span class='qtt-ajax'>{$product.quantity}</span>
		</a>
		<span class="ax-n-info">
			{l s='Product successfully added to your shopping cart' d='Shop.Theme.Checkout'}
		</span>
		<div class='group_button'>
			<a href='{$cart_url}' title='{l s="View cart" mod="nrtshoppingcart"}'>
				{l s='View cart' mod='nrtshoppingcart'}
			</a>
			<a href='{$urls.pages.order}' title='{l s="Checkout" mod="nrtshoppingcart"}'>
				{l s='Checkout' mod='nrtshoppingcart'}
			</a>
		</div>
	</div>
{else}
	<div class="ax-n-wrapper">
		<span class="ax-n-error">
			{l s='There are not enough products in stock. You cannot proceed with your order until the quantity is adjusted.' mod='nrtshoppingcart'}
		</span>
		<a href='{$cart_url}' class='goto_page' title='{l s="View cart" mod="nrtshoppingcart"}'>
			{l s='View cart' mod='nrtshoppingcart'}
		</a>
		<a href='{$urls.pages.order}' class='goto_page' title='{l s="Checkout" mod="nrtshoppingcart"}'>
			{l s='Checkout' mod='nrtshoppingcart'}
		</a>
	</div>
{/if}