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
	{assign var='granditem' value=0}
	{if isset($menu.granditem) && $menu.granditem}{$granditem=1}{/if}
	<li class="{if isset($ismobilemenu)}mo_sub_li mo_{elseif isset($iscolumnmenu)}col_sub_li col_{/if}element_li_depth_{$m_level} granditem_{$granditem} p_granditem_{if isset($p_granditem)}{$p_granditem}{else}1{/if}">
	{if $menus.item_t==5}
		<div class="nrt_mega_block_{$menus.id_nrt_mega_menu nofilter} style_content">
			{$menus.html nofilter}
		</div>
	{else}
		{assign var='has_children' value=(isset($menu.children) && is_array($menu.children) && count($menu.children))}
		<div class="menu_a_wrap">
		<a href="{if $menus.m_link}{$menus.m_link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$menus.m_title}"{/if}{if $menus.nofollow} rel="nofollow"{/if}{if $menus.new_window} target="_blank"{/if} class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$menus.id_nrt_mega_menu} {if isset($ismobilemenu)}mo_sub_a mo_{elseif isset($iscolumnmenu)}col_sub_a col_{/if}element_a_depth_{$m_level} element_a_item {if $has_children} has_children {/if}">{if $menus.icon_class}{$icon_class_value = $menus.icon_class|json_decode:1}{if $icon_class_value.type == 1}<img class="icon-img img-responsive" src="{$icon_class_value.value}" alt=""/>{else}<i class="{$icon_class_value.value}"></i>{/if}{else}<i class="las la-angle-right list_arrow hidden"></i>{/if}{$menus.m_name}{if $has_children && !isset($ismobilemenu) && !isset($iscolumnmenu)  && (!isset($granditem) || !$granditem)}<span class="is_parent_icon"><b class="is_parent_icon_h"></b><b class="is_parent_icon_v"></b></span>{/if}{if $menus.cate_label}<span class="cate_label">{$menus.cate_label}</span>{/if}</a>
		{if $has_children && (isset($ismobilemenu) || isset($iscolumnmenu))}<span class="icon-opener js-opener-menu"></span>{/if}
		</div>
		{if $has_children}
			<ul class="{if isset($ismobilemenu)}mo_sub_ul mo_{elseif isset($iscolumnmenu)}col_sub_ul col_{/if}element_ul_depth_{$m_level+1} p_granditem_{$granditem}">
			{foreach $menus.children as $menu}
				{if isset($ismobilemenu) && $menu.hide_on_mobile == 1}{continue}{/if}
				{include file="module:nrtmegamenu/views/templates/hook/megamenu-link.tpl" menus=$menu m_level=($m_level+1) p_granditem=$granditem}
			{/foreach}
			</ul>
		{/if}
	{/if}
	</li>
{/if}