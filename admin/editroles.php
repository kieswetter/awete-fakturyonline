<?php
if(!isset($_index_rights) || !isset($_GET['id'])){
	header("Location: ".getUrl()."admin");
}

$Logs = new cLogs("editroles.php");
$Logs->on();
$aErrors = array();
$aAlerts = array();
global $DB;

if(isset($_POST['update_role'])) {	
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
	}
	$Check = new cCheckForm();
	$Check->check('General', 'is_numeric($test) && '.($_POST['roleid'] == $_GET['id']),'There is no correct role!', $_POST['roleid']);
	if($Check->check('name', 'strlen($test) > 0 && strlen($test) < 101','The name of role must be max. 100 symbols!')) {
		/// check if name already exists ///
		if($exists = admin_roleExists($_POST['name'])){
			$Logs->addLog($exists, 'role EXISTS');
			$exists = !($exists['id'] == $_POST['roleid']);
		}
		$Logs->addLog($exists, 'role EXISTS');
		$Check->check('name', '$test==false','The name of role already exists!',$exists);
	}
	$Check->check('description', '$test != "" && strlen($test) <= 255','Description of role is required; with a maximum length 255!');
	$Check->check('parentid', 'is_numeric($test) || $test=="null"','The parent role is in wrong type');
	$Check->check('sort', 'is_numeric($test) || strlen($test)==0','The parent role is in wrong type');
	
	$Logs->addLog($Check->isValid(), 'form valid');
	
	if(!$Check->isValid()) {			
		foreach($Check->getErrors() as $k=>$error) {
			$aErrors[]= admin_getErrorToPrint($k,$error);
		}
		//throw new cException("Form is not valid!");
	}else{
		try{
			$aVals = array();
			$aVals[] = array('name',$_POST['name']);
			$aVals[] = array('description',$_POST['description']);
			$aVals[] = array('parentid',$_POST['parentid'],false);
			
			$sort = (int)$_POST['sort'];			
			$db_sorts = $DB->select('core_roles',array('id','sort'),array('id','<>',(int)$_POST['roleid']),'sort');
			$aSort = array();
			foreach($db_sorts as $dbsort) {				
				$aSort[] = (int)$dbsort['sort'];
			}
			if(!$sort) {
				$sort = array_pop($aSort) + 10;
			} else {				
				while(in_array($sort,$aSort)) {
					$sort++;					
				}
				$rebuildSort = true;									
			}
			$aVals[] = array('sort', $sort, false);
			
			/// insert values ///
			if(!$DB->update('core_roles',$aVals, array('id','=',$_POST['roleid']))) {
				throw new cException("Some error during update operation!");
			}
			/// rebuild of sorts ///
			if(isset($rebuildSort)) {
				$db_sorts = $DB->select('core_roles',array('id','sort'),null,'sort');
				foreach($db_sorts as $k=>$sort) {
					$newsort = $k*10;
					$Logs->addLog($newsort,'newsort');
					$DB->update('core_roles',array('sort',$newsort,false), array('id','=',$sort['id']));
				}
			}
			$aAlerts[] = "Role was updated.";	
		}catch(cException $e) {
			$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
	  		$aErrors[]= $msg;
			cLogsDb::addFileLog($msg);
		}
	}
}
/// end of POST data sent ///
$roles = admin_getAllRoles();
if( ($id = arraySearch($_GET['id'],$roles,'id')) !== false ) {
	$Role = $roles[$id];	
}else{
	header("Location: ".getUrl()."admin");
}

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
<h3>Edit role</h3>
<form action="" name="fc_editrole" method="post">
	<input type="hidden" name="roleid" value="<?php print $Role['id'];?>" /> 
	<fieldset>
		Name of role:<input name="name" size="100" value="<?php print $Role['name'];?>" maxLength="100"/><br />
		Description:<textarea name="description"><?php print $Role['description'];?></textarea><br />
		Parent role:
		<select name="parentid">
			<option value='null'>---</option>
			<?php foreach($roles as $role):	
				$selected = (($Role['parentid'] == $role['id']) ? 'selected="selected " ' : '');
				?>
				<option <?php print $selected;?>value='<?php print $role['id']?>'><?php print $role['name']?></option>
			<?php endforeach;?>
		</select>
		Sort: <input name="sort" size="3" maxLength="3" value="<?php echo $Role['sort'];?>"/>
	</fieldset>
	<input type="submit" value="Save" name="update_role" />
</form>