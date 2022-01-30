<nav>
	<a href="<?php echo BASEURL;?>">
		<img src="/assets/images/icon.png" class="nav-logo" alt="site logo">
	</a>
</nav>

<div id="content-wrapper">
<?php if(isset($profile_view)){?>
<section id="profile-section">
		<img src="<?php echo $usr['banner'];?>" class="profile-banner" alt="profile banner">
		<img src="<?php echo $usr['avatar'];?>" class="profile-avatar" alt="profile avatar">
		<span id="prouname" class="profile-uname"><?php echo $uname;?></span>
		<span class="profile-handle">
			@<span id="prohandle"><?php echo $handle;?></span>
			<?php if($followback == 1){?>
				<span class="phdiv">.</span>
				<span class="folback">FOLLOWS YOU</span>
			<?php }?>
		</span>
			<?php if(isset($_SESSION['uid'])){?>
			<?php if((isset($linhandle) && $linhandle == $handle) || $_GET['route'] == 'settings'){?>
				<span id="prosettings" class="profile-followbtn noslct"><i class="fa fa-cogs"></i></span>
			<?php }else{?>
				<?php if($isfollowing == 1){?>
					<span id="profollow" class="profile-followbtn noslct" data-id="<?php echo $proid;?>" data-action="unfollow"><i class="fa fa-user-check"></i></span>
				<?php }else{?>
					<span id="profollow" class="profile-followbtn-f noslct" data-id="<?php echo $proid;?>" data-action="follow"><i class="fa fa-user-plus"></i></span>
				<?php }?>
			<?php }?>
		<?php }?>
	<div class="profile-menubar noslct">
		<span id="proposts" class="profile-<?php echo $posts_active?>">POSTS</span>
		<span class="profile-mbdiv"> | </span>
		<span id="proresp" class="profile-<?php echo $resps_active?>">RESPONSES</span>
		<span class="profile-mbdiv"> | </span>
		<span id="proreposts" class="profile-<?php echo $reposts_active?>">REPOSTS</span>
	</div>
<?php }?>