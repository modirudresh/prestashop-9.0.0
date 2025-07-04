{if isset($productvideos)}
	{foreach from=$productvideos item=productvideo}
		<div class="btn-additional">
			<a class="btn-additional-video js-video-viewer" href="{$productvideo.url}" rel="nofollow">
				<span>{l s='Watch video' mod='nrtproductvideo'}</span>
			</a>
		</div>
	{/foreach}
{/if}