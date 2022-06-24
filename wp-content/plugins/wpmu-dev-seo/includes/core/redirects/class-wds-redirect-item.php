<?php

class Smartcrawl_Redirect_Item {
	/**
	 * @var int
	 */
	private $id = 0;
	/**
	 * @var string
	 */
	private $title = '';
	/**
	 * @var string
	 */
	private $source = '';
	/**
	 * @var string
	 */
	private $path = '';
	/**
	 * @var string
	 */
	private $destination = '';
	/**
	 * @var int
	 */
	private $type = 0;
	/**
	 * @var array
	 */
	private $options = '';

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_id( $id ) {
		$this->id = (int) $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_title( $title ) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_source() {
		return $this->source;
	}

	/**
	 * @param string $source
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_source( $source ) {
		$this->source = $source;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * @param string $path
	 */
	public function set_path( $path ) {
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_destination() {
		return $this->destination;
	}

	public function get_absolute_destination() {
		$destination = $this->get_destination();
		if ( strpos( $destination, '/' ) === 0 ) {
			return home_url( $destination );
		}

		return $destination;
	}

	/**
	 * @param string $destination
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_destination( $destination ) {
		$this->destination = $destination;
		return $this;
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @param array $options
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_options( $options ) {
		$this->options = empty( $options ) || ! is_array( $options )
			? array()
			: $options;

		return $this;
	}

	public function is_regex() {
		return array_search( 'regex', $this->get_options() ) !== false;
	}

	/**
	 * @return int
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * @param int $type
	 *
	 * @return Smartcrawl_Redirect_Item
	 */
	public function set_type( $type ) {
		$this->type = (int) $type;
		return $this;
	}

	public function deflate() {
		return array(
			'id'          => $this->id,
			'title'       => $this->title,
			'source'      => $this->source,
			'path'        => $this->path,
			'destination' => $this->destination,
			'type'        => $this->type,
			'options'     => $this->options,
		);
	}

	public static function inflate( $data ) {
		return ( new self() )
			->set_id( (int) smartcrawl_get_array_value( $data, 'id' ) )
			->set_title( smartcrawl_clean( smartcrawl_get_array_value( $data, 'title' ) ) )
			->set_source( smartcrawl_clean( smartcrawl_get_array_value( $data, 'source' ) ) )
			->set_path( smartcrawl_clean( smartcrawl_get_array_value( $data, 'path' ) ) )
			->set_destination( smartcrawl_clean( smartcrawl_get_array_value( $data, 'destination' ) ) )
			->set_options( smartcrawl_clean( smartcrawl_get_array_value( $data, 'options' ) ) )
			->set_type( (int) smartcrawl_get_array_value( $data, 'type' ) );
	}
}