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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/src/ProductVideoModel.php';

class NrtProductVideo extends Module implements WidgetInterface
{
    private $_html = '';
	public $current_link = '';
    public $config_name;
    public $defaults;
	
    public function __construct()
    {
        $this->name = 'nrtproductvideo';
		$this->tab = 'front_office_features';
        $this->version = '1.2.0';
		$this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();		
		$this->displayName = $this->l('Axon - Product Videos');
		$this->description = $this->l('Required by author: AxonVIZ.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->config_name = 'NRT_productvideo';
        $this->defaults = array(
            'show_global' => 0,
            'content_global' => 'https://www.youtube.com/watch?v=_KGExKxme7w',
        );
    }

    public function install()
    {
        return parent::install()
			&& $this->_createTab()
			&& $this->createTables()
			&& $this->setDefaults()
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionProductSave')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayProductVideoBtn');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab() && $this->dropTables() && $this->deleteDefaults();
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
        $tab->class_name = "AdminNrtProductVideo";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Products Videos";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtProductVideo');
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

    protected function createTables()
    {
        $return = true;
        $this->dropTables();
		
        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtproductvideo` (
				`id_nrtproductvideo_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_shop` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_nrtproductvideo_detail`, `id_shop`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtproductvideo_detail` (
			  `id_nrtproductvideo_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title_bo` varchar(255) NOT NULL,
			  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  PRIMARY KEY (`id_nrtproductvideo_detail`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtproductvideo_detail_lang` (
			  `id_nrtproductvideo_detail` int(10) unsigned NOT NULL,
			  `id_lang` int(10) unsigned NOT NULL,
			  `content` varchar(255) NOT NULL,
			  PRIMARY KEY (`id_nrtproductvideo_detail`,`id_lang`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');


        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtproductvideo_product` (
				`id_product` int(10) unsigned NOT NULL,
				`id_productvideo` varchar(255) NOT NULL,
				 PRIMARY KEY (`id_product`)
				) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');

        return $return;
    }

    protected function dropTables()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'nrtproductvideo`, `' . _DB_PREFIX_ . 'nrtproductvideo_detail`, `' . _DB_PREFIX_ . 'nrtproductvideo_product`, `' . _DB_PREFIX_ . 'nrtproductvideo_detail_lang`;');
    }

    public function setDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            if ($default == 'content_global') {
                $message_trads = array();
                foreach (Language::getLanguages(false) as $lang) {
                    $message_trads[(int) $lang['id_lang']] = str_replace($this->current_link, __PS_BASE_URI__, $value);
                }
                Configuration::updateValue($this->config_name . '_' . $default, $message_trads, true);
            } else {
                Configuration::updateValue($this->config_name . '_' . $default, $value);
            }

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
        if (Tools::isSubmit('submitProductVideo') || Tools::isSubmit('delete_id_productvideo')) {
            if ($this->_postValidation()) {
                $this->_postProcess();
                $this->_html .= $this->renderForm();
                $this->_html .= $this->renderList();
            } else {
                $this->_html .= $this->renderAddForm();
            }

        } elseif (Tools::isSubmit('addProductVideo') || (Tools::isSubmit('id_productvideo') && $this->productvideoExists((int) Tools::getValue('id_productvideo')))) {
            return $this->renderAddForm();
        } elseif (Tools::isSubmit('submitnrtproductvideoModule')) {
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

        /* Validation for productvideo */
        if (Tools::isSubmit('submitProductVideo')) {
            /* If edit : checks id_productvideo */
            if (Tools::isSubmit('id_productvideo')) {
                if (!Validate::isInt(Tools::getValue('id_productvideo')) && !$this->productvideoExists(Tools::getValue('id_productvideo'))) {
                    $errors[] = $this->l('Invalid id_productvideo');
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
									
            /* Checks title/description for default lang */
            $id_lang_default = (int) Configuration::get('PS_LANG_DEFAULT');
            if (Tools::strlen(Tools::getValue('content_' . $id_lang_default)) == 0) {
                $errors[] = $this->l('The url is not set.');
            }

        }
        /* Validation for deletion */
        elseif (Tools::isSubmit('delete_id_productvideo') && (!Validate::isInt(Tools::getValue('delete_id_productvideo')) || !$this->productvideoExists((int) Tools::getValue('delete_id_productvideo')))) {
            $errors[] = $this->l('Invalid id_productvideo');
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

        /* Processes productvideo */
        if (Tools::isSubmit('submitProductVideo')) {
            /* Sets ID if needed */
            if (Tools::getValue('id_productvideo')) {
                $productvideo = new ProductVideoModel((int) Tools::getValue('id_productvideo'));
                if (!Validate::isLoadedObject($productvideo)) {
                    $this->_html .= $this->displayError($this->l('Invalid id_productvideo'));

                    return false;
                }
            } else {
                $productvideo = new ProductVideoModel();
            }

            $productvideo->active = 1;
			$productvideo->title_bo = Tools::getValue('title_bo');

            /* Sets each langue fields */
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $productvideo->content[$language['id_lang']] = Tools::getValue('content_' . $language['id_lang']);

            }

            /* Processes if no errors  */
            if (!$errors) {
                /* Adds */
                if (!Tools::getValue('id_productvideo')) {
                    if (!$productvideo->add()) {
                        $errors[] = $this->displayError($this->l('The productvideo could not be added.'));
                    }

                }
                /* Update */
                elseif (!$productvideo->update()) {
                    $errors[] = $this->displayError($this->l('The productvideo could not be updated.'));
                }
            }
        } /* Deletes */
        elseif (Tools::isSubmit('delete_id_productvideo')) {
            $productvideo = new ProductVideoModel((int) Tools::getValue('delete_id_productvideo'));
            $res = $productvideo->delete();
            if (!$res) {
                $this->_html .= $this->displayError('Could not delete.');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }

        }

        /* Display errors if needed */
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
        } elseif (Tools::isSubmit('submitProductVideo') && Tools::getValue('id_productvideo')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        } elseif (Tools::isSubmit('submitProductVideo')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }

    }

    public function renderList()
    {
        $productvideos = $this->getProductsVideos();

        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
                'productvideos' => $productvideos,
            )
        );

        return $this->display(__FILE__, 'list.tpl');
    }

    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('ProductVideo informations'),
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
                        'label' => $this->l('Url'),
                        'name' => 'content',
                        'lang' => true,
						'required' => true,
                        'desc' => $this->l('Eg: https://www.youtube.com/watch?v=_KGExKxme7w'),
                    )
                ),
			   'buttons' => array(
					'cancelBlock' => array(
						'title' => $this->trans('Cancel', array(), 'Admin.Actions'),
						'href' => (Tools::safeOutput(Tools::getValue('back', false)))
									?: $this->context->link->getAdminLink('AdminNrtProductVideo'),
						'icon' => 'process-icon-cancel'
					)
				),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (Tools::isSubmit('id_productvideo') && $this->productvideoExists((int) Tools::getValue('id_productvideo'))) {
            $productvideo = new ProductVideoModel((int) Tools::getValue('id_productvideo'));
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_productvideo');
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductVideo';
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

    public function getProductsVideos($active = null)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_lang = $this->context->language->id;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_nrtproductvideo_detail` as id_productvideo, hss.`title_bo`, hssl.`content`
			FROM ' . _DB_PREFIX_ . 'nrtproductvideo hs
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtproductvideo_detail hss ON (hs.id_nrtproductvideo_detail = hss.id_nrtproductvideo_detail)
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtproductvideo_detail_lang hssl ON (hss.id_nrtproductvideo_detail = hssl.id_nrtproductvideo_detail)
			WHERE id_shop = ' . (int) $id_shop . '
			AND hssl.id_lang = ' . (int) $id_lang
        );
    }

    public function getAddNrtsValues()
    {
        $fields = array();

        if (Tools::isSubmit('id_productvideo') && $this->productvideoExists((int) Tools::getValue('id_productvideo'))) {
            $productvideo = new ProductVideoModel((int) Tools::getValue('id_productvideo'));
            $fields['id_productvideo'] = (int) Tools::getValue('id_productvideo', $productvideo->id);
        } else {
            $productvideo = new ProductVideoModel();
        }

        $languages = Language::getLanguages(false);
 		$fields['title_bo'] = Tools::getValue('title_bo', $productvideo->title_bo);
		
        foreach ($languages as $lang) {
            $fields['content'][$lang['id_lang']] = Tools::getValue('content_' . (int) $lang['id_lang'], isset($productvideo->content[$lang['id_lang']]) ? $productvideo->content[$lang['id_lang']] : '');
        }

        return $fields;
    }

    public function productvideoExists($id_productvideo)
    {
        $req = 'SELECT hs.`id_nrtproductvideo_detail` as id_productvideo
				FROM `' . _DB_PREFIX_ . 'nrtproductvideo` hs
				WHERE hs.`id_nrtproductvideo_detail` = ' . (int) $id_productvideo;
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
        $helper->submit_action = 'submitnrtproductvideoModule';
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
                        'label' => $this->l('Url global'),
                        'name' => 'content_global',
                        'lang' => true,
                        'desc' => $this->l('Eg: https://www.youtube.com/watch?v=_KGExKxme7w'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show global'),
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

            if ($default == 'content_global') {
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
            if ($default == 'content_global') {
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
            $productvideos = $this->getProductsVideos();
            $this->context->smarty->assign(array(
                'productvideos' => $this->getProductsVideos(),
                'selectedProductVideo' =>json_decode(ProductVideoModel::getProductVideos((int)$params["id_product"]), true),
            ));
            return $this->display(__FILE__, 'views/templates/admin/addtab.tpl');
        } else {
            return $this->displayError($this->l('You must save this product before adding tabs'));
        }
    }

    public function hookActionProductSave($params)
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_productvideos = Tools::getValue('id_nrtproductvideo');

        if (!isset($id_productvideos[0])) {
            return;
        }
		
        if ($id_productvideos[0]) {
            ProductVideoModel::assignProduct($id_product, $id_productvideos);
        } else {
            ProductVideoModel::unassignProduct($id_product);
        }
    }
	
    public function hookActionProductDelete($params)
    {
        $id_product = (int)$params["id_product"];
        ProductVideoModel::unassignProduct($id_product);
    }
	
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if (!isset($configuration['smarty']->tpl_vars['product']->value['id_product'])) {
            return;
        }

        if (preg_match('/^displayProductVideoBtn\d*$/', $hookName)) {
			
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-btn.tpl';
			
			$cacheId = 'btnVideos|'.$configuration['smarty']->tpl_vars['product']->value['id_product'];
			
			if (!$this->isCached($templateFile, $this->getCacheId($cacheId))) {
				$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
			}

			return $this->fetch($templateFile, $this->getCacheId($cacheId));
			
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
		$id_lang = $this->context->language->id;
        $id_product = (int) $configuration['smarty']->tpl_vars['product']->value['id_product'];
			
        $id_productvideos = ProductVideoModel::getProductVideos($id_product);
        $show_global = Configuration::get($this->config_name . '_show_global');
		$productvideos = array();

        if ($id_productvideos || $show_global) {
			if ($id_productvideos) {
				$id_productvideos = json_decode($id_productvideos, true);
				foreach($id_productvideos as $id_productvideo){					
					$obj = (array)(new ProductVideoModel((int) $id_productvideo, $this->context->language->id));
					if(trim($obj['content'])){
						$productvideos[] = array('url' => trim($obj['content']));
					}
				}
			}else{
				if(trim(Configuration::get($this->config_name . '_content_global', $id_lang))){
					$productvideos[] = array('url' => trim(Configuration::get($this->config_name . '_content_global', $id_lang)));
				}
			}				
        }
		
		return array('productvideos' => $productvideos);
    }
	
}
