<?php
class routesController {

	public static function get($route, $action){
		if($route == strtolower($_GET['route'])){
			$actionArray = explode('@', $action);
			$controller = $actionArray[0];
			$function = $actionArray[1];
			$controllerName = $controller.'Controller';
			include_controller($controller);
			return $controllerName::$function();
		}
	}
	
	public static function error(){
		$data['meta']['title'] = SITENAME.' - page not found';
		$data['meta']['description'] = 'Page not found';
		$data['copyright'] = '&#169; '.date('Y').' '.SITENAME;
		view::build('head', $data).
		view::build('nav', $data).
		view::build('error', $data).
		view::build('foot', $data);
	}

}