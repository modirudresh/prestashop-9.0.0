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

{if is_array($menus) && count($menus)}
	{if !isset($granditem)}{assign var='granditem' value=0}{/if}
	<ul class="{if isset($ismobilemenu)}mo_sub_ul mo_{elseif isset($iscolumnmenu)}col_sub_ul col_{/if}element_ul_depth_{$m_level} p_granditem_{if $m_level>2}{$granditem}{else}1{/if} {if $mm.is_mega && $m_level == 2 && $block.subtype==1}row{/if}">
	{foreach $menus as $menu}
		{assign var='has_children' value=(isset($menu.children) && is_array($menu.children) && count($menu.children))}
		<li class="{if isset($ismobilemenu)}mo_sub_li mo_{elseif isset($iscolumnmenu)}col_sub_li col_{/if}element_li_depth_{$m_level} granditem_{$granditem} p_granditem_{if $m_level>2}{$granditem}{else}1{/if} {if $mm.is_mega && $m_level == 2 && $block.subtype==1}col-lg-{((12/$block.items_md)*10/10)|replace:'.':'-'}{/if}">
        	<div class="menu_a_wrap">
                <a href="{if $menu.link}{$menu.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$menu.name}"{/if}{if $nofollow} rel="nofollow"{/if}{if $new_window} target="_blank"{/if} class="{if isset($ismobilemenu)}mo_sub_a mo_{elseif isset($iscolumnmenu)}col_sub_a col_{/if}element_a_depth_{$m_level} element_a_item {if $has_children} has_children {/if}"><i class="las la-angle-right list_arrow hidden"></i>{$menu.name}{if $has_children && !isset($ismobilemenu) && !isset($iscolumnmenu) && (!isset($granditem) || !$granditem)}<span class="is_parent_icon"><b class="is_parent_icon_h"></b><b class="is_parent_icon_v"></b></span>{/if}</a>
                {if $has_children && (isset($ismobilemenu) || isset($iscolumnmenu))}<span class="icon-opener js-opener-menu"></span>{/if}
        	</div>   
		{if $has_children}
			{include file="module:nrtmegamenu/views/templates/hook/megamenu-category.tpl" menus=$menu.children granditem=$granditem m_level=($m_level+1)}
		{/if}
		</li>
	{/foreach}
	</ul>
{/if}