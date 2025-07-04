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

class NrtVariantActionsModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }
	
    public function postProcess()
    {
		parent::initContent();
		
		$tplProduct = (int)Tools::getValue('tplProduct');
		$imageType = Tools::getValue('imageType');
        $idProduct = (int)Tools::getValue('idProduct');
		$idProductAttribute = (int)Tools::getValue('idProductAttribute');

		$template = 'catalog/_partials/miniatures/_partials/_product/product-' . $tplProduct . '.tpl';

        $this->getProduct($idProduct, $idProductAttribute, $imageType);
		
		if($template){
			$template = $template;
		}else{
			$template = $this->module->l('No template found', 'actions');
		}

		header('Content-Type: application/json');
		
        $this->ajaxRender(json_encode(array(
            'success' => true,
            'data' => [
                'message' => $this->context->smarty->fetch($template)
            ]
        )));

		exit;
    }

	public function getProduct($id_product, $id_product_attribute, $imageType)	{
		
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
	
			if(in_array($id_product_attribute, $products_for_template['default_image']['associatedVariants'])){
				$products_for_template['cover'] = $products_for_template['default_image'];
			}
	
			$products_for_template['axs_variant'] = true;
			
			$this->context->smarty->assign(array(
				'product' => $products_for_template,
				'imageType' => $imageType
			));
		}	
	}
}
