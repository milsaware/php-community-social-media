<?php
include_model('notifications');
include_model('auth');
include_controller('auth');
use authModel as auth;
use authController as authC;
use notificationsModel as model;
use notificationsController as controller;
class notificationsController {

	public static function index(){
		if(isset($_POST['action'])){
			$action = preg_replace('#[^a-z]#', '', $_POST['action']);
			if(isset($_SESSION['uid'])){
				$id = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
				if($action == 'count'){
					echo model::fetch_count($id);
					die;
				}elseif($action == 'notificationreset'){
					model::notificationReset($id);
					die;
				}
			}
		}else{
			(!isset($_SESSION['uid']))? authC::login() : controller::notifications();
		}
	}
	
	public static function notifications(){
		$metadata['meta']['title'] = 'Nettext: notifications';
		$metadata['meta']['description'] = 'notificaions';
		$uid = $_SESSION['uid'];
		$data['count'] = (isset($_POST['count']))?  : 0;
		$data['message'] = model::fetch_notifications($uid);
		$data['notifications'] = true;
		$data['fetch_not'] = (isset($_POST['fetch_not']))? 1 : 0;
		$data['newClass'] = (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] != 'old'))? 'notoptactv' : 'notopt';
		$data['oldClass'] = (isset($_GET['action']) && $_GET['action'] == 'old')? 'notoptactv' : 'notopt';
		
		$profile_details = auth::fetchUserDet($uid);
		foreach($profile_details as $row){
			$handle = $row['handle'];
			$uname = $row['uname'];
		}
		$hsmall = strtolower($handle);
		$hdir = auth::fetchUserDir($handle).$hsmall.DS;
		$data['hdir'] = $hdir;
		$lc_data['action'] = 'people';
		$lc_data['search_term'] = '';
		$lc_data['hdir'] = $hdir;
		$lc_data['handle'] = $handle;
		$lc_data['uname'] = $uname;
		$lc_data['homeClass'] = 'sl-nav';
		$lc_data['notClass'] = 'sl-nav-active';
		$lc_data['setClass'] = 'sl-nav';
		$lc_data['searchClass'] = '';

		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		$counts = auth::fetchCounts($uid);
		foreach($counts as $row){
			$lc_data['postsCount'] = $row['posts'];
			$lc_data['followersCount'] = $row['followers'];
			$lc_data['followingCount'] = $row['following'];
		}
		
		$request = (isset($_POST['request']))? true : false;
		$data['request'] = ($request == true)? (($request == 'newnot')? $request : true) : false;

		$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
		$footdata['JSini'] = 'notificationIni();';

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
	
	public static function fetch_count($count){
		echo model::fetch_count($count);
	}

}