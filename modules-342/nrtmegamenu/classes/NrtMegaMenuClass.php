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

class NrtMegaMenuClass extends ObjectModel
{
	/** @var integer banner id*/
    public $id;
	public $location;
    public $id_parent;
	public $id_nrt_mega_column;
	public $id_shop;
    public $level_depth;
    public $item_k;
    public $item_v;
	public $subtype;
	public $new_window;
	public $position;
	public $active;
    public $txt_color;
	public $link_color;
	public $txt_color_over;
	public $bg_color;
	public $bg_color_over;
	public $tab_content_bg;
	public $html;
	public $title;
	public $link;
	public $auto_sub;
	public $hide_on_mobile;
	public $alignment;
    public $nofollow;
    public $custom_class;
    public $width;
    public $is_mega;
    public $icon_class;
    public $sub_levels;
    public $sub_limit;
    public $item_limit;
    public $items_md;
    public $item_t;
    public $cate_label;
    public $cate_label_color;
    public $cate_label_bg;
    public $show_cate_img;
    public $bg_image;
    public $bg_repeat;
    public $bg_position;
    public $bg_margin_bottom;
    public $granditem;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table'     => 'nrt_mega_menu',
		'primary'   => 'id_nrt_mega_menu',
		'multilang' => true,
		'fields' => array(
            'location'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_nrt_mega_column'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_parent'       => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'id_shop'         => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'level_depth'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'item_k'     => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'item_v'                   => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isGenericName'),
			'subtype'           => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'new_window'      => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
			'position'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'active'          => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'auto_sub'        => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
			'hide_on_mobile'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'alignment'       => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'nofollow'        => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'custom_class'      => array('type' => self::TYPE_STRING, 'size' => 255, 'validate' => 'isGenericName'),
            'txt_color'       => array('type' => self::TYPE_STRING, 'size' => 7),
			'link_color'       => array('type' => self::TYPE_STRING, 'size' => 7),
			'txt_color_over'  => array('type' => self::TYPE_STRING, 'size' => 7),
			'bg_color'        => array('type' => self::TYPE_STRING, 'size' => 7),
            'bg_color_over'   => array('type' => self::TYPE_STRING, 'size' => 7),
            'tab_content_bg'  => array('type' => self::TYPE_STRING, 'size' => 7),
            'width'       => array('type' => self::TYPE_STRING, 'size' => 7),
            'is_mega'  => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
			'icon_class' 	  => array('type' => self::TYPE_HTML, 'validate' => 'isJson'),
            'sub_levels'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'sub_limit'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'item_limit'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'items_md'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'item_t'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'cate_label_color'  => array('type' => self::TYPE_STRING, 'size' => 7),
            'cate_label_bg'  => array('type' => self::TYPE_STRING, 'size' => 7),
            'show_cate_img'  => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'bg_image'      => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
            'bg_repeat'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'bg_position'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'bg_margin_bottom'        => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'granditem'  => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),

			// Lang fields
			'html'            => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'title'           => array('type' => self::TYPE_STRING, 'lang' => true,  'size' => 255, 'validate' => 'isGenericName'),
			'cate_label'           => array('type' => self::TYPE_STRING, 'lang' => true,  'size' => 255, 'validate' => 'isGenericName'),
			'link'            => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 255, 'validate' => 'isCleanHtml'),
		)
	);

    public function delete()
    {
        $sub = self::recurseTree($this->id,2,$this->level_depth,0);
        if($sub && count($sub))
		  $this->deleteRec($sub);

		if($res = parent::delete())
            $this->clearExtraItems($this->id);
        
        return $res;
    }

    public function deleteRec($sub)
    {
        foreach($sub as $v)
        {
            if(isset($v['children']) && $v['children'] && count($v['children']))
                $this->deleteRec($v['children']);
            Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'nrt_mega_menu`
			WHERE `id_nrt_mega_menu`='.(int)$v['id_nrt_mega_menu']);
            Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'nrt_mega_menu_lang`
			WHERE `id_nrt_mega_menu`='.(int)$v['id_nrt_mega_menu']);
            
            $this->clearExtraItems($v['id_nrt_mega_menu']);
        }
        
    }
    
    public function clearExtraItems($id_mega_menu = 0)
    {
        if ($id_mega_menu > 0)
        {
            NrtMegaColumnClass::deleteByMenu((int)$id_mega_menu);
            NrtMegaProductClass::deleteMenuProducts((int)$id_mega_menu);
            NrtMegaBrandClass::deleteByMenu((int)$id_mega_menu);
        }
    }

    public static function justifyTree(&$tree, &$n)
	{
		$left = $n++;
		foreach ($tree as $v)
		{
            if(isset($v['children']) && is_array($v['children']) && count($v['children']))
                self::justifyTree($v['children'], $n);
            
            $right = (int)$n++;
    		Db::getInstance()->execute('
    			UPDATE '._DB_PREFIX_.'nrt_mega_menu
    			SET nleft = '.(int)$left.', nright = '.(int)$right.'
    			WHERE id_nrt_mega_menu = '.(int)$v['id_nrt_mega_menu']);
	    }
	}
    
    public static function getById($id_nrt_mega_menu,$id_lang)
    {
        return Db::getInstance()->getRow('
            SELECT smm.*,smml.`html`,smml.`title`,smml.`link`,smml.`cate_label`
            FROM `'._DB_PREFIX_.'nrt_mega_menu` smm
            LEFT JOIN `'._DB_PREFIX_.'nrt_mega_menu_lang` smml ON smm.`id_nrt_mega_menu`=smml.`id_nrt_mega_menu`
            WHERE smml.`id_lang`='.(int)$id_lang.
            ' AND smm.`id_nrt_mega_menu`='.(int)$id_nrt_mega_menu.
            ' AND smm.`id_shop` IN (0, '.(int)Shop::getContextShopID().')'
        );
    }
	
    public static function getSub($id_parent,$active,$id_lang,$item_t)
    {
        return Db::getInstance()->executeS('
            SELECT smm.*,smml.`html`,smml.`title`,smml.`link`,smml.`cate_label`
            FROM `'._DB_PREFIX_.'nrt_mega_menu` smm
            LEFT JOIN `'._DB_PREFIX_.'nrt_mega_menu_lang` smml ON smm.`id_nrt_mega_menu`=smml.`id_nrt_mega_menu`
            WHERE smml.`id_lang`='.(int)$id_lang.
            ' AND smm.`id_parent`='.(int)$id_parent.
            ' AND smm.`item_t`='.(int)$item_t.
            ($active ? ' AND smm.`active`=1 ' : '').
            ' AND smm.`id_shop` IN (0, '.(int)Shop::getContextShopID().') 
            ORDER BY smm.`location`, smm.`position`');
    }

    public static function getByColumnId($id_nrt_mega_column, $id_lang, $active=0,$item_t=0,$id_parent=0)
    {
        $res = Db::getInstance()->executeS('
            SELECT smm.*,smml.`html`,smml.`title`,smml.`link`,smml.`cate_label`
            FROM `'._DB_PREFIX_.'nrt_mega_menu` smm
            LEFT JOIN `'._DB_PREFIX_.'nrt_mega_menu_lang` smml ON smm.`id_nrt_mega_menu`=smml.`id_nrt_mega_menu`
            WHERE smml.`id_lang`='.(int)$id_lang.
            ' AND smm.`id_nrt_mega_column`='.(int)$id_nrt_mega_column.
            ' AND smm.`id_parent`='.($id_parent).
            ($item_t ? ' AND smm.`item_t`='.$item_t : ' AND smm.`item_t`>0').
            ($active ? ' AND smm.`active`=1 ' : '').
            ' AND smm.`id_shop` IN (0, '.(int)Shop::getContextShopID().') 
            ORDER BY smm.`position`');
        return $res;
    }
    public static function recurseTree($id_parent,$max_depth=2,$current_depth=0,$active=0,$id_lang = null,$item_t=0)
    {
        $id_lang = is_null($id_lang) ? Context::getContext()->language->id : (int)$id_lang;

		if (!(int)$id_lang)
			$id_lang = _USER_ID_LANG_;
        $tree = self::getSub($id_parent,$active,$id_lang,$item_t);
        if ( ( $max_depth==0 || ($current_depth+1 < $max_depth) ) && $tree && count($tree))
            foreach($tree as &$v)
            {
                $jon = self::recurseTree($v['id_nrt_mega_menu'],$max_depth,$current_depth+1,$active,$id_lang,$item_t);
                if(is_array($jon) && count($jon))
                    $v['children'] = $jon;
            }

        return $tree;
    }
    public static function getTypes()
    {
        $module = new NrtMegaMenu();
		return array(
			1 => $module->l('Category'),
			2 => $module->l('Product'),
			3 => $module->l('CMS'),
			4 => $module->l('Manufacturer'),
			5 => $module->l('Supplier'),
			6 => $module->l('Shop'),
			7 => $module->l('Link'),
			8 => $module->l('CMS category'),
			9 => $module->l('ICON'),
			10 => $module->l('Blog category'),
			11 => $module->l('Blog'),
			12 => $module->l('Permanent link'),
		);
    }
    public static function getTopParent($id_nrt_mega_menu)
    {
        $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
        if($menu->id_parent)
            return NrtMegaMenuClass::getTopParent($menu->id_parent);
        else
            return $id_nrt_mega_menu;
    }
    public static function getSecondaryParent($id_nrt_mega_menu)
    {
        $menu = new NrtMegaMenuClass($id_nrt_mega_menu);
        if($menu->level_depth > 0)
            return NrtMegaMenuClass::getSecondaryParent($menu->id_parent);
        else
            return $id_nrt_mega_menu;
    }
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (property_exists($this, $key) && $key != 'id_'.$this->table && !isset($_FILES[$key]))
				$this->{$key} = $value;
		/* Multilingual fields */
        $fieldsLang = ['html', 'title', 'cate_label', 'link'];

        $languages = Language::getLanguages(false);
        foreach ($languages AS $language)
            foreach ($fieldsLang AS $field)
                if (isset($_POST[$field.'_'.(int)($language['id_lang'])]) && !isset($_FILES[$field.'_'.(int)($language['id_lang'])]))
                    $this->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];

	}
    public static function getCustomCss()
    {
        return Db::getInstance()->executeS('
            SELECT *
            FROM `'._DB_PREFIX_.'nrt_mega_menu`
            WHERE (`txt_color`!="" || `link_color`!="" || `txt_color_over`!="" || `bg_color`!="" || `bg_color_over`!="" || `tab_content_bg`!="" || `bg_image`!="" || `bg_margin_bottom`!="" || `cate_label_color`!="" || `cate_label_bg`!="")');
    }
    public static function deleteByColumn($id_nrt_mega_column=0)
    {
        if (!$id_nrt_mega_column)
            return false;
        $res = Db::getInstance()->executeS('
            SELECT `id_nrt_mega_menu`
            FROM `'._DB_PREFIX_.'nrt_mega_menu`
            WHERE `id_nrt_mega_column` = '.(int)$id_nrt_mega_column.'
        ');
        $ret = true;
        foreach($res AS $value)
        {
            $menu = new NrtMegaMenuClass($value['id_nrt_mega_menu']);
            $ret &= $menu->delete();
        }
        return $ret;
    }
}