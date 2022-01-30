<?php
use routesController as route;
$route = preg_replace('#[^a-z_]#', '', strtolower($_GET['route']));
$routes = array('home','posts','fetch_post','fetch_responses','submit_post','submit_response','submit_post_action','notifications','login','search','profile','settings','mute_user');

if (in_array($route, $routes)){
	route::get('home', 'index@index');
	route::get('posts', 'posts@index');
	route::get('fetch_post', 'posts@fetch_post');
	route::get('fetch_responses', 'posts@fetch_responses');
	route::get('submit_post', 'posts@submit_post');
	route::get('submit_response', 'posts@submit_response');
	route::get('submit_post_action', 'posts@submitPostAction');
	route::get('notifications', 'notifications@index');
	route::get('login', 'auth@index');
	route::get('search', 'search@index');
	route::get('profile', 'profile@index');
	route::get('settings', 'profile@settings');
	route::get('mute_user', 'profile@muteUser');
	route::get('postrequest', 'postrequest@index');
}else{
	route::error();
}