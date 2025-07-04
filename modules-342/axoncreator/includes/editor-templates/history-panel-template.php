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
<script type="text/template" id="tmpl-elementor-panel-history-page">
	<div id="elementor-panel-elements-navigation" class="elementor-panel-navigation">
		<div id="elementor-panel-elements-navigation-history" class="elementor-panel-navigation-tab elementor-active" data-view="history"><?php echo Wp_Helper::__( 'Actions', 'elementor' ); ?></div>
		<div id="elementor-panel-elements-navigation-revisions" class="elementor-panel-navigation-tab" data-view="revisions"><?php echo Wp_Helper::__( 'Revisions', 'elementor' ); ?></div>
	</div>
	<div id="elementor-panel-history-content"></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-history-tab">
	<div id="elementor-history-list"></div>
	<div class="elementor-history-revisions-message"><?php echo Wp_Helper::__( 'Switch to Revisions tab for older versions', 'elementor' ); ?></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-history-no-items">
	<i class="elementor-nerd-box-icon eicon-nerd"></i>
	<div class="elementor-nerd-box-title"><?php echo Wp_Helper::__( 'No History Yet', 'elementor' ); ?></div>
	<div class="elementor-nerd-box-message"><?php echo Wp_Helper::__( 'Once you start working, you\'ll be able to redo / undo any action you make in the editor.', 'elementor' ); ?></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-history-item">
	<div class="elementor-history-item__details">
		<span class="elementor-history-item__title">{{{ title }}}</span>
		<span class="elementor-history-item__subtitle">{{{ subTitle }}}</span>
		<span class="elementor-history-item__action">{{{ action }}}</span>
	</div>
	<div class="elementor-history-item__icon">
		<span class="eicon" aria-hidden="true"></span>
	</div>
</script>
