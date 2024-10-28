<?php
//index.php
require_once('include/config.inc.php');

if(empty($user->userid)){header("Location: user-login.php"); exit;}

$breadCrumb = "Dashboard";


include('templates/header.tpl.php');
include('templates/index.tpl.php');
include('templates/footer.tpl.php');
?>
