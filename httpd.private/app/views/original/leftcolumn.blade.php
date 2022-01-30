<?php
$home_widget = '
	<div class="section-block" id="homeNav">
		<div id="mainHome" class="'.$homeClass.'">
			<div class="sln-icon-block">
				<i class="fa fa-home nav-icon"></i>
			</div>
			<div class="sln-span-block">
				<span class="sln-span">Home</span>
			</div>
		</div>

		<div id="mainNot" class="'.$notClass.'">
			<div class="sln-icon-block">
				<i class="fa fa-bell nav-icon"></i>
			</div>
			<div class="sln-span-block">
				<span class="sln-span">Notifications</span>
				<span id="not-count"></span>
			</div>
		</div>

		<div id="mainSettings" class="'.$setClass.'">
			<div class="sln-icon-block">
				<i class="fa fa-cogs nav-icon"></i>
			</div>
			<div class="sln-span-block">
				<span class="sln-span">Settings</span>
			</div>
		</div>

		<div id="mainPost" class="sl-nav">
			<div class="sln-icon-block">
				<i class="fa fa-pencil-alt nav-icon"></i>
			</div>
			<div class="sln-span-block">
				<span class="sln-span">Post</span>
			</div>
		</div>

		<div id="mainSrch" class="sl-nav">
			<div class="sln-icon-block">
				<i class="fa fa-search nav-icon"></i>
			</div>
			<div class="sln-span-block">
				<span class="sln-span">Search</span>
			</div>
		</div>
	</div>
';

$search_widget = '
	<div id="lssearch-block" class="section-block" data-type="'.$action.'">
		<input type="text" id="ls-search" placeholder="search ..." value="'.$search_term.'">
		<span id="search_submit"><i class="fa fa-search"></i></span>
	</div>
';

$profile_following_widget = (isset($following_active) && isset($followers_active))? '
	<div class="section-block">
		<div class="pbfoptmenu noslct">
			<span id="profollowing" class="'.$following_active.'">FOLLOWING</span>
			<span class="pbfopt-mbdiv"> | </span>
			<span id="profollowers" class="'.$followers_active.'">FOLLOWERS</span>
		</div>
	</div>
' : '';

$b = (isset($bio))? $bio : '';

$profile_desc_widget = '
	<div class="section-block">
		<div class="profile-bio">'.$b.'</div>
	</div>
';

$main_profile_widget = (isset($hdir) && isset($uname) && isset($handle) && isset($postsCount) && isset($followersCount) && isset($followingCount))? '
	<div class="section-block" id="prowid" data-id="'.$handle.'">
		<img src="/assets/images/usr/'.$hdir.'banner.png" class="sl-banner">
		<img src="/assets/images/usr/'.$hdir.'avatar.png" class="sl-avatar">
		<span class="sl-profilename">'.$uname.'</span>
		<span class="sl-profilehandle">@'.$handle.'</span>
		<div class="slp-nav">
			<div class="slpnav-block">
				<span class="slpnb-count">'.$postsCount.'</span><br>
				<span class="slpnb-dsc">posts</span>
			</div>
			<div class="slpnav-block">
				<span class="slpnb-count">'.$followersCount.'</span><br>
				<span class="slpnb-dsc">followers</span>
			</div>
			<div class="slpnav-block">
				<span class="slpnb-count">'.$followingCount.'</span><br>
				<span class="slpnb-dsc">following</span>
			</div>
		</div>
	</div>
' : '';

if(isset($profile_view)){
	$pdw = ($b != '')? $profile_desc_widget : '';
	echo '
		<div id="prflsctlft">
			'.$pdw.'
			'.$profile_following_widget.'
			'.$home_widget.'
			'.$search_widget.'
		</div>
	';
}else{
	echo '
		<section class="section-left">
			'.$main_profile_widget.'
			'.$home_widget.'
			'.$search_widget.'
		</section>
	';
}