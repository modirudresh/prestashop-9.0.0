{if !empty($pagenums)}
	<div class="archive-bottom">
		<nav class="pagination">
			{if $c > 1}
				{if ($c-1) > 1}
					{$options.page = $c-1}
				{/if}
				<a class="page-numbers prev" href="{smartblog::GetSmartBlogLink($rule, $options)}" rel="prev">
					« {l s='Previous' d='Shop.Theme.Actions'}
				</a>
			{/if}
			{for $k=0 to $pagenums}
				{if ($k+1) == $c}
					<span class="page-numbers current">{$k+1}</span>
				{else}
					{$options=$options|array_diff_key:(['page']|array_flip)}
					{if ($k+1) > 1}
						{$options.page = $k+1}
					{/if}
					<a class="page-numbers" href="{smartblog::GetSmartBlogLink($rule, $options)}" rel="nofollow">{$k+1}</a>
				{/if}
			{/for}
			{if $c < ($pagenums+1)}		   
				{$options.page = $c+1}				   
				<a class="page-numbers next" href="{smartblog::GetSmartBlogLink($rule, $options)}" rel="next">
					{l s='Next' d='Shop.Theme.Actions'} »
				</a>
			{/if}	
		</nav>
	</div>
{/if}

