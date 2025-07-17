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
class Ets_baw_banner extends Ets_baw_obj
{
    public static $instance;
    public $title;
    public $image;
    public $image_alt;
    public $image_url;
    public $content_before_image;
    public $content_after_image;
    public $position;
    public $active;
    public static $definition = array(
		'table' => 'ets_baw_banner',
		'primary' => 'id_ets_baw_banner',
        'multilang' => true,
		'fields' => array(
            'title' => array('type'=> self::TYPE_STRING,'lang'=>true),
            'image' => array('type'=>self::TYPE_STRING,'lang'=>true),
            'image_alt' => array('type'=>self::TYPE_STRING,'lang'=>true),
            'image_url' => array('type'=>self::TYPE_STRING,'lang'=>true),
            'content_before_image' => array('type'=> self::TYPE_HTML,'lang'=>true),
            'content_after_image' => array('type'=> self::TYPE_HTML,'lang'=>true),
            'active'=> array('type'=> self::TYPE_INT),
        )
    );
    public function __construct($id_item = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
        if($this->id)
            $this->position = self::getPositionBYId($this->id);
	}
    public static function getInstance()
    {
        if (!(isset(self::$instance)) || !self::$instance) {
            self::$instance = new Ets_baw_banner();
        }
        return self::$instance;
    }
    public function l($string,$file_name='')
    {
        return Translate::getModuleTranslation('ets_banneranywhere', $string, $file_name ? : pathinfo(__FILE__, PATHINFO_FILENAME));
    }

	public function getListFields()
	{
		$configs = array(
			'title' => array(
				'type'=>'text',
				'lang'=>true,
				'label'=> $this->l('Title'),
				'validate'=>'isCleanHtml',
			),
			'image' => array(
				'type' => 'file_lang',
				'label' => $this->l('Image'),
				'validate'=>'isCleanHtml',
				'desc' => sprintf($this->l('Accepted format: jpg, gif, jpeg, png. Limit %dMB'),Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'))
			),
			'image_alt' => array(
				'type'=> 'text',
				'label' => $this->l('Image alt text'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'image_url' => array(
				'type'=> 'text',
				'label' => $this->l('Image link direct'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
				'desc' => sprintf($this->l('Image links must start with http:// or https://')),
			),
			'content_before_image' => array(
				'type'=>'textarea',
				'label' => $this->l('Content appears before the image'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'content_after_image' => array(
				'type'=>'textarea',
				'label' => $this->l('Content appears after the image'),
				'lang'=>true,
				'autoload_rte'=>true,
				'validate'=>'isCleanHtml',
			),
			'position' => array(
				'type' => 'checkbox',
				'label' => $this->l('Display positions'),
				'values' => array(
					'query' => $this->getPositions(),
					'id' => 'id_option',
					'name' => 'name'
				),
				'validate'=>'isCleanHtml',
			),
			'active' => array(
				'type'=>'switch',
				'label'=>$this->l('Active'),
				'default' =>1,
				'values' => array(
					array(
						'label' => $this->l('Yes'),
						'id' => 'active_on',
						'value' => 1,
					),
					array(
						'label' => $this->l('No'),
						'id' => 'active_off',
						'value' => 0,
					)
				),
			),
		);
		return array(
			'form' => array(
				'legend' => array(
					'title' => $this->id ? $this->l('Edit banner') : $this->l('Add banner') ,
				),
				'input' => array(),
				'submit' => array(
					'title' => $this->l('Save'),
				),
				'buttons'=> array(
					array(
						'title' => $this->l('Cancel'),
						//'type' => 'submit',
						'class' => 'pull-left',
						//'name' => 'btncancel',
						'icon' => 'process-icon-cancel',
						'href' => Context::getContext()->link->getAdminLink('AdminModules').'&configure=ets_banneranywhere'
					)
				),
				'name' => 'baw_banner',
				'key' => 'id_ets_baw_banner',
			),
			'configs' =>$configs,
		);
	}
	public function getPositions()
	{
		$positions =  array(
			'displayNav1' => array(
				'id_option' => 'displayNav1',
				'name'=> $this->l('[highlight]Header:[end_highlight] On the top navigation bar'),
			),
			'displayProductListHeaderBefore' => array(
				'id_option' => 'displayProductListHeaderBefore',
				'name'=> $this->l('[highlight]Category page:[end_highlight] On top of the header of product listing page'),
				'class'=> 'display_hook',
			),
			'displayFooterAfter' => array(
				'id_option' => 'displayFooterAfter',
				'name' => $this->l('[highlight]Footer:[end_highlight] On the bottom of Footer section'),
			),
			'displayFooterCategory' => array(
				'id_option' => 'displayFooterCategory',
				'name'=> $this->l('[highlight]Category page:[end_highlight] On the bottom of product category page'),
				'class'=> 'display_hook',
			),
			'displayFooterBefore' => array(
				'id_option' => 'displayFooterBefore',
				'name' => $this->l('[highlight]Footer:[end_highlight] On top of Footer section'),
			),
			'displayProductListHeaderAfter' => array(
				'id_option' => 'displayProductListHeaderAfter',
				'name'=> $this->l('[highlight]Category page:[end_highlight] Under the header of product listing page'),
				'class'=> 'display_hook',
			),
			'displayRightColumnBefore' => array(
				'id_option' => 'displayBeforeRightColumn',
				'name' => $this->l('[highlight]Right column:[end_highlight] On the top of the right column')
			),
			'displayAfterProductThumbs' => array(
				'id_option' => 'displayAfterProductThumbs',
				'name' => $this->l('[highlight]Product page:[end_highlight] Under the product thumbnail images on product detail page'),
				'class'=> 'display_hook',
			),
			'displayRightColumn' => array(
				'id_option' => 'displayRightColumn',
				'name' => $this->l('[highlight]Right column:[end_highlight] On the bottom of the right column')
			),
			'displayProductCommentsListHeaderBefore' => array(
				'id_option' => 'displayProductCommentsListHeaderBefore',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] On top of %sProduct Comments%s block on product detail page'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayLeftColumnBefore' => array(
				'id_option' => 'displayBeforeLeftColumn',
				'name' => $this->l('[highlight]Left column:[end_highlight] On the top of the left column')
			),
			'displayProductVariantsAfter' => array(
				'id_option' => 'displayProductVariantsAfter',
				'name' => $this->l('[highlight]Product page:[end_highlight] On the bottom of the product combination block'),
				'class'=> 'display_hook',
			),
			'displayLeftColumn' => array(
				'id_option' => 'displayLeftColumn',
				'name' => $this->l('[highlight]Left column:[end_highlight] On the bottom of the left column')
			),
			'displayProductAdditionalInfo' => array(
				'id_option' => 'displayProductAdditionalInfo',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] On bottom of %sSocial sharing%s block on product detail page'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyBefore1' => array(
				'id_option' => 'displayCartGridBodyBefore1',
				'name' => sprintf($this->l('[highlight]Cart page:[end_highlight] On the top of shopping cart detail on %sShopping cart%s page'),'"','"'),
			),
			'displayFooterProduct' => array(
				'id_option' => 'displayFooterProduct',
				'name' => $this->l('[highlight]Product page:[end_highlight] Under the product description section'),
				'class'=> 'display_hook',
			),
			'displayShoppingCartFooter' => array(
				'id_option' => 'displayShoppingCartFooter',
				'name' => $this->l('[highlight]Cart page:[end_highlight] On the bottom of shopping cart detail'),
			),
			'displayProductVariantsBefore' => array(
				'id_option' => 'displayProductVariantsBefore',
				'name' => $this->l('[highlight]Product page:[end_highlight] On top of the product combination block'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyBefore2' => array(
				'id_option' => 'displayCartGridBodyBefore2',
				'name' => $this->l('[highlight]Checkout page:[end_highlight] On top of the checkout page')
			),
			'displayReassurance' => array(
				'id_option' => 'displayReassurance',
				'name' => sprintf($this->l('[highlight]Product page:[end_highlight] Under the %sCustomer reassurance%s block'),'"','"'),
				'class'=> 'display_hook',
			),
			'displayCartGridBodyAfter' => array(
				'id_option' => 'displayCartGridBodyAfter',
				'name' => $this->l('[highlight]Checkout page:[end_highlight] On the bottom of the checkout page')
			),
			'displayBanner' => array(
				'id_option' => 'displayBanner',
				'name' => $this->l('[highlight]On top of the homepage banner[end_highlight]')
			),
			'displayHome' => array(
				'id_option' => 'displayHome',
				'name' => $this->l('[highlight]Home page[end_highlight]')
			),


		);
		$version = (string)_PS_VERSION_;
		$version = (string)Tools::substr($version, 0, 7);
		$version = str_replace('.', '', $version);
		$version = (int)$version;
		if($version <= 0)
		{
			unset($positions['displayProductListHeaderBefore']);
			unset($positions['displayProductListHeaderAfter']);
		}
		if($version<1770)
		{
			unset($positions['displayFooterCategory']);
		}
		if($version < 1700)
		{
			unset($positions['displayNav1']);
			unset($positions['displayFooterBefore']);
			unset($positions['displayFooterAfter']);
			unset($positions['displayCartGridBodyBefore1']);
			unset($positions['displayReassurance']);
		}
		if($version < 1710)
		{
			unset($positions['displayAfterProductThumbs']);
		}
		if($version < 1760)
		{
//            unset($positions['displayProductActions']);
			unset($positions['displayProductCommentsListHeaderBefore']);
		}
		return $positions;
	}
    public static function getBannersByPosition($position)
    {
        $sql = 'SELECt * FROM `'._DB_PREFIX_.'ets_baw_banner` b
        LEFT JOIN `'._DB_PREFIX_.'ets_baw_banner_lang` bl ON (b.id_ets_baw_banner = bl.id_ets_baw_banner AND bl.id_lang="'.(int)Context::getContext()->language->id.'")
        LEFT JOIN `'._DB_PREFIX_.'ets_baw_banner_position` bp ON (bp.id_ets_baw_banner = b.id_ets_baw_banner)
        WHERE b.active=1 AND bp.position = "'.pSQL($position).'" ORDER BY bp.sort ASC';
        return Db::getInstance()->executeS($sql);
    }
    public static function getBannerById($id_banner)
    {
        $sql = 'SELECt * FROM `'._DB_PREFIX_.'ets_baw_banner` b
        LEFT JOIN `'._DB_PREFIX_.'ets_baw_banner_lang` bl ON (b.id_ets_baw_banner = bl.id_ets_baw_banner AND bl.id_lang="'.(int)Context::getContext()->language->id.'")
        WHERE b.active=1 AND b.id_ets_baw_banner="'.(int)$id_banner.'"';
        return Db::getInstance()->getRow($sql);
    }
    public static function getListBanners($sort_order, $filter='',$sort='',$start=0,$limit=10,$total=false)
    {
        $id_lang = (int)Context::getContext()->language->id;  
        if($total)
            $sql ='SELECT COUNT(DISTINCT b.id_ets_baw_banner) FROM `'._DB_PREFIX_.'ets_baw_banner` b';
        else
            $sql ='SELECT DISTINCT b.id_ets_baw_banner,b.*,bl.*'.($sort_order ? ',bp.sort as sort_order':'').' FROM `'._DB_PREFIX_.'ets_baw_banner` b';
        $sql .=' LEFT JOIN `'._DB_PREFIX_.'ets_baw_banner_lang` bl ON (b.id_ets_baw_banner=bl.id_ets_baw_banner AND bl.id_lang="'.(int)$id_lang.'")
        LEFT JOIN `'._DB_PREFIX_.'ets_baw_banner_position` bp ON (bp.id_ets_baw_banner = b.id_ets_baw_banner)
        WHERE 1 '.($filter ? (string)$filter: '');
        if($total)
            return Db::getInstance()->getValue($sql);
        else
        {
            $sql .=($sort ? ' ORDER BY '.$sort: ' ORDER BY b.id_ets_baw_banner DESC').($limit ? ' LIMIT '.(int)$start.','.(int)$limit.'':'');
            return Db::getInstance()->executeS($sql);
        }
    }
    public function delete()
    {
    	$result = parent::delete();
        if($result)
        {
            if($this->image)
            {
                foreach($this->image as $image)
                {
                    if($image)
                        @unlink(_PS_ETS_BAW_IMG_DIR_.$image);
                }
            }
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_baw_banner_position` WHERE id_ets_baw_banner="'.(int)$this->id.'"');
        }
        return $result;
    }
    public function getMaxSortByPosition($position)
    {
        return Db::getInstance()->getValue('SELECT MAX(sort) FROM `'._DB_PREFIX_.'ets_baw_banner_position` WHERE position="'.pSQL($position).'"');
    }
    public function addPosition($add = true)
    {
        $positions = Tools::getValue('position');
        if($positions)
        {
            $sql ='INSERT INTO '._DB_PREFIX_.'ets_baw_banner_position(id_ets_baw_banner,position,sort) VALUES ';
            $values = '';
            foreach($positions as $position)
            {
                if(Validate::isCleanHtml($position))
                {
                    if($add || !Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ets_baw_banner_position` WHERE id_ets_baw_banner="'.(int)$this->id.'" AND position="'.pSQL($position).'"'))
                    {
                        $sort = 1 + $this->getMaxSortByPosition($position);
                        $values .='("'.(int)$this->id.'","'.pSQL($position).'","'.(int)$sort.'"),';
                    }
                }
            }
            if($values)
            {
                Db::getInstance()->execute($sql.trim($values,','));
            }
        }
        if(!$add)
        {
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_baw_banner_position` WHERE id_ets_baw_banner="'.(int)$this->id.'" '.($positions ? ' AND position NOT IN ("'.implode('","',array_map('pSQL',$positions)).'")' :''));
        }
    }
    public function add($auto_date=true,$null_values=false)
    {
    	$add = parent::add($auto_date,$null_values);
        if($add)
        {
            $this->addPosition(true);
        }
        return $add;
    }
    public function update($null_values=false)
    {
    	$update = parent::update($null_values);
        if($update)
        {
            if(!Tools::isSubmit('change_enabled'))
                $this->addPosition(false);
        }
        return $update;
    }
    public static function getPositionBYId($id_banner)
    {
        if($id_banner)
        {
            return Db::getInstance()->getValue('SELECT GROUP_CONCAT(position) FROM `'._DB_PREFIX_.'ets_baw_banner_position` WHERE id_ets_baw_banner='.(int)$id_banner);
        }
        return '';
    }
    public function updatePosition($banners)
    {
        $page = (int)Tools::getValue('page',1);
        $paginator_banner_select_limit = (int)Tools::getValue('paginator_banner_select_limit',20);
        $display_position = Tools::getValue('display_position');
        if($display_position && Validate::isCleanHtml($display_position))
        {
            foreach($banners as $key=> $banner)
            {
                $position=  1+ $key + ($page-1)*$paginator_banner_select_limit;
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ets_baw_banner_position` SET sort="'.(int)$position.'" WHERE id_ets_baw_banner='.(int)$banner.' AND position="'.pSQL($display_position).'"');
            }
            die(
                json_encode(
                    array(
                        'page'=>$page,
                        'success' => $this->l('Updated successfully'),
                        'limit' => $paginator_banner_select_limit,
                    )
                )
            );
        }
        
    }
}