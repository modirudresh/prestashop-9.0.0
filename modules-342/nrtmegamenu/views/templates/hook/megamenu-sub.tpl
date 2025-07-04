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

{if $mm.is_mega}
	<div class="menu_sub style_wide sub-menu-dropdown" {if $mm.location == 2}style{else}data-width{/if}="{if $mm.location == 2}width: {/if}{if $mm.width}{$mm.width}{else}800px{/if}">
		{if isset($is_horizontal) && $is_horizontal}<div class="container container-parent">{/if}
			<div class="row m_column_row">
				{assign var='t_width_tpl' value=0}
				{foreach $mm.column as $column}
					{if $column.hide_on_mobile == 2}{continue}{/if}
					{if isset($column.children) && count($column.children)}
						{assign var="t_width_tpl" value=$t_width_tpl+$column.width}
						{if $t_width_tpl>$mm.t_width}
							{assign var="t_width_tpl" value=$column.width}
							</div><div class="row m_column_row">
						{/if}
						<div class="nrt_mega_column_{$column.id_nrt_mega_column} col-md-{($column.width*10/10)|replace:'.':'-'}">
							{foreach $column.children as $block}
								{if $block.hide_on_mobile == 2}{continue}{/if}
								{if $block.item_t==1}
									{if $block.subtype==2  && isset($block.children)}
										<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
											{if $block.show_cate_img}
												{include file="module:nrtmegamenu/views/templates/hook/megamenu-cate-img.tpl" menu_cate=$block.children nofollow=$block.nofollow new_window=$block.new_window}
											{/if}
											<ul class="element_ul_depth_1">
												<li class="element_li_depth_1">
													<a href="{if $block.children.link}{$block.children.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$block.children.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if} class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$block.id_nrt_mega_menu} element_a_depth_1 element_a_item">{$block.children.name}{if $block.cate_label}<span class="cate_label">{$block.cate_label}</span>{/if}</a>
													{if isset($block.children.children) && is_array($block.children.children) && count($block.children.children)}
														<ul class="element_ul_depth_2">
															{foreach $block.children.children as $product}
																<li class="element_li_depth_2"><a href="{$product.link}"{if !$menu_title} title="{$product.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="element_a_depth_2 element_a_item"><i class="las la-angle-right list_arrow hidden"></i>{$product.name}</a></li>
															{/foreach}
														</ul>	
													{/if}
												</li>
											</ul>	
										</div>
									{elseif $block.subtype==0  && isset($block.children.children) && count($block.children.children)}
										<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
											<div class="row">
												{foreach $block.children.children as $menu}
													<div class="col-md-{((12/$block.items_md)*10/10)|replace:'.':'-'}">
														{if $block.show_cate_img}
															{include file="module:nrtmegamenu/views/templates/hook/megamenu-cate-img.tpl" menu_cate=$menu nofollow=$block.nofollow new_window=$block.new_window}
														{/if}
														<ul class="element_ul_depth_1">
															<li class="element_li_depth_1">
																<a href="{if $menu.link}{$menu.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$menu.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="element_a_depth_1 element_a_item">{$menu.name}</a>
																{if isset($menu.children) && is_array($menu.children) && count($menu.children)}
																	{assign var='granditem' value=0}
																	{if isset($block.granditem) && $block.granditem}{$granditem=1}{/if}
																	{include file="module:nrtmegamenu/views/templates/hook/megamenu-category.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$menu.children granditem=$granditem m_level=2}
																{/if}
															</li>
														</ul>	
													</div>
													{if $menu@iteration%$block.items_md==0 && !$menu@last}
														</div><div class="row">
													{/if}
												{/foreach}
											</div>
										</div>
									{elseif $block.subtype==1 || $block.subtype==3}
										<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
											{if $block.show_cate_img}
												{include file="module:nrtmegamenu/views/templates/hook/megamenu-cate-img.tpl" menu_cate=$block.children nofollow=$block.nofollow new_window=$block.new_window}
											{/if}
											<ul class="element_ul_depth_1">
												<li class="element_li_depth_1">
													<a href="{if $block.children.link}{$block.children.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$block.children.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$block.id_nrt_mega_menu} element_a_depth_1 element_a_item">{$block.children.name}{if $block.cate_label}<span class="cate_label">{$block.cate_label}</span>{/if}</a>
													{if $block.subtype==1 && isset($block.children.children) && is_array($block.children.children) && count($block.children.children)}
														{assign var='granditem' value=0}
														{if isset($block.granditem) && $block.granditem}{$granditem=1}{/if}
														{include file="module:nrtmegamenu/views/templates/hook/megamenu-category.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$block.children.children granditem=$granditem m_level=2}
													{/if}
												</li>
											</ul>	
										</div>
									{/if}
								{elseif $block.item_t==2 && isset($block.children) && count($block.children)}
									<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
										<div class="products_on_menu row">
											{$imageType	= 'home_default'}
											{if isset($opThemect.general_product_image_type_large) && $opThemect.general_product_image_type_large}
												{$imageType = $opThemect.general_product_image_type_large}
											{/if}	
											{foreach $block.children as $product}
												<div class="col-md-{((12/$block.items_md)*10/10)|replace:'.':'-'}">
													<a class="menu-product" href="{$product.url}" title="{$product.name}">
														<div class="menu-product-wrapper">
															<div class="wrapper-imgs">
																{if $product.cover}
																	{$image = $product.cover}
																{else}
																	{$image = $urls.no_picture_image}
																{/if}
																{include file='catalog/_partials/miniatures/_image/img-product.tpl'}
															</div>
															<div class="product_name">
																{$product.name}
															</div>   
															<div class="info-product">
																{if $product.show_price}
																	<div class="product-price-and-shipping">
																		{if $product.has_discount}
																			{hook h='displayProductPriceBlock' product=$product type="old_price"}
																			<span class="regular-price">{$product.regular_price}</span>
																		{/if}
																		{hook h='displayProductPriceBlock' product=$product type="before_price"}
																		<span class="price">
																			{capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
																			{if '' !== $smarty.capture.custom_price}
																				{$smarty.capture.custom_price nofilter}
																			{else}
																				{$product.price}
																			{/if}
																		</span>
																		{hook h='displayProductPriceBlock' product=$product type='unit_price'}
																		{hook h='displayProductPriceBlock' product=$product type='weight'}
																	</div>
																{/if}
															</div>	
														</div>
													</a>
												</div>
											{/foreach}
										</div>
									</div>
								{elseif $block.item_t==3 && isset($block.children) && count($block.children)}
									{if isset($block.subtype) && $block.subtype}
										<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
											<div class="row">
												{foreach $block.children as $brand}
													<div class="col-md-{((12/$block.items_md)*10/10)|replace:'.':'-'}">
														<ul class="element_ul_depth_1">
															<li class="element_li_depth_1">
																<a href="{$brand.url}" title="{$brand.name}"{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="advanced_element_a_depth_1 advanced_element_a_item">{$brand.name}</a>
															</li>
														</ul>	
													</div>
													{if $brand@iteration%$block.items_md==0 && !$brand@last}
														</div><div class="row">
													{/if}
												{/foreach}
											</div>
										</div>
									{else}
										<div class="nrt_mega_block_{$block.id_nrt_mega_menu} row">
											{foreach $block.children as $brand}
												<div class="col-md-{((12/$block.items_md)*10/10)|replace:'.':'-'}">
													<a href="{$brand.url}" title="{$brand.name}"{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if} class="nrt_mega_brand">
														<img class="img-responsive" src="{$brand.image}" alt="{$brand.name}" title="{$brand.name}" width="{$manufacturerSize.width}" height="{$manufacturerSize.height}" />
													</a>
												</div>
											{/foreach}
										</div>
									{/if}
								{elseif $block.item_t==4}
									<div class="nrt_mega_block_{$block.id_nrt_mega_menu}">
										<ul class="element_ul_depth_1">
											<li class="element_li_depth_1">
												{$class_has_icon_img = false}
												{if $block.icon_class}{$icon_class_value = $block.icon_class|json_decode:1}{if $icon_class_value.type == 1}{$class_has_icon_img = true}{/if}{/if}
												<a href="{if $block.m_link}{$block.m_link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$block.m_title}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$block.id_nrt_mega_menu} element_a_depth_1 element_a_item{if $class_has_icon_img} has-icon-img{/if}">{if $block.icon_class}{$icon_class_value = $block.icon_class|json_decode:1}{if $icon_class_value.type == 1}<img class="icon-img img-responsive" src="{$icon_class_value.value}" alt=""/>{else}<i class="{$icon_class_value.value}"></i>{/if}{/if}{$block.m_name}{if $block.cate_label}<span class="cate_label">{$block.cate_label}</span>{/if}</a>
												{if isset($block.children) && is_array($block.children) && count($block.children)}
													<ul class="element_ul_depth_2">
														{foreach $block.children as $menu}
															{if $menu.hide_on_mobile == 2}{continue}{/if}
															{include file="module:nrtmegamenu/views/templates/hook/megamenu-link.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$menu m_level=2}
														{/foreach}
													</ul>
												{/if}
											</li>
										</ul>	
									</div>
								{elseif $block.item_t==5 && $block.html}
									<div class="nrt_mega_block_{$block.id_nrt_mega_menu} style_content">
										{$block.html nofilter}
									</div>
								{/if}
							{/foreach}
						</div>
					{/if}
				{/foreach}
			</div>
		{if isset($is_horizontal) && $is_horizontal}</div>{/if}
	</div>
{else}
	<ul class="nrt_mega_multi_level_{$mm.id_nrt_mega_menu} menu_sub nrtmenu_multi_level" style="width: {if $mm.width}{$mm.width}{else}170px{/if}">
		{strip}
			{foreach $mm.column as $column}
				{if $column.hide_on_mobile == 2}{continue}{/if}
				{if isset($column.children) && count($column.children)}
					{foreach $column.children as $block}
						{if $block.hide_on_mobile == 2}{continue}{/if}
						{if $block.item_t==1}
							{if $block.subtype==2  && isset($block.children) && count($block.children)}
								{if isset($block.children.children) && is_array($block.children.children) && count($block.children.children)}
									{foreach $block.children.children as $product}
										<li class="element_li_depth_1"><a href="{$product.link}"{if !$menu_title} title="{$product.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="element_a_depth_1 element_a_item"><i class="las la-angle-right list_arrow hidden"></i>{$product.name}</a></li>
									{/foreach}
								{/if}
							{elseif $block.subtype==0  && isset($block.children.children) && count($block.children.children)}
								{foreach $block.children.children as $menu} 
									<li class="element_li_depth_1">
										{assign var='has_children' value=(isset($menu.children) && is_array($menu.children) && count($menu.children))}
										<div class="menu_a_wrap">
											<a href="{if $menu.link}{$menu.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$menu.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="element_a_depth_1 element_a_item {if $has_children} has_children {/if}"><i class="las la-angle-right list_arrow hidden"></i>{$menu.name}{if $has_children}<span class="is_parent_icon"><b class="is_parent_icon_h"></b><b class="is_parent_icon_v"></b></span>{/if}</a>
										</div>
										{if $has_children}
											{include file="module:nrtmegamenu/views/templates/hook/megamenu-category.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$menu.children m_level=2}
										{/if}
									</li>
								{/foreach}
							{elseif $block.subtype==1 || $block.subtype==3}
								<li class="element_li_depth_1">
									{assign var='has_children' value=(isset($block.children.children) && count($block.children.children))}
									<div class="menu_a_wrap">
										<a href="{if $block.children.link}{$block.children.link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$block.children.name}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$block.id_nrt_mega_menu} element_a_depth_1 element_a_item {if $has_children} has_children {/if}"><i class="las la-angle-right list_arrow hidden"></i>{$block.children.name}{if $has_children}<span class="is_parent_icon"><b class="is_parent_icon_h"></b><b class="is_parent_icon_v"></b></span>{/if}{if $block.cate_label}<span class="cate_label">{$block.cate_label}</span>{/if}</a>
									</div>
									{if $has_children}
										{include file="module:nrtmegamenu/views/templates/hook/megamenu-category.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$block.children.children m_level=2}
									{/if}
								</li>
							{/if}
						{elseif $block.item_t==4}
							<li class="element_li_depth_1">
								{assign var='has_children' value=(isset($block.children) && is_array($block.children) && count($block.children))}
								<div class="menu_a_wrap">
									<a href="{if $block.m_link}{$block.m_link}{else}javascript:void(0){/if}"{if !$menu_title} title="{$block.m_title}"{/if}{if $block.nofollow} rel="nofollow"{/if}{if $block.new_window} target="_blank"{/if}  class="{if isset($ismobilemenu)}mo_{elseif isset($iscolumnmenu)}col_{else}style_{/if}element_a_{$block.id_nrt_mega_menu} element_a_depth_1 element_a_item {if $has_children} has_children{/if}">{if $block.icon_class}{$icon_class_value = $block.icon_class|json_decode:1}{if $icon_class_value.type == 1}<img class="icon-img img-responsive" src="{$icon_class_value.value}" alt=""/>{else}<i class="{$icon_class_value.value}"></i>{/if}{else}<i class="las la-angle-right list_arrow hidden"></i>{/if}{$block.m_name}{if $has_children}<span class="is_parent_icon"><b class="is_parent_icon_h"></b><b class="is_parent_icon_v"></b></span>{/if}{if $block.cate_label}<span class="cate_label">{$block.cate_label}</span>{/if}</a>
								</div>
								{if $has_children}
									<ul class="element_ul_depth_2">
										{foreach $block.children as $menu}
											{if $menu.hide_on_mobile == 2}{continue}{/if}
											{include file="module:nrtmegamenu/views/templates/hook/megamenu-link.tpl" nofollow=$block.nofollow new_window=$block.new_window menus=$menu m_level=2}
										{/foreach}
									</ul>	
								{/if}
							</li>
						{elseif $block.item_t==5 && $block.html}
							<li class="element_li_depth_1">
								<div class="nrt_mega_block_{$block.id_nrt_mega_menu} style_content">
									{$block.html nofilter}
								</div>
							</li>
						{else}
							
						{/if}
					{/foreach}
				{/if}
			{/foreach}
		{/strip}
	</ul>
{/if}