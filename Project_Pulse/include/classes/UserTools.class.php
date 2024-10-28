<?php
//UserTools.class.php
class UserTools {
	/*
	* Class constructor
	*/
	private $db;
	
	function __construct() {
		 $this->db = new MySQLDB();
	}

	public function login($username='', $password='', $hashsalt=''){
		
		$newuser = new User($this->db->select("tbl_user", "fStatus<>0 AND fEmail='$username'"));
		
		switch(password_verify(hash_hmac("sha256", $password, $hashsalt), $newuser->password)){
			case true:
			$metadata = Constants::METADATA;
			$con_metadata = empty($metadata['user']) === false ? $metadata['user'] : array();
			$user_metadata = empty($newuser->metadata) === false ? json_decode($newuser->metadata,true):array();
			$newuser->authkey = Functions::getRandomString();
      $newuser->metadata = json_encode(self::updateMetaData($con_metadata,$user_metadata));
			$newuser->lastlogin = $newuser->lastactive;
			$newuser->loginip = $_SERVER['REMOTE_ADDR'];
			$lastactive = new DateTime("now", new DateTimeZone("UTC"));
			$lastactive->setTimestamp(time()); 
			$newuser->lastactive = $lastactive->format("Y-m-d H:i:s");
			$newuser->save();
			$_SESSION['admin_id'] = $newuser->usergroup <= 2 ? md5(md5($newuser->userid).md5($newuser->authkey)):NULL;
			$_SESSION['user_id'] = md5(md5($newuser->userid).md5($newuser->authkey));
			return true;
			break;
			
			default:
			return false;
		}
	}

	//auto login user after successfull accout activation
	public function autologin($email)
	{
		$newuser = new User($this->db->select("tbl_user","fEmail='$email' AND fStatus IN(1,12,13,14,15)"));
		
		switch(empty($newuser->userid) === false){
			case true:
			$metadata = Constants::METADATA;
			$con_metadata = empty($metadata['user']) === false ? $metadata['user'] : array();
			$user_metadata = empty($newuser->metadata) === false ? json_decode($newuser->metadata,true):array();
			$newuser->authkey = $user_metadata['profile']['emailVerified'] === false && $newuser->status == 12 ? $newuser->authkey : Functions::getRandomString();
			$newuser->metadata = json_encode(self::updateMetaData($con_metadata,$user_metadata));
			$lastactive = new DateTime("now", new DateTimeZone("UTC"));
			$lastactive->setTimestamp(time()); 
			$newuser->lastactive = $lastactive->format("Y-m-d H:i:s");
			$newuser->save();
			$_SESSION['admin_id'] = $newuser->usergroup <= 2 ? md5(md5($newuser->userid).md5($newuser->authkey)):NULL;
			$_SESSION['user_id'] = md5(md5($newuser->userid).md5($newuser->authkey));
			return true;
			break;
			
			default:
			return false;
		}
	}
    //Log the user out. Destroy the session variables.
	public function logout()
	{
		unset($_SESSION['user_id']);
		unset($_SESSION['admin_id']);
		unset($_SESSION['user_data']);
		session_destroy();
		session_start();
		session_regenerate_id();
	}

	//udpate user metadata to lates version of object from constants 
	private function updateMetaData(array $updateTo,array $updateFrom){
		
		foreach($updateTo as $key => &$value){
	
			switch(is_array($value)){
				case true:
				$value = Functions::updateTo($value,"$key",$updateFrom);	
				break;
				
				default:
				$value = array_key_exists($key,$updateFrom) ? $updateFrom[$key] :$value;
			}
		}
		return $updateTo;
	}

    public function get($id){
		
		return new User($this->db->select('tbl_user', "MD5(CONCAT(MD5(fUserID),MD5(fAuthKey))) = '$id' AND fStatus<>0"));
	}
}
?>