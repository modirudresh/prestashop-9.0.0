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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once dirname(__FILE__).'/classes/NrtMegaMenuClass.php';
require_once dirname(__FILE__).'/classes/NrtMegaColumnClass.php';
require_once dirname(__FILE__).'/classes/NrtMegaProductClass.php';
require_once dirname(__FILE__).'/classes/NrtMegaBrandClass.php';

class NrtMegaMenu extends Module implements WidgetInterface
{
    protected static $cache_nrtmegamenu;
    protected static $access_rights = 0775;
	private $_html = '';
    public $fields_list;
    public $fields_form;
    private $_baseUrl;
	private $spacer_size = '5';
    protected $templateFile;

    public static $_type = array(
        0 => 'Custom link',
        1 => 'Category',
        2 => 'Product',
        3 => 'CMS page',
        4 => 'Manufacturer',
        5 => 'Supplier',
        6 => 'Cms category',
        7 => 'Icon',
        8 => 'Blog category',
        9 => 'Blog',
        10 => 'Page',
    );
    public static $_item_type = array(
        1 => 'Category',
        2 => 'Product',
        3 => 'Brand',
        4 => 'Custom link',
        5 => 'Custom content',
    );
    public static $_bootstrap = array(
        array('id'=>1, 'name'=> '1/12'),
        array('id'=>2, 'name'=> '2/12'),
        array('id'=>2.4, 'name'=> '2.4/12'),
        array('id'=>4, 'name'=> '4/12'),
        array('id'=>5, 'name'=> '5/12'),
        array('id'=>6, 'name'=> '6/12'),
        array('id'=>7, 'name'=> '7/12'),
        array('id'=>8, 'name'=> '8/12'),
        array('id'=>9, 'name'=> '9/12'),
        array('id'=>10, 'name'=> '10/12'),
        array('id'=>11, 'name'=> '11/12'),
        array('id'=>12, 'name'=> '12/12'),
    );
    public $_align = array();
    public $_location = array();
	public function __construct()
	{
		$this->name          = 'nrtmegamenu';
		$this->tab           = 'front_office_features';
		$this->version       = '2.2.7';
		$this->author        = 'AxonVIZ';
		$this->need_instance = 0;

        $this->bootstrap = true;
		parent::__construct();
		$this->displayName   = $this->l('Axon - Megamenu');
		$this->description   = $this->l('Required by author: AxonVIZ.');
            
        $this->_align =  array(
                array(
                    'id' => 'alignment_0',
                    'value' => 0,
                    'label' => $this->l('Default')
                ),
                array(
                    'id' => 'alignment_1',
                    'value' => 1,
                    'label' => $this->l('Top Parent')
                ),
                array(
                    'id' => 'alignment_2',
                    'value' => 2,
                    'label' => $this->l('Bottom Parent')
                ),
            );
        $this->_location =  array(
                array(
                    'id' => 'location_0',
                    'value' => 0,
                    'label' => $this->l('Main horizontal menu')
                ),
                array(
                    'id' => 'location_1',
                    'value' => 1,
                    'label' => $this->l('Left/right column menu')
                ),
                array(
                    'id' => 'location_2',
                    'value' => 2,
                    'label' => $this->l('Dropdown vertical menu')
                ),
			);
	}
        
	public function install()
	{
	    $res = parent::install() 
            && $this->installDB() 
            && $this->_createTab()
            && $this->registerHook('actionCategoryAdd')
            && $this->registerHook('actionCategoryDelete')
            && $this->registerHook('actionCategoryUpdate')
            && $this->registerHook('actionObjectCategoryDeleteAfter')
            && $this->registerHook('actionObjectCategoryUpdateAfter')
            && $this->registerHook('actionObjectCmsDeleteAfter')
            && $this->registerHook('actionObjectCmsUpdateAfter')
            && $this->registerHook('actionObjectManufacturerDeleteAfter')
            && $this->registerHook('actionObjectManufacturerUpdateAfter')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('actionObjectProductUpdateAfter')
            && $this->registerHook('actionObjectSupplierDeleteAfter')
            && $this->registerHook('actionObjectSupplierUpdateAfter')
            && $this->registerHook('actionProductAdd')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('categoryUpdate')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayHeaderMobileLeft')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayMenuHorizontal')
            && $this->registerHook('displayMenuVertical')
            && $this->registerHook('displayRightColumn')
			&& Configuration::updateValue('nrt_vetical_menu_limit', 10);

        if ($res){
            foreach(Shop::getShops(false) as $shop){
                $res &= $this->sampleData($shop['id_shop']);
            }
        }

        $this->clearNrtMegamenuCache();

		return $res;
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
        $tab->class_name = "AdminMegaMenu";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Megamenu";
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
        $id_tab = Tab::getIdFromClassName('AdminMegaMenu');
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
    
    public function sampleData($id_shop)
    {
        $return = true;
        $path = _MODULE_DIR_.$this->name;
		$samples = array(
            0 => array(
                'sample_pid' => 0,
                'sample_cid' => 0,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => 0, 
                'id_parent' => 0, 
                'level_depth' => 0, 
                'item_k' => 7, 
                'item_v' => 1, 
                'is_mega' => 0, 
                'item_t' => 0, 
                'title' => 'Home', 
                'html' => '',
				'location' => 0,
            ),
            1 => array(
                'sample_pid' => 0,
                'sample_cid' => 0,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => 0, 
                'id_parent' => 0, 
                'level_depth' => 0, 
                'item_k' => 0, 
                'item_v' => '', 
                'is_mega' => 1, 
                'item_t' => 0, 
                'title' => 'Custom block', 
                'html' => '', 
				'location' => 0,
                'columns' => array(
                    0 => array('id_nrt_mega_column' => 0, 'width' => 4, ),
                    1 => array('id_nrt_mega_column' => 0, 'width' => 4, ),
                    2 => array('id_nrt_mega_column' => 0, 'width' => 4, ),
                ),
            ),
            2 => array(
                'sample_pid' => 1,
                'sample_cid' => 0,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => '', 
                'id_parent' => 0, 
                'level_depth' => 1, 
                'item_k' => 0, 
                'item_v' => '', 
                'is_mega' => 0, 
                'item_t' => 5, 
                'title' => '', 
				'location' => 0,
                'html' => '<h6>Welcome to AxonVIZ theme</h6><p>AxonVIZ theme is a modern, clean and professional Prestashop theme, it comes with a lot of useful features. AxonVIZ theme is fully responsive, it looks stunning on all types of screens and devices.</p><ul><li>Fully Customizable Design</li><li>Sidebar Shopping Cart</li></ul><br/><p><a class="btn btn-primary" title="Buy this theme" href="https://themeforest.net/user/axonviz" target="_blank">BUY THIS THEME</a></p>', 
            ),
            3 => array(
                'sample_pid' => 1,
                'sample_cid' => 1,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => '', 
                'id_parent' => 0, 
                'level_depth' => 1, 
                'item_k' => 0, 
                'item_v' => '', 
                'is_mega' => 0, 
                'item_t' => 5, 
                'title' => '', 
				'location' => 0,
                'html' => '<p><a href="#" title="AxonVIZ theme" rel="nofollow"><img class="img-responsive" src="'.__PS_BASE_URI__.'modules/nrtmegamenu/views/img/sample_1.jpg" alt="AxonVIZ theme"/></a><p><p>AxonVIZ theme is a modern, clean and professional Prestashop theme, it comes with a lot of useful features. AxonVIZ theme is fully responsive, it looks stunning on all types of screens and devices.</p>', 
            ),
            4 => array(
                'sample_pid' => 1,
                'sample_cid' => 2,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => '', 
                'id_parent' => 0, 
                'level_depth' => 1, 
                'item_k' => 0, 
                'item_v' => '', 
                'is_mega' => 0, 
                'item_t' => 5, 
                'title' => '', 
				'location' => 0,
                'html' => '<p><a href="#" title="AxonVIZ theme" rel="nofollow"><img class="img-responsive"  src="'.__PS_BASE_URI__.'modules/nrtmegamenu/views/img/sample_2.jpg" alt="AxonVIZ theme"/></a><p><p>AxonVIZ theme is a modern, clean and professional Prestashop theme, it comes with a lot of useful features. AxonVIZ theme is fully responsive, it looks stunning on all types of screens and devices.</p>', 
            ),
            /*5 => array(
                'sample_pid' => 0,
                'sample_cid' => 0,
                'id_nrt_mega_menu' => '', 
                'id_nrt_mega_column' => 0, 
                'id_parent' => 0, 
                'level_depth' => 0, 
                'item_k' => 7, 
                'item_v' => 1, 
                'is_mega' => 0, 
                'item_t' => 0, 
                'title' => 'Custom block', 
                'html' => '', 
				'location' => 2,
            ),*/
		);		
		foreach($samples as $k=>&$sample)
		{
			$module = new NrtMegaMenuClass();
            if($sample['id_nrt_mega_column']===0 || $sample['id_nrt_mega_column']==='0')
                $id_nrt_mega_column = 0;
            else
                $id_nrt_mega_column = $samples[$sample['sample_pid']]['columns'][$sample['sample_cid']]['id_nrt_mega_column'];

            $module->id_nrt_mega_column = (int)$id_nrt_mega_column;
			$module->id_parent = $sample['id_parent'];
			$module->level_depth = $sample['level_depth'];
            $module->item_k = $sample['item_k'];
            $module->item_v = $sample['item_v'];
            $module->is_mega = $sample['is_mega'];
			$module->width = '100vw';
            $module->item_t = $sample['item_t'];
			$module->location = $sample['location'];
            foreach (Language::getLanguages(false) as $lang)
            {
                $module->title[$lang['id_lang']] = $sample['title'];
                $module->html[$lang['id_lang']] = $sample['html'];
            }
			$module->active = 1;
			$module->position = $k*10;
			$module->id_shop = (int)$id_shop;
			$return &= $module->add();
            if($return)
            {
                $sample['id_nrt_mega_menu'] = $module->id;
                if(isset($sample['columns']) && count($sample['columns']))
                    foreach ($sample['columns'] as $ck => $column) {
                        $col = new NrtMegaColumnClass();
                        $col->id_nrt_mega_menu = $module->id;
                        $col->width = $column['width'];
                        $col->active = 1;
                        $col->position = $ck;
                        $return &= $col->add();
                        if($return)
                            $sample['columns'][$ck]['id_nrt_mega_column'] = $col->id;
                    }
            }
		}
		return $return;
    }

	public function installDb()
	{
		$return = true;
		$return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_mega_menu` (
                `id_nrt_mega_menu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `location` tinyint(1) unsigned NOT NULL DEFAULT 0,
				`id_nrt_mega_column` int(10) UNSIGNED NOT NULL DEFAULT 0,
				`id_parent` int(10) NOT NULL DEFAULT 0,
                `level_depth` tinyint(3) unsigned NOT NULL DEFAULT 0,   
                `id_shop` int(10) unsigned NOT NULL,      
                `item_k` tinyint(2) unsigned NOT NULL DEFAULT 0,  
				`item_v` varchar(255) DEFAULT NULL,    
                `subtype` tinyint(1) unsigned NOT NULL DEFAULT 0,  
                `position` int(10) unsigned NOT NULL DEFAULT 0,
                `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
    			`new_window` TINYINT( 1 ) NOT NULL DEFAULT 0,
                `txt_color` varchar(7) DEFAULT NULL,
                `link_color` varchar(7) DEFAULT NULL,
                `bg_color` varchar(7) DEFAULT NULL,
                `txt_color_over` varchar(7) DEFAULT NULL,
                `bg_color_over` varchar(7) DEFAULT NULL,
                `tab_content_bg` varchar(7) DEFAULT NULL,
                `auto_sub` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `nofollow` tinyint(1) unsigned NOT NULL DEFAULT 0,
				`custom_class` varchar(255) DEFAULT NULL,
                `hide_on_mobile` tinyint(1) unsigned NOT NULL DEFAULT 0, 
                `alignment` tinyint(1) unsigned NOT NULL DEFAULT 0, 
                `width` varchar(7) DEFAULT NULL,
                `is_mega` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `sub_levels` int(10) unsigned NOT NULL DEFAULT 2,
                `sub_limit` int(10) unsigned NOT NULL DEFAULT 0,
                `item_limit` int(10) unsigned NOT NULL DEFAULT 0,
                `items_md` tinyint(2) unsigned NOT NULL DEFAULT 4,
                `icon_class` text,
                `item_t` tinyint(2) unsigned NOT NULL DEFAULT 0,
                `cate_label_color` varchar(7) DEFAULT NULL,
                `cate_label_bg` varchar(7) DEFAULT NULL,
                `show_cate_img` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `bg_image` varchar(255) DEFAULT NULL,
                `bg_repeat` tinyint(1) unsigned DEFAULT 3,
                `bg_position` tinyint(1) unsigned DEFAULT 0,
                `bg_margin_bottom` int(10) unsigned DEFAULT 0,
                `granditem` tinyint(1) NOT NULL DEFAULT 0,
				PRIMARY KEY (`id_nrt_mega_menu`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_mega_menu_lang` (
                `id_nrt_mega_menu` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_lang` int(10) unsigned NOT NULL ,
                `title` varchar(255) DEFAULT NULL,
                `link` varchar(255) DEFAULT NULL,
                `html` text,
                `cate_label` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id_nrt_mega_menu`, `id_lang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_mega_column` (
                `id_nrt_mega_column` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_nrt_mega_menu` int(10) unsigned NOT NULL,
                `width` float(3,1) unsigned NOT NULL DEFAULT 4,
                `position` int(10) unsigned NOT NULL DEFAULT 0,
                `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `hide_on_mobile` tinyint(1) unsigned NOT NULL DEFAULT 0, 
                `title` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id_nrt_mega_column`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_mega_product` (
                `id_nrt_mega_menu` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                KEY `menu_product` (`id_nrt_mega_menu`,`id_product`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_mega_brand` (
                `id_nrt_mega_menu` int(10) unsigned NOT NULL,
                `id_manufacturer` int(10) unsigned NOT NULL,
                KEY `menu_brand` (`id_nrt_mega_menu`,`id_manufacturer`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		
		return $return;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!$this->uninstallDB() || 
			!$this->_deleteTab())
			return false;
        $this->clearNrtMegamenuCache();
		return true;
	}

	private function uninstallDb()
	{
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nrt_mega_menu`,`'._DB_PREFIX_.'nrt_mega_menu_lang`,`'._DB_PREFIX_.'nrt_mega_column`,`'._DB_PREFIX_.'nrt_mega_brand`,`'._DB_PREFIX_.'nrt_mega_product`');
	}
    
    private function _checkImageDir()
    {
        $result = '';
        if (!file_exists(_PS_UPLOAD_DIR_.$this->name))
        {
            $success = @mkdir(_PS_UPLOAD_DIR_.$this->name, self::$access_rights, true)
						|| @chmod(_PS_UPLOAD_DIR_.$this->name, self::$access_rights);
            if(!$success)
                $this->_html .= $this->displayError('"'._PS_UPLOAD_DIR_.$this->name.'" '.$this->l('An error occurred during new folder creation'));
        }

        if (!is_writable(_PS_UPLOAD_DIR_))
            $this->_html .= $this->displayError('"'._PS_UPLOAD_DIR_.$this->name.'" '.$this->l('directory isn\'t writable.'));
        
        if (!is_writable(_PS_MODULE_DIR_.$this->name.'/views/css'))
            $this->_html .= $this->displayError('"'._PS_MODULE_DIR_.$this->name.'/views/css'.'" '.$this->l('directory isn\'t writable.'));
            
        return $result;
    }
        
	public function getContent()
	{		
		$this->context->controller->addCSS($this->_path. 'views/css/admin.css');
		$this->context->controller->addCSS(_THEME_DIR_. 'assets/mod_css/line-awesome/line-awesome.css');
		$this->context->controller->addJS($this->_path. 'views/js/admin.js');
		
		Media::addJsDef(array(
            'ajaxProductsListUrl' => $this->context->link->getAdminLink('AdminMegaMenu').'&ajax=1&action=productsList',
            'ajaxBrandsListUrl' => $this->context->link->getAdminLink('AdminMegaMenu').'&ajax=1&action=brandsList'));

    	$id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        $check_result = $this->_checkImageDir();
				
        if (Tools::isSubmit('copynrtmegamenu'))
        {
            if($this->processCopyMegaMenu($id_nrt_mega_menu))
            {
                $this->clearNrtMegamenuCache();
				$this->generateCss();	
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=19&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            else
                $this->_html .= $this->displayError($this->l('An error occurred while copy menu.'));
					
        }
        if (isset($_POST['nrt_vetical_menu_limit']))
        {
			Configuration::updateValue('nrt_vetical_menu_limit', Tools::getValue('nrt_vetical_menu_limit'));
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
        }
		if (isset($_POST['savenrtmegamenu']) || isset($_POST['savenrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
				$menu = new NrtMegaMenuClass($id_nrt_mega_menu);
			else
				$menu = new NrtMegaMenuClass();
                
            $error = array();
            
    		$menu->copyFromPost();
    		$menu->id_parent = 0;
            $menu->level_depth = 0;
            
            $item = Tools::getValue('links');
            if($item)
            {
                $item_arr = explode('_',$item);
                if(count($item_arr)!=2)
                {
                    $this->_html .= $this->displayError($this->l('"Menu item" error'));
    			     return;
                }
                $menu->item_k = $item_arr[0];
                $menu->item_v = $item_arr[1];
            }
            else
            {
                $menu->item_k = 0;
                $menu->item_v = '';
            }

            // Check default language
            $default_lang_id = (int)(Configuration::get('PS_LANG_DEFAULT'));
            $defaultLanguage = new Language($default_lang_id);

            if(!$id_nrt_mega_menu)
            {
                $languages = Language::getLanguages(false);
        		foreach ($languages as $language)
                    if(!$menu->title[$language['id_lang']])
        			     $menu->title[$language['id_lang']] = $menu->title[$defaultLanguage->id];
            }

            if (!$menu->item_k && !$menu->title[$defaultLanguage->id])
                $error[] = $this->displayError($this->l('Please select an option from "Main menu" drop down list or fill out "Menu name" field.'));
                

            $menu->id_shop = (int)Shop::getContextShopID();

            if(!count($error))
            {
                $res = $this->UploadImage('bg_image_field');
                    
                if(count($res['error']))
                    $error = array_merge($error,$res['error']);
                elseif($res['image'])
                    $menu->bg_image = $res['image'];
            }

            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savenrtmegamenuAndStay']) || Tools::getValue('fr') == 'view')
                    {
                        $rd_str = isset($_POST['savenrtmegamenuAndStay']) && Tools::getValue('fr') == 'view' ? 'fr=view&update' : (isset($_POST['savenrtmegamenuAndStay']) ? 'update' : 'view');
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&'.$rd_str.'nrt_mega_menu&token='.Tools::getAdminTokenLite('AdminModules')); 
                    }   
                    else
                        $this->_html .= $this->displayConfirmation($this->l('Main menu').' '.($id_nrt_mega_menu ? $this->l('updated') : $this->l('added')));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during main menu').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
			else
				$this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));
					
        }

        if (isset($_POST['savecolumnnrtmegamenu']) || isset($_POST['savecolumnnrtmegamenuAndStay']))
		{
            $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
            if($id_nrt_mega_column)
                $column = new NrtMegaColumnClass($id_nrt_mega_column);
            else
                $column = new NrtMegaColumnClass();

            $error = array();
            $column->copyFromPost();

            if (!count($error) && $column->validateFields(false) && $column->validateFieldsLang(false))
            {
                if($column->save())
                {
                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savecolumnnrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$column->id.'&conf='.($id_nrt_mega_column?4:3).'&updatenrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$column->id_nrt_mega_menu.'&viewnrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during menu').' '.($id_nrt_mega_column ? $this->l('updating') : $this->l('creation')));
            }
            else
                $this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));				
        }
        if (isset($_POST['savecustomlinknrtmegamenu']) || isset($_POST['savecustomlinknrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
				$menu = new NrtMegaMenuClass($id_nrt_mega_menu);
			else
				$menu = new NrtMegaMenuClass();
                
            $error = array();
            
    		$menu->copyFromPost();

            $menu_parent = new NrtMegaMenuClass($menu->id_parent);
            $menu->level_depth = $menu_parent->level_depth+1;
            
            $item = Tools::getValue('links');
            if($item)
            {
                $item_arr = explode('_',$item);
                if(count($item_arr)!=2)
                {
                    $this->_html .= $this->displayError($this->l('"Menu item" error'));
                     return;
                }
                $menu->item_k = $item_arr[0];
                $menu->item_v = $item_arr[1];
            }

            // Check default language
            $default_lang_id = (int)(Configuration::get('PS_LANG_DEFAULT'));
            $defaultLanguage = new Language($default_lang_id);

            if(!$id_nrt_mega_menu)
            {
                $languages = Language::getLanguages(false);
                foreach ($languages as $language)
                    if(!$menu->title[$language['id_lang']])
                         $menu->title[$language['id_lang']] = $menu->title[$defaultLanguage->id];
            }

            if (!$menu->item_k && !$menu->title[$defaultLanguage->id])
                $error[] = $this->displayError($this->l('Please select an option from "Menu" drop down list or fill out "Menu name" field.'));

            $menu->id_shop = (int)Shop::getContextShopID();
            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savecustomlinknrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&updatenrt_mega_menu'.'&id_parent='.$menu->id_nrt_mega_column.'&ct=4&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during menu').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
			else
				$this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));	
        }
		if (isset($_POST['savecustomcontentnrtmegamenu']) || isset($_POST['savecustomcontentnrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
				$menu = new NrtMegaMenuClass($id_nrt_mega_menu);
			else
				$menu = new NrtMegaMenuClass();
                
            $error = array();
    		$menu->copyFromPost();

            // Check default language
            $default_lang_id = (int)(Configuration::get('PS_LANG_DEFAULT'));
            $defaultLanguage = new Language($default_lang_id);
    		if (!$menu->html[$defaultLanguage->id])
                $error[] = $this->displayError($this->l('The field "Custom content" is required at least in ').$defaultLanguage->name);

            $menu_parent = new NrtMegaMenuClass($menu->id_parent);
            $menu->level_depth = $menu_parent->level_depth+1;

            $menu->id_shop = (int)Shop::getContextShopID();
            $menu->active = 1;

            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savecustomcontentnrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&updatenrt_mega_menu'.'&id_parent='.$menu->id_nrt_mega_column.'&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during custom content').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
			else
				$this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));	
        }
        if (isset($_POST['savecategorynrtmegamenu']) || isset($_POST['savecategorynrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
                $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
            else
                $menu = new NrtMegaMenuClass();
                
            $error = array();
            
            $menu->copyFromPost();
            $menu->id_parent = 0;
            $menu->level_depth = 0;
            
            $item = Tools::getValue('links');
            if($item)
            {
                $item_arr = explode('_',$item);
                if(count($item_arr)!=2)
                {
                    $this->_html .= $this->displayError($this->l('"Menu item" error'));
                     return;
                }
                $menu->item_k = $item_arr[0];
                $menu->item_v = $item_arr[1];
            }
            else
                $error[] = $this->displayError($this->l('Please select an option from "Category" drop down list.'));
            
            if(!$menu->id_nrt_mega_column)
                $error[] = $this->displayError($this->l('An error occurred.'));

            $menu->id_shop = (int)Shop::getContextShopID();
            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savecategorynrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&updatenrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during menu item').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
            else
                $this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));
        }
        if (isset($_POST['saveproductnrtmegamenu']) || isset($_POST['saveproductnrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
                $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
            else
                $menu = new NrtMegaMenuClass();
                
            $error = array();
            
            $menu->copyFromPost();
            $menu->id_parent = 0;
            $menu->level_depth = 0;
            
            $products = trim(Tools::getValue('inputMenuProducts'),'-');
            if(!$products)
                $error[] = $this->displayError($this->l('The field "Product name" is required.'));

            if(!$menu->id_nrt_mega_column)
                $error[] = $this->displayError($this->l('An error occurred.'));

            $menu->id_shop = (int)Shop::getContextShopID();
            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    NrtMegaProductClass::deleteMenuProducts($menu->id);
                    $products_id = array_unique(explode('-', $products));
                    if (count($products_id))
                        NrtMegaProductClass::changeMenuProducts($menu->id, $products_id);

                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['saveproductnrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&updatenrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during menu item').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
            else
                $this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));
        }

        if (isset($_POST['savebrandnrtmegamenu']) || isset($_POST['savebrandnrtmegamenuAndStay']))
        {
            if($id_nrt_mega_menu)
                $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
            else
                $menu = new NrtMegaMenuClass();
                
            $error = array();
            
            $menu->copyFromPost();
            $menu->id_parent = 0;
            $menu->level_depth = 0;
            
            if(!$menu->id_nrt_mega_column)
                $error[] = $this->displayError($this->l('An error occurred.'));

            $menu->id_shop = (int)Shop::getContextShopID();
            if (!count($error) && $menu->validateFields(false) && $menu->validateFieldsLang(false))
            {
                if($menu->save())
                {
                    NrtMegaBrandClass::deleteByMenu($menu->id);
                    $res = true;
                    if($id_manufacturer = Tools::getValue('id_manufacturer'))
                    foreach($id_manufacturer AS $value)
                    $res &= Db::getInstance()->insert('nrt_mega_brand', array(
        					'id_manufacturer' => (int)$value,
        					'id_nrt_mega_menu' => (int)$menu->id
        				));

                    $this->clearNrtMegamenuCache();
					$this->generateCss();
                    if(isset($_POST['savebrandnrtmegamenuAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&conf='.($id_nrt_mega_menu?4:3).'&updatenrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules')); 
                    else
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                }
                else
                    $this->_html .= $this->displayError($this->l('An error occurred during menu item').' '.($id_nrt_mega_menu ? $this->l('updating') : $this->l('creation')));
            }
            else
                $this->_html .= count($error) ? implode('',$error) : $this->displayError($this->l('Invalid value for field(s).'));	
        }
        if(Tools::getValue('act')=='delete_image' && $identi = Tools::getValue('id_nrt_mega_menu'))
        {
            $result = array(
                'r' => false,
                'm' => '',
                'd' => ''
            );
            $menu = new NrtMegaMenuClass((int)$identi);
            if(Validate::isLoadedObject($menu))
            {   
                @unlink(_PS_UPLOAD_DIR_.$this->name.'/'.$menu->bg_image);
                @unlink(_PS_UPLOAD_DIR_.$this->name.'/thumb'.$menu->bg_image);
                $menu->bg_image = '';
                if($menu->save())
                {
                    $result['r'] = true;
                }
            }
            die(json_encode($result));
        }
	    if ((Tools::isSubmit('activenrt_mega_menu')))
        {
    		$menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            if(Validate::isLoadedObject($menu) && $menu->toggleStatus())
            {
                $this->clearNrtMegamenuCache();
                
                if($menu->id_nrt_mega_column)
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
                else
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            } 
            else
                $this->_html .= $this->displayError($this->l('An error occurred while updating the status.'));
        }
        
        if ((Tools::isSubmit('activenrt_mega_column')))
        {
            $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
    		$column = new NrtMegaColumnClass($id_nrt_mega_column);
            if(Validate::isLoadedObject($column) && $column->toggleStatus())
            {
                $this->clearNrtMegamenuCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$column->id_nrt_mega_menu.'&viewnrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'));
            } 
            else
                $this->_html .= $this->displayError($this->l('An error occurred while updating the status.'));
        }
		
        
        if (Tools::isSubmit('addnrtmegamenu'))
		{
            $helper = $this->initForm(); 
            $this->_html .= $helper->generateForm($this->fields_form);
			return $this->_html;
		}
        elseif (Tools::isSubmit('addmenunrtmegamenu'))
		{
            if(!Tools::getValue('id_parent'))
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));

            $ct = Tools::getValue('ct');
            switch ($ct) {
                case 1:
                    $helper = $this->initCategoryForm(); 
                    break;
                case 2:
                    $helper = $this->initProductForm(); 
                    break;
                case 3:
                    $helper = $this->initBrandForm(); 
                    break;
                case 4:
                    $helper = $this->initCustomLinkForm(); 
                    break;
                case 5:
                    $helper = $this->initCustomContentForm(); 
                    break;
                default:
                    break;
            }

            $this->_html .= $helper->generateForm($this->fields_form);

            return $this->_html;
		}
        elseif (Tools::isSubmit('updatenrt_mega_menu'))
        {
    		$menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            if(!Validate::isLoadedObject($menu) || $menu->id_shop!=(int)Shop::getContextShopID())
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));

            if($menu->item_t)
            {
                switch ($menu->item_t) {
                    case 1:
                        $helper = $this->initCategoryForm(); 
                        break;
                    case 2:
                        $helper = $this->initProductForm(); 
                        break;
                    case 3:
                        $helper = $this->initBrandForm(); 
                        break;
                    case 4:
                        $helper = $this->initCustomLinkForm(); 
                        break;
                    case 5:
                        $helper = $this->initCustomContentForm(); 
                        break;
                    default:
                        break;
                }
            }
            else
            {
                $helper = $this->initForm(); 
            }
            $this->_html .= $helper->generateForm($this->fields_form);
            return $this->_html; 
        }
        elseif (Tools::isSubmit('updatenrt_mega_column'))
        {
            $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
            $column = new NrtMegaColumnClass((int)$id_nrt_mega_column);
            if(!Validate::isLoadedObject($column))
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));

            $helper = $this->initColumnForm();
            $this->_html .= $helper->generateForm($this->fields_form);

            return $this->_html; 
        }
        else if (Tools::isSubmit('deletenrt_mega_menu'))
		{
    		$menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            if(Validate::isLoadedObject($menu))
            {
                if($menu->id_parent)
                    $menu_secondary_id = NrtMegaMenuClass::getSecondaryParent((int)$menu->id);
                    
                $menu->delete();
                $this->clearNrtMegamenuCache();
				$this->generateCss();
                
                if($menu->id_nrt_mega_column)
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$menu->id_nrt_mega_column.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
			
		}
        else if (Tools::isSubmit('deletenrt_mega_column'))
		{
            $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
    		$column = new NrtMegaColumnClass($id_nrt_mega_column);
            if(Validate::isLoadedObject($column))
            {
                $column->delete();
                $this->clearNrtMegamenuCache();
				$this->generateCss();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$column->id_nrt_mega_menu.'&viewnrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
			
		}
        elseif(Tools::isSubmit('viewnrt_mega_menu'))
        {
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            if(!Validate::isLoadedObject($menu))
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
              
            $helper = $this->initColumnList();
            $all = NrtMegaColumnClass::getAll($menu->id, 0);
            $this->_html .= $helper->generateList($all, $this->fields_list);
            
            return $this->_html;
        }
        elseif(Tools::isSubmit('viewnrt_mega_column'))
        {
            $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
    		$column = new NrtMegaColumnClass((int)$id_nrt_mega_column);
            if(!Validate::isLoadedObject($column))
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
              
            $helper = $this->initMenuList();
            $jon = NrtMegaMenuClass::getByColumnId($column->id, $this->context->language->id, 0, 0, 0);
            $all = array();
            foreach ($jon  as $k) {
                $all[] = $k;
                if($k['item_t']==4)
                {
                    $li = NrtMegaMenuClass::recurseTree($k['id_nrt_mega_menu'],0,0,0,$this->context->language->id, 4);
                    if(is_array($li) && count($li))
                    {
                        $this->getCustomLinkContent($li);

                        $res = array();
                        $this->_toFlat($res, $li); 
                        foreach ($res as $l) {
                            $all[] = $l;
                        }
                    }
                    $cs = NrtMegaMenuClass::recurseTree($k['id_nrt_mega_menu'],1,0,0,$this->context->language->id, 5);
                    if(is_array($cs) && count($cs))
                    {
                        $res = array();
                        $this->_toFlat($res, $cs);
                        foreach ($res as $c) {
                            $all[] = $c;
                        }
                    }
                }
            }
            $this->_html .= $helper->generateList($all, $this->fields_list);
            
			return $this->_html;
        }
        elseif (Tools::isSubmit('addcolumnnrtmegamenu')) {
            $id_parent = (int)Tools::getValue('id_parent');
            if(!$id_parent)
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            $menu = new NrtMegaMenuClass($id_parent);
            if(!Validate::isLoadedObject($menu))
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));


            $helper = $this->initColumnForm();
            $this->_html .= $helper->generateForm($this->fields_form);

            return $this->_html;
        }
        else
        {
            $helper = $this->initList();
            $all = NrtMegaMenuClass::recurseTree(0,1,0,0,$this->context->language->id,0);
            $this->_html .= $helper->generateList($all, $this->fields_list);
            return $this->_html.$this->initVerticalLimitForm()->generateForm($this->fields_form);
        }
            
	}
    public function getCustomLinkContent(&$li)
    {
        //
        if(is_array($li) && count($li)) 
            foreach($li as &$v)
            {
                if(isset($v['children']) && is_array($v['children']) && count($v['children'])) 
                    $this->getCustomLinkContent($v['children']);

                $cc = NrtMegaMenuClass::recurseTree($v['id_nrt_mega_menu'],1,0,0,$this->context->language->id, 5);
                if(is_array($cc) && count($cc))
                {
                    if(!isset($v['children']))
                        $v['children'] = array();

                    $v['children'] += $cc;
                }
            }
        return true;
    }
    private function _toFlat(&$res, $arr, $cid=0)
    {
        if(is_array($arr) && count($arr)) 
            foreach($arr as $v)
            {
                if($cid && $v['id_nrt_mega_menu']==$cid)
                    continue;
                $tmp=$v;
                unset($tmp['children']);
                $res[] = $tmp;
                if(isset($v['children']) && is_array($v['children']) && count($v['children'])) 
                    $this->_toFlat($res, $v['children'], $cid);
            }
        return true;
    }

    public function getMyAccountLinks()
    {
        return array(
            'my-account' => array('id'=>'10_my-account', 'name'=>$this->l('My account'), 'title'=>$this->l('Manage my customer account')),
            'order-follow' => array('id'=>'10_history', 'name'=>$this->l('My orders'), 'title'=>$this->l('My orders')),
            'order-follow' => array('id'=>'10_order-follow', 'name'=>$this->l('My merchandise returns'), 'title'=>$this->l('My returns')),
            'order-slip' => array('id'=>'10_order-slip', 'name'=>$this->l('My credit slips'), 'title'=>$this->l('My credit slips')),
            'addresses' => array('id'=>'10_addresses', 'name'=>$this->l('My addresses'), 'title'=>$this->l('My addresses')),
            'identity' => array('id'=>'10_identity', 'name'=>$this->l('My personal info'), 'title'=>$this->l('Manage my personal information')),
            'discount' => array('id'=>'10_discount', 'name'=>$this->l('My vouchers'), 'title'=>$this->l('My vouchers')),
        );
    }
    
    public function getInformationLinks()
    {
        return array(
            'prices-drop' => array('id'=>'10_prices-drop', 'name'=>$this->l('Specials'), 'title'=>$this->l('Specials')),
            'new-products' => array('id'=>'10_new-products', 'name'=>$this->l('New products'), 'title'=>$this->l('New products')),
            'best-sales' => array('id'=>'10_best-sales', 'name'=>$this->l('Top sellers'), 'title'=>$this->l('Top sellers')),
            'stores' => array('id'=>'10_stores', 'name'=>$this->l('Our stores'), 'title'=>$this->l('Our stores')),
            'contact' => array('id'=>'10_contact', 'name'=>$this->l('Contact us'), 'title'=>$this->l('Contact us')),
            'sitemap' => array('id'=>'10_sitemap', 'name'=>$this->l('Sitemap'), 'title'=>$this->l('Sitemap')),
            'manufacturer' => array('id'=>'10_manufacturer', 'name'=>$this->l('Manufacturers'), 'title'=>$this->l('Manufacturers')),
            'supplier' => array('id'=>'10_supplier', 'name'=>$this->l('Suppliers'), 'title'=>$this->l('Suppliers')),
        );
    }

    public function createCategoryLinks()
    {
        $id_lang = $this->context->language->id;
        $category_arr = array();
        $this->getCategoryOption($category_arr, Category::getRootCategory()->id, (int)$id_lang, (int)Shop::getContextShopID(),true);
        return $category_arr;
    }
    
    public function createLinks($icon=true)
    {
        $id_lang = $this->context->language->id;
        $category_arr = array();
		$this->getCategoryOption($category_arr, Category::getRootCategory()->id, (int)$id_lang, (int)Shop::getContextShopID(),true);
        
        $supplier_arr = array();
		$suppliers = Supplier::getSuppliers(false, $id_lang);
		foreach ($suppliers as $supplier)
            $supplier_arr[] = array('id'=>'5_'.$supplier['id_supplier'],'name'=>$supplier['name']);
            
        $manufacturer_arr = array();
		$manufacturers = Manufacturer::getManufacturers(false, $id_lang);
		foreach ($manufacturers as $manufacturer)
            $manufacturer_arr[] = array('id'=>'4_'.$manufacturer['id_manufacturer'],'name'=>$manufacturer['name']);
  
        $cms_arr = array();
		$this->getCMSOptions($cms_arr, 0, 1, $id_lang);
        
        $blog_category_arr = array();
        if(Module::isEnabled('smartblog'))
		{
			$smart_blog_categories = BlogCategory::getCategories($id_lang,true,false);
            $this->getSmartBlogCategoryOption($blog_category_arr, $smart_blog_categories);
        }
        
        $links = array(
            array('name'=>$this->l('Category'),'query'=>$category_arr),
            array('name'=>$this->l('Informations'),'query'=>$this->getInformationLinks()),
            array('name'=>$this->l('My account'),'query'=>$this->getMyAccountLinks()),
            array('name'=>$this->l('CMS'),'query'=>$cms_arr),
            array('name'=>$this->l('Supplier'),'query'=>$supplier_arr),
            array('name'=>$this->l('Manufacturer'),'query'=>$manufacturer_arr),
            array('name'=>$this->l('Blog'),'query'=>$blog_category_arr),
            /*array('name'=>$this->l('Products'),'query'=>array(
                array('id'=>'2_0', 'name'=>$this->l('Choose ID product')),
            )),*/
        );
        if($icon)
            array_unshift($links,array('name'=>$this->l('Icon'),'query'=>array(
                array('id'=>'7_1', 'name'=>$this->l('Home icon')),
            )));
        return $links;
    }
	
    private function initVerticalLimitForm()
    {
        $this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Config'),
                'icon' => 'icon-cogs'
			),
			'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Showing vertical menu limit :'),
                    'name' => 'nrt_vetical_menu_limit',
                    'default_value' => 10,
                    'class' => 'fixed-width-sm'                    
                )
			),
			'submit' => array(
				'title' => $this->l('Save')
			),
		);
                
        $helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
        $helper->module = $this;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savevetical_menu_limit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		$helper->fields_value['nrt_vetical_menu_limit'] = Configuration::get('nrt_vetical_menu_limit');
		return $helper;
    }
    
	private function initForm()
    {
    	$id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu)
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
        else
            $menu = new NrtMegaMenuClass();
        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'links' => array(
                    'type' => 'select',
                    'label' => $this->l('Main menu:'),
                    'name' => 'links',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                    'options' => array(
                        'optiongroup' => array (
                            'query' => $this->createLinks(),
                            'label' => 'name'
                        ),
                        'options' => array (
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('Select an option or fill out Menu name field')
                        ),
                    )
                ),
                'title' => array(
                    'type' => 'text',
                    'label' => $this->l('Menu name / Overwrite name:'),
                    'name' => 'title',
                    'size' => 64,
                    'lang' => true,
                    'required' => true,
                ),
                'link' => array(
                    'type' => 'text',
                    'label' => $this->l('Link:'),
                    'name' => 'link',
                    'size' => 64,
                    'lang' => true,
                ),
                array(
                    'type' => 'html',
                    'id'   => 'location',
                    'label' => $this->l('Display on:'),
                    'name' => $this->BuildRadioUI($this->_location, 'location', (int)$menu->location),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Submenu type:'),
                    'name' => 'is_mega',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'is_mega_1',
                            'value' => 1,
                            'label' => $this->l('Mega')
                        ),
                        array(
                            'id' => 'is_mega_0',
                            'value' => 0,
                            'label' => $this->l('Multi level')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Submenu width:'),
                    'name' => 'width',
                    'default_value' => '500px',
					'desc' => $this->l('Set maxium width of submenu. You must provide px or percent suffix (example 500px or 100vw)'),
					'class' => 'fixed-width-xxl'
                ),
                array(
                    'type' => 'html',
                    'id'   => 'alignment',
                    'label' => $this->l('Submenu alignment for Vertical menu:'),
                    'name' => $this->BuildRadioUI($this->_align, 'alignment', (int)$menu->alignment),
                    'desc' => $this->l('Only for Vertical menu.'),
                ),
                array(
                    'type' => 'fontello',
                    'label' => $this->l('Icon:'),
                    'name' => 'icon_class',
                    'values' => $this->get_fontello(),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Open in a new window:'),
                    'name' => 'new_window',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'new_window_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'new_window_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('No follow:'),
                    'name' => 'nofollow',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'nofollow_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'nofollow_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                    'desc' => $this->l('The "nofollow" option controls whether a nofollow attribute is placed on links, which affects the way search engines interact with those links.'),
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->l('Custom class:'),
                    'name' => 'custom_class',
                    'default_value' => ''                 
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Advanced Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'color',
                    'label' => $this->l('Link color:'),
                    'name' => 'txt_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link hover color:'),
                    'name' => 'txt_color_over',
                    'class' => 'color',
                    'size' => 20,
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Link background color:'),
                    'name' => 'bg_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link hover background color:'),
                    'name' => 'bg_color_over',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->l('Label:'),
                    'name' => 'cate_label',
                    'size' => 64,
                    'lang' => true,
                    'desc' => $this->l('E.g. "Hot", "New"'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable color:'),
                    'name' => 'cate_label_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable background:'),
                    'name' => 'cate_label_bg',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Submenu background color:'),
                    'name' => 'tab_content_bg',
                    'class' => 'color',
                    'size' => 20,
                ), 
                'bg_image_field' => array(
                    'type' => 'file',
                    'label' => $this->l('Submenu background image:'),
                    'name' => 'bg_image_field',
                    'desc' => '',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Repeat:'),
                    'name' => 'bg_repeat',
                    'default_value' => 3,
                    'values' => array(
                        array(
                            'id' => 'bg_repeat_no',
                            'value' => 3,
                            'label' => $this->l('No repeat')),
                        array(
                            'id' => 'bg_repeat_xy',
                            'value' => 0,
                            'label' => $this->l('Repeat xy')),
                        array(
                            'id' => 'bg_repeat_x',
                            'value' => 1,
                            'label' => $this->l('Repeat x')),
                        array(
                            'id' => 'bg_repeat_y',
                            'value' => 2,
                            'label' => $this->l('Repeat y')),
                    ),
                    'validation' => 'isUnsignedInt',
                ), 
                array(
                    'type' => 'radio',
                    'label' => $this->l('Position:'),
                    'name' => 'bg_position',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'bg_position_rb',
                            'value' => 0,
                            'label' => $this->l('right bottom')),
                        array(
                            'id' => 'bg_position_rt',
                            'value' => 4,
                            'label' => $this->l('right top')),
                        array(
                            'id' => 'bg_position_rc',
                            'value' => 5,
                            'label' => $this->l('right center')),
                        array(
                            'id' => 'bg_position_lt',
                            'value' => 1,
                            'label' => $this->l('left top')),
                        array(
                            'id' => 'bg_position_lc',
                            'value' => 2,
                            'label' => $this->l('left center')),
                        array(
                            'id' => 'bg_position_lb',
                            'value' => 3,
                            'label' => $this->l('left bottom')),
                        array(
                            'id' => 'bg_position_ct',
                            'value' => 6,
                            'label' => $this->l('center top')),
                        array(
                            'id' => 'bg_position_cc',
                            'value' => 7,
                            'label' => $this->l('center center')),
                        array(
                            'id' => 'bg_position_cb',
                            'value' => 8,
                            'label' => $this->l('center bottom')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                 array(
                    'type' => 'text',
                    'label' => $this->l('Submenu bottom padding:'),
                    'name' => 'bg_margin_bottom',
                    'suffix' => 'px',
                    'validation' => 'isUnsignedInt',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'  
                 ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );
        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
            if ($menu->bg_image)
                $this->fields_form[1]['form']['input']['bg_image_field']['image'] = '<img src="'._THEME_PROD_PIC_DIR_.$this->name.'/thumb'.$menu->bg_image.'" class="img_preview">
                    <p><a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$menu->id.'&token='.Tools::getAdminTokenLite('AdminModules').'" class="btn btn-default nrt_delete_image"><i class="icon-trash"></i> '.$this->l('Delete').'</a></p>';
        }
        $helper = new HelperForm();
		$helper->show_toolbar = false;
        $helper->module = $this;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savenrtmegamenu';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getFieldsValueForm($menu),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);         

        if(Validate::isLoadedObject($menu))
        {
            $helper->tpl_vars['fields_value']['links'] = $menu->item_k.'_'.$menu->item_v;
        }

		return $helper;
    }
    public function getParentList($id_nrt_mega_column, $cid)
    {
        $result = array();
        $parents = NrtMegaMenuClass::getByColumnId($id_nrt_mega_column, $this->context->language->id, 0, 4, 0);
        if(is_array($parents) && count($parents))
        {
            foreach($parents as &$v)
            {
                $jon = NrtMegaMenuClass::recurseTree($v['id_nrt_mega_menu'],0,0,$active=0,$this->context->language->id, 4);
                if(is_array($jon) && count($jon))
                    $v['children'] = $jon;
            }

            $res = array();
            if($parents)
                $this->_toFlat($res, $parents, $cid);    

            foreach ($res as $value)
            {
                $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$value['level_depth']);
                $result[] = array(
                    'id' => $value['id_nrt_mega_menu'],
                    'name' => $spacer.$this->displayTitle($value['title'],$value),
                );
            }
        }
        
        return $result;
    }

    public function recurseParents($v, $parents)
    {
        foreach($parents as $value)
        {
            if($v['id_nrt_mega_menu'] == $value['id_parent'])
            {
                $value = $this->recurseParents($value, $parents);
                $v['children'][$value['id_nrt_mega_menu']] = $value;
            }
        }
        return $v;
    }
    public function initCategoryForm()
    {
        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu)
        {
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            $id_parent = $menu->id_nrt_mega_column;
        }
        else
            $menu = new NrtMegaMenuClass();

        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');

        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Category'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'links' => array(
                    'type' => 'select',
                    'label' => $this->l('Categories:'),
                    'name' => 'links',
                    'required' => true,
                    'options' => array(
                        'query' => $this->createCategoryLinks(),
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('Select category'),
                        ),
                    )
                ),
                'title' => array(
                    'type' => 'text',
                    'label' => $this->l('Overwrite name:'),
                    'name' => 'title',
                    'size' => 64,
                    'lang' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show category image:'),
                    'name' => 'show_cate_img',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'show_cate_img_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'show_cate_img_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Menu item:'),
                    'name' => 'subtype',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'subtype_categories',
                            'value' => 0,
                            'label' => $this->l('Sub-categories')
                        ),
                        array(
                            'id' => 'subtype_self_categories',
                            'value' => 1,
                            'label' => $this->l('Self + Sub-categories')
                        ),
                        array(
                            'id' => 'subtype_products',
                            'value' => 2,
                            'label' => $this->l('Products')
                        ),
                        array(
                            'id' => 'subtype_products',
                            'value' => 3,
                            'label' => $this->l('Self only')
                        ),
                    ),
                ),
                array(
                    'type' => 'dropdownlistgroup',
                    'label' => $this->l('Items per row:'),
                    'name' => 'items',
                    'values' => array(
                            'maximum' => 6,
                            'medias' => array('md'),
                        ),
                    'desc' => $this->l('Actually only for Mega menu.'),
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->l('Levels:'),
                    'name' => 'sub_levels',
                    'default_value' => 2,
                    'class' => 'fixed-width-sm',
                    'desc' => $this->l('0 for no limits.'),                           
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Item limit:'),
                    'name' => 'item_limit',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm',     
                    'desc' => $this->l('0 for no limits. You have to fill this field if you have set "Menu item" to "Products".'),               
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Sub-item limit:'),
                    'name' => 'sub_limit',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm',     
                    'desc' => $this->l('0 for no limits.'),                 
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('How to display 4th level + menu items:'),
                    'name' => 'granditem',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'granditem_1',
                            'value' => 1,
                            'label' => $this->l('Display them under their parent menu items')
                        ),
                        array(
                            'id' => 'granditem_0',
                            'value' => 0,
                            'label' => $this->l('Display them when mouse over their parent menu items.')
                        )
                    ),
                    'desc' => $this->l('Only for Mega menu.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Open in a new window:'),
                    'name' => 'new_window',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'new_window_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'new_window_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('No follow:'),
                    'name' => 'nofollow',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'nofollow_on',

                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'nofollow_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                    'desc' => $this->l('The "nofollow" option controls whether a nofollow attribute is placed on links, which affects the way search engines interact with those links.'),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_column',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'item_t',
                    'default_value' => 1,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Advanced settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'color',
                    'label' => $this->l('Link color:'),
                    'name' => 'txt_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link hover color:'),
                    'name' => 'txt_color_over',
                    'class' => 'color',
                    'size' => 20,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Label:'),
                    'name' => 'cate_label',
                    'size' => 64,
                    'lang' => true,
                    'desc' => $this->l('E.g. "Hot", "New"'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable color:'),
                    'name' => 'cate_label_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable background:'),
                    'name' => 'cate_label_bg',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );
        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savecategorynrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($menu),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   

        if(Validate::isLoadedObject($menu))
        {
            $helper->tpl_vars['fields_value']['links'] = $menu->item_k.'_'.$menu->item_v;
        }
        $helper->tpl_vars['fields_value']['id_nrt_mega_column'] = $id_parent;
        $helper->tpl_vars['fields_value']['items_md'] = (int)$menu->items_md;

        return $helper;
    }
    public function initProductForm()
    {
        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu){
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            $id_parent = $menu->id_nrt_mega_column;
        }
        else
            $menu = new NrtMegaMenuClass();

        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');


        $menuProducts = NrtMegaProductClass::getMenuProductsLight($this->context->language->id, $menu->id);

        $product_div = '';
        $product_ids = '';
        $product_name = '';
        if(is_array($menuProducts) && count($menuProducts))
            foreach ($menuProducts as $v) {
                $product_div .= '<div class="form-control-static">
                    <button type="button" class="btn btn-default delMenuProduct" name="'.$v['id_product'].'">
                        <i class="icon-remove text-danger"></i>
                    </button>
                    '.$v['name'].' ('.$this->l('ref').': '.$v['reference'].')
                </div>';
                $product_ids .= $v['id_product'].'-';
                $product_name .= $v['name'].'Ã‚Â¤';
            }
        

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'product_name' => array(
                    'type' => 'text',
                    'label' => $this->l('Product name:'),
                    'name' => 'product_name',
                    'autocomplete' => false,
                    'desc' => $this->l('Current product').': <ul id="curr_product_name">'.$product_div.'</ul>',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'inputMenuProducts',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'nameMenuProducts',
                ),
                array(
                    'type' => 'dropdownlistgroup',
                    'label' => $this->l('Items per row:'),
                    'name' => 'items',
                    'values' => array(
                            'maximum' => 6,
                            'medias' => array('md'),
                        ),
                    'desc' => $this->l('Only for Mega menu.'),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_column',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'item_t',
                    'default_value' => 2,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveproductnrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($menu),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   

        $helper->tpl_vars['fields_value']['id_nrt_mega_column'] = $id_parent;
        $helper->tpl_vars['fields_value']['inputMenuProducts'] = $product_ids;
        $helper->tpl_vars['fields_value']['nameMenuProducts'] = $product_name;
        $helper->tpl_vars['fields_value']['items_md'] = (int)$menu->items_md;

        return $helper;
    }
    public function initBrandForm()
    {

        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu){
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            $id_parent = $menu->id_nrt_mega_column;
        }
        else
            $menu = new NrtMegaMenuClass();

        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show all Brands:'),
                    'name' => 'item_k',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'brands_item_k_1',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'brands_item_k_0',
                            'value' => 0,
                            'label' => $this->l('No'))
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                'manufacturers' => array(
					'type' => 'text',
					'label' => $this->l('Specific Brands:'),
					'name' => 'manufacturers',
                    'autocomplete' => false,
                    'class' => 'fixed-width-xxl',
                    'desc' => '',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Type:'),
                    'name' => 'subtype',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'subtype_0',
                            'value' => 0,
                            'label' => $this->l('Image')),
                        array(
                            'id' => 'subtype_1',
                            'value' => 1,
                            'label' => $this->l('List'))
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'dropdownlistgroup',
                    'label' => $this->l('Items per row:'),
                    'name' => 'items',
                    'values' => array(
                            'maximum' => 6,
                            'medias' => array('md'),
                        ),
                    'desc' => $this->l('Actually only for Mega menu.'),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_column',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'item_t',
                    'default_value' => 3,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savebrandnrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($menu),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   

        $helper->tpl_vars['fields_value']['id_nrt_mega_column'] = $id_parent;
        $helper->tpl_vars['fields_value']['items_md'] = (int)$menu->items_md;

        $manufacturers_html = '';
        if ($res = NrtMegaBrandClass::getByMenu((int)$menu->id))
            foreach($res AS $value)
            {
                $manufacturers_html .= '<li>'.Manufacturer::getNameById($value['id_manufacturer']).'
                <a href="javascript:void(0)" class="del_manufacturer"><i class="icon-remove text-danger"></i></a>
                <input type="hidden" name="id_manufacturer[]" value="'.$value['id_manufacturer'].'" /></li>';
            }
        
        $this->fields_form[0]['form']['input']['manufacturers']['desc'] = $this->l('Actually only for "Show all Brands" is set to "No".').'<br/>'.$this->l('Current manufacturers')
                .': <ul id="curr_manufacturers">'.$manufacturers_html.'</ul>'; 

        return $helper;
    }
    public function initCustomLinkForm()
    {
        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu)
        {
            $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
            $id_parent = $menu->id_nrt_mega_column;
        }
        else
        {
            $menu = new NrtMegaMenuClass();
        }
        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');
           

        $cid=0;
        if(Validate::isLoadedObject($menu))
            $cid = $menu->id;
        $parents_arr = $this->getParentList($id_parent, $cid);
        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Custom link'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Parent:'),
                    'name' => 'id_parent',
                    'options' => array(
                        'query' => $parents_arr,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 0,
                            'label' => $this->l('Please select')
                        )
                    )
                ),
                'links' => array(
                    'type' => 'select',
                    'label' => $this->l('Menu item:'),
                    'name' => 'links',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                    'options' => array(
                        'optiongroup' => array (
                            'query' => $this->createLinks(),
                            'label' => 'name'
                        ),
                        'options' => array (
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('Select an option or fill out Menu name field')
                        ),
                    )
                ),
                'title' => array(
                    'type' => 'text',
                    'label' => $this->l('Menu name:'),
                    'name' => 'title',
                    'size' => 64,
                    'lang' => true,
                    'required' => true,
                ),
                'link' => array(
                    'type' => 'text',
                    'label' => $this->l('Link:'),
                    'name' => 'link',
                    'size' => 64,
                    'lang' => true,
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('How to display it\'s sub menu items:'),
                    'name' => 'granditem',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'granditem_1',
                            'value' => 1,
                            'label' => $this->l('Display it\'s sub menu items under it')
                        ),
                        array(
                            'id' => 'granditem_0',
                            'value' => 0,
                            'label' => $this->l('Display it\'s sub menu items when mouse over it.')
                        )
                    ),
                    'desc' => $this->l('Only for Mega menu and level 4+ menu items.'),
                ),
                array(
                    'type' => 'fontello',
                    'label' => $this->l('Icon:'),
                    'name' => 'icon_class',
                    'values' => $this->get_fontello(),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Open in a new window:'),
                    'name' => 'new_window',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'new_window_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'new_window_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('No follow:'),
                    'name' => 'nofollow',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'nofollow_on',
                            'value' => 1,
                            'label' => $this->l('Yes')),
                        array(
                            'id' => 'nofollow_off',
                            'value' => 0,
                            'label' => $this->l('No')),
                    ),
                    'desc' => $this->l('The "nofollow" option controls whether a nofollow attribute is placed on links, which affects the way search engines interact with those links.'),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_column',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'item_t',
                    'default_value' => 4,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Advanced settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'color',
                    'label' => $this->l('Link color:'),
                    'name' => 'txt_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link hover color:'),
                    'name' => 'txt_color_over',
                    'class' => 'color',
                    'size' => 20,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Label:'),
                    'name' => 'cate_label',
                    'size' => 64,
                    'lang' => true,
                    'desc' => $this->l('E.g. "Hot", "New"'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable color:'),
                    'name' => 'cate_label_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Lable background:'),
                    'name' => 'cate_label_bg',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );
        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savecustomlinknrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($menu),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   

        $helper->tpl_vars['fields_value']['id_nrt_mega_column'] = $id_parent;

        if(Validate::isLoadedObject($menu))
        {
            $helper->tpl_vars['fields_value']['links'] = $menu->item_k.'_'.$menu->item_v;
			$helper->tpl_vars['fields_value']['id_parent'] = $menu->id_parent;
			
        }

        return $helper;

    }
    public function initCustomContentForm()
    {

        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        if($id_nrt_mega_menu){
            $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu);
            $id_parent = $menu->id_nrt_mega_column;
        }
        else
            $menu = new NrtMegaMenuClass();

        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');

        $cid=0;
        if(Validate::isLoadedObject($menu))
            $cid = $menu->id;
        $parents_arr = $this->getParentList($id_parent, $cid);

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Custom content'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Parent:'),
                    'name' => 'id_parent',
                    'options' => array(
                        'query' => $parents_arr,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 0,
                            'label' => $this->l('Please select')
                        )
                    )
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content:'),
                    'lang' => true,
                    'name' => 'html',
                    'cols' => 40,
                    'rows' => 10,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_column',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'item_t',
                    'default_value' => 5,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_column='.$id_parent.'&viewnrt_mega_column&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Advanced settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'color',
                    'label' => $this->l('Text color:'),
                    'name' => 'txt_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link color:'),
                    'name' => 'link_color',
                    'class' => 'color',
                    'size' => 20,
                ), 
                array(
                    'type' => 'color',
                    'label' => $this->l('Link hover color:'),
                    'name' => 'txt_color_over',
                    'class' => 'color',
                    'size' => 20,
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_advanced_column='.$id_parent.'&viewstadvancedcolumn&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        if(Validate::isLoadedObject($menu))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_menu');
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savecustomcontentnrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($menu),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   
        $helper->tpl_vars['fields_value']['id_nrt_mega_column'] = $id_parent;
		
        if(Validate::isLoadedObject($menu))
        {
           $helper->tpl_vars['fields_value']['id_parent'] = $menu->id_parent;
        }
		
        return $helper;
    }

    private function getCategoryOption(&$category_arr, $id_category = 1, $id_lang = false, $id_shop = false, $recursive = true)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

		if (is_null($category->id))
			return;

		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
		}

		$shop = (object) Shop::getShop((int)$category->getShopID());
		$category_arr[] = array('id'=>'1_'.(int)$category->id,'name'=>(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')');

		if (isset($children) && is_array($children) && count($children))
			foreach ($children as $child)
			{
				$this->getCategoryOption($category_arr, (int)$child['id_category'], (int)$id_lang, (int)$child['id_shop'],$recursive);
			}
	}

    private function getSmartBlogCategoryOption(&$blog_category_arr, $blog_categories)
    {
		$module = new NrtMegaMenu();
		$blog_category_arr[] = array('id'=>'8_0','name'=>$module->l('Blog (All Blog)'));
        foreach($blog_categories as $category)
        {
            $spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']);
			
            $name = $category['name'].$module->l(' (Category)');
                            
            $blog_category_arr[] = array('id'=>'8_'.(int)$category['id_smart_blog_category'],'name'=>(isset($spacer) ? $spacer : '').$name);
            
            foreach($this->getSmartBlogPage((int)$category['id_smart_blog_category']) AS $blog)
            {
                $blog_category_arr[] = array('id'=>'9_'.(int)$category['id_smart_blog_category'].'-'.(int)$blog['id_smart_blog_post'],'name'=>(isset($spacer) ? $spacer.str_repeat('&nbsp;', $this->spacer_size) : '').'-- '.$blog['meta_title']);
            }

        }
    }
    private function getSmartBlogPage($id_category = 0)
    {

      	$id_lang = (int) Context::getContext()->language->id;

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_lang pl INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post p ON pl.id_smart_blog_post=p.id_smart_blog_post INNER JOIN
                ' . _DB_PREFIX_ . 'smart_blog_post_category pc ON p.id_smart_blog_post=pc.id_smart_blog_post
                WHERE pl.id_lang=' . $id_lang . ' and p.active = 1 AND pc.id_smart_blog_category = ' . $id_category;
				
		return Db::getInstance()->executeS($sql);		

    }

	private function getCMSOptions(&$cms_arr, $parent = 0, $depth = 1, $id_lang = false)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		$categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);
		$pages = $this->getCMSPages((int)$parent, false, (int)$id_lang);

		$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$depth);

		foreach ($categories as $category)
		{
            $cms_arr[] = array('id'=>'6_'.$category['id_cms_category'],'name'=>$spacer.$category['name']);
			$this->getCMSOptions($cms_arr, $category['id_cms_category'], (int)$depth + 1, (int)$id_lang);
		}

		foreach ($pages as $page)
            $cms_arr[] = array('id'=>'3_'.$page['id_cms'],'name'=>$spacer.$page['meta_title']);
	}

    private function getCMSCategories($recursive = false, $parent = 1, $id_lang = false, $id_shop = false)
	{
        $id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		if ($recursive === false)
        {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
            FROM `'._DB_PREFIX_.'cms_category` bcp
            INNER JOIN `'._DB_PREFIX_.'cms_category_shop` cs
            ON (bcp.`id_cms_category` = cs.`id_cms_category`)
            INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
            ON (bcp.`id_cms_category` = cl.`id_cms_category`)
            WHERE cl.`id_lang` = '.(int)$id_lang.'
            AND cs.`id_shop` = '.(int)$id_shop.'
            AND cl.`id_shop` = '.(int)$id_shop.'
            AND bcp.`id_parent` = '.(int)$parent;

            return Db::getInstance()->executeS($sql);
        }
        else
        {
            $sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
            FROM `'._DB_PREFIX_.'cms_category` bcp
            INNER JOIN `'._DB_PREFIX_.'cms_category_shop` cs
            ON (bcp.`id_cms_category` = cs.`id_cms_category`)
            INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
            ON (bcp.`id_cms_category` = cl.`id_cms_category`)
            WHERE cl.`id_lang` = '.(int)$id_lang.'
            AND cs.`id_shop` = '.(int)$id_shop.'
            AND cl.`id_shop` = '.(int)$id_shop.'
            AND bcp.`id_parent` = '.(int)$parent;

			$results = Db::getInstance()->executeS($sql);
			foreach ($results as $result)
			{
				$sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], (int)$id_lang);
				if ($sub_categories && count($sub_categories) > 0)
					$result['sub_categories'] = $sub_categories;
				$categories[] = $result;
			}

			return isset($categories) ? $categories : false;
		}

	}

	private function getCMSPages($id_cms_category, $id_shop = false, $id_lang = false)
	{
		$id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		$sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE c.`id_cms_category` = '.(int)$id_cms_category.'
            AND cs.`id_shop` = '.(int)$id_shop.'
            AND cl.`id_shop` = '.(int)$id_shop.' 
			AND cl.`id_lang` = '.(int)$id_lang.'
			AND c.`active` = 1
			ORDER BY `position`';

		return Db::getInstance()->executeS($sql);
	}


    public static function displayTitle($value, $row)
	{
        $id_lang = (int)Context::getContext()->language->id;
		$id_shop = (int)Shop::getContextShopID();
        $spacer = str_repeat('&nbsp;', 5 * (int)$row['level_depth']);
        $name = '';
        switch($row['item_k'])
        {
            case 0:
                $name = $value;
            break;
            case 1:
                $category = new Category((int)$row['item_v'],$id_lang);
                if(Validate::isLoadedObject($category))
                    $name = $category->name;
            break;
            case 2:
                $product = new Product((int)$row['item_v'], false, (int)$id_lang);
                if ($product->id)
                    $name = $product->name;
            break;
            case 3:
                $cms = CMS::getLinks((int)$id_lang, array((int)$row['item_v']));
				if (count($cms))
					$name = $cms[0]['meta_title'];
            break;
            case 4:
                $manufacturer = new Manufacturer((int)$row['item_v'], (int)$id_lang);
				if ($manufacturer->id)
				    $name = $manufacturer->name;
            break;
            case 5:
                $supplier = new Supplier((int)$row['item_v'], (int)$id_lang);
				if ($supplier->id)
				    $name = $supplier->name;
            break;
			case 6:
				$category = new CMSCategory((int)$row['item_v'], (int)$id_lang);
				if ($category->id)
					$name = $category->name;
			break;
			case 7:
                $module = new NrtMegaMenu();
				return $module->l('Home icon');
			break;
			case 8:
	            if(Module::isEnabled('smartblog'))
				{
					if((int)$row['item_v']){
						$category = new BlogCategory((int)$row['item_v'],$id_lang);
						if(Validate::isLoadedObject($category))
						{
							$name = $category->name;
						}
					}else{
						$module = new NrtMegaMenu();
						$name = $module->l('Blog');
					}
				}
			break;
			case 9:
                if(Module::isEnabled('smartblog'))
				{					
					$item_arr = explode('-', $row['item_v']);
					$post = new SmartBlogPost((int)$item_arr[1],$id_lang);
					if(Validate::isLoadedObject($post))
					{
						$name = $post->meta_title;
					}	
				}            
			break;
			case 10:
                $module = new NrtMegaMenu(); 
                $information = $module->getInformationLinks();
                $myAccount = $module->getMyAccountLinks();  
                
                if(array_key_exists($row['item_v'],$information))
                    $name = $information[$row['item_v']]['name'];
                if(array_key_exists($row['item_v'],$myAccount))
                    $name = $myAccount[$row['item_v']]['name'];
			break;
        }
        return $row['title'] ? $row['title'] : $name;
	}

    public static function displayType($value, $row)
    {
        return self::$_type[$value];
    }

    public static function displayLocation($value, $row)
    {
        $location = '';
        $menu = Module::getInstanceByName('nrtmegamenu');
        foreach ($menu->_location as $v) {
            if($v['value']==$value)
            {
                $location = $v['label'];
                break;
            }
        }
        return $location;
    }

    public static function displayItemType($value, $row)
	{
		return self::$_item_type[$value];
	}

    protected function initList()
	{
		$this->fields_list = array(            
            'id_nrt_mega_menu' => array(
                'title' => $this->l('Id'),
                'width' => 120,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
			'title' => array(
				'title' => $this->l('Title'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayTitle',
				'callback_object' => 'NrtMegaMenu',
                'search' => false,
                'orderby' => false,
			),
            'item_k' => array(
                'title' => $this->l('Type'),
                'width' => 140,
                'type' => 'text',
                'callback' => 'displayType',
                'callback_object' => 'NrtMegaMenu',
                'search' => false,
                'orderby' => false,
            ),
            'location' => array(
                'title' => $this->l('Display on'),
                'width' => 140,
                'type' => 'text',
                'callback' => 'displayLocation',
                'callback_object' => 'NrtMegaMenu',
                'search' => false,
                'orderby' => false,
            ),
			'position' => array(
				'title' => $this->l('Position'),
				'width' => 140,
				'type' => 'text',
                'search' => false,
                'orderby' => false,
			),
            'active' => array(
				'title' => $this->l('Status'),
				'align' => 'center',
				'active' => 'active',
				'type' => 'bool',
				'orderby' => false,
				'width' => 25,
                'search' => false,
                'orderby' => false,
            ),
		);

		if (Shop::isFeatureActive())
			$this->fields_list['id_shop'] = array(
                'title' => $this->l('ID Shop'), 
                'align' => 'center', 
                'width' => 25, 
                'type' => 'int',
                'search' => false,
                'orderby' => false,
                );

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = false;
        $helper->module = $this;
		$helper->identifier = 'id_nrt_mega_menu';
		$helper->actions = array('view', 'edit', 'delete','duplicate');
		$helper->show_toolbar = true;
		$helper->toolbar_btn['new'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&add'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->l('Add main menu')
		);
		$helper->title = $this->displayName;
		$helper->table = 'nrt_mega_menu';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
	}
    
    public function displayDuplicateLink($token, $id, $name)
    {
        return '<li class="divider"></li><li><a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&copy'.$this->name.'&id_nrt_mega_menu='.(int)$id.'&token='.$token.'"><i class="icon-copy"></i>'.$this->l(' Duplicate ').'</a></li>';
    }

    public static function displayWidth($value, $row)
    {
        return ($value*10/10).'/12';
    }

    public function initColumnList()
    {
        $this->fields_list = array(
            'title' => array(
                'title' => $this->l('Title'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'width' => array(
                'title' => $this->l('Width'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class'=>'fixed-width-xxl',
                'callback' => 'displayWidth',
                'callback_object' => 'NrtMegaMenu',
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'class'=>'fixed-width-xxl',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'active',
                'type' => 'bool',
                'orderby' => false,
                'class'=>'fixed-width-sm',
                'search' => false,
                'orderby' => false,
            ),
        );

        $id_nrt_mega_menu = (int)Tools::getValue('id_nrt_mega_menu');
        $menu = new NrtMegaMenuClass((int)$id_nrt_mega_menu, $this->context->language->id);
        if(!$menu->is_mega)
            unset($this->fields_list['width']);

        $helper = new HelperList();
        $helper->module = $this;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_nrt_mega_column';
        $helper->actions = array('view', 'edit', 'delete');
        $helper->show_toolbar = true;
        $helper->toolbar_btn['new'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addcolumn'.$this->name.'&id_parent='.(int)Tools::getValue('id_nrt_mega_menu').'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add column')
        );
        $helper->toolbar_btn['back'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back')
        );
        $helper->tpl_vars['navigate'] = array(
            '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l("Home").'</a>',
            self::displayTitle($menu->title, get_object_vars($menu))
        );
        
        $helper->title = $this->l('Columns');
        $helper->table = 'nrt_mega_column';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        return $helper;
    }
    

    private function initColumnForm()
    {
        $id_nrt_mega_column = (int)Tools::getValue('id_nrt_mega_column');
        if($id_nrt_mega_column)
        {
            $column = new NrtMegaColumnClass((int)$id_nrt_mega_column);
            $id_parent = $column->id_nrt_mega_menu;
        }
        else
            $column = new NrtMegaColumnClass();

        if(!isset($id_parent) && Tools::getValue('id_parent'))
            $id_parent = (int)Tools::getValue('id_parent');
        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Column'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                'column_width' => array(
                    'type' => 'select',
                    'label' => $this->l('Width:'),
                    'name' => 'width',
                    'required' => true,
                    'options' => array(
                        'query' => self::$_bootstrap,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 3,
                            'label' => '3/12',
                        ),
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->l('This title would not show on the front office, only as a reminde.'),
                ),
                array(
                    'type' => 'radio',
					'label' => $this->l('Visibility:'),
					'name' => 'hide_on_mobile',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'hide_on_mobile_0',
							'value' => 0,
							'label' => $this->l('Visible on all devices')),
						array(
							'id' => 'hide_on_mobile_1',
							'value' => 1,
							'label' => $this->l('Visible on large devices (screen width > 1025px)')),
                        array(
							'id' => 'hide_on_mobile_2',
							'value' => 2,
							'label' => $this->l('Visible on small devices (screen width < 1025px)')),
					),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_nrt_mega_menu',
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$id_parent.'&viewnrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> '.$this->l('Back to list').'</a><br><br><a class="btn btn-default btn-block fixed-width-lg" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to home page</a>',    
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'fr',
                    'default_value' => Tools::getValue('fr'),
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'stay' => true
            ),
        );

        if(Validate::isLoadedObject($column))
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_nrt_mega_column');
        }

        $parent = new NrtMegaMenuClass($id_parent);
        if(!$parent->is_mega)
            unset($this->fields_form[0]['form']['input']['column_width']);

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savecolumnnrtmegamenu';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getFieldsValueForm($column),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );   

        $helper->tpl_vars['fields_value']['id_nrt_mega_menu'] = $id_parent;

        return $helper;
    }
    public static function displayContent($value, $row)
    {
        if($value)
            return NrtMegaMenu::displayTitle($value, $row);
        else
        {
            $module = new NrtMegaMenu();
            return $module->l('Custom content');
        }
    }

    public static function displayItemTitle($value, $row)
    {
        $context = Context::getContext();
        $res = '-';
        switch ($row['item_t']) {
            case 1:
            case 4:
                $res = str_repeat('&nbsp;', 5 * (int)$row['level_depth']).self::displayTitle($value, $row);
                break;
            case 2:
                $menuProducts = NrtMegaProductClass::getMenuProductsLight($context->language->id, $row['id_nrt_mega_menu']);
                if(is_array($menuProducts) && count($menuProducts))
                {
                    $res = '<ul class="item_list_in_td">';
                    foreach ($menuProducts as $v)
                        $res .= '<li>'.$v['name'].'</li>';
                    $res .= '</ul>';
                }
                break;
            case 3:
                if ($row['item_k'] == 1)
                {
                    $module = new NrtMegaMenu();
                    $res = $module->l('All');
                }
                else
                {
                    $menuBrands = NrtMegaBrandClass::getMenuBrandsLight($context->language->id, $row['id_nrt_mega_menu']);
                    
                    if(is_array($menuBrands) && count($menuBrands))
                    {
                        $res = '<ul class="item_list_in_td">';
                        foreach ($menuBrands as $v)
                            $res .= '<li>'.$v['name'].'</li>';
                        $res .= '</ul>';
                    }    
                }
                break;
            case 5:
                $res = str_repeat('&nbsp;', 5 * (int)$row['level_depth']).Tools::truncateString(strip_tags(stripslashes($row['html'])), 80);
                break;
            default:
                break;
        }
        return $res;
    }

    public function initMenuList()
    {
        $id_parent = (int)Tools::getValue('id_nrt_mega_column');
        $column = new NrtMegaColumnClass($id_parent);
        if(!Validate::isLoadedObject($column))
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));

        $this->fields_list = array(
			'item_t' => array(
				'title' => $this->l('Type'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayItemType',
				'callback_object' => 'NrtMegaMenu',
                'search' => false,
                'orderby' => false,
			),
            'title' => array(
                'title' => $this->l('Content'),
                'width' => 140,
                'type' => 'text',
                'callback' => 'displayItemTitle',
                'callback_object' => 'NrtMegaMenu',
                'search' => false,
                'orderby' => false,
            ),
            'link' => array(
                'title' => $this->l('Link'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ),
			'position' => array(
				'title' => $this->l('Position'),
				'width' => 140,
				'type' => 'text',
                'search' => false,
                'orderby' => false,
			),
            'active' => array(
				'title' => $this->l('Status'),
				'align' => 'center',
				'active' => 'active',
				'type' => 'bool',
				'orderby' => false,
				'width' => 25,
                'search' => false,
                'orderby' => false,
            ),
		);

		if (Shop::isFeatureActive())
			$this->fields_list['id_shop'] = array(
                'title' => $this->l('ID Shop'), 
                'align' => 'center', 
                'width' => 25, 
                'type' => 'int',
                'search' => false,
                'orderby' => false,
                );

        $leval0 = new NrtMegaMenuClass($column->id_nrt_mega_menu, $this->context->language->id);
        
		$helper = new HelperList();
        $helper->module = $this;
		$helper->shopLinkType = '';
		$helper->simple_header = false;
		$helper->identifier = 'id_nrt_mega_menu';
		$helper->actions = array('edit', 'delete');
		$helper->show_toolbar = true;
		$helper->toolbar_btn['new_category'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addmenu'.$this->name.'&id_parent='.$id_parent.'&ct=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add category block'),
            'class' => 'process-icon-new',
        );
        if($leval0->is_mega)
        {
            $helper->toolbar_btn['new_product'] =  array(
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addmenu'.$this->name.'&id_parent='.$id_parent.'&ct=2&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Add product block'),
                'class' => 'process-icon-new',
            );
            $helper->toolbar_btn['new_brand'] =  array(
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addmenu'.$this->name.'&id_parent='.$id_parent.'&ct=3&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Add brand block'),
                'class' => 'process-icon-new',
            );
        }
        $helper->toolbar_btn['new_custom_link'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addmenu'.$this->name.'&id_parent='.$id_parent.'&ct=4&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add custom link'),
            'class' => 'process-icon-new',
        );
        $helper->toolbar_btn['new_custom_content'] =  array(
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addmenu'.$this->name.'&id_parent='.$id_parent.'&ct=5&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add custom content'),
            'class' => 'process-icon-new',
        );
		$helper->toolbar_btn['back'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&id_nrt_mega_menu='.$column->id_nrt_mega_menu.'&viewnrt_mega_menu'.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->l('Back')
		);
        $helper->tpl_vars['navigate'] = array(
            '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l("Home").'</a>',
            '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&id_nrt_mega_menu='.$leval0->id.'&viewnrt_mega_menu'.'">'.self::displayTitle($leval0->title, get_object_vars($leval0)).'</a>',
            ($column->title ? $column->title : $this->l('Column'))
        );

        $helper->title = $this->l('Blocks');
		$helper->table = 'nrt_mega_menu';
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
    }

    public function hookDisplayHeader()
    {	
		$this->context->controller->registerJavascript('js_nrtmegamenu', 'modules/'.$this->name.'/views/js/front.min.js', ['media' => 'all', 'priority' => 150]);
		
		$this->context->controller->registerStylesheet('css_nrtmegamenu', 'modules/'.$this->name.'/views/css/front.css', ['media' => 'all', 'priority' => 998]);	
		
		$this->context->controller->registerStylesheet('css_font_awesome', '/themes/_libraries/font-awesome/css/font-awesome.min.css',['media' => 'all', 'priority' => -1]);
		
    }
	
    public function generateCss()
    {
		$custom_css = '';
		$data = NrtMegaMenuClass::getCustomCss();
		if(is_array($data) && count($data))
			foreach($data as $v)
			{
				$id = $v['id_nrt_mega_menu'];
					
				if($v['txt_color']){
					$custom_css .= '.wrapper-menu-horizontal a.style_element_a_'.$id.',.wrapper-menu-vertical a.style_element_a_'.$id.',.wrapper-menu-column a.col_element_a_'.$id.',.wrapper-menu-mobile a.mo_element_a_'.$id.',.wrapper-menu-horizontal .nrt_mega_block_'.$id.',.wrapper-menu-vertical .nrt_mega_block_'.$id.',.wrapper-menu-column .nrt_col_mega_block_'.$id.',.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.',.wrapper-menu-horizontal .nrt_mega_block_'.$id.' a,.wrapper-menu-vertical .nrt_mega_block_'.$id.' a,.wrapper-menu-column .nrt_col_mega_block_'.$id.' a,.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.' a{color:'.$v['txt_color'].';}';
					
					$v['item_t']==1 && $custom_css .= '.wrapper-menu-horizontal .nrt_mega_block_'.$id.' .element_a_depth_1,.wrapper-menu-vertical .nrt_mega_block_'.$id.' .element_a_depth_1,.wrapper-menu-column .nrt_col_mega_block_'.$id.' .col_element_a_depth_1,.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.' .mo_element_a_depth_1{color:'.$v['txt_color'].';}';
				}
				if($v['link_color']){
					$custom_css .= '.wrapper-menu-horizontal .nrt_mega_block_'.$id.' a,.wrapper-menu-vertical .nrt_mega_block_'.$id.' a,.wrapper-menu-column .nrt_col_mega_block_'.$id.' a,.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.' a{color:'.$v['link_color'].';}';
				}
				if($v['txt_color_over']){
					$custom_css .= '.wrapper-menu-horizontal a.style_element_a_'.$id.':hover,.wrapper-menu-horizontal .nrt_mega_'.$id.'.current .element_a_depth_0,.wrapper-menu-vertical a.style_element_a_'.$id.':hover,.wrapper-menu-vertical .nrt_mega_'.$id.'.current .element_a_depth_0,.wrapper-menu-column a.col_element_a_'.$id.':hover,.wrapper-menu-column .nrt_col_mega_'.$id.'.current .col_element_a_depth_0,.wrapper-menu-mobile a.mo_element_a_'.$id.':hover,.wrapper-menu-mobile .nrt_mo_mega_'.$id.'.current .mo_element_a_depth_0,.wrapper-menu-horizontal .nrt_mega_block_'.$id.' a:hover,.wrapper-menu-vertical .nrt_mega_block_'.$id.' a:hover,.wrapper-menu-column .nrt_col_mega_block_'.$id.' a:hover,.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.' a:hover{color:'.$v['txt_color_over'].';}';
					
					$v['item_t']==1 && $custom_css .= '.wrapper-menu-horizontal .nrt_mega_block_'.$id.' .element_a_depth_1:hover,.wrapper-menu-vertical .nrt_mega_block_'.$id.' .element_a_depth_1:hover,.wrapper-menu-column .nrt_col_mega_block_'.$id.' .col_element_a_depth_1:hover,.wrapper-menu-mobile .nrt_mo_mega_block_'.$id.' .mo_element_a_depth_1:hover{color:'.$v['txt_color_over'].';}';
				}    

				if($v['bg_color']){
					$custom_css .= '.wrapper-menu-horizontal a.style_element_a_'.$id.',.wrapper-menu-vertical a.style_element_a_'.$id.',.wrapper-menu-column a.col_element_a_'.$id.',.wrapper-menu-mobile a.mo_element_a_'.$id.'{background-color:'.$v['bg_color'].';}';
				}
				if($v['bg_color_over']){
					$custom_css .= '.wrapper-menu-horizontal a.style_element_a_'.$id.':hover,.wrapper-menu-horizontal .nrt_mega_'.$id.'.current .element_a_depth_0,.wrapper-menu-vertical a.style_element_a_'.$id.':hover,.wrapper-menu-vertical .nrt_mega_'.$id.'.current .element_a_depth_0,.wrapper-menu-column a.col_element_a_'.$id.':hover,.wrapper-menu-column .nrt_col_mega_'.$id.'.current .col_element_a_depth_0,.wrapper-menu-mobile a.mo_element_a_'.$id.':hover,.wrapper-menu-mobile .nrt_mo_mega_'.$id.'.current .mo_element_a_depth_0{background-color:'.$v['bg_color_over'].';}';
				}
				if($v['tab_content_bg']){
					$custom_css .= '.wrapper-menu-horizontal .nrt_mega_'.$id.' .menu_sub,.wrapper-menu-horizontal .nrt_mega_'.$id.' .nrtmenu_multi_level ul,.wrapper-menu-horizontal .nrt_mega_'.$id.' .element_ul_depth_2 ul,.wrapper-menu-vertical .nrt_mega_'.$id.' .menu_sub,.wrapper-menu-vertical .nrt_mega_'.$id.' .nrtmenu_multi_level ul,.wrapper-menu-vertical .nrt_mega_'.$id.' .element_ul_depth_2 ul,.wrapper-menu-column .nrt_col_mega_'.$id.' > .col_sub_ul,.wrapper-menu-mobile .nrt_mo_mega_'.$id.' > .mo_sub_ul{background-color:'.$v['tab_content_bg'].';}';
				}
				if($v['bg_image'])
				{
					$bg_img = _THEME_PROD_PIC_DIR_.$this->name.'/'.$v['bg_image'];
					$bg_img = context::getContext()->link->protocol_content.Tools::getMediaServer($bg_img).$bg_img;
					$custom_css .= '.wrapper-menu-horizontal .nrt_mega_'.$id.' .menu_sub,.wrapper-menu-vertical .nrt_mega_'.$id.' .menu_sub,.wrapper-menu-column .nrt_col_mega_'.$id.' > .col_sub_ul,.wrapper-menu-mobile .nrt_mo_mega_'.$id.' > .mo_sub_ul{background-image:url('.$bg_img.');';
					switch($v['bg_repeat']) {
						case 1 :
							$repeat_option = 'repeat-x';
							break;
						case 2 :
							$repeat_option = 'repeat-y';
							break;
						case 3 :
							$repeat_option = 'no-repeat';
							break;
						default :
							$repeat_option = 'repeat';
					}
					$custom_css .= 'background-repeat:'.$repeat_option.';';
					switch($v['bg_position']) {
						case 1 :
							$position_option = 'left top';
							break;
						case 2 :
							$position_option = 'left center';
							break;
						case 3 :
							$position_option = 'left bottom';
							break;
						case 4 :
							$position_option = 'right top';
							break;
						case 5 :
							$position_option = 'right center';

							break;
						case 6 :
							$position_option = 'center top';
							break;
						case 7 :
							$position_option = 'center center';
							break;
						case 8 :
							$position_option = 'center bottom';
							break;
						default :
							$position_option = 'right bottom';
					}
					$custom_css .= 'background-position: '.$position_option.';}';
				}
				if($v['bg_margin_bottom']){
					$custom_css .= '.wrapper-menu-horizontal .nrt_mega_'.$id.' .menu_sub,.wrapper-menu-vertical .nrt_mega_'.$id.' .menu_sub{padding-bottom:'.($v['bg_margin_bottom']+20).'px;}';
				}

				if($v['cate_label_color']){
					$custom_css .= 'a.style_element_a_'.$id.' .cate_label,.mo_element_a_'.$id.' .cate_label,.col_element_a_'.$id.' .cate_label{color:'.$v['cate_label_color'].';}';
				}
				if($v['cate_label_bg']){
					$custom_css .= 'a.style_element_a_'.$id.' .cate_label,.mo_element_a_'.$id.' .cate_label,.col_element_a_'.$id.' .cate_label{background-color:'.$v['cate_label_bg'].';border-color:'.$v['cate_label_bg'].';}';  
				}
			}
			
		$cssFile = $this->local_path.'views/css/front.css';
		
		$handle = fopen($cssFile, 'w');
		
		if($custom_css != ''){
        	fwrite($handle, $custom_css);
			fclose($handle);
		}else{
			fclose($handle);
			if (file_exists($cssFile))
			{
				unlink($cssFile);
			}		
		}
        return true;	
    }

    public static function getTWidth($column)
    {
        $t_width = $temp = 0;

        foreach ($column as $key => $value) {
            if($temp+$value['width']<=12)
                $temp += $value['width'];
            else
                $temp = $value['width'];

            if($temp>$t_width)
                $t_width = $temp;
        }
        return $t_width;
    }
		
    public function _prepareHook()
    {
        $all = NrtMegaMenuClass::recurseTree(0,1,0,1,$this->context->language->id,0);
		
		$user_groups = Customer::getGroupsStatic($this->context->customer->id);
		
		$id_lang = (int)$this->context->language->id;
        
        if(is_array($all) && count($all))
        {
            foreach($all as &$v)
            {
                $columns = NrtMegaColumnClass::getAll($v['id_nrt_mega_menu'], 1);
                if($v['is_mega'])
                    $v['t_width'] = self::getTWidth($columns);

                if(!$this->getLink($v))
                    continue;

                foreach ($columns as $col) 
                {
                    $jon = NrtMegaMenuClass::getByColumnId($col['id_nrt_mega_column'], $this->context->language->id, 1, 0, 0);

                    foreach ($jon  as $k)
                    {
                        switch ($k['item_t']) {
                            case '1':
								$category = new Category($k['item_v'], $this->context->language->id);
                                if(Validate::isLoadedObject($category))
                                {
                                    $categories = [];
                                    $categories[] = $category->recurseLiteCategTree(0, 0, null, null, 'default');

                                    $imageFiles = scandir(_PS_CAT_IMG_DIR_);

                                    $sub_categories = $this->generateCategoriesMenu(
                                        $categories, 
                                        0,
                                        $k['sub_levels'], 
                                        $k['item_limit'], 
                                        $k['sub_limit'],
                                        $imageFiles
                                    );

                                    if ($k['title']){
                                        $sub_categories[0]['name'] = $k['title'];
                                    }
                                    
                                    if($k['subtype'] == 2)
                                    {
                                        //products
                                        $products_c = $category->getProducts($this->context->language->id, 0, $k['item_limit']);

                                        foreach ($products_c as &$product_c) {
                                            $product_c['link'] = $this->context->link->getProductLink((int) $product_c['id_product'], null, null, null, (int) $this->context->language->id);
                                        }

                                        $sub_categories[0]['children'] = $products_c;
                                    }

                                    $k['children'] = $sub_categories[0];
                                } else {
                                    $k = [];
                                }
								
                                break;
                            case '2':
                                $menuProducts = NrtMegaProductClass::getMenuProducts($this->context->language->id, $k['id_nrt_mega_menu']);
                                $listProducts = array();
                                if ($menuProducts)
                                {
                                    $assembler = new ProductAssembler($this->context);
                                    $presenterFactory = new ProductPresenterFactory($this->context);
                                    $presentationSettings = $presenterFactory->getPresentationSettings();
                                    $presenter = new ProductListingPresenter(
                                        new ImageRetriever(
                                            $this->context->link
                                        ),
                                        $this->context->link,
                                        new PriceFormatter(),
                                        new ProductColorsRetriever(),
                                        $this->context->getTranslator()
                                    );
                                    $products_for_template = array();
                                    if (is_array($menuProducts)) {
                                        foreach ($menuProducts as $product) {
                                            if (!(int)$product['id_product']) {
                                                continue;
                                            }
                                            $product['id_product_attribute'] = Product::getDefaultAttribute($product['id_product']);
                                            $products_for_template[] = $presenter->present(
                                                $presentationSettings,
                                                $assembler->assembleProduct($product),
                                                $this->context->language
                                            );
                                        }
                                    }
                                    $k['children'] = $products_for_template;
                                }
                                break;
                            case '3':
                                if ($k['item_k'] == 1)
                                {
                                    $menuBrands = Manufacturer::getManufacturers();
                                    if($menuBrands)
                                    {
                                        foreach ($menuBrands as $b_k => $b_v)
                                        {
                                            $menuBrands[$b_k]['url'] = Context::getContext()->link->getManufacturerLink($b_v['id_manufacturer']);
                                            $menuBrands[$b_k]['image'] = Context::getContext()->link->getManufacturerImageLink($b_v['id_manufacturer']);
                                        }
                                    }
                                }
                                else
                                    $menuBrands = NrtMegaBrandClass::getMenuBrands($this->context->language->id, $k['id_nrt_mega_menu']);
                                $k['children'] = $menuBrands;
                                break;
                            case '4':
                                $cs_children = array();
                                $li = NrtMegaMenuClass::recurseTree($k['id_nrt_mega_menu'],0,0,1,$this->context->language->id, 4);
                                if(is_array($li) && count($li))
                                {
                                    $this->getCustomLinkContent($li);
                                    $cs_children = array_merge($cs_children, $li);
                                }
                                $li = NrtMegaMenuClass::recurseTree($k['id_nrt_mega_menu'],1,0,1,$this->context->language->id, 5);
                                if(is_array($li) && count($li))
                                {
                                     $cs_children = array_merge($cs_children, $li);
                                }
                                if(count($cs_children))
                                    $k['children'] = $cs_children;
                                $k = $this->recurseLink($k);
                                break;
                            case '5':
                            default:
                                break;
                        }
                        if($k)
                            $col['children'][] = $k;
                    }
                    $v['column'][] = $col;
                }
            }
        }
        return $all;
    }

	public function generateCategoriesMenu($categories, $depth, $sub_levels, $item_limit, $sub_limit, $imageFiles)
    {
        $nodes = [];

		$i = 0;
			
        foreach ($categories as $key => $category) {
            if (($item_limit && $i >= $item_limit && $depth)) {
                continue;
            }

			$cat = new Category((int)$category['id']);
            if(Validate::isLoadedObject($cat))
            {
                // Check if customer is set and check access
                if (Validate::isLoadedObject($this->context->customer) && !$cat->checkAccess($this->context->customer->id)) {
                    continue;
                }
                
                $i++;
                
                $node = [];

                $node['id'] = (int)$category['id'];
                $node['link'] = $category['link'];
                $node['name']   = $category['name'];

				$image_url = '';

				if (count(preg_grep('/^' . $node['id'] . '_thumb.jpg/i', $imageFiles)) > 0) {
					foreach ($imageFiles as $file) {
						if (preg_match('/^' . $node['id'] . '_thumb.jpg/i', $file) === 1) {
							$image_url = $this->context->link->getMediaLink(_THEME_CAT_DIR_ . $file);
							break;
						}
					}
				}
                
                $node['cat_image_url'] = $image_url;

                if (isset($category['children']) && !empty($category['children']) && ( !$sub_levels || $depth < $sub_levels )) {
                    $node['children'] = $this->generateCategoriesMenu($category['children'], $depth + 1, $sub_levels, $item_limit, $sub_limit, $imageFiles);
                }

                $nodes[] = $node;
            }
        }

        return $nodes;
    }
	
    public function hookDisplayMenuHorizontal($param)
    {
	   	$this->templateFile = 'module:nrtmegamenu/views/templates/hook/horizontal-megamenu.tpl';
		
        if (!$this->isCached($this->templateFile, $this->mdGetCacheId('menu-horizontal')))
        {
            if (!isset(NrtMegaMenu::$cache_nrtmegamenu))
                NrtMegaMenu::$cache_nrtmegamenu = $this->_prepareHook();

            if (NrtMegaMenu::$cache_nrtmegamenu === false)
                NrtMegaMenu::$cache_nrtmegamenu = array();

            $menu = $vertical = array();

            foreach (NrtMegaMenu::$cache_nrtmegamenu as $v) {
                if(!$v['location'])
                    $menu[] =$v;
                elseif ($v['location']==2) {
                    $vertical[] =$v;
                }
            }

            $this->smarty->assign(array(
                'nrtmenu' => $menu,
                'nrtvertical' => $vertical,
                'menu_title' => false,
                'manufacturerSize' => ['width' => 'auto', 'height' => 'auto'],
            ));
        }
        return $this->fetch($this->templateFile, $this->mdGetCacheId('menu-horizontal'));
    }
	
    public function hookDisplayMenuVertical($param)
    {
		$this->templateFile = 'module:nrtmegamenu/views/templates/hook/vertical-megamenu.tpl';
		
        if (!$this->isCached($this->templateFile, $this->mdGetCacheId('menu-vertical')))
        {
            if (!isset(NrtMegaMenu::$cache_nrtmegamenu))
                NrtMegaMenu::$cache_nrtmegamenu = $this->_prepareHook();

            if (NrtMegaMenu::$cache_nrtmegamenu === false)
                NrtMegaMenu::$cache_nrtmegamenu = array();

            $menu = $vertical = array();

            foreach (NrtMegaMenu::$cache_nrtmegamenu as $v) {
                if(!$v['location'])
                    $menu[] =$v;
                elseif ($v['location']==2) {
                    $vertical[] =$v;
                }
            }

            $this->smarty->assign(array(
                'nrtmenu' => $menu,
                'nrtvertical' => $vertical,
                'menu_title' => false,
				'vetical_menu_limit' => Configuration::get('nrt_vetical_menu_limit'),
                'manufacturerSize' => ['width' => 'auto', 'height' => 'auto'],
            ));
        }
        return $this->fetch($this->templateFile, $this->mdGetCacheId('menu-vertical'));
    }
	
    public function hookDisplayLeftColumn($param)
    {
        $this->setLastVisitedCategory();
		
		$this->templateFile = 'module:nrtmegamenu/views/templates/hook/column-megamenu.tpl';
		
        if (!$this->isCached($this->templateFile, $this->mdGetCacheId('column')))
        {
            if (!isset(NrtMegaMenu::$cache_nrtmegamenu))
                NrtMegaMenu::$cache_nrtmegamenu = $this->_prepareHook();

            if (NrtMegaMenu::$cache_nrtmegamenu === false)
                NrtMegaMenu::$cache_nrtmegamenu = array();

            $menu = array();
            foreach (NrtMegaMenu::$cache_nrtmegamenu as $v) {
                if($v['location']==1)
                    $menu[] =$v;
            }

            $this->smarty->assign(array(
                'nrtmenu' => $menu,
                'menu_title' => false,
                'manufacturerSize' => ['width' => 'auto', 'height' => 'auto'],
            ));
        }
        return $this->fetch($this->templateFile, $this->mdGetCacheId('column'));
    }
    public function hookDisplayRightColumn($param)
    {
        return $this->hookDisplayLeftColumn($param);
    }
    public function hookDisplayLeftColumnProduct($param)
    {
        return $this->hookDisplayLeftColumn($param);
    }
    public function hookdisplayRightColumnProduct($param)
    {
        return $this->hookDisplayLeftColumn($param);
    }
    public function setLastVisitedCategory()
    {
        $cache_id = 'nrtmegamenu::setLastVisitedCategory';
        if (!Cache::isStored($cache_id))
        {
            if (method_exists($this->context->controller, 'getCategory') && ($category = $this->context->controller->getCategory()))
                $this->context->cookie->last_visited_category = $category->id;
            elseif (method_exists($this->context->controller, 'getProduct') && ($product = $this->context->controller->getProduct()))
                if (!isset($this->context->cookie->last_visited_category)
                    || !Product::idIsOnCategoryId($product->id, array(array('id_category' => $this->context->cookie->last_visited_category)))
                    || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop))
                        $this->context->cookie->last_visited_category = (int)$product->id_category_default;
            Cache::store($cache_id, $this->context->cookie->last_visited_category);
        }
        return Cache::retrieve($cache_id);
    }
    public function hookDisplayBeforeBodyClosingTag($param)
    {
		$this->templateFile = 'module:nrtmegamenu/views/templates/hook/mobile-megamenu.tpl';
		
        if (!$this->isCached($this->templateFile, $this->getCacheId()))
        {
            if (!isset(NrtMegaMenu::$cache_nrtmegamenu))
                NrtMegaMenu::$cache_nrtmegamenu = $this->_prepareHook();


            if (NrtMegaMenu::$cache_nrtmegamenu === false)
                NrtMegaMenu::$cache_nrtmegamenu = array();
            
            $this->smarty->assign(array(
                'nrtmenu' => NrtMegaMenu::$cache_nrtmegamenu,
                'menu_title' => false,
                'manufacturerSize' => ['width' => 'auto', 'height' => 'auto'],
            ));
        }
        return $this->fetch($this->templateFile, $this->getCacheId());
    }
    public function recurseLink($row)
    {
        if(!$this->getLink($row))
            return false;
        if(isset($row['children']) && is_array($row['children']) && count($row['children'])) 
            foreach($row['children'] as &$v)
            {
                $temp_v = $this->recurseLink($v);
                if(!$temp_v)
                    continue;
                $v = $temp_v;
            }
        return $row;
    }
    public function getLink(&$row)
	{
	    $context = Context::getContext();
		$user_groups = Customer::getGroupsStatic($this->context->customer->id);
        $id_lang = (int)$context->language->id;
		$id_shop = (int)Shop::getContextShopID();
        $link=$name=$icon=$title='';
        switch($row['item_k'])
        {
            case 0:
                $link = $row['link'] ? $row['link'] : '';
                $name = $row['title'];
            break;
            case 1:           
                $category = new Category((int)$row['item_v'],$id_lang);
                if(Validate::isLoadedObject($category))
                {
                    $is_intersected = array_intersect($category->getGroups(), $user_groups);
                    if(!empty($is_intersected))
                    {
                        if ($category->level_depth >= 1)
                			$link = $category->getLink();
                		else
                			$link = Context::getContext()->link->getPageLink('index');
                        $name = $category->name;
                    }
                }
            break;
            case 2:
                $product = new Product((int)$row['item_v'], true, (int)$id_lang);
                if (Validate::isLoadedObject($product))
                {
                    $link = $product->getLink();
                    $name = $product->name;
                }
            break;
            case 3:
                $cms = CMS::getLinks((int)$id_lang, array((int)$row['item_v']));
				if (count($cms))
                {
                    $link = $cms[0]['link'];
                    $name = $cms[0]['meta_title'];
                }
            break;
            case 4:
                $manufacturer = new Manufacturer((int)$row['item_v'], (int)$id_lang);
				if (Validate::isLoadedObject($manufacturer))
                {
                    if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
						$manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
					else
						$manufacturer->link_rewrite = 0;
					$theLink = new Link;
                    $link = $theLink->getManufacturerLink((int)$manufacturer->id, $manufacturer->link_rewrite);
					$name = $manufacturer->name;
                }
            break;
            case 5:
                $supplier = new Supplier((int)$row['item_v'], (int)$id_lang);
				if (Validate::isLoadedObject($supplier))
                {
                    $theLink = new Link;
					$link = $theLink->getSupplierLink((int)$supplier->id, $supplier->link_rewrite);
                    $name = $supplier->name;
                }
            break;
			case 6:
				$category = new CMSCategory((int)$row['item_v'], (int)$id_lang);
				if (Validate::isLoadedObject($category))
                {
                    $link = $category->getLink();
                    $name = $category->name;
                }
			break;
			case 7:
                $link = Context::getContext()->link->getPageLink('index');
				if($row['title']){
					$name = $row['title'];
				}else{
					$name = '';
					$icon = '<i class="icon-home las la-home"></i>';
				}
			break;
			case 8:
	            if(Module::isEnabled('smartblog'))
				{
					if((int)$row['item_v']){
						$category = new BlogCategory((int)$row['item_v'],$id_lang);
						if(Validate::isLoadedObject($category))
						{
							$link_category = array();
							$link_category['id_blog_category'] = $category->id_smart_blog_category;
							$link_category['rewrite'] = $category->link_rewrite;
							$name = $category->name;
							$link = smartblog::GetSmartBlogLink('smartblog_category_rule',$link_category);
						}
					}else{
						$module = new NrtMegaMenu();
						$name = $module->l('Blog');
						$link = smartblog::GetSmartBlogLink('smartblog');
					}
				}
			break;
			case 9:
                if(Module::isEnabled('smartblog'))
				{					
					$item_arr = explode('-', $row['item_v']);
					$post = new SmartBlogPost((int)$item_arr[1],$id_lang);
					if(Validate::isLoadedObject($post))
					{
						$link_post = array();
						$link_post['id_post'] = $post->id_smart_blog_post;
						$link_post['rewrite'] = $post->link_rewrite;
						$name = $post->meta_title;
						$link = smartblog::GetSmartBlogLink('smartblog_post_rule',$link_post);
					}	
				}
			break;
            case 10:
		        $theLink = new Link;
                
                $catalog_mod = (bool)Configuration::get('PS_CATALOG_MODE') || !(bool)Group::getCurrent()->show_prices;
                
    			$voucherAllowed = CartRule::isFeatureActive();
    			$returnAllowed = (int)(Configuration::get('PS_ORDER_RETURN'));
                
                $module = new NrtMegaMenu(); 
                $information = $module->getInformationLinks();
                $myAccount = $module->getMyAccountLinks();  
                
                if($row['item_v'] == 'prices-drop' && !$catalog_mod)
                    $link = $theLink->getPageLink($row['item_v']); 
                if($row['item_v'] == 'new-products')
                    $link = $theLink->getPageLink($row['item_v']);
                if($row['item_v'] == 'best-sales' && !$catalog_mod)
                    $link = $theLink->getPageLink($row['item_v']);
                if($row['item_v'] == 'stores')
                    $link = $theLink->getPageLink($row['item_v']);
                if($row['item_v'] == 'contact')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'sitemap')
                    $link = $theLink->getPageLink($row['item_v']);
                if($row['item_v'] == 'manufacturer')
                    $link = $theLink->getPageLink($row['item_v']);
                if($row['item_v'] == 'supplier')
                    $link = $theLink->getPageLink($row['item_v']);
                    
                if($row['item_v'] == 'my-account')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'history')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'order-follow' && $returnAllowed)
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'order-slip')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'addresses')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'identity')
                    $link = $theLink->getPageLink($row['item_v'], true);
                if($row['item_v'] == 'discount' && $voucherAllowed)
                    $link = $theLink->getPageLink($row['item_v'], true);
                
                if($link)
                {
                    if(array_key_exists($row['item_v'],$information))
                    {
                        $name = $information[$row['item_v']]['name'];
                        $title = $information[$row['item_v']]['title'];
                    }
                    if(array_key_exists($row['item_v'],$myAccount))
                    {
                        $name = $myAccount[$row['item_v']]['name'];
                        $title = $myAccount[$row['item_v']]['title'];
                    }
                }
            break;
        }

        $row['m_link'] = $link;
        $row['m_name'] = $row['title'] ? $row['title'] : $name;
        $row['m_icon'] = $icon;
        $row['m_title'] = $title ? $title : $name;
		
        return true;
	}
    
	public function hookActionCategoryAdd($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionCategoryDelete($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionCategoryUpdate($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionObjectProductDelete($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionProductAdd($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionProductUpdate($params)
	{
		$this->clearNrtMegamenuCache();
	}
	public function hookActionObjectCategoryUpdateAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectCategoryDeleteAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectCmsUpdateAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectCmsDeleteAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectSupplierUpdateAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectSupplierDeleteAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}	

	public function hookActionObjectManufacturerUpdateAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectManufacturerDeleteAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookActionObjectProductUpdateAfter($params)
	{
		$this->hookActionObjectProductDeleteAfter($params);
	}
	
	public function hookActionObjectProductDeleteAfter($params)
	{
		$this->clearNrtMegamenuCache();
	}
	
	public function hookCategoryUpdate($params)
	{
		$this->clearNrtMegamenuCache();
	}
    
    private function clearNrtMegamenuCache()
    {
		$this->_clearCache('*');
    }

	/**
	 * Return the list of fields value
	 *
	 * @param object $obj Object
	 * @return array
	 */
	public function getFieldsValueForm($obj,$fields_form="fields_form")
	{
		foreach ($this->$fields_form as $fieldset)
			if (isset($fieldset['form']['input']))
				foreach ($fieldset['form']['input'] as $input)
					if (!isset($this->fields_value[$input['name']]))
						if (isset($input['type']) && $input['type'] == 'shop')
						{
							if ($obj->id)
							{
								$result = Shop::getShopById((int)$obj->id, $this->identifier, $this->table);
								foreach ($result as $row)
									$this->fields_value['shop'][$row['id_'.$input['type']]][] = $row['id_shop'];
							}
						}
						elseif (isset($input['lang']) && $input['lang'])
							foreach (Language::getLanguages(false) as $language)
							{
								$fieldValue = $this->getFieldValueForm($obj, $input['name'], $language['id_lang']);
								if (empty($fieldValue))
								{
									if (isset($input['default_value']) && is_array($input['default_value']) && isset($input['default_value'][$language['id_lang']]))
										$fieldValue = $input['default_value'][$language['id_lang']];
									elseif (isset($input['default_value']))
										$fieldValue = $input['default_value'];
								}
								$this->fields_value[$input['name']][$language['id_lang']] = $fieldValue;
							}
						else
						{
							$fieldValue = $this->getFieldValueForm($obj, $input['name']);
							if ($fieldValue===false && isset($input['default_value']))
								$fieldValue = $input['default_value'];
							$this->fields_value[$input['name']] = $fieldValue;
						}

		return $this->fields_value;
	}
    
	/**
	 * Return field value if possible (both classical and multilingual fields)
	 *
	 * Case 1 : Return value if present in $_POST / $_GET
	 * Case 2 : Return object value
	 *
	 * @param object $obj Object
	 * @param string $key Field name
	 * @param integer $id_lang Language id (optional)
	 * @return string
	 */
	public function getFieldValueForm($obj, $key, $id_lang = null)
	{
		if ($id_lang)
			$default_value = ($obj->id && isset($obj->{$key}[$id_lang])) ? $obj->{$key}[$id_lang] : false;
		else
			$default_value = isset($obj->{$key}) ? $obj->{$key} : false;

		return Tools::getValue($key.($id_lang ? '_'.$id_lang : ''), $default_value);
	}
    
    public function BuildRadioUI($array, $name, $checked_value = 0)
    {
        $html = '';
        foreach($array AS $key => $value)
        {
            $html .= '<label><input type="radio"'.($checked_value==$value['value'] ? ' checked="checked"' : '').' value="'.$value['value'].'" id="'.(isset($value['id']) ? $value['id'] : $name.'_'.$value['value']).'" name="'.$name.'">'.(isset($value['label'])?$value['label']:'').'</label>';
            if (($key+1) % 8 == 0)
                $html .= '<br />';
        }
        return $html;
    }

    public function get_fontello()
    {
        $res= array(
            'classes' => array(),
        );

		return $res;
    }
    
    protected function UploadImage($item)
    {
        $result = array(
            'error' => array(),
            'image' => '',
            'thumb' => '',
        );
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name']))
		{
			$type = strtolower(substr(strrchr($_FILES[$item]['name'], '.'), 1));
			$imagesize = array();
			$imagesize = @getimagesize($_FILES[$item]['tmp_name']);
			if (!empty($imagesize) &&
				in_array(strtolower(substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg', 'gif', 'jpeg', 'png')) &&
				in_array($type, array('jpg', 'gif', 'jpeg', 'png')))
			{
				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
				$salt = sha1(microtime());
                $c_name = Tools::encrypt($_FILES[$item]['name'].$salt);
                $c_name_thumb = 'thumb'.$c_name;
				if ($upload_error = ImageManager::validateUpload($_FILES[$item]))
					$result['error'][] = $upload_error;
				elseif (!$temp_name || !move_uploaded_file($_FILES[$item]['tmp_name'], $temp_name))
					$result['error'][] = $this->displayError($this->l('An error occurred during move image.'));
				else{
				   $infos = getimagesize($temp_name);
                   $ratio_y = 72;
    			   $ratio_x = $infos[0] / ($infos[1] / $ratio_y);
                   if(!ImageManager::resize($temp_name, _PS_UPLOAD_DIR_.$this->name.'/'.$c_name.'.'.$type, null, null, $type) || !ImageManager::resize($temp_name, _PS_UPLOAD_DIR_.$this->name.'/'.$c_name_thumb.'.'.$type, $ratio_x, $ratio_y, $type))
				       $result['error'][] = $this->displayError($this->l('An error occurred during the image upload.'));
				} 
				if (isset($temp_name))
					@unlink($temp_name);
                    
                if(!count($result['error']))
                {
                    $result['image'] = $c_name.'.'.$type;
                }
                return $result;
			}
        }
        else
            return $result;
    }
        
    public function processCopyMegaMenu($id_nrt_mega_menu = 0)
    {
        if (!$id_nrt_mega_menu)
            return false;
            
        $root = new NrtMegaMenuClass($id_nrt_mega_menu);
        
        $id_shop = (int)Context::getContext()->shop->id;
        
        // Copy main menu
        $root2 = clone $root;
        $root2->id = 0;
        $root2->id_nrt_mega_menu = 0;
        $root2->id_shop = $id_shop;
        $ret = $root2->add();
        
        // Copy menu column
        foreach(Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'nrt_mega_column WHERE id_nrt_mega_menu='.(int)$id_nrt_mega_menu) AS $row)
        {
            $column = new NrtMegaColumnClass((int)$row['id_nrt_mega_column']);
            $column->id_nrt_mega_menu = (int)$root2->id;
            $column->id=0;
            $column->id_nrt_mega_column = 0;
            $ret &= $column->add();
            
            $ret &= $this->processCopySubMenus($row['id_nrt_mega_column'], $column->id, $id_shop);
        }
        return $ret;
    }
    
    public function processCopySubMenus($id_menu_column_old = 0, $id_menu_column_new = 0, $id_shop = 0, $id_parent_old = 0, $id_parent_new=0)
    {
        if (!$id_menu_column_old || !$id_menu_column_new)
        {
            $this->_html .= $this->displayError($this->l('Id menu column error:'));
            return false;   
        }        
    
        $ret = true;
        $old = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'nrt_mega_menu WHERE id_nrt_mega_column='.(int)$id_menu_column_old.' AND id_parent='.$id_parent_old.' ORDER BY id_parent ASC');
        foreach($old AS $row)
        {
            $menu = new NrtMegaMenuClass($row['id_nrt_mega_menu']);
                
            $menu->id_shop = $id_shop;
            $menu->id = 0;
            $menu->id_nrt_mega_menu = 0;
            $menu->id_nrt_mega_column = $id_menu_column_new;
            $menu->id_parent = $id_parent_new;
            $ret &= $menu->add();
            
            $ret &= $this->processCopyBrands($row['id_nrt_mega_menu'], $menu->id);
            $ret &= $this->processCopyProducts($row['id_nrt_mega_menu'], $menu->id);
            $child = Db::getInstance()->getValue('SELECT COUNT(0) FROM '._DB_PREFIX_.'nrt_mega_menu WHERE id_parent='.(int)$row['id_nrt_mega_menu'].' AND id_nrt_mega_column='.(int)$id_menu_column_old);
            if ($child > 0)
                $ret &= $this->processCopySubMenus($id_menu_column_old, $id_menu_column_new, $id_shop, $row['id_nrt_mega_menu'], $menu->id);
        }
        return $ret;
    }
    
    public function processCopyBrands($id_menu_old = 0, $id_mene_new = 0)
    {
        $ret = true;
        $old = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'nrt_mega_brand WHERE id_nrt_mega_menu='.(int)$id_menu_old);
        foreach($old AS $row)
            $ret &= Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'nrt_mega_brand values('.(int)$id_mene_new.', '.(int)$row['id_manufacturer'].')');
        return $ret;
    }
    
    public function processCopyProducts($id_menu_old = 0, $id_mene_new = 0)
    {
        $ret = true;
        $old = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'nrt_mega_product WHERE id_nrt_mega_menu='.(int)$id_menu_old);
        foreach($old AS $row)
            $ret &= Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'nrt_mega_product values('.(int)$id_mene_new.', '.(int)$row['id_product'].')');
        return $ret;
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
		$templateFile = 'module:' . $this->name . '/views/templates/hook/button-canvas.tpl';
		
        return $this->fetch($templateFile, $this->getCacheId());
    }
    
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
    }
    
    protected function mdGetCacheId($key,$name = null)
	{
		$cache_id = parent::getCacheId($name);
		return $cache_id.'_'.$key;
	}
}
