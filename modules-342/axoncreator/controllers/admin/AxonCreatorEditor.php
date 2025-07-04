<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

use AxonCreator\Wp_Helper;
use AxonCreator\Plugin;

class AxonCreatorEditorController extends ModuleAdminController
{
    public $name = 'AxonCreatorEditor';

    public $display_header = false;

    public $content_only = true;

    public function initContent()
    {
        if ( ( !Tools::getValue('id_post') && !Tools::getValue('key_related') ) || !Tools::getValue('post_type') ) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminAxonCreatorHome'));
        }
		if( Wp_Helper::set_global_var() ){
			Plugin::instance()->editor->init();
		}
		die();
    }
	
    public function initProcess() {}

    public function initBreadcrumbs( $tab_id = null, $tabs = null ) {}

    public function initModal() {}

    public function initToolbarFlags() {}

    public function initNotifications() {}
	
}
