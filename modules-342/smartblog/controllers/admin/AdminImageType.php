<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminImageTypeController extends ModuleAdminController
{

    public function __construct()
    {
        $this->table = 'smart_blog_imagetype';
        $this->className = 'BlogImageType';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->bootstrap = true;

        parent::__construct();

        $this->fields_list = array(
            'id_smart_blog_imagetype' => array(
                'title' => $this->module->l('Id'),
                'width' => 100,
                'type' => 'text',
            ),
            'type_name' => array(
                'title' => $this->module->l('Type Name'),
                'width' => 350,
                'type' => 'text',
            ),
            'width' => array(
                'title' => $this->module->l('Width'),
                'width' => 60,
                'type' => 'text',
            ),
            'height' => array(
                'title' => $this->module->l('Height'),
                'width' => 60,
                'type' => 'text',
            ),
            'type' => array(
                'title' => $this->module->l('Type'),
                'width' => 220,
                'type' => 'text',
            )
        );
		
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ),
        );
		
        parent::__construct();
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Blog Category'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Name for the image type'),
                    'name' => 'type_name',
                    'size' => 60,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Width'),
                    'name' => 'width',
                    'size' => 15,
                    'required' => true,
					'suffix' => 'pixels'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Height'),
                    'name' => 'height',
                    'size' => 15,
                    'required' => true,
					'suffix' => 'pixels'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Type'),
                    'name' => 'type',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 'post',
                                'name' => 'Post'
                            ),
                            array(
                                'id_option' => 'category',
                                'name' => 'Category'
                            ),
                            array(
                                'id_option' => 'author',
                                'name' => 'Author'
                            )
                        ),
                        'id' => 'id_option',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->module->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active',
                            'value' => 1,
                            'label' => $this->module->l('Enabled')
                        ),
                        array(
                            'id' => 'active',
                            'value' => 0,
                            'label' => $this->module->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->module->l('Save'),
            )
        );

        if (!($BlogImageType = $this->loadObject(true)))
            return;

        $this->fields_form['submit'] = array(
            'title' => $this->module->l('Save   '),
        );
        return parent::renderForm();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

}
