<?php
//Functions.class.php

class Functions{
	const PARAMS_LIST = array(
		'MySQL_DB_NAME',
		'MySQL_DB_USER',
		'MySQL_DB_PASS',
		'MySQL_DB_HOST',
		'HASH_SALT',
		'SITE_URL',
		'ABS_PATH',
		'DOMAIN_NAME',
		'TIME_ZONE',
		'ADMIN_PHONE',
		'ADMIN_EMAIL',
		'WEBMASTER_PHONE',
		'WEBMASTER_EMAIL',
		'SUPPORT_PHONE',
		'SUPPORT_EMAIL',
		'SMTP_HOST',
		'SMTP_PORT',
		'SMTP_USERNAME',
		'SMTP_PASSWORD',
		'AUTO_LOGIN',
		'AUTO_LOGIN_ID',
		'SMS_ALERT',
		'SMS_MODE',
		'PUSH_ALERT',
		'PUSH_MODE',
		'EMAIL_ALERT',
		'EMAIL_MODE',
	);
	
	private static $debug = false;
	
	/*
	* Constructor
	*/
	function __construct(){}

	/*
	* Set debug option
	*
	* @access public
	* @param string $debug to true/false
	* @return nothing
	*/
	public static function setDebug($debug=false){self::$debug = $debug;}


	/*
	* Get the Enviroment Constants from parameters file
	*
	* @access public
	* @param string $path path/filename of parameter file
	* @param aray $params_list list of parameters to be defined
	* @return associative array on success, false on error
	*/
	public static function getParams($params_list,$path=''){
		
		//check if file exist on specified path
		if(empty($path)){
			self::$debug ? die('Missing path parameter') : die;
		}
		elseif(empty($params_list)){
			self::$debug ? die('Parameters not defined') : die;
		}
		elseif(($handle = @fopen($path, "r")) === false){
			self::$debug ? die('Parameter file not found') : die;
		}
		
		//read the file
		while (feof($handle) === false) { 
			$param = fgetcsv($handle, 512);
			switch(true){

				case empty($param[0]) && empty($param[1]) === false:
				$params['InvalidParameters'][] = array('Name' => 'undefined', 'Value' => $param[1]);	
				break;
				
				case empty($param[0]) === false && in_array($param[0], $params_list) === false:
				$params['InvalidParameters'][] = array('Name' => $param[0], 'Value' => 'unknown');
				break;
				
				case empty($param[0]) === false && isset($param[1]) === false:
				$params['InvalidParameters'][] = array('Name' => $param[0], 'Value' => 'undefined');
				break;	

				case empty($param[0]) === false && isset($param[1]):
				$params['Parameters'][] = array('Name' => $param[0], 'Value' => $param[1]);
				break;
			}
		}
		fclose($handle);
		return empty($params) ? false : $params;
	}

	/*
	* Set the Enviroment Constants from parameters array
	*
	* @access public
	* @param string $path path/filename of parameter file
	* @return true, die on error
	*/
	public static function setParams($path=''){

		$const = new ReflectionClass(__CLASS__);
		$params_list = $const->getConstant('PARAMS_LIST');

		$params = self::getParams($params_list,$path);

		//validate parameter array
		if(empty($params['Parameters'])){
			self::$debug ? die('Missing configuration parameters') : die;
		}
		elseif(count($params_list) != count($params['Parameters'])){
			self::$debug ? die('Parameters count mismatch') : die;
		}	
		elseif(empty($params['InvalidParameters']) === false){
			self::$debug ? die('Invalid configuration parameters') : die;
		}
		
		//parse parameter array and define constants
		foreach($params['Parameters'] as $param){
			switch($param['Name']){
				case 'AUTO_LOGIN':
				case 'SMS_ALERT':
				case 'PUSH_ALERT':
				case 'EMAIL_ALERT':
				define($param['Name'],filter_var($param['Value'],FILTER_VALIDATE_BOOLEAN));
				break;
				
				default:
				define($param['Name'],$param['Value']);
			}
		}
		return true;
	}

	/*
	* Check user password for complexity requirements
	*
	* @access public
	* @param string $password  e.g '12345678'
	* @param string regex (?=(.*[a-z]){3,}) must contain minimum 3 small alphabets
	* @param string regex (?=(.*[A-Z]){2,}) must contain minimum 2 capital alphabets
	* @param string regex (?=(.*[0-9]){2,}) must contain minimum 2 numerals
	* @param string regex (?=(.*[!@#$%^&*()\-__+.]){1,}) must contain atleast one specials character from specified characters
	* @param string regex .{8,} password must consist of minimum of 8 characters
	* @return true if password matched criteria else return false
	*/
	public static function password_validate($password){
		
		return preg_match('/^(?=(.*[a-z]){3,})(?=(.*[A-Z]){2,})(?=(.*[0-9]){2,})(?=(.*[!@#$%^&*()\-_+.]){1,}).{8,}$/',$password) ? true : false;
	
	}

	/*
	* Check generate and returns string of letters for given lenth
	*
	* @access public
	* @param string $length  e.g 18
	* @return random string for given length by default is 12
	*/

	public static function getRandomString($length='') {
		$length = $length == '' ? 12 : $length;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#^&*?';
		$string = '';    
	
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters)-1)];
		}
	
		return $string;
	}
    
	/*
	* Return number of words from input string equal to input length
    * Adds the mark at the returned string if count of words in string exceed length
	*
	* @access public
	* @param string $str  e.g "Hello World! Who Killed Kenny"
	* @param string $length  e.g 18
	* @param string $trail  e.g "..cont" default is "..."
	* @return word from input string equal to input length defualt is 5
	*/
    public static function getShortWords($str='',$length=5,$trail="..."){
		
		$result = '';
		if(!empty($str)){

			$word_list = str_word_count($str, 1,',0123456789');
			
			if(count($word_list) > $length){

				$result = implode(" ",array_slice($word_list, 0, $length));
				$result .= " $trail";	

			}else{$result = $str;}
		}
		
		return $result;	
	}

	/*
	* Return number of character from input string equal to input length
    * Adds the mark at the returned string if count of words in string exceed length
	*
	* @access public
	* @param string $str  e.g "Hello World! Who Killed Kenny"
	* @param string $length  e.g 18
	* @param string $trail  e.g "..cont" default is "..."
	* @return word from input string equal to input length defualt is 5
	*/
    public static function getShortChars($str='',$length=5,$trail=' ...'){
		
		$result = '';
		if(!empty($str)){

			//$str_length = strlen($str);
			
			if(strlen($str) > $length){

				$result = substr($str, 0, $length).$trail;

			}else{$result = $str;}
		}
		
		return $result;	
	}

	/*
	* Generate HTML for input element with innerHTML for the value from specified column from associative input array
    * Optionally element can be ecnlosed by specifying  before and after tags.
	*
	* @access public
	* @param string $array  associative array e.g array(array('id' => 1, 'name' => 'Jade Vela'), array('id' => 2, 'name' => 'Willam Shane'))
	* @param string $column  column key form array input e.g 'name'
	* @param string $element tag/element e.g "li" default is "div"
	* @param string $class  any css class name e.g "blue" default is ''
	* @param string $style  element style information e.g "display:flex" default is ''
	* @param string $before opening tag/element name e.g "<ul>" default is ''
	* @param string $after  closing tag/element e.g "</ul>" default is ''
	* @return word from input string equal to input length defualt is 5
	*/

    public static function getHTML($array=array(),$column='',$element='div',$class='', $style='',$before='',$after=''){
		
		$result = '';
		$success = true;

		if(empty($array)){
			$success = false;
			$result = self::$debug ? "Missing/empty array parameter" : $result;
		}
		elseif(empty($array[0]) || !is_array($array[0])){
			$success = false;
			$result = self::$debug ? "Non multi array parameter" : $result;
		}
		elseif(empty($column)){
			$success = false;
			$result = self::$debug ? "Missin/empty column parameter" : $result;
		}
		elseif(empty($array[0][$column])){
			$success = false;
			$result = self::$debug ? "Invalid column parameter" : $result;
		}
		
		if($success){
			$result = $before;
			foreach($array as $item){
				$result .='<'.$element.' class="'.$class.'" style="'.$style.'">'.$item[$column].'</'.$element.'>';	
			}	
			$result .= $after;
		
		}
		
		return $result;
		
	}

	/*
	* Search for needle in column within array 
	*
	* @access public
	* @param string $array  associative array e.g 
	* array(array('id' => 1, 'name' => 'Jade Vela'), array('id' => 2, 'name' => 'Willam Shane'))
    *
	* @param string $column  column key within array input e.g 'name'
	* @param string $needle value to look for e.g 'Jade Vela' default is ''
	* @return first array with key in which the needle was found for given column false otherwise
	*/
	public static function getObjectData($array=array(),$column='',$needle=0){
		$result = false;
		
		$success = true;
		
		if(is_array($array) === false){
			$success = false;
			$result = self::$debug ? 'Expecting data array' : $result;
		}
		elseif(count($array) == 0){
			$success = false;
			$result = self::$debug ? "input is empty array" : $result;
		}
		elseif(empty($column)){
			$success = false;
			$result = self::$debug ? 'Expecting column name' : $result;
		}
		
		if($success){
			foreach($array as $key=>$item){
				if(is_array($item) === false){
					$success = false;
					$result = self::$debug ? "Expecting array at index $key" : $result;
					break;
				}
			}
		}
		
		if($success){
			$key = array_search($needle, array_column($array, $column));
			$result = is_numeric($key) ? $array[$key]:false;
		}
		
		return $result;

	}
	
	/*
	* Return arry of values for given column 
	*
	* @access public
	* @param string $array  associative array e.g 
	* array(array('id' => 1, 'name' => 'Jade Vela'), array('id' => 2, 'name' => 'Willam Shane'))
    *
	* @param string $column  column key within array input e.g 'name'
	* @return first array of valued for given column null otherwise
	*/
	public static function createArray($array=array(),$column=''){
		$result = null;
		
		$success = true;
		
		if(is_array($array) === false){
			$success = false;
			$result = self::$debug ? 'Expecting data array' : $result;
		}
		elseif(count($array) == 0){
			$success = false;
			$result = self::$debug ? "input is empty array" : $result;
		}
		elseif(empty($column)){
			$success = false;
			$result = self::$debug ? 'Expecting column name' : $result;
		}
		
		if($success){
			foreach($array as $key=>$item){
				if(is_array($item) === false){
					$success = false;
					$result = self::$debug ? "Expecting array at index $key" : $result;
					break;
				}else{
					$result[] = $item[$column];
				}
			}
		}
		
		return is_null($result) === false ? array_unique($result): NULL;

	}
	
	public static function getString($array=[],$column='',$split=','){
		$result = null;
		$values = self::createArray($array,$column);
		$result = implode(',',$values) ;
		return $result;
	}
	/*
	* Search for value in associative array in given column
	*
	* @access public
	* @param string $array array where array values must be associative arrays 
	* @param string $needle search value
	* @param string $column search column associative array
	* @return true if needle is found in column of input array false otherwise
	* @Note return false if input is not array of associative array
	*/
	public static function searchArray($array, $needle, $column){
		$result = false;
		$key = array_search($needle, array_column($array,$column));
		$result = is_numeric($key) ? true:false;
		return $result;
	}
	
	
	/*
	* Core function to prepare update json objects for udpates
	*
	* @access public
	* @param string $updateTo array where array values must be associative arrays 
	* @param string $updateFrom array where array values must be associative arrays
	* @return updated updateTo array
	*/
	public static function updateMetaData(array $updateTo,array $updateFrom){
		
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
	
	/*
	* Update values of updateTo from contextual valus from updateFrom
	* Falls back to original values if contextual values not found
	*
	* @access public
	* @param string $updateTo array where array values must be associative arrays 
	* @param string $path root path of the value
	* @param string $updateFrom array where array values must be associative arrays
	* @return updated updateTo array
	* @Note return false if input is not array of associative array
	*/
	public static function updateTo(array $updateTo, string $path, array $updateFrom){
		
		if(is_array($updateTo) === false){
			self::$debug ? die('Expects array input provided null. (code:101001)') : die;
		}
		elseif(is_array($updateFrom) === false){
			self::$debug ? die('Expects array input provided null. (code:101002)') : die;
		}
		
		$result = array();
		foreach($updateTo as $key => $value_2){
			switch(is_array($value_2) === true){
				case true:
				$result[$key] = self::updateTo($value_2, "$path,$key",$updateFrom);
				break;
				
				default:
				$value_1 = self::getNestedVar($updateFrom,"$path,$key");
				$result[$key] = $value_1 !== 'not__found' ? $value_1 : $value_2;
			}
		}
		return $result;
	}
	
    /*
	* Return values from context if exist on given name path
	*
	* @access public
	* @param string $context array where array values must be associative arrays 
	* @param string $name string containing path keys separated with separator
	* @param string $separate sting symbol used in $name string to seprate path keys deulat is ","
	* @return value of key from context
	* @Note return false if path does not exist in context
	*/
	public static function getNestedVar($context, $name, $seprator = ',') {

		if(is_array($context) === false){
			self::$debug ? die('Expects array input provided null. (code:101001)') : die;
		}
		elseif(empty($name) === true){
			self::$debug ? die('Expects string of keys provided null. (code:101002)') : die;
		}

		$pieces = explode($seprator, $name);
		foreach ($pieces as $piece) {
			if (is_array($context) === false || array_key_exists($piece, $context) === false) {
				// error occurred
				return 'not__found';
			}
			$context = $context[$piece];
		}
		return $context;
	}
	    
     /*
	* Return last seen string based on the number of input minutes
	*
	* @access public
	* @param integer $minutes any number of minutes default is NULL 
	* @return result as string default is 'Online'
	*/
   public static function getLastSeen($minutes=NULL){
        
        $result = '';
        
        switch(true){
            case is_null($minutes):
            $result = 'Last Seen: Never';
            break;

            case $minutes >= 518400:
            $result = "Last Seen: ".floor($minutes/518400)." year ago";
            break;

            case $minutes >= 43200:
            $result =  "Last Seen: ".floor($minutes/43200)." month ago";
            break;

            case $minutes >= 1440:
            $result =  "Last Seen: ".floor($minutes/1440)." day ago";
            break;

            case $minutes >= 60:
            $result =  "Last Seen: ".floor($minutes/60)." hour ago";
            break;

            case $minutes >= 1:
            $result = "Last Seen: $minutes min ago";
            break;

            default:
            $result = 'Online';
        } 
        
        return $result;
    }

}
?>