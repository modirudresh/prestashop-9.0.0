<?php

class AdminBlogPostController extends ModuleAdminController {

    public $max_image_size = null;

    public function __construct() {
        $this->table = 'smart_blog_post';
        $this->className = 'SmartBlogPost';
        $this->lang = true;
        $this->image_dir = '../modules/smartblog/images';
        $this->context = Context::getContext();
        $this->_defaultOrderBy = 'created';
        $this->_defaultorderWay = 'DESC';
        $this->bootstrap = true;
        if (Shop::isFeatureActive()){
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
		}
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->fields_list = array(
            'id_smart_blog_post' => array(
                'title' => $this->module->l('Id'),
                'width' => 50,
                'type' => 'text',
                'orderby' => true,
                'filter' => true,
                'search' => true
            ),
            'viewed' => array(
                'title' => $this->module->l('View'),
                'width' => 50,
                'type' => 'text',
                'lang' => true,
                'orderby' => true,
                'filter' => false,
                'search' => false
            ),
            'image' => array(
                'title' => $this->module->l('Image'),
                'image' => $this->image_dir,
                'orderby' => false,
                'search' => false,
                'width' => 200,
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'meta_title' => array(
                'title' => $this->module->l('Title'),
                'width' => 440,
                'type' => 'text',
                'lang' => true,
                'orderby' => true,
                'filter' => true,
                'search' => true
            ),
            'created' => array(
                'title' => $this->module->l('Posted Date'),
                'width' => 100,
                'type' => 'date',
                'lang' => true,
                'orderby' => true,
                'filter' => true,
                'search' => true
            ),
            'active' => array(
                'title' => $this->module->l('Status'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => true,
                'filter' => true,
                'search' => true
            )
        );
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'smart_blog_post_shop sbs ON a.id_smart_blog_post=sbs.id_smart_blog_post && sbs.id_shop IN(' . implode(',', Shop::getContextListShopID()) . ')';
        $this->_select = 'sbs.id_shop';
        $this->_defaultOrderBy = 'a.id_smart_blog_post';
        $this->_defaultOrderWay = 'DESC';
		
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->module->l('Delete selected items?'),
            ),
        );
		
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->_group = 'GROUP BY a.id_smart_blog_post';
        }
        
        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin(array('tagify', 'tablednd', 'autocomplete'));
    }

    public function renderForm() {
        if (!($obj = $this->loadObject(true))){
            return;
		}

        $shops = false;
        if (Shop::isFeatureActive()){
            $shops = Shop::getShops();
		}


        if ($shops){
            foreach ($shops as $key => $shop){
                if (!$obj->isAssociatedToShop($shop['id_shop'])){
                    unset($shops[$key]);
				}
			}
		}


        $languages = Language::getLanguages(false);
        //Added From Old 


        /*         * ************* featured image ****************** */

        $featured_image = _MODULE_SMARTBLOG_DIR_ . $obj->id . '.jpg';
        $image_url = ImageManager::thumbnail($featured_image, $this->table . '_' . (int)pSQL(Tools::getvalue('id_smart_blog_post')) . '.jpg', 200, 'jpg', true, true);
        $image_size = file_exists($featured_image) ? filesize($featured_image) / 1000 : false;

        /*         * ************* featured image ****************** */

        // image gallary
        $id_smart_blog_post = (int) Tools::getValue('id_smart_blog_post');

        //start sdsimage type
        $image_uploader = new HelperImageUploader('file');
        $image_uploader->setMultiple(!(Tools::getUserBrowser() == 'Apple Safari' && Tools::getUserPlatform() == 'Windows'))
                ->setUseAjax(true)->setUrl(
                Context::getContext()->link->getAdminLink('AdminSmartBlogAjax') . '&ajax=1&id_smart_blog_post=' . (int) Tools::getvalue('id_smart_blog_post')
                . '&action=addGallaryImage');


        // test code

        $root = BlogCategory::getRootCategory();
        $default_category = $root['id_smart_blog_category'];
        if (!Tools::isSubmit('id_smart_blog_post')) {
            $selected_cat = BlogCategory::getCategoryInformations(Tools::getValue('categoryBox', array($default_category)), $this->default_form_language);
        } else {
            if (Tools::isSubmit('categoryBox')) {
                $selected_cat = BlogCategory::getCategoryInformations(Tools::getValue('categoryBox', array($default_category)), $this->default_form_language);
            } else {
                $selected_cat = BlogCategory::getPostCategoriesFull((int) Tools::getValue('id_smart_blog_post'), $this->default_form_language);
            }
        }

        $categories = array();
        foreach ($selected_cat as $key => $category) {
            $categories[] = $key;
        }
        $tree = new SmartBlogHelperTreeCategories('smartblog-associated-categories-tree', 'Associated categories');
        $tree->setTemplate('tree_associated_categories.tpl')
                ->setHeaderTemplate('tree_associated_header.tpl')
                ->setRootCategory((int) $root['id_category'])
                ->setUseCheckBox(true)
                ->setUseSearch(false)
                ->setSelectedCategories($categories);
        // end test code

        $temp_employees = Employee::getEmployees();
        $employees = array();
        foreach ($temp_employees as $employee) {
            $employee['fullname'] = $employee['firstname'] . ' ' . $employee['lastname'];
            $employee['id_select'] = $employee['id_employee'];
            $employees[] = $employee;
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Blog Post'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Blog Title'),
                    'name' => 'meta_title',
                    'id' => 'name',
                    'class' => 'copyMeta2friendlyURL',
                    'size' => 60,
                    'required' => true,
                    'desc' => $this->module->l('Enter Your Blog Post Title'),
                    'lang' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Author'),
                    'name' => 'id_author',
                    'options' => array(
                        'query' => $employees,
                        'id' => 'id_select',
                        'name' => 'fullname',
                    )
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->module->l('Short Description'),
                    'name' => 'short_description',
                    'rows' => 10,
                    'cols' => 62,
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->module->l('Enter Your Post Short Description')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->module->l('Description'),
                    'name' => 'content',
                    'lang' => true,
                    'rows' => 10,
                    'cols' => 62,
                    'class' => 'rte',
                    'autoload_rte' => true,
                    'desc' => $this->module->l('Enter Your Post Description')
                ),
                array(
                    'type' => 'file',
                    'label' => $this->module->l('Featured Image:'),
                    'name' => 'image',
                    'display_image' => true,
                    'image' => $image_url,
                    'size' => $image_size,
                    'delete_url' => self::$currentIndex . '&' . $this->identifier . '=' . pSQL(Tools::getvalue('id_smart_blog_post')) . '&token=' . $this->token . '&deleteImage=1',
                    'hint' => $this->module->l('Upload a feature image from your computer.'),
		    'desc'          => $this->module->l('Upload Only .jpg formatted image.'),
                ),
                array(
                    'type' => 'html',
                    'label' => $this->module->l('Blog Categories'),
                    'name' => 'id_category_big',
                    'required' => true,
                    'html_content' => $tree->render(),
                    'desc' => $this->module->l('Select Your Parent Category')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Meta Keyword'),
                    'name' => 'meta_keyword',
                    'lang' => true,
                    'size' => 60,
                    'required' => false,
                    'desc' => $this->module->l('Enter Your Post Meta Keyword. Separated by comma(,)')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->module->l('Meta Description'),
                    'name' => 'meta_description',
                    'rows' => 10,
                    'cols' => 62,
                    'lang' => true,
                    'required' => false,
                    'desc' => $this->module->l('Enter Your Post Meta Description')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->module->l('Link Rewrite'),
                    'name' => 'link_rewrite',
                    'size' => 60,
                    'lang' => true,
                    'required' => false,
                    'desc' => $this->module->l('Enetr Your Post Rewrite. Use In SEO Friendly URL')
                ),
                array(
                    'type' => 'tags',
                    'label' => $this->module->l('Tag'),
                    'name' => 'tags',
                    'size' => 60,
                    'lang' => true,
                    'required' => false,
                    'hint' => array(
                        $this->module->l('To add "tags" click in the field, write something, and then press "Enter."'),
                        $this->module->l('Invalid characters:') . ' &lt;&gt;;=#{}'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('Comment Status'),
                    'name' => 'comment_status',
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
				),
				array(
					'label' => $this->module->l('Published Date'),
					'name'  => 'created',
					'title' => $this->module->l('Published date'),
					'type'  => 'datetime',
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
                    )
                ), array(
                    'type' => 'hidden',
                    'name' => 'is_featured',
                    'required' => false,
                    'is_bool' => true,
                    'values' => 0
                )
            )
        );

        if (Hook::getIdByName('displayBackOfficeSmartBlogPosts')) {
            $this->fields_form['input'][count($this->fields_form['input'])] = unserialize(Hook::exec('displayBackOfficeSmartBlogPosts'));
        }

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->module->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }

        if (!($SmartBlogPost = $this->loadObject(true))){
            return;
	}

        $this->fields_form['submit'] = array(
            'title' => $this->module->l('Save'),
        );

        $image = ImageManager::thumbnail(_MODULE_SMARTBLOG_DIR_ . $SmartBlogPost->id_smart_blog_post . '.jpg', $this->table . '_' . (int) $SmartBlogPost->id_smart_blog_post . '.' . $this->imageType, 350, $this->imageType, true);

        $this->fields_value = array(
            'image' => $image ? $image : false,
            'size' => $image ? filesize(_MODULE_SMARTBLOG_DIR_ . $SmartBlogPost->id_smart_blog_post . '.jpg') / 1000 : false
        );
        if (Tools::getvalue('id_smart_blog_post') != '' && Tools::getvalue('id_smart_blog_post') != NULL) {
            foreach (Language::getLanguages(false) as $lang) {
                $this->fields_value['tags'][(int) $lang['id_lang']] = SmartBlogPost::getBlogTagsBylang((int) Tools::getvalue('id_smart_blog_post'), (int) $lang['id_lang']);
            }
        }

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int) Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
		
		$default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		
        foreach (Language::getLanguages(false) as $lang) {
             $this->tpl_form_vars['languages'][] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
		}
		
        $this->tpl_form_vars['max_image_size'] = $this->max_image_size / 1024 / 1024;
        $this->tpl_form_vars['iso_lang'] = $languages[0]['iso_code'];
        $this->tpl_form_vars['id_smart_blog_post'] = (int) Tools::getValue('id_smart_blog_post');
        $this->tpl_form_vars['default_language'] = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->tpl_form_vars['table'] = $this->table;
        $this->tpl_form_vars['token'] = Tools::getAdminTokenLite('AdminBlogPost');

        return parent::renderForm();
    }

    public function ajaxProcessGetCategoryTree() {
        $root_category = BlogCategory::getRootCategory();
        $category = pSQL(Tools::getValue('category', $root_category['id_category']));
        $full_tree = pSQL(Tools::getValue('fullTree', 0));
        $use_check_box = pSQL(Tools::getValue('useCheckBox', 1));
        $selected = Tools::getValue('selected', array());
        $id_tree = pSQL(Tools::getValue('type'));
        $input_name = str_replace(array('[', ']'), '', Tools::getValue('inputName', null));

        $tree = new SmartBlogHelperTreeCategories('subtree_associated_categories');
        $tree->setTemplate('subtree_associated_categories.tpl')
                ->setUseCheckBox($use_check_box)
                ->setUseSearch(false)
                ->setIdTree($id_tree)
                ->setSelectedCategories($selected)
                ->setFullTree($full_tree)
                ->setChildrenOnly(true)
                ->setNoJS(true)
                ->setRootCategory($category);

        if ($input_name) {
            $tree->setInputName($input_name);
        }

        die($tree->render());
    }
    protected function afterAdd($object)
    {

        $id = $this->object->id;
        if (!empty($id)) {
            $object = new SmartBlogPost($id);

            date_default_timezone_set(Configuration::get('PS_TIMEZONE'));
            // $object->id_author = $this->context->employee->id;
            $object->id_author = 1;

            $object->modified = date('Y-m-d H:i:s');
            if (strpos($object->created, '0000-00-00') !== FALSE || empty($object->created)) {
                $object->created = date('Y-m-d H:i:s');
            }
            $object->update();
            BlogCategory::updateAssocCat($object->id);
        Hook::exec('actionsbnewpost');
        }
    }

    protected function afterUpdate($object) {
        $id = $object->id;
        if (!empty($id)) {
            date_default_timezone_set(Configuration::get('PS_TIMEZONE'));
			$object->modified = date('Y-m-d H:i:s');
			$object->update();
			BlogCategory::updateAssocCat($object->id);
			// clear the post when update
			Hook::exec('actionsbupdatepost');
        }
        $res = parent::afterUpdate($object);
        $id_smart_blog_post = (int)Tools::getValue('id_smart_blog_post');
        $smart_blog_post = new SmartBlogPost($id_smart_blog_post);
        if (Validate::isLoadedObject($smart_blog_post)) {
//             $this->updateAccessories($object);
            $this->updateTags(Language::getLanguages(false), $object);
        }

        return $res;
    }
	
    public function postProcess() {

        if (!in_array($this->display, array('edit', 'add'))){
            $this->multishop_context_group = false;
		}
		
        if (Tools::isSubmit('forcedeleteImage') || (isset($_FILES['image']) && $_FILES['image']['size'] > 0) || Tools::getValue('deleteImage')) {
            $this->processForceDeleteImage();
            if (Tools::isSubmit('forcedeleteImage')){
                Tools::redirectAdmin(self::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminCategories') . '&conf=7');
	    }
        }

        if (Tools::isSubmit('submitAddsmart_blog_post')) {
            if (Tools::getValue('id_smart_blog_post')){
                $this->processUpdate();
            }
            else{
                $this->processAdd();
            }
        }else {
            parent::postProcess(true);
        }
    }

    public function processForceDeleteImage() {
        $blog_post = $this->loadObject(true);

        if (Validate::isLoadedObject($blog_post)) {

            $this->deleteImage($blog_post->id_smart_blog_post);
        }
    }

    public function deleteImage($id_smart_blog_post = 1) {

        if (!$id_smart_blog_post){
            return false;
	}


        // Delete base image
        if (file_exists(_MODULE_SMARTBLOG_DIR_ . '/' . $id_smart_blog_post . '.jpg')){
            unlink($this->image_dir . '/' . $id_smart_blog_post . '.jpg');
        }else{
            return false;
	}

        // now we need to delete the image type of post

        $files_to_delete = array();

        // Delete auto-generated images
        $image_types = BlogImageType::GetImageAllType('post');
        foreach ($image_types as $image_type){
            $files_to_delete[] = $this->image_dir . '/' . $id_smart_blog_post . '-' . $image_type['type_name'] . '.jpg';
	}
        // Delete tmp images
        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_' . $id_smart_blog_post . '.jpg';
        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_mini_' . $id_smart_blog_post . '.jpg';

        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_' . $id_smart_blog_post . '_' . $this->context->language->id . '.jpg';
        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_mini_' . $id_smart_blog_post . '_' . $this->context->language->id . '.jpg';

        foreach ($files_to_delete as $file){
            if (file_exists($file) && !@unlink($file)) {
                return false;
	    }
	}
        return true;
    }
   protected function postImage($id) {
        $ret = parent::postImage($id);
        if (isset($_FILES['image']) && isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])) {
            if ($error = ImageManager::validateUpload($_FILES['image'], 4000000)){
                return $this->displayError($this->module->l('Invalid image'));
            }else {

                $path = _PS_MODULE_DIR_ . 'smartblog/images/' . $id . '.' . $this->imageType;

                $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                if (!$tmp_name){
                    return false;
		}


                if (!move_uploaded_file($_FILES['image']['tmp_name'], $tmp_name)){
                    return false;
		}

                // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
                if (!ImageManager::checkImageMemoryLimit($tmp_name)){
                    $this->errors[] = Tools::displayError('Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value via your server\'s configuration settings. ');
		}
                $width = (isset($width))? $width : 0;
                $height = (isset($height))? $height : 0;
                $ext = (isset($ext))? $ext : 0;

                // Copy new image
                if (empty($this->errors) && !ImageManager::resize($tmp_name, $path, (int) $width, (int) $height, ($ext ? $ext : $this->imageType))) {
                    $this->errors[] = Tools::displayError('An error occurred while uploading the image.');
		}
                if (count($this->errors)) {
                    return false;
		}    
                if ($this->afterImageUpload()) {
                    unlink($tmp_name);
                    //  return true;
                }

                $posts_types = BlogImageType::GetImageAllType('post');
                $langs = Language::getLanguages();
                foreach ($posts_types as $image_type) {
                        $files_to_delete = array();

                        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_' . $id . '.jpg';
                        $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_mini_' . $id . '.jpg';

                        foreach ($langs as $l)
                        {
                            $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_' . $id . '_' . $l['id_lang'] . '.jpg';
                            $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'smart_blog_post_mini_' . $id . '_' . $l['id_lang'] . '.jpg';
                        }

                        $dir = '';
                        foreach ($files_to_delete as $file) {
                            if (file_exists($file) && !@unlink($file)) {
                            	$dir = _PS_MODULE_DIR_ . 'smartblog/images/' . $id . '-' . stripslashes($image_type['type_name']) . '.jpg';
			    }
			}
	        	if (file_exists($dir)) {
	                    unlink($dir);
			}
                }
                foreach ($posts_types as $image_type) {
                    ImageManager::resize($path, _PS_MODULE_DIR_ . 'smartblog/images/' . $id . '-' . stripslashes($image_type['type_name']) . '.jpg', (int) $image_type['width'], (int) $image_type['height']
                    );
                }
            }
        }
        return $ret;
    }

    public function updateTags($languages, $post) {
        $tag_success = true;
		
        if (!SmartBlogPost::deleteTagsForBlog((int) $post->id)){
			$this->errors[] = Tools::displayError('An error occurred while attempting to delete previous tags.');
		}
            
        foreach ($languages as $language){
            if ($value = pSQL(Tools::getValue('tags_' . $language['id_lang']))){
				$tag_success &= SmartBlogPost::addTags($language['id_lang'], (int) $post->id, $value);
			}
		}

        if (!$tag_success){
			$this->errors[] = Tools::displayError('An error occurred while adding tags.');
		}
            
        return $tag_success;
    }
}
