<?php

class ModelExtensionPavoblogComments extends Model {

	/**
	 * create - update comment
	 */
	public function updateComment( $args = array() ) {


		return $this->db->getLastId();
	}

	/**
	 * delete comment
	 */
	public function deleteComment( $comment_id = null ) {
		if ( ! $comment_id ) {
			trigger_error( sprintf( '%s was called. comment_id is NULL.', __FUNCTION__ ) );
		}

		$sql = "DELETE FROM " . DB_PREFIX . "pavoblog_comments WHERE comment_id = " . $comment_id;
		$this->db->query( $sql );

		return $this->db->getLastId();
	}

	/**
	 * get comments
	 */
	public function getComments( $args = array() ) {
		$args = array_merge( $args, array(
				'comment_id'	=> 0,
				'post_id'		=> 0,
				'user_id'		=> 0,
				'parent_id'		=> '',
				'approved'		=> 1,
				'order'			=> '',
				'orderby'		=> '',
				'comments_per_page'=> 10,
				'paged'			=> 0
			) );
		extract( $args );

		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_comments';
		$where = $limit = array();

		if ( $comment_id ) {
			$where[] = 'comment_id = ' . $comment_id;
		}

		if ( $post_id ) {
			$where[] = 'post_id = ' . $post_id;
		}

		// user_id
		if ( $user_id ) {
			$where[] = 'user_id = ' . $user_id;
		}

		if ( $parent_id ) {
			$where[] = 'parent_id = ' . $parent_id;
		}

		// approved
		$where[] = 'approved = ' . $approved;

		if ( $where ) {
			$where = implode( ' AND ', $where );
			$sql .= ' WHERE ' . $where;
		}

		if ( $order && $orderby ) {
			$sql .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		if ( $comments_per_page ) {
			$start = $paged * $comments_per_page;
			$sql .= ' LIMIT $start, $comments_per_page';
		}

		$query = $this->db->query( $sql );
		return $query->rows;
	}

	public function getComment( $comment_id = null ) {
		if ( ! $comment_id ) {
			trigger_error( sprintf( '%s was called. comment_id is NULL.', __FUNCTION__ ) );
		}

		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_comments WHERE comment_id = ' . $comment_id;

		$query = $this->db->query( $sql );
		return $query->rows;
	}

}
