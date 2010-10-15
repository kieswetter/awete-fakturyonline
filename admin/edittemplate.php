<?php
if(!isset($_index_rights) || !isset($_GET['id'])){
	header("Location: ".getUrl()."admin");
}

##########################################################################################
###################################### code ##############################################

$Logs = new cLogs("edittemplate.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['submit']) && (isset($_POST['tempid']) && is_numeric($_POST['tempid']))) {
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	$Check->check('js', 'preg_match("/^(([a-zA-Z0-9_-]+(\.)?[a-zA-Z0-9_-]+)+(,([a-zA-Z0-9_-]+((\.)?[a-zA-Z0-9_-]+)*)+)*)?$/",$test)','The wrong type of string in JS field!');
	$Check->check('css', 'preg_match("/^(([a-zA-Z0-9_-]+(\.)?[a-zA-Z0-9_-]+)+(,([a-zA-Z0-9_-]+((\.)?[a-zA-Z0-9_-]+)*)+)*)?$/",$test)','The wrong type of string in CSS field!');	
	$Logs->addLog($Check->isValid(), 'valid');
	//$Logs->addLog($Check->getErrors(),'errors');	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
	}else{		
		if($_POST['parent_temp']!='null'){
			$aVals[] = array('parentid',intval($_POST['parent_temp']),false);
		} else {
			$aVals[] = array('parentid','NULL',false);
		}
		$aVals[] = array('js',$_POST['js']);
		$aVals[] = array('css',$_POST['css']);
		$aVals[] = array('timemodified',getDateToDb());
		try{
			if(!$DB->update('core_templates', $aVals, array('id','=',$tempid))) {
				throw new cException("Some error during update operation!");
			}
			$aAlerts[] = "Template was updated.";
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}


$allTemps = admin_getAllTemps();
//$Logs->addLog($allTemps, "allTemps");
//$allPages = admin_getAllPages();//add_getAllPages();
//$Logs->addLog($allPages, "allPages");

$aTempData = admin_getTempData($_GET['id']);
$Logs->addLog($aTempData, "data");
###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
?>
<form action="" name="form_edit" method="post">
<input type="hidden" name="tempid" value="<?php print $_GET['id']?>" />
<fieldset>	
	<p>
		Name: <strong><?php print $aTempData['name'];?></strong>
		<br />
		Parent template: 
		<select name="parent_temp">
			<option value="null">---</option>
			<?php foreach($allTemps as $temp):
				if($temp['id'] == $aTempData['id']) {
					continue;
				}
				$selected = (($aTempData['parentid'] == $temp['id']) ? 'selected="selected" ' : ''); 
			?>
				<option value="<?php print $temp['id'];?>" <?php print $selected;?>><?php print $temp['name'];?></option>	
			<?php endforeach;?>
		</select>
		<br />
		JS files:<input name="js" value="<?php print $aTempData['js'];?>" /> (fill only names of js files without sufixes separeted by comma; they should by placed in pages/tpl/js/lib)
		<br />
		CSS files:<input name="css" value="<?php print $aTempData['css'];?>" /> (fill only names of css files without sufixes separeted by comma; they should by placed in pages/tpl/css/lib)
	</p>
	<input type="submit" name="submit" value="submit" />
</fieldset>
</form> 