<div id="search-popup" class="modal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog search-wrapper popup-wrapper" role="document">
	<div class="modal-content">
		<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
			<span aria-hidden="true">&times;</span>
		</button>
		<div class="modal-body">
			<h2>{l s='Search for products' mod='nrtsearchbar'}</h2>
			<p>{l s='Start typing to see products you are looking for.' mod='nrtsearchbar'}</p>
			<hr/>
			<form class="search-form has-ajax-search" method="get" action="{$search_controller_url}">
				<input type="hidden" name="controller" value="search">
				<input type="text" class="query form-control" placeholder="{l s='Enter your keyword ...' mod='nrtsearchbar'}" value="" name="s" required />
				<button type="submit" class="search-submit">
					{l s='Search' mod='nrtsearchbar'}
				</button>
			</form>
			<div class="search-results"></div>
		</div>
	</div>
</div></div>
