<?php
$id_array = (isset($_POST['idArray']))? $_POST['idArray'] : '';
$vm = isset($viewMore)? $viewMore : 0;
$handle = (isset($_SESSION['uid']))? authModel::fetchUserHandle($_SESSION['uid']) : '';
if(isset($request) && $request == false && !isset($profile_view)){
	echo '<section id="section-right">';
}

if(!isset($req) && !isset($main_post) && !isset($op) && !isset($notifications) && !isset($fetch_new) && !isset($new_response) && !isset($new_response) && !isset($fetch_post) && !isset($profile_view)){
	echo (isset($viewHome) && $viewHome == 1)? '' : '
		<div class="section-block">
			<textarea id="msgIn"></textarea>
			<label for="msgIn">
				<div class="placeholder ns">
					<i class="fa fa-pencil-alt"></i>
					<span class="phtxt">create post</span>
				</div>
			</label>
			<div id="msgSubmit">
				<i class="fa fa-pencil-alt"></i>
				Post
			</div>
		</div>
	';
}
	
if((isset($request) && $request == false) || (isset($viewHome) && $viewHome == 0)){
	if(!isset($profile_view)){
		echo '<div id="tlwrp">';
	}
}

if(isset($profile_view) && isset($request) && $request == false){
	echo '<div id="prflsctrgt">';
}

if(isset($req) && $req == 0){
	echo '
		<div id="search_tl">
			<div class="section-block">
				<div class="notoptmenu">
					<span id="peopleSearch" class="'.$peopleClass.'">People</span>
					<span class="notoptdiv">.</span>
					<span id="postsSearch" class="'.$peoplePosts.'">Posts</span>
				</div>
			</div>
	';
}

if(isset($notifications) && ((isset($request) && $request == false) || (isset($fetch_not) && $fetch_not == 1))){
	echo '
		<div id="notifications_tl">
			<div id="notnav" class="section-block">
				<div class="notoptmenu">
					<span id="notNew" class="'.$newClass.'">New</span>
					<span class="notoptdiv">.</span>
					<span id="notOld" class="'.$oldClass.'">Old</span>
					<span id="dltall" class="notoptdlt">Delete all</span>
				</div>
			</div>
			<div id="nottl">
	';
}

if(isset($new_response)){
	echo '<div class="rspdivider"></div>';
}

$i = 0;
$max = 26;
if(isset($message) && count($message) > 0){
	foreach($message as $row){
		$i++;
		if($i != $max){
			$id = (isset($row['incId']))? $row['incId'] : ((isset($row['id']))? $row['id'] : $row['uid']);
			$pid = (isset($row['id']))? $row['id'] : $row['uid'];
			
			$parid = (isset($row['parid']) && $row['parid'] != 0)? ((isset($row['repost']) && $row['repost'] == 0)? $pid : $row['parid']) : $pid;
			
			$udir = authModel::fetchUserDir($row['handle']).strtolower($row['handle']);
			$opid = authModel::fetchUserId($row['handle']);
			if(authModel::fetchMuted($opid) == 0){
				if($vm == 0){
					if(!isset($btype) && !isset($op)){
						echo '<div class="section-block">';
					}

					if(isset($notifications)){
						$notclass = ($row['ntype'] == 1)? 'rspndto' : (($row['ntype'] == 2)? 'respBar' : 'likeBar');
						$rthmsg = ($row['ntype'] == 1)? ' Responded to you' : (($row['ntype'] == 2)? ' Reposted your comment' : ' Liked your comment');
						$nothandle = ($row['ntype'] == 1)? $row['handle'] : $row['parent_handle'];
						$spncls = ($row['ntype'] == 1)? 'rthn' : 'rth';
						$nh = ($nothandle != $handle)? $nothandle : 'yous';
						echo '
							<div class="'.$notclass.'" data-id="'.$row['parid'].'">
								<span class="'.$spncls.'">@'.$nh.$rthmsg.'</span>
							</div>
						';
					}
						
						
					if(!isset($notifications) && !isset($new_response)){
						if(isset($row['asrepost']) && $row['asrepost'] == 1){
							echo '
								<div class="respBar" data-id="'.$row['parid'].'">
									<span class="rth">'.$row['repostHandle'].' reposted</span>
								</div>
							';
						}elseif(isset($row['parid']) && $row['parid'] != 0){
							echo '
								<div class="rspndto" data-id="'.$row['parid'].'">
									<span class="rth">Responding to @'.$row['parhandle'].'</span>
								</div>
							';
						}
					}

					$avatar = (isset($row['avatar']))? ((isset($profile_view))? ((isset($profile_view_pg) && $profile_view_pg == 1)? $udir : $row['avatar']) : $row['avatar'].strtolower($row['handle'])) : $udir;

					$timestamp = (isset($row['timestamp']))? '<span class="slp-timestamp">'.date('d M Y H:i', $row['timestamp']).'</span>' : '';
					$tlcbclass = (isset($main_post) || isset($op))? ' tlcbc-main' : '';
					$msgrow = (isset($row['message']))? $row['message'] : ((isset($row['bio']))? $row['bio'] : '');
					
					$tlcbClass = (isset($_GET['action']) && strtolower($_GET['action']) == 'people')? 'tlcb-ps' : 'tlcb-clkwrp';
					
					echo '
						<div class="timeline-avatar-block" data-id="'.$row['handle'].'">				
							<img src="/assets/images/usr/'.$avatar.'/avatar.png" class="tlb-avatar" alt="op profile avatar">
						</div>
						<div class="timeline-content-block" data-id="'.$pid.'">
							<div class="'.$tlcbClass.'" data-id="'.$pid.'">
								<div class="tlcb-userdetails">
									<span class="tlcb-username">'.$row['uname'].'</span>
									<span class="tlcb-handle">@'.$row['handle'].'</span>
									<span class="slp-sep">.</span>
									'.$timestamp.'
								</div>
								<div class="tlcb-content'.$tlcbclass.'">
									'.$msgrow.'
								</div>
							</div>					
					';
					
					if((isset($_GET['action']) && strtolower($_GET['action']) !== 'people') || !isset($_GET['action'])){
						echo '<div class="tlcbnav-block noslct" data-id="'.$pid.'">';

						if(isset($row['response_count']) && isset($row['repost_count']) && isset($row['like_count'])){
							$repostedClass = ($row['reposted'] == 0)? '' : ' icon-reposted';
							$likedClass = ($row['liked'] == 0)? '' : ' icon-liked';
							echo '
								<div class="tlcb-icon-block">
									<i class="fa fa-comment tlcb-icon"></i>
									<span class="tlcb-count">'.$row['response_count'].'</span>
								</div>
								<div class="tlcb-icon-block">
									<i class="fa fa-reply tlcb-icon'.$repostedClass.'"></i>
									<span class="tlcb-count">'.$row['repost_count'].'</span>
								</div>

								<div class="tlcb-icon-block">
									<i class="fa fa-heart tlcb-icon'.$likedClass.'"></i>
									<span class="tlcb-count">'.$row['like_count'].'</span>
								</div>
								<div class="tlcb-icon-block">
									<i class="fa fa-angle-down carat-icon"></i>
								</div>
							';
						}
						echo '</div>';
					}
					echo '<div class="newRespBlock"></div>';
					echo '</div>';

					if(!isset($new_response)){
						echo '<div class="mrspdivider"></div>';
					}
					if(!isset($btype) && !isset($op)){
						echo '</div>';
					}
				}

				if(isset($pid)){
					$id_array .= ','.$pid.',';
				}
				$id_array = (isset($viewHome))? $row['incId'] : ((isset($notifications))? $row['not_id'] : trim(str_replace(',,', ',', $id_array), ','));
			}
		}
	}
	$originId = (isset($row['id']))? $row['id'] : $parid;
	$ouid = (isset($row['uid']))? $row['uid'] : $parid;

	echo '<input type="hidden" id="idArray" value="'.$id_array.'">';

	if(!isset($btype) && !isset($main_post) && !isset($op) && !isset($fetch_new) && isset($row) && (count($message) == $max || (isset($row['count']) && $row['count'] == $max))){
		$lastId = (isset($notifications))? $id_array : ((isset($row['incId']))? $row['incId'] : $originId);
		echo '<div id="viewMore" last-id="'.$lastId.'">View More</div>';
	}
}else{
		echo '<div class="notwarn">No results found</div>';
		$id_array = substr_replace($id_array ,"", -1);
	if(isset($new_response)){
		echo '</div>';
	}
}

if(isset($viewMore) && $viewMore == 0){
	if(!isset($op)){
		echo '<div class="rspdivider"></div>';
	}
	echo '<div class="respwrapper">';
}

 
if(isset($response) && isset($op) && count($response) > 0){
	foreach($response as $row){
		$opid = authModel::fetchUserId($row['handle']);
		if(authModel::fetchMuted($opid) == 0){
			$repostedClass = ($row['reposted'] == 0)? '' : ' icon-reposted';
			$likedClass = ($row['liked'] == 0)? '' : ' icon-liked';
			echo '
				<div class="timeline-avatar-block" data-id="'.$row['handle'].'">
					<img src="/assets/images/usr/'.$row['avatar'].strtolower($row['handle']).'/avatar.png" class="tlb-avatar" alt="response profile avatar">
				</div>
				<div class="timeline-content-block">
					<div class="tlcb-clkwrp" data-id="'.$row['id'].'">
						<div class="tlcb-userdetails">
							<span class="tlcb-username">'.$row['uname'].'</span>
							<span class="tlcb-handle">@'.$row['handle'].'</span>
							<span class="slp-sep">.</span>
							<span class="slp-timestamp">'.date('d M Y H:i', $row['timestamp']).'</span>
						</div>
						<div class="tlcb-content">
							'.$row['message'].'
						</div>
					</div>
					<div class="tlcbnav-block noslct" data-id="'.$row['id'].'">
						<div class="tlcb-icon-block">
							<i class="fa fa-comment tlcb-icon"></i>
							<span class="tlcb-count">'.$row['response_count'].'</span>
						</div>

						<div class="tlcb-icon-block">
							<i class="fa fa-reply tlcb-icon'.$repostedClass.'"></i>
							<span class="tlcb-count">'.$row['repost_count'].'</span>
						</div>

						<div class="tlcb-icon-block">
							<i class="fa fa-heart tlcb-icon'.$likedClass.'"></i>
							<span class="tlcb-count">'.$row['like_count'].'</span>
						</div>
						<div class="tlcb-icon-block">
							<i class="fa fa-angle-down carat-icon"></i>
						</div>
					</div>
					<div class="newRespBlock"></div>
				</div>
			';

			if($op != 0){
				echo '<div class="rspdivider"></div>';
			}
		}
	}
}

if(!isset($notifications) && isset($id)){
	$id = (isset($_row['id']) && !isset($response))? $_row['id'] : $id;
	echo '<div id="rowCount" count="'.count($message).'" lastid="'.$id.'" originid="'.$originId.'" paruid="'.$ouid.'"></div>';
}

if(isset($notifications)){
	echo '</div>';
}

if(isset($response) && count($response) == 5){
	echo '<div class="viewMore" count="'.count($response).'" lastid="'.$row['id'].'" originid="'.$originId.'" paruid="'.$ouid.'">View More</div>';
	echo '</div>';
}

echo '</div>';

if(isset($request) && $request == false){
	echo '</section>';
}

?>