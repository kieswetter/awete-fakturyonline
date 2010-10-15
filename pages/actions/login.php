<?php
class login extends cPageAction {
	
	private $aErrors = array();
	
	public function __construct() {
		parent::__construct(get_class());
		$this->logs->on();
		self::action();	
		self::finalize();
	}
	
	private function action() {
		if(isset($_POST['login']) && isset($_POST['challenge'])){
			if(isset($_POST['password_hmac']) && strlen($_POST['password_hmac'])){
				self::login();
			}else{
				$this->aErrors[] = getString("Nebyly zaslány všechny potřebné údaje!",'login');
				$this->aErrors[] = getString("Zkontrolujte, zda máte zapnutý javascript!",'login');
			}
		}
		if(!$this->CFG->isAuthenticated()){
			/// 3s prodleva mezi nactenim a odeslanim formulare pro ok prihlaseni ///
			$vals = array(
						array('timecreated',getDateToDb(time()+3)),
						array('session',session_id())
					);
			$this->db->insert( "core_challenges", $vals );
			$challenge = $this->db->getLastId();
			$this->addVar($challenge, "challenge");
			$this->addVar(cBuildIndex::getActionUrl('login'), "href");	
		}
	}
	
	private function login() {
		$Auth = new cAuthentication();
		/// unsuccessful login proces ///
		if(!$Auth->login($_POST['login'], $_POST['password_hmac'], $_POST['challenge'])) {			
			$this->aErrors[] = getString("Přihlášení se nezdařilo!",'login');
			foreach($Auth->getErrors() as $error) {
				$this->aErrors[] = $error;		
			}
			$this->db->delete('core_challenges',array('id','=',$_POST['challenge']));
		}
	}
	
	private function finalize() {
		if(count($this->aErrors)){
			$this->addVar($this->aErrors,'errors');
		}
	}
}
?>