<?php
namespace YSM\Cache;

/**
 * Get cached query results
 * @param $key
 * @return bool|array
 */
function get_query_cache( $key ) {
	if ( ! empty( $key ) ) {
		$res = [];
		if ( \Ysm_Search::get_var( 'enable_transient' ) ) {
			$res = get_transient( $key );
		} else {
			$res = wp_cache_get( $key, 'site-transient' );
		}
		if ( ! empty( $res ) && is_array( $res ) && is_object( current( $res ) ) ) {
			$res = wp_list_pluck( $res, 'ID' );
		}

		return is_array( $res ) ? $res : false;
	}
	return false;
}

/**
 * Save query results in cache
 * @param $key
 * @param $res
 * @return bool
 */
function set_query_cache( $key, $res ) {
	if ( ! empty( $key ) ) {
		$queries_list = (array) wp_cache_get( 'ysm_query_list', 'site-transient' );
		if ( ! isset( $queries_list[ $key ] ) ) {
			$queries_list[ $key ] = array(
				'key'   => $key,
				'group' => 'site-transient',
			);
			if ( \Ysm_Search::get_var( 'enable_transient' ) ) {
				set_transient( 'ysm_query_list', $queries_list, MONTH_IN_SECONDS );
			} else {
				wp_cache_set( 'ysm_query_list', $queries_list, 'site-transient', MONTH_IN_SECONDS );
			}
		}
		if ( ! empty( $res ) && is_array( $res ) && is_object( current( $res ) ) ) {
			$res = wp_list_pluck( $res, 'ID' );
		}
		if ( empty( $res ) || ! is_array( $res ) ) {
			$res = array();
		}
		if ( \Ysm_Search::get_var( 'enable_transient' ) ) {
			return set_transient( $key, $res, MONTH_IN_SECONDS );
		} else {
			return wp_cache_set( $key, $res, 'site-transient', MONTH_IN_SECONDS );
		}
	}
	return false;
}

/**
 * Delete query cache
 */
function delete_query_cache() {
	$queries_list = wp_cache_get( 'ysm_query_list', 'site-transient' );
	if ( ! $queries_list ) {
		$queries_list = get_transient( 'ysm_query_list' );
	}
	if ( ! empty( $queries_list ) && is_array( $queries_list ) ) {
		foreach ( $queries_list as $query ) {
			if ( ! empty( $query['key'] ) && ! empty( $query['group'] ) ) {
				wp_cache_delete( $query['key'], $query['group'] );
				delete_transient( $query['key'] );
			}
		}
		wp_cache_delete( 'ysm_query_list', 'site-transient' );
		delete_transient( 'ysm_query_list' );
	}
}

/**
 * On post save
 *
 * @param  int $post_id The post ID being saved.
 * @return void
 */
function on_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	delete_query_cache();
}
add_action( 'save_post', __NAMESPACE__ . '\\on_save' );
