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
use AxonCreator\Wp_Helper;

require_once _PS_MODULE_DIR_   . 'nrtthemecustomizer/src/NrtCustomFonts.php';

class NrtThemeCustomizer extends Module implements WidgetInterface
{
    private $_output = '';
    private $_configDefaults = array();
    private $_websafeFonts = array();
    private $_googleFonts = array();
	private $_headerTypes = array();
	private $_headerTypesOnPage = array();
	private $_footerTypesOnPage = array();
	private $_homeTypes = array();
	private $_layoutWidthTypes = array();
	private $_productTypes = array();
	private $_productLayout = array();
	private $_tabsType = array();
	private $_categoryLayout = array();
    private $_configGlobals = array();
	private $_mobileheaderTypes;
	private $_footerTypes;

    function __construct()
    {
        $this->name = 'nrtthemecustomizer';
        $this->tab = 'front_office_features';
        $this->version = '2.3.6';
        $this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Axon - Theme Customizer');
        $this->description = $this->l('Required by author: AxonVIZ.');
		
        // Web-safe Fonts
        $this->_websafeFonts = array('Arial', 'Tahoma', 'Verdana', 'Helvetica', 'Times New Roman', 'Trebuchet MS', 'Georgia');
			
		$this->_configGlobals = array(
        	'active_header_layout' => array('type' => 'default', 'value' => 0),
			'active_header_sticky_layout' => array('type' => 'default', 'value' => 0),
        	'active_home_layout' => array('type' => 'default', 'value' => 0),
        	'active_footer_layout' => array('type' => 'default', 'value' => 0),
		);
		
        // Config defaults
        $this->_configDefaults = array(
			// General Options
            'general_main_layout' => array('type' => 'smarty', 'value' => 'wide'),
			'general_back_top' => array('type' => 'smarty', 'value' => 1),
			'general_affix_scroll' => array('type' => 'default', 'value' => 1),
			'general_header_sticky_affix' => array('type' => 'default', 'value' => 1),
			'general_product_image_second' => array('type' => 'smarty', 'value' => 1),
			'general_product_image_type_small' => array('type' => 'smarty', 'value' => 'cart_default'),
			'general_product_image_type_large' => array('type' => 'smarty', 'value' => 'home_default'),
			'general_container_max_width' => array('type' => 'default', 'value' => '100%'),
			'general_column_space' => array('type' => 'smarty', 'value' => 30),
			// Hompage Options
			'index_open_vertical_menu' => array('type' => 'default', 'value' => 0),
			'index_header_layout' => array('type' => 'default', 'value' => 'inherit'),
			'index_header_sticky_layout' => array('type' => 'default', 'value' => 'inherit'),
			'index_header_overlap' => array('type' => 'default', 'value' => 0),
			'index_footer_layout' => array('type' => 'default', 'value' => 'inherit'),
			// Contact Options
			'contact_override_content_by_hook' => array('type' => 'smarty', 'value' => 1),
			'contact_header_layout' => array('type' => 'default', 'value' => 'inherit'),
			'contact_header_sticky_layout' => array('type' => 'default', 'value' => 'inherit'),
			'contact_header_overlap' => array('type' => 'default', 'value' => 0),
			'contact_footer_layout' => array('type' => 'default', 'value' => 'inherit'),
			'contact_page_title_layout' => array('type' => 'default', 'value' => 'inherit'),
			// Contact Options
			'404_override_content_by_hook' => array('type' => 'smarty', 'value' => 0),
			'404_header_layout' => array('type' => 'default', 'value' => 'inherit'),
			'404_header_sticky_layout' => array('type' => 'default', 'value' => 'inherit'),
			'404_header_overlap' => array('type' => 'default', 'value' => 0),
			'404_footer_layout' => array('type' => 'default', 'value' => 'inherit'),
			'404_page_title_layout' => array('type' => 'default', 'value' => 'inherit'),
			// Footer Options
			'general_footer_fixed' => array('type' => 'smarty', 'value' => 0),
			// Page title
			'page_title_layout' => array('type' => 'smarty', 'value' => 1),
			'bg_page_title_img' => array('type' => 'smarty', 'value' => ''),
			'page_title_color' => array('type' => 'smarty', 'value' => 'dark'),
			// Category Page Options
			'category_header_layout' => array('type' => 'default', 'value' => 'inherit'),
			'category_header_sticky_layout' => array('type' => 'default', 'value' => 'inherit'),
			'category_header_overlap' => array('type' => 'default', 'value' => 0),
			'category_footer_layout' => array('type' => 'default', 'value' => 'inherit'),
			'category_page_title_layout' => array('type' => 'default', 'value' => 'inherit'),
			'category_show_sub' => array('type' => 'smarty', 'value' => 0),
        	'category_default_view' => array('type' => 'smarty', 'value' => 1),
			'category_banner_layout' => array('type' => 'smarty', 'value' => 1),
			'category_image_type' => array('type' => 'smarty', 'value' => ''),
			'category_product_infinite' => array('type' => 'smarty', 'value' => 1),
			'category_faceted_position' => array('type' => 'smarty', 'value' => 1),
			'category_layout_width_type' => array('type' => 'smarty', 'value' => 'container'),
			'category_layout' => array('type' => 'smarty', 'value' => 1),
        	'category_product_layout' => array('type' => 'smarty', 'value' => 1),
			'category_product_image_type' => array('type' => 'smarty', 'value' => ''),
			'category_product_hide_review' => array('type' => 'smarty', 'value' => 0),
			'category_product_hide_variant' => array('type' => 'smarty', 'value' => 0),
			'category_product_xl' => array('type' => 'default', 'value' => 4),
			'category_product_lg' => array('type' => 'default', 'value' => 4),
			'category_product_md' => array('type' => 'default', 'value' => 3),
			'category_product_xs' => array('type' => 'default', 'value' => 2),
			'category_product_space_xl' => array('type' => 'default', 'value' => 30),
			'category_product_space_lg' => array('type' => 'default', 'value' => 30),
			'category_product_space_md' => array('type' => 'default', 'value' => 30),
			'category_product_space_xs' => array('type' => 'default', 'value' => 30),
			// Product Page Options
			'product_header_layout' => array('type' => 'default', 'value' => 'inherit'),
			'product_header_sticky_layout' => array('type' => 'default', 'value' => 'inherit'),
			'product_footer_layout' => array('type' => 'default', 'value' => 'inherit'),
			'product_layout_width_type' => array('type' => 'smarty', 'value' => 'container'),
			'product_show_buy_now' => array('type' => 'smarty', 'value' => 1),
			'product_layout' => array('type' => 'smarty', 'value' => 1),
			'product_image_type' => array('type' => 'smarty', 'value' => ''),
			'product_image_thumb_type' => array('type' => 'smarty', 'value' => ''),
			'product_tabs_type' => array('type' => 'smarty', 'value' => 1),
			// Font Options
			'font_body' => array('type' => 'default', 'value' => 'None'),
			'font_title' => array('type' => 'default', 'value' => 'None'),
			'font_size_lg' => array('type' => 'default', 'value' => 62.5),
			'font_size_xs' => array('type' => 'default', 'value' => 62.5),
			// Color Options
			'color_scheme_dark' => array('type' => 'default', 'value' => 0),
			'color_primary' => array('type' => 'default', 'value' => ''),
            'button_color' => array('type' => 'default', 'value' => 'light'),
			'color_price' => array('type' => 'default', 'value' => ''),
			'color_new_label' => array('type' => 'default', 'value' => ''),
			'color_sale_label' => array('type' => 'default', 'value' => ''),
			// Background Options
			'background_color' => array('type' => 'default', 'value' => ''),
			'background_img' => array('type' => 'default', 'value' => ''),
			'background_img_repeat' => array('type' => 'default', 'value' => 'repeat'),
			'background_img_attachment' => array('type' => 'default', 'value' => 'scroll'),
			'background_img_size' => array('type' => 'default', 'value' => 'auto'),
			'background_body_color' => array('type' => 'default', 'value' => ''),
			'background_body_img' => array('type' => 'default', 'value' => ''),
			'background_body_img_repeat' => array('type' => 'default', 'value' => 'repeat'),
			'background_body_img_attachment' => array('type' => 'default', 'value' => 'scroll'),
			'background_body_img_size' => array('type' => 'default', 'value' => 'auto'),
			// Input Button Label
			'input_style' => array('type' => 'default', 'value' => 'rectangular'),
			'input_border_width' => array('type' => 'default', 'value' => 1),
			'button_style' => array('type' => 'default', 'value' => 'flat'),
			'button_border_width' => array('type' => 'default', 'value' => 1),
			'product_label' => array('type' => 'smarty', 'value' => 'rectangular'),
			//Style
			'style_on_theme' => array('type' => 'default', 'value' => '{}'),
			// Custom Codes
			'custom_css' => array('type' => 'default', 'value' => ''),
			'custom_js' => array('type' => 'default', 'value' => ''),
		 );	
		 
    }

    /* ------------------------------------------------------------- */
    /*  INSTALL THE MODULE
    /* ------------------------------------------------------------- */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
		
			&& $this->registerHook('actionObjectShopUpdateAfter')
			&& $this->registerHook('actionAdminControllerSetMedia')
			&& $this->registerHook('actionCategoryAdd')
			&& $this->registerHook('actionCategoryDelete')
			&& $this->registerHook('actionCategoryUpdate')
			&& $this->registerHook('actionObjectCmsAddAfter')
			&& $this->registerHook('actionObjectCmsDeleteAfter')
			&& $this->registerHook('actionObjectCmsUpdateAfter')
			&& $this->registerHook('actionProductDelete')
			&& $this->registerHook('actionProductSave')
			&& $this->registerHook('actionProductSearchAfter')
			&& $this->registerHook('actionProductSearchComplete')
			&& $this->registerHook('displayBackOfficeHeader')
			&& $this->registerHook('displayAdminProductsExtra')
			&& $this->registerHook('displayBeforeBodyClosingTag')
			&& $this->registerHook('filterProductSearch')
			&& $this->registerHook('displayHeader')
			&& $this->registerHook('productSearchProvider')
			&& $this->_createConfigs()
			&& $this->_createTab()
			&& $this->_createTables();
    }

    /* ------------------------------------------------------------- */
    /*  UNINSTALL THE MODULE
    /* ------------------------------------------------------------- */
    public function uninstall()
    {
        return parent::uninstall()
        //&& $this->_deleteConfigs()
        && $this->_deleteTab();
		//&& $this->_dropTables();
    }

    /* ------------------------------------------------------------- */
    /*  CREATE THE TABLES
    /* ------------------------------------------------------------- */
    public function _createTables()
    {
        $return = true;
        //$this->_dropTables();

        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrt_themect_page_config` (
				`page_id` int(10) unsigned NOT NULL,
				`page_type` varchar(40) NOT NULL default "",
                `config` longtext default NULL,
				 PRIMARY KEY (`page_id`, `page_type`)
				) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');
		
		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nrt_custom_fonts` (
				`id_nrt_custom_fonts` int(10) NOT NULL auto_increment,
				`title` varchar(256) NOT NULL,
				`font_style` varchar(256) NOT NULL,
				`font_weight` varchar(256) NOT NULL,
				`font_name` varchar(256) NOT NULL,
				`woff` varchar(256) NOT NULL,
				`woff2` varchar(256) NOT NULL,
				`ttf` varchar(256) NOT NULL,
				`svg` varchar(256) NOT NULL,
				`eot` varchar(256) NOT NULL,
                `active` tinyint(1) NOT NULL,
				 PRIMARY KEY (id_nrt_custom_fonts)
				) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
		');
		
        return $return;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE THE TABLES
    /* ------------------------------------------------------------- */
    public function _dropTables()
    {		
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nrt_themect_page_config`') && 
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nrt_custom_fonts`');
    }
    /* ------------------------------------------------------------- */
    /*  CREATE CONFIGS
    /* ------------------------------------------------------------- */
    private function _createConfigs()
    {
		$response = true;
		
        $config = file_get_contents($this->getLocalPath().'configs/demo1.json');
        $config = json_decode($config, true);
		
        foreach ($this->_configGlobals as $key => $default) {
            if (isset($config[$key])) {
				$response &= Configuration::updateValue($key, $config[$key]);
            } else{
				$response &= Configuration::updateValue($key, $default['value']);
            }
        }
						
        foreach ($this->_configDefaults as $key => $default) {
            if (isset($config['opThemect'][$key])) {
				$config['opThemect'][$key] = $config['opThemect'][$key];
            } else{
				$config['opThemect'][$key] = $default['value'];
            }
        }
		
		$response &= Configuration::updateValue('opThemect', json_encode($config['opThemect']));

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE CONFIGS
    /* ------------------------------------------------------------- */
    private function _deleteConfigs()
    {
		$response = true;	

        $response &= Configuration::deleteByName('opThemect');
		
        foreach ($this->_configGlobals as $key => $default) {
            $response &= Configuration::deleteByName($key);
        }

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  INSTALL DEMO DATA
    /* ------------------------------------------------------------- */
    private function _installDemoData()
    {
        return true;
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
		
		if( !Tab::getIdFromClassName('AdminNrtThemeCustomizerConfig') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminNrtThemeCustomizerConfig";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "- Theme Customizer";
			}
			$tab->id_parent = $parentTab_2->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminNrtCustomFonts') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminNrtCustomFonts";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "- Custom Fonts";
			}
			$tab->id_parent = $parentTab_2->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}

        return $response;
    }

    /* ------------------------------------------------------------- */
    /*  DELETE THE TAB MENU
    /* ------------------------------------------------------------- */
    private function _deleteTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminNrtCustomFonts');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = Tab::getIdFromClassName('AdminNrtThemeCustomizerConfig');
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
		$parentTabID = Tab::getIdFromClassName('AdminMenuFirst');
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0) {
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }
	
    /* ------------------------------------------------------------- */
    /*  GET CONTENT
    /* ------------------------------------------------------------- */
    public function getContent()
    {
        $id_shop = $this->context->shop->id;
        $languages = $this->context->language->getLanguages();
        $errors = array();
		
		$this->context->controller->addJqueryUI('ui.sortable');
		
        // Load css file for option panel
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/admin/admin.css');

        // Load js file for option panel
		$this->context->controller->addJS($this->_path.'views/js/admin/back.js');
		
		if(Tools::isSubmit('importConfiguration'))
		{
			if(isset($_FILES['uploadConfig']) && $_FILES['uploadConfig']['tmp_name'])
			{
				$str = file_get_contents($_FILES['uploadConfig']['tmp_name']);
				$arr = json_decode($str, true);

				foreach ($arr as $key => $value) {
					if(is_array($value)){
						$value = json_encode($value);
					}
					Configuration::updateValue($key, $value);
				}
												
				$this->_writeCss();
				$this->_writeJs();
				if (count($errors))
					 $this->_output .= $this->displayError(implode('<br />', $errors));
				else
					$this->_output .= $this -> displayConfirmation($this->l('Configuration imported'));
			}
			else
				$this->_output .= $this -> displayError($this->l('No config file'));	
		}
		elseif(Tools::isSubmit('importConfigurationDemo'))
		{
			$id_file = (int)Tools::getValue('importConfigurationDemo');
			
			$config_data = $this->get_config_data( $id_file );
									
			if($config_data['success'])
			{
				$arr = $config_data['data'];
								
				$config = $this->_getThemeConfig();
								
				foreach ($arr as $key => $value) {
					if( ( stripos( $key, 'header_layout' ) || stripos( $key, 'header_sticky_layout' ) || stripos( $key, 'home_layout' ) || stripos( $key, 'footer_layout' ) ) && $value != 'inherit' && $value != 0 ){
						continue;
					}
					if(is_array($value)){
						foreach ($value as $key_child => &$value_child) {
							if( ( stripos( $key_child, 'header_layout' ) || stripos( $key_child, 'header_sticky_layout' ) || stripos( $key_child, 'home_layout' ) || stripos( $key_child, 'footer_layout' ) ) && $value_child != 'inherit' && $value_child != 0 ){
								$value_child = $config[$key_child];
							}
						}
						$value = json_encode($value);
					}
					Configuration::updateValue($key, $value);
				}
				
				$this->_writeCss();
				$this->_writeJs();
				if (count($errors))
					 $this->_output .= $this->displayError(implode('<br />', $errors));
				else
					$this->_output .= $this -> displayConfirmation($this->l('Configuration imported'));
			}
			else
				$this->_output .= $this -> displayError($config_data['message']);	
		}
		elseif(Tools::isSubmit('nrtAddHooksForTheme'))
		{
			$this->axps_register_hook();

            // Prepare the output
            if (count($errors)) {
                $this->_output .= $this->displayError(implode('<br />', $errors));
            }
            else {
                $this->_output .= $this->displayConfirmation($this->l('Configuration updated'));
            }
		}
		elseif (Tools::isSubmit('nrtClearCache')) {
			Tools::clearSmartyCache();
			Media::clearCache();
		}
        elseif (Tools::isSubmit('submit' . $this->name)) {
            // Update config
			
			foreach ($this->_configGlobals as $key => $default) {
				if (Tools::isSubmit($key)) {
					Configuration::updateValue($key, Tools::getValue($key));
				}
			}

            $this->__updatePositions();
			
			$opThemect = [];
			
            foreach ($this->_configDefaults as $key => $default) {
                if (Tools::isSubmit($key)) {
                    if($key == 'custom_js'){ 
                        $opThemect[$key] = htmlentities(Tools::getValue($key));
                    }else{
                        $opThemect[$key] = Tools::getValue($key);
                    }
				}
			}
			
			Configuration::updateValue('opThemect', json_encode($opThemect));
			
            // Write the configurations to a CSS file
            $this->_writeCss();
			$this->_writeImgCss();
			$this->_writeJs();
			
            // Prepare the output
            if (count($errors)) {
                $this->_output .= $this->displayError(implode('<br />', $errors));
            }
            else {
                $this->_output .= $this->displayConfirmation($this->l('Configuration updated'));
            }

        }

        return $this->_output . $this->_displayForm();
    }

        /**
     * Clean positions.
     *
     * @param int $idParent Parent ID
     *
     * @return bool
     */
    public function __updatePositions()
    {
        $idParent = Tab::getIdFromClassName('AdminMenuSecond');

        $result = Db::getInstance()->executeS('
			SELECT `id_tab`
			FROM `' . _DB_PREFIX_ . 'tab`
			WHERE `id_parent` = ' . (int) $idParent . '
			ORDER BY `position`
		');

        $idConfig = Tab::getIdFromClassName('AdminNrtThemeCustomizerConfig');
        $idFonts = Tab::getIdFromClassName('AdminNrtCustomFonts');

        $tabConfig = new Tab($idConfig);

        if($tabConfig->position < 2){
            return true;
        }

        $order = 3;

        $sizeof = count($result);
        for ($i = 0; $i < $sizeof; ++$i) {
            $_order = $order;
            if( (int) $idConfig == (int) $result[$i]['id_tab'] ){
                $_order = 1;
            }
            if( (int) $idFonts == (int) $result[$i]['id_tab'] ){
                $_order = 2;
            }
            Db::getInstance()->execute(
                '
				UPDATE `' . _DB_PREFIX_ . 'tab`
				SET `position` = ' . $_order . '
				WHERE `id_tab` = ' . (int) $result[$i]['id_tab']
            );
            $order++;
        }

        return true;
    }
	
	public static function get_config_data( $config_id ) {
		require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';
		
		$url = sprintf( 'https://api.axonviz.com/api_configs?config_id=%d', $config_id );
		
		$body_args = [];

		$response = Wp_Helper::wp_remote_get( $url, [
			'timeout' => 40,
			'body' => $body_args,
		] );

		if ( Wp_Helper::is_wp_error( $response ) ) {
			return [ 'success' => false, 'message' => 'Error.', 'config' => [] ];
		}

		$response_code = (int) Wp_Helper::wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return [ 'success' => false, 'message' => sprintf( 'The request returned with a status code of %s.', $response_code ), 'config' => [] ];
		}

		$config_data = json_decode( Wp_Helper::wp_remote_retrieve_body( $response ), true );

		if ( empty( $config_data ) || ! is_array( $config_data ) ) {
			return [ 'success' => false, 'message' => 'An error occurred, please try again ( no_json )', 'config' => [] ];
		}

		return [ 'success' => $config_data['success'], 'message' => $config_data['message'], 'data' => $config_data['data'] ];
	}
	
    public function ajaxProcessExportThemeConfiguration()
    {
        $var = array();
		
		foreach ($this->_configGlobals as $key => $default) {
			$var[$key] = Configuration::get($key);
		}

        $var['opThemect'] = $this->_getThemeConfig();
		
        header('Content-disposition: attachment; filename=export_theme_config.json');
        header('Content-type: application/json');
        print_r(json_encode($var));
        die;
    }
	
    private function _defineLayoutsArray()
    {
		$imgDir = $this->context->link->getMediaLink(_MODULE_DIR_.$this->name.'/views/img');
				
        // Mobile Header Style
		
		$this->_mobileheaderTypes = array();
		
		for ($i = 1; $i <= 2; $i++) {
			$this->_mobileheaderTypes[] = array(
                'value' => $i,
                'name' => $this->l('Mobile ').$i,
				'img' => $imgDir.'/mobile-header/mobile-header-'.$i.'.png'
            );	
		}
		
		$choose = array();
		
		$choose[0] = array(
			'id' => '0',
			'name' => $this->l(' - Choose (optional) - ')
		);
		
		$inherit = array();
		
		$inherit[0] = array(
			'id' => 'inherit',
			'name' => $this->l('Inherit')
		);
		
		$inherit[1] = array(
			'id' => '0',
			'name' => $this->l('Hidden')
		);
			
		$list_headers = array();
		$list_footers = array();
		$list_homes = array();
				
	    if(Module::isEnabled('axoncreator')) {
			$module = Module::getInstanceByName('axoncreator');
			// Header Style
            $list_headers  = $module->getListByPostType('header');
			// Footer Style
            $list_footers = $module->getListByPostType('footer');
			// HOme Style
			$list_homes = $module->getListByPostType('home');					
		}
		
		$this->_headerTypes = array_merge($choose, $list_headers);
		$this->_headerTypesOnPage = array_merge($inherit, $list_headers);
		
		$this->_footerTypes = array_merge($choose, $list_footers);
		$this->_footerTypesOnPage = array_merge($inherit, $list_footers);
		
		$this->_homeTypes = array_merge($choose, $list_homes);
				
		$this->_productTypes = array();
		
		for ($i = 1; $i <= 30; $i++) {
			$this->_productTypes[] = array(
                'value' => $i,
                'name' => $this->l('Product ').$i,
				'img' => $imgDir.'/product-style/product-'.$i.'.png'
            );	
		}
		
        $this->_layoutWidthTypes = array(
            array(
                'value' => 'container-fluid',
                'name' => 'Wide'
            ),
            array(
                'value' => 'container-fluid max-width-1600',
                'name' => 'Wide(max-width 1600px)'
            ),
            array(
                'value' => 'container-fluid max-width-1400',
                'name' => 'Wide(max-width 1400px)'
            ),
            array(
                'value' => 'container',
                'name' => 'Boxed'
            )
        );
		
		        // Product Layout
        $this->_productLayout = array(
            array(
                'value' => '1',
                'name' => $this->l('Type 1'),
				'img' => $imgDir.'/product-layout/layout-1.png'
            ),
            array(
                'value' => '2',
                'name' => $this->l('Type 2'),
				'img' => $imgDir.'/product-layout/layout-2.png'
            ),
            array(
                'value' => '3',
                'name' => $this->l('Type 3'),
				'img' => $imgDir.'/product-layout/layout-3.png'
            ),
            array(
                'value' => '4',
                'name' => $this->l('Type 4'),
				'img' => $imgDir.'/product-layout/layout-4.png'
            ),
            array(
                'value' => '5',
                'name' => $this->l('Type 5'),
				'img' => $imgDir.'/product-layout/layout-5.png'
            ),
            array(
                'value' => '6',
                'name' => $this->l('Type 6'),
				'img' => $imgDir.'/product-layout/layout-6.png'
            ),
            array(
                'value' => '7',
                'name' => $this->l('Type 7'),
				'img' => $imgDir.'/product-layout/layout-7.png'
            ),
            array(
                'value' => '8',
                'name' => $this->l('Type 8'),
				'img' => $imgDir.'/product-layout/layout-8.png'
            ),
            array(
                'value' => '9',
                'name' => $this->l('Type 9'),
				'img' => $imgDir.'/product-layout/layout-9.png'
            ),
            array(
                'value' => '10',
                'name' => $this->l('Type 10'),
				'img' => $imgDir.'/product-layout/layout-10.png'
            ),
        );
		
		$this->_tabsType = array(
            array(
                'value' => 1,
                'name' => 'Horizontal Tabs'
            ),
            array(
                'value' => 3,
                'name' => 'Accordion Tabs'
            ),
            array(
                'value' => 4,
                'name' => 'Accordion Tabs (Show all)'
            )
        );
		
		        // Category Layout
        $this->_categoryLayout = array(
            array(
                'value' => '1',
                'name' => $this->l('Left Sidebar'),
				'img' => $imgDir.'/category-layout/layout-1.png'
            ),
            array(
                'value' => '2',
                'name' => $this->l('Right Sidebar'),
				'img' => $imgDir.'/category-layout/layout-2.png'
            ),
            array(
                'value' => '3',
                'name' => $this->l('One Column'),
				'img' => $imgDir.'/category-layout/layout-3.png'
            )
        );
	}
	
    /* ------------------------------------------------------------- */
    /*  DISPLAY CONFIGURATION FORM
    /* ------------------------------------------------------------- */
    private function _displayForm()
    {
        $id_default_lang = $this->context->language->id;
        $languages = $this->context->language->getLanguages();
        $id_shop = $this->context->shop->id;
		
		$this->_defineLayoutsArray();
		
        $ad_row = array(
			array('value'=>'1'),
			array('value'=>'2'),
			array('value'=>'3'),
			array('value'=>'4'),
			array('value'=>'5'),
			array('value'=>'6'),
			array('value'=>'7'),
			array('value'=>'8'),
			array('value'=>'9'),
			array('value'=>'10'),
        );
		
        $ad_space_item = array(
			array('value'=>'0'),
			array('value'=>'5'),
			array('value'=>'10'),
			array('value'=>'15'),
			array('value'=>'20'),
			array('value'=>'25'),
			array('value'=>'30'),
			array('value'=>'35'),
			array('value'=>'40'),
			array('value'=>'45'),
			array('value'=>'50'),
        );
		
		$images_formats = ImageType::getImagesTypes('products');
		$images_type = array();
				
		foreach ($images_formats as $key => $image) {
			$images_type[$key]['value'] = $image['name'];
			$images_type[$key]['name'] = $image['name'];
		}
		
		$c_images_formats = ImageType::getImagesTypes('categories');
		$c_images_type = array();
				
		foreach ($c_images_formats as $key => $image) {
			$c_images_type[$key]['value'] = $image['name'];
			$c_images_type[$key]['name'] = $image['name'];
		}
		
        $yes_no = array(
			array('value'=>'0','name'=>$this->l('No')),
			array('value'=>'1','name'=>$this->l('Yes'))
        );
        // General Options
        $layoutTypes = array(
            array(
                'value' => 'wide',
                'name' => 'Wide'
            ),
            array(
                'value' => 'boxed',
                'name' => 'Boxed'
            )
        );
				
        $layoutPageContentTypes = array(
            array(
                'value' => 1,
                'name' => 'Type 1'
            )
        );
		
        // Background Options
        $backgroundRepeatOptions = array(
            array(
                'value' => 'repeat-x',
                'name' => 'Repeat-X'
            ),
            array(
                'value' => 'repeat-y',
                'name' => 'Repeat-Y'
            ),
            array(
                'value' => 'repeat',
                'name' => 'Repeat Both'
            ),
            array(
                'value' => 'no-repeat',
                'name' => 'No Repeat'
            )
        );

        $backgroundAttachmentOptions = array(
            array(
                'value' => 'scroll',
                'name' => 'Scroll'
            ),
            array(
                'value' => 'fixed',
                'name' => 'Fixed'
            )
        );

        $backgroundSizeOptions = array(
            array(
                'value' => 'auto',
                'name' => 'Auto'
            ),
            array(
                'value' => 'cover',
                'name' => 'Cover'
            )
        );

        // Font Options
        $fontOptions = array();
		
		$fontOptions[] = array(
			'value' => 'None',
			'name' => 'Default'
		);	

		$custom_fonts = self::get_fonts();
		
		foreach ($custom_fonts as $key => $custom_font) {			
			$this->_websafeFonts[] = $custom_font['font_name'];
		}	

        foreach ($this->_websafeFonts as $fontName){
			$fontOptions[] = array(
				'value' => $fontName,
				'name' => $fontName
			);
        }

        // Google Fonts
		$this->_googleFonts = require_once(dirname(__FILE__).'/googlefonts.php');

        foreach ($this->_googleFonts as $fontName){
			if(!in_array($fontName, $this->_websafeFonts)){
				$fontOptions[] = array(
					'value' => $fontName,
					'name' => $fontName
				);
			}
        }
		
        $fields_form = array(
            'general' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('General')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'general_main_layout',
                            'label' => $this->l('Layout type'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $layoutTypes,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'general_back_top',
                            'label' => $this->l('Show Button BackTop'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'general_affix_scroll',
                            'label' => $this->l('Sticky Left/Right-column'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'general_header_sticky_affix',
                            'label' => $this->l('Sticky Header Affix'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
							'type' => 'select',
							'label' => $this->l('Second Product Image'),
							'name' => 'general_product_image_second',
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
								array('value' => 		0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
							'type' => 'select',
							'label' => $this->l('Orther Small Image Size'),
							'name' => 'general_product_image_type_small',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'label' => $this->l('Orther Large Image Size'),
							'name' => 'general_product_image_type_large',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						//////////////////////////////////xl
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Large Desktop (Min width 1025px)')
						),
						array(
                            'type' => 'text',
                            'label' => $this->l('Container max width'),
                            'desc' => $this->l('Set maxium width of page. You must provide px or percent suffix (example 1200px or 100%)'),
                            'name' => 'general_container_max_width',
                            'required' => false,
                            'class' => 'fixed-width-xxl'
                        ),
						array(
                            'type' => 'select',
                            'name' => 'general_column_space',
                            'label' => $this->l('Distance between columns'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 0, 'name' => 'Default'),
										array('value' => 30, 'name' => '30px'),
										array('value' => 50, 'name' => '50px')
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'header' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Header')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'active_header_layout',
                            'label' => $this->l('Header layout'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypes,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'active_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypes,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),				
		    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'homepage' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Homepage')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'active_home_layout',
                            'label' => $this->l('HomePage layout'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_homeTypes,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'index_open_vertical_menu',
                            'label' => $this->l('Open vertical menu on homepage'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 0, 'name' => $this->l('No')),
										array('value' => 1, 'name' => $this->l('Yes')),
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'index_header_layout',
                            'label' => $this->l('Header layout on homepage'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'index_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout on homepage'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'index_header_overlap',
                            'label' => $this->l('Header overlap on homepage'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'index_footer_layout',
                            'label' => $this->l('Footer layout on homepage'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
		    		),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'footer' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Footer')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'active_footer_layout',
                            'label' => $this->l('Footer layout'),
							'class' => 'fixed-width-xxl',
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypes,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'select',
							'name' => 'general_footer_fixed',
							'label' => $this->l('Footer fixed'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'1','name'=>$this->l('Yes')),
										array('value'=>'0','name'=>$this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
						)
		    		),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'page_title' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Page title')
					),
                    'input' => array(
						array(
							'type' => 'select',
							'name' => 'page_title_layout',
							'label' => $this->l('Page Title layout'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=> '0', 'name'=>$this->l('Hide')),
										array('value'=> '1', 'name'=>$this->l('Normal')),
										array('value'=> '2', 'name'=>$this->l('Small')),
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
                        array(
                            'type' => 'chose_image',
                            'name' => 'bg_page_title_img',
                            'label' => $this->l('Background Image Page Title'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
						array(
							'type' => 'select',
							'name' => 'page_title_color',
							'label' => $this->l('Page Title color'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'dark', 'name'=>$this->l('Dark')),
										array('value'=>'light', 'name'=>$this->l('Light')),
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
		    		),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'contact' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Contact page')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'contact_override_content_by_hook',
                            'label' => $this->l('Override content by hook "displayContactPageBuilder" ( edit with AxonCreator )'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 0, 'name' => $this->l('No')),
										array('value' => 1, 'name' => $this->l('Yes')),
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'contact_header_layout',
                            'label' => $this->l('Header layout on contact page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'contact_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout on contact page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'contact_header_overlap',
                            'label' => $this->l('Header overlap on contact page'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'contact_footer_layout',
                            'label' => $this->l('Footer layout on contact page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'select',
							'name' => 'contact_page_title_layout',
							'label' => $this->l('Page Title layout on contact page'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
									array('value' => 'inherit', 'name' => $this->l('Inherit')),
									array('value' => '0', 'name' => $this->l('Hide')),
									array('value' => '1', 'name' => $this->l('Normal')),
									array('value' => '2', 'name' => $this->l('Small'))
								),
								'id' => 'value',
								'name' => 'name'
							)
						),
					),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
			'404' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('404 page')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => '404_override_content_by_hook',
                            'label' => $this->l('Override content by hook "display404PageBuilder" ( edit with AxonCreator )'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 0, 'name' => $this->l('No')),
										array('value' => 1, 'name' => $this->l('Yes')),
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),	
						array(
                            'type' => 'select',
                            'name' => '404_header_layout',
                            'label' => $this->l('Header layout on 404 page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => '404_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout on 404 page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),	
						array(
                            'type' => 'select',
                            'name' => '404_header_overlap',
                            'label' => $this->l('Header overlap on 404 page'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => '404_footer_layout',
                            'label' => $this->l('Footer layout on 404 page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'select',
							'name' => '404_page_title_layout',
							'label' => $this->l('Page Title layout on 404 page'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
									array('value' => 'inherit', 'name' => $this->l('Inherit')),
									array('value' => '0', 'name' => $this->l('Hide')),
									array('value' => '1', 'name' => $this->l('Normal')),
									array('value' => '2', 'name' => $this->l('Small'))
								),
								'id' => 'value',
								'name' => 'name'
							)
						),
					),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'category_pages' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Category Pages')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'category_header_layout',
                            'label' => $this->l('Header layout on category page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'category_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout on category page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'category_header_overlap',
                            'label' => $this->l('Header overlap on category page'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'category_footer_layout',
                            'label' => $this->l('Footer layout on category page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'select',
							'name' => 'category_page_title_layout',
							'label' => $this->l('Page Title layout on category page'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
									array('value' => 'inherit', 'name' => $this->l('Inherit')),
									array('value' => '0', 'name' => $this->l('Hide')),
									array('value' => '1', 'name' => $this->l('Normal')),
									array('value' => '2', 'name' => $this->l('Small'))
								),
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Category Setup')
						),
						array(
                            'type' => 'select',
                            'name' => 'category_show_sub',
                            'label' => $this->l('Show Subcategories'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
							'type' => 'select',
							'name' => 'category_default_view',
							'label' => $this->l('Default view '),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'0','name'=>$this->l('Grid')),
										array('value'=>'1','name'=>$this->l('List'))
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'name' => 'category_banner_layout',
							'label' => $this->l('Banner layout '),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'0','name'=>$this->l('Full Width in Header title')),
										array('value'=>'1','name'=>$this->l('Boxed Banner'))
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'label' => $this->l('Banner Image Size '),
							'name' => 'category_image_type',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $c_images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'name' => 'category_product_infinite',
							'label' => $this->l('Pagination'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'0','name'=>$this->l('Pagination')),
										array('value'=>'1','name'=>$this->l('Load More')),
										array('value'=>'2','name'=>$this->l('Infinit scrolling')),
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'name' => 'category_faceted_position',
							'label' => $this->l('Faceted search position'),
							'class' => 'fixed-width-xxl',
                            'desc' => $this->l('If enabled Faceted search will be showed above product list. It is great for one column layouts. If you enable this you should probably unhook ps_facetedsearch from displayLeftColumn hook '),
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'0','name'=>$this->l('Default')),
										array('value'=>'1','name'=>$this->l('CanVas')),
										array('value'=>'2','name'=>$this->l('Midle Column')),
										array('value'=>'3','name'=>$this->l('Midle Column ( Dropdown )'))
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Category Layout')
						),
						array(
                            'type' => 'select',
                            'name' => 'category_layout_width_type',
                            'label' => $this->l('Width container'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_layoutWidthTypes,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'chose_style',
                            'name' => 'category_layout',
                            'label' => $this->l('Category Layout'),
							'values' => $this->_categoryLayout
                        ),	
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Products Grid Options')
						),
						array(
                            'type' => 'chose_style',
                            'name' => 'category_product_layout',
                            'label' => $this->l('Grid layout'),
							'values' => $this->_productTypes
                        ),
						array(
							'type' => 'select',
							'label' => $this->l('Products Image Size '),
							'name' => 'category_product_image_type',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
                            'type' => 'select',
                            'name' => 'category_product_hide_review',
                            'label' => $this->l('Hide Products Review'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
									array('value' => 0, 'name' => $this->l('No')),
									array('value' => 1, 'name' => $this->l('Yes'))
								),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
                            'type' => 'select',
                            'name' => 'category_product_hide_variant',
                            'label' => $this->l('Hide Products Variant'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
									array('value' => 0, 'name' => $this->l('No')),
									array('value' => 1, 'name' => $this->l('Yes'))
								),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						//////////////////////xl
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Desktop Large')
						),
						array(
							'type' => 'select',
							'label' => $this->l('Products per line'),
							'name' => 'category_product_xl',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_row,
								'id' => 'value',
								'name' => 'value'
							)
						),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Space between items'),
                            'name' => 'category_product_space_xl',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_space_item,
								'id' => 'value',
								'name' => 'value'
							)
                        ),
						//////////////////////lg
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Desktop')
						),
						array(
							'type' => 'select',
							'label' => $this->l('Products per line'),
							'name' => 'category_product_lg',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_row,
								'id' => 'value',
								'name' => 'value'
							)
						),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Space between items'),
                            'name' => 'category_product_space_lg',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_space_item,
								'id' => 'value',
								'name' => 'value'
							)
                        ),
						//////////////////////sm
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Tablet')
						),
						array(
							'type' => 'select',
							'name' => 'category_product_md',
							'label' => $this->l('Products per line'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_row,
								'id' => 'value',
								'name' => 'value'
							)
						),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Space between items'),
                            'name' => 'category_product_space_md',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_space_item,
								'id' => 'value',
								'name' => 'value'
							)
                        ),
						//////////////////////xs
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Mobile')
						),
						array(
							'type' => 'select',
							'name' => 'category_product_xs',
							'label' => $this->l('Products per line - phone'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_row,
								'id' => 'value',
								'name' => 'value'
							)
						),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Space between items'),
                            'name' => 'category_product_space_xs',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $ad_space_item,
								'id' => 'value',
								'name' => 'value'
							)
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'product_pages' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Product Detail Page')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'product_header_layout',
                            'label' => $this->l('Header layout on product page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'product_header_sticky_layout',
                            'label' => $this->l('Header Sticky layout on product page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_headerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),	
						array(
                            'type' => 'select',
                            'name' => 'product_footer_layout',
                            'label' => $this->l('Footer layout on product page'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_footerTypesOnPage,
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
						
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Product Setup')
						),
						
						array(
                            'type' => 'select',
                            'name' => 'product_layout_width_type',
                            'label' => $this->l('Width container'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $this->_layoutWidthTypes,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'product_show_buy_now',
                            'label' => $this->l('Show BuyNow'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
                        array(
                            'type' => 'chose_style',
                            'name' => 'product_layout',
                            'label' => $this->l('Product Detail Layout'),
							'values' => $this->_productLayout
                        ),
						array(
							'type' => 'select',
							'label' => $this->l('Cover Image Size '),
							'name' => 'product_image_type',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'label' => $this->l('Thumb Image Size '),
							'name' => 'product_image_thumb_type',
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $images_type,
								'id' => 'value',
								'name' => 'name'
							)
						),
						array(
							'type' => 'select',
							'name' => 'product_tabs_type',
							'label' => $this->l('Tabs Type'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => $this->_tabsType,
								'id' => 'value',
								'name' => 'name'
							)
						)
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'fonts' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Fonts')
					),
                    'input' => array(
                        array(
                            'type' => 'select',
                            'name' => 'font_body',
                            'label' => $this->l('Main Font Family'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $fontOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'font_title',
                            'label' => $this->l('Title Font Family'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $fontOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for - Desktop (Min width 1025px)')
						),
						array(
                            'type' => 'text',
                            'label' => $this->l('Font size'),
                            'desc' => $this->l('Changing it affects the font size of the whole site (example 62.5)'),
                            'name' => 'font_size_lg',
							'suffix' => '%',
                            'required' => false,
                            'class' => 'fixed-width-xxl'
                        ),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Setup for Tablet - Mobile (Max width 1024px)')
						),
						array(
                            'type' => 'text',
                            'label' => $this->l('Font size'),
                            'desc' => $this->l('Changing it affects the font size of the whole site (example 62.5)'),
                            'name' => 'font_size_xs',
							'suffix' => '%',
                            'required' => false,
                            'class' => 'fixed-width-xxl'
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
			'input_btn_label' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Input / Button / Product Label')
					),
                    'input' => array(
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Input')
						),
                        array(
                            'type' => 'select',
                            'name' => 'input_style',
                            'label' => $this->l('Input style'),
                            'required' => false,
                            'options' => array(
                                'query' => [
									['value' => 'rectangular', 'name' => 'Rectangular'],
									['value' => 'circle', 'name' => 'Circle'],
									['value' => 'round', 'name' => 'Round']
								],
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'input_border_width',
                            'label' => $this->l('Input boder width'),
                            'required' => false,
                            'options' => array(
                                'query' => [
									['value' => 1, 'name' => '1'],
									['value' => 2, 'name' => '2'],
									['value' => 3, 'name' => '3']
								],
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Button')
						),
                        array(
                            'type' => 'select',
                            'name' => 'button_style',
                            'label' => $this->l('Button style'),
                            'required' => false,
                            'options' => array(
                                'query' => [
									['value' => 'rectangular', 'name' => 'Rectangular'],
									['value' => 'circle', 'name' => 'Circle'],
									['value' => 'round', 'name' => 'Round']
								],
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
                            'type' => 'select',
                            'name' => 'button_border_width',
                            'label' => $this->l('Button boder width'),
                            'required' => false,
                            'options' => array(
                                'query' => [
									['value' => 1, 'name' => '1'],
									['value' => 2, 'name' => '2'],
									['value' => 3, 'name' => '3']
								],
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
						array(
							'type' => 'title_label',
							'name' => '',
							'label' => $this->l('Label')
						),
						array(
                            'type' => 'select',
                            'name' => 'product_label',
                            'label' => $this->l('Product Label Style'),
                            'required' => false,
                            'options' => array(
                                'query' => [
									['value' => 'rectangular', 'name' => 'Rectangular'],
									['value' => 'circle', 'name' => 'Circle'],
								],
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'colors' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Colors')
					),
                    'input' => array(
						array(
                            'type' => 'select',
                            'name' => 'color_scheme_dark',
                            'label' => $this->l('Turn on dark mode'),
                            'required' => false,
                            'lang' => false,
							'options' => array(
								'query' => array(
										array('value' => 1, 'name' => $this->l('Yes')),
										array('value' => 0, 'name' => $this->l('No'))
									),
								'id' => 'value',
								'name' => 'name'
							)
                        ),
						array(
							'type' => 'select',
							'name' => 'button_color',
							'label' => $this->l('Button color'),
							'class' => 'fixed-width-xxl',
							'required' => false,
							'options' => array(
								'query' => array(
										array('value'=>'dark', 'name'=>$this->l('Dark')),
										array('value'=>'light', 'name'=>$this->l('Light')),
									),
								'id' => 'value',
								'name' => 'name'
							)
						),
                        array(
                            'type' => 'color',
                            'name' => 'color_primary',
                            'label' => $this->l('Primary color scheme'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'color',
                            'name' => 'color_price',
                            'label' => $this->l('Price color scheme'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
						array(
                            'type' => 'color',
                            'name' => 'color_new_label',
                            'label' => $this->l('Label "NEW" color scheme'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
						array(
                            'type' => 'color',
                            'name' => 'color_sale_label',
                            'label' => $this->l('Label "SALE" color scheme'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'background' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Background')
					),
                    'input' => array(
                        array(
                            'type' => 'color',
                            'name' => 'background_color',
                            'label' => $this->l('Background color'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'chose_image',
                            'name' => 'background_img',
                            'label' => $this->l('Background image'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_img_repeat',
                            'label' => $this->l('Background repeat'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundRepeatOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_img_attachment',
                            'label' => $this->l('Background attachment'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundAttachmentOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_img_size',
                            'label' => $this->l('Background size'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundSizeOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'line_driver',
							'name' => '',
                        ),
                        array(
                            'type' => 'color',
                            'name' => 'background_body_color',
                            'label' => $this->l('Body background color'),
                            'desc' => $this->l('Body background color only visible in "Boxed" mode.'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'chose_image',
                            'name' => 'background_body_img',
                            'label' => $this->l('Body background image'),
                            'desc' => $this->l('Body background image only visible in "Boxed" mode.'),
                            'size' => 20,
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_body_img_repeat',
                            'label' => $this->l('Body background repeat'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundRepeatOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_body_img_attachment',
                            'label' => $this->l('Body background attachment'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundAttachmentOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => 'background_body_img_size',
                            'label' => $this->l('Body background size'),
                            'required' => false,
                            'lang' => false,
                            'options' => array(
                                'query' => $backgroundSizeOptions,
                                'id' => 'value',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'line_driver',
							'name' => '',
                        ),
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'style_on_theme' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Style on theme')
					),
                    'input' => array(
                        array(
                            'type' => 'no_label',
                            'name' => 'style_on_theme',
                            'label' => '',
                        )
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'custom_codes' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Custom Codes')
					),
                    'input' => array(
                        array(
                            'type' => 'textarea',
                            'name' => 'custom_css',
                            'rows' => 10,
                            'label' => $this->l('Custom CSS Code'),
                            'required' => false,
                            'lang' => false
                        ),
                        array(
                            'type' => 'textarea',
                            'name' => 'custom_js',
                            'rows' => 10,
                            'label' => $this->l('Custom JS Code'),
                            'required' => false,
                            'lang' => false
                        )
                    ),
                    // Submit Button
                    'submit' => array(
                        'title' => $this->l('Save all'),
                        'name' => 'savenrtThemeConfig'
                    )
                )
            ),
            'import_export' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Import / Export')
					),
                    'input' => array(
                        array(
                            'type' => 'no_label',
                            'name' => 'NRT_import_export',
                            'label' => '',
                        )
                    )
                )
            ),
            'add_hook_for_theme' => array(
                'form' => array(
					'legend' => array(
						'title' => $this->l('Add Hooks For Theme / Clear Cache')
					),
                    'input' => array(
                        array(
                            'type' => 'no_label',
                            'name' => 'NRT_add_hook_for_theme',
                            'label' => '',
                        )
                    )
                )
            )
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $helper->default_form_language = $id_default_lang;
        $helper->allow_employee_form_lang = $id_default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            )
        );

        foreach ($languages as $language) {
            $helper->languages[] = array(
                'id_lang' => $language['id_lang'],
                'iso_code' => $language['iso_code'],
                'name' => $language['name'],
                'is_default' => ($id_default_lang == $language['id_lang'] ? 1 : 0)
            );
        }
		
		$config = $this->_getThemeConfig();
		
        // Load config field values
		foreach ($this->_configDefaults as $key => $default) {
            if($key == 'custom_js'){ 
                $helper->fields_value[$key] = isset($config[$key]) ? html_entity_decode($config[$key]) : html_entity_decode($default['value']);
            }else{
                $helper->fields_value[$key] = isset($config[$key]) ? $config[$key] : $default['value'];
            }
        }
		
		foreach ($this->_configGlobals as $key => $default) {
			$helper->fields_value[$key] = Configuration::get($key);
		}
		
        $tabArray = array(
            'General' 			=> 	'fieldset_general',
            'Header' 			=> 	'fieldset_header_1',
            'Homepage' 			=> 	'fieldset_homepage_2',
			'Footer' 			=> 	'fieldset_footer_3',
			'Page Title' 		=> 	'fieldset_page_title_4',
			'Contact Page' 		=> 	'fieldset_contact_5',
			'404 Page' 			=> 	'fieldset_404_6',
            'Category Pages' 	=> 	'fieldset_category_pages_7',
            'Product Detail Page' 	=> 	'fieldset_product_pages_8',
            'Fonts' 			=> 	'fieldset_fonts_9',
            'Input / Button / Product Label'   => 	'fieldset_input_btn_label_10',
            'Colors' 			=> 	'fieldset_colors_11',
            'Background' 		=> 	'fieldset_background_12',
            'Style on theme'    => 	'fieldset_style_on_theme_13',
            'Custom Codes' 		=> 	'fieldset_custom_codes_14',
            'Import / Export' 	=> 	'fieldset_import_export_15',
			'Add Hook For Theme / Clear Cache' 	=> 	'fieldset_add_hook_for_theme_16',
        );

        // Custom variables
        $helper->tpl_vars = array(
			'export_link' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&ajax=1&action=exportThemeConfiguration',
            'nrttabs' => $tabArray,
            'shopId' => $id_shop
        );

        return $helper->generateForm($fields_form);
    }

    /* ------------------------------------------------------------- */
    /*  WRITE CSS
    /* ------------------------------------------------------------- */
    private function _writeCss()
    {		
        $config = $this->_getThemeConfig();

        // Starting of the cssCode
        $cssCode = '';
		
		/////////////////////////////////////////////////////////////////
		
		if($config['background_color']){		
			$cssCode .= 'main{background-color: '.$config['background_color'].';}';
		}
		
		if($config['background_img']){		
			$cssCode .= 'main{';
			$cssCode .= 'background-image: url("'.$config['background_img'].'");';
			$cssCode .= 'background-repeat: '.$config['background_img_repeat'].';';
			$cssCode .= 'background-attachment: '.$config['background_img_attachment'].';';
			$cssCode .= 'background-size: '.$config['background_img_size'].';';
			$cssCode .= '}';
		}
		
		/////////////////////////////////////////////////////////////////
		
		if($config['background_body_color']){		
			$cssCode .= 'body{background-color: '.$config['background_body_color'].';}';
		}
		
		if($config['background_body_img']){		
			$cssCode .= 'body{';
			$cssCode .= 'background-image: url("'.$config['background_body_img'].'");';
			$cssCode .= 'background-repeat: '.$config['background_body_img_repeat'].';';
			$cssCode .= 'background-attachment: '.$config['background_body_img_attachment'].';';
			$cssCode .= 'background-size: '.$config['background_body_img_size'].';';
			$cssCode .= '}';
		}
		
		/////////////////////////////////////////////////////////////////
		
		$cssCode .= ':root{';
			if($config['font_body'] != 'None'){		
				$cssCode .= '--font-family-body: "'.$config['font_body'].'", "Helvetica", "Arial", "sans-serif";';
			}
			if($config['font_title'] != 'None'){		
				$cssCode .= '--font-family-label: "'.$config['font_title'].'", "Helvetica", "Arial", "sans-serif";';
				$cssCode .= '--font-family-semi: "'.$config['font_title'].'", "Helvetica", "Arial", "sans-serif";';
				$cssCode .= '--font-family-title: "'.$config['font_title'].'", "Helvetica", "Arial", "sans-serif";';
			}
			if($config['color_primary']){	
				$cssCode .= '--color-a-hover: '.$config['color_primary'].';';
				$cssCode .= '--color-primary: '.$config['color_primary'].';';
				$cssCode .= '--color-secondary: '.$config['color_primary'].';';
				$cssCode .= '--bg-color-btn: '.$config['color_primary'].';';
				$cssCode .= '--bg-color-btn-hover: '.$config['color_primary'].';';
			}
			if($config['button_color'] == 'dark'){
				$cssCode .= '--color-btn: #333333;';
                $cssCode .= '--color-btn-hover: #333333;';
			}
			if($config['color_price']){		
				$cssCode .= '--color-price: '.$config['color_price'].';';
			}
			if($config['color_new_label']){		
				$cssCode .= '--bg-color-label-new: '.$config['color_new_label'].';';
			}
			if($config['color_sale_label']){		
				$cssCode .= '--bg-color-label-sale: '.$config['color_sale_label'].';';
			}
			if($config['input_style'] == 'circle'){		
				$cssCode .= '--ax-form-bi-rd: 35px;';
			}
			if($config['input_style'] == 'round'){		
				$cssCode .= '--ax-form-bi-rd: 5px;';
			}
			if($config['input_border_width']){		
				$cssCode .= '--ax-form-bi-width: '.$config['input_border_width'].'px;';
			}
			if($config['button_style'] == 'circle'){		
				$cssCode .= '--ax-form-btn-rd: 35px;';
			}
			if($config['button_style'] == 'round'){		
				$cssCode .= '--ax-form-btn-rd: 5px;';
			}
			if($config['button_border_width']){		
				$cssCode .= '--ax-form-btn-width: '.$config['button_border_width'].'px;';
			}
		$cssCode .= '}';
		
		/////////////////////////////////////////////////////////////////
		
		if($value = $config['category_product_space_xs']){		
			$cssCode .= '@media (max-width: 767px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_space_md']){			
			$cssCode .= '@media (min-width: 768px) and (max-width: 1024px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_space_lg']){			
			$cssCode .= '@media (min-width: 1025px) and (max-width: 1199px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_space_xl']){			
			$cssCode .= '@media (min-width: 1200px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_xs']){		
			$cssCode .= '@media (max-width: 767px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_md']){		
			$cssCode .= '@media (min-width: 768px) and (max-width: 1024px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_lg']){		
			$cssCode .= '@media (min-width: 1025px) and (max-width: 1199px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = $config['category_product_xl']){		
			$cssCode .= '@media (min-width: 1200px){';
			$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		////////////////////////////////////////////////////////
				
		if($value = $config['font_size_xs']){			
			$cssCode .= '@media (max-width: 1024px){';
			$cssCode .= 'html{font-size: '.$value.'%;}';
			$cssCode .= '}';
		}

		if($value = $config['font_size_lg']){			
			$cssCode .= '@media (min-width: 1025px){';
			$cssCode .= 'html{font-size: '.$value.'%;}';
			$cssCode .= '}';
		}

		////////////////////////////////////////////////////////
		
		if($value = $config['general_container_max_width']){
			$cssCode .= '@media (min-width: 1025px){';
			$cssCode .= '.container{max-width:'.$value.';}';
			$cssCode .= '}';
		}

		////////////////////////////////////////////////////////
		
		if($value = $config['general_header_sticky_affix']){
			$cssCode .= 'header.is-sticked.is-scroll-up #header-sticky, header.is-sticked.is-scroll-down #header-sticky{-webkit-transform: none; transform: none; visibility: visible; opacity: 1; pointer-events: all;}';
		}
				
		/////////////////////////////////////////////////////////////
		
		if($config['style_on_theme'] != ''){
			$styles = json_decode($config['style_on_theme'], true);
			
			foreach ($styles as $style) {
				if(!isset($style['label'])){
					$cssCode .= $style['selector'].'{'.$style['params'].':'.$style['value'].';}';
				}	
			}
		}
		
		if($config['custom_css'] != ''){
			$cssCode .= $config['custom_css'];
		}		
				
        $cssCode = trim(preg_replace('/\s+/', ' ', $cssCode));
		$id_shop = (int)$this->context->shop->id;

		$cssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/front/custom_s_' . $id_shop . '.css';

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

    /* ------------------------------------------------------------- */
    /*  WRITE JS
    /* ------------------------------------------------------------- */
    private function _writeJs()
    {
		$config = $this->_getThemeConfig();
		
        $jsCode = '';	
		
		if($config['custom_js'] != ''){
			$jsCode .= html_entity_decode($config['custom_js']);
		}
		
		$id_shop = (int)$this->context->shop->id;

		$jsFile = _PS_MODULE_DIR_ . $this->name . '/views/js/front/custom_s_' . $id_shop . '.js';

		if($jsCode){
			if(file_put_contents($jsFile, $jsCode)){
				return true;
			}else{
				return false;
			}
		}else{
			if(file_exists($jsFile)){
				unlink($jsFile);
			}
		} 

    }
    /* ------------------------------------------------------------- */
    /*  GET THEME CONFIG
    /* ------------------------------------------------------------- */
    private function _getThemeConfig()
    {
        return json_decode( Configuration::get('opThemect'), true );
    }
	
	/*-------------------------------------------------------------*/
    /*  HOOK (actionAdminControllerSetMedia) */
    /* ------------------------------------------------------------- */

    public function hookActionObjectShopUpdateAfter()
    {
		//$this->axps_register_hook();
    }

	/*-------------------------------------------------------------*/
    /*  HOOK (actionAdminControllerSetMedia) */
    /* ------------------------------------------------------------- */

    public function hookActionAdminControllerSetMedia()
    {
		if (Tools::isSubmit('submitRegenerateimage_type')) {
			$this->_writeImgCss();
		}
    }
	
    /* ------------------------------------------------------------- */
    /*  Standar Font google
    /* ------------------------------------------------------------- */
    public function StandardFont($configName)
    {
		$config = $this->_getThemeConfig();

		$custom_fonts = self::get_fonts();
		
		foreach ($custom_fonts as $key => $custom_font) {			
			$this->_websafeFonts[] = $custom_font['font_name'];
		}	
		
		if(!$config[$configName] || $config[$configName] == 'None' || in_array($config[$configName], $this->_websafeFonts)){
			return false;	
		}
		
		$font = str_replace( ' ', '+', $config[$configName] ) . ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';

		$fonts_url = sprintf( 'https://fonts.googleapis.com/css?family=%s', $font );

		$subsets = [
			'ru_RU' => 'cyrillic',
			'bg_BG' => 'cyrillic',
			'he_IL' => 'hebrew',
			'el' => 'greek',
			'vi' => 'vietnamese',
			'uk' => 'cyrillic',
			'cs_CZ' => 'latin-ext',
			'ro_RO' => 'latin-ext',
			'pl_PL' => 'latin-ext',
		];
			
		$locale= \Context::getContext()->language->iso_code;

		if ( isset( $subsets[ $locale ] ) ) {
			$fonts_url .= '&subset=' . $subsets[ $locale ];
		}
		
		return $fonts_url;
    }
	
    public function _customCss()
    {
        $id_shop = $this->context->shop->id;

        if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/views/css/front/custom_s_' . $id_shop . '.css')) {
            $this->_writeCss();
        }
		
        $cssFile = 'custom_s_' . $id_shop . '.css';
		$this->context->controller->registerStylesheet(
			'configCss', 
			'modules/'.$this->name.'/views/css/front/' . $cssFile, 
			['position' => 'bottom', 'priority' => 999]
		);
		
        $jsFile = 'custom_s_' . $id_shop . '.js';
		$this->context->controller->registerJavascript(
			'configJs', 
			'modules/'.$this->name.'/views/js/front/' . $jsFile, 
			['position' => 'bottom', 'priority' => 999]
		);
		
    }
	
	public function themect_parse_args($args, $defaults = '')
	{
		if (is_array($args)) {
			$r = &$args;
		} else {
			parse_str($args, $r);
		}

		if (is_array($defaults)) {
			return array_merge($defaults, $r);
		}
		
		return $r;
	}
	
    public function _getPageConfigOnFront()
    {	
		$pageConfig = [];
		
		$config = $this->_getThemeConfig();
		
        if (!empty($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        } else {
			$controller = Dispatcher::getInstance()->getController();
		}
		
		$controller = Tools::strtolower( $controller );
								
        switch ( $controller ) {
            case 'index':
				$pageConfig['open_vertical_menu'] = $config['index_open_vertical_menu'];

				if($config['index_header_layout'] != 'inherit'){
					$pageConfig['header_layout'] = $config['index_header_layout'];
				}

				if($config['index_header_sticky_layout'] != 'inherit'){
					$pageConfig['header_sticky_layout'] = $config['index_header_sticky_layout'];
				}
				
				$pageConfig['header_overlap'] = $config['index_header_overlap'];
				
				if($config['index_footer_layout'] != 'inherit'){
					$pageConfig['footer_layout'] = $config['index_footer_layout'];
				}
                break;
			case 'cms':
				$pageConfig = $this->_getThemeCtPageConfig((int)Tools::getValue('id_cms'), 'cms');
                break;
			case 'product':
				$pageConfig = $this->_getThemeCtPageConfig((int)Tools::getValue('id_product'), 'product');
				
				if(!isset($pageConfig['header_layout'])){
					if($config['product_header_layout'] != 'inherit'){
						$pageConfig['header_layout'] = $config['product_header_layout'];
					}
				}
				
				if(!isset($pageConfig['header_sticky_layout'])){
					if($config['product_header_sticky_layout'] != 'inherit'){
						$pageConfig['header_sticky_layout'] = $config['product_header_sticky_layout'];
					}
				}
				
				if(!isset($pageConfig['footer_layout'])){
					if($config['product_footer_layout'] != 'inherit'){
						$pageConfig['footer_layout'] = $config['product_footer_layout'];
					}
				}
                break;
			case 'search':
			case 'best-sales':
			case 'new-products':
			case 'prices-drop':	
			case 'manufacturer':
			case 'supplier':
			case 'category':
				$pageConfig = $this->_getThemeCtPageConfig((int)Tools::getValue('id_category'), 'category');
				
				if(!isset($pageConfig['header_layout']) || !$pageConfig['header_layout']){
					if($config['category_header_layout'] != 'inherit'){
						$pageConfig['header_layout'] = $config['category_header_layout'];
					}
				}
				
				if(!isset($pageConfig['header_sticky_layout']) || !$pageConfig['header_sticky_layout']){
					if($config['category_header_sticky_layout'] != 'inherit'){
						$pageConfig['header_sticky_layout'] = $config['category_header_sticky_layout'];
					}
				}
				
				if(!isset($pageConfig['header_overlap']) || !$pageConfig['header_overlap']){
					$pageConfig['header_overlap'] = $config['category_header_overlap'];
				}
				
				if(!isset($pageConfig['footer_layout']) || !$pageConfig['footer_layout']){
					if($config['category_footer_layout'] != 'inherit'){
						$pageConfig['footer_layout'] = $config['category_footer_layout'];
					}
				}
				
				if($config['category_page_title_layout'] != 'inherit'){
					$pageConfig['page_title_layout'] = $config['category_page_title_layout'];
				}
                break;
			case 'contact':
				if($config['contact_header_layout'] != 'inherit'){
					$pageConfig['header_layout'] = $config['contact_header_layout'];
				}

				if($config['contact_header_sticky_layout'] != 'inherit'){
					$pageConfig['header_sticky_layout'] = $config['contact_header_sticky_layout'];
				}
				
				$pageConfig['header_overlap'] = $config['contact_header_overlap'];
								
				if($config['contact_footer_layout'] != 'inherit'){
					$pageConfig['footer_layout'] = $config['contact_footer_layout'];
				}
				
				if($config['contact_page_title_layout'] != 'inherit'){
					$pageConfig['page_title_layout'] = $config['contact_page_title_layout'];
				}
                break;
			case 'pagenotfound':
				if($config['404_header_layout'] != 'inherit'){
					$pageConfig['header_layout'] = $config['404_header_layout'];
				}
				
				if($config['404_header_sticky_layout'] != 'inherit'){
					$pageConfig['header_sticky_layout'] = $config['404_header_sticky_layout'];
				}
				
				$pageConfig['header_overlap'] = $config['404_header_overlap'];
				
				if($config['404_footer_layout'] != 'inherit'){
					$pageConfig['footer_layout'] = $config['404_footer_layout'];
				}
				
				if($config['404_page_title_layout'] != 'inherit'){
					$pageConfig['page_title_layout'] = $config['404_page_title_layout'];
				}
                break;
		}
		
		return $pageConfig;
	}
		
 	/* ------------------------------------------------------------- */
    /*  PREPARE FOR HOOK
    /* ------------------------------------------------------------- */
    private function _prepHook()
    {			
        $config = $this->renderOptions();
				
        Media::addJsDef(array(
            'opThemect' => [
                'footer_fixed' => (bool)$config['general_footer_fixed'],
				'prev' => $this->l('Prev'),
				'next' => $this->l('Next'),
				'sidebar_sticky' => (bool)$config['general_affix_scroll'],
            ]
        )); 
											
		 /* LOAD CSS */
		$this->context->controller->registerStylesheet(
			'css_axps_line_awesome', 
			'/assets/mod_css/line-awesome/line-awesome.min.css',
			['media' => 'all', 'priority' => -1]
		);
		
		$custom_fonts = self::get_css_fonts();
		
		foreach ($custom_fonts as $key => $custom_font) {			
			$this->context->controller->registerStylesheet(
				'css_custom_font_' . $key, 
				'modules/'.$this->name.'/views/fonts/' . $custom_font['font_name'] . '/' . $custom_font['font_name'] . '-' . $custom_font['font_weight'] . '.css', 
				['media' => 'all', 'priority' => -1]
			);
		}
		
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
		
		$this->context->controller->registerStylesheet(
			'css_global', 
			'/assets/mod_css/global'.$dir_rtl.'.css',
			['media' => 'all', 'priority' => 997]
		);

		$this->context->controller->registerStylesheet(
			'css_type_product', 
			'/assets/mod_css/types-product'.$dir_rtl.'.css',
			['media' => 'all', 'priority' => 997]
		);
		$this->context->controller->registerStylesheet(
			'css_type_blog', 
			'/assets/mod_css/types-blog'.$dir_rtl.'.css',
			['media' => 'all', 'priority' => 997]
		);
		$this->context->controller->registerStylesheet(
			'css_type_image', 
			'/assets/mod_css/types-image'.$dir_rtl.'.css',
			['media' => 'all', 'priority' => 997]
		);
		if($config['color_scheme_dark']){
			$this->context->controller->registerStylesheet(
				'css_dark', 
				'/assets/mod_css/dark.css',
				['media' => 'all', 'priority' => 997]
			);
		}
		
		/* LOAD JS */

		if(version_compare(_PS_VERSION_, '9.0', '>=')){
			$theme_fix = 'theme_9';
		}else if(version_compare(_PS_VERSION_, '8.0', '>=')){
			$theme_fix = 'theme_8';
		} else {
			$theme_fix = 'theme_17';
		}
		
		$this->context->controller->registerJavascript(
			'theme-main', 
			'/assets/js/'.$theme_fix.'.js', 
			['position' => 'bottom', 'priority' => 1]
		);
		 
		$this->context->controller->registerJavascript(
			'js_global', 
			'/assets/mod_js/global.min.js', 
			['position' => 'bottom', 'priority' => 997]
		);

		/* -----------------------toastr-------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_toastr', 
			'/assets/mod_js/toastr.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);
		/* -----------------------swiper-------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_axps_swiper', 
			'/assets/mod_js/swiper/swiper.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);
		$this->context->controller->registerStylesheet(
			'css_axps_swiper', 
			'/assets/mod_js/swiper/swiper.css'
		);
		/* -----------------------photoswipe-------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_photoswipe', 
			'/assets/mod_js/photoswipe/photoswipe.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);
		$this->context->controller->registerJavascript(
			'js_photoswipe_skin', 
			'/assets/mod_js/photoswipe/photoswipe-ui-default.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);
		$this->context->controller->registerStylesheet(
			'css_photoswipe', 
			'/assets/mod_js/photoswipe/photoswipe.min.css'
		);
		$this->context->controller->registerStylesheet(
			'css_photoswipe_skin', 
			'/assets/mod_js/photoswipe/default-skin/default-skin'.$dir_rtl.'.min.css'
		);
		/* -----------------------tooltips--------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_tooltips', 
			'/assets/mod_js/jquery.tooltips.min.js', 
			['position' => 'bottom', 'priority' => 995]
		);
		/* -----------------------sticky-kit--------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_stickykit', 
			'/assets/mod_js/jquery.sticky-kit.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);
		/* -----------------------waypoints--------------------------------------- */
		$this->context->controller->registerJavascript(
			'js_axps_waypoints', 
			'/assets/mod_js/waypoints.min.js', 
			['position' => 'bottom', 'priority' => 51]
		);

        return true;
    }

    public function renderOptions()
    {		
		$opThemect = [];
		
        $config = $this->_getThemeConfig();
		
		$pageConfig = $this->_getPageConfigOnFront();
						
		if(isset($pageConfig['product_layout']) && $pageConfig['product_layout']){
			$config['product_layout'] = $pageConfig['product_layout'];
		}	
		
		if(isset($pageConfig['width_type']) && $pageConfig['width_type']){
			$config['product_layout_width_type'] = $pageConfig['width_type'];
			$config['category_layout_width_type'] = $pageConfig['width_type'];
		}	
		
		if(isset($pageConfig['tab_type']) && $pageConfig['tab_type']){
			$config['product_tabs_type'] = $pageConfig['tab_type'];
		}
		
		if(isset($pageConfig['category_layout']) && $pageConfig['category_layout']){
			$config['category_layout'] = $pageConfig['category_layout'];
		}	

		if(isset($pageConfig['page_title_color']) && $pageConfig['page_title_color']){
			$config['page_title_color'] = $pageConfig['page_title_color'];
		}	
						
		if(isset($pageConfig['open_vertical_menu']) && $pageConfig['open_vertical_menu']){
			$opThemect['open_vertical_menu'] = 1;
		}
		
		if(isset($pageConfig['page_title_layout'])){
			$config['page_title_layout'] = $pageConfig['page_title_layout'];
		}

		if(isset($pageConfig['header_overlap']) && $pageConfig['header_overlap'] && isset($config['page_title_layout']) && $config['page_title_layout']){
			$opThemect['header_overlap'] = 1;
		}
		
		if(Module::isEnabled('axoncreator')) {
			require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';
			
			if(isset($pageConfig['header_layout'])){
				Wp_Helper::add_filter( 'axoncreator_header_layout', function( $layout ) { 
					$pageConfig = $this->_getPageConfigOnFront();
					return (int) $pageConfig['header_layout']; 
				} );
			}
			if(isset($pageConfig['header_sticky_layout'])){
				Wp_Helper::add_filter( 'axoncreator_header_sticky_layout', function( $layout ) { 
					$pageConfig = $this->_getPageConfigOnFront();
					return (int) $pageConfig['header_sticky_layout']; 
				} );
			}
			if(isset($pageConfig['footer_layout'])){
				Wp_Helper::add_filter( 'axoncreator_footer_layout', function( $layout ) { 
					$pageConfig = $this->_getPageConfigOnFront();
					return (int) $pageConfig['footer_layout']; 
				} );
			}
		}
								
		foreach ($this->_configDefaults as $key => $default) {
			if ($default['type'] == 'smarty') {
                if(isset($config[$key])){
                    $opThemect[$key] = $config[$key];
                }else{
                    $opThemect[$key] = $default['value'];
                }
			}
		}
		
		$opThemect['font_body'] = $this->StandardFont('font_body');
	
		if($config['font_title'] != $config['font_body']){
			$opThemect['font_title'] = $this->StandardFont('font_title');
		}
				
		if (Tools::getIsset('shop_view')) {
			$view = Tools::getValue('shop_view');
			if ($view == 'grid') {
				$this->context->cookie->__set('opThemct_shop_view', 0);
			} elseif ($view == 'list') {
				$this->context->cookie->__set('opThemct_shop_view', 1);
			}
			$this->context->cookie->write();
		}
				
        if (isset($this->context->cookie->opThemct_shop_view)) {
            $opThemect['category_default_view'] = $this->context->cookie->opThemct_shop_view;
        }
		
		$opThemect = $this->getOptionsInUrl($opThemect);
					
        $this->context->smarty->assign('opThemect', $opThemect);
		
		return $config;
    }
	
    /* ------------------------------------------------------------- */
    /*  HOOK (displayHeader)
    /* ------------------------------------------------------------- */
	
    public function hookDisplayHeader()
    {
        $this->_prepHook();
		$this->_customCss();
		$this->_imageTypeCss();
		
		$config = $this->getOptionsInUrl();
		
		if( $config ){
			$cssCode = '';

			if( isset( $config['category_product_xs'] ) && ( $value = $config['category_product_xs'] ) ){
				$cssCode .= '@media (max-width: 767px){';
				$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
				$cssCode .= '}';
			}

			if( isset( $config['category_product_md'] ) && ( $value = $config['category_product_md'] ) ){		
				$cssCode .= '@media (min-width: 768px) and (max-width: 1024px){';
				$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
				$cssCode .= '}';
			}

			if( isset( $config['category_product_lg'] ) && ( $value = $config['category_product_lg'] ) ){
				$cssCode .= '@media (min-width: 1025px) and (max-width: 1199px){';
				$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
				$cssCode .= '}';
			}

			if( isset( $config['category_product_xl'] ) && ( $value = $config['category_product_xl'] ) ){
				$cssCode .= '@media (min-width: 1200px){';
				$cssCode .= '#box-product-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
				$cssCode .= '}';
			} 
			
			if( $cssCode ){
				$this->smarty->assign( ['css_unique' => '<style>' . $cssCode . '</style>'] );

				return $this->fetch( 'module:' . $this->name . '/views/templates/hook/css_unique.tpl' );
			}
		}		
    }

	public function _writeImgCss()
    {
       
		$images = ImageType::getImagesTypes();
				
        $cssCode = '';	

        foreach ($images as $image) {
			if((int)$image['height'] && (int)$image['width']){
				$padding = ((int)$image['height']/(int)$image['width'])*100;
		  		$cssCode .= '.'.$image['name'].'{padding-top:'.$padding.'%;}';	
			}else{
		  		$cssCode .= '.'.$image['name'].'{padding-top:100%;}';		
			}
        }
		
		$cssCode = trim(preg_replace('/\s+/', ' ', $cssCode));
		$id_shop = $this->context->shop->id;
		
		$cssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/front/images.css';

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
		
		return true;

    }

    public function _imageTypeCss()
    {
        $id_shop = $this->context->shop->id;
		
        $cssFile = 'images.css';

		if(!file_exists(_PS_MODULE_DIR_ . $this->name . '/views/css/front/images.css')){
			$this->_writeImgCss();
		}

		$this->context->controller->registerStylesheet(
			'ImgCss', 
			'modules/'.$this->name.'/views/css/front/' . $cssFile, 
			['position' => 'bottom', 'priority' => 997]
		);
		
    }
				
    public function hookProductSearchProvider()
    {
        if (Tools::getIsset('from-xhr')) {
            $this->renderOptions();
        }
    }

    public function hookActionProductSearchAfter()
    {
        if (Tools::getIsset('ajax')) {
            $this->renderOptions();
        }
    }
	
    public function hookFilterProductSearch($params)
    {	
		$config = $this->_getThemeConfig();
		
		$options = array();
		
		$options['category_product_infinite'] = $config['category_product_infinite'];
		
		$options = $this->getOptionsInUrl($options);
		
		if($options['category_product_infinite']){
			if (!Tools::getIsset('from-xhr')) {
				if (Tools::getIsset('page')) {
					//Tools::redirect($this->updateQueryString(array('page' => null)));
				}
			}else{
				if (Tools::getIsset('infinite')) {
					$params['searchVariables']['current_url'] = preg_replace('/&infinite/', '', $params['searchVariables']['current_url']);
					$params['searchVariables']['infinite'] = true;
				}
			}
		}
    }
	
	public function updateQueryString(array $extraParams = null)
    {
        $uriWithoutParams = explode('?', $_SERVER['REQUEST_URI'])[0];
        $url = Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . $uriWithoutParams;
        $params = [];
        $paramsFromUri = '';
        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $paramsFromUri = explode('?', $_SERVER['REQUEST_URI'])[1];
        }
        parse_str($paramsFromUri, $params);

        if (null !== $extraParams) {
            foreach ($extraParams as $key => $value) {
                if (null === $value) {
                    unset($params[$key]);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        if (null !== $extraParams) {
            foreach ($params as $key => $param) {
                if ('' === $param) {
                    unset($params[$key]);
                }
            }
        } else {
            $params = [];
        }

        $queryString = str_replace('%2F', '/', http_build_query($params, '', '&'));

        return $url . ($queryString ? "?$queryString" : '');
    }
	
    public function hookActionProductSearchComplete($hook_args)
    {
        if (isset($hook_args['js_enabled']) && $hook_args['js_enabled']) {
            $this->renderOptions();
        }
    }
	
	/*--------------------------------------------------------------------------------------------------------------------------------*/
			
    public function hookDisplayAdminProductsExtra($params)
    {
		$page_id = (int)$params["id_product"];
		
		$this->_defineLayoutsArray();
							
		array_splice( $this->_productLayout, 0, 0, array( array( 'value' => 'inherit', 'name' => $this->l('Inherit') ) ) );
		array_splice( $this->_layoutWidthTypes, 0, 0, array( array( 'value' => 'inherit', 'name' => $this->l('Inherit') ) ) );
		array_splice( $this->_tabsType, 0, 0, array( array( 'value' => 'inherit', 'name' => $this->l('Inherit') ) ) );

		$this->context->smarty->assign(array(
			'layouts' => $this->_productLayout,
			'widthTypes' => $this->_layoutWidthTypes,
			'tabsType' => $this->_tabsType,
			'selected' => $this->_getThemeCtPageConfig($page_id, 'product')
		));
		
		return $this->display(__FILE__, 'views/templates/admin/_product.tpl');
    }
	
    public function hookActionProductSave($params)
    {
		if (!Tools::getValue('id_product') || !Tools::getIsset('product_layout')) {
            return;
        }
		
        $page_id = (int) Tools::getValue('id_product');
		$product_layout = Tools::getValue('product_layout');
		$width_type = Tools::getValue('width_type');
		$tab_type = Tools::getValue('tab_type');
		
		$config = [];
		
		if($product_layout != 'inherit'){
			$config['product_layout'] = $product_layout;
		}
		
		if($width_type != 'inherit'){
			$config['width_type'] = $width_type;
		}
		
		if($tab_type != 'inherit'){
			$config['tab_type'] = $tab_type;
		}
		
		if($config){				
			$this->_setThemeCtPageConfig($page_id, 'product', $config);
		}else{
			$this->_unsetThemeCtPageConfig($page_id, 'product');
		} 
    }
	
    public function hookActionProductDelete($params)
    {
        $page_id = (int)$params["id_product"];
        $this->_unsetThemeCtPageConfig($page_id, 'product');
    }
	
	///////////////////////////////////////////////////////////////////////////////////
	
    public function hookActionCategoryAdd($params)
    {
		if (!isset($params['category']->id) || !Tools::getIsset('category_layout')) {
            return;
        }
		
        $page_id = (int)$params['category']->id;
        $category_layout = Tools::getValue('category_layout');
		$width_type = Tools::getValue('width_type');
		$page_title_color = Tools::getValue('page_title_color');
		
		$config = [];
		
		if($category_layout != 'inherit'){
			$config['category_layout'] = $category_layout;
		}
		
		if($width_type != 'inherit'){
			$config['width_type'] = $width_type;
		}
		
		if($page_title_color != 'inherit'){
			$config['page_title_color'] = $page_title_color;
		}

		if($config){			
			$this->_setThemeCtPageConfig($page_id, 'category', $config);
		}else{
			$this->_unsetThemeCtPageConfig($page_id, 'category');
		} 
    }
	
    public function hookActionCategoryUpdate($params)
    {
		$this->hookActionCategoryAdd($params);
    }
	
    public function hookActionCategoryDelete($params)
    {
        $page_id = (int)$params['category']->id;
		$this->_unsetThemeCtPageConfig($page_id, 'category');
    }
	
	///////////////////////////////////////////////////////////////////////////////////

	public function _setThemeCtPageConfig($page_id, $page_type, $config)
	{
		$res = true;
				
		$res &= Db::getInstance()->execute('
			INSERT INTO `' . _DB_PREFIX_ . 'nrt_themect_page_config` (`page_id`, `page_type`, `config`) 
			VALUES(' . (int)$page_id . ', \'' . $page_type . '\', \'' . json_encode($config) . '\') ON DUPLICATE KEY UPDATE config = VALUES(config)'
		);

		return $res;
	}

	public function _unsetThemeCtPageConfig($page_id, $page_type)
	{
		$res = true;

		$res &= Db::getInstance()->execute('
			DELETE FROM `' . _DB_PREFIX_ . 'nrt_themect_page_config`
			WHERE `page_id` = ' . (int)$page_id . ' 
			AND   `page_type` = \'' . $page_type . '\'
			');

		return $res;
	}
	
    public function _getThemeCtPageConfig($page_id, $page_type)
    {
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `config`
			FROM ' . _DB_PREFIX_ . 'nrt_themect_page_config 
			WHERE `page_id` = ' . (int)$page_id . ' 
			AND   `page_type` = \'' . $page_type . '\'
			');
		return $row ? json_decode($row['config'], true) : [];
	}
	
	///////////////////////////////////////////////////////////////////////////////////
		
    public function hookActionObjectCmsAddAfter($params) 
	{
				
        if (!isset($params['object']->id) || !Tools::getIsset('header_layout')) {
            return;
        }
		
		$page_id = (int)$params['object']->id;
			
        $header_layout = Tools::getValue('header_layout');
		$header_sticky_layout = Tools::getValue('header_sticky_layout');
		$header_overlap = Tools::getValue('header_overlap');
		$footer_layout = Tools::getValue('footer_layout');
		$page_title_layout = Tools::getValue('page_title_layout');
		$open_vertical_menu = Tools::getValue('open_vertical_menu');
		
		$config = [];
		
		if($header_layout != 'inherit'){
			$config['header_layout'] = $header_layout;
		}
		
		if($header_sticky_layout != 'inherit'){
			$config['header_sticky_layout'] = $header_sticky_layout;
		}
		
		if($header_overlap != 'inherit'){
			$config['header_overlap'] = $header_overlap;
		}
		
		if($footer_layout != 'inherit'){
			$config['footer_layout'] = $footer_layout;
		}
		
		if($page_title_layout != 'inherit'){
			$config['page_title_layout'] = $page_title_layout;
		}
		
		if($open_vertical_menu != 'inherit'){
			$config['open_vertical_menu'] = $open_vertical_menu;
		}

		if($config){			
			$this->_setThemeCtPageConfig($page_id, 'cms', $config);
		}else{
			$this->_unsetThemeCtPageConfig($page_id, 'cms');
		} 
    }
		
    public function hookActionObjectCmsUpdateAfter($params)
    {
		$this->hookActionObjectCmsAddAfter($params);
    }
	
    public function hookActionObjectCmsDeleteAfter($params)
    {
        if (!isset($params['object']->id)) {
            return;
        }
		$page_id = (int)$params['object']->id;
		$this->_unsetThemeCtPageConfig($page_id, 'cms');
    }
	
	public function hookDisplayBackOfficeHeader($params)
    {		
	    if(Module::isEnabled('axoncreator')) {

			$id_hook = (int) Hook::getIdByName('displayHeader');

			$module = Module::getInstanceByName('axoncreator');

			$position_a = $module->getPosition($id_hook);
			$position_t = $this->getPosition($id_hook);

			if($position_a < $position_t){
				$this->updatePosition($id_hook, 0, $position_a);
			}
		}

        if ($this->context->controller->controller_name == 'AdminCmsContent') {
			
			$page_id = (int) Tools::getValue('id_cms');

			if(!$page_id){
				global $kernel;

				$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();

				if (!isset($request->attributes)) {
					return;
				}

				$page_id = (int) $request->attributes->get('cmsPageId');
			}

			$this->_defineLayoutsArray();
												
			$status = array(
				array('value' => 'inherit', 'name' => $this->l('Inherit')),
				array('value' => 0, 'name' => $this->l('No')),
				array('value' => 1, 'name' => $this->l('Yes'))
			);
			
			$titles = array(
				array('value' => 'inherit', 'name' => $this->l('Inherit')),
				array('value' => '0', 'name' => $this->l('Hide')),
				array('value' => '1', 'name' => $this->l('Normal')),
				array('value' => '2', 'name' => $this->l('Small'))
			);
			
			$this->context->smarty->assign(array(
				'headers' => $this->_headerTypesOnPage,
				'footers' => $this->_footerTypesOnPage,
				'titles' => $titles,
				'status' => $status,
				'selected' => $this->_getThemeCtPageConfig($page_id, 'cms'),
			));
						
			return $this->display(__FILE__, 'views/templates/admin/_cms.tpl');
			
		} else if ($this->context->controller->controller_name == 'AdminCategories') {
			
			$page_id = (int) Tools::getValue('id_category');

			if(!$page_id){
				global $kernel;

				$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();

				if (!isset($request->attributes)) {
					return;
				}

				$page_id = (int) $request->attributes->get('categoryId');
			}

			$this->_defineLayoutsArray();
			
			$this->_categoryLayout[] = array(
				'value' => '4',
				'name' => $this->l('Only Axon - Creator'),
			);
			
			$this->_categoryLayout[] = array(
				'value' => '5',
				'name' => $this->l('Only Subcategories'),
			);
			
			$colors = array(
				array('value' => 'inherit', 'name' => $this->l('Inherit')),
				array('value' => 'dark', 'name' => $this->l('Dark')),
				array('value' => 'light', 'name' => $this->l('Light'))
			);
			
			array_splice( $this->_categoryLayout, 0, 0, array( array( 'value' => 'inherit', 'name' => $this->l('Inherit') ) ) );
			array_splice( $this->_layoutWidthTypes, 0, 0, array( array( 'value' => 'inherit', 'name' => $this->l('Inherit') ) ) );

			$this->context->smarty->assign(array(
				'layouts' => $this->_categoryLayout,
				'widthTypes' => $this->_layoutWidthTypes,
				'colors' => $colors,
				'selected' => $this->_getThemeCtPageConfig($page_id, 'category')
			));
			
			return $this->display(__FILE__, 'views/templates/admin/_category.tpl');
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////
	
    public function hookDisplayBeforeBodyClosingTag($params)
    {
		$templateFile = 'module:nrtthemecustomizer/views/templates/hook/orther.tpl';
		
		return $this->fetch($templateFile);
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
	
	///////////////////////////////////////////////////////////////////////////////////////////////
	
    public function getOptionsInUrl($options = []) {	
		
		if( !isset( $_GET['opt'] ) ) {
			return $options;
		}
		
		$datas = $_GET['opt'];
		
		$datas = explode( '/', $datas );
				
		foreach( $datas as $data ){
			$data = explode( '-', $data );
			if( count($data) > 1 ){
				
				switch ($data[0]) {
					case 'layout':
						if($data[1] > 0 && $data[1] < 6){
							$options['category_layout'] = $data[1];
						}
						if($data[1] > 0 && $data[1] < 11){
							$options['product_layout'] = $data[1];
						}
					break;
					case 'product_layout':
						if($data[1] > 0 && $data[1] < 9){
							$options['category_product_layout'] = $data[1];
						}
					break;
					case 'contact':
						if($data[1]){
							$options['contact_override_content_by_hook'] = 1;
						}
					break;
					case '404':
						if($data[1]){
							$options['404_override_content_by_hook'] = 1;
						}
					break;
					case 'product_label':
						if($data[1] == 'rectangular'){
							$options['product_label'] = 'rectangular';
						}
						if($data[1] == 'circle'){
							$options['product_label'] = 'circle';
						}
					break;
					case 'width':
						if($data[1] == 'full'){
							$options['category_layout_width_type'] = 'container-fluid';
							$options['product_layout_width_type'] = 'container-fluid';
						}
						if($data[1] == 1400){
							$options['category_layout_width_type'] = 'container-fluid max-width-1400';
							$options['product_layout_width_type'] = 'container-fluid max-width-1400';
						}
						if($data[1] == 1600){
							$options['category_layout_width_type'] = 'container-fluid max-width-1600';
							$options['product_layout_width_type'] = 'container-fluid max-width-1600';
						}
					break;
					case 'tab':
						if($data[1] == 1 || $data[1] == 3 || $data[1] == 4){
							$options['product_tabs_type'] = $data[1];
						}
					break;
					case 'filters':
						if($data[1] == 'canvas'){
							$options['category_faceted_position'] = 1;
						}
						if($data[1] == 'area'){
							$options['category_faceted_position'] = 2;
						}
					break;
					case 'items_xl':
						if($data[1] > 0 && $data[1] < 11){
							$options['category_product_xl'] = $data[1];
						}
					break;
					case 'items_lg':
						if($data[1] > 0 && $data[1] < 11){
							$options['category_product_lg'] = $data[1];
						}
					break;
					case 'items_md':
						if($data[1] > 0 && $data[1] < 11){
							$options['category_product_md'] = $data[1];
						}
					break;
					case 'items_xs':
						if($data[1] > 0 && $data[1] < 11){
							$options['category_product_xs'] = $data[1];
						}
					break;
					case 'pagination':
						if($data[1] == 0 || $data[1] == 1 || $data[1] == 2){
							$options['category_product_infinite'] = $data[1];
						}
					break;
				} 
				
			}
		}
	
		return $options;
		
    }
	
    public function get_css_fonts() {	
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_custom_fonts` WHERE `active` = 1';
		
		$fonts = Db::getInstance()->executeS( $sql );
				
		return $fonts;
	}
	
    public function get_fonts() {	
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_custom_fonts` WHERE `active` = 1 GROUP BY title';
		
		$fonts = Db::getInstance()->executeS( $sql );
				
		return $fonts;
	}

	public function axps_register_hook() {	

		$modules = 
			[
			'nrtthemecustomizer' 	=> ['actionAdminControllerSetMedia','actionCategoryAdd','actionCategoryDelete','actionCategoryUpdate','actionObjectCmsAddAfter','actionObjectCmsDeleteAfter','actionObjectCmsUpdateAfter','actionProductDelete','actionProductSave','actionProductSearchAfter','actionProductSearchComplete','displayBackOfficeHeader','displayAdminProductsExtra','displayBeforeBodyClosingTag','filterProductSearch','displayHeader','productSearchProvider'],
			'nrtmegamenu' 			=> ['actionCategoryAdd','actionCategoryDelete','actionCategoryUpdate','actionObjectCategoryDeleteAfter','actionObjectCategoryUpdateAfter','actionObjectCmsDeleteAfter','actionObjectCmsUpdateAfter','actionObjectManufacturerDeleteAfter','actionObjectManufacturerUpdateAfter','actionObjectProductDeleteAfter','actionObjectProductUpdateAfter','actionObjectSupplierDeleteAfter','actionObjectSupplierUpdateAfter','actionProductAdd','actionProductDelete','actionProductUpdate','categoryUpdate','displayBeforeBodyClosingTag','displayHeader','displayHeaderMobileLeft','displayLeftColumn','displayMenuHorizontal','displayMenuVertical','displayRightColumn'],
			'axoncreator' 			=> ['actionObjectBlogDeleteAfter','actionObjectCategoryDeleteAfter','actionObjectCmsDeleteAfter','actionObjectManufacturerDeleteAfter','actionObjectProductDeleteAfter','actionObjectSupplierDeleteAfter','display404PageBuilder','displayBackOfficeHeader','displayContactPageBuilder','displayFooterPageBuilder','displayFooterProduct','displayHome','displayIncludePageBuilder','displayProductSummary','displayFooterCategory','displayLeftColumn','displayLeftColumnProduct','displayHeaderNormal','displayHeaderSticky','displayProductAccessories','displayProductSameCategory','displayRightColumn','displayRightColumnProduct','displayShoppingCartFooter','overrideLayoutTemplate','filterBlogContent','filterCategoryContent','filterCmsContent','filterManufacturerContent','filterProductContent','filterSupplierContent','displayHeader'],
			'nrtaddthisbutton' 		=> ['displayWishListShareButtons'],
			'nrtcaptcha' 			=> ['displayNrtCaptcha','displayCustomerAccountForm','displayHeader'],
			'nrtcompare' 			=> ['displayButtonCompare','displayButtonCompareNbr','displayMenuMobileCanVas','displayMyAccountCanVas','displayHeader'],
			'nrtcookielaw' 			=> ['displayBeforeBodyClosingTag','displayHeader'],
			'nrtcountdown' 			=> ['displayCountDown','displayHeader'],
			'nrtcustomtab' 			=> ['actionProductDelete','actionProductSave','displayAdminProductsExtra','displayProductExtraContent'],
			'nrtpopupnewsletter' 	=> ['displayBeforeBodyClosingTag','displayHeader','registerGDPRConsent'],
			'nrtproductslinknav' 	=> ['displayProductsLinkNav','displayHeader','actionProductSave','actionProductDelete'],
			'nrtproducttags' 		=> ['displayProductTags','actionProductSave','actionProductDelete'],
			'nrtproductvideo' 		=> ['actionProductDelete','actionProductSave','displayBackOfficeHeader','displayAdminProductsExtra','displayHeader','displayProductVideoBtn'],
			'nrtreviews' 			=> ['registerNRTCaptcha','registerGDPRConsent','actionObjectProductDeleteAfter','displayBeforeBodyClosingTag','displayHeader','displayProductExtraComparison','displayProductExtraContent','displayProductListReviews','displayProductRating'],
			'nrtsearchbar' 			=> ['displayBeforeBodyClosingTag','displayButtonSearch','displayHeaderMobileRight','displaySearch','displayHeader','productSearchProvider'],
			'nrtshippingfreeprice' 	=> ['displayNrtCartInfo'],
			'nrtshoppingcart' 		=> ['displayBeforeBodyClosingTag','displayButtonCartNbr','displayHeaderMobileRight','displayHeader'],
			'nrtsizechart' 			=> ['actionProductDelete','actionProductSave','displayAdminProductsExtra','displayProductSizeGuide','displayHeader'],
			'nrtsocialbutton' 		=> ['displayBlogShareButtons','displayFollowButtons','displayProductShareButtons','displayHeader'],
			'nrtsociallogin' 		=> ['displaySocialLogin','displayHeader'],
			'nrtvariant' 			=> ['displayVariant','displayHeader'],
			'nrtwishlist' 			=> ['actionDeleteGDPRCustomer','actionExportGDPRData','actionProductDelete','displayBeforeBodyClosingTag','displayButtonWishList','displayButtonWishListNbr','displayCustomerAccount','displayHeader','displayMenuMobileCanVas','displayMyAccountCanVas','registerGDPRConsent'],
			'nrtzoom' 				=> ['displayHeader'],
			'smartblog' 			=> ['registerNRTCaptcha','registerGDPRConsent','actionsbappcomment','actionsbcat','actionsbdeletecat','actionsbdeletepost','actionsbheader','actionsbnewcat','actionsbnewpost','actionsbpostcomment','actionsbsearch','actionsbsingle','actionsbtogglecat','actionsbtogglepost','actionsbupdatecat','actionsbupdatepost','addWebserviceResources','displayBackOfficeHeader','displayHeader','moduleRoutes','actionLanguageLinkParameters'],
			'smartblogarchive' 		=> ['actionsbdeletepost','actionsbnewpost','actionsbtogglepost','actionsbupdatepost','displayHeader','displaySmartBlogLeft','displaySmartBlogRight'],
			'smartblogcategories' 	=> ['actionsbdeletecat','actionsbnewcat','actionsbtogglecat','actionsbupdatecat','displayHeader','displaySmartBlogLeft','displaySmartBlogRight'],
			'smartbloglatestcomments' => ['actionsbpostcomment','displaySmartBlogLeft','displaySmartBlogRight'],
			'smartblogpopularposts' => ['actionsbdeletepost','actionsbnewpost','actionsbsingle','actionsbtogglepost','actionsbupdatepost','displaySmartBlogLeft','displaySmartBlogRight'],
			'smartblogrecentposts' 	=> ['actionsbdeletepost','actionsbnewpost','actionsbtogglepost','actionsbupdatepost','displaySmartBlogLeft','displaySmartBlogRight'],
			'smartblogsearch' 		=> ['displaySmartBlogLeft','displaySmartBlogRight'],
			'smartblogtag' 			=> ['actionsbdeletepost','actionsbnewpost','actionsbtogglepost','actionsbupdatepost','displaySmartBlogLeft','displaySmartBlogRight'],
			'contactform' 			=> ['registerNRTCaptcha','registerGDPRConsent']
			];

		foreach ($modules as $module_name => $hooks) {
			if(Module::isInstalled($module_name)){
				if( $module_name == 'nrtthemecustomizer' ) {
					$module = $this;
				} else {
					$module = Module::getInstanceByName($module_name);
				}
				foreach ($hooks as $hook) {
					if (!Hook::isModuleRegisteredOnHook($module, $hook, $this->context->shop->id)) {
						Hook::registerHook($module, $hook);
					}
				}
			}
		}
	}
}