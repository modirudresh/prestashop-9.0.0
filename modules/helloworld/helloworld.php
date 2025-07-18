<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Helloworld extends Module
{
    public function __construct()
    {
        $this->name = 'helloworld';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Rudresh';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Hello World Module');
        $this->description = $this->l('Displays a Hello World message using a hook.');
    }

    public function install()
    {
        // Register hooks during installation
        if (!parent::install()) {
            return false;
        }
        // Register the hooks
        $this->registerHook('displayHome');
        $this->registerHook('displayProductAdditionalInfo');
        $this->registerHook('displayFooterbefore');
        // Return true if all hooks are registered successfully
        return true;
    }


    public function uninstall()
    {
        // Unregister hooks if necessary
        return $this->unregisterHook('displayHome')
            && $this->unregisterHook('displayProductAdditionalInfo')
            && $this->unregisterHook('displayFooterbefore')
            && parent::uninstall();
    }

    public function hookDisplayHome($params)
    {
        return '<div class="hello-world">Hello World from your first module!</div>';
    }
    public function hookDisplayProductAdditionalInfo($params)
    {
        return '<div class="hello-world">Hello World from the product page!</div>';
    }
    public function hookDisplayFooterbefore($params)
    {
        return '<div class="hello-world">Hello World before the footer!</div>';
    }
}
// Register the module in PrestaShop
if (!Module::isInstalled('helloworld')) {
    $module = new Helloworld();
    $module->install();
}