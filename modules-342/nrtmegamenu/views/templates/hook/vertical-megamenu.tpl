{*
*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*
*}

{if isset($nrtvertical) && count($nrtvertical)}
	<div class="wrapper-menu-vertical">		
		<div class="menu-vertical-title">
			<i class="la la-bars"></i>											
			<span>{l s='All categories' mod='nrtmegamenu'}</span>
		</div>
		{include file="module:nrtmegamenu/views/templates/hook/vertical-megamenu-ul.tpl"}
	</div>
{/if}
