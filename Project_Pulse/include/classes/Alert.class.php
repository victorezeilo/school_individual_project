<?php
//Alert.class.php
class Alert {
	public $alertid;
	public $campaignid;
	public $leadid;
	public $alerttype;
	public $to;
	public $alerttext;
	public $subject;
	public $trackid;
	public $sent;
	public $viewed;
	public $status;
	public $created;
	public $updated;
	public $uid;
	
	//Constructor is called whenever a new object is created.
	//Takes an associative array with the DB row as an argument.
	function __construct($data=[]) {
		$this->alertid = isset($data['fAlertID']) ? $data['fAlertID'] : NULL;
		$this->campaignid = isset($data['fCampaignID']) ? $data['fCampaignID'] : NULL;
		$this->leadid = isset($data['fLeadID']) ? $data['fLeadID'] : NULL;
		$this->alerttype = isset($data['fAlertType']) ? $data['fAlertType'] : NULL;
		$this->to = isset($data['fTo']) ? $data['fTo'] : NULL;
		$this->alerttext = isset($data['fAlertText']) ? $data['fAlertText'] : NULL;
		$this->subject = isset($data['fSubject']) ? $data['fSubject'] : NULL;
		$this->trackid = isset($data['fTrackID']) ? $data['fTrackID'] : NULL;
		$this->sent = isset($data['fSent']) ? $data['fSent'] : NULL;
		$this->viewed = isset($data['fViewed']) ? $data['fViewed'] : NULL;
    $this->status = isset($data['fStatus']) ? $data['fStatus'] : NULL;
		$this->created = isset($data['fCreated']) ? $data['fCreated'] : NULL;
		$this->updated = isset($data['fUpdated']) ? $data['fUpdated'] : NULL;
		$this->uid = isset($data['fUID']) ? $data['fUID'] : NULL;	
	}

	public function save($new = false) {
		//create a new database object.
		$db = new MySQLDB();
		
		$data = array(
			'fCampaignID' => !empty($this->campaignid) ? "'$this->campaignid'" : 'NULL',
			'fLeadID' => !empty($this->leadid) ? "'$this->leadid'" : 'NULL',
			'fAlertType' => !empty($this->alerttype) ? "'$this->alerttype'" : 'NULL',
			'fTo' => !empty($this->to) ? "'$this->to'" : 'NULL',
			'fAlertText' => !empty($this->alerttext) ? "'$this->alerttext'" : 'NULL',
			'fSubject' => !empty($this->subject) ? "'$this->subject'" : 'NULL',
			'fTrackID' => !empty($this->trackid) ? "'$this->trackid'" : 'NULL',
			'fSent' => !empty($this->sent) ? "'$this->sent'" : 'NULL',
			'fViewed' => !empty($this->viewed) ? "'$this->viewed'" : 'NULL',
			'fStatus' => !empty($this->status) ? "'$this->status'" : 0,
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
			$result = $db->insert($data, 'tbl_alert');
			$this->alertid = is_numeric($result) ? $result : $this->alertid;
			break;
			
			default:
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fUpdated'] = "'$this->updated'";
			//update the row in the database
			$result = $db->update($data, 'tbl_alert', 'fAlertID = '.$this->alertid);
			break;	
		}
		return is_numeric($result) ? true:$result;
	}

}
?>
