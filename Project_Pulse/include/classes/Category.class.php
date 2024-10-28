<?php
//Category.class.php

class Category {
	public $categoryid;
	public $categoryname;
	public $listorder;
	public $status;
	public $created;
	public $updated;
	public $uid;

	//Constructor is called whenever a new object is created.
	//Takes an associative array with the DB row as an argument.
	function __construct($data) {
		$this->categoryid = isset($data['fCategoryID']) ? $data['fCategoryID'] : NULL;
		$this->categoryname = isset($data['fCategoryName']) ? $data['fCategoryName'] : NULL;
		$this->listorder = isset($data['fListOrder']) ? $data['fListOrder'] : NULL;
		$this->status = isset($data['fStatus']) ? $data['fStatus'] : NULL;
		$this->created = isset($data['fCreated']) ? $data['fCreated'] : NULL;
		$this->updated = isset($data['fUpdated']) ? $data['fUpdated'] : NULL;
		$this->uid = isset($data['fUID']) ? $data['fUID'] : NULL;	
		
	}

	public function save($new = false) {
		//create a new database object.
		$db = new MySQLDB();

		$data = array(
			'fCategoryName' => !empty($this->categoryname) ? "'$this->categoryname'" : 'NULL',
			'fListOrder' => !empty($this->listorder) ? "'$this->listorder'" : 0,
			'fStatus' => !empty($this->status) ? "'$this->status'" : 0,
			'fUID' => !empty($this->uid) ? "'$this->uid'" : 'NULL'
		);

		$timestamp = new DateTime("now",new DateTimeZone("UTC"));
		$timestamp->setTimestamp(time());

		//switch between insert/update
		switch($new){
			case true: //insert case
			$this->created = $timestamp->format("Y-m-d H:i:s");
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fCreated'] = "'$this->created'";
			$data['fUpdated'] = "'$this->updated'";
			//insert record into database
			$result = $db->insert($data, 'tbl_setup_category');
			$this->categoryid = is_numeric($result) ? $result : $this->categoryid;
			break;
			
			default:
			$this->updated = $timestamp->format("Y-m-d H:i:s");
			$data['fUpdated'] = "'$this->updated'";
			//update the row in the database
			$result = $db->update($data, 'tbl_setup_category', 'fCategoryID = '.$this->categoryid);
			break;	
		}
		return is_numeric($result) ? true : $result;
	}

}
?>
