<?php
if(!isset($_index_rights)){
	header("Location: ".getUrl()."admin");
}

$Logs = new cLogs("addcapability.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['add_capab'])) {	
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	$namePat = '/^[a-zA-Z0-9_-]{1,50}$/';
	if($Check->check('name', 'preg_match("'.$namePat.'",$test)','The NAME must be without whitespaces and diacritical marks and max. 50 symbols!')){
		$Check->check('name', '$test==false','This capability already exists!',admin_capabExists($_POST['name']));
	}
	$Check->check('description', 'strlen($test) > 0 && strlen($test) < 266','The description of capability is required with max length 255 symbols!');
	
	$Logs->addLog($Check->isValid(), 'add new one valid');
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
	}else{
		try{
			$aVals = array();
			$aVals[] = array('name',$_POST['name']);
			$aVals[] = array('description',$_POST['description']);
			/// insert values ///
			if(!$DB->insert('core_capabilities',$aVals)) {
				throw new cException("Some error during insert operation!");
			}
			$aAlerts[] = "New capability waw added.";	
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}
/// end of POST data sent ///

###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
?>
<h3>Add new capability</h3>
<form action="" name="fc_capabilities" method="post">
	<fieldset>
		Name of capability:<input name="name" size="10" maxLength="50" /><br />
		Description:<textarea name="description"></textarea><br />
	</fieldset>
	<input type="submit" value="Add" name="add_capab" />
</form>
