<?php
include_model('auth');
include_model('posts');
use authModel as auth;
use postsModel as pm;
use postsController as p;
class postsController {

	public static function index(){
		if(isset($_GET['id'])){
			$uid = $_SESSION['uid'];

			$profile_details = auth::fetchUserDet($uid);
			foreach($profile_details as $row){
				$handle = $row['handle'];
				$uname = $row['uname'];
			}
			$hsmall = strtolower($handle);
			$hdir = auth::fetchUserDir($handle).$hsmall.DS;
			$data['hdir'] = $hdir;
			$message = pm::fetch_post($_GET['id']);
			$data['message'] = $message;
			$data['main_post'] = true;

			if($message){
				foreach($message as $row){
					$metadata['meta']['title'] = $handle.' posted on '.SITENAME;
					$metadata['meta']['description'] = substr(str_replace('<br>', '. ', str_replace('<p>', '. ', $row['message'])), 0, 450);
				}
			}else{
				$metadata['meta']['title'] = SITENAME.' - page not found';
				$metadata['meta']['description'] = 'Page not found';
				view::build('head', $metadata).
				view::build('nav', $data).
				view::build('error');
				die();
			}
			$data['request'] = (isset($_POST['request']))? true : false;
			$lc_data['hdir'] = $hdir;
			$lc_data['handle'] = $handle;
			$lc_data['uname'] = $uname;
			$lc_data['homeClass'] = 'sl-nav';
			$lc_data['notClass'] = 'sl-nav';
			$lc_data['setClass'] = 'sl-nav';
			$lc_data['searchClass'] = ' class="searchactive"';
			$lc_data['search_term'] = '';
			$lc_data['action'] = '';
			$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$counts = auth::fetchCounts($uid);
			foreach($counts as $row){
				$lc_data['postsCount'] = $row['posts'];
				$lc_data['followersCount'] = $row['followers'];
				$lc_data['followingCount'] = $row['following'];
			}

			$footdata['copyright'] = '&#169; '.date('Y').' '.SITENAME;
			$footdata['JSini'] = 'timelineIni();';

			view::build('head', $metadata).
			view::build('nav', $data).
			view::build('leftcolumn', $lc_data).
			view::build('timeline', $data).
			view::build('foot', $footdata);
		}else{
			view::build('error');
		}
	}

	public static function fetch_post(){
		if(isset($_POST['id'])){
			$data['message'] = pm::fetch_post($_POST['id']);
			$data['btype'] = 'fetch_post';
			$data['fetch_post'] = true;
			view::build('timeline', $data);
		}else{
			view::build('error');
		}
	}

	public static function fetch_responses(){
		if(isset($_POST['id'])){
			$data['message'] = pm::fetch_post($_POST['id']);
			$handle = (isset($_POST['handle']))? $_POST['handle'] : auth::fetchUserHandle(preg_replace('#[^0-9]#', '', $_POST['paruid']));
			$data['response'] = pm::fetch_responses($_POST['id'], $handle);
			$data['viewMore'] = (isset($_POST['lastid']))? 1 : 0;
			$data['op'] = true;
			view::build('timeline', $data);
		}else{
			view::build('error');
		}
	}

	public static function submit_post(){
		if(isset($_POST['msg'])){
			$id = pm::submit_post($_POST['msg']);
			$data['message'] = pm::fetch_post($id);
			$data['fetch_new'] = true;
			view::build('timeline', $data);
		}else{
			view::build('error');
		}
	}

	public static function submit_response(){
		if(isset($_POST['msg'])){
			$parid = (isset($_POST['parid']))? $_POST['parid'] : 0;
			$id = pm::submit_post($_POST['msg'], $parid);
			$data['message'] = pm::fetch_post($id);
			$data['op'] = 0;
			$data['new_response'] = true;
			view::build('timeline', $data);
		}else{
			view::build('error');
		}
	}

	public static function submitPostAction(){
		if(isset($_POST['type']) && isset($_POST['id']) && isset($_POST['handle'])){
			$id = preg_replace('#[^0-9]#', '', $_POST['id']);
			$type = preg_replace('#[^a-z]#', '', $_POST['type']);
			$handle = preg_replace('#[^a-z_]#', '', strtolower($_POST['handle']));
			pm::submitPostAction($id, $type, $handle);
		}else{
			view::build('error');
		}
	}

	
	public static function testrequest(){
		if(isset($_GET['post_id'])){
			$data = array();
			$post_id = preg_replace('#[^0-9]#', '', $_GET['post_id']);
			$post = pm::fetch_post($post_id);
			if($post){
				foreach($post as $row){
					$data[] = array(
						'uname' => $row['uname'],
						'handle' => $row['handle'],
						'avatar' => $row['avatar'],
						'id' => $row['id'],
						'message' => $row['message'],
						'parid' => $row['parid'],
						'like_count' => $row['like_count'],
						'repost_count' => $row['repost_count'],
						'response_count' => $row['response_count'],
						'timestamp' => $row['timestamp'],
						'paruid' => $row['paruid'],
						'parhandle' => $row['parhandle'],
						'uid' => $row['uid'],
						'liked' => $row['liked'],
						'reposted' => $row['reposted']
					);
				}
			}
			echo json_encode($data);
		}
	}
}