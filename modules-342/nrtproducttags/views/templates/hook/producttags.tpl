{if isset($tags)}
    <div class="product-tags">
    	<span class="label">{l s="Tags"  mod='nrtproducttags'}: </span>
        {foreach from=$tags item=tag key=i}
            <a href="{url entity='search' params=['tag' => $tag|escape]}">{$tag}</a>
        {/foreach}
    </div>
{/if}
