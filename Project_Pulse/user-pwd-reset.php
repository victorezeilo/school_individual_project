<?php
require_once("include/config.inc.php");

switch(true){
	case empty($user->userid) === false  && AUTO_LOGIN === false:
	$usertools->logout();
	$user = $usertools->get(-1);
	break;

	case empty($user->userid) === false  && AUTO_LOGIN === true:
	header('Location:'.(empty($_SERVER['HTTP_REFERER']) === false  && $_SERVER['HTTP_REFERER'] != 'user-pwd-reset.php' ? $_SERVER['HTTP_REFERER']:'./'));
	break;
}

$mainNav = explode(".","0.0.0");
$breadCrumb = 'Account Password';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] : [];
//echo '<pre>'.print_r($user_data,true).'</pre>';

$authkey = '';
$password = '';
$pwd_confirm = '';

if(isset($_GET['authkey'])){
	
	$authkey = filter_var(trim($_GET['authkey']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$success = true;
	
	if(empty($authkey)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid reset link, can not proceed";	
	}
	
	if($success){
		$authkey = mysqli_real_escape_string($con, $authkey);
		$newuser = new User($db->select("tbl_user","fStatus<>0 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$authkey'"));
		
		if(empty($newuser->userid)){
			$status_type = "warn";
			$status_msgs[] = "Notice: Invalid account parameter, can not proceed";	
		}
	}
	
} else {
	$status_type = "error";
	$status_msgs[] = "Error: Invalid reset link, can not proceed";	
}


if (isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'reset') {
	
	$authkey = isset($_GET['authkey']) ? filter_var($_GET['authkey'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$password = isset($user_data['post']['txt_password']) ? filter_var($user_data['post']['txt_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$pwd_confirm = isset($user_data['post']['txt_pwd_confirm']) ? filter_var($user_data['post']['txt_pwd_confirm'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	
	$success = true;
	
	if (empty($authkey)) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Invalid reset key';
	}
	elseif(Functions::password_validate($password) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Password must match minimum complexity requirement.";
	}
	elseif($password != $pwd_confirm){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Password and confirm password mismatch. (code 1043)";
	}

  if ($success) {
		
		//prepare input for use
		$authkey = mysqli_real_escape_string($con, $authkey);
		$password =  mysqli_real_escape_string($con, $password);

		$newuser = new User($db->select("tbl_user","fStatus<>0 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$authkey'"));
	
		if(empty($newuser->userid)) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'No valid account found for this email/username.';
		}
		elseif(!empty($newuser->userid) && strtotime($newuser->updated." +1 Day") < time()) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'Invalid reset link, expired.';
		}
		elseif(!empty($newuser->userid) && $newuser->status == 10) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'Unauthorized user account, please contact admin.';
		}
		elseif(!empty($newuser->userid) && $newuser->status == 11) {
			$status_type = "error";
			$success = false;
			$status_msgs[] = 'Inactive user account, please activate.';
		}
		elseif(Functions::password_validate($password) === false){
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Password must match minimum complexity requirement.";
		}
		elseif($password != $pwd_confirm){
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Password and confirm password mismatch. (code 1043)";
		}
	}

	if($success){
		$newuser->authkey = Functions::getRandomString();
		$newuser->password =  password_hash(hash_hmac("sha256", $password, HASH_SALT),PASSWORD_BCRYPT);
		
		if($newuser->status == 19){
			$metadata = json_decode($newuser->metadata,true);
			$metadata['profile']['emailVerified'] = true;
			$newuser->metadata = json_encode($metadata);
			$newuser->status = 12;
		}
		
		$newuser->save();
		unset($_SESSION['user_data']);
		$user_data['status_type'] = "success";
		$user_data['status_msgs'] = ["Password set, login using password."];
		$_SESSION['user_data'] = $user_data;
		header("Location: user-login.php");
		exit;
	}
	
}

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/user-pwd-reset.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>
