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
		$sql = "$select $join WHERE $where";

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
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category AS category WHERE description.language_id = ' . $language_id;

		$query = $this->db->query( $sql );
		return $query->row;
	}

	public function getCategoryDescription( $cat_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category_description';
		$sql .= ' WHERE category_id = ' . $cat_id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * create - update category
	 */
	public function update( $args = array() ) {
		$args = array_merge( $args, array(
				'category_id' 	=> '',
				'language_id'	=> '',
				'image'			=> '',
				'parent_id'		=> '',
				'status'		=> '',
				'date_added'	=> '',
				'date_modify'	=> '',
				'category_description'	=> array()
			) );
		extract( $args );

		if ( $category_description ) {
			foreach ( $category_description as $data ) {
				$this->updateCategoryData( $data );
			}
		}
	}

	/**
	 * update category data
	 */
	public function updateCategoryData( $args = array() ) {
		$args = array_merge( $args, array(
				'category_id' 	=> '',
				'language_id'	=> '',
				'name'			=> '',
				'description'	=> '',
				'meta_title'	=> '',
				'meta_description'	=> '',
				'meta_keyword'	=> ''
			) );
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

		$this->deleteCategoryData( $cat_id );

	}

	/**
	 * delete category description
	 */
	public function deleteCategoryData( $cat_id = null ) {
		$this->db->query( 'DELETE FROM ' . DB_PREFIX . 'pavoblog_category_description WHERE category_id = ' . (int) $cat_id );
	}

}
