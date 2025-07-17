<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

if (false) {
	class Contactform {
		public function sendMessage() {

		}
	}
}

class ContactformOverride extends Contactform
{
    public function sendMessage()
    {
        if (version_compare(_PS_VERSION_, '1.7.1', '>') && Module::isEnabled('ets_recaptcha_free') && ($captcha = Module::getInstanceByName('ets_recaptcha_free')) && $captcha->hookVal('contact')) {
            $captcha->captchaVal(Context::getContext()->controller->errors);
        } 
        if (Context::getContext()->controller->errors)
            return false;
        return parent::sendMessage();
    }
}