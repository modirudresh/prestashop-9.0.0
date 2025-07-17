{*
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
*}
{if isset($hookDisplayProductListHeaderAfter) && $hookDisplayProductListHeaderAfter}
    <div id="etsBAWhookDisplayProductListHeaderAfter" style="display: none">
        {$hookDisplayProductListHeaderAfter nofilter}
    </div>
{/if}
{if isset($hookDisplayProductListHeaderBefore) && $hookDisplayProductListHeaderBefore}
    <div id="etsBAWhookDisplayProductListHeaderBefore" style="display: none">
        {$hookDisplayProductListHeaderBefore nofilter}
    </div>
{/if}
{if isset($hookDisplayLeftColumnBefore) && $hookDisplayLeftColumnBefore}
    <div id="etsBAWhookDisplayLeftColumnBefore" style="display: none">
        {$hookDisplayLeftColumnBefore nofilter}
    </div>
{/if}
{if isset($hookDisplayRightColumnBefore) && $hookDisplayRightColumnBefore}
    <div id="etsBAWhookDisplayRightColumnBefore" style="display: none">
        {$hookDisplayRightColumnBefore nofilter}
    </div>
{/if}
{if isset($hookDisplayProductVariantsBefore) && $hookDisplayProductVariantsBefore}
    <div id="etsBAWhookDisplayProductVariantsBefore" style="display: none">
        {$hookDisplayProductVariantsBefore nofilter}
    </div>
{/if}
{if isset($hookDisplayProductVariantsAfter) && $hookDisplayProductVariantsAfter}
    <div id="etsBAWhookDisplayProductVariantsAfter" style="display: none">
        {$hookDisplayProductVariantsAfter nofilter}
    </div>
{/if}
{if isset($hookDisplayProductCommentsListHeaderBefore) && $hookDisplayProductCommentsListHeaderBefore}
    <div id="etsBAWhookDisplayProductCommentsListHeaderBefore" style="display: none">
        {$hookDisplayProductCommentsListHeaderBefore nofilter}
    </div>
{/if}
{if isset($hookDisplayCartGridBodyBefore1) && $hookDisplayCartGridBodyBefore1}
    <div id="etsBAWhookDisplayCartGridBodyBefore1" style="display: none">
        {$hookDisplayCartGridBodyBefore1 nofilter}
    </div>
{/if}
{if isset($hookDisplayCartGridBodyBefore2) && $hookDisplayCartGridBodyBefore2}
    <div id="etsBAWhookDisplayCartGridBodyBefore2" style="display: none">
        {$hookDisplayCartGridBodyBefore2 nofilter}
    </div>
{/if}
{if isset($hookDisplayCartGridBodyAfter) && $hookDisplayCartGridBodyAfter}
    <div id="etsBAWhookDisplayCartGridBodyAfter" style="display: none">
        {$hookDisplayCartGridBodyAfter nofilter}
    </div>
{/if}