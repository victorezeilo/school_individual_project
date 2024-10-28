<?php
require_once("../../include/config.inc.php");

$idx = NULL;
$item_list = isset($_SESSION['itemlist']) ? unserialize($_SESSION['itemlist']) : array();
$itemlist = array();

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_code = "error";
	$status_msgs[] = "Error: Invalid request parameters.";
}
/*elseif(empty($user->userid) === false){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt";
}
*/

if($success){

	$idx = isset($_POST["txt_idx"]) ? filter_var($_POST["txt_idx"], FILTER_SANITIZE_NUMBER_INT):'';

	if(filter_var($idx,FILTER_VALIDATE_INT,['options' => array('min_range' => 0)]) === false){
		$success = false;
		$status_type = "error";
		$status_msg = "Error: Invalid feature reference.";
	}
}

if($success){
			
	unset($item_list[$idx]);

	if(!empty($item_list)){foreach($item_list as $id){$itemlist[] = $id;}}

	$result = '<ul class="line-item header">
							<li class="w-30">#</li>
							<li>Feature Name</li>
							<li class="action">&nbsp;</li>
						</ul>';
	
	if(!empty($itemlist)){
		foreach($itemlist as $key=>$row){
			$result .='<ul class="line-item id="'.$key.'">
							<li class="w-30">'.($key + 1).'</li>
							<li>'.$row.'</li>
							<li class="action"><a href="javascript:void(0);" class="delete"><i class="fe fe-minus-circle"></i></a></li>
						</ul>';
		}
	}
	$_SESSION['itemlist'] = serialize($itemlist);
	$status_data['html'] = $result;
	$status_type = 'success';
	$status_msgs[] = "";
}

$response = array("type" => $status_type, "text" => $status_msgs, "data" => $status_data);
print json_encode($response);
?>