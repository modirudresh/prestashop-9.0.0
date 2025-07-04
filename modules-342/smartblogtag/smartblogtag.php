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

class SmartBlogTag extends Module implements WidgetInterface
{ 
    public $templateFile;
    protected $fields_form;
    
    public function __construct()
    {
        $this->name       = 'smartblogtag';
        $this->tab        = 'front_office_features';
        $this->version    = '2.0.4';
        $this->bootstrap  = true;
        $this->author     = 'SmartDataSoft';
        
        parent::__construct();
        
        $this->displayName      = $this->l('Smart Blog Tag');
        $this->description      = $this->l('The Most Powerfull Presta shop Blog  Module\'s tag - by smartdatasoft');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
        
        $this->templateFile = 'module:smartblogtag/views/templates/front/smartblogtag.tpl';
    }
    
    public function install()
    {
        return parent::install()
            && $this->registerHook('actionsbdeletepost')
            && $this->registerHook('actionsbnewpost')
            && $this->registerHook('actionsbtogglepost')
            && $this->registerHook('actionsbupdatepost')
            && $this->registerHook('displaySmartBlogLeft')
            && $this->registerHook('displaySmartBlogRight')
            && Configuration::updateGlobalValue('smartshowposttag', 5);
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->DeleteCache();
    }
    
    public function DeleteCache()
    {
        return $this->_clearCache($this->templateFile, $this->getCacheId());
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
                $view_data = array();
                $id_lang   = $this->context->language->id;
                if (Configuration::get('smartshowposttag') != '' && Configuration::get('smartshowposttag') != null) {
                    $limit = Configuration::get('smartshowposttag');
                } else {
                    $limit = 10;
                }
                $id_lang_default = configuration::get('PS_LANG_DEFAULT');
                $sqllangdefault  = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_tag p INNER JOIN 
		' . _DB_PREFIX_ . 'smart_blog_post_shop s ON p.id_post=s.id_smart_blog_post AND s.id_shop = ' . (int) Context::getContext()->shop->id . ' INNER JOIN 
		' . _DB_PREFIX_ . 'smart_blog_tag t ON p.id_tag= t.id_tag where t.id_lang = ' . (int) $id_lang_default . ' LIMIT ' . $limit;
                
                $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_tag p INNER JOIN 
		' . _DB_PREFIX_ . 'smart_blog_post_shop s ON p.id_post=s.id_smart_blog_post AND s.id_shop = ' . (int) Context::getContext()->shop->id . ' INNER JOIN 
		' . _DB_PREFIX_ . 'smart_blog_tag t ON p.id_tag= t.id_tag where t.id_lang = ' . (int) $id_lang . ' LIMIT ' . $limit;
                
                $tags = Db::getInstance()->ExecuteS($sql);
                
                $tmp_name = array();
                $tmp_tags = array();
                
                foreach ($tags as $key => $value) {
                    if (!in_array($value['name'], $tmp_name)) {
                        $tmp_name[] = $value['name'];
                        $tmp_tags[] = $value;
                    }
                }
                $tags = $tmp_tags;
                
                if (empty($tags)) {
                    $tags = Db::getInstance()->ExecuteS($sqllangdefault);
                }
                $this->smarty->assign(array(
                    'tags' => $tags
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

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
    
    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('save' . $this->name)) {
            Configuration::updateValue('smartshowposttag', Tools::getvalue('smartshowposttag'));
            $html   = $this->displayConfirmation($this->l('The settings have been updated successfully.'));
            $helper = $this->SettingForm();
            $html .= $helper->generateForm($this->fields_form);
            return $html;
        } else {
            $helper = $this->SettingForm();
            $html .= $helper->generateForm($this->fields_form);
            return $html;
        }
    }
    
    public function SettingForm()
    {
        $default_lang                 = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General Setting')
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of Tag Show'),
                    'name' => 'smartshowposttag',
                    'size' => 15,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );
        
        $helper                  = new HelperForm();
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->name;
        foreach (Language::getLanguages(false) as $lang)
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
        $helper->toolbar_btn              = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . 'token=' . Tools::getAdminTokenLite('AdminModules')
            )
        );
        $helper->default_form_language    = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title                    = $this->displayName;
        $helper->show_toolbar             = true;
        $helper->toolbar_scroll           = true;
        $helper->submit_action            = 'save' . $this->name;
        
        $helper->fields_value['smartshowposttag'] = Configuration::get('smartshowposttag');
        return $helper;
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
	
}