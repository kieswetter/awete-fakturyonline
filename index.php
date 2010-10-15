<?php
if(!session_id()){
  @session_start();
}

require_once("core/core_defines.inc.php");
require_once("core/global_fce.php");
require_once("custom_defines.inc.php");

$Logs = new cLogs("index.php");
$Logs->on();
$Logs->addLog($_POST,"POST");

$DB = new cDb();
$connection = $DB->connect();

if($connection) {
	$CFG = new cCfg();
	
	if(!isset($_GET['_pageAction_'])) {
		if(is_string($CFG->getDefaultPage('path'))) {
			header("Location: ".$CFG->getDefaultPage('path'));
		}else if(!ADMIN_PAGE_ACCESS_AUTHORIZIED){
			header("Location: ".HTTP_PATH."admin");
		}
	}
	
	$action = (get_magic_quotes_gpc() ? $_GET['_pageAction_'] : addslashes($_GET['_pageAction_']));	
	//$Logs->addLog($action,"_pageAction_");
	$CORE = new cBuildIndex($action);
	if(MK_DEBUG){
		$CORE->addCssToHead("core/logs.css");	
	}
	$Authent = new cAuthentication();
	$Authent->authenticate();
	/// user authenticated ///	
	if($CFG->isAuthenticated()) {
		$Logs->addLog("user authenticated", "authentication process");
		$CFG->setUserAccount();		
	}
	$CORE->buildPage();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if(isset($CORE)) { 
	$CORE->printHead();
}else{
	cBuildIndex::buildHead();
	cBuildIndex::printHead();
}
?>
</head>
<body>
<?php 
if(isset($CORE)) :
	$CORE->printBody();
else :
	foreach($DB->getErrors() as $err) :
	?>
		<div><?php print $err;?></div>	 
	<?php
	endforeach; 
endif;
?>
</body>
</html>