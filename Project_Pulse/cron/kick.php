<?php
//$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : dirname(__FILE__);
//echo $host;
switch(strtolower($host)) {
	//dev machine
	case "localhost":
	case "192.168.1.100":
	case "192.168.1.110":
	pclose(popen("start /b d:\php\php d:\WebApps\projectpulse.local\html\cron\cron.php", 'r'));
	break;
		
	//live
	case "/home1/smartap1/app013.smartapps4free.com/include":
	case "app013.smartapps4free.com":
	//echo $host;
	$pid = shell_exec("ps -A | grep php-cli | awk '{print $1}'");
	if(empty($pid)) {
		shell_exec("php /home1/smartap1/app013.smartapps4free.com/cron/cron.php >> /home1/smartap1/app013.smartapps4free.com/cron/err.txt 2>&1 & echo $!");
	}
	break;
}
?>
