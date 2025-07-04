<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/src/NrtReviewProduct.php';

class NrtReviews extends Module implements WidgetInterface
{
    public $defaults;
	
    public function __construct()
    {
        $this->name = 'nrtreviews';
		$this->tab = 'front_office_features';
        $this->version = '2.3.2';
		$this->author = 'AxonVIZ';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();		
        $this->displayName = $this->l('Axon - Product Reviews');
        $this->description = $this->l('Allows users to post reviews and rate products.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->defaults = array(
			'nrt_reviews_auto_publish' => 1,
			'nrt_reviews_allow_guests' => 1,
			'nrt_reviews_use_fulness' => 1,
            'nrt_reviews_minimal_time' => 30,
			'nrt_reviews_comments_per_page' => 5,
			'nrt_reviews_allow_upload_img' => 1,
			'nrt_reviews_upload_max_img' => 4
        );
    }
	
    public function install()
    {
        return parent::install()
			&& $this->_createTab()
			&& $this->setDefaults()
			&& $this->createTables()
            && $this->registerHook('registerNRTCaptcha')
            && $this->registerHook('registerGDPRConsent')
            && $this->registerHook('actionDeleteGDPRCustomer')
            && $this->registerHook('actionExportGDPRData')
            && $this->registerHook('actionObjectProductDeleteAfter')
            && $this->registerHook('displayBeforeBodyClosingTag')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayProductExtraComparison')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayProductListReviews')
            && $this->registerHook('displayProductRating');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->_deleteTab() && $this->deleteDefaults() && $this->dropTables();
    }
	
    private function _createTab()
    {
        $response = true;

        // First check for parent tab
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        }
        else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = "AdminMenuFirst";
            foreach (Language::getLanguages() as $lang) {
                $parentTab->name[$lang['id_lang']] = "AXON - MODULES";
            }
            $parentTab->id_parent = 0;
            $parentTab->module ='';
            $response &= $parentTab->add();
        }
		// Check for parent tab2
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		if ($parentTab_2ID) {
			$parentTab_2 = new Tab($parentTab_2ID);
		}
		else {
			$parentTab_2 = new Tab();
			$parentTab_2->active = 1;
			$parentTab_2->name = array();
			$parentTab_2->class_name = "AdminMenuSecond";
			foreach (Language::getLanguages() as $lang) {
				$parentTab_2->name[$lang['id_lang']] = "Modules";
			}
			$parentTab_2->id_parent = $parentTab->id;
			$parentTab_2->module = '';
			$parentTab_2->icon = 'build';
			$response &= $parentTab_2->add();
		}
		// Created tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminNrtReviews";
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = "- Products Reviews";
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminNrtReviews');
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        $tab = new Tab($id_tab);
        $tab->delete();
		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminMenuSecond');
		$tabCount_2 = Tab::getNbTabs($parentTab_2ID);
        if ($tabCount_2 == 0) {
            $parentTab_2 = new Tab($parentTab_2ID);
            $parentTab_2->delete();
        }
        // Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
        $tabCount = Tab::getNbTabs($parentTabID);
        if ($tabCount == 0) {
            $parentTab = new Tab($parentTabID);
            $parentTab->delete();
        }

        return true;
    }

    public function setDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::updateValue($default, $value);
        }
        return true;
    }
	
    public function deleteDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::deleteByName($default);
        }
        return true;
    }
	
    public function createTables()
    {
        $return = true;
        $this->dropTables();
		
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'nrt_review_product` (
				`id_nrt_review_product` int(10) NOT NULL auto_increment,
				`id_product` int(10) unsigned NOT NULL,
				`id_customer` int(10) unsigned NOT NULL,
				`id_guest` int(10) unsigned NOT NULL,
				`customer_name` varchar(64) NULL,
				`title` varchar(64) NULL,
				`comment` text NOT NULL,
				`image` text default NULL,
				`rating` float unsigned  NULL,
				`active` tinyint(1) NOT NULL,
				`fulness` int(10) unsigned NULL,
				`no_fulness` int(10) unsigned NULL,
				`date_add` datetime NOT NULL,
				PRIMARY KEY (`id_nrt_review_product`),
				KEY `id_product` (`id_product`),
				KEY `id_customer` (`id_customer`),
				KEY `id_guest` (`id_guest`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
					
        return $return;
    }

    public function dropTables()
    {
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'nrt_review_product`');
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminNrtReviews')
        );
    }

    public function hookDisplayHeader()
    {
		$dir_rtl = $this->context->language->is_rtl ? '-rtl' : '';
		
        $this->context->controller->registerStylesheet($this->name . '-css', 'modules/' . $this->name . '/views/css/front'.$dir_rtl.'.css', ['media' => 'all', 'priority' => 150]);
        $this->context->controller->registerJavascript($this->name . '-js', 'modules/' . $this->name . '/views/js/front.min.js', ['position' => 'bottom', 'priority' => 150]);

		$fulness = $this->context->cookie->reviewFulness;
		
		if ($fulness) {
			$fulness = json_decode($fulness, true);
		}else{
			$fulness = array();
		}

        if(Tools::getValue('id_product')){
            $avgReviews = NrtReviewProduct::getAvgReviews(Tools::getValue('id_product'));

            if($avgReviews['nbr']){
                $this->context->smarty->assign('axpsProductComments', $avgReviews);
            }
        }
		
        Media::addJsDef(array(
			'opReviews' => array(
					'actions' => $this->context->link->getModuleLink('nrtreviews', 'actions', array(), null, null, null, true),
					'login' => $this->context->link->getModuleLink('nrtreviews', 'login', array(), null, null, null, true),
					'fulness' => $fulness,
        )));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if (preg_match('/^displayBeforeBodyClosingTag\d*$/', $hookName)) {
            $templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-modal.tpl';
			$cacheId = 'mdReviews';
			
			return $this->fetch($templateFile, $this->getCacheId($cacheId));
        }elseif (preg_match('/^displayProductExtraContent\d*$/', $hookName)) {
			$templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-form.tpl';

			$assign = $this->getWidgetVariables($hookName, $configuration);
            $array = array();
			$this->smarty->assign($assign);
			
            $array[] = (new ProductExtraContent())
                ->setTitle($this->l('Reviews').'('.$assign['avgReviews']['nbr'].')')
                ->setContent($this->fetch($templateFile))
				->setAttr(array('data-is-review' => true));
			
            return $array;
        }elseif (preg_match('/^displayProductExtraComparison\d*$/', $hookName)) {
			$templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-comparison.tpl';
					
			$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
			
			return $this->fetch($templateFile);
        }elseif (preg_match('/^displayProductRating\d*$/', $hookName)) {
			$templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-rating.tpl';
			
			$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
			
			return $this->fetch($templateFile);
       }elseif (preg_match('/^displayProductListReviews\d*$/', $hookName)) {
			$templateFile = 'module:' . $this->name . '/views/templates/hook/' . 'display-list-reviews.tpl';	
            $cacheId = 'listReviews';
			
            return $this->fetch($templateFile, $this->getCacheId($cacheId));
       }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        if (preg_match('/^displayProductExtraContent\d*$/', $hookName)) {
            $data = $this->getComments((int)$configuration['product']->id, 1);

            return array(
                'reviews' => $data['reviews'],
                'avgReviews' => NrtReviewProduct::getAvgReviews((int)$configuration['product']->id),
				'allowGuests' => Configuration::get('nrt_reviews_allow_guests'),
				'useFulness' => $data['useFulness'],
				'allowUpload' => Configuration::get('nrt_reviews_allow_upload_img'),
                'isLogged' => $this->context->customer->isLogged(),
                'idProduct' => (int)$data['idProduct'],
				'nameProduct' => $configuration['product']->name,
                'logginText' => sprintf($this->l('You need to be %1$slogged in%2$s or %3$screate an account%4$s to give your appreciation of a review.'), '<a href="javascript:void(0)" data-toggle="canvas-widget" data-position="right" data-target="#canvas-my-account"><b>', '</b></a>', '<a href="' . $this->context->link->getPageLink('registration', true) . '"><b>', '</b></a>'),
                'id_module' => $this->id,
                'limit' => $data['limit'],
                'limit_start' => $data['limit_start'],
                'c' => $data['c'],
                'total' => $data['total'],
                'pagenums' => $data['pagenums'],
            );
        } elseif (preg_match('/^displayProductExtraComparison\d*$/', $hookName)) {
            
			$has_review = false;
			$list_product_reviews = array();

			foreach ($configuration['list_ids_product'] as $product) {
				$id_product = (int) $product['id_product'];
				$avgReviews = NrtReviewProduct::getAvgReviews((int) $id_product);
				$list_product_reviews[$id_product] = $avgReviews;
			}
						
            return array(
				'list_ids_product' => $configuration['list_ids_product'],
				'list_product_reviews' => $list_product_reviews
			);
        } elseif (preg_match('/^displayProductRating\d*$/', $hookName)) {
            return array(
				'product' => $configuration['product']
			);
        }
    }

    public function getComments($idProduct, $page)
    {
        $limit_start = 0;
        $posts_per_page = Configuration::get('nrt_reviews_comments_per_page') ? Configuration::get('nrt_reviews_comments_per_page') : 5;
        $limit = $posts_per_page;
        
        $total = count(NrtReviewProduct::getByProduct((int)$idProduct, 0, 99999999));
        
        $totalpages = ceil($total / $posts_per_page);
        
        if ((boolean) $page) {
            $c = (int)$page;
            if(!$c){
                $c = 1;	
            }
            $limit_start = $posts_per_page * ($c - 1);
        }

        $reviews = NrtReviewProduct::getByProduct((int)$idProduct, $limit_start, $limit);

        foreach ($reviews as &$review) {
            $images = json_decode($review['image'], true);
            foreach ($images as $img) {
                $review['images'][] = $this->context->link->getMediaLink(_MODULE_DIR_.'nrtreviews/images/'.$img);
            }
        }			
        
        return array(
            'idProduct' => (int)$idProduct,
            'reviews' => $reviews,
            'useFulness' => Configuration::get('nrt_reviews_use_fulness'),
            'limit' => isset($limit) ? $limit : 0,
            'limit_start' => isset($limit_start) ? $limit_start : 0,
            'c' => isset($c) ? $c : 1,
            'total' => $total,
            'pagenums' => $totalpages - 1,
        );
    }
	
    public function deleteImages($id)
    {
		$obj = new NrtReviewProduct((int)$id);
		
		$images = json_decode($obj->image, true);
		foreach ($images as $img) {
			$imgFile = _PS_MODULE_DIR_.'nrtreviews/images/'.$img;
			if(file_exists($imgFile)){
				unlink($imgFile);
			}
		}
	}

    public function hookActionObjectProductDeleteAfter($params)
    {
        if (!isset($params['object']->id)) {
            return;
        }
        $idProduct = (int)$params['object']->id;
		
		$reviews = NrtReviewProduct::getByProduct($idProduct);

		foreach ($reviews as $review) {
			$this->deleteImages((int)$review['id_nrt_review_product']);
		}	
		
        NrtReviewProduct::deleteReviewsProduct($idProduct);
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['id'])) {
            $sql = "DELETE FROM "._DB_PREFIX_."nrt_review_product WHERE id_customer = '".(int)pSQL($customer['id'])."'";
            if (Db::getInstance()->execute($sql)) {
                return json_encode(true);
            }
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['id'])) {
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "nrt_review_product WHERE id_customer = '".(int)pSQL($customer['id'])."'";
            if ($res = Db::getInstance()->executeS($sql)) {
                return json_encode($res);
            }
        }
    }
    
}
