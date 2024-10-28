<?php
//im_message_add.php
require_once('../include/config.inc.php');
require_once('../include/classes/IM.class.php');

$userid = '';
$contact_id = '';
$lastimid = '';
$success = true;

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
    //receive parse and validate input data
    //$status_data['post'] = $_POST;
    $contactid = isset($_POST['txt_contactid']) ? filter_var($_POST['txt_contactid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
    $lastimid = isset($_POST['txt_lastimid']) ? filter_var($_POST['txt_lastimid'], FILTER_SANITIZE_NUMBER_INT):'';

    if(empty($contactid)){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid contact reference';
    }
    elseif(filter_var($lastimid,FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false) {
			$success = false;
			$status_type = 'error';
			$status_msgs[] = 'Error: Invalid message reference';
    }
}

if($success){
    //prepare input for use
    $contactid = mysqli_real_escape_string($con,$contactid);
    $lastimid = intval($lastimid);

    $contact = $db->QuerySelect("SELECT t.fUserID fContactID FROM tbl_user t","fStatus<>0 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$contactid'");

    if(empty($contact['fContactID'])){
			 $success = false;
			 $status_type = 'error';
			 $status_msgs[] = 'Error: Invalid contact object';
    }
}

if($success){

    $userid = $user->userid;
    $contact_id = $contact['fContactID'];

    $result_list = $db->QuerySelect("SELECT fIMID, fFrom, fTo, fMessage,t.fCreated, CONCAT_WS(' ',t2.fFirstName,t2.fLastName) fFromName, fAvatar FROM tbl_im t LEFT JOIN tbl_user t2 ON t2.fUserID=t.fFrom","fIMID > $lastimid AND ((fFrom=$userid AND fTo=$contact_id) OR (fFrom=$contact_id AND fTo=$userid)) ORDER BY fIMID",false);
    
    $status_data['playSound'] = empty($result_list) === false ? true:false;
    
    $db->execQuery("UPDATE tbl_im SET fToStatus=21 WHERE fToStatus=20 AND fTo=$userid AND fFrom=$contact_id");

    $html = '';

    foreach($result_list as $row){
			/*
        $html .= '<li data-imid="'.$row['fIMID'].'">
                    <picture class="message-content-image"><img src="uploads/user/images/avatar/'.$row['fAvatar'].'"></picture>
                    <article class="message-content-im"><div><strong>'.$row['fFromName'].'</strong>'.date('d M, Y h:i:s a', strtotime($row['fCreated'])).' - UTC</div><div style="white-space: pre;white-space: pre-line;">'.$row['fMessage'].'</div></article>
                </li>';
			*/
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