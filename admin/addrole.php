<?php
if(!isset($_index_rights)){
	header("Location: ".getUrl()."admin");
}

$Logs = new cLogs("addrole.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['add_role'])) {	
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	/// max. 100 symbols ///
	if($Check->check('name', 'strlen($test) > 0 && strlen($test) < 101','The name of role must be max. 100 symbols!')) {
		/// check if name already exists ///
		$Check->check('name', '$test==false','The name of role already exists!',admin_roleExists($_POST['name']));
	}
	$Check->check('parentid', 'is_numeric($test) || $test=="null"','The parent role is in wrong type');
	
	$Logs->addLog($Check->isValid(), 'add new one valid');
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
	}else{
		try{
			$aVals = array();
			$aVals[] = array('name',$_POST['name']);
			if($_POST['parentid'] !== 'null') {
				$aVals[] = array('parentid',$_POST['parentid'],false);
			}
			/// insert values ///
			if(!$DB->insert('core_roles',$aVals)) {
				throw new cException("Some error during insert operation!");
			}
			$aAlerts[] = "New role was added.";	
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}
/// end of POST data sent ///

$roles = admin_getAllRoles();
 
$Logs->addLog($data,"admin_getAllCapabWithRoles");
$Logs->addLog($roles,"admin_getAllRoles");
###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
?>
<h3>Add new role</h3>
<form action="" name="fc_role" method="post">
	<fieldset>
		Name of role:<input name="name" size="100" maxLength="100" /><br />
		Parent role:
		<select name="parentid">
			<option value='null'>---</option>
			<?php foreach($roles as $role):	?>
				<option value='<?php print $role['id']?>'><?php print $role['name']?></option>
			<?php endforeach;?>
		</select>
	</fieldset>
	<input type="submit" value="Add" name="add_role" />
</form>