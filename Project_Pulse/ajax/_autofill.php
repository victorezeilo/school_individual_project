<?php
require_once("../include/config.inc.php");

$source = '';
$filter = '';
$itemid = '';
$term = '';
$object = '';

$object_map = array(
	'setup_state' => array(
		'default' => array(
			'filter' => array('column' => 't.fCountryID', 'term' => 'fStateName'),
			'return' => array('fStateID' => 'id', 'fStateName' => 'label'),
			'joins'	=> '',
			'where' => '',
			'sort'	=> 't.fListOrder',
		),
	),
	'setup_city' => array(
		'default' => array(
			'filter' => array('column' => 'fStateID', 'term' => 'fCityName'),
			'return' => array('fCityID' => 'id', 'fCityName' => 'label'),
			'joins'	=> '',
			'where' => '',
			'sort'	=> 't.fListOrder',
		),
		'autocomplete' => array(
			'filter' => array('column' => 't.fStateID', 'term' => 'fCityName'),
			'return' => array('DISTINCT t.fCityID' => 'id', "CONCAT_WS(' ',CONCAT_WS(' - ',fCityName,fStateCode),fZIP)" => 'label','fCityName' => 'value', 't.fStateID' => 'stateid','fStateName'=>'statename', 'fZIP' => 'zip'),
			'joins' => "LEFT JOIN tbl_setup_state t2 ON t2.fStateID=t.fStateID LEFT JOIN tbl_setup_chamber t3 ON t3.fCityID=t.fCityID",
			'where' => '',
			'sort'	=> 't.fListOrder',
		),
		'search' => array(
			'filter' => array('column' => 't.fStateID', 'term' => 'fCityName'),
			'return' => array('fCityID' => 'id', "CONCAT(fCityName,' - ',fStateCode)" => 'label'),
			'joins' => "LEFT JOIN tbl_setup_state t2 ON t2.fStateID=t.fStateID",
			'where' => '',
			'sort'	=> 't.fListOrder',
		),
	),
	'user' => array(
		'default' => array(
			'filter' => array('column' => 't.fUserID', 'term' => "CONCAT_WS(' ',t.fFirstName, t.fLastName)"),
			'return' => array('t.fUserID' => 'id', "CONCAT_WS(' ',t.fFirstName, t.fLastName)" => 'label'),
			'joins'	=> '',
			'where' => 't.fUserGroup<>1',
			'sort'	=> "CONCAT_WS(' ',t.fFirstName, t.fLastName)",
		),
		'surveyor' => array(
			'filter' => array('column' => 't.fUserID', 'term' => "CONCAT_WS(' ',t.fFirstName, t.fLastName)"),
			'return' => array('t.fUserID' => 'id', "CONCAT_WS(' ',t.fFirstName, t.fLastName)" => 'label'),
			'joins' => '',
			'where' => "t.fUserGroup=5",
			'sort'	=> "CONCAT_WS(' ',t.fFirstName, t.fLastName)",
		),
	),
	'setup_veteran' => array(
		'default' => array(
			'filter' => array('column' => 't.fVeteranID', 'term' => "t.fVeteranName"),
			'return' => array('t.fVeteranID' => 'id', "fVeteranName" => 'label'),
			'joins'	=> '',
			'where' => '',
			'sort'	=> "fVeteranName",
		),
	),
	'setup_chamber' => array(
		'default' => array(
			'filter' => array('column' => 't.fChamberID', 'term' => "t.fChamberName"),
			'return' => array('t.fChamberID' => 'id', "fChamberName" => 'label'),
			'joins'	=> '',
			'where' => '',
			'sort'	=> "fChamberName",
		),
	),
	'setup_insurance_company' => array(
		'default' => array(
			'filter' => array('column' => 't.fCompanyID', 'term' => "t.fCompanyName"),
			'return' => array('t.fCompanyID' => 'id', "fCompanyName" => 'label'),
			'joins'	=> '',
			'where' => '',
			'sort'	=> "fCompanyName",
		),
	),

);


$success = true;

if(isset($_SERVER['REQUEST_METHOD']) === false || $_SERVER['REQUEST_METHOD'] != 'POST'){
	$success = false;
	$status_type = "error";
	$status_msgs[] = "Error: Invalid request parameters. (code:101001)";
}
/* move this to specific places
elseif(empty($user->userid) || $user->usergroup > 4){
	$success = false;
	$status_type = "error";
	$status_msg = "Error: Unauthorized access attempt. (code:101002)";
}
*/

if($success){
	//sanitize and validate input
  //$status_data['post'] = $_POST;
	$object = isset($_POST['txt_object']) ? filter_var($_POST['txt_object'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	$source = isset($_POST['txt_source']) ? filter_var($_POST['txt_source'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	$filter = isset($_POST['txt_filter']) ? filter_var($_POST['txt_filter'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	$itemid = isset($_POST['txt_itemid']) ? filter_var($_POST['txt_itemid'], FILTER_SANITIZE_NUMBER_INT) : '';
	$term = isset($_POST['txt_term']) ? filter_var($_POST['txt_term'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
	
	//set the source to default if not provided
	$source = empty($source) === false ? $source : 'default';
	
	if(empty($object) || array_key_exists($object,$object_map) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102001)';
	}
	elseif($filter !== 'column' && $filter !== 'term'){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102002)';
	}
	elseif($filter === 'column' && filter_var($itemid, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102003)';
	}
	elseif($filter === 'term' && empty($term)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102004)';
	}
	elseif(array_key_exists($source,$object_map[$object]) === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Invalid/empty required paramter. (code:102005)';
	}

}

if($success){
	//prepare input for use
	$itemid = intval($itemid);
	$term = mysqli_real_escape_string($con,$term);;
	$object = mysqli_real_escape_string($con,$object);
	$source = mysqli_real_escape_string($con,$source);
	
	$query = '';
	switch($filter){
		case 'column':
		$query = "{$object_map[$object][$source]['filter'][$filter]}=$itemid";
		break;
		
		case 'term':
		$query = "{$object_map[$object][$source]['filter'][$filter]} LIKE '$term%'";
		break;
	}
	
	$query .= empty($object_map[$object][$source]['where']) === false ? " AND {$object_map[$object][$source]['where']}" : '';
	
	if($db->execQuery("DESCRIBE tbl_$object") === false){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Object not found. (code:103001)';
	}
	elseif(empty($query)){
		$success = false;
		$status_type = 'error';
		$status_msgs[] = 'Error: Item not found. (code:103002)';
	}

}

if($success){
	//process and return data
	//create columns list
	foreach($object_map[$object][$source]['return'] as $key=>$value){$data[] = "$key $value";}
	//stringify columns array
	$columns = implode(",",$data);
	//$status_data['sql'] = "SELECT $columns FROM tbl_$object t {$object_map[$object][$source]['joins']} WHERE t.fStatus=1 AND $query ORDER BY {$object_map[$object][$source]['sort']}";
	$list = $db->QuerySelect("SELECT $columns FROM tbl_$object t {$object_map[$object][$source]['joins']}","t.fStatus NOT IN(0,2,10) AND $query ORDER BY {$object_map[$object][$source]['sort']}",false);
	$status_type = 'success';
	$status_data['list'] = $list;
}

$response = array('type' => $status_type, 'text' => $status_msgs, 'data' => $status_data);
print json_encode($response);
?>