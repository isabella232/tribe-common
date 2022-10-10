<?php

namespace Tribe\Repository;

require_once __DIR__ . '/ReadTestBase.php';

class ReadRelationshipsTest extends ReadTestBase {

	/**
	 * It should allow filtering posts by related post meta fields
	 *
	 * @test
	 */
	public function should_allow_querying_by_not_related_to_meta() {
		$book_ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'book' ] );
		$review_ids = $this->factory()->post->create_many( 5, [ 'post_type' => 'review' ] );

		// Creates 2 reviews for book[4]
		update_post_meta( $review_ids[0], 'book_id', $book_ids[4] );
		update_post_meta( $review_ids[1], 'book_id', $book_ids[4] );
		// Creates 1 reviews for book[3]
		update_post_meta( $review_ids[2], 'book_id', $book_ids[3] );
		// Creates 1 reviews for book[2]
		update_post_meta( $review_ids[3], 'book_id', $book_ids[2] );
		// Create 1 review for a non-existing book
		update_post_meta( $review_ids[4], 'book_id', rand( 1000, PHP_INT_MAX ) );
		// books 0 and 1 do not have reviews.

		$repository = $this->repository();
		$not_related_ids = $repository->by_args( [ 'post_type' => 'book' ] )->by_not_related_to( [ 'book_id' ] )->get_ids();

		$this->assertEquals( 2, count( $not_related_ids ) );
		$this->assertEqualSets( [ $book_ids[0], $book_ids[1] ], $not_related_ids );
	}
}