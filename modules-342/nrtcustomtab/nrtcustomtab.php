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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/src/CustomTabModel.php';

class NrtCustomTab extends Module implements WidgetInterface
{
    private $_html = '';
    protected $config_name;
    protected $defaults;
	
    public function __construct()
    {
        $this->name = 'nrtcustomtab';
		$this->tab = 'front_office_features';
        $this->version = '2.0.5';
		$this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();		
		$this->displayName = $this->l('Axon - Custom Tabs Product');
		$this->description = $this->l('Required by author: AxonVIZ.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->config_name = 'NRT_customtab';
        $this->defaults = array(
            'show_global' => 1,
            'title_global' => 'Tab Title',
            'content_global' => 'Tab Content',
        );
		
    }

    public function install()
    {
		return parent::install()
            && $this->registerHook('actionProductDelete')	
			&& $this->registerHook('actionProductSave')
            && $this->registerHook('displayAdminProductsExtra')
			&& $this->registerHook('displayProductExtraContent')
            && $this->_createTab()
            && $this->createTables()
            && $this->setDefaults();
    }

    public function uninstall()
    {
		return parent::uninstall() && $this->_deleteTab() && $this->deleteTables();
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
        } else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminMenuFirst";
            foreach (Language::getLanguages() as $lang){
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
        $tab->class_name = "AdminNrtCustomTab";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- Custom Tab";
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
        $id_tab = Tab::getIdFromClassName('AdminNrtCustomTab');
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
    /**
     * Creates tables
     */
    protected function createTables()
    {

        $res = (bool) Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtcustomtab` (
				`id_nrtcustomtab_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_shop` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_nrtcustomtab_detail`, `id_shop`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtcustomtab_detail` (
			  `id_nrtcustomtab_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title_bo` varchar(255) NOT NULL,
			  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  PRIMARY KEY (`id_nrtcustomtab_detail`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtcustomtab_detail_lang` (
			  `id_nrtcustomtab_detail` int(10) unsigned NOT NULL,
			  `id_lang` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  PRIMARY KEY (`id_nrtcustomtab_detail`,`id_lang`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtcustomtab_product` (
				`id_product` int(10) unsigned NOT NULL,
				`id_customtab` varchar(255) NOT NULL,
				 PRIMARY KEY (`id_product`)
				) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');

        return $res;
    }

    /**
     * deletes tables
     */
    protected function deleteTables()
    {
        $customtabs = $this->getCustomTabs();
        foreach ($customtabs as $customtab) {
            $to_del = new CustomTabModel($customtab['id_customtab']);
            $to_del->delete();
        }

        return Db::getInstance()->execute('
			DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'nrtcustomtab`, `' . _DB_PREFIX_ . 'nrtcustomtab_detail`, `' . _DB_PREFIX_ . 'nrtcustomtab_product`, `' . _DB_PREFIX_ . 'nrtcustomtab_detail_lang`;
		');
    }

    public function setDefaults()
    {
        $response = true;

        foreach ($this->defaults as $default => $value) {
            if ($default == 'title_content' || $default == 'content_global') {
                $message_trads = array();
                foreach (Language::getLanguages(false) as $lang) {
                    $message_trads[(int) $lang['id_lang']] = $value;
                }
                $response &= Configuration::updateValue($this->config_name . '_' . $default, $message_trads, true);
            } else {
                $response &= Configuration::updateValue($this->config_name . '_' . $default, $value);
            }

        }

        return $response;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitCustomTab') || Tools::isSubmit('delete_id_customtab')) {
            if ($this->_postValidation()) {
                $this->_postProcess();
                $this->_html .= $this->renderForm();
                $this->_html .= $this->renderList();
            } else {
                $this->_html .= $this->renderAddForm();
            }

        } elseif (Tools::isSubmit('addCustomTab') || (Tools::isSubmit('id_customtab') && $this->customtabExists((int) Tools::getValue('id_customtab')))) {
            return $this->renderAddForm();
        } elseif (Tools::isSubmit('submitnrtcustomtabModule')) {
            $this->_postProcess2();

            $this->context->smarty->assign('module_dir', $this->_path);

            $this->_html .= $this->renderForm() . $this->renderList();
        } else {

            $this->context->smarty->assign('module_dir', $this->_path);

            $this->_html .= $this->renderForm() . $this->renderList();
        }
        return $this->_html;
    }

    private function _postValidation()
    {
        $errors = array();

        /* Validation for customtab */
        if (Tools::isSubmit('submitCustomTab')) {
            /* If edit : checks id_customtab */
            if (Tools::isSubmit('id_customtab')) {
                if (!Validate::isInt(Tools::getValue('id_customtab')) && !$this->customtabExists(Tools::getValue('id_customtab'))) {
                    $errors[] = $this->l('Invalid id_customtab');
                }

            }
			
            /* Checks title/description for default lang */
            if (Tools::strlen(Tools::getValue('title_bo')) == 0) {
                $errors[] = $this->l('The Title BackOffice is not set.');
            }
			
            /* Checks title/description for default lang */
            if (Tools::strlen(Tools::getValue('title_bo')) > 255) {
                $errors[] = $this->l('The Title BackOffice is too long.');
            }
			
            /* Checks title/description/*/
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                if (Tools::strlen(Tools::getValue('title_' . $language['id_lang'])) > 255) {
                    $errors[] = $this->l('The title is too long.');
                }

            }

            /* Checks title/description for default lang */
            $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
            if (Tools::strlen(Tools::getValue('title_' . $id_lang_default)) == 0) {
                $errors[] = $this->l('The title is not set.');
            }
				
            /* Checks title/description for default lang */
            $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
            if (Tools::strlen(Tools::getValue('description_' . $id_lang_default)) == 0) {
                $errors[] = $this->l('The description is not set.');
            }

        }
        /* Validation for deletion */
        elseif (Tools::isSubmit('delete_id_customtab') && (!Validate::isInt(Tools::getValue('delete_id_customtab')) || !$this->customtabExists((int) Tools::getValue('delete_id_customtab')))) {
            $errors[] = $this->l('Invalid id_customtab');
        }

        /* Display errors if needed */
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));

            return false;
        }

        /* Returns if validation is ok */

        return true;
    }

    private function _postProcess()
    {
        $errors = array();

        /* Processes customtab */
        if (Tools::isSubmit('submitCustomTab')) {
            /* Sets ID if needed */
            if (Tools::getValue('id_customtab')) {
                $customtab = new CustomTabModel((int) Tools::getValue('id_customtab'));
                if (!Validate::isLoadedObject($customtab)) {
                    $this->_html .= $this->displayError($this->l('Invalid id_customtab'));

                    return false;
                }
            } else {
                $customtab = new CustomTabModel();
            }

            $customtab->active = 1;
			$customtab->title_bo = Tools::getValue('title_bo');

            /* Sets each langue fields */
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $customtab->title[$language['id_lang']] = Tools::getValue('title_' . $language['id_lang']);
                $customtab->description[$language['id_lang']] = Tools::getValue('description_' . $language['id_lang']);

            }

            /* Processes if no errors  */
            if (!$errors) {
                /* Adds */
                if (!Tools::getValue('id_customtab')) {
                    if (!$customtab->add()) {
                        $errors[] = $this->displayError($this->l('The customtab could not be added.'));
                    }

                }
                /* Update */
                elseif (!$customtab->update()) {
                    $errors[] = $this->displayError($this->l('The customtab could not be updated.'));
                }
            }
        } /* Deletes */
        elseif (Tools::isSubmit('delete_id_customtab')) {
            $customtab = new CustomTabModel((int) Tools::getValue('delete_id_customtab'));
            $res = $customtab->delete();
            if (!$res) {
                $this->_html .= $this->displayError('Could not delete.');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=1&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }

        }

        /* Display errors if needed */
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
        } elseif (Tools::isSubmit('submitCustomTab') && Tools::getValue('id_customtab')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        } elseif (Tools::isSubmit('submitCustomTab')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=3&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }

    }

    public function renderList()
    {
        $customtabs = $this->getCustomTabs();

        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
                'customtabs' => $customtabs,
            )
        );

        return $this->display(__FILE__, 'list.tpl');
    }

    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('CustomTab informations'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title Back Office'),
                        'name' => 'title_bo',
						'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title Front'),
                        'name' => 'title',
                        'lang' => true,
						'required' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
						'required' => true,
                    )
                ),
			   'buttons' => array(
					'cancelBlock' => array(
						'title' => $this->trans('Cancel', array(), 'Admin.Actions'),
						'href' => (Tools::safeOutput(Tools::getValue('back', false)))
									?: $this->context->link->getAdminLink('AdminNrtCustomTab'),
						'icon' => 'process-icon-cancel'
					)
				),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (Tools::isSubmit('id_customtab') && $this->customtabExists((int) Tools::getValue('id_customtab'))) {
            $customtab = new CustomTabModel((int) Tools::getValue('id_customtab'));
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_customtab');
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCustomTab';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code,
            ),
            'fields_value' => $this->getAddNrtsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'module_path' => $this->_path,
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    public function getCustomTabs($active = null)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_lang = $this->context->language->id;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_nrtcustomtab_detail` as id_customtab, hss.`title_bo`, hssl.`title`, hssl.`description`
			FROM ' . _DB_PREFIX_ . 'nrtcustomtab hs
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtcustomtab_detail hss ON (hs.id_nrtcustomtab_detail = hss.id_nrtcustomtab_detail)
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtcustomtab_detail_lang hssl ON (hss.id_nrtcustomtab_detail = hssl.id_nrtcustomtab_detail)
			WHERE id_shop = ' . (int) $id_shop . '
			AND hssl.id_lang = ' . (int) $id_lang
        );
    }

    public function getAddNrtsValues()
    {
        $fields = array();

        if (Tools::isSubmit('id_customtab') && $this->customtabExists((int) Tools::getValue('id_customtab'))) {
            $customtab = new CustomTabModel((int) Tools::getValue('id_customtab'));
            $fields['id_customtab'] = (int) Tools::getValue('id_customtab', $customtab->id);
        } else {
            $customtab = new CustomTabModel();
        }

        $languages = Language::getLanguages(false);
 		$fields['title_bo'] = Tools::getValue('title_bo', $customtab->title_bo);
		
        foreach ($languages as $lang) {
            $fields['title'][$lang['id_lang']] = Tools::getValue('title_' . (int) $lang['id_lang'], isset($customtab->title[$lang['id_lang']]) ? $customtab->title[$lang['id_lang']] : '');
            $fields['description'][$lang['id_lang']] = Tools::getValue('description_' . (int) $lang['id_lang'], isset($customtab->description[$lang['id_lang']]) ? $customtab->description[$lang['id_lang']] : '');
        }

        return $fields;
    }

    public function customtabExists($id_customtab)
    {
        $req = 'SELECT hs.`id_nrtcustomtab_detail` as id_customtab
				FROM `' . _DB_PREFIX_ . 'nrtcustomtab` hs
				WHERE hs.`id_nrtcustomtab_detail` = ' . (int) $id_customtab;
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

        return ($row);
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitnrtcustomtabModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'module_path' => $this->_path,
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }
    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title global'),
                        'name' => 'title_global',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Global Custom Tab'),
                        'name' => 'content_global',
                        'autoload_rte' => true,
                        'lang' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show global Custom Tab'),
                        'name' => 'show_global',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $var = array();

        foreach ($this->defaults as $default => $value) {

            if ($default == 'title_global' || $default == 'content_global') {
                foreach (Language::getLanguages(false) as $lang) {
                    $var[$default][(int) $lang['id_lang']] = Configuration::get($this->config_name . '_' . $default, (int) $lang['id_lang']);
                }
            } else {
                $var[$default] = Configuration::get($this->config_name . '_' . $default);
            }

        }
        return $var;
    }

    /**
     * Save form data.
     */
    protected function _postProcess2()
    {
        foreach ($this->defaults as $default => $value) {
            if ($default == 'title_global' || $default == 'content_global') {
                $message_trads = array();
				
				foreach (Language::getLanguages(false) as $lang) {
					$message_trads[(int) $lang['id_lang']] = Tools::getValue($default.'_'.$lang['id_lang']);
                }

                Configuration::updateValue($this->config_name . '_' . $default, $message_trads, true);
            } else{
				
				Configuration::updateValue($this->config_name . '_' . $default, Tools::getValue($default));
			}

        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (Validate::isLoadedObject($product = new Product((int)$params["id_product"]))) {
            $customtabs = $this->getCustomTabs();
            $this->context->smarty->assign(array(
                'customtabs' => $this->getCustomTabs(),
                'selectedCustomTab' =>json_decode(CustomTabModel::getProductCustomTab((int)$params["id_product"]), true),
            ));
            return $this->display(__FILE__, 'views/templates/admin/addtab.tpl');
        } else {
            return $this->displayError($this->l('You must save this product before adding tabs'));
        }
    }

    public function hookActionProductSave($params)
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_customtabs = Tools::getValue('id_nrtcustomtab');
		
        if ($id_customtabs) {
            CustomTabModel::assignProduct($id_product, $id_customtabs);
        } else {
            CustomTabModel::unassignProduct($id_product);
        }
    }
	
    public function hookActionProductDelete($params)
    {
        $id_product = (int)$params["id_product"];
        CustomTabModel::unassignProduct($id_product);
    }

    public function hookDisplayProductExtraContent($params)
    {		
        $opTabs = $this->_prepareHook($params);
		
		if(!$opTabs){
			return true;
		}
		
		$array = array();

		if(count($opTabs['customtabs'])){
			foreach($opTabs['customtabs'] as $customtab){
				$array[] = (new ProductExtraContent())
				->setTitle($customtab['title'])
				->setContent('<div class="product-description">'.$customtab['description'].'</div>');
			}
		}else{
			if($opTabs['show_global']){
				$array[] = (new ProductExtraContent())
				->setTitle($opTabs['title_global'])
				->setContent('<div class="product-description">'.$opTabs['content_global'].'</div>');
			}
		}
				
		return $array;
    }

    public function _prepareHook($params)
    {
		$id_lang = $this->context->language->id;
        $product = (int) Tools::getValue('id_product');
        $id_customtabs = CustomTabModel::getProductCustomTab((int) Tools::getValue('id_product'));
        $show_global = Configuration::get($this->config_name . '_show_global');
		$customtabs = array();

        if ($id_customtabs || $show_global) {
			if ($id_customtabs) {
				$id_customtabs=json_decode($id_customtabs, true);
				foreach($id_customtabs as $id_customtab){
					$customtabs[] = (array)(new CustomTabModel((int) $id_customtab, $this->context->language->id));
				}
			}				
			return array('show_global' => $show_global,  
						 'title_global' => Configuration::get($this->config_name . '_title_global', $id_lang),
						 'content_global' => Configuration::get($this->config_name . '_content_global', $id_lang),
						 'customtabs' => $customtabs);
        }
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
}
