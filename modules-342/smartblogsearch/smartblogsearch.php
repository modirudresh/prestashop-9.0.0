<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')){
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class smartblogsearch extends Module implements WidgetInterface
{
    
    public function __construct()
    {
        $this->name       = 'smartblogsearch';
        $this->tab        = 'front_office_features';
        $this->version    = '2.0.4';
        $this->bootstrap  = true;
        $this->author     = 'SmartDataSoft';
        
        parent::__construct();
        
        $this->displayName = $this->l('Smart Blog Search');
        $this->description = $this->l('The Most Powerfull Presta shop Blog Search Module\'s - by smartdatasoft');

    }
        
    public function install()
    {
        return parent::install()
            && $this->registerHook('displaySmartBlogLeft')
            && $this->registerHook('displaySmartBlogRight');
    }
    
    public function uninstall()
    {
        return parent::uninstall();
    }
    
    public function hookLeftColumn($params)
    {
        if (Module::isInstalled('smartblog') != 1) {
            $this->smarty->assign(array(
                'smartmodname' => $this->name
            ));
            return $this->display(__FILE__, 'views/templates/front/install_required.tpl');
        } else {
            $this->smarty->assign(array(
                'search_query' => pSQL(Tools::getValue('search_query'))
            ));
            return $this->display(__FILE__, 'views/templates/front/smartblogsearch.tpl');
        }
    }
    
    public function hookRightColumn($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function hookdisplaySmartBlogLeft($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function hookdisplaySmartBlogRight($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}

}