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
		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post WHERE post_id = " . $post_id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	public function getPostData( $post_id = null ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post_description WHERE post_id =" . $post_id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * add post
	 */
	public function addPost( $data = array() ) {
		$data = array_merge( $args, array(
				'post_id'			=> 0,
				'name'				=> '',
				'image'				=> '',
				'user_id'			=> 1,
				'description'		=> '',
				'content'			=> '',
				'tag'				=> '',
				'date_added'		=> '',
				'dated_modifed'		=> '',
				'post_data'			=> array(),
				'post_seo_url'		=> array(),
				'post_store'	=> array()
			) );

		extract( $data );
		$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_post (`image`, `viewed`, `status`, `featured`, `user_id`, `date_added`, `date_modified`)";
		$sql .= " VALUES ( '". $this->db->escape( $image ) ."', '".(int)$viewed."', '".(int)$status."', '".(int)$featured."', '".(int)$user_id."', NOW(), NOW() )";

		$this->db->query( $sql );
		$post_id = $this->db->getLastId();

		if ( $post_id ) {
			foreach ( $post_data as $language_id => $data ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_description SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape( $name ) . "', description = '" . $this->db->escape( $description ) . "', content = '" . $this->db->escape( $content ) . "', tag = '" . $this->db->escape( $tag ) . "', meta_title = '" . $this->db->escape( $meta_title ) . "', meta_description = '" . $this->db->escape( $meta_description ) . "', meta_keyword = '" . $this->db->escape( $meta_keyword ) . "'");
			}
		}

		if ( $post_store ) {
			foreach ( $post_store as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_store SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if ( $post_seo_url ) {
			foreach ( $post_seo_url as $store_id => $language ) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_post');

		return $post_id;
	}

	public function editPost( $post_id = null, $data = array() ) {
		$data = array_merge( array(
				'post_id'			=> 0,
				'name'				=> '',
				'image'				=> '',
				'user_id'			=> 1,
				'description'		=> '',
				'content'			=> '',
				'tag'				=> '',
				'date_added'		=> '',
				'dated_modifed'		=> '',
				'post_data'			=> array(),
				'post_seo_url'		=> array(),
				'post_store'		=> array()
			), $data );

		extract( $data );

		$sql = "UPDATE " . DB_PREFIX . "pavoblog_post SET `image` = '".$image."', `viewd` = '".$viewd."', `status` = '".$status."', `featured` = '".$featured."', description = '" . $this->db->escape( $description ) . "', content = '" . $this->db->escape( $content ) . "', `tag` = '".$this->db->escape( $tag )."' , `user_id` = '".$user_id."', `date_added` = '".$date_added."', `date_modified` = NOW() WHERE post_id = '".$post_id."'";
		// excute query
		$this->db->query( $sql );

		// category description
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_post_description WHERE post_id = '" . (int)$post_id . "'");
		// category data
		foreach ( $data['post_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_description SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', content = '" . $this->db->escape($value['content']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// category to store
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_post_to_store WHERE post_id = '" . (int)$post_id . "'");
		if (isset($data['post_store'])) {
			foreach ( $data['post_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_store SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_post_id" . (int)$post_id . "'");
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_post');

		return $post_id;
	}

	/**
	 * delete post
	 */
	public function delete( $post_id = null ) {
		$sql = "DELETE FROM " . DB_PREFIX . "pavoblog_post WHERE ID = " . $post_id;
		$this->db->query( $sql );

		return $this->db->getLastId();
	}

}
