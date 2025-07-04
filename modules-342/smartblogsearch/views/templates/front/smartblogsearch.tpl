<div class="widget wrapper_smart_blog">
	<div class="widget-content">
		<div class="widget-title h3"><span>{l s='Blog Search' mod='smartblogsearch'}</span></div>
		<div class="block_content list-block">
			<form class="std" method="get" action="{smartblog::GetSmartBlogLink('smartblog_search_rule')}">
				<div class="input-group-1">
					<input type="text" class="form-control" value="{if isset($search_query) && $search_query}{$search_query}{/if}" name="search_query" required>
					<button class="btn btn-primary-r">{l s='Ok'  mod='smartblogsearch'}</button>
				</div>
			</form>
		</div>
	</div>
</div>
