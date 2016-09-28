<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Controller extends CI_Controller
{
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * @Explain: This function upload files in given directory.
	 * @Return:	Return an array containing detail of uploaded file/files.
	 *
	 * @param:	files				-> An array (for multiple upload) or string(for single upload), containing name of input field/fields(of type file)
	 * @param:	$config(optional)	-> An array, containing file restrictions
	 * 			Default values are given in function.
	 *
	 * @usage: $this->resource_model->do_upload($files);
	 */
	public function do_upload($files, $config = null) {
		
		if($config['allowed_types'] == 'gif|jpg|png') {
			// if(!isset($config['upload_path']))		$config['upload_path'] = './uploads/img/product/';
			//if(!isset($config['max_size']))			$config['max_size']	= '100';
			//if(!isset($config['max_width']))		$config['max_width'] = '1024';
			//if(!isset($config['max_height']))		$config['max_height'] = '768';
		}
		
		
		$config['encrypt_name'] = TRUE;	// Random File Name
		//$config['file_name'] = 'profile'.$this->session->userdata('sno');
		//$config['overwrite'] = TRUE;	// If set true file will be overwritten
		
		//$this->load->library('upload', $config);
		$this->load->library('upload');
		$this->upload->initialize($config);
		
		if(is_array($files)) {
			foreach($files as $file) {
				if ( ! $this->upload->do_upload($file))
				{
					if(isset($data)) {
						foreach($data as $file) {	//	Delete uploaded files if error occurred.
							unlink($file['full_path']);
						}
					}
					return $data = array('error' => $this->upload->display_errors());
				}
				else
				{
					//$data = array('upload_data' => $this->upload->data());
					//$this->load->view('upload_success', $data);
					
					$data[] = $this->upload->data();
					//return $data[0]['full_path'];
					
					if($config['allowed_types'] == 'gif|jpg|png') {
						$temp = $this->upload->data();
						$file_name = $temp['file_name'];
						$config['thumb_marker']		= '';
						$config['image_library']	= 'gd2';
						$config['source_image']		= $config['upload_path'].'/'.$file_name;
						$config['new_image']		= $config['upload_path'].'/thumbs/'.$file_name;
						$config['create_thumb']		= TRUE;
						$config['maintain_ratio']	= TRUE;
						$config['width']			= 150;
						$config['height']			= 170;
						//$this->load->library('image_lib', $config);
						$this->load->library('image_lib');
						$this->image_lib->initialize($config);
						$this->image_lib->resize();
					}
				}
			}
		}
		else
		{
			if ( ! $this->upload->do_upload($files))
			{
				return $data = array('error' => $this->upload->display_errors());
			}
			else
			{
				$data[] = $this->upload->data();
				
				if($config['allowed_types'] == 'gif|jpg|png') {
					$temp = $this->upload->data();
					$file_name = $temp['file_name'];
					$config['thumb_marker']		= '';
					$config['image_library']	= 'gd2';
					$config['source_image']		= $config['upload_path'].'/'.$file_name;
					$config['new_image']		= $config['upload_path'].'/thumb/'.$file_name;
					$config['create_thumb']		= TRUE;
					$config['maintain_ratio']	= TRUE;
					$config['width']			= 150;
					$config['height']			= 170;
					//$this->load->library('image_lib', $config);
					$this->load->library('image_lib');
					$this->image_lib->initialize($config);
					$this->image_lib->resize();
				}
			}
		}
		return $data;
	}
	
	public function delete_file($files) {
		if(is_array($files)) {
			foreach($files as $file) {
				@unlink($file);
			}
		} else {
			@unlink($files);
		}
	}
}