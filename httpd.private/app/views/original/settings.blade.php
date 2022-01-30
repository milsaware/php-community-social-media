<?php if($request == false){?>
<div id="prflsctrgt">
<?php }?>
	<div class="section-block">
		<div class="settings-block">
			<div id="profile-header" class="settings-header">
				<span class="sh1">Profile</span>
			</div>
			<div id="settings-input" style="display: none;">
				<label for="avatarIn"><img id="settings-avatar" src="<?php echo $usr['avatar'];?>"></label>
				<input type="file" id="avatarIn" accept=".png">

				<label for="bannerIn"><img id="settings-banner" src="<?php echo $usr['banner'];?>"></label>
				<input type="file" id="bannerIn" accept=".png, .jpg">

				<input type="text" class="si-in" placeholder="username" value="<?php echo $uname;?>">
				<textarea class="si-in" placeholder="Enter your bio" value="<?php echo $bio;?>"></textarea>
				<input type="text" class="si-in" placeholder="email address" value="<?php echo $email;?>">
				<input type="text" class="si-in" placeholder="location" value="<?php echo $location;?>">
			</div>
		</div>
	</div>
	<div class="section-block">
		<div class="settings-block">
			<div id="security-header" class="settings-header">
				<span class="sh1">Security</span>
			</div>
			<div id="security-content" style="display: none;">
				<div id="pswchng" class="scblk">change password</div>
				<div id="pswchng" class="scblk">lpK</div>
			</div>
		</div>
	</div>
	<div class="section-block">
		<div class="settings-block">
			<div id="muted-header" class="settings-header">
				<span class="sh1">Muted Accounts</span>
			</div>
			<div id="muted-content" style="display: none;">
				<input type="text" class="si-in" placeholder="muted accts here" value="">
			</div>
		</div>
	</div>
<?php if($request == false){?>
</div>
</section>
<?php }?>