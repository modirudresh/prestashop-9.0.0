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
class NrtMegaProductClass
{
    public static function deleteMenuProducts($id)
    {
        if(!$id)
            return false;
        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'nrt_mega_product` WHERE `id_nrt_mega_menu` = '.(int)$id);
    }

    public static function changeMenuProducts($id, $menu_products_id)
    {
        if(!$id)
            return false;
        $res = true;
        if (!$id_shop = (int)Shop::getContextShopID())
            return false;
        foreach ($menu_products_id as $id_product)
        {
            $exists = Db::getInstance()->getValue('
            SELECT `id_product` FROM `'._DB_PREFIX_.'product_shop`
            WHERE `id_shop` = '.$id_shop.'
            AND `id_product` = '.(int)$id_product.'
            ');
            if ($exists)
                $res &= Db::getInstance()->insert('nrt_mega_product', array(
                    'id_nrt_mega_menu' => (int)$id,
                    'id_product' => (int)$id_product
                 )); 
        }
            
        return $res;
    }
    public static function getMenuProductsLight($id_lang, $id_nrt_mega_menu)
    {
        $sql = 'SELECT p.`id_product`, p.`reference`, pl.`name`
                FROM `'._DB_PREFIX_.'nrt_mega_product` mp
                LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product`= mp.`id_product`)
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                    p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
                )
                WHERE `id_nrt_mega_menu` = '.(int)$id_nrt_mega_menu;

        return Db::getInstance()->executeS($sql);
    }
    public static function getMenuProducts($id_lang, $id_nrt_mega_menu)
    {
        $sql = 'SELECT `id_product`
                FROM `'._DB_PREFIX_.'nrt_mega_product`
                WHERE `id_nrt_mega_menu` = '.(int)$id_nrt_mega_menu;

        return Db::getInstance()->executeS($sql);
    }
    public static function deleteByIdProduct($id_product = 0)
    {
        if (!$id_product)
            return false;
        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'nrt_mega_product` WHERE `id_product` = '.(int)$id_product);
    }

}