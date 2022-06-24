<?php

/**
 * @method object buddypress()
 * @method string bp_current_component()
 * @method BP_Groups_Group groups_get_current_group()
 * @method string bp_core_get_user_domain( int $user_id )
 * @method string bp_core_get_user_displayname( int $user_id )
 * @method string bp_core_get_username( int $user_id )
 * @method object bp_get_displayed_user()
 * @method string bp_get_group_name( BP_Groups_Group $group )
 * @method string bp_get_group_description( BP_Groups_Group $group )
 * @method string bp_get_group_permalink( BP_Groups_Group $group )
 * @method array groups_get_groups( array $args )
 */
class Smartcrawl_Buddypress_Api {
	public function __call( $name, $arguments ) {
		if ( function_exists( $name ) ) {
			return call_user_func_array( $name, $arguments );
		}

		return null;
	}
}