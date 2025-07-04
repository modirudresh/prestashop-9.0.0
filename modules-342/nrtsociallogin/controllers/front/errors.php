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

class NrtSocialLoginErrorsModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();

		$this->setTemplate('module:nrtsociallogin/views/templates/front/errors.tpl');
	}
}
?>
