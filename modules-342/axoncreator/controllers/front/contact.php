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

class AxonCreatorContactModuleFrontController extends ModuleFrontController
{
	
    public function init()
    {
        parent::init();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
		
		$notifications = [];

        if (Tools::isSubmit('submitMessage') || $this->ajax) {
			if(Module::isEnabled('contactform'))
			{
				if (empty($this->context->cookie->contactFormToken) || empty($this->context->cookie->contactFormTokenTTL) || $this->context->cookie->contactFormTokenTTL < time()) {
					$this->context->cookie->contactFormToken = md5(uniqid());
					$this->context->cookie->contactFormTokenTTL = time() + 600;
				}
                
				$module = Module::getInstanceByName('contactform');
				$module->sendMessage();

				if (!empty($this->context->controller->errors)) {
					$notifications['messages'] = $this->context->controller->errors;
					$notifications['nw_error'] = true;
				} elseif (!empty($this->context->controller->success)) {
					$notifications['messages'] = $this->context->controller->success;
					$notifications['nw_error'] = false;
				}

				$notifications['contact_token'] = $this->context->cookie->contactFormToken;

				if ($this->ajax) {
					header('Content-Type: application/json');
					$this->ajaxRender(json_encode($notifications));
					exit;
				}
			}
        }
		
		die();
    }
}
