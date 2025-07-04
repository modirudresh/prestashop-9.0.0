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

?>
<script type="text/template" id="tmpl-elementor-panel-revisions">
	<div class="elementor-panel-box">
	<div class="elementor-panel-scheme-buttons">
			<div class="elementor-panel-scheme-button-wrapper elementor-panel-scheme-discard">
				<button class="elementor-button" disabled>
					<i class="eicon-close" aria-hidden="true"></i>
					<?php echo Wp_Helper::__( 'Discard', 'elementor' ); ?>
				</button>
			</div>
			<div class="elementor-panel-scheme-button-wrapper elementor-panel-scheme-save">
				<button class="elementor-button elementor-button-success" disabled>
					<?php echo Wp_Helper::__( 'Apply', 'elementor' ); ?>
				</button>
			</div>
		</div>
	</div>

	<div class="elementor-panel-box">
		<div class="elementor-panel-heading">
			<div class="elementor-panel-heading-title"><?php echo Wp_Helper::__( 'Revisions', 'elementor' ); ?></div>
		</div>
		<div id="elementor-revisions-list" class="elementor-panel-box-content"></div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-revisions-no-revisions">
	<i class="elementor-nerd-box-icon eicon-nerd" aria-hidden="true"></i>
	<div class="elementor-nerd-box-title"><?php echo Wp_Helper::__( 'No Revisions Saved Yet', 'elementor' ); ?></div>
	<div class="elementor-nerd-box-message">{{{ elementor.translate( elementor.config.revisions_enabled ? 'no_revisions_1' : 'revisions_disabled_1' ) }}}</div>
	<div class="elementor-nerd-box-message">{{{ elementor.translate( elementor.config.revisions_enabled ? 'no_revisions_2' : 'revisions_disabled_2' ) }}}</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-revisions-loading">
	<i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
</script>

<script type="text/template" id="tmpl-elementor-panel-revisions-revision-item">
	<div class="elementor-revision-item__wrapper {{ type }}">
		<div class="elementor-revision-item__gravatar">{{{ gravatar }}}</div>
		<div class="elementor-revision-item__details">
			<div class="elementor-revision-date">{{{ date }}}</div>
			<div class="elementor-revision-meta"><span>{{{ elementor.translate( type ) }}}</span> <?php echo Wp_Helper::__( 'By', 'elementor' ); ?> {{{ author }}}</div>
		</div>
		<div class="elementor-revision-item__tools">
			<# if ( 'current' === type ) { #>
				<i class="elementor-revision-item__tools-current eicon-star" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Current', 'elementor' ); ?></span>
			<# } else { #>
				<i class="elementor-revision-item__tools-delete eicon-close" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Delete', 'elementor' ); ?></span>
			<# } #>

			<i class="elementor-revision-item__tools-spinner eicon-loading eicon-animation-spin" aria-hidden="true"></i>
		</div>
	</div>
</script>
