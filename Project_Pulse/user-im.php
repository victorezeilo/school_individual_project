<?php
require_once('include/config.inc.php');

if(empty($user->userid)) { header("Location:user-login.php");exit;}

$mainNav = explode(".","4.0.0");
$breadCrumb = 'Message Center';


$contactid = isset($_GET['contactid']) ? filter_var($_GET['contactid'],FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
if(md5(md5($user->userid).md5($user->authkey)) == $contactid){$contactid = '';}

//'ea07cdbb274337f166c16ed044e0b1dd';//'16de2e0f7f857c2a4fdf82b9f880e2e4';

include('templates/header.tpl.php');
include('templates/user-im.tpl.php');
include('templates/footer.tpl.php');
?>