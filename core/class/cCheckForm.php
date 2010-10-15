<?php
class cCheckForm {
	
	private $aCheckingData = array();
	public $bIsError = false;
	public static $aVals = array(
		"aTrue" => 	array('true', 'True', 'TRUE', 'yes', 'Yes', 'y', 'Y', '1', 'on', 'On', 'ON', 1),
		"aFalse" => array('false', 'False', 'FALSE', 'no', 'No', 'n', 'N', '0', 'off', 'Off', 'OFF', 0, null),
		"aCheckFce" =>	array('is_string', 'is_bool', 'is_numeric', 'is_int', 'is_float')
				);
	private $logs;
				
	function __construct() {
		$this->logs = new cLogs(get_class());
		//$this->logs->on();
	}
	
	/**
	 * check if is object valid; if is set element, check validation of that element
	 * otherwise global validation of all elements 
	 * @param unknown_type $element
	 */
	public function isValid($element=null) {
		if($element) {
			return $this->aCheckingData[$nameOfField]['valid'];
		}
		
		return (true==$this->bIsError) ? false : true;
	}
	
	/**
	 * if exists errors for the data that were checked, returns assoc. array of err. messages
	 * otherwise returns empty array
	 * @return array
	 */
	public function getErrors() {
		$ret = array();
		foreach($this->aCheckingData as $k=>$data) {
			if(count($data['errors']['msg']) || isset($data['errors']['sent'])) {
				$ret[$k] = $data['errors'];		
			}
		}
		return $ret;	
	}
	
	/**
	 * 
	 * @param string $nameOfField - name of element to be stored in this object
	 * @param mixed $pattern - on this pattern the data is checked; string or array(string, string,...)
	 * @param mixed $errors[optional][default null] - msg to be set as an error; string or array(string, string,...) - each element of array recording to elem. in pattern
	 * @param mixed $data[optional][default null] - data to be checked; if not set, the $_POST/GET[$nameOfField] will be checked
	 * @param string $method[optional][default 'POST'] - method by which the data of $nameOfField was sent
	 * @return boolean 
	 */
	public function check($nameOfField, $patterns, $errors=null, $data=null, $method='POST') {
		if(!isset($this->aCheckingData[$nameOfField])) {
			$this->aCheckingData[$nameOfField] = array(	'valid'=>true,
														'errors'=> array('msg'=>array()) );
		}
		
		/// no checking ///
		if($patterns === true) {
			return true;
		}
		//$this->logs->addLog($nameOfField,"nameOfF");
		//$this->logs->addLog($method,"method");
		
		if(is_null($data)){
			eval("\$data = \$_".$method."['".$nameOfField."'];");
			eval("\$set=isset(\$_".$method."['".$nameOfField."']);");
			/// data are expected in REQUEST but hasn't been sent ///			
			if(!$set){
				$this->aCheckingData[$nameOfField]['errors']['sent'] = true;
				$this->bIsError = true;
				$this->aCheckingData[$nameOfField]['valid'] = false;
				return false;
			}
		}
		
		if(is_array($patterns)){
			foreach($patterns as $k=>$pat) {
				if(!$this->checkData($data, $pat)) {
					$this->aCheckingData[$nameOfField]['errors']['msg'][] = (is_array($errors) && isset($errors[$k])) ? $errors[$k] : $errors;
					$this->bIsError = true;
					$this->aCheckingData[$nameOfField]['valid'] = false;
				}
			}
		} else {
			if(!$this->checkData($data, $patterns)) {
				$this->bIsError = true;
				$this->aCheckingData[$nameOfField]['valid'] = false;
				if(!is_null($errors)) {
					$this->aCheckingData[$nameOfField]['errors']['msg'][] = $errors;
				}	
			} 
		}

		return $this->aCheckingData[$nameOfField]['valid'];
	}
	
	/**
	 * 
	 * @param string $nof - name of element in array $aCheckingData
	 * @param mixed  $test - data to be checked
	 * @param string $patern
	 * @return boolean
	 */
	private function checkData($test, $pattern) {	  	
       	//$this->logs->addLog($pattern,"pattern");
		/// check true ///
   		if(in_array($pattern, self::$aVals['aTrue'], true)) {
 			eval("\$res = in_array(\$test, self::\$aVals['aTrue'], true);");
	    
   		/// check false ///
   		} else if(in_array($pattern, self::$aVals['aFalse'], true)) {
	      	eval("\$res = in_array(\$test, self::\$aVals['aFalse'], true);");
	      			        	
	    /// check fce ///
   		} else if(in_array($pattern, self::$aVals['aCheckFce'], true)) {
      		eval("\$res = ".$pattern."(\$test);");// ? true : false;
    	
   		} else {
    		eval("\$res = ".$pattern.";");
    	}
    	$this->logs->addLog(" $test / $pattern => $res","result");
    	return $res;    
	}
	
	public function checkEmail($val) {
	  $pat = '/([a-z0-9])([-a-z0-9._])+([a-z0-9])\@([a-z0-9])([-a-z0-9_])+([a-z0-9])(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*/i';
	  return preg_match($pat, $val);
	}
	
	public function checkPhone($val) {
	  $pat = '/(+)?(([0-9]){2,}(\s)?([0-9]){2,})+/';
	  return preg_match($pat, $val);
	}
	
	public function checkUrl($val) {
		$pat = '/((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)/';
		return preg_match($pat, $val);	
	}

}