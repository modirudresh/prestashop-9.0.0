<?php
/*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class NrtReviewProduct extends ObjectModel
{
    public $id_nrt_review_product;
    public $id_product;
    public $id_customer;
    public $id_guest;
    public $customer_name;
    public $title;
    public $comment;
    public $image;
    public $rating;
    public $active;
    public $fulness;
	public $no_fulness;
    public $date_add;
	
    public static $definition = array(
        'table' => 'nrt_review_product',
        'primary' => 'id_nrt_review_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_guest' => array('type' => self::TYPE_INT),
            'customer_name' => array('type' => self::TYPE_STRING, 'size' => 255),
            'title' => array('type' => self::TYPE_STRING, 'size' => 255, 'required' => true),
            'comment' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 65535, 'required' => true),
			'image' => array('type' => self::TYPE_HTML, 'validate' => 'isJson'),
            'rating' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'fulness' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'no_fulness' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE),
        ),
    );

    public static function deleteReviewsProduct($idProduct)
    {
        if (!Validate::isUnsignedInt($idProduct)) {
            return;
        }
		
		$sql = 'DELETE FROM `' ._DB_PREFIX_. 'nrt_review_product` WHERE id_product = ' .(int) $idProduct;
		
		return Db::getInstance()->execute($sql);
    }

    public static function getByCustomer($idProduct, $idCustomer, $getLast = false, $idGuest = false)
    {
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_review_product` pr WHERE pr.`id_product` = '.(int)$idProduct.' AND '.(!$idGuest ? ' pr.`id_customer` = '.(int)$idCustomer : ' pr.`id_guest` = '.(int) $idGuest).' ORDER BY pr.`date_add` DESC '.($getLast ? ' LIMIT 1' : '');
		
		$results = Db::getInstance()->executeS($sql);
		
		if ($getLast && count($results)) {
			$results = array_shift($results);
		}
        return $results;
    }

    public static function getByProduct($idProduct, $limit_start = 0, $limit = 5)
    {
        if (!$limit_start){
            $limit_start = 0;
		}

        if (!$limit){
            $limit = 5;
		}

		$sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_review_product` pr WHERE pr.`id_product` = '.(int)$idProduct.' AND pr.`active` = 1 ORDER BY pr.`date_add` DESC LIMIT ' . $limit_start . ',' . $limit;
		
        return Db::getInstance()->executeS($sql);
    }

    public static function getAvgReviews($idProduct)
    {		
		$sql = 'SELECT (SUM(pr.`rating`) / COUNT(pr.`rating`)) AS avg, COUNT(pr.`rating`) as nbr FROM `'._DB_PREFIX_.'nrt_review_product` pr WHERE pr.`active` = 1  AND pr.`id_product` = '.(int)$idProduct;

        return Db::getInstance()->getRow($sql);
    }

    public static function getHasFulness($idProduct)
    {		
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_review_product` WHERE `active` = 1  AND `id_product` = '.(int)$idProduct.' AND `fulness` = 1';

        return count(Db::getInstance()->executeS($sql));
    }
	
    public static function getNotFulness($idProduct)
    {		
		$sql = 'SELECT * FROM `'._DB_PREFIX_.'nrt_review_product` WHERE `active` = 1  AND `id_product` = '.(int)$idProduct.' AND `fulness` = 0';

        return count(Db::getInstance()->executeS($sql));
    }
}
