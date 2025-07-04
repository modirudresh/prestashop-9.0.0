<?php
/*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtCaptcha extends Module implements WidgetInterface
{
    protected $templateFile;
    public static $recaptcha_js_api;
    public static $captcha_config = array();
    public static $error_messages = array();

    public function __construct()
    {
		$this->name = 'nrtcaptcha';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = 'AxonVIZ';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Axon - reCAPTCHA');
		$this->description = $this->l('Required by author: AxonVIZ.');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
		
        self::$recaptcha_js_api = 'https://www.google.com/recaptcha/api.js';
        self::$recaptcha_js_api .= '?hl='.$this->context->language->iso_code;
        $this->templateFile = 'module:' . $this->name . '/views/templates/hook/recaptcha.tpl';
        
        $this->loadConfig();
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayNrtCaptcha')
            && $this->registerHook('displayCustomerAccountForm')
            && $this->registerHook('actionSubmitAccountBefore')
            && $this->_createTab();
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->_deleteTab();
    }

    /* ------------------------------------------------------------- */
    /*  CREATE THE TAB MENU
    /* ------------------------------------------------------------- */
    private function _createTab()
    {
            $response = true;

            // First check for parent tab
            $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

            if ($parentTabID) {
                $parentTab = new Tab($parentTabID);
            }
            else {
                $parentTab = new Tab();
                $parentTab->active = 1;
                $parentTab->name = array();
                $parentTab->class_name = "AdminMenuFirst";
                foreach (Language::getLanguages() as $lang) {
                    $parentTab->name[$lang['id_lang']] = "AXON - MODULES";
                }
                $parentTab->id_parent = 0;
                $parentTab->module ='';
                $response &= $parentTab->add();
            }
			// Check for parent tab2
			$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
			if ($parentTab_2ID) {
				$parentTab_2 = new Tab($parentTab_2ID);
			}
			else {
				$parentTab_2 = new Tab();
				$parentTab_2->active = 1;
				$parentTab_2->name = array();
				$parentTab_2->class_name = "AdminMenuSecond";
				foreach (Language::getLanguages() as $lang) {
					$parentTab_2->name[$lang['id_lang']] = "Modules";
				}
				$parentTab_2->id_parent = $parentTab->id;
				$parentTab_2->module = '';
				$parentTab_2->icon = 'build';
				$response &= $parentTab_2->add();
			}
			// Created tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminNrtCaptcha";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- reCaptcha";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }
	 /* ------------------------------------------------------------- */
    /*  DELETE THE TAB MENU
    /* ------------------------------------------------------------- */
    private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtCaptcha');
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        $tab = new Tab($id_tab);
        $tab->delete();
		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		$tabCount_2 = Tab::getNbTabs($parentTab_2ID);
        if ($tabCount_2 == 0) {
            $parentTab_2 = new Tab($parentTab_2ID);
            $parentTab_2->delete();
        }
        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0){
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (((bool)Tools::isSubmit('submitNrtCaptcha')) == true) {
            $output .= $this->postProcess();
            $this->loadConfig();
        }

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('reCAPTCHA Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'NRTCAPTCHA_SITE_KEY',
                        'label' => $this->l('Site Key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'NRTCAPTCHA_SECRET_KEY',
                        'label' => $this->l('Secret Key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'name' => 'NRTCAPTCHA_VERSION',
                        'label' => $this->l('reCAPTCHA Version'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'name' => 'V2 / Manual Verification',
                                    'value' => '2'
                                ),
                                array(
                                    'name' => $this->l('V3 / Invisible'),
                                    'value' => '3'
                                )
                            ),
                            'id' => 'value',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'switch',
                        'name' => 'NRTCAPTCHA_IN_REG_FORM',
                        'label' => $this->l('Enable for registration form'),
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
                        ),
                        'is_bool' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNrtCaptcha';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => self::$captcha_config,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }
    
    protected function getConfigFormValues()
    {
        $site_key = Tools::getValue('NRTCAPTCHA_SITE_KEY', Configuration::get('NRTCAPTCHA_SITE_KEY'));
        $secret_key = Tools::getValue('NRTCAPTCHA_SECRET_KEY', Configuration::get('NRTCAPTCHA_SECRET_KEY'));
        $enable_reg_form = Tools::getValue('NRTCAPTCHA_IN_REG_FORM', Configuration::get('NRTCAPTCHA_IN_REG_FORM'));
        $version = Tools::getValue('NRTCAPTCHA_VERSION', Configuration::get('NRTCAPTCHA_VERSION'));

        return array(
            'NRTCAPTCHA_SITE_KEY' => $site_key,
            'NRTCAPTCHA_SECRET_KEY' => $secret_key,
            'NRTCAPTCHA_IN_REG_FORM' => $enable_reg_form,
            'NRTCAPTCHA_VERSION' => $version
        );
    }

    protected function postProcess()
    {
        $failed = 0;

        $form_values = self::$captcha_config;

        foreach (array_keys($form_values) as $key) {
            if (Tools::getIsset($key)) {
                $processed = Configuration::updateValue($key, Tools::getValue($key));
                if (!$processed) {
                    $failed++;
                }
            }
        }

        $modules = 
            [
            'nrtreviews' 			=> ['registerNRTCaptcha'],
            'smartblog' 			=> ['registerNRTCaptcha'],
            'contactform' 			=> ['registerNRTCaptcha']
            ];

        foreach ($modules as $module_name => $hooks) {
            if(Module::isInstalled($module_name)){
                $module = Module::getInstanceByName($module_name);
                foreach ($hooks as $hook) {
                    if (!Hook::isModuleRegisteredOnHook($module, $hook, $this->context->shop->id)) {
                        Hook::registerHook($module, $hook);
                    }
                }
            }
        }

        $this->_clearCache($this->templateFile);

        if ($failed) {
            return $this->displayError($this->l('Update failed'));
        } else {
            return $this->displayConfirmation($this->l('Update successful'));
        }
    }

    public function hookDisplayHeader()
    {
        if (self::$captcha_config['NRTCAPTCHA_SITE_KEY'] && self::$captcha_config['NRTCAPTCHA_SECRET_KEY']) {
            Media::addJsDef(array(
                'opCaptcha' => array(
                        'site_key' => self::$captcha_config['NRTCAPTCHA_SITE_KEY'],
                        'version' => self::$captcha_config['NRTCAPTCHA_VERSION']
            )));

            $this->context->controller->registerJavascript($this->name.'-js', 'modules/'.$this->name.'/views/js/front.min.js', ['position' => 'bottom', 'priority' => 999]);

            $this->context->controller->registerJavascript(
                'recaptcha-js',
                self::$recaptcha_js_api,
                array(
                    'server' => 'remote',
                    'position'  =>  'bottom'
                )
            );
        }
    }

    public function getConfigurations()
    {
        return array_merge(self::$captcha_config, self::$error_messages);
    }

    private function loadConfig()
    {
        self::$captcha_config = $this->getConfigFormValues();
        if (self::$captcha_config['NRTCAPTCHA_VERSION'] == 3) {
            self::$recaptcha_js_api .= '&render=' . self::$captcha_config['NRTCAPTCHA_SITE_KEY'];
        }

        self::$error_messages = array(
            'CAPTCHA_FAILED' => $this->l('Please complete the captcha')
        );

        if (self::$captcha_config['NRTCAPTCHA_VERSION'] == 3) {
            self::$error_messages['CAPTCHA_FAILED'] = $this->l('Invalid captcha response, please try again');
        }
    }

    public function verifyCaptcha($response)
    {
        if (!self::$captcha_config['NRTCAPTCHA_SITE_KEY'] || !self::$captcha_config['NRTCAPTCHA_SECRET_KEY']) {
            return true;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => self::$captcha_config['NRTCAPTCHA_SECRET_KEY'],
            'response' => $response
        );
        $options = array(
            'http' => array (
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $verify = Tools::file_get_contents($url, false, $context);
        $captcha_success = json_decode($verify);
        return $captcha_success->success;
    }

    public function hookActionSubmitAccountBefore($params)
    {
        if ((bool)self::$captcha_config['NRTCAPTCHA_IN_REG_FORM'] && !($this->context->controller instanceof OrderController)) {
            $nrtcaptcha_config = $this->getConfigurations();
            if ($this->verifyCaptcha(Tools::getValue("g-recaptcha-response"))) {
                return true;
            }
            $this->context->controller->errors[] = $nrtcaptcha_config['CAPTCHA_FAILED'];
            return false;
        }

        return true;
    }

    public function renderWidget($hookName = null, array $configuration = []) {	
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if (self::$captcha_config['NRTCAPTCHA_VERSION'] == 3) {
            return;
        }

        $this->templateFile = 'module:' . $this->name . '/views/templates/hook/recaptcha.tpl';
        $cacheId = 'nrtCaptcha';

        if (preg_match('/^displayCustomerAccountForm\d*$/', $hookName) && (bool)self::$captcha_config['NRTCAPTCHA_IN_REG_FORM'] && !($this->context->controller instanceof OrderController)) {	
            if (!$this->isCached($this->templateFile, $this->getCacheId($cacheId))){		
                $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            }
            
            return $this->fetch($this->templateFile, $this->getCacheId($cacheId));
        }elseif (preg_match('/^displayNrtCaptcha\d*$/', $hookName)) {	
            if (!isset($configuration['id_module'])) { return ''; }

            $id_module = (int) $configuration['id_module'];

            $id_hook = (int) Hook::getIdByName('registerNRTCaptcha', true);

            if (!Hook::getModulesFromHook($id_hook, $id_module)) { return ''; }

            if (!$this->isCached($this->templateFile, $this->getCacheId($cacheId))){		
                $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            }

            return $this->fetch($this->templateFile, $this->getCacheId($cacheId));
        }
    }

    public function getWidgetVariables( $hookName = null, array $configuration = [] )
    {												
        return array(
            'recaptcha_site_key' => self::$captcha_config['NRTCAPTCHA_SITE_KEY']
        );
    }
}
