<?php
/**
 * Plugin Name:       FlipBook3D — Elementor Widget
 * Plugin URI:        https://github.com/raselupm/flipbook3d
 * Description:       A realistic 3D PDF flip-book Elementor widget. Load any PDF or image gallery and let visitors flip through it with silky page-turn animations and configurable settings.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            FlipBook3D
 * Author URI:        https://github.com/raselupm/flipbook3d
 * License:           MIT
 * Text Domain:       flipbook3d-elementor
 *
 * Elementor tested up to: 3.20
 */

defined( 'ABSPATH' ) || exit;

define( 'FLIPBOOK3D_VERSION',    '1.0.0' );
define( 'FLIPBOOK3D_FILE',       __FILE__ );
define( 'FLIPBOOK3D_DIR',        plugin_dir_path( __FILE__ ) );
define( 'FLIPBOOK3D_URL',        plugin_dir_url( __FILE__ ) );
define( 'FLIPBOOK3D_ASSETS_URL', FLIPBOOK3D_URL . 'assets/' );
define( 'FLIPBOOK3D_MIN_ELEMENTOR', '3.0.0' );

/**
 * Main plugin class — singleton.
 */
final class FlipBook3D_Elementor_Plugin {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/* ------------------------------------------------------------------ */
	/*  INIT                                                                */
	/* ------------------------------------------------------------------ */

	public function init() {
		// Dependency: Elementor must be active
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'notice_missing_elementor' ] );
			return;
		}

		// Dependency: minimum Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, FLIPBOOK3D_MIN_ELEMENTOR, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'notice_elementor_version' ] );
			return;
		}

		// Hooks
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/register',               [ $this, 'register_widget' ] );
		// Register (not enqueue) scripts early — widget declares them via get_script_depends()
		// so Elementor's Improved Asset Loading enqueues them only when the widget is on the page.
		add_action( 'wp_enqueue_scripts',                       [ $this, 'register_assets' ] );
		add_action( 'elementor/editor/after_enqueue_scripts',   [ $this, 'enqueue_editor' ] );
	}

	/* ------------------------------------------------------------------ */
	/*  ADMIN NOTICES                                                       */
	/* ------------------------------------------------------------------ */

	public function notice_missing_elementor() {
		$msg = sprintf(
			/* translators: 1: Plugin name, 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'flipbook3d-elementor' ),
			'<strong>FlipBook3D Elementor</strong>',
			'<strong>Elementor</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $msg ); // phpcs:ignore
	}

	public function notice_elementor_version() {
		$msg = sprintf(
			/* translators: 1: Plugin name, 2: Elementor, 3: Required version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'flipbook3d-elementor' ),
			'<strong>FlipBook3D Elementor</strong>',
			'<strong>Elementor</strong>',
			FLIPBOOK3D_MIN_ELEMENTOR
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $msg ); // phpcs:ignore
	}

	/* ------------------------------------------------------------------ */
	/*  CATEGORY                                                            */
	/* ------------------------------------------------------------------ */

	public function register_category( $manager ) {
		$manager->add_category( 'flipbook3d', [
			'title' => esc_html__( 'FlipBook3D', 'flipbook3d-elementor' ),
			'icon'  => 'eicon-book',
		] );
	}

	/* ------------------------------------------------------------------ */
	/*  WIDGET                                                              */
	/* ------------------------------------------------------------------ */

	public function register_widget( $manager ) {
		require_once FLIPBOOK3D_DIR . 'widgets/flipbook3d-widget.php';
		$manager->register( new \FlipBook3D_Widget() );
	}

	/* ------------------------------------------------------------------ */
	/*  ASSETS — REGISTER ONLY                                             */
	/*  Elementor enqueues these automatically via get_script_depends()    */
	/*  and get_style_depends() declared in the widget class.              */
	/* ------------------------------------------------------------------ */

	public function register_assets() {
		// PDF.js (required for PDF rendering)
		wp_register_script(
			'flipbook3d-pdfjs',
			'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
			[],
			'3.11.174',
			true
		);

		// Core FlipBook3D styles
		wp_register_style(
			'flipbook3d-core',
			FLIPBOOK3D_ASSETS_URL . 'css/flipbook3d.css',
			[],
			FLIPBOOK3D_VERSION
		);

		// Core FlipBook3D script
		wp_register_script(
			'flipbook3d-core',
			FLIPBOOK3D_ASSETS_URL . 'js/flipbook3d.js',
			[ 'flipbook3d-pdfjs' ],
			FLIPBOOK3D_VERSION,
			true
		);

		// Elementor frontend handler
		wp_register_script(
			'flipbook3d-frontend',
			FLIPBOOK3D_ASSETS_URL . 'js/frontend.js',
			[ 'flipbook3d-core', 'elementor-frontend' ],
			FLIPBOOK3D_VERSION,
			true
		);
	}

	/* ------------------------------------------------------------------ */
	/*  ASSETS — EDITOR                                                     */
	/* ------------------------------------------------------------------ */

	public function enqueue_editor() {
		// Inline styles for the editor placeholder shown in the canvas
		wp_add_inline_style( 'elementor-editor', '
			.fb3d-editor-placeholder {
				display: flex;
				align-items: center;
				justify-content: center;
				flex-direction: column;
				gap: 12px;
				background: #f0ede8;
				border: 2px dashed #c8a96e;
				border-radius: 8px;
				padding: 60px 40px;
				min-width: 280px;
				font-family: Georgia, serif;
			}
			.fb3d-editor-placeholder .fb3d-icon  { font-size: 48px; }
			.fb3d-editor-placeholder .fb3d-title { font-size: 18px; color: #8b6914; font-weight: bold; }
			.fb3d-editor-placeholder .fb3d-meta  { font-size: 12px; color: #aaa; text-align: center; }
		' );
	}
}

FlipBook3D_Elementor_Plugin::instance();
