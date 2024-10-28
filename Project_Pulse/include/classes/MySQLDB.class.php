<?php
//DB.class.php
// @ next version for large scale application will be
// use of prepared statements. For small and medium scale web apps
// this class can sufficiently serve the purpose

//its time to update this class and use prepared statements ;)
class MySQLDB {

  protected $db_host;
	protected $db_name;
	protected $db_user;
	protected $db_pass;
  private static $connection;

    public function __construct($db_host=NULL,$db_name=NULL,$db_user=NULL,$db_pass=NULL){
		$this->db_host = $db_host;
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
	}
	
	//public functon  to use outside of class
	//CURD operation does not required use of myqli out side of class
	public function connect(){
		
		if(self::$connection === NULL){
			self::$connection = @new MySQLi($this->db_host,$this->db_user,$this->db_pass,$this->db_name);
			if(self::$connection->connect_error) die("Connection failed: " .self::$connection->connect_error);
			self::$connection->query("SET NAMES UTF8");
		}
		
		return self::$connection;
	}
	

	//executes any valid sql query and return the result.
	public function execQuery($sql){
		//echo $sql;
		$mysqli = $this->connect();
		$result = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		return $result;
	}


	//takes table name, array of fields names and engine type as input and creates a temporary table,
	public function createTmpTable($table, $data, $primary=NULL, $engine="MEMORY", $options=NULL){

		if(is_array($data)) {
			$sql = "CREATE TEMPORARY TABLE $table (".implode(", ",$data).") $primary ENGINE=".strtoupper($engine)." $options;";
			//echo $sql;
			$mysqli = $this->connect();
			$result = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
			return $result;
		} else {return false;}
	}

	//takes a mysql row set and returns an associative array, where the keys
	//in the array are the column names in the row set. If singleRow is set to
	//true, then it will return a single row instead of an array of rows.
	private function processRowSet($rowSet, $singleRow=false)
	{

		$resultArray = array();
		
		while($row = mysqli_fetch_assoc($rowSet))

		{
			array_push($resultArray, $row);
		}

		if($singleRow === true)
			return $resultArray[0];
		return $resultArray;
	}

	//Select rows from the database based on query.
	//returns a full row or rows from $table using $where as the where clause.
	//return value is an associative array with column names as keys.
	public function QuerySelect($query, $where='', $array=true) {

		$sql = empty($where) || $where == '' ? "$query" : "$query WHERE $where";
		//echo $sql."<br /><br />";
		$mysqli = $this->connect();
		$result = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		if(!$result == false){
			if(mysqli_num_rows($result) == 1)
				return $this->processRowSet($result, $array);
			return $this->processRowSet($result);
		} 
		return $result;
	}

	//Select rows from the database.
	//returns a full row or rows from $table using $where as the where clause.
	//return value is an associative array with column names as keys.
	public function select($table, $where='', $array=true) {

		$sql = empty($where) || $where == '' ? "SELECT * FROM $table" : "SELECT * FROM $table WHERE $where";
		//echo $sql;
		$mysqli = $this->connect();
		$result = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		if(!$result == false){
			if(mysqli_num_rows($result) == 1)
				return $this->processRowSet($result, $array);
			return $this->processRowSet($result);
		} 
		return $result;
	}

	//Updates a current row in the database.
	//takes an array of data, where the keys in the array are the column names
	//and the values are the data that will be inserted into those columns.
	//$table is the name of the table and $where is the sql where clause.
	public function update($data, $table, $where) {

		//this function was updated by zubair
		//older version use to send update query for each feild
		//separately that version is found in old website like iqbalmkhan.web.pk
		foreach ($data as $column => $value) {
			$rs[] = $column."=".$value; 
		}
		$data = implode(", ",$rs);
		$sql = "UPDATE $table SET $data WHERE $where";
		//echo $sql . "<br />";
		$mysqli = $this->connect();
		$result  = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		return $result;
	}

	//Inserts a new row into the database.
	//takes an array of data, where the keys in the array are the column names
	//and the values are the data that will be inserted into those columns.
	//$table is the name of the table.
	public function insert($data, $table) {

		$columns = implode(", ", array_keys($data));
		$values = implode(", ", $data);
		$sql = "INSERT INTO $table ($columns) VALUES ($values)";
		//echo $sql.'<br/><br/>';
		$mysqli = $this->connect();
		$result  = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		return $result ?  $mysqli->insert_id : $result;
	}

	//Delete record by where clause.
	public function delete($table, $where) {

		$sql = "DELETE FROM $table WHERE $where";
		//echo $sql;
		$mysqli = $this->connect();
		$result = $mysqli->query($sql); // or die("Error - SQLSTATE\n".$mysqli->error);
		return $result;
	}
}

?>