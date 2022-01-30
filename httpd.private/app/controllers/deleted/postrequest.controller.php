<?php
include_model('profile');
include_model('auth');
use profileModel as profile;
use authModel as auth;
use postrequestController as prq;
class postrequestController {

	public static function index(){
		if(!isset($_POST['request'])){
			$metadata['meta']['title'] = 'Nettext search';
			$metadata['meta']['description'] = 'Nettext search';
			$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
			view::build('head', $metadata).
			view::build('nav').
			view::build('error').
			view::build('foot', $footdata);
		}else{
			$action = preg_replace('#[^a-zA-Z0-9_]#', '', $_POST['action']);
			if($action == 'follow' || $action == 'unfollow')
			prq::submitFollow($action);
		}
	}
	
	public static function submitFollow($action){
		$followId = preg_replace('#[^0-9]#', '', $_POST['followId']);
		profile::submitFollow($followId, $action);
	}
}