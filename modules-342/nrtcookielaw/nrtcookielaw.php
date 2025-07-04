<?php
/*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtCookieLaw extends Module implements WidgetInterface
{
    protected $templateFile;
    protected $cf_defaults;

    public function __construct()
    {
        $this->name = 'nrtcookielaw';
		$this->version = '2.0.7';
		$this->tab = 'front_office_features';
        $this->author = 'AxonVIZ';
		$this->bootstrap = true;
		$this->need_instance = 0;

        $this->cf_defaults = array(
            'NRT_cookielaw_bg' => '#424851',
            'NRT_cookielaw_color' => '#ffffff',
            'NRT_cookielaw_content' => '<p>This website use cookies to ensure you get the best experience on our website.<span style="color: #fbc227;"><a href="#"><span style="color: #fbc227;">Privacy Policy</span></a></span></p>',
        );

        parent::__construct();

        $this->displayName = $this->l('Axon - Cookie Law notification');
        $this->description = $this->l('Show text about cookies in your shop');

        $this->templateFile = 'module:'.$this->name.'/views/templates/hook/cookielaw.tpl';
    }
	
    public function install()
    {			
        return  parent::install()
				&& $this->registerHook('displayHeader')
				&& $this->registerHook('displayBeforeBodyClosingTag')
				&& $this->_createConfigs()
				&& $this->_createTab();		
    }

    public function uninstall()
    {
        return  parent::uninstall()
				&& $this->_deleteConfigs()
				&& $this->_deleteTab();
    }
	
    /* ------------------------------------------------------------- */
    /*  CREATE CONFIGS
    /* ------------------------------------------------------------- */
    private function _createConfigs()
    {
			
		$response = true;	
		
		foreach ($this->cf_defaults as $cf_default => $value) {
			if ($cf_default == 'NRT_cookielaw_content') {
				$content = array();
				foreach (Language::getLanguages(false) as $lang) {
					$content[(int) $lang['id_lang']] = $value;
				}
				$response &= Configuration::updateValue($cf_default, $content, true);
			} else {
				$response &= Configuration::updateValue($cf_default, $value);
			}
		}

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE CONFIGS
    /* ------------------------------------------------------------- */
    private function _deleteConfigs()
    {
		$response = true;	
        foreach ($this->cf_defaults as $cf_default => $value) {
            $response &= Configuration::deleteByName($cf_default);
        }

        return $response;
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
        $tab->class_name = "AdminManageCookieLaw";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- Cookie Law";
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
        $id_tab = Tab::getIdFromClassName('AdminManageCookieLaw');
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

		if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
			return '<p class="alert alert-warning">' . $this->l('You cannot manage module from a "All Shops" or a "Group Shop" context, select directly the shop you want to edit') .'</p>';
		}

        if (Tools::isSubmit('submitModule')) {

            foreach ($this->cf_defaults as $cf_default => $value) {
                if ($cf_default == 'NRT_cookielaw_content') {
					
					$content = array();
					$languages = Language::getLanguages(false);
					foreach ($languages as $lang) {
						$content[$lang['id_lang']] = Tools::getValue('NRT_cookielaw_content_'.$lang['id_lang']);
					}
                    Configuration::updateValue($cf_default, $content, true);
                } else {
                    Configuration::updateValue($cf_default, Tools::getValue($cf_default));
                }
            }
            $output .= $this->displayConfirmation($this->l('Configuration updated'));
            $this->_clearCache($this->templateFile);
            $this->generateCss();
        }
		
        $output .= $this->renderForm();
		
        return $output;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Cookie law text'),
                        'name' => 'NRT_cookielaw_content',
                        'autoload_rte' => true,
                        'lang' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Background color'),
                        'name' => 'NRT_cookielaw_bg',
                        'size' => 30,
                    ),
                    array(
                        'type' => 'color',
                        'label' => $this->l('Text color'),
                        'name' => 'NRT_cookielaw_color',
                        'size' => 30,
                    ),
                ),
                'submit' => array(
                    'name' => 'submitModule',
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (Shop::isFeatureActive()) {
            $fields_form['form']['description'] = $this->l('The modifications will be applied to') . ' ' . (Shop::getContext() == Shop::CONTEXT_SHOP ? $this->l('shop') . ' ' . $this->context->shop->name : $this->l('all shops'));
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $var = array();
        foreach ($this->cf_defaults as $cf_default => $value) {
            if ($cf_default == 'NRT_cookielaw_content') {
                foreach (Language::getLanguages(false) as $lang) {
                    $var[$cf_default][(int) $lang['id_lang']] = Configuration::get($cf_default, (int) $lang['id_lang']);
                }
            } else {
                $var[$cf_default] = Configuration::get($cf_default);
            }
        }
        return $var;
    }

    public function hookDisplayHeader()
    {
		$id_shop = (int)$this->context->shop->id;

		if(!file_exists(_PS_MODULE_DIR_ . $this->name . "/views/css/custom_s_" . $id_shop . ".css")){
			$this->generateCss();
		}
        
        $this->context->controller->registerStylesheet($this->name.'-css', 'modules/'.$this->name.'/views/css/custom_s_'.$id_shop.'.css', ['media' => 'all', 'priority' => 150]);
        $this->context->controller->registerJavascript($this->name.'-js', 'modules/'.$this->name.'/views/js/front.min.js', ['position' => 'bottom', 'priority' => 150]);
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if (isset($_COOKIE['has_cookielaw'])) {
            return;
        }

        $cacheId = 'cookielaw';
		
        if (!$this->isCached($this->templateFile, $this->getCacheId($cacheId))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId($cacheId));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return array(
            'txt' => Configuration::get('NRT_cookielaw_content', $this->context->language->id),
        );
    }

    public function generateCss()
    {
        $cssCode = '';
		
		if(Configuration::get('NRT_cookielaw_color')){
			$cssCode .='#cookielaw .cookielaw-content{color: ' . Configuration::get('NRT_cookielaw_color') . ';}';
		}
		
		if(Configuration::get('NRT_cookielaw_bg')){
			$cssCode .='#cookielaw .cookielaw-content{background-color: ' . Configuration::get('NRT_cookielaw_bg') . ';}';
		}

        $cssCode = trim(preg_replace('/\s+/', ' ', $cssCode));
		$id_shop = (int)$this->context->shop->id;

		$cssFile = _PS_MODULE_DIR_ . $this->name . "/views/css/custom_s_" . $id_shop . ".css";

		if($cssCode){
			if(file_put_contents($cssFile, $cssCode)){
				return true;
			}else{
				return false;
			}
		}else{
			if(file_exists($cssFile)){
				unlink($cssFile);
			}
		} 
		
    }

}
