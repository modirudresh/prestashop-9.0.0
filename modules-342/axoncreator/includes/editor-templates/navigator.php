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
<script type="text/template" id="tmpl-elementor-navigator">
	<div id="elementor-navigator__header">
		<i id="elementor-navigator__toggle-all" class="eicon-expand" data-elementor-action="expand"></i>
		<div id="elementor-navigator__header__title"><?php echo Wp_Helper::__( 'Navigator', 'elementor' ); ?></div>
		<i id="elementor-navigator__close" class="eicon-close"></i>
	</div>
	<div id="elementor-navigator__elements"></div>
	<div id="elementor-navigator__footer">
		<i class="eicon-ellipsis-h"></i>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-navigator__elements">
	<# if ( obj.elType ) { #>
		<div class="elementor-navigator__item">
			<div class="elementor-navigator__element__list-toggle">
				<i class="eicon-sort-down"></i>
			</div>
			<#
			if ( icon ) { #>
				<div class="elementor-navigator__element__element-type">
					<i class="{{{ icon }}}"></i>
				</div>
			<# } #>
			<div class="elementor-navigator__element__title">
				<span class="elementor-navigator__element__title__text">{{{ title }}}</span>
			</div>
			<div class="elementor-navigator__element__toggle">
				<i class="eicon-preview-medium"></i>
			</div>
			<div class="elementor-navigator__element__indicators"></div>
		</div>
	<# } #>
	<div class="elementor-navigator__elements"></div>
</script>

<script type="text/template" id="tmpl-elementor-navigator__elements--empty">
	<div class="elementor-empty-view__title"><?php echo Wp_Helper::__( 'Empty', 'elementor' ); ?></div>
</script>

<script type="text/template" id="tmpl-elementor-navigator__root--empty">
	<i class="elementor-nerd-box-icon eicon-nerd" aria-hidden="true"></i>
	<div class="elementor-nerd-box-title"><?php echo Wp_Helper::__( 'Easy Navigation is Here!', 'elementor' ); ?></div>
	<div class="elementor-nerd-box-message"><?php echo Wp_Helper::__( 'Once you fill your page with content, this window will give you an overview display of all the page elements. This way, you can easily move around any section, column, or widget.', 'elementor' ); ?></div>
</script>
