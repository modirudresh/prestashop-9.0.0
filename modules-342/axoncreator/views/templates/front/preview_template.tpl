{**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 **}

{extends file='layouts/layout-full-width.tpl'}

{block name='header'}{/block}
{block name='breadcrumb'}{/block}
{block name="footer"}{/block}
	 
{block name='block_full_width'}
	<div id="content-wrapper">
		<div id="main-content">
			{hook h="displayContentWrapperTop"}
			{block name="content"}
				{include file="module:axoncreator/views/templates/hook/page_content.tpl"}
			{/block}
			{hook h="displayContentWrapperBottom"}
		</div>
	</div>
{/block}
