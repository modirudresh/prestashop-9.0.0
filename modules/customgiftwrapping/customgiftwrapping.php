<?php

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class Customgiftwrapping extends Module {
    public function __construct() {
        $this->name = 'customgiftwrapping';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l( 'Custom Gift Wrapping' );
        $this->description = $this->l( 'Allow customers to add gift wrapping to their cart.' );
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install() {
        return parent::install()
        && $this->registerHook( 'displayHeader' )
        && $this->registerHook( 'displayShoppingCart' )
        && $this->registerHook( 'displayShoppingCartFooter' )

        && Configuration::updateValue( 'GIFT_WRAPPING_ENABLED', true )
        && Configuration::updateValue( 'GIFT_WRAPPING_PRICE' );
    }

    public function uninstall() {
        for ( $i = 1; $i <= 4; $i++ ) {
            Configuration::deleteByName( "WRAPPER_IMAGE_$i" );
        }

        try {
            $this->unregisterHook( 'displayHeader' );
            $this->unregisterHook( 'displayShoppingCart' );
            $this->unregisterHook( 'displayShoppingCartFooter' );

        } catch ( Exception $e ) {
            PrestaShopLogger::addLog( 'Customgiftwrapping uninstall hook error: ' . $e->getMessage(), 3 );
        }

        Configuration::deleteByName( 'GIFT_WRAPPING_ENABLED' );
        Configuration::deleteByName( 'GIFT_WRAPPING_PRICE' );

        return parent::uninstall();
    }

    public function getContent() {
        $output = '';

        if ( Tools::isSubmit( 'submitGiftWrapping' ) ) {
            $giftWrappingPrice = Tools::getValue( 'GIFT_WRAPPING_PRICE' );

            if ( $giftWrappingPrice === '' || $giftWrappingPrice === null ) {
                $giftWrappingPrice = 0.00;
            } elseif ( !is_numeric( $giftWrappingPrice ) ) {
                $this->form_errors[ 'GIFT_WRAPPING_PRICE' ] = $this->l( 'Gift wrapping price must be a valid number.' );
            } elseif ( $giftWrappingPrice < 0 ) {
                $this->form_errors[ 'GIFT_WRAPPING_PRICE' ] = $this->l( 'Gift wrapping price cannot be negative.' );
            } else {
                $giftWrappingPrice = ( float ) number_format( $giftWrappingPrice, 2 );
            }

            // Check for at least 2 wrapping images ( existing or uploaded )
            $validImageCount = 0;
            for ( $i = 1; $i <= 4; $i++ ) {
                $inputName = "Wrapper_IMAGE_$i";
                $existing = Configuration::get( "WRAPPER_IMAGE_$i" );

                if (
                    ( isset( $_FILES[ $inputName ] ) && is_uploaded_file( $_FILES[ $inputName ][ 'tmp_name' ] ) ) ||
                    ( !empty( $existing ) )
                ) {
                    $validImageCount++;
                }
            }

            if ( $validImageCount < 2 ) {
                $this->form_errors[ 'WRAPPER_IMAGE' ] = $this->l( 'Please upload at least 2 wrapping images.' );
            }

            // Save config if no validation errors
            if ( empty( $this->form_errors ) ) {
                Configuration::updateValue( 'GIFT_WRAPPING_ENABLED', ( bool ) Tools::getValue( 'GIFT_WRAPPING_ENABLED' ) );
                Configuration::updateValue( 'GIFT_WRAPPING_PRICE', $giftWrappingPrice );

                // Save uploaded images as base64
                for ( $i = 1; $i <= 4; $i++ ) {
                    $inputName = "Wrapper_IMAGE_$i";
                    if ( isset( $_FILES[ $inputName ] ) && is_uploaded_file( $_FILES[ $inputName ][ 'tmp_name' ] ) ) {
                        $fileData = file_get_contents( $_FILES[ $inputName ][ 'tmp_name' ] );
                        $mimeType = mime_content_type( $_FILES[ $inputName ][ 'tmp_name' ] );
                        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode( $fileData );
                        Configuration::updateValue( "WRAPPER_IMAGE_$i", $base64 );
                    }
                }

                $output .= $this->displayConfirmation( $this->l( 'Settings updated successfully.' ) );
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm() {
        $form = new HelperForm();
        $form->module = $this;
        $form->name_controller = $this->name;
        $form->token = Tools::getAdminTokenLite( 'AdminModules' );
        $form->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $form->default_form_language = ( int ) Configuration::get( 'PS_LANG_DEFAULT' );
        $form->allow_employee_form_lang = Configuration::get( 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG' );
        $form->title = $this->displayName;
        $form->submit_action = 'submitGiftWrapping';
        $form->toolbar_scroll = true;
        $form->show_cancel_button = false;

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
            ],
            [
                'type' => 'text',
                'label' => $this->l( 'Gift Wrapping Price' ),
                'name' => 'GIFT_WRAPPING_PRICE',
                'required' => true,
                'class' => 'fixed-width-sm',
                'currency' => true,
                'desc' => $this->l( 'Set the price for gift wrapping.' ),
                'suffix' => $this->context->currency->sign,
                'hint' => $this->l( 'Enter the price for gift wrapping.' ),
            ]
        ];

        for ( $i = 1; $i <= 4; $i++ ) {
            $base64Image = Configuration::get( "WRAPPER_IMAGE_$i" );

            $desc = isset( $this->form_errors[ 'WRAPPER_IMAGE' ] )
            ? '<span class="text-danger">' . $this->form_errors[ 'WRAPPER_IMAGE' ] . '</span>'
            : $this->l( 'Supported formats: JPG, PNG, JPEG.' );

            $fields[] = [
                'type' => 'file',
                'label' => $this->l( "Wrapping Image $i" ),
                'name' => "Wrapper_IMAGE_$i",
                'class' => 'form-control col-md-6 flex-row',
                'accept' => 'image/*',
                'desc' => $desc,
            ];

            if ( $base64Image ) {
                $fields[] = [
                    'type' => 'html',
                    'class' => 'wrapper-image-preview',
                    'label' => $this->l( "Current Wrapper Image $i" ),
                    'name' => "wrapper_image_preview_$i",
                    'html_content' => '<div class="wrapper-image-preview">'
                    . '<img src="' . htmlspecialchars( $base64Image ) . '" alt="' . $this->l( "Wrapper Image $i" ) . '" class="img-fluid" style="width:60px;" />'
                    . '</div>',
                ];
            }
        }

        $form->fields_value = [
            'GIFT_WRAPPING_ENABLED' => Configuration::get( 'GIFT_WRAPPING_ENABLED' ),
            'GIFT_WRAPPING_PRICE' => Configuration::get( 'GIFT_WRAPPING_PRICE' ),
        ];

        return $form->generateForm( [
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l( 'Gift Wrapping Settings' ),
                    ],
                    'input' => $fields,
                    'submit' => [
                        'title' => $this->l( 'Save' ),
                    ],
                    'enctype' => 'multipart/form-data',
                ]
            ]
        ] );
    }

    public function hookActionCartSave( $params ) {
        $this->context->cart->gift = 0;
        $this->context->cart->gift_message = '';
    }

    public function hookDisplayHeader() {
        if ( 'cart' === Tools::getValue( 'controller' ) ) {
            $this->context->controller->addCSS( $this->_path . 'views/css/customgiftwrapping.css' );
        }
    }

    public function hookDisplayShoppingCart( $params ) {

        if ( !Configuration::get( 'GIFT_WRAPPING_ENABLED' ) ) {
            return '';
        }

        // Add gift wrap
        if ( Tools::isSubmit( 'submitGiftWrapping' ) && Tools::getValue( 'gift_wrap_selection' ) ) {
            $wrapKey = Tools::getValue( 'gift_wrap_selection' );
            // e.g., WRAPPER_IMAGE_1
            $this->context->cart->gift = 1;
            $this->context->cart->gift_message = $wrapKey;
            $this->context->cart->save();

            Tools::redirect( $_SERVER[ 'HTTP_REFERER' ] );
        }

        // Remove gift wrap
        if ( Tools::isSubmit( 'removeGiftWrapping' ) ) {
            $this->context->cart->gift = 0;
            $this->context->cart->gift_message = '';
            $this->context->cart->save();

            $this->context->cookie->__unset( 'gift_wrap_applied' );
            $this->context->cookie->__unset( 'gift_wrap_image' );
            $this->context->cookie->__unset( 'gift_wrap_price' );

            Tools::redirect( $_SERVER[ 'HTTP_REFERER' ] );
        }

        // Load base64 images
        $images = [];
        for ( $i = 1; $i <= 4; $i++ ) {
            $img = Configuration::get( "WRAPPER_IMAGE_$i" );
            if ( $img && strpos( $img, 'data:image' ) === 0 ) {
                $images[ "WRAPPER_IMAGE_$i" ] = $img;
            }
        }

        $selectedKey = $this->context->cart->gift_message;
        $selectedImage = isset( $images[ $selectedKey ] ) ? $images[ $selectedKey ] : null;

        $selectedImage = $this->context->cart->gift_message;
        $selectedKey = null;

        foreach ( range( 1, 4 ) as $i ) {
            $confKey = "WRAPPER_IMAGE_$i";
            if ( $selectedImage && $selectedImage === Configuration::get( $confKey ) ) {
                $selectedKey = $confKey;
                break;
            }
        }

        $this->context->smarty->assign( [
            'gift_wrapping_enabled' => Configuration::get( 'GIFT_WRAPPING_ENABLED' ),
            'gift_wrapping_price'   => Tools::convertPrice( Configuration::get( 'GIFT_WRAPPING_PRICE' ), $this->context->currency ),
            'wrapper_images'        => $images,
            'gift_wrap_applied'     => ( bool ) $this->context->cart->gift,
            'selected_wrap_image'   => $selectedImage,
            'selected_wrap_key'     => $selectedKey,
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayShoppingCart.tpl' );
    }

    public function hookDisplayShoppingCartFooter( $params ) {
        if ( !$this->context->cart->gift || !$this->context->cart->gift_message ) {
            return '';
        }

        $this->context->smarty->assign( [
            'gift_wrapping_extra_fee' => Tools::convertPrice( Configuration::get( 'GIFT_WRAPPING_PRICE' ), $this->context->currency ),
        ] );

        return $this->display( __FILE__, 'views/templates/hook/displayShoppingCartFooter.tpl' );
    }
}
