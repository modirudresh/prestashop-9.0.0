<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')){
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class smartblogarchive extends Module implements WidgetInterface
{
    
    public $templateFile;
    
    public function __construct()
    {
        $this->name       = 'smartblogarchive';
        $this->tab        = 'front_office_features';
        $this->version    = '2.0.8';
        $this->bootstrap  = true;
        $this->author     = 'SmartDataSoft';
        
        parent::__construct();
        
        $this->displayName      = $this->l('Smart Blog Archive');
        $this->description      = $this->l('The Most Powerfull Presta shop Blog Archive Module\'s - by smartdatasoft');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
        
        $this->templateFile = 'module:smartblogarchive/views/templates/front/smartblogarchive.tpl';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionsbdeletepost')
            && $this->registerHook('actionsbnewpost')
            && $this->registerHook('actionsbtogglepost')
            && $this->registerHook('actionsbupdatepost')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displaySmartBlogLeft')
            && $this->registerHook('displaySmartBlogRight')
            && Configuration::updateValue('smartblogarchive_type', 2)
            && Configuration::updateValue('SMART_BLOG_ARCHIVE_DHTML', 0);
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->DeleteCache();
    }
    
    public function getContent()
    {
        $html = '';
        // If we try to update the settings
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('smartblogarchive_type', Tools::getValue('smartblogarchive_type'));
            $dhtml = Tools::getValue('SMART_BLOG_ARCHIVE_DHTML');
            Configuration::updateValue('SMART_BLOG_ARCHIVE_DHTML', (int) $dhtml);
            
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
            $this->_clearCache('smartblogarchive.tpl');
            Tools::redirectAdmin('index.php?tab=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        }
        
        $html .= $this->renderForm();
        
        return $html;
    }
    
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('How you like to display Archive Type'),
                        'name' => 'smartblogarchive_type',
                        //'hint' => $this->l('Select which  way.'),
                        'values' => array(
                            
                            array(
                                'id' => 'year',
                                'value' => 1,
                                'label' => $this->l('Year Wise')
                            ),
                            array(
                                'id' => 'month_year',
                                'value' => 2,
                                'label' => $this->l('Month & Year Wise')
                            )
                            
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Dynamic'),
                        'name' => 'SMART_BLOG_ARCHIVE_DHTML',
                        'desc' => $this->l('Activate dynamic (animated) mode for category sublevels.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            )
        );
        
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;        
        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token         = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars      = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(array(
            $fields_form
        ));
    }

    public function hookLeftColumn($params)
    {
        if (Module::isInstalled('smartblog') != 1) {
            $this->smarty->assign(array(
                'smartmodname' => $this->name
            ));
            return $this->display(__FILE__, 'views/templates/front/install_required.tpl');
        } else {
            if (!$this->isCached($this->templateFile, $this->getCacheId())) {
                
                if (Configuration::get('smartblogarchive_type') == 1) {
                    $archives = SmartBlogPost::getArchiveOld();
                } else {
                    $archives = SmartBlogPost::getArchive();
                }
                
                $this->smarty->assign(array(
                    'archive_type' => Configuration::get('smartblogarchive_type'),
                    'isDhtml' => Configuration::get('SMART_BLOG_ARCHIVE_DHTML'),
                    'archives' => $archives
                ));
            }
            return $this->fetch($this->templateFile, $this->getCacheId());
        }
    }
    
    public function hookRightColumn($params)
    {
        return $this->hookLeftColumn($params);
    }
    
    public function hookdisplaySmartBlogLeft($params)
    {
        return $this->hookLeftColumn($params);
    }
    
    public function hookdisplaySmartBlogRight($params)
    {
        return $this->hookLeftColumn($params);
    }
    
    public function DeleteCache()
    {
        return $this->_clearCache($this->templateFile, $this->getCacheId());
    }
	
    public function hookactionsbdeletepost($params)
    {
        return $this->DeleteCache();
    }
	
    public function hookactionsbnewpost($params)
    {
        return $this->DeleteCache();
    }
	
    public function hookactionsbupdatepost($params)
    {
        return $this->DeleteCache();
    }
	
    public function hookactionsbtogglepost($params)
    {
        return $this->DeleteCache();
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
	
    public function getConfigFieldsValues()
    {
        return array(
            'smartblogarchive_type' => Tools::getValue('smartblogarchive_type', Configuration::get('smartblogarchive_type')),
            'SMART_BLOG_ARCHIVE_DHTML' => Tools::getValue('SMART_BLOG_ARCHIVE_DHTML', Configuration::get('SMART_BLOG_ARCHIVE_DHTML'))
        );
    }
	
}