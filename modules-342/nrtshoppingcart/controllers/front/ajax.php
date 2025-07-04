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

class NrtShoppingcartAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        parent::initContent();

        $modal = null;
        $notices = null;
        
        if (Tools::getValue('action') === 'add-to-cart') {
            $modal = $this->module->renderModal(
                (int) Tools::getValue('id_product'),
                (int) Tools::getValue('id_product_attribute'),
                (int) Tools::getValue('id_customization')
            );
            $notices = $this->module->renderNotices(
                (int) Tools::getValue('id_product'),
                (int) Tools::getValue('id_product_attribute'),
                (int) Tools::getValue('id_customization')
            );
        }elseif (Tools::getValue('action') === 'delete-all-cart') {
            $this->module->emptyCart();
        }

		if(ob_get_contents()){
			ob_end_clean();
		}

        header('Content-Type: application/json');
        
        die(json_encode([
            'preview' => $this->module->renderWidget(null, [ 'is_ajax_cart' => true ]),
			'canvas' => $this->module->renderWidget('displayBeforeBodyClosingTag', []),
            'modal'   => $modal,
            'notices'   => $notices
        ]));
    }
}
