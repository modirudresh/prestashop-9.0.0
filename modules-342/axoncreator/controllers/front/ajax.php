<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AxonCreatorAjaxModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
    }

    public function postProcess()
    {
		parent::initContent();
				
        if (Tools::getValue('type') == 'product') {
            $this->_renderProducts();
        }elseif (Tools::getValue('type') == 'blog') {
            $this->_renderBlogs();
        }
    }

    public function _renderProducts()
    {
		header('Content-Type: application/json');
		$options = Tools::getValue('options');
		
		if($options['source'] != 's'){
			$data = $this->module->_prepProducts($options);		
		}else{
			$data = $this->module->_prepProductsSelected($options);	
		}
		
		$content = array_merge($options, $data);
		
       	$this->context->smarty->assign(array('content' => $content));
		
		$template = 'module:' . $this->module->name . '/views/templates/widgets/products.tpl';
								
        $this->ajaxRender(json_encode(array(
			'lastPage' => $content['lastPage'],
            'html' => $this->context->smarty->fetch($template)
        )));	
		
		exit;
	}
	
    public function _renderBlogs()
    {
		header('Content-Type: application/json');
		$options = Tools::getValue('options');

		$data = $this->module->_prepBlogs($options);		
		
		$content = array_merge($options, $data);
		
       	$this->context->smarty->assign(array('content' => $content));
		
		$template = 'module:' . $this->module->name . '/views/templates/widgets/blogs.tpl';
								
        $this->ajaxRender(json_encode(array(
            'html' => $this->context->smarty->fetch($template)
        )));	
		
		exit;
	}
			
}
