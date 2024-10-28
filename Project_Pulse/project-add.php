<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Project.class.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid) || $user->usergroup >= 4){header("Location: user-login.php"); exit;}

$mainNav = explode(".","3.1.0");
$breadCrumb = 'Projects';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$id = NULL;
$title = NULL;
$description = NULL;
$managerid = NULL;
$manager = NULL;
$startdate = NULL;
$enddate = NULL;
$report = NULL;
$specific = 
$metadata = NULL;

//reset task list
//unset($_SESSION['tasklist']);

//get object from id
if (isset($_GET['id']) && isset($user_data['post']['btn_submit']) === false) {
	
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
		$row = $db->select("tbl_project", "fProjectID=$id AND fStatus<>0");;
		
		if(empty($row['fProjectID'])){
			$success = false;
			header('HTTP/1.0 404 Not Found');
			header("Location: 404.php");
			die();
		}
	}
	
	if($success){
		$title = $row['fTitle'];
		$description = $row['fDescription'];
		$managerid = $row['fManagerID'];
		$manager = $row['fManager'];
		$startdate = $row['fStartDate'];
		$enddate = $row['fEndDate'];
		$report = $row['fReport'];
		$specific = $row['fSpecific'];
		$_SESSION['tasklist'][$id] = empty($row['fMetaData']) === false ? json_decode($row['fMetaData'],true):[];
		//$metadata = $row['fMetaData']; //empty($row['fMetaData']) === false ? json_decode($row['fMetaData'],true):[];
	}
}

//Handle create/update
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'save') {
	//sanitize and validate user input
	$id = isset($user_data['post']['txt_id']) ? filter_var($user_data['post']['txt_id'],FILTER_SANITIZE_NUMBER_INT):NULL;
	$title = isset($user_data['post']['txt_title']) ? filter_var($user_data['post']['txt_title'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$description = isset($user_data['post']['txt_description']) ? filter_var($user_data['post']['txt_description'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$manager = isset($user_data['post']['txt_manager']) ? filter_var($user_data['post']['txt_manager'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;	
	$managerid = isset($user_data['post']['txt_managerid']) ? filter_var($user_data['post']['txt_managerid'],FILTER_SANITIZE_NUMBER_INT):NULL;
	$startdate = isset($user_data['post']['txt_startdate']) ? filter_var($user_data['post']['txt_startdate'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$enddate = isset($user_data['post']['txt_enddate']) ? filter_var($user_data['post']['txt_enddate'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$report = isset($user_data['post']['txt_report']) ? filter_var($user_data['post']['txt_report'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$specific = isset($user_data['post']['txt_specific']) ? filter_var($user_data['post']['txt_specific'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$metadata = isset($user_data['post']['txt_metadata']) ? filter_var($user_data['post']['txt_metadata'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	//$metadata = $user_data['post']['txt_metadata'];
	//$_SESSION['tasklist'] = empty($metadata) === false ? json_decode($metadata,true):[];
	
	$success = true;

	if(empty($id) === false && filter_var($id,FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Invalid object reference. (code:101001)";
	}
	if(empty($title)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Project name is required";
	}
	if(empty($manager)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Project manager is required";
	}
	if(filter_var($managerid,FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Invalid manager reference. (code 101001)";
	}
	if(empty($startdate)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Start date is required";
	}
	if(empty($enddate)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "End date is required";
	}
	if($report == 'specific' && empty($specific)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Report date is required";
	}
	
	if($success){
		//prepare input for use
		$id = intval($id);
		$managerid = intval($managerid);
		
		$row = $db->select("tbl_project","fStatus<>0 AND fProjectID=$id  LIMIT 1");
		$row_manager = $db->QuerySelect("SELECT fUserID, CONCAT_WS(' ',fFirstName,fLastName)fFullName FROM tbl_user","fStatus<>0 AND fUserGroup<>1 AND fUserID=$managerid LIMIT 1");
		$start_date = DateTime::createfromFormat("Y-m-d", $startdate, new DateTimeZone(TIME_ZONE));
		$end_date = DateTime::createfromFormat("Y-m-d", $enddate, new DateTimeZone(TIME_ZONE));
		
		if($report == 'specific'){
			$specific_date = DateTime::createfromFormat("Y-m-d", $specific, new DateTimeZone(TIME_ZONE));	
		}
		
						
		if(empty($id) === false && empty($row['fProjectID'])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Invalid object reference. (code:102001)";
		}
		if(empty($row_manager['fUserID'])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid manager id';
		}
		elseif($row_manager['fFullName'] != $manager){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid manager name';
		}
		if($start_date === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid start date';
		}
		if($end_date === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid end date';
		}
		if($end_date <= $start_date){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: End date must be higher than start date';
		}
		
	}
	
	if($success){
		
		$title = mysqli_real_escape_string($con,$title);
		$description = mysqli_real_escape_string($con,$description);
		$manager = mysqli_real_escape_string($con,$manager);
				
		$object =  new Project($row);
		
		$object->projectid = $id;
		$object->ownerid = empty($id) ? $user->userid : $object->ownerid; 
		$object->owner = empty($id) ? "$user->firstname $user->lastname": $object->owner; 
		$object->title = $title;
		$object->description = $description;
		$object->managerid = $managerid;
		$object->manager = $manager;
		$object->startdate = $start_date->format('Y-m-d');
		$object->enddate = $end_date->format('Y-m-d');
		$object->report = $report;
		$object->specific = $report == 'specific' ? $specific_date->format('Y-m-d') : '';
		$object->metadata = empty($_SESSION['tasklist'][$id]) === false ? json_encode($_SESSION['tasklist'][$id]):NULL;
		$object->status = empty($id) ? 1 : $object->status; 
		$object->uid = $user->userid;

		$success = $object->save(empty($id));
		$status_type = 'error';
		$status_msgs[] = 'Error: Object create/update failed';
	}
	
	if($success){
		
		//send out signup link for new user
		if(empty($id)){
	
/*			$subject = "Activate your account";
			
			ob_start(); # start buffer
			include_once('templates/mail/user_pwd_set.html');
			# we pass the output to a variable
			$alerttext = ob_get_contents();
			ob_end_clean();

			$alerttext = str_replace("{user_email}",$object->email,$alerttext);
			$alerttext = str_replace("{reset_url}",SITE_URL."user_pwd_reset.php?authkey=".md5(md5($object->userid).md5($object->authkey)),$alerttext);
			$alerttext = str_replace("{domain_name}",DOMAIN_NAME,$alerttext);

			$alert = new Alert();
			$alert->alerttype = 2;
			$alert->to = $object->email;
			$alert->alerttext = htmlentities($alerttext, ENT_QUOTES);
			$alert->subject = $subject;
			$alert->status = 20;
			$alert->uid = $user->userid;
			
			if($alert->save(true))
				include("cron/kick.php");*/
		}
		
		$id = $object->projectid;
		if(isset($_SESSION['tasklist'][0])){
			$_SESSION['tasklist'][$id] = $_SESSION['tasklist'][0];
			unset($_SESSION['tasklist'][0]);
		}
		$status_type = 'success';
		$status_msgs = ['Success: Object create/update successful'];
		
	}	
}

$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/project-add.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>