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

class NrtWishlistViewModuleFrontController extends ModuleFrontController
{	
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
		
		$page['page_name'] = 'view-wishlist';
		
        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            $this->context->shop->theme->getLayoutNameForPage('module-nrtwishlist-view') => true,
            'page-view-wishlist' => true,
            'tax-display-'.($this->getDisplayTaxesLabel() ? 'enabled' : 'disabled') => true,
        );
				
		$page['body_classes'] = $body_classes;
		
        $page['meta']['title'] = $this->module->l('My wishlist', 'view');

        return $page;
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        if ($this->context->customer->isLogged() && !Tools::getValue('token')) {
            $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        }

        $breadcrumb['links'][] = [
            'title' => $this->module->l('My wishlist', 'view'),
            'url' => $this->context->link->getModuleLink('nrtwishlist', 'view')
        ];

        return $breadcrumb;
    }


    public function initContent()
    {
        parent::initContent();

		$idLang = (int)$this->context->language->id;
		$idShop = (int)$this->context->shop->id;
		
        $readOnly = true;
		
        $token = '';

        $encryptedValue = Tools::getValue('token');

        if($encryptedValue){
            $engine = new PhpEncryption(_NEW_COOKIE_KEY_);
            $idCustomer = (int) $engine->decrypt($encryptedValue);

            if((int) $idCustomer == (int) $this->context->customer->id){
                $token = $engine->encrypt((string)$idCustomer);
                $readOnly = false;
            }

            $wlProducts = NrtWishlistProduct::getWishlistProducts($idCustomer);
        } else {
            if ($this->context->customer->isLogged()) {
                $engine = new PhpEncryption(_NEW_COOKIE_KEY_);
                $idCustomer = (string)$this->context->customer->id;
                $token = $engine->encrypt($idCustomer);
                $readOnly = false;

                $wlProducts = NrtWishlistProduct::getWishlistProducts($idCustomer);
            } else {
                if (Configuration::get('nrt_wishlist_allow_guests')) {
                    $idCustomer = 0;
                    $readOnly = false;

                    $wlProducts = array();

                    $productsIds = $this->context->cookie->nrtWishList;
                
                    if ($productsIds) {
                        $productsIds = json_decode($productsIds, true);
        
                        foreach ($productsIds as $key=>$productsId) {
                            $product = explode('-', $productsId);

                            if (Validate::isLoadedObject(new Product((int)$product[0], true, $idLang, $idShop, $this->context))) {
                                $wlProducts[] = ['id_product' => (int)$product[0], 'id_product_attribute' => (int)$product[1]];
                            }else{
                                unset($productsIds[$key]);
                                $this->context->cookie->__set('nrtWishList', json_encode(array_merge($productsIds), true));
                            }
                        }
                    }
                } else {
                    Tools::redirect('index.php?controller=authentication&back=my-account');
                }
            }
        }
		
		$products = array();
		
		foreach($wlProducts as $item){
			if((int)$item['id_product']){
				$product = new Product((int)$item['id_product'], true, $idLang, $idShop, $this->context);
				if (Validate::isLoadedObject($product)) {
                    $products[]= [
                        'id_product' => (int)$item['id_product'],
                        'id_product_attribute' => (int)$item['id_product_attribute'],
                    ];
				}
			}
		}

        $this->context->smarty->assign(array(
            'wlProducts' => $this->convertProducts($products),
            'token' => $token,
            'readOnly' => $readOnly,
        ));

        $this->setTemplate('module:nrtwishlist/views/templates/front/view.tpl');
    }

	public function convertProducts($products)	
	{		
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
		$products_for_template = [];
		if(is_array($products)){
			foreach ($products as $rawProduct) {
				$product = $presenter->present(
					$presentationSettings,
					$assembler->assembleProduct($rawProduct),
					$this->context->language
				);
				$products_for_template[] = $product;				
			}
		}
		return 	$products_for_template;
	}
}
