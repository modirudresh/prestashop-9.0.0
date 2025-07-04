<?php
/*
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to https://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2020 PrestaShop SA
*  @license    https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtSocialButton extends Module implements WidgetInterface
{
    protected static $share = [];
	protected static $follow = [];

    private $templateFile;

	protected $_configDefaults;

    public function __construct()
    {
		$this->name = 'nrtsocialbutton';
		$this->tab = 'front_office_features';
		$this->version = '1.0.7';
		$this->author = 'AxonVIZ';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Axon - Social Button');
		$this->description = $this->l('Required by author: AxonVIZ.');
		$this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
		
		$this->templateFile = 'module:nrtsocialbutton/views/templates/hook/social.tpl';
		
        // Config defaults
		
		self::$share['facebook'] = 1;
		self::$share['twitter'] = 1;
		self::$share['email'] = 0;
		self::$share['pinterest'] = 1;
		self::$share['linkedin'] = 1;
		self::$share['odnoklassniki'] = 0;
		self::$share['whatsapp'] = 0;
		self::$share['vk'] = 0;
		self::$share['telegram'] = 1;
		self::$share['viber'] = 0;
		
		self::$follow['facebook'] = '#';
		self::$follow['twitter'] = '#';
		self::$follow['instagram'] = '#';
		self::$follow['youtube'] = '#';
		self::$follow['pinterest'] = '#';
		self::$follow['tumblr'] = '';
		self::$follow['linkedin'] = '';
		self::$follow['vimeo'] = '';
		self::$follow['flickr'] = '';
		self::$follow['github'] = '';
		self::$follow['dribbble'] = '';
		self::$follow['behance'] = '';
		self::$follow['soundcloud'] = '';
		self::$follow['spotify'] = '';
		self::$follow['odnoklassniki'] = '';
		self::$follow['whatsapp'] = '';
		self::$follow['vk'] = '';
		self::$follow['snapchat'] = '';
		self::$follow['tiktok'] = '';
		self::$follow['telegram'] = '';
		
        $this->_configDefaults = array(
			'NRT_SOCIAL_SHARE' => serialize(self::$share),
			'NRT_SOCIAL_FOLLOW' => serialize(self::$follow)
		);		
    }

    public function install()
    {
        return parent::install()
			&& $this->registerHook('displayBlogShareButtons')
			&& $this->registerHook('displayFollowButtons')
			&& $this->registerHook('displayProductShareButtons')
			&& $this->registerHook('displayHeader')
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
        foreach ($this->_configDefaults as $default => $value) {
            $response &= Configuration::updateValue($default, $value);
        }

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE CONFIGS
    /* ------------------------------------------------------------- */
    private function _deleteConfigs()
    {
		$response = true;	
        foreach ($this->_configDefaults as $default => $value) {
            $response &= Configuration::deleteByName($default);
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
        $tab->class_name = "AdminManageSocialButton";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang){
            $tab->name[$lang['id_lang']] = "- Social Button";
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
        $id_tab = Tab::getIdFromClassName('AdminManageSocialButton');
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

    public function getConfigFieldsValues()
    {
        $values = [];
		
		$shares = unserialize(Configuration::get('NRT_SOCIAL_SHARE'));
		$follows = unserialize(Configuration::get('NRT_SOCIAL_FOLLOW'));

        foreach (self::$share as $key => $network) {
            $values['NRT_SC_'.Tools::strtoupper($key)] = (int) Tools::getValue('NRT_SC_'.Tools::strtoupper($key), isset($shares[$key]) ? $shares[$key] : '');
        }
		
        foreach (self::$follow as $key => $network) {
            $values['NRT_SF_'.Tools::strtoupper($key)] = Tools::getValue('NRT_SF_'.Tools::strtoupper($key), isset($follows[$key]) ? $follows[$key] : '');
        }

        return $values;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitNrtSocialButton')) {
            foreach (self::$share as $key => $network) {
				self::$share[$key] = (int) Tools::getValue('NRT_SC_'.Tools::strtoupper($key));
            }
			Configuration::updateValue('NRT_SOCIAL_SHARE', serialize(self::$share));
			
            foreach (self::$follow as $key => $network) {
				self::$follow[$key] = Tools::getValue('NRT_SF_'.Tools::strtoupper($key));
            }
			Configuration::updateValue('NRT_SOCIAL_FOLLOW', serialize(self::$follow));

            $this->_clearCache($this->templateFile);

            $output .= $this->displayConfirmation($this->l('Settings updated.'));

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
        }

        $helper = new HelperForm();
        $helper->submit_action = 'submitNrtSocialButton';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = ['fields_value' => $this->getConfigFieldsValues()];

        $fields = [];
        foreach (self::$share as $key => $network) {
            $fields[] = [
                'type' => 'switch',
                'label' => $this->l('Share - ') . $key,
                'name' => 'NRT_SC_'.Tools::strtoupper($key),
                'values' => [
                    [
                        'id' => Tools::strtolower($key).'_active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => Tools::strtolower($key).'_active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ];
        }
		
        foreach (self::$follow as $key => $network) {
            $fields[] = [
                'type' => 'text',
                'label' => $this->l('Follow - ') . $key,
                'name' => 'NRT_SF_'.Tools::strtoupper($key),
            ];
        }

        return $output.$helper->generateForm([
            [
                'form' => [
                    'legend' => [
                        'title' => $this->displayName,
                        'icon' => 'icon-share',
                    ],
                    'input' => $fields,
                    'submit' => [
                        'title' => $this->l('Save'),
                    ],
                ],
            ],
        ]);
    }
	
    public function hookDisplayHeader()
    {		
        $this->context->controller->registerStylesheet($this->name.'-css', 'modules/'.$this->name.'/views/css/front.css', ['media' => 'all', 'priority' => 150]);
    }

    public function renderWidget($hookName, array $params)
    {
		if ($hookName == 'displayFollowButtons') {
			$key = 'nrtsocialbutton|follow';

			if (!$this->isCached($this->templateFile, $this->getCacheId($key))) {
				$this->smarty->assign($this->getWidgetVariables($hookName, $params));
			}

			return $this->fetch($this->templateFile, $this->getCacheId($key));
		}elseif($hookName == 'displayProductShareButtons' || $hookName == 'displayBlogShareButtons'){
			if (isset($params['link']) && !empty($params['link'])) {
				$params['link'] = $params['link'];
			}else{
				$params['link'] = Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}

			$this->smarty->assign($this->getWidgetVariables($hookName, $params));

			return $this->fetch($this->templateFile);
		}else{
			return;
		}
    }

    public function getWidgetVariables($hookName, array $params)
    {
		if ($hookName == 'displayFollowButtons') {
			
			$follows = unserialize(Configuration::get('NRT_SOCIAL_FOLLOW'));
			
			$social_links = [];
						
			if ($follows['facebook']) {
				$social_links['facebook'] = [
					'label' => $this->l('Facebook'),
					'class' => 'facebook',
					'icon' => '<i class="lab la-facebook-f"></i>',
					'url' => $follows['facebook']
				];
			}
			
			if ($follows['twitter']) {
				$social_links['twitter'] = [
					'label' => $this->l('Twitter'),
					'class' => 'twitter',
					'icon' => '<i class="lab la-twitter"></i>',
					'url' => $follows['twitter']
				];
			}
			
			if ($follows['instagram']) {
				$social_links['instagram'] = [
					'label' => $this->l('Instagram'),
					'class' => 'instagram',
					'icon' => '<i class="lab la-instagram"></i>',
					'url' => $follows['instagram']
				];
			}
			
			if ($follows['youtube']) {
				$social_links['youtube'] = [
					'label' => $this->l('Youtube'),
					'class' => 'youtube',
					'icon' => '<i class="lab la-youtube"></i>',
					'url' => $follows['youtube']
				];
			}
			
			if ($follows['pinterest']) {
				$social_links['pinterest'] = [
					'label' => $this->l('Pinterest'),
					'class' => 'pinterest',
					'icon' => '<i class="lab la-pinterest"></i>',
					'url' => $follows['pinterest']
				];
			}
			
			if ($follows['tumblr']) {
				$social_links['tumblr'] = [
					'label' => $this->l('Tumblr'),
					'class' => 'tumblr',
					'icon' => '<i class="lab la-tumblr"></i>',
					'url' => $follows['tumblr']
				];
			}
			
			if ($follows['linkedin']) {
				$social_links['linkedin'] = [
					'label' => $this->l('Linkedin'),
					'class' => 'linkedin',
					'icon' => '<i class="lab la-linkedin-in"></i>',
					'url' => $follows['linkedin']
				];
			}
			
			if ($follows['vimeo']) {
				$social_links['vimeo'] = [
					'label' => $this->l('vimeo'),
					'class' => 'vimeo',
					'icon' => '<i class="lab la-vimeo"></i>',
					'url' => $follows['vimeo']
				];
			}
						
			if ($follows['flickr']) {
				$social_links['flickr'] = [
					'label' => $this->l('Flickr'),
					'class' => 'flickr',
					'icon' => '<i class="lab la-flickr"></i>',
					'url' => $follows['flickr']
				];
			}
			
			if ($follows['github']) {
				$social_links['github'] = [
					'label' => $this->l('Github'),
					'class' => 'github',
					'icon' => '<i class="lab la-github"></i>',
					'url' => $follows['github']
				];
			}
			
			if ($follows['dribbble']) {
				$social_links['dribbble'] = [
					'label' => $this->l('Dribbble'),
					'class' => 'dribbble',
					'icon' => '<i class="lab la-dribbble"></i>',
					'url' => $follows['dribbble']
				];
			}
			
			if ($follows['behance']) {
				$social_links['behance'] = [
					'label' => $this->l('Behance'),
					'class' => 'behance',
					'icon' => '<i class="lab la-behance"></i>',
					'url' => $follows['behance']
				];
			}
			
			if ($follows['soundcloud']) {
				$social_links['soundcloud'] = [
					'label' => $this->l('Soundcloud'),
					'class' => 'soundcloud',
					'icon' => '<i class="lab la-soundcloud"></i>',
					'url' => $follows['soundcloud']
				];
			}
						
			if ($follows['spotify']) {
				$social_links['spotify'] = [
					'label' => $this->l('Spotify'),
					'class' => 'spotify',
					'icon' => '<i class="lab la-spotify"></i>',
					'url' => $follows['spotify']
				];
			}
			
			if ($follows['odnoklassniki']) {
				$social_links['odnoklassniki'] = [
					'label' => $this->l('Odnoklassniki'),
					'class' => 'odnoklassniki',
					'icon' => '<i class="lab la-odnoklassniki"></i>',
					'url' => $follows['odnoklassniki']
				];
			}
			
			if ($follows['whatsapp']) {
				$social_links['whatsapp'] = [
					'label' => $this->l('Whatsapp'),
					'class' => 'whatsapp',
					'icon' => '<i class="lab la-whatsapp"></i>',
					'url' => $follows['whatsapp']
				];
			}
			
			if ($follows['vk']) {
				$social_links['vk'] = [
					'label' => $this->l('VK'),
					'class' => 'vk',
					'icon' => '<i class="lab la-vk"></i>',
					'url' => $follows['vk']
				];
			}
						
			if ($follows['snapchat']) {
				$social_links['snapchat'] = [
					'label' => $this->l('Snapchat'),
					'class' => 'snapchat',
					'icon' => '<i class="lab la-snapchat"></i>',
					'url' => $follows['snapchat']
				];
			}
			
			if ($follows['tiktok']) {
				$social_links['tiktok'] = [
					'label' => $this->l('Tiktok'),
					'class' => 'tiktok',
					'icon' => '<i class="las la-file-audio"></i>',
					'url' => $follows['tiktok']
				];
			}
			
			if ($follows['telegram']) {
				$social_links['telegram'] = [
					'label' => $this->l('Telegram'),
					'class' => 'telegram',
					'icon' => '<i class="lab la-telegram"></i>',
					'url' => $follows['telegram']
				];
			}
			
			return [
				'social_links' => $social_links,
			];
			
		}elseif($hookName == 'displayProductShareButtons' || $hookName == 'displayBlogShareButtons'){
			
			$page_link = $params['link'];
			$img_url = '';
			$page_title = '';
			
			if (isset($params['img']) && !empty($params['img'])) {
				$img_url = $params['img'];
			}
			
			if (isset($params['title']) && !empty($params['title'])) {
				$page_title = $params['title'];
			}
			
			$shares = unserialize(Configuration::get('NRT_SOCIAL_SHARE'));
			
			$social_links = [];
			
			if ($shares['facebook']) {
				$social_links['facebook'] = [
					'label' => $this->l('Facebook'),
					'class' => 'facebook',
					'icon' => '<i class="lab la-facebook-f"></i>',
					'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $page_link
				];
			}
			
			if ($shares['twitter']) {
				$social_links['twitter'] = [
					'label' => $this->l('Twitter'),
					'class' => 'twitter',
					'icon' => '<i class="lab la-twitter"></i>',
					'url' => 'https://twitter.com/share?url=' . $page_link
				];
			}
			
			if ($shares['email']) {
				$social_links['email'] = [
					'label' => $this->l('Email'),
					'class' => 'email',
					'icon' => '<i class="las la-envelope"></i>',
					'url' => 'mailto:?subject=' . $this->l('Check%20this%20') . $page_link
				];
			}
			
			if ($shares['pinterest']) {
				$social_links['pinterest'] = [
					'label' => $this->l('Pinterest'),
					'class' => 'pinterest',
					'icon' => '<i class="lab la-pinterest"></i>',
					'url' => 'https://pinterest.com/pin/create/button/?url=' . $page_link . '&media=' . $img_url . '&description=' . urlencode( $page_title )
				];
			}
			
			if ($shares['linkedin']) {
				$social_links['linkedin'] = [
					'label' => $this->l('Linkedin'),
					'class' => 'linkedin',
					'icon' => '<i class="lab la-linkedin-in"></i>',
					'url' => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $page_link
				];
			}
			
			if ($shares['odnoklassniki']) {
				$social_links['odnoklassniki'] = [
					'label' => $this->l('Odnoklassniki'),
					'class' => 'odnoklassniki',
					'icon' => '<i class="lab la-odnoklassniki"></i>',
					'url' => 'https://connect.ok.ru/offer?url=' . $page_link
				];
			}
			
			if ($shares['whatsapp']) {
				$social_links['whatsapp_desktop'] = [
					'label' => $this->l('Whatsapp'),
					'class' => 'whatsapp hidden-md-down',
					'icon' => '<i class="lab la-whatsapp"></i>',
					'url' => 'https://api.whatsapp.com/send?text=' . urlencode( $page_link )
				];
				$social_links['whatsapp_mobile'] = [
					'label' => $this->l('Whatsapp'),
					'class' => 'whatsapp hidden-lg-up',
					'icon' => '<i class="lab la-whatsapp"></i>',
					'url' => 'whatsapp://send?text=' . urlencode( $page_link )
				];
			}
			
			if ($shares['vk']) {
				$social_links['vk'] = [
					'label' => $this->l('VK'),
					'class' => 'vk',
					'icon' => '<i class="lab la-vk"></i>',
					'url' => 'https://vk.com/share.php?url=' . $page_link . '&image=' . $img_url . '&title=' . $page_title
				];
			}
			
			if ($shares['telegram']) {
				$social_links['telegram'] = [
					'label' => $this->l('Telegram'),
					'class' => 'telegram',
					'icon' => '<i class="lab la-telegram"></i>',
					'url' => 'https://telegram.me/share/url?url=' . $page_link
				];
			}
			
			if ($shares['viber']) {
				$social_links['viber'] = [
					'label' => $this->l('Viber'),
					'class' => 'viber hidden-lg-up',
					'icon' => '<i class="lab la-viber"></i>',
					'url' => 'viber://forward?text=' . $page_link
				];
			}

			return [
				'social_links' => $social_links,
			];
			
		}
    }
}
