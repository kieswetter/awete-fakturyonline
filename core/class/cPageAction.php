<?php
class cPageAction {
	
	public $logs;
	public $aPageData = array();
	protected $db, $CFG;
	protected $aVars = array();
	
	private $aTemplateTree = array();
	private $aContent = array();
	
	private $aHeadJsTemps = array();
	private $aHeadCssTemps = array();
	private $sJsLib = "";
	private $sCssLib = "";
		
	protected function __construct($sPageName) {
		$this->CFG = new cCfg();
		$this->aPageData = cBuildIndex::getLngPageData($sPageName,'name');
		$this->logs = new cLogs(get_class()." - ".$sPageName.".php");
		//$this->logs->on();
		//$this->logs->addLog($this->aPageData, "aPageData");
		$this->db = new cDb();
		
		self::setTemplateTree();		
	}
	
	private final function setContent($temp) {
		$content = "";
		$oVar = new stdClass();
		try {
			$name = "tpl_".$temp['name'];
			if(requireFile('pages/tpl/php/'.$name.'.php')) {
            	$oTemp = new $name;
            } else {
				throw new cException("Php template file ".$name." not found!"); 
			}
			/// set content ///
	        $content = "";
	        $fileTpl = ROOT_PATH."pages/tpl/".$temp['name'].".tpl.php";
			if(@is_file($fileTpl) && filesize($fileTpl) > 0){
	            $handle = fopen($fileTpl, "r");
	            $content = fread($handle, filesize($fileTpl));
	            fclose($handle);
	        }
			/// set vars ///
			$oVar = new stdClass();
	        foreach($oTemp->getFinalVars() as $name => $var) {
				eval("\$oVar->$name = \$var;");		
			}
			//$this->logs->addLog($temp,"temp");
        }catch(cException $e) {
            $this->logs->addLog($e->getMessage());
        }
        $this->aContent[$temp['name']] = array("oVars" => $oVar, "content" => $content);
	}
	
	private final function setTemplateTree() {
		$tempid = $this->aPageData['template'];
		do {
			$res = $this->db->select('core_templates',null,array('id','=',$tempid));
			$this->aTemplateTree[] = $res[0];
			$tempid = $res[0]['parentid'];	
		} while(!is_null($tempid));
		
		$this->aTemplateTree = array_reverse($this->aTemplateTree);
		$this->logs->addLog($this->aTemplateTree,"aTemplateTree");
	}
	
	private final function buildContent() {
		foreach($this->aTemplateTree as $temp) {
			self::setHead($temp);
			self::setContent($temp);
		}
		/// css lib files ///
		$aCss = explode(",",$this->sCssLib);
		foreach($aCss as $css) {
			cBuildIndex::addCssToHead("pages/tpl/css/lib/".trim($css).".css");
		}
		foreach($this->aHeadCssTemps as $temp) {
			cBuildIndex::addCssToHead("pages/tpl/css/".$temp.".css");		
		}
		
		/// javascript lib files ///
		$aJs = explode(",",$this->sJsLib);
		foreach($aJs as $js) {
			cBuildIndex::addJsToHead("pages/tpl/js/lib/".trim($js).".js");
		}
		foreach($this->aHeadJsTemps as $temp) {
			cBuildIndex::addJsToHead("pages/tpl/js/".$temp.".js");		
		}
		
		/// vars of this page are not set ///
		if(!isset($this->aContent[$this->getName()])) {
			$oVar = new stdClass();
			$this->aContent[$this->getName()] = array("oVars"=>$oVar, "content" => "");
		}else{
			$oVar = $this->aContent[$this->getName()]['oVars'];	
		}
		
		foreach(self::getFinalVars() as $name => $val) {
				eval("\$oVar->".$name." = \$val;");
		}
		$this->aContent[$this->getName()]['oVars'] = $oVar;
	}
	
	private final function setHead($temp) {
		$this->sJsLib .= $temp['js'];
		$this->sCssLib .= $temp['css'];
		$this->aHeadCssTemps[] = $temp['name'];
		$this->aHeadJsTemps[] = $temp['name'];
		//cBuildIndex::addCssToHead("pages/tpl/css/".$temp['name'].".css");
		//cBuildIndex::addJsToHead("pages/tpl/js/".$temp['name'].".js");
	}
	
	public function getData($name=null) {
		if(is_null($name)){
			return 	$this->aPageData;
		}else if(isset($this->aPageData[$name])){
			return $this->aPageData[$name];
		} else {
			return null;
		}
	}
	
	public function getName() {
		return self::getData('name');	
	}
	
	protected final function addVar($value,$name) {
		$this->aVars[$name] = $value;	
	}
	
	public final function getVar($name) {
		return (isset($this->aVars[$name])? $this->aVars[$name] : null);	
	}

	public final function getFinalVars() {
		//$this->logs->addLog($this->aVars,"VARS - ".$this->getName());
		return $this->aVars;	
	}
	
	/**
	 * returns array of VARS and template content of each template of page tree
	 * @param string $tempname[optional] - name of template;<br>if it's set returns only array of VARS and content of that template
	 * @return array
	 */
	public final function getTemplatesContent($tempname = null) {
		//$this->logs->addLog($this->aContent,"TEMP(VARS)");
		if($tempname) {
			return (isset($this->aContent[$tempname])) ?  $this->aContent[$tempname] : array(); 
		}
		return $this->aContent;
	}
	
	public function end() {
		self::buildContent();
	} 
	
	function __destruct() {
		
	}
}
?>