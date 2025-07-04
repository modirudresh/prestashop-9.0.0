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

if(ob_get_contents()){
	ob_end_clean();
}

class NrtReviewsActionsModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (Tools::getValue('process') == 'add') {
            $this->processAdd();
        }elseif(Tools::getValue('process') == 'fulness'){
			$this->processFulness();
		}elseif(Tools::getValue('process') == 'avg'){
			$this->processAvgs();
		}elseif(Tools::getValue('process') == 'comments'){
			$this->processComments();
		}
    }

    public function processComments()
    {
        header('Content-Type: application/json');

        $idProduct = Tools::getValue('id_product');
        $page = Tools::getValue('page');

		$template = _PS_MODULE_DIR_ . $this->module->name.'/views/templates/hook/display-list-comments.tpl';

        $this->context->smarty->assign($this->module->getComments($idProduct, $page));	

		$html = $this->context->smarty->fetch($template);
		
		$this->ajaxRender(json_encode(array(
			'html' => $html
		)));	

		exit;
	}
	
    public function processAvgs()
    {
        header('Content-Type: application/json');
				
        $listIds = Tools::getValue('listIds');
		
        $products = [];
		
		foreach ($listIds as $id) {
			$avgReviews = NrtReviewProduct::getAvgReviews((int) $id);
            $products[] = [
                'id_product' => $id,
                'avgReviews' => $avgReviews,
            ];
		}

		$this->ajaxRender(json_encode(array(
			'success' => true,
			'products' => $products,
		)));

		exit;
	}
	
    public function processFulness()
    {
        header('Content-Type: application/json');
				
		$fulness = $this->context->cookie->reviewFulness;
		
		if ($fulness) {
			$fulness = json_decode($fulness, true);
		}else{
			$fulness = array();
		}
		
        $idReview = (int)Tools::getValue('idReview');
        $value = (int)Tools::getValue('value');
		
		$obj = new NrtReviewProduct($idReview);
		if (!$obj->id_nrt_review_product || !$obj->active) {
			$this->ajaxRender(json_encode(array(
				'success' => false,
			)));

			exit;
		}
		
		if (isset($fulness[$idReview])) {			
			if((int)$fulness[$idReview] && $value){
				if($obj->fulness){
					$obj->fulness -= 1;
				}
				unset($fulness[$idReview]);
			}elseif((int)$fulness[$idReview] && !$value){
				$obj->no_fulness += 1;
				if($obj->fulness){
					$obj->fulness -= 1;
				}
				$fulness[$idReview] = 0;
			}elseif(!(int)$fulness[$idReview] && $value){
				$obj->fulness += 1;
				if($obj->no_fulness){
					$obj->no_fulness -= 1;
				}
				$fulness[$idReview] = 1;
			}elseif(!(int)$fulness[$idReview] && !$value){
				if($obj->no_fulness){
					$obj->no_fulness -= 1;
				}
				unset($fulness[$idReview]);
			}
		}else{
			if($value){
				$obj->fulness += 1;
				$fulness[$idReview] = 1;
			}else{
				$obj->no_fulness += 1;
				$fulness[$idReview] = 0;
			}
		}
		
		$obj->save();
		
		$this->context->cookie->__set('reviewFulness', json_encode($fulness));	

		$this->ajaxRender(json_encode(array(
			'success' => true,
			'fulness' => $fulness,
			'is_fulness' => $obj->fulness,
			'no_fulness' => $obj->no_fulness,
		)));

		exit;
	}
	
    public function processAdd()
    {
        header('Content-Type: application/json');
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
			$this->ajaxRender(json_encode(array(
				'is_logged' => true,
				'errors' => array($this->module->l('Your file exceeds allowed upload file dimension!', 'actions')),
			)));

			exit;
		}

        if (!$this->context->customer->isLogged() && !Configuration::get('nrt_reviews_allow_guests')) {
            $this->ajaxRender(json_encode(array(
                'is_logged' => false,
            )));

			exit;
        }

        $msg = '';
		$reload = false;
        $errors = array();
        $idGuest = 0;
        $idCustomer = (int)$this->context->customer->id;
        $idProduct = (int)Tools::getValue('id_product');
        $product = new Product($idProduct);

		$title = Tools::getValue('title');
		$comment = Tools::getValue('comment');
		$customer_name = Tools::getValue('customer_name');
		$rating = Tools::getValue('rating');
		
        if (!$idCustomer) {
            $idGuest = $this->context->customer->id_guest;
        }

        if (!Validate::isInt($idProduct)) {
            $errors[] = $this->module->l('Product id is not valid', 'actions');
        }
		
		if (!$title){
			$errors[] = $this->module->l('Title is required', 'actions');
		}elseif(!Validate::isGenericName($title)){
			$errors[] = $this->module->l('Title is not valid', 'actions');
		}
				
		if (!$idCustomer && !$customer_name){
			$errors[] = $this->module->l('Name is required', 'actions');
		}elseif(!$idCustomer && !Validate::isGenericName($customer_name)){
			$errors[] = $this->module->l('Name is not valid', 'actions');
		}
				
		if (!$comment){
			$errors[] = $this->module->l('Comment is required', 'actions');
		}elseif(!Validate::isMessage($comment)){
			$errors[] = $this->module->l('Comment is not valid', 'actions');
		}

        if (!Validate::isInt($rating)) {
            $errors[] = $this->module->l('Rating is not valid', 'actions');
        }

        if (!$product->id) {
            $errors[] = $this->module->l('Product not found', 'actions');
        }

		if (Module::isEnabled('nrtcaptcha')) {
			$id_module = (int) $this->module->id;
			$id_hook = (int) Hook::getIdByName('registerNRTCaptcha', true);
			
			if (Hook::getModulesFromHook($id_hook, $id_module)) { 
				$nrtcaptcha = Module::getInstanceByName('nrtcaptcha');
				$nrtcaptcha_config = $nrtcaptcha->getConfigurations();
				if ($nrtcaptcha->verifyCaptcha(Tools::getValue("g-recaptcha-response")) == false) {
					$errors[] = $nrtcaptcha_config['CAPTCHA_FAILED'];
				}
			}
		}
		
		if(isset($_FILES['image'])){
			$files = $_FILES['image'];

			$extensions = [ 'gif', 'jpg', 'jpeg', 'jpe', 'png', 'webp' ];

			foreach ($files['name'] as $key => $file_name) {
				if($files['error'][$key] != UPLOAD_ERR_NO_FILE){
					$type = Tools::strtolower(Tools::substr(strrchr($file_name, '.'), 1));
					if(!in_array($type, $extensions)){
						$errors[] = sprintf($this->module->l('%1$s not a (.%2$s)', 'actions'), $file_name, implode(' .', $extensions));
					}
					if($files['size'][$key] > (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE')){
						$errors[] = sprintf($this->module->l('%s is too large', 'actions'), $file_name);
					}
					if($files['error'][$key] || !@getimagesize($files['tmp_name'][$key])){
						$errors[] = sprintf($this->module->l('%s is error', 'actions'), $file_name);
					}
				}
			}

			if(count($files['name']) > (int)Configuration::get('nrt_reviews_upload_max_img')){
				$errors[] = sprintf($this->module->l('Only up to %s images can be import', 'actions'), (int)Configuration::get('nrt_reviews_upload_max_img'));
			}
		}
		
        if (!count($errors)) {
            $check = NrtReviewProduct::getByCustomer($idProduct, $idCustomer, true, $idGuest);

            if (!$check || ($check && (strtotime($check['date_add']) + (int)Configuration::get('nrt_reviews_minimal_time')) <= time())) {
				
				$images = array();
				if(isset($_FILES['image'])){
					foreach ($files['name'] as $key => $file_name) {
						if($files['error'][$key] != UPLOAD_ERR_NO_FILE){
							$type = Tools::strtolower(Tools::substr(strrchr($file_name, '.'), 1));
							$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
							$salt = sha1(microtime());
							$fileName = $salt.'_'.$file_name;
							if (!$temp_name || !move_uploaded_file($files['tmp_name'][$key], $temp_name)) {
								return false;
							} elseif (!ImageManager::resize($temp_name, $this->module->getLocalPath().'images/'.$fileName, null, null, $type)) {
								$errors[] = $this->displayError($this->module->l('An error occurred during the image upload process.', 'actions'));
							}
							if (isset($temp_name)) {
								@unlink($temp_name);
							}
							$images[] = $fileName;
						}
					}
				}
				
                $obj = new NrtReviewProduct();
                $obj->id_product = $idProduct;
                $obj->id_customer = $idCustomer;
                $obj->id_guest = $idGuest;
                $obj->title = strip_tags($title);
                $obj->rating = (int)$rating;
                $obj->comment = strip_tags($comment);
				$obj->image = json_encode($images);
                $obj->customer_name = strip_tags($customer_name);

                if (!$obj->customer_name) {
                    $obj->customer_name = pSQL($this->context->customer->firstname .' '. $this->context->customer->lastname);
                }

                if (Configuration::get('nrt_reviews_auto_publish')) {
                    $obj->active = 1;
                } else {
                    $obj->active = 0;
                }

                $obj->save();

                $result = true;
				if(!Configuration::get('nrt_reviews_auto_publish')){
					$msg = $this->module->l('Your comment has been submitted and will be available once approved by a moderator!', 'actions');
					$reload = true;
				}else{
					$msg = $this->module->l('Your comment has been added!', 'actions');
					$reload = true;
				}
               
            } else {
                $result = false;
				$errors[] = sprintf($this->module->l('Please wait %s seconds before posting another comment', 'actions'), (strtotime($check['date_add']) + (int)Configuration::get('nrt_reviews_minimal_time')) - time());
            }
        } else {
            $result = false;
        }

        $this->ajaxRender(json_encode(array(
			'is_logged' => true,
            'success' => $result,
			'errors' => $errors,
			'msg' => $msg,
			'reload' => $reload
        )));

		exit;
    }
}
