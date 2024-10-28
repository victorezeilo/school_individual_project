<?php
require_once("../include/config.inc.php");

$action_list = ['add','edit','delete'];
$tasklist = NULL;
$id = NULL;
$action = NULL;
$taskid = NULL;
$taskname = NULL;
$member = NULL;
$memberid = NULL;
$tasketa = NULL;
$tasketa_date = NULL;

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_code = "error";
	$status_msgs[] = "Error: Invalid request parameters.";
}
elseif(empty($user->userid)){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}

if($success){
	//$status_data['post'] = $_POST;
	$action = isset($_POST["txt_action"]) ? filter_var($_POST["txt_action"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$taskid = isset($_POST["txt_taskid"]) ? filter_var($_POST["txt_taskid"], FILTER_SANITIZE_NUMBER_INT):NULL;
	$taskname = isset($_POST["txt_taskname"]) ? filter_var($_POST["txt_taskname"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$member = isset($_POST["txt_member"]) ? filter_var($_POST["txt_member"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$memberid = isset($_POST["txt_memberid"]) ? filter_var($_POST["txt_memberid"], FILTER_SANITIZE_NUMBER_INT):NULL;
	$tasketa = isset($_POST["txt_tasketa"]) ? filter_var($_POST["txt_tasketa"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	$id = isset($_POST['txt_id']) ? filter_var($_POST['txt_id'], FILTER_SANITIZE_NUMBER_INT):NULL;
	
	$id = intval($id);
	$tasklist = isset($_SESSION['tasklist'][$id]) ? $_SESSION['tasklist'][$id]:[];
	
	if(empty($action) === false && in_array($action,$action_list) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid action parameter';
	}
}

//add task handler
if($success && $action === 'add'){
	
	if(empty($taskname)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Task name is required.";
	}
	if(empty($member)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Assigned to is required';
	}
	elseif(filter_var($memberid, FILTER_VALIDATE_INT,['options' => array('min_range' => 1)]) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid assigned to parameter. (code 101001)';
	}
	if(empty($tasketa)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Task ETA is required.";
	}
	
	if($success){
		$memberid = intval($memberid);
		$tasketa_date =  DateTime::createfromFormat("Y-m-d", $tasketa, new DateTimeZone(TIME_ZONE));
		
		$row = $db->QuerySelect("SELECT fUserID, CONCAT_WS(' ',fFirstName,fLastName)fFullName FROM tbl_user","fStatus<>0 AND fUserGroup<>1 AND fUserID=$memberid LIMIT 1");
		
		if(empty($row['fUserID'])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid assigned to parameter. (code 102001)';
		}
		elseif($row['fFullName'] != $member){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid assigned to parameter. (code 102002)';
		}
		if($tasketa_date === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid task ETA';
		}
	}

	if($success){
		$taskid = md5(md5($id).md5(time()).md5($memberid));
		
		$data = array(
			'fTaskID' => $taskid, 
			'fTaskName' => $taskname, 
			'fMemberID' =>$memberid, 
			'fAssignedTo' => $member, 
			'fTaskETA' => $tasketa_date->format('m/d/Y'), 
			'fStatus' => 'Inprogress', 
			'fReport' => array('fFileName' => NULL, 'fStatus' => NULL),
			'fComments' => []
		);
		array_unshift($tasklist,$data);	
	}
	
}

//delete task handler
if($success && $action === 'delete'){
	
	unset($tasklist[$taskid]);
	$task_list = array_values($tasklist);
	$tasklist = $task_list;
}

/*
//reset task list
unset($tasklist);
unset($_SESSION['tasklist']);
$success = false;*/

if($success){

	$result = '<ul class="line-item header">
							<li>#</li>
							<li class="fill">Task</li>
							<li class="w-160">Assigned To</li>
							<li class="w-100">ETA</li>
							<li><a href="javascript:void(0);" title="Delete All"><i class="fa-regular fa-trash-can"></i></a></li>
						</ul>';
	foreach($tasklist as $key=>$row){

		$result .='<ul class="line-item id="'.$key.'">
						<li>'.($key+1).'</li>
						<li class="fill">'.$row['fTaskName'].'</li>
						<li class="w-160">'.$row['fAssignedTo'].'</li>
						<li class="w-100">'.$row['fTaskETA'].'</li>
						<li><a href="javascript:void(0);" title="Delete" class="delete" data-action="delete"><i class="fa-regular fa-trash-can"></i></a></li>
					</ul>';
	}
	$_SESSION['tasklist'][$id] = $tasklist;
	$status_data['html'] = $result;
	$status_type = 'success';
	$status_msgs[] = '';
}

$response = array("type" => $status_type, "text" => $status_msgs, "data" => $status_data);
print json_encode($response);
?>