<?php
/*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtShoppingcart extends Module implements WidgetInterface
{
	
    protected $updateOperationError = array();
	
    public function __construct()
    {
        $this->name = 'nrtshoppingcart';
        $this->tab = 'front_office_features';
        $this->version = '2.3.3';
        $this->author = 'AxonVIZ';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();
		$this->displayName = $this->l('Axon - Shopping Cart');
		$this->description = $this->l('Adds a block containing the customer\'s shopping cart.');	
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->controllers = array('ajax');
    }

    public function install()
    {
        return
            parent::install()
                && $this->registerHook('displayBeforeBodyClosingTag')
                && $this->registerHook('displayButtonCartNbr')
                && $this->registerHook('displayHeaderMobileRight')
                && $this->registerHook('displayHeader')
                && Configuration::updateValue('PS_BLOCK_CART_AJAX', 1)
				&& Configuration::updateValue('NRT_CART_ACTION_AFTER_ADD', 'canvas')
				&& $this->_createTab()	
        ;
    }
	
    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab();
    }
	
	public function _createTab()
    {
        $response = true;

        // First check for parent tab
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        }
        else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminMenuFirst";
            foreach (Language::getLanguages() as $lang) {
                $parentTab->name[$lang['id_lang']] = "AXON - MODULES";
            }
            $parentTab->id_parent = 0;
            $parentTab->module ='';
            $response &= $parentTab->add();
        }
		// Check for parent tab2
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		if ($parentTab_2ID) {
			$parentTab_2 = new Tab($parentTab_2ID);
		}
		else {
			$parentTab_2 = new Tab();
			$parentTab_2->active = 1;
			$parentTab_2->name = array();
			$parentTab_2->class_name = "AdminMenuSecond";
			foreach (Language::getLanguages() as $lang) {
				$parentTab_2->name[$lang['id_lang']] = "Modules";
			}
			$parentTab_2->id_parent = $parentTab->id;
			$parentTab_2->module = '';
			$parentTab_2->icon = 'build';
			$response &= $parentTab_2->add();
		}
		// Created tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminNrtShoppingCart";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Shopping Cart";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    public function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtShoppingCart');
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        $tab = new Tab($id_tab);
        $tab->delete();
		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		$tabCount_2 = Tab::getNbTabs($parentTab_2ID);
        if ($tabCount_2 == 0) {
            $parentTab_2 = new Tab($parentTab_2ID);
            $parentTab_2->delete();
        }
        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0) {
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }
	
    public function hookDisplayHeader()
    {
        if (Configuration::isCatalogMode()) {
            return;
        }

		$this->context->controller->registerJavascript('modules-nrtshoppingcart', 'modules/'.$this->name.'/views/js/shoppingcart.min.js', ['position' => 'bottom', 'priority' => 150]);
		Media::addJsDef(array(
			'opShoppingCart' => array(
				'has_ajax' => (bool)Configuration::get('PS_BLOCK_CART_AJAX'),
				'ajax' => $this->context->link->getModuleLink('nrtshoppingcart', 'ajax', array(), null, null, null, true),
				'action_after' => Configuration::get('NRT_CART_ACTION_AFTER_ADD'),
			)
		));
    }

    private function getCartSummaryURL()
    {
        return $this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            array(
                'action' => 'show'
            ),
            false,
            null,
            true
        );
    }

    public function getWidgetVariables($hookName, array $params)
    {
        $errors = [];

        $isAvailable = $this->areProductsAvailable();

        if (true !== $isAvailable) {
            $errors[] = $isAvailable;
        }

        return array(
            'errors' => $errors,
            'cart' => $this->getPresentedCart(),
            'cart_url' => $this->getCartSummaryURL(),
			'has_ajax' => (bool)Configuration::get('PS_BLOCK_CART_AJAX'),
			'icon' => isset($params['icon']) ? $params['icon'] : '',
            'is_ajax_cart' => isset($params['is_ajax_cart'])
        );
    }

    private function getPresentedCart()
    {
        if (!empty($this->context->smarty->getTemplateVars('cart'))) {
            return $this->context->smarty->getTemplateVars('cart');
        } else {
            return (new CartPresenter())->present($this->context->cart);
        }
    }

    public function renderWidget($hookName, array $params)
    {
        if (Configuration::isCatalogMode()) {
            return;
        }

        $this->smarty->assign($this->getWidgetVariables($hookName, $params));
		
        if ($hookName == 'displayBeforeBodyClosingTag') {
			if((bool)Configuration::get('PS_BLOCK_CART_AJAX')){
				return $this->fetch('module:nrtshoppingcart/views/templates/hook/shoppingcart-canvas.tpl');
			}
		}else{
            $priceFormatter = new PriceFormatter();

            $this->smarty->assign(array(
                'default_cart_amount' => $priceFormatter->format(0)
            ));

        	return $this->fetch('module:nrtshoppingcart/views/templates/hook/shoppingcart.tpl');
		}
    }

    public function renderModal($id_product, $id_product_attribute, $id_customization)
    {
        $data = $this->getPresentedCart();
        $product = null;
        foreach ($data['products'] as $p) {
            if ((int) $p['id_product'] == $id_product &&
                (int) $p['id_product_attribute'] == $id_product_attribute &&
                (int) $p['id_customization'] == $id_customization) {
                $product = $p;
                break;
            }
        }

        $this->smarty->assign(array(
            'product' => $product,
            'cart' => $data,
            'cart_url' => $this->getCartSummaryURL(),
        ));

        return $this->fetch('module:nrtshoppingcart/views/templates/hook/modal.tpl');
    }

    public function renderNotices($id_product, $id_product_attribute, $id_customization)
    {
        $data = $this->getPresentedCart();
        $product = null;
        foreach ($data['products'] as $p) {
            if ((int) $p['id_product'] == $id_product &&
                (int) $p['id_product_attribute'] == $id_product_attribute &&
                (int) $p['id_customization'] == $id_customization) {
                $product = $p;
                break;
            }
        }

        $this->smarty->assign(array(
            'product' => $product,
            'cart' => $data,
            'cart_url' => $this->getCartSummaryURL(),
        ));

        return $this->fetch('module:nrtshoppingcart/views/templates/hook/notices.tpl');
    }

    /**
     * This process delete a product from the cart.
     */
    public function emptyCart()
    {
        $cart = (new CartPresenter())->present($this->context->cart);
        foreach ($cart['products'] as $p) {
			$id_product = (int) $p['id_product'];
			$id_product_attribute = (int) $p['id_product_attribute'];
			$id_customization = (int) $p['id_customization'];
            $id_address_delivery = (int) $p['id_address_delivery'];
			$this->processDeleteProductInCart($id_product, $id_product_attribute, $id_customization, $id_address_delivery);
        }
	}
	
    /**
     * This process delete a product from the cart.
     */
    public function processDeleteProductInCart($id_product, $id_product_attribute, $id_customization, $id_address_delivery)
    {
        $customization_product = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'customization`'
            . ' WHERE `id_cart` = ' . (int) $this->context->cart->id
            . ' AND `id_product` = ' . (int) $id_product
            . ' AND `id_customization` != ' . (int) $id_customization
            . ' AND `in_cart` = 1'
            . ' AND `quantity` > 0'
        );

        if (count($customization_product)) {
            $product = new Product((int) $id_product);
            if ($id_product_attribute > 0) {
                $minimal_quantity = (int) ProductAttribute::getAttributeMinimalQty($id_product_attribute);
            } else {
                $minimal_quantity = (int) $product->minimal_quantity;
            }

            $total_quantity = 0;
            foreach ($customization_product as $custom) {
                $total_quantity += $custom['quantity'];
            }

            if ($total_quantity < $minimal_quantity) {
                $this->errors[] = sprintf($this->l('You must add %quantity% minimum quantity'), $minimal_quantity);
                return false;
            }
        }

        $data = array(
            'id_cart' => (int) $this->context->cart->id,
            'id_product' => (int) $id_product,
            'id_product_attribute' => (int) $id_product_attribute,
            'customization_id' => (int) $id_customization,
            'id_address_delivery' => (int) $id_address_delivery,
        );

        Hook::exec('actionObjectProductInCartDeleteBefore', $data, null, true);

        if ($this->context->cart->deleteProduct(
            $id_product,
            $id_product_attribute,
            $id_customization,
            $id_address_delivery
        )) {
            Hook::exec('actionObjectProductInCartDeleteAfter', $data);

            if (!Cart::getNbProducts((int) $this->context->cart->id)) {
                $this->context->cart->setDeliveryOption(null);
                $this->context->cart->gift = 0;
                $this->context->cart->gift_message = '';
                $this->context->cart->update();
            }

            $isAvailable = $this->areProductsAvailable();
            if (true !== $isAvailable) {
                $this->updateOperationError[] = $isAvailable;
            }
        }

        CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
    }
	
    /**
     * Check if the products in the cart are available.
     *
     * @return bool|string
     */
    public function areProductsAvailable()
    {
        $products = $this->context->cart->getProducts();

        foreach ($products as $product) {
            $currentProduct = new Product();
            $currentProduct->hydrate($product);

            if ($currentProduct->hasAttributes() && $product['id_product_attribute'] === '0') {
                return sprintf($this->l('The item %s in your cart is now a product with attributes. Please delete it and choose one of its combinations to proceed with your order.'), $product['name']);
            }
        }

        $product = $this->context->cart->checkQuantities(true);

        if (true === $product || !is_array($product)) {
            return true;
        }

        if ($product['active']) {
            return sprintf($this->l('You can only buy %1$s "%2$s". Please adjust the quantity in your cart to continue.'), $product['quantity_available'], $product['name']);
        }

        return sprintf($this->l('This product (%s) is no longer available.'), $product['name']);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBlockCart')) {
            $ajax = Tools::getValue('PS_BLOCK_CART_AJAX');
            if ($ajax != 0 && $ajax != 1) {
                $output .= $this->displayError($this->l('Ajax: Invalid choice.'));
            } else {
                Configuration::updateValue('PS_BLOCK_CART_AJAX', (int)($ajax));
				Configuration::updateValue('NRT_CART_ACTION_AFTER_ADD', Tools::getValue('NRT_CART_ACTION_AFTER_ADD'));
            }
        }
        return $output.$this->renderForm();
    }
	
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ajax cart'),
                        'name' => 'PS_BLOCK_CART_AJAX',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
					
					array(
						'type' => 'select',
						'name' => 'NRT_CART_ACTION_AFTER_ADD',
						'label' => $this->l('Show action after add cart'),
						'required' => false,
						'options' => array(
							'query' => array(
									array('value'=>'notices','name'=>$this->l('Notices')),
									array('value'=>'modal','name'=>$this->l('Modal')),
                                    array('value'=>'canvas','name'=>$this->l('Mini cart')),
								),
							'id' => 'value',
							'name' => 'name'
						)
					),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockCart';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab
        .'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PS_BLOCK_CART_AJAX' => (bool)Tools::getValue('PS_BLOCK_CART_AJAX', Configuration::get('PS_BLOCK_CART_AJAX')),
			'NRT_CART_ACTION_AFTER_ADD' => Tools::getValue('NRT_CART_ACTION_AFTER_ADD', Configuration::get('NRT_CART_ACTION_AFTER_ADD')),
        );
    }
}
