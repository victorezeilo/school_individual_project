<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid) || $user->usergroup >= 4){header("Location: user-login.php"); exit;}

$mainNav = explode(".","2.1.0");
$breadCrumb = 'User Accounts';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$usergroup_list = $db->select('tbl_user_group',"fStatus=1 AND fGroupID<>1",false);

$id = NULL;
$usergroup = NULL;
$firstname = NULL;
$lastname = NULL;
$email = NULL;
$enable = NULL;


//get object from id
if (isset($_GET['id'])) {
	
	$id = filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);
	$success = true;
	
	if(filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$id = NULL;
		header('HTTP/1.0 404 Not Found');
		header("Location: 404.php");
		die();	
	}
	
	if($success){
		//prepare input for use
		$id = intval($id);
		$row = $result = $db->select("tbl_user", "fUserID=$id AND fStatus <> 0 AND fUserGroup <> 1");;
		
		if(empty($row['fUserID'])){
			$success = false;
			header('HTTP/1.0 404 Not Found');
			header("Location: 404.php");
			die();
		}
	}
	
	if($success){
		$usergroup = $row['fUserGroup'];
		$firstname = $row['fFirstName'];
		$lastname = $row['fLastName'];
		$email = $row['fEmail'];
		$enable = $row['fStatus'];
	}
}

//Handle create/update
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'save') {
	//sanitize and validate user input
	$id = isset($user_data['post']['txt_id']) ? filter_var($user_data['post']['txt_id'],FILTER_SANITIZE_NUMBER_INT):NULL;
	$usergroup = isset($user_data['post']['txt_usergroup']) ? filter_var($user_data['post']['txt_usergroup'],FILTER_SANITIZE_NUMBER_INT):NULL;
	$firstname = isset($user_data['post']['txt_firstname']) ? filter_var($user_data['post']['txt_firstname'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$lastname = isset($user_data['post']['txt_lastname']) ? filter_var($user_data['post']['txt_lastname'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;	
	$email = isset($user_data['post']['txt_email']) ? filter_var($user_data['post']['txt_email'],FILTER_SANITIZE_EMAIL):NULL;
	$enable = isset($user_data['post']['txt_enable']) ? filter_var($user_data['post']['txt_enable'], FILTER_SANITIZE_NUMBER_INT):10;
	
	
	$success = true;

	if(empty($id) === false && filter_var($id,FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Invalid object reference. (code:101001)";
	}
	
	if(filter_var($usergroup,FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: User group is required";
	}
	if(empty($id) && empty($firstname)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: First name is required";
	}

	if(empty($id) && empty($lastname)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Last name is required";
	}
	if(filter_var($enable, FILTER_VALIDATE_INT,array('options' => array('min_range' => 1, 'max_range' => 10))) === false){
		$success = false;
		$status_type = 'error';
		$status_msg[] = 'Error: Invalid status parameter';
	}
	
	if($success){
		//prepare input for use
		$id = intval($id);
		$usergroup = intval($usergroup);
		$firstname = empty($id) ? mysqli_real_escape_string($con,$firstname): $row['fFirstName'];
		$lastname = empty($id) ? mysqli_real_escape_string($con,$lastname): $row['fLastName'];
		$email =  empty($id) ? mysqli_real_escape_string($con,$email): $row['fEmail'];
		$enable = intval($enable);
				
		$row = $db->select("tbl_user","fStatus<>0 AND fUserID=$id  LIMIT 1");
		$pair = $db->select("tbl_user","fStatus<>0 AND fEmail='$email' LIMIT 1");
						
		if(empty($id) === false && empty($row['fUserID'])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Invalid object reference. (code:102001)";
		}
		if(empty($pair['fUserID']) === false && $pair['fUserID'] != $id){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Duplicate user account.";
		}
	}
	
	if($success){
		
		$object =  new User($row);
		
		$object->userid = $id;
		$object->usergroup = $usergroup;
		$object->email = empty($id) || $object->status == 19 ? $email : $object->email;
		$object->firstname = empty($id) || $object->status == 19 ? $firstname : $object->firstname;
		$object->lastname = empty($id) || $object->status == 19 ? $lastname : $object->lastname;
		$object->avatar = empty($id) ? 'user_01.jpg' : $object->avatar;
		$object->timezone = empty($id) ? TIME_ZONE : $object->timezone;
		$object->authkey = empty($id) ? Functions::getRandomString() : $object->authkey;
		$object->metadata = empty($id) ? json_encode($constant_metadata['user']) : $object->metadata;
		$object->status = empty($id) ? 19 : ($object->status == 1 || $object->status == 10 ? $enable : $object->status); 
		$object->uid = $user->userid;

		$success = $object->save(empty($id));
		$status_type = 'error';
		$status_msgs[] = 'Error: User create/update failed';
	}
	
	if($success){
		//send out signup link for new user
		if(empty($id)){
	
			$subject = "Setup your account";
			
			ob_start(); # start buffer
			include_once('templates/mail/user_pwd_set.html');
			# we pass the output to a variable
			$alerttext = ob_get_contents();
			ob_end_clean();

			$alerttext = str_replace("{user_email}",$object->email,$alerttext);
			$alerttext = str_replace("{reset_url}",SITE_URL."user-pwd-reset.php?authkey=".md5(md5($object->userid).md5($object->authkey)),$alerttext);
			$alerttext = str_replace("{domain_name}",DOMAIN_NAME,$alerttext);

			$alert = new Alert();
			$alert->alerttype = 2;
			$alert->to = $object->email;
			$alert->alerttext = htmlentities($alerttext, ENT_QUOTES);
			$alert->subject = $subject;
			$alert->status = 20;
			$alert->uid = $user->userid;
			
			if($alert->save(true))
				include("cron/kick.php");
		}
		
		$id = $object->userid;
		$status_type = 'success';
		$status_msgs = ['Success: User create/update successful'];
		
	}	
}



$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/user-add.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>