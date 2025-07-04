<?php
/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class BlogPostCategory extends ObjectModel
{
    public $id_smart_blog_category;
    public $id_smart_blog_post;
    public static $definition = array(
        'table' => 'smart_blog_post_category',
        'primary' => 'id_smart_blog_post',
        'multilang' => false,
        'fields' => array(
            'id_smart_blog_category' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_smart_blog_post' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt')
        ),
    );

    public static function getToltalByCategory($id_lang, $id_category, $limit_start, $limit)
    {
        $result = array();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_lang pl INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post p ON pl.id_smart_blog_post=p.id_smart_blog_post INNER JOIN
                ' . _DB_PREFIX_ . 'smart_blog_post_category pc ON p.id_smart_blog_post=pc.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . ' and p.active = 1 AND pc.id_smart_blog_category = ' . $id_category . '
                ORDER BY p.id_smart_blog_post DESC LIMIT ' . (int)$limit_start . ',' . (int)$limit;

        if (!$posts = Db::getInstance()->executeS($sql))
            return false;

        return $posts;
    }

}