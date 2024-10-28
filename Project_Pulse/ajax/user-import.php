<?php 
require_once("../include/config.inc.php");

$step = NULL;
$filename = NULL;
$user_list = NULL;
$group_list = $db->QuerySelect("SELECT fGroupID, fGroupName FROM tbl_user_group","fStatus=1 AND fGroupID<>1",false);
$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters";
}
elseif(empty($user->userid)){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}

if($success){
    
	//$status_data['get'] = $_GET;
	$step = isset($_POST['txt_step']) ? filter_var($_POST['txt_step'],FILTER_SANITIZE_NUMBER_INT) : NULL;
	$filename = isset($_POST['txt_filename']) ? filter_var($_POST['txt_filename'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):NULL;
	
	if(filter_var($step,FILTER_VALIDATE_INT,['options' => array('min_range' => 1, 'max_range' => 3)]) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid step parameter.";
	}
	if(empty($filename)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: File name is required.";
	}
	elseif(file_exists("../uploads/temp/$filename") === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: File does not exist.";
	}
	elseif(($handle = fopen("../uploads/temp/$filename", "r")) === false){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Could not open file.";
	}
}

if($success && $step == 1){

	$counter = 1;
	$result = '<ul class="line-item header">
						<li class="w-50">#</li>
						<li class="w-240">Email</li>
						<li class="fill">First Name</li>
						<li class="fill">Last Name</li>
						<li class="w-160">Role</li>
					</ul>';
	
	while (($data = fgetcsv($handle, 1000, ",")) !== false) {

		if(empty($data) || count($data) != 4){
			//$status_msgs[] = "Notice: Empty/Incomplete data skipped";
			continue;
		}
		
		$result .='<ul class="line-item id="'.$counter.'">
						<li class="w-50">'.$counter.'</li>
						<li class="w-240">'.$data[0].'</li>
						<li class="fill">'.$data[1].'</li>
						<li class="fill">'.$data[2].'</li>
						<li class="w-160">'.$data[3].'</li>
					</ul>';

		
		$email = mysqli_real_escape_string($con,$data[0]);
		$pair = $db->select('tbl_user',"fEmail='$email' AND fStatus<>0 LIMIT 1");
		
		if(empty($pair['fUserID']) === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Account exist for $email at line $counter";
		}
		if(filter_var($data[0],FILTER_VALIDATE_EMAIL) === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Invalid email at line $counter";
		}
		if(empty($data[1])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: First name required at line $counter";
		}
		if(empty($data[2])){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Last name required at line $counter";
		}
		if(Functions::searchArray($group_list,$data[3],'fGroupName') === false){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Invalid role at $counter";
		}
		if(empty($user_list) === false && Functions::searchArray($user_list,$data[0],'0')){
			$success = false;
			$status_type = 'error';
			$status_msgs[] = "Error: Duplicate data found at line $counter";
		}
		$user_list[] = $data;
		$counter++;
	}
	fclose($handle);
	$status_data['html'] = $result;
	if($success) {
		$status_type = 'success';
		$status_msgs[] = 'Success: Ready to import, click next';
		$status_data['step'] = 2;
		$_SESSION['user_list'] = $user_list;
	}
}

if($success && $step == 2){
	
	$user_list = isset($_SESSION['user_list']) ? $_SESSION['user_list']:NULL;
	
	if(empty($user_list)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = "Error: User list is empty.";
	}
	
	foreach($user_list as $row){
		$groupid = Functions::getObjectData($group_list,'fGroupName',$row[3])['fGroupID'];
		$arr[] = "($groupid,'{$row[0]}','{$row[1]}','{$row[2]}','user_01.jpg','".TIME_ZONE."','".Functions::getRandomString()."','".json_encode($constant_metadata['user'])."',19,$user->userid)";
	}
	
	$success = $db->execQuery("INSERT INTO tbl_user (fUserGroup,fEmail,fFirstName,fLastName,fAvatar,fTimeZone,fAuthKey,fMetaData,fStatus,fUID) VALUES".implode(',',$arr));
	$status_type = 'error';
	$status_msgs[] = "Error: Import data failed.";
	
	if($success){
		unset($_SESSION['user_list']);
		$status_type = 'success';
		$status_msgs = ['Success: Import data complete.'];
		$status_data['step'] = 3;
	}
}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>