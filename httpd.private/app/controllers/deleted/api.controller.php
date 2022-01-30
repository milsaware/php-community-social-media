<?php
include_model('auth');
include_model('posts');
use postsModel as postsM;
use authModel as auth;
use apiController as apiC;
class apiController {

	public static function index(){
		$action = $_GET['action'];
		if($action == 'fetch_post'){
			$arr = postsM::fetch_post($_GET['data_one'], $_GET['data_two']);
			$json = json_encode($arr);
			echo $json;
		}
	}
	
	public static function login(){
			$metadata['meta']['title'] = 'Nettext: The public\'s platform. Log in, register or download';
			$metadata['meta']['description'] = 'Log in, register or download Nettext 1.0';
			$data['error_msg'] = (isset($_SESSION['error']))? '<div id="errorMsg">'.$_SESSION['error'].'</div>' : '';
			$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
			$footdata['JSini'] = 'loginIni();';
			unset($_SESSION['error']);

			view::build('head', $metadata).
			view::build('nav').
			view::build('login', $data).
			view::build('foot', $footdata);
	}
	
	public static function registerSubmit(){
		$reg = auth::registerSubmit();
		if($reg != 0){
			$_SESSION['uid'] = $reg;
		}else{
			$_SESSION['error'] = 'The handle already exists';
			echo 'error';
		}
	}
	
	public static function loginSubmit(){
		$log = auth::loginSubmit();
		if($log != 0){
			$_SESSION['uid'] = $log;
		}else{
			$_SESSION['error'] = 'The log in details were incorrect';
			echo 'error';
		}
	}
}