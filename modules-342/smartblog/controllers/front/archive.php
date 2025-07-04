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

class SmartBlogArchiveModuleFrontController extends ModuleFrontController
{

    public $ssl = false;	
	
    public function init()
    {
        parent::init();
    }
	
    public function getTemplateVarPage()
    {
        $id_lang = (int) Context::getContext()->language->id;

        $page = parent::getTemplateVarPage();
		
		$page['page_name'] = 'blog-category';
		
        $body_classes = array(
            'lang-'.$this->context->language->iso_code => true,
            'lang-rtl' => (bool) $this->context->language->is_rtl,
            'country-'.$this->context->country->iso_code => true,
            'currency-'.$this->context->currency->iso_code => true,
            $this->context->shop->theme->getLayoutNameForPage('module-smartblog-archive') => true,
            'page-blog-category' => true,
            'tax-display-'.($this->getDisplayTaxesLabel() ? 'enabled' : 'disabled') => true,
        );
				
		$page['body_classes'] = $body_classes;
		$page['meta']['description'] = Configuration::get('smartblogmetadescrip', $id_lang);
		$page['meta']['keywords'] = Configuration::get('smartblogmetakeyword', $id_lang);
        $page['meta']['title'] = $this->module->l('Blog Archive', 'archive');
		
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
		
        $year = Tools::getvalue('year');
        $month = Tools::getvalue('month');

        foreach ($languages as $lang) {
			if($year){
				$alternativeLangs[$lang['language_code']] = smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year), $lang['id_lang']);
			}	

			if($month){
				$dateObj   = DateTime::createFromFormat('!m', $month);
				$month_name = $dateObj->format('F');
				$alternativeLangs[$lang['language_code']] = smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year, 'month' => $month), $lang['id_lang']);
			}
        }

        return $alternativeLangs;
    }
	
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
		
        $year = Tools::getvalue('year');
        $month = Tools::getvalue('month');

        $breadcrumb['links'][] = [
            'title' => $this->module->l('Blog', 'archive'),
            'url' => smartblog::GetSmartBlogLink('smartblog')
        ];
		
		if($year){
			$breadcrumb['links'][] = [
				'title' => $this->module->l($year, 'archive'),
				'url' => smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year))
			];
		}	
		
        if($month){
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $month_name = $dateObj->format('F');
			$breadcrumb['links'][] = [
				'title' => $this->module->l($month_name, 'archive'),
				'url' => smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year, 'month' => $month))
			];
        }
		
        return $breadcrumb;
    }

    public function initContent()
    {
        
        $year = Tools::getvalue('year');
        $month = Tools::getvalue('month');
        $day = Tools::getvalue('day');
		
		$limit_start = 0;
        $posts_per_page = Configuration::get('smartpostperpage');  
		$limit = $posts_per_page;
		
		$total_result = SmartBlogPost::getArchiveResult($month, $year, $day, 0, 99999999);
		
		if(!$total_result){
			$total = 0;
		}else{
			$total = count($total_result);
		}
		
        $totalpages = ceil($total / $posts_per_page);
		
        if ((boolean) Tools::getValue('page')) {
            $c = (int) Tools::getValue('page');
			if(!$c){
				$c = 1;	
			}
            $limit_start = $posts_per_page * ($c - 1);
        }
				
		if(preg_match('/^page/', Tools::getvalue('month'))){
            $c = (int) Tools::getValue('day');
            $limit_start = $posts_per_page * ($c - 1);
			$month = false;
			$day = false;
		}
		
        $posts = SmartBlogPost::getArchiveResult($month, $year, $day, $limit_start, $limit);
		
		if(!$posts){
			$posts = array();
		}
				
		parent::initContent();
   
        $this->context->smarty->assign(array(
			'blog_category_post_layout' => Configuration::get('blog_category_post_layout'),
            'posts' => SmartBlogPost::ConvertPost($posts, Configuration::get('blog_category_post_image_type')),
            'year' => $year,
            'month' => $month,
            'smartshowauthor' => Configuration::get('smartshowauthor'),
            'smartshowauthorstyle' => Configuration::get('smartshowauthorstyle'),
            'smartshowviewed' => Configuration::get('smartshowviewed'),
            'limit' => isset($limit) ? $limit : 0,
            'limit_start' => isset($limit_start) ? $limit_start : 0,
            'c' => isset($c) ? $c : 1,
            'total' => $total,
            'pagenums' => $totalpages - 1
        ));
		
        $this->setTemplate('module:smartblog/views/templates/front/archive.tpl');
    }

    public function getCanonicalURL()
    {
        $year = Tools::getvalue('year');
        $month = Tools::getvalue('month');
        
        $url = smartblog::GetSmartBlogLink('smartblog');
		
		if($year){
		    $url = smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year));
		}	
		
        if($month){
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $month_name = $dateObj->format('F');

            $url = smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $year, 'month' => $month));
        }

        return $url;
    }
}
