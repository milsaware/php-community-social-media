<?php
use authModel as auth;
class notificationsModel {

	public static function fetch_notifications($uid, $type='new'){
		$last_id = (isset($_POST['last_id']))? preg_replace('#[^0-9]#', '', $_POST['last_id']) : 0;
		$uid = preg_replace('#[^0-9]#', '', $uid);
		$action = (isset($_GET['action']))? $_GET['action'] : '';
		$limit = 25;

		$data_one = array();
		$data_two = array();
		$usrRow = auth::fetchUserDet($uid);
		
		foreach($usrRow as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}
		
		$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;
		$where = (isset($_GET['action']) && $_GET['action'] == 'old')? 'WHERE viewed = 1' : 'WHERE viewed = 0';
		$where .= ($last_id == 0)? '' : ' AND id < '.$last_id;
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'notifications.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, from_id, post_id, ntype, viewed, resp_id
			FROM notifications
			'.$where.'
			ORDER BY id DESC
			LIMIT '.$limit.'
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$update_id = $row['id'];
				$data_one[] = array(
					'id' => $update_id,
					'from_id' => $row['from_id'],
					'post_id' => $row['post_id'],
					'ntype' => $row['ntype'],
					'viewed' => $row['viewed'],
					'resp_id' => $row['resp_id']
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
		
		$result = array();
		$valueKey = false;
		foreach ($data_one as $key => $val) {
			 $result[$key] = $val['id'];
				$valueKey = true;
		}
		if($valueKey == true && $action == ''){
			$minId = min($result);
			$maxId = max($result);
			
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'notifications.db', SQLITE3_OPEN_READWRITE);
			$query = 'UPDATE notifications SET viewed = 1 WHERE id BETWEEN '.$minId.' AND '.$maxId;
			$query = $db->prepare($query);
			$query->execute();
			$query->close();
			$db->close();
		}

		// 1 - response
		// 2 - repost
		// 3 - like
		// 4 - follow

		$data = array();
		foreach($data_one as $row_one){
			$authId = ($row_one['ntype'] == 1)? preg_replace('#[^0-9]#', '', $row_one['from_id']) : $uid;
			$whereId = ($row_one['ntype'] == 1)? $row_one['resp_id'] : $row_one['post_id'];
			$parent_handle = '';

			if($row_one['ntype'] == 2 || $row_one['ntype'] == 3){
				$parRow = auth::fetchUserDet($row_one['from_id']);
				foreach($parRow as $par_row){
					$parent_handle = $par_row['handle'];
				}
			}
			

			$usrRow = auth::fetchUserDet($authId);

			foreach($usrRow as $par_row){
				$uname = $par_row['uname'];
				$handle = $par_row['handle'];
			}

			$hsmall = strtolower($handle);
			$hdir = auth::fetchUserDir($hsmall);

			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle
				FROM posts
				WHERE id = '.$whereId.'
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$lirep = auth::fetchLikes($row['id']);
					$liked = ($lirep == 1 || $lirep == 2)? 1 : 0;
					$reposted = ($lirep == 2 || $lirep == 3)? 1 : 0;

					$data[] = array(
						'id' => $row['id'],
						'parid' => $row['parid'],
						'not_id' => $update_id,
						'handle' => $handle,
						'uname' => $uname,
						'avatar' => $hdir,
						'message' => $row['message'],
						'like_count' => $row['likes'],
						'repost_count' => $row['reposts'],
						'response_count' => $row['responses'],
						'timestamp' => $row['timestamp'],
						'parhandle' => $row['parhandle'],
						'parent_handle' => $parent_handle,
						'ntype' => $row_one['ntype'],
						'liked' => $liked,
						'reposted' => $reposted
					);
				}

				$result->finalize();
				$query->close();
			}
			$db->close();

		}
		return $data;
	}

	public static function fetch_count($uid){
		$usrRow = auth::fetchUserDet($uid);

		foreach($usrRow as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}

		$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;

		$value = 0;
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT notifications
			FROM counts
			LIMIT 1
		';
		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$value = $row['notifications'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $value;
	}

	public static function notificationReset($uid){
		$usrRow = auth::fetchUserDet($uid);

		foreach($usrRow as $row){
			$handle = $row['handle'];
		}

		$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($handle).strtolower($handle).DS.'db.db', SQLITE3_OPEN_READWRITE);
		$query = '
			UPDATE counts
			SET notifications = 0
		';
		$query = $db->prepare($query);
		$query->execute();
		$query->close();
		$db->close();
	}
}