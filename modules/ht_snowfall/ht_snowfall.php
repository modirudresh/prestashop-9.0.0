<?php

/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Ht_Snowfall extends Module {

    public function __construct() {
        $this->name = 'ht_snowfall';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Hiddentechies';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Hiddentechies Snow Fall Effect');
        $this->description = $this->l('Hiddentechies Snow Fall Effect');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install() {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        Configuration::updateValue('SNOWFALL_SNOW_FLAKE_SIZE', '10');
        Configuration::updateValue('SNOWFALL_NUMBER_FLAKES', '30');
        Configuration::updateValue('SNOWFALL_SNOW_MIN_SPEED', '12');
        Configuration::updateValue('SNOWFALL_SNOW_MAX_SPEED', '15');
        Configuration::updateValue('SNOWFALL_SNOW_FLAKE_COLOR', '#ffffff');
        Configuration::updateValue('SNOWFALL_SNOW_DISABLE_TIME', '0');

        return parent::install() &&
                $this->registerHook('displayFooter') &&
                $this->registerHook('displayHeader');
    }

    public function uninstall() {
        if (!parent::uninstall() ||
                !Configuration::deleteByName('SNOWFALL_SNOW_FLAKE_SIZE') ||
                !Configuration::deleteByName('SNOWFALL_NUMBER_FLAKES') ||
                !Configuration::deleteByName('SNOWFALL_SNOW_MIN_SPEED') ||
                !Configuration::deleteByName('SNOWFALL_SNOW_MAX_SPEED') ||
                !Configuration::deleteByName('SNOWFALL_SNOW_FLAKE_COLOR') ||
                !Configuration::deleteByName('SNOWFALL_SNOW_DISABLE_TIME')
        )
            return false;

        return true;
    }

    public function getContent() {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $snowfall_flake_size = (string) Tools::getValue('SNOWFALL_SNOW_FLAKE_SIZE');
            $snowfall_number_flakes = (string) Tools::getValue('SNOWFALL_NUMBER_FLAKES');
            $snowfall_min_speed = (string) Tools::getValue('SNOWFALL_SNOW_MIN_SPEED');
            $snowfall_max_speed = (string) Tools::getValue('SNOWFALL_SNOW_MAX_SPEED');
            $snowfall_flake_color = (string) Tools::getValue('SNOWFALL_SNOW_FLAKE_COLOR');
            $snowfall_disable_ime = (string) Tools::getValue('SNOWFALL_SNOW_DISABLE_TIME');

            if (!$snowfall_flake_size || empty($snowfall_flake_size) || empty($snowfall_number_flakes) || empty($snowfall_min_speed) || empty($snowfall_max_speed) || empty($snowfall_flake_color)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('SNOWFALL_SNOW_FLAKE_SIZE', $snowfall_flake_size);
                Configuration::updateValue('SNOWFALL_NUMBER_FLAKES', $snowfall_number_flakes);
                Configuration::updateValue('SNOWFALL_SNOW_MIN_SPEED', $snowfall_min_speed);
                Configuration::updateValue('SNOWFALL_SNOW_MAX_SPEED', $snowfall_max_speed);
                Configuration::updateValue('SNOWFALL_SNOW_FLAKE_COLOR', $snowfall_flake_color);
                Configuration::updateValue('SNOWFALL_SNOW_DISABLE_TIME', $snowfall_disable_ime);

                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // $proinfo = $this->displayProductInformation();
        // return $output . $proinfo . $this->displayForm();

        return $output . $this->displayForm();
    }

    // public function displayProductInformation() {
    //     $html = '';

    //     if (in_array('curl', get_loaded_extensions())) {

    //         // Define the path for latest notifications
    //         $file = 'https://www.hiddentechies.com/documentation/notifications/latest_notifications_presta.xml';
    //         define('LATEST_NOTIFICATIONS_FILE', $file);

    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, LATEST_NOTIFICATIONS_FILE);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //         $response = curl_exec($ch);
    //         $errmsg = curl_error($ch);
    //         $curlInfo = curl_getinfo($ch);
    //         curl_close($ch);

    //         if ($errmsg == '') {
    //             $xml = simplexml_load_string($response);
    //             $title = $xml->item->title;
    //             $content_info = $xml->item->content_info;

    //             $html .= '<div class="panel"><h3>Product Information</h3><div class="display-ht-notifications" style="display:block; border-bottom: 1px solid #cccccc;padding-bottom: 20px;clear: both;">';
    //             $html .= $content_info;
    //             $html .= '</div></div>';
    //         }
    //     }

    //     return $html;
    // }

    public function displayForm() {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Max Snow Flake Size (px)'),
                    'name' => 'SNOWFALL_SNOW_FLAKE_SIZE',
                    'size' => 20,
                    'required' => true,
                    'hint' => $this->l('Max Entry Value 10 (px)')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of Flakes'),
                    'name' => 'SNOWFALL_NUMBER_FLAKES',
                    'size' => 20,
                    'required' => true,
                    'hint' => $this->l('Recommended Maximum of 25')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Snow Flake Minimum Speed Seconds'),
                    'name' => 'SNOWFALL_SNOW_MIN_SPEED',
                    'size' => 20,
                    'required' => true,
                    'hint' => $this->l('Top to Bottom Speed: Must be less than Snow Flake Maximum Speed')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Snow Flake Maximum Speed Seconds'),
                    'name' => 'SNOWFALL_SNOW_MAX_SPEED',
                    'size' => 20,
                    'required' => true,
                    'hint' => $this->l('Top to Bottom Speed: Must be less than Snow Flake Minimum Speed')
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Snow Flake Color'),
                    'name' => 'SNOWFALL_SNOW_FLAKE_COLOR',
                    'size' => 20,
                    'required' => true,
                    'hint' => $this->l('e.g: #ffffff ')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Disable Snow Flakes After (Seconds)'),
                    'name' => 'SNOWFALL_SNOW_DISABLE_TIME',
                    'size' => 20,
                    'required' => false
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['SNOWFALL_SNOW_FLAKE_SIZE'] = Configuration::get('SNOWFALL_SNOW_FLAKE_SIZE');
        $helper->fields_value['SNOWFALL_NUMBER_FLAKES'] = Configuration::get('SNOWFALL_NUMBER_FLAKES');
        $helper->fields_value['SNOWFALL_SNOW_MIN_SPEED'] = Configuration::get('SNOWFALL_SNOW_MIN_SPEED');
        $helper->fields_value['SNOWFALL_SNOW_MAX_SPEED'] = Configuration::get('SNOWFALL_SNOW_MAX_SPEED');
        $helper->fields_value['SNOWFALL_SNOW_FLAKE_COLOR'] = Configuration::get('SNOWFALL_SNOW_FLAKE_COLOR');
        $helper->fields_value['SNOWFALL_SNOW_DISABLE_TIME'] = Configuration::get('SNOWFALL_SNOW_DISABLE_TIME');

        return $helper->generateForm($fields_form);
    }

    public function hookDisplayFooter($params) {
        // Load current value
        $this->context->smarty->assign(
                array(
                    'snowfall_flake_size' => Configuration::get('SNOWFALL_SNOW_FLAKE_SIZE'),
                    'snowfall_number_flakes' => Configuration::get('SNOWFALL_NUMBER_FLAKES'),
                    'snowfall_min_speed' => Configuration::get('SNOWFALL_SNOW_MIN_SPEED'),
                    'snowfall_max_speed' => Configuration::get('SNOWFALL_SNOW_MAX_SPEED'),
                    'snowfall_flake_color' => Configuration::get('SNOWFALL_SNOW_FLAKE_COLOR'),
                    'snowfall_disable_time' => Configuration::get('SNOWFALL_SNOW_DISABLE_TIME'),
                )
        );
        return $this->display(__FILE__, 'ht_snowfall.tpl');
    }

    public function hookDisplayHeader() {
        $this->context->controller->addCSS($this->_path . 'views/css/snowfall.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/snow-flurry.min.js', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/snow-custom.js', 'all');
    }

}
