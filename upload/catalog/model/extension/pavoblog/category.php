<?php

class ModelExtensionPavoBlogCategory extends Model {

	public function getCategories( $data = array() ) {
		$data = array_merge( array(
			'start'		=> 0,
			'limit'		=> 10,
			'orderby'	=> 'category_id',
			'order'		=> 'DESC',
			'language_id'	=> $this->config->get( 'config_language_id' ),
			'store_id'		=> $this->config->get( 'config_store_id' )
		), $data );
		extract( $data );

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM " . DB_PREFIX . "pavoblog_category AS cat";
		if ( $store_id ) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_to_store AS catSt ON catSt.category_id = cat.category_id AND catSt.store_id = " . (int)$store_id;
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_description AS catdesc ON catdesc.category_id = post.category_id AND catdesc.language_id = " . $this->db->escape( $language_id );

		$where = ' WHERE 1=1';

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

 	/**
 	 * select single category
 	 */
 	public function getCategory( $category_id = null ) {
 		$language_id = $this->config->get( 'config_language_id' );
 		$store_id = $this->config->get( 'config_store_id' );

 		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_category AS cat";
 		$sql .= " INNER JOIN " . DB_PREFIX . "pavoblog_post_description AS catdesc ON catdesc.category_id = cat.category_id AND catdesc.language_id = " . (int)$category_id;
 		$sql .= " INNER JOIN " . DB_PREFIX . "pavoblog_category_to_store AS catSt ON catSt.store_id = " . (int)$store_id;
 		$sql .= " WHERE cat.category_id = " . (int) $category_id;

 		$query = $this->db->query( $sql );
 		return $query->row;
 	}

}