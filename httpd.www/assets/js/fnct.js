var notChk;
var idArray = '';

window.onpopstate = function(event) {
	//event.preventDefault();
	loading('fullLoad', 'content-wrapper');
	location.reload();
};

checkNotifications();
window.clearInterval(notCheck);
var notCheck = setInterval(checkNotifications, 10000);

function loginSubmit(){
	let wrapper = document.getElementById('content-wrapper');
	let navHandle = document.getElementById('nav-handle');
	let navPswd = document.getElementById('nav-pswd');
	let e = this;
	let handle = navHandle.value;
	let pswd = navPswd.value;

	if(handle.length > 2 && handle.length < 16){
		navHandle.style.cssText = 'border-bottom:1px solid green;';
	}

	if(pswd.length > 6){
		navPswd.style.cssText = 'border-bottom:1px solid green;';
	}

	if(handle.length > 2 && handle.length < 16 && pswd.length > 6){
		wrapper.innerHTML = '';
		loading('fullLoad', 'content-wrapper');
		$.ajax({
			type: "POST",
			url: "/index.php?route=login",
			data: {handle: handle, pswd: pswd, login: true},
			success: function(data) {
				if(data == 'error'){
					setTimeout(function(){
						window.location.href = '/login';
					}, 3000);
				}else{
					location.reload();
				}
			}
		});			
	}else{
		if(handle.length < 3 || handle.length > 15){
			navHandle.style.cssText = 'border-bottom:1px solid red;';
		}
		if(pswd.length < 7){
			navPswd.style.cssText = 'border-bottom:1px solid red;';
		}
	}
}

function registerSubmit(){
	let e = this;
	let wrapper = document.getElementById('content-wrapper');
	let regName = document.getElementById('reg-name');
	let regHandle = document.getElementById('reg-handle');
	let regPswd = document.getElementById('reg-pswd');
	let name = regName.value;
	let handle = regHandle.value;
	let pswd = regPswd.value;

	if(name.length > 0 && name.length < 31){
		regName.style.cssText = 'border-bottom:1px solid green;';
	}

	if(handle.length > 2 && handle.length < 16){
		regHandle.style.cssText = 'border-bottom:1px solid green;';
	}

	if(pswd.length > 6){
		regPswd.style.cssText = 'border-bottom:1px solid green;';
	}

	if(name.length > 0 && name.length < 31 && handle.length > 2 && handle.length < 16 && pswd.length > 6){
		wrapper.innerHTML = '';
		loading('fullLoad', 'content-wrapper');
		$.ajax({
			type: "POST",
			url: "/index.php?route=login",
			data: {name: name, handle: handle, pswd: pswd, register: true},
			success: function(data) {
				if(data == 'error'){
					window.location.href = '/login';
				}else{
					location.reload();
				}
			}
		});			
	}else{
		if(name.length < 1 || name.length > 30){
			regName.style.cssText = 'border-bottom:1px solid red;';
		}
		if(handle.length < 3 || handle.length > 15){
			regHandle.style.cssText = 'border-bottom:1px solid red;';
		}
		if(pswd.length < 7){
			regPswd.style.cssText = 'border-bottom:1px solid red;';
		}
	}
}

function loginIni(){
	setTimeout(function(){
		$('#errorMsg').fadeOut(1000);
	}, 3000);

	$('#login-submit').unbind();
	$('#login-submit').bind('click', function(){
		loginSubmit();
	});

	$('#nav-handle, #nav-pswd').unbind();
	$('#nav-handle, #nav-pswd').bind('keydown', function(e){
		if(e.keyCode == 13){
			loginSubmit();
		}
	});

	$('#reg-submit').unbind();
	$('#reg-submit').bind('click', function(){
		registerSubmit();
	});

	$('#reg-name, #reg-handle, #reg-pswd').unbind();
	$('#reg-name, #reg-handle, #reg-pswd').bind('keydown', function(e){
		if(e.keyCode == 13){
			registerSubmit();
		}
	});
}

function notificationIni(){
	$('#notNew').unbind();
	$('#notNew').bind('click', function(){
		document.getElementById('notifications_tl').innerHTML = '';
		loading('mainLoad', 'notifications_tl');
		$.ajax({
			type: "POST",
			url: "/index.php?route=notifications",
			data: {request: true, fetch_not: true},
			success: function(data) {
				window.history.pushState('notifications', 'notifications', '/notifications');
				document.getElementById('notifications_tl').innerHTML = data;
				notificationIni();
			}
		});
	});

	$('#notOld').unbind();
	$('#notOld').bind('click', function(){
		document.getElementById('notifications_tl').innerHTML = '';
		loading('mainLoad', 'notifications_tl');
		$.ajax({
			type: "POST",
			url: "/index.php?route=notifications&action=old",
			data: {request: true, fetch_not: true},
			success: function(data) {
				window.history.pushState('notifications', 'notifications', '/notifications/old');
				document.getElementById('notifications_tl').innerHTML = data;
				notificationIni();
			}
		});
	});

	$('#dltall').unbind();
	$('#dltall').bind('click', function(){
		console.log('delete all clicked')
	});
	timelineIni();
}

function checkNotifications(){
	$.ajax({
		type: "POST",
		url: "/index.php?route=notifications",
		data: {request: true, action: 'count'},
		success: function(data) {
			if(document.getElementById('not-count')){
				notCount = document.getElementById('not-count');
				if(data != 0){
					notCount.innerText = Number(data).toLocaleString();
				}else{
					notCount.innerText = '';
				}
			}
		}
	});
}

function profileIni(){
	switchProfilePage('#proresp', 'prohandle', 'responses');
	switchProfilePage('#proposts', 'prohandle', 'posts');
	switchProfilePage('#proreposts', 'prohandle', 'reposts');
	switchProfilePage('#profollowing', 'prohandle', 'following');
	switchProfilePage('#profollowers', 'prohandle', 'followers');
	bindFollowBtn('#profollow');
	bindSettingsBtn('#prosettings');
	timelineIni();
}

function bindSettingsBtn(e){
	if(window.location.pathname != '/settings'){
		$(e).unbind();
		$(e).bind('click', function(){
			if(document.getElementById('prflsctrgt')){
				document.getElementById('prflsctrgt').innerHTML = '';
				loading('profileLoad', 'prflsctrgt');
			}else{
				document.getElementById('content-wrapper').innerHTML = '';
				loading('fullLoad', 'content-wrapper');
			}
				window.history.pushState('Settings', 'settings', '/settings/');
				location.reload(true);
		});
	}
}

function settingsIni(){
	let profileHeader = document.getElementById('profile-header');
	$(profileHeader).unbind();
	$(profileHeader).bind('click', function(){
		setin = document.getElementById('settings-input');
		setin.style.cssText = (setin.style.cssText == 'display: none;')? '' :  'display:none;';
		settingsIni();
	});

	let securityHeader = document.getElementById('security-header');
	$(securityHeader).unbind();
	$(securityHeader).bind('click', function(){
		secin = document.getElementById('security-content');
		secin.style.cssText = (secin.style.cssText == 'display: none;')? '' :  'display:none;';
		settingsIni();
	});

	let nodesHeader = document.getElementById('nodes-header');
	$(nodesHeader).unbind();
	$(nodesHeader).bind('click', function(){
		nodin = document.getElementById('nodes-content');
		nodin.style.cssText = (nodin.style.cssText == 'display: none;')? '' :  'display:none;';
		settingsIni();
	});

	let mutedHeader = document.getElementById('muted-header');
	$(mutedHeader).unbind();
	$(mutedHeader).bind('click', function(){
		min = document.getElementById('muted-content');
		min.style.cssText = (min.style.cssText == 'display: none;')? '' :  'display:none;';
		settingsIni();
	});

	let filtersHeader = document.getElementById('filters-header');
	$(filtersHeader).unbind();
	$(filtersHeader).bind('click', function(){
		fin = document.getElementById('filters-content');
		fin.style.cssText = (fin.style.cssText == 'display: none;')? '' :  'display:none;';
		settingsIni();
	});

	document.getElementById('avatarIn').onchange = function(){
		let settingsAvatar = document.getElementById('settings-avatar');
		let profileAvatar = document.getElementsByClassName('profile-avatar')[0];
		previewImg(this, settingsAvatar, profileAvatar);
	};

	document.getElementById('bannerIn').onchange = function(){
		let settingsBanner = document.getElementById('settings-banner');
		let profileBanner = document.getElementsByClassName('profile-banner')[0];
		previewImg(this, settingsBanner, profileBanner);
	};
}

function previewImg(e, sid, pid) {
	let file = e.files[0];
	let reader  = new FileReader();
	reader.onload = function(e){
		sid.src = e.target.result;
		pid.src = e.target.result;
	}
	reader.readAsDataURL(file);
}

function bindFollowBtn(e){
	$(e).unbind();
	$(e).bind('click', function(){
		$(this).unbind();
		let followId = this.getAttribute('data-id');
		let action = this.getAttribute('data-action');
		let f = this;
		$.ajax({
			type: "POST",
			url: "/index.php?route=profile",
			data: {request: true, action: action, followId: followId},
			success: function(data) {
				f.innerHTML = (action == 'follow')? '<i class="fa fa-user-check"></i>' : '<i class="fa fa-user-plus"></i>';
				let newAtt = (action == 'follow')? 'unfollow' : 'follow';
				f.setAttribute('data-action', newAtt);
				setTimeout(function(){
					bindFollowBtn(e);
				}, 7000);
			}
		});
	});
}

function switchProfilePage(id, unelid, type){
	$(id).unbind();
	let uname = document.getElementById(unelid).innerText;
	$(id).bind('click', function(){
		let e = this;
		let parent = e.parentElement;
		let mboptactv = document.getElementsByClassName('profile-mboptactv');
		for(var i = 0; i < mboptactv.length; i++){
			mboptactv[i].setAttribute('class', 'profile-mbopt');
		}
		let pbfoptactv = document.getElementsByClassName('pbfoptactv');
		for(var i = 0; i < pbfoptactv.length; i++){
			pbfoptactv[i].setAttribute('class', 'pbfopt');
		}
		
		let newAtt = (type == 'following' || type == 'followers')? 'pbfoptactv' : 'profile-mboptactv';
		this.setAttribute('class', newAtt);
		document.getElementById('prflsctrgt').innerHTML = '';
		loading('profileLoad', 'prflsctrgt');
		$.ajax({
			type: "POST",
			url: "/index.php?route=profile&action="+uname+"&data_one="+type,
			data: {request: true},
			success: function(data) {
				if(window.location.pathname != '/profile/'+uname+'/'+type){
					window.history.pushState('profile', 'profile', '/profile/'+uname+'/'+type);
				}
				document.getElementById('prflsctrgt').innerHTML = data;
				profileIni();
				timelineIni();
			}
		});
	});
}

function fetchProfile(e,p,l){
	let handle = p.getAttribute('data-id');
	if(document.getElementById('prflsctrgt')){
		document.getElementById('prflsctrgt').innerHTML = '';
		loading('mainLoad', 'prflsctrgt');
	}else{
		document.getElementById('content-wrapper').innerHTML = '';
		loading('fullLoad', 'content-wrapper');
	}
	if(window.location.pathname != '/profile/'+handle+'/'+ l){
		window.history.pushState('Profile', handle, '/profile/'+handle+'/'+ l);
		location.reload(true);
	}
}

function leftColumnIni(){
	let bannerClick = document.getElementsByClassName('sl-banner')[0];
	$(bannerClick).unbind();
	$(bannerClick).bind('click', function(){
		let e = this;
		fetchProfile(e);
	});

	let avatarClick = document.getElementsByClassName('sl-avatar')[0];
	$(avatarClick).unbind();
	$(avatarClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement;
		fetchProfile(e, parent, '');
	});

	let pnClick = document.getElementsByClassName('sl-profilename')[0];
	$(pnClick).unbind();
	$(pnClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement;
		fetchProfile(e, parent, '');
	});

	let phClick = document.getElementsByClassName('sl-profilehandle')[0];
	$(phClick).unbind();
	$(phClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement;
		fetchProfile(e, parent, '');
	});

	let postsClick = document.getElementsByClassName('slpnav-block')[0];
	$(postsClick).unbind();
	$(postsClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement.parentElement;
		fetchProfile(e, parent, '');
	});

	let followersClick = document.getElementsByClassName('slpnav-block')[1];
	$(followersClick).unbind();
	$(followersClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement.parentElement;
		fetchProfile(e, parent, 'followers');
	});

	let followingClick = document.getElementsByClassName('slpnav-block')[2];
	$(followingClick).unbind();
	$(followingClick).bind('click', function(){
		let e = this;
		let parent = e.parentElement.parentElement;
		fetchProfile(e, parent, 'following');
	});

	$('#mainHome').unbind();
	$('#mainHome').bind('click', function(){
		let e = this;
		$.ajax({
			type: "POST",
			url: "/index.php",
			data: {request: true},
			success: function(data) {
				clearActive();
				$(e).attr("class", "sl-nav-active");
				if(window.location.pathname != '/'){
					window.history.pushState('home', 'home', '/');
				}

				if(document.getElementById('section-right')){
					sr = document.getElementById('section-right');
					sr.innerHTML = data;
					sr.scrollTop = 0;
				}else{
					loading('fullLoad', 'content-wrapper');
					location.reload(true);
				}
				timelineIni();
			}
		});
	});

	$('#mainNot').unbind();
	$('#mainNot').bind('click', function(){
		let e = this;
		$.ajax({
			type: "POST",
			url: "/index.php?route=notifications",
			data: {request: 'newnot', fetch_not: true},
			success: function(data) {
				clearActive();
				clearNotificationCount();
				$(e).attr("class", "sl-nav-active");
				if(window.location.pathname != '/notifications'){
					window.history.pushState('Notifications', 'notifications', '/notifications');
				}

				if(document.getElementById('section-right')){
					document.getElementById('section-right').innerHTML = data;
					document.getElementById('section-right').scrollTop = 0;
				}else{
					loading('fullLoad', 'content-wrapper');
					location.reload(true);
				}
				notificationIni();
			}
		});
	});

	$('#mainPost').unbind();
	$('#mainPost').bind('click', function(){
		let cwrap = document.getElementById('content-wrapper');
		let shadowBox = document.createElement('div');
		shadowBox.setAttribute('id', 'shadowBox');
		cwrap.append(shadowBox);

		let shadowBlock = document.createElement('div');
		shadowBlock.setAttribute('class', 'shadow-block');
		let msgIn = document.createElement('textarea');
		msgIn.setAttribute('id', 'msgIn');
		msgIn.setAttribute('placeholder', 'What\s on your mind?');
		shadowBlock.append(msgIn);
		let msgSubmit = document.createElement('div');
		msgSubmit.setAttribute('id', 'msgSubmit');
		msgSubmit.innerText = 'Post';
		shadowBlock.append(msgSubmit);
		cwrap.append(shadowBlock);
		
		setTimeout(function(){
			shadowBox.style.cssText = 'background-color:#000;';
			shadowBlock.style.cssText = 'opacity:1';
		}, 1);

		$(window).keydown(function(e){
			if(e.key == 'Escape' || e.key == 'Esc' || e.keycode == 27){
				$(this).unbind();
				removeShadowBox(shadowBox, shadowBlock);
			}
		});

		$(shadowBox).bind('click', function(){
			$(this).unbind();
			removeShadowBox(shadowBox, shadowBlock);
		});
		
	});

	$('#mainSrch').unbind();
	$('#mainSrch').bind('click', function(){
		let cwrap = document.getElementById('content-wrapper');
		let shadowBox = document.createElement('div');
		shadowBox.setAttribute('id', 'shadowBox');
		cwrap.append(shadowBox);

		let shadowBlock = document.createElement('div');
		shadowBlock.setAttribute('class', 'shadow-block');
		let srchIn = document.createElement('input');
		srchIn.setAttribute('type', 'text');
		srchIn.setAttribute('id', 'main-search');
		srchIn.setAttribute('placeholder', 'search ...');
		shadowBlock.append(srchIn);
		$(srchIn).keydown(function (e){
			if(e.key == 'Enter'){
				let e = this;
				if(e.value.length < 3){
					e.style.cssText = 'border-bottom:1px solid red';
				}else{
					var f = document.getElementById('main_search_submit');
					search(e, f);
				}
			}
		});

		let srchSubmit = document.createElement('div');
		srchSubmit.setAttribute('id', 'main_search_submit');
		let srchIco = document.createElement('i');
		srchSubmit.setAttribute('class', 'fa fa-search');
		srchSubmit.append(srchIco);
		shadowBlock.append(srchSubmit);
		$(srchSubmit).unbind();
		$(srchSubmit).bind('click', function(){
			let e = this;
			let parent = this.parentElement;
			let searchIn = parent.querySelector('#main-search');
			if(searchIn.value.length < 3){
				searchIn.style.cssText = 'border-bottom:1px solid red';
			}else{
				$(this).unbind();
				$('#main-search').unbind();
				search(searchIn, e);
			}
		});
		cwrap.append(shadowBlock);
		
		setTimeout(function(){
			shadowBox.style.cssText = 'background-color:#000;';
			shadowBlock.style.cssText = 'opacity:1';
		}, 1);

		$(window).keydown(function(e){
			if(e.key == 'Escape' || e.key == 'Esc' || e.keycode == 27){
				$(this).unbind();
				removeShadowBox(shadowBox, shadowBlock);
			}
		});

		$(shadowBox).bind('click', function(){
			$(this).unbind();
			removeShadowBox(shadowBox, shadowBlock);
		});
		
	});

	$('#mainSettings').unbind();
	$('#mainSettings').bind('click', function(){
		if(window.location.pathname != '/settings'){
			if(document.getElementById('prflsctrgt')){
				document.getElementById('prflsctrgt').innerHTML = '';
				loading('profileLoad', 'prflsctrgt');
			}else{
				document.getElementById('content-wrapper').innerHTML = '';
				loading('fullLoad', 'content-wrapper');
			}
			window.history.pushState('Settings', 'settings', '/settings/');
			location.reload(true);
		}
	});

	$('#search_submit').unbind();
	$('#search_submit').bind('click', function(){
		let e = this;
		let parent = this.parentElement;
		let searchIn = parent.querySelector('#ls-search');
		if(searchIn.value.length < 3){
			searchIn.style.cssText = 'border-bottom:1px solid red';
		}else{
			$(this).unbind();
			$('#ls-search').unbind();
			search(searchIn, e);
		}
	});

	$('#ls-search').off("keydown");
	$('#ls-search').keydown(function (e){
		if(e.key == 'Enter'){
			let e = this;
			if(e.value.length < 3){
				e.style.cssText = 'border-bottom:1px solid red';
			}else{
				var f = document.getElementById('search_submit');
				search(e, f);
			}
		}
	});
}

function removeShadowBox(sa, sb){
	sa.style.cssText = 'background-color:transparent;';
	sb.style.cssText = 'opacity:0';
	setTimeout(function(){
		$(sa).remove();
		$(sb).remove();
	}, 500);
}

function clearNotificationCount(){
	$.ajax({
		type: "POST",
		url: "/index.php?route=notifications",
		data: {action: 'notificationreset'},
		success: function(data) {
			document.getElementById('not-count').innerText = '';
		}
	});
}

function getLastId(){
	if(document.getElementById('idArray')){
		var idArrayEl = document.getElementById('idArray');
		idArray = (idArray == '')? idArrayEl.value : idArray + ',' + idArrayEl.value;
		idArray = Array.from(new Set(idArray.split(','))).toString();
		(idArrayEl).remove();
	}
}

function searchIni(){
	getLastId();
	let q = document.getElementById('ls-search').value;
	$('#peopleSearch').unbind();
	$('#peopleSearch').bind('click', function(){
		$(this).unbind();
		let e = this;
		document.getElementById('section-right').innerHTML = '';
		loading('mainLoad', 'section-right');
		$.ajax({
			type: "POST",
			url: "/index.php?route=search&action=people&data_one="+q,
			data: {request: true},
			success: function(data) {
				if(window.location.pathname != '/search/people/'+q){
					window.history.pushState('search', 'search', '/search/people/'+q);
					location.reload(true);
				}
			}
		});
	});
	$('#postsSearch').unbind();
	$('#postsSearch').bind('click', function(){
		$(this).unbind();
		document.getElementById('section-right').innerHTML = '';
		loading('mainLoad', 'section-right');
		$.ajax({
			type: "POST",
			url: "/index.php?route=search&action=posts&data_one="+q,
			data: {request: true},
			success: function(data) {
				if(window.location.pathname != '/search/posts/'+q){
					window.history.pushState('search', 'search', '/search/posts/'+q);
					location.reload(true);
				}
			}
		});
	});
	timelineIni();
}

function search(searchIn, e){
	idArray = '';
	action = (e.parentElement.getAttribute('data-type'))? e.parentElement.getAttribute('data-type') : 'people';
	searchIn.style.cssText = '';
	let q = searchIn.value;
	var query = q.replace(/[^a-z0-9 ]/gi, ' ');
	query = query.replace(/ +(?= )/g,'');
	if(document.getElementById('section-right')){
		document.getElementById('section-right').innerHTML = '';
		loading('mainLoad', 'section-right');
	}else{
		loading('fullLoad', 'content-wrapper');
	}
	$.ajax({
		type: "POST",
		url: "/index.php?route=search&action="+action+"&data_one="+query,
		data: {request: true},
		success: function(data) {
			clearActive();
			$("#search_submit").attr("class", "searchactive");
			if(window.location.pathname != '/search/'+action+'/'+query){
				window.history.pushState('Search', 'Search: '+query, '/search/'+action+'/'+query);
			}
			if(document.getElementById('section-right')){
				document.getElementById('section-right').innerHTML = data;
			}else{
				location.reload(true);
			}
			searchIni();
			timelineIni();
		}
	});
}

function clearActive(){
	$("#mainHome").attr("class", "sl-nav");
	$("#mainNot").attr("class", "sl-nav");
	$("#search_submit").attr("class", "");
}

function loading(id, elid){
	let cwrap = document.getElementById(elid);
	let loadingDisplay = document.createElement('div');
	loadingDisplay.setAttribute('id', id);
	loadingDisplay.setAttribute('class', 'loading');

	for(var i = 1; i <= 11; i++){
		var span = document.createElement('span');
		span.innerText = '.';
		loadingDisplay.append(span);
	}

	cwrap.append(loadingDisplay);
}

function autoLoad(){
	let tl = (document.getElementById('section-right'))? document.getElementById('section-right') : document.getElementById('prflsctrgt');
	$(tl).unbind();
	if(document.getElementById('viewMore')){
		$(tl).bind('scroll', function() {
			if(tl.scrollHeight - (tl.scrollTop + 350) <= tl.clientHeight){
				$(tl).unbind();
				viewMore();
			}
		});
	}
}

function timelineIni(){
	getLastId();
	leftColumnIni();
	
	var msgIn = document.getElementById('msgIn');
	
	if(document.getElementById('section-right') || document.getElementById('prflsctrgt')){
		autoLoad();
	}
	
	let tab = document.getElementsByClassName('timeline-avatar-block');
	for(var i = 0; i < tab.length; i++){
		let handle = tab[i].getAttribute('data-id');
		$(tab[i]).unbind();
		$(tab[i]).bind('click', function(){
			if(document.getElementById('prflsctrgt')){
			//	document.getElementById('prflsctrgt').innerHTML = '';
				loading('mainLoad', 'prflsctrgt');
				$('#mainLoad').remove();
			}else{
				document.getElementById('content-wrapper').innerHTML = '';
				loading('fullLoad', 'content-wrapper');

			}
			if(window.location.pathname != '/profile/'+handle){
				window.history.pushState('Profile', handle, '/profile/'+handle);
				location.reload(true);
			}
		});
	}
	
	inputFocus('msgIn', 'msgSubmit');

	$('#msgSubmit').unbind();
	$('#msgSubmit').bind('click', function(){
		let msgIn = document.getElementById('msgIn');
		msg = msgIn.value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		if(msg.length > 3){
			$(this).unbind();
			let e = this;
			msgIn.style.cssText = '';
			msgSubmitBind(e);
		}else{
			msgIn.style.cssText = 'border:1px solid red;';
		}
	});

	let respond = document.getElementsByClassName("fa-comment");
	for (var i = 0; i < respond.length; i++) {
		$(respond[i]).unbind();
		$(respond[i]).bind('click', function(){
			$(this).unbind('click', arguments.callee);
			let e = this;
			let parone = this.parentElement;
			let partwo = parone.parentElement;
			partwo = (partwo.getAttribute('class') && partwo.getAttribute('class') == 'timeline-content-block')? partwo : partwo.parentElement;
			let id = partwo.getAttribute('data-id');

			var rspnsmsgblck = document.createElement('div');
			rspnsmsgblck.setAttribute('class', 'rspnsmsgblck');
			partwo.append(rspnsmsgblck);

			var rspndmsgblck = document.createElement('textarea');
			rspndmsgblck.setAttribute('class', 'rspndmsgblck');
			rspndmsgblck.placeholder = 'enter your response';
			
			rspnsmsgblck.append(rspndmsgblck);
			textAutoGrow(rspndmsgblck);
			$(rspndmsgblck).focus();

			var msgSubmit = document.createElement('div');
			msgSubmit.setAttribute('class', 'msgSubmit');
			msgSubmit.innerText = 'post';
			$(msgSubmit).bind('click', function(){
				let e = this;
				$(e).unbind();
				responseSubmit(e);
			});
			rspnsmsgblck.append(msgSubmit);
		});
	}

	let tlcbc = document.getElementsByClassName("tlcb-clkwrp");
	for (var i = 0; i < tlcbc.length; i++) {
		$(tlcbc[i]).unbind();
		$(tlcbc[i]).bind('click', function(){
			$(this).unbind('click', arguments.callee);
			let e = this;
			let id = e.getAttribute('data-id');
			let gpar = e.parentElement.parentElement;
			let handle = e.querySelector(".tlcb-handle").innerText.substr(1).toLowerCase();
			for(var i = 1; i < 250; i++){
				if(gpar.getAttribute('class')&& gpar.getAttribute('class') == 'section-block'){
					gpar = gpar;
					break;
				}else{
					gpar = gpar.parentElement;
				}
			}
			gpar = (gpar.getAttribute('class')&& gpar.getAttribute('class') == 'section-block')? gpar : gpar.parentElement;
			gpar = (gpar.getAttribute('class')&& gpar.getAttribute('class') == 'section-block')? gpar : gpar.parentElement;

			$.ajax({
				type: "POST",
				url: "/index.php?route=fetch_responses",
				data: {id: id, handle: handle},
				success: function(data) {
					$(e).unbind();
					gpar.innerHTML = '';
					e.removeAttribute('class');
					gpar.innerHTML = data;
					gpar.scrollIntoView();
					moreResponses();
					timelineIni();
				}
			});
			timelineIni();
		});
	}

	let tlcps = document.getElementsByClassName("tlcb-ps");
	for (var i = 0; i < tlcps.length; i++) {
		$(tlcps[i]).unbind();
		$(tlcps[i]).bind('click', function(){
			let e = this;
			$(e).unbind('click', arguments.callee);
			let parent = e.parentElement.parentElement;
			let p = parent.getElementsByClassName('timeline-avatar-block')[0];
			fetchProfile(e,p,'');
		});
	}

	let unel = document.getElementsByClassName("tlcb-userdetails");
	for (var i = 0; i < unel.length; i++) {
		$(unel[i]).unbind();
		$(unel[i]).bind('click', function(){
			let e = this;
			$(e).unbind('click', arguments.callee);
			let parent = e.parentElement.parentElement.parentElement;
			let p = parent.getElementsByClassName('timeline-avatar-block')[0];
			fetchProfile(e,p,'');
		});
	}

	let rtc = document.getElementsByClassName("rspndto");
	for (var i = 0; i < rtc.length; i++) {
		$(rtc[i]).unbind();
		$(rtc[i]).bind('click', function(){
			$(this).unbind('click', arguments.callee);
			let e = this;
			let id = this.getAttribute('data-id');
			let handle = (e.querySelector(".rthn"))? '' : e.querySelector(".rth").innerText.substr(15).toLowerCase();
			$.ajax({
				type: "POST",
				url: "/index.php?route=fetch_post",
				data: {id: id, handle: handle},
				success: function(data) {
					$(e).unbind();
					e.removeAttribute('class');
					e.innerHTML = data;
					timelineIni();
				}
			});
		});
	}

	let likeBtn = document.getElementsByClassName("fa fa-heart tlcb-icon");
	for (var i = 0; i < likeBtn.length; i++) {
		$(likeBtn[i]).unbind();
		$(likeBtn[i]).bind('click', function(){
			setPostAction(this, 'like',  'fa fa-heart tlcb-icon icon-liked', 'e40000');
		});
	}

	let caratBtn = document.getElementsByClassName("fa fa-angle-down carat-icon");
	for (var i = 0; i < caratBtn.length; i++) {
		$(caratBtn[i]).unbind();
		$(caratBtn[i]).bind('click', function(){
			let e = this;
			$(e).unbind();
			$(e).bind('click', function(){
				$(e).unbind();
				$('#tlmenu').remove();
				timelineIni();
			});
			$('#tlmenu').remove();
			let tlmenu = document.createElement('div');
			tlmenu.setAttribute('id', 'tlmenu');
			tlmenu.setAttribute('class', 'noslct');
			let tlmenuopt = document.createElement('div');
			tlmenuopt.setAttribute('id', 'tlmenuopt');
			tlmenuopt.innerText = 'Mute User';
			$(tlmenuopt).bind('click', function(){
				let a = this;
				let par = a.parentElement.parentElement.parentElement.parentElement.parentElement;
				let avBlock = par.getElementsByClassName('timeline-avatar-block')[0];
				let handle = avBlock.getAttribute('data-id');
				$.ajax({
					type: "POST",
					url: "/index.php?route=mute_user",
					data: {handle: handle},
					success: function(data) {
						if(data == 1){
							removeElements(handle);
							$('#tlmenu').remove();
						}
					}
				});
				$('#tlmenu').remove();
				timelineIni();
			});
			tlmenu.append(tlmenuopt);

			e.parentElement.append(tlmenu);
		});
	}

	let unlikeBtn = document.getElementsByClassName("fa fa-heart tlcb-icon icon-liked");
	for (var i = 0; i < unlikeBtn.length; i++) {
		$(unlikeBtn[i]).unbind();
		$(unlikeBtn[i]).bind('click', function(){
			setPostAction(this, 'unlike',  'fa fa-heart tlcb-icon', '357286');
		});
	}

	let repostBtn = document.getElementsByClassName("fa fa-reply tlcb-icon");
	for (var i = 0; i < repostBtn.length; i++) {
		$(repostBtn[i]).unbind();
		$(repostBtn[i]).bind('click', function(){
			setPostAction(this, 'repost',  'fa fa-reply tlcb-icon icon-reposted', '05ca05');
		});
	}

	let unrepostBtn = document.getElementsByClassName("fa fa-reply tlcb-icon icon-reposted");
	for (var i = 0; i < unrepostBtn.length; i++) {
		$(unrepostBtn[i]).unbind();
		$(unrepostBtn[i]).bind('click', function(){
			setPostAction(this, 'unrepost',  'fa fa-reply tlcb-icon', '357286');
		});
	}

	$('#viewMore').unbind();
	$('#viewMore').bind('click', function(){
		viewMore();
	});
}

function removeElements(handle){
	avbl = document.getElementsByClassName('timeline-avatar-block');
	for (var i = 0; i < avbl.length; i++) {
		let h = avbl[i].getAttribute('data-id');
		if(h.toLowerCase() == handle.toLowerCase()){
			let parent = avbl[i].parentElement;
			$(parent).remove();
			removeElements(handle);
		}
	}
}

function inputFocus(id, submitId){
	$('#'+id).unbind();
	$('#'+id).bind('focus', function(){
		document.getElementsByClassName('placeholder')[0].style.cssText = 'display:none';
		let msgIn = document.getElementById(id);
		let msgSubmit = document.getElementById(submitId);
		let phr = document.getElementsByClassName('placeholder')[0];
		phr.style.cssText = 'display:none';
		textAutoGrow(msgIn);
		$('#'+id).bind('keyup', function(){
			if(msgIn.value.replace(/\s+/g, '').length > 3){
				msgSubmit.style.cssText = 'display:block;color:#0a8e14';
				$('#'+submitId).unbind();
				$('#'+submitId).bind('click', function(){
					let msgIn = document.getElementById(id);
					msg = msgIn.value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
					if(msg.length > 3){
						$(this).unbind();
						let e = this;
						msgIn.style.cssText = '';
						msgSubmitBind(e);
					}else{
						msgIn.style.cssText = 'border:1px solid red;';
					}
				});
			}else{
				msgSubmit.style.cssText = 'display:none';
			}
		});
		$('#'+id).bind('blur', function(){
			let msgIn = document.getElementById(id);
			let phr = document.getElementsByClassName('placeholder')[0];
			if(msgIn.value.replace(/\s+/g, '') !== ''){
				phr.style.cssText = 'display:none';
			}else{
				phr.style.cssText = '';
			}
			if(msgIn.value.replace(/\s+/g, '').length < 4){
				msgSubmit.style.cssText = 'display:none';
			}else{
				msgSubmit.style.cssText = 'display:block;color:#0a8e14';
			}
			inputFocus(id, submitId);
		});
	});
}

function setPostAction(e, type, newAtt, c){
	$(e).unbind();
	let par = e.parentElement;
	let id = par.parentElement.getAttribute('data-id');
	let countEl = par.querySelector('.tlcb-count');
	let count = parseInt(countEl.innerText);
	let newCount = (type == 'like' || type == 'repost')? count + 1 : count - 1;
	let handle = par.parentElement.parentElement.querySelector('.tlcb-handle').innerText;
	e.setAttribute('class', newAtt);
	countEl.innerText = newCount;
	e.style.cssText = 'color:#' + c;
	setTimeout(function(){
		submitPostAction(type, id, handle);
	}, 1500);
}

function submitPostAction(type, id, handle){
	$.ajax({
		type: "POST",
		url: "/index.php?route=submit_post_action",
		data: {type: type, id: id, handle: handle},
		success: function(data) {
			timelineIni();
		}
	});
}

function moreResponses(){
	if(document.getElementsByClassName('viewMore')[0]){
		if(document.getElementById('rowCount')){
			let rowCountEl = document.getElementById('rowCount');
			let rowCount = rowCountEl.getAttribute('count');
			let lastid = rowCountEl.getAttribute('lastid');
			let parid = rowCountEl.getAttribute('originid');
			let paruid = rowCountEl.getAttribute('paruid');
		
			let parEl = rowCountEl.parentElement;
			if(rowCount == 5){
				rowCountEl.removeAttribute('id');
				rowCountEl.removeAttribute('paruid');
				rowCountEl.removeAttribute('lastid');
				rowCountEl.removeAttribute('count');
				rowCountEl.removeAttribute('originId');
				rowCountEl.removeAttribute('orhandle');
				rowCountEl.setAttribute('class', 'viewMore');
				rowCountEl.setAttribute('data-lid', lastid);
				rowCountEl.setAttribute('data-oid', parid);
				rowCountEl.setAttribute('data-ouid', paruid);
				$(rowCountEl).unbind();
				$(rowCountEl).bind('click', function(){
					$(this).unbind();
					moreResp(this, parEl, parid, lastid, paruid);
				});
			}else{
				$(rowCountEl).remove();
			}
		}
	}

	if(document.getElementsByClassName('viewMore')[0]){
		let viewMoreEl = document.getElementsByClassName('viewMore');
		for(var i = 0; i < viewMoreEl.length; i++){
			let rowCount = viewMoreEl[i].getAttribute('count');
			let lastid = viewMoreEl[i].getAttribute('lastid');
			let parid = viewMoreEl[i].getAttribute('originid');
			let paruid = viewMoreEl[i].getAttribute('paruid');
			let parEl = viewMoreEl[i].parentElement;
			if(viewMoreEl[i].getAttribute('clicked')){
				viewMoreEl[i].removeAttribute('clicked');
			}else{
				viewMoreEl[i].setAttribute('clicked', 'true');
			}
			$(viewMoreEl[i]).unbind();
			$(viewMoreEl[i]).bind('click', function(){
				$(this).unbind();
				moreResp(this, parEl, parid, lastid, paruid);
			});
		}
	}
}

function moreResp(e, par, parid, lastid, paruid){
	let loadingDisplay = document.createElement('div');
	loadingDisplay.setAttribute('id', 'moreRespLoading');
	loadingDisplay.setAttribute('class', 'loading');

	for(var i = 1; i <= 11; i++){
		var span = document.createElement('span');
		span.innerText = '.';
		loadingDisplay.append(span);
	}

	e.parentElement.append(loadingDisplay);
	$(e).remove();
	$.ajax({
		type: "POST",
		url: "/index.php?route=fetch_responses",
		data: {id: parid, paruid: paruid, lastid: lastid},
		success: function(data) {
			$(loadingDisplay).remove();
			par.innerHTML += data;
			moreResponses();
			timelineIni();
		}
	});
	timelineIni();
}

function viewMore(){
	let lastId = 0;
	let viewMoreEl = document.getElementById('viewMore');
	let loadingDisplay = document.createElement('div');
	loadingDisplay.setAttribute('id', 'viewMoreLoading');
	loadingDisplay.setAttribute('class', 'loading');

	for(var i = 1; i <= 11; i++){
		var span = document.createElement('span');
		span.innerText = '.';
		loadingDisplay.append(span);
	}

	if(document.getElementById('viewMore')){
		viewMoreEl.parentElement.append(loadingDisplay);
		lastId = viewMoreEl.getAttribute('last-id');
	}
	$('#viewMore').remove();
	if(lastId != '0'){
		var cururl = window.location.pathname;
		$.ajax({
			type: "POST",
			url: cururl,
			data: {request: true, idArray: idArray, req: 1, viewHome: 1, last_id: lastId},
			success: function(data) {
				$('#rowCount').remove();
				$('#viewMoreLoading').remove();
				let tl = (document.getElementById('search_tl'))? document.getElementById('search_tl') : ((document.getElementById('notifications_tl'))? document.getElementById('notifications_tl') :
				((document.getElementById('prflsctrgt'))? document.getElementById('prflsctrgt') : document.getElementById('tlwrp')));
				if(data != ''){
					tl.innerHTML += data;
					moreResponses();
				}else{				
					moreResponses();
				}
				searchIni();
				notificationIni();
				timelineIni();
			}
		});
	}else{
		$('#viewMoreLoading').remove();
	}
}

function msgSubmitBind(e){
	e.innerHTML = '<i class="fa fa-spinner spinner-submit"></i>';
	let msgIn = document.getElementById('msgIn');
	$.ajax({
		type: "POST",
		url: "/index.php?route=submit_post",
		data: {msg: msgIn.value},
		success: function(data) {
			$('#tlwrp').prepend(data);
			msgIn.value = '';
			msgIn.style.height = "44px";
			e.innerHTML = 'Post';
			if(document.getElementsByClassName('slpnb-count')[0]){
				var postCountEl = document.getElementsByClassName('slpnb-count')[0];
				var parseCount = parseInt(postCountEl.innerText);
				parseCount++;
				postCountEl.innerText = parseCount;
			}
			timelineIni();
		}
	});
}

function primeElement(el, cur){
	par = el.parentElement;
	if(par.getAttribute('class') !== el){
		primeElement(el, par);
	}else{}
}

function responseSubmit(e){
	let parone = e.parentElement;
	let partwo = (parone.getAttribute('class') == 'timeline-content-block')? parone : parone.parentElement;
	let parthree = (partwo.getAttribute('class') == 'timeline-content-block')? partwo : partwo.parentElement;
	parthree = (parthree.getAttribute('class') == 'timeline-content-block')? parthree : parthree.parentElement;
	let parid = parthree.getAttribute('data-id');
	let msgIn = $(parone).find('.rspndmsgblck');
	if(msgIn[0].value.replace(/\s/g, '').length > 3){
		msgIn[0].style.cssText = 'border:1px solid #018201';
		e.style.cssText = 'opacity:0;cursor:default';
		e.innerText = '';
		let loadingDisplay = document.createElement('div');
		loadingDisplay.setAttribute('id', 'respondLoading');
		loadingDisplay.setAttribute('class', 'loading');

		for(var i = 1; i <= 11; i++){
			var span = document.createElement('span');
			span.innerText = '.';
			loadingDisplay.append(span);
		}

		e.parentElement.append(loadingDisplay);
		let icoblo = partwo.getElementsByClassName('tlcb-icon-block')[0];
		let respCountEl = icoblo.getElementsByClassName('tlcb-count')[0];
		respCountEl.innerText = parseInt(respCountEl.innerText) + 1;
		let newRespBlock = $(parthree).find('.newRespBlock');
		$.ajax({
			type: "POST",
			url: "/index.php?route=submit_response",
			data: {msg: msgIn[0].value, parid: parid},
			success: function(data) {
				$(parone).remove();
				$(newRespBlock[0]).prepend(data);
				if(document.getElementsByClassName('slpnb-count')[0]){
					var postCountEl = document.getElementsByClassName('slpnb-count')[0];
					var parseCount = parseInt(postCountEl.innerText);
					parseCount++;
					postCountEl.innerText = parseCount;
				}
				timelineIni();
			}
		});
	}else{
		msgIn[0].style.cssText = 'border:1px solid #e40000';
		$(e).bind('click', function(){
			$(this).unbind();
			responseSubmit(this);
		});
	}
}

function textAutoGrow(id){
	$(id).unbind();
	$(id).off("keydown");
	$(id).keydown(function() {
		if (event.key === "Enter" && (parseInt(this.style.height) < 600 || this.style.height == '')){
			this.style.height = "5px";
			this.style.height = (this.scrollHeight)+"px";
		}
		else if(event.keyCode == 86 && event.ctrlKey && (parseInt(this.style.height) < 600 || this.style.height == '')){
			this.style.height = "5px";
			this.style.height = (this.scrollHeight)+"px";
		}
		else if(event.keyCode == 8 || event.keyCode == 46){
			this.style.height = "5px";
			this.style.height = (parseInt(this.scrollHeight) < 600)? (parseInt(this.scrollHeight) - 12)+"px" : '328px';
		}
	});
}