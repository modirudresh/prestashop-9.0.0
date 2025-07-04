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

use AxonCreator\Wp_Helper;

function upgrade_module_1_0_2($object)
{	
	require_once _PS_MODULE_DIR_   . 'axoncreator/src/Wp_Helper.php';
	
	$response = true;
	
	$post_langs = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'axon_creator_post_lang');
	
	foreach ($post_langs as $post_lang) {					
		$response &= Wp_Helper::delete_post_meta( (int) $post_lang['id_axon_creator_post'], '_elementor_css_id_lang_' . $post_lang['id_lang'] );
	}

	$response &= Wp_Helper::delete_post_meta_by_key('_elementor_scheme_last_updated');
	
	$response &= Wp_Helper::delete_post_meta_by_key('_elementor_global_css');

    return $response;
}
