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

if (!defined('_PS_VERSION_'))
    exit;

class Ets_rcf_defines
{
    public static $instance;
    public $captchaType = array();
    public $context;
    public $smarty;
    public function __construct()
    {
        $this->context = Context::getContext();
        if (is_object($this->context->smarty)) {
            $this->smarty = $this->context->smarty;
        }
        $this->captchaType = array(
            'google' => array(
                'id_option' => 'google',
                'name' => $this->l('Google reCAPTCHA - V2'),
                'img' => 'google.png',
            ),
            'google_v3' => array(
                'id_option' => 'google_v3',
                'name' => $this->l('Google reCAPTCHA - V3'),
                'img' => 'google_v3.png',
            ),
        );
    }
    public static function getInstance()
    {
        if (!(isset(self::$instance)) || !self::$instance) {
            self::$instance = new Ets_rcf_defines();
        }
        return self::$instance;
    }
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_recaptcha_free', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public function getConfigInputs()
    {
        return array(
            array(
                'type' => 'checkbox',
                'name' => 'ETS_RCF_POSITION',
                'label' => $this->l('Select forms to enable captcha'),
                'validate' => 'isCleanHtml',
                'required' => true,
                'default' => 'contact,register,login',
                'values' => array(
                    'query' => array(
                        array(
                            'label' => $this->l('Contact form (Recommended)'),
                            'value'=>'contact'
                        ),
                        array(
                            'label' => $this->l('Registration form (Recommended)'),
                            'value'=>'register'
                        ),
                        array(
                            'label' => $this->l('Login form'),
                            'value'=>'login'
                        ),
                    ),
                    'id' => 'value',
                    'name' => 'label'
                ),
            ),
           array(
                'name'=>'ETS_RCF_CAPTCHA_TYPE',
                'label' => $this->l('reCAPTCHA type'),
                'type' => 'pa_img_radio',
                'required' => true,
                'values' => $this->captchaType,
                'default' => 'google',
            ),
           array(
                'label' => $this->l('Site key'),
                'name'=>'ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY',
                'type' => 'text',
                'showRequired' => true,
                'col' => '4',
                'tab' => 'captcha',
                'form_group_class'=> 'ets_rcf_captcha_type google'
            ),
            array(
                'name' => 'ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY',
                'label' => $this->l('Secret key'),
                'type' => 'text',
                'showRequired' => true,
                'col' => '4',
                'tab' => 'captcha',
                'form_group_class'=> 'ets_rcf_captcha_type google'
            ),
            array(
                'name' => 'ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY',
                'label' => $this->l('Site key'),
                'type' => 'text',
                'showRequired' => true,
                'col' => '4',
                'tab' => 'captcha',
                'form_group_class'=> 'ets_rcf_captcha_type google_v3'
            ),
            array(
                'label' => $this->l('Secret key'),
                'name' => 'ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY',
                'type' => 'text',
                'showRequired' => true,
                'col' => '4',
                'tab' => 'captcha',
                'form_group_class'=> 'ets_rcf_captcha_type google_v3'
            ),
        );
    }
}