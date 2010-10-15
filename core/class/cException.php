<?php
class cException extends Exception{
	
	function __construct($msg=''){
		parent::__construct($msg);
	}
	
	/**
	 * creates custom exception message for cDb functions when mysql error occures
	 * @param string $sFunction[optional] - name of function that calls this method
	 * @param strint $sMessage[optional] - extra message added to the end 
	 * @return string
	 */
	public function getDbMessageError($sFunction='', $sMessage='') {
		$ret = "Caught exception: $sFunction: " . $this->getMessage();
		$ret .= "\n\t" . mysql_error();
		$ret .= "\n\t" . $sMessage;
		return $ret;
	} 
}
?>