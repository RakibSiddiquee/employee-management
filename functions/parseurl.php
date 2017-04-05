<?php
if(isset($_SERVER["REQUEST_URI"])){
	$url = explode("/", $_SERVER['REQUEST_URI']);

	//$dd = substr($url[count($url) - 1],0,strpos($url[count($url) - 1], '?'));
	//var_dump($dd);
	//var_dump($url);

	if(strpos($url[count($url) - 1], '?')){
		$url = substr($url[count($url) - 1],0,strpos($url[count($url) - 1], '?'));
		if($url){
			require_once $url . '.php';
		} else {
			require_once 'login.php';
		}
	}else{
		$url = $url[count($url) - 1];
		if($url){
			require_once $url . '.php';
		} else {
			require_once 'login.php';
		}
	}

}