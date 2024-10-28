<?php
require_once("include/config.inc.php");

switch(true){
	case empty($user->userid) === false  && AUTO_LOGIN === false:
	$usertools->logout();
	$user = $usertools->get(-1);
	break;

	case empty($user->userid) === false  && AUTO_LOGIN === true:
	header('Location:'.(empty($_SERVER['HTTP_REFERER']) === false  && $_SERVER['HTTP_REFERER'] != 'user-login.php' ? $_SERVER['HTTP_REFERER']:'./'));
	break;
}

switch(empty($user->userid) === false  && AUTO_LOGIN){
	case true:
	header('Location:'.(empty($_SERVER['HTTP_REFERER']) === false  && $_SERVER['HTTP_REFERER'] != 'user-login.php' ? $_SERVER['HTTP_REFERER']:'./'));
	break;

	default:
	$usertools->logout();
	header("Location:user-login.php");
}
?>
