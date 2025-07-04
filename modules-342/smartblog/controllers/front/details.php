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

class SmartBlogDetailsModuleFrontController extends ModuleFrontController
{
    protected $post;
	
    public function init()
    {
        parent::init();
		
		$this->context->controller->registerJavascript('modules-smartblog', 'modules/'.$this->module->name.'/js/smartblog.min.js', ['position' => 'bottom', 'priority' => 150]);
		
        Media::addJsDef(array(
			'opBlog' => array('ajax' => $this->context->link->getModuleLink('smartblog', 'ajax', 
																					array(), null, null, null, true))
        ));
		
        switch ((int) Configuration::get('smartblogurlpattern')) {
            case 1:
                $id_post = smartblog::slug2id(Tools::getValue('rewrite'));
                break;
            case 2:
                $id_post = pSQL(Tools::getvalue('id_post'));
                break;
            default:
                $id_post = pSQL(Tools::getvalue('id_post'));
        }
		
        $this->post = new SmartBlogPost($id_post, $this->context->language->id, $this->context->shop->id);			
		
		$_GET['id_blog'] = $this->post->id;
		
		if (!Validate::isLoadedObject($this->post) || !$this->post->isAssociatedToShop() || !$this->post->active) {
			Tools::redirect('index.php?controller=404');
		}
				
    }
	
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
		
		$page['page_name'] = 'blog-details';
		
        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            $this->context->shop->theme->getLayoutNameForPage('module-smartblog-details') => true,
            'page-blog-details' => true,
            'tax-display-'.($this->getDisplayTaxesLabel() ? 'enabled' : 'disabled') => true,
        );
				
		$page['body_classes'] = $body_classes;
		$page['meta']['description'] = $this->post->meta_description;
		$page['meta']['keywords'] = $this->post->meta_keyword;
		$page['meta']['title'] = $this->post->meta_title;
		
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
            $post = new SmartBlogPost($this->post->id, $lang['id_lang'], $this->context->shop->id);
            $alternativeLangs[$lang['language_code']] = smartblog::GetSmartBlogLink('smartblog_post_rule', array('id_post' => $post->id, 'rewrite' => $post->link_rewrite), $lang['id_lang']);
        }

        return $alternativeLangs;
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
		
        $breadcrumb['links'][] = [
            'title' => $this->module->l('Blog', 'details'),
            'url' => smartblog::GetSmartBlogLink('smartblog')
        ];
		
        $breadcrumb['links'][] = [
            'title' => $this->post->meta_title,
			'url' => SmartBlogLink::getSmartBlogPostLink($this->post->id, $this->post->link_rewrite)
        ];
				
        return $breadcrumb;
    }

    public function initContent()
    {
		Hook::exec('actionsbsingle', array('id_post' => $this->post->id));
        
		$blogcomment = new BlogComment();
		$SmartBlogPost = new SmartBlogPost();
		$BlogCategory = new BlogCategory();

		$id_lang = $this->context->language->id;

		$post = $SmartBlogPost->getPost($this->post->id, $id_lang);    

		$title_category = array();
		$getPostCategories = $this->getPostCategories($this->post->id); 

		$i = 0;
		foreach($getPostCategories as $category){ 
			$title_category[] = $BlogCategory->getNameCategory($getPostCategories[$i]['id_smart_blog_category']); 
			$i++;
		} 

		$tags = $SmartBlogPost->getBlogTags($this->post->id);
		$comment = $blogcomment->getComment($this->post->id);
		$countcomment = $blogcomment->getToltalComment($this->post->id);

		$posts_previous = SmartBlogPost::getPreviousPostsById($id_lang, $this->post->id);

		$posts_next = SmartBlogPost::getNextPostsById($id_lang, $this->post->id);

		SmartBlogPost::postViewed($this->post->id);

		//here we can give validation if category page or other page it will show

		$post['created'] =  smartblog::displayDate($post['created']);
		
        $filteredBlog = Hook::exec(
            'filterBlogContent',
            ['object' => $post],
            $id_module = null,
            $array_return = false,
            $check_exceptions = true,
            $use_push = false,
            $id_shop = null,
            $chain = true
        );
		
        if (!empty($filteredBlog['object'])) {
            $post = $filteredBlog['object'];
        }

		$this->context->smarty->assign(array(
			'modules_dir'=> $this->context->link->getMediaLink(_MODULE_DIR_),
			'post' => $post,
			'posts_next' => $posts_next,
			'posts_previous' => $posts_previous,
			'comments' => $comment ? $comment : [],
			'enableguestcomment' => Configuration::get('smartenableguestcomment'),
			'is_logged' => $this->context->customer->isLogged(true),
			'is_logged_email' => $this->context->customer->email,
			'is_logged_name' => $this->context->customer->firstname.' '.$this->context->customer->lastname,
			'tags' => $tags,
            'id_module' => $this->module->id,
			'cat_link_rewrite' => (isset($title_category[0][0]['link_rewrite'])) ? $title_category[0][0]['link_rewrite'] : '',
			'smartshowauthorstyle' => Configuration::get('smartshowauthorstyle'),
			'smartshowauthor' => Configuration::get('smartshowauthor'),
			'countcomment' => $countcomment,
		));

		$post_images = array();

		$imageType = Configuration::get('smartlistpostimagesize');
		$images = BlogImageType::GetImageByType($imageType);

		foreach($images as $image){
			if($image['type'] == 'post'){
				$post_images['type'] = $imageType;
				$post_images['width'] = $image['width'];
				$post_images['height'] = $image['height'];
				break;
			}
		}

		$this->context->smarty->assign('post_images', $post_images);
        		
		parent::initContent();

        $this->setTemplate('module:smartblog/views/templates/front/details.tpl');
    }
	
    public function getPostCategories($id_post){
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'smart_blog_post_category` WHERE id_smart_blog_post =  ' . (int)$id_post;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
   
    public function getCanonicalURL()
    {
        return SmartBlogLink::getSmartBlogPostLink($this->post->id, $this->post->link_rewrite);
    }
}
