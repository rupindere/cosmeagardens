<?php
/**
 * Handles imports
 *
 * @package wpmu-dev-seo
 */

/**
 * Imports handling class
 */
class Smartcrawl_Import extends Smartcrawl_WorkUnit {

	/**
	 * Model instance
	 *
	 * @var Smartcrawl_Model_IO
	 */
	private $_model;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->_model = new Smartcrawl_Model_IO();
	}

	/**
	 * Loads all options
	 *
	 * @param string $json JSON model to load from.
	 *
	 * @return Smartcrawl_Import instance
	 */
	public static function load( $json ) {
		$me = new self();

		$me->load_all( $json );

		return $me;
	}

	/**
	 * Loads everything
	 *
	 * @param string $json JSON model to load from.
	 *
	 * @return Smartcrawl_Model_IO instance
	 */
	public function load_all( $json ) {
		$data = json_decode( $json, true );
		if ( empty( $data ) ) {
			return $this->_model;
		}

		$this->_model->set_version( (string) smartcrawl_get_array_value( $data, 'version' ) );
		$this->_model->set_url( (string) smartcrawl_get_array_value( $data, 'url' ) );

		foreach ( $this->_model->get_sections() as $section ) {
			if ( ! isset( $data[ $section ] ) || ! is_array( $data[ $section ] ) ) {
				continue;
			}
			$this->_model->set( $section, $data[ $section ] );
		}

		return $this->_model;
	}

	/**
	 * Handles staging area saves
	 *
	 * @return bool
	 */
	public function save() {
		$overall_status = true;

		foreach ( $this->_model->get_sections() as $section ) {
			$method = array( $this, "save_{$section}" );
			if ( ! is_callable( $method ) ) {
				continue;
			}
			$status = call_user_func( $method );

			if ( ! $status ) {
				$this->add_error( $section, __( 'Import process failed, aborting', 'wds' ) );
				$overall_status = false;
			}
			if ( ! $overall_status ) {
				break;
			}
		}

		return $overall_status;
	}

	/**
	 * Save options
	 *
	 * @return bool
	 */
	public function save_options() {
		$overall_status = true;
		foreach ( $this->_model->get( Smartcrawl_Model_IO::OPTIONS ) as $key => $value ) {
			if ( false === $value ) {
				continue;
			} // Do not force-add false values.
			if ( 'wds_blog_tabs' === $key ) {
				$old = get_site_option( $key );
				$status = update_site_option( $key, $value );
			} else {
				$status = update_option( $key, $value );
			}
//			if ( false ) {
//				$this->add_error( $key, sprintf( __( 'Failed importing options: %s', 'wds' ), $key ) );
//				$overall_status = false;
//			}
			if ( ! $overall_status ) {
				break;
			}
		}

		return $overall_status;
	}

	/**
	 * Saves ignores
	 *
	 * @return bool
	 */
	public function save_ignores() {
		if ( ! $this->is_source_same_as_destination() ) {
			return true;
		}

		$data = $this->_model->get( Smartcrawl_Model_IO::IGNORES );
		$ignores = new Smartcrawl_Model_Ignores();

		$overall_status = true;
		foreach ( $data as $key ) {
			$status = $ignores->set_ignore( $key );

			/*
			// Now, since update_(site_)option can return non-true for success, we do not do any error checking.
			if (!$status) {
				$this->add_error($key, sprintf(__('Failed importing ignores: %s', 'wds'), $key));
				$overall_status = false;
			}
			if (!$overall_status) break;
			*/
		}

		return $overall_status;
	}

	/**
	 * Saves extra URLs
	 *
	 * @return bool
	 */
	public function save_extra_urls() {
		if ( ! $this->is_source_same_as_destination() ) {
			return true;
		}

		$data = $this->_model->get( Smartcrawl_Model_IO::EXTRA_URLS );
		$result = Smartcrawl_Sitemap_Utils::set_extra_urls( $data );

		return Smartcrawl_Sitemap_Utils::get_extra_urls() === $data;
	}

	/**
	 * Saves ignored URLs
	 *
	 * @return bool
	 */
	public function save_ignore_urls() {
		if ( ! $this->is_source_same_as_destination() ) {
			return true;
		}

		$data = $this->_model->get( Smartcrawl_Model_IO::IGNORE_URLS );
		$result = Smartcrawl_Sitemap_Utils::set_ignore_urls( $data );

		return Smartcrawl_Sitemap_Utils::get_ignore_urls() === $data;
	}

	/**
	 * Saves ignored post IDs
	 *
	 * @return bool
	 */
	public function save_ignore_post_ids() {
		if ( ! $this->is_source_same_as_destination() ) {
			return true;
		}

		$data = $this->_model->get( Smartcrawl_Model_IO::IGNORE_POST_IDS );
		$result = Smartcrawl_Sitemap_Utils::set_ignore_ids( $data );

		return Smartcrawl_Sitemap_Utils::get_ignore_ids() === $data;
	}

	/**
	 * Saves postmeta entries
	 *
	 * @TODO: actually implement this.
	 *
	 * @return bool
	 */
	public function save_postmeta() {
		return true;
	}

	/**
	 * Saves taxmeta entries
	 *
	 * @TODO: actually implement this
	 *
	 * @return bool
	 */
	public function save_taxmeta() {
		return true;
	}

	/**
	 * Saves redirects
	 *
	 * @return bool
	 */
	public function save_redirects() {
		if ( ! $this->is_source_same_as_destination() ) {
			return true;
		}

		$upgrade_helper = new Smartcrawl_217_Redirect_Upgrade();
		$redirect_data = $this->_model->get( Smartcrawl_Model_IO::REDIRECTS );
		if ( $upgrade_helper->is_old_redirects_version( $this->_model->get_version() ) ) {
			$redirect_type_data = $this->_model->get( Smartcrawl_Model_IO::REDIRECT_TYPES );
			$redirects = $upgrade_helper->transform_data( $redirect_data, $redirect_type_data, false );
		} else {
			$redirects = array_map( array( 'Smartcrawl_Redirect_Item', 'inflate' ), $redirect_data );
		}

		$table = Smartcrawl_Redirects_Database_Table::get();
		$table->delete_all();
		$table->insert_redirects( $redirects );

		return true;
	}

	/**
	 * Gets filtering prefix
	 *
	 * @return string
	 */
	public function get_filter_prefix() {
		return 'wds-import';
	}

	private function is_source_same_as_destination() {
		$data = $this->_model->get_all();
		if ( empty( $data['url'] ) ) {
			return false;
		}

		return $this->normalize_url( $data['url'] ) === $this->normalize_url( home_url() );
	}

	private function normalize_url( $url ) {
		$url = str_replace( array( 'http://', 'https://', 'www.' ), '', $url );

		return untrailingslashit( $url );
	}
}