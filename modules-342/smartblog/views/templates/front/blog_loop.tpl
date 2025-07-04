{$blogLayout = 1}

{if isset($blog_category_post_layout)}
	{$blogLayout = $blog_category_post_layout}
{/if}

<div id="box-blog-grid" class="blogs blog-type-{$blogLayout}">
	<div class="archive-wrapper-items wrapper-items">
		{foreach from=$posts item=post}
			<div class="item">
				{include file="catalog/_partials/miniatures/_partials/_blog/blog-{$blogLayout}.tpl"}
			</div>
		{/foreach}
	</div>
</div>