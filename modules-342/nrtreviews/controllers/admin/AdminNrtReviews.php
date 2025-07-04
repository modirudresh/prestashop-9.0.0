<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminNrtReviewsController extends ModuleAdminController
{
    public $name;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'NrtReviewProduct';
        $this->table = 'nrt_review_product';
        $this->identifier = 'id_nrt_review_product';

        $this->_defaultOrderBy = 'date_add';
        $this->_defaultOrderWay = 'DESC';
        $this->list_no_link = true;

        $this->addRowAction('delete');

        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
		
        $this->bulk_actions = array(
            'divider' => array(
                'text' => 'divider'
            ),
            'delete' => array(
                'text' => $this->module->l('Delete selected'),
                'confirm' => $this->module->l('Delete selected items?'),
                'icon' => 'icon-trash'
            ),
        );

        $this->fields_list = array(
            'id_nrt_review_product' => array(
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'id_product' => array(
                'title' => $this->module->l('Product'),
                'align' => 'center',
                'callback' => 'formatProduct',
                'class' => 'fixed-width-xs'
            ),
            'title' => array(
                'title' => $this->module->l('Title'),
            ),
            'rating' => array(
                'title' => $this->module->l('Rating'),
                'class' => 'fixed-width-xs',
                'callback' => 'formatRating',
                'align' => 'center',
            ),
            'comment' => array(
                'title' => $this->module->l('Comment'),
                'callback' => 'getCommentClean',
                'orderby' => false
            ),
            'image' => array(
                'title' => $this->module->l('Images'),
                'callback' => 'formatImage',
                'orderby' => false
            ),
            'customer_name' => array(
                'title' => $this->module->l('Author'),
            ),
            'fulness' => array(
                'title' => $this->module->l('Helpful'),
            ),
            'no_fulness' => array(
                'title' => $this->module->l('No Helpful'),
            ),
            'date_add' => array(
                'title' => $this->module->l('Date'),
                'type' => 'date'
            ),
            'active' => array(
                'title' => $this->module->l('Published'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => true,
                'filter' => true,
            )
        );
		
        $this->fields_options = array(
            'general' => array(
                'title' => $this->module->l('General'),
                'icon' => 'icon-cogs',
                'fields' => array(
                    'nrt_reviews_auto_publish' => array(
                        'title' => $this->module->l('Auto publish comments'),
						'hint' => $this->module->l('If disabled you will have to approve comments manually '),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'nrt_reviews_allow_guests' => array(
                        'title' => $this->module->l('Allow guest reviews'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
					'nrt_reviews_use_fulness' => array(
                        'title' => $this->module->l('Enable upvotes / downvotes on reviews'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'nrt_reviews_minimal_time' => array(
                        'title' => $this->module->l('Minimum time between 2 reviews from the same user'),
                        'cast' => 'intval',
                        'type' => 'text',
                        'size' => '2',
						'class' => 'fixed-width-xs',
                        'suffix' => 'seconds',
					),
					'nrt_reviews_allow_upload_img' => array(
                        'title' => $this->module->l('Enable upload image'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool'
                    ),
                    'nrt_reviews_upload_max_img' => array(
                        'title' => $this->module->l('Max image upload'),
                        'cast' => 'intval',
                        'type' => 'text',
                        'size' => '2',
						'class' => 'fixed-width-xs',
                        'suffix' => 'image',
					),
                    'nrt_reviews_comments_per_page' => array(
                        'title' => $this->module->l('Number of comments per page'),
                        'cast' => 'intval',
                        'type' => 'text',
                        'size' => '2',
						'class' => 'fixed-width-xs',
                        'suffix' => 'comments',
					),
                ),
                'submit' => array('title' => $this->module->l('Save'))
            ),
        );
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('Product reviews');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public static function getCommentClean($comment)
    {
        return strip_tags(stripslashes($comment));
    }

    public static function formatRating($rating)
    {
        return $rating . '/5';
    }

    public static function formatProduct($idProduct)
    {
        $product = new Product((int)$idProduct, false, (int)Context::getContext()->language->id);
        return $product->name . ' (id: ' . $idProduct . ')';
    }
	
    public static function formatImage($images)
    {
		$images = json_decode($images, true);
		$imgGroup = '';
		
		foreach ($images as $img) {
			$imgGroup .= '<img style="width:50px;height:auto;margin-right:5px;" src="'.Context::getContext()->link->getMediaLink(_MODULE_DIR_.'nrtreviews/images/'.$img).'"/>';
		}
		
        return $imgGroup;
    }
	
    public function postProcess()
    {
        if (Tools::isSubmit('submitBulkenableSelection'.$this->table)) {
            $this->processBulkSelection(1);
        } elseif (Tools::isSubmit('submitBulkdisableSelection'.$this->table)) {
            $this->processBulkSelection(0);
        } elseif (Tools::isSubmit('delete'.$this->table)) {
			if(Tools::getValue('id_nrt_review_product')){
				$this->module->deleteImages(Tools::getValue('id_nrt_review_product'));
			}
        }

        parent::postProcess();
    }

    public function processBulkSelection($active)
    {
        $result = true;
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id) {
                $obj = new NrtReviewProduct((int)$id);
                $obj->active = (int)$active;
                $result &= $obj->save();
            }
        }
        return $result;
    }
	
}
