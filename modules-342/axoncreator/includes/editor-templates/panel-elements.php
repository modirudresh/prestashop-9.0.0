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
<script type="text/template" id="tmpl-elementor-panel-elements">
	<div id="elementor-panel-elements-loading">
		<i class="eicon-loading eicon-animation-spin"></i>
	</div>
	<div id="elementor-panel-elements-navigation" class="elementor-panel-navigation">
		<div id="elementor-panel-elements-navigation-all" class="elementor-panel-navigation-tab elementor-active" data-view="categories"><?php echo Wp_Helper::__( 'Elements', 'elementor' ); ?></div>
	</div>
	<div id="elementor-panel-elements-language-area"></div>
	<div id="elementor-panel-elements-search-area"></div>
	<div id="elementor-panel-elements-wrapper"></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-categories">
	<div id="elementor-panel-categories"></div>

	<div id="elementor-panel-get-pro-elements" class="elementor-nerd-box">
		<i class="elementor-nerd-box-icon eicon-hypster" aria-hidden="true"></i>
		<div class="elementor-nerd-box-message"><?php echo Wp_Helper::__( 'Get more with Elementor Pro', 'elementor' ); ?></div>
		<a class="elementor-button elementor-button-default elementor-nerd-box-link" target="_blank" href="<?php echo Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=panel-widgets&utm_campaign=gopro&utm_medium=wp-dash' ); ?>"><?php echo Wp_Helper::__( 'Go Pro', 'elementor' ); ?></a>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-elements-category">
	<div class="elementor-panel-category-title">{{{ title }}}</div>
	<div class="elementor-panel-category-items"></div>
</script>

<script type="text/template" id="tmpl-elementor-panel-element-search">
	<label for="elementor-panel-elements-search-input" class="screen-reader-text"><?php echo Wp_Helper::__( 'Search Widget:', 'elementor' ); ?></label>
	<input type="search" id="elementor-panel-elements-search-input" placeholder="<?php Wp_Helper::esc_attr_e( 'Search Widget...', 'elementor' ); ?>" />
	<i class="eicon-search" aria-hidden="true"></i>
</script>

<script type="text/template" id="tmpl-elementor-element-library-element">
	<div class="elementor-element">
		<div class="icon">
			<i class="{{ icon }}" aria-hidden="true"></i>
		</div>
		<div class="elementor-element-title-wrapper">
			<div class="title">{{{ title }}}</div>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-global">
	<div class="elementor-nerd-box">
		<i class="elementor-nerd-box-icon eicon-hypster" aria-hidden="true"></i>
		<div class="elementor-nerd-box-title"><?php echo Wp_Helper::__( 'Meet Our Global Widget', 'elementor' ); ?></div>
		<div class="elementor-nerd-box-message"><?php echo Wp_Helper::__( 'With this feature, you can save a widget as global, then add it to multiple areas. All areas will be editable from one single place.', 'elementor' ); ?></div>
		<div class="elementor-nerd-box-message"><?php echo Wp_Helper::__( 'This feature is only available on Elementor Pro.', 'elementor' ); ?></div>
		<a class="elementor-button elementor-button-default elementor-nerd-box-link" target="_blank" href="<?php echo Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=panel-global&utm_campaign=gopro&utm_medium=wp-dash' ); ?>"><?php echo Wp_Helper::__( 'Go Pro', 'elementor' ); ?></a>
	</div>
</script>

<script type="text/template" id="tmpl-elementor-panel-element-language">
	<div class="elementor-panel-language-box">
		<span><?php Wp_Helper::_e( 'Editing:', 'elementor' ); ?></span>
		<select>
			<# _.each( elementor.config.languages, function( language ) { #>
				<option value="{{{ language.id_lang }}}" <# if (elementor.config.id_lang == language.id_lang) {#> selected <# } #> >{{{ language.name }}}</option>
				<# } ); #>
		</select>
		<div id="elementor-panel-elements-language-import" <# if( elementor.config.languages.length < 2 ){ #> style="display: none;" <# } #>>
			<a id="elementor-panel-elements-language-import-btn" href="javascript:void(0)" title="<?php Wp_Helper::_e( 'Import from  other language', 'elementor' ); ?>"><i class="eicon-copy elementor-panel-elements-language-clone"></i><i class="eicon-close elementor-panel-elements-language-close"></i></a>
			<div id="elementor-panel-elements-language-import-list">
				<?php Wp_Helper::_e( 'Import content from  other language', 'elementor' ); ?>
				<ul>
				<# _.each( elementor.config.languages, function( language ) { #>
					<# if (!(elementor.config.id_lang == language.id_lang)) {#> <li><a href="#" class="elementor-panel-elements-language-import-lng" data-language="{{{ language.id_lang }}}"  >{{{ language.name }}}</a></li><# } #>
							<# } ); #>
				</ul>
			</div>
		</div>
	</div>
</script>
