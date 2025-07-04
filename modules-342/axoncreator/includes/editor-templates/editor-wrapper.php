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

namespace AxonCreator;

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}
$body_classes = [
	'elementor-editor-active',
];

if ( Wp_Helper::is_rtl() ) {
	$body_classes[] = 'rtl';
}

$notice = '';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if ( \Tools::usingSecureMode() ) { ?>
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <?php } ?>
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo AXON_CREATOR_ASSETS_URL . 'images/favicon.ico'; ?>">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo AXON_CREATOR_ASSETS_URL . 'images/favicon.ico'; ?>">
	<title><?php echo Wp_Helper::__( 'AxonCreator', 'elementor' ) . ' | ' . ( Wp_Helper::$post_title ? Wp_Helper::$post_title : '' ); ?></title>
	<?php Wp_Helper::do_action( 'wp_head' ); ?>
	<script>
		 var ajaxurl = '<?php echo Wp_Helper::get_ajax_editor(); ?>';
	</script>
</head>
<body class="<?php echo implode( ' ', $body_classes ); ?>">
<div id="elementor-editor-wrapper">
	<div id="elementor-panel" class="elementor-panel"></div>
	<div id="elementor-preview">
		<div id="elementor-loading">
			<div class="elementor-loader-wrapper">
				<div class="elementor-loader">
					<div class="elementor-loader-boxes">
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
						<div class="elementor-loader-box"></div>
					</div>
				</div>
				<div class="elementor-loading-title"><?php echo Wp_Helper::__( 'Loading', 'elementor' ); ?></div>
			</div>
		</div>
		<div id="elementor-preview-responsive-wrapper" class="elementor-device-desktop elementor-device-rotate-portrait">
			<div id="elementor-preview-loading">
				<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
			</div>
			<?php if ( $notice ) { ?>
				<div id="elementor-notice-bar">
					<i class="eicon-elementor-circle"></i>
					<div id="elementor-notice-bar__message"><?php echo sprintf( $notice['message'], $notice['action_url'] ); ?></div>
					<div id="elementor-notice-bar__action"><a href="<?php echo $notice['action_url']; ?>" target="_blank"><?php echo $notice['action_title']; ?></a></div>
					<i id="elementor-notice-bar__close" class="eicon-close"></i>
				</div>
			<?php } // IFrame will be created here by the Javascript later. ?>
		</div>
	</div>
	<div id="elementor-navigator"></div>
</div>
<?php
	Wp_Helper::do_action( 'wp_footer' );
	/** This action is documented in wp-admin/admin-footer.php */
	Wp_Helper::do_action( 'admin_print_footer_scripts' );
?>
</body>
</html>
