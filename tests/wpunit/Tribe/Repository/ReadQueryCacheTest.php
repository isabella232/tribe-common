<?php

namespace Tribe\Repository;

require_once __DIR__ . '/ReadTestBase.php';

class ReadQueryCacheTest extends ReadTestBase {

	/**
	 * It should use the last built query when requesting for found or count after a fetch.
	 *
	 * @test
	 */
	public function should_use_the_last_built_query_when_requesting_for_found_or_count() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$repository->get_ids();

		$this->queries()->assertCountQueries( $start_count + 1 );
		$fetch_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $fetch_query );

		$repository->found();

		$found_query = $repository->get_last_built_query();
		$this->assertSame( $fetch_query, $found_query );
		$this->queries()->assertCountQueries( $start_count + 1 );

		$repository->count();

		$count_query = $repository->get_last_built_query();
		$this->assertSame( $fetch_query, $count_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should not requery when asking for posts a second time
	 *
	 * @test
	 */
	public function should_not_requery_when_asking_for_posts_a_second_time() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$repository->all();

		$this->queries()->assertCountQueries( $start_count + 1 );
		$first_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $first_query );

		$repository->all();

		$second_query = $repository->get_last_built_query();
		$this->assertSame( $first_query, $second_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should requery when asking for posts a second time after flush
	 *
	 * @test
	 */
	public function should_requery_when_asking_for_posts_a_second_time_after_flush() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$repository->all();

		$this->queries()->assertCountQueries( $start_count + 1 );
		$first_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $first_query );

		$repository->flush();

		$repository->all();

		$second_query = $repository->get_last_built_query();
		$this->assertNotSame( $first_query, $second_query );
		$this->queries()->assertCountQueries( $start_count + 2 );
	}

	/**
	 * It should not rerun the query when getting all after ids
	 *
	 * @test
	 */
	public function should_not_rerun_the_query_when_getting_all_after_ids() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$repository->get_ids();

		$this->queries()->assertCountQueries( $start_count + 1 );
		$first_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $first_query );

		$posts = $repository->all();

		$this->assertContainsOnlyInstancesOf( \WP_Post::class, $posts );
		$second_query = $repository->get_last_built_query();
		$this->assertSame( $first_query, $second_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should not rerun the query when getting ids after posts
	 *
	 * @test
	 */
	public function should_not_rerun_the_query_when_getting_ids_after_posts() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$posts = $repository->all();

		$this->assertContainsOnlyInstancesOf( \WP_Post::class, $posts );
		$this->queries()->assertCountQueries( $start_count + 1 );
		$first_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $first_query );

		$ids = $repository->get_ids();

		$this->assertEquals( $books, $ids );
		$second_query = $repository->get_last_built_query();
		$this->assertSame( $first_query, $second_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should not requery when getting ids after ids
	 *
	 * @test
	 */
	public function should_not_requery_when_getting_ids_after_ids() {
		$books = self::factory()->post->create_many( 2, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$ids = $repository->get_ids();

		$this->assertEquals( $books, $ids );
		$this->queries()->assertCountQueries( $start_count + 1 );
		$first_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $first_query );

		$ids = $repository->get_ids();

		$this->assertEquals( $books, $ids );
		$second_query = $repository->get_last_built_query();
		$this->assertSame( $first_query, $second_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should not requery when fetching with pick method after ids
	 *
	 * @test
	 * @dataProvider pick_methods
	 */
	public function should_not_requery_when_fetching_with_pick_method_after_ids(
		$method,
		$compare_index,
		$args = null
	) {
		$books = self::factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$ids = $repository->get_ids();

		$this->assertEquals( $books, $ids );
		$this->queries()->assertCountQueries( $start_count + 1 );
		$original_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $original_query );

		if ( empty( $args ) ) {
			$fetched = $repository->{$method}();
		} else {
			$fetched = $repository->{$method}( ...$args );
		}

		if ( ! is_array( $compare_index ) ) {
			$this->assertEquals( $ids[ $compare_index ], $fetched );
		} else {
			$this->assertEquals(
				array_intersect_key( $ids, array_combine( $compare_index, $compare_index ) ),
				$fetched
			);
		}
		$pick_query = $repository->get_last_built_query();
		$this->assertSame( $original_query, $pick_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}

	/**
	 * It should not requery when fetching with pick method after posts
	 *
	 * @test
	 * @dataProvider pick_methods
	 */
	public function should_not_requery_when_fetching_with_pick_method_after_posts(
		$method,
		$compare_index,
		$args = null
	) {
		$books = self::factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		global $wpdb;
		$start_count = $this->queries()->countQueries();
		$repository = $this->repository();

		$this->assertNull( $repository->get_last_built_query() );

		$posts = $repository->all();

		$this->assertContainsOnlyInstancesOf( \WP_Post::class, $posts );
		$this->queries()->assertCountQueries( $start_count + 1 );
		$original_query = $repository->get_last_built_query();
		$this->assertInstanceOf( \WP_Query::class, $original_query );

		if ( empty( $args ) ) {
			$fetched = $repository->{$method}();
		} else {
			$fetched = $repository->{$method}( ...$args );
		}

		if ( ! is_array( $compare_index ) ) {
			$this->assertEquals( $posts[ $compare_index ], $fetched );
		} else {
			$this->assertEquals(
				array_intersect_key( $posts, array_combine( $compare_index, $compare_index ) ),
				$fetched
			);
		}
		$pick_query = $repository->get_last_built_query();
		$this->assertSame( $original_query, $pick_query );
		$this->queries()->assertCountQueries( $start_count + 1 );
	}


	public function pick_methods() {
		return [
			'first' => [ 'first', 0 ],
			'last'  => [ 'last', 4 ],
			'nth'   => [ 'nth', 3, [ 4 ] ],
			'take'   => [ 'take', [0,1,2], [ 3] ],
		];
	}

}
