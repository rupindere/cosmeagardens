<?php

class Smartcrawl_Settings_Dashboard extends Smartcrawl_Settings_Admin {

	const CRAWL_TIMEOUT_CODE = 'crawl_timeout';
	const BOX_SOCIAL = 'wds-social-dashboard-box';
	const BOX_ADVANCED_TOOLS = 'wds-advanced-tools-dashboard-box';
	const BOX_ONPAGE = 'wds-title-and-meta-dashboard-box';
	const BOX_CONTENT_ANALYSIS = 'wds-content-analysis-box';
	const BOX_SITEMAP = 'wds-sitemap-box';
	const BOX_LIGHTHOUSE = 'wds-lighthouse';
	const BOX_TOP_STATS = 'wds-dashboard-stats';
	const BOX_REPORTS = 'wds-reports-box';
	const BOX_UPGRADE = 'wds-upgrade';
	const BOX_SCHEMA = 'wds-schema-box';
	private static $_instance;
	protected $_seo_service;
	protected $_uptime_service;

	public static function get_instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function validate( $input ) {
		return $input;
	}

	public function init() {
		$this->slug = Smartcrawl_Settings::TAB_DASHBOARD;
		$this->page_title = __( 'SmartCrawl Wizard: Dashboard', 'wds' );

		add_action( 'wp_ajax_wds-activate-component', array( $this, 'json_activate_component' ) );
		add_action( 'wp_ajax_wds-reload-box', array( $this, 'json_reload_component' ) );

		parent::init();
	}

	public function json_activate_component() {
		$result = array( 'success' => false );
		$data = $this->get_request_data();

		$option_id = sanitize_key( smartcrawl_get_array_value( $data, 'option' ) );
		$flag = sanitize_key( smartcrawl_get_array_value( $data, 'flag' ) );
		$value = (bool) smartcrawl_get_array_value( $data, 'value' );

		if ( is_null( $option_id ) || is_null( $flag ) ) {
			wp_send_json( $result );

			return;
		}

		$options = self::get_specific_options( $option_id );
		$options[ $flag ] = $value;
		self::update_specific_options( $option_id, $options );

		$result['success'] = true;
		wp_send_json( $result );
	}

	public function json_reload_component() {
		$result = array( 'success' => false );
		$data = $this->get_request_data();

		$box_id = smartcrawl_get_array_value( $data, 'box_id' );

		if ( is_null( $box_id ) ) {
			wp_send_json( $result );

			return;
		}

		if ( ! is_array( $box_id ) ) {
			$box_id = array( $box_id );
		}
		$box_id = array_map( 'sanitize_key', $box_id );

		$box_id = array_unique( $box_id );

		foreach ( $box_id as $id ) {
			$result[ $id ] = $this->load_box_markup( $id );
		}

		$result['success'] = true;
		wp_send_json( $result );
	}

	private function load_box_markup( $box_id ) {
		switch ( $box_id ) {
			case self::BOX_SOCIAL:
				return $this->_load( 'dashboard/dashboard-widget-social' );

			case self::BOX_ADVANCED_TOOLS:
				return $this->_load( 'dashboard/dashboard-widget-advanced-tools' );

			case self::BOX_ONPAGE:
				return $this->_load( 'dashboard/dashboard-widget-onpage' );

			case self::BOX_CONTENT_ANALYSIS:
				return $this->_load( 'dashboard/dashboard-widget-content-analysis' );

			case self::BOX_SITEMAP:
				return $this->_load( 'dashboard/dashboard-widget-sitemap' );

			case self::BOX_LIGHTHOUSE:
				return Smartcrawl_Lighthouse_Dashboard_Renderer::load( 'dashboard/dashboard-widget-lighthouse' );

			case self::BOX_TOP_STATS:
				return Smartcrawl_Lighthouse_Dashboard_Renderer::load( 'dashboard/dashboard-top-lighthouse' );

			case self::BOX_SCHEMA:
				return $this->_load( 'dashboard/dashboard-widget-schema' );
		};

		return null;
	}

	/**
	 * Add admin settings page
	 */
	public function options_page() {
		wp_enqueue_script( Smartcrawl_Controller_Assets::DASHBOARD_PAGE_JS );

		$uptime = $this->_get_uptime_service();

		$this->_render_page( 'dashboard/dashboard', array() );
	}

	protected function _get_uptime_service() {
		if ( ! empty( $this->_uptime_service ) ) {
			return $this->_uptime_service;
		}

		$this->_uptime_service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_UPTIME );

		return $this->_uptime_service;
	}

	/**
	 * Add sub page to the Settings Menu
	 */
	public function add_page() {
		if ( ! $this->_is_current_tab_allowed() ) {
			return false;
		}

		$this->smartcrawl_page_hook = add_menu_page(
			$this->page_title,
			$this->get_title(),
			$this->capability,
			$this->slug,
			array( &$this, 'options_page' ),
			$this->get_icon()
		);

		$this->smartcrawl_page_hook = add_submenu_page(
			$this->slug,
			$this->page_title,
			$this->get_sub_title(),
			$this->capability,
			$this->slug,
			array( &$this, 'options_page' )
		);

		add_action( "admin_print_styles-{$this->smartcrawl_page_hook}", array( &$this, 'admin_styles' ) );
	}

	public function get_title() {
		return smartcrawl_is_build_type_full()
			? __( 'SmartCrawl Pro', 'wds' )
			: __( 'SmartCrawl', 'wds' );
	}

	public function get_sub_title() {
		return __( 'Dashboard', 'wds' );
	}

	/**
	 * Always allow dashboard tab if there's more than one tab allowed
	 *
	 * Overrides Smartcrawl_Settings::_is_current_tab_allowed
	 *
	 * @return bool
	 */
	protected function _is_current_tab_allowed() {
		if ( parent::_is_current_tab_allowed() ) {
			return true;
		}
		// Else we always add dashboard if there are other pages
		$all_tabs = Smartcrawl_Settings_Settings::get_blog_tabs();

		return ! empty( $all_tabs );
	}

	/**
	 * Default settings
	 */
	public function defaults() {
		$this->options = Smartcrawl_Settings::get_options();
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		$svg = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M14.4446 3.34827C13.129 2.46922 11.5823 2 10 2C7.87827 2 5.84343 2.84285 4.34314 4.34314C2.84285 5.84343 2 7.87827 2 10C2 11.0501 2.20668 12.0846 2.60267 13.0462L10.6489 5H8.99999V4H12H13V8H12V6.3359L3.57263 14.7633C4.1858 15.5907 4.9514 16.2898 5.82541 16.8244L10.6499 12H8.99997V11H12H13V15H12V13.3369L7.68051 17.6564C8.9348 18.0364 10.2675 18.1035 11.5607 17.8463C13.1126 17.5376 14.538 16.7757 15.6569 15.6569C16.7757 14.538 17.5376 13.1126 17.8463 11.5607C18.1549 10.0089 17.9966 8.40035 17.3911 6.93854C16.7856 5.47673 15.7602 4.22732 14.4446 3.34827ZM12 5.00006V5H11.9999L12 5.00006ZM4.44428 1.6853C6.08877 0.586489 8.02219 0 10 0C12.6522 0 15.1957 1.05359 17.071 2.92895C18.9464 4.80432 20 7.34783 20 10C20 11.9778 19.4135 13.9112 18.3147 15.5557C17.2159 17.2002 15.6541 18.4819 13.8268 19.2388C11.9996 19.9956 9.98888 20.1937 8.04908 19.8079C6.10927 19.422 4.32748 18.4696 2.92896 17.071C1.53043 15.6725 0.577995 13.8907 0.192143 11.9509C-0.193709 10.0111 0.00435805 8.00042 0.761235 6.17316C1.51811 4.3459 2.79979 2.78412 4.44428 1.6853Z" fill="#F0F6FC"/></svg>';
		$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg );
		return $icon; // phpcs:ignore -- base64_encode is harmless here
	}

	protected function _get_seo_service() {
		if ( ! empty( $this->_seo_service ) ) {
			return $this->_seo_service;
		}

		$this->_seo_service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_SEO );

		return $this->_seo_service;
	}

	/**
	 * TODO: replace with check_ajax_referer
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( $_POST['_wds_nonce'], 'wds-nonce' ) ? stripslashes_deep( $_POST ) : array();
	}
}