<?php

namespace Tribe\Repository;

use Tribe__Repository__Query_Filters as Query_Filters;
use Tribe__Repository__Read as Read_Repository;

class ReadTest extends \Codeception\TestCase\WPTestCase {
	protected $schema = [];
	protected $query_filters;
	protected $default_args = [ 'post_type' => 'book', 'orderby' => 'ID', 'order' => 'ASC' ];

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->repository();

		$this->assertInstanceOf( Read_Repository::class, $sut );
	}

	/**
	 * @return Read_Repository
	 */
	private function repository() {
		$query_filters = $this->query_filters ?? new Query_Filters();

		return new Read_Repository( $this->schema, $query_filters, $this->default_args );
	}

	/**
	 * It should return all posts (non paginated) by default
	 *
	 * @test
	 */
	public function should_return_all_posts_by_default() {
		$ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		update_option( 'posts_per_page', 2 );

		$this->assertEquals( 5, $this->repository()->found() );
		$this->assertEquals( 5, $this->repository()->count() );
		$this->assertCount( 5, $this->repository()->all() );
		$this->assertEquals( reset( $ids ), $this->repository()->first()->ID );
		$this->assertEquals( end( $ids ), $this->repository()->last()->ID );
		$this->assertEquals( $ids[1], $this->repository()->nth( 2 )->ID );
		$this->assertEquals( $ids[2], $this->repository()->nth( 3 )->ID );
		$this->assertNull( $this->repository()->nth( 23 ) );
	}

	/**
	 * It should allow offsetting the results
	 *
	 * @test
	 */
	public function should_allow_offsetting_the_results() {
		$ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		update_option( 'posts_per_page', 2 );

		$this->assertEquals( 5, $this->repository()->offset( 2 )->found() );
		$this->assertEquals( 3, $this->repository()->offset( 2 )->count() );
		$this->assertCount( 3, $this->repository()->offset( 2 )->all() );
		$this->assertEquals( $ids[2], $this->repository()->offset( 2 )->first()->ID );
		$this->assertEquals( end( $ids ), $this->repository()->offset( 2 )->last()->ID );
		$this->assertEquals( $ids[3], $this->repository()->offset( 2 )->nth( 2 )->ID );
		$this->assertEquals( $ids[4], $this->repository()->offset( 2 )->nth( 3 )->ID );
		$this->assertNull( $this->repository()->nth( 23 ) );
	}

	/**
	 * It should allow paginating results
	 *
	 * @test
	 */
	public function should_allow_paginating_results() {
		$ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		update_option( 'posts_per_page', 2 );

		$page_1 = $this->repository()
		               ->per_page( 3 )
		               ->page( 1 );

		$this->assertEquals( 5, $page_1->found() );
		$this->assertEquals( 3, $page_1->count() );
		$this->assertCount( 3, $page_1->all() );
		$this->assertEquals( $ids[0], $page_1->first()->ID );
		$this->assertEquals( $ids[2], $page_1->last()->ID );
		$this->assertEquals( $ids[0], $page_1->nth( 1 )->ID );
		$this->assertEquals( $ids[1], $page_1->nth( 2 )->ID );
		$this->assertEquals( $ids[2], $page_1->nth( 3 )->ID );
		$this->assertNull( $page_1->nth( 4 ) );

		$page_2 = $this->repository()
		               ->per_page( 3 )
		               ->page( 2 );

		$this->assertEquals( 5, $page_2->found() );
		$this->assertEquals( 2, $page_2->count() );
		$this->assertCount( 2, $page_2->all() );
		$this->assertEquals( $ids[3], $page_2->first()->ID );
		$this->assertEquals( $ids[4], $page_2->last()->ID );
		$this->assertEquals( $ids[3], $page_2->nth( 1 )->ID );
		$this->assertEquals( $ids[4], $page_2->nth( 2 )->ID );
		$this->assertNull( $page_2->nth( 3 ) );
	}

	/**
	 * It should respect the fields setting
	 *
	 * @test
	 */
	public function should_respect_the_fields_setting() {
		$ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		update_option( 'posts_per_page', 2 );

		$page_1 = $this->repository()
		               ->per_page( 3 )
		               ->page( 1 )
		               ->fields( 'ids' );

		$this->assertEquals( 5, $page_1->found() );
		$this->assertEquals( 3, $page_1->count() );
		$this->assertCount( 3, $page_1->all() );
		$this->assertEquals( $ids[0], $page_1->first() );
		$this->assertEquals( $ids[2], $page_1->last() );
		$this->assertEquals( $ids[0], $page_1->nth( 1 ) );
		$this->assertEquals( $ids[1], $page_1->nth( 2 ) );
		$this->assertEquals( $ids[2], $page_1->nth( 3 ) );
		$this->assertNull( $page_1->nth( 4 ) );

		$page_2 = $this->repository()
		               ->per_page( 3 )
		               ->page( 2 )
		               ->fields( 'ids' );

		$this->assertEquals( 5, $page_2->found() );
		$this->assertEquals( 2, $page_2->count() );
		$this->assertCount( 2, $page_2->all() );
		$this->assertEquals( $ids[3], $page_2->first() );
		$this->assertEquals( $ids[4], $page_2->last() );
		$this->assertEquals( $ids[3], $page_2->nth( 1 ) );
		$this->assertEquals( $ids[4], $page_2->nth( 2 ) );
		$this->assertNull( $page_2->nth( 3 ) );
	}

	/**
	 * It should allow getting posts by title
	 *
	 * @test
	 */
	public function should_allow_getting_posts_by_title() {
		$titles = [ 'one', 'one two', 'one two three' ];
		$posts  = array_map( function ( $title ) {
			return $this->factory()->post->create( [ 'post_type' => 'book', 'post_title' => $title ] );
		}, $titles );

		$this->assertEquals(
			$posts[1],
			$this->repository()->fields( 'ids' )->by( 'title', 'one two' )->first()
		);
		$this->assertEquals(
			[ $posts[1], $posts[2] ],
			$this->repository()->fields( 'ids' )->by( 'title_like', 'two' )->all()
		);
		$this->assertEquals(
			$posts,
			$this->repository()->by( 'title_like', 'one' )->fields( 'ids' )->all()
		);
	}

	/**
	 * It should allow getting posts by content
	 *
	 * @test
	 */
	public function should_allow_getting_posts_by_content() {
		$contents = [ 'one', 'one two', 'one two three', 'foo bar' ];
		$posts    = array_map( function ( $content ) {
			return $this->factory()->post->create( [ 'post_type' => 'book', 'post_content' => $content ] );
		}, $contents );

		$this->assertEquals(
			[ $posts[1], $posts[2] ],
			$this->repository()->fields( 'ids' )->by( 'content', 'two' )->all()
		);
		$this->assertEquals(
			$posts[3],
			$this->repository()->fields( 'ids' )->by( 'content', 'bar' )->first()
		);
	}

	/**
	 * It should allow searching posts
	 *
	 * @test
	 */
	public function should_allow_searching_posts() {
		$ids [] = $this->factory()->post->create( [
			'post_type'    => 'book',
			'post_title'   => 'One',
			'post_content' => 'lorem'
		] );
		$ids [] = $this->factory()->post->create( [
			'post_type'    => 'book',
			'post_title'   => 'two',
			'post_content' => 'lorem one'
		] );
		$ids [] = $this->factory()->post->create( [
			'post_type'    => 'book',
			'post_title'   => 'three',
			'post_content' => 'lorem two'
		] );

		$this->assertEquals(
			[ $ids[0], $ids[1] ],
			$this->repository()->fields( 'ids' )->search( 'one' )->all()
		);
	}

	/**
	 * It should allow getting posts by meta
	 *
	 * @test
	 */
	public function should_allow_getting_posts_by_meta() {
		$post_1 = $this->factory()->post->create( [
			'post_type'  => 'book',
			'meta_input' => [
				'common'        => 'common_1',
				'number_meta'   => '1',
				'string_meta'   => 'foo',
				'interval_meta' => 'foo',
				'woot'          => 'zap',
			]
		] );
		$post_2 = $this->factory()->post->create( [
			'post_type'  => 'book',
			'meta_input' => [
				'common'        => 'common_2',
				'number_meta'   => '23',
				'string_meta'   => 'bar',
				'interval_meta' => 'bar',
			]
		] );

		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta', 'common', 'common_1' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_equals', 'common', 'common_1' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_not_equals', 'common', 'common_2' )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_gt', 'number_meta', 12 )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_greater_than', 'number_meta', 12 )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2
		], $this->repository()->fields( 'ids' )->by( 'meta_gte', 'number_meta', '1' )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2
		], $this->repository()->fields( 'ids' )->by( 'meta_greater_than_or_equal', 'number_meta', '1' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_like', 'string_meta', 'foo' )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_not_like', 'string_meta', 'foo' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_lt', 'number_meta', 12 )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_less_than', 'number_meta', '12' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_lte', 'number_meta', 1 )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_less_than_or_equal', 'number_meta', 1 )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2
		], $this->repository()->fields( 'ids' )->by( 'meta_in', 'interval_meta', [ 'foo', 'bar' ] )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_not_in', 'interval_meta', [
			'foo',
			'baz'
		] )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_between', 'number_meta', [
			18,
			25
		] )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_not_between', 'number_meta', [
			18,
			25
		] )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_exists', 'woot' )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_not_exists', 'woot' )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_regexp', 'string_meta', '^b.*' )->all() );
		$this->assertEquals( [ $post_2 ], $this->repository()->fields( 'ids' )->by( 'meta_equals_regexp', 'string_meta', '^b.*' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_not_regexp', 'string_meta', '^b.*' )->all() );
		$this->assertEquals( [ $post_1 ], $this->repository()->fields( 'ids' )->by( 'meta_not_equals_regexp', 'string_meta', '^b.*' )->all() );
	}

	/**
	 * It should allow getting posts by taxonomy terms
	 *
	 * @test
	 */
	public function should_allow_getting_posts_by_taxonomy_terms() {
		// needed to assign terms
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$fiction     = $this->factory()->term->create( [
			'taxonomy' => 'genre',
			'name'     => 'fiction',
			'slug'     => 'fict'
		] );
		$history     = $this->factory()->term->create( [
			'taxonomy' => 'genre',
			'name'     => 'history',
			'slug'     => 'hist'
		] );
		$non_fiction = $this->factory()->term->create( [
			'taxonomy' => 'genre',
			'name'     => 'non-fiction',
			'slug'     => 'non-fict'
		] );
		$post_1      = $this->factory()->post->create( [
			'post_type' => 'book',
			'tax_input' => [ 'genre' => [ 'fiction' ] ]
		] );
		$post_2      = $this->factory()->post->create( [
			'post_type' => 'book',
			'tax_input' => [ 'genre' => [ 'non-fiction', 'history' ] ]
		] );
		$post_3      = $this->factory()->post->create( [
			'post_type' => 'book',
			'tax_input' => [ 'genre' => [ 'non-fiction' ] ]
		] );
		$post_4      = $this->factory()->post->create( [ 'post_type' => 'book' ] );

		$tax = 'genre';

		$this->assertEquals( [
			$post_1,
			$post_2,
			$post_3
		], $this->repository()->fields( 'ids' )->by( 'taxonomy_exists', $tax )->all() );
		$this->assertEquals( [
			$post_4
		], $this->repository()->fields( 'ids' )->by( 'taxonomy_not_exists', $tax )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2,
		], $this->repository()->fields( 'ids' )->by( 'term_id_in', $tax, [ $fiction, $history ] )->all() );
		$this->assertEquals( [
			$post_3,
			$post_4,
		], $this->repository()->fields( 'ids' )->by( 'term_id_not_in', $tax, [ $fiction, $history ] )->all() );
		$this->assertEquals( [
			$post_2,
		], $this->repository()->fields( 'ids' )->by( 'term_id_and', $tax, [ $non_fiction, $history ] )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2,
		], $this->repository()->fields( 'ids' )->by( 'term_name_in', $tax, [ 'fiction', 'history' ] )->all() );
		$this->assertEquals( [
			$post_3,
			$post_4,
		], $this->repository()->fields( 'ids' )->by( 'term_name_not_in', $tax, [ 'fiction', 'history' ] )->all() );
		$this->assertEquals( [
			$post_2,
		], $this->repository()->fields( 'ids' )->by( 'term_name_and', $tax, [ 'non-fiction', 'history' ] )->all() );
		$this->assertEquals( [
			$post_1,
			$post_2
		], $this->repository()->fields( 'ids' )->by( 'term_slug_in', $tax, [ 'fict', 'hist' ] )->all() );
		$this->assertEquals( [
			$post_3,
			$post_4,
		], $this->repository()->fields( 'ids' )->by( 'term_slug_not_in', $tax, [ 'fict', 'hist' ] )->all() );
		$this->assertEquals( [
			$post_2,
		], $this->repository()->fields( 'ids' )->by( 'term_slug_and', $tax, [ 'non-fict', 'hist' ] )->all() );
	}

	/**
	 * It should allow selecting posts by date
	 *
	 * @test
	 */
	public function should_allow_selecting_posts_by_date() {
		$tz_string = 'Asia/Tokyo';
		update_option( 'timezone_string', $tz_string );
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$tz          = new \DateTimeZone( $tz_string );
		$a_week_ago  = new \DateTime( '-1 week', $tz );
		$two_hours_ago = new \DateTime( '-2 hours', $tz );
		$an_hour_ago = new \DateTime( '-1 hour', $tz );
		$in_a_week   = new \DateTime( '+1 week', $tz );

		// create posts using the timezone-localized `post_date`
		$past_post   = $this->factory()->post->create( [
			'post_type' => 'book',
			'post_date' => $a_week_ago->format( 'Y-m-d H:i:s' )
		] );
		$recent_post = $this->factory()->post->create( [
			'post_type' => 'book',
			'post_date' => $an_hour_ago->format( 'Y-m-d H:i:s' )
		] );
		$future_post = $this->factory()->post->create( [
			'post_type'   => 'book',
			'post_date'   => $in_a_week->format( 'Y-m-d H:i:s' ),
			'post_status' => 'future'
		] );

		$string_date = '-1 hour';
		$date        = $two_hours_ago->format( 'Y-m-d H:i:s' );
		$date_gmt    = $an_hour_ago
			->setTimezone( new \DateTimeZone( 'UTC' ) )
			->format( 'Y-m-d H:i:s' );

		codecept_debug( 'Setup: ' . json_encode( [
				'system_timezone' => date_default_timezone_get(),
				'wp_timezone'     => get_option( 'timezone_string' ),
				'past_post'       => [
					'date'     => get_post( $past_post )->post_date,
					'date_gmt' => get_post( $past_post )->post_date_gmt,
				],
				'recent_post'     => [
					'date'     => get_post( $recent_post )->post_date,
					'date_gmt' => get_post( $recent_post )->post_date_gmt,
				],
				'future_post'     => [
					'date'     => get_post( $future_post )->post_date,
					'date_gmt' => get_post( $future_post )->post_date_gmt,
				],
				'a_week_ago'      => $a_week_ago->format( 'Y-m-d H:i:s' ),
				'an_hour_ago'     => $an_hour_ago->format( 'Y-m-d H:i:s' ),
				'in_a_week'       => $in_a_week->format( 'Y-m-d H:i:s' ),
				'string_date'     => $string_date,
				'date'            => $date,
				'date_gmt'        => $date_gmt,
			], JSON_PRETTY_PRINT ) );

		$this->assertEquals( [
			$recent_post,
			$future_post,
		], $this->repository()->fields( 'ids' )->by( 'date', $date )->all() );
		$this->assertEquals( [
			$recent_post,
			$future_post,
		], $this->repository()->fields( 'ids' )->by( 'after_date', $string_date )->all() );
		$this->assertEquals( [
			$past_post,
			$recent_post,
		], $this->repository()->fields( 'ids' )->by( 'before_date', $date )->all() );

		$this->assertEquals( [
			$recent_post,
			$future_post,
		], $this->repository()->fields( 'ids' )->by( 'date_gmt', $string_date )->all() );
		$this->assertEquals( [
			$recent_post,
			$future_post,
		], $this->repository()->fields( 'ids' )->by( 'after_date_gmt', $date_gmt )->all() );
		$this->assertEquals( [
			$past_post,
			$recent_post,
		], $this->repository()->fields( 'ids' )->by( 'before_date_gmt', $string_date )->all() );
	}

	/**
	 * It should allow fetching posts by status
	 *
	 * @test
	 */
	public function should_allow_fetching_posts_by_status() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$in_a_week = date( 'Y-m-d H:i:s', strtotime( '+1 week' ) );
		$draft     = $this->factory()->post->create( [ 'post_type' => 'book', 'post_status' => 'draft' ] );
		$published = $this->factory()->post->create( [ 'post_type' => 'book' ] );
		$future    = $this->factory()->post->create( [
			'post_type'   => 'book',
			'post_date'   => $in_a_week,
			'post_status' => 'future'
		] );

		$repository = $this->repository()->fields( 'ids' );

		$this->assertEquals( [ $published ], $repository->by( 'status', 'publish' )->all() );
		$this->assertEquals( [ $published, $future ], $repository->by( 'status', [
			'publish',
			'future'
		] )->all() );
		$this->assertEquals( [ $draft, $published, $future ], $repository->by( 'status', 'any' )->all() );
	}

	public function setUp() {
		parent::setUp();
		register_post_type( 'book' );
		register_taxonomy( 'genre', 'book' );
	}
}
