<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Controller extends CI_Controller
{
	public $user = NULL;
	public $USER_PACKAGE_DETAIL = array();

	public $IP_ADDR;						//IP ADDRESS
	public $SERVER_TIME;					//CURRENT CONTROLLER CLASS

	public $CURRENT_CTRL = NULL;			//CURRENT CONTROLLER CLASS
	public $CURRENT_MTD = NULL;				//CURRENT CONTROLLER METHOD
	public $BASE_URL;						//BASE URL
	
	private $VIEW_DATA_ARR = array();		//DATA ARRAY FOR VIEW FILES
	
	public $NO_LOGIN_PAGES;					//PAGES THAT DOESN'T NEED LOGGED IN SESSION
	public $LOGIN_PAGES;					//PAGES THAT NEED LOGGED IN SESSION, ALLOWED TO ALL USERS
	public $CSRF_TOKEN_NAME;				//CSRF TOKEN NAME
	public $CSRF_TOKEN_VALUE;				//CSRF TOKEN VALUE

	public $IS_USER_NOTIFICATIONS_READ = NULL;
	public $SITE_MENUS = array();
	public $NOTIFICATIONS = array();
	public $SITE_SETTINGS = array();

	public function __construct() {
		parent::__construct();

		$this->load->model([
			'users_model',
		]);

		$this->user = $this->session->userdata('user');
		//$this->load_default_language_files();
		$this->initialize_public_variables();
	}

	private function initialize_public_variables()
	{
		$this->IP_ADDR = $this->input->server('REMOTE_ADDR');

		$this->CURRENT_CTRL = $this->router->class;
		$this->CURRENT_MTD = $this->router->method;
		$this->BASE_URL = base_url();

		$this->NO_LOGIN_PAGES = [
			'account/index', 
			'account/user_login', 
			'account/user_register', 
			'account/forgot_password', 
			'account/reset_password'
		];

		$this->LOGIN_PAGES = [
			'user/profile'
		];

		// $this->SITE_SETTINGS = $this->posts_model->get_site_settings();
		$this->CSRF_TOKEN_NAME = $this->security->get_csrf_token_name();
		$this->CSRF_TOKEN_VALUE = $this->security->get_csrf_hash();

		$this->set([
			'site_settings'					=> $this->SITE_SETTINGS,
			'csrf_token_name'				=> $this->CSRF_TOKEN_NAME,
			'csrf_token_value'				=> $this->CSRF_TOKEN_VALUE,
			'base_url'						=> $this->BASE_URL,
			'notifications'					=> $this->NOTIFICATIONS,
			'is_user_notifications_read'	=> $this->IS_USER_NOTIFICATIONS_READ,
			'page_title'					=> 'Site Name'
		]);
	}
	
	public function isLogin()
	{
		return ($this->session->userdata('user')) ? true : false;
	}

	public function checkLogin()
	{
		if($this->session->userdata('user') === NULL){
			redirect('home');
		}
	}

	public function set($set_key, $set_value = false) {
		if ( is_array($set_key) ) {
			$this->VIEW_DATA_ARR = array_merge($this->VIEW_DATA_ARR, $set_key);
		} else {
			$this->VIEW_DATA_ARR[$set_key] = $set_value;
		}
	}

	public function setView($view = false)
	{
		if ($view === false) {
			$view = $this->CURRENT_CTRL . '/' . $this->CURRENT_MTD;
		}

		$this->set('view', $view);
		// $this->load->view('include/default_view', $this->VIEW_DATA_ARR);
		// $this->load->view('new_template/include/default_view', $this->VIEW_DATA_ARR);
		// if (isset($this->user['adminlogin'] ) && $this->user['adminlogin'] === true) {
		if ($this->uri->segment(1) === 'admin') {
			
			$this->load->view('admin/template/template', $this->VIEW_DATA_ARR);

		}else{

			$this->load->view('includes/template', $this->VIEW_DATA_ARR);
		}

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

	public function emailTemplate($data)
	{
	    //$WebSetting = getWebSiteSettings();
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
		<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		  
		<style>
		.container {
		        width: 1200px;
		        margin: 0 auto;
		        height: auto;
		        text-align: center;
		        overflow: hidden;
		        font-family: arial;
		    }
		.content_table1{
		        border:15px solid #111111;
		        width: 100%;
		        padding:30px 0px 50px 0px;
		        border-spacing: 0px;
		        border-bottom:0px;
		        border-top:0px;
		    }
		.content_table1 td{
		    border-bottom: 1px solid #b8b8b8;
		    padding: 10px 0px;
		    width: 50%;
		}
		.content_table2{
		        border:15px solid #111111;
		        width: 100%;
		        padding:30px 0px 50px 0px;
		        border-spacing: 0px;
		        border-top:0px;
		        border-bottom: 0px;
		    }

		.logo_table{
		        border:15px solid #111111;
		        width: 100%;
		        padding:30px 0px 0px 0px;
		        border-spacing: 0px;
		        border-bottom:0px solid #b8b8b8;
		    }

		.logo_table img{
		        width: 250px;
		        background-color: gray;
		        margin-left: 20px;
		        margin-bottom: 20px;
		    }

		.footer_table{
		        background-color:#111111;
		        width: 100%;
		        padding:20px 0px 20px 0px;
		        border-spacing: 0px;
		       color: #fff;
		    }
		.table_heading{
		    border-left: 15px solid #101010;
		    width: 97.5%;
		    display: inline-block;
		    border-right: 15px solid #101010;
		    padding: 10px 0px;
		    margin-top: 0px;
		    color: #fff;
		    background-color:#5b5959; 
		}
		.content_table2 td {
		    padding: 10px 0px;
		}
		</style>
		    
		    </head>
		<body>
		   
		    <div class="container">
		        <table class="logo_table">
		            <tr>
		                <td align="left" class="logo_td"><img src="'.base_url('assets/images/logo.png').'"></td>
		            </tr> 
		        </table>
		   
		        '.$data.'
		        <table class="footer_table">
		            <tr>
		                <td align="center"  class="footer_td" >© '.date('Y').'  All Rights Reserved. </td>
		            </tr>   
		        </table>
		        </div>
		    </body>
		</html>';
	}  

	public function sendEmail($data= array(),$files = false)
	{
		$config = array (
				'mailtype' => 'html',
				'charset'  => 'utf-8',
				'priority' => '1'
			);
		$this->email->initialize($config);
	    $this->load->library('email');
	    $this->email->from($data['from']);
	    $this->email->to($data['to']);
	    $this->email->subject($data['subject']);
	    $this->email->message($data['body']);
	    //$this->email->set_mailtype('html');
	    if($files){
	      	foreach($files as $file){
	        	$this->email->attach(base_url().$file);
	      	}
	    }
	    if($this->email->send()){
	      //echo 'yes';exit();
	        return true;
	    }else{
	      // show_error($CI->email->print_debugger());
	      //echo 'no';exit();
	      return false;
	    }
	}

    //Upload Mulitple Files
	public function uploadMultipleFiles($fileName='', $config=null)
	{
		// retrieve the number of images uploaded;
		$number_of_files = sizeof($_FILES[$fileName]['tmp_name']);
		// considering that do_upload() accepts single files, we will have to do a small hack so that we can upload multiple files. For this we will have to keep the data of uploaded files in a variable, and redo the $_FILE.
		$files = $_FILES[$fileName];
		$errors = array();
	
		// first make sure that there is no error in uploading the files
		for($i=0;$i<$number_of_files;$i++)
		{
		  if($_FILES[$fileName]['error'][$i] != 0) $errors[$i][] = 'Couldn\'t upload file '.$_FILES[$fileName]['name'][$i];
		}
		if(sizeof($errors)==0)
		{
		  // now, taking into account that there can be more than one file, for each file we will have to do the upload
		  // we first load the upload library
		  $this->load->library('upload');

		  for ($i = 0; $i < $number_of_files; $i++) {
			$_FILES['uploadFile']['name'] = $files['name'][$i];
			$_FILES['uploadFile']['type'] = $files['type'][$i];
			$_FILES['uploadFile']['tmp_name'] = $files['tmp_name'][$i];
			$_FILES['uploadFile']['error'] = $files['error'][$i];
			$_FILES['uploadFile']['size'] = $files['size'][$i];
			//now we initialize the upload library
			$this->upload->initialize($config);
			// we retrieve the number of files that were uploaded
			if ($this->upload->do_upload('uploadFile'))
			{
			  $data['uploads'][$i] = $this->upload->data();
			}
			else
			{
			  $data['upload_errors'][$i] = $this->upload->display_errors();
			}
		  }
		}
		else
		{
		  return false;
		}
		
		return $data;

	}

    protected function convertTimeToMinutes($time)
	{
		$parts = explode(':', $time);
		return ($parts[0] * 60) + $parts[1];
	}

	protected function convertMinutesToTime($minutes)
	{
		$hours = floor($minutes / 60);
		$minutes = $minutes - ($hours * 60);
		return $hours . ':' . $minutes;
	}

	protected function copy_dir($src, $dst) { 
	    $dir = opendir($src); 
	    @mkdir($dst); 
	    while(false !== ( $file = readdir($dir)) ) { 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                $this->copy_dir($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    } 
	    closedir($dir); 
	}

	protected function delete_dir($dir)
	{
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
	}

	protected function create_zip_dir($dir_path)
    {
        // Get real path for our folder
        $rootPath = realpath($dir_path);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($dir_path.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Initialize empty "delete list"
        $filesToDelete = array();

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);

                // Add current file to "delete list"
                // delete it later cause ZipArchive create archive only after calling close function and ZipArchive lock files until archive created)
                if ($file->getFilename() != 'important.txt')
                {
                    $filesToDelete[] = $filePath;
                }
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Delete all files from "delete list"
       /* foreach ($filesToDelete as $file)
        {
            unlink($file);
        }*/
    }

	public function create_log($message)
	{
		if ($message && in_array('founder', array_column($this->user['roles'], 'role_code'))) {

			$logs_file_path =  'downloads/logs/log-'.date('Y-m-d');
			$file_content = date('Y-m-d H:i:s') . ' --> ' . $this->user['display_name'] . ' --> ' .$message."\r\n";

        	$file_handler = fopen($logs_file_path . '.txt', 'a+') or die('unable to open file');
        	fwrite($file_handler, $file_content);
        	fclose($file_handler);
		}
	}
}