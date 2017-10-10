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
	public function index() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/posts', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
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
		$this->load->model( 'user/user' );

		$post_id = isset( $this->request->get['post_id'] ) ? $this->request->get['post_id'] : 0;
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePostForm() ) {
			if ( $post_id ) {
				$this->model_extension_pavoblog_post->editPost( $post_id, $this->request->post );
			} else {
				$post_id = $this->model_extension_pavoblog_post->addPost( $this->request->post );
			}

			$this->session->data['success'] = $this->language->get( 'text_success' );
			if ( $post_id ) {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/post', 'post_id=' . $post_id . '&user_token=' . $this->session->data['user_token'], true ) ); exit();
			} else {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) ); exit();
			}
		}

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}
		// languages
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['users'] = array();
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get( 'text_default' )
		);

		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$this->load->model( 'tool/image' );
		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' 		=> $this->language->get( 'text_home' ),
			'href' 		=> $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/index', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// posts
		$this->data['post'] = $post_id ? $this->model_extension_pavoblog_post->getPost( $post_id ) : array();
		$this->data['post']['date_added'] = isset( $this->data['post']['date_added'] ) ? date( 'Y-m-d', strtotime( $this->data['post']['date_added'] ) ) : date( 'Y-m-d' );
		$this->data['post']['thumb'] = ! empty( $this->data['post']['image'] ) ? $this->model_tool_image->resize( $this->data['post']['image'], 100, 100 ) : $this->model_tool_image->resize( 'no_image.png', 100, 100);
		if ( ! isset( $this->data['post']['user_id'] ) ) {
			$this->data['post']['user_id'] = $this->session->data['user_id'];
		}

		$this->data['post_data'] = array();
		if ( ! empty( $this->request->post['post_data'] ) ) {
			$this->data['post_data'] = $this->request->post['post_data'];
		} else if ( $post_id ) {
			$this->data['post_data'] = $this->model_extension_pavoblog_post->getPostData( $post_id );
		}

		$this->data['post_store'] = array();
		if ( ! empty( $this->request->post['post_store'] ) ) {
			$this->data['post_store'] = $this->request->post['post_store'];
		} else if ( $post_id ) {
			$this->data['post_store'] = $post_id ? $this->model_extension_pavoblog_post->getPostStore( $post_id ) : array();
		}

		$this->data['post_seo_url'] = array();
		if ( ! empty( $this->request->post['post_seo_url'] ) ) {
			$this->data['post_seo_url'] = $this->request->post['post_seo_url'];
		} else if ( $post_id ) {
			$this->data['post_seo_url'] = $this->model_extension_pavoblog_post->getSeoUrlData( $post_id );
		}

		$this->data['thumb'] = isset( $this->data['post']['image'] ) ? $this->data['post']['image'] : HTTPS_CATALOG . 'image/cache/catalog/opencart-logo-100x100.png';
		$this->data['action'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) );

		$action_url = $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true );
   		if ( $post_id ) {
   			$action_url = $this->url->link( 'extension/module/pavoblog/post', 'post_id='.$post_id.'&user_token=' . $this->session->data['user_token'], true );
   		}
   		$this->data['action'] = str_replace( '&amp;', '&', $action_url );

		// users
		$users = $this->model_user_user->getUsers();
		foreach ( $users as $user ) {
			$this->data['users'][] = array(
					'user_id'    => $user['user_id'],
					'username'   => $user['username']
				);
		}

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
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_categories_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// categories
   		$this->data['categories'] = $this->model_extension_pavoblog_category->getAll();

   		if ( $this->data['categories'] ) {
   			foreach ( $this->data['categories'] as $key => $category ) {
   				$category['edit'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/category', 'id='.$category['category_id'].'&user_token=' . $this->session->data['user_token'], true ) );
   				$this->data['categories'][$key] = $category;
   			}
   		}
   		$this->data['action']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) );
   		$this->data['add_new_url']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true ) );

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
	 * add - edit category
	 */
	public function category() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/category' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );
		$this->load->model( 'tool/image' );

		$category_id = isset( $this->request->get['category_id'] ) ? $this->request->get['category_id'] : 0;
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateCategoryForm() ) {
			if ( $category_id ) {
				$this->model_extension_pavoblog_category->edit( $category_id, $this->request->post );
			} else {
				$category_id = $this->model_extension_pavoblog_category->add( $this->request->post );
			}
			$this->session->data['success'] = $this->language->get( 'text_success' );

			if ( $category_id ) {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/category', 'category_id=' . $category_id . '&user_token=' . $this->session->data['user_token'], true ) ); exit();
			} else {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true ) ); exit();
			}
		}

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_categories_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);
   		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get( 'text_default' )
		);

		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

   		// languages
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['category'] = $this->model_extension_pavoblog_category->get( $category_id );

		$this->data['category_data'] = array();
		if ( ! empty( $this->request->post['category_data'] ) ) {
			$this->data['category_data'] = $this->request->post['category_data'];
		} else if ( $category_id ) {
			$this->data['category_data'] = $this->model_extension_pavoblog_category->getCategoryDescription( $category_id );
		}

		$this->data['category_seo_url'] = array();
		if ( ! empty( $this->request->post['category_seo_url'] ) ) {
			$this->data['category_seo_url'] = $this->request->post['category_seo_url'];
		} else if ( $category_id ) {
			$this->data['category_seo_url'] = $this->model_extension_pavoblog_category->getSeoUrlData( $category_id );
		}

		$this->data['category']['thumb'] 	= ! empty( $this->data['category']['image'] ) ? $this->model_tool_image->resize( $this->data['category']['image'], 100, 100 ) : $this->model_tool_image->resize( 'no_image.png', 100, 100);
		$this->data['category_store'] 		= $category_id ? $this->model_extension_pavoblog_category->getCategoryStore( $category_id ) : array();

		// categories
   		$this->data['categories'] = $this->model_extension_pavoblog_category->getAll();

   		$action_url = $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true );
   		if ( $category_id ) {
   			$action_url = $this->url->link( 'extension/module/pavoblog/category', 'category_id='.$category_id.'&user_token=' . $this->session->data['user_token'], true );
   		}
   		$this->data['action']	= str_replace( '&amp;', '&', $action_url );
		$this->data['back_url']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'category_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/category', $this->data ) );
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
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
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
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
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

	/**
	 * validate category form
	 */
	protected function validateCategoryForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/category' )) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		foreach ($this->request->post['category_data'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->errors['name'][$language_id] = $this->language->get( 'error_name' );
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->errors['meta_title'][$language_id] = $this->language->get( 'error_meta_title' );
			}
		}

		if ($this->request->post['category_seo_url']) {
			$this->load->model( 'design/seo_url' );

			foreach ($this->request->post['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['post_id']) || (($seo_url['query'] != 'pavo_post_id=' . $this->request->get['post_id'])))) {
								$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}

		return ! $this->errors;
	}

	/**
	 * validate post form
	 */
	protected function validatePostForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/post' )) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		foreach ($this->request->post['post_data'] as $language_id => $value) {
			if ( ( utf8_strlen( $value['name'] ) < 1 ) || ( utf8_strlen( $value['name'] ) > 255 ) ) {
				$this->errors['name'][$language_id] = $this->language->get( 'error_name' );
			}

			if ( ( utf8_strlen( $value['meta_title'] ) < 1 ) || ( utf8_strlen( $value['meta_title'] ) > 255 ) ) {
				$this->errors['meta_title'][$language_id] = $this->language->get( 'error_meta_title' );
			}
		}

		if ($this->request->post['post_seo_url']) {
			$this->load->model( 'design/seo_url' );

			foreach ($this->request->post['post_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['post_id']) || (($seo_url['query'] != 'pavo_post_id=' . $this->request->get['post_id'])))) {
								$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}

		return ! $this->errors;
	}

	/**
	 * install actions
	 * create new permission and tables
	 */
	public function install() {
		// START ADD USER PERMISSION
		$this->load->model( 'user/user_group' );
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

		$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_to_store` (
					`post_id` int(11) NOT NULL,
					`store_id` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
			");

		// post description
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_description` (
				`post_id` int(11) NOT NULL,
				`language_id` int(11) NOT NULL,
				`name` varchar(255) NOT NULL,
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
			  `column` int(3) NOT NULL DEFAULT '1',
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

		$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category_to_store` (
					`category_id` int(11) NOT NULL,
					`store_id` int(11) NOT NULL
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
		$this->load->model( 'user/user_group' );
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