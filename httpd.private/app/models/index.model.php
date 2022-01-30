<?php
include_model('auth');
use authModel as auth;
class indexModel {

	public static function fetch_timeline($uid){
		$last_id = (isset($_POST['last_id']))? preg_replace('#[^0-9]#', '', $_POST['last_id']) : 0;
		$uid = preg_replace('#[^0-9]#', '', $uid);
		$userdet = auth::fetchUserDet($uid);
		foreach($userdet as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}

		$hsmall = strtolower($handle);
		$hdir = '';
		$hsplit = str_split($hsmall);
		foreach($hsplit as $h){
			$hdir .= $h.DS;
		}
		$hdir .= $hsmall.DS;
		
		$data_one = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid
			FROM following
			LIMIT 250
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data_one[] = array(
					'uid' => preg_replace('#[^0-9]#', '', $row['uid'])
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
		
		$where = ' uid IN (';
		foreach($data_one as $row){
			$where .= $row['uid'].',';
		}
		$where .= $uid.') ';
		$where .= ($last_id != 0)? ' AND id < '.$last_id : '';

		$data_two = array();
		$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, uid, parid
			FROM pmap
			WHERE '.$where.' 
			ORDER BY id DESC 
			LIMIT 26
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data_two[] = array(
					'incId' => $row['id'],
					'id' => $row['id'],
					'uid' => $row['uid'],
					'parid' => $row['parid']
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		$data = array();
		foreach($data_two as $row_one){
			$from_id = preg_replace('#[^0-9]#', '', $row_one['uid']);
			$usrRow = auth::fetchUserDet($from_id);

			foreach($usrRow as $par_row){
				$uname = $par_row['uname'];
				$handle = $par_row['handle'];
			}
			$fhdir = auth::fetchUserDir($handle);
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$fhdir.$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle, repost
				FROM posts
				WHERE id = :id
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':id', $row_one['id'], SQLITE3_INTEGER);
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					if($row['repost'] == 1){
						$lirep =  auth::fetchLikes($row['parid']);
						$liked = ($lirep == 1 || $lirep == 2)? 1 : 0;
						$reposted = ($lirep == 2 || $lirep == 3)? 1 : 0;
						$repostHandle = $handle;
						$repdir = auth::fetchUserDir(strtolower($row['parhandle']));
						$rephan = strtolower($row['parhandle']);
						$repdb = new SQLite3(SYS.'db'.DS.'user'.DS.$repdir.$rephan.DS.'db.db', SQLITE3_OPEN_READONLY);
						$repquery = '
							SELECT uname
							FROM profile
							LIMIT 1
						';
						if($repquery = $repdb->prepare($repquery)){
							$represult = $repquery->execute();

							while ($reprow = $represult->fetchArray(SQLITE3_ASSOC)){
								$repuname = $reprow['uname'];
							}
							$represult->finalize();
							$repquery->close();
						}

						$repquery = '
							SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle, repost
							FROM posts
							WHERE id = :id
							LIMIT 1
						';
						if($repquery = $repdb->prepare($repquery)){
							$repquery->bindValue(':id', $row['parid'], SQLITE3_INTEGER);
							$represult = $repquery->execute();

							while ($reprow = $represult->fetchArray(SQLITE3_ASSOC)){
								$data[] = array(
									'id' => $reprow['id'],
									'parid' => $reprow['parid'],
									'handle' => $row['parhandle'],
									'uname' => $repuname,
									'avatar' => $repdir,
									'message' => $reprow['message'],
									'like_count' => $reprow['likes'],
									'repost_count' => $reprow['reposts'],
									'response_count' => $reprow['responses'],
									'timestamp' => $reprow['timestamp'],
									'parhandle' => $reprow['parhandle'],
									'liked' => $liked,
									'reposted' => $reposted,
									'repostHandle' => $repostHandle,
									'asrepost' => 1,
									'incId' => $row_one['id'],
									'count' => count($data_two)
								);
							}
							$represult->finalize();
							$repquery->close();
						}
						$repdb->close();
					}else{
						$lirep =  auth::fetchLikes($row['id']);
						$liked = ($lirep == 1 || $lirep == 2)? 1 : 0;
						$reposted = ($lirep == 2 || $lirep == 3)? 1 : 0;
						$data[] = array(
							'id' => $row['id'],
							'parid' => $row['parid'],
							'handle' => $handle,
							'uname' => $uname,
							'avatar' => $fhdir,
							'message' => $row['message'],
							'like_count' => $row['likes'],
							'repost_count' => $row['reposts'],
							'response_count' => $row['responses'],
							'timestamp' => $row['timestamp'],
							'parhandle' => $row['parhandle'],
							'liked' => $liked,
							'reposted' => $reposted,
							'asrepost' => 0,
							'incId' => $row_one['id'],
							'count' => count($data_two)
						);
					}
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
		}
		return $data;
	}

	public static function fetch_messages($uid){
		$uid = preg_replace('#[^0-9]#', '', $uid);

		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uname, handle
			FROM users
			WHERE uid = :uid
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':uid', $row_one['uid'], SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$uname = $row['uname'];
				$handle = $row['handle'];
			}

			$result->finalize();
			$query->close();

			$hsmall = strtolower($handle);
			$hdir = '';
			$hsplit = str_split($hsmall);
			foreach($hsplit as $h){
				$hdir .= $h.DS;
			}

			$udb = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT message, likes, reposts, responses, timestamp
				FROM posts
				LIMIT 10
			';
				
			if($query = $udb->prepare($query)){
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$data[] = array(
						'handle' => $handle,
						'uname' => $uname,
						'avatar' => $hdir,
						'message' => $row['message'],
						'like_count' => $row['likes'],
						'repost_count' => $row['reposts'],
						'response_count' => $row['responses'],
						'timestamp' => $row['timestamp']
					);
				}

				$result->finalize();
				$query->close();
			}
			$udb->close();
		}
		$db->close();

		return $data;
	}
}