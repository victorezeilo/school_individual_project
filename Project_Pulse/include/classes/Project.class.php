<?php
//Project.class.php
class Project {
	public $projectid;
	public $ownerid;
	public $owner;
	public $title;
	public $description;
	public $managerid;
	public $manager;
	public $startdate;
	public $enddate;
	public $report;
	public $specific;
	public $metadata;
	public $status;
	public $created;
	public $updated;
	public $uid;
	
	//Constructor is called whenever a new object is created.
	//Takes an associative array with the DB row as an argument.
	function __construct($data=[]) {
		$this->projectid = isset($data['fProjectID']) ? $data['fProjectID'] : NULL;
		$this->ownerid = isset($data['fOwnerID']) ? $data['fOwnerID'] : NULL;
		$this->owner = isset($data['fOwner']) ? $data['fOwner'] : NULL;
		$this->title = isset($data['fTitle']) ? $data['fTitle'] : NULL;
		$this->description = isset($data['fDescription']) ? $data['fDescription'] : NULL;
		$this->managerid = isset($data['fManagerID']) ? $data['fManagerID'] : NULL;
		$this->manager = isset($data['fManager']) ? $data['fManager'] : NULL;
		$this->startdate = isset($data['fStartDate']) ? $data['fStartDate'] : NULL;
		$this->enddate = isset($data['fEndDate']) ? $data['fEndDate'] : NULL;
		$this->report = isset($data['fReport']) ? $data['fReport'] : NULL;
		$this->specific = isset($data['fSpecific']) ? $data['fSpecific'] : NULL;
		$this->metadata = isset($data['fMetaData']) ? $data['fMetaData'] : NULL;
    $this->status = isset($data['fStatus']) ? $data['fStatus'] : NULL;
		$this->created = isset($data['fCreated']) ? $data['fCreated'] : NULL;
		$this->updated = isset($data['fUpdated']) ? $data['fUpdated'] : NULL;
		$this->uid = isset($data['fUID']) ? $data['fUID'] : NULL;	
	}

	public function save($new = false) {
		//create a new database object.
		$db = new MySQLDB();
		
		$data = array(
			'fOwnerID' => !empty($this->ownerid) ? "'$this->ownerid'" : 'NULL',
			'fOwner' => !empty($this->owner) ? "'$this->owner'" : 'NULL',
			'fTitle' => !empty($this->title) ? "'$this->title'" : 'NULL',
			'fDescription' => !empty($this->description) ? "'$this->description'" : 'NULL',
			'fManagerID' => !empty($this->managerid) ? "'$this->managerid'" : 'NULL',
			'fManager' => !empty($this->manager) ? "'$this->manager'" : 'NULL',
			'fStartDate' => !empty($this->startdate) ? "'$this->startdate'" : 'NULL',
			'fEndDate' => !empty($this->enddate) ? "'$this->enddate'" : 'NULL',
			'fReport' => !empty($this->report) ? "'$this->report'" : 'NULL',
			'fSpecific' => !empty($this->specific) ? "'$this->specific'" : 'NULL',
			'fMetaData' => !empty($this->metadata) ? "'$this->metadata'" : 'NULL',
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
			$result = $db->insert($data, 'tbl_project');
			$this->projectid = is_numeric($result) ? $result : $this->projectid;
			break;
			
			default:
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fUpdated'] = "'$this->updated'";
			//update the row in the database
			$result = $db->update($data, 'tbl_project', 'fProjectID = '.$this->projectid);
			break;	
		}
		return is_numeric($result) ? true:$result;
	}

}
?>
