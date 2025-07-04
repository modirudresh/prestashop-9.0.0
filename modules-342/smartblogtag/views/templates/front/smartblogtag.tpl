{if isset($tags) AND !empty($tags)}
<div class="widget widget_tag_cloud">
	<div class="widget-content">
		<div class="widget-title h3"><span>{l s='Tags Post' mod='smartblogtag'}</span></div>
		<div class="tagcloud">
		   {foreach from=$tags item="tag"}
				<a title="{$tag.name}" href="{smartblog::GetSmartBlogLink('smartblog_tag_rule')}?tag={$tag.name|escape}">
					{$tag.name}
				</a>
		   {/foreach}
		</div>
	</div>
</div>
{/if}