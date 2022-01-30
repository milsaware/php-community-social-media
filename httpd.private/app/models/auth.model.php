<?php
use authModel as auth;
class authModel {
	
	public static function loginSubmit(){
		$return = 0;
		$handle_exists = 0;
		$handle = preg_replace('#[^a-zA-Z0-9_]#', '', $_POST['handle']);
		$hsmall = strtolower($handle);
		$pswd = $_POST['pswd'];
		
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid, handle
			FROM users
			WHERE hsmall = :hsmall
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':hsmall', $hsmall, SQLITE3_TEXT);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$handle_exists = 1;
				$userId = $row['uid'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
		
		
		if($handle_exists == 1){
			$hdir = authModel::fetchUserDir($hsmall);
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$hsmall.DS.'enc.db', SQLITE3_OPEN_READONLY);

			$query = '
				SELECT enc_one
				FROM enc
				LIMIT 1
			';
			if($query = $db->prepare($query)){
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$enc_one = $row['enc_one'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
			
			if (password_verify($pswd, $enc_one)) {
				$return = $userId;
			}
		}

		return $return;
	}
	
	public static function registerSubmit(){
		$handle_exists = 0;
		$return = 0;
		$name = char_convert_special($_POST['name']);
		$handle = preg_replace('#[^a-zA-Z0-9_]#', '', $_POST['handle']);
		$hsmall = strtolower($handle);
		$pswd = password_hash($_POST['pswd'], PASSWORD_ARGON2I);
		
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid, handle
			FROM users
			WHERE hsmall = :hsmall
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':hsmall', $hsmall, SQLITE3_TEXT);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$handle_exists = 1;
				$userId = $row['uid'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
		
		if($handle_exists == 0){
			$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READWRITE);

			$query = 'INSERT INTO "users" ("uname", "handle", "hsmall") VALUES (:uname, :handle, :hsmall)';
			$query = $db->prepare($query);
			$query->bindValue(':uname', $name, SQLITE3_TEXT);
			$query->bindValue(':handle', $handle, SQLITE3_TEXT);
			$query->bindValue(':hsmall', $hsmall, SQLITE3_TEXT);
			$query->execute();
			$query->close();
			
			$query = '
				SELECT uid
				FROM users
				WHERE hsmall = :hsmall
				LIMIT 1
			';

			if($query = $db->prepare($query)){
				$query->bindValue(':hsmall', $hsmall, SQLITE3_TEXT);
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$return = $row['uid'];
				}

				$result->finalize();
				$query->close();
			}
			$db->close();

			$hsplit = str_split(strtolower($hsmall));
			$dbDir = SYS.'db'.DS.'user'.DS;
			$imgDir = USRIMGROOT.DS;
			foreach($hsplit as $h){
				$dbDir .= $h.DS;
				if (!is_dir($dbDir)) {
					mkdir($dbDir);
				}
				$imgDir .= $h.DS;
				if (!is_dir($imgDir)) {
					mkdir($imgDir);
				}
			}
			if (!is_dir($dbDir.$hsmall)) {
				mkdir($dbDir.$hsmall);
			}
			if (!is_dir($imgDir.$hsmall)) {
				mkdir($imgDir.$hsmall);
			}

			$source = SYS.'db'.DS.'user'.DS.'blank.db';
			$dest = $dbDir.$hsmall.DS.'db.db';
			if(!is_file($dest)){
				copy($source, $dest);
			}

			$db = new SQLite3($dest, SQLITE3_OPEN_READWRITE);
			$query = 'INSERT INTO "profile" ("uname", "handle") VALUES (:uname, :handle)';
			$query = $db->prepare($query);
			$query->bindValue(':uname', $name, SQLITE3_TEXT);
			$query->bindValue(':handle', $handle, SQLITE3_TEXT);
			$query->execute();
			$query->close();
			$db->close();

			$source = SYS.'db'.DS.'user'.DS.'notifications.blank.db';
			$dest = $dbDir.$hsmall.DS.'notifications.db';
			if(!is_file($dest)){
				copy($source, $dest);
			}

			$source = SYS.'db'.DS.'user'.DS.'enc.blank.db';
			$dest = $dbDir.$hsmall.DS.'enc.db';
			if(!is_file($dest)){
				copy($source, $dest);
			}

			$db = new SQLite3($dest, SQLITE3_OPEN_READWRITE);
			$query = 'INSERT INTO "enc" ("enc_one") VALUES (:enc_one)';
			$query = $db->prepare($query);
			$query->bindValue(':enc_one', $pswd, SQLITE3_TEXT);
			$query->execute();
			$query->close();
			$db->close();

			$imgsource = IMGROOT.DS.'default_avatar.png';
			$imgdest = $imgDir.$hsmall.DS.'avatar.png';
			if(!is_file($imgdest)){
				copy($imgsource, $imgdest);
			}

			$imgsource = IMGROOT.DS.'default_banner.png';
			$imgdest = $imgDir.$hsmall.DS.'banner.png';
			if(!is_file($imgdest)){
				copy($imgsource, $imgdest);
			}
		}

		return $return;
	}

	public static function fetchLikes($id){
		$id = preg_replace('#[^0-9]#', '' , $id);
		$uid = preg_replace('#[^0-9]#', '' , $_SESSION['uid']);
		$hsmall = strtolower(auth::fetchUserHandle($uid));
		$dir = auth::fetchUserDir($hsmall);
		$liked = 0;
		$reposted = 0;
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.$dir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id
			FROM likes
			WHERE id = :id
			LIMIT 1
		';
		if($query = $db->prepare($query)){
			$query->bindValue(':id', $id, SQLITE3_INTEGER);
			$result = $query->execute();
			if($row = $result->fetchArray(SQLITE3_ASSOC)){
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

			if($row = $result->fetchArray(SQLITE3_ASSOC)){
				$reposted = 1;
			}

			$result->finalize();
			$query->close();
		}
		$db->close();
		
		if($liked == 0 && $reposted == 0){
			$return = 0;
		}elseif($liked == 1 && $reposted == 0){
			$return = 1;
		}elseif($liked == 1 && $reposted == 1){
			$return = 2;
		}elseif($liked == 0 && $reposted == 1){
			$return = 3;
		}
		return $return;
	}

	public static function fetchMuted($id){
		if(isset($_SESSION['uid'])){
			$uid = preg_replace('#[^0-9]#', '', $_SESSION['uid']);
			$hsmall = strtolower(auth::fetchUserHandle($uid));
			$dir = auth::fetchUserDir($hsmall);
			$muted = 0;
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$dir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT uid
				FROM muted
				WHERE uid = :uid
				LIMIT 1
			';
			if($query = $db->prepare($query)){
				$query->bindValue(':uid', $id, SQLITE3_INTEGER);
				$result = $query->execute();
				if($row = $result->fetchArray(SQLITE3_ASSOC)){
					$muted = 1;
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
			return $muted;
		}
	}

	public static function fetchUserId($handle){
		$id = '';
		$hsmall = strtolower($handle);
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid
			FROM users
			WHERE hsmall = :hsmall
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':hsmall', $hsmall, SQLITE3_TEXT);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$id = $row['uid'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $id;
	}

	public static function fetchUserHandle($uid){
		$handle = '';
		$hsmall = strtolower($handle);
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT handle
			FROM users
			WHERE uid = :uid
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$handle = $row['handle'];
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $handle;
	}

	public static function fetchUserDet($uid){
		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid, handle
			FROM users
			WHERE uid = :uid
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':uid', $uid, SQLITE3_INTEGER);
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$handle = $row['handle'];
				$uhdir = auth::fetchUserDir(strtolower($handle)).strtolower($handle);

				$dba = new SQLite3(SYS.'db'.DS.'user'.DS.$uhdir.DS.'db.db', SQLITE3_OPEN_READONLY);
				$uquery = '
					SELECT uname, handle, bio
					FROM profile
					LIMIT 1
				';
				if($uquery = $dba->prepare($uquery)){
					$uresult = $uquery->execute();

					while($urow = $uresult->fetchArray(SQLITE3_ASSOC)){
						$data[] = array(
							'uid' => $row['uid'],
							'uname' => $urow['uname'],
							'handle' => $row['handle'],
							'bio' => $urow['bio']
						);
					}

					$uresult->finalize();
					$uquery->close();
				}
				$dba->close();
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $data;
	}

	public static function fetchUserDir($handle){
		$hdir = '';
		$hsplit = str_split(strtolower($handle));
		foreach($hsplit as $h){
			$hdir .= $h.DS;
		}
		return $hdir;
	}
	
	public static function confirmHandle($handle){
		$return = 0;
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT hsmall
			FROM users
			WHERE hsmall = :handle
			LIMIT 1
		';

		if($query = $db->prepare($query)){
			$query->bindValue(':handle', $handle, SQLITE3_TEXT);
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
	
	public static function isBlocked($handle){
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($hsmall).$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
	}
	
	public static function fetchCounts($uid){
		$usrRow = auth::fetchUserDet($uid);

		foreach($usrRow as $row){
			$uname = $row['uname'];
			$handle = $row['handle'];
		}

		$hdir = auth::fetchUserDir($handle).strtolower($handle).DS;

		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'user'.DS.auth::fetchUserDir($handle).$handle.DS.'db.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT likes, reposts, posts, following, followers
			FROM counts
			LIMIT 1
		';
		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$data[] = array(
					'likes' => $row['likes'],
					'reposts' => $row['reposts'],
					'posts' => $row['posts'],
					'following' => $row['following'],
					'followers' => $row['followers']
				);
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		return $data;
	}
	
}