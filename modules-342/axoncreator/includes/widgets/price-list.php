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

/**
 * Elementor button widget.
 *
 * Elementor widget that displays a button with the ability to control every
 * aspect of the button design.
 *
 * @since 1.0.0
 */
class Widget_Price_List extends Widget_Base {

	public function get_name() {
		return 'price-list';
	}

	public function get_title() {
		return Wp_Helper::__( 'Price List', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	public function get_keywords() {
		return [ 'pricing', 'list', 'product', 'image', 'menu', 'axps' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_list',
			[
				'label' => Wp_Helper::__( 'List', 'elementor' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'price',
			[
				'label' => Wp_Helper::__( 'Price', 'elementor' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'title',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'label_block' => 'true',
			]
		);

		$repeater->add_control(
			'item_description',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => '',
			]
		);

		$repeater->add_control(
			'image',
			[
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'default' => [ 'url' => '#' ],
			]
		);

		$this->add_control(
			'price_list',
			[
				'label' => Wp_Helper::__( 'List Items', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' => Wp_Helper::__( 'First item on the list', 'elementor' ),
						'item_description' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
						'price' => '$20',
						'link' => [ 'url' => '#' ],
					],
					[
						'title' => Wp_Helper::__( 'Second item on the list', 'elementor' ),
						'item_description' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
						'price' => '$9',
						'link' => [ 'url' => '#' ],
					],
					[
						'title' => Wp_Helper::__( 'Third item on the list', 'elementor' ),
						'item_description' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
						'price' => '$32',
						'link' => [ 'url' => '#' ],
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_style',
			[
				'label' => Wp_Helper::__( 'List', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading__title',
			[
				'label' => Wp_Helper::__( 'Title & Price', 'elementor' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-header' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .elementor-price-list-header',
			]
		);

		$this->add_control(
			'heading_item_description',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .elementor-price-list-description',
			]
		);

		$this->add_control(
			'heading_separator',
			[
				'label' => Wp_Helper::__( 'Separator', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'separator_style',
			[
				'label' => Wp_Helper::__( 'Style', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'solid' => Wp_Helper::__( 'Solid', 'elementor' ),
					'dotted' => Wp_Helper::__( 'Dotted', 'elementor' ),
					'dashed' => Wp_Helper::__( 'Dashed', 'elementor' ),
					'double' => Wp_Helper::__( 'Double', 'elementor' ),
					'none' => Wp_Helper::__( 'None', 'elementor' ),
				],
				'default' => 'dotted',
				'render_type' => 'template',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-separator' => 'border-bottom-style: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'separator_weight',
			[
				'label' => Wp_Helper::__( 'Weight', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 10,
					],
				],
				'condition' => [
					'separator_style!' => 'none',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-separator' => 'border-bottom-width: {{SIZE}}{{UNIT}}',
				],
				'default' => [
					'size' => 2,
				],
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-separator' => 'border-bottom-color: {{VALUE}};',
				],
				'condition' => [
					'separator_style!' => 'none',
				],
			]
		);

		$this->add_control(
			'separator_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 40,
					],
				],
				'condition' => [
					'separator_style!' => 'none',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-separator' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'body.rtl {{WRAPPER}} .elementor-price-list-image' => 'padding-left: calc({{SIZE}}{{UNIT}}/2);',
					'body.rtl {{WRAPPER}} .elementor-price-list-image + .elementor-price-list-text' => 'padding-right: calc({{SIZE}}{{UNIT}}/2);',
					'body:not(.rtl) {{WRAPPER}} .elementor-price-list-image' => 'padding-right: calc({{SIZE}}{{UNIT}}/2);',
					'body:not(.rtl) {{WRAPPER}} .elementor-price-list-image + .elementor-price-list-text' => 'padding-left: calc({{SIZE}}{{UNIT}}/2);',
				],
				'default' => [
					'size' => 20,
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_item_style',
			[
				'label' => Wp_Helper::__( 'Item', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'row_gap',
			[
				'label' => Wp_Helper::__( 'Rows Gap', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
					'em' => [
						'max' => 5,
						'step' => 0.1,
					],
				],
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'default' => [
					'size' => 20,
				],
			]
		);

		$this->add_control(
			'vertical_align',
			[
				'label' => Wp_Helper::__( 'Vertical Align', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => Wp_Helper::__( 'Top', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => Wp_Helper::__( 'Center', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => Wp_Helper::__( 'Bottom', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-list-item' => 'align-items: {{VALUE}};',
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'bottom' => 'flex-end',
				],
				'default' => 'top',
			]
		);

		$this->end_controls_section();
	}

	private function render_image( $item, $instance ) {		
		if ( '' !== $item['image']['url'] ) {
			$image_src = $item['image']['url'];
			return sprintf( '<img loading="lazy" src="%s" alt="%s" />', $image_src, Wp_Helper::esc_attr( Control_Media::get_image_alt( $item['image'] ) ) );
		}else{
			return '';
		}
	}

	private function render_item_header( $item ) {
		$url = $item['link']['url'];

		$item_id = $item['_id'];

		if ( $url ) {
			$unique_link_id = 'item-link-' . $item_id;

			$this->add_render_attribute( $unique_link_id, 'class', 'elementor-price-list-item' );

			$this->add_link_attributes( $unique_link_id, $item['link'] );

			return '<li><a ' . $this->get_render_attribute_string( $unique_link_id ) . '>';
		} else {
			return '<li class="elementor-price-list-item">';
		}
	}

	private function render_item_footer( $item ) {
		if ( $item['link']['url'] ) {
			return '</a></li>';
		} else {
			return '</li>';
		}
	}

	protected function render() {
		$settings = $this->get_settings_for_display(); ?>

		<ul class="elementor-price-list">

		<?php foreach ( $settings['price_list'] as $index => $item ) : ?>
			<?php if ( ! empty( $item['title'] ) || ! empty( $item['price'] ) || ! empty( $item['item_description'] ) ) :
				$title_repeater_setting_key = $this->get_repeater_setting_key( 'title', 'price_list', $index );
				$description_repeater_setting_key = $this->get_repeater_setting_key( 'item_description', 'price_list', $index );
				$this->add_inline_editing_attributes( $title_repeater_setting_key );
				$this->add_inline_editing_attributes( $description_repeater_setting_key );
				$this->add_render_attribute( $title_repeater_setting_key, 'class', 'elementor-price-list-title' );
				$this->add_render_attribute( $description_repeater_setting_key, 'class', 'elementor-price-list-description' );
				?>
				<?php echo $this->render_item_header( $item ); ?>
				<?php if ( ! empty( $item['image']['url'] ) ) : ?>
					<div class="elementor-price-list-image">
					<?php echo $this->render_image( $item, $settings ); ?>
				</div>
				<?php endif; ?>

				<div class="elementor-price-list-text">
				<?php if ( ! empty( $item['title'] ) || ! empty( $item['price'] ) ) : ?>
					<div class="elementor-price-list-header">
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<span <?php echo $this->get_render_attribute_string( $title_repeater_setting_key ); ?>><?php echo $item['title']; ?></span>
					<?php endif; ?>
						<?php if ( 'none' != $settings['separator_style'] ) : ?>
							<span class="elementor-price-list-separator"></span>
						<?php endif; ?>
						<?php if ( ! empty( $item['price'] ) ) : ?>
							<span class="elementor-price-list-price"><?php echo $item['price']; ?></span>
						<?php endif; ?>
				</div>
				<?php endif; ?>
					<?php if ( ! empty( $item['item_description'] ) ) : ?>
						<p <?php echo $this->get_render_attribute_string( $description_repeater_setting_key ); ?>><?php echo $item['item_description']; ?></p>
					<?php endif; ?>
			</div>
				<?php echo $this->render_item_footer( $item ); ?>
			<?php endif; ?>
		<?php endforeach; ?>

		</ul>

		<?php
	}

	/**
	 * Render Price List widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<ul class="elementor-price-list">
			<#
				for ( var i in settings.price_list ) {
					var item = settings.price_list[i],
						item_open_wrap = '<li class="elementor-price-list-item">',
						item_close_wrap = '</li>';
					if ( item.link.url ) {
						item_open_wrap = '<li><a href="' + item.link.url + '" class="elementor-price-list-item">';
						item_close_wrap = '</a></li>';
					}

					if ( ! _.isEmpty( item.title ) || ! _.isEmpty( item.price ) || ! _.isEmpty( item.description ) || ! _.isEmpty( item.image ) ) { #>

					{{{ item_open_wrap }}}
					<# if ( item.image && item.image.id ) {

						var image = {
							id: item.image.id,
							url: item.image.url,
							size: settings.image_size_size,
							dimension: settings.image_size_custom_dimension,
							model: view.getEditModel()
						};

						var image_url = elementor.imagesManager.getImageUrl( image );

						if ( image_url ) { #>
							<div class="elementor-price-list-image"><img src="{{ image_url }}" alt="{{ item.title }}"></div>
						<# } #>

					<# } #>


					<# if ( ! _.isEmpty( item.title ) || ! _.isEmpty( item.price ) || ! _.isEmpty( item.item_description ) ) { #>
						<div class="elementor-price-list-text">

							<# if ( ! _.isEmpty( item.title ) || ! _.isEmpty( item.price ) ) { #>
								<div class="elementor-price-list-header">

								<# if ( ! _.isEmpty( item.title ) ) { #>
									<span class="elementor-price-list-title">{{{ item.title }}}</span>
								<# } #>

								<# if ( 'none' != settings.separator_style ) { #>
									<span class="elementor-price-list-separator"></span>
								<# } #>

								<# if ( ! _.isEmpty( item.price ) ) { #>
									<span class="elementor-price-list-price">{{{ item.price }}}</span>
								<# } #>

								</div>
							<# } #>

							<# if ( ! _.isEmpty( item.item_description ) ) { #>
								<p class="elementor-price-list-description">{{{ item.item_description }}}</p>
							<# } #>

						</div>
					<# } #>

					{{{ item_close_wrap }}}

					<# } #>
			 <# } #>
		</ul>
		<?php
	}
}
