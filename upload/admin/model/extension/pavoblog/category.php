<?php

class ModelExtensionPavoblogCategory extends Model {

	/**
	 * get all categories with args
	 */
	public function getAll( $args = array() ) {
		$args = array_merge( $args, array(
				'parent_id'		=> '',
				'status'		=> '',
				'order'			=> 'ASC',
				'orderby'		=> 'category_id',
				'categories_per_page'	=> 10,
				'paged'			=> 1
			) );

		extract( $args );

		$select = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category AS category';
		$join = $where = array();

		if ( $parent_id ) {
			$where[] = 'category.parent_id = ' . $parent_id;
		}

		if ( $status != '' ) {
			$where[] = ' category.status = ' . $status;
		}

		$join = implode( '', $join );
		$where = implode( 'AND ', $where );
		$sql = "$select $join";
		if ( $where ) {
			$sql .= ' WHERE ' . $where;
		}

		if ( $order && $orderby ) {
			$sql .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		if ( $categories_per_page != -1 ) {
			$start = ( $paged - 1 ) * $categories_per_page;
			$sql .= ' LIMIT ' . $start . ', ' . $categories_per_page;
		}

		$query = $this->db->query( $sql );

		return $query->rows;
	}

	/**
	 * get single category
	 * @param $category_id
	 */
	public function get( $category_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category AS category WHERE category_id = ' . $category_id;
		$query = $this->db->query( $sql );
		return $query->row;
	}

	public function getCategoryDescription( $category_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category_description WHERE category_id = ' . $category_id;

		$results = array();
		$query = $this->db->query( $sql );
		foreach ( $query->rows as $result) {
			$results[$result['language_id'] ] = $result;
		}

		return $results;
	}

	/**
	 * create - update category
	 */
	public function add( $data = array() ) {
		$params = array(
			'image' 	=> ! empty( $data['image'] ) 		? $this->db->escape( $data['image'] ) : '',
			'parent_id' => ! empty( $data['parent_id'] ) 	? (int)$data['parent_id'] : 0,
			'column' 	=> ! empty( $data['column'] ) 		? (int)$data['column'] : 1,
			'status'	=> ! empty( $data['status'] ) 		? (int)$data['status'] : 1,
		);
		$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_category ( `image`, `parent_id`, `column`, `status`, `date_added`, `date_modified` )";
		$sql .= " VALUES ( '".$params['image']."', '".$params['parent_id']."', '".$params['column']."', '".$params['status']."', NOW(), NOW() )";

		$this->db->query( $sql );
		// category id
		$category_id = $this->db->getLastId();

		// category data
		foreach ( $data['category_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['category_store'])) {
			foreach ( $data['category_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_cat_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_category');

		return $category_id;
	}

	/**
	 * edit category
	 */
	public function edit( $category_id, $data = array() ) {
		$params = array(
			'image' 	=> ! empty( $data['image'] ) 		? $this->db->escape( $data['image'] ) : '',
			'parent_id' => ! empty( $data['parent_id'] ) 	? (int)$data['parent_id'] : 0,
			'column' 	=> ! empty( $data['column'] ) 		? (int)$data['column'] : 1,
			'status'	=> ! empty( $data['status'] ) 		? (int)$data['status'] : 1,
		);
		$sql = "UPDATE " . DB_PREFIX . "pavoblog_category SET `image` = '".$params['image']."', `parent_id` = '".$params['parent_id']."', `column` = '".$params['column']."', `status` = '".$params['status']."', `date_modified` = NOW() WHERE category_id = '".$category_id."'";
		// excute query
		$this->db->query( $sql );

		// category description
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_category_description WHERE category_id = '" . (int)$category_id . "'");
		// category data
		foreach ( $data['category_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// category to store
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_category_to_store WHERE category_id = '" . (int)$category_id . "'");
		if (isset($data['category_store'])) {
			foreach ( $data['category_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_cat_id" . (int)$category_id . "'");
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_cat_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_category');

		return $category_id;
	}

	/**
	 * delete category
	 */
	public function delete( $cat_id = null ) {
		$this->db->query( "DELETE FROM " . DB_PREFIX . 'pavoblog_category WHERE category_id = ' . (int)$cat_id );
		$query = $this->db->query( 'SELECT category_id FROM ' . DB_PREFIX . 'pavoblog_category WHERE parent_id = ' . (int)$cat_id );
		if ( $query->cols ) {
			foreach ( $query->cols as $cat_id ) {
				$this->delete( $cat_id );
			}
		}

		$this->db->query( 'DELETE FROM ' . DB_PREFIX . 'pavoblog_category_description WHERE category_id = ' . (int) $cat_id );

	}

}
