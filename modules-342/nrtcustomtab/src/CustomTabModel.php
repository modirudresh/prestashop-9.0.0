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

class CustomTabModel extends ObjectModel
{
	public $title_bo;
	public $title;
	public $description;
	public $active;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'nrtcustomtab_detail',
		'primary' => 'id_nrtcustomtab_detail',
		'multilang' => true,
		'fields' => array(
			'active' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'title_bo' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			// Lang fields
			'title' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'description' =>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'required' => true),
		)
	);

	public	function __construct($id_slide = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
		parent::__construct($id_slide, $id_lang, $id_shop);
	}

	public function add($autodate = true, $null_values = false)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$res = parent::add($autodate, $null_values);
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'nrtcustomtab` (`id_shop`, `id_nrtcustomtab_detail`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}

	public function delete()
	{
		$res = true;

		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'nrtcustomtab`
			WHERE `id_nrtcustomtab_detail` = '.(int)$this->id
		);

		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'nrtcustomtab_product`
			WHERE `id_customtab` = '.(int)$this->id
		);

		$res &= parent::delete();
		return $res;
	}

	public static function getProductCustomTab($id_product)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_customtab`
			FROM '._DB_PREFIX_.'nrtcustomtab_product 
			WHERE id_product = '.(int)$id_product
		);
	}

	public static function assignProduct($id_product, $id_customtab)
	{
		$res = true;
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'nrtcustomtab_product` (`id_product`, `id_customtab`) 
			VALUES('.(int)$id_product.', \''.json_encode($id_customtab).'\') ON DUPLICATE KEY UPDATE id_customtab=VALUES(id_customtab)'
		);

		return $res;
	}

	public static function unassignProduct($id_product)
	{
		$res = true;

		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'nrtcustomtab_product`
			WHERE `id_product` = '.(int)$id_product
		);

		return $res;
	}


}
