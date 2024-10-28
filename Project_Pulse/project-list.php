<?php
//project_list.php
require_once("include/config.inc.php");
require_once("include/classes/Project.class.php");

if(empty($user->userid) || $user->usergroup >= 6){header("Location: user-login.php"); exit;}

$mainNav = explode(".","3.2.0");
$breadCrumb = 'Projects';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';
$success = true;
$action = '';
$ids = [];

$action_list = array(
	'active'		=> "Activate",
	'overdue' 	=> "Overdue",
	'complete'	=> 'Complete',
	'fail'			=> 'Fail',
	'delete'		=> 'Delete'
);

//handle object update actions
if(isset($user_data['post']['btn_submit']) && $user_data['post']['btn_submit'] == 'update') {
	
	$action = isset($user_data['post']['txt_action']) ? filter_var($user_data['post']['txt_action'],FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	$ids = isset($user_data['post']['ids']) ? filter_var_array($user_data['post']['ids']):[];

	if(empty($action) || array_key_exists($action, $action_list) === false) {
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Invalid action parameter.";
	}
	if(count($ids) <= 0) {
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: Object list is empty.";
	}

	if($success){

		switch($action) {
			case 'delete';
			$status = 0;
			break;
			
			case 'active':
			$status = 1;
			break;

			case 'fail':
			$status = 20;
			break;

			case 'complete':
			$status = 21;
			break;
			
			case 'overdue':
			$status = 22;
			break;	
		}

		foreach($ids as $val) {
			$success = true;
			$object = new Project($db->select("tbl_project", "fStatus<>0 AND fProjectID=$val"));

			if(empty($object->projectid)){
				$success = false;
				$status_msgs[] = "Error: Object not found. (id:$val)";
			}
			if($user->usergroup >= 3 && $user->userid != $object->ownerid){
				$success = false;
				$status_msgs[] = "Error: Permission denied. (id:$val)";
			}

			if($success){
				$object->status = $status;
				$object->uid = $user->userid;
				$object->save();
			}
		}

		switch(count($status_msgs)){
			case 0:
			$status_type = 'success';
			$status_msgs[] = "Success: Operation command completed";
			break;

			default:
			array_unshift($status_msgs,"Warning: Failed or partial success.");
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
	'title' 			=> "Title",
	'owner'				=> 'Owner',
	'manager'			=> 'Manager',
	'statustext'	=> 'Status'
);

$sortcolumn_list = array(
	'title' 			=> "Title",
	'manager'			=> 'Manager',
	'startdate'		=> 'Start Date',
	'enddate'			=> 'End Date',
	'statustext'		=> 'Status'
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
include("templates/project-list.tpl.php");
include("templates/footer.tpl.php");

unset($_SESSION['user_data']);
?>