<?php
/**
 * Plugin Name: Smart WooCommerce Search PRO
 * Plugin URI:  https://yummywp.com/plugins/smart-woocommerce-search/
 * Description: Smart Ajax Search allows you to instantly search WooCommerce products.
 * Author:      YummyWP
 * Author URI:  https://yummywp.com
 * Version:     2.4.3
 * Domain Path: /languages
 * Text Domain: smart-woocommerce-search
 *
 * Requires PHP: 5.4
 *
 * WC requires at least: 3.0
 * WC tested up to: 6.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether free version is activated
 */
if ( defined( 'YSM_VER' ) ) {
	deactivate_plugins( 'smart-woocommerce-search/index.php' );
	return;
}

/**
 * Define main constants
 */
define( 'YSM_PRO', true );

if ( ! defined( 'YSM_VER' ) ) {
	define( 'YSM_VER', 'ysaps-2.4.3' );
}

if ( ! defined( 'YSM_DIR' ) ) {
	define( 'YSM_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YSM_URI' ) ) {
	define( 'YSM_URI', plugin_dir_url( __FILE__ ) );
}

include_once YSM_DIR . 'inc/index.php';

/**
 * Load plugin textdomain.
 */
if ( ! function_exists( 'ysm_load_textdomain' ) ) {
	function ysm_load_textdomain() {
		load_plugin_textdomain( 'smart-woocommerce-search', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'ysm_load_textdomain' );
}

/**
 * Add Pages to Admin Menu
 */
if ( ! function_exists( 'ysm_add_menu_page' ) ) {
	function ysm_add_menu_page() {
		add_menu_page( __( 'Smart Search', 'smart-woocommerce-search' ),
			__( 'Smart Search <sup>PRO</sup>', 'smart-woocommerce-search' ),
			'manage_options',
			'smart-search',
			null,
			'dashicons-search',
			'39.9'
		);

		add_submenu_page( 'smart-search',
			__( 'Search Widgets', 'smart-woocommerce-search' ),
			__( 'Widgets', 'smart-woocommerce-search' ),
			'manage_options',
			'smart-search',
			'ysm_display_admin_page_widgets'
		);

		add_submenu_page( 'smart-search',
			__( 'Add New Search Widget', 'smart-woocommerce-search' ),
			__( 'Add New', 'smart-woocommerce-search' ),
			'manage_options',
			'smart-search-custom-new',
			'ysm_display_admin_page_widget_new'
		);

		add_submenu_page( 'smart-search',
			__( 'Smart Ajax Product Search', 'smart-woocommerce-search' ),
			__( 'Additional', 'smart-woocommerce-search' ),
			'manage_options',
			'smart-search-about',
			'ysm_display_admin_page_about'
		);
	}
	add_action( 'admin_menu', 'ysm_add_menu_page' );
}

if ( ! function_exists( 'ysm_display_admin_page_widgets' ) ) {
	function ysm_display_admin_page_widgets() {
		include_once YSM_DIR . 'templates/admin-page-widgets.php';
	}
}

if ( ! function_exists( 'ysm_display_admin_page_widget_new' ) ) {
	function ysm_display_admin_page_widget_new() {
		include_once YSM_DIR . 'templates/admin-page-widget-new.php';
	}
}

if ( ! function_exists( 'ysm_display_admin_page_about' ) ) {
	function ysm_display_admin_page_about() {
		include_once YSM_DIR . 'templates/admin-page-about.php';
	}
}

/**
 * Include Front Scripts
 */
if ( ! function_exists( 'ysm_enqueue_scripts' ) ) {
	function ysm_enqueue_scripts() {
		wp_enqueue_style( 'smart-search', YSM_URI . 'assets/dist/css/general.css', array(), YSM_VER );

		$rest_url = rest_url( 'ysm/v1/search' ) . '?';
		$search_page_url = home_url( '/' );

		if ( defined( 'POLYLANG_BASENAME' ) ) {
			$rest_url = add_query_arg( array( 'lang' => pll_current_language() ), $rest_url ) . '&';
			$search_page_url = pll_home_url();
		} elseif ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE !== '' ) {
			$rest_url = add_query_arg( array( 'lang' => ICL_LANGUAGE_CODE ), $rest_url ) . '&';
			$search_page_url = apply_filters( 'wpml_home_url', $search_page_url );
		}

		$localized                  = array();
		$localized['restUrl']       = $rest_url;
		$localized['searchPageUrl'] = $search_page_url;

		$active_widgets  = ysm_get_custom_widgets();
		$default_widgets = ysm_get_default_widgets();

		$available_default_widgets_ids = ysm_get_default_widgets_ids();

		foreach ( $default_widgets as $w_key => $default_widget ) {
			if ( isset( $available_default_widgets_ids[ $w_key ] ) ) {
				$enable_key = ( 'default' === $w_key ) ? 'enable_search' : 'enable_' . $w_key . '_search';
				if ( ! empty( $default_widget['settings'][ $enable_key ] ) ) {
					$active_widgets[ $w_key ] = $default_widget;
				}
			}
		}

		foreach ( $active_widgets as $k => $v ) {

			if ( $k === 'default' ) {
				$css_id  = '.widget_search.ysm-active';
				$js_pref = '';
			} elseif ( $k === 'product' ) {
				$css_id  = '.widget_product_search.ysm-active';
				$js_pref = $k . '_';
			} elseif ( $k === 'avada' ) {
				$css_id  = '.fusion-search-form.ysm-active';
				$js_pref = $k . '_';
			} else {
				$css_id  = '.ysm-search-widget-' . $k;
				$js_pref = 'custom_' . $k . '_';
			}

			if ( false === strpos( $js_pref, 'custom_' ) ) {
				$localized[ 'enable_' . $js_pref . 'search' ] = 1;
			}

			// check if AJAX disabled
			if ( ! empty( $v['settings']['disable_ajax'] ) ) {
				$localized[ $js_pref . 'disable_ajax' ] = (bool) $v['settings']['disable_ajax'];
			} else {
				$localized[ $js_pref . 'disable_ajax' ] = false;
			}

			if ( isset( $v['settings']['char_count'] ) ) {
				$localized[ $js_pref . 'char_count' ] = (int) $v['settings']['char_count'];
			}

			if ( isset( $v['settings']['no_results_text'] ) ) {
				$localized[ $js_pref . 'no_results_text' ] = __( $v['settings']['no_results_text'], 'smart-woocommerce-search' );
			}

			if ( ! empty( $v['settings']['loader'] ) ) {
				if ( is_array( $v['settings']['loader'] ) ) {
					$v['settings']['loader'] = $v['settings']['loader'][0];
				}
				$localized[ $js_pref . 'loader_icon' ] = YSM_URI . 'assets/images/' . $v['settings']['loader'] . '.gif';
			} else {
				$localized[ $js_pref . 'loader_icon' ] = YSM_URI . 'assets/images/loader1.gif';
			}

			if ( ! empty( $v['settings']['stop_words'] ) || ! empty( $v['settings']['enable_fuzzy_search'] ) ) {
				$localized[ $js_pref . 'prevent_bad_queries' ] = false;
			} else {
				$localized[ $js_pref . 'prevent_bad_queries' ] = true;
			}

			$pt_list = array();

			if ( ! empty( $v['settings']['post_type_product'] ) ) {
				$pt_list['product'] = $v['settings']['post_type_product'];
			}

			if ( ! empty( $v['settings']['post_type_post'] ) ) {
				$pt_list['post'] = $v['settings']['post_type_post'];
			}

			if ( ! empty( $v['settings']['post_type_page'] ) ) {
				$pt_list['page'] = $v['settings']['post_type_page'];
			}

			if ( isset( $pt_list['product'] ) && empty( $v['settings']['search_page_layout_posts'] ) ) {
				$localized[ $js_pref . 'layout' ] = 'product';
			}

			if ( ! empty( $v['settings']['popup_height'] ) ) {
				$localized[ $js_pref . 'popup_height' ] = intval( $v['settings']['popup_height'] );
			} else {
				$localized[ $js_pref . 'popup_height' ] = 400;
			}

			if ( ! empty( $v['settings']['search_page_default_output'] ) ) {
				$localized[ $js_pref . 'default_output' ] = (bool) $v['settings']['search_page_default_output'];
			}

			ysm_add_inline_styles_to_stack( $v, $css_id );

			if ( ! empty( $v['settings']['product_slug'] ) ) {
				$localized[ $js_pref . 'product_slug' ] = $v['settings']['product_slug'];
			}
		}

		$styles = Ysm_Style_Generator::create();

		wp_add_inline_style( 'smart-search', $styles );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			wp_enqueue_script( 'smart-search-autocomplete', YSM_URI . 'assets/src/js/jquery.autocomplete.js', array( 'jquery' ), false, 1 );
			wp_enqueue_script( 'smart-search-custom-scroll', YSM_URI . 'assets/src/js/jquery.nanoscroller.js', array( 'jquery' ), false, 1 );
			wp_enqueue_script( 'smart-search-general', YSM_URI . 'assets/src/js/general.js', array( 'jquery' ), time(), 1 );
		} else {
			wp_enqueue_script( 'smart-search-general', YSM_URI . 'assets/dist/js/main.js', array( 'jquery' ), YSM_VER, 1 );
		}

		wp_localize_script( 'smart-search-general', 'ysm_L10n', $localized );

	}
	add_action( 'wp_enqueue_scripts', 'ysm_enqueue_scripts' );
}

/**
 * Include Admin Scripts
 */
if ( ! function_exists( 'ysm_admin_enqueue_scripts' ) ) {
	function ysm_admin_enqueue_scripts() {
		$cur_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( ! $cur_page || false === strpos( $cur_page, 'smart-search' ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'smart-search-admin', YSM_URI . 'assets/dist/css/admin.css' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'smart-search-admin', YSM_URI . 'assets/dist/js/admin.js', array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-sortable',
			'jquery-ui-slider',
			'underscore',
			'wp-color-picker',
			'wp-util',
		), false, 1 );

		wp_localize_script( 'smart-search-admin', 'ysm_L10n', array(
			'column_delete' => __( 'Delete column?', 'smart-woocommerce-search' ),
			'row_delete'    => __( 'Delete row?', 'smart-woocommerce-search' ),
			'widget_delete' => __( 'Delete widget?', 'smart-woocommerce-search' ),
		) );

		// Select2
		wp_enqueue_style( 'ysrs-select2', YSM_URI . 'assets/dist/css/select2.min.css', array(), YSM_VER );
		wp_enqueue_script( 'ysrs-select2', YSM_URI . 'assets/dist/js/select2.min.js', array(), YSM_VER, true );
	}
	add_action( 'admin_enqueue_scripts', 'ysm_admin_enqueue_scripts' );
}

/**
 * Filter Admin title
 */
if ( ! function_exists( 'ysm_change_admin_title' ) ) {
	function ysm_change_admin_title( $admin_title, $title ) {
		$is_smart_search = false;
		$cur_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( $cur_page && 'smart-search' === $cur_page ) {
			$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
			$id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
			if ( $action && 'edit' === $action && ! empty( $id ) ) {
				$is_smart_search = true;
				if ( ysm_get_default_widgets_names( $id ) ) {
					$id = ysm_get_default_widgets_names( $id );
				}
				/* translators: %s: Name/id of a widget */
				$title = sprintf( __( 'Edit Widget: %s', 'smart-woocommerce-search' ), $id );
			}
		}

		if ( $is_smart_search ) {
			if ( is_network_admin() ) {
				/* translators: %s: Name of a site */
				$admin_title = sprintf( __( 'Network Admin: %s', 'smart-woocommerce-search' ), esc_html( get_current_site()->site_name ) );
			} elseif ( is_user_admin() ) {
				/* translators: %s: Name of a site */
				$admin_title = sprintf( __( 'User Dashboard: %s', 'smart-woocommerce-search' ), esc_html( get_current_site()->site_name ) );
			} else {
				$admin_title = get_bloginfo( 'name' );
			}
			/* translators: Admin screen title. 1: Admin screen name, 2: Network or site name. */
			$admin_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress', 'smart-woocommerce-search' ), $title, $admin_title );
		}

		return $admin_title;
	}
	add_filter( 'admin_title', 'ysm_change_admin_title', 10, 2 );
}

/**
 * Add plugin action links
 * @param $links
 * @return array
 */
if ( ! function_exists( 'ysm_plugin_action_links' ) ) {
	function ysm_plugin_action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=smart-search' ), __( 'Settings', 'smart-woocommerce-search' ) );

		return $links;
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ysm_plugin_action_links' );
}

if ( ! function_exists( 'ysm_gettext' ) ) {
	function ysm_gettext( $translation, $text, $domain ) {
		if ( ! $translation || $translation === $text ) {
			$translations = get_translations_for_domain( 'smart_search' );
			$fallback_translation  = $translations->translate( $text );
			if ( $fallback_translation && $fallback_translation !== $text ) {
				$translation = $fallback_translation;
			}
		}

		return $translation;
	}
	add_filter( 'gettext_smart-woocommerce-search', 'ysm_gettext', 99, 3 );
}

/**
 * Init Search
 */
Ysm_Setting::init();
Ysm_Widget_Manager::init();
Ysm_Search::init();
