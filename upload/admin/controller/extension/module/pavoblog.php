<?php

class ControllerExtensionModulePavoBlog extends Controller {

	/**
	 * data
	 */
	private $data = array();

	// errors storge
	private $errors = array();

	/**
	 * posts list
	 */
	public function posts() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/posts', 'token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// posts
		$this->data['posts'] = $this->model_extension_pavoblog_post->getAll( array(

			) );

		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'posts_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/posts', $this->data ) );
	}

	/**
	 * post view - edit
	 */
	public function post() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );

		// languages
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/posts', 'token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		$this->data['post_id'] = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : 0;
		// posts
		$this->data['post'] = $this->data['post_id'] ? $this->model_extension_pavoblog_post->get( $this->data['post_id'] ) : array();
		$this->data['image'] = isset( $this->data['post']['image'] ) ? $this->data['post']['image'] : HTTPS_CATALOG . 'image/cache/catalog/opencart-logo-100x100.png';

		// enqueue scripts, stylesheet needed to display editor
		$this->document->addScript( 'view/javascript/summernote/summernote.js' );
		$this->document->addScript( 'view/javascript/summernote/opencart.js' );
		$this->document->addStyle( 'view/javascript/summernote/summernote.css' );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'post_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/post', $this->data ) );
	}

	/**
	 * categories
	 */
	public function categories() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/category' );

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_categories_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/categories', 'token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// comments
   		$this->data['comments'] = $this->model_extension_pavoblog_category->getAll();

		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'categories_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/categories', $this->data ) );
	}

	/**
	 * comments list
	 */
	public function comments() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// comments
   		$this->data['comments'] = $this->model_extension_pavoblog_comment->getAll();

		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'comments_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/comments', $this->data ) );
	}

	/**
	 * view - edit comment
	 */
	public function comment() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		$this->data['comment_id'] = ! empty( $_REQUEST['comment_id'] ) ? (int) $_REQUEST['comment_id'] : 0;
		// comments
   		$this->data['comment'] = $this->data['comment_id'] ? $this->model_extension_pavoblog_comment->get( $this->data['comment_id'] ) : array();

		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'comments_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/comment', $this->data ) );
	}

	/**
	 * pavo blog settings
	 */
	public function settings() {

	}

	protected function validate( $type = 'modify', $route = '' ) {
		if ( ! $this->user->hasPermission( $type, $route )) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return ! $this->error;
	}

	/**
	 * install actions
	 * create new permission and tables
	 */
	public function install() {
		// START ADD USER PERMISSION
		$this->load->model('user/user_group');
		// access - modify pavoblog edit
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/settings' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/settings' );
		// access - modify pavoblog posts
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/posts' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/post' );
		// categories
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/categories' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/category' );
		// comments
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/comments' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/comment' );
		// END ADD USER PERMISSION

		// CREATE TABLES
		// posts, comments, categories
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post` (
				`post_id` int(11) NOT NULL AUTO_INCREMENT,
				`image` varchar(255) DEFAULT NULL,
				`viewed` int(5) NOT NULL DEFAULT '0',
				`status` tinyint(1) NOT NULL,
				`featured` tinyint(1) NOT NULL,
				`user_id` int(11) NOT NULL,
				`date_added` datetime NOT NULL,
				`date_modified` datetime NOT NULL,
				PRIMARY KEY (`post_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// post description
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_description` (
				`post_id` int(11) NOT NULL,
				`language_id` int(11) NOT NULL,
				`title` varchar(255) NOT NULL,
				`description` text NOT NULL,
				`content` text NOT NULL,
				`tag` text NOT NULL,
				`meta_title` varchar(255) NOT NULL,
				`meta_description` varchar(255) NOT NULL,
				`meta_keyword` varchar(255) NOT NULL,
				PRIMARY KEY (`post_id`,`language_id`),
				KEY `title` (`title`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// blog category
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category` (
			  `category_id` int(11) NOT NULL AUTO_INCREMENT,
			  `image` varchar(255) DEFAULT NULL,
			  `parent_id` int(11) NOT NULL DEFAULT '0',
			  `status` tinyint(1) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  PRIMARY KEY (`category_id`),
			  KEY `parent_id` (`parent_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// category description
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category_description` (
			  `category_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  `meta_title` varchar(255) NOT NULL,
			  `meta_description` varchar(255) NOT NULL,
			  `meta_keyword` varchar(255) NOT NULL,
			  PRIMARY KEY (`category_id`,`language_id`),
			  KEY `name` (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// comment table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_comment` (
			  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
			  `email` varchar(96) NOT NULL,
			  `post_id` int(11) NOT NULL,
			  `user_id` int(11) NOT NULL DEFAULT '0',
			  `author` varchar(64) NOT NULL,
			  `text` text NOT NULL,
			  `rating` int(1) NOT NULL,
			  `status` tinyint(1) NOT NULL DEFAULT '0',
			  `parent_id` int(11) NOT NULL DEFAULT '0',
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  PRIMARY KEY (`comment_id`),
			  KEY `post_id` (`post_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
	}

	/**
	 * uninstall actions 
	 * remove user permission
	 */
	public function uninstall() {
		// START REMOVE USER PERMISSION
		$this->load->model('user/user_group');
		// access - modify pavoblog edit
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/settings' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/settings' );
		// access - modify pavoblog posts
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/posts' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/post' );
		// categories
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/categories' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/category' );
		// comments
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/comments' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/comment' );
		// END REMOVE USER PERMISSION
	}
}