<?php
class cLogs {
	
	public static $aLogsAll = array();
	private static $aLogsVarsAll = array();
	private static $aLogsDbAll = array();
	
	protected $aLogs = array();
	protected $sCallingPage;
	protected $bOn = false;
	private static $nNumberOfInstants = 0;
	
	function __construct($page = "") {
		$this->sCallingPage = $page;
		self::$nNumberOfInstants ++;
	}
	
	/**
	 * switch on Logs
	 */
	public function on() {
		$this->bOn = true;
	}
	
	/**
	 * switch off Logs
	 */
	public function off() {
		$this->bOn = false;
	}
	
	public function setPage($string) {
		$this->sCallingPage = $string;
	}
	/**
	 * add message to logs
	 * @param mixed $value - on the output will be printed by var_dump()
	 * @param string $name - name of value
	 * @param string $log_class[otpional][default ""] - for DB logs use '_dbLog'
	 */
	public function addLog($value, $name="-undefined-", $log_class="") {
		$log = self::getParsedLog($value,$name,$log_class);
		if($this->bOn) {						
			$this->aLogs[] = $log;
		}
		if(substr($log_class,0,6) == "_dbLog"){
			cLogs::$aLogsDbAll[] = $log; 
		}
	}
	
	public function addVarsLog($value, $name="-undefined-", $log_class="") {
		$log_class = "_vars ".$log_class;
		//self::addLog($value,$name, $log_class);
		cLogs::$aLogsVarsAll[] = self::getParsedLog($value,$name,$log_class); 
	}
	
	private function getParsedLog($value,$name,$log_class) {
		$msg = "[$name] ".gettype($value)."(";
		if(is_array($value) || is_object($value)){
			$msg .= count($value);
		}else{
			$msg .= strlen($value);
		}
		$msg .= ") ".getDateToPrint(mktime());
		return array($msg, $value, $log_class);
	}
	
	/**
	 * prints all logs added to this class to screen 
	 */
	private function printAllLogs() {
		echo "<div class='_logs'>";
		foreach(self::$aLogsAll as $aLog) {
			echo "<div class='_logs_page'>";
			echo "<div class='_log_pagetitle'>".$aLog[0]."</div>\n"; 
			$this->printActualLogs($aLog[1]);
			echo "\n";
			echo "</div>\n";			
		}
		echo "</div>";
	}
	
	/**
	 * return array of all messages in logs
	 * @return array
	 */
	public function getLogs() {
		return $this->aLogs;
	}
	
	/**
	 * parse all logs to html code separated by <br /> tag
	 * @param array $aLogs[optional][default null] - alternative data to print;
	 * if null is set log data of this object will be print 
	 * @return string
	 */
	public function printActualLogs($aLogs=null) {
		foreach($aLogs as $log) {
			echo "<div class='_logtitle ".$log[2]."'>".$log[0]."</div>\n";
			echo "<div class='_log ".$log[2]."'>";
			self::printLog($log[1],false);
			echo "</div>";			
			echo "\n";
		}
	}
	
	private function printLog($log, $parent=true) {
		if(is_object($log) || is_array($log)) {
			if($parent) {
				echo " {";
			}
			foreach($log as $k=>$l){	
				if(is_string($l) || is_integer($l)){
					$count = strlen($l);
				}else{
					$count = count($l);
				}
				echo"<div class='_log_parent ".(!$parent?'first':'')."'>";
					echo"<span class='_log_title'>[$k] => ".gettype($l)."($count)</span>";					
					self::printLog($l);
				echo"</div>";
			}
			if($parent) {
				echo "}";
			}
		}else{
			echo "<pre class='_log_final'>";
				var_dump($log);
				//print $log;
			echo "</pre>";
		}
	}
	
	function __destruct() {
		self::$nNumberOfInstants --;
		if($this->bOn) {
			self::$aLogsAll[] = array($this->sCallingPage, $this->aLogs);
		}
		
		/// this instance of this class is destructed as last one /// 
		if(self::$nNumberOfInstants == 0 && MK_DEBUG){
			cLogs::$aLogsAll[] = array("ALL DB QUERIES",cLogs::$aLogsDbAll);
			cLogs::$aLogsAll[] = array("VARS",cLogs::$aLogsVarsAll);
			$this->printAllLogs();
		}
	} 
}
?>
