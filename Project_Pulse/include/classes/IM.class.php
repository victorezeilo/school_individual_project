<?php
//IM.class.php

class IM {
	public $imid;
	public $from;
	public $to;
	public $message;
    public $fromstatus;
	public $tostatus;
	public $created;
	public $updated;
	public $uid;

	//Constructor is called whenever a new object is created.
	//Takes an associative array with the DB row as an argument.
	function __construct($data) {
		$this->imid = isset($data['fIMID']) ? $data['fIMID'] : NULL;
		$this->from = isset($data['fFrom']) ? $data['fFrom'] : NULL;
		$this->to = isset($data['fTo']) ? $data['fTo'] : NULL;
		$this->message = isset($data['fMessage']) ? $data['fMessage'] : NULL;
		$this->fromstatus = isset($data['fFromStatus']) ? $data['fFromStatus'] : NULL;
		$this->tostatus = isset($data['fToStatus']) ? $data['fToStatus'] : NULL;
		$this->created = isset($data['fCreated']) ? $data['fCreated'] : NULL;
		$this->updated = isset($data['fUpdated']) ? $data['fUpdated'] : NULL;
		$this->uid = isset($data['fUID']) ? $data['fUID'] : NULL;	
		
	}

	public function save($new = false) {
		//create a new database object.
		$db = new MySQLDB();
		
		$data = array(
			'fFrom' => !empty($this->from) ? "'$this->from'" : 'NULL',
			'fTo' => !empty($this->to) ? "'$this->to'" : 'NULL',
			'fMessage' => !empty($this->message) ? "'$this->message'" : 'NULL',
			'fFromStatus' => !empty($this->fromstatus) ? "'$this->fromstatus'" : 0,
			'fToStatus' => !empty($this->tostatus) ? "'$this->tostatus'" : 0,
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
			$result = $db->insert($data, 'tbl_im');
			$this->imid = is_numeric($result) ? $result : $this->imid;
			break;
			
			default:
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fUpdated'] = "'$this->updated'";
			//update the row in the database
			$result = $db->update($data, 'tbl_im', 'fIMID = '.$this->imid);
			break;	
		}
		return is_numeric($result) ? true:$result;
	}
}
?>
