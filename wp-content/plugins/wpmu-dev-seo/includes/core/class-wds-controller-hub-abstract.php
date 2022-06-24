<?php

abstract class Smartcrawl_Controller_Hub_Abstract {

	public function json_receive_audit_data( $params = array(), $action = '' ) {

	}

	public function sync_ignores_list( $params = array(), $action = '' ) {
		return false;
	}

	public function json_sync_ignores_list( $params = array(), $action = '' ) {
	}

	public function purge_ignores_list( $params = array(), $action = '' ) {
		return false;
	}

	public function json_purge_ignores_list( $params = array(), $action = '' ) {
	}

	public function sync_extras_list( $params = array(), $action = '' ) {
		return false;
	}

	public function json_sync_extras_list( $params = array(), $action = '' ) {
	}

	public function purge_extras_list( $params = array(), $action = '' ) {
		return false;
	}

	public function json_purge_extras_list( $params = array(), $action = '' ) {
	}

	public function json_seo_summary() {
		$options = Smartcrawl_Settings::get_options();

		// Twitter cards
		$twitter_enabled = (bool) smartcrawl_get_array_value( $options, 'twitter-card-enable' );
		$card_type = smartcrawl_get_array_value( $options, 'twitter-card-type' );
		$twitter = $twitter_enabled
			? array( 'card_type' => $card_type )
			: array();

		// Pinterest
		$pinterest_status = smartcrawl_get_array_value( $options, 'pinterest-verification-status' );
		$pinterest_value = smartcrawl_get_array_value( $options, 'pinterest-verify' );
		$pinterest = empty( $pinterest_value )
			? array()
			: array(
				'status' => 'fail' === $pinterest_status ? 'unverified' : 'verified',
			);

		// URL redirects
		$redirects_table = Smartcrawl_Redirects_Database_Table::get();
		$redirects_count = $redirects_table->get_count();

		// Moz
		$moz_access_id = smartcrawl_get_array_value( $options, 'access-id' );
		$moz_secret_key = smartcrawl_get_array_value( $options, 'secret-key' );
		$moz_active = $moz_access_id && $moz_secret_key;
		$moz = array(
			'active' => $moz_active,
			'data'   => $moz_active
				? get_option( Smartcrawl_Controller_Moz_Cron::OPTION_ID, array() )
				: (object) array(),
		);

		// Robots file
		$robots_controller = Smartcrawl_Controller_Robots::get();

		// The whole advanced page can be disabled in the network settings
		$autolinks_settings = Smartcrawl_Autolinks_Settings::get_instance();
		$advanced_active = Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_AUTOLINKS );
		$autolinks_active = $this->is_active( 'autolinks' );
		$autolinks = $autolinks_active
			? array(
				'insert'  => $autolinks_settings->get_insert_options(),
				'link_to' => $autolinks_settings->get_linkto_options(),
			)
			: array();
		$advanced = $advanced_active
			? array(
				'autolinks'          => (object) $autolinks,
				'url_redirects'      => $redirects_count,
				'moz'                => $moz,
				'robots_txt_active'  => $robots_controller->robots_active(),
				'autolinking_active' => Smartcrawl_Settings::get_setting( 'autolinks' ),
			)
			: array();

		// Lighthouse reporting schedule
		$lighthouse_reporting_enabled = Smartcrawl_Lighthouse_Options::is_cron_enabled();
		$lighthouse_reporting = $lighthouse_reporting_enabled
			? array(
				'frequency'  => Smartcrawl_Lighthouse_Options::reporting_frequency(),
				'day'        => Smartcrawl_Lighthouse_Options::reporting_dow(),
				'time'       => Smartcrawl_Lighthouse_Options::reporting_tod(),
				'recipients' => count( Smartcrawl_Lighthouse_Options::email_recipients() ),
			)
			: array();
		$lighthouse = array(
			'reporting' => (object) $lighthouse_reporting,
		);

		// Crawler reporting schedule
		$seo_service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_SEO );
		$seo_report = $seo_service->get_report();
		$crawler_reporting_enabled = smartcrawl_get_array_value( $options, 'crawler-cron-enable' );
		$crawler_reporting = $crawler_reporting_enabled
			? array(
				'frequency'  => smartcrawl_get_array_value( $options, 'crawler-frequency' ),
				'day'        => smartcrawl_get_array_value( $options, 'crawler-dow' ),
				'time'       => smartcrawl_get_array_value( $options, 'crawler-tod' ),
				'recipients' => count( Smartcrawl_Sitemap_Settings::get_email_recipients() ),
			)
			: array();
		$sitemap_stats = Smartcrawl_Sitemap_Utils::get_meta_data();
		$sitemap_se_stats = get_option( Smartcrawl_Sitemap_Utils::ENGINE_NOTIFICATION_OPTION_ID );
		$sitemap = $this->is_active( 'sitemap' )
			? array(
				'url'                      => smartcrawl_get_sitemap_url(),
				'last_update'              => smartcrawl_get_array_value( $sitemap_stats, 'time' ),
				'last_google_notification' => smartcrawl_get_array_value( $sitemap_se_stats, array(
					'google',
					'time',
				) ),
				'last_bing_notification'   => smartcrawl_get_array_value( $sitemap_se_stats, array(
					'bing',
					'time',
				) ),
				'crawler'                  => array(
					'in_progress'        => $seo_report->is_in_progress(),
					'last_run_timestamp' => $seo_service->get_last_run_timestamp(),
					'reporting'          => (object) $crawler_reporting,
				),
			)
			: array();

		// Third-party import
		$import_plugins = array();
		$yoast_importer = new Smartcrawl_Yoast_Importer();
		if ( $yoast_importer->data_exists() ) {
			$import_plugins[] = 'yoast';
		}
		$aioseo = new Smartcrawl_AIOSEOP_Importer();
		if ( $aioseo->data_exists() ) {
			$import_plugins[] = 'aioseo';
		}

		$onpage_active = $this->is_active( 'onpage' );
		$onpage = $onpage_active
			? array(
				'meta'              => $this->get_titles_and_meta(),
				'static_homepage'   => get_option( 'show_on_front' ) === 'page',
				'public_post_types' => count( get_post_types( array( 'public' => true ) ) ),
			)
			: array();

		$social_active = $this->is_active( 'social' );
		$social = $social_active
			? array(
				'opengraph_active' => (bool) smartcrawl_get_array_value( $options, 'og-enable' ),
				'twitter'          => (object) $twitter,
				'pinterest'        => (object) $pinterest,
			)
			: array();

		$analysis = array();
		$analysis_model = new Smartcrawl_Model_Analysis();
		$seo_analysis_enabled = Smartcrawl_Settings::get_setting( 'analysis-seo' );
		if ( $seo_analysis_enabled ) {
			$analysis['seo'] = $analysis_model->get_overall_seo_analysis();
		}
		$readability_analysis_enabled = Smartcrawl_Settings::get_setting( 'analysis-readability' );
		if ( $readability_analysis_enabled ) {
			$analysis['readability'] = $analysis_model->get_overall_readability_analysis();
		}

		// Schema
		$schema_utils = Smartcrawl_Schema_Utils::get();
		$is_schema_type_person = $schema_utils->is_schema_type_person();
		$schema_active = ! smartcrawl_get_array_value( $options, 'disable-schema' )
		                 && Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_SCHEMA );
		$schema = $schema_active ? array(
			'org_type' => $is_schema_type_person
				? Smartcrawl_Schema_Type_Constants::TYPE_PERSON
				: Smartcrawl_Schema_Type_Constants::TYPE_ORGANIZATION,
			'org_name' => $is_schema_type_person
				? $schema_utils->get_personal_brand_name()
				: $schema_utils->get_organization_name(),
			'types'    => $this->get_schema_types(),
		) : array();

		wp_send_json_success( array(
			'onpage'     => (object) $onpage,
			'schema'     => (object) $schema,
			'social'     => (object) $social,
			'advanced'   => (object) $advanced,
			'lighthouse' => (object) $lighthouse,
			'sitemap'    => (object) $sitemap,
			'analysis'   => (object) $analysis,
			'import'     => array( 'plugins' => $import_plugins ),
		) );
	}

	public function json_run_crawl() {
		$service = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_SEO );
		$started = $service->start();

		if ( is_wp_error( $started ) ) {
			wp_send_json_error( array(
				'message' => $started->get_error_message(),
			) );
		} elseif ( ! $started ) {
			wp_send_json_error();
		} else {
			wp_send_json_success();
		}
	}

	private function get_titles_and_meta() {
		$meta = array();
		$posts_on_front = 'posts' === get_option( 'show_on_front' ) ||
		                  0 === (int) get_option( 'page_on_front' );
		$meta_title = $meta_description = $home_robots = '';

		if ( $posts_on_front ) {
			$blog_home = new Smartcrawl_Blog_Home();
			$meta_title = $blog_home->get_meta_title();
			$meta_description = $blog_home->get_meta_description();
			$home_robots = $blog_home->get_robots();
		} else {
			$page_on_front = Smartcrawl_Post_Cache::get()->get_post( (int) get_option( 'page_on_front' ) );
			if ( $page_on_front ) {
				$meta_title = $page_on_front->get_meta_title();
				$meta_description = $page_on_front->get_meta_description();
				$home_robots = $page_on_front->get_robots();
			}
		}
		$meta['home'] = array(
			'label'       => esc_html__( 'Homepage', 'wds' ),
			'title'       => $meta_title,
			'description' => $meta_description,
			'noindex'     => strpos( $home_robots, 'noindex' ) !== false,
			'nofollow'    => strpos( $home_robots, 'nofollow' ) !== false,
			'url'         => home_url(),
		);

		foreach (
			get_post_types( array(
				'public'  => true,
				'show_ui' => true,
			), 'objects' ) as $post_type
		) {
			$random_post = $this->get_random_post( $post_type->name );
			if ( ! $random_post ) {
				continue;
			}
			$smartcrawl_random_post = Smartcrawl_Post_Cache::get()->get_post( $random_post->ID );
			if ( ! $smartcrawl_random_post ) {
				continue;
			}
			$meta[ $post_type->name ] = array(
				'label'       => $post_type->label,
				'title'       => $smartcrawl_random_post->get_meta_title(),
				'description' => $smartcrawl_random_post->get_meta_description(),
				'noindex'     => $smartcrawl_random_post->is_noindex(),
				'nofollow'    => $smartcrawl_random_post->is_nofollow(),
				'url'         => $smartcrawl_random_post->get_permalink(),
			);
		}

		return $meta;
	}

	/**
	 * @param $post_type
	 *
	 * @return WP_Post|null
	 */
	private function get_random_post( $post_type ) {
		$posts = get_posts( array(
			'post_status'    => array( 'publish', 'inherit' ),
			'orderby'        => 'rand',
			'posts_per_page' => 1,
			'post_type'      => $post_type,
		) );

		return smartcrawl_get_array_value( $posts, 0 );
	}

	private function get_schema_types() {
		$schema_types = Smartcrawl_Controller_Schema_Types::get()->get_schema_types();

		return array_unique( array_column( $schema_types, 'type' ) );
	}

	private function is_active( $module ) {
		return Smartcrawl_Settings::get_setting( $module )
		       && Smartcrawl_Settings_Admin::is_tab_allowed( 'wds_' . $module );
	}

	public function apply_config( $params ) {
		if ( empty( $params->configs ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid config', 'wds' ) )
			);
		}

		$configs = json_decode( json_encode( $params->configs ), true );
		Smartcrawl_Controller_Configs::get()->apply_config( $configs );
		wp_send_json_success();
	}

	public function export_config() {
		$config = Smartcrawl_Config_Model::create_from_plugin_snapshot();
		wp_send_json_success( array(
			'configs' => $config->get_configs(),
			'strings' => $config->get_strings(),
		) );
	}

	public function json_refresh_lighthouse_report() {
		$lighthouse = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_LIGHTHOUSE );
		$lighthouse->refresh_report();
	}
}