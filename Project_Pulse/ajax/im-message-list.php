<?php
//im_contact_list.php
require_once('../include/config.inc.php');

$userid = '';
$contact_id = '';
$pagecount = '';
$position = '';
$start = 0;
$rows_per_page = 10;

$success= true;

if(isset($_SERVER['REQUEST_METHOD']) === false && $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters.";
}
elseif(empty($user->userid)){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}

if($success){
	//$status_data['post'] = $_POST;
	$pagecount = isset($_POST["page_count"]) ? filter_var($_POST["page_count"], FILTER_SANITIZE_NUMBER_INT):$pagecount;
	$position = isset($_POST["position"]) ? filter_var($_POST["position"], FILTER_SANITIZE_NUMBER_INT):$position;
	$contactid = isset($_POST['txt_contactid']) ? filter_var($_POST['txt_contactid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';

	if(filter_var($pagecount, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid pagination data. (code 101001)";
	}
	elseif(filter_var($position, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid pagination data. (code 101002)";
	}
	elseif(empty($contactid)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid contact reference';
	}
}

if($success){

	$contactid = mysqli_real_escape_string($con,$contactid);

	$contact = $db->QuerySelect("SELECT t.fUserID fContactID, CONCAT_WS(' ',fFirstName, fLastName)fContactName, fAvatar, fAuthKey, TIMESTAMPDIFF(MINUTE, fLastActive, UTC_TIMESTAMP) fActive FROM tbl_user t","fStatus<>0  AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$contactid'");

	if(empty($contact['fContactID'])){
		 $success = false;
		 $status_type = 'error';
		 $status_msgs[] = 'Error: Invalid contact object';
	}
}

if($success){

	$userid = $user->userid;
	$contact_id = $contact['fContactID'];

	$unread = $db->QuerySelect("SELECT COUNT(fIMID)fUnread FROM tbl_im","fTo=$userid AND fFrom=$contact_id AND fToStatus=20");
	$rows_per_page = empty($unread['fUnread']) === false && $unread['fUnread'] > $rows_per_page ? $unread['fUnread'] : $rows_per_page;

	$pagecount = intval($pagecount);
  $position = intval($position);
	$rows_per_page = intval($rows_per_page);
  $start = $position * $rows_per_page;
    
	if($pagecount == 0){

			$results = $db->QuerySelect("SELECT COUNT(fIMID)t_records FROM tbl_im t","(fFrom=$userid AND fTo=$contact_id) OR (fFrom=$contact_id AND fTo=$userid)");

		switch($results){
			case false:
			case $results['t_records'] == 0:
			$success = false;
			$status_type = "info"; 
			$status_msgs[] = "Info: No records found (code 102001)";
			break;

			default:
			$total_records = $results['t_records'];
			$pagecount = ceil($total_records/$rows_per_page);
		}
	}
    
	if($pagecount == 0){
		$success = false;
		$status_type = "notice"; 
		$status_msgs[] = "Notice: No messages found";
	}
    
}

if($success){

  $result_list = $db->QuerySelect("SELECT fIMID, fFrom, fTo, fMessage,t.fCreated, CONCAT_WS(' ',t2.fFirstName,t2.fLastName) fFromName, fAvatar FROM tbl_im t
  LEFT JOIN tbl_user t2 ON t2.fUserID=t.fFrom","(fFrom=$userid AND fTo=$contact_id) OR (fFrom=$contact_id AND fTo=$userid) ORDER BY fIMID DESC LIMIT $start, $rows_per_page",false);
    
  switch($result_list){
		case false:
		case count($result_list) == 0:
		$success = false;
		$status_type = 'notice';
		$status_msgs[] = 'Notice: No more messages found.';
		break;
		
		default:	
		$status_data['pageCount'] = $pagecount;
	}
}

if($success){
    //sort the lates to show latest at bottom of UI
    usort($result_list, function($a,$b){return $a['fIMID'] <=> $b['fIMID'];});
    $db->execQuery("UPDATE tbl_im SET fToStatus=21 WHERE fToStatus=20 AND fTo=$userid AND fFrom=$contact_id");
   
    $html = '';
    
    foreach($result_list as $row){
			switch($row['fFrom']){
				case $userid:
				$html .= '<li data-imid="'.$row['fIMID'].'" class="me">
						<article class="im-wrapper-im">
							<div><strong>[Me]</strong>'.date('d M Y, h:i a', strtotime($row['fCreated'])).'</div>
							<div style="white-space: pre;white-space: pre-line;">'.$row['fMessage'].'</div>
						</article>
						<picture class="im-wrapper-image"><img src="uploads/user/images/avatar/'.$row['fAvatar'].'"></picture>
					</li>';		
				break;
				
				default:
				$html .= '<li data-imid="'.$row['fIMID'].'">
						<picture class="im-wrapper-image"><img src="uploads/user/images/avatar/'.$row['fAvatar'].'"></picture>
						<article class="im-wrapper-im">
							<div><strong>['.$row['fFromName'].']</strong>'.date('d M Y, h:i a', strtotime($row['fCreated'])).'</div>
							<div style="white-space: pre;white-space: pre-line;">'.$row['fMessage'].'</div>
						</article>
					</li>';			
			}
			
    }
    
    $status_type = 'success';
    $status_data['html'] = $html;
}

$response = array("type" => $status_type, "text" => $status_msgs, "data" => $status_data);
print(json_encode($response));
?>