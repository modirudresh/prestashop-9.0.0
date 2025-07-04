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

{if isset($nrtmenu) && is_array($nrtmenu) && count($nrtmenu)}
	<div class="wrapper-menu-horizontal">
		{include file="module:nrtmegamenu/views/templates/hook/horizontal-megamenu-ul.tpl"}
	</div>
{/if}