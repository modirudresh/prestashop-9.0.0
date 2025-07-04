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

<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="{l s='Close (Esc)' mod='nrtthemecustomizer'}"></button>
                <button class="pswp__button pswp__button--share" title="{l s='Share' mod='nrtthemecustomizer'}"></button>
                <button class="pswp__button pswp__button--fs" title="{l s='Toggle fullscreen' mod='nrtthemecustomizer'}"></button>
                <button class="pswp__button pswp__button--zoom" title="{l s='Zoom in/out' mod='nrtthemecustomizer'}"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="{l s='Previous' mod='nrtthemecustomizer'}"></button>
            <button class="pswp__button pswp__button--arrow--right" title="{l s='Next' mod='nrtthemecustomizer'}"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
</div>
	
<div id="modal-iframe-popup" class="modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-iframe-wrapper popup-wrapper" role="document"><div class="modal-content"><button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"><span aria-hidden="true">×</span></button><div id="modal-iframe-content" class="modal-body"></div></div></div></div>

<div id="axps_loading" class=""><div class="axps_loading_inner"><span class="spinner"></span></div></div>

{include file="_partials/canvas/account.tpl"} 
{include file="_partials/canvas/facets.tpl"} 

<div class="canvas-widget-backdrop" data-dismiss="canvas-widget"></div>

{if isset($opThemect.general_back_top) && $opThemect.general_back_top}
    <button id="back-top">
        <i class="las la-angle-up"></i>
    </button>
{/if}