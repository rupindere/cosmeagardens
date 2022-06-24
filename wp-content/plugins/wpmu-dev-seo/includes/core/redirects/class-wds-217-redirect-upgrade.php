<?php

/**
 * TODO: delete after a few versions have passed with the new redirects table
 */
class Smartcrawl_217_Redirect_Upgrade {
	const OPTION_ID = 'wds_redirects_upgraded_to_217';

	public function transform_data( $old_redirects, $old_types, $deflate ) {
		$utils = Smartcrawl_Redirect_Utils::get();
		$new_redirects = array();

		if ( $old_redirects ) {
			foreach ( $old_redirects as $old_source => $old_destination ) {
				$type = smartcrawl_get_array_value( $old_types, $old_source );
				$redirect = $utils->create_redirect_item( $old_source, $old_destination, $type );

				$new_redirects[] = $deflate
					? $redirect->deflate()
					: $redirect;
			}
		}

		return $new_redirects;
	}

	public function is_old_redirects_version( $version ) {
		return version_compare( $version, '2.16.0', '<=' );
	}

	public function upgrade_done() {
		return get_option( self::OPTION_ID, false );
	}

	public function set_upgrade_done() {
		return update_option( self::OPTION_ID, true );
	}
}