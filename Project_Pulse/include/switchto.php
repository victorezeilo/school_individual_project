<?php
require_once("config.inc.php");

$switchto = '';

if(isset($_GET['switchto']) && isset($_SESSION['admin_id'])){

	$switchto = filter_var($_GET['switchto'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$admin_id = filter_var($_SESSION['admin_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	$success = true;
	
	if(empty($switchto)){
		$success = false;
	}

	if(empty($admin_id)){
		$success = false;
	}

	if($success){

		$switchto =  mysqli_real_escape_string($con,$switchto);
		$admin_id = mysqli_real_escape_string($con,$admin_id);

		$newadmin = $db->select("tbl_users","fStatus=1 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$admin_id' AND fUserGroup IN (1,2)");
		$newuser = $db->select("tbl_users","fStatus NOT IN (0,10,11) AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$switchto'");

		if(empty($newadmin['fUserID'])){
			$success = false;
		}
		if(empty($newuser['fUserID'])){
			$success = false;
		}
	}

	if($success){
		$user = $usertools->get(md5(md5($newuser['fUserID']).md5($newuser['fAuthKey'])));
		$_SESSION['user_id'] = md5(md5($user->userid).md5($user->authkey));
	}
}

switch($user->status){
	case 1:
		switch($user->usergroup){
			case 1:
			case 2:
			case 3:
			header("Location:../_dashboard/user_list.php");
			break;

			case 5:
			header("Location:../");
			break;	

			case 6:
			header("Location:../");
			break;

			default:
			header("Location:../");
			break;	
		}		
	break;

	case 11:
	case 12:
	case 18:
	header("Location:../user_profile.php");
	break;
		
	default:
	header("Location:../");
}
?>