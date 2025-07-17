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
require_once(dirname(__FILE__) . '/classes/ets_rcf_defines.php');

class Ets_recaptcha_free extends Module
{
    const CACERT_LOCATION = 'https://curl.haxx.se/ca/cacert.pem';
    public $_errors = array();
    public $is17 = false;
    public $is16 = false;
    protected $contact;
    protected $customer_thread;
    public $hooks = array(
        'displayPaCaptcha',
        'displayHeader',
        'displayCustomerAccountForm',
        'displayBackOfficeHeader',
    );
    public $_html = '';
    public $fields_form;
    public $refs;

    public function __construct()
    {
        $this->name = 'ets_recaptcha_free';
        $this->tab = 'front_office_features';
        $this->version = '1.1.3';
        $this->author = 'PrestaHero';
        $this->need_instance = 0;
        $this->module_key = Tools::hash($this->name);
        $this->bootstrap = true;
        parent::__construct();
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('reCAPTCHA Free');
        $this->description = $this->l('Free PrestaShop captcha module for contact form, login form and registration form.');
$this->refs = 'https://prestahero.com/';
        $this->module_key = '6f02499c023453b04b674cfe6cf5502f';
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
        $this->is16 = version_compare(_PS_VERSION_, '1.6.0', '>=');
    }

    public function install()
    {
        if (Module::isInstalled('ets_advancedcaptcha')) {
            throw new PrestaShopException($this->l("The module Advanced captcha has been installed"));
        }
        return parent::install() && $this->_installHooks() && $this->_installDefaultConfig() && $this->regexTemplates();
    }

    public function regexTemplates()
    {
        /*
        * 1.7
        * - Theme: themes/classic/modules/contactform/views/templates/widget/contacform.tpl
        * - Regex: (<\/section>\s*<footer(?:[^>]+?form-footer[^>]*?)>)/ms;
        * - Replace: {hook h='displayPaCaptcha' posTo='contact'}$1
        * 1.6 & 1.5
        * - Theme: /themes/default-bootstrap/contact-form.tpl
        * - Regex: (\{if\s+\$fileupload\s*==\s*1\}(?:.+\"fileUpload\".+?)\{\/if\})/ms
        * - Replace: {hook h='displayPaCaptcha' posTo='contact'}$1
        */
        $tpl_contact = ($this->is17 ? 'modules/contactform/views/templates/widget/contactform' : 'contact-form') . '.tpl';
        $tpl_contact = (@file_exists(_PS_THEME_DIR_ . $tpl_contact) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_contact;
        if (!@file_exists($tpl_contact . '.backup') && @file_exists($tpl_contact)) {
            $tpl_content = Tools::file_get_contents($tpl_contact);
            if (!preg_match('/\{hook[^\}]+?displayPaCaptcha[^\}]+?\}/ms', $tpl_content) && @copy($tpl_contact, $tpl_contact . '.backup')) {
                $pattern = $this->is17 ? '/(<\/section>\s*<footer(?:[^>]+?form-footer[^>]*?)>)/ms' : '/(\{if\s+\$fileupload\s*==\s*1\}(?:.+\"fileUpload\".+?)\{\/if\})/ms';
                $tpl_content = preg_replace(
                    $pattern,
                    '{hook h=\'displayPaCaptcha\' posTo=\'contact\'}$1',
                    $tpl_content
                );
                @file_put_contents($tpl_contact, $tpl_content);
            }
        }
        /*
        * 2. themes/classic/templates/customer/_partials/login-form.tpl:
        * - Regex: (<div\s+class\s*=\s*"[^"]*?forgot-password[^"]*?">)/ms;
        * - Replace: {hook h='displayPaCaptcha' posTo='login'}
        * 1.6 & 1.5
        * - Regex: (<p[^>]+?lost_password[^>]*?>(?:.+?)<\/p>)/ms
        * - Replace: {hook h='displayPaCaptcha' posTo='login'}$1
        */
        $tpl_login = ($this->is17 ? 'templates/customer/_partials/login-form' : 'authentication') . '.tpl';
        $tpl_login = (@file_exists(_PS_THEME_DIR_ . $tpl_login) ? _PS_THEME_DIR_ : _PS_PARENT_THEME_DIR_) . $tpl_login;
        if (!@file_exists($tpl_login . '.backup') && @file_exists($tpl_login)) {
            $tpl_content = Tools::file_get_contents($tpl_login);
            if (!preg_match('/\{hook[^\}]+?displayPaCaptcha[^\}]+?\}/ms', $tpl_content) && @copy($tpl_login, $tpl_login . '.backup')) {
                $pattern = $this->is17 ? '/(<div\s+class\s*=\s*"[^"]*?forgot-password[^"]*?">)/ms' : '/(<p[^>]+?lost_password[^>]*?>(?:.+?)<\/p>)/ms';
                $tpl_content = preg_replace(
                    $pattern,
                    '{hook h=\'displayPaCaptcha\' posTo=\'login\'}$1',
                    $tpl_content
                );
                @file_put_contents($tpl_login, $tpl_content);
            }
        }
        return true;
    }


    /**
     * @param string $template
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return Smarty_Internal_Template
     */
    protected function getCurrentSubTemplate($template, $cache_id = null, $compile_id = null)
    {
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        if (!isset($this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id])) {
            if (false === strpos($template, 'module:') &&
                !file_exists(_PS_ROOT_DIR_ . '/' . $template) &&
                !file_exists($template)
            ) {
                $template = $this->getTemplatePath($template);
            }

            $this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id] = $this->context->smarty->createTemplate(
                $template,
                $cache_id,
                $compile_id,
                $this->smarty
            );
        }

        return $this->current_subtemplate[$template . '_' . $cache_id . '_' . $compile_id];
    }

    public function getDefaultCompileId()
    {
        return Context::getContext()->shop->theme_name;
    }


    public function unInstall()
    {
        return parent::unInstall() && $this->_unInstallHooks() && $this->_unInstallDefaultConfig();
    }

    public function _installHooks()
    {
        foreach ($this->hooks as $hook) {
            $this->registerHook($hook);
        }
        return true;
    }

    public function _unInstallHooks()
    {
        foreach ($this->hooks as $hook) {
            $this->unRegisterHook($hook);
        }
        return true;
    }

    public function _installDefaultConfig()
    {
        $inputs = Ets_rcf_defines::getInstance()->getConfigInputs();
        $languages = Language::getLanguages(false);
        if ($inputs) {
            foreach ($inputs as $input) {
                if ($input['type'] == 'html')
                    continue;
                if (isset($input['default']) && $input['default']) {
                    if (isset($input['lang']) && $input['lang']) {
                        $values = array();
                        foreach ($languages as $language) {
                            if (isset($input['default_is_file']) && $input['default_is_file'])
                                $values[$language['id_lang']] = file_exists(dirname(__FILE__) . '/default/' . $input['default_is_file'] . '_' . $language['iso_code'] . '.txt') ? Tools::file_get_contents(dirname(__FILE__) . '/default/' . $input['default_is_file'] . '_' . $language['iso_code'] . '.txt') : Tools::file_get_contents(dirname(__FILE__) . '/default/' . $input['default_is_file'] . '_en.txt');
                            else
                                $values[$language['id_lang']] = isset($input['default_lang']) && $input['default_lang'] ? $this->getTextLang($input['default_lang'], $language) : $input['default'];
                        }
                        Configuration::updateGlobalValue($input['name'], $values, isset($input['autoload_rte']) && $input['autoload_rte'] ? true : false);
                    } else
                        Configuration::updateGlobalValue($input['name'], $input['default']);
                }
            }
        }
        return true;
    }

    public function _unInstallDefaultConfig()
    {
        $inputs = Ets_rcf_defines::getInstance()->getConfigInputs();
        if ($inputs) {
            foreach ($inputs as $input) {
                if ($input['type'] == 'html')
                    continue;
                Configuration::deleteByName($input['name']);
            }
        }
        return true;
    }

    public function getContent()
    {
        $this->_html = '';
        if ($this->context->cookie->_success) {
            $this->_html .= $this->displayConfirmation($this->context->cookie->_success);
            $this->context->cookie->_success = '';
            $this->context->cookie->write();
        }
        if ($this->context->cookie->_error_message) {
            $this->_html .= $this->displayError($this->context->cookie->_error_message);
            $this->context->cookie->_error_message = '';
            $this->context->cookie->write();
        }
        $inputs = Ets_rcf_defines::getInstance()->getConfigInputs();
        if (Tools::isSubmit('btnSubmitSave')) {
            $this->saveSubmit($inputs);
        }
        $this->_html .= $this->renderForm($inputs, 'btnSubmitSave', $this->l('Settings'));
        // $this->_html .= $this->displayIframe();
        return $this->_html;
    }

    public function renderForm($inputs, $submit, $title)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $title,
                    'icon' => ''
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = $this->id;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = $submit;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $language->id;
        $helper->override_folder = '/';
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
            'fields_value' => $this->getFieldsValues($inputs),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'link' => $this->context->link,
            'path' => $this->_path,
        );
        $this->fields_form = array();
        return $helper->generateForm(array($fields_form));
    }

    public function getFieldsValues($inputs)
    {
        $languages = Language::getLanguages(false);
        $fields = array();
        $inputs = $inputs;
        if ($inputs) {
            foreach ($inputs as $input) {
                if (!isset($input['lang'])) {
                    $fields[$input['name']] = Tools::getValue($input['name'], $input['type'] == 'checkbox' && Configuration::get($input['name']) ? explode(',', Configuration::get($input['name'])) : Configuration::get($input['name']));
                } else {
                    foreach ($languages as $language) {
                        $fields[$input['name']][$language['id_lang']] = Tools::getValue($input['name'] . '_' . $language['id_lang'], Configuration::get($input['name'], $language['id_lang']));
                    }
                }
            }
        }
        return $fields;
    }

    public function saveSubmit($inputs)
    {
        $this->_postValidation($inputs);
        if (!count($this->_errors)) {
            $languages = Language::getLanguages(false);
            $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
            if ($inputs) {
                foreach ($inputs as $input) {
                    if ($input['type'] != 'html') {
                        if (isset($input['lang']) && $input['lang']) {
                            $values = array();
                            foreach ($languages as $language) {
                                $value_default = Tools::getValue($input['name'] . '_' . $id_lang_default);
                                $value = Tools::getValue($input['name'] . '_' . $language['id_lang']);
                                $values[$language['id_lang']] = ($value && Validate::isCleanHtml($value, true)) || !isset($input['required']) ? $value : (Validate::isCleanHtml($value_default, true) ? $value_default : '');
                            }
                            Configuration::updateValue($input['name'], $values, true);
                        } else {

                            if ($input['type'] == 'checkbox') {
                                $val = Tools::getValue($input['name'], array());
                                if (is_array($val) && self::validateArray($val)) {
                                    Configuration::updateValue($input['name'], implode(',', $val));
                                }
                            } else {
                                $val = Tools::getValue($input['name']);
                                if (Validate::isCleanHtml($val))
                                    Configuration::updateValue($input['name'], $val);
                            }

                        }
                    }

                }
            }
            if (Tools::isSubmit('ajax')) {
                die(
                json_encode(
                    array(
                        'success' => $this->l('Settings updated'),
                    )
                )
                );
            }
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        } else {
            if (Tools::isSubmit('ajax')) {
                die(
                json_encode(
                    array(
                        'errors' => $this->displayError($this->_errors),
                    )
                )
                );
            }
            $this->_html .= $this->displayError($this->_errors);
        }
    }

    public function _postValidation($inputs)
    {
        $languages = Language::getLanguages(false);
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        foreach ($inputs as $input) {
            if ($input['type'] == 'html')
                continue;
            if (isset($input['lang']) && $input['lang']) {
                if (isset($input['required']) && $input['required']) {
                    $val_default = Tools::getValue($input['name'] . '_' . $id_lang_default);
                    if (!$val_default) {
                        $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                    } elseif ($val_default && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($val_default, true))
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    elseif ($val_default && !Validate::isCleanHtml($val_default, true))
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    else {
                        foreach ($languages as $language) {
                            if (($value = Tools::getValue($input['name'] . '_' . $language['id_lang'])) && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($value, true))
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                            elseif ($value && !Validate::isCleanHtml($value, true))
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                        }
                    }
                } else {
                    foreach ($languages as $language) {
                        if (($value = Tools::getValue($input['name'] . '_' . $language['id_lang'])) && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($value, true))
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                        elseif ($value && !Validate::isCleanHtml($value, true))
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                    }
                }
            } else {
                $val = Tools::getValue($input['name']);
                if ($input['type'] != 'checkbox') {

                    if ($val === '' && isset($input['required']) && $input['required']) {
                        $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                    }
                    if ($val !== '' && isset($input['validate']) && ($validate = $input['validate']) && $validate == 'isColor' && !Validate::isColor($val)) {
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    } elseif ($val !== '' && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($val)) {
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    } elseif ($val !== '' && isset($input['validate']) && ($validate = $input['validate']) && $validate == 'isUnsignedInt' && $val == 0) {
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    } elseif ($val !== '' && !Validate::isCleanHtml($val))
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                } else {
                    if (!$val && isset($input['required']) && $input['required']) {
                        $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                    } elseif ($val && !self::validateArray($val, isset($input['validate']) ? $input['validate'] : ''))
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                }
            }
        }
        $ETS_RCF_CAPTCHA_TYPE = Tools::getValue('ETS_RCF_CAPTCHA_TYPE');
        if ($ETS_RCF_CAPTCHA_TYPE == 'google') {
            $ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY = Tools::getValue('ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY');
            $ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY = Tools::getValue('ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY');
            if (!$ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY)
                $this->_errors[] = $this->l('Site key is required');
            elseif (!Validate::isCleanHtml($ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY))
                $this->_errors[] = $this->l('Site key is not valid');
            if (!$ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY)
                $this->_errors[] = $this->l('Secret key is required');
            elseif (!Validate::isCleanHtml($ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY))
                $this->_errors[] = $this->l('Secret key is not valid');
        }
        if ($ETS_RCF_CAPTCHA_TYPE == 'google_v3') {
            $ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY = Tools::getValue('ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY');
            $ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY = Tools::getValue('ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY');
            if (!$ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY)
                $this->_errors[] = $this->l('Site key is required');
            elseif (!Validate::isCleanHtml($ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY))
                $this->_errors[] = $this->l('Site key is not valid');
            if (!$ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY)
                $this->_errors[] = $this->l('Secret key is required');
            elseif (!Validate::isCleanHtml($ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY))
                $this->_errors[] = $this->l('Secret key is not valid');
        }
    }

    public function getTextLang($text, $lang, $file_name = '')
    {
        if (is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif (is_object($lang))
            $iso_code = $lang->iso_code;
        else {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
        $modulePath = rtrim(_PS_MODULE_DIR_, '/') . '/' . $this->name;
        $fileTransDir = $modulePath . '/translations/' . $iso_code . '.' . 'php';
        if (!@file_exists($fileTransDir)) {
            return $text;
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $text_tras = preg_replace("/\\\*'/", "\'", $text);
        $strMd5 = md5($text_tras);
        $keyMd5 = '<{' . $this->name . '}prestashop>' . ($file_name ?: $this->name) . '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if ($matches && isset($matches[2])) {
            return $matches[2];
        }
        return $text;
    }

    public static function validateArray($array, $validate = 'isCleanHtml')
    {
        if (!is_array($array)) {
            if (method_exists('Validate', $validate)) {
                return Validate::$validate($array);
            }
            return true;
        }
        if (method_exists('Validate', $validate)) {
            if ($array && is_array($array)) {
                $ok = true;
                foreach ($array as $val) {
                    if (!is_array($val)) {
                        if ($val && !Validate::$validate($val)) {
                            $ok = false;
                            break;
                        }
                    } else
                        $ok = self::validateArray($val, $validate);
                }
                return $ok;
            }
        }
        return true;
    }
public function addJquery()
{
    if (version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
        $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-' . _PS_JQUERY_VERSION_ . '.min.js');
    } else {
        // For PrestaShop 1.7.7+ and above, add jQuery manually if needed
        $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-3.7.1.min.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-migrate-3.4.0.min.js');
    }
}
    // public function addJquery()
    // {
    //     if (version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.7.0', '<'))
    //         $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-' . _PS_JQUERY_VERSION_ . '.min.js');
    //     else
    //         $this->context->controller->addJquery();
    // }

    public function hookDisplayBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');
        $configure = Tools::getValue('configure');
        if ($controller == 'AdminModules' && $configure == $this->name) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    public function captchaPro($params)
    {
        if (!(isset($params['rand'])) || !$params['rand'] || !(isset($params['posTo'])) || !$params['posTo'])
            return false;
        $this->smarty->assign(
            array(
                'modules_dir' => _MODULE_DIR_,
                'is16' => $this->is16,
                'is17' => $this->is17,
                'hl' => $this->context->language->iso_code,
                'posTo' => isset($params['posTo']) ? $params['posTo'] : false,
                'ETS_RCF_CAPTCHA_TYPE' => Configuration::get('ETS_RCF_CAPTCHA_TYPE'),
                'captcha_page' => isset($params['page']) ? $params['page'] : 'index',
            )
        );
        return $this->display(__FILE__, 'captcha.tpl');
    }

    public function hookDisplayHeader()
    {
        if (Configuration::get('ETS_RCF_POSITION')) {
            $controller = trim(Tools::getValue('controller'));
            $pos = explode(',', Configuration::get('ETS_RCF_POSITION'));
            $create_account = (int)Tools::getValue('create_account');
            if ($pos && (($controller === 'authentication' || $controller === 'registration') && ($create_account != 1 && in_array('login', $pos) || ($create_account == 1 || $controller === 'registration') && in_array('register', $pos))) ||
                ($controller === 'order' && in_array('register', $pos)) ||
                ($controller === 'contact' && in_array('contact', $pos))
            ) {
                $this->context->controller->addJS($this->_path . 'views/js/front.js');
                $this->context->controller->addCss($this->_path . 'views/css/front.css');
                if (($captcha_type = Configuration::get('ETS_RCF_CAPTCHA_TYPE')) == 'google' || $captcha_type == 'google_v3') {
                    $this->smarty->assign(array(
                        'ETS_RCF_CAPTCHA_TYPE' => $captcha_type,
                        'ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY' => Configuration::get('ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY'),
                        'ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY' => trim(Configuration::get('ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY')),
                        'ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY' => Configuration::get('ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY'),
                        'ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY' => trim(Configuration::get('ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY')),
                        'hl' => $this->context->language->iso_code
                    ));
                    return $this->display(__FILE__, 'head.tpl');
                }
            }
        }
    }

    public function hookVal($posTo)
    {
        $ETS_RCF_CAPTCHA_TYPE = Configuration::get('ETS_RCF_CAPTCHA_TYPE');
        if ($ETS_RCF_CAPTCHA_TYPE) {
            if ($ETS_RCF_CAPTCHA_TYPE == 'google') {
                $site_key = Configuration::get('ETS_RCF_GOOGLE_CAPTCHA_SITE_KEY');
                $secret_key = Configuration::get('ETS_RCF_GOOGLE_CAPTCHA_SECRET_KEY');
            } else {
                $site_key = Configuration::get('ETS_RCF_GOOGLE_V3_CAPTCHA_SITE_KEY');
                $secret_key = Configuration::get('ETS_RCF_GOOGLE_V3_CAPTCHA_SECRET_KEY');
            }
            if ($secret_key && $site_key) {
                if (Configuration::get('ETS_RCF_POSITION')) {
                    $pos = explode(',', Configuration::get('ETS_RCF_POSITION'));
                    if (in_array($posTo, $pos))
                        return true;
                }
            }

        }

        return false;
    }

    public function hookDisplayPaCaptcha($params)
    {
        if (($page = trim(Tools::getValue('controller'))) && Validate::isControllerName($page) && isset($params['posTo']) && $params['posTo'] && $this->hookVal($params['posTo'])) {
            $params['page'] = $page;
            $params['rand'] = md5(rand());
            return $this->captchaPro($params);
        }
    }

    public function hookDisplayCustomerAccountForm()
    {
        if (Tools::getValue('controller') != 'identity')
            return $this->hookDisplayPaCaptcha(array(
                'posTo' => 'register'
            ));
        else
            return '';
    }

    public function captchaVal(&$errors)
    {
        if (($captcha_type = Configuration::get('ETS_RCF_CAPTCHA_TYPE')) == 'google' || $captcha_type == 'google_v3') {
            if (Tools::getIsset('g-recaptcha-response') && ($reCaptcha = Tools::getValue('g-recaptcha-response')) && Validate::isCleanHtml($reCaptcha)) {
                $secret = Configuration::get('ETS_RCF_GOOGLE' . ($captcha_type == 'google_v3' ? '_V3' : '') . '_CAPTCHA_SECRET_KEY');

                $site_verify = "https://www.google.com/recaptcha/api/siteverify";
                $query_build = http_build_query(array(
                    'secret' => $secret,
                    'response' => $reCaptcha
                ));
                $response = Tools::file_get_contents($site_verify . '?' . $query_build);
                $response = json_decode($response);
                if (!$response || (property_exists($response, 'success') && $response->success == false) || (property_exists($response, 'score') && $response->score < 0.5)) {
                    $errors[] = $this->l('reCaptcha is invalid.');
                }
            } else
                $errors[] = $this->l('reCaptcha error');
            if (!Tools::getIsset('g-recaptcha-response')) {
                die(json_encode(array(
                    'error' => true,
                    'message' => $this->l('404 not found!'),
                )));
            }
        }
    }

    // public function displayIframe()
    // {
    //     switch ($this->context->language->iso_code) {
    //         case 'en':
    //             $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_' . $this->name . '&utm_medium=iframe';
    //             break;
    //         case 'it':
    //             $url = 'https://cdn.prestahero.com/it/prestahero-product-feed?utm_source=feed_' . $this->name . '&utm_medium=iframe';
    //             break;
    //         case 'fr':
    //             $url = 'https://cdn.prestahero.com/fr/prestahero-product-feed?utm_source=feed_' . $this->name . '&utm_medium=iframe';
    //             break;
    //         case 'es':
    //             $url = 'https://cdn.prestahero.com/es/prestahero-product-feed?utm_source=feed_' . $this->name . '&utm_medium=iframe';
    //             break;
    //         default:
    //             $url = 'https://cdn.prestahero.com/prestahero-product-feed?utm_source=feed_' . $this->name . '&utm_medium=iframe';
    //     }
    //     $this->smarty->assign(
    //         array(
    //             'url_iframe' => $url
    //         )
    //     );
    //     return $this->display(__FILE__, 'iframe.tpl');
    // }
}