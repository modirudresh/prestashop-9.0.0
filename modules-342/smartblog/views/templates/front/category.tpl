{extends file='page.tpl'}

{block name="left_content"}
    {hook h="displaySmartBlogLeft"}
{/block} 

{block name="right_content"}
    {hook h="displaySmartBlogRight"}
{/block}

{block name='page_header_title'}
	{$category.name}
	{if $category.description}
		<div class="category-description">{$category.description nofilter}</div>
	{/if}
{/block} 

{block name='page_content'}
    {if !count($posts)}
    	<p class="alert alert-warning">{l s='No Post in Category' mod='smartblog'}</p>
    {else}
		{if $smartdisablecatimg}
			<div class="sdsblogCategory">  
				{if isset($category.img)}
					<img alt="{$category.meta_title}" src="{$category.img}" class="img-responsive">
				{/if}
			</div>
		{/if}
		<div class="smartblogcat clearfix">
			{include file='module:smartblog/views/templates/front/blog_loop.tpl' posts=$posts}
		</div>
		{$options.id_category = $category.id}
		{$options.rewrite = $category.link_rewrite}
		{$rule = 'smartblog_category_rule'}
		{include file='module:smartblog/views/templates/front/pagination.tpl'}
	{/if}
{/block}