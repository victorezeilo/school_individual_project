<?php
require_once("../include/config.inc.php");

$itemid = '';
$aitemid = '';
$sort = '';
$object = '';

$object_map = array(
	'products' => 'fProductID',
	'setup_product_groups' => 'fGroupID',
	'setup_language' => 'fLanguageID',
	'setup_country' => 'fCountryID',
	'setup_state' => 'fStateID',
	'setup_city' => 'fCityID',
	'setup_package' => 'fPackageID',
	'setup_coupon'	=> 'fCouponID',
  'faq' => 'fFAQID',
);

$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters. (code:101001)";
}
elseif(empty($user->userid) || $user->usergroup > 3){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Unauthorized access attempt. (code:101002)";
}

if($success){
	
	//$status_data = $_POST;
	//sanitize and validate input
	$itemid = isset($_POST['txt_itemid']) ? filter_var($_POST['txt_itemid'], FILTER_SANITIZE_NUMBER_INT) : '';
	$aitemid = isset($_POST['txt_aitemid']) ? filter_var($_POST['txt_aitemid'], FILTER_SANITIZE_NUMBER_INT) : '';
	$sort = isset($_POST['txt_sort']) ? filter_var($_POST['txt_sort'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	$object = isset($_POST['txt_object']) ? filter_var($_POST['txt_object'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	
	if(filter_var($itemid, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102001)';
	}
	elseif(empty($aitemid) || filter_var($aitemid, FILTER_VALIDATE_INT) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102002)';
	}
	elseif($sort !== 'ASC' && $sort !== 'DESC'){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102003)';
	}
	elseif(empty($object) || array_key_exists($object,$object_map) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102004)';
	}

}

if($success){
	//prepare input for use
	$itemid = intval($itemid);
	$aitemid = intval($aitemid);
	$object = mysqli_real_escape_string($con,$object);
	
	$item = $db->select("tbl_$object","{$object_map[$object]}=$itemid AND fStatus<>0");
	$aitem = $aitemid !== -1 ? $db->select("tbl_$object","{$object_map[$object]}=$aitemid AND fStatus <> 0") : '';
	
	//$status_data['item'] = $item;
	//$status_data['aitem'] = $aitem;
	
	if($db->execQuery("DESCRIBE tbl_$object") === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Object not found. (code:103001)';
	}
	elseif(empty($item[$object_map[$object]])){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Item not found. (code:103002)';
	}
	elseif($aitemid !== -1 && empty($aitem[$object_map[$object]])){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Item not found. (code:103003)';
	}

}

if($success){
	//perform sorting
	
	//we must also reset listorder for missing numbers
	$db->execQuery("SET @i=0;");
	$db->execQuery("UPDATE tbl_$object SET fListOrder = @i:=@i+1 WHERE fStatus <> 0 ORDER BY fListOrder, {$object_map[$object]} DESC;");

	//lets sort
	$item_listorder = $item['fListOrder'];
	$aitem_listorder = $aitemid !== -1 ? $aitem['fListOrder'] : 0;
	
	switch(true){
		case $sort === 'ASC' && $aitem_listorder < $item_listorder:

		//$aitem_listorder = 0; //adjacent sort order
		$j = $aitem_listorder + 1;
		
		$db->execQuery("SET @j=$j");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=@j:=@j+1 WHERE fStatus <> 0 AND fListOrder >=$j AND {$object_map[$object]} <> $itemid ORDER BY fListOrder;");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=$j WHERE {$object_map[$object]}=$itemid;");
		break;
		
		case $sort === 'ASC' && $aitem_listorder > $item_listorder:
		//$aitem_listorder = 20; //adjacent sort order
		$j = 0;
		
		$db->execQuery("UPDATE tbl_$object SET fListOrder=$aitem_listorder WHERE {$object_map[$object]}=$itemid;");
		$db->execQuery("SET @j=$j");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=@j:=@j+1 WHERE fStatus <> 0 AND fListOrder <= $aitem_listorder AND {$object_map[$object]} <> $itemid ORDER BY fListOrder");
		break;
		
		//case when $aitem_listorder = 0 must be handled before proceeding two cases
		case $sort === 'DESC' && $aitem_listorder === 0:
		$j = $aitem_listorder;
		
		$db->execQuery("UPDATE tbl_$object SET fListOrder=NULL WHERE {$object_map[$object]} = $itemid");		
		$db->execQuery("SET @j=$j");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=@j:=@j+1 WHERE fStatus <> 0 ORDER BY fListOrder IS NULL, fListOrder;");
		break;
		
		
		case $sort === 'DESC' && $aitem_listorder < $item_listorder:
		//$aitem_listorder = 1; //adjacent sort order
		//$item_listorder = 6; //item sort order
		$j = $item_listorder + 1;
		$db->execQuery("SET @j=$j");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=@j:=@j-1 WHERE fStatus <> 0 AND fListOrder >=$aitem_listorder AND fListOrder <= $item_listorder AND {$object_map[$object]} <> $itemid ORDER BY fListOrder DESC;");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=$aitem_listorder WHERE {$object_map[$object]}=$itemid;");
		//$status_data['ai_so < i_so: query 1'] = $res1;
		break;
		
		case $sort === 'DESC' && $aitem_listorder > $item_listorder:
		//$aitem_listorder = 18; //ajacent sort order
		$j = $aitem_listorder - 1;	
		$db->execQuery("UPDATE tbl_$object SET fListOrder=$j WHERE {$object_map[$object]}=$itemid;");
		$db->execQuery("SET @j=$j");
		$db->execQuery("UPDATE tbl_$object SET fListOrder=@j:=@j-1 WHERE fStatus <> 0 AND fListOrder <=$j AND {$object_map[$object]} <> $itemid ORDER BY fListOrder DESC;");
		//$status_data['ai_so < i_so: query 1'] = $res1;
		break;	
		
	}

}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>