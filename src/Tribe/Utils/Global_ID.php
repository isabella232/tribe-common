<?php
class Tribe__Utils__Global_ID {

	/**
	 * Type of the ID
	 * @var string|bool
	 */
	private $type = false;

	/**
	 * Origin of this Instance of ID
	 * @var string|bool
	 */
	private $origin = false;


	/**
	 * Dont allow creation of Global IDs for other types of source
	 * @var array
	 */
	private $valid_types = array(
		'url',
		'meetup',
		'facebook',
		'eventbrite',
	);

	/**
	 * For some types of ID we have a predefined Origin
	 * @var array
	 */
	private $type_origins = array(
		'meetup' => 'meetup.com',
		'facebook' => 'facebook.com',
		'eventbrite' => 'eventbrite.com',
	);

	/**
	 * A setter and getter for the Type of ID
	 *
	 * @param  string|null  $name  When null is passed it will return the current Type
	 * @return mixed               Will return False on invalid type or the Type in String
	 */
	public function type( $name = null ) {
		if ( is_null( $name ) ) {
			return $this->type;
		}

		$name = strtolower( $name );

		if ( ! in_array( $name, $this->valid_types ) ) {
			return false;
		}

		$this->type = $name;

		return $this->type;
	}

	/**
	 * A setter and getter for the origin on this ID
	 *
	 * @param  string|null  $name  When null is passed it will return the current Origin
	 * @return mixed               Will return False on invalid origin or the Origin in String
	 */
	public function origin( $url = null ) {
		if ( ! empty( $this->type_origins[ $this->type ] ) ) {
			$this->origin = $this->type_origins[ $this->type ];
		}

		if ( is_null( $url ) ) {
			return $this->origin;
		}

		$parts = wp_parse_url( $url );

		if ( ! $parts ) {
			return false;
		}

		$this->origin = $parts['host'];

		if ( ! empty( $parts['path'] ) ) {
			$this->origin .= $parts['path'];
		}

		if ( ! empty( $parts['query'] ) ) {
			$this->origin .= '?' . $parts['query'];
		}

		return $this->origin;
	}

	/**
	 * A very simple Generation of IDs
	 *
	 * @param  array  $args Which query arguments will be added to the Origin
	 *
	 * @return string
	 */
	public function generate( array $args = array() ) {
		// We can't do this without type or origin
		if ( ! $this->type() || ! $this->origin() ) {
			return false;
		}

		return add_query_arg( $args, $this->origin() );
	}

	/**
	 * Parse the Global ID string.
	 *
	 * @param string $global_id The previously generated global ID string.
	 *
	 * @return array The parsed $args information built by self::generate()
	 *
	 * @since TBD
	 */
	public function parse( string $global_id ) {
		$parsed_global_id = null;

		if ( $global_id ) {
			$parsed = wp_parse_url( 'http://' . $global_id );

			if ( ! empty( $parsed['query'] ) ) {
				$parsed_query = [];

				wp_parse_str( $parsed['query'], $parsed_query );

				if ( ! empty( $parsed_query ) ) {
					$parsed_global_id = $parsed_query;
				}
			}
		}

		return $parsed_global_id;
	}
}
