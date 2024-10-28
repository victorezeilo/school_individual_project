<?php
session_start();
$user_data = array();

//echo "<pre>".print_r($_POST,true)."</pre>";
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

	$user_data['post'] = $_POST; 
}

//echo "<pre>".print_r($_FILES,true)."</pre>";
if(isset($_FILES) && !empty($_FILES)){
	$files_data = array();
	foreach($_FILES as $key=>$val){
		//$key is elements name property
		//$value contains the data
		if($val['error'] == 0){
			move_uploaded_file($val['tmp_name'],"../uploads/temp/".$val['name']);
			$files_data[$key] = $val;
		}
	}
	//echo "<pre>".print_r($files_data,true)."</pre>";
	$user_data['files'] = $files_data; 
}

//echo "<pre>".print_r($_GET,true)."</pre>";
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET)){

	$user_data['get'] = $_GET; 
}

//echo $_SERVER['HTTP_REFERER'];
$_SESSION['user_data'] = $user_data;
header("Location:".$_SERVER['HTTP_REFERER']);
?>