<?php
class cTemplate {
	
	protected $aVars = array();
	protected $tempname;
	protected $db, $CFG;
	public $logs;
	
	function __construct($name) {
		$this->tempname = $name;
		$this->CFG = new cCfg();
		$this->logs = new cLogs(get_class()." - tpl_".$name.".php");
		$this->db = new cDb();		 
	}
	
	protected function addVar($value,$name) {
		$this->aVars[$name] = $value;	
	}
	
	public function getVar($name) {
		return (isset($this->aVars[$name])? $this->aVars[$name] : null);	
	}
	
	public final function getFinalVars() {
		self::end();
		return $this->aVars;	
	}
	
	final public function end() {
		
	}
	
	function __destruct() {
		
	}
}
?>
