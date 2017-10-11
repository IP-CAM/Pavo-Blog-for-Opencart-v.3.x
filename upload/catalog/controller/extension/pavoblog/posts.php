<?php

class ControllerExtensionPavoBlogPosts extends Controller {

	public function index() {
		/**
		 * load model - language
		 */
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );
		$data = array();

		/**
		 * all posts
		 */
		$data['posts'] = $this->model_extension_pavoblog_post->getPosts();
var_dump($data['posts']); die();
		/**
		 * set document title
		 */
		$this->document->setTitle( $this->language->get( 'heading_title' ) );

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		/**
		 * set layout template
		 */
		$this->response->setOutput( $this->load->view( 'pavoblog/posts', $data ) );
	}

	/**
	 * ajax set display mode
	 */
	public function ajaxSetMode() {

	}

}