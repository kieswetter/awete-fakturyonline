<?php
class cCfg {
	
	#### vars to be set ###
	public static $aLangs = array('cs','en');
	protected static $aDefaultPage = array('name'=>'faktura');
	public static $sPageAfterLofin = 'faktura';	
	public static $sPageNotFound = 'pagenotfound';
	public static $sNotAllowedPage = 'pagenotfound'; //TODO - now is not used 4.8.2010
	
	public static $lng = null;	
	protected static $aUserData = array();	
	protected static $aUserCapabilities = array();	
	protected static $bConfSet = false; 
	public static $logs;
	
	private static $idActiveUseraccount = null;
	
	public function __construct() {
		/// config haven't been set yet ///
		if(!cCfg::$bConfSet) {
			
			cCfg::$logs = new cLogs('conf');
			cCfg::$logs->on();
			cCfg::$lng = cCfg::$aLangs[0];
			self::setDefaultPage(self::$aDefaultPage['name']);
			
			cCfg::$bConfSet = true;
		}
	}
	
	final public function setUserData($name, $value) {
		self::$aUserData[$name] = $value;	
	}
	
	final public function getUserData($var = null) {
		if(is_null($var)){
			return self::$aUserData;
		}
		if(isset(self::$aUserData[$var])) {
			return self::$aUserData[$var];
		}else{
			return null;
		}	
	}
	
	final public function hasCapability($capab){
		if(is_null($role = self::getUserData('role'))) {
			return false;
		}
		/// if user is superadmin, he is always allowed to action ///
		if(in_array('superadmin', self::$aUserCapabilities)){
			return true;
		}
		
		return (in_array($capab, self::$aUserCapabilities));
	}
	
	final public function setCapability($sCapab = null) {
		if(!is_null($sCapab)) {
			self::$aUserCapabilities[] = $sCapab;	
		} else {
			$result = cDb::select(	array('core_role_capability',
									array('capability','=','core_capabilities.id','INNER'),
									'core_capabilities'),
								null,
								array('core_role_capability.role','=',self::getUserData('roleid'))
							);
			foreach($result as $capab) {
				self::$aUserCapabilities[] = $capab['name'];	
			}
		}
		cCfg::$logs->addLog(self::$aUserCapabilities,"user CAPABILITIES");
			
	}
	
	final public function setDefaultPage($pageName) {
		$data = cBuildIndex::getLngPageData($pageName);		
		
		if(is_array($data) && count($data)) {
			self::$aDefaultPage['title'] = $data['title'];
			self::$aDefaultPage['menu_title'] = $data['menutitle'];
			self::$aDefaultPage['url'] = $data['url'];
			self::$aDefaultPage['path'] = cBuildIndex::getActionUrl($pageName);			
			self::$aDefaultPage['name'] = $pageName;
			self::$aDefaultPage['parentid'] = intval($data['parentid']);
			self::$aDefaultPage['usecache'] = ($data['usecache'] == '1') ? true : false;
			self::$aDefaultPage['id'] = intval($data['id']); 
		}
						
	}
	
	final public function getDefaultPage($param = null) {
		if(!is_null($param) && isset(self::$aDefaultPage[$param])) {
			return self::$aDefaultPage[$param];
		} else {
			return self::$aDefaultPage;
		}
	}
	
	/**
	 * @return boolean
	 */
	final public function isAuthenticated() {
		if(!is_null(self::getUserData('id'))) {
			return true;
		}else{
			return false;
		} 
	}
	
	/// CUSTOM FUNCTIONS ///
	public function setUserAccount() {
		$result = cDb::select('useraccount','*',array('user','=',self::getUserData('id')));
		cCfg::$aUserData['account'] = array();
		if(is_array($result) && count($result)){
			//cCfg::$idActiveUseraccount = $result[0]['id'];
			cCfg::$aUserData['account'] = $result[0];
		}
		$aDod = cDb::select('dodavatele','*',array('useracc','=',$result[0]['id']));
		cCfg::$aUserData['dodavatele'] = array();
		foreach($aDod as $dod) {
			cCfg::$aUserData['dodavatele'][$dod['id']] = $dod;
			if(is_null(cCfg::$idActiveUseraccount)){
				cCfg::$idActiveUseraccount = $dod['id'];
			}
		}
		cCfg::$logs->addLog(cCfg::$aUserData, 'aUserData');	
	}
	
	/**
	 * vrati id aktivniho dodavatele daneho uzivatele - resp. id uzivatelskeho uctu  
	 * @return int
	 */
	public function getActiveDodId() {
		return cCfg::$idActiveUseraccount;
	}
	
	/**
	 * vrati id uzivatelskeho uctu nebo NULL, pokud neni nastaveno
	 * @return int/null
	 */
	public function getUseraccountId() {
		if(isset(cCfg::$aUserData['account']) && isset(cCfg::$aUserData['account']['id'])) {
			return cCfg::$aUserData['account']['id'];
		} else {
			return null;
		}
	}
}

?>