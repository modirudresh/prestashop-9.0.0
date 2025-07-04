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
 *  @version  Release: $Revision: 7060 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtProductsLinkNav extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'nrtproductslinknav';
		$this->version = '2.1.7';
		$this->tab = 'front_office_features';
        $this->author = 'AxonVIZ';
		$this->bootstrap = true;
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Axon - Next And Previouse Product Link');
        $this->description = $this->l('Show butttons to previouse or next product on product page');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
		
        $this->templateFile = 'module:nrtproductslinknav/views/templates/hook/nav.tpl';
    }

    public function install()
    {
        return (parent::install() && 
				$this->registerHook('displayProductsLinkNav') && 
				$this->registerHook('displayHeader') &&
                $this->registerHook('actionProductSave') &&
                $this->registerHook('actionProductDelete')
		);
    }

    public function uninstall()
    {
        return (parent::uninstall());
    }

    public function hookActionProductSave($params)
    {
        if(!isset($params['id_product'])){
            return;
        }
    }
	
    public function hookActionProductDelete($params)
    {
        if(!isset($params['id_product'])){
            return;
        }
    }
	
    public function hookDisplayHeader($params) {}

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
		
        if (!isset($configuration['smarty']->tpl_vars['product']->value['id_product']) || !isset($configuration['smarty']->tpl_vars['product']->value['id_category_default']) || $this->context->controller->php_self != 'product') {
            return;
        }
		
		$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));	

		return $this->fetch($this->templateFile);
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $id_product = (int) $configuration['smarty']->tpl_vars['product']->value['id_product'];
        $id_category = (int) $configuration['smarty']->tpl_vars['product']->value['id_category_default'];
		
        $links = $this->getLinksInCategory($id_product, $id_category);
		
        return $links;
    }

    public function getLinksInCategory($id_product, $id_category)
    {    
        $links = [];

        $row_position = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT position FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_category = ' . (int)$id_category . ' AND id_product = ' . (int)$id_product);
        $position = (int)$row_position['position'];

        $row_prev_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT cp.id_product FROM `' . _DB_PREFIX_ . 'category_product` cp RIGHT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = cp.id_product WHERE cp.id_category = ' . (int)$id_category . ' AND p.active = 1 AND cp.position < ' . (int)$position . ' ORDER BY cp.position DESC');
        $row_next_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT cp.id_product FROM `' . _DB_PREFIX_ . 'category_product` cp RIGHT JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = cp.id_product WHERE cp.id_category = ' . (int)$id_category . ' AND p.active = 1 AND cp.position > ' . (int)$position . ' ORDER BY cp.position ASC');

        $priceFormatter = new PriceFormatter();
        $imageRetriever = new ImageRetriever($this->context->link);

        if($row_prev_product){
            $rawProduct =  new Product((int)$row_prev_product['id_product'], true, (int)$this->context->language->id, (int)$this->context->shop->id, $this->context);
            if (Validate::isLoadedObject($rawProduct)) {
                $rawProduct->url = $rawProduct->getLink();
                $rawProduct->default_image = $imageRetriever->getImage($rawProduct, $rawProduct->getCoverWs());
                $rawProduct->price = $priceFormatter->convertAndFormat($rawProduct->price);
                $links['prev'] = (array)$rawProduct;
            }
        }

        if($row_next_product){
            $rawProduct =  new Product((int)$row_next_product['id_product'], true, (int)$this->context->language->id, (int)$this->context->shop->id, $this->context);
            if (Validate::isLoadedObject($rawProduct)) {
                $rawProduct->url = $rawProduct->getLink();
                $rawProduct->default_image = $imageRetriever->getImage($rawProduct, $rawProduct->getCoverWs());
                $rawProduct->price = $priceFormatter->convertAndFormat($rawProduct->price);
                $links['next'] = (array)$rawProduct;
            }
        }

        return $links;
    }	
}
