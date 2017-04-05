<?php
require_once 'core/init.php';
$settings = new Settings();
$user = new User();

if(!empty($settings->timeZone())){
	date_default_timezone_set($settings->timeZone());
} else {
	date_default_timezone_set('Asia/Dhaka');	
}
//var_dump(date_default_timezone_get());


$title = '';
if(isset($_SERVER["REQUEST_URI"])){
	$url = explode("/", $_SERVER['REQUEST_URI']);

	if(is_numeric($url[count($url) - 1])){
		array_pop($url);
		$url = $url[count($url) - 1];
	}else{
		$url = $url[count($url) - 1];
	}

	$value = trim($url);

	if(strpos($url, '_')){
		$title = ucfirst(str_replace('_', ' ', $url));
	}else{
		$title = ucfirst($url);
	}
}


if($user->isLoggedIn()){ 
	ob_start();
	require_once 'header.php';
	require_once 'aside.php';
}
require_once 'functions/parseurl.php';
if($user->isLoggedIn()){
	require_once 'footer.php';
	ob_end_flush();

}

?>