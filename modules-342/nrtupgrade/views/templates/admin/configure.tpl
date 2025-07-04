{*
* 2007-2014 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="currentNrtConfigurationBlock">
    <div class="panel">
        <div class="panel-heading">{l s='The pre-Upgrade checklist' mod='nrtupgrade'}</div>
        <p class="alert alert-info">{l s='Before starting the upgrade process, please make sure this checklist is all green.' mod='nrtupgrade'}</p>
        <table class="table" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td>
                        {l s='Your store\'s root directory (%s) is writable (with appropriate CHMOD permissions).' sprintf=[$config.rootDirectory] mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.rootDirectoryIsWritable}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled"> {$config.rootDirectoryWritableReport}
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='PHP\'s "Safe mode" option is turned off' mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.safeModeIsDisabled}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/warning.gif" alt="warn">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='PHP\'s "allow_url_fopen" option is turned on, or cURL is installed' mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.allowFOpenIsEnabledOrAllowCUrlIsInstalled}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='PHP\'s "zip" extension is enabled' mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.zipIsEnabled}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='Enable maintenance mode and add your maintenance IP in' mod='nrtupgrade'} 
                        <a href="{$config.maintenanceLink}" target="_blank">{l s='Shop parameters > General > Maintenance' mod='nrtupgrade'} </a>
                    </td>
                    <td>
                        {if $config.storeIsInMaintenance}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='PrestaShop\'s caching features are disabled' mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.cachingIsDisabled}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {if $config.maxExecutionTime == 0}
                            {l s='PHP\'s max_execution_time setting has a high value or is disabled entirely (current value: unlimited)' mod='nrtupgrade'}
                        {else}
                            {l s='PHP\'s max_execution_time setting has a high value or is disabled entirely (current value: %s seconds)' sprintf=[$config.maxExecutionTime] mod='nrtupgrade'}
                        {/if}
                    </td>
                    <td>
                        {if $config.maxExecutionTime == 0}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/warning.gif" alt="warn">
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='Install and Enable the AxonCreator module' mod='nrtupgrade'}
                    </td>
                    <td>
                        {if $config.requiredAxonCreator}
                            <img src="../img/admin/enabled.gif" alt="ok">
                        {else}
                            <img src="../img/admin/disabled.gif" alt="disabled">
                        {/if}
                    </td>
                </tr>
                {if $config.requiredAxonCreator}
                    <tr>
                        <td>
                            {if $config.checkLicense}
                                {l s='Active License the AxonCreator module' mod='nrtupgrade'}
                            {else}
                                {l s='Please active your license the AxonCreator module in'} <a href="{$config.activeLink}" target="_blank">{l s='AXON - CREATOR > Settings & License > License' mod='nrtupgrade'} </a>
                                {l s='or Submit ticket in our support service in' mod='nrtupgrade'} <a href="https://lightatendthemes.ticksy.com/" target="_blank">{l s='Support Team' mod='nrtupgrade'} </a>
                            {/if}
                        </td>
                        <td>
                            {if $config.checkLicense}
                                <img src="../img/admin/enabled.gif" alt="ok">
                            {else}
                                <img src="../img/admin/disabled.gif" alt="disabled">
                            {/if}
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
        <br>
        <p class="alert alert-info">{l s='Please also make sure you make a full manual backup of your files and database.' mod='nrtupgrade'}</p>
    </div>
</div>

<div id="upgradeNrtButtonBlock">
    <div class="panel">
        <div class="panel-heading">
            {l s='Start your Upgrade' mod='nrtupgrade'}
        </div>
        <p>
            {l s='Your current Akira version' mod='nrtupgrade'}: <strong>{$config.currentPsVersion}</strong>
        </p>
        <p>
            {l s='Latest official version.' mod='nrtupgrade'}: <strong>{$config.latestChannelVersion}</strong>
        </p>
        <br>
        {if $config.showUpgradeButton}
            <p class="alert alert-info">{l s='We will update Akira modules and theme in /themes/akira. If your theme name has changed you will have to do it manually.' mod='nrtupgrade'}</p>
            <div>
                <a href="#" id="upgradeNrtNow" class="btn btn-primary">
                    {if $config.upgrade_now}
                        {l s='Upgrade Akira now!' mod='nrtupgrade'}
                    {else}
                        {l s='Upgrade Akira again!' mod='nrtupgrade'}
                    {/if}
                </a>
                <a href="#" id="upgradeNrtLoading" class="btn btn-primary hidden">
                    {if $config.upgrade_now}
                        {l s='Upgrade Akira now!' mod='nrtupgrade'}
                    {else}
                        {l s='Upgrade Akira again!' mod='nrtupgrade'}
                    {/if}
                    <i class="icon-refresh icon-spin"></i>
                </a>
            </div>
        {/if}
    </div>
</div>
{if $config.showUpgradeButton}
    <div id="activityNrtLogBlock" style="display:none">
        <div class="panel">
            <div class="panel-heading">
                {l s='Activity Log' mod='nrtupgrade'}
            </div>
            <p id="upgradeNrtResultCheck" style="display: none;" class="alert alert-success"></p>

            <div><div id="upgradeNrtResultToDoList" style="display: none;" class="alert alert-info col-xs-12"></div></div><br>

            <div class="row">
                <div id="currentlyNrtProcessing" class="col-xs-12" style="display:none;">
                    <h4 id="pleaseNrtWait"> 
                        {l s='Currently processing' mod='nrtupgrade'} <img src="../img/loader.gif"/>
                    </h4>
                    <div id="infoNrtStep" class="processingNrt">
                        {l s='Analyzing the situation...' mod='nrtupgrade'}
                    </div>
                </div>
            </div>
            <br>
            <div id="quickNrtInfo" class="clear processingNrt col-xs-12"></div>
            <div class="row">
                <div id="errorNrtDuringUpgrade" class="col-xs-12" style="display:none;">
                    <h4>
                        {l s='Errors' mod='nrtupgrade'}
                    </h4>
                    <div id="infoNrtError" class="processingNrt"></div>
                </div>
            </div>
        </div>
    </div>
{/if}

