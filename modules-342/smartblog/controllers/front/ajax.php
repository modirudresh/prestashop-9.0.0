<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SmartBlogAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        parent::initContent();

		$array_error = array();
		$array_success = array();
		
		$id_lang = (int) Context::getContext()->language->id;
		$id_post = pSQL(Tools::getValue('id_post'));
		$post = SmartBlogPost::getPost($id_post, $id_lang);
		$context = Context::getContext();
		
		if ($post['comment_status']) {
			$id_parent_post = (int) pSQL(Tools::getValue('id_parent_post'));
			$name = pSQL(Tools::getValue('name'));
			$comment = pSQL(Tools::getValue('comment'));
			$mail = pSQL(Tools::getValue('mail'));
			
			if (Tools::getValue('website') == '') {
				$website = '#';
			} else {
				$website = pSQL(Tools::getValue('website'));
			}

			if (!$name){
				$array_error['name'] = $this->module->l('Name is required', 'ajax');
			}elseif(!Validate::isGenericName($name)){
				$array_error['name'] = $this->module->l('Name is not valid', 'ajax');
			}

			if (!$mail){
				$array_error['mail'] = $this->module->l('Email is required', 'ajax');
			}elseif(!Validate::isEmail($mail)){
				$array_error['mail'] = $this->module->l('Email is not valid', 'ajax');
			}
			
			if(!$comment || strlen($comment) > 1500 || strlen($comment) < 25){
				$array_error['comment'] = $this->module->l('Comment must be between 25 and 1500 characters!', 'ajax');
			}

			if (Module::isEnabled('nrtcaptcha')) {
				$id_module = (int) $this->module->id;
				$id_hook = (int) Hook::getIdByName('registerNRTCaptcha', true);
				
				if (Hook::getModulesFromHook($id_hook, $id_module)) { 
					$nrtcaptcha = Module::getInstanceByName('nrtcaptcha');
					$nrtcaptcha_config = $nrtcaptcha->getConfigurations();
					if ($nrtcaptcha->verifyCaptcha(Tools::getValue("g-recaptcha-response")) == false) {
						$array_error['recaptcha'] = $nrtcaptcha_config['CAPTCHA_FAILED'];
					}
				}
			}

			if (is_array($array_error) && count($array_error)) {
				if(ob_get_contents()){
					ob_end_clean();
				}
				header('Content-Type: application/json');
				
				die(json_encode(array('error' => $array_error)));
			} else {
				$comments = array();
				$comments['name'] = $name;
				$comments['mail'] = $mail;
				$comments['comment'] = $comment;
				$comments['website'] = $website;
				if (!$id_parent_post = Tools::getvalue('comment_parent')) {
					$id_parent_post = 0;
				}
				$value = Configuration::get('smartacceptcomment');
				
				$bc = new BlogComment();
				$bc->id_post = (int) $id_post;
				$bc->name = $name;
				$bc->email = $mail;
				$bc->content = $comment;
				$bc->website = $website;
				$bc->id_parent = (int) $id_parent_post;
				$bc->active = (int) $value;
				$bc->created = Date('y-m-d H:i:s');
				if ($bc->add()) {
					if($value){
						$array_success['success'] = $this->module->l('Your comment has been added!', 'ajax');
					}else{
						$array_success['success'] = $this->module->l('Your comment has been submitted and will be available once approved by a moderator!', 'ajax');
					}
					Hook::exec('actionsbpostcomment', array('bc' => $bc));
					if(ob_get_contents()){
						ob_end_clean();
					}
					header('Content-Type: application/json');
					die(json_encode($array_success));
				}
			}
		}		
    }
}
