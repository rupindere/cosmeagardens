<?php

/**
 * Class Smartcrawl_Compatibility
 *
 * Fixes third-party compatibility issues
 */
class Smartcrawl_Compatibility extends Smartcrawl_Base_Controller {
	/**
	 * Singleton instance
	 *
	 * @var Smartcrawl_Compatibility
	 */
	private static $_instance;

	/**
	 * Obtain instance without booting up
	 *
	 * @return Smartcrawl_Compatibility instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function init() {
		add_filter( 'wds-omitted-shortcodes', array( $this, 'avada_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'divi_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'wpbakery_omitted_shortcodes' ) );
		add_filter( 'wds-omitted-shortcodes', array( $this, 'swift_omitted_shortcodes' ) );
		add_filter( 'bbp_register_topic_taxonomy', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_forum_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_topic_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'bbp_register_reply_post_type', array( $this, 'allow_sitemap_access' ) );
		add_filter( 'wds-sitemaps-sitemap_url', array( $this, 'change_sitemap_url_for_domain_map' ) );
		// Disable defender login redirect because we are not entirely sure about its security implications
		//add_filter( 'wds-report-admin-url', array( $this, 'ensure_defender_login_redirect' ) );

		return true;
	}

	public function allow_sitemap_access( $args ) {
		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$is_sitemap_request = strpos( $request, '/sitemap.xml' ) === strlen( $request ) - strlen( '/sitemap.xml' );
		$sc_sitemap_active = Smartcrawl_Settings::get_setting( 'sitemap' );
		if ( $sc_sitemap_active && $is_sitemap_request ) {
			$args['show_ui'] = true;
		}
		return $args;
	}

	public function avada_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'fusion_code',
			'fusion_imageframe',
			'fusion_slide',
			'fusion_syntax_highlighter',
		) );
	}

	public function divi_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'et_pb_code',
			'et_pb_fullwidth_code',
		) );
	}

	public function wpbakery_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'vc_raw_js',
			'vc_raw_html',
		) );
	}

	public function swift_omitted_shortcodes( $omitted ) {
		return array_merge( $omitted, array(
			'spb_raw_js',
			'spb_raw_html',
		) );
	}

	private function is_preview_request() {
		return is_admin()
		       && smartcrawl_is_switch_active( 'DOING_AJAX' )
		       && isset( $_POST['_wds_nonce'] )
		       && (
			       wp_verify_nonce( $_POST['_wds_nonce'], 'wds-metabox-nonce' )
			       || wp_verify_nonce( $_POST['_wds_nonce'], 'wds-onpage-nonce' )
		       );
	}

	public function ensure_defender_login_redirect( $url ) {
		if (
			is_user_logged_in()
			|| ! method_exists( '\WP_Defender\Module\Advanced_Tools\Component\Mask_Api', 'maybeAppendTicketToUrl' )
		) {
			return $url;
		}

		return \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::maybeAppendTicketToUrl( $url );
	}

	public function change_sitemap_url_for_domain_map( $sitemap_url ) {
		if (
			is_multisite()
			&& class_exists( 'domain_map' )
			&& smartcrawl_is_switch_active( 'SMARTCRAWL_SITEMAP_DM_SIMPLE_DISCOVERY_FALLBACK' )
		) {
			$sitemap_url = ( is_network_admin() ? '../../' : ( is_admin() ? '../' : '/' ) ) . 'sitemap.xml'; // Simplest possible logic.
		}

		return $sitemap_url;
	}
}