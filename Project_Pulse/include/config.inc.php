<?php
//config.inc.php
require_once("classes/Functions.class.php");
require_once("classes/Constants.class.php");
require_once("classes/MySQLDB.class.php");
require_once("classes/User.class.php");
require_once("classes/UserTools.class.php");

$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : dirname(__FILE__);
switch(strtolower($host)) {
  //developer machine
	case "192.168.10.100":
	case "192.168.10.110":
	case "localhost":
	case "d:\webapps\projectpulse.local\html\include":
	Functions::setDebug(true);
	$param_file_path = "/WebApps/projectpulse.local/config/app/.params";
	Functions::setParams($param_file_path);
	break;

  //testing server
	case "/home1/smartap1/app013.smartapps4free.com/include":
	case "app013.smartapps4free.com":
	$param_file_path = "/home1/smartap1/etc/app013/config/app/.params";
	Functions::setParams($param_file_path);
	break;

	default:
	die('Path to configuration params not found');
}

//initialize database connection create db connection object
$db =  new MySQLDB(@MySQL_DB_HOST,@MySQL_DB_NAME,@MySQL_DB_USER,@MySQL_DB_PASS);
$con = $db->connect();

//initialize $usertools object
$usertools = new UserTools();

//start the session
session_start();

if(defined('CRON_JOB') === false){

	//main nav bar
	//$faq_navitem = $db->QuerySelect("SELECT fFAQID, fQuestion FROM tbl_faq t","fStatus=1 AND fNavItem=1 ORDER BY fListOrder LIMIT 5",false);

    //initialize common variables to prevent notices on pages;
	$constant_metadata = Constants::METADATA;

	$mainNav = explode(".","0.0.0");
	$breadCrumb =  NULL;
	$success = false;
	$status_type = '';
	$status_msgs = [];
	$status_data = [];
	$response = json_encode(array('type' => $status_type, 'text' => $status_msgs));
	$classname = '';
  $im_count = '';

	//auto login
	if(AUTO_LOGIN && isset($_SESSION['user_id']) === false){$usertools->autologin(mysqli_real_escape_string($con,AUTO_LOGIN_ID));}

	//refresh session variables if logged in
	if(isset($_SESSION["user_id"])) {
		
		//creates user object from serialized session value.
		$user = $usertools->get(mysqli_real_escape_string($con,$_SESSION['user_id']));
		//echo '<pre>'.print_r($user,true).'</pre>';
		switch(true){
			case empty($user->userid):
			case $user->status == 10:
			$user = $usertools->get(-1);
			$usertools->logout();
			header('Location:user-login.php');
			break;
			
			default:
			$user_datetime = new DateTime('now', new DateTimeZone($user->timezone));
			$user_datetime->setTimestamp(time());
			$lastactive = new DateTime("now", new DateTimeZone("UTC"));
			$lastactive->setTimestamp(time()); 
			$user->lastactive = $lastactive->format("Y-m-d H:i:s");
			$user->save();
      
		}
	}
}
?>