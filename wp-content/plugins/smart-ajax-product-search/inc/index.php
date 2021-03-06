<?php
namespace YSM_APP;

const VERSION = '1.0.0';

if ( ! class_exists( 'Puc_v4_Factory' ) ) {
	include_once __DIR__ . '/app/plugin-update-checker/plugin-update-checker.php';
}

function set_ymapp_version( $ver ) {
	if ( version_compare( $ver, VERSION, '<' ) ) {
		return VERSION;
	}
	return $ver;
}
add_filter( 'ymapp_version', __NAMESPACE__ . '\\set_ymapp_version' );

function init_app() {
	$version = apply_filters( 'ymapp_version', VERSION );
	if ( VERSION === $version ) {
		if ( ! class_exists( 'YMAPP\Abstract_Setting_Field' ) ) {
			include_once __DIR__ . '/app/index.php';
		}
	}

	\Puc_v4_Factory::buildUpdateChecker(
		'https://yummywp.com/wp-content/json-data/smart-ajax-product-search.json',
		YSM_DIR . 'index.php',
		'smart-ajax-product-search'
	);
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init_app', 10 );

include_once __DIR__ . '/custom/cache.php';
include_once __DIR__ . '/custom/functions.php';
include_once __DIR__ . '/custom/hooks.php';
include_once __DIR__ . '/custom/rest.php';
include_once __DIR__ . '/custom/inline-styles-functions.php';
include_once __DIR__ . '/custom/class-ysm-search.php';
include_once __DIR__ . '/custom/class-ysm-db.php';
include_once __DIR__ . '/custom/class-ysm-setting.php';
include_once __DIR__ . '/custom/class-ysm-message.php';
include_once __DIR__ . '/custom/class-ysm-widget-manager.php';
include_once __DIR__ . '/custom/class-ysm-search-widget.php';
include_once __DIR__ . '/custom/class-ysm-style-generator.php';
include_once __DIR__ . '/compat/compat-visual-composer.php';
include_once __DIR__ . '/compat/compat-elementor.php';
