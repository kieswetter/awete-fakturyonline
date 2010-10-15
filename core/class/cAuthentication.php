<?php
class cAuthentication {	
	
	public $logs; // object of cLogs
	private static $bAuthenticated = false;
	//private $db;
	private $aErrors = array();
	private static $bInit = false;
	
	public function __construct() {
		$this->logs = new cLogs(get_class());
		$this->logs->on();
		//$this->db = new cDb();
		
		if (isset($_GET["logout"])){
        	$this->logout();
		}		
		/* /// script for login process ///
		if(isset($_POST['login']) && isset($_POST['challenge'])){
			$this->logs->addLog($_POST['login'],"POST: login");			
			if(isset($_POST['password_hmac'])){
				$login = self::login($_POST['login'], $_POST['password_hmac'], $_POST['challenge']);
			}else if(isset($_POST['password'])){
				$login = self::login($_POST['login'], $_POST['password'], $_POST['challenge'], false);
			}
			/// successful login proces ///
			if($login){
				header('Location:'.cBuildIndex::getActionUrl(cCfg::$sPageAfterLofin));
			}
		}
		*/
		if(!cAuthentication::$bInit) {
			cAuthentication::$bInit = true;
			/// delete all expired records ///
			cDb::delete('core_challenges', array('timecreated','<',getDateToDb(time())));
			cDb::delete('core_authentications', array('timeinit','<',getDateToDb(time())));
		}    	
	}
	
	public function authenticate() {   
		/// user is logged ///
		if($aUser = $this->checkLoggedUser()){
			self::$bAuthenticated = $this->update($aUser);
		}
				
		if(self::$bAuthenticated) {
			cCfg::setCapability();
		}
		return self::$bAuthenticated;
	}
	
	public function isAuthenticated() {
		return self::$bAuthenticated;
	}
	
	/**
	 * Check data with data of user in DB. Only active users can be authenticate.
	 * If is set $bSave to false password will be convert to sha1 and compare with stamp in DB - NO SAFE and NO RECOMMENDED<br/>
	 * Otherwise passw needs to be sent in stamp of hmac_sha1 fce according to sal($challenge) and passw.
	 * If all goes correct then $challenge has to exist in core_challenges.    
	 * @param string $login
	 * @param string $passw - for safe authentication it should be cyphred by sha1 and hmac (RECOMENDED)
	 * @param string $challenge - value saved to table 'core_challenges' and should be sent by POST while login process
	 * @param boolean $bSave[otpional][default true] - if $passw is sent in hash form, the value should be true
	 * @return boolean
	 */
	public function login($login, $passw, $challenge, $bSave = true) {		
		$this->logs->addLog("login() called");
		$logged = false;
		
		$login = (get_magic_quotes_gpc() ? $login : addslashes($login));
		
		$row = cDb::select("core_users", '*', array('login','=',$login), null, 1);
		/// login doesn't exist ///
		if(!is_array($row) || count($row) == 0){
			$this->aErrors[] = getString("User doesn't exist!",'core');
			return false;
		} else {
			$row = $row[0];
		}
		/// user is not active ///
		if(!(int)$row['active']){
			$this->aErrors[] = getString("Your account has been deactivated!",'core');
			return false;
		}
		/// $passw is sent in hmac/sha1 form ///
		if ($bSave) {
    		$valid = (self::hmac_sha1($row["password"], $challenge) == $passw);
		} else {
			$valid = ($row["password"] == self::cyphrePassword($passw));
		}
		$this->logs->addLog($passw,'password');
		$this->logs->addLog(self::hmac_sha1($row["password"],$challenge),'password1');
		/// user doesn't match sent password ///
		if (!$valid) {
    		$this->aErrors[] = getString("User with this password doesn't exist!",'core');
    		return false;
		}
		
		try{
			$where = array(	array('id','=',intval($challenge)),'AND',array('session','=',session_id()) );
			/// chalenge wasn't inserted from the same session ///
			if(!cDb::delete("core_challenges", $where)) {
	    		throw new cException(getString("You are trying to login without authorization or after long time the page was generated!",'core'));
	    	}
	    	$this->logs->addLog(cDb::getAffectedRows(),"deleted:");
			try{
				if(!self::insert($row)){
					throw new cException(getString("Some error during login process!",'core'));
				}
				if(REDIRECT_AFTER_LOGIN){
		    		header('Location:'.cBuildIndex::getActionUrl(cCfg::$sPageAfterLofin));	
		    	}
				return true;
			}catch(cException $e) {
				$this->aErrors[] = $e->getMessage();
				return false;
			}
		} catch (cException $e) {
			$this->logs->addLog(cDb::getAffectedRows(),"deleted:");
    		$this->aErrors[] = $e->getMessage();
			return false;
		}   
	}
	
	private function insert($aUser) {
		$time = getDateToDb(time() + AUTH_TIMEOUT);
		$sess = session_id();
		$res = cDb::insert('core_authentications',array(	array('timeinit',$time),
													array('user',$aUser['id'],false),
													array('session',$sess),
													array('ip',$_SERVER['REMOTE_ADDR'])
							));
	  	if(!$res){
	    	cLogs::addLog("#### AuthInsert: false");
	    	$this->aErrors[] = getString("Some error during login process!",'core');
	    	$this->aErrors[] = getString("Contact your admin to solve this problem!",'core');
	    	$this->clearSessions();
	    	$this->clearCookies();
	    	return false;    
	  	}
	  	$this->setSessions($aUser);
	  	$this->setCookies($aUser);
	    return true;
	}
	
	public function logout() {
		$userid = cCfg::getUserData('id');
    	$sess = session_id();
    	if($userid !== false && $sess){
    		$where = array(array('session','=',$sess),'AND',array('ip','=',$_SERVER['REMOTE_ADDR']));
      		$result = cDb::delete('core_authentications', $where);
		}
    	$this->clearSessions();
    	header('Location: '.cBuildIndex::getActionUrl(cCfg::getDefaultPage('name')));
	}
	
	private function update($aUser) {
		$ok = false;
		//$this->logs->addLog($aUser,"aUser - update");
		$time = getDateToDb(time() + AUTH_TIMEOUT);
  		if(is_numeric($aUser['id']) && $aUser['session']){
    		$ok = cDb::update("core_authentications", array('timeinit',$time,true),
    						array(array('user','=',$aUser['id']),'AND',array('session','=',session_id()))
    					);
  			
    	}
    	if(!$ok){
    		$this->clearSessions();
    		$this->clearCookies();
    		return false;
    	}
    			
		$this->setSessions($aUser);
	  	$this->setCookies($aUser);
	  	return true;
	}
	
	public function checkLoggedUser() {
		if($_SESSION['session']){
	  		$user = cDb::select(array(	'core_authentications',
	  									array('user','=','core_users.id','LEFT'),
	  									'core_users',
	  									array('role','=','core_roles.id','LEFT'),
	  									'core_roles'),
								array('core_users.*','session','core_roles.name as rolename'),
								array(	array('session','=',$_SESSION['session']),
										'AND',
										array('ip','=',$_SERVER['REMOTE_ADDR']),
										'AND',
										array('core_users.active','=',1),
									), 
								null, 1
								);
			if(count($user)){
				return $user[0];
			}
	  	}
	  	return false;
	}
	
	private function setSessions($aUser) {
		$_SESSION['session'] = session_id();
		
  		cCfg::setUserData('id',$aUser['id']);
		cCfg::setUserData('name',$aUser['name']);
  		cCfg::setUserData('surname',$aUser['surname']);
  		cCfg::setUserData('roleid',$aUser['role']);
  		cCfg::setUserData('role',$aUser['rolename']); 		
	}
	
	private function clearSessions() {
		unset($_SESSION['session']);
	}
	
	private function setCookies($aUser) {
		setcookie("CORE_user",$aUser['id'], time() + AUTH_TIMEOUT, "/");
  		setcookie("CORE_name",$aUser['name'], time() + AUTH_TIMEOUT, "/");
  		setcookie("CORE_surname",$aUser['surname'], time() + AUTH_TIMEOUT, "/");
	}
	
	private function clearCookies() {
		setcookie("CORE_user",'', time() - 3600, "/");
		unset($_COOKIE['CORE_user']);
		setcookie("CORE_name",'', time() - 3600, "/");
		unset($_COOKIE['CORE_name']);
		setcookie("CORE_surname",'', time() - 3600, "/");
		unset($_COOKIE['CORE_surname']);	
	}
	
	public function hmac_md5($key, $data) {
		$blocksize = 64;
	    if (strlen($key) > $blocksize) {
	        $key = pack("H*", md5($key));
	    }
	    $key = str_pad($key, $blocksize, chr(0x00));
	    $k_ipad = $key ^ str_repeat(chr(0x36), $blocksize);
	    $k_opad = $key ^ str_repeat(chr(0x5c), $blocksize);
	    
	    return md5($k_opad . pack("H*", md5($k_ipad . $data)));
	}
	
	public function hmac_sha1($key, $data) {
		$blocksize = 64;
	    if (strlen($key) > $blocksize) {
	        $key = pack("H*", sha1($key));
	    }
	    $key = str_pad($key, $blocksize, chr(0x00));
	    $k_ipad = $key ^ str_repeat(chr(0x36), $blocksize);
	    $k_opad = $key ^ str_repeat(chr(0x5c), $blocksize);
	    
	    return sha1($k_opad . pack("H*", sha1($k_ipad . $data)));
	}
	
	public function cyphrePassword($passw) {
		return sha1($passw);
	}
	
	public function getErrors() {
		return $this->aErrors;
	}
}

?>