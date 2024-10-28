<?php
require_once("../include/config.inc.php");
require_once("../include/paginator/Paginator.class.php");

$pagecount = 0;
$position = 0;
$rows_per_page = 25;

//sort options
$default_sortcolumn = 'userid';
$sortcolumn =  '';
$sort = '';

//filter options
$filtercolumn = '';
$filtervalue = '';
$filtered = '';

$column_map = array(
	'userid' 			=> 't.fUserID',
	'fullname' 		=> "CONCAT_WS(' ',fFirstName, fLastName)",
	'email' 			=> 'fEmail',
	'mobile'			=> 'fMobile',
	'usergroup'		=> 'fGroupName',
	'loginip'			=> "fLoginIP",
	'lastactive' 	=> 'fLastActive',
	'signup' 			=> 't.fCreated',
	'statustext'	=> 'fStatusText',
);

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters";
}
elseif(empty($user->userid) || $user->usergroup >= 4){
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
	
	if(empty($filtercolumn) === false){
		
		switch($filtercolumn){
			case 'fullname':
			case 'email':
			case 'mobile':
			case 'usergroup':
			case 'loginip':
			case 'statustext':
			$filtercolumn = $column_map[$filtercolumn];
			$filtervalue =  mysqli_real_escape_string($con,$filtervalue);
			$filtered = "AND $filtercolumn LIKE '$filtervalue%'";
			break;
				
			case 'lastactive':
			case 'signup':
			$filtercolumn = $column_map[$filtercolumn];
			$filtervalue =  mysqli_real_escape_string($con,$filtervalue);
			$filtered = "AND DATE_FORMAT($filtercolumn,'%Y-%m-%d') = '$filtervalue'";
			break;
		}
	}

		
	$results = $db->QuerySelect("SELECT COUNT(t.fUserID) as t_records FROM tbl_user t
		LEFT JOIN tbl_setup_status t1 on t1.fStatusID=t.fStatus  
		LEFT JOIN tbl_user_group t2 ON t2.fGroupID=t.fUserGroup","t.fStatus NOT IN(0,17,18) AND fUserGroup<>1 $filtered");

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
	$rows = $db->QuerySelect("SELECT t.fUserID, fUserGroup, CONCAT_WS(' ',fFirstName, fLastName)fFullName, fEmail, 0 fEmailVerified, fMobile,0 fMobVerified, fScreenName, fGroupName, fLastActive, IF(TIMESTAMPDIFF(MINUTE,fLastActive,CONVERT_TZ(NOW(),'SYSTEM','+00:00'))<5,1,0)fIsActive, t.fCreated, t.fAuthKey, t.fMetaData, t.fStatus, fStatusText, fLoginIP  FROM tbl_user t
LEFT JOIN tbl_setup_status t1 on t1.fStatusID=t.fStatus
LEFT JOIN tbl_user_group t2 ON t2.fGroupID=t.fUserGroup", "t.fStatus NOT IN(0,17,18) AND fUserGroup<>1 $filtered ORDER BY $sortcolumn $sort LIMIT $start, $rows_per_page", false);
	
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

	$html = '';
	foreach($rows as $row){
		$metadata = empty($row['fMetaData']) ? $constant_metadata['user'] : json_decode($row['fMetaData'],true);
		$html .='<div class="data-list">
              <ul>
								<li><input type="checkbox" name="ids[]" value="'.$row['fUserID'].'" data-syncID="checkAll"><label>ID:</label>'.$row['fUserID'].'</li>
								<li><label>Full Name:</label>'.$row['fFullName'].' '.($row['fIsActive'] ? '<i class="fas fa-circle fa-online"></i>':'<i class="fas fa-circle fa-offline"></i>').'</li>
                <li><label>Email:</label><a href="mailto:'.$row['fEmail'].'">'.$row['fEmail'].'</a>| '.($metadata['profile']['emailVerified'] ? 'Verified':'Unverified').'</li>
              </ul>
              <ul>
                <li><label>Create Date:</label>'.(empty($row['fCreated']) === false ? date('M d, Y',strtotime($row['fCreated'])):'').'</li>
                <li><label>Last Active:</label>'.(empty($row['fLastActive']) === false ? date('M d, Y h:i a',strtotime($row['fLastActive'])):'').'</li>
								<li><label>Login IP:</label>'.$row['fLoginIP'].'</li>
              </ul>
              <ul>
								<li><label>User Group:</label>'.$row['fGroupName'].'</li>
                <li><label>Status:</label>'.$row['fStatusText'].'</li>
                <li>
									'.($row['fStatus'] != 12 && $row['fStatus'] != 19 && $row['fUserID'] !== $user->userid ? '<a href="user-im.php?contactid='.md5(md5($row['fUserID']).md5($row['fAuthKey'])).'" title="Message"><i class="fa-regular fa-comments"></i></a>':'').($row['fStatus'] == 19 ? '<a href="javascript:void(0);" title="Send Activation Link" data-action="sendlink"><i class="fa-solid fa-paper-plane"></i></a>':'').($row['fStatus'] == 10 ? '<a href="#" title="Unlock" style="color: #00A800" data-action="unlock"><i class="fa-solid fa-user-check"></i></a>':'').($row['fStatus'] == 1 ? '<a href="#" style="color: #BD0002" data-action="lock" title="Lock"><i class="fa-solid fa-user-lock"></i></a>':'').'
									<a href="#" title="Delete" style="color: #BD0002" data-action="delete"><i class="fa-solid fa-user-slash"></i></a>
									<a href="#" onClick="location.href=\'user-add.php?id='.$row['fUserID'].'\'" title="Edit User"><i class="fa-solid fa-user-pen"></i></a>
                </li>
              </ul>
            </div>';
	}
	
	$status_type = "success";
	$status_data['html'] = $html;
}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>