<?php
include_model('auth');
use authModel as auth;
use postsModel as posts;
class postsModel {

	public static function confirmPost($id, $handle){
		$return = 0;
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($handle).$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id
			FROM posts
			WHERE id = :id
			LIMIT 1
		';
		if($query = $db->prepare($query)){
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			$result = $query->execute();

			if($row = $result->fetchArray(SQLITE3_ASSOC)){
				$return = 1;
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $return;
	}

	public static function fetch_post($id, $h=''){
		$oid = preg_replace('#[^0-9]#', '', $id);
		$handle = (isset($_GET['handle']))? $_GET['handle'] : ((isset($_POST['handle']))?
		preg_replace('#[^a-z0-9_]#', '', strtolower($_POST['handle'])) :
		((isset($_POST['paruid']))? preg_replace('#[^a-z0-9_]#', '', strtolower(auth::fetchUserHandle(preg_replace('#[^0-9]#', '', $_POST['paruid'])))) : ((isset($_GET['route']) && isset($_GET['handle']) && strtolower($_GET['route']) == 'posts')?  preg_replace('#[^a-z0-9_]#', '', strtolower($_GET['handle'])) : '')));
		
		$handle = ($h == '')? $handle : $h;

		if($handle == ''){
			$handle = strtolower(auth::fetchUserHandle(preg_replace('#[^0-9]#', '', $_SESSION['uid'])));
		}
		if(!isset($_POST['lastid'])){
			if(auth::confirmHandle($handle) == 0){
				echo '<div class="post_error">Message doesn\'t exist (43 fetch_post '.$handle.')</div>';
				return;
			}

			if(posts::confirmPost($oid, $handle) == 0){
				echo '<div class="post_error">Message doesn\'t exist (fetch_post post id '.$_GET['handle'].' & '.$oid.')</div>';
				return;
			}
		}

		$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, uid, parid
			FROM pmap
			WHERE id = :id
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$id = $row['id'];
				$uid = $row['uid'];
				$parid = $row['parid'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uname, handle
			FROM users
			WHERE uid = :uid
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$uname = $row['uname'];
				$handle = $row['handle'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
			
		$hsmall = strtolower($handle);
		$hdir = auth::fetchUserDir($hsmall);

		$liked = 0;
		$reposted = 0;
		if(isset($_SESSION['uid'])){
			$sessid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT uname, handle
				FROM users
				WHERE uid = :uid
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':uid', $sessid, SQLITE3_INTEGER);
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$uhandle = $row['handle'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();

			$uhdir = auth::fetchUserDir(strtolower($uhandle)).strtolower($uhandle);

			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$uhdir.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT id
				FROM likes
				WHERE id = :id
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $query->execute();

				if($resrow = $result->fetchArray(SQLITE3_ASSOC)){
					$liked = 1;
				}

				$result->finalize();
				$query->close();
			}

			$query = '
				SELECT id
				FROM reposts
				WHERE id = :id
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $query->execute();

				if($resrow = $result->fetchArray(SQLITE3_ASSOC)){
					$reposted = 1;
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
		}

		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, message, parid, likes, reposts, responses, timestamp, paruid, parhandle
			FROM posts
			WHERE id = :id
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data[] = array(
					'uname' => $uname,
					'handle' => $handle,
					'avatar' => $hdir,
					'id' => $row['id'],
					'message' => $row['message'],
					'parid' => $row['parid'],
					'like_count' => $row['likes'],
					'repost_count' => $row['reposts'],
					'response_count' => $row['responses'],
					'timestamp' => $row['timestamp'],
					'paruid' => $row['paruid'],
					'parhandle' => $row['parhandle'],
					'uid' => $uid,
					'liked' => $liked,
					'reposted' => $reposted
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $data;
	}

	public static function fetch_responses($id, $handle){
		$where = (isset($_POST['lastid']))? ' AND res_id < :lastid' : '';
		if(isset($_POST['lastid'])){
			$paruid = preg_replace('#[^0-9]#', '', $_POST['paruid']);
			$parhandle = preg_replace('#[^a-z0-9_]#', '', strtolower(auth::fetchUserHandle($paruid)));
		}else{
			$parhandle = '';
		}
		$handle = (isset($_POST['lastid']))? $parhandle : $handle;
		if(auth::confirmHandle($handle) == 0){
			echo '<div class="post_error">Message doesn\'t exist (handle = '.$handle.' handle error)</div>';
		}

		$id = preg_replace('#[^0-9]#', '', $id);
		if(posts::confirmPost($id, $handle) == 0){
			echo '<div class="post_error">Message doesn\'t exist (handle = '.$handle.' id = '.$id.' id error)</div>';
		}

		$hdir = auth::fetchUserDir($handle);

		$data_one = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT res_id, res_handle
			FROM responses
			WHERE par_id = :id'.$where.'
			ORDER BY res_id DESC
			LIMIT 5
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			if(isset($_POST['lastid'])){
				$lastid = preg_replace('#[^0-9]#', '', $_POST['lastid']);
				$query->bindValue(':lastid', $lastid, SQLITE3_INTEGER);
			}
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data_one[] = array(
					'res_id' => $row['res_id'],
					'res_handle' => $row['res_handle']
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		$data = array();
		foreach($data_one as $row_one){
			$handle = $row_one['res_handle'];
			$hsmall = strtolower($row_one['res_handle']);
			$hdir = auth::fetchUserDir($hsmall);
			$res_id = $row_one['res_id'];

			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT uname
				FROM profile
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$uname = $row['uname'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
			
			if(isset($uname)){
				$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
				$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
				$query = '
					SELECT uname, handle
					FROM users
					WHERE uid = :uid
					LIMIT 1
				';

				if($query = $db->prepare($query)){
					$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
					$result = $query->execute();

					while ($row = $result->fetchArray(SQLITE3_ASSOC)){
						$uhandle = $row['handle'];
					}

					$result->finalize();
					$query->close();
				}
				$db->close();

				$uhdir = auth::fetchUserDir(strtolower($uhandle)).strtolower($uhandle);

				$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
				$query = '
					SELECT id, message, parid, likes, reposts, responses, timestamp, paruid, parhandle
					FROM posts
					WHERE id = :id
					ORDER BY id DESC
					LIMIT 10
				';

				if($query = $db->prepare($query)){
					$query->bindValue(':id', $res_id, SQLITE3_INTEGER);
					$result = $query->execute();

					while ($row = $result->fetchArray(SQLITE3_ASSOC)){
						$lirep = auth::fetchLikes($row['id']);
						$liked = ($lirep == 1 || $lirep == 2)? 1 : 0;
						$reposted = ($lirep == 2 || $lirep == 3)? 1 : 0;

						$data[] = array(
							'uname' => $uname,
							'handle' => $handle,
							'avatar' => $hdir,
							'id' => $row['id'],
							'message' => $row['message'],
							'parid' => $row['parid'],
							'like_count' => $row['likes'],
							'repost_count' => $row['reposts'],
							'response_count' => $row['responses'],
							'timestamp' => $row['timestamp'],
							'paruid' => $row['paruid'],
							'parhandle' => $row['parhandle'],
							'liked' => $liked,
							'reposted' => $reposted
						);
					}

					$result->finalize();
					$query->close();
				}
				$db->close();
			}
		}

		return $data;
	}

	public static function submit_post($msg, $parid=0){
		$parhandle = '';
		$paruid = 0;
		$parespCount = 0;
		if($parid != 0){
			$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT uid
				FROM pmap
				WHERE id = :parid
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':parid', $parid, SQLITE3_INTEGER);
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$paruid = $row['uid'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();

			$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT handle
				FROM users
				WHERE uid = :paruid
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':paruid', $paruid, SQLITE3_INTEGER);
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$parhandle = $row['handle'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
		}

		$msg = trim($msg);
		$msg = strip_tags($msg);
		$string = str_replace(["\r\n", "\r", "\n"], " ", $msg);
		$string = str_replace("  ", " ", $string);
		$msg = char_convert_special($msg);
		$msg = str_replace(["\r\n", "\r", "\n"], "<br>", $msg);
		$msg = str_replace("<br><br>", "<p>", $msg);
		
		$keywords = '';
		$blacklist = array('and', 'but', 'the');

		$string = strtolower($string);
		$string = preg_replace('#[^a-z0-9 ]#', '', $string);
		$string = array_unique(explode(' ', $string));
		foreach($string as $row){
			if(strlen(str_replace("  ", " ", $keywords)) > 259){
				break;
			}else{
				if(strlen($row) > 2 && !in_array($row, $blacklist)){
					$keywords .= ' '.$row.' ';
				}
			}
		}
		$keywords = str_replace("  ", " ", $keywords);

		$date = date_create();
		$timestamp = date_timestamp_get($date);
		
		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uname, handle
			FROM users
			WHERE uid = :uid
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$uname = $row['uname'];
				$handle = $row['handle'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READWRITE);

		$query = 'INSERT INTO "msg" ("message", "parid", "timestamp", "usrid", "paruid", "handle", "uname", "parhandle") VALUES (:message, :parid, :timestamp, :userid, :paruid, :handle, :uname, :parhandle)';
		if($query = $db->prepare($query)){
			$query->bindValue(':message', $keywords, SQLITE3_TEXT);
			$query->bindValue(':parid', $parid, SQLITE3_INTEGER);
			$query->bindValue(':timestamp', $timestamp, SQLITE3_INTEGER);
			$query->bindValue(':userid', $uid, SQLITE3_INTEGER);
			$query->bindValue(':paruid', $paruid, SQLITE3_INTEGER);
			$query->bindValue(':handle', $handle, SQLITE3_TEXT);
			$query->bindValue(':uname', $uname, SQLITE3_TEXT);
			$query->bindValue(':parhandle', $parhandle, SQLITE3_TEXT);
			$query->execute();
			$query->close();
		}
		
		$query = 'SELECT last_insert_rowid() as id';
		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$last_id = $row['id'];
			}

			$result->finalize();
			$query->close();
		}

		$query = 'INSERT INTO "pmap" ("id", "uid", "parid") VALUES (:id, :uid, :parid)';
		if($query = $db->prepare($query)){
			$query->bindValue(':id', $last_id, SQLITE3_INTEGER);
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$query->bindValue(':parid', $parid, SQLITE3_INTEGER);
			$query->execute();
			$query->close();
		}
		$db->close();

		$hsmall = strtolower($handle);

		$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'db.db', SQLITE3_OPEN_READWRITE);
		$query = 'INSERT INTO "posts" ("id", "message", "parid", "timestamp", "paruid", "parhandle") VALUES (:id, :message, :parid, :timestamp, :paruid, :parhandle)';
		$query = $db->prepare($query);
		$query->bindValue(':id', $last_id, SQLITE3_INTEGER);
		$query->bindValue(':message', $msg, SQLITE3_TEXT);
		$query->bindValue(':parid', $parid, SQLITE3_INTEGER);
		$query->bindValue(':timestamp', $timestamp, SQLITE3_INTEGER);
		$query->bindValue(':paruid', $paruid, SQLITE3_INTEGER);
		$query->bindValue(':parhandle', $parhandle, SQLITE3_TEXT);
		$query->execute();
		$query->close();
		
		$query = '
			UPDATE counts
			SET posts = posts + 1
		';
		$query = $db->prepare($query);
		$query->execute();
		$query->close();

		$db->close();
		
		if($parid != 0){
			$hsmall = strtolower($parhandle);
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'db.db', SQLITE3_OPEN_READWRITE);
			$query = 'INSERT INTO "responses" ("par_id", "res_id", "res_handle") VALUES (:par_id, :res_id, :res_handle)';
			$query = $db->prepare($query);
			$query->bindValue(':par_id', $parid, SQLITE3_INTEGER);
			$query->bindValue(':res_id', $last_id, SQLITE3_INTEGER);
			$query->bindValue(':res_handle', $handle, SQLITE3_TEXT);
			$query->execute();
			$query->close();
			$query = '
				UPDATE posts
				SET responses = responses + 1
				WHERE id = :id
			';
			$query = $db->prepare($query);
			$query->bindValue(':id', $parid, SQLITE3_INTEGER);
			$query->execute();
			$query->close();
			$db->close();
			posts::send_notification('response', $paruid, $uid, $parid, $last_id);
		}

		return $last_id;
	}
	
	public static function submitPostAction($id, $type, $handle){
		if(isset($_SESSION['uid'])){
			$date=date_create();
			$timestamp = date_timestamp_get($date);
			$paruid = preg_replace('#[^0-9]#', '', auth::fetchUserId($handle));
			$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$handle = preg_replace('#[^a-z_]#', '', $handle);
			$hsmall = strtolower(auth::fetchUserHandle(preg_replace('#[^0-9]#', '', $uid)));
			
			$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READWRITE);
			if($type == 'repost'){				
				$check = 0;
				$query = '
					SELECT id
					FROM msg
					WHERE parid = :parid
					AND usrid = :uid
					AND repost = 1
					LIMIT 1
				';
				if($query = $db->prepare($query)){
					$query->bindValue(':parid', $id, SQLITE3_INTEGER);
					$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
					$result = $query->execute();

					if($row = $result->fetchArray(SQLITE3_ASSOC)){
						$check = 1;
					}

					$result->finalize();
					$query->close();
				}

				if($check == 0){
					$query = 'INSERT INTO "msg" ("usrid", "parid", "timestamp", "paruid", "parhandle", "repost") VALUES (:usrid, :parid, :timestamp, :paruid, :parhandle, 1)';
					$query = $db->prepare($query);
					$query->bindValue(':usrid', $uid, SQLITE3_INTEGER);
					$query->bindValue(':parid', $id, SQLITE3_INTEGER);
					$query->bindValue(':paruid', $paruid, SQLITE3_INTEGER);
					$query->bindValue(':timestamp', $timestamp, SQLITE3_INTEGER);
					$query->bindValue(':parhandle', $handle, SQLITE3_TEXT);
					$query->execute();
					$query->close();

					$query = 'SELECT last_insert_rowid() as id';
					if($query = $db->prepare($query)){
						$result = $query->execute();

						while ($row = $result->fetchArray(SQLITE3_ASSOC)){
							$last_id = $row['id'];
						}

						$result->finalize();
						$query->close();
					}

					$query = 'INSERT INTO "pmap" ("id", "uid", "parid", "repost") VALUES (:id, :uid, :parid, 1)';
					$query = $db->prepare($query);
					$query->bindValue(':id', $last_id, SQLITE3_INTEGER);
					$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
					$query->bindValue(':parid', $id, SQLITE3_INTEGER);
					$query->execute();
					$query->close();
				}
			}
				
			if($type == 'unrepost'){
				$query = '
					DELETE
					FROM msg
					WHERE parid = :id
					AND usrid = :uid
					AND repost = 1
				';
				$query = $db->prepare($query);
				$query->bindValue(':id', $id, SQLITE3_INTEGER);
				$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
				$query->execute();
				$query->close();

				$query = '
					DELETE
					FROM pmap
					WHERE parid = :id
					AND uid = :uid
					AND repost = 1
				';
				$query = $db->prepare($query);
				$query->bindValue(':id', $id, SQLITE3_INTEGER);
				$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
				$query->execute();
				$query->close();
			}
			$db->close();

			$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'db.db', SQLITE3_OPEN_READWRITE);
			if($type == 'repost'){
				
				$check = 0;
				$query = '
					SELECT id
					FROM posts
					WHERE parid = :parid
					AND repost = 1
					LIMIT 1
				';
				if($query = $db->prepare($query)){
					$query->bindValue(':parid', $id, SQLITE3_INTEGER);
					$result = $query->execute();

					if($row = $result->fetchArray(SQLITE3_ASSOC)){
						$check = 1;
					}

					$result->finalize();
					$query->close();
				}
				
				if($check == 0){
					$query = 'INSERT INTO "reposts" ("id") VALUES (:id)';
					$query = $db->prepare($query);
					$query->bindValue(':id', $id, SQLITE3_INTEGER);
					$query->execute();
					$query->close();
				}
			}
			
			if($type == 'unrepost'){
				$query = 'DELETE FROM reposts WHERE id = :id';
				$query = $db->prepare($query);
				$query->bindValue(':id', $id, SQLITE3_INTEGER);
				$query->execute();
				$query->close();
			}

			$query = ($type == 'like')?
				'INSERT INTO "likes" ("id") VALUES (:id)' :
				(($type == 'unlike')? 'DELETE FROM likes WHERE id = :id' :
				(($type == 'repost' && $check == 0)? 'INSERT INTO "posts" ("id", "parid", "timestamp", "paruid", "parhandle", "repost") VALUES (:last_id, :id, :timestamp, :paruid, :parhandle, 1)':
				(($type == 'unrepost')? 'DELETE FROM posts WHERE parid = :id AND repost = 1' : '')));
			$query = $db->prepare($query);
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			if($type == 'repost'){
				$query->bindValue(':last_id', $last_id, SQLITE3_INTEGER);
				$query->bindValue(':timestamp', $timestamp, SQLITE3_INTEGER);
				$query->bindValue(':paruid', $paruid, SQLITE3_INTEGER);
				$query->bindValue(':parhandle', $handle, SQLITE3_TEXT);
			}
			$query->execute();
			$query->close();

			$db->close();

			$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($handle).$handle.DS.'db.db', SQLITE3_OPEN_READWRITE);
			$query = ($type == 'like')?
				'UPDATE posts SET likes = likes + 1 WHERE id = :id' :
				(($type == 'unlike')? 'UPDATE posts SET likes = likes - 1 WHERE id = :id AND likes > 0' :
				(($type == 'repost')? 'UPDATE posts SET reposts = reposts + 1 WHERE id = :id' :
				(($type == 'unrepost')? 'UPDATE posts SET reposts = reposts - 1 WHERE id = :id AND reposts > 0' : '')));
			$query = $db->prepare($query);
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			$query->execute();
			$query->close();
			$db->close();

			if($type == 'like' || $type == 'repost'){
				posts::send_notification($type, $paruid, $uid, $id, 0);
			}
		}
	}
	
	public static function send_notification($type, $uid, $paruid, $pid, $resp_id){
		$sesuid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		if($sesuid != $uid){
			$resp_id = preg_replace('#[^0-9]#', '', $resp_id);
			$pid = preg_replace('#[^0-9]#', '', $pid);
			if($type !== 'follow'){
				$ntype = ($type == 'response')? 1 : (($type == 'repost')? 2 : 3);
				$hsmall = strtolower(auth::fetchUserHandle(preg_replace('#[^0-9]#', '', $uid)));
				$check = 0;

				$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'notifications.db', SQLITE3_OPEN_READONLY);
				$query = '
					SELECT from_id
					FROM notifications
					WHERE from_id = :from_id
					AND post_id = :post_id
					AND ntype = :ntype
					AND resp_id = :resp_id
					LIMIT 1
				';
				if($query = $db->prepare($query)){
					$query->bindValue(':from_id', $paruid, SQLITE3_INTEGER);
					$query->bindValue(':post_id', $pid, SQLITE3_INTEGER);
					$query->bindValue(':ntype', $ntype, SQLITE3_INTEGER);
					$query->bindValue(':resp_id', $resp_id, SQLITE3_INTEGER);
					$result = $query->execute();

					if($row = $result->fetchArray(SQLITE3_ASSOC)){
						$check = 1;
					}

					$result->finalize();
					$query->close();
				}
				$db->close();
				
				if($check == 0){
					$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'notifications.db', SQLITE3_OPEN_READWRITE);
					$query = 'INSERT INTO "notifications" ("from_id", "post_id", "ntype", "resp_id") VALUES (:from_id, :post_id, :ntype, :resp_id)';
					$query = $db->prepare($query);
					$query->bindValue(':from_id', $paruid, SQLITE3_INTEGER);
					$query->bindValue(':post_id', $pid, SQLITE3_INTEGER);
					$query->bindValue(':ntype', $ntype, SQLITE3_INTEGER);
					$query->bindValue(':resp_id', $resp_id, SQLITE3_INTEGER);
					$query->execute();
					$query->close();

					$db->close();

					$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'db.db', SQLITE3_OPEN_READWRITE);
					$query = '
						UPDATE counts
						SET notifications = notifications + 1
					';
					$query = $db->prepare($query);
					$query->execute();
					$query->close();

					$db->close();
				}
			}
		}
	}
}