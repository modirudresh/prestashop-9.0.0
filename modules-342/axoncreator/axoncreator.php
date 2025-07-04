<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';
require_once AXON_CREATOR_PATH . 'includes/plugin.php';
require_once AXON_CREATOR_PATH . 'src/AxonCreatorPost.php';
require_once AXON_CREATOR_PATH . 'src/AxonCreatorRelated.php';
require_once AXON_CREATOR_PATH . 'src/AxonCreatorTemplate.php';
require_once AXON_CREATOR_PATH . 'src/AxonCreatorRevisions.php';

use AxonCreator\Wp_Helper;
use AxonCreator\Plugin;

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use Symfony\Component\HttpFoundation\Request;

use PrestaShop\PrestaShop\Adapter\NewProducts\NewProductsProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\PricesDrop\PricesDropProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\BestSales\BestSalesProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Manufacturer\ManufacturerProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

use PrestaShop\PrestaShop\Adapter\ObjectPresenter;

class AxonCreator extends Module implements WidgetInterface
{			
	private static $ax_hfh =   ['header' => null, 
                                'header_sticky' => null, 
                                'home' => null, 
                                'footer' => null, 
                                'hooks' => ['displayLeftColumn' => null, 
                                            'displayRightColumn' => null, 
                                            'displayProductAccessories' => null, 
                                            'displayProductSameCategory' => null, 
                                            'displayFooterProduct' => null, 
                                            'displayLeftColumnProduct' => null, 
                                            'displayRightColumnProduct' => null, 
                                            'displayContactPageBuilder' => null, 
                                            'displayShoppingCartFooter' => null, 
                                            'displayProductSummary' => null,
											'displayHeaderCategory' => null,
                                            'displayFooterCategory' => null,
                                            'display404PageBuilder' => null], 
                                'id_editor' => null ];
    protected $ax_templateFile;
	protected $css_js_ax_templateFile;
	
    private static $ax_overrided = [];
    
    public function __construct()
    {
        $this->name = 'axoncreator';
		$this->version = AXON_CREATOR_VERSION;
		$this->tab = 'front_office_features';
        $this->author = 'AxonVIZ';
		$this->bootstrap = true;
		$this->controllers = array('preview', 'ajax_editor', 'ajax', 'subscription', 'contact');
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('AxonCreator - Website Builder');
        $this->description = $this->l('Prestashop website builder, with no limits of design. AxonCreator Website Builder that delivers high-end page designs and advanced capabilities.');

        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->ax_templateFile = 'module:' . $this->name . '/views/templates/hook/page_content.tpl';
		$this->css_js_ax_templateFile = 'module:' . $this->name . '/views/templates/hook/css_js_unique.tpl';
    }

    public function install()
    {
        return parent::install()
			
            && $this->create_tab_Bo()
			&& $this->create_tab_Db()
			
			&& $this->registerHook('actionObjectBlogDeleteAfter')
			&& $this->registerHook('actionObjectCategoryDeleteAfter')
			&& $this->registerHook('actionObjectCmsDeleteAfter')
			&& $this->registerHook('actionObjectManufacturerDeleteAfter')
			&& $this->registerHook('actionObjectProductDeleteAfter')
			&& $this->registerHook('actionObjectSupplierDeleteAfter')
			&& $this->registerHook('display404PageBuilder')
			&& $this->registerHook('displayBackOfficeHeader')
			&& $this->registerHook('displayContactPageBuilder')
			&& $this->registerHook('displayFooterPageBuilder')
			&& $this->registerHook('displayFooterProduct')
			&& $this->registerHook('displayHome')
			&& $this->registerHook('displayIncludePageBuilder')
			&& $this->registerHook('displayProductSummary')
			&& $this->registerHook('displayHeaderCategory')
			&& $this->registerHook('displayFooterCategory')
			&& $this->registerHook('displayLeftColumn')
			&& $this->registerHook('displayLeftColumnProduct')
			&& $this->registerHook('displayHeaderNormal')
			&& $this->registerHook('displayHeaderSticky')
			&& $this->registerHook('displayProductAccessories')
			&& $this->registerHook('displayProductSameCategory')
			&& $this->registerHook('displayRightColumn')
			&& $this->registerHook('displayRightColumnProduct')
			&& $this->registerHook('displayShoppingCartFooter')
			&& $this->registerHook('displayHeader')
			
            && $this->registerHook('overrideLayoutTemplate')			
			&& $this->creatDemoData();
    }

    public function uninstall()
    {
        return parent::uninstall()
			
            && $this->delete_tab_Bo();
			//&& $this->delete_tab_Db();
    }

    public function create_tab_Bo()
    {
        $response = true;
		$langs = Language::getLanguages(false);

        $id_improve = Tab::getIdFromClassName('IMPROVE');
		
        // First check for parent tab
        $parentTabID = Tab::getIdFromClassName('AdminAxonCreatorFirst');
		
        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        } else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminAxonCreatorFirst";
            foreach($langs as $lang) {
            	$parentTab->name[$lang['id_lang']] = "Axon - Creator";
            }
            $parentTab->id_parent = $id_improve;
            $parentTab->module ='';
			$parentTab->icon = 'axon-logo';
            $response &= $parentTab->add();
        }
		
		if( !Tab::getIdFromClassName('AxonCreatorEditor') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AxonCreatorEditor";
			$tab->name = array();
			foreach($langs as $lang) {
				$tab->name[$lang['id_lang']] = "AxonCreatorEditor";
			}
			$tab->id_parent = -1;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorParent') ) {
			// Created tab
			$tab_3 = new Tab();
			$tab_3->active = 1;
			$tab_3->class_name = "AdminAxonCreatorParent";
			$tab_3->name = array();
			foreach (Language::getLanguages(true) as $lang) {
				$tab_3->name[$lang['id_lang']] = "- Add & Edit Content";
			}
			$tab_3->id_parent = $parentTab->id;
			$tab_3->module = '';
			$response &= $tab_3->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorHeader') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorHeader";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "Header";
			}
			$tab->id_parent = $tab_3->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorFooter') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorFooter";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "Footer";
			}
			$tab->id_parent = $tab_3->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorHome') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorHome";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "Home";
			}
			$tab->id_parent = $tab_3->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorHook') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorHook";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "Hook";
			}
			$tab->id_parent = $tab_3->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorParent2') ) {
			// Created tab
			$tab_4 = new Tab();
			$tab_4->active = 1;
			$tab_4->class_name = "AdminAxonCreatorParent2";
			$tab_4->name = array();
			foreach (Language::getLanguages(true) as $lang) {
				$tab_4->name[$lang['id_lang']] = "- Settings & License";
			}
			$tab_4->id_parent = $parentTab->id;
			$tab_4->module = '';
			$response &= $tab_4->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorSettings') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorSettings";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "General";
			}
			$tab->id_parent = $tab_4->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
		
		if( !Tab::getIdFromClassName('AdminAxonCreatorLicense') ) {
			// Created tab
			$tab = new Tab();
			$tab->active = 1;
			$tab->class_name = "AdminAxonCreatorLicense";
			$tab->name = array();
			foreach (Language::getLanguages() as $lang) {
				$tab->name[$lang['id_lang']] = "License";
			}
			$tab->id_parent = $tab_4->id;
			$tab->module = $this->name;
			$response &= $tab->add();
		}
				
        return $response;
    }

    public function delete_tab_Bo()
    {			
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorHeader');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorFooter');
        $tab = new Tab($id_tab);
        $tab->delete();
	
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorHome');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorHook');
        $tab = new Tab($id_tab);
        $tab->delete();
				
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorParent');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AxonCreatorEditor');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorLicense');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorSettings');
        $tab = new Tab($id_tab);
        $tab->delete();
		
        $id_tab = (int)Tab::getIdFromClassName('AdminAxonCreatorParent2');
        $tab = new Tab($id_tab);
        $tab->delete();		

        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTabID = Tab::getIdFromClassName('AdminAxonCreatorFirst');
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0) {
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }
	
	public function creatDemoData()
    {
		$response = true;

		if(Configuration::get('nrt_axoncreator_install')){
			return $response;
		}

		if( Module::isEnabled('nrtelementor') ){
			$header_b = Configuration::get('active_header_layout');
			$header_s_b = Configuration::get('active_header_sticky_layout');
			$header_home_b = Configuration::get('active_home_layout');
			$header_footer_b = Configuration::get('active_footer_layout');
			
			if( Configuration::get('opThemect') ){
				$opThemect = Configuration::get('opThemect');
				$opThemect = json_decode($opThemect, true);
			}else{
				$opThemect = [];
			}

			$posts = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'nrt_elementor_post');
			
			foreach ($posts as $post) {
				$post_langs = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'nrt_elementor_post p 
				INNER JOIN ' . _DB_PREFIX_ . 'nrt_elementor_post_lang pl ON p.id_nrt_elementor_post = pl.id_nrt_elementor_post 
				WHERE p.id_nrt_elementor_post = ' . (int) $post['id_nrt_elementor_post']);
				
				$obj = new AxonCreatorPost();

				$obj->id_employee = Wp_Helper::get_current_user_id();	
				$obj->title = $post['name'];
				$obj->post_type = $post['post_type'];	
				
				foreach ($post_langs as $post_lang) {
					$content = $post_lang['content'];
					
					$content = json_encode( self::axps_migration_content( $content ) );
					$obj->content[$post_lang['id_lang']] = $content;
					$obj->content_autosave[$post_lang['id_lang']] = $content;
				}

				$response &= $obj->add();
				
				foreach ($post_langs as $post_lang) {					
					Wp_Helper::delete_post_meta( (int) $obj->id, '_elementor_css_id_lang_' . $post_lang['id_lang'] );
				}
				
				if( $header_b == $post['id_nrt_elementor_post'] ){
					Configuration::updateValue('active_header_layout', (int) $obj->id);
				}
				
				if( $header_s_b == $post['id_nrt_elementor_post'] ){
					Configuration::updateValue('active_header_sticky_layout', (int) $obj->id);
				}
				
				if( $header_home_b == $post['id_nrt_elementor_post'] ){
					Configuration::updateValue('active_home_layout', (int) $obj->id);
				}
				
				if( $header_footer_b == $post['id_nrt_elementor_post'] ){
					Configuration::updateValue('active_footer_layout', (int) $obj->id);
				}
				
				if( Configuration::get('header_layout_on_index') == $post['id_nrt_elementor_post'] ){
					$opThemect['index_header_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('header_sticky_layout_on_index') == $post['id_nrt_elementor_post'] ){
					$opThemect['index_header_sticky_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('footer_layout_on_index') == $post['id_nrt_elementor_post'] ){
					$opThemect['index_footer_layout'] =(int) $obj->id;
				}
				//////////////
				if( Configuration::get('header_layout_on_contact') == $post['id_nrt_elementor_post'] ){
					$opThemect['contact_header_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('header_sticky_layout_on_contact') == $post['id_nrt_elementor_post'] ){
					$opThemect['contact_header_sticky_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('footer_layout_on_contact') == $post['id_nrt_elementor_post'] ){
					$opThemect['contact_footer_layout'] =(int) $obj->id;
				}
				//////////////
				if( Configuration::get('header_layout_on_category') == $post['id_nrt_elementor_post'] ){
					$opThemect['category_header_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('header_sticky_layout_on_category') == $post['id_nrt_elementor_post'] ){
					$opThemect['category_header_sticky_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('footer_layout_on_category') == $post['id_nrt_elementor_post'] ){
					$opThemect['category_footer_layout'] =(int) $obj->id;
				}
				//////////////
				if( Configuration::get('header_layout_on_product') == $post['id_nrt_elementor_post'] ){
					$opThemect['product_header_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('header_sticky_layout_on_product') == $post['id_nrt_elementor_post'] ){
					$opThemect['product_header_sticky_layout'] =(int) $obj->id;
				}
				
				if( Configuration::get('footer_layout_on_product') == $post['id_nrt_elementor_post'] ){
					$opThemect['product_footer_layout'] =(int) $obj->id;
				}
				
				$cmss = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'nrt_themect_page_config WHERE `page_type` = \'cms\'');
				
				foreach ($cmss as $cms) {
					$config = json_decode($cms['config'], true);
					if( isset( $config['header_layout'] ) && $config['header_layout'] == $post['id_nrt_elementor_post'] ){
						$config['header_layout'] = (int) $obj->id;
					}
					if( isset( $config['header_sticky_layout'] ) && $config['header_sticky_layout'] == $post['id_nrt_elementor_post'] ){
						$config['header_sticky_layout'] = (int) $obj->id;
					}
					if( isset( $config['footer_layout'] ) && $config['footer_layout'] == $post['id_nrt_elementor_post'] ){
						$config['footer_layout'] = (int) $obj->id;
					}
					
					$response &= Db::getInstance()->execute('
						INSERT INTO `' . _DB_PREFIX_ . 'nrt_themect_page_config` (`page_id`, `page_type`, `config`) 
						VALUES(' . (int)$config['page_id'] . ', \'' . $config['page_type']  . '\', \'' . json_encode($config) . '\') ON DUPLICATE KEY UPDATE config = VALUES(config)'
					);
				}
				
				if( $post['post_type'] != 'header' && $post['post_type'] != 'footer' && $post['post_type'] != 'home' && $post['post_type'] != 'block' ){
					if( $post['post_type'] == 'hook' ){
						$hook = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'nrt_page_hook h 
						INNER JOIN ' . _DB_PREFIX_ . 'nrt_page_hook_shop hs ON h.id_nrt_page_hook = hs.id_nrt_page_hook 
						WHERE h.id_elementor = ' . (int) $post['id_nrt_elementor_post'] );
												
						$response &= Db::getInstance()->execute('
							INSERT INTO `' . _DB_PREFIX_ . 'axon_creator_related` (`id_post`, `post_type`, `key_related`) 
							VALUES(' . (int) $obj->id . ', \'' . $post['post_type'] . '\', \'' . $hook['hook_name'] . '\') ON DUPLICATE KEY UPDATE post_type = VALUES(post_type)'
						);
						
						$id_hook = (int) Db::getInstance()->Insert_ID();
						
						$response &= Db::getInstance()->execute('
							INSERT INTO `' . _DB_PREFIX_ . 'axon_creator_related_shop` (`id_axon_creator_related`, `id_shop`) 
							VALUES(' . (int) $id_hook . ', ' . (int) $hook['id_shop'] . ') ON DUPLICATE KEY UPDATE id_shop = VALUES(id_shop)'
						);
					}else{
						$related = new AxonCreatorRelated();
						$related->key_related = $post['id_page'];	
						$related->post_type = $post['post_type'];	
						$related->id_post = (int) $obj->id;
						$response &= $related->add();	
					}
				}
				
			}
			
			$templates_data = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'nrt_elementor_template');

			foreach ($templates_data as $template_data) {

				$content = $template_data['content'];

				$content = json_encode( self::axps_migration_content( $content ) );

				$template = new \AxonCreatorTemplate();

				$template->id_employee = Wp_Helper::get_current_user_id();
				$template->title = $template_data['name'];
				$template->type = 'page';
				$template->thumbnail = false;
				$template->content = $content;
				$template->page_settings = '[]';
				$response &= $template->add();
			}
			
			$response &= Configuration::updateValue('opThemect', json_encode($opThemect));
			
			if( $response ){
				$module = \Module::getInstanceByName('nrtelementor');
				$module->disable();
			}

		}else{
			$languages = Language::getLanguages(false);
				
			$path = $this->getLocalPath().'install/';

			for ($x = 1; $x <= 18; $x++) {
				if(file_exists($path.$x.'.json')){
					if( $x == 14 ) { continue; }
					if( $x == 16 ) { continue; }
					if( $x == 18 ) { continue; }

					$file = $path.$x.'.json';

					////Header 
					if( $x == 2 ) { $name = 'Header 1'; 					$post_type = 'header'; }
					if( $x == 4 ) { $name = 'Header sticky 1'; 				$post_type = 'header'; }
					if( $x == 11 ){ $name = 'Header 2'; 					$post_type = 'header'; }
					if( $x == 12 ){ $name = 'Header 3'; 					$post_type = 'header'; }
					if( $x == 13 ){ $name = 'Header 4'; 					$post_type = 'header'; }
					////Footer 
					if( $x == 3 ){ $name = 'Footer 1'; 						$post_type = 'footer'; }
					////Home 
					if( $x == 1 ) { $name = 'Home 1'; 						$post_type = 'home'; }
					if( $x == 15 ){ $name = 'Home 2'; 						$post_type = 'home'; }
					////Hook 
					if( $x == 5 ) { $name = 'displayProductAccessories'; 	$post_type = 'hook'; }
					if( $x == 6 ) { $name = 'displayProductSameCategory'; 	$post_type = 'hook'; }
					if( $x == 7 ) { $name = 'displayRightColumnProduct'; 	$post_type = 'hook'; }
					if( $x == 8 ) { $name = 'displayLeftColumnProduct'; 	$post_type = 'hook'; }
					if( $x == 9 ) { $name = 'displayLeftColumn'; 			$post_type = 'hook'; }
					if( $x == 10 ){ $name = 'displayRightColumn'; 			$post_type = 'hook'; }
					if( $x == 17 ){ $name = 'displayContactPageBuilder'; 	$post_type = 'hook'; }

					//////////////////

					$content = file_get_contents($file, true);

					$obj = new AxonCreatorPost();
										
					$content = json_encode( self::axps_migration_content( $content ) );

					$obj->id_employee = Wp_Helper::get_current_user_id();	
					$obj->title = $name;
					$obj->post_type = $post_type;	
					foreach ($languages as $lang) {
						$obj->content[$lang['id_lang']] = $content;
						$obj->content_autosave[$lang['id_lang']] = $content;
					}
					$response &= $obj->add();

					if( $post_type == 'hook' ){ 
						$related = new AxonCreatorRelated();
						$related->key_related = $name;	
						$related->post_type = $post_type;	
						$related->id_post = $obj->id;
						$response &= $related->add();				  
					}
				}
			}

		}

		$response &= Configuration::updateValue('nrt_axoncreator_install', true);
		
		return $response;
    }
	
	public static function axps_migration_content( $content ) {
		$content = str_replace('nrtelementor', 'axoncreator', $content);

		$content = str_replace( __PS_BASE_URI__ . 'modules/axoncreator/views/img/', 'modules/axoncreator/assets/images/', $content );
		$content = str_replace( trim( json_encode( __PS_BASE_URI__ . 'modules/axoncreator/views/img/' ), '"' ), trim( json_encode( 'modules/axoncreator/assets/images/' ), '"' ), $content );

		$content = str_replace( __PS_BASE_URI__ . 'img/cms/', 'img/cms/', $content );
		$content = str_replace( trim( json_encode( __PS_BASE_URI__ . 'img/cms/' ), '"' ), trim( json_encode( 'img/cms/' ), '"' ), $content );

		$content = str_replace( __PS_BASE_URI__ . 'modules/axoncreator/', 'modules/axoncreator/', $content );
		$content = str_replace( trim( json_encode( __PS_BASE_URI__ . 'modules/axoncreator/' ), '"' ), trim( json_encode( 'modules/axoncreator/' ), '"' ), $content );

		$content = str_replace( 'modules/axoncreator/assets/images/', __PS_BASE_URI__ . 'modules/axoncreator/assets/images/', $content );
		$content = str_replace( trim( json_encode( 'modules/axoncreator/assets/images/' ), '"' ), trim( json_encode( __PS_BASE_URI__ . 'modules/axoncreator/assets/images/' ), '"' ), $content );

		$content = str_replace( 'img/cms/', __PS_BASE_URI__ . 'img/cms/', $content );
		$content = str_replace( trim( json_encode( 'img/cms/' ), '"' ), trim( json_encode( __PS_BASE_URI__ . 'img/cms/' ), '"' ), $content );

		$content = str_replace( 'modules/axoncreator/', __PS_BASE_URI__ . 'modules/axoncreator/', $content );
		$content = str_replace( trim( json_encode( 'modules/axoncreator/' ), '"' ), trim( json_encode( __PS_BASE_URI__ . 'modules/axoncreator/' ), '"' ), $content );

		$content = str_replace( '//', '/', $content );
		$content = str_replace( trim( json_encode( '//' ), '"' ), trim( json_encode( '/' ), '"' ), $content );
		
		$content = json_decode( $content, true );
		
		if( !$content ){
			$content = [];
		}
		
		$content = Plugin::instance()->db->iterate_data( $content, function( $element ) {					
			if( isset($element['widgetType']) ){
				if( $element['widgetType'] == 'axps-button-link-account' ){ $element['widgetType'] = 'axps-my-account'; }
				if( $element['widgetType'] == 'axps-button-link-cart' ){ $element['widgetType'] = 'axps-my-cart'; }
				if( $element['widgetType'] == 'axps-button-link-compare' ){ $element['widgetType'] = 'axps-my-compare'; }
				if( $element['widgetType'] == 'axps-button-link-wishlist' ){ $element['widgetType'] = 'axps-my-wishlist'; }
				if( $element['widgetType'] == 'axps-call-to-action' ){ $element['widgetType'] = 'call-to-action'; }
				if( $element['widgetType'] == 'axps-countdown' ){ $element['widgetType'] = 'countdown'; }
				if( $element['widgetType'] == 'axps-custom-hook' ){ 
					$element['widgetType'] = 'shortcode';
					if ( isset( $element['settings'][ 'custom_hook' ] ) ) {
						$element['settings'][ 'shortcode' ] = $element['settings'][ 'custom_hook' ];
						unset( $element['settings'][ 'custom_hook' ] );
					}
				}
				if( $element['widgetType'] == 'axps-dropdown-currency' ){ $element['widgetType'] = 'axps-currencies'; }
				if( $element['widgetType'] == 'axps-dropdown-language' ){ $element['widgetType'] = 'axps-languages'; }
				if( $element['widgetType'] == 'axps-email' ){ $element['widgetType'] = 'axps-subscription'; }
				if( $element['widgetType'] == 'axps-image-carousel' ){ 
					$element['widgetType'] = 'axps-image'; 
					if ( isset( $element['settings'][ 'carousel' ] ) ) {
						$element['settings'][ 'items' ] = $element['settings'][ 'carousel' ];
						unset( $element['settings'][ 'carousel' ] );
					}
				}
				if( $element['widgetType'] == 'axps-price-list' ){ $element['widgetType'] = 'price-list'; }
				if( $element['widgetType'] == 'axps-testimonial-carousel' ){ 
					$element['widgetType'] = 'axps-testimonial'; 
					if ( isset( $element['settings'][ 'carousel' ] ) ) {
						$element['settings'][ 'items' ] = $element['settings'][ 'carousel' ];
						unset( $element['settings'][ 'carousel' ] );
					}
				}

				////////////////////////////////

				if( $element['widgetType'] == 'call-to-action' ){
					if(isset($element['settings']['icon'])){
						$element['settings'] = self::axps_on_import_migration( $element['settings'], 'icon', 'selected_icon', true );
					}
					if(isset($element['settings']['button_icon'])){
						$element['settings'] = self::axps_on_import_migration( $element['settings'], 'icon', 'selected_button_icon', true );
					}
				}

				if( $element['widgetType'] == 'flip-box' ){
					if(isset($element['settings']['icon'])){
						$element['settings'] = self::axps_on_import_migration( $element['settings'], 'icon', 'selected_icon', true );
					}
				}

				if( $element['widgetType'] == 'button' ){
					if(isset($element['settings']['icon'])){
						$element['settings'] = self::axps_on_import_migration( $element['settings'], 'icon', 'selected_icon', true );
					}
				}

				if( $element['widgetType'] == 'icon-box' ){
					if(isset($element['settings']['icon'])){
						$element['settings'] = self::axps_on_import_migration( $element['settings'], 'icon', 'selected_icon', true );
					}
				}

				if( $element['widgetType'] == 'icon-list' ){
					if(isset($element['settings']['icon_list']) && is_array($element['settings']['icon_list'])){
						foreach ($element['settings']['icon_list'] as &$icon) {
							$icon = self::axps_on_import_migration( $icon, 'icon', 'selected_icon', true );
						}
					}
				}

				if( $element['widgetType'] == 'social-icons' ){
					if(isset($element['settings']['social_icon_list']) && is_array($element['settings']['social_icon_list'])){
						foreach ($element['settings']['social_icon_list'] as &$icon) {
							$icon = self::axps_on_import_migration( $icon, 'social', 'social_icon', true );
						}
					}
				}
			}

			return $element;
		} );
		
		return $content;
	}
	
	public static function axps_on_import_migration( array $element, $old_control = '', $new_control = '', $remove_old = false ) {

		if ( ! isset( $element[ $old_control ] ) ) {
			return $element;
		}

		// Case when old value is saved as empty string
		$new_value = [
			'value' => '',
			'library' => '',
		];

		// Case when old value needs migration
		if ( ! empty( $element[ $old_control ] ) ) {
			$new_value = AxonCreator\Icons_Manager::fa4_to_fa5_value_migration( $element[ $old_control ] );
		}

		$element[ $new_control ] = $new_value;

		//remove old value
		if ( $remove_old ) {
			unset( $element[ $old_control ] );
		}

		return $element;
	}
	
    public function create_tab_Db()
    {
        $return = true;
        //$this->delete_tab_Db();
		
		//////////////////Post////////////////////
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_post` (
                `id_axon_creator_post` int(10) NOT NULL auto_increment,
                `id_employee` int(10) unsigned NOT NULL,
                `title` varchar(40) NOT NULL,
				`post_type` varchar(40) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_axon_creator_post`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_post_lang` (
                `id_axon_creator_post` int(10) NOT NULL,
                `id_lang` int(10) NOT NULL ,
                `content` longtext default NULL,
                `content_autosave` longtext default NULL,
                PRIMARY KEY (`id_axon_creator_post`, `id_lang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		//////////////////Hook////////////////////
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_related` (
                `id_axon_creator_related` int(10) NOT NULL auto_increment,
				`id_post` int(10) unsigned NOT NULL,
                `post_type` varchar(255) NOT NULL,
                `key_related` varchar(255) NOT NULL,
                PRIMARY KEY (`id_axon_creator_related`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');		
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_related_shop` (
                `id_axon_creator_related` int(10) NOT NULL,
                `id_shop` int(10) NOT NULL ,
                PRIMARY KEY (`id_axon_creator_related`, `id_shop`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		//////////////////Template////////////////////
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_template` (
                `id_axon_creator_template` int(10) NOT NULL auto_increment,
                `id_employee` int(10) unsigned NOT NULL,
                `title` varchar(40) NOT NULL,
				`type` varchar(40) NOT NULL,
                `content` longtext default NULL,
                `page_settings` longtext default NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_axon_creator_template`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		//////////////////Meta////////////////////
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_meta` (
                `id_axon_creator_meta` int(10) NOT NULL auto_increment,
                `id` int(10) unsigned NOT NULL,
                `name` varchar(255) DEFAULT NULL,
                `value` longtext,
                PRIMARY KEY (`id_axon_creator_meta`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		//////////////////Revisions////////////////////
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'axon_creator_revisions` (
                `id_axon_creator_revisions` int(10) NOT NULL auto_increment,
                `id_post` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `id_employee` int(10) unsigned NOT NULL,
                `content` longtext default NULL,
                `page_settings` longtext default NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_axon_creator_revisions`, `id_post`, `id_lang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		
        return $return;
    }

    public function delete_tab_Db()
    {
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_post`') && 
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_post_lang`') && 
				   				   
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_related`') && 
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_related_shop`') && 
				   				   
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_template`') &&
				   
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_meta`') &&
				   
			   Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'axon_creator_revisions`');
    }
	
    public function hookDisplayBackOfficeHeader($params)
    {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		$this->context->controller->addCSS( AXON_CREATOR_ASSETS_URL . 'css/axps-admin' . $suffix . '.css' );
				
		$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		
		$controller_name = $this->context->controller->controller_name;
		
		$controllers = [ 'AdminCategories', 'AdminProducts', 'AdminCmsContent', 'AdminManufacturers', 'AdminSuppliers', 'AdminBlogPost' ];
		
        if ( in_array( $controller_name, $controllers ) ) {
			global $kernel;

			$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();

			switch ( $controller_name ) {
				case 'AdminCategories':
					$id_page = (int) Tools::getValue('id_category');
					if( !$id_page ){
						if ( !isset( $request->attributes ) ) { return; }
						$id_page = (int) $request->attributes->get('categoryId');
					}
					$post_type = 'category';
				break;
				case 'AdminProducts':
					$id_page = (int) Tools::getValue('id_product');
					if( !$id_page ){
						if ( !isset( $request->attributes ) ) { return; }
						$id_page = (int) $request->attributes->get('id');
					}
					$post_type = 'product';
				break;
				case 'AdminCmsContent':
					$id_page = (int) Tools::getValue('id_cms');
					if( !$id_page ){
						if ( !isset( $request->attributes ) ) { return; }
						$id_page = (int) $request->attributes->get('cmsPageId');
					}
					$post_type = 'cms';
				break;
				case 'AdminManufacturers':
					$id_page = (int) Tools::getValue('id_manufacturer');
					if( !$id_page ){
						if ( !isset( $request->attributes ) ) { return; }
						$id_page = (int) $request->attributes->get('manufacturerId');
					}
					$post_type = 'manufacturer';
				break;
				case 'AdminSuppliers':
					$id_page = (int) Tools::getValue('id_supplier');
					if( !$id_page ){
						if ( !isset( $request->attributes ) ) { return; }
						$id_page = (int) $request->attributes->get('supplierId');
					}
					$post_type = 'supplier';
				break;
				case 'AdminBlogPost':
					$id_page = (int) Tools::getValue('id_smart_blog_post');
					$post_type = 'blog';
				break;	
			}

			if (!$id_page) {
				$this->context->smarty->assign(array(
					'urlPageBuilder' => ''
				));
			} else{
				$url = $this->context->link->getAdminLink('AxonCreatorEditor').'&post_type=' . $post_type . '&key_related=' . $id_page . '&id_lang=' . $id_lang;

				$this->context->smarty->assign(array(
					'urlPageBuilder' => $url
				));
			}

			return $this->fetch(_PS_MODULE_DIR_ .'/'. $this->name . '/views/templates/admin/backoffice_header.tpl');
		}
	}

    public function getContent()
    {
        Tools::redirectAdmin( $this->context->link->getAdminLink('AdminAxonCreatorHeader') );
    }
	
    public function hookDisplayHeader()
    {	
        if( Wp_Helper::is_preview_mode() ) {
            header_register_callback(function () {
                header_remove('X-Frame-Options');
                header_remove('X-Content-Type-Options');
                header_remove('X-Xss-Protection');
                header_remove('Content-Security-Policy');
            });  
        }
        
		Wp_Helper::reset_post_var();

		$cssAndJs = $this->getCssAndJs();
		
		$cssFiles = $cssAndJs['axps_styles'];
		
		$jsFiles = $cssAndJs['axps_javascripts'];
		
		foreach( $cssFiles as $css ){
		   $this->context->controller->registerStylesheet( $css['id'], $css['url'], [ 'media' => $css['media'], 'priority' => $css['priority'] ] );
		}

		foreach( $jsFiles as $js ){
			$this->context->controller->registerJavascript( $js['id'], $js['url'], [ 'position' => $js['position'], 'priority' => $js['priority'] ] );
		}
		
		$languages = [];
		$data_languages = $this->getListLanguages();
		
		$currencies = [];
		$data_currencies = $this->getListCurrencies();
		
		if( $data_languages ){
			foreach( $data_languages['languages'] as $language ){
				$languages[$language['id_lang']] = $this->context->link->getLanguageLink($language['id_lang']);
			}
			$languages['length'] = count( $data_languages['languages'] );
		}
		
		if( $data_currencies ){
			foreach( $data_currencies['currencies'] as $currency ){
				$currencies[$currency['id']] = $currency['url'];
			}
			$currencies['length'] = count( $data_currencies['currencies'] );
		}

		if (empty($this->context->cookie->contactFormToken) || empty($this->context->cookie->contactFormTokenTTL) || $this->context->cookie->contactFormTokenTTL < time()) {
			$this->context->cookie->contactFormToken = md5(uniqid());
			$this->context->cookie->contactFormTokenTTL = time() + 600;
		}
			
        Media::addJsDef(
			['opAxonCreator' => ['ajax' => $this->context->link->getModuleLink('axoncreator', 'ajax', [], null, null, null, true),
							     'contact' => $this->context->link->getModuleLink('axoncreator', 'contact', [], null, null, null, true),
								 'contact_token' => $this->context->cookie->contactFormToken,
							     'subscription' => $this->context->link->getModuleLink('axoncreator', 'subscription', [], null, null, null, true),
							     'languages' => $languages,
							     'currencies' => $currencies,
								 'axps_id_product' => (int)Tools::getValue('id_product'),
								 'axps_id_category' => (int)Tools::getValue('id_category'),
								 'axps_is_editor' => (Wp_Helper::is_preview_mode() || Dispatcher::getInstance()->getController() == 'ajax_editor' || (int)Tools::getValue( 'wp_preview' ))?1:0]
		]);	
					
	 	self::$ax_hfh['header'] = Wp_Helper::apply_filters( 'axoncreator_header_layout', (int) Configuration::get('active_header_layout') );
		self::$ax_hfh['header_sticky'] = Wp_Helper::apply_filters( 'axoncreator_header_sticky_layout', (int) Configuration::get('active_header_sticky_layout') );
		self::$ax_hfh['home'] = Wp_Helper::apply_filters( 'axoncreator_home_layout', (int) Configuration::get('active_home_layout') );
		self::$ax_hfh['footer'] = Wp_Helper::apply_filters( 'axoncreator_footer_layout', (int) Configuration::get('active_footer_layout') );
		
		$post_type = Tools::getValue( 'post_type' );
		
		if( (int)Tools::getValue( 'id_post' ) && Wp_Helper::is_preview_mode() && in_array( $post_type, array( 'header', 'home', 'footer', 'hook' ) ) ){
			$id_post = (int)Tools::getValue( 'id_post' );
			
			if( $post_type == 'header' ){
				self::$ax_hfh['header'] = $id_post;
				self::$ax_hfh['header_sticky'] = null;
			}
			
			if( $post_type == 'home' ){
				self::$ax_hfh['home'] = $id_post;
			}
			
			if( $post_type == 'footer' ){
				self::$ax_hfh['footer'] = $id_post;
			}
			
			self::$ax_hfh['id_editor'] = $id_post;
		}elseif( (int)Tools::getValue( 'wp_preview' ) ){
			$post = new AxonCreatorPost( (int)Tools::getValue( 'wp_preview' ), Wp_Helper::$id_lang );
			
			$id_post = (int) $post->id;
			
			$post_type = $post->post_type;
			
			if( $post_type == 'header' ){
				self::$ax_hfh['header'] = $id_post;
				self::$ax_hfh['header_sticky'] = null;
			}
			
			if( $post_type == 'home' ){
				self::$ax_hfh['home'] = $id_post;
			}
			
			if( $post_type == 'footer' ){
				self::$ax_hfh['footer'] = $id_post;
			}
		}
		
		foreach( self::$ax_hfh['hooks'] as $key => $value ){
			Wp_Helper::$post_type = 'hook';
			Wp_Helper::$key_related = $key;
			
			$related = Wp_Helper::getRelatedByKey();
            if($related){
                self::$ax_hfh['hooks'][$key] = (int) $related['id_post'];
            }
		}
			
		$cacheIdCssJs = 'pageBuilder|GlobalJs';
				
		if( !Wp_Helper::is_preview_mode() ) {
			$cacheIdCssJs = 'pageBuilder|GlobalCssJs';
		} 

		if (!$this->isCached($this->css_js_ax_templateFile, $this->getCacheId($cacheIdCssJs))){		
			$css_unique = '';
			
			if( !Wp_Helper::is_preview_mode() ) {
				$css_unique = Plugin::instance()->frontend->parse_global_css_code();
			}
			
			$js_unique = '
			<script type="text/javascript">
				var elementorFrontendConfig = ' . json_encode(Plugin::instance()->frontend->get_init_settings()) . ';
			</script>';

			$this->smarty->assign(['css_js_unique' => $css_unique . $js_unique]);
		}

		return $this->fetch($this->css_js_ax_templateFile, $this->getCacheId($cacheIdCssJs));
    }
		
    public function getCssAndJs()
	{
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		$axps_styles = [];
		
		$axps_styles[] = [
			'id' => 'css_axps_eicons', 
			'url' => 'modules/' . $this->name . '/assets/lib/eicons/css/elementor-icons.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_font_awesome', 
			'url' => 'modules/' . $this->name . '/assets/lib/font-awesome/css/font-awesome.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_fontawesome', 
			'url' => 'modules/' . $this->name . '/assets/lib/font-awesome/css/fontawesome.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_regular', 
			'url' => 'modules/' . $this->name . '/assets/lib/font-awesome/css/regular.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_solid', 
			'url' => 'modules/' . $this->name . '/assets/lib/font-awesome/css/solid.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_brands', 
			'url' => 'modules/' . $this->name . '/assets/lib/font-awesome/css/brands.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_line_awesome', 
			'url' => 'modules/' . $this->name . '/assets/lib/line-awesome/line-awesome.min.css', 
			'media' => 'all', 
			'priority' => -1
		];

		$axps_styles[] = [
			'id' => 'css_axps_pe_icon', 
			'url' => 'modules/' . $this->name . '/assets/lib/pe-icon/Pe-icon-7-stroke.min.css', 
			'media' => 'all', 
			'priority' => -1
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_animations', 
			'url' => 'modules/' . $this->name . '/assets/lib/animations/animations.min.css', 
			'media' => 'all', 
			'priority' => 150
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_flatpickr', 
			'url' => 'modules/' . $this->name . '/assets/lib/flatpickr/flatpickr.min.css', 
			'media' => 'all', 
			'priority' => 150
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_frontend', 
			'url' => 'modules/' . $this->name . '/assets/css/frontend' . $dir_rtl . '.min.css', 
			'media' => 'all', 
			'priority' => 150
		];

		$axps_styles[] = [
			'id' => 'css_axps_swiper', 
			'url' => 'modules/' . $this->name . '/assets/lib/swiper/swiper.css', 
			'media' => 'all', 
			'priority' => 150
		];
		
		$axps_styles[] = [
			'id' => 'css_axps_widgets', 
			'url' => 'modules/' . $this->name . '/assets/widgets/css/axps-widgets' . $dir_rtl . $suffix . '.css', 
			'media' => 'all', 
			'priority' => 150
		];
		
		if( Wp_Helper::is_preview_mode() ) {
			$axps_styles[] = [
				'id' => 'css_axps_e_select2', 
				'url' => 'modules/' . $this->name . '/assets/lib/e-select2/css/e-select2.min.css', 
				'media' => 'all', 
				'priority' => 150
			];

			$axps_styles[] = [
				'id' => 'css_axps_editor_preview', 
				'url' => 'modules/' . $this->name . '/assets/css/editor-preview' . $dir_rtl . '.min.css', 
				'media' => 'all', 
				'priority' => 150
			];

			$axps_styles[] = [
				'id' => 'css_axps_preview', 
				'url' => 'modules/' . $this->name . '/assets/css/axps-preview' . $suffix . '.css', 
				'media' => 'all', 
				'priority' => 150
			];
		}
		
		$axps_javascripts = [];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_frontend_modules', 
			'url' => 'modules/' . $this->name . '/assets/js/frontend-modules.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
				
		$axps_javascripts[] = [
			'id' => 'js_axps_waypoints', 
			'url' => 'modules/' . $this->name . '/assets/lib/waypoints/waypoints.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_flatpickr', 
			'url' => 'modules/' . $this->name . '/assets/lib/flatpickr/flatpickr.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_imagesloaded', 
			'url' => 'modules/' . $this->name . '/assets/lib/imagesloaded/imagesloaded.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_jquery_numerator', 
			'url' => 'modules/' . $this->name . '/assets/lib/jquery-numerator/jquery-numerator.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_swiper', 
			'url' => 'modules/' . $this->name . '/assets/lib/swiper/swiper.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_dialog', 
			'url' => 'modules/' . $this->name . '/assets/lib/dialog/dialog.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_countdown', 
			'url' => 'modules/' . $this->name . '/assets/lib/countdown/countdown.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
				
		$axps_javascripts[] = [
			'id' => 'js_axps_widgets', 
			'url' => 'modules/' . $this->name . '/assets/widgets/js/axps-widgets' . $suffix . '.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		$axps_javascripts[] = [
			'id' => 'js_axps_frontend', 
			'url' => 'modules/' . $this->name . '/assets/js/frontend.min.js', 
			'position' => 'bottom', 
			'priority' => 51
		];
		
		if( Wp_Helper::is_preview_mode() ) {
			$axps_javascripts[] = [
				'id' => 'js_axps_inline_editor', 
				'url' => 'modules/' . $this->name . '/assets/lib/inline-editor/js/inline-editor.min.js', 
				'position' => 'bottom', 
				'priority' => 51
			];
		}
		
		return [ 'axps_styles' => $axps_styles, 'axps_javascripts' => $axps_javascripts ];
    }
		
    public function hookOverrideLayoutTemplate()
    {
		Wp_Helper::render_widget();

        Wp_Helper::reset_post_var();

        if ( !isset($this->context->smarty->tpl_vars['configuration']) || isset( self::$ax_overrided[ Wp_Helper::$id_post ] ) ) {
            return;
        }
        
        switch ( Wp_Helper::$post_type ) {
            case 'category':
				if(isset($this->context->smarty->tpl_vars['category'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;	

					$content = $this->context->smarty->tpl_vars['category'];
					$content_replace = &$this->context->smarty->tpl_vars['category'];
	
					$content->value['description'] .= $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
                break;
            case 'product':
				if(isset($this->context->smarty->tpl_vars['product'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

					$content = $this->context->smarty->tpl_vars['product'];
					$content_replace = &$this->context->smarty->tpl_vars['product'];
	
					$content->value['description'] .= $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
                break;
            case 'manufacturer':
				if(isset($this->context->smarty->tpl_vars['manufacturer'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

					$content = $this->context->smarty->tpl_vars['manufacturer'];
					$content_replace = &$this->context->smarty->tpl_vars['manufacturer'];
	
					$content->value['description'] .= $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
                break;
            case 'supplier':
				if(isset($this->context->smarty->tpl_vars['supplier'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

					$content = $this->context->smarty->tpl_vars['supplier'];
					$content_replace = &$this->context->smarty->tpl_vars['supplier'];
	
					$content->value['description'] .= $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
                break;
			case 'blog':
				if(isset($this->context->smarty->tpl_vars['post'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

					$content = $this->context->smarty->tpl_vars['post'];
					$content_replace = &$this->context->smarty->tpl_vars['post'];
					
					$content->value['content'] .= $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
				break;
			case 'cms':
				if(isset($this->context->smarty->tpl_vars['cms'])){
					self::$ax_overrided[ Wp_Helper::$id_post ] = true;
					$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

					$content = $this->context->smarty->tpl_vars['cms'];
					$content_replace = &$this->context->smarty->tpl_vars['cms'];
					
					$content->value['content'] = '<div class="container container-parent">' . $content->value['content'] . '</div>' . $this->_axRenderContent($cacheId);
					$content_replace = $content;
				}
				break;
        }
    }
									
    public function renderWidget($hookName = null, array $configuration = []) {	
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

		$id_lang = (int)$this->context->language->id;
		$id_shop = (int)$this->context->shop->id;

		Wp_Helper::$id_editor = self::$ax_hfh['id_editor'];
		
		if ( preg_match('/^displayProductSameCategory\d*$/', $hookName) ){
            if ( isset( $configuration['smarty']->tpl_vars['product']->value['id_product'] ) ) {    

				$id_product = (int) $configuration['smarty']->tpl_vars['product']->value['id_product'];

				$product =  new Product($id_product, true, $id_lang, $id_shop, $this->context);

				if (!Validate::isLoadedObject($product)) {
					return;
				}
				
				$category = new Category($product->id_category_default);

				$searchProvider = new CategoryProductSearchProvider($this->context->getTranslator(), $category);
	
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage(2)->setPage(1);
				$query->setIdCategory($category->id)->setSortOrder(
					new SortOrder('product', 'name', 'desc')
				);
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();

				if( count($products) < 2 && !( Wp_Helper::is_preview_mode() || Dispatcher::getInstance()->getController() == 'ajax_editor' || (int)Tools::getValue( 'wp_preview' ) ) ){
					return;
				}
            }
        } else if ( preg_match('/^displayProductAccessories\d*$/', $hookName) ){
			if( ( !isset($this->context->smarty->tpl_vars['accessories']->value) || !$this->context->smarty->tpl_vars['accessories']->value ) && 
                !( Wp_Helper::is_preview_mode() || Dispatcher::getInstance()->getController() == 'ajax_editor' || (int)Tools::getValue( 'wp_preview' ) ) ){
				return;
			}
        }

        if (preg_match('/^displayHeaderNormal\d*$/', $hookName)) {			
			if( !self::$ax_hfh['header'] ){ return; }

			Wp_Helper::$id_post = self::$ax_hfh['header'];

			$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

			return '<div id="header-normal">' . $this->_axRenderContent($cacheId) . '</div>';
        } else if (preg_match('/^displayHeaderSticky\d*$/', $hookName)) {			
			if( !self::$ax_hfh['header_sticky'] ){ return; }
			
			Wp_Helper::$id_post = self::$ax_hfh['header_sticky'];

			$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

			return '<div id="header-sticky" class="has-sticky">' . $this->_axRenderContent($cacheId) . '</div>';
        } else if (preg_match('/^displayHome\d*$/', $hookName)) {
			if( !self::$ax_hfh['home'] ){ return; }
			
			Wp_Helper::$id_post = self::$ax_hfh['home'];

			$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

			return $this->_axRenderContent($cacheId);
        } else if (preg_match('/^displayFooterPageBuilder\d*$/', $hookName)) {
			if( !self::$ax_hfh['footer'] ){ return; }
			
			Wp_Helper::$id_post = self::$ax_hfh['footer'];

			$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

			return $this->_axRenderContent($cacheId);
        } else {			
			if( !isset( self::$ax_hfh['hooks'][$hookName] ) || !self::$ax_hfh['hooks'][$hookName] ){ return; }
			
			Wp_Helper::$post_type = 'hook';
			Wp_Helper::$key_related = $hookName;
			Wp_Helper::$id_post = self::$ax_hfh['hooks'][$hookName];

			$cacheId = 'pageBuilder|' . Wp_Helper::$id_post;

			if ( preg_match('/^displayProductAccessories\d*$/', $hookName) || preg_match('/^displayProductSameCategory\d*$/', $hookName) || 
				preg_match('/^displayFooterProduct\d*$/', $hookName) || preg_match('/^displayLeftColumnProduct\d*$/', $hookName) || 
				preg_match('/^displayRightColumnProduct\d*$/', $hookName) || preg_match('/^displayProductSummary\d*$/', $hookName) 
			) {
				if ( isset( $configuration['smarty']->tpl_vars['product']->value['id_product'] ) ) {
					$this->context->smarty->tpl_vars['axps_id_product'] = $id_product = (int) $configuration['smarty']->tpl_vars['product']->value['id_product'];
				}
			} else if ( preg_match('/^displayHeaderCategory\d*$/', $hookName) || preg_match('/^displayFooterCategory\d*$/', $hookName) ) {
				if ( isset( $configuration['smarty']->tpl_vars['category']->value['id'] ) ){
					$this->context->smarty->tpl_vars['axps_id_category'] = $id_category = (int) $configuration['smarty']->tpl_vars['category']->value['id'];
		
					$cacheId .= '|category|' . $id_category;
				}
			}

			return $this->_axRenderContent($cacheId);
        }
    }

    public function _axRenderContent($cacheId = '')
    {	
        if( !Wp_Helper::$id_post ){ return ''; }
			
		if( Wp_Helper::$id_post == Wp_Helper::$id_editor ){		
			return $this->_axRenderEditor();
		}
		
        if (!$this->isCached($this->ax_templateFile, $this->getCacheId($cacheId))){			
			$content = '';
						
			$get_content = $this->getWidgetVariables();

			if( $get_content ){ $content .= $get_content; }
						
            $this->smarty->assign(['content' => $content]);
        }
										
		return $this->fetch($this->ax_templateFile, $this->getCacheId($cacheId));
    }

    public function _axRenderEditor()
    {	
        if( !Wp_Helper::$id_post || !Wp_Helper::$id_editor ){ return ''; }
			
		if( Wp_Helper::$id_post == Wp_Helper::$id_editor ){		
			$content = '';
						
			$get_content = $this->getWidgetVariables();

			if( $get_content ){ $content .= $get_content; }
						
			$this->smarty->assign(['content' => $content]);
										
			return $this->fetch($this->ax_templateFile);
		}
    }

    public function getWidgetVariables( $hookName = null, array $configuration = [] )
    {		
		if( !Wp_Helper::$id_post ){ return; }
		
		$with_css = Wp_Helper::$id_post != Wp_Helper::$id_editor;

		$content = '';

		if( Wp_Helper::$id_post && Validate::isLoadedObject( new AxonCreatorPost( Wp_Helper::$id_post, Wp_Helper::$id_lang ) ) ){	            
			$content .= Plugin::instance()->frontend->get_builder_content( Wp_Helper::$id_post, $with_css );
		}

		Wp_Helper::reset_post_var();
				
		return $content;
    }
	
    public function deleteRelated( $id, $type ) 
	{
		Wp_Helper::$id_shop = (int) $this->context->shop->id;
		Wp_Helper::$post_type = $type;
		Wp_Helper::$key_related = $id;
		
		$related = Wp_Helper::getRelatedByKey();
		
		if( $related ){
			$obj = new AxonCreatorRelated( $related['id_axon_creator_related'] );
			$obj->delete();
			
			$post = new AxonCreatorPost( $related['id_post'] );
			$post->delete();
		}
    }

    public function clearGlobalCssCache() 
	{
        $this->_clearCache($this->css_js_ax_templateFile);
    }
		
    public function clearElementorCache( $postId ) 
	{
        $this->_clearCache($this->ax_templateFile, $this->getCacheId('pageBuilder|' . $postId));
    }
	
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        if (!isset($params['object']->id)) {
            return;
        }
		
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'category' );
    }
	
    public function hookActionObjectProductDeleteAfter($params)
    {
        if (!isset($params['object']->id)) {
            return;
        }
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'product' );
    }
	
    public function hookActionObjectCmsDeleteAfter($params) 
	{
        if (!isset($params['object']->id)) {
            return;
        }
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'cms' );
    }
	
    public function hookActionObjectManufacturerDeleteAfter($params) 
	{
        if (!isset($params['object']->id)) {
            return;
        }
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'manufacturer' );
    }
	
    public function hookActionObjectSupplierDeleteAfter($params) 
	{
        if (!isset($params['object']->id)) {
            return;
        }
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'supplier' );
    }

    public function hookActionObjectBlogDeleteAfter($params) 
	{
        if (!isset($params['object']->id)) {
            return;
        }
		$id = (int)$params['object']->id;
		
		$this->deleteRelated( $id, 'blog' );
    }
	
    public function _prepBlogs($settings)
	{
		$content = array();
		
		$source = $settings['source'];
		$limit = (int)$settings['limit'] <= 0 ? 10 : (int)$settings['limit'];
		$image_size = $settings['image_size'];
		$order_by = $settings['order_by'];
		$order_way = $settings['order_way'];

		if($order_by == 'name'){
			$order_by = 'pl.meta_title';
		}elseif($order_by == 'date_add'){
			$order_by = 'p.created';
		}else{
			$order_by = 'p.id_smart_blog_post';
		}

		$content['blogs'] = $this->execBlogs($source, $limit, $image_size, $order_by, $order_way);
		
		$content['items_type_path'] = $this->_getBlogsPath($settings['items_type']);

		return $content;
	}
	
    public function execBlogs($source, $limit, $image_size, $order_by, $order_way)
	{	
		$blogs = array();
		
		if($source == 'n'){
			$blogs = SmartBlogPost::GetPostLatestHome($limit, $image_size, null, $order_by, $order_way);
		}else{
			$blogs = SmartBlogPost::GetPostByCategory($source, $limit, $image_size, null, $order_by, $order_way);
		}
		
		return $blogs;	
	}
	
    public function _getBlogsPath($items_type)
	{		
		$items_type_path = [];

		for( $i = 1; $i <= 30; $i++ ){
			$items_type_path[$i] = 'catalog/_partials/miniatures/_partials/_blog/blog-' . $i . '.tpl';
		}

		$items_type_path = Wp_Helper::apply_filters( 'axoncreator_blogs_type_path', $items_type_path );	
		
		return $items_type_path[$items_type];
	}
	
    public function _getProductsPath($items_type)
	{		
		$items_type_path = [];

		for( $i = 1; $i <= 30; $i++ ){
			$items_type_path[$i] = 'catalog/_partials/miniatures/_partials/_product/product-' . $i . '.tpl';
		}

		$items_type_path = Wp_Helper::apply_filters( 'axoncreator_products_type_path', $items_type_path );	
		
		return $items_type_path[$items_type];
	}
	
    public function _prepProductsSelected($settings)
	{	
		$content = array();
		$data = array();
		
		$content['products'] = $this->execProducts('s', $settings, 0, null, null, 1);	
		$content['lastPage'] = true;
		
		$content['items_type_path'] = $this->_getProductsPath($settings['items_type']);
		
		return $content;
	}
		
    public function _prepProducts($settings)
	{	
		$content = array();
		
		$source = $settings['source'];
		$limit = (int)$settings['limit'] <= 0 ? 10 : (int)$settings['limit'];
		$order_by = $settings['order_by'];
		$order_way = $settings['order_way'];
		
		if($source == 'c'){
			$source = $settings['category'];
			if ($settings['randomize']) {
				$order_by = 'rand';
			}
		}
				
		$page = $settings['paged'];
				
		$content['products'] = $this->execProducts($source,  $settings, $limit, $order_by, $order_way, $page);
		
		$content['lastPage'] = true;
		
		if( $page > 1 ){
			$content['lastPage'] = !(bool)$this->execProducts($source,  $settings, $limit, $order_by, $order_way, $page + 1);
		}
		
		$content['items_type_path'] = $this->_getProductsPath($settings['items_type']);
		
		return $content;
	}
	
    public function execProducts($source, $settings, $limit, $order_by, $order_way, $page = 1)
	{	
		$id_lang = (int)$this->context->language->id;
		$id_shop = (int)$this->context->shop->id;

		$exclude_id_product = 0;

		$products = [];
				
        switch ($source) {
            case 'n':
				$searchProvider = new NewProductsProductSearchProvider($this->context->getTranslator());
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
				$query->setQueryType('new-products')->setSortOrder(new SortOrder('product', $order_by, $order_way));
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();	
				
                break;
            case 'p':
				$searchProvider = new PricesDropProductSearchProvider($this->context->getTranslator());
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
				$query->setQueryType('prices-drop')->setSortOrder(new SortOrder('product', $order_by, $order_way));
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();		
				
                break;
            case 'm':			
				$manufacturer = new Manufacturer($settings['manufacturer']);
								
				$searchProvider = new ManufacturerProductSearchProvider($this->context->getTranslator(), $manufacturer);
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
				$query->setQueryType('manufacturer')->setIdManufacturer($manufacturer->id)->setSortOrder(new SortOrder('product', $order_by, $order_way));
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();
								
                break;
            case 'sl':			
				$supplier = new Supplier($settings['supplier']);
								
				$searchProvider = new SupplierProductSearchProvider($this->context->getTranslator(), $supplier);
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
				$query->setQueryType('supplier')->setIdSupplier($supplier->id)->setSortOrder(new SortOrder('product', $order_by, $order_way));
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();
								
                break;
            case 'b':
				if($order_by == 'position'){
					$order_by = 'sales';
				}	
									
				$searchProvider = new BestSalesProductSearchProvider($this->context->getTranslator());
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
				$query->setQueryType('best-sales')->setSortOrder(new SortOrder('product', $order_by, $order_way));
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();		
                break;
            case 's':
				if(!is_array($settings['product_ids'])){
					return $products;
				}
				foreach($settings['product_ids'] as $product_id){
					$arr = explode('_', $product_id);
		
					if(isset($arr[1])){
						$id_p = $arr[1];
					}else{
						$id_p = $product_id;
					}

					if((int)$id_p){
						$id_product = (int)$id_p;
						$product =  new Product($id_product, true, $id_lang, $id_shop, $this->context);
						if (Validate::isLoadedObject($product)) {
							$product->id_product = (int)$id_product;
							$products[]= (array)$product;
						}
					}
				}	
				
                break;
            case 'p_s':
				if((isset($settings['axps_id_product']) && $settings['axps_id_product']) || isset($this->context->smarty->tpl_vars['axps_id_product'])){
					$id_product = isset($this->context->smarty->tpl_vars['axps_id_product']) ? (int)$this->context->smarty->tpl_vars['axps_id_product'] : (int)$settings['axps_id_product'];
					$product =  new Product($id_product, true, $id_lang, $id_shop, $this->context);

					if (!Validate::isLoadedObject($product)) {
						return;
					}
					
					$category = new Category($product->id_category_default);

					$searchProvider = new CategoryProductSearchProvider($this->context->getTranslator(), $category);

					$context = new ProductSearchContext($this->context);
					$query = new ProductSearchQuery();
					$query->setResultsPerPage((int)$limit + 1)->setPage($page);
					$query->setIdCategory($category->id)->setSortOrder(
						$order_by == 'rand'
						? SortOrder::random()
						: new SortOrder('product', $order_by, $order_way)
					);
					$result = $searchProvider->runQuery($context, $query);
					$products = $result->getProducts();

					$exclude_id_product = $id_product;
				}
				
                break;
            case 'p_a':
				if((isset($settings['axps_id_product']) && $settings['axps_id_product']) || isset($this->context->smarty->tpl_vars['axps_id_product'])){
					$id_product = isset($this->context->smarty->tpl_vars['axps_id_product']) ? (int)$this->context->smarty->tpl_vars['axps_id_product'] : (int)$settings['axps_id_product'];
					$product =  new Product($id_product, true, $id_lang, $id_shop, $this->context);

					if (!Validate::isLoadedObject($product)) {
						return;
					}

					$products = $product->getAccessories($id_lang);
				}
				
                break;
            default:
                $id_category_arr = explode('_', $source);

                if(isset($id_category_arr[1])){
                    $id_category = $id_category_arr[1];
                }else{
                    $id_category = $source;
                }

				$category = new Category((int)$id_category);
		
				$searchProvider = new CategoryProductSearchProvider($this->context->getTranslator(), $category);
				
				$context = new ProductSearchContext($this->context);
				$query = new ProductSearchQuery();
				$query->setResultsPerPage($limit)->setPage($page);
                $query->setQueryType('category')->setIdCategory($category->id)->setSortOrder(
                    $order_by == 'rand'
                    ? SortOrder::random()
                    : new SortOrder('product', $order_by, $order_way)
                );
				$result = $searchProvider->runQuery($context, $query);
				$products = $result->getProducts();		
				
                break;
        }

		if( ($source == 'p_s' || $source == 'p_a') && (Wp_Helper::is_preview_mode() || Dispatcher::getInstance()->getController() == 'ajax_editor' || (int)Tools::getValue( 'wp_preview' ) || (isset($settings['axps_is_editor']) && (int)$settings['axps_is_editor'])) ){
			$order_by = 'position';
			$order_way = 'ASC';

			$searchProvider = new NewProductsProductSearchProvider($this->context->getTranslator());
			$context = new ProductSearchContext($this->context);
			$query = new ProductSearchQuery();
			$query->setResultsPerPage($limit)->setPage($page);
			$query->setSortOrder(new SortOrder('product', $order_by, $order_way));
			$result = $searchProvider->runQuery($context, $query);
			$products = $result->getProducts();	
		}

		$products = $this->convertProducts($products, $exclude_id_product, $limit);
			
		return $products;
	}

	public function aProduct( $id_product )	
	{	
		$products = [];
		$id_lang = (int)$this->context->language->id;
		$id_shop = (int)$this->context->shop->id;
		$product =  new Product( $id_product, false, $id_lang, $id_shop, $this->context );
		if ( Validate::isLoadedObject($product) ) {
			$products[]= ['id_product' => (int)$id_product];
		}else{
			return;
		}
		$products = $this->convertProducts( $products, 0, 1 );
		return $products[0];
	}

	public function convertProducts($products, $exclude_id_product, $limit)	
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
		$products_for_template = [];
		if(is_array($products)){
			foreach ($products as $rawProduct) {
				if ($rawProduct['id_product'] != $exclude_id_product && (count($products_for_template) < (int) $limit || !(int) $limit)) {
					$product = $presenter->present(
						$presentationSettings,
						$assembler->assembleProduct($rawProduct),
						$this->context->language
					);
					$products_for_template[] = $product;
				}
			}
		}
		
		return 	$products_for_template;
	}
			
    public function getListByPostType($postType)
    {
		$query = new DbQuery();
		$query->select('*');
		$query->from('axon_creator_post', 'p');
		$query->where('p.post_type = "' . $postType . '"');
		$sqlResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        $arrays = array();
        foreach ($sqlResult as $p) {
            $arrays[$p['id_axon_creator_post']] = array(
                'id' => $p['id_axon_creator_post'],
                'name' => $p['title']
            );
        }

        return  $arrays;
    }
	
	/*------------------get Languages----------------------------*/
	
    public function getListLanguages()
    {
		$languages = Language::getLanguages( true, $this->context->shop->id );
		
		if( count( $languages ) < 2 ){
			return;
		}
		
        foreach ( $languages as &$lang ) {
            $lang['name_simple'] = preg_replace( '/\s\(.*\)$/', '', $lang['name'] );
        }
				
		$params = [
			'languages' => $languages,
			'current_language' => [
				'id_lang' => $this->context->language->id,
				'name' => $this->context->language->name,
				'name_simple' => preg_replace( '/\s\(.*\)$/', '', $this->context->language->name ),
				'iso_code' => $this->context->language->iso_code
			]
		];

        return $params;
    }
	
	/*------------------get Currencies----------------------------*/
	
    public function getListCurrencies()
    {
		if( Configuration::isCatalogMode() || !Currency::isMultiCurrencyActivated() ) {
			return;
		}
		
		$current_currency = null;
        $serializer = new ObjectPresenter();
        $currencies = array_map(
            function ($currency) use ($serializer, &$current_currency) {				
                $currencyArray = $serializer->present($currency);

                // serializer doesn't see 'sign' because it is not a regular
                // ObjectModel field.
                $currencyArray['sign'] = $currency->sign;

                $url = $this->context->link->getLanguageLink($this->context->language->id);

                $parsedUrl = parse_url($url);
                $urlParams = [];
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $urlParams);
                }
                $newParams = array_merge(
                    $urlParams,
                    [
                        'SubmitCurrency' => 1,
                        'id_currency' => $currency->id,
                    ]
                );
                $newUrl = sprintf('%s://%s%s%s?%s',
                    $parsedUrl['scheme'],
                    $parsedUrl['host'],
                    isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '',
                    $parsedUrl['path'],
                    http_build_query($newParams)
                );

                $currencyArray['url'] = $newUrl;

                if ($currency->id == $this->context->currency->id) {
                    $currencyArray['current'] = true;
                    $current_currency = $currencyArray;
                } else {
                    $currencyArray['current'] = false;
                }

                return $currencyArray;
            },
            Currency::getCurrencies(true, true)
        );
				
		$params = [
			'currencies' => $currencies,
			'current_currency' => $current_currency,
		];

        return $params;
    }
	
	/*------------------get Product Categories----------------------------*/
	
	public function getCategories()
    {
		$category = new Category((int)Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id);
			
        $range = '';
        $maxdepth = 0;
        if (Validate::isLoadedObject($category)) {
            if ($maxdepth > 0) {
                $maxdepth += $category->level_depth;
            }
            $range = 'AND nleft >= '.(int)$category->nleft.' AND nright <= '.(int)$category->nright;
        }

        $resultIds = array();
        $resultParents = array();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
			FROM `'._DB_PREFIX_.'category` c
			INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
			INNER JOIN `'._DB_PREFIX_.'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = '.(int)$this->context->shop->id.')
			WHERE (c.`active` = 1 OR c.`id_category` = '.(int)Configuration::get('PS_HOME_CATEGORY').')
			AND c.`id_category` != '.(int)Configuration::get('PS_ROOT_CATEGORY').'
			'.((int)$maxdepth != 0 ? ' AND `level_depth` <= '.(int)$maxdepth : '').'
			'.$range.'
			ORDER BY `level_depth` ASC, cs.`position` ASC');
        foreach ($result as &$row) {
            $resultParents[$row['id_parent']][] = &$row;
            $resultIds[$row['id_category']] = &$row;
        }
		
		$categoriesSource = array();
		
		$this->getTree($resultParents, $resultIds, $maxdepth, ($category ? $category->id : null), 0, $categoriesSource);

        return $categoriesSource;
    }

    public function getTree($resultParents, $resultIds, $maxDepth, $id_category, $currentDepth, &$categoriesSource)
    {
        if (is_null($id_category)) {
            $id_category = $this->context->shop->getCategory();
        }

        if (isset($resultIds[$id_category])) {
            $link = $this->context->link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']);
            $name = str_repeat('&nbsp;&nbsp;', 1 * $currentDepth).$resultIds[$id_category]['name'];
            $desc = $resultIds[$id_category]['description'];
        } else {
            $link = $name = $desc = '';
        }
		
		$categoriesSource[$currentDepth . '_' . $id_category] = $name;
		
        if (isset($resultParents[$id_category]) && count($resultParents[$id_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth)) {
            foreach ($resultParents[$id_category] as $subcat) {
                $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1, $categoriesSource);
            }
        }
		
    }
}
