<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid) || $user->usergroup >= 6){header("Location: user-login.php"); exit;}

$mainNav = explode(".","9.0.0");
$breadCrumb = 'User Accounts';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$id = NULL;
$pwd_current = NULL;
$pwd_new = NULL;
$pwd_confirm = NULL;

//Handle create/update
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'save') {
	//sanitize and validate user input
	$pwd_current = isset($user_data['post']['txt_pwd_current']) ? filter_var($user_data['post']['txt_pwd_current'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$pwd_new = isset($user_data['post']['txt_pwd_new']) ? filter_var($user_data['post']['txt_pwd_new'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$pwd_confirm = isset($user_data['post']['txt_pwd_confirm']) ? filter_var($user_data['post']['txt_pwd_confirm'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;	
	
	$success = true;
	
	if(empty($pwd_current)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Current password is required.";
	}
  elseif(password_verify(hash_hmac("sha256", $pwd_current, HASH_SALT), $user->password) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid current password.';
  }
	if(empty($pwd_new)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "First name is required";
	}
	elseif(Functions::password_validate($pwd_new) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Password must match minimum complexity requirement.";
	}
	elseif($pwd_new != $pwd_confirm){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Password and confirm password mismatch.";
	}
	
	if($success){
		//prepare input for use
		$user->password =  password_hash(hash_hmac("sha256", $pwd_new, HASH_SALT),PASSWORD_BCRYPT);

		$success = $user->save();
		$status_type = 'error';
		$status_msgs[] = 'Error: Change password failed.';
	}
	
	if($success){

		$status_type = 'success';
		$status_msgs = ['Success: Change password successful.'];
		
	}	
}

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/user-pwd-change.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>