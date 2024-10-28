<?php
//im_contact_list.php
require_once('../include/config.inc.php');

$userid = '';
$contactid = '';
$keepid = '';

$success= true;

if(isset($_SERVER['REQUEST_METHOD']) === false && $_SERVER['REQUEST_METHOD'] != 'ُPOST'){
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
     
	 $contactid = isset($_POST['txt_contactid']) ? filter_var($_POST['txt_contactid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';
	 $keepid = isset($_POST['txt_keepid']) ? filter_var($_POST['txt_keepid'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';

	 $contactid = mysqli_real_escape_string($con,$contactid);
	 $keepid = mysqli_real_escape_string($con,$keepid);
	 $contact = $db->QuerySelect("SELECT t.fUserID fContactID, CONCAT_WS(' ',fFirstName, fLastName)fContactName, fAvatar, fAuthKey, TIMESTAMPDIFF(MINUTE, fLastActive, UTC_TIMESTAMP) fActive FROM tbl_user t","fStatus<>0 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$contactid'");
	 $keep = $db->QuerySelect("SELECT t.fUserID fContactID, CONCAT_WS(' ',fFirstName, fLastName)fContactName, fAvatar, fAuthKey, TIMESTAMPDIFF(MINUTE, fLastActive, UTC_TIMESTAMP) fActive FROM tbl_user t","fStatus<>0 AND MD5(CONCAT(MD5(fUserID),MD5(fAuthKey)))='$keepid'");
	 
	 $support = $db->QuerySelect("SELECT t.fUserID fContactID, 'Support Desk' fContactName, fAvatar, fAuthKey, TIMESTAMPDIFF(MINUTE, fLastActive, UTC_TIMESTAMP) fActive FROM tbl_user t","fStatus<>0 AND fEmail='".SUPPORT_EMAIL."'");
     
 }

if($success){
    
	$userid = $user->userid;

	$contact_list = $db->QuerySelect("SELECT fTo fContactID, CONCAT_WS(' ',fFirstName, fLastName)fContactName, fAvatar, fAuthKey, TIMESTAMPDIFF(MINUTE, fLastActive, UTC_TIMESTAMP) fActive FROM (SELECT fTo FROM tbl_im WHERE fFrom=$userid UNION DISTINCT SELECT fFrom FROM tbl_im WHERE fTo=$userid) t LEFT JOIN tbl_user t1 ON t1.fUserID=t.fTo","",false);

	if(empty($contact['fContactID']) === false && Functions::searchArray($contact_list,$contact['fContactID'], 'fContactID') === false){
			$contact_list[] = $contact;
	}

	if(empty($keep['fContactID']) === false && Functions::searchArray($contact_list,$keep['fContactID'], 'fContactID') === false){
			$contact_list[] = $keep;
	}

	if(empty($support['fContactID']) === false && $support['fContactID'] != $userid && Functions::searchArray($contact_list,$support['fContactID'], 'fContactID') === false){
			$contact_list[] = $support;
	}

	foreach($contact_list as &$item){

		$last_msg = $db->QuerySelect("SELECT fIMID,fMessage, fUnread FROM tbl_im t
LEFT JOIN (SELECT fTo, COUNT(fIMID)fUnread FROM tbl_im WHERE fTo=$userid AND fFrom={$item['fContactID']} AND fToStatus=20) t1 ON t1.fTo=t.fTo OR t1.fTo=t.fFrom","(t.fFrom=$userid AND t.fTo={$item['fContactID']}) OR (t.fFrom={$item['fContactID']} AND t.fTo=$userid) ORDER BY fIMID DESC LIMIT 1");
		$item['fIMID'] = empty($last_msg['fIMID']) === false ? $last_msg['fIMID'] : 4294967295;
		$item['fMessage'] = empty($last_msg['fMessage']) === false ? $last_msg['fMessage'] : '';
		$item['fUnread'] = empty($last_msg['fUnread']) === false ? $last_msg['fUnread'] : 0;
		unset($item);
	}
	usort($contact_list, function($a,$b){return $b['fIMID'] <=> $a['fIMID'];});

	//$status_data['contact_list'] = $contact_list;

	$html = '';

	foreach($contact_list as $item){
		$html .= '<li data-contactid="'.(md5(md5($item['fContactID']).md5((empty($item['fAuthKey']) === false ? $item['fAuthKey']:'')))).'">
							<picture class="im-contact-list-image">
								<img src="uploads/user/images/avatar/'.(empty($item['fAvatar']) === false ? $item['fAvatar']:'user_01.jpg').'">
								<i class="fas fa-circle '.(is_null($item['fActive']) === false && $item['fActive'] <= 1 ? 'fa-online':'fa-offline').'"></i>
							</picture>
							<article class="im-contact-list-contact">
								<div>
									<strong>'.$item['fContactName'].'</strong>
									<p>'.Functions::getLastSeen($item['fActive']).'</p>
								</div>
								<div>'.(strlen($item['fMessage']) > 21 ? substr($item['fMessage'],0,21).'...': $item['fMessage']).'</div>
							</article>
						</li>';
	}

	$status_type = 'success';
	$status_data['html'] = $html;
}

$response = array("type" => $status_type, "text" => $status_msgs, "data" => $status_data);
print(json_encode($response));
?>