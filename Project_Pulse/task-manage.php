<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Project.class.php");

if(empty($user->userid) || $user->usergroup >= 6){header("Location: user-login.php"); exit;}

$mainNav = explode(".","1.0.0");
$breadCrumb = 'Dashboard';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$taskid = NULL;
$action = NULL;
$status = NULL;

if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'save') {
	//sanitize and validate user input
	$taskid = isset($user_data['post']['txt_taskid']) ? filter_var($user_data['post']['txt_taskid'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$action = isset($user_data['post']['txt_action']) ? filter_var($user_data['post']['txt_action'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$status = isset($user_data['post']['txt_status']) ? filter_var($user_data['post']['txt_status'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	
	$success = true;
	
	if(empty($taskid)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid object reference.';
	}
	if(empty($action)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid command param';
	}
	if(empty($status)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid status param';
	}
	
	if($success){
		//prepare input for use
		$taskid =  mysqli_real_escape_string($con,$taskid);
		
		$row = $db->select("tbl_project","fStatus<>0 AND JSON_CONTAINS(fMetaData->'$[*].fTaskID',JSON_ARRAY('$taskid')) LIMIT 1");
		
		if(empty($row['fProjectID'])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid object reference';
		}
	}

	if($success){
		$metadata = json_decode($row['fMetaData'],true);
		$key = array_search($taskid, array_column($metadata, 'fTaskID'));
		$task = &$metadata[$key];
	
		if($action == 'update'){
			$task['fStatus'] = $status;
			
		}
		elseif($action == 'read'){
			$task['fReport']['fStatus'] = 1;
		}
		elseif($action == 'unread'){
			$task['fReport']['fStatus'] = 2;
		}
		
		$object =  new Project($row);
		$object->metadata = json_encode($metadata);
		
		if($object->save()){
			$status_type = 'success';
			$status_msgs[] = 'Success: Object update complete.';			
		}else{
			$status_type = 'error';
			$status_msgs[] = 'Error: Object update failed';
		}
	}
}

$filtered = $user->usergroup >= 3 ? "AND (fManagerID = $user->userid OR fOwnerID=$user->userid)":'';

$rows = $db->QuerySelect("SELECT t.fTitle, fManager,fManagerID, MD5(CONCAT(MD5(fManagerID),MD5(t3.fAuthKey))) fManagerIM, t.fMetaData,JSON_UNQUOTE(JSON_EXTRACT(t.fMetaData, '$[*].fMemberID'))fMembers FROM tbl_project t
LEFT JOIN tbl_user t3 ON t3.fUserID=t.fManagerID","t.fStatus<>0 AND t.fMetaData IS NOT NULL $filtered",false);

//$members = array_unique(json_decode($row['fMetaData']['fMemberID'],true),SORT_STRING);
//echo '<pre>'.print_r($members,true).'</pre>';

$tasklist = [];
foreach($rows as $row){
	$metadata = json_decode($row['fMetaData'],true);
	$ids = implode(',',array_unique(json_decode($row['fMembers'],true)));
	$members = $db->QuerySelect("SELECT fUserID, MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))fMemberIM FROM tbl_user","fStatus<>0 AND fUserID IN($ids)",false);
	$memberIM = array_column($members,'fMemberIM','fUserID');
	
	foreach($metadata as $item){
		$item['fTitle'] = $row['fTitle'];
		$item['fManager'] = $row['fManager'];
		$item['fManagerIM'] = $row['fManagerIM']; //MD5(CONCAT(MD5(fUserID),MD5(fAuthKey))
		$item['fMemberIM'] = $memberIM[$item['fMemberID']];
		$tasklist[] = $item;
	}
}

usort($tasklist, function($a,$b){return $a['fTaskETA'] <=> $b['fTaskETA'];});


$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include("templates/header.tpl.php");
include("templates/task-manage.tpl.php");
include("templates/footer.tpl.php");

unset($_SESSION['user_data']);
?>