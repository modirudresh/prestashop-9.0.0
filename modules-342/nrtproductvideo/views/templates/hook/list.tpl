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

<div class="panel"><h3><i class="icon-list-ul"></i> {l s='Product Videos list' mod='nrtproductvideo'}
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')}&configure=nrtproductvideo&addProductVideo=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>
	<div id="productvideosContent">
		<div id="productvideos">
			{foreach from=$productvideos item=productvideo}
				<div id="productvideos_{$productvideo.id_productvideo}" class="panel" style="padding: 8px 8px 3px 15px;">
					<div class="row">
						<div class="col-md-12">
							<h4 class="pull-left">#{$productvideo.id_productvideo} - {$productvideo.title_bo}</h4>
							<div class="btn-group-action pull-right">
								
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')}&configure=nrtproductvideo&id_productvideo={$productvideo.id_productvideo}">
									<i class="icon-edit"></i>
									{l s='Edit' mod='nrtproductvideo'}
								</a>
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')}&configure=nrtproductvideo&delete_id_productvideo={$productvideo.id_productvideo}" onClick="if(!confirm('Delete selected item ?')) return false;">
									<i class="icon-trash"></i>
									{l s='Delete' mod='nrtproductvideo'}
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>