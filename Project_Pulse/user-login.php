<?php
//user-login.php
require_once('include/config.inc.php');
require_once('include/captcha/Captcha.class.php');

switch(true){
  case empty($user->userid) === false  && AUTO_LOGIN === false:
  $usertools->logout();
	$user = $usertools->get(-1);
  break;

  case empty($user->userid) === false  && AUTO_LOGIN === true:
  header('Location:'.(empty($_SERVER['HTTP_REFERER']) === false  && $_SERVER['HTTP_REFERER'] != 'user-login.php' ? $_SERVER['HTTP_REFERER']:'./'));
  break;
}

$breadCrumb = "Account Login";
$captcha =  new Captcha();

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] : [];
//echo '<pre>'.print_r($user_data,true).'</pre>';

if(isset($user_data['status_type']) && isset($user_data['status_msgs'])){
	$status_type = $user_data['status_type'];
	$status_msgs[] = $user_data['status_msgs'];
}

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/user-login.tpl.php');
include('templates/footer.tpl.php');
?>