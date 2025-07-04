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

class NrtMegaBrandClass
{
    public static function deleteByMenu($id_nrt_mega_menu)
    {
    	if(!$id_nrt_mega_menu)
    		return false;
        return Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'nrt_mega_brand WHERE `id_nrt_mega_menu`='.(int)$id_nrt_mega_menu);
    }
    public static function getByMenu($id_nrt_mega_menu)
    {
    	if(!$id_nrt_mega_menu)
    		return false;
        return Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'nrt_mega_brand WHERE `id_nrt_mega_menu`='.(int)$id_nrt_mega_menu);
    }

    public static function changeMenuBrands($id, $menu_brands_id)
    {
        if(!$id)
            return false;
        $res = true;
        foreach ($menu_brands_id as $id_brand)
            $res &= Db::getInstance()->insert('nrt_mega_brand', array(
                'id_nrt_mega_menu' => (int)$id,
                'id_manufacturer' => (int)$id_brand
            ));
        return $res;
    }


    public static function getMenuBrandsLight($id_lang, $id_nrt_mega_menu)
	{
		$sql = 'SELECT m.*, ml.`description`, ml.`short_description`
			FROM `'._DB_PREFIX_.'nrt_mega_brand` stb
            LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = stb.`id_manufacturer` 
			LEFT JOIN `'._DB_PREFIX_.'manufacturer_lang` ml ON (
				m.`id_manufacturer` = ml.`id_manufacturer`
				AND ml.`id_lang` = '.(int)$id_lang.'
			)
			'.Shop::addSqlAssociation('manufacturer', 'm');
			$sql .= ' WHERE stb.`id_nrt_mega_menu` = '.$id_nrt_mega_menu.' AND m.`active` = 1';

		$manufacturers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		if ($manufacturers === false)
			return false;

		return $manufacturers;
	}

    public static function getMenuBrands($id_lang, $id_nrt_mega_menu)
    {
    	$manufacturers = self::getMenuBrandsLight($id_lang, $id_nrt_mega_menu);
    	if ($manufacturers === false)
			return false;
		
		$total_manufacturers = count($manufacturers);

		for ($i = 0; $i < $total_manufacturers; $i++)
        {
            $manufacturers[$i]['url'] = Context::getContext()->link->getManufacturerLink($manufacturers[$i]['id_manufacturer']);
			$manufacturers[$i]['image'] = Context::getContext()->link->getManufacturerImageLink($manufacturers[$i]['id_manufacturer']);
        }
		return $manufacturers;
    }
}