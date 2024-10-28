<?php
require_once("../include/config.inc.php");
require_once("../include/paginator/Paginator.class.php");

$pagecount = 0;
$position = 0;
$rows_per_page = 25;

//sort options
$default_sortcolumn = 'projectid';
$sortcolumn =  '';
$sort = '';

//filter options
$filtercolumn = '';
$filtervalue = '';
$filtered = '';

$column_map = array(
	'projectid' 	=> 'fProjectID',
	'title' 			=> 't.fTitle',
	'owner'				=> 'fOwner',
	'manager' 		=> 'fManager',
	'startdate'		=> 'fStartDate',
	'enddate'			=> 'fEndDate',
	'statustext'	=> 'fStatusText',
);

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters";
}
elseif(empty($user->userid) || $user->usergroup >= 6){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}

if($success){
	//$status_data['post'] = $_POST;
	$position = isset($_POST["position"]) ? filter_var($_POST["position"], FILTER_SANITIZE_NUMBER_INT):$position;
	$rows_per_page = isset($_POST["rows_per_page"]) ? filter_var($_POST["rows_per_page"], FILTER_SANITIZE_NUMBER_INT):$rows_per_page;

	$filtercolumn = isset($_POST["filtercolumn"]) ? filter_var($_POST["filtercolumn"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):$filtercolumn;
	$filtervalue = isset($_POST["filtervalue"]) ? filter_var($_POST["filtervalue"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):$filtervalue;

	$sortcolumn = isset($_POST["sortcolumn"]) ? filter_var($_POST["sortcolumn"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):$sortcolumn;
	$sort = isset($_POST["sort"]) ? filter_var($_POST["sort"], FILTER_SANITIZE_FULL_SPECIAL_CHARS):$sort;

	if(filter_var($position, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid pagination data. (code 101001)";
	}
	elseif(filter_var($rows_per_page, FILTER_VALIDATE_INT, array('options' => array('min_range' => 25, 'max_range' => 1000))) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Incorrect pagination data. (code 101002)";
	}
}

if($success){

	//prepare input for use
	$position = intval($position);
	$rows_per_page = intval($rows_per_page);
	$start = $position*$rows_per_page; 
	
	$sortcolumn = empty($sortcolumn) === false ?  $sortcolumn : $default_sortcolumn;
	$sortcolumn = $column_map[$sortcolumn];
	$sort = mysqli_real_escape_string($con,$sort);
	
	if($user->usergroup >= 3){
		$filtered = "AND (fManagerID = $user->userid OR fOwnerID=$user->userid OR JSON_CONTAINS(fMetaData->'$[*].fMemberID',JSON_ARRAY($user->userid)))";
	}
	
	if(empty($filtercolumn) === false){
		
		switch($filtercolumn){
			case 'title':
			case 'owner':
			case 'manager':
			case 'admintext':
			$filtercolumn = $column_map[$filtercolumn];
			$filtervalue =  mysqli_real_escape_string($con,$filtervalue);
			$filtered = "AND $filtercolumn LIKE '$filtervalue%'";
			break;
				
			case 'startdate':
			case 'enddate':
			$filtercolumn = $column_map[$filtercolumn];
			$filtervalue =  mysqli_real_escape_string($con,$filtervalue);
			$filtered = "AND DATE_FORMAT($filtercolumn,'%Y-%m-%d') = '$filtervalue'";
			break;
		}
	}

		
	$results = $db->QuerySelect("SELECT COUNT(t.fProjectID) as t_records FROM tbl_project t
		LEFT JOIN tbl_setup_status t1 on t1.fStatusID=t.fStatus","t.fStatus<>0 $filtered");

	switch($results){
		case false:
		case $results['t_records'] == 0:
		$success = false;
		$status_type = "none"; 
		$status_msgs[] = "Notice: No records found.";
		break;

		default:
		$total_records = $results['t_records'];
		$pagecount = ceil($total_records/$rows_per_page);
	}
}

if($success){

	//prepare request output
	$rows = $db->QuerySelect("SELECT t.fProjectID, fOwnerID, fOwner, MD5(CONCAT(MD5(fOwnerID),MD5(t2.fAuthKey))) fOwnerIM, t.fTitle, fDescription, fManagerID, fManager, MD5(CONCAT(MD5(fManagerID),MD5(t3.fAuthKey))) fManagerIM, fStartDate, fEndDate, t.fMetaData, t.fStatus, fStatusText FROM tbl_project t
	LEFT JOIN tbl_setup_status t1 ON t1.fStatusID=t.fStatus
    LEFT JOIN tbl_user t2 ON t2.fUserID=t.fOwnerID
    LEFT JOIN tbl_user t3 ON t3.fUserID=t.fManagerID", "t.fStatus<>0 $filtered ORDER BY $sortcolumn $sort LIMIT $start, $rows_per_page", false);
	
	switch($rows){
		case false:
		case count($rows) == 0:
		$success = false;
		$status_type = 'warn';
		$status_msgs[] = 'Warning: No records founds (code 103001)';
		break;
		
		default:	
		$status_data['page_count'] = $pagecount;
		$pager = new Paginator(array('rows_per_page' => $rows_per_page, 'links_per_page' => 20, 'page_count' => $pagecount, 'position' => $position));
		$status_data['pagination'] = $pager->paginate();
		$status_data['pagenumber'] = $pager->renderPage();
	}
}

if($success){

	$html = '<ul class="line-item header">
							<li><input type="checkbox" id="checkAll" data-sync="true"> #</li>
							<li class="fill">Project</li>
							<li class="w-160">Owner</li>
							<li class="w-160">Manager</li>
							<li class="w-100">Tasks</li>
							<li class="w-100">Start</li>
							<li class="w-100">End</li>
							<li class="w-100">Status</li>
							<li class="w-160"></li>
						</ul>';
	foreach($rows as $key=>$row){
		$tasklist = empty($row['fMetaData']) === false ? json_decode($row['fMetaData'],true):[];
		$html .='<ul class="line-item" id="'.$row['fProjectID'].'">
						<li><input type="checkbox" name="ids[]" value="'.$row['fProjectID'].'" data-syncID="checkAll" class="mr-10">'.($key+1).'</li>
						<li class="fill" title="'.$row['fDescription'].'">'.$row['fTitle'].'</li>
						<li class="w-160">'.$row['fOwner'].'<a href="user-im.php?contactid='.$row['fOwnerIM'].'" class="ml-10" title="Message"><i class="fa-regular fa-comments"></i></a></li>
						<li class="w-160">'.$row['fManager'].'<a href="user-im.php?contactid='.$row['fManagerIM'].'" class="ml-10" title="Message"><i class="fa-regular fa-comments"></i></a></li>
						<li class="w-100">'.count($tasklist).'</li>
						<li class="w-100">'.date('m/d/Y',strtotime($row['fStartDate'])).'</li>
						<li class="w-100">'.date('m/d/Y',strtotime($row['fEndDate'])).'</li>
						<li class="w-100">'.$row['fStatusText'].'</li>
						<li class="w-160 center">
							'.($user->usergroup <= 2 || $row['fOwnerID'] == $user->userid || $row['fManagerID'] == $user->userid ? '<a href="project-add.php?id='.$row['fProjectID'].'" title="Edit"><i class="fa-solid fa-pencil"></i></a>':'').'
							<!--<a href="project-view.php?id='.$row['fProjectID'].'" title="Details" class="mlr-10"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>-->
							<a href="task-list.php" class="mlr-10" title="Task List"><i class="fa-solid fa-list-check"></i></a>
							<a href="#" title="Delete" style="color: #BD0002" data-action="delete"><i class="fa-solid fa-trash-alt"></i></a>
						</li>
					</ul>';
	}
	
	$status_type = "success";
	$status_data['html'] = $html;
}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>