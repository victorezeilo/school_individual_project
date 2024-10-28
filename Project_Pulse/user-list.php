<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid) || $user->usergroup >= 4){header("Location: user-login.php"); exit;}

$mainNav = explode(".","2.2.0");
$breadCrumb = 'User Accounts';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';
$success = true;
$action = '';
$ids = [];

$action_list = array(
	'unlock'	=> "Unlock",
	'lock' 		=> "Lock",
	'delete'	=> 'Delete',
	'sendlink' => 'Send Link'
);

//handle user update actions
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'update') {
	
	$action = isset($user_data['post']['txt_action']) ? filter_var($user_data['post']['txt_action'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$ids = isset($user_data['post']['ids']) ? filter_var_array($user_data['post']['ids'],FILTER_SANITIZE_NUMBER_INT):NULL;

	if(empty($action) || array_key_exists($action, $action_list) === false) {
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Invalid action parameter.";
	}
	if(empty($ids) || filter_var_array($ids, array('filter' => FILTER_VALIDATE_INT, 'options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Object id is required";
	}	

	if($success){

		switch($action) {
			case 'unlock':
			$status = 1;
			break;

			case 'lock':
			$status = 10;
			break;

			case 'delete':
			$status = 0;
			break;	
		}

		foreach($ids as $val) {
			$success = true;
			$val =  intval($val);
			$newuser = new User($db->select("tbl_user", "fUserID=$val"));

			if(empty($newuser->userid)){
				$success = false;
				$status_msgs[] = "Object not found. (id:$val)";
			}
			if($status == 1 && $newuser->status != 10){
				$success = false;
				$status_msgs[] = "$newuser->email unlock not allowed.";
			}
			if($status == 10 && $newuser->status != 1){
				$success = false;
				$status_msgs[] = "$newuser->email lock not allowed";
			}		
			if($success){
				$newuser->status = $status;
				$newuser->uid = $user->userid;
				$newuser->save();
				if($user->userid == $newuser->userid) $user = $newuser;	
			}
		}

		switch(count($status_msgs)){
			case 0:
			$status_type = 'success';
			$status_msgs[] = "Success: Operation command completed";
			break;

			default:
			array_unshift($status_msgs,"Operation command failed or partial success.");
			$status_type = 'warn';
		}

		if($user->status == 10){
			unset($_SESSION['user_data']);
			header("Location:user-login.php");
			exit;
		}
	}
}

//handle sending activation link
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'sendlink') {
	$action = isset($user_data['post']['txt_action']) ? filter_var($user_data['post']['txt_action'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$ids = isset($user_data['post']['ids']) ? filter_var_array($user_data['post']['ids'],FILTER_SANITIZE_NUMBER_INT):[];
	
	$success = true;
	
	if(empty($ids) || filter_var_array($ids, array('filter' => FILTER_VALIDATE_INT, 'options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Object id is required";
	}	
	
	if($success){
		
		foreach($ids as $val) {
			$success = true;
			$val =  intval($val);
			$object = new User($db->select("tbl_user", "fUserID=$val"));

			if(empty($object->userid)){
				$success = false;
				$status_msgs[] = "Object not found. (id:$val)";
			}
	
			if($success){
				$subject = "Setup your account";

				ob_start(); # start buffer
				include('templates/mail/user_pwd_set.html');
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
		}		
		
		switch(count($status_msgs)){
			case 0:
			$status_type = 'success';
			$status_msgs[] = "Success: Operation command completed";
			break;

			default:
			array_unshift($status_msgs,"Operation command failed or partial success.");
			$status_type = 'warn';
		}
		
	}
}

//Search/Filter, Sort and Limit Options
$filter_list = array();
$filtercolumn = '';
$filtervalue = '';

$sortcolumn = '';
$sort = '';

$filtercolumn_list = array(
	'fullname' 		=> "Full Name",
	'email'				=> 'Email',
	'usergroup'		=> 'Group',
	'loginip'			=> 'Login IP',
	'signup'			=> 'Signup',
	'statustext'	=> 'Status'
);

$sortcolumn_list = array(
	'fullname' 		=> "Full Name",
	'email'				=> 'Email',
	'usergroup'		=> 'Group',
	'loginip'			=> 'Login IP',
	'signup'			=> 'Signup',
	'statustext'	=> 'Status'
);

$resultcount_list = array(
	50  	=> 50,
	100  	=> 100,
	500  	=> 500,
	1000 	=> 1000
);

$filtercolumn = isset($user_data['post']['filtercolumn']) ? $user_data['post']['filtercolumn'] : '';
$filtervalue = isset($user_data['post']['filtervalue']) ? $user_data['post']['filtervalue']:'';

$sortcolumn = isset($user_data['post']['sortcolumn']) && !empty($user_data['post']['sortcolumn']) ? $user_data['post']['sortcolumn']:'';
$sort = isset($user_data['post']['sort']) ? $user_data['post']['sort']:"ASC";

$rows_per_page = isset($user_data['post']['rows_per_page']) ? $user_data['post']['rows_per_page']:25;


$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include("templates/header.tpl.php");
include("templates/user-list.tpl.php");
include("templates/footer.tpl.php");

unset($_SESSION['user_data']);
?>