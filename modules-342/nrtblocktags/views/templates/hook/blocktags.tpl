{if isset($tags) && $tags}
<div class="widget widget_tag_cloud">
	<div class="widget-content">
		<div class="widget-title h3"><span>{l s='Popular tags' mod='nrtblocktags'}</span></div>
		<div class="tagcloud">
		   {foreach from=$tags item="tag"}
				<a href="{url entity='search' params=['tag' => $tag.name|escape]}" title="{$tag.name|escape:html:'UTF-8'}">
					{$tag.name|escape:html:'UTF-8'}
				</a>
		   {/foreach}
		</div>
	</div>
</div>
{/if}