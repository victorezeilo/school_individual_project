<?php
//User.class.php

class User {
	public $userid;
	public $usergroup;
	public $adminid;
	public $veteranid;
	public $email;
	public $password;
	public $title;
	public $firstname;
	public $lastname;
	public $screenname;
	public $gender;
  public $dob;
	public $avatar;
	public $about;
	public $mobile;
	public $phone;
	public $fax;
	public $street1;
	public $street2;
	public $cityname;
	public $statecode;
	public $zip;
	public $language;
	public $company;
	public $timezone;
	public $authkey;
	public $metadata;
	public $status;
	public $loginip;
	public $lastlogin;
	public $lastactive;
	public $created;
	public $updated;
	public $uid;

	//Constructor is called whenever a new object is created.
	//Takes an associative array with the DB row as an argument.
	function __construct($data) {
		$this->userid = isset($data['fUserID']) ? $data['fUserID'] : NULL;
		$this->usergroup = isset($data['fUserGroup']) ? $data['fUserGroup'] : NULL;
		$this->adminid = isset($data['fAdminID']) ? $data['fAdminID'] : NULL;
		$this->veteranid = isset($data['fVeteranID']) ? $data['fVeteranID'] : NULL;
		$this->email = isset($data['fEmail']) ? $data['fEmail'] : NULL;
		$this->password = isset($data['fPassword']) ? $data['fPassword'] : NULL;
		$this->title = isset($data['fTitle']) ? $data['fTitle'] : NULL;
		$this->firstname = isset($data['fFirstName']) ? $data['fFirstName'] : NULL;
		$this->lastname = isset($data['fLastName']) ? $data['fLastName'] : NULL;
		$this->screenname = isset($data['fScreenName']) ? $data['fAdminID'] : NULL;
		$this->gender = isset($data['fGender']) ? $data['fGender'] : NULL;
		$this->dob = isset($data['fDOB']) ? $data['fDOB'] : NULL;
		$this->avatar = isset($data['fAvatar']) ? $data['fAvatar'] : NULL;
		$this->about = isset($data['fAbout']) ? $data['fAbout'] : NULL;
		$this->mobile = isset($data['fMobile']) ? $data['fMobile'] : NULL;
		$this->phone = isset($data['fPhone']) ? $data['fPhone'] : NULL;
		$this->fax = isset($data['fFax']) ? $data['fFax'] : NULL;
		$this->street1 = isset($data['fStreet1']) ? $data['fStreet1'] : NULL;
		$this->street2 = isset($data['fStreet2']) ? $data['fStreet2'] : NULL;
		$this->cityname = isset($data['fCityName']) ? $data['fCityName'] : NULL;
		$this->statecode = isset($data['fStateCode']) ? $data['fStateCode'] : NULL;
		$this->zip = isset($data['fZIP']) ? $data['fZIP'] : NULL;
		$this->language = isset($data['fLanguage']) ? $data['fLanguage'] : NULL;
		$this->company = isset($data['fCompany']) ? $data['fCompany'] : NULL;
		$this->timezone = isset($data['fTimeZone']) ? $data['fTimeZone'] : NULL;
		$this->authkey = isset($data['fAuthKey']) ? $data['fAuthKey'] : NULL;
		$this->metadata = isset($data['fMetaData']) ? $data['fMetaData'] : NULL;
		$this->status = isset($data['fStatus']) ? $data['fStatus'] : NULL;
		$this->loginip = isset($data['fLoginIP']) ? $data['fLoginIP'] : NULL;
		$this->lastlogin = isset($data['fLastLogin']) ? $data['fLastLogin'] : NULL;
		$this->lastactive = isset($data['fLastActive']) ? $data['fLastActive'] : NULL;
		$this->created = isset($data['fCreated']) ? $data['fCreated'] : NULL;
		$this->updated = isset($data['fUpdated']) ? $data['fUpdated'] : NULL;
		$this->uid = isset($data['fUID']) ? $data['fUID'] : NULL;	
	}

	public function save($new = false) {
		//create a new database object.
		$db = new MySQLDB();
		
		$data = array(
			'fUserGroup' => !empty($this->usergroup) ? "'$this->usergroup'" : 'NULL',
			'fAdminID' => !empty($this->adminid) ? "'$this->adminid'" : 'NULL',
			'fVeteranID' => !empty($this->veteranid) ? "'$this->veteranid'" : 'NULL',
			'fEmail' => !empty($this->email) ? "'$this->email'" : 'NULL',
			'fPassword' => !empty($this->password) ? "'$this->password'" : 'NULL',
			'fTitle' => !empty($this->title) ? "'$this->title'" : 'NULL',
			'fFirstName' => !empty($this->firstname) ? "'$this->firstname'" : 'NULL',
			'fLastName' => !empty($this->lastname) ? "'$this->lastname'" : 'NULL',
			'fScreenName' => !empty($this->screenname) ? "'$this->screenname'" : 'NULL',
			'fGender' => !empty($this->gender) ? "'$this->gender'" : 'NULL',
			'fDOB' => !empty($this->dob) ? "'$this->dob'" : 'NULL',
			'fAvatar' => !empty($this->avatar) ? "'$this->avatar'" : 'NULL',
			'fAbout' => !empty($this->about) ? "'$this->about'" : 'NULL',
			'fMobile' => !empty($this->mobile) ? "'$this->mobile'" : 'NULL',
			'fPhone' => !empty($this->phone) ? "'$this->phone'" : 'NULL',
			'fFax' => !empty($this->fax) ? "'$this->fax'" : 'NULL',
			'fStreet1' => !empty($this->street1) ? "'$this->street1'" : 'NULL',
			'fStreet2' => !empty($this->street2) ? "'$this->street2'" : 'NULL',
			'fCityName' => !empty($this->cityname) ? "'$this->cityname'" : 'NULL',
			'fStateCode' => !empty($this->statecode) ? "'$this->statecode'" : 'NULL',
			'fZIP' => !empty($this->zip) ? "'$this->zip'" : 'NULL',
			'fLanguage' => !empty($this->language) ? "'$this->language'" : 'NULL',
			'fCompany' => !empty($this->company) ? "'$this->company'" : 'NULL',
			'fTimeZone' => !empty($this->timezone) ? "'$this->timezone'" : 'NULL',
			'fAuthKey' => !empty($this->authkey) ? "'$this->authkey'" : 'NULL',
			'fMetaData' => !empty($this->metadata) ? "'$this->metadata'" : 'NULL',
			'fStatus' => !empty($this->status) ? "'$this->status'" : 0,
			'fLoginIP' => !empty($this->loginip) ? "'$this->loginip'" : 'NULL',
			'fLastLogin' => !empty($this->lastlogin) ? "'$this->lastlogin'" : 'NULL',
			'fLastActive' => !empty($this->lastactive) ? "'$this->lastactive'" : 'NULL',
			'fUID' => !empty($this->uid) ? "'$this->uid'" : 'NULL'
		);
		
		$timestamp = new DateTime("now",new DateTimeZone("UTC"));
		$timestamp->setTimestamp(time());
		
		switch($new){
			case true: //insert case
			$this->created = $timestamp->format("Y-m-d H:i:s");
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fCreated'] = "'$this->created'";
			$data['fUpdated'] = "'$this->updated'";
			//insert record into database
			$result = $db->insert($data, 'tbl_user');
			$this->userid = is_numeric($result) ? $result : $this->userid;
			break;
			
			default:
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fUpdated'] = "'$this->updated'";
			//update the row in the database
			$result = $db->update($data, 'tbl_user', 'fUserID = '.$this->userid);
			break;	
		}
		return is_numeric($result) ? true : $result;
	}

}
?>
