<?php
/**
 * 2007-2016 PrestaShop
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
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')){
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class SmartBlogCategories extends Module implements WidgetInterface
{ 
    public $templateFile;
    
    public function __construct()
    {
        $this->name       = 'smartblogcategories';
        $this->tab        = 'front_office_features';
        $this->version    = '2.0.4';
        $this->bootstrap  = true;
        $this->author     = 'SmartDataSoft';
        
        parent::__construct();
        
        $this->displayName      = $this->l('Smart Blog Categories');
        $this->description      = $this->l('The Most Powerfull Presta shop Blog  Module\'s tag - by smartdatasoft');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
        
        $this->templateFile = 'module:smartblogcategories/views/templates/front/smartblogcategories.tpl';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionsbdeletecat')
            && $this->registerHook('actionsbnewcat')
            && $this->registerHook('actionsbtogglecat')
            && $this->registerHook('actionsbupdatecat')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displaySmartBlogLeft')
            && $this->registerHook('displaySmartBlogRight')
            && Configuration::updateValue('SMART_BLOG_CATEGORIES_DHTML', 0)
            && Configuration::updateValue('SMART_BLOG_CATEGORIES_POST_COUNT', 0)
            && Configuration::updateValue('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY', 0)
            && Configuration::updateValue('SMART_BLOG_CATEGORIES_DROPDOWN', 0)
            && Configuration::updateValue('sort_category_by', 'id_desc')
            && Configuration::updateValue('smartblogrootcat', 1);
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->DeleteCache();
    }
    
    public function getContent()
    {
        $html = '';
        // If we try to update the settings
        if (Tools::isSubmit('submitModule')) {
            
            $smartblogrootcat = Tools::getValue('smartblogrootcat');
            Configuration::updateValue('smartblogrootcat', (int) $smartblogrootcat);
            
            $sort_category_by = Tools::getValue('sort_category_by');
            Configuration::updateValue('sort_category_by', $sort_category_by);
            
            $dhtml = Tools::getValue('SMART_BLOG_CATEGORIES_DHTML');
            Configuration::updateValue('SMART_BLOG_CATEGORIES_DHTML', (int) $dhtml);
            
            Configuration::updateValue('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY', (int) Tools::getValue('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY'));
            
            $dhtml = Tools::getValue('SMART_BLOG_CATEGORIES_POST_COUNT');
            Configuration::updateValue('SMART_BLOG_CATEGORIES_POST_COUNT', (int) $dhtml);
            
            Configuration::updateValue('SMART_BLOG_CATEGORIES_DROPDOWN', (int) Tools::getValue('SMART_BLOG_CATEGORIES_DROPDOWN'));
            
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
            $this->_clearCache($this->templateFile);
            Tools::redirectAdmin('index.php?tab=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'));
        }
        
        $html .= $this->renderForm();
        
        return $html;
    }
    
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display as dropdown'),
                        'name' => 'SMART_BLOG_CATEGORIES_DROPDOWN',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show only assigned categories of post'),
                        'name' => 'SMART_BLOG_ASSIGNED_CATEGORIES_ONLY',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Sort Sidebar Category List By'),
                        'name' => 'sort_category_by',
                        'desc' => 'Blog category list that is shown in the blog page sidebars',
                        'required' => false,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'name_ASC',
                                    'name' => 'Name ASC (A-Z)'
                                ),
                                array(
                                    'id_option' => 'name_DSC',
                                    'name' => 'Name DESC (Z-A)'
                                ),
                                array(
                                    'id_option' => 'id_ASC',
                                    'name' => 'Id ASC'
                                ),
                                array(
                                    'id_option' => 'id_ASC',
                                    'name' => 'Id DESC'
                                )
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show Root Category (HOME)'),
                        'name' => 'smartblogrootcat',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show post counts'),
                        'name' => 'SMART_BLOG_CATEGORIES_POST_COUNT',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Dynamic'),
                        'name' => 'SMART_BLOG_CATEGORIES_DHTML',
                        'desc' => $this->l('Activate dynamic (animated) mode for category sublevels.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            )
        );
        
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;        
        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token         = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars      = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(array(
            $fields_form
        ));
    }
	
    public function getConfigFieldsValues()
    {
        return array(
            'smartblogrootcat' => Tools::getValue('smartblogrootcat', Configuration::get('smartblogrootcat')),
            'SMART_BLOG_CATEGORIES_DHTML' => Tools::getValue('SMART_BLOG_CATEGORIES_DHTML', Configuration::get('SMART_BLOG_CATEGORIES_DHTML')),
            'SMART_BLOG_CATEGORIES_POST_COUNT' => Tools::getValue('SMART_BLOG_CATEGORIES_POST_COUNT', Configuration::get('SMART_BLOG_CATEGORIES_POST_COUNT')),
            'sort_category_by' => Tools::getValue('sort_category_by', Configuration::get('sort_category_by')),
            'SMART_BLOG_ASSIGNED_CATEGORIES_ONLY' => Tools::getValue('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY', Configuration::get('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY')),
            'SMART_BLOG_CATEGORIES_DROPDOWN' => Tools::getValue('SMART_BLOG_CATEGORIES_DROPDOWN', Configuration::get('SMART_BLOG_CATEGORIES_DROPDOWN'))
            
        );
    }
    
    public function hookLeftColumn($params)
    {
        
        if (Module::isInstalled('smartblog') != 1) {
            $this->smarty->assign(array(
                'smartmodname' => $this->name
            ));
            return $this->display(__FILE__, 'views/templates/front/install_required.tpl');
        } else {
		
			if (!$this->isCached($this->templateFile)) {
				$view_data = array();
				$id_lang   = $this->context->language->id;

				/*arif call*/

				$maxdepth = 4;
				// Get all groups for this customer and concatenate them as a string: "1,2,3..."
				$groups   = implode(', ', Customer::getGroupsStatic((int) $this->context->customer->id));

				$active = 1;
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT *
				FROM `' . _DB_PREFIX_ . 'smart_blog_category` c
				LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_category_lang` cl ON c.`id_smart_blog_category` = cl.`id_smart_blog_category`
				LEFT JOIN `' . _DB_PREFIX_ . 'smart_blog_category_shop` cs ON c.`id_smart_blog_category` = cs.`id_smart_blog_category`
				WHERE   `id_lang` = ' . (int) $id_lang . '
				' . ($active ? 'AND `active` = 1' : '') . '
				AND cs.`id_shop` = ' . (int) Context::getContext()->shop->id . '
				ORDER BY `meta_title` ASC');

				$resultParents = array();
				$resultIds     = array();

				foreach ($result as &$row) {
					$resultParents[$row['id_parent']][] =& $row;
					$resultIds[$row['id_smart_blog_category']] =& $row;
				}

				$root_id        = (Configuration::get('smartblogrootcat') || Configuration::get('SMART_BLOG_CATEGORIES_DROPDOWN')) ? 0 : 1;
				$blockCategTree = $this->getTree($resultParents, $resultIds, 10, 0);

				if (!Configuration::get('smartblogrootcat')) {
					$blockCategTree = array(
						'id' => 0,
						'link' => '',
						'name' => '',
						'desc' => '',
						'children' => $blockCategTree['children']?$blockCategTree['children'][0]['children']:array()
					);
				}

				$isDhtml = Configuration::get('SMART_BLOG_CATEGORIES_DHTML');
				$this->smarty->assign('blockCategTree', $blockCategTree);
				$this->smarty->assign('isDhtml', $isDhtml);
				$this->smarty->assign('isDropdown', Configuration::get('SMART_BLOG_CATEGORIES_DROPDOWN'));
				$this->smarty->assign('select', true);

			}
			return $this->fetch($this->templateFile, $this->getCacheId());

		}
    }
    
    public function getTree($resultParents, $resultIds, $maxDepth, $id_smart_blog_category = null, $currentDepth = 0)
    {
        if (is_null($id_smart_blog_category)) {
            $id_smart_blog_category = $this->context->shop->getCategory();
        }
        
        $children = array();
        
        if (isset($resultParents[$id_smart_blog_category]) && count($resultParents[$id_smart_blog_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth)) {
            foreach ($resultParents[$id_smart_blog_category] as $subcat) {
                $children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_smart_blog_category'], $currentDepth + 1);
            }
        }
        
        if (isset($resultIds[$id_smart_blog_category])) {
            
            $tmp_all_child     = $id_smart_blog_category . BlogCategory::getAllChildCategory($id_smart_blog_category, '');
            $tmp_all_child     = array_values(array_unique(explode(",", $tmp_all_child)));
            $tmp_post_of_child = BlogCategory::getTotalPostOfChildParent($tmp_all_child);
            $total_post        = (Configuration::get('SMART_BLOG_CATEGORIES_POST_COUNT')) ? ' (' . $tmp_post_of_child . ')' : '';
            
            $link = SmartBlogLink::getSmartBlogCategoryLink($id_smart_blog_category, $resultIds[$id_smart_blog_category]['link_rewrite']);
            $name = $resultIds[$id_smart_blog_category]['name'];
            $desc = $resultIds[$id_smart_blog_category]['description'];
            if ($tmp_post_of_child == 0 && Configuration::get('SMART_BLOG_ASSIGNED_CATEGORIES_ONLY')) {
                $name       = '';
                $link       = '';
                $total_post = '';
            }
            
            $level_depth = str_repeat('&nbsp;', $resultIds[$id_smart_blog_category]['level_depth'] * 2);
        } else {
            $level_depth = $total_post = $link = $name = $desc = '';
        }
        
        return array(
            'id' => $id_smart_blog_category,
            'link' => $link,
            'name' => $name . $total_post,
            'level_depth' => $level_depth,
            'desc' => $desc,
            'children' => $children
        );
    }
	
    public function hookRightColumn($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function hookdisplaySmartBlogLeft($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function hookdisplaySmartBlogRight($params)
    {
        return $this->hookLeftColumn($params);
    }
	
    public function DeleteCache()
    {
        return $this->_clearCache($this->templateFile, $this->getCacheId());
    }
	
    public function hookactionsbdeletecat($params)
    {
        return $this->DeleteCache();
    }
	
    public function hookactionsbnewcat($params)
    {
        return $this->DeleteCache();
    }
	
    public function hookactionsbupdatecat($params)
    {
        return $this->DeleteCache();
    }
    public function hookactionsbtogglecat($params)
    {
        return $this->DeleteCache();
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}
    
}