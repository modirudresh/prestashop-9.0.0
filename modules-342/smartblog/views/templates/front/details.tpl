{extends file='page.tpl'}

{$image = SmartBlogLink::getImageLink($post.link_rewrite, $post.id_post, $post_images.type)}

{block name='head' append}
  <meta property="og:type" content="article">
  {if $image}
    <meta property="og:image" content="{$image}">
  {/if}
{/block}

{block name="left_content"}
    {hook h="displaySmartBlogLeft"}
{/block} 

{block name="right_content"}
    {hook h="displaySmartBlogRight"}
{/block}

{$title_page = {l s='Blog' mod='smartblog'}}

{block name='wrapper_page_header_title'}
	<h3 class="h1">
		{$title_page}
	</h3>
{/block}

{block name='page_content'}
<div class="blog-single" itemscope="itemscope" itemtype="http://schema.org/Blog">
	<div class="blog-single-inner" itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost">
		
		<h1 class="entry-title" itemprop="headline">{$post.meta_title}</h1>
		
		<ul class="entry-info">
			{if $smartshowauthor ==1}
				<li class="post-author">
					<span itemprop="author">
						{if $smartshowauthorstyle != 0}
							{$post.firstname}&nbsp;{$post.lastname}
						{else} 
							{$post.lastname}&nbsp;{$post.firstname}
						{/if}
					</span>
				</li>
			{/if}
			
			<li class="post-date">
				<span itemprop="datePublished">
					{$post.created}
				</span>
			</li>
			
			{$assocCats = BlogCategory::getPostCategoriesFull($post.id_post)}
			{$catCounts = 0}
			{if !empty($assocCats)}
				<li class="post-cat">
					{foreach $assocCats as $catid=>$assoCat}
						{if $catCounts > 0}<span>, </span>{/if}
						{$catlink=[]}
						{$catlink.id_category = $assoCat.id_category}
						{$catlink.rewrite = $assoCat.link_rewrite}
						<a title="{$assoCat.name}" href="{SmartBlogLink::getSmartBlogCategoryLink($assoCat.id_category, $assoCat.link_rewrite)}">{$assoCat.name}</a>
						{$catCounts = $catCounts + 1}
					{/foreach}
				</li>
			{/if}
			{if Configuration::get('smartenablecomment')}
                <li class="post-comment-link">
                    <a href="{SmartBlogLink::getSmartBlogPostLink($post.id_post, $post.link_rewrite)}#comments">
                        {if $countcomment != ''}
                            {$countcomment}
                        {else}
                            0
                        {/if}
                        {l s='Comments' mod='smartblog'}
                    </a>
                </li>
			{/if}
		</ul>
		
		{if $image != 'false'}
			<div class="entry-thumbnail">
				<div class="wrapper-imgs">
					{$post.title = $post.meta_title}
					{$post.image.url = $image}
					{$post.image.type = "blog_post_{$post_images.type}"}
					{$post.image.width = $post_images.width}
					{$post.image.height = $post_images.height}

					{include file='catalog/_partials/miniatures/_image/img-blog.tpl'}
				</div>
			</div>	
			<meta itemprop="image" content="{$image}">
		{/if}

		<meta itemprop="url" content="{SmartBlogLink::getSmartBlogPostLink($post.id_post, $post.link_rewrite)}">  

		<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
			 <span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
				<meta itemprop="url" content="{$link->getMediaLink($shop.logo)}">
				<meta itemprop="width" content="{Configuration::get('SHOP_LOGO_WIDTH')}">
				<meta itemprop="height" content="{Configuration::get('SHOP_LOGO_HEIGHT')}">
			 </span>
			<meta itemprop="name" content="{$shop.name}">
		</span>

		<meta itemprop="datePublished" content="{$post.created}">
		<meta itemprop="dateModified" content="{$post.modified}">
		<meta itemprop="mainEntityOfPage" content="{$urls.base_url}">

		<div class="entry-content">
		   {$post.content nofilter}
		</div>
		
		{hook h='displaySmartAfterPost'}
		
		<div class="single-footer">
			{if $tags != ''}
				<div class="entry-tags">
					{foreach from=$tags item=tag}
						{$param = []}
						{$param.tag = $tag.name}
						<a title="{$tag.name}" href="{smartblog::GetSmartBlogLink('smartblog_tag_rule', $param)}">
							{$tag.name}
						</a>
					{/foreach}	
				</div>
			{/if}
			{hook h='displayBlogShareButtons' link=SmartBlogLink::getSmartBlogPostLink($post.id_post, $post.link_rewrite) img=$image title=$post.meta_title}
		</div>
		
		<nav class="single-navigation" role="navigation">
			<div class="nav-btn prev-btn">
				{foreach from=$posts_previous item="post"}
					{if isset($post.id_smart_blog_post)}
						<div class="nav-btn-inner">
							<a class="previous-post" href="{SmartBlogLink::getSmartBlogPostLink($post.id_smart_blog_post, $post.link_rewrite)}">
								<i class="las la-chevron-circle-left"></i>
								<span class="btn-label">{l s='Newer' mod='smartblog'}</span>
								<span class="title-post">{$post.meta_title}</span>
							</a>
						</div>	
					{/if}
				{/foreach}
			</div>
			<div class="back-btn">
				<a class="back-blog" href="{smartblog::GetSmartBlogLink('smartblog')}" title="{l s='Back to list' mod='smartblog'}">
					<i class="las la-th-large"></i>
				</a>
			</div>
			<div class="nav-btn next-btn">
				{foreach from=$posts_next item="post"}
					{if isset($post.id_smart_blog_post)}
						<div class="nav-btn-inner">
							<a class="next-post" href="{SmartBlogLink::getSmartBlogPostLink($post.id_smart_blog_post, $post.link_rewrite)}">
								<span class="btn-label">{l s='Older' mod='smartblog'}</span>
								<span class="title-post">{$post.meta_title}</span>
								<i class="las la-chevron-circle-right"></i>
							</a>
						</div>
					{/if}
				{/foreach}
			</div>
		</nav>

	</div>
</div>      

{if Configuration::get('smartenablecomment')}
    <div id="comments">
        {if $countcomment != ''}
            <h3 class="comment-reply-title">{if $countcomment != ''}{$countcomment}{else}0{/if}&nbsp;{l s='Comments' mod='smartblog'}<span></span></h3>
            {include file="module:smartblog/views/templates/front/comment_loop.tpl" comments=$comments}
        {/if}
    </div>
	{if !$enableguestcomment && !$is_logged}
		<p class="alert alert-warning">
			{l s='You must be logged in to post comments' mod='smartblog'}
		</p>
	{else}
		{if $post.comment_status}
			<div class="comment-respond" id="respond">
				<h4 class="comment-reply-title" id="reply-title">
				{l s='Leave a Reply' mod='smartblog'}
					<span style="display: none;" id="cancel-comment-reply-link">{l s='Cancel Reply' mod='smartblog'}</span>
				</h4>
				<form action="#" method="post" id="commentform" class="row">
					{if $is_logged}
						<input type="hidden" class="form-control" value="{$is_logged_name}" name="name" required>
						<input type="hidden" class="form-control" value="{$is_logged_email}" name="mail" required>
						<input type="hidden" value="" name="website" class="form-control">
					{else}
						<div class="form-group col-xs-12 col-sm-12 col-md-6">
							<label class="required">{l s='Name' mod='smartblog'}</label>
							<input type="text" tabindex="1" class="form-control" value="" name="name" required>
						</div>
						<div class="form-group col-xs-12 col-sm-12 col-md-6">
							<label class="required">{l s='E-mail' mod='smartblog'}</label>
							<input type="text" tabindex="2" class="form-control" value="" name="mail" required>
						</div>
						<div class="form-group col-xs-12 col-md-4 hidden">
							<label>{l s='Website' mod='smartblog'}</label>
							<input type="text" value="" name="website" class="form-control" placeholder="{l s='Site url with http://' mod='smartblog'}">
						</div>
					{/if}
					<div class="form-group col-xs-12">
						<label class="required">{l s='Comment' mod='smartblog'}</label>
						<textarea class="form-control" rows="10" name="comment" required></textarea>
					</div>
                    {if isset($id_module)}
                        <div class="form-group col-xs-12">
                            {hook h='displayNrtCaptcha' id_module=$id_module}
                        </div>
                    {/if}
                    {if isset($id_module)}
                        <div class="form-group col-xs-12">
                            {hook h='displayGDPRConsent' id_module=$id_module}
                        </div>
                    {/if}
					<input type='hidden' name='comment_post_ID' value='1478' id='comment_post_ID' />
					<input type='hidden' name='id_post' value='{$post.id_post}' id='id_post' />
					<input type='hidden' name='comment_parent' id='comment_parent' value='0'/>
					<div class="col-xs-12">
						<div id="new_comment_blog_error" class="alert alert-danger" style="display:none;">
							<ul></ul>
						</div>
					</div>
					<div class="submit col-xs-12">
						<button type="submit" name="addComment" id="submitComment" class="btn btn-primary-r">
							{l s='Submit' mod='smartblog'}
						</button>
					</div>
				</form>
			</div>
		{/if}
	{/if}
{/if}
{/block}