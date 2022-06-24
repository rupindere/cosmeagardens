<?php

class Smartcrawl_Redirect_Utils {
	const DEFAULT_TYPE = 302;

	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_default_type() {
		$default_type = Smartcrawl_Settings::get_setting( 'redirections-code' );
		return empty( $default_type )
			? self::DEFAULT_TYPE
			: $default_type;
	}

	public function create_redirect_item( $source, $destination, $type = null, $title = '', $options = array() ) {
		$redirect_item = ( new Smartcrawl_Redirect_Item() )
			->set_destination( $this->prepare_destination( $destination ) )
			->set_type( $this->prepare_type( $type ) )
			->set_title( smartcrawl_clean( $title ) )
			->set_options( $this->prepare_options( $options ) );

		if ( $redirect_item->is_regex() ) {
			$redirect_item
				->set_source( $source )
				->set_path( 'regex' );
		} else {
			$source_normalized = $this->prepare_source( $source );
			$path_normalized = $this->source_to_path( $source_normalized );
			$redirect_item
				->set_source( $source_normalized )
				->set_path( $path_normalized );
		}

		return $redirect_item;
	}

	private function prepare_source( $source ) {
		return $this->prepare_url( $source );
	}

	private function prepare_type( $type ) {
		$default_type = $this->get_default_type();
		$type = empty( $type )
			? $default_type
			: $type;

		return intval( $type );
	}

	private function prepare_options( $options ) {
		return empty( $options ) || ! is_array( $options )
			? array()
			: smartcrawl_clean( $options );
	}

	private function remove_scheme( $url ) {
		return str_replace( array( 'http://', 'https://' ), '', $url );
	}

	public function source_to_path( $source ) {
		$path = $this->remove_scheme( $source );
		$home_url = $this->remove_scheme( home_url( '/' ) );

		if ( strpos( $path, $home_url ) === 0 ) {
			$path = str_replace( $home_url, '/', $path );
		}

		$path = parse_url( $path, PHP_URL_PATH );

		return $this->normalize_path( $path );
	}

	public function normalize_path( $path ) {
		// No slash at the end
		$path = untrailingslashit( $path );

		// Normalize case
		$path = Smartcrawl_String_Utils::lowercase( $path );

		// Encode characters
		$path = $this->encode_path( $path );

		// Always start with a slash
		return $this->enforce_starting_slash( $path );
	}

	private function encode_path( $path ) {
		$decode = [
			'/',
			':',
			'[',
			']',
			'@',
			'~',
			',',
			'(',
			')',
			';',
		];

		// URL encode everything - this converts any i10n to the proper encoding
		$path = rawurlencode( $path );

		// We also converted things we don't want encoding, such as a /. Change these back
		foreach ( $decode as $char ) {
			$path = str_replace( rawurlencode( $char ), $char, $path );
		}

		return $path;
	}

	private function prepare_destination( $destination ) {
		return $this->prepare_url( $destination );
	}

	private function enforce_starting_slash( $string ) {
		return '/' . ltrim( $string, '/' );
	}

	private function prepare_url( $url ) {
		// Trim
		$url = trim( $url );
		// Remove new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );
		// Remove control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );
		// Decode
		$url = rawurldecode( $url );

		return $this->is_url_absolute( $url )
			? $url
			: $this->enforce_starting_slash( $url );
	}

	private function is_url_absolute( $url ) {
		return strpos( $url, 'http://' ) === 0
		       || strpos( $url, 'https://' ) === 0;
	}
}