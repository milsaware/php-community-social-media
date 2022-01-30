<?php
class app {

	public static function fetchController($controller){
		require_once(CONTROLLER.$controller.'Controller.php');
	}

}