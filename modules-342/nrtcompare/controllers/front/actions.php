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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class NrtCompareActionsModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Tools::getValue('process') == 'remove') {
            $this->processRemove();
        } elseif (Tools::getValue('process') == 'add') {
            $this->processAdd();
        } elseif (Tools::getValue('process') == 'removeAll') {
            $this->processRemoveAll();
        }
    }

    public function processRemove()
    {
        header('Content-Type: application/json');
		
        $idProduct = (int)Tools::getValue('idProduct');
		$idProductAttribute = (int)Tools::getValue('idProductAttribute');
		
        $productsIds = $this->context->cookie->nrtCompareN;
		
		if ($productsIds) {
			$productsIds = json_decode($productsIds, true);
		}else{
			$productsIds = array();
		}
		
		if (($key = array_search($idProduct.'-'.$idProductAttribute, $productsIds)) !== false) {
			unset($productsIds[$key]);
		}

		$productsIds = array_merge($productsIds);

        $this->context->cookie->__set('nrtCompareN', json_encode($productsIds, true));

		$this->ajaxRender(json_encode(array(
			'productsIds' => $productsIds
		)));

		exit;
    }

    public function processRemoveAll()
    {
        header('Content-Type: application/json');

        $productsIds = array();
        $this->context->cookie->__set('nrtCompareN', json_encode($productsIds, true));
		
		$this->ajaxRender(json_encode(array(
			'productsIds' => $productsIds
		)));

		exit;
    }

    public function processAdd()
    {
        header('Content-Type: application/json');

        $idProduct = (int)Tools::getValue('idProduct');
		$idProductAttribute = (int)Tools::getValue('idProductAttribute');

        $productsIds = $this->context->cookie->nrtCompareN;
		
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
				'productsIds' => $productsIds,
				'notices' => $this->context->smarty->fetch($template)
			)));
			exit;
		}

        $this->getProduct($idProduct, $idProductAttribute);

        if (!in_array($idProduct.'-'.$idProductAttribute, $productsIds)) {
            $productsIds[] = $idProduct.'-'.$idProductAttribute;
        }

		$this->context->cookie->__set('nrtCompareN', json_encode($productsIds, true));

		$this->ajaxRender(json_encode(array(
			'productsIds' => $productsIds,
            'notices' => $this->context->smarty->fetch($template)
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
