<?php
/**
 * FlipBook3D Elementor Widget
 *
 * Registers all user-configurable controls and renders the widget markup
 * that the frontend JS handler picks up to initialise FlipBook3D.
 */

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;

defined( 'ABSPATH' ) || exit;

class FlipBook3D_Widget extends Widget_Base {

	/* ------------------------------------------------------------------ */
	/*  IDENTITY                                                            */
	/* ------------------------------------------------------------------ */

	public function get_name()       { return 'flipbook3d'; }
	public function get_title()      { return esc_html__( 'FlipBook3D', 'flipbook3d-elementor-widget' ); }
	public function get_icon()       { return 'eicon-book'; }
	public function get_categories() { return [ 'flipbook3d' ]; }
	public function get_keywords()   { return [ 'flipbook', 'pdf', 'book', '3d', 'flip', 'page', 'viewer', 'magazine' ]; }

	/**
	 * Tells Elementor which scripts to enqueue when this widget is on the page.
	 * Works with Elementor's Improved Asset Loading (3.3+) — no need for
	 * elementor/frontend/after_enqueue_scripts.
	 */
	public function get_script_depends() {
		return [ 'flipbook3d-pdfjs', 'flipbook3d-core', 'flipbook3d-frontend' ];
	}

	public function get_style_depends() {
		return [ 'flipbook3d-core' ];
	}

	/* ------------------------------------------------------------------ */
	/*  CONTROLS                                                            */
	/* ------------------------------------------------------------------ */

	protected function register_controls() {

		/* ============================================================
		   TAB: CONTENT
		   Section 1 — Content Source
		   ============================================================ */
		$this->start_controls_section( 'section_source', [
			'label' => esc_html__( 'Content Source', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'source_type', [
			'label'   => esc_html__( 'Source Type', 'flipbook3d-elementor-widget' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'pdf_url',
			'options' => [
				'pdf_url'   => esc_html__( 'PDF — External URL', 'flipbook3d-elementor-widget' ),
				'pdf_media' => esc_html__( 'PDF — Media Library', 'flipbook3d-elementor-widget' ),
				'images'    => esc_html__( 'Image Gallery', 'flipbook3d-elementor-widget' ),
			],
		] );

		// -- PDF: External URL --
		$this->add_control( 'pdf_url', [
			'label'         => esc_html__( 'PDF URL', 'flipbook3d-elementor-widget' ),
			'type'          => Controls_Manager::URL,
			'placeholder'   => 'https://example.com/document.pdf',
			'show_external' => false,
			'default'       => [ 'url' => '' ],
			'condition'     => [ 'source_type' => 'pdf_url' ],
			'description'   => esc_html__( 'Paste the full URL of your PDF. The server must allow cross-origin access (CORS).', 'flipbook3d-elementor-widget' ),
		] );

		// -- PDF: Media Library --
		$this->add_control( 'pdf_media', [
			'label'       => esc_html__( 'PDF File', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::MEDIA,
			'media_types' => [ 'application/pdf' ],
			'condition'   => [ 'source_type' => 'pdf_media' ],
			'description' => esc_html__( 'Upload or select a PDF from your Media Library.', 'flipbook3d-elementor-widget' ),
		] );

		// -- Images --
		$this->add_control( 'image_gallery', [
			'label'       => esc_html__( 'Image Gallery', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::GALLERY,
			'condition'   => [ 'source_type' => 'images' ],
			'description' => esc_html__( 'Each image becomes one page. Use images with consistent dimensions for the best result.', 'flipbook3d-elementor-widget' ),
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: CONTENT
		   Section 2 — Book Dimensions
		   ============================================================ */
		$this->start_controls_section( 'section_dimensions', [
			'label' => esc_html__( 'Book Dimensions', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'book_width', [
			'label'       => esc_html__( 'Book Width (px)', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => [ 'px' ],
			'range'       => [ 'px' => [ 'min' => 300, 'max' => 2000, 'step' => 10 ] ],
			'default'     => [ 'size' => 900, 'unit' => 'px' ],
			'description' => esc_html__( 'Total width of the open book (both pages combined). The book will be automatically capped to its container width.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'aspect_ratio_preset', [
			'label'   => esc_html__( 'Page Aspect Ratio', 'flipbook3d-elementor-widget' ),
			'type'    => Controls_Manager::SELECT,
			'default' => '1.414',
			'options' => [
				'1.414'  => esc_html__( 'A4 Portrait (1.414)', 'flipbook3d-elementor-widget' ),
				'1.294'  => esc_html__( 'US Letter Portrait (1.294)', 'flipbook3d-elementor-widget' ),
				'0.707'  => esc_html__( 'A4 Landscape (0.707)', 'flipbook3d-elementor-widget' ),
				'0.773'  => esc_html__( 'US Letter Landscape (0.773)', 'flipbook3d-elementor-widget' ),
				'1.0'    => esc_html__( 'Square (1.0)', 'flipbook3d-elementor-widget' ),
				'1.5'    => esc_html__( 'Magazine (1.5)', 'flipbook3d-elementor-widget' ),
				'custom' => esc_html__( 'Custom…', 'flipbook3d-elementor-widget' ),
			],
			'description' => esc_html__( 'Height ÷ width ratio per page. Match this to your PDF or images.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'aspect_ratio_custom', [
			'label'     => esc_html__( 'Custom Ratio (height ÷ width)', 'flipbook3d-elementor-widget' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0.2,
			'max'       => 4.0,
			'step'      => 0.001,
			'default'   => 1.414,
			'condition' => [ 'aspect_ratio_preset' => 'custom' ],
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: CONTENT
		   Section 3 — Animation
		   ============================================================ */
		$this->start_controls_section( 'section_animation', [
			'label' => esc_html__( 'Animation & Rendering', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'flip_duration', [
			'label'       => esc_html__( 'Flip Duration (ms)', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => [ 'px' ],
			'range'       => [ 'px' => [ 'min' => 100, 'max' => 2000, 'step' => 50 ] ],
			'default'     => [ 'size' => 700, 'unit' => 'px' ],
			'description' => esc_html__( 'Duration of the 3D page-turn animation in milliseconds. Lower = snappier; higher = more dramatic.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'render_scale', [
			'label'       => esc_html__( 'Render Scale', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => [ 'px' ],
			'range'       => [ 'px' => [ 'min' => 0.5, 'max' => 3.0, 'step' => 0.1 ] ],
			'default'     => [ 'size' => 1.5, 'unit' => 'px' ],
			'description' => esc_html__( 'Internal canvas render scale. Higher = sharper pages but more GPU/RAM usage. 1.5–2 is the sweet spot for most screens.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'page_background', [
			'label'       => esc_html__( 'Page Background Colour', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#ffffff',
			'description' => esc_html__( 'Colour shown behind page content (fill for transparent images or PDFs with a white canvas).', 'flipbook3d-elementor-widget' ),
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: CONTENT
		   Section 4 — Controls & Behaviour
		   ============================================================ */
		$this->start_controls_section( 'section_behaviour', [
			'label' => esc_html__( 'Controls & Behaviour', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'start_page', [
			'label'       => esc_html__( 'Start Page', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 1,
			'max'         => 9999,
			'step'        => 1,
			'default'     => 1,
			'description' => esc_html__( 'Which page to open when the book first loads (1 = first page).', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'mute_sound', [
			'label'        => esc_html__( 'Page-Flip Sound', 'flipbook3d-elementor-widget' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Muted', 'flipbook3d-elementor-widget' ),
			'label_off'    => esc_html__( 'Enabled', 'flipbook3d-elementor-widget' ),
			'return_value' => 'yes',
			'default'      => '',
			'description'  => esc_html__( 'The page-flip sound is synthesised in the browser via Web Audio API. Mute it here if unwanted.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'show_controls', [
			'label'        => esc_html__( 'Navigation Bar', 'flipbook3d-elementor-widget' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Visible', 'flipbook3d-elementor-widget' ),
			'label_off'    => esc_html__( 'Hidden', 'flipbook3d-elementor-widget' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => esc_html__( 'Show or hide the bottom bar with First / Prev / Next / Last buttons.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'show_page_numbers', [
			'label'        => esc_html__( 'Page Number Overlay', 'flipbook3d-elementor-widget' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Visible', 'flipbook3d-elementor-widget' ),
			'label_off'    => esc_html__( 'Hidden', 'flipbook3d-elementor-widget' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => esc_html__( 'Small page-number label shown in the corner of each page.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'show_fullscreen', [
			'label'        => esc_html__( 'Fullscreen Button', 'flipbook3d-elementor-widget' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Visible', 'flipbook3d-elementor-widget' ),
			'label_off'    => esc_html__( 'Hidden', 'flipbook3d-elementor-widget' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'show_controls' => 'yes' ],
			'description'  => esc_html__( 'Show the fullscreen toggle inside the navigation bar.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'click_to_flip', [
			'label'        => esc_html__( 'Click Page to Flip', 'flipbook3d-elementor-widget' ),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Enabled', 'flipbook3d-elementor-widget' ),
			'label_off'    => esc_html__( 'Disabled', 'flipbook3d-elementor-widget' ),
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => esc_html__( 'Allow clicking on the left/right page edges to flip pages.', 'flipbook3d-elementor-widget' ),
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: STYLE
		   Section 1 — Theme Colours
		   ============================================================ */
		$this->start_controls_section( 'section_colors', [
			'label' => esc_html__( 'Theme Colours', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'color_accent', [
			'label'       => esc_html__( 'Accent Colour', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#e8c87d',
			'selectors'   => [ '{{WRAPPER}} .flipbook3d-wrapper' => '--fb-accent: {{VALUE}};' ],
			'description' => esc_html__( 'Used for button borders, hover highlights, and arrow indicators.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'color_spine', [
			'label'       => esc_html__( 'Spine Colour', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#c8a96e',
			'selectors'   => [ '{{WRAPPER}} .flipbook3d-wrapper' => '--fb-spine-color: {{VALUE}};' ],
			'description' => esc_html__( 'The vertical book spine strip at the centre of the spread.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'color_page_bg', [
			'label'       => esc_html__( 'Page Background (CSS var)', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#ffffff',
			'selectors'   => [ '{{WRAPPER}} .flipbook3d-wrapper' => '--fb-page-bg: {{VALUE}};' ],
			'description' => esc_html__( 'CSS variable counterpart of the Page Background setting. Both should match.', 'flipbook3d-elementor-widget' ),
		] );

		$this->add_control( 'section_controls_colors_heading', [
			'label'     => esc_html__( 'Navigation Bar', 'flipbook3d-elementor-widget' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'color_ui_bg', [
			'label'       => esc_html__( 'Bar Background', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => 'rgba(10,10,20,0.85)',
			'selectors'   => [ '{{WRAPPER}} .flipbook3d-wrapper' => '--fb-ui-bg: {{VALUE}};' ],
		] );

		$this->add_control( 'color_ui_text', [
			'label'       => esc_html__( 'Bar Text Colour', 'flipbook3d-elementor-widget' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#f0e8d8',
			'selectors'   => [ '{{WRAPPER}} .flipbook3d-wrapper' => '--fb-ui-text: {{VALUE}};' ],
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: STYLE
		   Section 2 — Typography
		   ============================================================ */
		$this->start_controls_section( 'section_typography', [
			'label' => esc_html__( 'Typography', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'btn_font_size', [
			'label'      => esc_html__( 'Button Font Size', 'flipbook3d-elementor-widget' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 10, 'max' => 24, 'step' => 1 ] ],
			'default'    => [ 'size' => 13, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .flipbook3d-btn'       => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .flipbook3d-page-info' => 'font-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'page_num_font_size', [
			'label'      => esc_html__( 'Page Number Font Size', 'flipbook3d-elementor-widget' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 8, 'max' => 20, 'step' => 1 ] ],
			'default'    => [ 'size' => 11, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .flipbook3d-page-num' => 'font-size: {{SIZE}}{{UNIT}};' ],
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: STYLE
		   Section 3 — Controls Bar Style
		   ============================================================ */
		$this->start_controls_section( 'section_controls_style', [
			'label'     => esc_html__( 'Navigation Bar Style', 'flipbook3d-elementor-widget' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_controls' => 'yes' ],
		] );

		$this->add_control( 'controls_border_radius', [
			'label'      => esc_html__( 'Bar Border Radius', 'flipbook3d-elementor-widget' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60, 'step' => 1 ] ],
			'default'    => [ 'size' => 40, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .flipbook3d-controls' => 'border-radius: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_control( 'controls_padding', [
			'label'      => esc_html__( 'Bar Padding', 'flipbook3d-elementor-widget' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px' ],
			'default'    => [ 'top' => '10', 'right' => '20', 'bottom' => '10', 'left' => '20', 'unit' => 'px', 'isLinked' => false ],
			'selectors'  => [ '{{WRAPPER}} .flipbook3d-controls' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'controls_shadow',
			'selector' => '{{WRAPPER}} .flipbook3d-controls',
		] );

		$this->end_controls_section();

		/* ============================================================
		   TAB: STYLE
		   Section 4 — Wrapper
		   ============================================================ */
		$this->start_controls_section( 'section_wrapper', [
			'label' => esc_html__( 'Widget Wrapper', 'flipbook3d-elementor-widget' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'wrapper_align', [
			'label'   => esc_html__( 'Alignment', 'flipbook3d-elementor-widget' ),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => [ 'title' => esc_html__( 'Left', 'flipbook3d-elementor-widget' ),   'icon' => 'eicon-text-align-left' ],
				'center'     => [ 'title' => esc_html__( 'Center', 'flipbook3d-elementor-widget' ), 'icon' => 'eicon-text-align-center' ],
				'flex-end'   => [ 'title' => esc_html__( 'Right', 'flipbook3d-elementor-widget' ),  'icon' => 'eicon-text-align-right' ],
			],
			'default'   => 'center',
			'selectors' => [ '{{WRAPPER}} .fb3d-outer' => 'justify-content: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'wrapper_padding', [
			'label'      => esc_html__( 'Padding', 'flipbook3d-elementor-widget' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [ '{{WRAPPER}} .fb3d-outer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );

		$this->add_control( 'wrapper_bg', [
			'label'     => esc_html__( 'Background Colour', 'flipbook3d-elementor-widget' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .fb3d-outer' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'book_shadow',
			'label'    => esc_html__( 'Book Box Shadow', 'flipbook3d-elementor-widget' ),
			'selector' => '{{WRAPPER}} .flipbook3d-stage',
		] );

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/*  RENDER — PHP (frontend + preview)                                   */
	/* ------------------------------------------------------------------ */

	protected function render() {
		$s = $this->get_settings_for_display();

		// ---- Resolve PDF URL ----
		$pdf_url = '';
		if ( 'pdf_url' === $s['source_type'] ) {
			$pdf_url = ! empty( $s['pdf_url']['url'] ) ? esc_url( $s['pdf_url']['url'] ) : '';
		} elseif ( 'pdf_media' === $s['source_type'] ) {
			$pdf_url = ! empty( $s['pdf_media']['url'] ) ? esc_url( $s['pdf_media']['url'] ) : '';
		}

		// ---- Resolve aspect ratio ----
		$aspect = ( 'custom' === $s['aspect_ratio_preset'] )
			? floatval( $s['aspect_ratio_custom'] )
			: floatval( $s['aspect_ratio_preset'] );
		if ( $aspect <= 0 ) { $aspect = 1.414; }

		// ---- Resolve image URLs ----
		$image_urls = [];
		if ( 'images' === $s['source_type'] && ! empty( $s['image_gallery'] ) ) {
			foreach ( $s['image_gallery'] as $img ) {
				if ( ! empty( $img['url'] ) ) {
					$image_urls[] = esc_url( $img['url'] );
				}
			}
		}

		// ---- Build data attributes ----
		$widget_id = 'fb3d-' . $this->get_id();

		$data = [
			'data-source-type'        => $s['source_type'],
			'data-pdf-url'            => $pdf_url,
			'data-images'             => wp_json_encode( $image_urls ),
			'data-width'              => intval( $s['book_width']['size'] ),
			'data-aspect-ratio'       => $aspect,
			'data-flip-duration'      => intval( $s['flip_duration']['size'] ),
			'data-scale'              => floatval( $s['render_scale']['size'] ),
			'data-page-bg'            => $s['page_background'],
			'data-mute'               => $s['mute_sound'],
			'data-show-controls'      => $s['show_controls'],
			'data-show-page-numbers'  => $s['show_page_numbers'],
			'data-show-fullscreen'    => $s['show_fullscreen'],
			'data-click-to-flip'      => $s['click_to_flip'],
			'data-start-page'         => max( 1, intval( $s['start_page'] ) ),
		];

		$attr_string = '';
		foreach ( $data as $attr => $value ) {
			$attr_string .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
		}

		// ---- Output ----
		?>
		<div class="fb3d-outer" style="display:flex;">
			<div id="<?php echo esc_attr( $widget_id ); ?>"
				 class="fb3d-widget"
				 <?php echo $attr_string; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			>
				<?php if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) :
					$meta = '';
					if ( $pdf_url ) {
						$meta = esc_html( basename( $pdf_url ) );
					} elseif ( ! empty( $image_urls ) ) {
						/* translators: %d: number of images */
						$meta = sprintf( esc_html__( '%d page images', 'flipbook3d-elementor-widget' ), count( $image_urls ) );
					} else {
						$meta = esc_html__( 'Select a PDF or images in the panel →', 'flipbook3d-elementor-widget' );
					}
					$dims = sprintf(
						/* translators: 1: width in px, 2: aspect ratio */
						esc_html__( 'Width: %1$dpx · Aspect: %2$s', 'flipbook3d-elementor-widget' ),
						intval( $s['book_width']['size'] ),
						$aspect
					);
				?>
					<div class="fb3d-editor-placeholder">
						<span class="fb3d-icon">📖</span>
						<span class="fb3d-title"><?php esc_html_e( 'FlipBook3D', 'flipbook3d-elementor-widget' ); ?></span>
						<span class="fb3d-meta"><?php echo $meta; // phpcs:ignore ?></span>
						<span class="fb3d-meta" style="opacity:.6;"><?php echo $dims; // phpcs:ignore ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  RENDER — JS / Elementor Live Preview Template                       */
	/* ------------------------------------------------------------------ */

	protected function _content_template() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		?>
		<#
		var sourceType  = settings.source_type;
		var pdfUrl      = ( 'pdf_url'   === sourceType ) ? settings.pdf_url.url   : '';
		if ( 'pdf_media' === sourceType ) { pdfUrl = settings.pdf_media.url; }
		var imgCount    = ( 'images'    === sourceType && settings.image_gallery ) ? settings.image_gallery.length : 0;
		var aspect      = ( 'custom'    === settings.aspect_ratio_preset ) ? settings.aspect_ratio_custom : settings.aspect_ratio_preset;
		var meta        = pdfUrl ? pdfUrl : ( imgCount ? imgCount + ' page images' : 'Select a PDF or images in the panel →' );
		var dims        = 'Width: ' + settings.book_width.size + 'px · Aspect: ' + aspect;
		#>
		<div class="fb3d-outer" style="display:flex;justify-content:center;">
			<div class="fb3d-editor-placeholder">
				<span class="fb3d-icon">📖</span>
				<span class="fb3d-title">FlipBook3D</span>
				<span class="fb3d-meta">{{{ meta }}}</span>
				<span class="fb3d-meta" style="opacity:.6;">{{{ dims }}}</span>
			</div>
		</div>
		<?php
	}
}
