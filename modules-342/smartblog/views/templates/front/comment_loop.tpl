{if count($comments) > 0}
    <ul class="{if isset($is_child) && $is_child}children{else}comment-list{/if}">
		{foreach from=$comments item=comment}
			{if $comment.id_smart_blog_comment != ''}
				<li id="comment-{$comment.id_smart_blog_comment|intval}" class="comment">
					<div id="div-comment-{$comment.id_smart_blog_comment|intval}" class="comment-body">
						<div class="comment-meta">
							<div class="comment-author">
								<img alt="" src="{$modules_dir}smartblog/images/avatar/avatar_author_default.jpg" class="avatar" width="74" height="74">
								<span class="author-name">{$comment.name}</span>
								<span class="says">{l s='says:' mod='smartblog'}</span>		
							</div>
							<div class="comment-metadata">
								<span itemprop="commentTime">{$comment.created|date_format}</span>
							</div>
						</div>
						<p>{$comment.content nofilter}</p>
						{if Configuration::get('smartenablecomment')}
							{if $post.comment_status}
								<div class="comment-reply">
									<a rel="nofollow" class="comment-reply-link" href="javascript:void(0)" onclick="return addComment.moveForm('div-comment-{$comment.id_smart_blog_comment}', '{$comment.id_smart_blog_comment}', 'respond', '{$comment.id_post|intval}')">{l s='Reply' mod='smartblog'}</a>					
								</div>
							{/if}
						{/if}
					</div>	
					{if isset($comment.child_comments)}
						{include file="module:smartblog/views/templates/front/comment_loop.tpl" comments=$comment.child_comments is_child=true}
					{/if}
				</li>
			{/if}
		{/foreach}
    </ul>
{/if}
                                        
                                        