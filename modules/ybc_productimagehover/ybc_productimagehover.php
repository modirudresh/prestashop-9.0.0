<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*/

if (!defined('_PS_VERSION_')) { exit; }
         
class Ybc_productimagehover extends Module
{    
    public $refs;
    public function __construct()
	{
		$this->name = 'ybc_productimagehover';
		$this->tab = 'front_office_features';
		$this->version = '1.0.8';
		$this->author = 'PrestaHero';
		$this->need_instance = 0;
		$this->module_key = Tools::hash($this->name);
        $this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Product image rollover');
		$this->description = $this->l('Display second image when hover over product image');
$this->refs = 'https://prestahero.com/';

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }
     /**
	 * @see Module::install()
	 */
    public function install()
	{
        Configuration::updateValue('YBC_PI_TRANSITION_EFFECT','fade');
        Configuration::updateValue('YBC_PI_THOSE_PAGES','allpage'); 
        return  parent::install() 
            && $this->registerHook('productImageHover') 
            && $this->registerHook('displayBackOfficeHeader') 
            && $this->registerHook('displayHeader');
    }
    /**
	 * @see Module::uninstall()
	 */
     
	public function uninstall()
	{
	    Configuration::deleteByName('YBC_PI_TRANSITION_EFFECT');
        Configuration::deleteByName('YBC_PI_THOSE_PAGES');
        return parent::uninstall();
    }
    
    public function hookDisplayHeader()
    {

        $page_setting = explode(',', Configuration::get('YBC_PI_THOSE_PAGES'));
        if(!in_array('allpage', $page_setting) && (($page = Tools::strtolower(trim(Tools::getValue('controller')))) && $page && !in_array($page, $page_setting)))
            return;   
        if(version_compare(_PS_VERSION_, '1.7.0', '>='))
            $this->context->controller->addCSS($this->_path.'views/css/fix17.css', 'all');
        elseif(version_compare(_PS_VERSION_, '1.6.0', '>='))
            $this->context->controller->addCSS($this->_path.'views/css/productimagehover.css', 'all');
        else  
            $this->context->controller->addCSS($this->_path.'views/css/fix15.css', 'all'); 
        
        $this->context->controller->addJS($this->_path.'views/js/productimagehover.js');
        $this->context->smarty->assign(array(
            'baseAjax'=>$this->context->link->getModuleLink('ybc_productimagehover', 'ajax'),
            'YBC_PI_TRANSITION_EFFECT' => Configuration::get('YBC_PI_TRANSITION_EFFECT'),
            '_PI_VER_17_'=> version_compare(_PS_VERSION_, '1.7.0', '>=') ? 1 : 0,
            '_PI_VER_16_'=> version_compare(_PS_VERSION_, '1.6.0', '>=') ? 1 : 0,
        ));
        return $this->display(__FILE__,'renderJs.tpl');      
    }
    
    public function hookDisplayBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        $this->smarty->assign(array(
            'linkJsAdmin' => $controller == 'AdminModules' && Tools::getValue('configure') == 'ybc_productimagehover' ? $this->_path.'views/js/admin.js' : ''
        ));
        return $this->display(__FILE__, 'admin_head.tpl');
    }
    
    public function getContent()
    {
        if(Tools::isSubmit('submitUpdate')){
            Configuration::updateValue('YBC_PI_TRANSITION_EFFECT', Tools::strtolower(trim(Tools::getValue('YBC_PI_TRANSITION_EFFECT'))));
            Tools::getValue('YBC_PI_THOSE_PAGES') && is_array(Tools::getValue('YBC_PI_THOSE_PAGES'))? Configuration::updateValue('YBC_PI_THOSE_PAGES', Tools::strtolower(trim(implode(',',Tools::getValue('YBC_PI_THOSE_PAGES'))))):Configuration::updateValue('YBC_PI_THOSE_PAGES','');
        }
        if(version_compare(_PS_VERSION_, '1.6.0', '>='))
            $postUrl = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        else
            $postUrl = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules');
        
        $those_pages = array(
            array(
                'id' => 'allpage',
                'name' => $this->l('All pages')
            ),
            array(
                'id' => 'index',
                'name' => $this->l('Home')
            ),
            array(
                'id' => 'category',
                'name' => $this->l('Category')
            ),
            array(
                'id' => 'search',
                'name' => $this->l('Search result')
            ),
            array(
                'id' => 'pricesdrop',
                'name' => $this->l('Special products')
            ),
            array(
                'id' => 'newproducts',
                'name' => $this->l('New products')
            ),
            array(
                'id' => 'bestsales',
                'name' => $this->l('Best sales')
            ),
            array(
                'id' => 'manufacturer',
                'name' => $this->l('Brand (manufacturer)')
            ),
            array(
                'id' => 'supplier',
                'name' => $this->l('Supplier')
            ),
        );
        if(version_compare(_PS_VERSION_, '1.6.0', '>=')){
            $those_pages[] = array(
                'id' => 'product',
                'name' => $this->l('Product detail')
            );
        }
        $this->smarty->assign(            
            array(
                'postUrl' => $postUrl,
                'effects' => array(
                    array(
                        'id' => 'zoom',
                        'name' => $this->l('Zoom')
                    ),
                    array(
                        'id' => 'fade',
                        'name' => $this->l('Fade')
                    ),
                    array(
                        'id' => 'vertical_scrolling_bottom_to_top',
                        'name' => $this->l('Vertical scrolling  bottom to top')
                    ),
                    array(
                        'id' => 'vertical_scrolling_top_to_bottom',
                        'name' => $this->l('Vertical scrolling top to bottom')
                    ),                    
                    array(
                        'id' => 'horizontal_scrolling_left_to_right',
                        'name' => $this->l('Horizontal scrolling left to right')
                    ),
                    array(
                        'id' => 'horizontal_scrolling_right_to_left',
                        'name' => $this->l('Horizontal scrolling right to left')
                    )
                ),
                'those_pages' =>$those_pages,
                'YBC_PI_TRANSITION_EFFECT' => Configuration::get('YBC_PI_TRANSITION_EFFECT'),
                'YBC_PI_THOSE_PAGES' => Configuration::get('YBC_PI_THOSE_PAGES')? explode(',',Configuration::get('YBC_PI_THOSE_PAGES')): array(),
                'setting_updated' => Tools::isSubmit('submitUpdate') ? true : false,
            )
        );
        $template = ((version_compare(_PS_VERSION_, '1.6.0', '>=') ? 1 : 0)?'admin-config.tpl':'admin-config-v15.tpl');
        return $this->display(__FILE__, $template).$this->displayIframe();
    }
    
    public function hookProductImageHover($params)
    {

        $page_setting = explode(',', Configuration::get('YBC_PI_THOSE_PAGES'));
        if(!in_array('allpage', $page_setting) && (($page = Tools::strtolower(trim(Tools::getValue('controller')))) && $page && !in_array($page, $page_setting)))
            return;
            
        if(isset($params['id_product']))
        {
            $product_id=$params['id_product'];
            $sql= "SELECT id_image 
                   FROM  `"._DB_PREFIX_."image` 
                   WHERE  `id_product` =  (int)$product_id AND (cover = 0 OR cover is null) ORDER BY  `position` ASC";
                   
            $image = Db::getInstance()->getRow($sql);
            
            if(!$image)
            {
                $sql= "SELECT id_image 
                       FROM  `"._DB_PREFIX_."image` 
                       WHERE  `id_product` =  (int)$product_id AND cover =  1 ORDER BY  `position` ASC";
                $image = Db::getInstance()->getRow($sql);               
            }
            if($image){
                $product = new Product($product_id,false,$this->context->language->id,$this->context->shop->id);
                
                $this->smarty->assign(array(
                    'product_name' => $product->name,
                    'img_url' => $this->context->link->getImageLink($product->link_rewrite, (int)$image['id_image'], method_exists('ImageType', 'getFormattedName') ? ImageType::getFormattedName('home') : 'home')
                ));               
            }
            else
                return;        
        }
        $this->smarty->assign(array('YBC_PI_TRANSITION_EFFECT' => Configuration::get('YBC_PI_TRANSITION_EFFECT')));
        return $this->display(__FILE__, 'image.tpl');
    }

    public function displayIframe()
    {
        switch($this->context->language->iso_code) {
            case 'en':
                $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
                break;
            case 'it':
                $url = 'https://cdn.prestahero.com/it/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
                break;
            case 'fr':
                $url = 'https://cdn.prestahero.com/fr/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
                break;
            case 'es':
                $url = 'https://cdn.prestahero.com/es/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
                break;
            default:
                $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
        }
        $this->smarty->assign(
            array(
                'url_iframe' => $url
            )
        );
        return $this->display(__FILE__,'iframe.tpl');
    }
}