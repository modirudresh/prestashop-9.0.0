<?php

if ( !defined( '_PS_VERSION_' ) ) {
    exit;
}

class CustomPreOrder extends Module {

    public function __construct() {
        $this->name = 'custompreorder';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l( 'Custom Pre-Order' );
        $this->description = $this->l( 'Allow customers to pre-order out-of-stock products.' );
    }

    public function install() {
        return parent::install()
        && $this->registerHook( 'displayProductButtons' )
        && $this->installDb();
    }

    public function uninstall() {
        return parent::uninstall()
        && $this->uninstallDb();
    }

    protected function installDb() {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_."preorder` (
            `id_preorder` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT UNSIGNED NOT NULL,
            `id_customer` INT UNSIGNED NOT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_preorder`)
        ) ENGINE="._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        return Db::getInstance()->execute( $sql );
    }

    protected function uninstallDb() {
        $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'preorder`';
        return Db::getInstance()->execute( $sql );
    }

    public function hookDisplayProductButtons( $params ) {
        $product = new Product( $params[ 'product' ][ 'id_product' ], false, $this->context->language->id );

        if ( !$product->checkQty( 1 ) ) {
            $this->context->smarty->assign( [
                'product_id' => $product->id,
                'link' => $this->context->link->getModuleLink( $this->name, 'preorder' )
            ] );
            return $this->display( __FILE__, 'views/templates/hook/preorder_button.tpl' );
        }
        return '';
    }
}
