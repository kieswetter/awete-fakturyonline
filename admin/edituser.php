<?php
if(!isset($_index_rights) || !isset($_GET['id'])){
	header("Location: ".getUrl()."admin");
}

$Logs = new cLogs("editroles.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['update_user'])) {	
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$aVals = array();
	$Check = new cCheckForm();
	$Check->check('General', 'is_numeric($test) && '.($_POST['userid'] == $_GET['id']),'There is no correct user!', $_POST['userid']);
	$Check->check('name', 'strlen($test) > 0 && strlen($test) < 101','The name must be max. 100 symbols!');
	$Check->check('surname', 'strlen($test) > 0 && strlen($test) < 101','The surname must be max. 100 symbols!');
	$Check->check('role', 'is_numeric($test)','The role is in wrong format!');
	/// change login ///
	if(strlen($_POST['login'])){
		/// max. 50 symbols ///
		$loginPat = '/^[a-zA-Z0-9_-]{1,50}$/';
		if($Check->check('login', 'preg_match("'.$loginPat.'",$test)','The login must be without whitespaces and diacritical marks and max. 50 symbols!')) {
			/// check if login already exists ///
			if($exists = admin_roleExists($_POST['login'])){
				$Logs->addLog($exists, 'user login EXISTS');
				$exists = !($exists['id'] == $_POST['userid']);
			}
			$Logs->addLog($exists, 'user login EXISTS');
			$Check->check('login', '$test==false','This login already exists!',$exists);
		}
		$aVals[] = array('login',$_POST['login']);
	}
	/// change password ///
	if(strlen($_POST['passw'])){
		$passwPat = '/^[a-zA-Z0-9_-]{1,50}$/';
		if($Check->check('password', 'strlen($test)>5 && strlen($test)<51','The password must have at least 6 symbols and max. 50, without diacritical marks and whitespaces!', $_POST['passw'])) {
			$Check->check('password', '$test==true','Verification of password is not correct!', ($_POST['passw'] == $_POST['passw_ver']));
		}
		$aVals[] = array('password', cAuthentication::cyphrePassword($_POST['passw']));
	}
	
	$Logs->addLog($Check->isValid(), 'update valid');
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
	}else{
		try{
			$aVals[] = array('name',$_POST['name']);
			$aVals[] = array('surname',$_POST['surname']);
			$aVals[] = array('role',$_POST['role'],false);
			if($_POST['active'] == '1'){
				$aVals[] = array('active',1,false);
			}else if($_POST['active'] == '0'){
				$aVals[] = array('active',0,false);
			}
			
			if(!$DB->update('core_users',$aVals, array('id','=',$_POST['userid']))) {
				throw new cException("Some error during update operation!");
			}
			$aAlerts[] = "User was updated.";	
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')');
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}
/// end of POST data sent ///

$users = admin_getAllUsers();
if( ($id = arraySearch($_GET['id'],$users,'id')) !== false ) {
	$User = $users[$id];	
}else{
	header("Location: ".getUrl()."admin");
}
$roles = admin_getAllRoles();
$Logs->addLog($users,"admin_getAllUsers");

###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
$name = $User['name'];
$surname = $User['surname'];
$roleid = $User['role'];
$active = ($User['active'] == '1') ? true : false; 
if(isset($_POST['update_user'])){
	$name=$_POST['name'];
	$surname=$_POST['surname'];
	$roleid=$_POST['role'];
	$active = ($_POST['active'] == '1') ? true : false;
}
?>
<h3>Edit user</h3>
<form action="" name="fc_editrole" method="post">
	<input type="hidden" name="userid" value="<?php print $User['id'];?>" /> 
	<fieldset>
		Name:<input name="name" value="<?php print $name?>" size="100" maxLength="100" /><br />
		Surname:<input name="surname" value="<?php print $surname?>"size="100" maxLength="100" /><br />
		Role:
		<select name="role">
			<?php foreach($roles as $role):
				$ch = $role['id'] == $roleid ? "selected='selected' " : "";
				?>
				<option <?php print $ch?>value='<?php print $role['id']?>'><?php print $role['name']?></option>
			<?php endforeach;?>
		</select><br />
		Active: <input name="active" value="1" type="radio" <?php if($active) print 'checked="checked" '?>/> Yes  
		<input name="active" value="0" type="radio" <?php if(!$active) print 'checked="checked" '?>/> No<br />
		<br />
		Login:<input name="login" value="" size="20" maxLength="50" /><br />
		Password:<input type="password" name="passw" size="20" maxLength="50" /><br />
		Verification of password(write it again):<input type="password" name="passw_ver" size="20" maxLength="50" /><br />
	</fieldset>
	<input type="submit" value="Add" name="update_user" />
</form>