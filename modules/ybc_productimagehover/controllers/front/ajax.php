<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class Ybc_productimagehoverAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    
    public $is17;
    public $is16;
    
    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        $this->is17 = version_compare(_PS_VERSION_, '1.7.0', '>=');
        $this->is16 = version_compare(_PS_VERSION_, '1.6.0', '>=');
        $list = array();
        if(($ids = explode(',',Tools::getValue('ids'))) && is_array($ids) && $ids){
            $temp = array();
            foreach($ids as $id) {
                if (!in_array((int)$id, $temp))
                    $temp[] = (int)$id;
            }
            if($temp){
                foreach ($temp as $idProduct){
                    $product = new Product($idProduct, null, $this->context->language->id);
                    if(!$product || !$product->id){
                        continue;
                    }
                    $idImgDefault = $this->getImageDefault($product);
                    $hoverImage = (int) Db::getInstance()->getValue("SELECT `id_image` FROM `"._DB_PREFIX_."image` WHERE id_image !=".(int)$idImgDefault." AND id_product=".(int)$idProduct);
                    if(!$hoverImage){
                        continue;
                    }
                    $image_rewrite = $this->context->link->getImageLink($product->link_rewrite, (int)$hoverImage, $this->is17 ? ImageType::getFormattedName('home') : ImageType::getFormatedName('home'));
                    $list[$idProduct] = '<img class="'.(Configuration::get('YBC_PI_TRANSITION_EFFECT')? Configuration::get('YBC_PI_TRANSITION_EFFECT'):'fade').' replace-2x img-responsive ybc_img_hover" src="'.$image_rewrite.'" alt="'.$product->name.'" itemprop="image" title="'.$product->name.'"/>';
                }
            }
        }

        die(json_encode($list));
    }
    public function getImageDefault($product)
    {
        $idLang = $this->context->language->id;
        $idProduct = $product->id;
        $idImageProduct = null;
        if($idDefaultAttribute = $product->getDefaultIdProductAttribute()){
            $images = Image::getImages($idLang, $idProduct, $idDefaultAttribute);
            if($images){
                foreach ($images as $img){
                    if($img['cover'] == 1){
                        $idImageProduct = $img['id_image'];
                        break;
                    }
                }
                if(!$idImageProduct){
                    $idImageProduct = $images[0];
                }
            }

        }
        else{
            $imageCover = Image::getCover($idProduct);
            if($imageCover && isset($imageCover['id_image'])){
                $idImageProduct = $imageCover['id_image'];
            }
            else{
                $images = $product->getImages($idLang);
                if($images && is_array($images)){
                    $idImageProduct = $images[0];
                }
            }
        }
        return (int)$idImageProduct;
    }
}