<?php
class cBuildIndex {
	
	private $aPageTree = array();
	private $oPage;
	public static $aActPage;
	public $logs;
	private $aBodyVars = array();
	private $sContent = "";
	private static $sHead = "";
	private static $aHeadJs = array();
	private static $aHeadCss = array();
	private static $sTitle = TITLE;
	private $sFoot = "";
	
	function __construct($action) {
		$this->logs = new cLogs(get_class($this));
		$this->logs->on();
		$this->setActualPageTree($action);
		//$this->logs->addLog($this->aPageTree,"aPageTree");
	}
	
	public function buildHead() {
		//$this->logs->addLog(cBuildIndex::$aHeadJs, "addCssToHead");
		self::$sHead = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".ENCODING."\" />\n";
		//self::$sHead .= "<meta http-equiv=\"keywords\" content=\"$meta\" />\n";
		
		self::$sHead .= "<title>".self::$sTitle."</title>\n";
		
		foreach(cBuildIndex::$aHeadCss as $css) {
			self::$sHead .= '<link href="'.$css.'" rel="stylesheet" type="text/css" />'."\n"; 	
		}
		
		foreach(cBuildIndex::$aHeadJs as $js) {
			self::$sHead .= '<script type="text/javascript" src="'.$js.'"></script>'."\n";
		}
	}
	
	public static function addJsToHead($file) {
		if(!in_array($file,cBuildIndex::$aHeadJs) && @is_file(ROOT_PATH.$file)) {
			cBuildIndex::$aHeadJs[] = HTTP_PATH.$file;
		}
			
	}
	
	public static function addCssToHead($file) {
		if(!in_array($file,cBuildIndex::$aHeadCss) && @is_file(ROOT_PATH.$file)) {
			cBuildIndex::$aHeadCss[] = HTTP_PATH.$file;
		}
	}
		
	public function buildPage() {
		try {
			if(requireFile('pages/actions/'.$this->aPageTree[0]['name'].'.php')) {
            	$this->oPage = new $this->aPageTree[0]['name'];
            } else {
				throw new cException("Action ".$this->aPageTree[0]['name']." not found!"); 
			}
        }catch(cException $e) {
            $this->logs->addLog($e->getMessage());
        }
    	self::$aActPage = $this->oPage->getData();
		self::$sTitle .= " - ".$this->oPage->aPageData['title'];
    	$this->oPage->end();
        self::buildHead();
        self::buildBody();
	}

	private function buildBody() {
		$page = $this->oPage->getData('name');
		
		$aCont = array();
		/// content of all templates ///
		foreach($this->oPage->getTemplatesContent() as $name => $aTemp) {
			array_push($aCont, array($aTemp['content'],$name)); 
		}		

		if($this->oPage->getData('usecache')){
			$filename = ROOT_PATH."cache/".$page."_".cCfg::$lng.".php";
			/// filen in cache exists ///
			if($handle = @fopen($filename,"r+")) {			
				$tpl = fread($handle, filesize($filename));
			} else {
				$handle = @fopen($filename,"w+");						
				$tpl = self::buildTplContent($aCont);
				fwrite($handle, self::buildTplContent($aCont));				
			}
			fclose($handle);
		} else {
			$tpl = self::buildTplContent($aCont);
		}
		
		$this->sContent .= $tpl;
	}
	
	private function buildTplContent($aCont) {
		$aBody = array_shift($aCont);
		$body = $aBody[0];
		/// there are still children templates ///
		if(count($aCont)) {				
			$sub = self::buildTplContent($aCont);
			$body = preg_replace("/<\?php\s+print\s+(\W{1})(sub_content;\s*\?>)/", $sub, $body);			
		}
		
		return $body;
		
	}
	
	public function addToBody($content) {
		$this->sContent .= "\n$content\n";
	}
	
	public function printHead() {
		print self::$sHead;
	}
	
	public function printBody() {		
		/// vars from all templates formated to object ///
		foreach($this->oPage->getTemplatesContent() as $name => $aTemp) {
			$this->logs->addVarsLog($aTemp['oVars'],$name);
			eval("\$".$name."=\$aTemp['oVars'];"); 
		}		

		$this->sContent = eval("\n?>\n".$this->sContent."\n<?php\n");
	}
	
	private function setActualPageTree($sAction) {
		switch ($sAction) :
			case ("") :
	        	$this->aPageTree = self::getPageTree(cCfg::getDefaultPage('id'));        
		      break;
		    
			default :
		        $page = self::getLngPageData($sAction, 'url');
		        /// page is published ///
		        if($page['published'] == '1'){
					$this->aPageTree = self::getPageTree($page['id']);
		        }				
			break;
      	endswitch;

      	if(!count($this->aPageTree)){
      		$page = self::getPageData(cCfg::$sPageNotFound, 'name');
      		$this->aPageTree = self::getPageTree($page['id']);	
      	}
	}
	
	/**
	 * return object of actual page
	 * @return object
	 */
	public static function getActualPage() {
		return self::$aActPage;
	}
	/**
	 * get all data from DB about the the language set of page according to name of page/action;
	 * @param mixed $paramValue - can be number or string; value of column to be selected from DB
	 * @param string $paramName[optional][default 'name'] - name of column in table core_pages; the query is based on it;
	 * <p><strong>allowed: id, name, url, title, menutitle</strong></p>
	 * @param string $lng[otpional][default ''] 
	 * @return array - if no record about this pageName, returns empty array otherwise return the first record
	 */
	public function getLngPageData($paramValue,$paramName = 'name', $lng = "") {
		$paramValue = (get_magic_quotes_gpc() ? $paramValue : addslashes($paramValue));
		
		if($lng == "") {
			$lng = cCfg::$lng;
		}
		
		if(in_array($paramName, array("id", "name"))) {
			$aPageVal = array('page.'.$paramName,'=',$paramValue);
		} else if(in_array($paramName, array("url","title","menutitle"))) {
			$aPageVal = array('lngpage.'.$paramName,'=',$paramValue);
		} else {
			return array();
		}
		
		$result = cDb::select(array('core_pages as page',array('page.id','=','page','LEFT'),'core_lngpages as lngpage'),
							array('page.id as id','parentid','name','template','user','page.timecreated','page.timemodified','timeexpired','published','usecache',
								'lngpage.id as lngpageid','lng','title','url','menutitle'),
							array($aPageVal,'AND',
									array(array('lng','=',$lng),'OR',array('lng','=',null))
							),
							'lng DESC',
							1
							);
	
		if(is_array($result) && count($result)) {
			$result[0]['href'] = self::getPageUrl($result[0]);
			return $result[0];
		} else {
			return array();
		}
	}
	
/**
	 * get all data from DB about the page
	 * @param mixed $paramValue - can be number or string; value of column to be selected from DB
	 * @param string $paramName[optional][default 'name'] - name of column in table core_pages; the query is based on it;
	 * <p><strong>allowed: id, name</strong></p> 
	 * @return array - if no record about this pageName, returns empty array otherwise return the first record
	 */
	public function getPageData($paramValue,$paramName = 'name') {
		$paramValue = (get_magic_quotes_gpc() ? $paramValue : addslashes($paramValue));
		
		if(in_array($paramName, array("id", "name"))) {
			$aPageVal = array($paramName,'=',$paramValue);
		} else {
			return array();
		}
		
		$result = cDb::select('core_pages', '*', array($aPageVal));
	
		if(is_array($result) && count($result)) {
			return $result[0];
		} else {
			return array();
		}
	}
	
	/**
	 * create complete url of the page/action according to it's parents as well
	 * @param string $sActionName - name of php file in pages/actions and column name in core_pages
	 * @return string - url; if a record in DB doesn't exist, returns getUrl()
	 */
	public static function getActionUrl($sActionName) {
		$result = self::getLngPageData($sActionName);
		return $result['href'];
	}
	
	/**
	 * create complete url of the page/action according to it's lng and parents pages as well
	 * @param array $aPagedata - data of lng page (needs to contain elements: parentid, url, lng)
	 * @return string - url; if a record in DB doesn't exist, returns getUrl()
	 */
	public function getPageUrl($aPagedata){
		/// page does't have parents pages ///
		if(is_null($aPagedata['parentid'])) {
			return getUrl($aPagedata['url']); 	
		}
		
		$aPages = self::getPageTree(intval($aPagedata['parentid']), $aPagedata['lng']);
		
		$url = "";
		foreach($aPages as $page) {
			$url .= $page['url']."/";
		}
		$url .= $aPagedata['url'];
		
		return getUrl($url);
	}
	
	/**
	 * goes through the tree of parent pages and return the array of associated arrays of each page
	 * @param number $pageid - id of page for which to get the parent tree
	 * @param string $lng[optional][default '']
	 * @return array
	 */
	public function getPageTree($pageid, $lng = '') {
		$aChilds = array();
		
		if(count($r = self::getLngPageData($pageid, 'id', $lng))) {
			$aChilds[] = $r;	
		}
		//$this->logs->addLog($r,"result pageTree");
		while(!is_null($r['parentid'])) {
			$aChilds[] = self::getPageTree(intval($r['parentid']), $lng);
		}
		return $aChilds;//array('id'=>$r['id'],'name'=>$r['name'],'url'=>$r['url']);
	}
	
	public function end() {
		
	}
	
	function __destruct() {

	}
}
?>