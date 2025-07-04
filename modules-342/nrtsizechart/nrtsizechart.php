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

require_once dirname(__FILE__).'/src/SizeGuideModel.php';

class NrtSizeChart extends Module implements WidgetInterface
{
    private $_html = '';
	private $templateFile;
    protected $config_name;
    protected $defaults;
	
    public function __construct()
    {
        $this->name = 'nrtsizechart';
		$this->tab = 'front_office_features';
        $this->version = '2.1.1';
		$this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();		
        $this->displayName = $this->l('Axon - Size Guide Chart');
        $this->description = $this->l('Show popup with size guide');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
		
        $this->config_name = 'NRTSIZECHART';
        $this->defaults = array(
            'width' => 630,
            'height' => 650,
            'sh_measure' => 0,
            'sh_global' => 1,
            'content' => 'Content of newsletter popup',
            'global' => 'Content of newsletter popup',
        );
		
		$this->templateFile = 'module:nrtsizechart/views/templates/hook/sizechart.tpl';
		
    }

    public function install()
    {
		return parent::install()
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionProductSave')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductSizeGuide')
            && $this->registerHook('displayHeader')
            && $this->_createTab()
            && $this->createTables()
            && $this->setDefaults()
            && $this->generateCss();
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
        $tab->class_name = "AdminNrtSizechart";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- SizeChart";
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
        $id_tab = Tab::getIdFromClassName('AdminNrtSizechart');
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
        /* guides */
        $res = (bool) Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtsizechart` (
				`id_nrtsizechart_guides` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_shop` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id_nrtsizechart_guides`, `id_shop`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');

        /* guides configuration */
        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtsizechart_guides` (
			  `id_nrtsizechart_guides` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			  PRIMARY KEY (`id_nrtsizechart_guides`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');

        /* guides lang configuration */
        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtsizechart_guides_lang` (
			  `id_nrtsizechart_guides` int(10) unsigned NOT NULL,
			  `id_lang` int(10) unsigned NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  PRIMARY KEY (`id_nrtsizechart_guides`,`id_lang`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');

        /* guides product association */
        $res &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrtsizechart_product` (
				`id_product` int(10) unsigned NOT NULL,
				`id_guide` int(10) unsigned NOT NULL,
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
        $guides = $this->getGuides();
        foreach ($guides as $guide) {
            $to_del = new SizeGuideModel($guide['id_guide']);
            $to_del->delete();
        }

        return Db::getInstance()->execute('
			DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'nrtsizechart`, `' . _DB_PREFIX_ . 'nrtsizechart_guides`, `' . _DB_PREFIX_ . 'nrtsizechart_product`, `' . _DB_PREFIX_ . 'nrtsizechart_guides_lang`;
		');
    }

    public function setDefaults()
    {
        $response = true;

        foreach ($this->defaults as $default => $value) {
            if ($default == 'content') {
                $message_trads = array();
                foreach (Language::getLanguages(false) as $lang) {
                    $message_trads[(int) $lang['id_lang']] = '';
                }

                $response &= Configuration::updateValue($this->config_name . '_' . $default, $message_trads, true);
            } elseif ($default == 'global') {
                $message_trads = array();
                foreach (Language::getLanguages(false) as $lang) {
                    $message_trads[(int) $lang['id_lang']] = '<table class="table table-striped"><thead><tr><th>Size</th><th>XS</th><th>S</th><th>M</th><th>L</th></tr></thead><tbody><tr><th scope="row">Euro</th><td>32/34</td><td>36</td><td>38</td><td>40</td></tr><tr><th scope="row">USA</th><td>0/2</td><td>4</td><td>6</td><td>8</td></tr><tr><th scope="row">Bust(in)</th><td>31-32</td><td>33</td><td>34</td><td>36</td></tr><tr><th scope="row">Bust(cm)</th><td>80.5-82.5</td><td>84.5</td><td>87</td><td>92</td></tr><tr><th scope="row">Waist(in)</th><td>24-25</td><td>26</td><td>27</td><td>29</td></tr><tr><th scope="row">Waist(cm)</th><td>62.5-64.5</td><td>66.5</td><td>69</td><td>74</td></tr><tr><th scope="row">Hips(in)</th><td>34-35</td><td>36</td><td>37</td><td>39</td></tr><tr><th scope="row">Hips(cm)</th><td>87.5-89.5</td><td>91.5</td><td>94</td><td>99</td></tr></tbody></table><div class="font-weight-bold">How To Measure Your Bust</div><p>With your arms relaxed at your sides, measure around the fullest part of your chest.</p><div class="font-weight-bold">How To Measure Your Waist</div><p>Measure around the narrowest part of your natural waist, generally around the belly button. To ensure a comfortable fit, keep one finger between the measuring tape and your body.</p>';
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
        if (Tools::isSubmit('submitGuide') || Tools::isSubmit('delete_id_guide')) {
            if ($this->_postValidation()) {
                $this->_postProcess();
                $this->_html .= $this->renderForm();
                $this->_html .= $this->renderList();
            } else {
                $this->_html .= $this->renderAddForm();
            }

        } elseif (Tools::isSubmit('addGuide') || (Tools::isSubmit('id_guide') && $this->guideExists((int) Tools::getValue('id_guide')))) {
            return $this->renderAddForm();
        } elseif (Tools::isSubmit('submitnrtsizechartModule')) {
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

        /* Validation for guide */
        if (Tools::isSubmit('submitGuide')) {
            /* If edit : checks id_guide */
            if (Tools::isSubmit('id_guide')) {
                if (!Validate::isInt(Tools::getValue('id_guide')) && !$this->guideExists(Tools::getValue('id_guide'))) {
                    $errors[] = $this->l('Invalid id_guide');
                }

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
        elseif (Tools::isSubmit('delete_id_guide') && (!Validate::isInt(Tools::getValue('delete_id_guide')) || !$this->guideExists((int) Tools::getValue('delete_id_guide')))) {
            $errors[] = $this->l('Invalid id_guide');
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

        /* Processes guide */
        if (Tools::isSubmit('submitGuide')) {
            /* Sets ID if needed */
            if (Tools::getValue('id_guide')) {
                $guide = new SizeGuideModel((int) Tools::getValue('id_guide'));
                if (!Validate::isLoadedObject($guide)) {
                    $this->_html .= $this->displayError($this->l('Invalid id_guide'));

                    return false;
                }
            } else {
                $guide = new SizeGuideModel();
            }

            $guide->active = 1;

            /* Sets each langue fields */
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $guide->title[$language['id_lang']] = Tools::getValue('title_' . $language['id_lang']);
                $guide->description[$language['id_lang']] = Tools::getValue('description_' . $language['id_lang']);

            }

            /* Processes if no errors  */
            if (!$errors) {
                /* Adds */
                if (!Tools::getValue('id_guide')) {
                    if (!$guide->add()) {
                        $errors[] = $this->displayError($this->l('The guide could not be added.'));
                    }

                }
                /* Update */
                elseif (!$guide->update()) {
                    $errors[] = $this->displayError($this->l('The guide could not be updated.'));
                }

                $this->clearCache();
            }
        } /* Deletes */
        elseif (Tools::isSubmit('delete_id_guide')) {
            $guide = new SizeGuideModel((int) Tools::getValue('delete_id_guide'));
            $res = $guide->delete();
            $this->clearCache();
            if (!$res) {
                $this->_html .= $this->displayError('Could not delete.');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=1&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }

        }

        /* Display errors if needed */
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
        } elseif (Tools::isSubmit('submitGuide') && Tools::getValue('id_guide')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        } elseif (Tools::isSubmit('submitGuide')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=3&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }

    }

    public function renderList()
    {
        $guides = $this->getGuides();

        $this->context->smarty->assign(
            array(
                'link' => $this->context->link,
                'guides' => $guides,
            )
        );

        return $this->display(__FILE__, 'list.tpl');
    }

    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Guide informations'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'title',
						'required' => true,
                        'lang' => true,
                    ),

                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'name' => 'description',
						'required' => true,
                        'autoload_rte' => true,
                        'lang' => true,
                    )
                ),
			   'buttons' => array(
					'cancelBlock' => array(
						'title' => $this->trans('Cancel', array(), 'Admin.Actions'),
						'href' => (Tools::safeOutput(Tools::getValue('back', false)))
									?: $this->context->link->getAdminLink('AdminNrtSizechart'),
						'icon' => 'process-icon-cancel'
					)
				),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        if (Tools::isSubmit('id_guide') && $this->guideExists((int) Tools::getValue('id_guide'))) {
            $guide = new SizeGuideModel((int) Tools::getValue('id_guide'));
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_guide');
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGuide';
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
            'module_path' => $this->_path
        );

        $helper->override_folder = '/';

        return $helper->generateForm(array($fields_form));
    }

    public function getGuides($active = null)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_lang = $this->context->language->id;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_nrtsizechart_guides` as id_guide, hssl.`title`, hssl.`description`
			FROM ' . _DB_PREFIX_ . 'nrtsizechart hs
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtsizechart_guides hss ON (hs.id_nrtsizechart_guides = hss.id_nrtsizechart_guides)
			LEFT JOIN ' . _DB_PREFIX_ . 'nrtsizechart_guides_lang hssl ON (hss.id_nrtsizechart_guides = hssl.id_nrtsizechart_guides)
			WHERE id_shop = ' . (int) $id_shop . '
			AND hssl.id_lang = ' . (int) $id_lang
        );
    }

    public function getAddNrtsValues()
    {
        $fields = array();

        if (Tools::isSubmit('id_guide') && $this->guideExists((int) Tools::getValue('id_guide'))) {
            $guide = new SizeGuideModel((int) Tools::getValue('id_guide'));
            $fields['id_guide'] = (int) Tools::getValue('id_guide', $guide->id);
        } else {
            $guide = new SizeGuideModel();
        }

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields['title'][$lang['id_lang']] = Tools::getValue('title_' . (int) $lang['id_lang'], isset($guide->title[$lang['id_lang']]) ? $guide->title[$lang['id_lang']] : '');
            $fields['description'][$lang['id_lang']] = Tools::getValue('description_' . (int) $lang['id_lang'], isset($guide->description[$lang['id_lang']]) ? $guide->description[$lang['id_lang']] : '');
        }

        return $fields;
    }

    public function guideExists($id_guide)
    {
        $req = 'SELECT hs.`id_nrtsizechart_guides` as id_guide
				FROM `' . _DB_PREFIX_ . 'nrtsizechart` hs
				WHERE hs.`id_nrtsizechart_guides` = ' . (int) $id_guide;
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
        $helper->submit_action = 'submitnrtsizechartModule';
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
    public function getAttributes()
    {
        $attributes = AttributeGroup::getAttributesGroups($this->context->language->id);

        $selectAttributes = array();

        foreach ($attributes as $attribute) {
            $selectAttributes[$attribute['id_attribute_group']]['id_option'] = $attribute['id_attribute_group'];
            $selectAttributes[$attribute['id_attribute_group']]['name'] = $attribute['name'];
        }

        return $selectAttributes;
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
                        'label' => $this->l('Width'),
                        'name' => 'width',
                        'suffix' => 'px',
                        'desc' => $this->l('Popup window width.'),
                        'size' => 20,
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Height of main content'),
                        'name' => 'height',
                        'suffix' => 'px',
                        'desc' => $this->l('Popup window height.'),
                        'size' => 20,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show how to measure tab'),
                        'name' => 'sh_measure',
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
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('How to measure tab'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'lang' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show global size guide'),
                        'name' => 'sh_global',
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
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Global size guide'),
                        'name' => 'global',
                        'autoload_rte' => true,
                        'lang' => true,
                        'cols' => 60,
                        'rows' => 30,
                    ),
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

            if ($default == 'content' || $default == 'global') {
                foreach (Language::getLanguages(false) as $lang) {
                    $var[$default][(int) $lang['id_lang']] = html_entity_decode(Configuration::get($this->config_name . '_' . $default, (int) $lang['id_lang']));
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
            if ($default == 'content' || $default == 'global') {
                $message_trads = array();
				
				foreach (Language::getLanguages(false) as $lang) {
					$message_trads[(int) $lang['id_lang']] = Tools::getValue($default.'_'.$lang['id_lang']);
                }

                Configuration::updateValue($this->config_name . '_' . $default, $message_trads, true);
            } else {
                Configuration::updateValue($this->config_name . '_' . $default, Tools::getValue($default));
            }

        }
        $this->clearCache();
        $this->generateCss();
    }

    public function clearCache()
    {
        $this->_clearCache($this->templateFile);
    }

    public function generateCss()
    {
        $css = '';
        $css .= '#moda_sizechart .modal-dialog{ max-width: ' . (int) Configuration::get($this->config_name . '_width') . 'px;}';
        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $my_file = $this->local_path . 'views/css/sizechart_g_' . (int) $this->context->shop->getContextShopGroupID() . '.css';
        } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $my_file = $this->local_path . 'views/css/sizechart_s_' . (int) $this->context->shop->getContextShopID() . '.css';
        }else{
			$my_file = $this->local_path . 'views/css/sizechart.css';
		}	
		if(file_put_contents($my_file, $css)){
			return true;
		}else{
			return false;
		}
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        if ($this->context->controller->php_self == 'product') {
            $this->context->controller->addCSS($this->_path . 'views/css/front.css');
            if (Shop::getContext() == Shop::CONTEXT_GROUP) {
                $this->context->controller->addCSS(($this->_path) . 'views/css/sizechart_g_' . (int) $this->context->shop->getContextShopGroupID() . '.css', 'all');
            } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $this->context->controller->addCSS(($this->_path) . 'views/css/sizechart_s_' . (int) $this->context->shop->getContextShopID() . '.css', 'all');
            }else{
				$this->context->controller->addCSS(($this->_path) . 'views/css/sizechart.css', 'all');
			}
            
            $id_guide = SizeGuideModel::getProductGuide((int) Tools::getValue('id_product'));
            $sh_global = Configuration::get($this->config_name . '_sh_global');
    
            if ($id_guide || $sh_global) {
			    $this->smarty->assignGlobal('has_sizeguide', true);
            }
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {

        if (Validate::isLoadedObject($product = new Product((int)$params["id_product"]))) {
            $guides = $this->getGuides();

            $this->context->smarty->assign(array(
                'guides' => $this->getGuides(),
                'selectedGuide' => (int) SizeGuideModel::getProductGuide((int)$params["id_product"]),
            ));
            return $this->display(__FILE__, 'views/templates/admin/addtab.tpl');
        } else {
            return $this->displayError($this->l('You must save this product before adding tabs'));
        }
    }

    public function hookdisplayProductListReviews($params)
    {
        $prodtid = (int) $params['product']['id_product'];

        return $this->hookdisplayProductAttributesPL($prodtid);

    }
    public function hookActionProductSave($params)
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_guide = (int) Tools::getValue('id_nrtsizechart');

        if (!isset($id_guide)) {
            return;
        }

        if ($id_guide) {
            SizeGuideModel::assignProduct($id_product, $id_guide);
        } else {
            SizeGuideModel::unassignProduct($id_product);
        }

    }
    public function hookActionProductDelete($params)
    {
        $id_product = (int)$params["id_product"];
        SizeGuideModel::unassignProduct($id_product);
    }

    public function _prepareHook($params)
    {
        $id_guide = SizeGuideModel::getProductGuide((int) Tools::getValue('id_product'));
        $sh_global = Configuration::get($this->config_name . '_sh_global');

        if ($id_guide || $sh_global) {
            if ($id_guide) {
                $guide = new SizeGuideModel((int) $id_guide, $this->context->language->id);
                $cache_id = 'nrtsizechart|' . (int) $id_guide;
            } else {
                $cache_id = 'nrtsizechart';
            }
			
            if (!$this->isCached($this->templateFile, $this->getCacheId($cache_id))) {
                if ($id_guide) {
                    $this->smarty->assign(
                        array(
                            'guide' => $guide,
                        )
                    );
                }

                $this->smarty->assign(
                    array(
                        'howto' =>html_entity_decode(Configuration::get($this->config_name . '_content', $this->context->language->id)),
                        'sh_measure' => Configuration::get($this->config_name . '_sh_measure'),
                        'sh_global' => $sh_global,  
                        'global' =>html_entity_decode(Configuration::get($this->config_name . '_global', $this->context->language->id)),
                    )

                );

            }
			
            return $this->fetch($this->templateFile, $this->getCacheId($cache_id));
        }
    }

    public function hookDisplayProductSizeGuide($params)
    {
        if ($this->context->controller->php_self != 'product') {
            return;
        }

        return $this->_prepareHook($params);
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
}
