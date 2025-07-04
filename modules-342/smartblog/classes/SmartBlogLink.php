<?php
/*
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @copyright  2007-2015 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class SmartBlogLink
{
    public function __construct()
    {				
		
        if (!defined('_PS_BASE_URL_')) {
            define('_PS_BASE_URL_', Tools::getShopDomain(true));
        }
		
        if (!defined('_PS_BASE_URL_SSL_')) {
            define('_PS_BASE_URL_SSL_', Tools::getShopDomainSsl(true));
        }
		
    }

    /**
     * Returns a link to a blog image for display
     * Note: the new image filesystem stores blog images in subdirectories of img/p/
     *
     * @param string $name rewrite link of the image
     * @param string $ids id part of the image filename - can be "id_blog-id_image" (legacy support, recommended) or "id_image" (new)
     * @param string $type
     */
    public static function getImageLink($name, $ids, $type = null)
    {
		$protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';  
		//$allow = (int) Configuration::get('PS_REWRITING_SETTINGS');
		
		$allow = 0;

        // legacy mode or default image
        $theme = ((Shop::isFeatureActive() && file_exists(_MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . '-' . (int) Context::getContext()->shop->id . '.jpg')) ? '-' . Context::getContext()->shop->id : '');
		
        if ((Configuration::get('PS_LEGACY_IMAGES') && (file_exists(_MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg')))) {
            if ($allow == 1) {
                $uri_path = __PS_BASE_URI__ . 'blog/' . $ids . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
				$img_dir = _MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
            } else {
				$uri_path = __PS_BASE_URI__ . 'modules/smartblog/images/' . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
				$img_dir = _MODULE_SMARTBLOG_DIR_ . $ids . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
        } else {
            // if ids if of the form id_blog-id_image, we want to extract the id_image part
            $split_ids = explode('-', $ids);
            $id_image = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);
            $theme = ((Shop::isFeatureActive() && file_exists(_MODULE_SMARTBLOG_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . ($type ? '-' . $type : '') . '-' . (int) Context::getContext()->shop->id . '.jpg')) ? '-' . Context::getContext()->shop->id : '');
            if ($allow == 1) {
                $uri_path = __PS_BASE_URI__ . 'blog/' . $id_image . ($type ? '-' . $type : '') . $theme . '/' . $name . '.jpg';
				$img_dir = _MODULE_SMARTBLOG_DIR_ . $id_image . ($type ? '-' . $type : '') . $theme . '.jpg';
            } else { 
                $uri_path = __PS_BASE_URI__ . 'modules/smartblog/images/' . $id_image . ($type ? '-' . $type : '') . $theme . '.jpg';
				$img_dir = _MODULE_SMARTBLOG_DIR_ . $id_image . ($type ? '-' . $type : '') . $theme . '.jpg';
            }
        }

        if (file_exists($img_dir)) {
            $return_val = $protocol_content . Tools::getMediaServer($uri_path) . $uri_path;
        } else {
            $split_ids = explode('-', $ids);
            $id_image = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);
            
            $tmp_main_img = __PS_BASE_URI__ . 'modules/smartblog/images/'.$id_image.'.jpg';
			$img_main_dir = _MODULE_SMARTBLOG_DIR_ . $id_image . '.jpg';
            if(file_exists($img_main_dir)){
                $posts_types = BlogImageType::GetImageAllType('post');
                foreach ($posts_types as $image_type) {
                    if(stripslashes($image_type['type_name']) == $type){
                        ImageManager::resize(__DIR__."/../images/".$id_image.".jpg", _PS_MODULE_DIR_ . 'smartblog/images/'.$id_image.'-' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']);
                    }
                }
            } else {
                if(Configuration::get('smartshownoimg')){
                    $no_img = __PS_BASE_URI__ . 'modules/smartblog/images/no.jpg';
                    if ($allow == 1) {
                        $return_val = __PS_BASE_URI__ . 'blog/' . 'no' . ($type ? '-' . $type : '') . '/' . $name . '.jpg';
						$return_val_dir = _MODULE_SMARTBLOG_DIR_ . 'blog/' . 'no' . ($type ? '-' . $type : '') . '.jpg';
                    } else {
                        $return_val = __PS_BASE_URI__ . 'modules/smartblog/images/no' . ($type ? '-' . $type : '') . '.jpg';
						$return_val_dir = _MODULE_SMARTBLOG_DIR_ . 'modules/smartblog/images/no' . ($type ? '-' . $type : '') . '.jpg';
                    }
                    $return_val = $protocol_content . Tools::getMediaServer($return_val) . $return_val;
                    if (file_exists($return_val_dir)) {
                        $posts_types = BlogImageType::GetImageAllType('post');

                        foreach ($posts_types as $image_type) {
                            if(stripslashes($image_type['type_name']) == $type){
                                ImageManager::resize(__DIR__."/../images/no.jpg", _PS_MODULE_DIR_ . 'smartblog/images/no-' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']);

                            }
                        }
                    }
                } else {
                    $return_val = "false";
                }
            }
        }

        return (isset($return_val))? $return_val : '';
    }

    public static function getSmartBlogPostLink($id_post, $rewrite = null)
    {		
		return smartblog::GetSmartBlogLink('smartblog_post_rule', array('id_post' => $id_post, 'rewrite' => $rewrite));
    }

    public static function getSmartBlogCategoryLink($id_category, $rewrite = null)
    {			
        return smartblog::GetSmartBlogLink('smartblog_category_rule', array('id_blog_category' => $id_category, 'rewrite' => $rewrite));
    }
	        
}
