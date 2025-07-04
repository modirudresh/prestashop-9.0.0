<div class="search-widget search-wrapper">
	<form class="search-form has-ajax-search" method="get" action="{$search_controller_url}">
		<div class="wrapper-form">
			<input type="hidden" name="controller" value="search">
			<input type="text" class="query" placeholder="{l s='Enter your keyword ...' mod='nrtsearchbar'}" value="" name="s" required />
			<button type="submit" class="search-submit">
				{l s='Search' mod='nrtsearchbar'}
			</button>
		</div>
	</form>
	<div class="search-results-wrapper"><div class="wrapper-scroll"><div class="search-results wrapper-scroll-content"></div></div></div>
</div>