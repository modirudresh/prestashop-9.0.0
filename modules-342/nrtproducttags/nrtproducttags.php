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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class NrtProductTags extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'nrtproducttags';
        $this->author = 'AxonVIZ';
        $this->version = '2.0.5';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Axon - Product Tags');
        $this->description = $this->l('Show tags on product page');

        $this->templateFile = 'module:nrtproducttags/views/templates/hook/producttags.tpl';
    }

    public function install()
    {
        return (parent::install() && $this->registerHook('displayProductTags') && $this->registerHook('actionProductSave') && $this->registerHook('actionProductDelete'));
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
        
        $id_product = (int) $params['id_product'];

        $cacheId = $this->name.'|'.$id_product;

        $this->_clearCache($this->templateFile, $this->getCacheId($cacheId));
    }
	
    public function hookActionProductDelete($params)
    {
        if(!isset($params['id_product'])){
            return;
        }

        $id_product = (int) $params['id_product'];

        $cacheId = $this->name.'|'.$id_product;

        $this->_clearCache($this->templateFile, $this->getCacheId($cacheId));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }
		
        if ($this->context->controller->php_self != 'product' || !isset($configuration['smarty']->tpl_vars['product']->value['id_product']) || !preg_match('/^displayProductTags\d*$/', $hookName)){
            return;
        }

		$cacheId = $this->name.'|'.$configuration['smarty']->tpl_vars['product']->value['id_product'];
		
		if (!$this->isCached($this->templateFile, $this->getCacheId($cacheId))) {
			$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
		}

        return $this->fetch($this->templateFile, $this->getCacheId($cacheId));
		
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $tags = Tag::getProductTags((int) $configuration['smarty']->tpl_vars['product']->value['id_product']);

        if (is_array($tags)) {
            if (isset($tags[(int) Context::getContext()->language->id])){
                return array(
                    'tags' => $tags[(int) Context::getContext()->language->id],
                );
            }
        }

        return [];
    }
}
