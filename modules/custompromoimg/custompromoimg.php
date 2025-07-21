<?php

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class CustomPromoImg extends Module {
    protected $form_errors = [];

    public function __construct() {
        $this->name = 'custompromoimg';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l( 'Promo with Image' );
        $this->description = $this->l( 'Displays a custom promotional image on the homepage.' );
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install() {
        return parent::install()
        && $this->registerHook( 'displayHome' )
        && Configuration::updateValue( 'PROMO_MESSAGE', '' )
        && Configuration::updateValue( 'PROMO_DESCRIPTION', '' )
        && Configuration::updateValue( 'PROMO_ENABLED', true )
        && Configuration::updateValue( 'PROMO_IMAGE', '' )
        && Configuration::updateValue( 'PROMO_LINK_TEXT', '' )
        && Configuration::updateValue( 'PROMO_LINK_URL', '' )
        && Configuration::updateValue( 'PROMO_START_DATE', '' )
        && Configuration::updateValue( 'PROMO_END_DATE', '' )
        && Configuration::updateValue( 'PROMO_POSITION', 'top' )
        && Configuration::updateValue( 'CUSTOM_PROMO_BLOCKS', json_encode( [] ) )
        && Configuration::updateValue( 'PROMO_CUSTOM_CSS', '' );
    }

    public function uninstall() {
        return parent::uninstall()
        && $this->unregisterHook( 'displayHome' )
        && Configuration::deleteByName( 'PROMO_MESSAGE' )
        && Configuration::deleteByName( 'PROMO_DESCRIPTION' )
        && Configuration::deleteByName( 'PROMO_ENABLED' )
        && Configuration::deleteByName( 'PROMO_IMAGE' )
        && Configuration::deleteByName( 'PROMO_LINK_TEXT' )
        && Configuration::deleteByName( 'PROMO_LINK_URL' )
        && Configuration::deleteByName( 'PROMO_START_DATE' )
        && Configuration::deleteByName( 'PROMO_END_DATE' )
        && Configuration::deleteByName( 'PROMO_POSITION' )
        && Configuration::deleteByName( 'CUSTOM_PROMO_BLOCKS' )
        && Configuration::deleteByName( 'PROMO_CUSTOM_CSS' );
    }

    public function getContent() {
        $output = '';

        if ( Tools::isSubmit( 'submitCustomPromo' ) ) {
            $promo_message = Tools::getValue( 'PROMO_MESSAGE' );
            $promo_description = Tools::getValue( 'PROMO_DESCRIPTION' );
            $promo_enabled = ( bool )Tools::getValue( 'PROMO_ENABLED' );
            $promo_link_text = Tools::getValue( 'PROMO_LINK_TEXT' );
            $promo_link_url = Tools::getValue( 'PROMO_LINK_URL' );
            $promo_start = Tools::getValue( 'PROMO_START_DATE' );
            $promo_end = Tools::getValue( 'PROMO_END_DATE' );
            $promo_position = Tools::getValue( 'PROMO_POSITION' );
            $promo_custom_css = Tools::getValue( 'PROMO_CUSTOM_CSS' );

            if ( empty( $promo_message ) ) {
                $this->form_errors[ 'PROMO_MESSAGE' ] = $this->l( 'Promo message is required.' );
            }

            if ( empty( $promo_description ) ) {
                $this->form_errors[ 'PROMO_DESCRIPTION' ] = $this->l( 'Promo description is required.' );
            }

            if ( isset( $_FILES[ 'PROMO_IMAGE' ] ) && $_FILES[ 'PROMO_IMAGE' ][ 'size' ] > 0 ) {
                $error = ImageManager::validateUpload( $_FILES[ 'PROMO_IMAGE' ] );
                if ( $error ) {
                    $this->form_errors[ 'PROMO_IMAGE' ] = $error;
                } else {
                    $image_data = file_get_contents( $_FILES[ 'PROMO_IMAGE' ][ 'tmp_name' ] );
                    $mime_type = mime_content_type( $_FILES[ 'PROMO_IMAGE' ][ 'tmp_name' ] );
                    $base64_image = 'data:' . $mime_type . ';base64,' . base64_encode( $image_data );
                    Configuration::updateValue( 'PROMO_IMAGE', $base64_image, true );
                }
            }

            if ( empty( $this->form_errors ) ) {
                Configuration::updateValue( 'PROMO_MESSAGE', $promo_message );
                Configuration::updateValue( 'PROMO_DESCRIPTION', $promo_description, true );
                Configuration::updateValue( 'PROMO_ENABLED', $promo_enabled );
                Configuration::updateValue( 'PROMO_LINK_TEXT', $promo_link_text );
                Configuration::updateValue( 'PROMO_LINK_URL', $promo_link_url );
                Configuration::updateValue( 'PROMO_START_DATE', $promo_start );
                Configuration::updateValue( 'PROMO_END_DATE', $promo_end );
                Configuration::updateValue( 'PROMO_POSITION', $promo_position );
                Configuration::updateValue( 'PROMO_CUSTOM_CSS', $promo_custom_css, true );

                $output .= $this->displayConfirmation( $this->l( 'Settings updated successfully.' ) );
            }
        }

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
        $form->default_form_language = ( int )Configuration::get( 'PS_LANG_DEFAULT' );
        $form->allow_employee_form_lang = ( int )Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' );
        $form->title = $this->displayName;
        $form->submit_action = 'submitCustomPromo';

        $promo_image = Configuration::get( 'PROMO_IMAGE' );

        $form->fields_value = [
            'PROMO_MESSAGE' => Tools::getValue( 'PROMO_MESSAGE', Configuration::get( 'PROMO_MESSAGE' ) ),
            'PROMO_DESCRIPTION' => Tools::getValue( 'PROMO_DESCRIPTION', Configuration::get( 'PROMO_DESCRIPTION' ) ),
            'PROMO_ENABLED' => Tools::getValue( 'PROMO_ENABLED', Configuration::get( 'PROMO_ENABLED' ) ),
            'PROMO_LINK_TEXT' => Tools::getValue( 'PROMO_LINK_TEXT', Configuration::get( 'PROMO_LINK_TEXT' ) ),
            'PROMO_LINK_URL' => Tools::getValue( 'PROMO_LINK_URL', Configuration::get( 'PROMO_LINK_URL' ) ),
            'PROMO_START_DATE' => Tools::getValue( 'PROMO_START_DATE', Configuration::get( 'PROMO_START_DATE' ) ),
            'PROMO_END_DATE' => Tools::getValue( 'PROMO_END_DATE', Configuration::get( 'PROMO_END_DATE' ) ),
            'PROMO_POSITION' => Tools::getValue( 'PROMO_POSITION', Configuration::get( 'PROMO_POSITION' ) ),
            'PROMO_CUSTOM_CSS' => Tools::getValue( 'PROMO_CUSTOM_CSS', Configuration::get( 'PROMO_CUSTOM_CSS' ) ),
        ];

        $fields_form = [
            'form' => [
                'legend' => [ 'title' => $this->l( 'Custom Promo Settings' ) ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l( 'Custom Promo Message' ),
                        'name' => 'PROMO_MESSAGE',
                        'required' => true,
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->l( 'Custom Promo Image' ),
                        'name' => 'PROMO_IMAGE',
                        'desc' => $this->l( 'Upload an image to display with the promotional message.' ),
                        'image' => $promo_image ? $promo_image : false,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l( 'Custom Promo Description' ),
                        'name' => 'PROMO_DESCRIPTION',
                        'autoload_rte' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l( 'CTA Button Text' ),
                        'name' => 'PROMO_LINK_TEXT',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l( 'CTA Button Link' ),
                        'name' => 'PROMO_LINK_URL',
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l( 'Promo Start Date' ),
                        'name' => 'PROMO_START_DATE',
                    ],
                    [
                        'type' => 'date',
                        'label' => $this->l( 'Promo End Date' ),
                        'name' => 'PROMO_END_DATE',
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l( 'Promo Block Position' ),
                        'name' => 'PROMO_POSITION',
                        'options' => [
                            'query' => [
                                [ 'id_option' => 'top', 'name' => $this->l( 'Top' ) ],
                                [ 'id_option' => 'center', 'name' => $this->l( 'Center' ) ],
                                [ 'id_option' => 'bottom', 'name' => $this->l( 'Bottom' ) ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l( 'Enable Custom Promo' ),
                        'name' => 'PROMO_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [ 'id' => 'active_on', 'value' => 1, 'label' => $this->l( 'Enabled' ) ],
                            [ 'id' => 'active_off', 'value' => 0, 'label' => $this->l( 'Disabled' ) ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l( 'Custom CSS' ),
                        'name' => 'PROMO_CUSTOM_CSS',
                        'rows' => 8,
                        'cols' => 60,
                    ],
                ],
                'submit' => [
                    'title' => $this->l( 'Save' ),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        return $form->generateForm( [ $fields_form ] );
    }

    public function hookDisplayHome( $params ) {
        if ( !Configuration::get( 'PROMO_ENABLED' ) ) {
            return '';
        }

        $today = date( 'Y-m-d' );
        $start = Configuration::get( 'PROMO_START_DATE' );
        $end = Configuration::get( 'PROMO_END_DATE' );

        if ( ( $start && $today < $start ) || ( $end && $today > $end ) ) {
            return '';
        }

        $customCss = Configuration::get( 'PROMO_CUSTOM_CSS' );
        if ( !empty( $customCss ) ) {
            $cssPath = _PS_MODULE_DIR_ . $this->name . '/views/css/generated-custom.css';
            file_put_contents( $cssPath, $customCss );
            $this->context->controller->addCSS( $this->_path . 'views/css/generated-custom.css' );
        }

        $this->context->controller->addCSS( $this->_path . 'views/css/custompromo.css' );

        $this->context->smarty->assign( [
            'promo_message' => Configuration::get( 'PROMO_MESSAGE' ),
            'promo_description' => Configuration::get( 'PROMO_DESCRIPTION' ),
            'promo_image' => Configuration::get( 'PROMO_IMAGE' ),
            'promo_link_text' => Configuration::get( 'PROMO_LINK_TEXT' ),
            'promo_link_url' => Configuration::get( 'PROMO_LINK_URL' ),
            'promo_position' => Configuration::get( 'PROMO_POSITION' ),
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayHome.tpl' );
    }
}
