<?php

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class Custompromo extends Module {
    protected $form_errors = [];

    public function __construct() {
        $this->name = 'custompromo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l( 'Custom Promo' );
        $this->description = $this->l( 'Displays a custom promotional message on the homepage.' );
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install() {
        return parent::install() &&
        $this->registerHook( 'displayHome' ) &&
        Configuration::updateValue( 'CUSTOM_PROMO_MESSAGE', '' ) &&
        Configuration::updateValue( 'CUSTOM_PROMO_DESCRIPTION', '' ) &&
        Configuration::updateValue( 'CUSTOM_PROMO_ENABLED', true );
    }

    public function uninstall() {
        return parent::uninstall() &&
        $this->unregisterHook( 'displayHome' ) &&
        Configuration::deleteByName( 'CUSTOM_PROMO_MESSAGE' ) &&
        Configuration::deleteByName( 'CUSTOM_PROMO_DESCRIPTION' ) &&
        Configuration::deleteByName( 'CUSTOM_PROMO_ENABLED' );
    }

    public function getContent() {
        $output = '';

        if ( Tools::isSubmit( 'submitCustomPromo' ) ) {
            $promo_message = Tools::getValue( 'CUSTOM_PROMO_MESSAGE' );
            $promo_description = Tools::getValue( 'CUSTOM_PROMO_DESCRIPTION', null );
            $promo_enabled = Tools::getValue( 'CUSTOM_PROMO_ENABLED' );

            if ( empty( $promo_message ) ) {
                $this->form_errors[ 'CUSTOM_PROMO_MESSAGE' ] = $this->l( 'Promo message is required.' );
            }

            if ( empty( $promo_description ) ) {
                $this->form_errors[ 'CUSTOM_PROMO_DESCRIPTION' ] = $this->l( 'Promo description is required.' );
            }

            if ( empty( $this->form_errors ) ) {
                Configuration::updateValue( 'CUSTOM_PROMO_MESSAGE', $promo_message );
                Configuration::updateValue( 'CUSTOM_PROMO_DESCRIPTION', $promo_description, true );

                Configuration::updateValue( 'CUSTOM_PROMO_ENABLED', ( bool )$promo_enabled );

                $output .= $this->displayConfirmation( $this->l( 'Settings updated successfully.' ) );
            }
        }

        // Load TinyMCE + init script
        $this->context->controller->addJS( _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js' );
        $this->context->controller->addJS( _PS_JS_DIR_ . 'admin/tinymce.inc.js' );
        $this->context->controller->addJS( $this->_path . 'views/js/admin.js' );

        return $output . $this->renderForm();
    }

    protected function renderForm() {
        $form = new HelperForm();

        $form->module = $this;
        $form->name_controller = $this->name;
        $form->token = Tools::getAdminTokenLite( 'AdminModules' );
        $form->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $form->default_form_language = ( int ) Configuration::get( 'PS_LANG_DEFAULT' );
        $form->allow_employee_form_lang = ( int ) Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' );
        $form->title = $this->displayName;
        $form->submit_action = 'submitCustomPromo';

        $form->fields_value = [
            'CUSTOM_PROMO_MESSAGE' => Tools::getValue( 'CUSTOM_PROMO_MESSAGE', Configuration::get( 'CUSTOM_PROMO_MESSAGE' ) ),
            'CUSTOM_PROMO_DESCRIPTION' => Tools::getValue( 'CUSTOM_PROMO_DESCRIPTION', Configuration::get( 'CUSTOM_PROMO_DESCRIPTION' ) ),
            'CUSTOM_PROMO_ENABLED' => Tools::getValue( 'CUSTOM_PROMO_ENABLED', Configuration::get( 'CUSTOM_PROMO_ENABLED' ) ),
        ];

        $fields_form = [
            'form' => [
                'legend' => [ 'title' => $this->l( 'Custom Promo Settings' ) ],
                'description' => $this->l( 'Configure the custom promotional message to display on the homepage.' ),
                [
                    'type' => 'html',
                    'name' => 'divider',
                    'html_content' => '<hr>',
                    'height' => '1px',
                ],

                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l( 'Custom Promo Message' ),
                        'name' => 'CUSTOM_PROMO_MESSAGE',
                        'required' => true,
                        'desc' => isset( $this->form_errors[ 'CUSTOM_PROMO_MESSAGE' ] ) ?
                        '<span class="text-danger">' . $this->form_errors[ 'CUSTOM_PROMO_MESSAGE' ] . '</span>' :
                        $this->l( 'Enter the promotional message to display on the homepage.' ),
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l( 'Custom Promo Description' ),
                        'name' => 'CUSTOM_PROMO_DESCRIPTION',
                        'autoload_rte' => true,
                        'required' => true,
                        'cols' => 40,
                        'rows' => 10,
                        'desc' => isset( $this->form_errors[ 'CUSTOM_PROMO_DESCRIPTION' ] ) ?
                        '<span class="text-danger">' . $this->form_errors[ 'CUSTOM_PROMO_DESCRIPTION' ] . '</span>' :
                        $this->l( 'Enter the promotional description to display on the homepage.' ),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l( 'Enable Custom Promo' ),
                        'name' => 'CUSTOM_PROMO_ENABLED',
                        'is_bool' => true,
                        'desc' => $this->l( 'Enable or disable the custom promo message.' ),
                        'values' => [
                            [ 'id' => 'active_on', 'value' => 1, 'label' => $this->l( 'Enabled' ) ],
                            [ 'id' => 'active_off', 'value' => 0, 'label' => $this->l( 'Disabled' ) ],
                        ],
                    ]
                ],
                'submit' => [
                    'title' => $this->l( 'Save' ),
                    'class' => 'btn btn-default pull-right',
                ]
            ]
        ];

        return $form->generateForm( [ $fields_form ] );
    }

    public function hookDisplayHome( $params ) {
        if ( !Configuration::get( 'CUSTOM_PROMO_ENABLED' ) ) {
            return '';
        }

        $this->context->controller->addCSS( $this->_path . 'views/css/custompromo.css' );

        $this->context->smarty->assign( [
            'custom_promo_message' => Configuration::get( 'CUSTOM_PROMO_MESSAGE' ),
            'custom_promo_description' => Configuration::get( 'CUSTOM_PROMO_DESCRIPTION' ),
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayHome.tpl' );
    }
}
