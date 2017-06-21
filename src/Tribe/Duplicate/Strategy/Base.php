<?php

/**
 * Class Tribe__Duplicate__Strategy__Base
 *
 * The common root for duplicate finding strategies.
 *
 * @since TBD
 */
abstract class Tribe__Duplicate__Strategy__Base {

	/**
	 * Whether the key identifies a numerice post field or not.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @since TBD
	 */
	protected function is_a_numeric_post_field( $key ) {
		return in_array( $key, array( 'ID', 'post_author', 'post_parent', 'menu_order', 'comment_count' ) );
	}
}