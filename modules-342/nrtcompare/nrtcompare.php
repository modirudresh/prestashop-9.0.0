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
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class NrtCompare extends Module implements WidgetInterface
{
    public $defaults;

    public function __construct()
    {
		$this->name = 'nrtcompare';
		$this->tab = 'front_office_features';
		$this->version = '2.2.8';
		$this->author = 'AxonVIZ';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Axon - Product Compare');
		$this->description = $this->l('Required by author: AxonVIZ.');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->defaults = array(
            'nrt_compare_enabled_notices' => 1
        );
    }

    public function install()
    {
		return parent::install()
            && $this->_createTab()
            && $this->setDefaults()
            && $this->registerHook('displayHeader')	
			&& $this->registerHook('displayButtonCompareNbr')
            && $this->registerHook('displayButtonCompare')
			&& $this->registerHook('displayMenuMobileCanVas')
			&& $this->registerHook('displayMyAccountCanVas');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab() && $this->deleteDefaults();
    }

    public function _createTab()
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
        $tab->class_name = "AdminNrtCompare";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Compare";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    public function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtCompare');
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
            Configuration::updateValue($default, $value);
        }
        return true;
    }
	
    public function deleteDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::deleteByName($default);
        }
        return true;
    }

    public function getContent()
    {
		$output = '';
		$response = true;	
        
        if (Tools::isSubmit('submit'.$this->name)) {
			foreach ($this->defaults as $default => $value) {
				$response &= Configuration::updateValue($default, Tools::getValue($default));
			}

			if (!$response){
                $output = '<div class="alert alert-danger conf error">'.$this->l('An error occurred on saving.').'</div>';
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }   
		}

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled notices'),
                        'name' => 'nrt_compare_enabled_notices',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
				
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submit'.$this->name;
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		
		$helper->tpl_vars = array(
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getFormValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
        return $helper->generateForm(array($fields_form));
    }

    public function getFormValues()
    {
		$values = array();
		foreach ($this->defaults as $default => $value) {
            $values[$default] = Configuration::get($default);
        }
		return $values;
    }

    public function hookDisplayHeader()
    {
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
		
        $this->context->controller->registerStylesheet($this->name.'-css', 'modules/'.$this->name.'/views/css/front'.$dir_rtl.'.css', ['media' => 'all', 'priority' => 150]);
        $this->context->controller->registerJavascript($this->name.'-js', 'modules/'.$this->name.'/views/js/front.min.js', ['position' => 'bottom', 'priority' => 150]);

        $productsIds = $this->context->cookie->nrtCompareN;
		
		if($productsIds) {
			$productsIds = json_decode($productsIds, true);
		}else{
			$productsIds = array();
		}

        Media::addJsDef(array(
			'opCompare' => array(
					'actions' => $this->context->link->getModuleLink('nrtcompare', 'actions', array(), null, null, null, true),
                    'enabled_notices' => (bool)Configuration::get('nrt_compare_enabled_notices'),
					'ids' =>  $productsIds,
					'alert' => ['add' => $this->l('Add to Compare'),
								'view' => $this->l('Go to Compare')]
        )));
    }
		
    public function renderWidget($hookName = null, array $configuration = [])
    {		
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
		
        $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-nb.tpl';
		
		$cacheId = 'nbCompare';
		
		if (preg_match('/^displayButtonCompare\d*$/', $hookName)) {
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-btn.tpl';

			$cacheId = 'btnCompare';
        }

        return $this->fetch($templateFile, $this->getCacheId($cacheId));
    }

    public function getWidgetVariables($hookName = null, array $configuration = []){}
}
