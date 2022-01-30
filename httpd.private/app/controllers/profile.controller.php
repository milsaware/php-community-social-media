<?php
include_model('profile');
include_model('posts');
include_model('auth');
include_controller('auth');
use authModel as auth;
use authController as authC;
use profileModel as profile;
use postsModel as posmod;
use profileController as procon;
class profileController {

	public static function index(){
		if((isset($_POST['action']))){
			if(!isset($_POST['request'])){
				$metadata['meta']['title'] = 'Page not found';
				$metadata['meta']['description'] = 'Page not found';
				$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
				view::build('head', $metadata).
				view::build('nav').
				view::build('error').
				view::build('foot', $footdata);
				die();
			}else{
				$action = preg_replace('#[^a-zA-Z0-9_]#', '', $_POST['action']);
				if($action == 'follow' || $action == 'unfollow')
				procon::submitFollow($action);
				die();
			}
		}

		$action = (isset($_GET['action']))? true : false;
		$uid = ($action == true)? auth::fetchUserId(preg_replace('#[^a-zA-z0-9_]#', '', $_GET['action'])) : $_SESSION['uid'];

		$profile_details = auth::fetchUserDet($uid);
		foreach($profile_details as $row){
			$data['proid'] = $row['uid'];
			$handle = $row['handle'];
			$uname = $row['uname'];
			$data['bio'] = $row['bio'];
			$metadata['meta']['title'] = $uname.' profile';
			$metadata['meta']['description'] = $row['bio'];
		}

		$loggedInHandle = (isset($_SESSION['uid']))? $_SESSION['uid'] : 0;
		if($loggedInHandle != 0){
			$orprodet = auth::fetchUserDet($loggedInHandle);
			foreach($orprodet as $row){
				$data['linhandle'] = $row['handle'];
			}
		}

		if(isset($handle)){
			$whitelist = array('posts', 'responses', 'reposts', 'following', 'followers');
			if(isset($_GET['data_one'])){
				$pg = strtolower(preg_replace('#[^a-zA-Z]#', '', $_GET['data_one']));
				$type = (in_array($pg, $whitelist))? $pg : 'posts';
			}else{
				$type = 'posts';
			}
			$hsmall = strtolower($handle);
			$hdir = auth::fetchUserDir($handle).$hsmall.DS;
			$data['type'] = $type;
			$data['usr']['banner'] = '/assets/images/usr/'.$hdir.'banner.png';
			$data['usr']['avatar'] = '/assets/images/usr/'.$hdir.'avatar.png';
			$data['request'] = (isset($_POST['request']))? true : false;
			$data['action'] = 'people';
			$data['search_class'] = '';
			$data['search_term'] = '';
			$data['uname'] = $uname;
			$data['handle'] = $handle;
			$data['homeClass'] = 'sl-nav';
			$data['notClass'] = 'sl-nav';
			$data['setClass'] = 'sl-nav';
			$data['message'] = ($type == 'following' || $type == 'followers')? profile::fetchFollowing($type, $handle) : profile::fetchPosts($uid);
			$data['posts_active'] = ($type == 'posts')? 'mboptactv' : 'mbopt';
			$data['resps_active'] = ($type == 'responses')? 'mboptactv' : 'mbopt';
			$data['reposts_active'] = ($type == 'reposts')? 'mboptactv' : 'mbopt';
			$data['following_active'] = ($type == 'following')? 'pbfoptactv' : 'pbfopt';
			$data['followers_active'] = ($type == 'followers')? 'pbfoptactv' : 'pbfopt';
			$data['isfollowing'] = profile::isfollwing($uid);
			$data['followback'] = profile::isfollwing(0, $handle);
			$data['profile_view'] = true;
			$data['profile_view_pg'] = ($type == 'reposts')? 1 : 0;

			$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
			$footdata['JSini'] = 'profileIni();';

			if(!isset($_POST['request'])){
				view::build('head', $metadata).
				view::build('nav', $data).
				view::build('leftcolumn', $data).
				view::build('timeline', $data).
				view::build('foot', $footdata);
			}else{
				view::build('timeline', $data);
			}
		}else{
			view::build('head', $metadata).
			view::build('nav').
			view::build('error');
		}
	}
	
	public static function settings(){
		if(isset($_SESSION['uid'])){
			$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$profile_details = auth::fetchUserDet($uid);
			foreach($profile_details as $row){
				$data['proid'] = $row['uid'];
				$handle = $row['handle'];
				$uname = $row['uname'];
				$data['handle'] = $handle;
				$data['bio'] = $row['bio'];
				$metadata['meta']['title'] = $uname.' settings';
				$metadata['meta']['description'] = $uname.' settings';
			}

			if(isset($handle)){
				$hsmall = strtolower($handle);
				$hdir = auth::fetchUserDir($handle).$hsmall.DS;
				$data['usr']['banner'] = '/assets/images/usr/'.$hdir.'banner.png';
				$data['usr']['avatar'] = '/assets/images/usr/'.$hdir.'avatar.png';
				$data['request'] = (isset($_POST['request']))? true : false;
				$data['action'] = 'people';
				$data['search_class'] = '';
				$data['search_term'] = '';
				$data['uname'] = $uname;
				$data['handle'] = $handle;
				$data['homeClass'] = 'sl-nav';
				$data['notClass'] = 'sl-nav';
				$data['setClass'] = 'sl-nav-active';
				$data['posts_active'] = 'mbopt';
				$data['resps_active'] = 'mbopt';
				$data['reposts_active'] = 'mbopt';
				$data['following_active'] = 'pbfopt';
				$data['followers_active'] = 'pbfopt';
				$data['isfollowing'] = profile::isfollwing($uid);
				$data['followback'] = profile::isfollwing(0, $handle);
				$data['profile_view'] = true;
				$data['email'] = '';
				$data['location'] = '';

				$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
				$footdata['JSini'] = 'settingsIni();profileIni();';

				if(!isset($_POST['request'])){
					view::build('head', $metadata).
					view::build('nav', $data).
					view::build('leftcolumn', $data).
					view::build('settings', $data).
					view::build('foot', $footdata);
				}else{
					view::build('settings', $data);
				}
			}else{
				view::build('head', $metadata).
				view::build('nav').
				view::build('error');
			}
		}else{
			authC::login();
		}
	}

	public static function submitFollow($action){
		$followId = preg_replace('#[^0-9]#', '', $_POST['followId']);
		profile::submitFollow($followId, $action);
	}

	public static function muteUser(){
		if(isset($_POST['handle'])){
			profile::muteUser();
		}else{
			$metadata['meta']['title'] = 'Page doesn\'t exist';
			$metadata['meta']['description'] = 'Page doesn\'t exist';
			view::build('head', $metadata).
			view::build('nav').
			view::build('error');
		}
	}
}