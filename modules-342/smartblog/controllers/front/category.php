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

class SmartBlogCategoryModuleFrontController extends ModuleFrontController
{
    protected $category;
	
    public function init()
    {
        parent::init();

        switch ((int)Configuration::get('smartblogurlpattern')) {
            case 1:
                $id_category = smartblog::categoryslug2id(Tools::getValue('rewrite'));
                break;
            case 2:
                $id_category = Tools::getValue('id_blog_category');               
                break; 
            default:
                $id_category = Tools::getValue('id_blog_category');
        }
        
		$this->category = new BlogCategory($id_category, $this->context->language->id, $this->context->shop->id);

		if (!Validate::isLoadedObject($this->category) || !$this->category->isAssociatedToShop() || !$this->category->active) {
			Tools::redirect('index.php?controller=404');
		}
    }
	
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
		
		$page['page_name'] = 'blog-category';
		
        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            $this->context->shop->theme->getLayoutNameForPage('module-smartblog-category') => true,
            'page-blog-category' => true,
            'tax-display-'.($this->getDisplayTaxesLabel() ? 'enabled' : 'disabled') => true,
        );
				
		$page['body_classes'] = $body_classes;
		$page['meta']['description'] = $this->category->meta_description;
		$page['meta']['keywords'] = $this->category->meta_keyword;
		$page['meta']['title'] = $this->category->meta_title;
        
        return $page;
    }
	
    protected function getAlternativeLangsUrl()
    {
        $alternativeLangs = array();
        $languages = Language::getLanguages(true, $this->context->shop->id);

        if ($languages < 2) {
            // No need to display alternative lang if there is only one enabled
            return $alternativeLangs;
        }

        foreach ($languages as $lang) {
            $category = new BlogCategory($this->category->id, $lang['id_lang'], $this->context->shop->id);
            $alternativeLangs[$lang['language_code']] = smartblog::GetSmartBlogLink('smartblog_category_rule', array('id_blog_category' => $category->id, 'rewrite' => $category->link_rewrite), $lang['id_lang']);
        }

        return $alternativeLangs;
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
		
        $breadcrumb['links'][] = [
            'title' => $this->module->l('Blog', 'category'),
            'url' => smartblog::GetSmartBlogLink('smartblog')
        ];
		
        $breadcrumb['links'][] = [
            'title' => $this->category->name,
			'url' => smartblog::GetSmartBlogLink('smartblog_category_rule', array('id_blog_category' => $this->category->id, 'rewrite' => $this->category->link_rewrite))
        ];
				
        return $breadcrumb;
    }
	
    public function initContent()
    {             

        $limit_start = 0;
        $posts_per_page = Configuration::get('smartpostperpage');
        $limit = $posts_per_page;
							
		$total = (int)SmartBlogPost::getToltalByCategory($this->context->language->id, $this->category->id);
		Hook::exec('actionsbcat', array('id_blog_category' => $this->category->id)); 
		
        $totalpages = ceil($total / $posts_per_page);
		
        if ((boolean) Tools::getValue('page')) {
            $c = (int)Tools::getValue('page');
			if(!$c){
				$c = 1;	
			}
            $limit_start = $posts_per_page * ($c - 1);
        }

		if (file_exists(_PS_MODULE_DIR_ . 'smartblog/images/category/' . $this->category->id . '.jpg')) {
			$protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';  
			$uri_path = __PS_BASE_URI__ . 'modules/smartblog/images/category/' . $this->category->id . '.jpg';
			$this->category->img = $protocol_content.Tools::getMediaServer($uri_path).$uri_path;
		} else {
			$cat_image = 'no';
		}
						
		$posts = BlogPostCategory::getToltalByCategory($this->context->language->id, $this->category->id, $limit_start, $limit);

		if(!$posts){
			$posts = array();
		}

        parent::initContent();

        $this->context->smarty->assign(array(
			'blog_category_post_layout' => Configuration::get('blog_category_post_layout'),
            'posts' => SmartBlogPost::ConvertPost($posts, Configuration::get('blog_category_post_image_type')),
			'category' => (array)$this->category,
            'smartdisablecatimg' => Configuration::get('smartdisablecatimg'),
            'smartshowauthor' => Configuration::get('smartshowauthor'),
            'smartshowauthorstyle' => Configuration::get('smartshowauthorstyle'),
            'smartshowviewed' => Configuration::get('smartshowviewed'),
            'limit' => isset($limit) ? $limit : 0,
            'limit_start' => isset($limit_start) ? $limit_start : 0,
            'c' => isset($c) ? $c : 1,
            'total' => $total,
            'pagenums' => $totalpages - 1,
        ));
		
        $this->setTemplate('module:smartblog/views/templates/front/category.tpl');
    }

    public function getCanonicalURL()
    {
        return smartblog::GetSmartBlogLink('smartblog_category_rule', array('id_blog_category' => $this->category->id, 'rewrite' => $this->category->link_rewrite));
    }
}
