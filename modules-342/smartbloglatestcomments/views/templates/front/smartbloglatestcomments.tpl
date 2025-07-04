{if isset($latesComments) AND !empty($latesComments)}
<div class="widget wrapper_smart_blog widget_recent_comments">
	<div class="widget-content">
		<div class="widget-title h3"><span>{l s='Latest Comments' mod='smartbloglatestcomments'}</span></div>
		<div class="block_content">
			 <ul>
				{foreach from=$latesComments item="comment"}
					 <li>
						{$comment.name}&nbsp;<i>{l s='on' mod='smartbloglatestcomments'}&nbsp;</i>
						<a class="title" href="{SmartBlogLink::getSmartBlogPostLink($comment.id_post,$comment.link_rewrite)}#comment-{$comment.id_smart_blog_comment}">{SmartBlogPost::subStr($comment.content, 30) nofilter}</a>
					 </li>
				{/foreach}
			</ul>
		</div>
	</div>
</div>
{/if}