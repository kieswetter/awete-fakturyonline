<?php
class tpl_main extends cTemplate {
	
	private $aSuperadminlinks = array();
	private $oPage; 
	
	public function __construct() {
		parent::__construct(substr(get_class(),4));
		$this->logs->on();
		self::action();		
	}
	
	private function action() {
		$oPage = $this->parseArrayToObject(cBuildIndex::getActualPage());
		$this->addVar($oPage,"page");
		
		$zalozky = array();
		$aZal = array('faktura','prehled','zakazky');
		foreach($aZal as $page) {
			/// page doesn't exists //
			if(!$lngPage = cBuildIndex::getLngPageData($page)){
				continue;
			}
			if($lngPage['published'] == '0'){
				continue;
			}
			$oP = $this->parseArrayToObject($lngPage);
			if($oPage->id == $oP->id) {
				$oP->active = true;
			}
			$zalozky[] = $oP;			
		}
		$this->addVar($zalozky,"zalozky"); 
		
		$nastaveni = new stdClass();
		$nastaveni->href = cBuildIndex::getActionUrl('nastaveni');
		$this->addVar($nastaveni,"nastaveni");
		
		if(count($aUser = $this->CFG->getUserData())) {
			$this->addVar($this->parseArrayToObject($aUser),"user");
		}
		
		$login = new stdClass();
		if($this->CFG->isAuthenticated()){
			$login->authenticated = true;
			$login->href = cBuildIndex::getActionUrl('login')."?logout";
		}else{
			$login->authenticated = false;
			$login->href = cBuildIndex::getActionUrl('login');
		}
		$this->addVar($login, 'login');
		
		$this->aSuperadminlinks[] = self::parseArrayToObject(array('href' => getUrl('admin'),'text' => 'Admin page'));		
		self::finish();
	}
	
	public function parseArrayToObject($array) {
		$obj = new stdClass();
		foreach($array as $k=>$v) {
			$obj->$k = $v;
		}
		return $obj;	
	}
	
	private function finish() {
		if($this->CFG->hasCapability('superadmin')) {
			$this->addVar($this->aSuperadminlinks,'superadminlinks');			
		}
	}
}
?>