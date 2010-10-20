<?php
if(!session_id()){
  @session_start();
}

require_once("../core/core_defines.inc.php");
require_once(ROOT_PATH."core/global_fce.php");
requireFile("admin/admin_fce.php");

$DB = new cDb();
$DB->connect();
$CFG = new cCfg();

$Logs = new cLogs("index.php");
$Logs->on();
$Logs->addLog($_POST,"POST");
$_aErrors = array();
$_aAlerts = array();

$Authent = new cAuthentication();
$bAut = $Authent->authenticate();
if( (!$bAut && ADMIN_PAGE_ACCESS_AUTHORIZIED) ||
	($bAut && !$CFG->hasCapability('superadmin')) ){
	header("Location: ".HTTP_PATH);
}

$_index_rights = true;

foreach($_GET as $k=>$v){
		$_GET[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
}
/// update capabilities ///
if(isset($_POST['update_capab'])) {
	_updateCapabilities();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<link href="css/admin.css" rel="stylesheet" type="text/css" />
	<link href="<?php print getUrl()?>core/logs.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/admin.js"></script>
	<title>administrace</title>
</head>
<body>
<?php
if(isset($_GET['show'])) : ?>
	<script type="text/javascript">
		show = '<?php print $_GET['show'];?>';
	</script>
<?php endif;
print "<ul class='top_links'>";
print "<li><a href='".getUrl()."' title='Homepage'>Get out of admin</a></li>";
print "<li><a href='".getUrl()."?logout' title='Logout'>Logout</a></li>";
print "</ul>";
print "<ul class='sub_links'>";
	_showActions('add','Add new page / Add new template');
	_showActions('addrole', 'Add new role');
	_showActions('adduser', 'Add new user');
	_showActions('addcapability', 'Add new capability');
	_showActions('updatelng','Update languages');
print "</ul>";

foreach($_aErrors as $error) {
	print "<div class='error'>$error</div>";
}
foreach($_aAlerts as $alert) {
	print "<div class='alert'>$alert</div>";
}

if(isset($_GET['action'])) {
	print "<ul class='sub_links other'>";
	print "<li><a href='".getUrl()."admin' title='Back'>Back to admin homepage</a></li>";
	print "</ul>";
	requireFile("admin/".trim($_GET['action']).".php");
} else {
	?>
		<div id="show_pagetree" class="switcher">pages</div>
		<div id="show_temptree" class="switcher">templates</div>
		<div id="show_roletree" class="switcher">roles</div>
		<div id="show_usertree" class="switcher">users</div>
		<div id="show_capatree" class="switcher">capabilities</div>
		<div class="clear"></div>
	<?php 
	print "<div id='page_tree'>";
		_showPageTree();
	print "</div>";
	print "<div id='template_tree'>";
		_showTemplateTree();
	print "</div>";
	print "<div id='roles_tree'>";
		_showRoles();
	print "</div>";
	print "<div id='users_tree'>";
		_showUsers();
	print "</div>";
	print "<div id='capab_tree'>";
		_showCapabilities();
	print "</div>";
}
?>

</body>
</html>

<?php
####################################### functions ######################################### 
function _showActions($href,$title) {
	print "<li><a href='".getUrl("admin/index.php")."?action=$href"."' title='$title'>$title</a></li>";
}

function _showPageTree() {
	global $Logs;
	$aPages = admin_getAllPages();
	//$Logs->addLog($aPages,"aPages");
?>
	<h3>Pages</h3>
	<table border="1">
		<thead>
		<tr>
			<td rowspan="2">Name</td>
			<td>Template(id)</td>
			<td>Use cache</td>
			<td>User</td>
			<td>Time created / last modified</td>
			<td>Published</td>
			<td rowspan="2">&nbsp;</td>
		</tr>
		<tr>
			
			<td>Language</td>
			<td>Url</td>
			<td>Title</td>
			<td colspan="2">Title in menu</td>
		</tr>
		</thead>
<?php 
	foreach($aPages as $page) :
		//var_dump($page);
		$editTemp = "<a href=''"
?>
		<tr>
			<th rowspan="<?php print (count($page['lng'])+1); ?>"><?php print $page['name']?></th>
			<td>
				<a title="edit template" href="<?php getUrl('admin')?>?action=edittemplate&id=<?php print $page['template'];?>">
					<?php print $page['tempname']?>
				</a>
			</td>
			<td><?php print $page['usecache']?></td>
			<td><?php print $page['user']?></td>
			<td>
				<?php 	print getDateToPrintFromDb($page['timecreated']);
						print "/<br />".getDateToPrintFromDb($page['timecreated']);
				?>
			</td>
			<td><?php print $page['published'];?></td>
			<td rowspan="<?php print (count($page['lng'])+1); ?>">
				<a href="<?php getUrl('admin')?>?action=editpage&id=<?php print $page['id'];?>">edit</a>
			</td>
		</tr>
<?php 	
		foreach($page['lng'] as $lng=>$aLng) :
?>
			<tr>
				
				<td><?php print !is_string($lng) ? "-all-" : $lng; ?></td>
				<td><?php print $aLng['url']?></td>
				<td><?php print $aLng['title']?></td>
				<td colspan="2"><?php print !strlen($aLng['menutitle']) ? $aLng['title'] : $aLng['menutitle']?></td>
			</tr>
<?php 
		endforeach;
	endforeach;
?>
	</table>
<?php 
}

function _showTemplateTree() {
	global $Logs;
	$aTemps = admin_getAllTemps();
	//$Logs->addLog($aTemps,"aPages");
?>
	<h3>Templates</h3>
	<table border="1">
		<thead>
		<tr>
			<td>Name</td>
			<td>Parent Template</td>
			<td>Time created / last modified</td>
			<td>Javascript files</td>
			<td>CSS files</td>
			<td>&nbsp;</td>
		</tr>
		</thead>
<?php 
	foreach($aTemps as $temp) :
		if(!is_null($temp['parentid'])){
			$parent = admin_getTempData($temp['parentid']);
			$parent_name = $parent['name'];			
		}else{
			$parent_name = '---';
		}
	//var_dump($temp);
?>
		<tr>
			<th><?php print $temp['name']?></th>
			<td><?php print $parent_name?></td>
			<td>
				<?php 	print getDateToPrintFromDb($temp['timecreated']);
						print "/<br />".getDateToPrintFromDb($temp['timemodified']);
				?>
			</td>
			<td><?php print str_replace(',','<br />',$temp['js']);?></td>
			<td><?php print str_replace(',','<br />',$temp['css']);?></td>
			<td>
				<a href="<?php getUrl('admin')?>?action=edittemplate&id=<?php print $temp['id'];?>">edit</a>
			</td>
		</tr>
<?php
	endforeach;
?>
	</table>
<?php 
}

function _showRoles(){
	global $Logs;
	$roles = admin_getAllRoles();
?>
	<h3>Roles</h3>
	<table border="1">
		<thead>
		<tr>
			<td>Name</td>
			<td>Parent role</td>
			<td>Description</td>
			<td>Sort</td>
			<td>Edit</td>
		</tr>
		</thead>

		<?php foreach($roles as $role):	?>
			<tr>
				<th><?php print $role['name']?></th>
				<td><?php $parent = arraySearch($role['parentid'],$roles,'id');?>					
					<?php if($parent !== false)print $roles[$parent]['name']; ?>
				</td>
				<td><?php print $role['description']?></td>
				<td><?php print $role['sort']?></td>
				<td>
					<a href="<?php getUrl('admin')?>?action=editroles&id=<?php print $role['id'];?>">edit</a>
				</td>
			</tr>
		<?php endforeach;?>
	</table>
<?php 	
}

function _showUsers(){
	global $Logs;
	$users = admin_getAllUsers();
	$roles = admin_getAllRoles();
?>
	<h3>Users</h3>
	<table border="1">
		<thead>
		<tr>
			<td>Name</td>
			<td>Surname</td>
			<td>Login</td>
			<td>Role</td>
			<td>Active</td>
			<td>&nbsp;</td>
		</tr>
		</thead>

		<?php foreach($users as $user):	?>
			<tr>
				<td><?php print $user['name']?></td>
				<td><?php print $user['surname']?></td>
				<td><?php print $user['login']?></td>
				<td><?php $parent = arraySearch($user['role'],$roles,'id');?>					
					<?php if($parent !== false)print $roles[$parent]['name']; ?>
				</td>
				<td><?php print ((int)$user['active'] ? 'Yes' : 'No');?></td>
				<td>
					<a href="<?php getUrl('admin')?>?action=edituser&id=<?php print $user['id'];?>">edit</a>
				</td>
			</tr>
		<?php endforeach;?>
	</table>
<?php 	
}

function _showCapabilities() {
	global $Logs;
	$data = admin_getAllCapabWithRights();
	$roles = admin_getAllRoles();		
?>
	<h3>Edit capabilities</h3>
	<form action="?show=capa" name="fc_capabilities" method="post">
		<table border="1">
			<thead>
			<tr>
				<td>Capability</td>
				<td>Description</td>
				<?php foreach($roles as $role):	?>
					<td>
						<strong><?php print $role['name']?></strong><br />
						<?php $parent = arraySearch($role['parentid'],$roles,'id');?>					
						<?php if($parent !== false)print "(".$roles[$parent]['name'].")"; ?>
					</td>
				<?php endforeach;?>
				<!-- td>Odstranit</td-->
			</tr>
			</thead>
	
	<?php foreach($data as $cap) : ?>		
			<tr>
				<th><?php print $cap['name']?></th>
				<td><?php print $cap['description']?></td>
				<?php foreach($roles as $role):
					$checkName = "caprole_".$cap['id']."_".$role['id'];
					$checked = (in_array($role['id'],$cap['aRoles']) ? 'checked="checked"' : "");
				?>
					<td>
						<input <?php print $checked;?> type="checkbox" name="<?php print $checkName;?>" />
					</td>
				<?php endforeach;?>
				
				<!-- td>
					<a href="<?php getUrl('admin')?>?action=deleteCap&id=<?php print $cap['id'];?>">odstranit</a>
				</td-->
			</tr>
	<?php endforeach;?>
		</table>
		<input type="submit" value="Save" name="update_capab" />
	</form>
<?php 
}

function _updateCapabilities() {
	global $DB;
	global $_aErrors;
	global $_aAlerts;
	
	$Check = new cCheckForm();
	$colsToDb = array(array('capability',false), array('role',false));
	$dataToDb = array();
	foreach($_POST as $k=>$v){
		$_POST[$k] = (get_magic_quotes_gpc() ? trim($v) : trim(addslashes($v)));
		$aDat = explode('_',$k,3);
		/// post data with values for core_role_capability ///
		if($aDat[0] == "caprole" && count($aDat) == 3) {			
			$Check->check('cap', 'is_numeric($test)','The id of capability is in wrong type',$aDat[1]);
			$Check->check('role', 'is_numeric($test)','The id of role is in wrong type',$aDat[2]);
			$dataToDb[] = array($aDat[1],$aDat[2]);
		}		
	}
	//$Logs->addLog($Check->isValid(), 'valid');
	
	try{
		if(!$Check->isValid()) {			
			foreach($Check->getErrors() as $k=>$error) {
				$_aErrors[]= implode("(<strong>$k</strong>)<br />",$error['msg'])."(<strong>$k</strong>)";
			}
			throw new cException("Form is not valid!");
		}
		/// make backup of original table ///
		if(!$DB->createCopyOfTable('core_role_capability','core_role_capability_back')){
			throw new cException("Some error during backup operation of old data!");
		}
		/// empty original table ///
		if(!$DB->truncateTable('core_role_capability')){
			/// drop backup table ///
			$DB->dropTable('core_role_capability_back');
			throw new cException("Some error during insert operation!");
		}
		/// insert new values to original table ///
		if(!$DB->insertMore('core_role_capability',$colsToDb,$dataToDb)) {
			/// copy data from backup to original table ///
			$DB->createCopyOfTable('core_role_capability_bak','core_role_capability');
			throw new cException("Some error during insert operation!");
		}
		/// empty backup table ///
		$DB->dropTable('core_role_capability_back');
		
		$_aAlerts[] = "Capabilities were updated.";	
	}catch(cException $e) {
		$msg = $e->getDbMessageError(__METHOD__.'(line:'.__LINE__.')',$query);
  		$_aErrors[]= $msg;
		cLogsDb::addFileLog($msg);
	}
}
?>