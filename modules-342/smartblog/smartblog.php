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

define('_MODULE_SMARTBLOG_DIR_', _PS_MODULE_DIR_ . 'smartblog/images/');

require_once dirname(__FILE__).'/classes/BlogCategory.php';
require_once dirname(__FILE__).'/classes/BlogImageType.php';
require_once dirname(__FILE__).'/classes/BlogTag.php';
require_once dirname(__FILE__).'/classes/SmartBlogPost.php';
require_once dirname(__FILE__).'/classes/SmartBlogHelperTreeCategories.php';
require_once dirname(__FILE__).'/classes/BlogComment.php';
require_once dirname(__FILE__).'/classes/BlogPostCategory.php';
require_once dirname(__FILE__).'/classes/SmartBlogLink.php';

class smartblog extends Module implements WidgetInterface
{
	const REWRITE_PATTERN = '[_a-zA-Z0-9\x{0600}-\x{06FF}\pL\pS-]*';

    protected $fields_form;

    public function __construct(){
        $this->name = 'smartblog';
        $this->tab = 'front_office_features';
        $this->version = '3.2.3';
        $this->author = 'SmartDataSoft';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->controllers = array('all', 'category', 'details', 'search', 'tagpost', 'archive', 'ajax');
        parent::__construct();
        $this->displayName = $this->l('Smart Blog');
        $this->description = $this->l('The Most Powerfull Prestashop Blog  Module - by smartdatasoft');
    }

    public function install()
    {

		Configuration::updateGlobalValue('smartlistpostimagesize', 'large_default');
		Configuration::updateGlobalValue('smartmodulepostimagesize', 'small_default');

        Configuration::updateGlobalValue('smartpostperpage', 5);
        Configuration::updateGlobalValue('smartshowauthorstyle', 1);
        Configuration::updateGlobalValue('smartshowauthor', 1);
        Configuration::updateGlobalValue('smartmainblogurl', 'blog');
        Configuration::updateGlobalValue('smartusehtml', 1);
        Configuration::updateGlobalValue('smartenablecomment', 1);
        Configuration::updateGlobalValue('smartenableguestcomment', 1);
        Configuration::updateGlobalValue('smartshowviewed', 1);
        Configuration::updateGlobalValue('smartshownoimg', 1);
        Configuration::updateGlobalValue('smartacceptcomment', 1);
        Configuration::updateGlobalValue('smartdisablecatimg', 1);
        Configuration::updateGlobalValue('smartdataformat', 'd M, Y');
        Configuration::updateGlobalValue('smartblogurlpattern', 1);

        Configuration::updateGlobalValue('smartblogmetatitle', 'All Post', true);
        Configuration::updateGlobalValue('smartblogmetakeyword', 'axon,blog,smartblog,prestashop blog,prestashop,blog', true);
        Configuration::updateGlobalValue('smartblogmetadescrip', 'Prestashop powerfull blog site developing module. It has hundrade of extra plugins. This module developed by smartdatasoft', true);
		
		Configuration::updateGlobalValue('blog_category_post_layout', 2);
		Configuration::updateGlobalValue('blog_category_post_image_type', 'home_default');
		Configuration::updateGlobalValue('blog_category_post_xl', 3);
		Configuration::updateGlobalValue('blog_category_post_lg', 3);
		Configuration::updateGlobalValue('blog_category_post_md', 2);
		Configuration::updateGlobalValue('blog_category_post_xs', 1);
		Configuration::updateGlobalValue('blog_category_post_space_xl', 30);
		Configuration::updateGlobalValue('blog_category_post_space_lg', 30);
		Configuration::updateGlobalValue('blog_category_post_space_md', 20);
		Configuration::updateGlobalValue('blog_category_post_space_xs', 10);
		      
        $this->htaccessUpdate();
        
        if (!parent::install()
            || !$this->registerHook('registerNRTCaptcha')
            || !$this->registerHook('registerGDPRConsent')
            || !$this->registerHook('actionDeleteGDPRCustomer')
            || !$this->registerHook('actionExportGDPRData')
            || !$this->registerHook('actionsbappcomment')
            || !$this->registerHook('actionsbcat')
            || !$this->registerHook('actionsbdeletecat')
            || !$this->registerHook('actionsbdeletepost')
            || !$this->registerHook('actionsbheader')
            || !$this->registerHook('actionsbnewcat')
            || !$this->registerHook('actionsbnewpost')
            || !$this->registerHook('actionsbpostcomment')
            || !$this->registerHook('actionsbsearch')
            || !$this->registerHook('actionsbsingle')
            || !$this->registerHook('actionsbtogglecat')
            || !$this->registerHook('actionsbtogglepost')
            || !$this->registerHook('actionsbupdatecat')
            || !$this->registerHook('actionsbupdatepost')
            || !$this->registerHook('addWebserviceResources')
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('moduleRoutes')
            || !$this->registerHook('actionLanguageLinkParameters')
        ){
            return false;
		}
		
        $sql = array();
		
        require_once(dirname(__FILE__) . '/sql/install.php');
		
        foreach ($sql as $sq){
            if (!Db::getInstance()->Execute($sq)){
                return false;
			}
		}
		
        $this->CreateSmartBlogTabs();
        $this->SampleDataInstall();
        $this->installDummyData();

        return true;
    }

    public function installDummyData(){
        $image_types = BlogImageType::GetImageAllType('post');
		
        $id_smart_blog_posts = $this->getAllPost();
		
        foreach ($id_smart_blog_posts as $id_smart_blog_post) {
				
            foreach ($image_types as $image_type) {
				$path = _PS_MODULE_DIR_.'smartblog/images/'.$id_smart_blog_post['id_smart_blog_post'].'.jpg';
				$path_no = _PS_MODULE_DIR_.'smartblog/images/no.jpg';
				
				if(file_exists($path)){
                	ImageManager::resize($path, _PS_MODULE_DIR_ . 'smartblog/images/' . $id_smart_blog_post['id_smart_blog_post'] . '-' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']);
				}
				if(file_exists($path_no)){
                	ImageManager::resize($path_no, _PS_MODULE_DIR_ . 'smartblog/images/no-' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']);
				}
            }
        }
       return true;
    }

    public static function getAllPost()
    {
        $sql = 'SELECT p.id_smart_blog_post 
                FROM `' . _DB_PREFIX_ . 'smart_blog_post_lang` p';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)){
            return false;
		}
        return $result;
    }

    public function hookdisplayBackOfficeHeader($params)
    {
        return $this->display(__FILE__, 'views/templates/admin/addjs.tpl');
    }

    public function hookDisplayHeader($params)
    {
		$this->BlogImageTypeCss();
		$this->BlogConfigCss();
    }
	
    public function hookAddWebserviceResources($resources)
    {
		return $resources[] = array('smart_blog_categorys' => array('description' => 'The blog category', 'class' => 'BlogCategory'),
								    'smart_blog_posts' => array('description' => 'The blog posts', 'class' => 'SmartBlogPost'),
								    'smart_blog_post_categorys' => array('description' => 'The blog posts in category', 'class' => 'BlogPostCategory'));
    }

    public function renderWidget($hookName = null, array $configuration = []) {}

    public function getWidgetVariables($hookName = null, array $configuration = []) {}

    public function htaccessUpdate()
    {
        $content = file_get_contents(_PS_ROOT_DIR_ . '/.htaccess');
        if (!preg_match('/\# Images Blog\n/', $content)) {
            $content = preg_replace_callback('/\# Images\n/', array($this, 'updateSiteHtaccess'), $content);
            @file_put_contents(_PS_ROOT_DIR_ . '/.htaccess', $content);
        }
    }

    public function updateSiteHtaccess($match)
    {
        $htupdate = '';
        require_once dirname(__FILE__) . '/htupdate.php';
        $str = '';
        if (isset($match[0])) {
            $str .= "\n{$htupdate}\n\n{$match[0]}\n";
        }
        return $str;
    }

    private function CreateSmartBlogTabs()
    {
		$langs = Language::getLanguages();
		$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
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
			$parentTab_2ID = Tab::getIdFromClassName('AdminSmartBlog');
			if ($parentTab_2ID) {
				$parentTab_2 = new Tab($parentTab_2ID);
			}
			else {
				$parentTab_2 = new Tab();
				$parentTab_2->active = 1;
				$parentTab_2->name = array();
				$parentTab_2->class_name = "AdminSmartBlog";
				foreach (Language::getLanguages() as $lang) {
					$parentTab_2->name[$lang['id_lang']] = "Blogs";
				}
				$parentTab_2->id_parent = $parentTab->id;
				$parentTab_2->module = '';
				$parentTab_2->icon = 'create';
				$response &= $parentTab_2->add();
			}			
                $tab_id = $parentTab_2->id;
                require_once(dirname(__FILE__) . '/sql/install_tab.php');
                foreach ($tabvalue as $tab){
                    $newtab = new Tab();
                    $newtab->class_name = $tab['class_name'];
                    $newtab->id_parent = $tab_id;
                    $newtab->module = $tab['module'];
                    foreach ($langs as $l) {
                        $newtab->name[$l['id_lang']] = $this->l($tab['name']);
                    }
                    $newtab->save();
                }
                return true;
            }
    /* ------------------------------------------------------------- */
    /*  DELETE THE TAB MENU
    /* ------------------------------------------------------------- */
    private function _deleteTab()
    {
        $id_tab = Tab::getIdFromClassName('AdminSmartBlogGeneral');
        $parentTabID = Tab::getIdFromClassName('AdminMenuFirst');

        $tab = new Tab($id_tab);
        $tab->delete();

		// Get the number of tabs inside our parent tab
        // If there is no tabs, remove the parent
		$parentTab_2ID = Tab::getIdFromClassName('AdminSmartBlog');
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
    public function SampleDataInstall()
    {
        $damisql = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category (id_parent,level_depth,active) VALUES (0,0,1);";
        Db::getInstance()->execute($damisql);

        $damisql_1 = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category (id_parent,level_depth,active) VALUES (1,1,1);";
        Db::getInstance()->execute($damisql_1);

        $damisq1l = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category_shop (id_smart_blog_category,id_shop) VALUES (1,'" . (int) Context::getContext()->shop->id . "');";
        Db::getInstance()->execute($damisq1l);

        $damisq1l_1 = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category_shop (id_smart_blog_category,id_shop) VALUES (2,'" . (int) Context::getContext()->shop->id . "');";
        Db::getInstance()->execute($damisq1l_1);

        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            $damisql2 = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category_lang (id_smart_blog_category,name,meta_title,id_lang,link_rewrite) VALUES (1,'Home','Home','" . (int) $language['id_lang'] . "','home');";
            Db::getInstance()->execute($damisql2);

            $damisql2_1 = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_category_lang (id_smart_blog_category,name,meta_title,id_lang,link_rewrite) VALUES (2,'Politics','Politics','" . (int) $language['id_lang'] . "','politics');";
            Db::getInstance()->execute($damisql2_1);
        }
        for ($i = 1; $i <= 8; $i++) {
            Db::getInstance()->Execute('
                                                INSERT INTO `' . _DB_PREFIX_ . 'smart_blog_post`(`id_author`, `position`, `active`, `available`, `created`, `viewed`, `comment_status`) 
                                                VALUES(1,0,1,1,"' . Date('y-m-d H:i:s') . '",0,1)');
        }

        $languages = Language::getLanguages(false);
        for ($i = 1; $i <= 8; $i++) {
			$name_tag='Phasellus';
            if($i==1):
				$name_tag='Sedorci';
				$title='Vivamus eu enim massa nunc';
				$rewrite='vivamus-eu-enim-massa-nunc';
            elseif($i==2):
				$name_tag='Phasellus';
				$title='Curabitur sit amet ex amet';
				$rewrite='curabitur-sit-amet-ex-amet';
            elseif($i==3):
				$name_tag='Pellentesque';
				$title='Mauris vulputate cras amet';
				$rewrite='mauris-vulputate-cras-amet';
            elseif($i==4):
				$name_tag='Suspendisse';
				$title='Suspendisse nullam sodales';
				$rewrite='suspendisse-nullam-sodales';
            elseif($i==5):
				$name_tag='Euismod';
				$title='Class aptent taciti nullam';
				$rewrite='class-aptent-taciti-nullam';
            elseif($i==6):
				$name_tag='Lorem';
				$title='In congue magna sit nullam';
				$rewrite='in-congue-magna-sit-nullam';
		    elseif($i==7):
				$name_tag='Ullamcorper';
				$title='Vestibulum at orci aliquam';
				$rewrite='vestibulum-at-orci-aliquam';
		    elseif($i==8):
				$name_tag='Tempus';
				$title='Aliquam hendrerit mi metus';
				$rewrite='aliquam-hendrerit-mi-metus';
            endif;
			
			$ShortDescription='Vestibulum malesuada varius mi id congue. Phasellus aliquam mollis ex, eleifend dictum arcu egestas sit amet. Sed lacinia ante porttitor diam tincidunt fermentum. Etiam quis semper lacus. Sed a convallis est, vitae amet.';
			$MetaDescription='Sed orci felis, mattis quis suscipit et, laoreet quis risus. Phasellus bibendum quam nec felis lacinia consectetur. Integer eget lorem nec arcu facilisis eleifend. Vestibulum sed ante ac nisi tempus semper a eu tellus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Morbi at mauris id ante pellentesque varius. Suspendisse ullamcorper erat in est euismod iaculis. Proin vel viverra fusce.';
			$Description='<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>
<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \'de Finibus Bonorum et Malorum\' (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \'Lorem ipsum dolor sit amet..\', comes from a line in section 1.10.32.</p>
<p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \'de Finibus Bonorum et Malorum\' by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>';	
			
			foreach ($languages as $language){
				if(!Db::getInstance()->Execute('
					INSERT INTO `'._DB_PREFIX_.'smart_blog_post_lang`(`id_smart_blog_post`,`id_lang`,`meta_title`,`meta_description`,`short_description`,`content`,`link_rewrite`)
				VALUES('.$i.','.(int)$language['id_lang'].', 
				"'.$title.'", 
				"'.$MetaDescription.'","'.$ShortDescription.'","'.$Description.'","'.$rewrite.'"
				)'))
			return false;
			}	
			Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'smart_blog_tag`(`id_tag`,`id_lang`,`name`) 
				VALUES('.$i.',1,"'.$name_tag.'")');
			Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'smart_blog_post_tag`(`id_tag`,`id_post`) 
				VALUES('.$i.','.$i.')');	
        }
        for ($i = 1; $i <= 8; $i++) {
            Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'smart_blog_post_shop`(`id_smart_blog_post`, `id_shop`)	VALUES(' . $i . ',' . (int) Context::getContext()->shop->id . ')');
        }
        for ($i = 1; $i <= 7; $i++) {
            if ($i == 1):
                $type_name = 'home_default';
                $width = '370';
                $height = '227';
                $type = 'post';
            elseif ($i == 2):
                $type_name = 'small_default';
                $width = '121';
                $height = '74';
                $type = 'post';
            elseif ($i == 3):
                $type_name = 'large_default';
                $width = '870';
                $height = '534';
                $type = 'post';
            elseif ($i == 4):
                $type_name = 'small_default';
                $width = '65';
                $height = '45';
                $type = 'category';
            elseif ($i == 5):
                $type_name = 'home_default';
                $width = '240';
                $height = '160';
                $type = 'category';
            elseif ($i == 6):
                $type_name = 'large_default';
                $width = '870';
                $height = '400';
                $type = 'category';
            elseif ($i == 7):
                $type_name = 'author_default';
                $width = '74';
                $height = '74';
                $type = 'author';
            endif;
            $damiimgtype = "INSERT INTO " . _DB_PREFIX_ . "smart_blog_imagetype (type_name,width,height,type,active) VALUES ('" . $type_name . "','" . $width . "','" . $height . "','" . $type . "',1);";
            Db::getInstance()->execute($damiimgtype);
        }
		
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('smartblogmetatitle') ||
            !Configuration::deleteByName('smartblogmetakeyword') ||
            !Configuration::deleteByName('smartblogmetadescrip') ||
            !Configuration::deleteByName('smartpostperpage') ||
			!Configuration::deleteByName('smartlistpostimagesize') ||
			!Configuration::deleteByName('smartmodulepostimagesize') ||
            !Configuration::deleteByName('smartacceptcomment') ||
            !Configuration::deleteByName('smartusehtml') ||
            !Configuration::deleteByName('smartshowviewed') ||
            !Configuration::deleteByName('smartdisablecatimg') ||
            !Configuration::deleteByName('smartenablecomment') ||
            !Configuration::deleteByName('smartenableguestcomment') ||
            !Configuration::deleteByName('smartmainblogurl') ||
            !Configuration::deleteByName('smartshowauthorstyle') ||
            !Configuration::deleteByName('smartshownoimg') ||
            !Configuration::deleteByName('smartshowauthor') ||
            !Configuration::deleteByName('smartblogurlpattern') ||
            !Configuration::deleteByName('smartdataformat') ||
            !Configuration::deleteByName('blog_category_post_layout') ||
            !Configuration::deleteByName('blog_category_post_image_type') ||
            !Configuration::deleteByName('blog_category_post_xl') ||
            !Configuration::deleteByName('blog_category_post_lg') ||
            !Configuration::deleteByName('blog_category_post_md') ||
            !Configuration::deleteByName('blog_category_post_xs') ||
            !Configuration::deleteByName('blog_category_post_space_xl') ||
            !Configuration::deleteByName('blog_category_post_space_lg') ||
            !Configuration::deleteByName('blog_category_post_space_md') ||
            !Configuration::deleteByName('blog_category_post_space_xs')
        )
            return false;

        $idtabs = array();

        require_once(dirname(__FILE__) . '/sql/uninstall_tab.php');
		
        foreach ($idtabs as $tabid){
            if ($tabid) {
                $tab = new Tab($tabid);
                $tab->delete();
            }
		}
		
        $sql = array();
        require_once(dirname(__FILE__) . '/sql/uninstall.php');
        foreach ($sql as $s) :
            if (!Db::getInstance()->Execute($s))
                return false;
        endforeach;

        // $this->SmartHookDelete();
        $this->_deleteTab();

        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('savesmartblog')) {

            $title = [];
            $keyword = [];
            $descrip = [];

            $languages = Language::getLanguages(false);

            foreach ($languages as $lang) {
                $title[$lang['id_lang']] = Tools::getValue('smartblogmetatitle_'.$lang['id_lang']);
                $keyword[$lang['id_lang']] = Tools::getValue('smartblogmetakeyword_'.$lang['id_lang']);
                $descrip[$lang['id_lang']] = Tools::getValue('smartblogmetadescrip_'.$lang['id_lang']);
            }

            Configuration::updateValue('smartblogmetatitle', $title, true);
            Configuration::updateValue('smartblogmetakeyword', $keyword, true);
            Configuration::updateValue('smartblogmetadescrip', $descrip, true);
            Configuration::updateValue('smartmainblogurl', Tools::getvalue('smartmainblogurl'));
            Configuration::updateValue('smartenablecomment', Tools::getvalue('smartenablecomment'));
            Configuration::updateValue('smartenableguestcomment', Tools::getvalue('smartenableguestcomment'));
            Configuration::updateValue('smartpostperpage', Tools::getvalue('smartpostperpage'));
			Configuration::updateValue('smartlistpostimagesize', Tools::getvalue('smartlistpostimagesize'));
			Configuration::updateValue('smartmodulepostimagesize', Tools::getvalue('smartmodulepostimagesize'));
            Configuration::updateValue('smartblogurlpattern', Tools::getvalue('smartblogurlpattern'));
            Configuration::updateValue('smartacceptcomment', Tools::getvalue('smartacceptcomment'));
            Configuration::updateValue('smartshowviewed', Tools::getvalue('smartshowviewed'));
            Configuration::updateValue('smartdisablecatimg', Tools::getvalue('smartdisablecatimg'));
            Configuration::updateValue('smartshowauthorstyle', Tools::getvalue('smartshowauthorstyle'));
            Configuration::updateValue('smartshowauthor', Tools::getvalue('smartshowauthor'));
            Configuration::updateValue('smartusehtml', Tools::getvalue('smartusehtml'));
            Configuration::updateValue('smartshownoimg', Tools::getvalue('smartshownoimg'));
            Configuration::updateValue('smartdataformat', Tools::getvalue('smartdataformat'));
			
			Configuration::updateValue('blog_category_post_layout', Tools::getvalue('blog_category_post_layout'));
			Configuration::updateValue('blog_category_post_image_type', Tools::getvalue('blog_category_post_image_type'));
			Configuration::updateValue('blog_category_post_xl', Tools::getvalue('blog_category_post_xl'));
			Configuration::updateValue('blog_category_post_lg', Tools::getvalue('blog_category_post_lg'));
			Configuration::updateValue('blog_category_post_md', Tools::getvalue('blog_category_post_md'));
			Configuration::updateValue('blog_category_post_xs', Tools::getvalue('blog_category_post_xs'));
			Configuration::updateValue('blog_category_post_space_xl', Tools::getvalue('blog_category_post_space_xl'));
			Configuration::updateValue('blog_category_post_space_lg', Tools::getvalue('blog_category_post_space_lg'));
			Configuration::updateValue('blog_category_post_space_md', Tools::getvalue('blog_category_post_space_md'));
			Configuration::updateValue('blog_category_post_space_xs', Tools::getvalue('blog_category_post_space_xs'));
			
			$this->_writeConfigCss();

            $this->processImageUpload($_FILES);
            $html = $this->displayConfirmation($this->l('The settings have been updated successfully.'));
            $helper = $this->SettingForm();
            $html .= $helper->generateForm($this->fields_form);
            $helper = $this->regenerateform();
            $html .= $helper->generateForm($this->fields_form);
            $this->htaccessUpdate();
			
			$links = $this->smartBlogRoutes();
			
			foreach ($links as $key => $link) {
				if(Configuration::get('PS_ROUTE_'.$key)){
					Configuration::updateValue('PS_ROUTE_'.$key, $link['rule']);
				}
			}
			
            return $html;
        } elseif (Tools::isSubmit('generateimage')) {
					
            $this->_writeBlogImgCss();
            
            if (Tools::getvalue('isdeleteoldthumblr') != 1) {
                BlogImageType::ImageGenerate();
                $html = $this->displayConfirmation($this->l('Generate New Thumblr Succesfully.'));
                $helper = $this->SettingForm();
                $html .= $helper->generateForm($this->fields_form);
                $helper = $this->regenerateform();
                $html .= $helper->generateForm($this->fields_form);                
                return $html;
            } else {
                BlogImageType::ImageDelete();
                BlogImageType::ImageGenerate();
                $html = $this->displayConfirmation($this->l('Delete Old Image and Generate New Thumblr Succesfully.'));
                $helper = $this->SettingForm();
                $html .= $helper->generateForm($this->fields_form);
                $helper = $this->regenerateform();
                $html .= $helper->generateForm($this->fields_form);
                
                return $html;
            }
			
        } else {
            $helper = $this->SettingForm();
            $html = $helper->generateForm($this->fields_form);
            $helper = $this->regenerateform();
            $html .= $helper->generateForm($this->fields_form);            
            return $html;
        }
    }
	
	private function _writeConfigCss()
    {
        $cssCode = '';
		
		if($value = Configuration::get('blog_category_post_space_xs')){		
			$cssCode .= '@media (max-width: 767px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_space_md')){		
			$cssCode .= '@media (min-width: 768px) and (max-width: 1024px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_space_lg')){		
			$cssCode .= '@media (min-width: 1025px) and (max-width: 1199px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_space_xl')){		
			$cssCode .= '@media (min-width: 1200px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items{margin-left: calc(-'.$value.'px/2);margin-right: calc(-'.$value.'px/2);}';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{padding-left: calc('.$value.'px/2);padding-right: calc('.$value.'px/2);margin-bottom: '.$value.'px;}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_xs')){	
			$cssCode .= '@media (max-width: 767px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_md')){		
			$cssCode .= '@media (min-width: 768px) and (max-width: 1024px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_lg')){	
			$cssCode .= '@media (min-width: 1025px) and (max-width: 1199px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}
		
		if($value = Configuration::get('blog_category_post_xl')){	
			$cssCode .= '@media (min-width: 1200px){';
			$cssCode .= '#box-blog-grid .archive-wrapper-items > .item{-ms-flex: 0 0 calc(100%/'.$value.'); flex: 0 0 calc(100%/'.$value.'); max-width: calc(100%/'.$value.');}';
			$cssCode .= '}';
		}

        $cssCode = trim(preg_replace('/\s+/', ' ', $cssCode));
		$id_shop = (int)$this->context->shop->id;
		
        $cssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/front/custom_s_' . $id_shop . '.css';

		if($cssCode){
			if(file_put_contents($cssFile, $cssCode)){
				return true;
			}else{
				return false;
			}
		}else{
			if(file_exists($cssFile)){
				unlink($cssFile);
			}
		} 
		
		return true;
    }
	
    public function BlogConfigCss()
    {
        $id_shop = (int)$this->context->shop->id;
		
        $cssFile = 'custom_s_' . $id_shop . '.css';

        if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/views/css/front/' . $cssFile)) {
            $this->_writeConfigCss();
        }

        $this->context->controller->registerStylesheet(
            'BlogConfigCss', 
            'modules/'.$this->name.'/views/css/front/' . $cssFile, 
            ['position' => 'bottom', 'priority' => 997]
        );
    }

    public function _writeBlogImgCss()
    {
		$images = BlogImageType::GetImageAll();
		
        $cssCode = '';

        foreach ($images as $image) {
			if((int)$image['height'] && (int)$image['width']){
				$padding = ((int)$image['height']/(int)$image['width'])*100;
		  		$cssCode .= '.blog_'.$image['type'].'_'.$image['type_name'].'{padding-top:'.$padding.'%;}';	
			}else{
		  		$cssCode .= '.blog_'.$image['type'].'_'.$image['type_name'].'{padding-top:100%;}';		
			}
        }
		
        $cssCode = trim(preg_replace('/\s+/', ' ', $cssCode));
		$id_shop = (int)$this->context->shop->id;
		
        $cssFile = _PS_MODULE_DIR_ . $this->name . '/views/css/front/blog_images.css';

		if($cssCode){
			if(file_put_contents($cssFile, $cssCode)){
				return true;
			}else{
				return false;
			}
		}else{
			if(file_exists($cssFile)){
				unlink($cssFile);
			}
		} 
		
		return true;
		
    }
	
    public function BlogImageTypeCss()
    {
        $id_shop = $this->context->shop->id;
		
        $cssFile = 'blog_images.css';

        if (!file_exists(_PS_MODULE_DIR_ . $this->name . '/views/css/front/' . $cssFile)) {
            $this->_writeBlogImgCss();
        }

        $this->context->controller->registerStylesheet(
            'BlogImgCss', 
            'modules/'.$this->name.'/views/css/front/' . $cssFile, 
            ['position' => 'bottom', 'priority' => 997]
        );
    }
			
    protected function regenerateform()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Blog Thumblr Configuration'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Delete Old Thumblr'),
                    'name' => 'isdeleteoldthumblr',
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
                )
            ),
            'submit' => array(
                'title' => $this->l('Re Generate Thumblr'),
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang){
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
		}
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->show_toolbar = false;
        $helper->submit_action = 'generateimage';
        $helper->fields_value['isdeleteoldthumblr'] = Configuration::get('isdeleteoldthumblr');
        return $helper;
    }

    public function processImageUpload($FILES)
    {
        if (isset($FILES['avatar']) && isset($FILES['avatar']['tmp_name']) && !empty($FILES['avatar']['tmp_name'])) {
            if (ImageManager::validateUpload($FILES['avatar'], 4000000))
                return $this->displayError($this->l('Invalid image'));
            else {
                $ext = Tools::substr($FILES['avatar']['name'], strrpos($FILES['avatar']['name'], '.') + 1);
                $file_name = 'avatar.' . $ext;
                $path = _PS_MODULE_DIR_ . 'smartblog/images/avatar/' . $file_name;
                if (!move_uploaded_file($FILES['avatar']['tmp_name'], $path))
                    return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                else {
                    $author_types = BlogImageType::GetImageAllType('author');
                    foreach ($author_types as $image_type) {
                        $dir = _PS_MODULE_DIR_ . 'smartblog/images/avatar/avatar_' . stripslashes($image_type['type_name']) . '.jpg';
                        if (file_exists($dir))
                            unlink($dir);
                    }
                    $images_types = BlogImageType::GetImageAllType('author');
                    foreach ($images_types as $image_type) {
                        ImageManager::resize($path, _PS_MODULE_DIR_ . 'smartblog/images/avatar/avatar_' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']
                        );
                    }
                }
            }
        }
    }

    public function SettingForm()
    {
		$_blogTypes = array();
		
		for ($i = 1; $i <= 30; $i++) {
			$_blogTypes[] = array(
                'value' => $i,
                'name' => $this->l('Blog type - ').$i,
            );	
		}
		
        $ad_row = array(
			array('value'=>'1'),
			array('value'=>'2'),
			array('value'=>'3'),
			array('value'=>'4'),
			array('value'=>'5'),
			array('value'=>'6'),
			array('value'=>'7'),
			array('value'=>'8'),
			array('value'=>'9'),
			array('value'=>'10'),
        );
		
        $ad_space_item = array(
			array('value'=>'0'),
			array('value'=>'5'),
			array('value'=>'10'),
			array('value'=>'20'),
			array('value'=>'30'),
			array('value'=>'40'),
			array('value'=>'50'),
        );
		
		$images_formats = BlogImageType::GetImageAllType('post');
		$images_type = array();
				
		foreach ($images_formats as $key => $image) {
			$images_type[$key]['value'] = $image['type_name'];
			$images_type[$key]['name'] = $image['type_name'];
		}
				
        $blog_url = smartblog::GetSmartBlogLink('smartblog');
        $img_desc = '';
        $img_desc .= '' . $this->l('Upload a Avatar from your computer.<br/>N.B : Only jpg image is allowed');
        $img_desc .= '<br/><img style="clear:both;border:1px solid black;" alt="" src="' . __PS_BASE_URI__ . 'modules/smartblog/images/avatar/avatar.jpg" height="100" width="100"/><br />';
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Setting'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta Title'),
                    'name' => 'smartblogmetatitle',
                    'lang' => true,
                    'size' => 70,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta Keyword'),
                    'name' => 'smartblogmetakeyword',
                    'lang' => true,
                    'size' => 70,
                    'required' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Meta Description'),
                    'name' => 'smartblogmetadescrip',
                    'lang' => true,
                    'rows' => 7,
                    'cols' => 66,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Main Blog Url'),
                    'name' => 'smartmainblogurl',
                    'size' => 15,
                    'required' => true,
                    'desc' => '<p class="alert alert-info"><a href="' . $blog_url . '">' . $blog_url . '</a></p>'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use .html with Friendly Url'),
                    'name' => 'smartusehtml',
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
                    'type' => 'radio',
                    'label' => $this->l('Blog Page Url Pattern'),
                    'name' => 'smartblogurlpattern',
                    'required' => false,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'smartblogurlpattern_a',
                            'value' => 1,
                            'label' => $this->l('alias/{rewrite}html ( ex: alias/share-the-love-for-prestashop-1-7.html)')
                        ),
                        array(
                            'id' => 'smartblogurlpattern_b',
                            'value' => 2,
                            'label' => $this->l('alias/{id_post}_{rewrite}html ( ex: alias/1_share-the-love-for-prestashop-1-7.html)')
                        ), 
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Number of posts per page'),
                    'name' => 'smartpostperpage',
                    'size' => 15,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Date format'),
                    'name' => 'smartdataformat',
                    'size' => 15,
                    'required' => true,
                ),
				
				array(
					'type' => 'select',
					'label' => $this->l('Orther Post Pages Image Size '),
					'name' => 'smartlistpostimagesize',
					'required' => false,
					'options' => array(
						'query' => $images_type,
						'id' => 'value',
						'name' => 'name'
					)
				),
				
				array(
					'type' => 'select',
					'label' => $this->l('Orther Post Modules Image Size '),
					'name' => 'smartmodulepostimagesize',
					'required' => false,
					'options' => array(
						'query' => $images_type,
						'id' => 'value',
						'name' => 'name'
					)
				),
				
                array(
                    'type' => 'switch',
                    'label' => $this->l('Auto accepted comment'),
                    'name' => 'smartacceptcomment',
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
                    'label' => $this->l('Enable Comment'),
                    'name' => 'smartenablecomment',
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
                    'label' => $this->l('Allow Guest Comment'),
                    'name' => 'smartenableguestcomment',
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
                    'label' => $this->l('Show Author Name'),
                    'name' => 'smartshowauthor',
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
                ), array(
                    'type' => 'switch',
                    'label' => $this->l('Show Post Viewed'),
                    'name' => 'smartshowviewed',
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
                    'label' => $this->l('Show Author Name Style'),
                    'desc' => 'YES : \'First Name Last Name\'<br> NO : \'Last Name First Name\'',
                    'name' => 'smartshowauthorstyle',
                    'required' => false,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('First Name, Last Name')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Last Name, First Name')
                        )
                    )
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('AVATAR Image:'),
                    'name' => 'avatar',
                    'display_image' => false,
                    'desc' => $img_desc
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show \'No Image\''),
                    'name' => 'smartshownoimg',
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
                    'label' => $this->l('Show Category'),
                    'name' => 'smartdisablecatimg',
                    'required' => false,
                    'desc' => 'Show category image and description on category page',
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
					'type' => 'title_label',
					'name' => '',
					'label' => $this->l('Blogs Grid Options')
				),
				array(
					'type' => 'select',
					'name' => 'blog_category_post_layout',
					'label' => $this->l('Grid layout'),
					'class' => 'fixed-width-xxl',
					'options' => array(
						'query' => $_blogTypes,
						'id' => 'value',
						'name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Blogs Image Size '),
					'name' => 'blog_category_post_image_type',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $images_type,
						'id' => 'value',
						'name' => 'name'
					)
				),
				//////////////////////xl
				array(
					'type' => 'title_label',
					'name' => '',
					'label' => $this->l('Setup for - Desktop Large')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Blogs per line'),
					'name' => 'blog_category_post_xl',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_row,
						'id' => 'value',
						'name' => 'value'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Space between items'),
					'name' => 'blog_category_post_space_xl',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_space_item,
						'id' => 'value',
						'name' => 'value'
					)
				),
				//////////////////////lg
				array(
					'type' => 'title_label',
					'name' => '',
					'label' => $this->l('Setup for - Desktop')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Blogs per line'),
					'name' => 'blog_category_post_lg',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_row,
						'id' => 'value',
						'name' => 'value'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Space between items'),
					'name' => 'blog_category_post_space_lg',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_space_item,
						'id' => 'value',
						'name' => 'value'
					)
				),
				//////////////////////sm
				array(
					'type' => 'title_label',
					'name' => '',
					'label' => $this->l('Setup for - Tablet')
				),
				array(
					'type' => 'select',
					'name' => 'blog_category_post_md',
					'label' => $this->l('Blogs per line'),
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_row,
						'id' => 'value',
						'name' => 'value'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Space between items'),
					'name' => 'blog_category_post_space_md',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_space_item,
						'id' => 'value',
						'name' => 'value'
					)
				),
				//////////////////////xs
				array(
					'type' => 'title_label',
					'name' => '',
					'label' => $this->l('Setup for - Mobile')
				),
				array(
					'type' => 'select',
					'name' => 'blog_category_post_xs',
					'label' => $this->l('Blogs per line - phone'),
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_row,
						'id' => 'value',
						'name' => 'value'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Space between items'),
					'name' => 'blog_category_post_space_xs',
					'class' => 'fixed-width-xxl',
					'required' => false,
					'options' => array(
						'query' => $ad_space_item,
						'id' => 'value',
						'name' => 'value'
					)
				),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        foreach (Language::getLanguages(false) as $lang){
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
		}
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . 'token=' . Tools::getAdminTokenLite('AdminModules'),
            )
        );
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'save' . $this->name;

		$helper->fields_value['smartlistpostimagesize'] = Configuration::get('smartlistpostimagesize');
		$helper->fields_value['smartmodulepostimagesize'] = Configuration::get('smartmodulepostimagesize');
        $helper->fields_value['smartpostperpage'] = Configuration::get('smartpostperpage');
        $helper->fields_value['smartdataformat'] = Configuration::get('smartdataformat');
        $helper->fields_value['smartacceptcomment'] = Configuration::get('smartacceptcomment');
        $helper->fields_value['smartshowauthorstyle'] = Configuration::get('smartshowauthorstyle');
        $helper->fields_value['smartshowauthor'] = Configuration::get('smartshowauthor');
        $helper->fields_value['smartmainblogurl'] = Configuration::get('smartmainblogurl');
        $helper->fields_value['smartusehtml'] = Configuration::get('smartusehtml');
        $helper->fields_value['smartshowviewed'] = Configuration::get('smartshowviewed');
        $helper->fields_value['smartdisablecatimg'] = Configuration::get('smartdisablecatimg');
        $helper->fields_value['smartenablecomment'] = Configuration::get('smartenablecomment');
        $helper->fields_value['smartenableguestcomment'] = Configuration::get('smartenableguestcomment');
        $helper->fields_value['smartshownoimg'] = Configuration::get('smartshownoimg');
        $helper->fields_value['smartblogurlpattern'] = Configuration::get('smartblogurlpattern');

		foreach (Language::getLanguages(false) as $lang){
			$helper->fields_value['smartblogmetatitle'][(int)$lang['id_lang']] =html_entity_decode(Configuration::get('smartblogmetatitle', (int)$lang['id_lang']));
            $helper->fields_value['smartblogmetakeyword'][(int)$lang['id_lang']] =html_entity_decode(Configuration::get('smartblogmetakeyword', (int)$lang['id_lang']));
            $helper->fields_value['smartblogmetadescrip'][(int)$lang['id_lang']] =html_entity_decode(Configuration::get('smartblogmetadescrip', (int)$lang['id_lang']));
		}
		
		$helper->fields_value['blog_category_post_layout'] = Configuration::get('blog_category_post_layout');
		$helper->fields_value['blog_category_post_image_type'] = Configuration::get('blog_category_post_image_type');
		$helper->fields_value['blog_category_post_xl'] = Configuration::get('blog_category_post_xl');
		$helper->fields_value['blog_category_post_lg'] = Configuration::get('blog_category_post_lg');
		$helper->fields_value['blog_category_post_md'] = Configuration::get('blog_category_post_md');
		$helper->fields_value['blog_category_post_xs'] = Configuration::get('blog_category_post_xs');
		$helper->fields_value['blog_category_post_space_xl'] = Configuration::get('blog_category_post_space_xl');
		$helper->fields_value['blog_category_post_space_lg'] = Configuration::get('blog_category_post_space_lg');
		$helper->fields_value['blog_category_post_space_md'] = Configuration::get('blog_category_post_space_md');
		$helper->fields_value['blog_category_post_space_xs'] = Configuration::get('blog_category_post_space_xs');
		        
        return $helper;
    }

    public static function GetSmartBlogUrl($id_lang = null, $id_shop = null)
    {
        $ssl = null;
        $force_ssl = null;
		
        $ssl_enable = Configuration::get('PS_SSL_ENABLED');
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if ($id_shop == null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        $rewrite_set = (int) Configuration::get('PS_REWRITING_SETTINGS');
        if ($ssl === null) {
            if ($force_ssl === null)
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            $ssl = $force_ssl;
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null){
            $shop = new Shop($id_shop);
		}
        else{
            $shop = Context::getContext()->shop;
		}
        $base = ($ssl == 1 && $ssl_enable == 1) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain;
        $langUrl = Language::getIsoById($id_lang) . '/';
        if ((!$rewrite_set && in_array($id_shop, array((int) Context::getContext()->shop->id, null))) || !Language::isMultiLanguageActivated($id_shop) || !(int) Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop)){
            $langUrl = '';
		}

        return $base . $shop->getBaseURI() . $langUrl;
    }

    public static function GetSmartBlogLink($rule = 'smartblog', $params = array(), $id_lang = null, $id_shop = null)
    {		
        switch ($rule) {
            case 'smartblog_search_rule':
                $rule = 'module-smartblog-search';
                break;
            case 'smartblog_tag_rule':
                $rule = 'module-smartblog-tagpost';
                break; 
			case 'smartblog_archive_rule':
                $rule = 'module-smartblog-archive';
                break; 
			case 'smartblog_category_rule':
                $rule = 'module-smartblog-category';
                break; 
			case 'smartblog_post_rule':
                $rule = 'module-smartblog-details';
                break; 
            default:
                $rule = 'module-smartblog-all';
        }
				
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
		
        if ($id_shop == null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
		
        $url = smartblog::GetSmartBlogUrl($id_lang, $id_shop);
        $dispatcher = Dispatcher::getInstance();
        $force_routes = (bool) Configuration::get('PS_REWRITING_SETTINGS');
		
		$smartblogurlpattern = (int) Configuration::get('smartblogurlpattern');
		
		if(isset($_GET['id_blog'])){
			unset($_GET['id_blog']);
		}
		
		if($smartblogurlpattern == 1){
			if(isset($params['id_post'])){
				unset($params['id_post']);
			}
			if(isset($params['id_blog_category'])){
				unset($params['id_blog_category']);
			}
		}
		
        return $url . $dispatcher->createUrl($rule, $id_lang, $params, $force_routes, $anchor = '', $id_shop);
		
    }

    public function hookActionLanguageLinkParameters($params)
    {
        if(isset($params['linkParams']['module']) && $params['linkParams']['module'] == $this->name){
            $controller = Dispatcher::getInstance()->getController();

            if($controller == 'category' && isset($params['linkParams']['rewrite'])){
                $category = new BlogCategory(self::categoryslug2id($params['linkParams']['rewrite']), $params['linkIdLang'], $this->context->shop->id);
    
                $params['linkParams']['rewrite'] = $category->link_rewrite;
            }
    
            if($controller == 'details' && isset($params['linkParams']['rewrite'])){
                $post = new SmartBlogPost(self::slug2id($params['linkParams']['rewrite']), $params['linkIdLang'], $this->context->shop->id);
    
                $params['linkParams']['rewrite'] = $post->link_rewrite;
            }
        }
    }

    public function hookModuleRoutes($params)
    {
        return $this->smartBlogRoutes();
    }
	
	public function smartBlogRoutes()
    {
		$slug = Configuration::get('smartmainblogurl');
		
        $usehtml = (int) Tools::getValue('smartusehtml', Configuration::get('smartusehtml'));
		
        if ($usehtml) {
            $html = '.html';
        } else {
            $html = '';
        }

        $pattern = (int) Configuration::get('smartblogurlpattern');

        $k_category = [];
        if( $pattern != 1 ){
            $k_category['id_blog_category'] = array('regexp' => '[0-9]+', 'param' => 'id_blog_category');
        }
        $k_category['rewrite'] = array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite');

        $k_details = [];
        if( $pattern != 1 ){
            $k_details['id_post'] = array('regexp' => '[0-9]+', 'param' => 'id_post');
        }
        $k_details['rewrite'] = array('regexp' => self::REWRITE_PATTERN, 'param' => 'rewrite');

        return array(
            'module-smartblog-all' => array(
                'controller' => 'all',
                'rule' => $slug . $html,
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'smartblog'
                )
            ),
            'module-smartblog-search' => array(
				'controller' => 'search',
				'rule' => $slug .  '/' . Tools::str2url($this->l('search')) . $html,
				'keywords' => array(),
				'params' => array(
					'fc' => 'module',
					'module' => 'smartblog'
				)
            ),
            'module-smartblog-tagpost' => array(
				'controller' => 'tagpost',
				'rule' => $slug .  '/' . Tools::str2url($this->l('tag')) . $html,
				'keywords' => array(),
				'params' => array(
					'fc' => 'module',
					'module' => 'smartblog'
				)
            ),
            'module-smartblog-archive' => array(
                'controller' => 'archive',
				'rule' => $slug .  '/' . Tools::str2url($this->l('archive')) . $html,
				'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'smartblog'
                )
            ),
            'module-smartblog-category' => array(
                'controller' => 'category',
                'rule' => $slug . '/' . Tools::str2url($this->l('category')) . '/' . ( $pattern != 1 ? '{id_blog_category}_' : '' ) . '{rewrite}' . $html,
                'keywords' => $k_category,
                'params' => array(
                    'fc' => 'module',
                    'module' => 'smartblog'
                )
            ),
            'module-smartblog-details' => array(
                'controller' => 'details',
                'rule' => $slug .'/' . ( $pattern != 1 ? '{id_post}_' : '' ) . '{rewrite}' . $html,
                'keywords' => $k_details,
                'params' => array(
                    'fc' => 'module',
                    'module' => 'smartblog'
                )
            )
		);
	}

    public static function displayDate($date, $id_lang = null, $full = false, $separator = null)
    {
        if ($id_lang !== null) {
            Tools::displayParameterAsDeprecated('id_lang');
        }
        if ($separator !== null) {
            Tools::displayParameterAsDeprecated('separator');
        }

        if (!$date || !($time = strtotime($date))) {
            return $date;
        }

        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00') {
            return '';
        }

        if (!Validate::isDate($date) || !Validate::isBool($full)) {
            throw new PrestaShopException('Invalid date');
        }

        $date_format = Configuration::get('smartdataformat');

        return self::_translateDate( date($date_format, $time) );
    }

    public static function _translateDate($date)
    {
        $module = Module::getInstanceByName('smartblog');

        $months = [ 
            'Jan' => $module->l('Jan'),
            'Feb' => $module->l('Feb'),
            'Mar' => $module->l('Mar'),
            'Apr' => $module->l('Apr'),
            'May' => $module->l('May'),
            'Jun' => $module->l('Jun'),
            'Jul' => $module->l('Jul'),
            'Aug' => $module->l('Aug'),
            'Sep' => $module->l('Sep'),
            'Oct' => $module->l('Oct'),
            'Nov' => $module->l('Nov'),
            'Dec' => $module->l('Dec'),

            'January' => $module->l('January'),
            'February' => $module->l('February'),
            'March' => $module->l('March'),
            'April' => $module->l('April'),
            'May' => $module->l('May'),
            'June' => $module->l('June'),
            'July' => $module->l('July'),
            'August' => $module->l('August'),
            'September' => $module->l('September'),
            'October' => $module->l('October'),
            'November' => $module->l('November'),
            'December' => $module->l('December'),
        ];

        foreach ($months as $key => $value) {
            if(strpos($date, $key) !== false){
                $date = str_replace($key, $value, $date);
                break;
            }
        }

        $days = [ 
            'Sun' => $module->l('Sun'),
            'Mon' => $module->l('Mon'),
            'Tue' => $module->l('Tue'),
            'Wed' => $module->l('Wed'),
            'Thu' => $module->l('Thu'),
            'Fri' => $module->l('Fri'),
            'Sat' => $module->l('Sat'),

            'Sunday' => $module->l('Sunday'),
            'Monday' => $module->l('Monday'),
            'Tuesday' => $module->l('Tuesday'),
            'Wednesday' => $module->l('Wednesday'),
            'Thursday' => $module->l('Thursday'),
            'Friday' => $module->l('Friday'),
            'Saturday' => $module->l('Saturday'),
        ];

        foreach ($days as $key => $value) {
            if(strpos($key, $date) !== false){
                $date = str_replace($key, $value, $date);
                break;
            }
        }

        return $date;
    }

    public static function categoryslug2id($rewrite)
    {
        $sql = 'SELECT p.id_smart_blog_category 
                FROM `' . _DB_PREFIX_ . 'smart_blog_category_lang` p 
                WHERE p.link_rewrite =  "' . pSQL($rewrite) . '"';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)){
            return false;
		}
        return $result['id_smart_blog_category'];
    }

    public static function slug2id($rewrite)
    {
        $sql = 'SELECT p.id_smart_blog_post 
                FROM `' . _DB_PREFIX_ . 'smart_blog_post_lang` p 
                WHERE p.link_rewrite =  "' . pSQL($rewrite) . '"';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)){
            return false;
		}
		
        return $result['id_smart_blog_post'];
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
    }

    public function hookActionExportGDPRData($customer)
    {
    }

}