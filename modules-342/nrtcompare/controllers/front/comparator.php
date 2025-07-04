<?php
/*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*/

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class NrtCompareComparatorModuleFrontController extends ModuleFrontController
{		
    public $ssl = true;
	
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
		
		$page['page_name'] = 'view-compare';
		
        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            $this->context->shop->theme->getLayoutNameForPage('module-nrtcompare-view') => true,
            'page-view-compare' => true,
            'tax-display-'.($this->getDisplayTaxesLabel() ? 'enabled' : 'disabled') => true,
        );
				
		$page['body_classes'] = $body_classes;
		
        $page['meta']['title'] = $this->module->l('My Compare', 'comparator');

        return $page;
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->module->l('My Compare', 'comparator'),
            'url' => $this->context->link->getModuleLink('nrtcompare', 'comparator')
        ];

        return $breadcrumb;
    }

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        parent::initContent();
		
		$this->setProductsComparison();
		
		$this->setTemplate('module:nrtcompare/views/templates/front/comparator.tpl');
    }
	
    public function setProductsComparison()
    {
        $list_products = array();
        $compareProducts = array();
        $ordered_features = array();
        $product_features = array();
		$list_ids_product = array();
		
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;
        $productsIds = $this->context->cookie->nrtCompareN;
		
        if ($productsIds) {
            $productsIds = json_decode($productsIds, true);
			
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();

            $assembler = new ProductAssembler($this->context);
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            foreach ($productsIds as $key=>$productsId) {
				$product = explode('-', $productsId);
				$idProduct = (int)$product[0];
				$idProductAttribute = (int)$product[1];
				$list_ids_product[$key]['id_product'] = $idProduct;
				$list_ids_product[$key]['id_product_attribute'] = $idProductAttribute;
                $product =  new Product($idProduct, true, $idLang, $idShop, $this->context);

                if (Validate::isLoadedObject($product)) {				
					$presentedProduct = $presenter->present(
						$presentationSettings,
						$assembler->assembleProduct([
                            'id_product' => $idProduct,
                            'id_product_attribute' => $idProductAttribute,
                        ]),
						$this->context->language
					);
									
					$list_products[] = $presentedProduct;
	
					foreach ($presentedProduct['features'] as $feature) {
						$product_features[$presentedProduct['id_product']][$feature['id_feature']] = $feature['value'];
					}
				}else{
                    unset($productsIds[$key]);
                    $this->context->cookie->__set('nrtCompareN', json_encode(array_merge($productsIds), true));
                }
            }

            $ordered_features = $this->getFeaturesForComparison($productsIds, $idLang);
        }

        $this->context->smarty->assign(array(
            'list_products' => $list_products,
            'ordered_features' => $ordered_features,
            'product_features' => $product_features,
			'list_ids_product' => $list_ids_product
        ));		
    }

    public function getFeaturesForComparison($productsIds, $idLang)
    {
        if (!Feature::isFeatureActive()) {
            return false;
        }

        $ids = '';
        foreach ($productsIds as $productsId) {
			$product = explode('-', $productsId);
            $ids .= (int)$product[0].',';
        }

        $ids = rtrim($ids, ',');

        if (empty($ids)) {
            return false;
        }

        return Db::getInstance()->executeS('
			SELECT f.*, fl.*
			FROM `'._DB_PREFIX_.'feature` f
			LEFT JOIN `'._DB_PREFIX_.'feature_product` fp
				ON f.`id_feature` = fp.`id_feature`
			LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl
				ON f.`id_feature` = fl.`id_feature`
			WHERE fp.`id_product` IN ('.$ids.')
			AND `id_lang` = '.(int)$idLang.'
			GROUP BY f.`id_feature`
			ORDER BY f.`position` ASC
		');
    }
}
