<?php

class Smartcrawl_Redirects_Database_Table {
	private static $_instance;

	private $version = '1.0.0';

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function create_table() {
		global $wpdb;

		$table_name = $this->get_table_name();
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( "CREATE TABLE {$table_name} (
			id bigint UNSIGNED NOT NULL auto_increment,
			title varchar(200) NOT NULL DEFAULT '',
    		source varchar(200) NOT NULL DEFAULT '',
    		path varchar(200) NOT NULL DEFAULT '',
    		destination varchar(200) NOT NULL DEFAULT '',
    		type smallint NOT NULL DEFAULT 0,
			options varchar(500) NOT NULL DEFAULT '',
		  	PRIMARY KEY  (id)
		) $collate;" );

		update_option( "{$table_name}_version", $this->version );
	}

	public function table_exists() {
		global $wpdb;
		$table_name = $this->get_table_name();
		return $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}';" );
	}

	public function delete_all() {
		global $wpdb;
		$table_name = $this->get_table_name();
		return $wpdb->query( "DELETE FROM {$table_name} WHERE 1" );
	}

	public function drop_table() {
		global $wpdb;
		$table_name = $this->get_table_name();
		return $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	public function get_table_name() {
		global $wpdb;

		return "{$wpdb->prefix}smartcrawl_redirects";
	}

	/**
	 * @param $id
	 *
	 * @return Smartcrawl_Redirect_Item|null
	 */
	public function get_redirect( $id ) {
		global $wpdb;

		$table_name = $this->get_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ) );
		return $this->map_row_to_model( $row );
	}

	/**
	 * @param $source
	 *
	 * @return Smartcrawl_Redirect_Item|null
	 */
	public function get_redirect_by_source( $source ) {
		global $wpdb;

		$table_name = $this->get_table_name();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE source = %s", $source ) );
		return $this->map_row_to_model( $row );
	}

	/**
	 * @param $source
	 *
	 * @return Smartcrawl_Redirect_Item[]|false
	 */
	public function get_redirects_by_source_regex( $source ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		$redirects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE path = 'regex' AND %s RLIKE source", $source ) );
		return $redirects
			? array_map( array( $this, 'map_row_to_model' ), $redirects )
			: false;
	}

	/**
	 * @param $path
	 *
	 * @return Smartcrawl_Redirect_Item[]|false
	 */
	public function get_redirects_by_path( $path ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		$redirects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE path = %s", $path ) );
		return $redirects
			? array_map( array( $this, 'map_row_to_model' ), $redirects )
			: false;
	}

	private function get_raw_redirects( $ids ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		$ids = implode( ',', array_filter( array_map( 'intval', $ids ) ) );
		$where = '';
		if ( $ids ) {
			$where = " WHERE id IN ({$ids})";
		}

		$redirects = $wpdb->get_results( "SELECT * FROM {$table_name}{$where}", OBJECT_K );

		return $redirects ?: false;
	}

	public function get_deflated_redirects( $ids = array() ) {
		$redirects = $this->get_raw_redirects( $ids );
		return $redirects
			? array_map( array( $this, 'map_row_to_deflated' ), $redirects )
			: false;
	}

	/**
	 * @return Smartcrawl_Redirect_Item[]|false
	 */
	public function get_redirects( $ids = array() ) {
		$redirects = $this->get_raw_redirects( $ids );
		return $redirects
			? array_map( array( $this, 'map_row_to_model' ), $redirects )
			: false;
	}

	public function delete_redirect( $id ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		return $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
	}

	public function delete_redirects( $ids ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		$ids = implode( ',', array_filter( array_map( 'intval', $ids ) ) );
		if ( ! $ids ) {
			return false;
		}
		return $wpdb->query( "DELETE FROM {$table_name} WHERE id IN ({$ids})" );
	}

	/**
	 * @param $redirect Smartcrawl_Redirect_Item
	 *
	 * @return int
	 */
	public function save_redirect( $redirect ) {
		global $wpdb;
		$table_name = $this->get_table_name();
		if ( $redirect->get_id() ) {
			$wpdb->update(
				$table_name,
				$this->map_model_to_row( $redirect ),
				array( 'id' => $redirect->get_id() ),
				$this->formats()
			);

			return $redirect->get_id();
		} else {
			$inserted = $wpdb->insert(
				$table_name,
				$this->map_model_to_row( $redirect ),
				$this->formats()
			);
			return $inserted
				? $wpdb->insert_id
				: false;
		}
	}

	public function insert_redirects( $redirects ) {
		global $wpdb;
		$table_name = $this->get_table_name();

		$values_array = array();
		foreach ( $redirects as $redirect ) {
			$values_array[] = $wpdb->prepare(
				"(%s, %s, %s, %s, %d, %s)",
				$redirect->get_title(),
				$redirect->get_source(),
				$redirect->get_path(),
				$redirect->get_destination(),
				$redirect->get_type(),
				$this->options_to_string( $redirect->get_options() )
			);
		}

		if ( empty( $values_array ) ) {
			return 0;
		}

		$values = implode( ',', $values_array );

		$query = "INSERT INTO {$table_name} (title, source, path, destination, type, options) VALUES {$values};";

		return $wpdb->query( $query );
	}

	/**
	 * @param $redirects Smartcrawl_Redirect_Item[]|false|int
	 */
	public function update_redirects( $redirects ) {
		global $wpdb;
		$table_name = $this->get_table_name();

		$values_array = array();
		foreach ( $redirects as $redirect ) {
			if ( ! $redirect->get_id() ) {
				return false;
			}

			$values_array[] = $wpdb->prepare(
				"(%d, %s, %s, %s, %s, %d, %s)",
				$redirect->get_id(),
				$redirect->get_title(),
				$redirect->get_source(),
				$redirect->get_path(),
				$redirect->get_destination(),
				$redirect->get_type(),
				$this->options_to_string( $redirect->get_options() )
			);
		}

		if ( empty( $values_array ) ) {
			return 0;
		}

		$values = implode( ',', $values_array );

		$query = "INSERT INTO {$table_name} (id, title, source, path, destination, type, options) VALUES {$values} ON DUPLICATE KEY UPDATE title = VALUES(title), source = VALUES(source), path = VALUES(path), destination = VALUES(destination), type = VALUES(type), options = VALUES(options);";

		return $wpdb->query( $query );
	}

	public function get_count() {
		global $wpdb;
		$table_name = self::get_table_name();

		return $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
	}

	private function map_row_to_deflated( $row ) {
		$model = $this->map_row_to_model( $row );
		return $model->deflate();
	}

	private function map_row_to_model( $row ) {
		if ( ! $row ) {
			return null;
		}

		return ( new Smartcrawl_Redirect_Item() )->set_id( $row->id )
		                                         ->set_title( $row->title )
		                                         ->set_source( $row->source )
		                                         ->set_path( $row->path )
		                                         ->set_destination( $row->destination )
		                                         ->set_type( $row->type )
		                                         ->set_options( $this->options_to_array( $row->options ) );
	}

	/**
	 * @param Smartcrawl_Redirect_Item $redirect
	 *
	 * @return array
	 */
	protected function map_model_to_row( Smartcrawl_Redirect_Item $redirect ) {
		return array(
			'title'       => $redirect->get_title(),
			'source'      => $redirect->get_source(),
			'path'        => $redirect->get_path(),
			'destination' => $redirect->get_destination(),
			'type'        => $redirect->get_type(),
			'options'     => $this->options_to_string( $redirect->get_options() ),
		);
	}

	private function formats() {
		return array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
		);
	}

	private function options_to_string( $array_options ) {
		if ( empty( $array_options ) ) {
			return '';
		}

		return implode( '|', $array_options );
	}

	private function options_to_array( $string_options ) {
		if ( empty( $string_options ) ) {
			return array();
		}

		return explode( '|', $string_options );
	}
}