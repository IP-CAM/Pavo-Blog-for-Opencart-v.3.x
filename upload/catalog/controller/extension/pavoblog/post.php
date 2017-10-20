<?php

class ControllerExtensionPavoBlogPost extends Controller{

	public function index() {
		/**
		 * load model - language
		 */
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );
		$this->load->model( 'tool/image' );

		$data = array();
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

		$post_id = isset( $this->request->get['post_id'] ) ? abs( $this->request->get['post_id'] ) : false;
		if ( ! $post_id ) {
			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link('error', '') ) ); exit();
		}
		$post = $this->model_extension_pavoblog_post->getPost( $post_id );
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog'),
			'href' => $this->url->link('extension/pavoblog/archive')
		);
		$data['breadcrumbs'][] = array(
			'text'	=> $post['name'],
			'href'	=> $this->url->link( 'extension/pavoblog/single', 'post_id=' . $post['post_id'] )
		);
		if ( empty( $post['post_type'] ) ) {
			$post['post_type'] = 'image';
		}
		if ( ! empty( $post['image'] ) ) {
			if ( $post['image'] && $this->config->get( 'pavoblog_post_single_image_type' ) == 0 ) {
				$post['thumb'] = $this->model_tool_image->resize( $post['image'], $this->config->get('pavoblog_single_image_width'), $this->config->get('pavoblog_single_image_height' ) );
			} else {
				$post['thumb'] = ( $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER ) . 'image/' . $post['image'];
			}
		} else {
			$post['thumb'] = $this->model_tool_image->resize( 'placeholder.png', $this->config->get('pavoblog_single_image_width'), $this->config->get('pavoblog_single_image_height' ) );
		}
		if ( ! empty( $post['content'] ) ) {
			$post['content'] = html_entity_decode( $post['content'], ENT_QUOTES, 'UTF-8' );
		}

		$data['post'] = $post;
		/**
		 * set layout template
		 */
		$this->response->setOutput( $this->load->view( 'pavoblog/single', $data ) );
	}

}