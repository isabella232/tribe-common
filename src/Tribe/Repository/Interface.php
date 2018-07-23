<?php

/**
 * Interface Tribe__Repository__Interface
 *
 * @since TBD
 *
 */
interface Tribe__Repository__Interface
	extends Tribe__Repository__Read_Interface,
	Tribe__Repository__Update_Interface {

	const PERMISSION_EDITABLE = 'editable';
	const PERMISSION_READABLE = 'readable';

	/**
	 * Returns the current default query arguments of the repository.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_default_args();

	/**
	 * Sets the default arguments of the repository.
	 *
	 * @since TBD
	 *
	 * @param array $default_args
	 *
	 * @return mixed
	 */
	public function set_default_args( array $default_args );

	/**
	 * Sets the dynamic part of the filter tag that will be used to filter
	 * the query arguments and object.
	 *
	 * @param string $filter_name
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function filter_name( $filter_name );

	/**
	 * Sets the formatter in charge of formatting items to the correct format.
	 *
	 * @since TBD
	 *
	 * @param Tribe__Repository__Formatter_Interface $formatter
	 */
	public function set_formatter( Tribe__Repository__Formatter_Interface $formatter );


	/**
	 * Build, without initializing it, the query.
	 *
	 * @since TBD
	 *
	 * @return WP_Query
	 */
	public function build_query();
}
