<?php
require_once("../include/config.inc.php");

$username = '';
$password = '';
$captcha = '';
$captchacode = '';

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_code = "error";
	$status_msgs[] = "Error: Invalid request parameters.";
}
elseif(empty($user->userid) === false){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}

if($success){
	//$status_data['post'] = $_POST;
	$username = isset($_POST['txt_username']) ? filter_var($_POST['txt_username'],FILTER_SANITIZE_EMAIL):'';
	$password = isset($_POST['txt_password']) ? filter_var($_POST['txt_password'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$captcha = isset($_POST['txt_captcha']) ? filter_var($_POST['txt_captcha'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$captchacode = filter_var($_SESSION['captchacode'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	//validate input
	if (empty($username) || filter_var($username,FILTER_VALIDATE_EMAIL) === false) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid user credentials (code 101001)';
	}
	elseif(empty($password)) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid user credentials (code 101002)';
	}
	if(empty($captcha) || md5($captcha) != $captchacode){
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Invalid security code";
	}
}

if($success){
	//prepare input vars for using
	$username = mysqli_real_escape_string($con,$username);
  $password = mysqli_real_escape_string($con,$password);
	
	$newuser = new User($db->select("tbl_user", "fStatus<>0 AND fEmail='$username'"));
	$user_metadata = empty($newuser->metadata) === false ? json_decode($newuser->metadata,true) : $constant_metadata['user'];
	
	if(empty($newuser->userid)) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid user credentials (code 102001)';
	}
  elseif(password_verify(hash_hmac("sha256", $password, HASH_SALT), $newuser->password) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid user credentials (code 102002)';
  }
  elseif($newuser->status == 10 || $newuser->status == 19) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Unauthorized user account, contact admin';
	}
	elseif($newuser->status == 11 || filter_var($user_metadata['profile']['emailVerified'],FILTER_VALIDATE_BOOLEAN) === false){
		$authcode = Functions::getRandomString();
    $_SESSION['authcode'] = md5(md5($newuser->userid).md5($authcode));
    $success = false;
		$status_type = "notice";
		$status_msgs[] = "Notice: Account requires activation before login. (code 102003)";
		$status_data['user_status'] = 11;
		$status_data['goLink']= "include/submit.php?authcode=".md5($authcode).md5($newuser->userid);
	}
	elseif($usertools->login($username,$password,HASH_SALT) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid user credentials (code 102004)';
	}

}

if ($success) {

	switch($newuser->status){
		case 1:
		$status_type = "success";
		$status_msgs[] = "Success: Login successfull, please wait...";
		$status_data['goLink']= './';
		break;
		
		case 12:
		case 13:
		case 14:
		case 15:
		$status_type = "success";
		$status_msgs[] = "Success: Login successfull, please wait...";
		$status_data['goLink']= "user-profile.php";
		break;
		
		default:
		$status_type = "warning";
		$status_msgs[] = "Warning: Unknown error, Contact admin";
		break;
	}
}

$response = array("type" => $status_type, "text" => $status_msgs, "data" => $status_data);
echo json_encode($response);
?>