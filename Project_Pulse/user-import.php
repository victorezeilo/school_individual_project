<?php
//user_list.php
require_once("include/config.inc.php");

if(empty($user->userid) || $user->usergroup >= 3){header("Location: user-login.php"); exit;}

$mainNav = explode(".","0.0.0");
$breadCrumb = 'Import Users';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include("templates/header.tpl.php");
include("templates/user-import.tpl.php");
include("templates/footer.tpl.php");

unset($_SESSION['user_data']);
?>