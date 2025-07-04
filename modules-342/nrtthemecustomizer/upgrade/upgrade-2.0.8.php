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

function upgrade_module_2_0_8($object)
{	
	$response = true;
	
	$configs = array(
		// General Options
		'general_main_layout' => array('before' => 'nrt_themect_general_main_layout', 'value' => 'wide'),
		'general_affix_scroll' => array('before' => 'nrt_themect_general_affix_scroll', 'value' => 1),
		'general_product_image_type_small' => array('before' => 'nrt_themect_orther_product_image_type', 'value' => 'cart_default'),
		'general_product_image_type_large' => array('before' => 'nrt_themect_orther_product_large_image_type', 'value' => 'home_default'),
		'general_container_max_width' => array('before' => 'nrt_themect_general_container_max_width', 'value' => '100%'),
		'general_column_space' => array('before' => 'nrt_themect_general_column_space', 'value' => 30),
		// Hompage Options
		'index_open_vertical_menu' => array('before' => 'open_vertical_menu_on_index', 'value' => 0),
		'index_header_layout' => array('before' => 'header_layout_on_index', 'value' => 'inherit'),
		'index_header_sticky_layout' => array('before' => 'header_sticky_layout_on_index', 'value' => 'inherit'),
		'index_header_overlap' => array('before' => 'header_overlap_on_index', 'value' => 0),
		'index_footer_layout' => array('before' => 'footer_layout_on_index', 'value' => 'inherit'),
		// Contact Options
		'contact_override_content_by_hook' => array('before' => 'override_content_by_hook_on_contact', 'value' => 1),
		'contact_header_layout' => array('before' => 'header_layout_on_contact', 'value' => 'inherit'),
		'contact_header_sticky_layout' => array('before' => 'header_sticky_layout_on_contact', 'value' => 'inherit'),
		'contact_header_overlap' => array('before' => 'header_overlap_on_contact', 'value' => 0),
		'contact_footer_layout' => array('before' => 'footer_layout_on_contact', 'value' => 'inherit'),
		'contact_page_title_layout' => array('before' => 'page_title_layout_on_contact', 'value' => 'inherit'),
		// Contact Options
		'404_override_content_by_hook' => array('before' => 'smarty', 'value' => 1),
		'404_header_layout' => array('before' => 'default', 'value' => 'inherit'),
		'404_header_sticky_layout' => array('before' => 'default', 'value' => 'inherit'),
		'404_header_overlap' => array('before' => 'default', 'value' => 0),
		'404_footer_layout' => array('before' => 'default', 'value' => 'inherit'),
		'404_page_title_layout' => array('before' => 'default', 'value' => 'inherit'),
		// Footer Options
		'general_footer_fixed' => array('before' => 'nrt_themect_footer_fixed', 'value' => 0),
		// Page title
		'page_title_layout' => array('before' => 'nrt_themect_page_title_layout', 'value' => 1),
		'bg_page_title_img' => array('before' => 'nrt_themect_bg_page_title_img', 'value' => ''),
		'page_title_color' => array('before' => 'nrt_themect_page_title_color', 'value' => 'dark'),
		// Category Page Options
		'category_header_layout' => array('before' => 'header_layout_on_category', 'value' => 'inherit'),
		'category_header_sticky_layout' => array('before' => 'header_sticky_layout_on_category', 'value' => 'inherit'),
		'category_header_overlap' => array('before' => 'header_overlap_on_category', 'value' => 0),
		'category_footer_layout' => array('before' => 'footer_layout_on_category', 'value' => 'inherit'),
		'category_page_title_layout' => array('before' => 'page_title_layout_on_category', 'value' => 'inherit'),
		'category_show_sub' => array('before' => 'nrt_themect_category_show_sub', 'value' => 0),
		'category_default_view' => array('before' => 'nrt_themect_category_default_view', 'value' => 1),
		'category_banner_layout' => array('before' => 'nrt_themect_category_banner_layout', 'value' => 1),
		'category_image_type' => array('before' => 'nrt_themect_category_image_type', 'value' => ''),
		'category_product_infinite' => array('before' => 'nrt_themect_category_product_infinite', 'value' => 1),
		'category_faceted_position' => array('before' => 'nrt_themect_category_faceted_position', 'value' => 1),
		'category_layout_width_type' => array('before' => 'nrt_themect_category_layout_width_type', 'value' => 'container'),
		'category_layout' => array('before' => 'nrt_themect_category_layout', 'value' => 1),
		'category_product_layout' => array('before' => 'nrt_themect_category_product_layout', 'value' => 1),
		'category_product_image_type' => array('before' => 'nrt_themect_category_product_image_type', 'value' => ''),
		'category_product_xl' => array('before' => 'nrt_themect_category_product_xl', 'value' => 4),
		'category_product_lg' => array('before' => 'nrt_themect_category_product_lg', 'value' => 4),
		'category_product_md' => array('before' => 'nrt_themect_category_product_md', 'value' => 3),
		'category_product_xs' => array('before' => 'nrt_themect_category_product_xs', 'value' => 2),
		'category_product_space_xl' => array('before' => 'nrt_themect_category_product_space_xl', 'value' => 30),
		'category_product_space_lg' => array('before' => 'nrt_themect_category_product_space_lg', 'value' => 30),
		'category_product_space_md' => array('before' => 'nrt_themect_category_product_space_md', 'value' => 30),
		'category_product_space_xs' => array('before' => 'nrt_themect_category_product_space_xs', 'value' => 30),
		// Product Page Options
		'product_header_layout' => array('before' => 'header_layout_on_product', 'value' => 'inherit'),
		'product_header_sticky_layout' => array('before' => 'header_sticky_layout_on_product', 'value' => 'inherit'),
		'product_footer_layout' => array('before' => 'footer_layout_on_product', 'value' => 'inherit'),
		'product_layout_width_type' => array('before' => 'nrt_themect_product_layout_width_type', 'value' => 'container'),
		'product_layout' => array('before' => 'nrt_themect_product_layout', 'value' => 1),
		'product_image_type' => array('before' => 'nrt_themect_product_image_type', 'value' => ''),
		'product_image_thumb_type' => array('before' => 'nrt_themect_product_image_thumb_type', 'value' => ''),
		'product_tabs_type' => array('before' => 'nrt_themect_product_tabs_type', 'value' => 1),
		// Font Options
		'font_gg_cyrillic' => array('before' => 'nrt_themect_font_gg_cyrillic', 'value' => 0),
		'font_gg_greek' => array('before' => 'nrt_themect_font_gg_greek', 'value' => 0),
		'font_gg_vietnamese' => array('before' => 'nrt_themect_font_gg_vietnamese', 'value' => 0),
		'font_body' => array('before' => 'nrt_themect_font_body', 'value' => 'None'),
		'font_title' => array('before' => 'nrt_themect_font_title', 'value' => 'None'),
		'font_size_lg' => array('before' => 'nrt_themect_font_size_lg', 'value' => 62.5),
		'font_size_xs' => array('before' => 'nrt_themect_font_size_xs', 'value' => 62.5),
		// Color Options
		'color_scheme_dark' => array('before' => '', 'value' => 0),
		'color_primary' => array('before' => 'nrt_themect_color_primary', 'value' => ''),
		'color_price' => array('before' => 'nrt_themect_color_price', 'value' => ''),
		'color_new_label' => array('before' => 'nrt_themect_color_new_label', 'value' => ''),
		'color_sale_label' => array('before' => 'nrt_themect_color_sale_label', 'value' => ''),
		// Background Options
		'background_color' => array('before' => 'nrt_themect_bg_color', 'value' => ''),
		'background_img' => array('before' => 'nrt_themect_bg_img', 'value' => ''),
		'background_img_repeat' => array('before' => 'nrt_themect_bg_img_repeat', 'value' => 'repeat'),
		'background_img_attachment' => array('before' => 'nrt_themect_bg_img_attachment', 'value' => 'scroll'),
		'background_img_size' => array('before' => 'nrt_themect_bg_img_size', 'value' => 'auto'),
		'background_body_color' => array('before' => 'nrt_themect_bg_body_color', 'value' => ''),
		'background_body_img' => array('before' => 'nrt_themect_bg_body_img', 'value' => ''),
		'background_body_img_repeat' => array('before' => 'nrt_themect_bg_body_img_repeat', 'value' => 'repeat'),
		'background_body_img_attachment' => array('before' => 'nrt_themect_bg_body_img_attachment', 'value' => 'scroll'),
		'background_body_img_size' => array('before' => 'nrt_themect_bg_body_img_size', 'value' => 'auto'),
		// Input Button Label
		'input_style' => array('before' => '', 'value' => 'rectangular'),
		'input_border_width' => array('before' => '', 'value' => 1),
		'button_style' => array('before' => '', 'value' => 'flat'),
		'button_border_width' => array('before' => '', 'value' => 1),
		'product_label' => array('before' => '', 'value' => 'rectangular'),
		//Style
		'style_on_theme' => array('before' => 'nrt_themect_style_on_theme', 'value' => '{}'),
		// Custom Codes
		'custom_css' => array('before' => 'nrt_themect_custom_css', 'value' => ''),
		'custom_js' => array('before' => 'nrt_themect_custom_js', 'value' => ''),
	 );	
	
	if( Configuration::get('opThemect') ){
		$opThemect = Configuration::get('opThemect');
		$opThemect = json_decode($opThemect, true);
	}else{
		$opThemect = [];
	}

	foreach ($configs as $key => $config) {
		$opThemect[$key] =  ($config['before']) ? Configuration::get($config['before']) : $config['value'];
	}

	$response &= Configuration::updateValue('opThemect', json_encode($opThemect));
	
    return $response;
}
