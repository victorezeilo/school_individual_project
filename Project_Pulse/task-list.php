<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Project.class.php");

if(empty($user->userid) || $user->usergroup >= 6){header("Location: user-login.php"); exit;}

$mainNav = explode(".","5.0.0");
$breadCrumb = 'Task List';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$taskid = NULL;
$media = NULL;

if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'save') {
	//sanitize and validate user input
	$taskid = isset($user_data['post']['txt_taskid']) ? filter_var($user_data['post']['txt_taskid'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$media = isset($user_data['post']['txt_media']) ? filter_var($user_data['post']['txt_media'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	
	$success = true;
	
	if(empty($taskid)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid object reference.';
	}
	if(empty($media)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid report file name';
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
		if(rename("uploads/temp/$media","uploads/reports/$media") === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Failed to copy report file';
		}
	}

	if($success){
		$metadata = json_decode($row['fMetaData'],true);
		$key = array_search($taskid, array_column($metadata, 'fTaskID'));
		$task = &$metadata[$key];
		
		
		if(empty($task['fReport']['fFileName']) === false && file_exists("uploads/reports/{$task['fReport']['fFileName']}")){
			 unlink("uploads/reports/{$task['fReport']['fFileName']}");
		}
		
		$task['fReport']['fFileName'] = $media;
		$task['fReport']['fStatus'] = 2;
		
		$object =  new Project($row);
		$object->metadata = json_encode($metadata);
		
		if($object->save()){
			$status_type = 'success';
			$status_msgs[] = 'Success: Report submited for review.';			
		}else{
			$status_type = 'error';
			$status_msgs[] = 'Error: Report submission failed failed';
		}
	}
}

$userid = $user->userid;

$project_list = $db->QuerySelect("SELECT t.fTitle, fManager,fManagerID, fAuthKey, t.fMetaData FROM tbl_project t
LEFT JOIN tbl_user t1 ON t1.fUserID=t.fManagerID","t.fStatus<>0 AND t.fMetaData IS NOT NULL AND JSON_CONTAINS(t.fMetaData->'$[*].fMemberID',JSON_ARRAY($userid));",false);

$tasklist = [];
foreach($project_list as $row){
	$metadata = json_decode($row['fMetaData'],true);
	foreach($metadata as $item){
		if($item['fMemberID'] == $userid){
			$item['fTitle'] = $row['fTitle'];
			$item['fManager'] = $row['fManager'];
			$item['fContactID'] = md5(md5($row['fManagerID']).md5($row['fAuthKey'])); //MD5(CONCAT(MD5(fUserID),MD5(fAuthKey))
			$tasklist[] = $item;
		}
	}
}

usort($tasklist, function($a,$b){return $a['fTaskETA'] <=> $b['fTaskETA'];});


$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include("templates/header.tpl.php");
include("templates/task-list.tpl.php");
include("templates/footer.tpl.php");

unset($_SESSION['user_data']);
?>