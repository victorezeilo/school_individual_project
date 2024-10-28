<?php
require_once("include/config.inc.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid)){header("Location:user-login.php");exit;}

if(empty($user->userid)){
	header('HTTP/1.0 404 Not Found');
	header("Location: 404.php");
	die();	
}

$mainNav = explode(".","0.0.0");
$breadCrumb = 'User Profile';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] : '';
//echo '<pre>'.print_r($_SESSION,true).'</pre>';

$user_metadata = empty($user->metadata) === false ? json_decode($user->metadata,true) : $constant_metadata['user'];
$media = '';
$firstname = $user->firstname;
$lastname = $user->lastname;
$mobile = $user->mobile;

if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'remove'){
	$user->avatar != 'user_01.jpg' ? unlink("uploads/user/images/avatar/$user->avatar") : '';
	$user->avatar = 'user_01.jpg';
	$user->save();
}

if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit']== 'save'){
	//sanitize and validate user input
	$media = isset($user_data['post']['txt_media']) ? filter_var($user_data['post']['txt_media'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$firstname = isset($user_data['post']['txt_firstname']) ? filter_var($user_data['post']['txt_firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$lastname = isset($user_data['post']['txt_lastname']) ? filter_var($user_data['post']['txt_lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$mobile = isset($user_data['post']['txt_mobile']) ? filter_var($user_data['post']['txt_mobile'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';	

	$success = true;
	//validate input
	if($user->status == 12 && empty($firstname)) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Please enter first name.";
	}
	if($user->status == 12 && empty($lastname)) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Please enter last name.";
	}
	if(empty($mobile) || preg_match('/^\(?(\d{3})\)?[-]?(\d{3})[-]?(\d{4})$/',$mobile) === false){
			$success = false;
			$status_type = "error";
			$status_msgs[] = "Error: Please enter valid phone";
	}

	if($success){

    $firstname = $user->status == 12 ? mysqli_real_escape_string($con, $firstname) : $user->firstname;
    $lastname = $user->status == 12 ? mysqli_real_escape_string($con, $lastname) : $user->lastname;
    $mobile = mysqli_real_escape_string($con, $mobile);

    if(empty($media) === false && rename("uploads/temp/$media","uploads/user/images/avatar/$media")){
			$user->avatar != 'user_01.jpg' ? unlink("uploads/user/images/avatar/$user->avatar") : '';
			$user->avatar = $media;
    }
    
    $user->firstname = $firstname ;
    $user->lastname =$lastname;
    $user->mobile = $mobile;
		$user_metadata['setup']['profile'] = $user->status == 12 ?  true : $user_metadata['setup']['profile'];
		$user->status = $user->status == 12 ? 1 : $user->status;
		
		$success = $user->save();
    $status_type = 'error';
    $status_msgs[] = 'Error: Profile update failed';
		
		if($success){
			$status_type = 'success';
			$status_msgs = ['Success: Profile update complete'];
		}
	}
}

$avatar = $user->avatar == 'user_01.jpg' ? 'assets/images/user_01.jpg' : "uploads/user/images/avatar/$user->avatar";

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include("templates/header.tpl.php");
include("templates/user-profile.tpl.php");
include("templates/footer.tpl.php");
unset($_SESSION['user_data']);
?>