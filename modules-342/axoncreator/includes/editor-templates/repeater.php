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
<script type="text/template" id="tmpl-elementor-repeater-row">
	<div class="elementor-repeater-row-tools">
		<# if ( itemActions.drag_n_drop ) {  #>
			<div class="elementor-repeater-row-handle-sortable">
				<i class="eicon-ellipsis-v" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Drag & Drop', 'elementor' ); ?></span>
			</div>
		<# } #>
		<div class="elementor-repeater-row-item-title"></div>
		<# if ( itemActions.duplicate ) {  #>
			<div class="elementor-repeater-row-tool elementor-repeater-tool-duplicate">
				<i class="eicon-copy" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Duplicate', 'elementor' ); ?></span>
			</div>
		<# }
		if ( itemActions.remove ) {  #>
			<div class="elementor-repeater-row-tool elementor-repeater-tool-remove">
				<i class="eicon-close" aria-hidden="true"></i>
				<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Remove', 'elementor' ); ?></span>
			</div>
		<# } #>
	</div>
	<div class="elementor-repeater-row-controls"></div>
</script>
