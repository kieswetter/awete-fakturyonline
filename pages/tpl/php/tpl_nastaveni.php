<?php
class tpl_nastaveni extends cTemplate {
	
	public function __construct() {
		parent::__construct(substr(get_class(),4));
		self::action();		
	}
	
	private function action() {
		// here goes your code //
	}
}
?>