<?php
function include_controller($file){
	include_once(CONTROLLER.$file.'.controller.php');
}

function include_model($file){
	include_once(MODEL.$file.'.model.php');
}

function url($ext){
	return BASEURL.$ext;
}

function char_convert_special($string){
	$search = array("!", '"', "$", "%", "*", "(", ")", "<", ">", "?", "+", "-", "="); 
	$replace = array("&#33;", "&#34;", "&#36;", "&#37;", "&#42;", "&#40;", "&#41;", "&#60;", "&#62;", "&#63;", "&#43;", "&#45;", "&#61;"); 
	return str_replace($search, $replace, $string);
}

function get_extension($file) {
	$p = '.';
	$extension = end(explode(".", $file));
	return $extension ? $extension : false;
}

function truncate_chars($text, $limit, $ellipsis = ' >...') {
	if( strlen($text) > $limit ) {
		$endpos = strpos(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $text), ' ', $limit);
		if($endpos !== FALSE){
			$text = trim(substr($text, 0, $endpos)) . $ellipsis;
		}
	}
		return $text;
}

function random_string($n) { 
	$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
	$string = ''; 

	for ($i = 0; $i < $n; $i++) { 
		$index = rand(0, strlen($charset) - 1); 
		$string .= $charset[$index]; 
	} 

	return $string; 
}

function userDir($handle){
	$hdir = '';
	$hsplit = str_split($handle);
	foreach($hsplit as $h){
		$hdir .= $h.DS;
	}
	return $hdir;
}

$request = parse_url($_SERVER['REQUEST_URI']);
$path = $request["path"];
$result = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $path), '/');