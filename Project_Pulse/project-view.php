<?php
//user_list.php
require_once("include/config.inc.php");
require_once("include/classes/Project.class.php");
require_once("include/classes/Alert.class.php");

if(empty($user->userid) || $user->usergroup >= 6){header("Location: user-login.php"); exit;}

$mainNav = explode(".","3.1.0");
$breadCrumb = 'Project View';

$user_data = isset($_SESSION['user_data']) ? $_SESSION['user_data'] :'';
//echo '<pre>'.print_r($user_data,true).'</pre>';

$id = NULL;
$title = NULL;
$description = NULL;
$managerid = NULL;
$manager = NULL;
$startdate = NULL;
$enddate = NULL;
$metadata = NULL;

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
		$tasklist = empty($row['fMetaData']) === false ? json_decode($row['fMetaData'],true):[];
	}
}


$response = json_encode(array("type" => $status_type, "text" => $status_msgs));

include('templates/header.tpl.php');
include('templates/project-view.tpl.php');
include('templates/footer.tpl.php');

unset($_SESSION['user_data']);
?>