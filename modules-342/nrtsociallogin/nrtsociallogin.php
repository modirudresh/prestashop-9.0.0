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

use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtSocialLogin extends Module implements WidgetInterface
{
	private $templateFile;
	
	public function __construct()
	{
		$this->name = 'nrtsociallogin';
		$this->tab = 'front_office_features';
		$this->version = '3.0.2';
		$this->author = 'AxonVIZ';
		$this->need_instance = 0;
		$this->bootstrap = true;
		
		parent::__construct();
			 
		$this->displayName = $this->l('Axon - Social Login');
		$this->description = $this->l('Professionally developed and free module that allows your users to register and login to PrestaShop with their Social Network account (Twitter, Facebook, LinkedIn, Google ...)');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->templateFile = 'module:nrtsociallogin/views/templates/hook/button.tpl';
	}

	public function install(){
		
		$success = (parent::install()
            && $this->setCfDefaults()
			&& $this->installTab()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displaySocialLogin')
		);
		
		return $success;
	}

	public function uninstall(){
		
		$success = (parent::uninstall()
			&& $this->deleteCfDefaults()
			&& $this->uninstallTab()
		);
		
		return $success;
	}
	
	public function installTab()
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
        $tab->class_name = "AdminNrtSocialLogin";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Social Login";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE THE TAB MENU
    /* ------------------------------------------------------------- */
	
    public function uninstallTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtSocialLogin');
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
		
    public function setCfDefaults()
    {
		$setting = array();
		$setting['show_popup'] 					= 1;
		$setting['redirect_url'] 				= 0;
		$setting['display_button'] 				= 0;
		
		$setting['facebook']['enable'] 			= 1;
		$setting['facebook']['app_id'] 			= '';
		$setting['facebook']['app_secret'] 		= '';
		
		$setting['gplus']['enable'] 			= 1;
		$setting['gplus']['client_id'] 			= '';
		$setting['gplus']['client_secret'] 		= '';
		
		$setting['live']['enable'] 				= 1;
		$setting['live']['client_id'] 			= '';
		$setting['live']['client_secret'] 		= '';
		
		$setting['linked']['enable'] 			= 1;
		$setting['linked']['client_id'] 		= '';
		$setting['linked']['client_secret'] 	= '';
		
		$setting['twitter']['enable'] 			= 1;
		$setting['twitter']['client_id'] 		= '';
		$setting['twitter']['client_secret'] 	= '';
		
		$setting['yahoo']['enable'] 			= 0;
		$setting['yahoo']['consumer_key'] 		= '';
		$setting['yahoo']['consumer_secret'] 	= '';
		
		$setting['insta']['enable'] 			= 1;
		$setting['insta']['client_id'] 			= '';
		$setting['insta']['client_secret'] 		= '';
		
		$setting['amazon']['enable'] 			= 0;
		$setting['amazon']['client_id'] 		= '';
		$setting['amazon']['client_secret'] 	= '';
		
		$setting['pay']['enable'] 				= 0;
		$setting['pay']['client_id'] 			= '';
		$setting['pay']['client_secret'] 		= '';
		
		$setting['foursquare']['enable'] 		= 0;
		$setting['foursquare']['client_id'] 	= '';
		$setting['foursquare']['client_secret'] = '';
		
		$setting['github']['enable']			= 0;
		$setting['github']['client_id']			= '';
		$setting['github']['client_secret'] 	= '';
		
		$setting['disqus']['enable'] 			= 0;
		$setting['disqus']['client_id'] 		= '';
		$setting['disqus']['client_secret'] 	= '';
				
		$setting['wordpress']['enable'] 		= 0;
		$setting['wordpress']['client_id'] 		= '';
		$setting['wordpress']['client_secret'] 	= '';
		
		$setting['dropbox']['enable'] 			= 0;
		$setting['dropbox']['client_id'] 		= '';
		$setting['dropbox']['client_secret'] 	= '';
        
        Configuration::updateValue('NRT_SOCIAL_LOGIN_CONFIG', serialize($setting));
		
        return true;
    }

    public function deleteCfDefaults()
    {
        Configuration::deleteByName('NRT_SOCIAL_LOGIN_CONFIG');
		
        return true;
    }
	
	public function isSelectedShopGroup(){
		if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL){
			return true;
		} else {
			return false;
		}
	}

	public function _displayForm(){		
		return $this->generateAdminPage();
	}

	public function _getUrlCallback($platform){		
		$redirect_uri = $this->context->link->getModuleLink('nrtsociallogin', $platform);

		$lang_str = '&id_lang='.$this->context->language->id;
		$redirect_uri = str_replace($lang_str, '', $redirect_uri);

		$lang_str = '/'.$this->context->language->iso_code.'/';
		$redirect_uri = str_replace($lang_str, '/', $redirect_uri);

		return $redirect_uri;
	}

	public function generateAdminPage(){
		$config_social = unserialize(Configuration::get('NRT_SOCIAL_LOGIN_CONFIG'));

		$config_social['facebook']['callback'] = $this->_getUrlCallback('facebook');
		$config_social['gplus']['callback'] = $this->_getUrlCallback('google');
		$config_social['live']['callback'] = $this->_getUrlCallback('live');
		$config_social['linked']['callback'] = $this->_getUrlCallback('linkedin');
		$config_social['twitter']['callback'] = $this->_getUrlCallback('twitter');
		$config_social['yahoo']['callback'] = $this->_getUrlCallback('yahoo');
		$config_social['insta']['callback'] = $this->_getUrlCallback('instagram');
		$config_social['amazon']['callback'] = $this->_getUrlCallback('amazon');
		$config_social['pay']['callback'] = $this->_getUrlCallback('paypal');
		$config_social['foursquare']['callback'] = $this->_getUrlCallback('foursquare');
		$config_social['github']['callback'] = $this->_getUrlCallback('github');
		$config_social['disqus']['callback'] = $this->_getUrlCallback('disqus');
		$config_social['wordpress']['callback'] = $this->_getUrlCallback('wordpress');
		$config_social['dropbox']['callback'] = $this->_getUrlCallback('dropbox');

		$this->context->smarty->assign(array(
			'action' => Tools::safeOutput($_SERVER['REQUEST_URI']),
			'config_social' => $config_social
		));

		return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
	}
	
	public function _postProcess(){
		$setting = array();
		
		$setting['show_popup'] 					= trim(Tools::getValue('show_popup'));
		$setting['redirect_url'] 				= trim(Tools::getValue('redirect_url'));
		$setting['display_button'] 				= trim(Tools::getValue('display_button'));
		
		$setting['facebook']['enable'] 			= trim(Tools::getValue('facebook_enable'));
		$setting['facebook']['app_id'] 			= trim(Tools::getValue('facebook_app_id'));
		$setting['facebook']['app_secret'] 		= trim(Tools::getValue('facebook_app_secret'));
		
		$setting['gplus']['enable'] 			= trim(Tools::getValue('gplus_enable'));
		$setting['gplus']['client_id'] 			= trim(Tools::getValue('gplus_client_id'));
		$setting['gplus']['client_secret'] 		= trim(Tools::getValue('gplus_client_secret'));
		
		$setting['live']['enable'] 				= trim(Tools::getValue('live_enable'));
		$setting['live']['client_id'] 			= trim(Tools::getValue('live_client_id'));
		$setting['live']['client_secret'] 		= trim(Tools::getValue('live_client_secret'));
		
		$setting['linked']['enable'] 			= trim(Tools::getValue('linked_enable'));
		$setting['linked']['client_id'] 		= trim(Tools::getValue('linked_client_id'));
		$setting['linked']['client_secret'] 	= trim(Tools::getValue('linked_client_secret'));
		
		$setting['twitter']['enable'] 			= trim(Tools::getValue('twitter_enable'));
		$setting['twitter']['client_id'] 		= trim(Tools::getValue('twitter_client_id'));
		$setting['twitter']['client_secret'] 	= trim(Tools::getValue('twitter_client_secret'));
		
		$setting['yahoo']['enable'] 			= trim(Tools::getValue('yahoo_enable'));
		$setting['yahoo']['consumer_key'] 		= trim(Tools::getValue('yahoo_consumer_key'));
		$setting['yahoo']['consumer_secret'] 	= trim(Tools::getValue('yahoo_consumer_secret'));
		
		$setting['insta']['enable'] 			= trim(Tools::getValue('insta_enable'));
		$setting['insta']['client_id'] 			= trim(Tools::getValue('insta_client_id'));
		$setting['insta']['client_secret'] 		= trim(Tools::getValue('insta_client_secret'));
		
		$setting['amazon']['enable'] 			= trim(Tools::getValue('amazon_enable'));
		$setting['amazon']['client_id'] 		= trim(Tools::getValue('amazon_client_id'));
		$setting['amazon']['client_secret'] 	= trim(Tools::getValue('amazon_client_secret'));
		
		$setting['pay']['enable'] 				= trim(Tools::getValue('pay_enable'));
		$setting['pay']['client_id'] 			= trim(Tools::getValue('pay_client_id'));
		$setting['pay']['client_secret'] 		= trim(Tools::getValue('pay_client_secret'));
		
		$setting['foursquare']['enable'] 		= trim(Tools::getValue('foursquare_enable'));
		$setting['foursquare']['client_id'] 	= trim(Tools::getValue('foursquare_client_id'));
		$setting['foursquare']['client_secret'] = trim(Tools::getValue('foursquare_client_secret'));
		
		$setting['github']['enable']			= trim(Tools::getValue('github_enable'));
		$setting['github']['client_id']			= trim(Tools::getValue('github_client_id'));
		$setting['github']['client_secret'] 	= trim(Tools::getValue('github_client_secret'));
		
		$setting['disqus']['enable'] 			= trim(Tools::getValue('disqus_enable'));
		$setting['disqus']['client_id'] 		= trim(Tools::getValue('disqus_client_id'));
		$setting['disqus']['client_secret'] 	= trim(Tools::getValue('disqus_client_secret'));
				
		$setting['wordpress']['enable'] 		= trim(Tools::getValue('wordpress_enable'));
		$setting['wordpress']['client_id'] 		= trim(Tools::getValue('wordpress_client_id'));
		$setting['wordpress']['client_secret'] 	= trim(Tools::getValue('wordpress_client_secret'));
		
		$setting['dropbox']['enable'] 			= trim(Tools::getValue('dropbox_enable'));
		$setting['dropbox']['client_id'] 		= trim(Tools::getValue('dropbox_client_id'));
		$setting['dropbox']['client_secret'] 	= trim(Tools::getValue('dropbox_client_secret'));
	
		$this->_clearCache($this->templateFile);

		Configuration::updateValue('NRT_SOCIAL_LOGIN_CONFIG', serialize($setting));
	}
	
	public function getContent()
	{
		$html = "";

		if (Tools::isSubmit('login_social_submit')){
			$this->_postProcess();
		}
					
		if (!$this->isSelectedShopGroup()){	
			$html .= $this->_displayForm();
		}else{
			$html .= '
				<p class="alert alert-warning">'.
					$this->l('You cannot manage the module from a "All Shops" or a "Group Shop" context, select directly the shop you want to edit').'
				</p>';
		}	

		return $html;
	}

	public function addUser($user_data)
	{
		if (!Customer::customerExists(strip_tags($user_data['email']), false, true))
		{	
			$customer = new Customer();

			$customer->firstname = ucwords($user_data['first_name']);
			$customer->lastname = ucwords($user_data['last_name']);
			$customer->email = $user_data['email'];
			$password = Tools::passwdGen();
			$crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
			$customer->passwd = $crypto->hash($password, _COOKIE_KEY_);
			$customer->id_gender = isset($user_data['gender']) ? (int) $user_data['gender'] : null;
			$customer->birthday = null;
			$customer->active = 1;
			$customer->optin = true;
			$customer->is_guest = false;

			$ok = $customer->save();

			if ($ok) {
				$this->context->updateCustomer($customer);
				$this->context->cart->update();
				// Send a welcome information email, only for registered customers
				$this->sendConfirmationMail($customer, $password);
				Hook::exec('actionCustomerAccountAdd', [
					'newCustomer' => $customer,
				]);
			}
		}

		Hook::exec('actionAuthenticationBefore');

        $customer = new Customer();
        $authentication = $customer->getByEmail($user_data['email']);
                            
		if (isset($authentication->active) && !$authentication->active) {
			return false;
		} elseif (!$authentication || !$customer->id || $customer->is_guest) {
			return false;
		} else {
			$this->context->updateCustomer($customer);

			Hook::exec('actionAuthentication', ['customer' => $this->context->customer]);

			// Login information have changed, so we check if the cart rules still apply
			CartRule::autoRemoveFromCart($this->context);
			CartRule::autoAddToCart($this->context);
		}

		return true;
	}
	
	public function sendConfirmationMail(Customer $customer, $password){
        if ($customer->is_guest || !Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            return true;
        }

        return Mail::Send(
            $this->context->language->id,
            'account',
            Context::getContext()->getTranslator()->trans(
                'Welcome!',
                array(),
                'Emails.Subject'
            ),
			array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{email}' => $customer->email,
				'{passwd}' => $password),
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
            null,
            null,
            null,
            null,
            dirname(__FILE__).'/mails/'
        );
	}

    public function hookDisplayHeader()
    {
		$settings = Configuration::get('NRT_SOCIAL_LOGIN_CONFIG');
		$loginizer_data = unserialize($settings);

		Media::addJsDef(array(
			'opSLogin' => ['show_popup' => (bool)$loginizer_data['show_popup'],
						   'redirect_url' => (bool)$loginizer_data['redirect_url']]
        ));

		$this->context->controller->addCss($this->_path.'views/css/front.css');
        $this->context->controller->addJs($this->_path.'views/js/front.min.js');
    }
	
    public function renderWidget($hookName, array $params)
    {
		if (!$this->isCached($this->templateFile, $this->getCacheId())) {
			$this->smarty->assign($this->getWidgetVariables($hookName, $params));
		}

		return $this->fetch($this->templateFile, $this->getCacheId());
    }

    public function getWidgetVariables($hookName, array $params)
    {
		$settings = Configuration::get('NRT_SOCIAL_LOGIN_CONFIG');
		$loginizer_data = unserialize($settings);
		
        return array(
            'loginizer_data' => $loginizer_data
        );
    }
	
}
