{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if $isDropdown}
	<div class="widget block-categories">
		<div class="widget-content">
			<div class="widget-title h3"><span>{l s='Blog Category' mod='smartblogcategories'}</span></div>
			<div class="block_content">
				<select onchange="document.location.href=this.options[this.selectedIndex].value;">
					<option value="">{l s='Select Category' mod='smartblogcategories'}</option>
					{include file="module:smartblogcategories/category-tree-branch.tpl" node=$blockCategTree last='true' select='false'}
				</select>
			</div>
		</div>
	</div>
{else}
	{function name="blockCategTree" nodes=[] depth=0}
	  {strip}
	    {if $nodes|count}
	      <ul>
	        {foreach from=$nodes item=node}
	        	{if $node.name != ''}
		          <li data-depth="{$depth}">
		            {if $depth===0}
		              <a href="{$node.link}" title="{$node.name}">{$node.name}</a>
		              {if $node.children}
		              	
			              	{if $isDhtml}
								<span class="navbar-toggler collapse-icons" data-target="#exBlogCollapsingNavbar{$node.id}" data-toggle="collapse">
								  <i class="las la-plus add"></i>
								  <i class="las la-minus remove"></i>
								</span>
			                {/if}
			            
		                <div class="category-sub-menu{if $isDhtml} collapse{/if}" id="exBlogCollapsingNavbar{$node.id}">
		                  {blockCategTree nodes=$node.children depth=$depth+1}
		                </div>
		              {/if}
		            {else}
		              <a class="category-sub-link" href="{$node.link}" title="{$node.name}">{$node.name}</a>
		              {if $node.children}
		              	{if $isDhtml}
			                <span class="arrows" data-toggle="collapse" data-target="#exBlogCollapsingNavbar{$node.id}">
                                  <i class="las la-plus arrow-right"></i>
                                  <i class="las la-minus arrow-down"></i>
			                </span>
			            {/if}
		                <div class="category-sub-menu{if $isDhtml} collapse{/if}" id="exBlogCollapsingNavbar{$node.id}">
		                  {blockCategTree nodes=$node.children depth=$depth+1}
		                </div>
		              {/if}
		            {/if}
		          </li>
		        {/if}
	        {/foreach}
	      </ul>
	    {/if}
	  {/strip}
	{/function}
	
	{if $blockCategTree.children}
		<div class="widget block-categories">
			<div class="widget-content">
				<div class="widget-title h3"><span>{l s='Blog Category' mod='smartblogcategories'}</span></div>
				<div class="block_content">{blockCategTree nodes=$blockCategTree.children}</div>
			</div>
		</div>
	{/if}
{/if}