<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/src/NrtWishlistProduct.php';

class NrtWishlist extends Module implements WidgetInterface
{
    public $defaults;

    public function __construct()
    {
        $this->name = 'nrtwishlist';
		$this->tab = 'front_office_features';
        $this->version = '2.3.0';
		$this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->controllers = array('view');
        parent::__construct();
        $this->displayName = $this->l('Axon - Wishlist Block');
        $this->description = $this->l('Adds a block containing the customer\'s wishlists.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->defaults = array(
			'nrt_wishlist_allow_guests' => 1,
            'nrt_wishlist_enabled_notices' => 1
        );
    }
	
    public function install()
    {
        return parent::install()
			&& $this->_createTab()
			&& $this->setDefaults()
			&& $this->createTables()
            && $this->registerHook('actionDeleteGDPRCustomer')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('displayButtonWishList')
            && $this->registerHook('displayButtonWishListNbr')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayMenuMobileCanVas')
            && $this->registerHook('displayMyAccountCanVas')
            && $this->registerHook('registerGDPRConsent');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab() && $this->deleteDefaults() && $this->dropTables();
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
        $tab->class_name = "AdminNrtWishList";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- WishList";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    public function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtWishList');
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
	
    public function createTables()
    {
        $return = true;

        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_wishlist_product` (
				`id_nrt_wishlist_product` int(10) NOT NULL auto_increment,
				`id_product` int(10) unsigned NOT NULL,
				`id_product_attribute` int(10) unsigned NOT NULL,
				`id_customer` int(10) unsigned NOT NULL,
				`id_shop` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id_nrt_wishlist_product`, `id_product` ,`id_product_attribute`, `id_customer`, `id_shop`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
					
        return $return;
    }

    public function dropTables()
    {
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nrt_wishlist_product`');
    }

    public function getContent()
    {
		$output = '';
		$response = true;	
        if (Tools::isSubmit('submit'.$this->name)) {
			foreach ($this->defaults as $default => $value) {
				$response &= Configuration::updateValue($default, Tools::getValue($default));
			}
			if (!$response)
				$output = '<div class="alert alert-danger conf error">'.$this->l('An error occurred on saving.').'</div>';
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
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
                        'label' => $this->l('Allow guest reviews'),
                        'name' => 'nrt_wishlist_allow_guests',
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
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enabled notices'),
                        'name' => 'nrt_wishlist_enabled_notices',
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
		
        $this->context->controller->registerStylesheet($this->name.'-css', 'modules/'.$this->name.'/views/css/front'.$dir_rtl.'.css', ['media' => 'all', 'priority' => 998]);
        $this->context->controller->registerJavascript($this->name.'-js', 'modules/'.$this->name.'/views/js/front.min.js', ['position' => 'bottom', 'priority' => 150]);
		
        $productsIds = NrtWishlistProduct::getWishlistProductsIds((int)$this->context->customer->id);

        if (!$this->context->customer->isLogged() && Configuration::get('nrt_wishlist_allow_guests')) {
            $productsIds = $this->context->cookie->nrtWishList;
        
            if($productsIds) {
                $productsIds = json_decode($productsIds, true);
            }else{
                $productsIds = array();
            }
        }

        Media::addJsDef(array(
			'opWishList' => array(
					'actions' => $this->context->link->getModuleLink('nrtwishlist', 'actions', array(), null, null, null, true),
					'login' => $this->context->link->getModuleLink('nrtwishlist', 'login', array(), null, null, null, true),
                    'enabled_notices' => (bool)Configuration::get('nrt_wishlist_enabled_notices'),
					'ids' =>  $productsIds,
					'alert' => ['add' => $this->l('Add to Wishlist'),
								'view' => $this->l('Go to Wishlist')]
        )));
		
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
		
		$templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-nb.tpl';
		
		$cacheId = 'nbWishList';
		
        if (preg_match('/^displayCustomerAccount\d*$/', $hookName)) {
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-account.tpl';
			$cacheId = 'acWishList';
        } elseif (preg_match('/^displayBeforeBodyClosingTag\d*$/', $hookName)) {
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-modal.tpl';
			$cacheId = 'mdWishList';
        } elseif (preg_match('/^displayButtonWishList\d*$/', $hookName)) {
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-btn.tpl';
			$cacheId = 'btnWishList';
        }
		
        return $this->fetch($templateFile, $this->getCacheId($cacheId));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
    }

    public function hookActionProductDelete($product)
    {
        if (!empty($product['id_product'])) {
            Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."nrt_wishlist_product WHERE id_product = '".(int)pSQL($product['id_product'])."'");
        }
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id'])) {
            $sql = "DELETE FROM "._DB_PREFIX_."nrt_wishlist_product WHERE id_customer = '".(int)pSQL($customer['id'])."'";
            if (Db::getInstance()->execute($sql)) {
                return json_encode(true);
            }
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['id'])) {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "nrt_wishlist_product WHERE id_customer = '".(int)pSQL($customer['id'])."'";
            if ($res = Db::getInstance()->executeS($sql)) {
                return json_encode($res);
            }
        }
    }
}
