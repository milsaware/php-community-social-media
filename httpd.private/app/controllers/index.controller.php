<?php
include_Controller('auth');
use authController as authC;
include_model('auth');
use authModel as auth;
include_model('index');
use indexModel as im;
use indexController as i;
class indexController {

	public static function index(){
		(!isset($_SESSION['uid']))? authC::login() : i::timeline();
	}
	
	public static function timeline(){
		$metadata['meta']['title'] = SITENAME;
		$metadata['meta']['description'] = SITENAME;

		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		$data['message'] = im::fetch_timeline($uid);

		$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
		$footdata['JSini'] = 'timelineIni();';

		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		$counts = auth::fetchCounts($uid);
		foreach($counts as $row){
			$lc_data['postsCount'] = $row['posts'];
			$lc_data['followersCount'] = $row['followers'];
			$lc_data['followingCount'] = $row['following'];
		}

		$profile_details = auth::fetchUserDet($uid);
		foreach($profile_details as $row){
			$handle = $row['handle'];
			$uname = $row['uname'];
		}
		$hsmall = strtolower($handle);
		$lc_data['hdir'] = auth::fetchUserDir($handle).$hsmall.DS;
		$lc_data['handle'] = $handle;
		$lc_data['uname'] = $uname;
		$lc_data['homeClass'] = 'sl-nav-active';
		$lc_data['notClass'] = 'sl-nav';
		$lc_data['setClass'] = 'sl-nav';
		$lc_data['searchClass'] = '';
		$action = (isset($_GET['action']))? $_GET['action'] : 'people';
		$lc_data['action'] = $action;
		$data['action'] = $action;
		$data_one = '';
		$lc_data['search_term'] = '';
		
		$data['viewHome'] = (isset($_POST['viewHome']))? 1 : 0;
		$request = (isset($_POST['request']))? true : false;
		$data['request'] = ($request == true)? (($request == 'newnot')? $request : true) : false;

		if(!isset($_POST['request'])){
			view::build('head', $metadata).
			view::build('nav', $data).
			view::build('leftcolumn', $lc_data).
			view::build('timeline', $data).
			view::build('foot', $footdata);
		}else{
			view::build('timeline', $data);
		}
	}
	
	public static function login(){
		echo 'login function here';
	}

}