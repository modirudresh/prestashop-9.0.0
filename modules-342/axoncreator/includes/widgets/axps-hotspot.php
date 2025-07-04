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
 * Elementor menu anchor widget.
 *
 * Elementor widget that allows to link and menu to a specific position on the
 * page.
 *
 * @since 1.0.0
 */
class Widget_Axps_Hotspot extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'axps-hotspot';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return Wp_Helper::__( 'Image Hotspot ( Text/Product )', 'elementor' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-image-hotspot';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'axon-elements' ];
	}
	
	public function get_keywords() {
		return [ 'axps', 'hotspot' ];
	}
		
	/**
	 * Register Site Logo controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->register_content_controls();
		$this->register_styling_controls();
	}

	/**
	 * Register Site Logo General Controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'general_content_section',
			[
				'label' => Wp_Helper::__( 'General', 'elementor' ),
			]
		);

		$this->add_control(
			'image',
			[
				'label'   => Wp_Helper::__( 'Choose image', 'elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Items settings.
		 */
		$this->start_controls_section(
			'items_content_section',
			[
				'label' => Wp_Helper::__( 'Items', 'elementor' ),
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'hotspot_tabs' );

		$repeater->start_controls_tab(
			'content_tab',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
			]
		);

		$repeater->add_control(
			'hotspot_type',
			[
				'label'   => Wp_Helper::__( 'Type', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'text'    => Wp_Helper::__( 'Text', 'elementor' ),
					'product' => Wp_Helper::__( 'Product', 'elementor' ),
				],
				'default' => 'text',
			]
		);

		$repeater->add_control(
			'hotspot_dropdown_side',
			[
				'label'       => Wp_Helper::__( 'Dropdown side', 'elementor' ),
				'description' => Wp_Helper::__( 'Show the content on left or right side, top or bottom.', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'left'   => Wp_Helper::__( 'Left', 'elementor' ),
					'right'  => Wp_Helper::__( 'Right', 'elementor' ),
					'top'    => Wp_Helper::__( 'Top', 'elementor' ),
					'bottom' => Wp_Helper::__( 'Bottom', 'elementor' ),
				],
				'default'     => 'left',
			]
		);

		/**
		 * Text settings
		 */
		$repeater->add_control(
			'title',
			[
				'label'     => Wp_Helper::__( 'Title', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Title hotspot',
				'condition' => [
					'hotspot_type' => [ 'text' ],
				],
			]
		);

		$repeater->add_control(
			'image',
			[
				'label'     => Wp_Helper::__( 'Choose image', 'elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'hotspot_type' => [ 'text' ],
				],
			]
		);

		$repeater->add_control(
			'link_text',
			[
				'label'     => Wp_Helper::__( 'Link text', 'elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Button',
				'condition' => [
					'hotspot_type' => [ 'text' ],
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label'     => Wp_Helper::__( 'Link', 'elementor' ),
				'type'      => Controls_Manager::URL,
				'autocomplete' => false,
				'default'   => [
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				],
				'condition' => [
					'hotspot_type' => [ 'text' ],
				],
			]
		);

		$repeater->add_control(
			'content',
			[
				'label'     => Wp_Helper::__( 'Content', 'elementor' ),
				'type'      => Controls_Manager::TEXTAREA,
				'condition' => [
					'hotspot_type' => [ 'text' ],
				],
			]
		);

		/**
		 * Product settings
		 */
		$repeater->add_control(
			'product_id',
			[
				'label'       => Wp_Helper::__( 'Select product', 'elementor' ),
				'type'        => Controls_Manager::AUTOCOMPLETE,
				'search'      => 'axps_get_products_by_query',
				'render'      => 'axps_get_products_title_by_id',
				'multiple'    => false,
				'label_block' => true,
				'condition'   => [
					'hotspot_type' => [ 'product' ],
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'position_tab',
			[
				'label' => Wp_Helper::__( 'Position', 'elementor' ),
			]
		);

		$repeater->add_responsive_control(
			'hotspot_position_horizontal',
			[
				'label'     => Wp_Helper::__( 'Horizontal position (%)', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 50,
				],
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.image-hotspot' => 'left: {{SIZE}}%;',
				],
			]
		);

		$repeater->add_responsive_control(
			'hotspot_position_vertical',
			[
				'label'     => Wp_Helper::__( 'Vertical position (%)', 'elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 50,
				],
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.image-hotspot' => 'top: {{SIZE}}%;',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		/**
		 * Repeater settings
		 */
		$this->add_control(
			'items',
			[
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
					],
				],
			]
		);

		$this->end_controls_section();
	}
	/**
	 * Register Site Image Style Controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function register_styling_controls() {
		
		$this->start_controls_section(
			'general_style_section',
			[
				'label' => Wp_Helper::__( 'General', 'elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon',
			[
				'label'   => Wp_Helper::__( 'Hotspot icon', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'circle' => Wp_Helper::__( 'Circle', 'elementor' ),
					'plus'     => Wp_Helper::__( 'Plus', 'elementor' ),
				],
				'default' => 'circle',
			]
		);

		$this->add_control(
			'action',
			[
				'label'       => Wp_Helper::__( 'Hotspot action', 'elementor' ),
				'description' => Wp_Helper::__( 'Open hotspot content on click or hover', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'hover' => Wp_Helper::__( 'Hover', 'elementor' ),
					'click' => Wp_Helper::__( 'Click', 'elementor' ),
				],
				'default'     => 'hover',
			]
		);
		
		$this->add_control(
			'hotspot_color',
			[
				'label' => Wp_Helper::__( 'Hotspot Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1367EF',
				'selectors' => [
					'{{WRAPPER}} .hotspot-icon-plus .image-hotspot.hotspot-opened .hotspot-btn, {{WRAPPER}} .hotspot-icon-plus .image-hotspot:hover .hotspot-btn, {{WRAPPER}} .hotspot-icon-plus .hotspot-btn::before, {{WRAPPER}} .hotspot-icon-plus .hotspot-btn::after, {{WRAPPER}} .hotspot-icon-circle .hotspot-btn' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .hotspot-content' => 'background: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'title_color',
			[
				'label' => Wp_Helper::__( 'Title Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .hotspot-content-title, {{WRAPPER}} .hotspot-content-title a' => 'color: {{VALUE}} !important;',
				],
			]
		);
		
		$this->add_control(
			'text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .hotspot-content-text' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'price_color',
			[
				'label' => Wp_Helper::__( 'Price Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#FF4141',
				'selectors' => [
					'{{WRAPPER}} .price' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'button_color',
			[
				'label' => Wp_Helper::__( 'Button Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#1367EF',
				'selectors' => [
					'{{WRAPPER}} .btn-action' => 'background-color: {{VALUE}};',
				],
			]
		);
		
		$this->end_controls_section();
		
	}

	/**
	 * Render Site Image output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function render() {
		
		if ( Wp_Helper::is_admin() ) {
			return;
		}
		
		$default_settings = [
			'image'                 => '',
			'action'                => 'hover',
			'icon'                  => 'circle',
			'items'                 => [],
		];

		$settings     = Wp_Helper::wp_parse_args( $this->get_settings_for_display(), $default_settings );
		$image_output = '';

		$this->add_render_attribute(
			[
				'wrapper' => [
					'class' => [
						'image-hotspot-wrapper',
						'hotspot-' . $settings['action'],
						'hotspot-icon-' . $settings['icon'],
					],
				],
			]
		);

		if ( isset( $settings['image']['url'] ) ) {
			$image_output = '<img loading="lazy" class="image-hotspot-img" src="' .  Wp_Helper::esc_url( $settings['image']['url'] ) . '">';
		}
		
		$context = \Context::getContext();
		
		$module = \Module::getInstanceByName('axoncreator');

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="image-hotspot-hotspots">
				<?php if ( $image_output ) : ?>
					<?php echo $image_output; // phpcs:ignore ?>
				<?php endif; ?>
				
				<?php foreach ( $settings['items'] as $index => $item ) : ?>
					<?php
					$default_settings = [
						'hotspot'               => '',
						'hotspot_type'          => 'product',
						'hotspot_dropdown_side' => 'left',
						'product_id'            => '',
						'title'                 => '',
						'link_text'             => '',
						'link'                  => '',
						'image'                 => '',
					];

					$settings   = Wp_Helper::wp_parse_args( $item, $default_settings );
					$attributes = '';
					$args       = [];

					if ( 'product' === $settings['hotspot_type'] && $settings['product_id'] ) {
						$products = [];

						$id_arr = explode('_', $settings['product_id']);

						if(isset($id_arr[1])){
							$id_p = $id_arr[1];
						}else{
							$id_p = $settings['product_id'];
						}

						$id_product = (int)$id_p;
						$product = $module->aProduct( $id_product );
						
						if( !$product ){
							return;
						}
					}

					if ( 'text' === $settings['hotspot_type'] && ( $settings['title'] || $settings['content'] || $settings['link_text'] || isset( $settings['image']['id'] ) ) ) {
						$attributes   = $this->get_link_attrs( $settings['link'] );
						$image_output = '';
						
						if ( isset( $settings['image']['url'] ) && $settings['image']['url'] ) {
							$image_output = Group_Control_Image_Size::get_attachment_image_html( $settings );
						}
					}

					?>
					<div class="image-hotspot hotspot-type-<?php echo Wp_Helper::esc_attr( $settings['hotspot_type'] ); ?> elementor-repeater-item-<?php echo Wp_Helper::esc_attr( $item['_id'] ); ?>">
						<span class="hotspot-anm"></span>
						<div class="hotspot-btn"></div>

						<?php if ( 'product' === $settings['hotspot_type'] && isset( $product ) && $product ) : ?>
							<div class="hotspot-product hotspot-content hotspot-dropdown-<?php echo Wp_Helper::esc_attr( $settings['hotspot_dropdown_side'] ); ?>">
								<?php if( $product->cover ){ ?>
								<div class="hotspot-content-image">
									<a href="<?php echo $product->url; ?>">
										<img loading="lazy" class="img-responsive" title="" alt="" src="<?php echo $product->cover['bySize']['home_default']['url']; ?>">
									</a>
								</div>
								<?php } ?>
								
								<h4 class="hotspot-content-title">
									<a href="<?php echo $product->url; ?>">
										<?php echo $product->name; ?>
									</a>
								</h4>
								
								<?php if( $product->show_price ){ ?>
									<div class="price"><?php echo $product->price; ?></div>
								<?php } ?>
								
								<div class="js-product-miniature" data-id-product="<?php echo $product->id; ?>" data-id-product-attribute="<?php echo $product->id_product_attribute; ?>">
									<form action="<?php echo $context->link->getPageLink( 'cart' ); ?>" method="post">
										<?php if( !(bool) \Configuration::isCatalogMode() && $product->add_to_cart_url && ( $product->quantity > 0 || $product->allow_oosp ) ) { ?>
											  <?php if( !$product->id_product_attribute ) { ?>	
												<input type="hidden" name="token" value="<?php echo \Tools::getToken(false); ?>">
												<input type="hidden" name="id_product" value="<?php echo $product->id; ?>">
												<input type="hidden" name="qty" value="<?php echo $product->minimal_quantity; ?>" min="<?php echo $product->minimal_quantity; ?>">
												<button href="javascript:void(0)" class="btn-action" data-button-action="add-to-cart" title="<?php echo $context->getTranslator()->trans('Add to cart', [], 'Shop.Theme.Actions'); ?>">
													<?php echo $context->getTranslator()->trans('Add to cart', [], 'Shop.Theme.Actions'); ?>
												</button>
											  <?php } else { ?> 
												<a 	href="javascript:void(0)" 
													class="btn-action quick-view" data-link-action="quickview" 
													title="<?php echo $context->getTranslator()->trans('Select options', [], 'Shop.Theme.Actions'); ?>">
													<?php echo $context->getTranslator()->trans('Select options', [], 'Shop.Theme.Actions'); ?>
												</a> 
											  <?php } ?>
										  <?php } else { ?>
											<a  href="<?php echo $product->url; ?>" class="btn-action" title="<?php echo $context->getTranslator()->trans('Discover', [], 'Shop.Theme.Axon'); ?>">
												<?php echo $context->getTranslator()->trans('Discover', [], 'Shop.Theme.Axon'); ?>
											</a>
										  <?php } ?>
									</form>
								</div>
							</div>
						<?php else : ?>
							<div class="hotspot-text hotspot-content hotspot-dropdown-<?php echo Wp_Helper::esc_attr( $settings['hotspot_dropdown_side'] ); ?>">
								<div class="hotspot-content-image">
									<?php echo $image_output; ?>
								</div>
								
								<h4 class="hotspot-content-title">
									<?php echo Wp_Helper::esc_html( $settings['title'] ); ?>
								</h4>
								
								<div class="hotspot-content-text">
									<?php echo Wp_Helper::esc_html( $settings['content'] ); ?>
								</div>
								
								<a class="btn-action" <?php echo $attributes; ?>>
									<span><?php echo Wp_Helper::esc_html( $settings['link_text'] ); ?></span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render Menu Cart output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.4.0
	 * @access protected
	 */
	protected function _content_template() {
	}

	/**
	 * Get image url
	 *
	 * @since 1.0.0
	 *
	 * @param array $link Link data array.
	 *
	 * @return string
	 */
	protected function get_link_attrs( $link ) {
		$link_attrs = '';

		if ( isset( $link['url'] ) && $link['url'] ) {
			$link_attrs = ' href="' . Wp_Helper::esc_url( $link['url'] ) . '"';

			if ( isset( $link['is_external'] ) && 'on' === $link['is_external'] ) {
				$link_attrs .= ' target="_blank"';
			}

			if ( isset( $link['nofollow'] ) && 'on' === $link['nofollow'] ) {
				$link_attrs .= ' rel="nofollow"';
			}
		}

		if ( isset( $link['class'] ) ) {
			$link_attrs .= ' class="' . Wp_Helper::esc_attr( $link['class'] ) . '"';
		}

		if ( isset( $link['data'] ) ) {
			$link_attrs .= $link['data'];
		}

		if ( isset( $link['custom_attributes'] ) ) {
			$custom_attributes = Utils::parse_custom_attributes( $link['custom_attributes'] );
			foreach ( $custom_attributes as $key => $value ) {
				$link_attrs .= ' ' . $key . '="' . $value . '"';
			}
		}

		return $link_attrs;
	}
}