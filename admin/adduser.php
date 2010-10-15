<?php
if(!isset($_index_rights)){
	header("Location: ".getUrl()."admin");
}

$Logs = new cLogs("adduser.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['add_user'])) {	
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	$Check->check('name', 'strlen($test)>0 && strlen($test)<101','The name is required with max 100 symbols including whitespaces!');
	$Check->check('surname', 'strlen($test)>0 && strlen($test)<101','The surname is required with max 100 symbols including whitespaces!');
	$Check->check('role', 'is_numeric($test)','The role is required!');
	/// max. 50 symbols ///
	$loginPat = '/^[a-zA-Z0-9_-]{1,50}$/';
	if($Check->check('login', 'preg_match("'.$loginPat.'",$test)','The login must be without whitespaces and diacritical marks and max. 50 symbols!')) {
		/// check if login already exists ///
		$Check->check('login', '$test==false','This login already exists!',admin_userLoginExists($_POST['login']));
	}
	$passwPat = '/^[a-zA-Z0-9_-]{1,50}$/';
	if($Check->check('password', 'strlen($test)>5 && strlen($test)<51','The password must have at least 6 symbols and max. 50, without diacritical marks and whitespaces!', $_POST['passw'])) {
		$Check->check('password', '$test==true','Verification of password is not correct!', ($_POST['passw'] == $_POST['passw_ver']));
	}
	
	$Logs->addLog($Check->isValid(), 'add new one valid');
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
	}else{
		try{
			$aVals = array();
			$aVals[] = array('name',$_POST['name']);
			$aVals[] = array('surname',$_POST['surname']);
			$aVals[] = array('login',$_POST['login']);
			$aVals[] = array('password', cAuthentication::cyphrePassword($_POST['passw']));
			$aVals[] = array('role',$_POST['role'],false);
			
			/// insert values ///
			if(!$DB->insert('core_users',$aVals)) {
				throw new cException("Some error during insert operation!");
			}
			$aAlerts[] = "New user was added.";	
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}
/// end of POST data sent ///

$roles = admin_getAllRoles();
 
$Logs->addLog($roles,"admin_getAllRoles");
###########################################################################################
##################################### code to print #######################################
foreach($aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}
$name = "";
$surname = "";
$roleid = "";
$login = "";
if(isset($_POST['add_user'])){
	$name=$_POST['name'];
	$surname=$_POST['surname'];
	$roleid=$_POST['role'];
	$login=$_POST['login'];
}
?>
<h3>Add new user</h3>
<form action="" name="fc_user" method="post">
	<fieldset>
		Name:<input name="name" value="<?php print $name?>" size="20" maxLength="50" /><br />
		Surname:<input name="surname" value="<?php print $surname?>"size="20" maxLength="50" /><br />
		Role:
		<select name="role">
			<?php foreach($roles as $role):
				$ch = $role['id'] == $roleid ? "selected='selected' " : "";
				?>
				<option <?php print $ch?>value='<?php print $role['id']?>'><?php print $role['name']?></option>
			<?php endforeach;?>
		</select>
		<br />
		Login:<input name="login" value="<?php print $login?>" size="15" maxLength="50" /><br />
		Password:<input type="password" name="passw" size="15" maxLength="50" /><br />
		Verification of password(write it again):<input type="password" name="passw_ver" size="15" maxLength="50" /><br />
	</fieldset>
	<input type="submit" value="Add" name="add_user" />
</form>