{*
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
*}
{if $setting_updated}
    <div class="conf">{l s='Setting updated' mod='ybc_productimagehover'}</div>
{/if}
<form class="defaultForm form-horizontal" enctype="multipart/form-data" method="post" action="{$postUrl|escape:'html':'UTF-8'}">
    <fieldset>
        <legend><img src="../img/t/AdminTools.gif"/>{l s="Setting" mod='ybc_productimagehover'}</legend>
        <table>
            <tbody>
                <tr>
                    <td><label class="control-label" for="transition-effect">{l s='Transition effect' mod='ybc_productimagehover'}</label></td>
                    <td>
                          <select id="transition-effect" class="fixed-width-xl" name="YBC_PI_TRANSITION_EFFECT">
                            {foreach from=$effects item='effect'}
                                <option {if $effect.id == $YBC_PI_TRANSITION_EFFECT}selected="selected"{/if} value="{$effect.id|escape:'html':'UTF-8'}">{$effect.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>                  
                    </td>
                </tr>
                <tr>
                    <td><label class="control-label col-lg-3" for="those-pages">{l s='Apply transition effect on those pages' mod='ybc_productimagehover'}</label></td>
                    <td>
                        {foreach from=$those_pages item='page'}
                            <p class="checkbox">
                                <label>
                                    <input type="checkbox"
                                           class="{if $page.id=='allpage'}all-page{/if}"
                                           name="YBC_PI_THOSE_PAGES[]"
                                           value="{$page.id|escape:'html':'UTF-8'}"
                                           {if in_array('allpage', $YBC_PI_THOSE_PAGES) || in_array($page.id, $YBC_PI_THOSE_PAGES)}checked="checked"{/if}
                                    /> {$page.name|escape:'html':'UTF-8'}
                                </label>
                            </p>
                        {/foreach}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <button class="btn btn-default pull-right" name="submitUpdate" id="module_form_submit_btn" value="1" type="submit">
                		  <i class="process-icon-save"></i> {l s='Update settings' mod='ybc_productimagehover'}
                	    </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
</form>
<div class="clear"></div>