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

{cycle values='comparison_feature_odd,comparison_feature_even' assign='classname'}
<div class="compare-row">
	<div class="compare-col compare-label">{l s='Reviews' mod='nrtreviews'}</div>
	{foreach from=$list_ids_product item="product"}
		<div class="compare-col compare-value js-compare-{$product.id_product}-0 text-xs-center">
			{if isset($list_product_reviews[$product.id_product]['nbr']) AND $list_product_reviews[$product.id_product]['nbr'] > 0}
				<span class="reviews_note">
					{$avg = $list_product_reviews[$product.id_product]['avg']}
					<span class="star_content star_content_avg"><span style="width:{($avg/5)*100}%"></span></span>
					<span class="nb-reviews">({$list_product_reviews[$product.id_product]['nbr']})</span>
				</span>
			{else}
				-
			{/if}
		</div>
	{/foreach}
</div>