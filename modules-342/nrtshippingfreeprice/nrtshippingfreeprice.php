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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class NrtShippingFreePrice extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'nrtshippingfreeprice';
		$this->version = '1.0.1';
		$this->tab = 'front_office_features';
        $this->author = 'AxonVIZ';
		$this->bootstrap = true;
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Axon - Shipping Free Price');
        $this->description = $this->l('Required by author: AxonVIZ.');

        $this->templateFile = 'module:'.$this->name.'/views/templates/hook/shippingfreeprice.tpl';
    }
	
    public function install()
    {			
        return  parent::install() && $this->registerHook('displayNrtCartInfo');		
    }

    public function uninstall()
    {
        return  parent::uninstall();
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if ($this->context->cart->isVirtualCart()){
            return;
        }

        $result = $this->getWidgetVariables($hookName, $configuration);

        if ($result) {
            $this->smarty->assign($result);
            return $this->fetch($this->templateFile);
        } else {
            return;
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $hide = false;

        $free_ship_from = Tools::convertPrice(
            (float) Configuration::get('PS_SHIPPING_FREE_PRICE'),
            Currency::getCurrencyInstance((int) Context::getContext()->currency->id)
        );

        $currentShipping = Context::getContext()->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        if(!$currentShipping){
            return;
        }

        $tax_excluded_display = Group::getPriceDisplayMethod(Group::getCurrent()->id);

        if ($tax_excluded_display ){
            $total = Context::getContext()->cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING);
        } else{
            $total = Context::getContext()->cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        }

        if ($free_ship_from == 0 || ($free_ship_from - $total) <= 0) {
            return;
        }

        if (count(Context::getContext()->cart->getOrderedCartRulesIds(CartRule::FILTER_ACTION_SHIPPING))) {
            return;
        }

        $priceFormatter = new PriceFormatter();

        return array(
            'free_ship' => sprintf($this->l('Spend %1$s get free shipping!'), '<b class="price">' . $priceFormatter->format($free_ship_from - $total) . '</b>'),
        );
    }
}
