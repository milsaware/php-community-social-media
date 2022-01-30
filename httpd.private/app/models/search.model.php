<?php
use authModel as auth;
class searchModel {

	public static function fetchPeople($q){
		$idArray = (isset($_POST['idArray']))? str_replace(',,', ',', rtrim($_POST['idArray'], ',')) : 0;
		$blacklist = array('and', 'but', 'the');
		$string = preg_replace('#[^a-zA-Z0-9_ ]#', '', $q);
		$qarray = array_unique(explode(' ', $string));
		$where = '(';
		for($i=0; $i < count($qarray); $i++){
			if(isset($qarray[$i])){
				$where .= ($i == 0)? ' keywords LIKE "% '.$qarray[$i].' %"' : ' OR keywords LIKE "%'.$qarray[$i].'%"';
			}
		}

		$where .= ($idArray != 0)? ') AND uid NOT IN ('.$idArray.')' : ')';
		
		$data = array();
		$db = new SQLite3(SYS.'db'.DS.'usr.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT uid, uname, handle
			FROM users
			WHERE '.$where.'
			LIMIT 26
		';

		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$uname = $row['uname'];
				$handle = $row['handle'];
				$hsmall = strtolower($handle);
				$unsmall = strtolower($uname);

				$uhdir = auth::fetchUserDir(strtolower($handle)).strtolower($handle);
				$dbfile = SYS.'db'.DS.'user'.DS.$uhdir.DS.'db.db';
				if(file_exists($dbfile)){
					$bio = '';
					$dba = new SQLite3($dbfile, SQLITE3_OPEN_READONLY);
					$uquery = '
						SELECT bio
						FROM profile
						LIMIT 1
					';
					if($uquery = $dba->prepare($uquery)){
						$uresult = $uquery->execute();

						while($urow = $uresult->fetchArray(SQLITE3_ASSOC)){
							$bio = $urow['bio'];
						}

						$uresult->finalize();
						$uquery->close();
					}
					$dba->close();

					$hdir = '';
					$hsplit = str_split(strtolower($handle));
					foreach($hsplit as $h){
						$hdir .= $h.DS;
					}
					$score = 0;
					$unameArray = explode(' ', $unsmall);
					foreach($unameArray as $unrow){
						$rowSmall = strtolower($unrow);
						if(in_array($rowSmall, $qarray)){
							$score++;
						}
					}
					$data[] = array(
						'uid' => $row['uid'],
						'uname' => $uname,
						'handle' => $handle,
						'hdir' => $hdir,
						'score' => $score,
						'bio' => $bio
					);
				}
			}

			$result->finalize();
			$query->close();
		}
		$db->close();

		$score = array_column($data, 'score');
		array_multisort($score, SORT_DESC, $data);
		
		return $data;
	}

	public static function fetchPosts($q){
		$idArray = (isset($_POST['idArray']))? str_replace(',,', ',', rtrim($_POST['idArray'], ',')) : 0;
		$blacklist = array('and', 'but', 'the');
		$string = strtolower($q);
		$string = preg_replace('#[^a-z0-9 ]#', '', $string);
		$qarray = array_unique(explode(' ', $string));
		$where = '(message LIKE "% '.$q.' %"';
		for($i=0; $i < count($qarray); $i++){
			if(isset($qarray[$i]) && !in_array($qarray[$i], $blacklist)){
				$where .= ' OR message LIKE "% '.$qarray[$i].' %"';
			}
		}
		$where .= ($idArray != 0)? ') AND id NOT IN ('.$idArray.')' : ')';
		$data_one = array();
		$db = new SQLite3(SYS.'db'.DS.'posts.db', SQLITE3_OPEN_READONLY);
		$query = '
			SELECT id, usrid, parid, message
			FROM msg
			WHERE '.$where.'
			ORDER BY id DESC
			LIMIT 26
		';
				
		if($query = $db->prepare($query)){
			$result = $query->execute();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)){
				$score = 0;
				$msg = strip_tags($row['message']);
				$msg = preg_replace("/[^a-zA-z0-9 ]+/", "", $msg);
				$msg = preg_replace('/\s/', ' ', $msg);
				$msg = strtolower($msg);
				$mesaageArray = explode(' ', $msg);
				foreach($mesaageArray as $mrow){
					$rowSmall = strtolower($mrow);
					if(in_array($rowSmall, $qarray)){
						$score++;
					}
				}
				$data_one[] = array(
					'id' => $row['id'],
					'uid' => $row['usrid'],
					'parid' => $row['parid'],
					'score' => $score
				);
				
			}

			$result->finalize();
			$query->close();
		}else{
			mysqli_error($db);
		}
		$db->close();

		$data = array();
		foreach($data_one as $row_one){
			$from_id = preg_replace('#[^0-9]#', '', $row_one['uid']);
			$usrRow = auth::fetchUserDet($from_id);

			foreach($usrRow as $par_row){
				$uname = $par_row['uname'];
				$handle = $par_row['handle'];
			}
			
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

			$hsmall = strtolower($par_row['handle']);
			$hdir = auth::fetchUserDir($handle);
			$db = new SQLite3(SYS.'db'.DS.'user'.DS.$hdir.$hsmall.DS.'db.db', SQLITE3_OPEN_READONLY);
			$query = '
				SELECT id, parid, message, likes, reposts, responses, timestamp, parhandle
				FROM posts
				WHERE id = '.$row_one['id'].'
				LIMIT 50
			';

			if($query = $db->prepare($query)){
				$result = $query->execute();

				while ($row = $result->fetchArray(SQLITE3_ASSOC)){
					$lirep = auth::fetchLikes($row_one['id']);
					$liked = ($lirep == 1 || $lirep == 2)? 1 : 0;
					$reposted = ($lirep == 2 || $lirep == 3)? 1 : 0;

					$data[] = array(
						'uid' => $from_id,
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
						'score' => $row_one['score'],
						'liked' => $liked,
						'reposted' => $reposted
					);
				}

				$result->finalize();
				$query->close();
			}
			$db->close();
		}
		$scoreSort = array_column($data, 'score');
		array_multisort($scoreSort, SORT_DESC, $data);
		return $data;
	}
}