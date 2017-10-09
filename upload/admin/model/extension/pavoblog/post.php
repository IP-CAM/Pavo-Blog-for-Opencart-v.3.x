<?php

class ModelExtensionPavoblogPost extends Model {

	/**
	 * get posts
	 */
	public function getAll( $args = array() ) {
		$args = array_merge( $args, array(
				'posts_per_page'	=> 10,
				'paged'				=> 1,
				'order'				=> 'ASC',
				'orderby'			=> 'post_id', // date_added
				'language'			=> $this->config->get( 'config_language_id' )
			) );
		extract( $args );

		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_post AS posts';
		$sql .= ' INNER JOIN ' . DB_PREFIX . 'pavoblog_post_description AS post_desc ON posts.post_id = post_desc.post_id';

		$sql .= ' WHERE post_desc.language_id = ' . $language;

		// order
		if ( $order && $orderby ) {
			switch ( $orderby ) {
				case 'post_id':
				case 'ID':
						$orderby = 'posts.post_id';
					break;
				case 'date_added':
						$orderby = 'posts.date_added';
					break;
				case 'date_modified':
						$orderby = 'posts.date_modified';
					break;
				case 'user_id':
						$orderby = 'posts.user_id';
					break;

				default:
						$orderby = 'posts.post_id';
					break;
			}
			$sql .= " ORDER BY {$orderby} {$order}";
		}

		// limit
		if ( $posts_per_page && $paged ) {
			$start = ( $paged - 1 ) * $posts_per_page;
			$sql .= " LIMIT {$start}, {$posts_per_page}";
		}

		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * get single post
	 */
	public function getPost( $post_id = null ) {
		if ( ! $post_id ) {
			trigger_error( sprintf( '%s was called. post_id is null', __FUNCTION__ ) );
		}

		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post WHERE ID = " . $post_id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * add post
	 */
	public function addPost( $args = array() ) {
		var_dump($args); die();
		$args = array_merge( $args, array(
				'ID'			=> 0,
				'title'			=> '',
				'user_id'		=> 0,
				'created_at'	=> '',
				'updated_at'	=> ''
			) );
		extract( $args );

		return $this->db->getLastId();
	}

	public function editPost( $args = array() ) {

	}

	/**
	 * delete post
	 */
	public function delete( $post_id = null ) {
		if ( ! $post_id ) {
			trigger_error( sprintf( '%s was called. post_id is NULL.', __FUNCTION__ ) );
		}

		$sql = "DELETE FROM " . DB_PREFIX . "pavoblog_post WHERE ID = " . $post_id;
		$this->db->query( $sql );

		return $this->db->getLastId();
	}

}
