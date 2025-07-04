{if isset($customtabs) }
	{foreach from=$customtabs item=customtab key=customtabKey}
        <div class="tab-pane" id="custom-tab-{$customtabKey}">
        	<div class="product-description">
            	{$customtab->description nofilter}
            </div>
        </div>
    {/foreach}
{else}  
<div class="tab-pane" id="custom-tab-global">
	<div class="product-description">
		{$content_global|stripslashes nofilter}
    </div>
</div>
{/if}