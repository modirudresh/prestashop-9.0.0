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
{if $PLW_HTML || $PLW_LOADING_MESSAGE}
    <div class="plw_content" style="background: {$PLW_BACKGROUND_COLOR|escape:'htmlall':'UTF-8'};">
        <div class="plw_content_center">
            {if $PLW_HTML}<div class="plw_icon">{str_replace(array('{bgcolor}','{size}','{size2}'),array($PLW_ICON_COLOR,$PLW_SPINNER_SIZE,$PLW_SPINNER_SIZE2),$PLW_HTML) nofilter}</div>{/if}
            {if $PLW_LOADING_MESSAGE}<div class="plw_text" style="color: {$PLW_TEXT_COLOR|escape:'htmlall':'UTF-8'};">{$PLW_LOADING_MESSAGE|escape:'htmlall':'UTF-8'}</div>{/if}
        </div>
    </div>
{/if}