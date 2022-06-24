<?php

class Smartcrawl_Health_Settings extends Smartcrawl_Settings_Admin {
	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $_instance;

	/**
	 * Singleton instance getter
	 *
	 * @return self instance
	 */
	public static function get_instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function validate( $input ) {
		return array();
	}

	public function init() {
		$this->option_name = 'wds_health_options';
		$this->name = Smartcrawl_Settings::COMP_HEALTH;
		$this->slug = Smartcrawl_Settings::TAB_HEALTH;
		$this->action_url = admin_url( 'options.php' );
		$this->page_title = __( 'SmartCrawl Wizard: SEO Health', 'wds' );

		add_action( 'wp_ajax_wds-save-health-settings', array( $this, 'save_health_settings' ) );

		parent::init();
	}

	public function save_health_settings() {
		$data = $this->get_request_data();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		Smartcrawl_Lighthouse_Options::save_form_data( $_GET['wds_health_options'] );

		wp_send_json_success();
	}

	public function get_title() {
		return __( 'SEO Health', 'wds' );
	}

	public function options_page() {
		wp_enqueue_script( Smartcrawl_Controller_Assets::LIGHTHOUSE_JS );

		$this->_render( 'lighthouse/lighthouse-settings' );
	}

	public function defaults() {
		Smartcrawl_Lighthouse_Options::save_defaults();
	}

	protected function _get_view_defaults() {
		$mode_defaults = Smartcrawl_Lighthouse_Renderer::get_instance()->get_view_defaults();

		return array_merge(
			array(
				'active_tab' => $this->_get_active_tab( 'tab_lighthouse' ),
			),
			$mode_defaults,
			parent::_get_view_defaults()
		);
	}

	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( $_POST['_wds_nonce'], 'wds-health-nonce' )
			? $_POST
			: array();
	}
}