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

use AxonCreator\Wp_Helper;

class AdminAxonCreatorHookController extends ModuleAdminController
{
    public $name;

    public function __construct()
    {		
        $this->bootstrap = true;
        $this->className = 'AxonCreatorRelated';
        $this->table = 'axon_creator_related';

		Shop::addTableAssociation( 'axon_creator_related', array('type' => 'shop') );
		
        $this->addRowAction('edit');
        $this->addRowAction('delete');
		
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
		
        $this->_orderBy = 'id_axon_creator_related';
        $this->identifier = 'id_axon_creator_related';

        $this->fields_list = array(
            'id_axon_creator_related' => array('title' => $this->module->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'key_related' => array('title' => $this->module->l('Hook Name'), 'width' => 'auto'),
            'id_post' => array('title' => $this->module->l('Active'), 'callback' => 'getPostStatus', 'search' => false, 'align' => 'center', 'type' => 'bool')
        );
		
		$this->_where = ' AND `post_type` = "hook"';
		
        $this->bulk_actions = array(
			'enableSelection' => [
				'text' => $this->module->l('Enable selection'),
				'icon' => 'icon-power-off text-success',
			],
			'disableSelection' => [
				'text' => $this->module->l('Disable selection'),
				'icon' => 'icon-power-off text-danger',
			],
			'divider' => [
				'text' => 'divider',
			],
            'delete' => [
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ],
        );

        $this->name = 'AdminAxonCreatorHook';
    }
	
	public function initContent()
    {
		if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
			parent::initContent();
		}else{
			$this->context->smarty->assign(array(
				'content' => '<p class="alert alert-warning">'.$this->module->l('You cannot manage the hook from a "All Shops" or a "Group Shop" context, select directly the shop you want to edit').'</p>'
			));		
		}		
	}

    public static function getPostStatus($value, $object)
    {
		$post = new AxonCreatorPost((int)$object['id_post']);

        return '<a class="list-action-enable action-'.($post->active?'enabled':'disabled').'" href="'.Wp_Helper::get_exit_to_dashboard( 'AdminAxonCreatorHook', [ 'id_axon_creator_post' => $object['id_post'], 'statusaxon_creator_post' ] ).'" title="Enabled"><i class="icon-check '.($post->active?'':'hidden').'"></i><i class="icon-remove '.($post->active?'hidden':'').'"></i></a>';
    }
	
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('Axon - Hook');
    }
	
    public function renderList()
    {				
        return Wp_Helper::api_get_notification() . parent::renderList();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit' . $this->className)) {
			
			$key_related = Tools::getValue('key_related');
			
			Wp_Helper::$id_shop = $this->context->shop->id;
			Wp_Helper::$post_type = 'hook';
			Wp_Helper::$key_related = $key_related;
			
			$related = Wp_Helper::getRelatedByKey();
			
			if($related && !(int)Tools::getValue('id_axon_creator_related')){
				Tools::redirectAdmin($this->context->link->getAdminLink($this->name));
			}
													
			if((int)Tools::getValue('id_axon_creator_related')){
				$obj = new AxonCreatorRelated((int)Tools::getValue('id_axon_creator_related'));
				$post = new AxonCreatorPost((int)$obj->id_post);
			}else{
				$obj = new AxonCreatorRelated();
				$post = new AxonCreatorPost();
			}
			
			if($related && $related['id_axon_creator_related'] != $obj->id){
				$key_related = $obj->key_related;
			}
			
            if (!Hook::isModuleRegisteredOnHook($this->module, $key_related, $this->context->shop->id)) {
                Hook::registerHook($this->module, $key_related);
            }
			
			$post->id_employee = (int) $this->context->employee->id;
			$post->title = $key_related;
			$post->post_type = 'hook';
			$post->active = (int)Tools::getValue('active');
			$returnObjectPost = $post->save();
			
            if (!$returnObjectPost) {
                return false;
            }
			
			$obj->post_type = 'hook';
			$obj->key_related = $key_related;
			$obj->id_post = $post->id;
            $returnObject = $obj->save();

            if (!$returnObject) {
                return false;
            }
			
			Tools::redirectAdmin($this->context->link->getAdminLink($this->name) . '&id_axon_creator_related='.$obj->id .'&updateaxon_creator_related');
        }
		
		if (Tools::isSubmit('statusaxon_creator_post')) {
			$post = new AxonCreatorPost((int)Tools::getValue('id_axon_creator_post'));
			if($post->active){
				$post->active = 0;
			}else{
				$post->active = 1;
			}
			$returnObjectPost = $post->save();
			
            if (!$returnObjectPost) {
                return false;
            }
			
			Tools::redirectAdmin($this->context->link->getAdminLink($this->name));
		}
		
        return parent::postProcess();
    }
	
    /**
     * Enable multiple items.
     *
     * @return bool true if success
     */
    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    /**
     * Disable multiple items.
     *
     * @return bool true if success
     */
    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    /**
     * Toggle status of multiple items.
     *
     * @param bool $status
     *
     * @return bool true if success
     *
     * @throws PrestaShopException
     */
    protected function processBulkStatusSelection($status)
    {
        $result = true;
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id) {
                /** @var ObjectModel $object */
				$obj = new AxonCreatorRelated((int) $id);
				
                $object = new AxonCreatorPost((int) $obj->id_post);
                $object->active = (int) $status;
                $result &= $object->update();
            }
        }

        return $result;
    }

    public function renderForm()
    {		
		$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		
        $obj = new AxonCreatorRelated((int) Tools::getValue('id_axon_creator_related'));
				
        if ($obj->id){
            if (!Hook::isModuleRegisteredOnHook($this->module, $obj->key_related, $this->context->shop->id)) {
                Hook::registerHook($this->module, $obj->key_related);
            }

            $url = $this->context->link->getAdminLink('AxonCreatorEditor').'&post_type=hook&id_post=' . $obj->id_post . '&id_lang='. $id_lang;
			$post = new AxonCreatorPost((int)$obj->id_post);
			$obj->active = (int) $post->active;
        }
        else{
            $url = false;
			$obj->active = 1;
        }
				
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => isset($obj->id) ? $this->module->l('Edit layout.') : $this->module->l('New layout'),
                'icon' => isset($obj->id) ? 'icon-edit' : 'icon-plus-square',
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_axon_creator_related',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Hook'),
                    'name' => 'key_related',
                    'class' => 'fixed-width-xxl',
                    'options' => array(
                        'query' => $this->getDisplayHooksForHelper(),
                        'id' => 'name',
                        'name' => 'name'
                    )
                ),
				array(
					'type'     => 'switch',
					'label'    => $this->module->l('Status'),
					'name'     => 'active',
					'is_bool'  => true,
					'values'   => array(
						array(
							'id'    => 'active',
							'value' => 1,
							'label' => $this->module->l('Enabled'),
						),
						array(
							'id'    => 'active',
							'value' => 0,
							'label' => $this->module->l('Disabled'),
						),
					),
				),
                array(
                    'type' => 'page_trigger',
                    'label' => '',
                    'url'  => $url,
                )
            ),
            'buttons' => array(
                'cancelBlock' => array(
                    'title' => $this->module->l('Cancel'),
                    'href' => (Tools::safeOutput(Tools::getValue('back', false)))
                        ?: $this->context->link->getAdminLink($this->name),
                    'icon' => 'process-icon-cancel',
                ),
            ),
            'submit' => array(
                'name' => 'submit' . $this->className,
                'title' => $this->module->l('Save'),
            ),
        );

        if (Tools::getValue('name')) {
            $obj->title = Tools::getValue('name');
        }

        $helper = $this->buildHelper();
        $helper->fields_value = (array) $obj;
        return Wp_Helper::api_get_notification() . $helper->generateForm($this->fields_form);
    }

    protected function buildHelper()
    {
        $helper = new HelperForm();

        $helper->module = $this->module;
		$helper->table = $this->table;
        $helper->identifier = $this->className;
        $helper->token = Tools::getAdminTokenLite($this->name);
        $helper->languages = $this->_languages;
        $helper->currentIndex = $this->context->link->getAdminLink($this->name);
        $helper->default_form_language = $this->default_form_language;
        $helper->allow_employee_form_lang = $this->allow_employee_form_lang;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = $this->initToolbar();

        return $helper;
    }
	
	/*------------------get Front Hook----------------------------*/

    public function getDisplayHooksForHelper()
    {
        return $hooks = [
			['name' => 'displayLeftColumn'],
			['name' => 'displayRightColumn'],
			['name' => 'displayProductAccessories'],
			['name' => 'displayProductSameCategory'],
			['name' => 'displayFooterProduct'],
            ['name' => 'displayLeftColumnProduct'],
			['name' => 'displayProductSummary'],
			['name' => 'displayRightColumnProduct'],
			['name' => 'displayContactPageBuilder'],
			['name' => 'displayShoppingCartFooter'],
            ['name' => 'displayHeaderCategory'],
            ['name' => 'displayFooterCategory'],
			['name' => 'display404PageBuilder'],
		];
    }		
}
