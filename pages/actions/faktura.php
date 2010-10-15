<?php
class faktura extends cPageAction {
	
	private $data;
	private $oCheck;
	private $process = null;
	private $aErrors = array();
	private $aAlerts = array();
	private $aFakToDb = array();
	private $aDodToDb = array();
	private $aOdbToDb = array();
	private $aPost = array();	
	private $aUserData = array();
	
	public function __construct() {
		parent::__construct(get_class());
		if($this->CFG->isAuthenticated() && !count($this->CFG->getUserData('account')) && !$this->CFG->hasCapability('superadmin')) {
			header("Location: ".cBuildIndex::getActionUrl('nastaveni'));
		}
		$this->logs->on();
		$this->aUserData = $this->CFG->getUserData();
		self::action();
		self::finish();	
	}
	
	private function action() {
		self::setButtons();
		if(isset($_POST['fc_faktura_save'])){
			foreach($_POST as $k=>$v){
				$this->aPost[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
			}
			$this->oCheck = new cCheckForm();
			/// formular je v poradku ///
			if( self::checkForm() ) {
				self::saveActions($this->aPost['fc_faktura_save']);
			}
			self::setData($this->aPost);
		}else{
			self::setData();
		}
	}
	
	private function saveActions($action) {
		switch($action) :
	 		/// ulozeni faktury pro prihlasene ///
			case ('fc_save'):
	 			/// user nema opravneni ulozit fakturu = neprihlaseni uzivatele///
	 			if(!$this->CFG->hasCapability('faktura_save')){
	 				$this->aErrors[] = getString("Nemáte oprávnění ukládat faktury!",'faktura');
	 				break;
	 			}
	 			$this->aFakToDb[] = array('dodavatel',$this->aPost['dod_id'],false);
	 			
	 			/// vybran existujici odberatel ///
	 			if(isset($this->aPost['odb_id'])) {
	 				/// id odberatele do DB pro fakturu ///
	 				$this->aFakToDb[] = array('odberatel',$this->aPost['odb_id'],false);
	 			/// nepodarilo se ulozit noveho odberatele ///
	 			}else if(($odbId = !self::saveOdberatel()) !== false) {
	 				$this->aFakToDb[] = array('odberatel',$odbId,false);
	 			}else{
	 				break;
	 			}
	 			
	 			self::parseFakDataToDb();
	 			/// nepodarilo se ulozit fakturu ///
	 			if(!self::saveFaktura()) {
	 				/// smazani nove ulozeneho odberatele, pokud byl vlozen ///
	 				if(isset($odbId)){
	 					$this->db->delete('odberatele',array('id','=',$odbId));
	 				}
	 				break;
	 			}
	 			$this->aAlerts[] = getString("Faktura byla uložena.",'faktura');
	 			break;
	 		case ('fc_print'):
	 			
	 			/// vybran existujici dodavatel u prihlaseneho uzivatele ///
	 			if(isset($this->aPost['dod_id']) && $this->CFG->isAuthenticated()) {
	 				$this->aFakToDb[] = array('dodavatel',$this->aPost['dod_id'],false);			
	 			/// nepodarilo se ulozit noveho dodavatele ///
	 			}else if(($dodId = self::saveDodavatel()) !== false) {
	 				$this->aFakToDb[] = array('dodavatel',$dodId,false);
	 			}else{
	 				break;
	 			}
	 			
	 			/// vybran existujici odberatel ///
	 			if(isset($this->aPost['odb_id'])) {
	 				/// id odberatele do DB pro fakturu ///
	 				$this->aFakToDb[] = array('odberatel',$this->aPost['odb_id'],false);
	 			/// nepodarilo se ulozit noveho odberatele ///
	 			}else if(($odbId = self::saveOdberatel()) !== false) {
	 				$this->aFakToDb[] = array('odberatel',$odbId,false);
	 			}else{
	 				/// smazani nove ulozeneho dodavatele, pokud existuje ///
	 				if(isset($dodId)){
	 					$this->db->delete('dodavatele',array('id','=',$dodId));
	 				}
	 				break;
	 			}
	 			
	 			self::parseFakDataToDb();
	 			
	 			/// nepodarilo se ulozit fakturu ///
	 			if(!self::saveFaktura()) {
	 				/// smazani nove ulozeneho dodavatele, pokud byl vlozen ///
	 				if(isset($dodId)){
	 					$this->db->delete('dodavatele',array('id','=',$dodId));
	 				}
	 				/// smazani nove ulozeneho odberatele, pokud byl vlozen ///
	 				if(isset($odbId)){
	 					$this->db->delete('odberatele',array('id','=',$odbId));
	 				}
	 				break;
	 			}
	 			$this->aAlerts[] = getString("Faktura byla uložena.",'faktura');
	 			self::printFaktura();
	 			break;
	 		case ('fc_pdf'):
				if(!$this->CFG->hasCapability('faktura_pdf')){
	 				$this->aErrors[] = getString("Nemáte oprávnění generovat pdf faktury!",'faktura');
	 				break;
	 			}
	 			//TODO//
	 			break;
	 		/// nebyla poslana spravna hodnota action ///
	 		default:
	 			$this->aErrors[] = getString("Vyžadujete neoprávněnou operaci!",'faktura');
	 			break;
	 	endswitch;
	}
	
	private function checkForm() {
		$regDate = '/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/'; /// format 1.12.2020
		$regPsc = '/[0-9]{3}+\s?[0-9]{1,2}+/';
		
		/// neprihlaseny uzivatel bez uctu ///
		if(!$this->CFG->isAuthenticated()){
			$this->oCheck->check('dod_nazev', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_ulice', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_mesto', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_psc', "preg_match('$regPsc',\$test)",getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_ico', 'is_numeric($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_dic', 'strlen($test)==0 || is_numeric($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_web', 'strlen($test)==0 || $this->checkUrl($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_email', 'strlen($test)==0 || $this->checkEmail($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_tel', 'strlen($test)==0 || $this->checkPhone($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('dod_fax', 'strlen($test)==0 || $this->checkPhone($test)',getString('Pole Název je poviné!','faktura'));		
			$this->oCheck->check('dod_dph', '$test=="'.CD_FAKT_PLATCE_DPH_ANO.'" || $test=="'.CD_FAKT_PLATCE_DPH_NE.'"', getString('Pole Název je poviné!','faktura'));	
		}else{
			
			$this->oCheck->check('dod_id', 'is_numeric($test)',getString('Nebyly zaslány údaje o dodavateli!','faktura'));
			$dodEx = (arraySearch($this->aPost['dod_id'], $this->CFG->getUserData('dodavatele'),'id') !== false ? true : false);
			$this->oCheck->check('dod_id', '$test==true',getString('Daný dodavatel neexistuje!','faktura'), $dodEx);
		}

		/// prihlaseny uzivate a existujici odberatel ///
		if(isset($this->aPost['odb_id']) && $this->CFG->isAuthenticated()) {
			$odbEx = (count(self::getOdberatel($this->aPost['odb_id'], $this->CFG->getUseraccountId())) ? true : false);
			$this->oCheck->check('odb_id', '$test==true',getString('Daný odběratel neexistuje!','faktura'), $odbEx);	
		}else{
			$this->oCheck->check('odb_nazev', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_ulice', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_mesto', 'strlen($test)>0',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_psc', "preg_match('$regPsc',\$test)",getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_ico', 'strlen($test)==0 || is_numeric($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_dic', 'strlen($test)==0 || is_numeric($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_email', 'strlen($test)==0 || $this->checkEmail($test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('odb_tel', 'strlen($test)==0 || $this->checkPhone($test)',getString('Pole Název je poviné!','faktura'));
		
			$this->oCheck->check('splatnost', "preg_match('/(\d)+/',\$test)",getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('datum_vystaveni', 'preg_match("'.$regDate.'",$test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('datum_splatnosti', 'preg_match("'.$regDate.'",$test)',getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('cislo_faktury', "preg_match('/(\d)+/',\$test)",getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('variabilni_symbol', "preg_match('/(\d)*/',\$test)",getString('Pole Název je poviné!','faktura'));
			$this->oCheck->check('vystavil_tel', 'strlen($test)==0 || $this->checkPhone($test)',getString('Pole Název je poviné!','faktura'));
		}
				
		/// chyby ve formulari ///
		if(!$this->oCheck->isValid()) {			
			$err = new stdClass();
			foreach($this->oCheck->getErrors() as $k=>$error) {
				$err->$k = $error;
			}
			/// errors pro zobrazeni u jednotlivych formularovych polozek ///
			$this->addVar($err,'form_errors');
			$this->logs->addLog($this->oCheck->getErrors());
			return false;
		}
		
		return true;
	}
	
	private function parseFakDataToDb() {		 		
		/// prihlaseny uzivatel ///
		if(!is_null($this->CFG->getUseraccountId())){
			$this->aFakToDb[] = array('useracc',$this->CFG->getUseraccountId(),false);
		}
		
		/// zbytek dat faktury ///
		$this->aFakToDb[] = array('cislo',$this->aPost['cislo_faktury'],false);
		$this->aFakToDb[] = array('splatnost',$this->aPost['splatnost'],false);
		$this->aFakToDb[] = array('datum_vyst',getDateToDb(getStringToTime($this->aPost['datum_vystaveni'],'j.n.Y')));
		$this->aFakToDb[] = array('datum_splat',getDateToDb(getStringToTime($this->aPost['datum_splatnosti'],'j.n.Y')));
		$this->aFakToDb[] = array('zpusob_uhr',$this->aPost['zpusob_uhrady'],false);
		$this->aFakToDb[] = array('varsymbol',$this->aPost['variabilni_symbol']);
		$this->aFakToDb[] = array('vystavil',$this->aPost['vystavil']);
		$this->aFakToDb[] = array('vystavil_tel',$this->aPost['vystavil_tel']);
		$this->aFakToDb[] = array('typ', $this->aPost['typ_faktury'], false);
		$this->aFakToDb[] = array('ip', $_SERVER['REMOTE_ADDR']);
	}
	
	private function saveFaktura() {
		try {
			if(!$this->db->insert('faktury',$this->aFakToDb)){
				throw new cException(getString("Neočekávaná chyba během ukládání faktury!",'faktura'));
			}
			//$this->aAlerts[] = getString("Faktura byla uložena.",'faktura');
			return true;	
		}catch(cException $e) {
	  		$this->aErrors[] = $e->getMessage();
			cLogsDb::addFileLog($e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')'));
			return false;
		}
	}
	
	private function printFaktura() {
		
	}
	
	/**
	 * ulozeni dodavatele v pripade, ze neni uzivatel prihlaseny(neuklada uzivatele useracc)
	 * @return int/false - ID dodavatele v DB nebo false, kdyz se operace nezdari
	 */
	private function saveDodavatel() {		
		$this->aDodToDb[] = array('nazev',$this->aPost['dod_nazev']);
		$this->aDodToDb[] = array('ulice',$this->aPost['dod_ulice']);
		$this->aDodToDb[] = array('mesto',$this->aPost['dod_mesto']);
		$this->aDodToDb[] = array('psc',$this->aPost['dod_psc']);
		$this->aDodToDb[] = array('ico',$this->aPost['dod_ico'],false);
		if($this->aPost['dod_dic'] != ""){
			$this->aDodToDb[] = array('dic',$this->aPost['dod_dic'],false);			
		}
		if($this->aPost['dod_soud_misto'] != "") {
			$this->aDodToDb[] = array('soud',$this->aPost['dod_soud_misto']);
		}
		if($this->aPost['dod_soud_spis'] != "") {
			$this->aDodToDb[] = array('spis_zn',$this->aPost['dod_soud_spis']);
		}
		if($this->aPost['dod_web'] != "") {
			$this->aDodToDb[] = array('web',$this->aPost['dod_web']);
		}
		if($this->aPost['dod_email'] != "") {
			$this->aDodToDb[] = array('email',$this->aPost['dod_email']);
		}
		if($this->aPost['dod_tel'] != "") {
			$this->aDodToDb[] = array('tel',$this->aPost['dod_tel']);
		}
		if($this->aPost['dod_fax'] != "") {
			$this->aDodToDb[] = array('fax',$this->aPost['dod_fax']);
		}
		$this->aDodToDb[] = array('platce_dph',$this->aPost['dod_dph'], false);
			
		try {
			if(!$this->db->insert('dodavatele',$this->aDodToDb)){
				throw new cException(getString("Neočekávaná chyba během ukládání dodavatele!",'faktura'));
			}
			//$this->aAlerts[] = getString("Byl přidán nový dodavatel.",'faktura');	
		}catch(cException $e) {
	  		$this->aErrors[] = $e->getMessage();
			cLogsDb::addFileLog($e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')'));
			//$this->logs->addLog($e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')'),'save_dodavatel','_dbLog');
			return false;
		}
		return $this->db->getLastId();		
	}
	
	/**
	 * Ulozeni noveho odberatele, pokud je uzivatel prihlasen, ulozi ho pod jeho useraccount
	 * @return int/false - ID odberatele in DB nebo vrati false, pokud se nezdarila operace
	 */
	private function saveOdberatel(){
		/// prihlaseny uzivatel ///
		if(!is_null($this->CFG->getUseraccountId())){
			$this->aOdbToDb[] = array('useracc',$this->CFG->getUseraccountId(),false);
		}
		$this->aOdbToDb[] = array('nazev',$this->aPost['odb_nazev']);
		$this->aOdbToDb[] = array('ulice',$this->aPost['odb_ulice']);
		$this->aOdbToDb[] = array('mesto',$this->aPost['odb_mesto']);
		$this->aOdbToDb[] = array('psc',$this->aPost['odb_psc']);
		if($this->aPost['odb_ico'] != "") {
			$this->aOdbToDb[] = array('ico',(int)$this->aPost['odb_ico'],false);
		}
		if($this->aPost['odb_dic'] != "") {
			$this->aOdbToDb[] = array('dic',$this->aPost['odb_dic']);
		}
		if($this->aPost['odb_email'] != "") {
			$this->aOdbToDb[] = array('email',$this->aPost['odb_email']);
		}
		if($this->aPost['odb_tel'] != "") {
			$this->aOdbToDb[] = array('tel',$this->aPost['odb_tel']);
		}
		
		try {
			if(!$this->db->insert('odberatele',$this->aOdbToDb)){
				throw new cException(getString("Neočekávaná chyba během ukládání odběratele!",'faktura'));
			}
			//$this->aAlerts[] = getString("Byl přidán nový odběratel.",'faktura');	
		}catch(cException $e) {
	  		$this->aErrors[] = $e->getMessage();
			cLogsDb::addFileLog($e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')'));
			return false;
		}
		return $this->db->getLastId();	
	}
	
	/**
	 * Funkce vrati data odberatele dle ID a v pripade zadani druheho parametru
	 * i v zavislosti na ID uzivatelskeho uctu. Pokud takoveho odberatele nenalezne, vrati prazdne pole.
	 * Pokud je $id null a je nastaven druhy parametr, vrati odberatele pro dany uz. ucet.
	 * Pokud jsou oba parametry NULL, vrati vsechny odberatele v db.
	 * @param int $id[optional][default null] - id odberatele
	 * @param int $userAccountId[optional][default null] - id uzivatelskeho uctu, pod kterym je odberatel ulozen
	 * @return array
	 */
	public function getOdberatel($id = null, $userAccountId = null) {
		if(is_null($userAccountId) && is_null($id)) {
			return cDb::select('odberatele', '*');
		}else if(is_null($userAccountId)) { 
			$where = array('id','=',trim($id));
		}else if(is_null($id)) {
			$where = array('useracc','=',trim($userAccountId));
		}else {
			$where = array(array('id','=',trim($id)),'AND',array('useracc','=',trim($userAccountId)));
		}
		return cDb::select('odberatele', '*', $where);	
	}
	
	/**
	 * Funkce vrati pole vsech dodavatelu. Pokud je zadan parametr, vrati pouze dodavatele pro daneho uzivatele/jeho ucet.
	 * @param int $useraccountId[optional]
	 * @return array
	 */
	public function getDodavatele($useraccountId = null) {
		if(is_null($useraccountId)){
			$result = $this->db->select('dodavatele','*');
		}else{
			$result = $this->db->select('dodavatele','*',array('useracc','=',$useraccountId));
		}
		return $result;
	}
	
	private function setButtons() {
		$butts = array();
		if($this->CFG->isAuthenticated()) {
			$butts[] = array('type' => 'submit', 'name'=>'fc_save', 'value'=>getString('Uložit'), 'id'=>'btnsub_save');
			$butts[] = array('type' => 'submit', 'name'=>'fc_print', 'value'=>getString('Uložit a tisknout'), 'id'=>'btnsub_print');
			if($this->CFG->hasCapability('faktura_pdf')) {
				$butts[] = array('type' => 'submit', 'name'=>'fc_pdf', 'value'=>getString('Uložit a generovat PDF'), 'id'=>'btnsub_pdf');
			}
		}else{
			$butts[] = array('type' => 'submit', 'name'=>'fc_print', 'value'=>getString('Tisknout'),'id'=>'btnsub_print');
		}
		$this->addVar($butts, 'submits');	
	}
	
	private function setData($array=null) {		
		if(is_array($array)) {
			$this->data = tpl_main::parseArrayToObject($array);
		/// defaultni nastaveni dat ///
		}else{
			$this->data = new stdClass(); 
			$this->data->datum_vystaveni = getDateToPrint(mktime(),"j.n.Y");
			$this->data->splatnost = 0; /// pocet dnu
			$this->data->datum_splatnosti = getDateToPrint(mktime()+($this->data->splatnost*24*60*60),"j.n.Y");
			$this->data->typ_faktury = CD_FAKT_TYP_POL;
		}
		if($this->CFG->isAuthenticated()){
			$this->data->dod_id = $this->CFG->getActiveDodId();
			$this->addVar($this->CFG->getUserData('dodavatele'), 'dodavatele');
			$aOdb = self::getOdberatel(null,$this->CFG->getUseraccountId());
			$od = array();
			foreach($aOdb as $odb) {
				$od[$odb['id']] = $odb;
			}
			$this->addVar($od, 'odberatele');
		}
	}
	
	private function finish(){
		self::addVar($this->data,'data');
		$this->logs->addLog($this->aAlerts,'alerts');
		$this->logs->addLog($this->aErrors,'errors');
		if(count($this->aErrors)){
			self::addVar($this->aErrors,'errors');
		}		
		if(count($this->aAlerts)){
			self::addVar($this->aAlerts,'alerts');
		}
	}
}
?>