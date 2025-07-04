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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class NrtWishlistActionsModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Tools::getValue('process') == 'add') {
            $this->processAdd();
        } elseif (Tools::getValue('process') == 'remove') {
            $this->processRemove();
        } elseif (Tools::getValue('process') == 'removeAll') {
            $this->processRemoveAll();
        }
    }

    public function processAdd()
    {
        header('Content-Type: application/json');

        $idProduct = (int)Tools::getValue('idProduct');
        $idProductAttribute = (int)Tools::getValue('idProductAttribute');

        if (!$this->context->customer->isLogged() && Configuration::get('nrt_wishlist_allow_guests')) {
            $productsIds = $this->context->cookie->nrtWishList;
		
            if ($productsIds) {
                $productsIds = json_decode($productsIds, true);
            }else{
                $productsIds = array();
            }
                            
            $id_lang = (int)Context::getContext()->language->id;
            $idShop = (int)$this->context->shop->id;
            
            $product =  new Product($idProduct, true, $id_lang, $idShop, $this->context);
    
            $template = 'module:' . $this->module->name . '/views/templates/hook/' . 'notices.tpl';
            
            if (!Validate::isLoadedObject($product)) {
                $this->ajaxRender(json_encode(array(
                    'is_logged' => true,
                    'productsIds' => $productsIds,
                    'notices' => $this->context->smarty->fetch($template)
                )));
                exit;
            }
    
            $this->getProduct($idProduct, $idProductAttribute);

            if (!in_array($idProduct.'-'.$idProductAttribute, $productsIds)) {
                $productsIds[] = $idProduct.'-'.$idProductAttribute;
            }
    
            $this->context->cookie->__set('nrtWishList', json_encode($productsIds, true));

            $this->ajaxRender(json_encode(array(
                'is_logged' => true,
                'productsIds' => $productsIds,
                'notices' => $this->context->smarty->fetch($template)
            )));
            exit;
        }

        if (!$this->context->customer->isLogged()) {
            $this->ajaxRender(json_encode(array(
                'is_logged' => false
            )));
            exit;
        }
		
        $productsIds = NrtWishlistProduct::getWishlistProductsIds((int)$this->context->customer->id);
		
        $idCustomer = (int)$this->context->customer->id;
        $idShop = (int)$this->context->shop->id;
        $idLang = (int)$this->context->language->id;

        $product = new Product($idProduct, false, $idLang, $idShop, $this->context);

        if (!Validate::isLoadedObject($product)) {
            $template = 'module:' . $this->module->name . '/views/templates/hook/' . 'notices.tpl';

            $this->ajaxRender(json_encode(array(
                'is_logged' => true,
                'productsIds' => $productsIds,
                'notices' => $this->context->smarty->fetch($template)
            )));

            exit;
        }else if (NrtWishlistProduct::getIdWishlistProduct($idCustomer, (int)$product->id, $idProductAttribute)) {
            $template = 'module:' . $this->module->name . '/views/templates/hook/' . 'notices.tpl';

            $this->getProduct($idProduct, $idProductAttribute);
            
            $this->ajaxRender(json_encode(array(
                'is_logged' => true,
                'productsIds' => $productsIds,
                'notices' => $this->context->smarty->fetch($template)
            )));

            exit;
        } else {
            $obj = new NrtWishlistProduct();
            $obj->id_product = $idProduct;
            $obj->id_customer = $idCustomer;
            $obj->id_product_attribute = $idProductAttribute;
            $obj->id_shop = $idShop;

            if ($obj->add()) {
				$productsIds = NrtWishlistProduct::getWishlistProductsIds((int)$this->context->customer->id);

                $template = 'module:' . $this->module->name . '/views/templates/hook/' . 'notices.tpl';

                $this->getProduct($idProduct, $idProductAttribute);

                $this->ajaxRender(json_encode(array(
                    'is_logged' => true,
                	'productsIds' => $productsIds,
                    'notices' => $this->context->smarty->fetch($template)
                )));

                exit;
            }
        }
    }
	
    public function processRemove()
    {
        header('Content-Type: application/json');

        $idProduct = (int)Tools::getValue('idProduct');
        $idProductAttribute = (int)Tools::getValue('idProductAttribute');

        if (!$this->context->customer->isLogged() && Configuration::get('nrt_wishlist_allow_guests')) {
            $productsIds = $this->context->cookie->nrtWishList;
		
            if ($productsIds) {
                $productsIds = json_decode($productsIds, true);
            }else{
                $productsIds = array();
            }
            
            $restIds = array();
            
            foreach ($productsIds as $key => $product) {
                if ($idProduct.'-'.$idProductAttribute != $product) {
                    $restIds[] = $product;
                }
            }
    
            $this->context->cookie->__set('nrtWishList', json_encode($restIds, true));
    
            $this->ajaxRender(json_encode(array(
                'is_logged' => true,
                'productsIds' => $restIds
            )));

            exit;
        }

        if (!$this->context->customer->isLogged()) {
            $this->ajaxRender(json_encode(array(
                'is_logged' => false,
            )));
            exit;
        }
		
        $idCustomer = (int)$this->context->customer->id;
		
		$id_wishlist_product = NrtWishlistProduct::getIdWishlistProduct($idCustomer, $idProduct, $idProductAttribute);
		
        $wishlistProduct = new NrtWishlistProduct((int)$id_wishlist_product);
        $wishlistProduct->delete();
		
        $productsIds = NrtWishlistProduct::getWishlistProductsIds($idCustomer);
		
        $this->ajaxRender(json_encode(array(
            'is_logged' => true,
            'productsIds' => $productsIds
        )));

        exit;
    }

    public function processRemoveAll()
    {
        header('Content-Type: application/json');

        if (!$this->context->customer->isLogged() && Configuration::get('nrt_wishlist_allow_guests')) {
            $productsIds = array();
            $this->context->cookie->__set('nrtWishList', json_encode($productsIds, true));
            
            $this->ajaxRender(json_encode(array(
                'is_logged' => true,
                'productsIds' => $productsIds
            )));

            exit;
        }

        if (!$this->context->customer->isLogged()) {
            $this->ajaxRender(json_encode(array(
                'is_logged' => false,
            )));

            exit;
        }

        $idCustomer = (int)$this->context->customer->id;

        $wlProducts = NrtWishlistProduct::getWishlistProducts((int)$idCustomer);

		foreach($wlProducts as $item){
            $idProduct = (int)$item['id_product'];
            $idProductAttribute = (int)$item['id_product_attribute'];
            
            $id_wishlist_product = NrtWishlistProduct::getIdWishlistProduct($idCustomer, $idProduct, $idProductAttribute);
            
            $wishlistProduct = new NrtWishlistProduct((int)$id_wishlist_product);
            $wishlistProduct->delete();
		}

        $productsIds = NrtWishlistProduct::getWishlistProductsIds($idCustomer);
		
		$this->ajaxRender(json_encode(array(
            'is_logged' => true,
			'productsIds' => $productsIds
		)));

        exit;
    }

    public function getProduct($id_product, $id_product_attribute)	{
		
		$id_lang = (int)Context::getContext()->language->id;
		$idShop = (int)$this->context->shop->id;
		
		$product =  new Product($id_product, true, $id_lang, $idShop, $this->context);

		if (Validate::isLoadedObject($product)) {
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
            $products_for_template = $presenter->present(
                $presentationSettings,
				$assembler->assembleProduct([
					'id_product' => $id_product,
					'id_product_attribute' => $id_product_attribute,
				]),
                $this->context->language
            );
            
            $this->context->smarty->assign(array(
                'product' => $products_for_template,
            ));
		}
	}
}
