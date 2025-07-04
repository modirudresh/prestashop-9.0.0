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

class NrtSearchbar extends Module implements WidgetInterface
{
    public $templateFileBtn;
	public $templateFile;
	public $templateFileBox;
	public $defaults;
    public $cfgName;

    public function __construct()
    {
        $this->name = 'nrtsearchbar';
        $this->author = 'AxonVIZ';
        $this->version = '2.2.4';
        $this->need_instance = 0;
		$this->bootstrap = true;
		$this->cfgName = 'nrtsearch_';

        parent::__construct();

        $this->displayName = $this->l('Axon - Search Bar');
        $this->description = $this->l('Adds a quick search field to your website.');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);

		$this->templateFileBtn = 'module:nrtsearchbar/views/templates/hook/btn_search.tpl';
        $this->templateFile = 'module:nrtsearchbar/views/templates/hook/searchbar.tpl';
		$this->templateFileBox = 'module:nrtsearchbar/views/templates/hook/searchbar_modal.tpl';
		$this->defaults = array(
			'max_items' => 36
		);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('displayButtonSearch')
            && $this->registerHook('displayHeaderMobileRight')
            && $this->registerHook('displaySearch')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionProductSearchAfter')
			&& $this->setDefaults()
			&& $this->_createTab()
        ;
    }
	
    public function uninstall()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::deleteByName($this->cfgName . $default);
        }
        if (!parent::uninstall() || !$this->_deleteTab()) {
            return false;
        }
        return true;
    }
	
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
        $tab->class_name = "AdminNrtSearchBar";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Products Search";
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
        $id_tab = Tab::getIdFromClassName('AdminNrtSearchBar');
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
        if ($tabCount == 0) {
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }
	
    public function setDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::updateValue($this->cfgName . $default, $value);
        }
        return true;
    }
	
    public function postProcess()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $languages = Language::getLanguages(false);
            $values = array();
			$values[$this->cfgName.'max_items'] = Tools::getValue($this->cfgName.'max_items');
			
			Configuration::updateValue($this->cfgName.'max_items', $values[$this->cfgName.'max_items']);

            $this->_clearCache($this->templateFileBtn);
            $this->_clearCache($this->templateFile);
            $this->_clearCache($this->templateFileBox);
	
            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        return '';
    }

    public function getContent()
    {
		$this->context->controller->addJqueryPlugin('tagify');
        return $this->postProcess().$this->renderForm();
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
						'type' => 'text',
						'label' => $this->l('Max items'),
						'name' => $this->cfgName.'max_items',
						'required' => false,
						'class' => 'fixed-width-xxl',
						'suffix' => 'items'
					),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
		
        $languages = Language::getLanguages(false);
        $fields = array();
		$fields[$this->cfgName.'max_items'] = Configuration::get($this->cfgName.'max_items');

        return $fields;
    }

    public function hookDisplayHeader()
    {		
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
				
        $this->context->controller->registerStylesheet($this->name.'-css', 'modules/'.$this->name.'/views/css/front'.$dir_rtl.'.css', ['media' => 'all', 'priority' => 150]); 
        $this->context->controller->registerJavascript($this->name.'-autocomplete', 'modules/'.$this->name.'/views/js/jquery.autocomplete.min.js', ['position' => 'bottom', 'priority' => 150]);	
        $this->context->controller->registerJavascript($this->name.'-js', 'modules/'.$this->name.'/views/js/front.min.js', ['position' => 'bottom', 'priority' => 150]);
		
		$opThemect = json_decode( Configuration::get('opThemect'), true );
		
		$search_string = Tools::getValue('s');
		
        if (!$search_string) {
            $search_string = Tools::getValue('search_query');
        }
		
        Media::addJsDef(array(
			'opSearch' => array('all_results_product' => $this->l('View all product results'),
								'noProducts' => $this->l('No products found'),
								'count' => Configuration::get($this->cfgName.'max_items'),
							    'sku' => $this->l('SKU:'),
								'divider' => $this->l('Results from product'),
								'search_string' => $search_string,
							    'imageType' => isset($opThemect['general_product_image_type_small'])?$opThemect['general_product_image_type_small']:'')
        ));
    }
	
    public function renderWidget($hookName, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
		
		$cacheId = 'nrtSearchBtn';

		if ($hookName == 'displaySearch') {
			$templateFile = $this->templateFile;
            $cacheId = 'nrtSearch';

            if (!$this->isCached($templateFile, $this->getCacheId($cacheId))) {
                $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            }
		}elseif($hookName == 'displayBeforeBodyClosingTag'){
			$templateFile = $this->templateFileBox;
            $cacheId = 'nrtSearchBox';

            if (!$this->isCached($templateFile, $this->getCacheId($cacheId))) {
                $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            }
		}else{
			$templateFile = $this->templateFileBtn;
		}
						
		return $this->fetch($templateFile, $this->getCacheId($cacheId));
    }

    public function getWidgetVariables($hookName, array $configuration = [])
    {
        $widgetVariables = array(
            'search_controller_url' => $this->context->link->getPageLink('search', null, null, null, false, null, true)
        );

		return $widgetVariables;
    }
	
    ///////////////////////////////////////////////////////////////////////////////////////////////

    public function hookActionProductSearchAfter($data)
    {
        if(Tools::getValue('nrtAjax')){
            if(ob_get_contents()){
                ob_end_clean();
            }
            header('Content-Type: application/json');
            $this->ajaxDie(json_encode($this->getAjaxProductSearchVariables($data)));
            
            return;
        }
    }

    public function getAjaxProductSearchVariables($data)
    {
        if (!empty($data['products']) && is_array($data['products'])) {
            $data['products'] = $this->prepareProductArrayForAjaxReturn($data['products']);
        }

        return $data;
    }

    public function prepareProductArrayForAjaxReturn(array $products)
    {
        $filter = $this->get('prestashop.core.filter.front_end_object.product_collection');

        return $filter->filter($products);
    }

    protected function ajaxDie($value = null, $controller = null, $method = null)
    {
        $this->ajaxRender($value, $controller, $method);
        exit;
    }

    public function ajaxRender($value = null, $controller = null, $method = null)
    {
        if ($controller === null) {
            $controller = get_class($this);
        }

        if ($method === null) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $method = $bt[1]['function'];
        }

        Hook::exec('actionAjaxDie' . $controller . $method . 'Before', ['value' => &$value]);
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        echo $value;
    }
}
