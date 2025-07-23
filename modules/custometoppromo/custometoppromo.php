<?php

declare( strict_types = 1 );

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class Custometoppromo extends Module {
    protected $form_errors = [];

    public function __construct() {
        $this->name = 'custometoppromo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l( 'Custom Topbar Promo' );
        $this->description = $this->l( 'Displays a custom promotional message at the top of the homepage.' );

        $this->ps_versions_compliancy = [
            'min' => '1.7.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install() {
        return parent::install()
        && $this->registerHook( 'displayAfterBodyOpeningTag' )
        && Configuration::updateValue( 'CUSTOME_TOP_PROMO_MESSAGE', '' )
        && Configuration::updateValue( 'CUSTOME_TOP_PROMO_DESCRIPTION', 'Enjoy exclusive deals and offers every day! Shop now and save big.' )
        && Configuration::updateValue( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR', '#f0f0f0' )
        && Configuration::updateValue( 'CUSTOME_TOP_PROMO_TEXT_COLOR', '#000000' )
        && Configuration::updateValue( 'CUSTOME_TOP_PROMO_ENABLED', true );
    }

    public function uninstall() {
        return parent::uninstall()
        && $this->unregisterHook( 'displayAfterBodyOpeningTag' )
        && Configuration::deleteByName( 'CUSTOME_TOP_PROMO_MESSAGE' )
        && Configuration::deleteByName( 'CUSTOME_TOP_PROMO_DESCRIPTION' )
        && Configuration::deleteByName( 'CUSTOME_TOP_PROMO_ENABLED' )
        && Configuration::deleteByName( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR' )
        && Configuration::deleteByName( 'CUSTOME_TOP_PROMO_TEXT_COLOR' );
    }

    public function getContent() {
        $output = '';

        if ( Tools::isSubmit( 'submitCustomeTopPromo' ) ) {
            $message = Tools::getValue( 'CUSTOME_TOP_PROMO_MESSAGE' );
            $bgColor = Tools::getValue( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR' );
            $textColor = Tools::getValue( 'CUSTOME_TOP_PROMO_TEXT_COLOR' );
            $enabled = Tools::getValue( 'CUSTOME_TOP_PROMO_ENABLED' );

            if ( empty( $message ) ) {
                $this->form_errors[ 'CUSTOME_TOP_PROMO_MESSAGE' ] = $this->l( 'Promo message is required.' );
            }

            if ( empty( $this->form_errors ) ) {
                Configuration::updateValue( 'CUSTOME_TOP_PROMO_MESSAGE', $message );
                Configuration::updateValue( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR', $bgColor );
                Configuration::updateValue( 'CUSTOME_TOP_PROMO_TEXT_COLOR', $textColor );
                Configuration::updateValue( 'CUSTOME_TOP_PROMO_ENABLED', ( bool )$enabled );
                $output .= $this->displayConfirmation( $this->l( 'Settings updated successfully.' ) );
            } else {
                foreach ( $this->form_errors as $error ) {
                    $output .= $this->displayError( $error );
                }
            }
        }

        return $output . $this->renderForm();
    }

    public function renderForm() {
        $form = new HelperForm();
        $form->module = $this;
        $form->name_controller = $this->name;
        $form->token = Tools::getAdminTokenLite( 'AdminModules' );
        $form->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $form->submit_action = 'submitCustomeTopPromo';

        $form->fields_value = [
            'CUSTOME_TOP_PROMO_MESSAGE' => Configuration::get( 'CUSTOME_TOP_PROMO_MESSAGE' ),
            'CUSTOME_TOP_PROMO_DESCRIPTION' => Configuration::get( 'CUSTOME_TOP_PROMO_DESCRIPTION' ),
            'CUSTOME_TOP_PROMO_BACKGROUND_COLOR' => Configuration::get( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR' ),
            'CUSTOME_TOP_PROMO_TEXT_COLOR' => Configuration::get( 'CUSTOME_TOP_PROMO_TEXT_COLOR' ),
            'CUSTOME_TOP_PROMO_ENABLED' => Configuration::get( 'CUSTOME_TOP_PROMO_ENABLED' ),
        ];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l( 'Custom Top Promo Settings' ),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l( 'Promo Message' ),
                        'name' => 'CUSTOME_TOP_PROMO_MESSAGE',
                        'required' => true,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l( 'Promo Background Color' ),
                        'name' => 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR',
                        'required' => true,
                        'default' => '#f0f0f0',
                        'class' => 'colorpicker',
                        'desc' => $this->l( 'Select the background color for the promo banner.' )
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l( 'Text Color' ),
                        'name' => 'CUSTOME_TOP_PROMO_TEXT_COLOR',
                        'required' => true,
                        'default' => '#000000',
                        'class' => 'colorpicker',
                        'desc' => $this->l( 'Select the text color for the promo banner.' )
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l( 'Promo Details' ),
                        'name' => 'CUSTOME_TOP_PROMO_DESCRIPTION',
                        'rows' => 5,
                        'cols' => 40
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l( 'Enable Promo?' ),
                        'name' => 'CUSTOME_TOP_PROMO_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [ 'id' => 'enabled_on', 'value' => 1, 'label' => $this->l( 'Enabled' ) ],
                            [ 'id' => 'enabled_off', 'value' => 0, 'label' => $this->l( 'Disabled' ) ]
                        ]
                    ]
                ],
                'submit' => [
                    'title' => $this->l( 'Save Settings' ),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        return $form->generateForm( [ $fields_form ] );
    }

    public function hookDisplayAfterBodyOpeningTag( $params ) {
        if ( !Configuration::get( 'CUSTOME_TOP_PROMO_ENABLED' ) ) {
            return '';
        }
        $this->context->smarty->assign( [
            'custom_top_promo_message' => Configuration::get( 'CUSTOME_TOP_PROMO_MESSAGE' ),
            'custom_top_promo_description' => Configuration::get( 'CUSTOME_TOP_PROMO_DESCRIPTION' ),
            'custom_color' => Configuration::get( 'CUSTOME_TOP_PROMO_BACKGROUND_COLOR' ),
            'custom_text_color' => Configuration::get( 'CUSTOME_TOP_PROMO_TEXT_COLOR' )
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayAfterBodyOpeningTag.tpl' );
    }

}
