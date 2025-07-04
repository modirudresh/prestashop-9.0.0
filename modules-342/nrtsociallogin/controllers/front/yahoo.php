<?php
/*
* 2017 AxonVIZ
*
* NOTICE OF LICENSE
*
*  @author AxonVIZ <axonviz.com@gmail.com>
*  @copyright  2017 axonviz.com
*   
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_   . 'nrtsociallogin/libraries/hybridauth/autoload.php';

use Hybridauth\Hybridauth;

class NrtSocialLoginYahooModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
		parent::initContent();
		
		$desktop = 0;
		
		if(isset($_COOKIE['cookieSw']) && (int)$_COOKIE['cookieSw'] > 1199) {
			$desktop = 1;
		}

		if(isset($_COOKIE['cookieSr'])) {
			$back = $_COOKIE['cookieSr'];
		} else {
			$back = $this->context->link->getPageLink( 'index', null, $this->context->language->id, null, false, $this->context->shop->id, false );
		}
		
		$settings = unserialize(Configuration::get('NRT_SOCIAL_LOGIN_CONFIG'));

		if (!Tools::strlen($settings['yahoo']['consumer_key']) || !Tools::strlen($settings['yahoo']['consumer_secret'])) {
			Tools::redirect($this->context->link->getModuleLink('nrtsociallogin', 'credentials'));
		}
		
		try {
			$hybridauth = new Hybridauth(
				[
					'callback' => $this->module->_getUrlCallback('yahoo'),
					'providers' => [
						'Yahoo' => [
							'enabled' => true,
							'keys' => [ 'id' => $settings['yahoo']['consumer_key'], 'secret' => $settings['yahoo']['consumer_secret']]
						]
					],
				]
			);

			$authenticate = $hybridauth->authenticate( 'Yahoo' );
			
			$user_data = $authenticate->getUserProfile();

			$authenticate->disconnect();

			if (count((array)$user_data) > 0) {
				$social_data = [];

				$social_data['first_name'] = $user_data->firstName ? $user_data->firstName : $user_data->displayName;
				$social_data['last_name'] = $user_data->lastName ? $user_data->lastName : $user_data->displayName;
				$social_data['email'] = $user_data->email;
				$social_data['gender'] = 0;

				if(isset($user_data->gender)) {
					$social_data['gender'] = ($user_data->gender == 'male') ? 1 : 2;
				}

				$social_data['username'] = $user_data->firstName ? $user_data->firstName : $user_data->displayName;

				$obj = new NrtSocialLogin();
	
				$result = $obj->addUser($social_data);

				if ($result) {
					if ($settings['show_popup'] == 1 && $desktop) {
						echo 	'<script type="text/javascript">
									window.opener.document.location.replace("'.$back.'");
									window.close();
								</script>';
					} else {
						Tools::redirect($back);
					}
				} else {
					Tools::redirect($this->context->link->getModuleLink('nrtsociallogin', 'errors'));
				}
			} else {
				Tools::redirect($this->context->link->getModuleLink('nrtsociallogin', 'errors'));
			}	
		} catch (\Exception $e) {
			//echo 'Oops, we ran into an issue! ' . $e->getMessage();
			Tools::redirect($this->context->link->getModuleLink('nrtsociallogin', 'errors'));
		}
    }		
}

