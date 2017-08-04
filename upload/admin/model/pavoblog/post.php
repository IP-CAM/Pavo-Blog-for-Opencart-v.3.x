<?php

class ModelPavoblogPost extends Model {

	/**
	 * get posts
	 */
	public function getPosts( $args = array() ) {
		$args = array_merge( $args, array(
				'posts_per_page'	=> 10,
				'paged'				=> 1,
				'order'				=> 'ASC',
				'orderby'			=> 'ID'
			) );
		extract( $args );

		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_posts";

		if ( $posts_per_page && $paged ) {
			$start = 0;
			$
		}

		if ( $order && $orderby ) {
			// $sql .= " LIMIT ";
		}

		$query = $this->db->query( $sql );
		return $query->rows;
	}

}
