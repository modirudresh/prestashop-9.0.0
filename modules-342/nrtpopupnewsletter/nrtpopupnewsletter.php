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

class NrtPopupNewsletter extends Module implements WidgetInterface
{
    const GUEST_NOT_REGISTERED = -1;
    const CUSTOMER_NOT_REGISTERED = 0;
    const GUEST_REGISTERED = 1;
    const CUSTOMER_REGISTERED = 2;

    protected $templateFile;

    function __construct()
    {
		$this->name = 'nrtpopupnewsletter';
		$this->tab = 'front_office_features';
		$this->version = '2.1.6';
		$this->author = 'AxonVIZ';
		
		$this->bootstrap = true;
		parent::__construct();	

		$this->displayName = $this->l('Axon - Popup Newsletter');
		$this->description = $this->l('Shows popup newsletter window with your message');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

		$this->templateFile = 'module:nrtpopupnewsletter/views/templates/hook/popup-' . Configuration::get('NRT_NEWSLETTER_LAYOUT') . '.tpl';
	}

	public function install()
	{		
		$title = array();
		$content = array();
		
		foreach (Language::getLanguages() as $lang) {
		  $title[(int)$lang['id_lang']] = '<p>NEWSLETTER</p>';
		  $content[(int)$lang['id_lang']] = '<p>Sign up to our newsletter and get exclusive deals you won find any where else straight to your inbox!</p>';
		}
                		
		if (parent::install() && 
			$this->_createTab() &&
			$this->registerHook('displayBeforeBodyClosingTag') && 
			$this->registerHook('registerGDPRConsent') && 
			$this->registerHook('displayHeader') &&
			Configuration::updateValue('NRT_NEWSLETTER_TEXT_COLOR', 'dark') &&
			Configuration::updateValue('NRT_NEWSLETTER_LAYOUT', 3) &&
			Configuration::updateValue('NRT_NEWSLETTER', true) &&
			Configuration::updateValue('NRT_NEWSLETTER_PAGES', true) &&
			Configuration::updateValue('NRT_TITLE', $title, true) &&
			Configuration::updateValue('NRT_TEXT', $content, true) &&
			Configuration::updateValue('NRT_NEWSLETTER_FORM', true) &&
			Configuration::updateValue('NRT_BG', false) &&
			Configuration::updateValue('NRT_BG_IMAGE', _MODULE_DIR_.$this->name.'/img/background_image1.jpg') && Configuration::updateValue('NRT_COUNTDOWN_POPUP', 3000) &&
			Configuration::updateValue('NRT_COUNTDOWN_POPUP_START', '0000-00-00 00:00:00'))
			{
				return true;
			}
		return false;
	}
	
	public function uninstall()
	{
		return 
			$this->_deleteTab() &&
			Configuration::deleteByName('NRT_NEWSLETTER_TEXT_COLOR') &&
			Configuration::deleteByName('NRT_NEWSLETTER_LAYOUT') &&
			Configuration::deleteByName('NRT_NEWSLETTER') &&
			Configuration::deleteByName('NRT_NEWSLETTER_PAGES') &&
			Configuration::deleteByName('NRT_TITLE') &&	
			Configuration::deleteByName('NRT_TEXT') &&	
			Configuration::deleteByName('NRT_NEWSLETTER_FORM') &&
			Configuration::deleteByName('NRT_BG') &&
			Configuration::deleteByName('NRT_BG_IMAGE') &&
			Configuration::deleteByName('NRT_COUNTDOWN_POPUP') &&
			Configuration::deleteByName('NRT_COUNTDOWN_POPUP_START') &&
			parent::uninstall();
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
		$tab->class_name = "AdminPopupNewsletter";
		$tab->name = array();
		foreach (Language::getLanguages() as $lang) {
			$tab->name[$lang['id_lang']] = "- Popup newsletter";
		}
		$tab->id_parent = $parentTab_2->id;
		$tab->module = $this->name;
		$response &= $tab->add();

		return $response;
	}

	/* ------------------------------------------------------------- */
	/*  DELETE THE TAB MENU
	/* ------------------------------------------------------------- */
	public function _deleteTab()
	{
		$id_tab = Tab::getIdFromClassName('AdminPopupNewsletter');
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

	public function getContent()
	{

		$this->context->controller->getLanguages();
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $errors = array();
                
		if (Tools::isSubmit('nrt_submit')) {
			$this->registerHook('registerGDPRConsent');
			
			Configuration::updateValue('NRT_NEWSLETTER_TEXT_COLOR', Tools::getValue('NRT_NEWSLETTER_TEXT_COLOR'));
			Configuration::updateValue('NRT_NEWSLETTER_LAYOUT', (int)Tools::getValue('NRT_NEWSLETTER_LAYOUT'));
			Configuration::updateValue('NRT_NEWSLETTER', (bool)Tools::getValue('NRT_NEWSLETTER'));
			Configuration::updateValue('NRT_NEWSLETTER_PAGES', (bool)Tools::getValue('NRT_NEWSLETTER_PAGES'));
			Configuration::updateValue('NRT_NEWSLETTER_FORM', (bool)Tools::getValue('NRT_NEWSLETTER_FORM'));
			Configuration::updateValue('NRT_BG', Tools::getValue('NRT_BG'));
			if (Tools::isSubmit('NRT_BG_IMAGE')){
				Configuration::updateValue('NRT_BG_IMAGE', Tools::getValue('NRT_BG_IMAGE'));
			}
			$message_trads = array();
			$message_trads2 = array();
			foreach ($_POST as $key => $value){
				if (preg_match('/NRT_TITLE_/i', $key))
				{
					$id_lang = preg_split('/NRT_TITLE_/i', $key);
					$message_trads2[(int)$id_lang[1]] = $value;
				}
				if (preg_match('/NRT_TEXT_/i', $key))
				{
					$id_lang = preg_split('/NRT_TEXT_/i', $key);
					$message_trads[(int)$id_lang[1]] = $value;
				}
			}
			Configuration::updateValue('NRT_TEXT', $message_trads, true);
			Configuration::updateValue('NRT_TITLE', $message_trads2, true);
			$start = Tools::getValue('NRT_COUNTDOWN_POPUP_START');
			if (!$start) {
				$start = '0000-00-00 00:00:00';
			}
			$end = Tools::getValue('NRT_COUNTDOWN_POPUP');
			if (!$end) {
				$end = '0000-00-00 00:00:00';
			}
			if ($end != '0000-00-00 00:00:00' && strtotime($end) < strtotime($start)) {
				$errors[] = $this->l('Invalid date range');
			} else {
				Configuration::updateValue('NRT_COUNTDOWN_POPUP', Tools::getValue('NRT_COUNTDOWN_POPUP'));
				Configuration::updateValue('NRT_COUNTDOWN_POPUP_START', Tools::getValue('NRT_COUNTDOWN_POPUP_START'));
			}
			if (count($errors)){
				$output .= $this->displayError(implode('<br />', $errors));
			} else {
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}

			$this->_clearCache($this->templateFile);
		}
		return $output.$this->renderForm();
	}

	public function hookDisplayBeforeBodyClosingTag($params)
	{				
		if (!$this->isCached($this->templateFile, $this->getCacheId())) {
			$this->context->smarty->assign(array(
				'nrt_ppp' => $this->getConfigFromDB(),
				'id_module' => $this->id,
			));		
		}
		
		return $this->fetch($this->templateFile, $this->getCacheId());
	}

	public function hookDisplayHeader($params)
	{
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
		
		$this->context->controller->addJS(($this->_path).'views/js/front.min.js');
		$this->context->controller->addCSS(($this->_path).'views/css/front'.$dir_rtl.'.css');
        Media::addJsDef(array(
			'opPopUp' => array('ajax' => $this->context->link->getModuleLink('ps_emailsubscription', 'subscription', array(), null, null, null, true),
								'time_dl' => Configuration::get('NRT_COUNTDOWN_POPUP'),
								'pp_start' => !(isset($_COOKIE['has_cookiepopup']) || (!Configuration::get('NRT_NEWSLETTER_PAGES') && $this->context->controller->php_self != 'index')))
        ));
	}

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
        
	public function setMedia()
	{
		parent::setMedia();
		$this->addJqueryUI('ui.datepicker');
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Module Appearance'),
					'icon' => 'icon-cogs'
				),
				'input' => array(	
					array(
						'type' => 'switch',
						'label' => $this->l('Show Newsletter in popup'),
						'name' => 'NRT_NEWSLETTER',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Yes')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('No')
									)
					),
						),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Newsletter in All pages'),
						'name' => 'NRT_NEWSLETTER_PAGES',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Yes')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('No')
									)
								),
						),
					array(
						'type' => 'select',
						'name' => 'NRT_NEWSLETTER_LAYOUT',
						'label' => $this->l('Popup layout'),
						'class' => 'fixed-width-xxl',
						'required' => false,
						'options' => array(
							'query' => array(
									array('value'=>1,'name'=>$this->l('Type 1')),
									array('value'=>2,'name'=>$this->l('Type 2')),
									array('value'=>3,'name'=>$this->l('Type 3')),
									array('value'=>4,'name'=>$this->l('Type 4')),
								),
							'id' => 'value',
							'name' => 'name'
						)
					),
					array(
							'type' => 'textarea',
							'name' => 'NRT_TITLE',
							'label' => $this->l('Popup title'),
							'rows' => 10,
							'cols' => 40,
							'required' => false,
							'lang' => true,
							'autoload_rte' => true
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Popup content'),
						'name' => 'NRT_TEXT',
						'rows' => 10,
						'cols' => 40,
						'lang' => true,
						'autoload_rte' => true
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Newsletter form in popup'),
						'name' => 'NRT_NEWSLETTER_FORM',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Yes')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('No')
									)
								),
						),
					array(
						'type' => 'switch',
						'label' => $this->l('Show background image'),
						'name' => 'NRT_BG',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => true,
										'label' => $this->l('Yes')
									),
									array(
										'id' => 'active_off',
										'value' => false,
										'label' => $this->l('No')
									)
								),
						),
                    array(
						'type' => 'background_image',
						'label' => $this->l('Popup background image'),
						'name' => 'NRT_BG_IMAGE',
						'size' => 30,
					),
					array(
						'type' => 'select',
						'name' => 'NRT_NEWSLETTER_TEXT_COLOR',
						'label' => $this->l('Text color'),
						'class' => 'fixed-width-xxl',
						'required' => false,
						'options' => array(
							'query' => array(
									array('value'=>'dark','name'=>$this->l('Dark')),
									array('value'=>'light','name'=>$this->l('Light')),
								),
							'id' => 'value',
							'name' => 'name'
						)
					),
					array(
							'type' => 'hidden',
							'label' => $this->l('Countdown from'),
							'name' => 'NRT_COUNTDOWN_POPUP_START',
							'size' => 10,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Delays show popup'),
						'name' => 'NRT_COUNTDOWN_POPUP',
						'required' => false,
						'class' => 'fixed-width-xxl',
						'suffix' => 'million seconds'
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		

		$languages = Language::getLanguages(false);
		foreach ($languages as $k => $language){
			$languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');
		}

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->languages = $languages;
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
		$helper->allow_employee_form_lang = true;
		$helper->toolbar_scroll = true;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            )
        );
		$helper->title = $this->displayName;
		$helper->submit_action = 'nrt_submit';
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
		);
		return $helper->generateForm(array($fields_form));
	}
	
	public function getConfigFieldsValues()
	{
		$values = array(
			'NRT_NEWSLETTER_TEXT_COLOR' => Tools::getValue('NRT_NEWSLETTER_TEXT_COLOR', Configuration::get('NRT_NEWSLETTER_TEXT_COLOR')),
			'NRT_NEWSLETTER_LAYOUT' => Tools::getValue('NRT_NEWSLETTER_LAYOUT', Configuration::get('NRT_NEWSLETTER_LAYOUT')),
			'NRT_NEWSLETTER_PAGES' => Tools::getValue('NRT_NEWSLETTER_PAGES', Configuration::get('NRT_NEWSLETTER_PAGES')),
			'NRT_NEWSLETTER' => Tools::getValue('NRT_NEWSLETTER', Configuration::get('NRT_NEWSLETTER')),
			'NRT_NEWSLETTER_FORM' => Tools::getValue('NRT_NEWSLETTER_FORM', Configuration::get('NRT_NEWSLETTER_FORM')),
			'NRT_BG' => Tools::getValue('NRT_BG', Configuration::get('NRT_BG')),
			'NRT_BG_IMAGE' => Tools::getValue('NRT_BG_IMAGE', Configuration::get('NRT_BG_IMAGE')),
			'NRT_COUNTDOWN_POPUP' => Tools::getValue('NRT_COUNTDOWN_POPUP', Configuration::get('NRT_COUNTDOWN_POPUP')),
			'NRT_COUNTDOWN_POPUP_START' => Tools::getValue('NRT_COUNTDOWN_POPUP_START', Configuration::get('NRT_COUNTDOWN_POPUP_START')),
		);

		foreach (Language::getLanguages(false) as $lang){
			$values['NRT_TITLE'][(int)$lang['id_lang']] =html_entity_decode(Configuration::get('NRT_TITLE', (int)$lang['id_lang']));
			$values['NRT_TEXT'][(int)$lang['id_lang']] =html_entity_decode(Configuration::get('NRT_TEXT', (int)$lang['id_lang']));
		}
		return $values;
	}

	public function getConfigFromDB()
	{
		$now = date('Y-m-d H:i:00');
		$start_date = (Configuration::get('NRT_COUNTDOWN_POPUP_START') ? Configuration::get('NRT_COUNTDOWN_POPUP_START'): '0000-00-00 00:00:00');
		if (strtotime($start_date) > strtotime($now)){
			$end_date = "0000-00-00 00:00:00";
		} else {
			$end_date = (Configuration::get('NRT_COUNTDOWN_POPUP') ? Configuration::get('NRT_COUNTDOWN_POPUP'): '0000-00-00 00:00:00');
		}
		return array(
			'NRT_NEWSLETTER_TEXT_COLOR' => (Configuration::get('NRT_NEWSLETTER_TEXT_COLOR') ? Configuration::get('NRT_NEWSLETTER_TEXT_COLOR'): 'dark'),
			'NRT_NEWSLETTER_LAYOUT' => (Configuration::get('NRT_NEWSLETTER_LAYOUT') ? Configuration::get('NRT_NEWSLETTER_LAYOUT'): 1),
			'NRT_NEWSLETTER' => (Configuration::get('NRT_NEWSLETTER') ? Configuration::get('NRT_NEWSLETTER'): false),
			'NRT_NEWSLETTER_PAGES' => (Configuration::get('NRT_NEWSLETTER_PAGES') ? Configuration::get('NRT_NEWSLETTER_PAGES'): false),
			'NRT_NEWSLETTER_FORM' => (Configuration::get('NRT_NEWSLETTER_FORM') ? Configuration::get('NRT_NEWSLETTER_FORM'): false),
			'NRT_TEXT' => html_entity_decode(Configuration::get('NRT_TEXT', $this->context->language->id) ? Configuration::get('NRT_TEXT', $this->context->language->id): false),
                        'NRT_TITLE' => html_entity_decode(Configuration::get('NRT_TITLE', $this->context->language->id) ?  Configuration::get('NRT_TITLE', $this->context->language->id): false),
			'NRT_BG' => (Configuration::get('NRT_BG') ? Configuration::get('NRT_BG'): 0),
			'NRT_BG_IMAGE' => (Configuration::get('NRT_BG_IMAGE') ? Configuration::get('NRT_BG_IMAGE'): 0),
            'NRT_COUNTDOWN_POPUP' => $end_date
		);
	}
}