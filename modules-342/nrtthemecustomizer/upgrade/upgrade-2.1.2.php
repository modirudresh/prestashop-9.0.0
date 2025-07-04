<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_2($object)
{	
	$response = true;

	// First check for parent tab
	$parentTabID = Tab::getIdFromClassName('AdminMenuFirst');
	$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');

	if ($parentTabID && $parentTab_2ID) {
		$parentTab = new Tab($parentTabID);
		$parentTab_2 = new Tab($parentTab_2ID);
	}

	if( !Tab::getIdFromClassName('AdminNrtCustomFonts') ) {
		// Created tab
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = "AdminNrtCustomFonts";
		$tab->name = array();
		foreach (Language::getLanguages() as $lang) {
			$tab->name[$lang['id_lang']] = "- Custom Fonts";
		}
		$tab->id_parent = $parentTab_2->id;
		$tab->module = 'nrtthemecustomizer';
		$response &= $tab->add();
	}
	
	$response &= $object->_createTables();

	return $response;
}
