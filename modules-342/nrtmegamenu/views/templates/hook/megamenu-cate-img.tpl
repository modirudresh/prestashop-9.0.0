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

{if $menu_cate.cat_image_url}
	<a href="{$menu_cate.link}"{if !$menu_title} title="{$menu_cate.name}"{/if} class="menu_cate_img"{if $nofollow} rel="nofollow"{/if}{if $new_window} target="_blank"{/if}>
		<img class="img-responsive" src="{$menu_cate.cat_image_url}" alt="{$menu_cate.name}" title="{$menu_cate.name}"/>
	</a>
{/if}