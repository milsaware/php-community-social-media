<?php
session_start();
$_GET['route'] = (isset($_GET['route']))? $_GET['route'] : 'home';
define("VERSION", "1.0");
define("SKIN", "original");
define("SITENAME", "PHP Social Media");
define("BASEURL", "/");
define("IMGURL", BASEURL."assets/images/");
define("DS", DIRECTORY_SEPARATOR);
define("RT", getcwd() . DS);
$root = (strpos(RT, DS.'httpd.www'.DS) !== false)? substr(RT, 0, strpos(RT, DS.'httpd.www')) : RT;
define("ROOT", $root.DS);
define("PUROOT", ROOT."httpd.www".DS);
define("PROOT", ROOT."httpd.private".DS);
define("USRIMGROOT", PUROOT."assets".DS."images".DS."usr");
define("IMGROOT", PUROOT."assets".DS."images");
define("APP", PROOT."app".DS);
define("VIEWS", APP."views".DS);
define("SYS", PROOT."system".DS);
define("CONTROLLER", APP."controllers".DS);
define("MODEL", APP."models".DS);
define("SYSCONT", SYS."controllers".DS);
require(SYSCONT.'appController.php');
require(SYSCONT.'routesController.php');
require(SYSCONT.'viewController.php');

include_once(SYS.'functions.php');
include_once PROOT.DS.'/routes/web.php';