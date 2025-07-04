<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

use AxonCreator\Wp_Helper;
use AxonCreator\Plugin;

class AdminAxonCreatorSettingsController extends ModuleAdminController
{
    public $name;

    public function __construct()
    {		
        $this->bootstrap = true;
		
        parent::__construct();
		
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }

		Wp_Helper::$id_shop = (int)Tools::getValue( 'id_shop', $this->context->shop->id );
		
		$disable_color_schemes = Tools::getValue( 'elementor_disable_color_schemes', Wp_Helper::get_option( 'elementor_disable_color_schemes' ) );
		$disable_typography_schemes = Tools::getValue( 'elementor_disable_typography_schemes', Wp_Helper::get_option( 'elementor_disable_typography_schemes' ) );
		$editor_break_lines = Tools::getValue( 'elementor_editor_break_lines', Wp_Helper::get_option( 'elementor_editor_break_lines' ) );
		$css_print_method = Tools::getValue( 'elementor_css_print_method', Wp_Helper::get_option( 'elementor_css_print_method' ) );
		$max_saved_revision = (int)Tools::getValue( 'elementor_max_saved_revision', Wp_Helper::get_option( 'elementor_max_saved_revision' ) );
		
        $this->fields_options = array(
            'general' => array(
                'title' => $this->module->l('Settings'),
                'fields' => array(
                    'elementor_disable_color_schemes' => array(
                        'title' => $this->module->l('Disable Default Colors'),
                        'desc' => $this->module->l('Checking this box will disable AxonCreator\'s Default Colors, and make AxonCreator inherit the colors from your theme.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
						'defaultValue' => $disable_color_schemes ? true : false
                    ),
					'elementor_disable_typography_schemes' => array(
                        'title' => $this->module->l('Disable Default Fonts'),
                        'desc' => $this->module->l('Checking this box will disable AxonCreator\'s Default Fonts, and make AxonCreator inherit the fonts from your theme.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
						'defaultValue' => $disable_typography_schemes ? true : false
                    ),
					'elementor_editor_break_lines' => array(
                        'title' => $this->module->l('Switch Editor Loader Method'),
                        'desc' => $this->module->l('For troubleshooting server configuration conflicts.'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'type' => 'bool',
						'defaultValue' => $editor_break_lines ? true : false
                    ),
                    'elementor_css_print_method' => array(
                        'title' => $this->module->l('CSS Print Method'),
                        'desc' => $this->module->l('Use external CSS files for all generated stylesheets. Choose this setting for better performance (recommended).') . '<br/>' . 
								  $this->module->l('Use internal CSS that is embedded in the head of the page. For troubleshooting server configuration conflicts and managing development environments.') ,
                        'type' => 'select',
						'list' => [
							[ 'id' => 'external', 'name' => $this->module->l('External File') ],
							[ 'id' => 'internal', 'name' => $this->module->l('Internal Embedding') ]
						],
                        'identifier' => 'id',
						'defaultValue' => $css_print_method == 'external' ? 'external' : 'internal'
                    ),
					'elementor_max_saved_revision' => array(
                        'title' => $this->module->l('Max Saved Revision History'),
                        'desc' => $this->module->l('Automatically delete revision history when the number of records is exceeded.'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'cast' => 'intval',
						'class' => 'fixed-width-xxl',
						'defaultValue' => $max_saved_revision
                    ),
                ),
                'submit' => array('name' => 'submitAxonSettingsGeneral', 'title' => $this->module->l('Save'))
            )
        );
		
        $this->name = 'AdminAxonCreatorSettings';
		
		$license_key = Wp_Helper::api_get_license_key();
		
		if ( empty( $license_key ) ) {
			$this->errors[] = Wp_Helper::__( 'Enter your license key here, to activate AxonCreator, and get feature updates, premium support and unlimited access to the template library.', 'elementor' ) . ' <a href="' . Wp_Helper::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . Wp_Helper::__( 'Click here.', 'elementor' ) . '</a>';
		}else{
			$license_data = Wp_Helper::api_get_license_data();
			
			if( !isset( $license_data['license'] ) ){
				$license_data = Wp_Helper::api_get_license_data( true );
			}
			
			if ( Wp_Helper::STATUS_EXPIRED === $license_data['license'] ) {
				$this->errors[] = Wp_Helper::__( 'Your License Has Expired. Renew your license today to keep getting feature updates, premium support and unlimited access to the template library.', 'elementor' ) . ' <a href="' . Wp_Helper::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . Wp_Helper::__( 'Click here.', 'elementor' ) . '</a>';
			}
			
			if ( Wp_Helper::STATUS_SITE_INACTIVE === $license_data['license'] ) {
				$this->errors[] = Wp_Helper::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ) . ' <a href="' . Wp_Helper::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . Wp_Helper::__( 'Click here.', 'elementor' ) . '</a>';
			}
			
			if ( Wp_Helper::STATUS_INVALID === $license_data['license'] ) {
				$this->errors[] = Wp_Helper::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ) . ' <a href="' . Wp_Helper::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . Wp_Helper::__( 'Click here.', 'elementor' ) . '</a>';
			}
		}
    }
	
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('Axon - General');
    }
		
    public function postProcess()
    {
		if (Tools::isSubmit('submitAxonSettingsGeneral')) {
			if( Tools::getValue( 'elementor_disable_color_schemes' ) ){
				Wp_Helper::update_option( 'elementor_disable_color_schemes', 'yes' );
			}else{
				Wp_Helper::delete_option( 'elementor_disable_color_schemes' );
			}
			
			if( Tools::getValue( 'elementor_disable_typography_schemes' ) ){
				Wp_Helper::update_option( 'elementor_disable_typography_schemes', 'yes' );
			}else{
				Wp_Helper::delete_option( 'elementor_disable_typography_schemes' );
			}
			
			if( Tools::getValue( 'elementor_editor_break_lines' ) ){
				Wp_Helper::update_option( 'elementor_editor_break_lines', 'yes' );
			}else{
				Wp_Helper::delete_option( 'elementor_editor_break_lines' );
			}
						
			if( Tools::getValue( 'elementor_css_print_method' ) == 'external' ){
				Wp_Helper::update_option( 'elementor_css_print_method', 'external' );
			}else{
				Wp_Helper::delete_option( 'elementor_css_print_method' );
			}

			if( Tools::getIsset( 'elementor_max_saved_revision' ) && (int)Tools::getValue( 'elementor_max_saved_revision' ) >= 0 && Validate::isUnsignedInt(Tools::getValue( 'elementor_max_saved_revision' )) ){
				Wp_Helper::update_option( 'elementor_max_saved_revision', (int)Tools::getValue( 'elementor_max_saved_revision' ) );

				$sql = 'SELECT `id_axon_creator_post` FROM `'._DB_PREFIX_.'axon_creator_post`';

				$posts = Db::getInstance()->executeS( $sql );

				$languages = Language::getLanguages();

				foreach ( $posts as $post ) {
					foreach ( $languages as $lang ) {
						$this->delete_revisions_old($post['id_axon_creator_post'], $lang['id_lang'], (int)Tools::getValue( 'elementor_max_saved_revision' ));
					}
				}
			} else {
				$this->errors[] = Wp_Helper::__( 'Max Saved Revision History: Invalid number.', 'elementor' );
			}
						
			Plugin::instance()->files_manager->clear_cache();
		}
    }		
	
    public function delete_revisions_old($id_post, $id_lang, $limit) {	
		$res = true;
		
        $sql = 'SELECT `id_axon_creator_revisions` FROM `'._DB_PREFIX_.'axon_creator_revisions` 
				WHERE `id_post` = ' . $id_post . '
				AND `id_lang` = ' . $id_lang . '
				ORDER BY `date_add` ASC';
		
		$revisions = Db::getInstance()->executeS( $sql );

		$count = count($revisions);

		if( $count <= $limit ){
			return true;
		}
		
		foreach ( $revisions as $key => $revision ) {
			$revi = new AxonCreatorRevisions( $revision['id_axon_creator_revisions'] );
			$res &= $revi->delete();
			$count--;
			if( $count <= $limit ){
				break;
			}
		}
		
		return $res;
	}
}
