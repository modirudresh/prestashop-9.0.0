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

require_once(dirname(__FILE__) . '/classes/ets_baw_paggination_class.php');
require_once(dirname(__FILE__) . '/classes/ets_baw_defines.php');
require_once(dirname(__FILE__) . '/classes/ets_baw_obj.php');
require_once(dirname(__FILE__) . '/classes/ets_baw_banner.php');
if (!defined('_PS_ETS_BAW_IMG_DIR_')) {
    define('_PS_ETS_BAW_IMG_DIR_', _PS_IMG_DIR_.'ets_banneranywhere/');
}
if (!defined('_PS_ETS_BAW_IMG_')) {
    define('_PS_ETS_BAW_IMG_', _PS_IMG_.'ets_banneranywhere/');
}
class Ets_banneranywhere extends Module
{
    public $is17 = false;
    public $hooks = array(
        'displayBackOfficeHeader',
        'displayHeader',
        'displayCustomDiscountRule',
        'displayShoppingCartFooter',
        'displayProductAdditionalInfo',
        'displayRightColumnProduct',
        'actionOutputHTMLBefore',
    );
    public $html;
    public $module_dir;
    public static $trans;
    public $_errors = array();
    public $refs;
    public $secure_key;

    public function __construct()
    {
        $this->name = 'ets_banneranywhere';
        $this->tab = 'front_office_features';
        $this->version = '1.0.6';
        $this->author = 'ETS-Soft';
        $this->need_instance = 0;
        $this->module_key = Tools::hash($this->name);
        $this->bootstrap = true;
        parent::__construct();
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_dir = $this->_path;
        $this->displayName = $this->l('Banner anywhere');
        $this->description = $this->l('Easily add banners on the Home page, Category page, product page, or wherever you want on your PrestaShop store');
        $this->refs = 'https://prestahero.com/';
		if(version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
    }
    public function install()
    {
        return parent::install() && $this->installHooks() && $this->installDb();
    }
    public function unInstall()
    {
        return parent::unInstall() && $this->unInstallHooks() && $this->unInstallDb() && $this->rrmdir(_PS_ETS_BAW_IMG_DIR_);
    }
    public function installDb()
    {
        return Ets_baw_defines::getInstance()->installDb();
    }
    public function unInstallDb()
    {
        return Ets_baw_defines::getInstance()->unInstallDb();
    }
    public function installHooks()
    {
        foreach($this->hooks as $hook)
        {
            $this->registerHook($hook);
        }
        $position_hooks = Ets_baw_banner::getInstance()->getPositions();
        foreach(array_keys($position_hooks) as $position_hook)
        {
            $this->registerHook($position_hook);
        }
        return true;
    }
    public function unInstallHooks()
    {
        foreach($this->hooks as $hook)
        {
            $this->unRegisterHook($hook);
        }
        $position_hooks = Ets_baw_banner::getInstance()->getPositions();
        foreach(array_keys($position_hooks) as $position_hook)
        {
            $this->unRegisterHook($position_hook);
        }
        return true;
    }
    public function rrmdir($dir) {
        $dir = rtrim($dir,'/');
        if ($dir && is_dir($dir)) {
             if($objects = scandir($dir))
             {
                 foreach ($objects as $object) {
                       if ($object != "." && $object != "..") {
                         if (is_dir($dir."/".$object) && !is_link($dir."/".$object))
                           $this->rrmdir($dir."/".$object);
                         else
                           @unlink($dir."/".$object);
                       }
                 }
             }
             rmdir($dir);
       }
       return true;
    }
    public function getTextLang($text, $lang,$file_name='')
    {
        if(is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif(is_object($lang))
            $iso_code = $lang->iso_code;
        else
        {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
		$modulePath = rtrim(_PS_MODULE_DIR_, '/').'/'.$this->name;
        $fileTransDir = $modulePath.'/translations/'.$iso_code.'.'.'php';
        if(!@file_exists($fileTransDir)){
            return $text;
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $text_tras = preg_replace("/\\\*'/", "\'", $text);
        $strMd5 = md5($text_tras);
        $keyMd5 = '<{' . $this->name . '}prestashop>' . ($file_name ? : $this->name) . '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if($matches && isset($matches[2])){
           return  $matches[2];
        }
        return $text;
    }
    public function addJquery()
    {
        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.7.0', '<'))
            $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-'._PS_JQUERY_VERSION_.'.min.js');
        else
            $this->context->controller->addJquery();
    }
    public function hookDisplayBackOfficeHeader()
    {
        $configure = Tools::getValue('configure');
        $controller = Tools::getValue('controller');
        if($controller=='AdminModules' && $configure== $this->name)
        {
            
            $this->context->controller->addCSS($this->_path.'views/css/admin.css');
            $this->addJquery();
            $this->context->controller->addJqueryUI('ui.sortable');
            $this->context->controller->addJS($this->_path.'views/js/admin.js');
        }
    }
    public function getContent()
    {
        $html = '';
        if(($action = Tools::getValue('action')) && $action=='updateBannerOrdering' && ($banners=Tools::getValue('list-banner')) && self::validateArray($banners,'isInt'))
        {
            Ets_baw_banner::getInstance()->updatePosition($banners);
        }
        if(Tools::isSubmit('del') && ($id_ets_baw_banner = (int)Tools::getValue('id_ets_baw_banner')) && ($bannerObj = new Ets_baw_banner($id_ets_baw_banner)) && Validate::isLoadedObject($bannerObj))
        {
            if($bannerObj->delete())
            {
                $this->context->cookie->ets_baw_success = $this->l('Deleted successfully');
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=ets_banneranywhere');
            }
        }
        if(Tools::isSubmit('change_enabled') && ($id_ets_baw_banner = (int)Tools::getValue('id_ets_baw_banner')) && ($bannerObj = new Ets_baw_banner($id_ets_baw_banner)) && Validate::isLoadedObject($bannerObj))
        {
            $active = (int)Tools::getValue('change_enabled');
            $bannerObj->active = $active;
            if($bannerObj->update())
            {
                $this->context->cookie->ets_baw_success = $this->l('Updated successfully');
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=ets_banneranywhere');
            }
        }
        if(Tools::isSubmit('editbanner') &&  ($id_ets_baw_banner = (int)Tools::getValue('id_ets_baw_banner')))
            $banner = new Ets_baw_banner($id_ets_baw_banner);
        else
            $banner = new Ets_baw_banner();
        if(Tools::isSubmit('save_baw_banner'))
        {
            $result = $banner->saveData();
            if(isset($result['errors']) && $result['errors'])
                $html .= $this->displayError($result['errors']);
            elseif(isset($result['success']) && $result['success'])
            {
                $this->context->cookie->ets_baw_success = $result['success'][0];
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=ets_banneranywhere');
            }
        }
        if($this->context->cookie->ets_baw_success)
        {
            $html .= $this->displayConfirmation($this->context->cookie->ets_baw_success);
            $this->context->cookie->ets_baw_success ='';
        }
        if(Tools::isSubmit('addNewBanner') || Validate::isLoadedObject($banner))
            $html .= $banner->renderForm();
        else
            $html .= $this->renderListBanner();
        // $html .= $this->displayIframe();
        return $html;
    }
    public function renderListBanner()
    {
        $fields_list = array(
            'id_ets_baw_banner' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
                'filter' => true,
                'sort' => true,
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'type' => 'text',
                'strip_tag' => false,
                'filter' => false,
                'sort' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'type' => 'text',
                'filter' => true,
                'sort' => true,
            ),
            'image_alt' => array(
                'title' => $this->l('Alt text'),
                'type' => 'text',
                'filter' => true,
                'sort' => true,
            ),
            'short_code' => array(
                'title' => $this->l('Short code'),
                'type' => 'text',
                'strip_tag' => false,
                'filter' => false,
                'sort' => false,
                'toltip' => $this->l('Copy the short code above, paste it into anywhere on your product description, CMS page content, tpl file, etc. in order to display this banner'),
            ),
            'display_position' => array(
                'title' => $this->l('Position'),
                'type' => 'select',
                'strip_tag' => false,
                'sort' => false,
                'filter' => true,
                'filter_list' => array(
                    'list' => Ets_baw_banner::getInstance()->getPositions(),
                    'id_option' => 'id_option',
                    'value' => 'name',
                ),
            ),
            'sort_order' => array(
                'title' => $this->l('Sort order'),
                //'width' => 40,
                'type' => 'text',
                'sort' => true,
                'filter' => false,
                'update_position' => true,
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'type' => 'active',
                'sort' => true,
                'filter' => true,
                'filter_list' => array(
                    'id_option' => 'active',
                    'value' => 'title',
                    'list' => array(
                        0 => array(
                            'active' => 0,
                            'title' => $this->l('Disabled')
                        ),
                        1 => array(
                            'active' => 1,
                            'title' => $this->l('Enabled')
                        ),
                    )
                )
            ),
        );
        $filter = '';
        $show_resset = false;
        if(($id_ets_abw_banner = Tools::getValue('id_ets_abw_banner'))!='' && Validate::isCleanHtml($id_ets_abw_banner))
        {
            $filter .= ' AND b.id_ets_abw_banner='.(int)$id_ets_abw_banner;
            $show_resset = true;
        }
        if(($title=Tools::getValue('title'))!='' && Validate::isCleanHtml($title))
        {
            $filter .= ' AND bl.title LIKE "%'.pSQL($title).'%"';
            $show_resset = true;
        }
        if(($active=Tools::getValue('active'))!='' && Validate::isCleanHtml($active))
        {
            $filter .= ' AND b.active = "'.(int)$active.'"';
            $show_resset = true;
        }
        if(($image_alt = Tools::getValue('image_alt'))!='' && Validate::isCleanHtml($image_alt))
        {
            $filter .= ' AND bl.image_alt LIKE "%'.pSQL($image_alt).'%"';
            $show_resset = true;
        }
        if(($image_url = Tools::getValue('image_alt'))!='' && Validate::isCleanHtml($image_url))
        {
            $filter .= ' AND bl.image_url LIKE "%'.pSQL($image_url).'%"';
            $show_resset = true;
        }
        $sort_order = false;
        if(($position= Tools::getValue('display_position'))!='' && Validate::isCleanHtml($position))
        {
            $filter .= ' AND (bp.position = "'.pSQL($position).'")';
            $show_resset = true;
            $sort_order = true;
        }
        else
        {
            unset($fields_list['sort_order']);
        }
        $sort = "";
        $sort_type=Tools::getValue('sort_type',$sort_order ? 'asc':'desc');
        $sort_value = Tools::getValue('sort',$sort_order ? 'sort_order':'id_ets_baw_banner');
        if($sort_value)
        {
            switch ($sort_value) {
                case 'id_ets_baw_banner':
                    $sort .=' b.id_ets_baw_banner';
                    break;
                case 'title':
                    $sort .=' bl.title';
                    break;
                case 'active':
                    $sort .=' b.active';
                    break;
                case 'image_alt':
                    $sort .=' bl.image_alt';
                    break;
                case 'image_url':
                    $sort .=' bl.image_url';
                    break;
                case 'sort_order':
                    $sort .=' bp.`sort` ';
                    break;
            }
            if($sort && $sort_type && in_array($sort_type,array('asc','desc')))
                $sort .= ' '.$sort_type;
        }
        //Paggination
        $page = (int)Tools::getValue('page');
        if($page<=0)
            $page = 1;
        $totalRecords = (int)Ets_baw_banner::getListBanners($sort_order,$filter,$sort,0,0,true);
        $paggination = new Ets_baw_paggination_class();            
        $paggination->total = $totalRecords;
        $paggination->url = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&page=_page_'.$this->getFilterParams($fields_list,'banner');
        $paggination->limit = (int)Tools::getValue('paginator_banner_select_limit',20);
        $paggination->name ='banner';
        $totalPages = ceil($totalRecords / $paggination->limit);
        if($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = $paggination->limit * ($page - 1);
        if($start < 0)
            $start = 0;
        $banners = Ets_baw_banner::getListBanners($sort_order,$filter,$sort,$start,$paggination->limit,false);
        if($banners)
        {
            foreach($banners as &$banner)
            {
                if($banner['image'])
                    $banner['image'] = $this->displayText(null,'img',null,null,null,null,$this->context->link->getMediaLink(_PS_ETS_BAW_IMG_.$banner['image']));
                $this->smarty->assign(
                    array(
                        'id_banner' => $banner['id_ets_baw_banner'],
                    )
                );
                $banner['short_code'] = $this->display(__FILE__,'short_code.tpl');
                $banner['display_position'] = Ets_baw_banner::getPositionBYId($banner['id_ets_baw_banner']);
                if($banner['display_position'])
                {
                    $display_positions = Ets_baw_banner::getInstance()->getPositions();
                    $positions = array();
                    foreach($display_positions as $display)
                    {
                        $positions[$display['id_option']] = $display['name'];
                    }
                    $this->smarty->assign(
                        array(
                            'display_positions' => explode(',',$banner['display_position']),
                            'positions' => $positions,
                        )
                    );
                    $banner['display_position'] = $this->display(__FILE__,'position.tpl');
                }
                
            }
        }
        $paggination->text =  $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
        $paggination->style_links = $this->l('links');
        $paggination->style_results = $this->l('results');
        $listData = array(
            'name' => 'banner',
            'actions' => array('view','delete'),
            'icon' => 'icon-rule',
            'currentIndex' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.($paggination->limit!=20 ? '&paginator_banner_select_limit='.$paggination->limit:''),
            'postIndex' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name,
            'identifier' => 'id_ets_baw_banner',
            'show_toolbar' => true,
            'show_action' => true,
            'show_add_new' => true,
            'link_new' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&addNewBanner=1',
            'add_new_text' => $this->l('Add new banner'),
            'title' => $this->l('Banners'),
            'fields_list' => $fields_list,
            'field_values' => $banners,
            'paggination' => $paggination->render(),
            'filter_params' => $this->getFilterParams($fields_list,'banner'),
            'show_reset' =>$show_resset,
            'totalRecords' => $totalRecords,
            'sort'=> $sort_value,
            'sort_type' => $sort_type,
            'show_bulk_action'=>false,
        );            
        return  $this->renderList($listData);
    }
    public function hookActionOutputHTMLBefore($params)
    {
        if (isset($params['html']) && $params['html'])
        {
            $params['html'] = $this->doShortcode($params['html']);
        }
    }
    public function doShortcode($str)
    {
        return preg_replace_callback('~\[banner\-any\-where id="(\d+)"\]~',array($this,'replace'), $str);//[social-locker ]
    }
    public function replace ($matches)
    {
        if(is_array($matches) && count($matches)==2)
        {
            $form = $this->displayBlockBanner(array(
                'id_banner' => (int)$matches[1]
            ));
            if($form)
                return $form;
            else
                return '';
        }
    }
    public function displayBlockBanner($params)
    {
        if(isset($params['id_banner']) && ($id_banner = (int)$params['id_banner']))
        {
            $banner = Ets_baw_banner::getBannerById($id_banner);
        }
        elseif(isset($params['banner']))
            $banner = $params['banner'];
        if(isset($banner) && $banner)
        {
            $this->smarty->assign(
                array(
                    'banner' => $banner,
                    'banner_class' => isset($params['banner_class']) ? $params['banner_class'] :'',
                    'position' =>isset($params['position']) ? $params['position']:'',
                )
            );
            return $this->display(__FILE__,'banner.tpl');
        }
        
    }
    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->displayBannerByPosition('displayProductAdditionalInfo');
    }
    public function displayBannerByPosition($position,$banner_class='')
    {
        if($banners = Ets_baw_banner::getBannersByPosition($position))
        {
            $html = '';
            foreach($banners as $banner)
            {
                $html .= $this->displayBlockBanner(array('banner' => $banner,'banner_class'=>$banner_class,'position'=> Tools::strtolower($position)));
            }
            return $html;
        }
    }
    public function _header()
    {
        
        if ($this->context->controller->php_self == 'category') {
            $this->smarty->assign(array(
                'hookDisplayProductListHeaderAfter' => $this->hookDisplayProductListHeaderAfter(),
                'hookDisplayProductListHeaderBefore' => $this->hookDisplayProductListHeaderBefore(),
            ));
        }
        if ($this->context->controller->php_self == 'product') {
            $this->smarty->assign(array(
                'hookDisplayProductVariantsBefore' => $this->hookDisplayProductVariantsBefore(),
                'hookDisplayProductVariantsAfter' => $this->hookDisplayProductVariantsAfter(),
                'hookDisplayProductCommentsListHeaderBefore' => $this->hookDisplayProductCommentsListHeaderBefore(),
            ));
        }
        if ($this->context->controller->php_self == 'cart') {
            $this->smarty->assign(array(
                'hookDisplayCartGridBodyBefore1' => $this->hookDisplayCartGridBodyBefore1(),
            ));
        }
        if ($this->context->controller->php_self == 'order') {
            $this->smarty->assign(array(
                'hookDisplayCartGridBodyBefore1' => $this->hookDisplayCartGridBodyBefore1(),
                'hookDisplayCartGridBodyBefore2' => $this->hookDisplayCartGridBodyBefore2(),
                'hookDisplayCartGridBodyAfter' => $this->hookDisplayCartGridBodyAfter(),
            ));
        }
        $this->smarty->assign(array(
            'hookDisplayLeftColumnBefore' => $this->hookDisplayLeftColumnBefore(),
            'hookDisplayRightColumnBefore' => $this->hookDisplayRightColumnBefore(),
        ));
        return $this->display(__FILE__, 'render-js.tpl');
    }
    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
        $this->context->controller->addJS($this->_path.'views/js/app.js');
        $this->context->controller->addJS($this->_path.'views/js/front.js');
        return $this->_header();
    }
    public function hookDisplayHome()
    {
        return $this->displayBannerByPosition('displayHome');
    }
    public function hookDisplayLeftColumn()
    {
        return $this->displayBannerByPosition('displayLeftColumn','block');
    }
    public function hookDisplayRightColumn()
    {
        return $this->displayBannerByPosition('displayRightColumn','block');
    }
    public function hookDisplayLeftColumnBefore()
    {
        return $this->displayBannerByPosition('displayBeforeLeftColumn','block');
    }
    public function hookDisplayRightColumnBefore()
    {
        return $this->displayBannerByPosition('displayBeforeRightColumn','block');
    }
    public function hookDisplayCartGridBodyBefore1()
    {
        if(($controller = Tools::getValue('controller')) && $controller=='cart')
        return $this->displayBannerByPosition('displayCartGridBodyBefore1');
    }
    public function hookDisplayCartGridBodyBefore2()
    {
        return $this->displayBannerByPosition('displayCartGridBodyBefore2');
    }
    public function hookDisplayCartGridBodyAfter()
    {
        return $this->displayBannerByPosition('displayCartGridBodyAfter');
    }
    public function hookDisplayProductListHeaderBefore()
    {
        return $this->displayBannerByPosition('displayProductListHeaderBefore');
    }
    public function hookDisplayProductListHeaderAfter()
    {
        return $this->displayBannerByPosition('displayProductListHeaderAfter');
    }
    public function hookDisplayFooterCategory()
    {
        return $this->displayBannerByPosition('displayFooterCategory');
    }
    public function hookDisplayBanner()
    {
        if(($controller = Tools::getValue('controller')) && $controller=='index')
        {
            return $this->displayBannerByPosition('displayBanner');
        }
    } 
    public function hookDisplayNav1()
    {
        return $this->displayBannerByPosition('displayNav1');
    }
    public function hookDisplayFooterBefore()
    {
        return $this->displayBannerByPosition('displayFooterBefore');
    }
    public function hookDisplayFooterAfter()
    {
        return $this->displayBannerByPosition('displayFooterAfter');
    }
    public function hookDisplayFooterProduct()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayFooterProduct');
    }
    public function hookDisplayProductVariantsBefore()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayProductVariantsBefore');
    }
    public function hookDisplayProductVariantsAfter()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayProductVariantsAfter');
    }
    public function hookDisplayReassurance()
    {
        $controller = Tools::getValue('controller');
        if($controller=='product')
            return $this->displayBannerByPosition('displayReassurance');
    }
    public function hookDisplayAfterProductThumbs()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayAfterProductThumbs');
    }
    public function hookDisplayProductActions()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayProductActions');
    }
    public function hookDisplayProductCommentsListHeaderBefore()
    {
        $action = Tools::getValue('action');
        if($action!='quickview')
            return $this->displayBannerByPosition('displayProductCommentsListHeaderBefore');
    }
    public function hookDisplayShoppingCartFooter()
    {
        return $this->displayBannerByPosition('displayShoppingCartFooter');
    }
    public function displayPaggination($limit,$name)
    {
        $this->context->smarty->assign(
            array(
                'limit' => $limit,
                'pageName' => $name,
            )
        );
        return $this->display(__FILE__,'limit.tpl');
    }
    public function hookDisplayCustomDiscountRule()
    {
        return $this->displayBannerByPosition('displayCustomDiscountRule');
    }
    public function hookDisplayRightColumnProduct()
    {
        return $this->displayBannerByPosition('displayRightColumnProduct');
    }
    public function displayText($content=null,$tag='',$class=null,$id=null,$href=null,$blank=false,$src = null,$name = null,$value = null,$type = null,$data_id_product = null,$rel = null,$attr_datas=null)
    {
        $this->smarty->assign(
            array(
                'content' =>$content,
                'tag' => $tag,
                'tag_class'=> $class,
                'tag_id' => $id,
                'href' => $href,
                'blank' => $blank,
                'src' => $src,
                'attr_name' => $name,
                'value' => $value,
                'type' => $type,
                'data_id_product' => $data_id_product,
                'attr_datas' => $attr_datas,
                'rel' => $rel,
            )
        );
        return $this->display(__FILE__,'html.tpl');
    }
    public function getFilterParams($field_list,$table='')
    {
        $params = '';        
        if($field_list)
        {
            if(Tools::isSubmit('ets_baw_submit_'.$table))
                $params .='&ets_baw_submit_'.$table.='=1';
            foreach($field_list as $key => $val)
            {
                $value_key = (string)Tools::getValue($key);
                $value_key_max = (string)Tools::getValue($key.'_max');
                $value_key_min = (string)Tools::getValue($key.'_min');
                if($value_key!='')
                {
                    $params .= '&'.$key.'='.urlencode($value_key);
                }
                if($value_key_max!='')
                {
                    $params .= '&'.$key.'_max='.urlencode($value_key_max);
                }
                if($value_key_min!='')
                {
                    $params .= '&'.$key.'_min='.urlencode($value_key_min);
                } 
            }
            unset($val);
        }
        return $params;
    }
    public function renderList($listData)
    { 
        if(isset($listData['fields_list']) && $listData['fields_list'])
        {
            foreach($listData['fields_list'] as $key => &$val)
            {
                $value_key = (string)Tools::getValue($key);
                $value_key_max = (string)Tools::getValue($key.'_max');
                $value_key_min = (string)Tools::getValue($key.'_min');
                if(isset($val['filter']) && $val['filter'] && ($val['type']=='int' || $val['type']=='date'))
                {
                    if(Tools::isSubmit('ets_baw_submit_'.$listData['name']))
                    {
                        $val['active']['max'] =  trim($value_key_max);   
                        $val['active']['min'] =  trim($value_key_min); 
                    }
                    else
                    {
                        $val['active']['max']='';
                        $val['active']['min']='';
                    }  
                }  
                elseif(!Tools::isSubmit('del') && Tools::isSubmit('ets_baw_submit_'.$listData['name']))               
                    $val['active'] = trim($value_key);
                else
                    $val['active']='';
            }
        }  
        if(!isset($listData['class']))
            $listData['class']='';  
        $this->smarty->assign($listData);
        return $this->display(__FILE__, 'list_helper.tpl');
    }
    public static function validateArray($array,$validate='isCleanHtml')
    {
        if($array)
        {
            if(!is_array($array))
            return false;
            if(method_exists('Validate',$validate))
            {
                if($array && is_array($array))
                {
                    $ok= true;
                    foreach($array as $val)
                    {
                        if(!is_array($val))
                        {
                            if($val && !Validate::$validate($val))
                            {
                                $ok= false;
                                break;
                            }
                        }
                        else
                            $ok = self::validateArray($val,$validate);
                    }
                    return $ok;
                }
            }
        }
        return true;
    }
    // public function displayIframe()
    // {
    //     switch($this->context->language->iso_code) {
    //       case 'en':
    //         $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
    //         break;
    //       case 'it':
    //         $url = 'https://cdn.prestahero.com/it/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
    //         break;
    //       case 'fr':
    //         $url = 'https://cdn.prestahero.com/fr/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
    //         break;
    //       case 'es':
    //         $url = 'https://cdn.prestahero.com/es/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
    //         break;
    //       default:
    //         $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_'.$this->name.'&utm_medium=iframe';
    //     }
    //     $this->smarty->assign(
    //         array(
    //             'url_iframe' => $url
    //         )
    //     );
    //     return $this->display(__FILE__,'iframe.tpl');
    // }
    public function checkCreatedColumn($table,$column)
    {
        $fieldsCustomers = Db::getInstance()->ExecuteS('DESCRIBE '._DB_PREFIX_.pSQL($table));
        $check_add=false;
        foreach($fieldsCustomers as $field)
        {
            if($field['Field']==$column)
            {
                $check_add=true;
                break;
            }
        }
        return $check_add;
    }
}