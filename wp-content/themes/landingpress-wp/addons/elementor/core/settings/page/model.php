<?php
namespace Elementor\Core\Settings\Page;

use Elementor\Controls_Manager;
use Elementor\Core\Settings\Base\Model as BaseModel;
use Elementor\Group_Control_Background;
use Elementor\Settings;
use Elementor\Core\Settings\Manager as SettingsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Model extends BaseModel {

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function __construct( array $data = [] ) {
		$this->post = get_post( $data['id'] );

		if ( ! $this->post ) {
			$this->post = new \WP_Post( (object) [] );
		}

		parent::__construct( $data );
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function get_name() {
		return 'page-settings';
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function get_unique_name() {
		return $this->get_name() . '-' . $this->post->ID;
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function get_css_wrapper_selector() {
		return 'body.elementor-page-' . $this->get_id();
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function get_panel_page_settings() {
		return [
			'title' => __( 'Document Settings', 'elementor' ),
		];
	}

	/**
	 * @since 1.6.0
	 * @access public
	 */
	public function on_export( $element_data ) {
		if ( ! empty( $element_data['settings']['template'] ) && Manager::TEMPLATE_CANVAS !== $element_data['settings']['template'] ) {
			unset( $element_data['settings']['template'] );
		}

		return $element_data;
	}

	/**
	 * @since 1.6.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_page_settings',
			[
				'label' => __( 'Document Settings', 'elementor' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'clear_page',
			[
				'type' => Controls_Manager::BUTTON,
				'label' => __( 'Delete All Content', 'elementor' ),
				'text' => __( 'Delete', 'elementor' ),
				'button_type' => 'warning',
				'event' => 'elementor:clearPage',
			]
		);

		$this->add_control(
			'post_title',
			[
				'label' => __( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => $this->post->post_title,
				'label_block' => true,
			]
		);

		$page_title_selector = SettingsManager::get_settings_managers( 'general' )->get_model()->get_settings( 'elementor_page_title_selector' );

		if ( ! $page_title_selector ) {
			$page_title_selector = 'h1.entry-title';
		}

		// $this->add_control(
		// 	'hide_title',
		// 	[
		// 		'label' => __( 'Hide Title', 'elementor' ),
		// 		'type' => Controls_Manager::SWITCHER,
		// 		'label_off' => __( 'No', 'elementor' ),
		// 		'label_on' => __( 'Yes', 'elementor' ),
		// 		'description' => sprintf(
		// 			/* translators: %s: Setting Page URL */
		// 			__( 'Not working? You can set a different selector for the title in the <a href="%s" target="_blank">Settings page</a>.', 'elementor' ),
		// 			Settings::get_url() . '#tab-style'
		// 		),
		// 		'selectors' => [
		// 			'{{WRAPPER}} ' . $page_title_selector => 'display: none',
		// 		],
		// 		'export' => '__return_true',
		// 	]
		// );

		if ( post_type_supports( $this->post->post_type, 'excerpt' ) ) {
			$this->add_control(
				'post_excerpt',
				[
					'label' => __( 'Excerpt', 'elementor' ),
					'type' => Controls_Manager::TEXTAREA,
					'default' => $this->post->post_excerpt,
					'label_block' => true,
				]
			);
		}

		if ( Manager::is_cpt_custom_templates_supported() ) {
			require_once ABSPATH . '/wp-admin/includes/template.php';

			$options = [
				'default' => __( 'Default', 'elementor' ),
			];

			$templates = array_flip( get_page_templates( null, $this->post->post_type ) );
			asort( $templates );

			$options += $templates;

			$this->add_control(
				'template',
				[
					'label' => __( 'Template', 'elementor' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'default',
					'options' => $options,
					'export' => function( $value ) {
						return Manager::TEMPLATE_CANVAS === $value;
					},
					'label_block' => true,
				]
			);
		}

		$post_type_object = get_post_type_object( $this->post->post_type );

		$can_publish = $post_type_object && current_user_can( $post_type_object->cap->publish_posts );

		if ( 'publish' === $this->post->post_status || 'private' === $this->post->post_status || $can_publish ) {
			$this->add_control(
				'post_status',
				[
					'label' => __( 'Status', 'elementor' ),
					'type' => Controls_Manager::SELECT,
					'default' => $this->post->post_status,
					'options' => get_post_statuses(),
				]
			);
		}

		$post_type = $this->post->post_type;

		if ( $post_type == 'page' || $post_type == 'post' ) {

			$this->add_control(
				'_landingpress_page_width',
				[
					'label' => __( 'Page Width', 'landingpress-wp' ),
					'type' => Controls_Manager::SELECT,
					'default' => '0',
					'options' => [
						'0' => __( 'default', 'landingpress-wp' ), 
						'500' => '500px', 
						'600' => '600px', 
						'700' => '700px', 
						'800' => '800px',
						'900' => '900px',
						'960' => '960px',
						'1000' => '1000px',
						'1100' => '1100px',
						'1140' => '1140px',
						'1200' => '1200px',
					],
					'condition' => [
						'template' => [ 
							'default', 
							'page_landingpress_boxed.php', 
							'page_landingpress_boxed_hf.php' 
						],
					],
					// 'label_block' => true,
				]
			);

			$this->add_control(
				'_landingpress_hide_sidebar',
				[
					'label' => __( 'Hide Sidebar', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_header',
				[
					'label' => __( 'Hide Header', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default', 
							'page_landingpress_hf.php', 
							'page_landingpress_boxed_hf.php', 
							'page_landingpress_slim_hf.php' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_menu',
				[
					'label' => __( 'Hide Header Menu', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default', 
							'page_landingpress_hf.php', 
							'page_landingpress_boxed_hf.php', 
							'page_landingpress_slim_hf.php' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_footerwidgets',
				[
					'label' => __( 'Hide Footer Widgets', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default', 
							'page_landingpress_hf.php', 
							'page_landingpress_boxed_hf.php', 
							'page_landingpress_slim_hf.php' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_footer',
				[
					'label' => __( 'Hide Footer', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default', 
							'page_landingpress_hf.php', 
							'page_landingpress_boxed_hf.php', 
							'page_landingpress_slim_hf.php' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_breadcrumb',
				[
					'label' => __( 'Hide Breadcrumb', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default' 
						],
					],
				]
			);

			$this->add_control(
				'_landingpress_hide_title',
				[
					'label' => __( 'Hide Title', 'landingpress-wp' ),
					'type' => Controls_Manager::SWITCHER,
					'default' => '',
					'label_on' => __( 'yes', 'landingpress-wp' ),
					'label_off' => __( 'default', 'landingpress-wp' ),
					'return_value' => 'yes',
					'condition' => [
						'template' => [ 
							'default' 
						],
					],
				]
			);

			if ( $post_type == 'post' ) {

				$this->add_control(
					'_landingpress_hide_comments',
					[
						'label' => __( 'Hide Comments', 'landingpress-wp' ),
						'type' => Controls_Manager::SWITCHER,
						'default' => '',
						'label_on' => __( 'yes', 'landingpress-wp' ),
						'label_off' => __( 'default', 'landingpress-wp' ),
						'return_value' => 'yes',
						'condition' => [
							'template' => [ 
								'default' 
							],
						],
					]
				);

			}

			$templates_local = \Elementor\Plugin::$instance->templates_manager->get_source( 'local' );
			$templates = $templates_local->get_items();
			$options_templates = array( '0' => esc_html__( 'Choose...', 'landingpress-wp' ) );
			foreach ( $templates as $template ) {
				$template_id = $template['template_id'];
				$options_templates[$template_id] = $template['title'].' ('.$template['type'].')';
			}

			$this->add_control(
				'_landingpress_page_header_custom',
				[
					'label' => __( 'Custom Header', 'landingpress-wp' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default' => 'default',
						'disable' => 'disable',
						'custom' => 'custom',
					],
					'condition' => [
						'template!' => 'elementor_canvas',
					],
					// 'label_block' => true,
				]
			);

			$this->add_control(
				'_landingpress_page_header_elementor',
				[
					'label' => __( 'Choose Header...', 'landingpress-wp' ),
					'type' => Controls_Manager::SELECT,
					'default' => '0',
					'options' => $options_templates,
					'condition' => [
						'_landingpress_page_header_custom' => 'custom',
					],
					'label_block' => true,
				]
			);

			$this->add_control(
				'_landingpress_page_footer_custom',
				[
					'label' => __( 'Custom Footer', 'landingpress-wp' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default' => 'default',
						'disable' => 'disable',
						'custom' => 'custom',
					],
					'condition' => [
						'template!' => 'elementor_canvas',
					],
					// 'label_block' => true,
				]
			);

			$this->add_control(
				'_landingpress_page_footer_elementor',
				[
					'label' => __( 'Choose Footer...', 'landingpress-wp' ),
					'type' => Controls_Manager::SELECT,
					'default' => '0',
					'options' => $options_templates,
					'condition' => [
						'_landingpress_page_footer_custom' => 'custom',
					],
					'label_block' => true,
				]
			);

		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_page_style',
			[
				'label' => __( 'Page Style', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'label' => __( 'Background', 'elementor' ),
				'fields_options' => [
					'__all' => [
						'export' => '__return_true',
					],
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => __( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'export' => '__return_true',
			]
		);

		$this->end_controls_section();
	}
}
