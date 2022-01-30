<?php
class view {

	public static function build($template, $data=''){
		if($data != ''){
			foreach($data as $key => $list){
				$$key = $list;
			}
		}
		require_once(VIEWS.SKIN.DS.$template.'.blade.php');
	}

}