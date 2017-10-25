<?php

class ModelExtensionPavoBlogPost extends Model {

	public function getPosts( $data = array() ) {
		$data = array_merge( array(
			'start'			=> 0,
			'limit'			=> 10,
			'category_id'	=> '',
			'tags'			=> '',
			'user_id'		=> '',
			'username'		=> '',
			'featured' 		=> '',
			'orderby'		=> 'post_id',
			'order'			=> 'DESC',
			'language_id'	=> $this->config->get( 'config_language_id' ),
 			'date_added'	=> date( 'Y-m-d' ),
 			'status'		=> 1,
 			'store_id'		=> $this->config->get('config_store_id')
		), $data );
		extract( $data );

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT post.*, pdesc.*, user.username FROM " . DB_PREFIX . "pavoblog_post AS post";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_to_store AS postst ON postst.post_id = post.post_id AND postst.store_id = " . (int)$store_id;
		if ( $category_id ) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category AS cat ON ( cat.category_id = " . $this->db->escape( $category_id ) . " OR cat.parent_id = ".(int)$category_id." )";
			$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_to_category AS post2cat ON post2cat.post_id = post.post_id";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_description AS pdesc ON pdesc.post_id = post.post_id AND pdesc.language_id = " . $this->db->escape( $language_id );
		$sql .= " LEFT JOIN " . DB_PREFIX . "user as user ON user.user_id = post.user_id";

		$where = ' WHERE 1=1';
		if ( $user_id ) {
			$where .= " AND post.user_id = " . (int)$user_id;
		}
		if ( $username ){
			$sql .= " user.username = '".$this->db->escape($username)."'";
		}

		if ( $featured )
			$where .= " AND post.feauterd = " . (int)$featured;

		if ( $category_id )
			$where .= " AND post2cat.category_id = " . (int)$category_id;

		if ( $tags ) {
			$implode = array();

			$words = explode( ' ', trim( preg_replace('/\s+/', ' ', $tags ) ) );

			foreach ( $words as $word ) {
				$implode[] = "post.tag LIKE '%" . $this->db->escape($word) . "%'";
			}

			if ( $implode ) {
				$where .= " " . implode(" AND ", $implode) . "";
			}
		}

		if ( $date_added ) {
			$where .= " AND post.date_added <= '" . $this->db->escape( $date_added ) . "'";
		}

		if ( $status ) {
			$where .= " AND post.status =" .(int)$status;
		}

		$sql .= $where;
		$order = '';
		if ( $order && $orderby ) {
			$order = " ORDERBY post.{$order} $orderby";
		}

		$sql .= $order;
		$litmit = '';
		if ( $start !== '' && $limit !== '' ) {
			$limit = " LIMIT {$start}, {$limit}";
		}

		$sql .= " GROUP BY post.post_id";

		$sql .= $limit;

		$query = $this->db->query( $sql );
		return $query->rows;
 	}

	public function getTotals() {
		$query = $this->db->query( 'SELECT FOUND_ROWS()' );
		if ( $query->row && isset( $query->row['FOUND_ROWS()'] ) ) {
			return (int)$query->row['FOUND_ROWS()'];
		}
		return 0;
	}

 	/**
 	 * get post
 	 *
 	 * @param $post_id
 	 */
 	public function getPost( $post_id = null ) {
 		$language_id = $this->config->get( 'config_language_id' );
 		$store_id = $this->config->get( 'config_store_id' );
 		$sql = "SELECT post.*, user.username, user.user_id, pdesc.* FROM " . DB_PREFIX . "pavoblog_post AS post";
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_description AS pdesc ON pdesc.post_id = post.post_id AND pdesc.language_id = " . (int)$language_id;
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_to_store AS pstore ON pstore.post_id = post.post_id AND pstore.store_id = " . (int)$store_id;
 		$sql .= " LEFT JOIN " . DB_PREFIX . "user as user ON user.user_id = post.user_id";
 		$sql .= " WHERE post.post_id = " . (int)$post_id;

 		$query = $this->db->query( $sql );
 		return $query->row;
 	}

 	/**
 	 * get related posts
 	 */
 	public function getRelatedPosts( $post_id = null ) {
 		$subsql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post AS p";
 		$subsql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_to_category AS pcat ON pcat.post_id = p.post_id";
 		$subsql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_to_store AS pstore ON pstore.post_id = p.post_id";
 		$subsql .= " WHERE p.post_id = " . (int)$post_id;

 		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post AS posts, ($subsql) AS sub";
 	}

 	/**
 	 * get lastest posts
 	 */
 	public function getLastestPosts( $limit = '' ) {
 		return $this->getPosts( array(
 			'date_added'	=> date( 'Y-m-d' ),
 			'limit'			=> $limit,
 			'order'			=> 'DESC',
 			'order_by'		=> 'post_id'
 		) );
 	}

 	/**
 	 * get popular posts
 	 * order by viewed
 	 */
 	public function getPopularPosts( $limit = '' ) {
 		return $this->getPosts( array(
 			'limit'			=> $limit,
 			'order'			=> 'DESC',
 			'order_by'		=> 'viewed'
 		) );
 	}

 	/**
 	 * get categories
 	 */
 	public function getCategories( $post_id = false ) {
 		$sql = "SELECT cat.*, pdesc.name FROM " . DB_PREFIX . "pavoblog_post_to_category AS cat";
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_description AS pdesc ON pdesc.category_id = cat.category_id AND pdesc.language_id = " . (int)$this->config->get( 'config_language_id' );
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_to_store AS store ON store.category_id = cat.category_id  AND store.store_id = " . (int)$this->config->get( 'config_store_id_id' );
 		$sql .= " WHERE cat.post_id = " . (int) $post_id;

 		$query = $this->db->query( $sql );
 		$results = array();

 		foreach ( $query->rows as $row ) {
 			$row['url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/pavoblog/archive', 'pavo_cat_id=' . $row['category_id'] ) );
 			$results[] = $row;
 		}

 		return $results;
 	}

}