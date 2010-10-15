<?php
class cLogsDb extends cLogs {
	
	private static $fileToWrite = "logs/dbLogs.txt";
	public static $aDbFileLogs = array();
	//private static $bLogsWritten = false;
	private static $count_glob = 0;
	private static $startLogs = false;
	
	public function __construct($page="") {
		if(cLogsDb::$startLogs === false) {
			cLogsDb::$startLogs = microtimeFloat();
		}
		cLogsDb::$fileToWrite = "logs/".getDateToPrint(mktime(),"Ymd").".txt";
		parent::__construct("DB ".$page);
		cLogsDb::$count_glob ++;
	}
	
	/**
	 * writes logs added to this class to log file
	 */
	public static function writeLogsToFile() {
		if(count(self::$aDbFileLogs)){
			if($handle = fopen(ROOT_PATH.cLogsDb::$fileToWrite,'a+')) {
				cLogsDb::$fileToWrite = "logs/".getDateToPrint(mktime(),"Ymd")."a.txt";
				fwrite($handle, "\n----------------------- DB QUERIES ".getDateToPrint(mktime())." --------------------\n");
				foreach(cLogsDb::$aDbFileLogs as $log) {
					fwrite($handle, $log);
				}
				//fwrite($handle, "\n");
				fclose($handle);
			}
		}
	}
	
	/**
	 * prints all logs added to this class to screen 
	 */
	public function printAllLogs() {
		echo "<pre><strong>All DB LOGS</strong>\n".implode("\n",self::$aDbFileLogs)."</pre>";	
	}
	
	/**
	 * adds msg to logs(passes msg to parent cLogs with prefix DB_LOG);
	 * if second parameter is true adds also the msg to static logs in this class 
	 * @param mixxed $value
	 * @param string $name
	 */
	public function addLog($value, $name="--", $dbLog = "_dbLog") {		
		parent::addLog($value,$name,$dbLog);
	}
	
	/**
	 * adds msg to static logs in this class
	 * @param string $msg
	 */
	public static function addFileLog($msg) {
		$time = microtimeFloat()- cLogsDb::$startLogs;
		cLogsDb::$aDbFileLogs[] = getDateToPrint(mktime(),"m-d-Y H:i:s")." + $time\n\t".$msg."\n";
		//$this->addLog($msg,"MYSQL ERROR", "_dbLog _error");
	}
	
	function __destruct() {
		cLogsDb::$count_glob --;
		parent::__destruct();
		if(cLogsDb::$count_glob == 0 && DB_DEBUG) {
			//cLogs::$aLogsAll[] = array("DB_QUERIES",cLogsDb::$aDbFileLogs);
			cLogsDb::writeLogsToFile();
		}
	}
}
?>