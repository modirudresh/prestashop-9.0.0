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

use AxonCreator\Core\Base\Document;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

$document_types = Plugin::$instance->documents->get_document_types();

$types = [];

$selected = get_query_var( 'elementor_library_type' );

foreach ( $document_types as $document_type ) {
	if ( $document_type::get_property( 'show_in_library' ) ) {
		/**
		 * @var Document $instance
		 */
		$instance = new $document_type();

		$types[ $instance->get_name() ] = $document_type::get_title();
	}
}

/**
 * Create new template library dialog types.
 *
 * Filters the dialog types when printing new template dialog.
 *
 * @since 2.0.0
 *
 * @param array    $types          Types data.
 * @param Document $document_types Document types.
 */
$types = Wp_Helper::apply_filters( 'elementor/template-library/create_new_dialog_types', $types, $document_types );
?>
<script type="text/template" id="tmpl-elementor-new-template">
	<div id="elementor-new-template__description">
		<div id="elementor-new-template__description__title"><?php echo Wp_Helper::__( 'Templates Help You <span>Work Efficiently</span>', 'elementor' ); ?></div>
		<div id="elementor-new-template__description__content"><?php echo Wp_Helper::__( 'Use templates to create the different pieces of your site, and reuse them with one click whenever needed.', 'elementor' ); ?></div>
		<?php
		/*
		<div id="elementor-new-template__take_a_tour">
			<i class="eicon-play-o"></i>
			<a href="#"><?php echo Wp_Helper::__( 'Take The Video Tour', 'elementor' ); ?></a>
		</div>
		*/
		?>
	</div>
	<form id="elementor-new-template__form" action="<?php esc_url( Wp_Helper::admin_url( '/edit.php' ) ); ?>">
		<input type="hidden" name="post_type" value="elementor_library">
		<input type="hidden" name="action" value="elementor_new_post">
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'elementor_action_new_post' ); ?>">
		<div id="elementor-new-template__form__title"><?php echo Wp_Helper::__( 'Choose Template Type', 'elementor' ); ?></div>
		<div id="elementor-new-template__form__template-type__wrapper" class="elementor-form-field">
			<label for="elementor-new-template__form__template-type" class="elementor-form-field__label"><?php echo Wp_Helper::__( 'Select the type of template you want to work on', 'elementor' ); ?></label>
			<div class="elementor-form-field__select__wrapper">
				<select id="elementor-new-template__form__template-type" class="elementor-form-field__select" name="template_type" required>
					<option value=""><?php echo Wp_Helper::__( 'Select', 'elementor' ); ?>...</option>
					<?php
					foreach ( $types as $value => $type_title ) {
						printf( '<option value="%1$s" %2$s>%3$s</option>', $value, selected( $selected, $value, false ), $type_title );
					}
					?>
				</select>
			</div>
		</div>
		<?php
		/**
		 * Template library dialog fields.
		 *
		 * Fires after Elementor template library dialog fields are displayed.
		 *
		 * @since 2.0.0
		 */
		Wp_Helper::do_action( 'elementor/template-library/create_new_dialog_fields' );
		?>

		<div id="elementor-new-template__form__post-title__wrapper" class="elementor-form-field">
			<label for="elementor-new-template__form__post-title" class="elementor-form-field__label">
				<?php echo Wp_Helper::__( 'Name your template', 'elementor' ); ?>
			</label>
			<div class="elementor-form-field__text__wrapper">
				<input type="text" placeholder="<?php echo Wp_Helper::esc_attr__( 'Enter template name (optional)', 'elementor' ); ?>" id="elementor-new-template__form__post-title" class="elementor-form-field__text" name="post_data[post_title]">
			</div>
		</div>
		<button id="elementor-new-template__form__submit" class="elementor-button elementor-button-success"><?php echo Wp_Helper::__( 'Create Template', 'elementor' ); ?></button>
	</form>
</script>
