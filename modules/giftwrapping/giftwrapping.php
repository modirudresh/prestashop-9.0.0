<?php

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class Giftwrapping extends Module {
    public function __construct() {
        $this->name = 'giftwrapping';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l( 'Gift Wrapping' );
        $this->description = $this->l( 'Allows customers to add gift wrapping to their orders.' );
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install() {
        return parent::install()
        && $this->registerHook( 'displayShoppingCart' )
        && $this->registerHook( 'displayOrderConfirmation' )
        && Configuration::updateValue( 'GIFT_WRAPPING_ENABLED', 1 )
        && Configuration::updateValue( 'GIFT_WRAPPING_PRICE', 0.00 )
        && Configuration::updateValue( 'GIFT_NOTE', '' );
    }

    public function uninstall() {
        for ( $i = 1; $i <= 4; $i++ ) {
            Configuration::deleteByName( "WRAPPER_IMAGE_$i" );
        }

        return parent::uninstall()
        && Configuration::deleteByName( 'GIFT_WRAPPING_ENABLED' )
        && Configuration::deleteByName( 'GIFT_WRAPPING_PRICE' )
        && Configuration::deleteByName( 'GIFT_NOTE' );
    }

    public function getContent() {
        $output = '';

        if ( Tools::isSubmit( 'submitGiftWrapping' ) ) {
            $enabled = ( bool )Tools::getValue( 'GIFT_WRAPPING_ENABLED' );
            $price = ( float )Tools::getValue( 'GIFT_WRAPPING_PRICE' );
            $note = Tools::getValue( 'GIFT_NOTE' );

            Configuration::updateValue( 'GIFT_WRAPPING_ENABLED', $enabled );
            Configuration::updateValue( 'GIFT_WRAPPING_PRICE', $price );
            Configuration::updateValue( 'GIFT_NOTE', $note );

            for ( $i = 1; $i <= 4; $i++ ) {
                if ( isset( $_FILES[ "Wrapper_IMAGE_$i" ] ) && is_uploaded_file( $_FILES[ "Wrapper_IMAGE_$i" ][ 'tmp_name' ] ) ) {
                    $path = _PS_MODULE_DIR_ . $this->name . '/uploads/';
                    if ( !file_exists( $path ) ) {
                        mkdir( $path, 0755, true );
                    }

                    $filename = uniqid( 'wrap_' ) . '.' . pathinfo( $_FILES[ "Wrapper_IMAGE_$i" ][ 'name' ], PATHINFO_EXTENSION );
                    move_uploaded_file( $_FILES[ "Wrapper_IMAGE_$i" ][ 'tmp_name' ], $path . $filename );
                    Configuration::updateValue( "WRAPPER_IMAGE_$i", $filename );
                }
            }

            $output .= $this->displayConfirmation( $this->l( 'Settings updated successfully.' ) );
        }

        // ✅ Assign expected Smarty variables to avoid undefined key errors
        $this->context->smarty->assign( [
            'GIFT_WRAPPING_ENABLED' => Configuration::get( 'GIFT_WRAPPING_ENABLED' ),
            'GIFT_WRAPPING_PRICE' => Configuration::get( 'GIFT_WRAPPING_PRICE' ),
            'GIFT_NOTE' => Configuration::get( 'GIFT_NOTE' ),
        ] );

        return $output . $this->renderForm();
    }

    protected function renderForm() {
        $form = new HelperForm();

        $form->module = $this;
        $form->name_controller = $this->name;
        $form->token = Tools::getAdminTokenLite( 'AdminModules' );
        $form->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $form->default_form_language = ( int )Configuration::get( 'PS_LANG_DEFAULT' );
        $form->allow_employee_form_lang = ( int )Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' );
        $form->title = $this->displayName;
        $form->submit_action = 'submitGiftWrapping';
        $form->toolbar_scroll = true;
        $form->show_cancel_button = false;
        $form->override_folder = '';

        $wrapper_images = [];
        for ( $i = 1; $i <= 4; $i++ ) {
            $wrapper_images[ $i ] = Configuration::get( "WRAPPER_IMAGE_$i" );
        }

        $fields_value = [
            'GIFT_WRAPPING_ENABLED' => (int)Tools::getValue('GIFT_WRAPPING_ENABLED', Configuration::get('GIFT_WRAPPING_ENABLED', null) ?? 0),
            'GIFT_WRAPPING_PRICE' => Configuration::get( 'GIFT_WRAPPING_PRICE' ),
            'GIFT_NOTE' => Configuration::get( 'GIFT_NOTE' ),
        ];

        $form->fields_value = $fields_value;

        $fields = [
            [
                'type' => 'switch',
                'label' => $this->l( 'Enable Gift Wrapping' ),
                'name' => 'GIFT_WRAPPING_ENABLED',
                'is_bool' => true,
                'values' => [
                    [ 'id' => 'active_on', 'value' => 1, 'label' => $this->l( 'Enabled' ) ],
                    [ 'id' => 'active_off', 'value' => 0, 'label' => $this->l( 'Disabled' ) ],
                ],
                'desc' => $this->l( 'Enable or disable the gift wrapping feature.' ),
            ],
            [
                'type' => 'text',
                'label' => $this->l( 'Gift Wrapping Price' ),
                'name' => 'GIFT_WRAPPING_PRICE',
                'required' => true,
                'desc' => $this->l( 'Set the price for gift wrapping.' ),
            ],
        ];

        for ( $i = 1; $i <= 4; $i++ ) {
            $fields[] = [
                'type' => 'file',
                'label' => $this->l( "Gift Wrapping Image $i" ),
                'name' => "Wrapper_IMAGE_$i",
                'desc' => $this->l( "Upload an image for gift wrapping option $i." ),
                'image' => isset( $wrapper_images[ $i ] ) ? $wrapper_images[ $i ] : false,
            ];
        }

        $fields[] = [
            'type' => 'textarea',
            'label' => $this->l( 'Gift Note' ),
            'name' => 'GIFT_NOTE',
            'autoload_rte' => true,
            'required' => false,
            'cols' => 40,
            'rows' => 10,
            'desc' => $this->l( 'Enter a gift note that will be displayed on the gift wrapping.' ),
        ];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l( 'Gift Wrapping Settings' ),
                    'icon' => 'icon-cogs',
                ],
                'input' => $fields,
                'submit' => [
                    'title' => $this->l( 'Save' ),
                    'class' => 'btn btn-default pull-right',
                ],
                'enctype' => 'multipart/form-data',
            ],
        ];

        return $form->generateForm( [ $fields_form ] );
    }

    public function hookDisplayShoppingCart( $params ) {
        if ( !Configuration::get( 'GIFT_WRAPPING_ENABLED' ) ) {
            return '';
        }

        $this->context->smarty->assign( [
            'GIFT_WRAPPING_ENABLED' => Configuration::get( 'GIFT_WRAPPING_ENABLED' ),
            'GIFT_WRAPPING_PRICE' => Configuration::get( 'GIFT_WRAPPING_PRICE' ),
            'gift_wrapping_price' => Tools::convertPrice( Configuration::get( 'GIFT_WRAPPING_PRICE' ), $this->context->currency ),
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayShoppingCart.tpl' );
    }

}
