<?php
require_once("../include/config.inc.php");
require_once("../include/captcha/Captcha.class.php");

$captcha = '';
		
$image = new Captcha();
echo $image->SecurityCode();	
?>