<?php
use authModel as auth;
class profileModel {

	public static function fetchPosts($uid){
		$last_id = (isset($_POST['last_id']))? preg_replace('#[^0-9]#', '', $_POST['last_id']) : 0;
		$uid = preg_replace('#[^0-9]#', '', $uid);
		$limit = 50;
		$usrRow = auth::fetchUserDet($uid);
		
		foreach($usrRow as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}

		if(isset($_GET['data_one'])){
			$pg = strtolower(preg_replace('#[^a-zA-Z]#', '', $_GET['data_one']));
			$where = ($pg == 'responses')? 'parid != 0 AND repost = 0' : (($pg == 'reposts')? 'repost = 1' : 'parid = 0');
		}else{
			$where = 'parid = 0';
		}

		$where .= ($last_id != 0)? ' AND id < '.$last_id : '';

		$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;

		if(isset($_SESSION['uid'])){
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
		}
		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle, repost
			FROM posts
			WHERE '.$where.'
			ORDER BY id DESC
			LIMIT 26
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$originId =	($row['repost'] == 0)? $row['id'] : $row['parid'];
				$liked = 0;
				$reposted = 0;
				if(isset($_SESSION['uid'])){
					$cdb = new SQLite3(SYS.'db'.DS.'user'.DS.$uhdir.DS.'db.db', SQLITE3_OPEN_READONLY);
					$que = '
						SELECT id
						FROM likes
						WHERE id = :id
						LIMIT 1
					';

					if($que = $cdb->prepare($que)){
						$que->bindValue(':id', $originId, SQLITE3_INTEGER);
						$res = $que->execute();

						if($resrow = $res->fetchArray(SQLITE3_ASSOC)){
							$liked = 1;
						}

						$res->finalize();
						$que->close();
					}

					$que = '
						SELECT id
						FROM reposts
						WHERE id = :id
						LIMIT 1
					';

					if($que = $cdb->prepare($que)){
						$que->bindValue(':id', $row['id'], SQLITE3_INTEGER);
						$res = $que->execute();

						if($resrow = $res->fetchArray(SQLITE3_ASSOC)){
							$reposted = 1;
						}

						$res->finalize();
						$que->close();
					}
					$cdb->close();
				}
				if(isset($_GET['data_one']) && strtolower(preg_replace('#[^a-zA-Z]#', '', $_GET['data_one'])) == 'reposts'){
					$rhdir = auth::fetchUserDir($row['parhandle']).strtolower($row['parhandle']).DS;
					
					$rdb = new SQLite3(SYS.'db'.DS.'user'.DS.$rhdir.'db.db', SQLITE3_OPEN_READONLY);
					$que = '
						SELECT uname
						FROM profile
						LIMIT 1
					';
					if($que = $rdb->prepare($que)){
						$res = $que->execute();

						while($rrow = $res->fetchArray(SQLITE3_ASSOC)){
							$urname = $rrow['uname'];
						}
						$res->finalize();
						$que->close();
					}

					$que = '
						SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle, repost
						FROM posts
						WHERE id = :id
						LIMIT 1
					';
					if($que = $rdb->prepare($que)){
						$que->bindValue(':id', $row['parid'], SQLITE3_INTEGER);
						$res = $que->execute();

						while($rrow = $res->fetchArray(SQLITE3_ASSOC)){
							$data[] = array(
								'incId' => $originId,
								'id' => $rrow['id'],
								'parid' => $rrow['parid'],
								'handle' => $row['parhandle'],
								'uname' => $urname,
								'avatar' => $hdir,
								'message' => $rrow['message'],
								'like_count' => $rrow['likes'],
								'repost_count' => $rrow['reposts'],
								'response_count' => $rrow['responses'],
								'timestamp' => $rrow['timestamp'],
								'parhandle' => $rrow['parhandle'],
								'repost' => $rrow['repost'],
								'liked' => $liked,
								'reposted' => $reposted
							);
						}
						$res->finalize();
						$que->close();
					}
					$rdb->close();
				}else{
					$data[] = array(
						'id' => $row['id'],
						'parid' => $row['parid'],
						'handle' => $handle,
						'uname' => $uname,
						'avatar' => $hdir,
						'message' => $row['message'],
						'like_count' => $row['likes'],
						'repost_count' => $row['reposts'],
						'response_count' => $row['responses'],
						'timestamp' => $row['timestamp'],
						'parhandle' => $row['parhandle'],
						'repost' => $row['repost'],
						'liked' => $liked,
						'reposted' => $reposted
					);
				}
			}
			$result->finalize();
			$query->close();
		}

		$db->close();
		return $data;
	}

	public static function submitFollow($followId, $action){
		$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
		if($uid != $followId){
			$usrRow = auth::fetchUserDet($uid);
		
			foreach($usrRow as $row){
				$uname = $row['uname'];
				$handle = $row['handle'];
			}
			$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;
			$data = array();
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READWRITE);
			$query = ($action == 'follow')? 'INSERT INTO "following" ("uid") VALUES (:followId)' : 'DELETE FROM "following" WHERE "uid" = :followId';
			$query = $db->prepare($query);
			$query->bindValue(':followId', $followId, SQLITE3_INTEGER);
			$query->execute();
			$query->close();
			$db->close();

			$usrRow = auth::fetchUserDet($followId);

			foreach($usrRow as $row){
				$uname = $row['uname'];
				$handle = $row['handle'];
			}
			$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;
			$data = array();
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READWRITE);
			$query = ($action == 'follow')? 'INSERT INTO "followers" ("uid") VALUES (:uid)' : 'DELETE FROM "followers" WHERE "uid" = :uid';
			$query = $db->prepare($query);
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$query->execute();
			$query->close();
			$db->close();
		}
	}
	
	public static function fetchFollowing($type, $h){
		$uid = preg_replace('#[^0-9]#', '', auth::fetchUserId($h));
		$limit = 50;
		$usrRow = auth::fetchUserDet($uid);

		foreach($usrRow as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}

		$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;

		$data_one = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid
			FROM '.$type.'
			ORDER BY id ASC
			LIMIT 50
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data_one[] = array(
					'uid' => $row['uid']
				);
			}
			$result->finalize();
			$query->close();
		}
		$db->close();

		$i = 1;
		$idin = '';
		foreach ($data_one as $row){
			$idrow = preg_replace('#[^0-9]#', '', $row['uid']);
			$idin .= ($i == 1)? '"'.$idrow.'"' : ', "'.$idrow.'"';
			$i++;
		}

		$data_two = array();
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid, uname, handle, hsmall
			FROM users
			WHERE uid IN ('.$idin.')
			LIMIT 50
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data_two[] = array(
					'uid' => $row['uid'],
					'uname' => $row['uname'],
					'handle' => $row['handle'],
					'hsmall' => $row['hsmall']
				);
			}
			$result->finalize();
			$query->close();
		}
		$db->close();
		
		$data = array();
		foreach ($data_two as $row){
			$uhdir = auth::fetchUserDir(strtolower($row['handle'])).strtolower($row['handle']).DS;
			$dba = new SQLite3(SYS.'db'.DS.'user'.DS.$uhdir.'db.db', SQLITE3_OPEN_READONLY);
			$uquery = '
				SELECT uname, bio
				FROM profile
				LIMIT 1
			';
			if($uquery = $dba->prepare($uquery)){
				$uresult = $uquery->execute();

				while($urow = $uresult->fetchArray(SQLITE3_ASSOC)){
					$bio = $urow['bio'];
					$uname = $urow['uname'];
				}

				$uresult->finalize();
				$uquery->close();
			}
			$dba->close();

			$data[] = array(
				'avatar' => $uhdir,
				'id' => $row['uid'],
				'uname' => $row['uname'],
				'handle' => $row['handle'],
				'hsmall' => $row['hsmall'],
				'bio' => $bio
			);
		}

		return $data;
	}
	
	public static function isfollwing($followId, $h=''){
		$return = 0;
		if(isset($_SESSION['uid'])){
			$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$from = ($h == '')? 'following' : 'followers';
			$followId = ($h == '')? $followId : preg_replace('#[^0-9]#', '', auth::fetchUserId($h));
			if($uid != $followId){
				$usrRow = auth::fetchUserDet($uid);
			
				foreach($usrRow as $row){
					$uname = $row['uname'];
					$handle = $row['handle'];
				}
				$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;
				$data = array();
				$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READWRITE);
				$query = '
					SELECT uid
					FROM '.$from.'
					WHERE uid = :followId
					LIMIT 1
				';

				if($query = $db->prepare($query)){
					$query->bindValue(':followId', $followId, SQLITE3_INTEGER);
					$result = $query->execute();

					while ($row = $result->fetchArray(SQLITE3_ASSOC)){
						$return = 1;
					}
					$result->finalize();
					$query->close();
				}
				$db->close();
			}
		}

		return $return;
	}

	public static function muteUser(){
		if(isset($_SESSION['uid'])){
			$handle = strtolower($_POST['handle']);
			$handle = preg_replace('#[^a-z0-9_]#', '', $handle);
			$uid = auth::fetchUserId($handle);

			$suid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			if($suid != $uid){
				$shandle = auth::fetchUserHandle($suid);
				if(auth::confirmHandle($shandle) == 1){
					$hdir = auth::fetchUserDir($shandle).strtolower($shandle).DS;
					$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READWRITE);
					$query = 'INSERT INTO "muted" ("uid", "handle") VALUES (:uid, :handle)';
					$query = $db->prepare($query);
					$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
					$query->bindValue(':handle', $handle, SQLITE3_TEXT);
					$query->execute();
					$query->close();
					$db->close();
					echo 1;
				}else{
					echo 'handle not exist';
				}
			}else{
				echo 'cant mute yourself!';
			}
		}
	}
}