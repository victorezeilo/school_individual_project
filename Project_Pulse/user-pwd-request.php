<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Alert.class.php");

switch(true){
	case empty($user->userid) === false  && AUTO_LOGIN === false:
	$usertools->logout();
	$user = $usertools->get(-1);
	break;

	case empty($user->userid) === false  && AUTO_LOGIN === true:
	header('Location:'.(empty($_SERVER['HTTP_REFERER']) === false  && $_SERVER['HTTP_REFERER'] != 'user_pwd_request.php' ? $_SERVER['HTTP_REFERER']:'./'));
	break;
}

$mainNav = explode(".","0.0.0");
$breadCrumb = 'Account Password';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] : [];

if (isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'send') {
	
	$email = filter_var($user_data['post']['txt_username'], FILTER_SANITIZE_EMAIL);

	$success = true;
	
	if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = 'Error: Invalid email, please enter correct email.';
	}


	if ($success) {
		
		//prepare input for use
		$email = mysqli_real_escape_string($con, $email);
		$newuser = new User($db->select("tbl_user", "fEmail='$email' AND fStatus<>0  LIMIT 1"));


		if(empty($newuser->userid)) {
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'No valid account found for this email/username.';
		}
	
		elseif(empty($newuser->userid) === false && $newuser->status == 10) {
			
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'Unauthorized user account, please contact admin.';
		}
	
		elseif(empty($newuser->userid) === false && $newuser->status == 11) {
			
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'Inactive user account, please activate.';
		}

		elseif(empty($newuser->userid) === false && $newuser->status != 1) {
			
			$success = false;
			$status_type = "error";
			$status_msgs[] = 'Account status not valid, please contact admin.';
		}

		if($success) { 

			$newuser->authkey = Functions::getRandomString();
			$newuser->save();
			
			$to = "$newuser->email";
			$subject = "Your account reset information!";

			ob_start(); # start buffer
			include_once('templates/mail/user_pwd_request.html');
			# we pass the output to a variable
			$alerttext = ob_get_contents();
			ob_end_clean();

			$alerttext = str_replace("{user_email}",$newuser->email,$alerttext);
			$alerttext = str_replace("{reset_url}",SITE_URL."user-pwd-reset.php?authkey=".md5(md5($newuser->userid).md5($newuser->authkey)),$alerttext);
			$alerttext = str_replace("{domain_name}",DOMAIN_NAME,$alerttext);
            
			$alert = new Alert();
			$alert->alerttype = 2;
			$alert->to = $newuser->email;
			$alert->alerttext = htmlentities($alerttext, ENT_QUOTES);
			$alert->subject = $subject;
			$alert->status = 20;
			
			switch($alert->save(true)){
				case true:
				include("cron/kick.php");
				$status_type = "success";
				$status_msgs[] = "Success: Email sent to $email";
				break;
				
				default:
				$status_type = "error";
				$status_msgs[] = "Error: Failed to send email to $email";
				break;	
			}
		}
	}
}

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/user-pwd-request.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>