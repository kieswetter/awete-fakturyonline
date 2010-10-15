<?php
if(!isset($_index_rights)){
	header("Location: ".getUrl()."admin");
}

function add_insertNewTemplate() {
	global $DB;
	$aVals = array(array('name',$_POST['newtemp']));
	if($_POST['parent_temp']!='null'){
		$aVals[] = array('parentid',intval($_POST['parent_temp']),false);
	}
	if($bTemp = $DB->insert('core_templates', $aVals)) {
		$tempid = $DB->getLastId();
		
		$hTemp = fopen(ROOT_PATH."pages/tpl/".$_POST['newtemp'].".tpl.php","w+");
		fclose($hTemp);
		$hTemp = fopen(ROOT_PATH."pages/tpl/css/".$_POST['newtemp'].".css","w+");
		fclose($hTemp);
		$hTemp = fopen(ROOT_PATH."pages/tpl/js/".$_POST['newtemp'].".js","w+");
		fclose($hTemp);

		$hTemp = fopen(ROOT_PATH."pages/tpl/php/tpl_".$_POST['newtemp'].".php","w+");
		$toFile = "<?php
class tpl_".$_POST['newtemp']." extends cTemplate {
	
	public function __construct() {
		parent::__construct(substr(get_class($this),4));
		self::action();		
	}
	
	private function action() {
		// here goes your code //
	}
}
?>";
		fwrite($hTemp,$toFile);
		fclose($hTemp);
		
	} else {
		$tempid = false;
	}			
	
	return (is_numeric($tempid) ? intval($tempid) : false);
}

function add_insertNewPage() {
	global $DB;
	
	if($_POST['temp']=='null'){
		$tempid = add_insertNewTemplate();
	} else {
		$tempid = intval($_POST['temp']);
	}
	
	/// instance of page 'core_pages' ///
	$bPage = false;
	if(is_numeric($tempid)) {
		$aVals = array(	array('name',$_POST['page']),
						array('template',$tempid,false),
						//array('user',cCfg::getUserData('id'),false)
						array('user',0,false)
						);
		if($_POST['parent_page']!='null'){
			$aVals[] = array('parentid',intval($_POST['parent_page']),false);
		}
		if(isset($_POST['cache'])) {
			$aVals[] = array('usecache',1,false);
		}	 
		$bPage = $DB->insert('core_pages', $aVals);
		$pageid = $DB->getLastId();
	}
	
	/// language pages 'core_lngpages'///
	if($bPage) {
		$bPageLng = array();
		foreach(cCfg::$aLangs as $lng) {
			if(isset($_POST['lng_'.$lng])){
				$bLng = true;
				if($_POST['menu_title_'.$lng] == "") {
					$_POST['menu_title_'.$lng] = $_POST['title_'.$lng];
				}
				$bPageLng[$lng] = $DB->insert('core_lngpages', array( array('title',$_POST['title_'.$lng]),
													array('page',$pageid,false),
													array('lng',$lng),
													array('menutitle',$_POST['menu_title_'.$lng]),
													array('url',admin_createPageUrl($_POST['title_'.$lng]))												
							));
			}	
		}
	
		if(!count($bPageLng)) {
			$bPageLng = $DB->insert('core_lngpages', array( array('title',$_POST['title']),
													array('page',$pageid,false),
													array('menutitle',$_POST['menu_title']),
													array('url',admin_createPageUrl($_POST['title']))												
							));
		}
	}
	
	if($bPage && $bPageLng) {
		/// create php file with action ///
		$hPage = fopen(ROOT_PATH."pages/actions/".$_POST['page'].".php","w+");
		$toFile = "<?php
class ".$_POST['page']." extends cPageAction {
	
	public function __construct() {
		parent::__construct(get_class());
		self::action();	
	}
	
	private function action() {
		/// your code goes here 
	}
}
?>";
		fwrite($hPage,$toFile);
		fclose($hPage);
		
		return true;
	}
	return false;
}
##########################################################################################
###################################### code ##############################################

$Logs = new cLogs("add.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();

if(isset($_POST['add_template'])) {
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	
	$namePat = '/^([a-z]+[a-z0-9_-]+){1,50}$/';
	/// existing templates wasn't selected ///
	if($Check->check('newtemp', 'preg_match("'.$namePat.'",$test)','The name of TEMPLATE must be in lowercase letters without whitespaces and diacritical marks and max. 50 symbols! (e.g.: newhomepage or new_home-page1')) {
		$Check->check('newtemp', '$test==false','The name of TEMPLATE already exists!',admin_tempExists($_POST['newtemp']));
	}
	
	$Logs->addLog($Check->isValid(), 'valid');
	//$Logs->addLog($Check->getErrors(),'errors');	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
		$result = false;
	}else{
		$result = add_insertNewTemplate();
	}
	if($result) {
		$aAlerts[] = "Template was added.";
	}else{
		$aErrors[] = "Template wasn't added.";
	}
	$Logs->addLog($result, 'new template RESULT');
}

/// POST data to insert new page are sent ///
if(isset($_POST['add_page'])) {
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	
	$namePat = '/^([a-z]+[a-z0-9_-]+){1,50}$/';
	$titlePat = '/^([a-z]+[a-z0-9_-]+){1,100}$/';
	/// existing templates wasn't selected ///
	if($_POST['temp'] == 'null') {
		if($Check->check('newtemp', 'preg_match("'.$namePat.'",$test)' ,'The name of TEMPLATE must be in lowercase letters, starts with a letter and without whitespaces and diacritical marks and max. 50 symbols! (e.g.: newhomepage or new_home-page1')) {
			$Check->check('newtemp', '$test==false','The name of TEMPLATES already exists!',admin_tempExists($_POST['newtemp']));
		}
	}
	
	if($Check->check('page', 'preg_match("'.$namePat.'",$test)','The name of PAGE must be in lowercase letters, starts with a letter and without whitespaces and diacritical marks and max. 50 symbols! (e.g.: newhomepage or new_home-page1')) {
		$Check->check('page', '$test==false','The name of PAGE already exists!',admin_pageExists($_POST['page']));
	}
	
	$bTitle = false;
	foreach($_POST as $k=>$v) {
		if(substr($k,0,3) == 'lng'){
			/// max 100 symbols ///
			$Check->check('title'.substr($k,3), 'strlen($test) > 0 && strlen($test) < 101','Title of PAGE'.strtoupper(substr($k,3)).' is required with max. 100 symbols!');
			$bTitle = true;
		}
	}
	$Check->check('page_title', '$test == true','Title of PAGE is required!',$bTitle);
	$Logs->addLog($Check->isValid(), 'valid');
	//$Logs->addLog($Check->getErrors(),'errors');	
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
		$result = false;
	}else{
		$result = add_insertNewPage();
	}
	if($result) {
		$aAlerts[] = "Page was added.";
	}
	
	$Logs->addLog($result, 'new page RESULT');
}

$allTemps = admin_getAllTemps();
$Logs->addLog($allTemps, "allTemps");
$allPages = admin_getAllPages();//add_getAllPages();
$Logs->addLog($allPages, "allPages");
###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
?>
<form action="" name="form_add" method="post">
<fieldset>
	<h3>Create template / Select existing template</h3>
	<fieldset>
		<p>
	TEMPLATE (if existing template is selected, new will not be applied and parent as well): 
		<select name="temp">
		<option value="null">---</option>
		<?php foreach($allTemps as $temp): ?>
			<option value="<?php print $temp['id'];?>"><?php echo $temp['name'];?></option>	
		<?php endforeach;?>
		</select>
	<br />
	
	Name of new TEMPLATE: <input name="newtemp" maxLength="50" />
	<br />
	Parent TEMPLATE for new one: 
		<select name="parent_temp">
		<option value="null">---</option>
		<?php foreach($allTemps as $temp): ?>
			<option value="<?php print $temp['id'];?>"><?php print $temp['name'];?></option>	
		<?php endforeach;?>
		</select>
		</p>
		<input type="submit" name="add_template" value="submit(NEW TEMPLATE)" />
	</fieldset>
	<h3>Create new page</h3>
	<fieldset>	
		<p>
		Name of PAGE: <input name="page" maxLength="50" />
		</p><p>
		Parent PAGE (in url and pages hierarchy): 
			<select name="parent_page">
			<option value="null">---</option>
			<?php foreach($allPages as $temp): ?>
				<option value="<?php print $temp['id'];?>">
					<?php 	print $temp['name']." [";
							foreach($temp['lng'] as $lng=>$aLng) {
								print $aLng['url'];
								if(is_string($lng)){
									print "($lng) ";
								}							
							}
							print "]";
					?>
				</option>	
			<?php endforeach;?>
			</select>
		</p><p>
		Use CACHE: <input type="checkbox" name="cache"/>
		</p>
		<fieldset>
			Language (if NONE is checked, page will be available for all languages but with same title):<br />
			<p>
				None: <input name="lng" type="checkbox" value="null" />
				Title: <input name="title" maxLength="100"/><br/>
				Title for menu: <input name="menu_title" maxLength="100" />
			</p>
			<?php foreach(cCfg::$aLangs as $lng): ?>
				<p>
				<?php print $lng?>: <input name="lng_<?php print $lng?>" type="checkbox" value="<?php print $lng;?>" />
				Title: <input name="title_<?php print $lng;?>" maxLength="100" /><br/>
				Title for menu: <input name="menu_title_<?php print $lng;?>" maxLength="100" />
				</p>
			<?php endforeach;?>		
		</fieldset>
	</fieldset>
	<input type="submit" name="add_page" value="submit" />
</fieldset>
</form> 