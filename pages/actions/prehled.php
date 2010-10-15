<?php
class prehled extends cPageAction {
	
	public function __construct() {
		parent::__construct(get_class());
		self::action();	
	}
	
	private function action() {
		$list = $this->db->selectAsObjects('faktury', null, array('useracc','=',$this->CFG->getUserData('id')));
		$this->logs->on();		
		$this->addVar($list,"fakturylist"); 
	}
}
?>