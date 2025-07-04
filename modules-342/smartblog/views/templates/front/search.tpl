{extends file='page.tpl'}

{block name="left_content"}
    {hook h="displaySmartBlogLeft"}
{/block} 

{block name="right_content"}
    {hook h="displaySmartBlogRight"}
{/block}

{$title_page = {l s='Search Results for:' mod='smartblog'}}

{block name='page_header_title'}
	{$title_page} {$search_query}
{/block}

{block name='page_content'}
    {if !count($posts)}
		<div class="pagenotfound">
			<h3>{l s='Sorry, but nothing matched your search terms.' mod='smartblog'}</h3>
			<p>
				{l s='Please try again with some different keywords.' mod='smartblog'}
			</p>
			<form class="std" method="get" action="{smartblog::GetSmartBlogLink('smartblog_search_rule')}">
				<fieldset>
				  <div class="input-group-1">
					<input class="form-control grey" value="{$search_query}" name="search_query" id="search_query" type="text" required>
					<button class="btn btn-primary-r"><span>{l s='Ok' mod='smartblog'}</span></button>
				</div>
				</fieldset>
			</form>
			<br/>
			<p>
			  <a href="{smartblog::GetSmartBlogLink('smartblog')}" class="btn btn-primary-r">
				<i class="las la-reply"></i>
				<span>{l s='Blog page' mod='smartblog'}</span>
			  </a>
			</p>
			<br/>
		</div>                                
    {else}
        <div class="smartblogcat clearfix">
            {include file="module:smartblog/views/templates/front/blog_loop.tpl" posts=$posts}
        </div>
		{$options.search_query = $search_query}
		{$rule = 'smartblog_search_rule'}
		{include file='module:smartblog/views/templates/front/pagination.tpl'}
    {/if}
{/block}