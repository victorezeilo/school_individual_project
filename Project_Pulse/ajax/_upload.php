<?php 
require_once("../include/config.inc.php");
require_once("../include/simpleimage/SimpleImage.class.php");


//valid action types
$action_list = array("avatar",'import-user','report');

$upload = '';
$action = '';

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
    
	//$status_data['files'] = $_FILES;
	$upload = isset($_FILES['txt_upload']) ? filter_var_array($_FILES['txt_upload']):array();
	$action = isset($_POST['txt_action']) ? filter_var($_POST['txt_action'], FILTER_SANITIZE_FULL_SPECIAL_CHARS):'';

	//allowed file types
	$mime_list = array(
		"image/jpeg" =>".jpg", 
		"image/png" => ".png", 
		"image/gif" => ".gif", 
		"image/tiff" => ".tif",
		'text/csv' => '.csv',
		'application/vnd.ms-excel' => '.csv',
		'application/pdf' => '.pdf'
	);    

	if(empty($action) || !in_array($action,$action_list)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid action parameter.";
	}
	if(!isset($upload['name']) || empty($upload['name'])) {
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid image file, please selected another.";
	}
	elseif($upload['error'] == 1 && $upload['size'] == 0){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: File upload failed, maximum allowed file size is 256 MB.";
	}
	elseif(!array_key_exists($upload['type'], $mime_list)){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Invalid file type, please selected another.";
	}
	if(!move_uploaded_file($upload['tmp_name'],"../uploads/temp/".$upload['name'])){
		$success = false;
		$status_type = "error";
		$status_msgs[] = "Error: Failed to move media content.";
	}

}

if($success && $action === 'avatar'){

	$media = date("YmdHis")."_".rand(1000,9999).$mime_list[$upload['type']];
	$img = new SimpleImage();
	$img->load("../uploads/temp/{$upload['name']}");
	$img->resize(250,250);
	$img->save("../uploads/temp/$media");
	unlink("../uploads/temp/{$upload['name']}");
	$img = '<img src="'."uploads/temp/$media".'" width="214" alt="">';

	$status_data['action'] = $action;
	$status_data['media'] = $media;
	$status_data['img'] = $img;

	$status_type = "success";
	$status_msgs[] = "Success: Media upload successful.";
}

if($success && $action === 'import-user'){

	$status_data['fileName'] = $upload['name'];
	$status_type = "success";
	$status_msgs[] = "Success: File upload successful.";
}

if($success && $action === 'report'){

	$filename = date("YmdHis")."-".rand(1000,9999).$mime_list[$upload['type']];
	rename("../uploads/temp/{$upload['name']}", "../uploads/temp/$filename");
	$status_data['fileName'] = $filename;
	$status_type = "success";
	$status_msgs[] = "Success: File upload successful.";
}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>