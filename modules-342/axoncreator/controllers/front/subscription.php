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

class AxonCreatorSubscriptionModuleFrontController extends ModuleFrontController
{
    private $variables = [];
	
    public function init()
    {
        parent::init();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->variables['value'] = Tools::getValue('email', '');
        $this->variables['msg'] = '';
        $this->variables['conditions'] = Configuration::get('NW_CONDITIONS', $this->context->language->id);

        if (Tools::isSubmit('submitNewsletter') || $this->ajax) {
			if(Module::isEnabled('ps_emailsubscription'))
			{
				$module = Module::getInstanceByName('ps_emailsubscription');
				$module->newsletterRegistration();
				if ($module->error) {
					$this->variables['msg'] = $module->error;
					$this->variables['nw_error'] = true;
				} elseif ($module->valid) {
					$this->variables['msg'] = $module->valid;
					$this->variables['nw_error'] = false;
				}

				if ($this->ajax) {
					header('Content-Type: application/json');
					$this->ajaxRender(json_encode($this->variables));
					exit;
				}
			}
        }
		
		die();
    }
}
