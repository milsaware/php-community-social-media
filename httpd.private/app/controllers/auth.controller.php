<?php
include_model('auth');
use authModel as auth;
use authController as authC;
class authController {

	public static function index(){
		if(isset($_POST['register'])){
			authC::registerSubmit();
		}
		elseif(isset($_POST['login'])){
			authC::loginSubmit();
		}
		else{
			(!isset($_SESSION['uid']))? authC::login() : header('Location: /');
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