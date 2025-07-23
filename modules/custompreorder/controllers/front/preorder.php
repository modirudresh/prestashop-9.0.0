<?php

class PreOrderPreorderModuleFrontController extends ModuleFrontController {
    public function postProcess() {
        $id_product = ( int )Tools::getValue( 'id_product' );
        $id_customer = ( int )$this->context->customer->id;

        if ( $id_product && $id_customer ) {
            Db::getInstance()->insert( 'preorder', [
                'id_product' => $id_product,
                'id_customer' => $id_customer,
                'date_add' => date( 'Y-m-d H:i:s' ),
            ] );
            $this->context->controller->success = $this->module->l( 'Product pre-ordered successfully!' );
        }

        Tools::redirect( $_SERVER[ 'HTTP_REFERER' ] );
    }
}
