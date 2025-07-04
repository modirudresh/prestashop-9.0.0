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

class AdminBlogcommentController extends ModuleAdminController
{

    public $asso_type = 'shop';

    public function __construct()
    {
        $this->table = 'smart_blog_comment';
        $this->className = 'BlogComment';
        $this->context = Context::getContext();
        $this->bootstrap = true;
        if (Shop::isFeatureActive()){
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
		}

        parent::__construct();

        $this->fields_list = array(
            'id_smart_blog_comment' => array(
                'title' => $this->module->l('Id'),
                'width' => 50,
                'type' => 'text',
            ),
            'email' => array(
                'title' => $this->module->l('Email'),
                'width' => 50,
                'type' => 'text',
                'lang' => true
            ),
            'meta_title' => array(
                'title' => $this->module->l('Post Title'),
                'filter_key' => 'smp!meta_title',
                'align' => 'center'
            ),
            'name' => array(
                'title' => $this->module->l('Name'),
                'width' => 150,
                'type' => 'text'
            ),
            'content' => array(
                'title' => $this->module->l('Comment'),
                'width' => 200,
                'type' => 'text',
                'callback'=>'getCommentClean'
            ),
            'created' => array(
                'title' => $this->module->l('Date'),
                'width' => 60,
                'type' => 'text',
                'lang' => true
            ),
            'active' => array(
                'title' => $this->module->l('Status'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false
            )
        );

        $this->bulk_actions = array(
         'delete' => array(
             'text' => $this->module->l('Delete selected'),
             'icon' => 'icon-trash',
             'confirm' => $this->module->l('Delete selected items?')
         )
     );
                 
        $this->_join = ' LEFT JOIN '._DB_PREFIX_.'smart_blog_comment_shop sbs ON a.id_smart_blog_comment=sbs.id_smart_blog_comment && sbs.id_shop IN('.implode(',',Shop::getContextListShopID()).')';
        $this->_join .= ' LEFT JOIN '._DB_PREFIX_.'smart_blog_post_lang smp ON a.id_post=smp.id_smart_blog_post and smp.id_lang = '.(int)Context::getContext()->language->id;
 
        $this->_select = 'sbs.id_shop';
        $this->_defaultOrderBy = 'a.id_smart_blog_comment';
        $this->_defaultOrderWay = 'DESC';
        $this->_select = 'smp.meta_title';
         //$this->_defaultOrderBy = 'a.id_smart_blog_comment';

        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->_group = 'GROUP BY a.id_smart_blog_comment';
        }

        parent::__construct();
    }

    public function renderList()
    {

        // adds actions
   
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // re-defines toolbar & buttons
        $this->toolbar_title = $this->module->l('REVIEWS WAITING FOR APPROVAL');
        $this->initToolbar();



        $this->list_id = 'orders';
        $this->_filterHaving = null;
        $this->_where = ' AND a.active = 0';
        $first_list = parent::renderList();
        // unsets actions
        $this->actions = array();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->is_template_list = true;

        // re-defines toolbar & buttons
        $this->toolbar_title = $this->module->l('APPROVED REVIEWS');
        $this->initToolbar();

        unset($this->_orderBy);
        $this->_orderBy = 'a.id_smart_blog_comment';

        unset($this->list_id);
        $this->list_id = 'templates';

        unset($this->_filterHaving);
        $this->_filterHaving = null;

        unset($this->_where);
        $this->_where = ' AND a.active = 1';

        $second_list = null;

        // inits list
        $second_list = parent::renderList();

        return $first_list . $second_list;

    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Blog Comment'),
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->module->l('Comment'),
                    'name' => 'content',
                    'rows' => 10,
                    'cols' => 62,
                    'required' => false,
                    'desc' => $this->module->l('Enter Your Category Description')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Email'),
                    'name' => 'email',
                    'required' => false,
                    'readonly' => true,

                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Name'),
                    'name' => 'name',
                    'rows' => 10,
                    'cols' => 62,
                    'required' => false,
                    'readonly' => true,

                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Post Title'),
                    'name' => 'meta_title',
                    'required' => false,
                    'readonly' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled')
                        )
                    ),
                     'desc' => $this->module->l('You Can Enable or Disable Your Comments')
                )
            ),
            'submit' => array(
                'title' => $this->module->l('Save'),
            )
        );

        if (!$this->loadObject(true))
            return;

        $this->fields_form['submit'] = array(
            'title' => $this->module->l('Save   '),
        );
        return parent::renderForm();
    }

    public static function getCommentClean($comment)
    {
        return strip_tags(stripslashes($comment));
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

}
