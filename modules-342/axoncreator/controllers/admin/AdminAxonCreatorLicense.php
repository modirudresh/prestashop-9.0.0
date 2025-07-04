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

class AdminAxonCreatorLicenseController extends ModuleAdminController
{
    public $name;

    public function __construct()
    {		
        $this->bootstrap = true;
		
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDashboard'));
        }
		
        $this->name = 'AdminAxonCreatorLicense';
    }
	
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->module->l('Axon - License');
    }
	
    public function renderList()
    {
		ob_start();
		$this->render_manually_activation_widget();
		$html = ob_get_clean();
		
        return parent::renderList() . $html;
    }
	
    private function render_manually_activation_widget() {
		$license_key = Wp_Helper::api_get_license_key();
		
		?>
		<form class="form-horizontal" method="post" action="<?php echo Wp_Helper::get_exit_to_dashboard( $this->name ); ?>">
			<div id="configuration_fieldset_general" class="panel ">
				<div class="panel-heading"><i class="icon-cogs"></i> <?php Wp_Helper::_e( 'License', 'elementor' ); ?></div>
				<div class="form-wrapper">	
					<?php if ( empty( $license_key ) ) : ?>
						<div class="form-group">
							<label class="control-label col-lg-3 required"><?php Wp_Helper::_e( 'Your License Key', 'elementor' ); ?></label>
							<div class="col-lg-9">
								<input class="regular-text code" name="axon_creator_license_key" type="text" value="" placeholder="<?php Wp_Helper::esc_attr_e( 'Please enter your license key here', 'elementor' ); ?>" style="max-width: 500px;display: inline-block;vertical-align: middle;"/>
								<input type="submit" class="btn btn-primary" name="submitAxonActivateLicense" value="<?php Wp_Helper::esc_attr_e( 'Activate', 'elementor' ); ?>" style="display: inline-block;vertical-align: middle;"/>
							</div>
						</div>
					<?php else :
						$license_data = Wp_Helper::api_get_license_data( true ); ?>
						
						<div class="form-group">
							<label class="control-label col-lg-3 required"><?php Wp_Helper::_e( 'Your License Key', 'elementor' ); ?></label>
							<div class="col-lg-9">
								<input type="text" value="<?php echo Wp_Helper::esc_attr( Wp_Helper::api_get_hidden_license_key() ); ?>" style="max-width: 500px;display: inline-block;vertical-align: middle;" disabled/>
								<input type="submit" class="btn btn-primary" name="submitAxonDeactivateLicense" value="<?php Wp_Helper::esc_attr_e( 'Deactivate', 'elementor' ); ?>" style="display: inline-block;vertical-align: middle;"/>
							</div>
							<div class="col-lg-9 col-lg-offset-3">
								<br/>
								<?php Wp_Helper::_e( 'Status', 'elementor' ); ?>:
								<?php if ( Wp_Helper::STATUS_EXPIRED === $license_data['license'] ) : ?>
									<span style="color: #ff0000; font-style: italic;"><?php Wp_Helper::_e( 'Expired', 'elementor' ); ?></span>
								<?php elseif ( Wp_Helper::STATUS_SITE_INACTIVE === $license_data['license'] ) : ?>
									<span style="color: #ff0000; font-style: italic;"><?php Wp_Helper::_e( 'Mismatch', 'elementor' ); ?></span>
								<?php elseif ( Wp_Helper::STATUS_INVALID === $license_data['license'] ) : ?>
									<span style="color: #ff0000; font-style: italic;"><?php Wp_Helper::_e( 'Invalid', 'elementor' ); ?></span>
								<?php elseif ( Wp_Helper::STATUS_DISABLED === $license_data['license'] ) : ?>
									<span style="color: #ff0000; font-style: italic;"><?php Wp_Helper::_e( 'Disabled', 'elementor' ); ?></span>
								<?php else : ?>
									<span style="color: #008000; font-style: italic;"><?php Wp_Helper::_e( 'Active', 'elementor' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="col-lg-9 col-lg-offset-3">
								<?php if ( Wp_Helper::STATUS_EXPIRED === $license_data['license'] ) : ?>
									<br/>
									<p class="alert alert-danger"><?php echo Wp_Helper::__( 'Your License Has Expired. Renew your license today to keep getting feature updates, premium support and unlimited access to the template library.', 'elementor' ); ?></p>
								<?php endif; ?>
								<?php if ( Wp_Helper::STATUS_SITE_INACTIVE === $license_data['license'] ) : ?>
									<br/>
									<p class="alert alert-danger"><?php echo Wp_Helper::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ); ?></p>
								<?php endif; ?>
								<?php if ( Wp_Helper::STATUS_INVALID === $license_data['license'] ) : ?>
									<br/>
									<p class="alert alert-danger"><?php echo Wp_Helper::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ); ?></p>
								<?php endif; ?>
								<?php if ( Wp_Helper::STATUS_DISABLED === $license_data['license'] ) : ?>
									<br/>
									<p class="alert alert-danger"><?php echo Wp_Helper::__( 'Your license key has been cancelled (most likely due to a refund request). Please consider acquiring a new license.', 'elementor' ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<div class="col-lg-9 col-lg-offset-3">
							<hr/><a href="https://api.axonviz.com/manage-licenses" class="btn btn-primary" target="_blank"><?php Wp_Helper::_e( 'Manage Licenses', 'elementor' ); ?></a>
						</div>
					</div>
				</div>
			</div>	
            <div id="configuration_fieldset_general" class="panel ">
				<div class="panel-heading"><i class="icon-cogs"></i> <?php Wp_Helper::_e( 'Migration Site Address', 'elementor' ); ?></div>
				<div class="form-wrapper">	
                    <div class="form-group">
						<label class="control-label col-lg-3 required"><?php Wp_Helper::_e( 'Your License Key', 'elementor' ); ?></label>
						<div class="col-lg-9">
							<input class="regular-text code" name="axon_creator_license_key_migration" type="text" value="" placeholder="<?php Wp_Helper::esc_attr_e( 'Please enter your license key here', 'elementor' ); ?>" style="max-width: 500px;display: inline-block;vertical-align: middle;"/>
							<input type="submit" class="btn btn-primary" name="submitAxonMigrationAddress" value="<?php Wp_Helper::esc_attr_e( 'Migration', 'elementor' ); ?>" style="display: inline-block;vertical-align: middle;"/>
						</div>
					</div>
                    <div class="form-group">
						<label class="control-label col-lg-3 required"><?php Wp_Helper::_e( 'Old URL ( including HTTPS/SSL migration )', 'elementor' ); ?></label>
						<div class="col-lg-9">
							<input class="regular-text code" name="axon_creator_old_url" type="text" value="" placeholder="<?php Wp_Helper::esc_attr_e( 'Please enter your old url', 'elementor' ); ?>" style="max-width: 500px;display: inline-block;vertical-align: middle;"/>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php
	}
		
    public function postProcess()
    {
		if (Tools::isSubmit('submitAxonActivateLicense')) {
			if( !Tools::getValue( 'axon_creator_license_key' ) ){
				return $this->errors[] = Wp_Helper::__( 'The license key is required. ', 'elementor' );;
			}
			
			$license_key = trim( Tools::getValue( 'axon_creator_license_key' ) );
			
			$data = Wp_Helper::api_activate_license( $license_key );

			if ( !is_array( $data ) ) {
				return $this->errors[] = $data;
			}

			if ( Wp_Helper::STATUS_VALID !== $data['license'] ) {
				$error_msg = Wp_Helper::api_get_error_message( $data['error'] );
				return $this->errors[] = $error_msg;
			}

			Wp_Helper::api_set_license_key( $license_key );
			Wp_Helper::api_set_license_data( $data );
		}
		if (Tools::isSubmit('submitAxonDeactivateLicense')) {
			Wp_Helper::api_deactivate();
		}
		if (Tools::isSubmit('submitAxonMigrationAddress')) {
			if( !Tools::getValue( 'axon_creator_license_key_migration' ) ){
				$this->errors[] = Wp_Helper::__( 'The license key is required. ', 'elementor' );;
			}

			if( !Tools::getValue( 'axon_creator_old_url' ) ){
				$this->errors[] = Wp_Helper::__( 'The old url is required. ', 'elementor' );;
			}

            if($this->errors){
                return $this->errors;
            }
			
			$license_key = trim( Tools::getValue( 'axon_creator_license_key_migration' ) );
            $url_old = trim( Tools::getValue( 'axon_creator_old_url' ) );

			Wp_Helper::api_deactivate($license_key, $url_old);
			
			$data = Wp_Helper::api_activate_license( $license_key );

			if ( !is_array( $data ) ) {
				return $this->errors[] = $data;
			}

			if ( Wp_Helper::STATUS_VALID !== $data['license'] ) {
				$error_msg = Wp_Helper::api_get_error_message( $data['error'] );
				return $this->errors[] = $error_msg;
			}

			Wp_Helper::api_set_license_key( $license_key );
			Wp_Helper::api_set_license_data( $data );
		}
    }			
}
