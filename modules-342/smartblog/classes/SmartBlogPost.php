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

class SmartBlogPost extends ObjectModel
{
    public $id;
    public $id_smart_blog_post;
    public $id_author;
    public $position = 0;
    public $active = 1;
    public $available;
    public $created;
    public $modified;
    public $short_description;
    public $viewed;
    public $comment_status = 1;
    public $associations;
    public $meta_title;
    public $meta_keyword;
    public $meta_description;
    public $image;
    public $content;
    public $link_rewrite;
    public $is_featured;
    public $id_smart_blog_category;
    public static $definition = array(
        'table' => 'smart_blog_post',
        'primary' => 'id_smart_blog_post',
        'multilang' => true,
        'fields' => array(
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_author' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'available' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'created' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'modified' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'viewed' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'is_featured' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'comment_status' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'associations' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'image' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'meta_title' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'lang' => true, 'required' => true),
            'meta_keyword' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
            'short_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true),
            'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
            'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true)
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation('smart_blog_post', array('type' => 'shop'));
        parent::__construct($id, $id_lang, $id_shop);
    }

    public static function getRelatedPostsById_post($id_post = null)
    {
        if (Configuration::get('smartshowrelatedpost') != '' && Configuration::get('smartshowrelatedpost') != null) {
            $limit = Configuration::get('smartshowrelatedpost');
        } else {
            $limit = 5;
        }
        
        
        $sql = 'SELECT itl.*,it.* FROM `' . _DB_PREFIX_ . 'smart_blog_post` it,`' . _DB_PREFIX_ . 'smart_blog_post_category` itc1, `' . _DB_PREFIX_ . 'smart_blog_post_category` itc2 ,`' . _DB_PREFIX_ . 'smart_blog_post_lang` itl, `' . _DB_PREFIX_ . 'smart_blog_post_shop` its'

                . ' WHERE it.id_smart_blog_post = itc2.id_smart_blog_post AND itl.id_smart_blog_post = itc2.id_smart_blog_post AND  itc1.id_smart_blog_category =itc2.id_smart_blog_category  AND itc1.id_smart_blog_post ='.(int)$id_post.' AND itc2.id_smart_blog_post <>'.(int)$id_post.' AND it.active =1 AND itl.id_lang = '.(int) Context::getContext()->language->id.' AND its.id_smart_blog_post = it.id_smart_blog_post AND its.id_shop = '.(int) Context::getContext()->shop->id. ' ORDER BY it.id_smart_blog_post DESC LIMIT 0,' . (int)$limit;

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql); 
            
            $id_posts = array();
            
            foreach($result as $id_item){
                
                if(!in_array($id_item, $id_posts)){
                    $id_posts[]=$id_item;
                }
                
            }
            return $id_posts;
    }

    public static function getPost($id_post, $id_lang = null)
    {
        $result = array();
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post 
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND p.active= 1 AND p.id_smart_blog_post = ' . (int)$id_post;

        if (!$post = Db::getInstance()->executeS($sql)){
            return false;
		}

        $selected_cat = BlogCategory::getPostCategoriesFull((int) $post[0]['id_smart_blog_post'], $id_lang);

        $result['id_category'] = 1;
        $result['cat_link_rewrite'] = '';
        $result['cat_name'] = '';

        foreach ($selected_cat as $key => $value) {
            $result['id_category'] = $selected_cat[$key]['id_category'];
            $result['cat_link_rewrite'] = $selected_cat[$key]['link_rewrite'];
            $result['cat_name'] = $selected_cat[$key]['name'];
        }
        
        $result['id_post'] = $post[0]['id_smart_blog_post'];
        $result['meta_title'] = $post[0]['meta_title'];
        $result['meta_description'] = $post[0]['meta_description'];
        $result['short_description'] = $post[0]['short_description'];
        $result['meta_keyword'] = $post[0]['meta_keyword'];
        $result['link_rewrite'] = $post[0]['link_rewrite'];
        $result['content'] = $post[0]['content'];
        $result['active'] = $post[0]['active'];
        $result['created'] = $post[0]['created'];
		$result['modified'] = $post[0]['modified'];
        $result['comment_status'] = $post[0]['comment_status'];
        $result['viewed'] = $post[0]['viewed'];
        $result['is_featured'] = $post[0]['is_featured'];
        $employee = new Employee($post[0]['id_author']);
        $result['lastname'] = $employee->lastname;
        $result['firstname'] = $employee->firstname;
        if (file_exists(_PS_MODULE_DIR_ . 'smartblog/images/' . $post[0]['id_smart_blog_post'] . '.jpg')) {
            $image = $post[0]['id_smart_blog_post'] . '.jpg';
            $result['post_img'] = $image;
        } else {
            $result['post_img'] = NULL;
        }
        
        return $result;
    }

    public static function getAllPost($id_lang = null, $limit_start = 0, $limit = 5, $id_shop = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if ($id_shop == null) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        if ($limit_start == '' || $limit_start < 0){
            $limit_start = 0;
		}
        if ($limit == ''){
            $limit = 5;
		}
        $result = array();
        $BlogCategory = '';

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . $id_shop . '
                WHERE pl.id_lang=' . $id_lang . '
                AND p.active= 1 ORDER BY p.id_smart_blog_post DESC LIMIT ' . (int)$limit_start . ',' . (int)$limit;

        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}

        return $posts;
    }

    public static function getToltal($id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND p.active= 1';
        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}

        $return_count = 0;

        foreach ($posts as $post) {
            if (new DateTime() >= new DateTime($post['created'])){
                $return_count++;
            } else {
                continue;
            }
        }
        
        return $return_count;
    }

    public static function getToltalByCategory($id_lang = null, $id_category = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if ($id_category == null) {
            $id_category = 1;
        }
        $sql = 'SELECT COUNT(*) AS num FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN
                ' . _DB_PREFIX_ . 'smart_blog_post_category pc ON p.id_smart_blog_post=pc.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON pc.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND p.active= 1 AND pc.id_smart_blog_category = ' . (int)$id_category;
        return Db::getInstance()->getValue($sql);
    }

    public static function addTags($id_lang = null, $id_post = null, $tag_list = [], $separator = ',')
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if (!Validate::isUnsignedId($id_lang)){
            return false;
		}

        if (!is_array($tag_list)){
            $tag_list = array_filter(array_unique(array_map('trim', preg_split('#\\' . $separator . '#', $tag_list, null, PREG_SPLIT_NO_EMPTY))));
		}

        $list = array();
        if (is_array($tag_list)){
            foreach ($tag_list as $tag) {
                $id_tag = BlogTag::TagExists($tag, (int) $id_lang);
                if (!$id_tag) {
                    $tag_obj = new BlogTag(null, $tag, (int) $id_lang);
                    if (!Validate::isLoadedObject($tag_obj)) {
                        $tag_obj->name = $tag;
                        $tag_obj->id_lang = (int) $id_lang;
                        $tag_obj->add();
                    }
                    if (!in_array($tag_obj->id, $list))
                        $list[] = $tag_obj->id;
                }
                else {
                    if (!in_array($id_tag, $list))
                        $list[] = $id_tag;
                }
            }
		}
        $data = '';
        foreach ($list as $tag){
            $data .= '(' . (int) $tag . ',' . (int) $id_post . '),';
		}
        $data = rtrim($data, ',');

        return Db::getInstance()->execute('
		INSERT INTO `' . _DB_PREFIX_ . 'smart_blog_post_tag` (`id_tag`, `id_post`)
		VALUES ' . $data);
    }
    public static function subStr($string, $length){
        return strlen($string) > $length ? substr($string,0,$length)."..." : $string;
    }
    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)){
            return false;
		}
        return true;
    }

    public static function postViewed($id_post)
    {

        $sql = 'UPDATE ' . _DB_PREFIX_ . 'smart_blog_post as p SET p.viewed = (p.viewed+1) where p.id_smart_blog_post = ' . (int)$id_post;

        return Db::getInstance()->execute($sql);

    }

    public static function deleteTagsForBlog($id_post)
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'smart_blog_post_tag` WHERE `id_post` = ' . (int) $id_post);
    }

    public static function getBlogTags($id_post, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT  t.`name`
		FROM ' . _DB_PREFIX_ . 'smart_blog_tag t
		LEFT JOIN ' . _DB_PREFIX_ . 'smart_blog_post_tag pt ON (pt.id_tag = t.id_tag AND t.id_lang = ' . (int)$id_lang . ')
		WHERE pt.`id_post`=' . (int) $id_post)){
            return false;
		}
        return $tmp;
    }

    public static function getBlogTagsBylang($id_post, $id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $tags = '';
        if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                    SELECT  t.`name`
                    FROM ' . _DB_PREFIX_ . 'smart_blog_tag t
                    LEFT JOIN ' . _DB_PREFIX_ . 'smart_blog_post_tag pt ON (pt.id_tag = t.id_tag AND t.id_lang = ' . (int)$id_lang . ')
                    WHERE pt.`id_post`=' . (int) $id_post)){
            return false;
		}
        $i = 1;
        foreach ($tmp as $val) {
            if ($i >= count($tmp)) {
                $tags .= $val['name'];
            } else {
                $tags .= $val['name'] . ',';
            }
            $i++;
        }
        return $tags;
    }

    public static function getPopularPosts($id_lang = null)
    {
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        if (Configuration::get('smartshowpopularpost') != '' && Configuration::get('smartshowpopularpost') != null) {
            $limit = Configuration::get('smartshowpopularpost');
        } else {
            $limit = 5;
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT p.id_author ,p.viewed ,p.created , p.id_smart_blog_post,pl.meta_title,pl.link_rewrite FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                    ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                    ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                    WHERE pl.id_lang=' . (int)$id_lang . ' AND p.active = 1 ORDER BY p.viewed DESC LIMIT 0,' . (int)$limit);
        foreach ($result as $key => $value) {
            $result[$key]['created'] = smartblog::displayDate($value['created']);
        }
        return $result;
    }

    public static function getRecentPosts($id_lang = null)
    {
        
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
         
		if (Configuration::get('smartshowrecentpost') != '' && Configuration::get('smartshowrecentpost') != null) {
			$limit = Configuration::get('smartshowrecentpost');
		} else {
			$limit = 5;
		}
         
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT  p.id_author,p.id_smart_blog_post,p.created,pl.meta_title,pl.link_rewrite FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . '  AND p.active = 1 ORDER BY p.id_smart_blog_post DESC LIMIT 0,' . (int)$limit);

        foreach ($result as $key => $value) {
            $result[$key]['created'] = smartblog::displayDate($value['created']);
        }

        return $result;
    }

    public static function tagsPost($tags, $id_lang = null, $limit_start = 0, $limit = 5)
    {
        $result = array();
        if ($id_lang == null){
            $id_lang = (int) Context::getContext()->language->id;
		}

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post=ps.id_smart_blog_post  AND  ps.id_shop = ' . (int) Context::getContext()->shop->id . ' INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_tag pt ON pl.id_smart_blog_post = pt.id_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_tag t ON pt.id_tag=t.id_tag 
                WHERE pl.id_lang=' . (int)$id_lang . '  AND p.active = 1 	 		
                AND t.name="' . pSQL($tags) . '" ORDER BY p.id_smart_blog_post DESC LIMIT ' . (int)$limit_start . ',' . (int)$limit;	

        if (!$posts = Db::getInstance()->executeS($sql)){
            return array();
		}

        return $posts;
    }

    public static function getArchiveResult($month = null, $year = null, $day = null, $limit_start = 0, $limit = 5, $id_lang = null)
    {
        $BlogCategory = '';
        $day = (int)$day;
        $month = (int)$month;
        $year = (int)$year;
        $result = array();
        if ($id_lang == null) {
            $id_lang = (int) Context::getContext()->language->id;
        }
        $id_lang = (int)$id_lang;
        $limit_start = (int)$limit_start;
        $limit = (int)$limit;
        if ($month != '' and $month != NULL and $year != '' and $year != NULL and $day != '' and $day != NULL) {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post s INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_lang sl ON s.id_smart_blog_post = sl.id_smart_blog_post
                 and sl.id_lang = ' . $id_lang . ' INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON ps.id_smart_blog_post = s.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
            where s.active = 1 and DAY(s.created) = ' . $day . ' and MONTH(s.created) = ' . $month . ' AND YEAR(s.created) = ' . $year . ' ORDER BY s.id_smart_blog_post DESC LIMIT ' . $limit_start . ',' . $limit;	
        } elseif ($month != '' and $month != NULL and $year != '' and $year != NULL) {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post s INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_lang sl ON s.id_smart_blog_post = sl.id_smart_blog_post
                 and sl.id_lang = ' . $id_lang . ' INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON ps.id_smart_blog_post = s.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
            where s.active = 1 and MONTH(s.created) = ' . $month . ' AND YEAR(s.created) = ' . $year . ' ORDER BY s.id_smart_blog_post DESC LIMIT ' . $limit_start . ',' . $limit;	
        } elseif ($month == '' and $month == NULL and $year != '' and $year != NULL) {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post s INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_lang sl ON s.id_smart_blog_post = sl.id_smart_blog_post
                 and sl.id_lang = ' . $id_lang . ' INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON ps.id_smart_blog_post = s.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
           where s.active = 1 AND YEAR(s.created) = ' . $year . ' ORDER BY s.id_smart_blog_post DESC LIMIT ' . $limit_start . ',' . $limit;	
				
        } elseif ($month != '' and $month != NULL and $year == '' and $year == NULL) {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post s INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_lang sl ON s.id_smart_blog_post = sl.id_smart_blog_post
                 and sl.id_lang = ' . $id_lang . ' INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON ps.id_smart_blog_post = s.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
           where s.active = 1 AND   MONTH(s.created) = ' . $month . ' ORDER BY s.id_smart_blog_post DESC LIMIT ' . $limit_start . ',' . $limit;	
        } else {
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post s INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_lang sl ON s.id_smart_blog_post = sl.id_smart_blog_post
                 and sl.id_lang = ' . $id_lang . ' INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON ps.id_smart_blog_post = s.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
            where s.active = 1 ORDER BY s.id_smart_blog_post DESC LIMIT ' . $limit_start . ',' . $limit;	
        }
        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}

        return $posts;
    }

    public static function getArchiveD($month, $year)
    {

        $sql = 'SELECT DAY(p.created) as day FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . ' 
                 where MONTH(p.created) = ' . (int)$month . ' AND YEAR(p.created) = ' . (int)$year . ' GROUP BY DAY(p.created)';

        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}

        return $posts;
    }

    public static function getArchiveM($year)
    {

        $sql = 'SELECT MONTH(p.created) as month FROM ' . _DB_PREFIX_ . 'smart_blog_post p  INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . ' 
                 where YEAR(p.created) = ' . (int)$year . ' GROUP BY MONTH(p.created)';

        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}
        return $posts;
    }

    public static function getArchive()
    {
        $result = array();
        $sql = 'SELECT YEAR(p.created) as year FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . ' 
                GROUP BY YEAR(p.created) ORDER BY p.created DESC';

        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}

        $result = array(
            'id' => 0,
            'name' => '',
            'link' => '',
            'level_depth' => '',
            'desc' => '',
            'children' => array()
        );
        $i = 0;
        $count = 0;
        foreach ($posts as $key => $value) {
            $result['children'][$i] = array(
                'id' => $count,
                'name' => $value['year'],
                'link' => smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $value['year'])),
                'level_depth' => '',
                'desc' => '',
                'children' => array()
            );
            $count++;

            $months = self::getArchiveM($value['year']);
            $j = 0;
            foreach ($months as $month) {

                $monthNum  = $month['month'];
                $dateObj   = DateTime::createFromFormat('!m', $monthNum);
                $monthName = $dateObj->format('F');

                $result['children'][$i]['children'][$j] = array(
                    'id' => $count,
                    'name' => $monthName,
                    'link' => smartblog::GetSmartBlogLink('smartblog_archive_rule', array('year' => $value['year'], 'month' => $month['month'])),
                    'level_depth' => '',
                    'desc' => '',
                    'children' => array()
                );
                $count++;
                $days = self::getArchiveD($month['month'], $value['year']);
                $k = 0;
                $j++;
            }
            $i++;
        }

        return $result;
    }

    public static function getArchiveOld()
    {
        $result = array();
        $sql = 'SELECT YEAR(p.created) as year FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . ' 
                GROUP BY YEAR(p.created)';

        if (!$posts = Db::getInstance()->executeS($sql)){
            return false;
		}
        $i = 0;
        foreach ($posts as $value) {
            $result[$i]['year'] = $value['year'];
            $result[$i]['month'] = self::getArchiveM($value['year']);
            $months = self::getArchiveM($value['year']);
            $j = 0;
            foreach ($months as $month) {
                $result[$i]['month'][$j]['day'] = self::getArchiveD($month['month'], $value['year']);
                $j++;
            }
            $i++;
        }
        return $result;
    }

    //  need optimization ($keyword = NULL, $id_lang = NULL, $limit_start = 0, $limit = 5)
    public static function SmartBlogSearchPost($keyword = NULL, $id_lang = NULL, $limit_start = 0, $limit = 5)
    {
        if ($keyword == NULL){
            return array();
		}
        if ($id_lang == NULL){
            $id_lang = (int) Context::getContext()->language->id;
		}
        $keyword = pSQL($keyword);
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_lang pl, ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . ' 
                WHERE pl.id_lang=' . (int)$id_lang . '  AND p.active = 1 
                AND pl.id_smart_blog_post=p.id_smart_blog_post AND
                (pl.meta_title LIKE \'%' . $keyword . '%\' OR
                 pl.meta_keyword LIKE \'%' . $keyword . '%\' OR
                 pl.meta_description LIKE \'%' . $keyword . '%\' OR
                 pl.content LIKE \'%' . $keyword . '%\') ORDER BY p.id_smart_blog_post DESC LIMIT ' . (int)$limit_start . ',' . (int)$limit;
        if (!$posts = Db::getInstance()->executeS($sql)){
            return array();
		}

        return $posts;
    }

    public static function SmartBlogSearchPostCount($keyword = NULL, $id_lang = NULL)
    {
        if ($keyword == NULL){
            return 0;
		}
        if ($id_lang == NULL){
            $id_lang = (int) Context::getContext()->language->id;
		}
        $keyword = pSQL($keyword);
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_lang pl, ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '  
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND pl.id_smart_blog_post=p.id_smart_blog_post AND p.active = 1 AND 
                (pl.meta_title LIKE \'%' . $keyword . '%\' OR
                 pl.meta_keyword LIKE \'%' . $keyword . '%\' OR
                 pl.meta_description LIKE \'%' . $keyword . '%\' OR
                 pl.content LIKE \'%' . $keyword . '%\') ORDER BY p.id_smart_blog_post DESC';
		
        $posts = Db::getInstance()->executeS($sql);

        return count($posts);
    }

    public static function getBlogImage()
    {

        $sql = 'SELECT id_smart_blog_post FROM ' . _DB_PREFIX_ . 'smart_blog_post';

        if (!$result = Db::getInstance()->executeS($sql)){
            return false;
		}
        return $result;
    }

    public static function GetPostSlugById($id_post, $id_lang = null)
    {
        if ($id_lang == null){
            $id_lang = (int) Context::getContext()->language->id;
		}

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post 
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND p.active= 1 AND p.id_smart_blog_post = ' . (int)$id_post;

        if (!$post = Db::getInstance()->executeS($sql)){
            return false;
		}

        return $post[0]['link_rewrite'];
    }

    public static function GetPostMetaByPost($id_post, $id_lang = null)
    {
        $meta = array();
        if ($id_lang == null){
            $id_lang = (int) Context::getContext()->language->id;
		}

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post 
                WHERE pl.id_lang=' . (int)$id_lang . '
                AND p.active= 1 AND p.id_smart_blog_post = ' . (int)$id_post;

        if (!$post = Db::getInstance()->executeS($sql)){
            return false;
		}

        if ($post[0]['meta_title'] == '' && $post[0]['meta_title'] == NULL) {
            $meta['meta_title'] = Configuration::get('smartblogmetatitle', $id_lang);
        } else {
            $meta['meta_title'] = $post[0]['meta_title'];
        }

        if ($post[0]['meta_description'] == '' && $post[0]['meta_description'] == NULL) {
            $meta['meta_description'] = Configuration::get('smartblogmetadescrip', $id_lang);
        } else {
            $meta['meta_description'] = $post[0]['meta_description'];
        }

        if ($post[0]['meta_keyword'] == '' && $post[0]['meta_keyword'] == NULL) {
            $meta['meta_keywords'] = Configuration::get('smartblogmetakeyword', $id_lang);
        } else {
            $meta['meta_keywords'] = $post[0]['meta_keyword'];
        }
        return $meta;
    }

    public static function GetPostLatestHome($limit, $imgType = 'home_default', $id_lang = null, $orderby = 'p.id_smart_blog_post', $orderway = 'DESC')
    {
        if ($limit == '' && $limit == null){
            $limit = 3;
		}
        if ($id_lang == null){
            $id_lang = (int) Context::getContext()->language->id;
		}

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . ' 		
                AND p.active= 1 ORDER BY  '.$orderby.' '.$orderway.'
                LIMIT ' . (int)$limit;

        $posts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return self::ConvertPost($posts, $imgType);
    }
	
    public static function GetPostByCategory($id_category, $limit, $imgType = 'home_default', $id_lang = null, $orderby = 'p.id_smart_blog_post', $orderway = 'DESC')
    {
		$limit_start = 0;

        if ($id_lang == null){
            $id_lang = (int) Context::getContext()->language->id;
		}
		
		$sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'smart_blog_post_lang pl INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post p ON pl.id_smart_blog_post=p.id_smart_blog_post INNER JOIN
                ' . _DB_PREFIX_ . 'smart_blog_post_category pc ON p.id_smart_blog_post=pc.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON pl.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . ' and p.active = 1 AND pc.id_smart_blog_category = ' . $id_category . '
                ORDER BY '.$orderby.' '.$orderway.' LIMIT ' . (int)$limit_start . ',' . (int)$limit;
		
		$posts = Db::getInstance()->executeS($sql);

        return self::ConvertPost($posts, $imgType);
    }
	
    public static function ConvertPost($posts, $imgType)
    {		
		$images = BlogImageType::GetImageByType($imgType);
		$result = array();
        $i = 0;
        foreach ($posts as $post) {
            $result[$i]['id'] = $post['id_smart_blog_post'];
            $result[$i]['title'] = $post['meta_title'];
            $result[$i]['meta_description'] = strip_tags($post['meta_description']);
            $result[$i]['short_description'] = strip_tags($post['short_description']);
            $result[$i]['content'] = strip_tags($post['content']);
            $result[$i]['display_date'] = smartblog::displayDate($post['created']);
            $result[$i]['created'] = $post['created'];
            $result[$i]['viewed'] = $post['viewed'];
            $result[$i]['is_featured'] = $post['is_featured'];
            $result[$i]['link_rewrite'] = $post['link_rewrite'];
				
			$result[$i]['url'] = SmartBlogLink::getSmartBlogPostLink($post['id_smart_blog_post'], $post['link_rewrite']);
			$result[$i]['image']['url'] = SmartBlogLink::getImageLink($post['link_rewrite'], $post['id_smart_blog_post'], $imgType);

			foreach($images as $image){
				if($image['type'] == 'post'){
					$result[$i]['image']['type'] = 'blog_post_'.$imgType;
					$result[$i]['image']['width'] = $image['width'];
					$result[$i]['image']['height'] = $image['height'];
					break;
				}
			}			

			$result[$i]['totalcomment'] = BlogComment::getToltalComment($result[$i]['id']);
			
			$employee = new Employee( $post['id_author']);
			$result[$i]['lastname'] = $employee->lastname;
			$result[$i]['firstname'] = $employee->firstname;
			
			$result[$i]['viewed'] = $post['viewed'];
            $result[$i]['smartshowauthorstyle'] = Configuration::get('smartshowauthorstyle');
            $result[$i]['smartshowauthor'] = Configuration::get('smartshowauthor');

            $i++;
        }
        return $result;
    }
	
    public static function getNextPostsById($id_lang = null, $id_post = null)
    {
        $sql = 'SELECT  p.id_smart_blog_post,pl.meta_title,pl.link_rewrite FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . '  AND p.active = 1 AND p.id_smart_blog_post = ' . (int)$id_post . '+1';
  
        if (!$posts_next = Db::getInstance()->executeS($sql)){
            return false;
		}
		
        return $posts_next;
    }

    public static function getPreviousPostsById($id_lang = null, $id_post = null)
    {
        $sql = 'SELECT  p.id_smart_blog_post,pl.meta_title,pl.link_rewrite FROM ' . _DB_PREFIX_ . 'smart_blog_post p INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_lang pl ON p.id_smart_blog_post=pl.id_smart_blog_post INNER JOIN 
                ' . _DB_PREFIX_ . 'smart_blog_post_shop ps ON p.id_smart_blog_post = ps.id_smart_blog_post AND ps.id_shop = ' . (int) Context::getContext()->shop->id . '
                WHERE pl.id_lang=' . (int)$id_lang . '  AND p.active = 1 AND p.id_smart_blog_post = ' . (int)$id_post . '-1';

 
        if (!$posts_previous = Db::getInstance()->executeS($sql)){
            return false;
		}
        return $posts_previous;
    }

}