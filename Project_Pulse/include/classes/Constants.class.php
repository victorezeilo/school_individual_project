<?php
//Constants.class.php
class Constants{
	
	const PERIOD_LIST = array(
		array('id' => 1, 'name' => 'Day(s)'),
		array('id' => 2, 'name' => 'Month(s)'),
		array('id' => 3, 'name' => 'Year(s)')
	);

	const TRXTYPE_LIST = array(
		array('id' => 1, 'name' => 'Income'),
		array('id' => 2, 'name' => 'Expense'),
	);

	const PAYMODE_LIST = array(
		array('id' => 1, 'name' => 'Cash'),
		array('id' => 2, 'name' => 'Cheque'),
		array('id' => 3, 'name' => 'Online')
	);
	
	const SUBSCRIPTIONTYPE_LIST = array(
		array('id' => 1, 'name'	=> 'Free Trial'),
		array('id' => 2, 'name' => 'Free Subscrption'),
		array('id' => 3, 'name'	=> 'Paid Subscription')
	);

	const RECURRINGTYPE_LIST = array(
		array('id' => 1, 'name' => 'Continue'),
		array('id' => 2, 'name' => 'Period'),
		array('id' => 3, 'name' => 'Fixed'),
	);

	const ALERTTYPE_LIST = array(
		array('id' => 1, 'name' => 'SMS'),
		array('id' => 2, 'name'	=> 'EMAIL'),
	);

	const PAYGATEWAY_LIST = array(
		array('id' => 1, 'name' => 'Paypal', 'label1' => 'Paypal Client ID', 'label2' => 'Paypal Secret'),
		array('id' => 2, 'name'	=> 'Authorize.NET', 'label1' => 'API Login ID', 'label2' => 'Transaction Key'),
	);
	
	const METADATA = array(
		'user' => array(
				'setup' => array('profile' => false, 'contact' => false, 'schedule' => false, 'subscription' => false),
				'profile' => array('emailVerified' => false, 'mobileVerified' => false,'SNChecked' => false, 'newsAlert' => true, 'allowIM' => false, 'allowMsg' => false),
		),
	);

	public function __construct(){}
	
	public static function getValue($constant='',$search='',$column='',$return='',$debug=false){
		
		$column = $column  == '' ? 'id' : $column;
		$return = $return == '' ? 'name' : $return;
		
		$result = '';
		$success = true;
		
		$thisclass = new ReflectionClass(__CLASS__);
		$const = $thisclass->getConstant($constant);
	
		if(empty($constant)){
			$success = false;
			$result = $debug ? "Missing/empty constant parameter" : $result;
		}
		elseif(empty($const)){
			$success = false;
			$result =  $debug ? "Constant $constant not found" : $result;
		}
		elseif($search == ''){
			$success = false;
			$result =  $debug ? "Missing/empty column parameter" : $result;
		}
		
		if($success){
			
			$key = array_search($search, array_column($const, $column));
			switch(is_numeric($key)){
				case true:
				switch(!empty($const[$key][$return])){
					case true:
					$result = $const[$key][$return];
					break;
					
					default:
					$result =  $debug ? "Column not found" : $result;
					break;	
				}
				break;
				
				default:
				$result =  $debug ? "Key not found" : $result;
				break;
			}
		}
		
		return $result;
		
	}	

	public static function getString($constant='',$ids='',$column='',$symbol=",",$debug=false){
		
		$result = NULL;
		$symbol = empty($symbol) ? "," : $symbol;
		
		$thisclass = new ReflectionClass(__CLASS__);
		$const = $thisclass->getConstant($constant);

		$success = true;
		
		if(empty($constant)){
			$success = false;
			$result = $debug ? "Missing/empty constant parameter" : $result;
		}
		elseif(empty($const)){
			$success = false;
			$result =  $debug ? "Constant $constant not found" : $result;
		}
		elseif(empty($ids)){
			$success = false;
			$result = $debug ? "Missing/empty ids parameter" : $result;
		}
		elseif(empty($column)){
			$success = false;
			$result = $debug ? "Missing/empty column parameter" : $result;
		}

		if($success){
			$ids = explode(',',$ids);
			foreach($ids as $item){
				$key = array_search($item, array_column($const, 'id'));
				$output[] = $const[$key][$column];	
			}
			$result = implode($symbol,$output);
		}

		return $result;
	}

	public static function getArray($constant='',$ids='',$keycol='',$valuecol='',$debug=false){
		
		$result = NULL;
		$keycol = empty($keycol) ? "status" : $keycol;
		$valuecol = empty($valuecol) ? "name" : $valuecol;
		
		$thisclass = new ReflectionClass(__CLASS__);
		$const = $thisclass->getConstant($constant);

		$success = true;
		
		if(empty($constant)){
			$success = false;
			$result = $debug ? "Missing/empty constant parameter" : $result;
		}
		elseif(empty($const)){
			$success = false;
			$result =  $debug ? "Constant $constant not found" : $result;
		}
		elseif(empty($ids)){
			$success = false;
			$result = $debug ? "Missing/empty ids parameter" : $result;
		}
		elseif(empty($keycol)){
			$success = false;
			$result = $debug ? "Missing/empty key parameter" : $result;
		}
		elseif(empty($valuecol)){
			$success = false;
			$result = $debug ? "Missing/empty value parameter" : $result;
		}

		if($success){
			$ids = explode(',',$ids);
			foreach($ids as $item){
				$key = array_search($item, array_column($const, 'id'));
				$output[] = array($keycol => $const[$key][$keycol], $valuecol => $const[$key][$valuecol]);	
			}
			$result = $output;
		}

		return $result;
	}
	
	public static function getValueCI($constant='',$ids='',$needle='',$column='',$return='',$debug=false){
		
		$column = $column  == '' ? 'name' : $column;
		$return = $return == '' ? 'id' : $return;
		
		$result = '';
		$success = true;
		
		//$thisclass = new ReflectionClass(__CLASS__);
		//$const = $thisclass->getConstant($constant);
	
		if(empty($constant)){
			$success = false;
			$result = $debug ? "Missing/empty constant parameter" : $result;
		}
		/*
		elseif(empty($const)){
			$success = false;
			$result =  $debug ? "Constant $constant not found" : $result;
		}
		*/
		elseif(empty($ids)){
			$success = false;
			$result = $debug ? "Missing/empty ids parameter" : $result;
		}
		elseif($needle == ''){
			$success = false;
			$result =  $debug ? "Missing/empty column parameter" : $result;
		}
		
		if($success){
			
			$str = self::getString($constant,$ids,$column);
			
			$array = explode(',',$str);
			
			$key = array_search(strtolower($needle), array_map('strtolower', $array));
			
			switch(is_numeric($key)){
				case true:
				switch(!empty($array[$key])){
					case true:
					$result = self::getValue($constant,$array[$key],$column,$return);
					break;
					
					default:
					$result =  $debug ? "Column not found" : $result;
					break;	
				}
				break;
				
				default:
				$result =  $debug ? "Key not found" : $result;
				break;
			}
		}
		
		return $result;
		
	}	
}
?>