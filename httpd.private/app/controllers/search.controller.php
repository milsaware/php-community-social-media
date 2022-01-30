<?php
include_controller('auth');
include_model('auth');
include_model('search');
use searchModel as searchM;
use searchController as searchC;
use authModel as auth;
use authController as authC;
class searchController {

	public static function index(){
		(!isset($_SESSION['uid']))? authC::login() : searchC::search();
	}
	
	public static function search(){
		$metadata['meta']['title'] = 'Nettext search';
		$metadata['meta']['description'] = 'Nettext search';
		$uid = $_SESSION['uid'];

		$profile_details = auth::fetchUserDet($uid);
		foreach($profile_details as $row){
			$handle = $row['handle'];
			$uname = $row['uname'];
		}
		$hsmall = strtolower($handle);
		$hdir = auth::fetchUserDir($handle).$hsmall.DS;
		$data['hdir'] = $hdir;
		$data['request'] = (isset($_POST['request']))? true : false;
		$lc_data['hdir'] = $hdir;
		$lc_data['handle'] = $handle;
		$lc_data['uname'] = $uname;
		$lc_data['homeClass'] = 'sl-nav';
		$lc_data['notClass'] = 'sl-nav';
		$lc_data['setClass'] = 'sl-nav';
		$lc_data['searchClass'] = ' class="searchactive"';
		$action = (isset($_GET['action']))? $_GET['action'] : 'people';
		$lc_data['action'] = $action;
		$data['action'] = $action;
		$data_one = '';
		$data['req'] = (isset($_POST['req']))? 1 : 0;
		$data['search'] = true;
		
		$data['peopleClass'] = ($action == 'people')? 'notoptactv' : 'notopt';
		$data['peoplePosts'] = ($action == 'posts')? 'notoptactv' : 'notopt';
		
		$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
		$footdata['JSini'] = 'searchIni();';

		if(isset($_GET['action'])){
			if(isset($_GET['data_one'])){
				$data_one = $_GET['data_one'];
				if($_GET['action'] == 'people'){
					$data['message'] = searchM::fetchPeople($_GET['data_one']);
				}
				elseif($_GET['action'] == 'posts'){
					$data['message'] = searchM::fetchPosts($_GET['data_one']);
				}
				else{
					$data['message'] = searchM::fetchPeople($_GET['data_one']);
				}
				$metadata['meta']['title'] = 'Nettext search: '.$_GET['action'];
				$metadata['meta']['description'] = 'Nettext search: '.$_GET['action'];
			}else{
				$metadata['meta']['title'] = 'Nettext search: '.$_GET['action'];
				$metadata['meta']['description'] = 'Nettext search: '.$_GET['action'];
			}
		}else{
			echo 'search index';
		}
		
		$lc_data['search_term'] = $data_one;
		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		$counts = auth::fetchCounts($uid);
		foreach($counts as $row){
			$lc_data['postsCount'] = $row['posts'];
			$lc_data['followersCount'] = $row['followers'];
			$lc_data['followingCount'] = $row['following'];
		}
		
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
}