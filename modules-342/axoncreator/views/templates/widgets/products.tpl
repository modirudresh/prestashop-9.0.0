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
	 
{if $content.products|@count > 0} 
	{if $content.image_size} 
		{$imageType	= $content.image_size}
	{else}
		{$imageType	= 'home_default'}
	{/if}

	{$i = 0}

	{if isset($content.per_col) && $content.per_col}
		{$y = $content.per_col}
	{else}
		{$y = 1}
	{/if}

	{foreach from=$content.products item="product"}
		{if $i mod $y eq 0}
		<div class="swiper-slide item">
		{/if}
			{include file={$content.items_type_path}}
		{$i = $i+1}	
		{if $i mod $y eq 0 || $i eq count($content.products)}
		</div>
		{/if}
	{/foreach}
{else}
    <div class="swiper-slide-alert swiper-slide item">
        <div class="item-inner">
            <p class="alert alert-info clearfix">
                {l s='No products at this time.' mod='axoncreator'}
            </p>
        </div>
    </div>
{/if} 

